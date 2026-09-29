<?php

namespace App\Http\Controllers\Master;

use App\Exports\ItemPenjualanExport;
use App\Exports\PenjualanExport;
use App\Exports\PenjualanExportDetail;
use App\Exports\PenjualanPelangganExport;
use App\Exports\SalesReportExport;
use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use App\Models\SalesTransaction;
use App\Models\Module;

use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesTransactionExport;
use App\Models\MainCategory;
use App\Models\PaymentType;
use App\Models\SalesTransactionItem;
use Request as Req;
use Illuminate\Support\Facades\Storage;

class SaleReportController extends BackendController
{
    function __construct()
    {
        parent::__construct();
        $this->data['page_title'] = "Sale Report Page";
        $this->data['titleHeader'] = Module::where('url', '=', Req::segment(1))->first(['icon', 'name', 'url']);
    }

    /**
     * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
     */
    public function index()
    {
        \RuleHelper::UserHasPermission('access');

        $currentDate = Carbon::now();
        $daysInMonth = $currentDate->daysInMonth;
        $monthYear = $currentDate->format('m-Y');
        $start = request()->get('start_date', '01-' . $monthYear);
        $end = request()->get('end_date', $daysInMonth . '-' . $monthYear);
        
        if (!session()->has('store')) {
            return back()->with('error_message', 'Silakan membuat entitas bisnis terlebih dahulu!');
        }
        
        $store = session()->get('store');

        $tglAwal = Carbon::parse($start)->format('Y-m-d') . ' 00:00:00';
        $tglAkhir = Carbon::parse($end)->format('Y-m-d') . ' 23:59:00';

        $this->data['tglAwal'] = Carbon::parse($start)->format('d-m-Y');
        $this->data['tglAkhir'] = Carbon::parse($end)->format('d-m-Y');

        // Ambil semua jenis pembayaran berdasarkan outlet
        $jenisPembayaran = PaymentType::whereNull('deleted_at')
            // ->where('store_id', $store['id'])
            ->select(['id', 'payment_type'])
            ->get();

        if ($jenisPembayaran->isEmpty()) {
            $dataRender = '<tr style="text-align: center;">
                            <td style="text-align: center;border: none !important;">Belum ada data ditemukan</td>
                        </tr>';
            $this->data['dataRender'] = $dataRender;

            return view('backend.pages.sale_transaction.report', $this->data);
        } else {
            $arrayTipePembayaran = [];
            foreach ($jenisPembayaran as $value) {
                $tmp = [];
                $tmp['id'] = $value->id;
                $tmp['payment_type'] = $value->payment_type;
                $tmp['total'] = 0; // Inisialisasi total pembayaran ke 0
                $arrayTipePembayaran[$value->id] = $tmp;
            }

            // Ambil data transaksi penjualan dengan join ke tabel pembayaran dan pelanggan
            $data = SalesTransaction::leftJoin('customers', 'customers.id', '=', 'sales_transactions.customer_id')
                ->leftJoin('payment_types', 'sales_transactions.payment_type', '=', 'payment_types.id')
                ->leftJoin('sale_people', 'sales_transactions.sale_people_id', '=', 'sale_people.id')
                ->select([
                    'sales_transactions.id as transaction_id',
                    'sales_transactions.date',
                    'sales_transactions.invoice_id',
                    'sales_transactions.sub_total',
                    'sales_transactions.total',
                    'sales_transactions.payment_type',
                    'sale_people.name as sales_name',
                    'sales_transactions.channel',
                    'sales_transactions.payment_status',
                    'customers.first_name as customer_first_name',
                    'customers.last_name as customer_last_name',
                    'payment_types.payment_type as payment_type_name',
                    'sales_transactions.additional_cost',
                    'sales_transactions.courier',
                ])
                ->whereNull('sales_transactions.deleted_at')
                ->where('sales_transactions.store_id', $store['id'])
                ->whereIn('sales_transactions.payment_status', ['paid', 'completed', 'shipping'])
                ->whereBetween('sales_transactions.date', [$tglAwal, $tglAkhir])
                ->get();


            $groupedData = $data->groupBy('transaction_id');
            $totalSemua = 0;
            $totalSubTotalProduk = 0;
            $totalSemuaOngkir = 0;
            $totalBelumBayar = 0;

            $dataRender = '<thead class="table-active">
        <tr>
            <th scope="col">No</th>
            <th scope="col">Tanggal</th>
            <th scope="col">Kode Transaksi</th>
            <th scope="col">Nama Sales</th>
            <th scope="col">Nama Pelanggan</th>
            <th scope="col">Sub Total Produk</th>
            <th scope="col">Ongkir</th>';

            foreach ($jenisPembayaran as $value) {
                $dataRender .= '<th scope="col">' . $value->payment_type . '</th>';
            }

            // <th scope="col">Belum Bayar</th>
            $dataRender .= '
                        <th scope="col">Status Bayar</th>
                        <th scope="col">Total</th>
                    </tr>
                </thead>
                <tbody>';

            $rowNumber = 1;

            foreach ($groupedData as $transactionId => $records) {
                $transaction = $records->first();
                $courier = is_string($transaction->courier) ? json_decode($transaction->courier, true) : $transaction->courier;
                $ecommerceOngkir = $courier['cost'] ?? 0;
                $posOngkir = $transaction->additional_cost ?? 0;
                $totalOngkir = $ecommerceOngkir + $posOngkir;

                $totalSubTotalProduk += (double) $transaction->sub_total;
                $totalSemuaOngkir += (double) $totalOngkir;

                // Kelompokkan pembayaran berdasarkan metode pembayaran
                $paymentsByType = $records->groupBy('payment_type');
                $totalPayments = $records->sum('sub_total');

                // Hitung sisa pembayaran (belum bayar)
                $belumBayar = $transaction->total - $totalPayments;

                $dataRender .= '<tr>
                <td>' . $rowNumber++ . '</td>
                <td>' . Carbon::parse($transaction->date)->format('d F Y H:i:s') . '</td>
                <td>' . $transaction->invoice_id . '</td>
                <td>' . $transaction->sales_name . '</td>
                <td>' . $transaction->customer_first_name . " " . $transaction->customer_last_name . '</td>
                <td>Rp. ' . $this->rupiah($transaction->sub_total) . '</td>
                <td>Rp. ' . $this->rupiah($totalOngkir) . '</td>';

                foreach($jenisPembayaran as $pay){
                        if(in_array($transaction->payment_status, ["paid", "shipping", "completed"])){
                            if($pay->id == $transaction->payment_type){
                                $dataRender .= '<td>Rp. ' . $this->rupiah($transaction->total) . '</td>';
                            }else{
                                $dataRender .= '<td>' .'-'. '</td>';
                            }
                        }else{
                            $dataRender .= '<td>' .'-'. '</td>';
                        }
                    }
                // Jika status paid, set "belum bayar" ke 0 walaupun ada nilai kembalian.

                if (in_array($transaction->payment_status, ["paid", "shipping", "completed"]) && isset($arrayTipePembayaran[$transaction->payment_type])) {
                    $arrayTipePembayaran[$transaction->payment_type]['total'] += (double) $transaction->total;
                }

                $status = (in_array($transaction->payment_status, ["paid", "shipping", "completed"]))
                    ? '<span class="text-success"><i class="ri-checkbox-circle-line"></i> Lunas</span>'
                    : '<span class="text-warning"><i class="ri-file-info-line"></i> Belum Bayar</span>';

                $dataRender .= '<td>' . $status . '</td>
                            <td>Rp. ' . (in_array($transaction->payment_status, ["paid", "shipping", "completed"]) ? $this->rupiah($transaction->total) : '0') . '</td>
                        </tr>';

                        if(in_array($transaction->payment_status, ["paid", "shipping", "completed"])){
                            $totalSemua += (double) $transaction->total;
                        }
            }

            $dataRender .= '</tbody>
                    <tfoot class="table-active">
                        <tr>                                                    
                            <td colspan="5" style="text-align:center;font-weight:bold;">TOTAL</td>
                            <td>Rp. ' . $this->rupiah($totalSubTotalProduk) . '</td>
                            <td>Rp. ' . $this->rupiah($totalSemuaOngkir) . '</td>';

            foreach ($arrayTipePembayaran as $pay) {
                // $totalPerPayment[$pay->payment_type] += $pay->total;
                $dataRender .= '<td>Rp. ' . $this->rupiah($pay['total']) . '</td>';
            }

            $dataRender .= '
                    <td></td>
                    <td>Rp. ' . $this->rupiah($totalSemua) . '</td>
                </tr>    
            </tfoot>';


            $this->data['dataRender'] = $dataRender;

            return view('backend.pages.sale_transaction.report', $this->data);
        }
    }

    public function preview_penjualan(Request $request)
    {
        if (!session()->has('store')) {
            return back()->with('error_message', 'Silakan membuat entitas bisnis terlebih dahulu!');
        }
        $split = explode(' to ', $request->rentang);
        $tglAwal = Carbon::parse($split[0])->format('Y-m-d') . ' 00:00:00';
        $tglAkhir = Carbon::parse($split[1] ?? $split[0])->format('Y-m-d') . ' 23:59:00';
        $store = session()->get('store');
        if ($request->jenis == 'penjualan') {
            // Ambil store dari session
            $store = session()->get('store');

            // Ambil semua jenis pembayaran berdasarkan store
            $jenisPembayaran = PaymentType::whereNull('deleted_at')
                ->select(['id', 'payment_type'])
                ->get();

            if ($jenisPembayaran->isEmpty()) {
                $dataRender = '<tr style="text-align: center;">
                                    <td style="text-align: center;border: none !important;">Belum ada data ditemukan</td>
                                </tr>';
                return json_encode(['data' => $dataRender]);
            } else {
                $arrayTipePembayaran = [];
                foreach ($jenisPembayaran as $value) {
                    $tmp = [];
                    $tmp['id'] = $value->id;
                    $tmp['payment_type'] = $value->payment_type;
                    $tmp['total'] = 0; // Inisialisasi total pembayaran ke 0
                    $arrayTipePembayaran[$value->id] = $tmp;
                }

                // Ambil data transaksi penjualan dengan join ke tabel pembayaran dan pelanggan
                $data = SalesTransaction::leftJoin('customers', 'customers.id', '=', 'sales_transactions.customer_id')
                    ->leftJoin('payment_types', 'sales_transactions.payment_type', '=', 'payment_types.id')
                    ->leftJoin('sale_people', 'sales_transactions.sale_people_id', '=', 'sale_people.id')
                    ->select([
                    'sales_transactions.id as transaction_id',
                    'sales_transactions.date',
                    'sales_transactions.invoice_id',
                    'sales_transactions.sub_total',
                    'sales_transactions.total',
                    'sales_transactions.payment_type',
                    'sale_people.name as sales_name',
                    'sales_transactions.channel',
                    'sales_transactions.payment_status',
                    'customers.first_name as customer_first_name',
                    'customers.last_name as customer_last_name',
                    'payment_types.payment_type as payment_type_name',
                    'sales_transactions.additional_cost',
                    'sales_transactions.courier',
                    ])
                    ->whereNull('sales_transactions.deleted_at')
                    ->where('sales_transactions.store_id', $store['id'])
                    ->whereIn('sales_transactions.payment_status', ['paid', 'completed', 'shipping'])
                    ->whereBetween('sales_transactions.date', [$tglAwal, $tglAkhir])
                    ->get();

                $groupedData = $data->groupBy('transaction_id');
                $totalSemua = 0;
                $totalSubTotalProduk = 0;
                $totalSemuaOngkir = 0;
                $totalBelumBayar = 0;

                $dataRender = '<thead class="table-active">
            <tr>
                <th scope="col">No</th>
                <th scope="col">Tanggal</th>
                <th scope="col">Kode Transaksi</th>
                <th scope="col">Nama Sales</th>
                <th scope="col">Nama Pelanggan</th>
                <th scope="col">Sub Total Produk</th>
                <th scope="col">Ongkir</th>';

                foreach ($jenisPembayaran as $value) {
                    $dataRender .= '<th scope="col">' . $value->payment_type . '</th>';
                }

                $dataRender .= '
                            <th scope="col">Status Bayar</th>
                            <th scope="col">Total</th>
                        </tr>
                    </thead>
                    <tbody>';

                $rowNumber = 1;

                foreach ($groupedData as $transactionId => $records) {
                    $transaction = $records->first();
                    $courier = is_string($transaction->courier) ? json_decode($transaction->courier, true) : $transaction->courier;
                    $ecommerceOngkir = $courier['cost'] ?? 0;
                    $posOngkir = $transaction->additional_cost ?? 0;
                    $totalOngkir = $ecommerceOngkir + $posOngkir;

                    $totalSubTotalProduk += (double) $transaction->sub_total;
                    $totalSemuaOngkir += (double) $totalOngkir;

                    // Kelompokkan pembayaran berdasarkan metode pembayaran
                    $paymentsByType = $records->groupBy('payment_type');
                    $totalPayments = $records->sum('sub_total');

                    $dataRender .= '<tr>
                    <td>' . $rowNumber++ . '</td>
                    <td>' . Carbon::parse($transaction->date)->format('d F Y H:i:s') . '</td>
                    <td>' . $transaction->invoice_id . '</td>
                    <td>' . $transaction->sales_name . '</td>
                    <td>' . $transaction->customer_first_name . " " . $transaction->customer_last_name . '</td>
                    <td>Rp. ' . $this->rupiah($transaction->sub_total) . '</td>
                    <td>Rp. ' . $this->rupiah($totalOngkir) . '</td>';

                    foreach($jenisPembayaran as $pay){
                        if(in_array($transaction->payment_status, ["paid", "shipping", "completed"])){
                            if($pay->id == $transaction->payment_type){
                                $dataRender .= '<td>Rp. ' . $this->rupiah($transaction->total) . '</td>';
                            }else{
                                $dataRender .= '<td>' .'-'. '</td>';
                            }
                        }else{
                            $dataRender .= '<td>' .'-'. '</td>';
                        }
                    }

                    if (in_array($transaction->payment_status, ["paid", "shipping", "completed"]) && isset($arrayTipePembayaran[$transaction->payment_type])) {
                        $arrayTipePembayaran[$transaction->payment_type]['total'] += (double) $transaction->total;
                    }

                    $status = (in_array($transaction->payment_status, ["paid", "shipping", "completed"]))
                        ? '<span class="text-success"><i class="ri-checkbox-circle-line"></i> Lunas</span>'
                        : '<span class="text-warning"><i class="ri-file-info-line"></i> Belum Bayar</span>';

                    $dataRender .= '<td>' . $status . '</td>
                                <td>Rp. ' . (in_array($transaction->payment_status, ["paid", "shipping", "completed"]) ? $this->rupiah($transaction->total) : '0') . '</td>
                            </tr>';

                    if(in_array($transaction->payment_status, ["paid", "shipping", "completed"])){
                            $totalSemua += (double) $transaction->total;
                    }
                }

                $dataRender .= '</tbody>
                        <tfoot class="table-active">
                            <tr>                                                    
                                <td colspan="5" style="text-align:center;font-weight:bold;">TOTAL</td>
                                <td>Rp. ' . $this->rupiah($totalSubTotalProduk) . '</td>
                                <td>Rp. ' . $this->rupiah($totalSemuaOngkir) . '</td>';

                foreach ($arrayTipePembayaran as $pay) {
                    // Just display the actual total for each payment type
                    $dataRender .= '<td>Rp. ' . $this->rupiah($pay['total']) . '</td>';
                }

                $dataRender .= '<td></td>
                        
                        <td>Rp. ' . $this->rupiah($totalSemua) . '</td>
                    </tr>    
                </tfoot>';

                return json_encode(['data' => $dataRender]);
            }
        }

        if ($request->jenis == 'detail_penjualan') {
            $store = session()->get('store');

            // Ambil semua jenis pembayaran berdasarkan outlet
            $jenisPembayaran = PaymentType::whereNull('deleted_at')
                // ->where('store_id', $store['id'])
                ->select(['id', 'payment_type'])
                ->get();

            if ($jenisPembayaran->isEmpty()) {
                $dataRender = '<tr style="text-align: center;">
                                    <td style="text-align: center;border: none !important;">Belum ada data ditemukan</td>
                                </tr>';
                return json_encode(['data' => $dataRender]);
            } else {
                $arrayTipePembayaran = [];
                foreach ($jenisPembayaran as $value) {
                    $tmp = [];
                    $tmp['id'] = $value->id;
                    $tmp['name'] = $value->payment_type;
                    $tmp['total'] = 0; // Inisialisasi total pembayaran ke 0
                    $arrayTipePembayaran[$value->id] = $tmp;
                }

                // Ambil data transaksi penjualan dengan join ke tabel pelanggan saja
                $data = SalesTransaction::leftJoin('customers', 'customers.id', '=', 'sales_transactions.customer_id')
                ->leftJoin('payment_types', 'payment_types.id', '=', 'sales_transactions.payment_type')
                ->select([
                        'sales_transactions.id as transaction_id',
                        'sales_transactions.date',
                        'sales_transactions.invoice_id',
                        'sales_transactions.sub_total',
                        'sales_transactions.total',
                        'sales_transactions.payment_type',
                        'sales_transactions.channel',
                        'sales_transactions.payment_status',
                        'customers.first_name as customer_first_name',
                        'customers.last_name as customer_last_name',
                        'payment_types.payment_type as payment_type_name',
                        'sales_transactions.discount_percent',
                        'sales_transactions.discount_nominal',
                        'sales_transactions.additional_cost',
                        'sales_transactions.courier',
                    ])
                    ->whereNull('sales_transactions.deleted_at')
                    ->where('sales_transactions.store_id', $store['id'])
                    ->whereIn('sales_transactions.payment_status', ['paid', 'completed', 'shipping'])
                    ->whereBetween('sales_transactions.date', [$tglAwal, $tglAkhir])
                    ->get();

                $totalSemua = 0;
                $totalSubTotal = 0;
                $totalBelumBayar = 0;

                $dataRender = '<thead class="table-active">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Kode Transaksi</th>
                    
                    <th>Nama Pelanggan</th>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Sub Total Item</th>
                    <th>Sub Total</th>
                    <th>Ongkir</th>
                    <th>Diskon</th>';

                foreach ($jenisPembayaran as $value) {
                    $dataRender .= '<th scope="col">' . $value->payment_type . '</th>';
                }

                $dataRender .= '
                                <th scope="col">Status Bayar</th>
                                <th scope="col">Total</th>
                            </tr>
                        </thead>
                        <tbody>';

                $rowNumber = 1;
                $priceListTotal = 0;
                $subTotalListAkhir = 0;
                $totalSemuaOngkir = 0;
                $totalSemuaDiskon = 0;

                foreach ($data as $transaction) {
                    $transactionId = $transaction->transaction_id;
                    $totalSubTotal += (double) $transaction->sub_total;

                    // Ambil data pembayaran untuk transaksi ini dari tabel transaction_sales_payments
                    $items = SalesTransactionItem::where('sales_transaction_id', $transactionId)
                    ->join('product_details', 'product_details.id', '=', 'sales_transaction_items.product_detail_id')
                    ->join('products', 'products.id', '=', 'product_details.product_id')
                    ->leftJoin('variants', 'product_details.variant_id', '=', 'variants.id')
                    ->leftJoin('colors', 'product_details.color_id', '=', 'colors.id')
                    ->select(
                        'sales_transaction_items.qty',
                        'products.title as product_name',
                        'sales_transaction_items.final_price as product_price',
                        'variants.name as variant',
                        'colors.label as color'
                        )
                        ->get();

                    // Kelompokkan pembayaran berdasarkan metode pembayaran
                    $paymentsByType = [];
                    $totalPayments = 0;

                    // Jika tidak ada pembayaran di transaction_sales_payments, gunakan user_payment
                    if ($totalPayments == 0 && $transaction->user_payment > 0) {
                        $totalPayments = (double) $transaction->user_payment;
                        $paymentsByType[$transaction->main_payment_type_id] = $transaction->user_payment;
                    }

                    // Hitung sisa pembayaran (belum bayar)
                    $belumBayar = $transaction->total - $totalPayments;

                    $dataRender .= '<tr>
                        <td>' . $rowNumber++ . '</td>
                        <td>' . Carbon::parse($transaction->date)->format('d F Y H:i:s') . '</td>
                        <td>' . $transaction->invoice_id . '</td>
                        <td>' . $transaction->customer_first_name . " " . $transaction->customer_last_name . '</td>';

                    // Tambahkan bagian Item, Qty, Harga, dan Sub Total Item
                    $itemList = '';
                    $qtyList = '';
                    $priceList = '';
                    $subTotalList = '';

                    foreach ($items as $item) {
                        $itemList .= "{$item->product_name} ({$item->color}, {$item->variant})<br>";
                        $qtyList .= $item->qty . '<br>';
                        $priceList .= 'Rp. ' . $this->rupiah($item->product_price) . '<br>';
                        $priceListTotal += $item->product_price;
                        $subTotalList .= 'Rp. ' . $this->rupiah($item->qty * $item->product_price) . '<br>';
                        $subTotalListAkhir += $item->qty * $item->product_price;
                    }

                    $courier = is_string($transaction->courier) ? json_decode($transaction->courier, true) : $transaction->courier;
                    $ecommerceOngkir = $courier['cost'] ?? 0;
                    $posOngkir = $transaction->additional_cost ?? 0;
                    $totalOngkir = $ecommerceOngkir + $posOngkir;
                    $totalSemuaOngkir += $totalOngkir;

                    $diskonPercent = (double) ($transaction->discount_percent ?? 0);
                    $diskonNominal = (double) ($transaction->discount_nominal ?? 0);
                    $diskon = $diskonNominal + (($transaction->sub_total * $diskonPercent) / 100);
                    $totalSemuaDiskon += $diskon;

                    $dataRender .= '
                    <td class="border_item">' . $itemList . '</td>
                    <td class="border_item">' . $qtyList . '</td>
                    <td class="border_item">' . $priceList . '</td>
                    <td class="border_item">' . $subTotalList . '</td>
                    <td class="border_item">Rp. ' . $this->rupiah($transaction->sub_total) . '</td>
                    <td class="border_item">Rp. ' . $this->rupiah($totalOngkir) . '</td>
                    <td class="border_item">Rp. ' . $this->rupiah($diskon) . '</td>';

                    foreach($jenisPembayaran as $pay){
                        if(in_array($transaction->payment_status, ["paid", "shipping", "completed"])){
                            if($pay->id == $transaction->payment_type){
                                $dataRender .= '<td>Rp. ' . $this->rupiah($transaction->total) . '</td>';
                            }else{
                                $dataRender .= '<td>' .'-'. '</td>';
                            }
                        }else{
                            $dataRender .= '<td>' .'-'. '</td>';
                        }
                    }

                    if (in_array($transaction->payment_status, ["paid", "shipping", "completed"]) && isset($arrayTipePembayaran[$transaction->payment_type])) {
                        $arrayTipePembayaran[$transaction->payment_type]['total'] += (double) $transaction->total;
                    }

                    $totalBelumBayar += max(0, $belumBayar);

                    $status = (in_array($transaction->payment_status, ["paid", "shipping", "completed"]))
                        ? '<span class="text-success"><i class="ri-checkbox-circle-line"></i> Lunas</span>'
                        : '<span class="text-warning"><i class="ri-file-info-line"></i> Belum Bayar</span>';

                    $dataRender .= '<td class="border_item">' . $status . '</td>
                        <td>Rp. ' . (in_array($transaction->payment_status, ["paid", "shipping", "completed"]) ? $this->rupiah($transaction->total) : '0') . '</td>
                    </tr>';

                    if(in_array($transaction->payment_status, ["paid", "shipping", "completed"])){
                            $totalSemua += (double) $transaction->total;
                    }
                }

                $dataRender .= '</tbody>
                <tfoot class="table-active">
                    <tr>                                                    
                        <td colspan="5" style="text-align:center;font-weight:bold;">TOTAL</td> 
                        <td></td>
                        <td>Rp. ' . $this->rupiah($priceListTotal) . '</td>
                        <td>Rp. ' . $this->rupiah($subTotalListAkhir) . '</td>
                        <td>Rp. ' . $this->rupiah($totalSubTotal) . '</td>
                        <td>Rp. ' . $this->rupiah($totalSemuaOngkir) . '</td>
                        <td>Rp. ' . $this->rupiah($totalSemuaDiskon) . '</td>';

                foreach ($arrayTipePembayaran as $pay) {
                    $dataRender .= '<td>Rp. ' . $this->rupiah($pay['total']) . '</td>';
                }

                $dataRender .= '<td></td>
                    <td>Rp. ' . $this->rupiah($totalSemua) . '</td>
                </tr>    
             </tfoot>';

                return json_encode(['data' => $dataRender]);
            }
        }

        if($request->jenis == 'sales_person_report'){
            $salesReport = DB::table('sales_transactions')
                ->join('sale_people', 'sales_transactions.sale_people_id', '=', 'sale_people.id')
                ->select(
                    'sale_people.name as nama_sales',
                    DB::raw('COUNT(sales_transactions.id) as jumlah_transaksi'),
                    DB::raw('SUM(CAST(sales_transactions.total AS numeric)) as total')
                )
                ->whereBetween('sales_transactions.date', [$tglAwal, $tglAkhir])
                ->whereIn('sales_transactions.payment_status', ['paid', 'completed', 'shipping'])
                ->where('sales_transactions.store_id', $store['id'])
                ->whereNull('sales_transactions.deleted_at')
                ->groupBy('sale_people.id', 'sale_people.name')
                ->orderBy('jumlah_transaksi', 'desc')
                ->get();

            $dataRender = '
                    <thead class="table-active">
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Nama Sales</th>
                        <th scope="col">Total Transaksi</th>
                        <th scope="col">Total Nilai Transaksi</th>
                    </tr>
                    </thead>
                    <tbody>';

                        $rowNumber = 1;

                    foreach ($salesReport as $row) {
                        $dataRender .= '
                        <tr>
                        <td class="border_item">' . $rowNumber++ . '</td>
                        <td class="border_item">' . $row->nama_sales . '</td>
                        <td class="border_item">' . $row->jumlah_transaksi . '</td>
                        <td class="border_item">Rp. ' . $this->rupiah($row->total) . '</td>
                        </tr>';
                        
                    }

                    $dataRender .= '</tbody>
                    </table>';

            return json_encode(['data' => $dataRender]);
        }

        if($request->jenis == 'item_penjualan'){
            $store = session()->get('store');

            $data = SalesTransactionItem::leftJoin('sales_transactions', 'sales_transactions.id', '=', 'sales_transaction_items.sales_transaction_id')
            ->leftJoin('product_details', 'sales_transaction_items.product_detail_id', '=', 'product_details.id')
            ->leftJoin('products', 'product_details.product_id', '=', 'products.id')
            ->leftJoin('variants', 'product_details.variant_id', '=', 'variants.id')
            ->leftJoin('colors', 'product_details.color_id', '=', 'colors.id')
            ->select(
                'sales_transactions.id as transaction_id',
                'sales_transactions.date',
                'sales_transaction_items.qty',
                'products.title as product_name',
                'variants.name as variant',
                'colors.label as color'
            )
            ->whereNull('sales_transactions.deleted_at')
            ->where('sales_transactions.store_id', $store['id'])
            ->whereBetween('sales_transactions.date', [$tglAwal, $tglAkhir])
            ->get()
            ->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item->date)->format('Y-m-d');
            });

            $rowNumber = 1;

            $dataRender = '<thead class="table-active">
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Barang</th>
                <th class="text-center">Qty</th>
            </tr>
            </thead>';

            foreach ($data as $dataPenjualan) {
                $firstRow = true;

                $groupedProducts = $dataPenjualan->groupBy(function ($item) {
                    return $item->product_name . '|' . $item->variant . '|' . $item->color;
                });

                foreach ($groupedProducts as $items) {
                    $firstItem = $items->first();
                    $totalQty = $items->sum('qty');

                    $dataRender .= '<tr>';

                    if ($firstRow) {
                        $dataRender .= '<td>' . $rowNumber++ . '</td>';
                        $dataRender .= '<td class="text-danger">' . \Carbon\Carbon::parse($firstItem->date)->format('d/m/Y') . '</td>';
                        $firstRow = false;
                    } else {
                        $dataRender .= '<td></td><td></td>';
                    }

                    $dataRender .= '<td>' . $firstItem->product_name . ' (' . $firstItem->variant . ', ' . $firstItem->color . ')</td>';
                    $dataRender .= '<td class="text-center">' . $totalQty . '</td>';
                    $dataRender .= '</tr>';
                }
            }
            return json_encode(['data' => $dataRender]);
        }

        if($request->jenis == 'penjualan_pelanggan'){
            $store = session()->get('store');

            $data = SalesTransaction::join('customers', 'customers.id', '=', 'sales_transactions.customer_id')
            ->select(
                'customers.first_name as first_name',
                'customers.id as customer_id',
                'customers.last_name as last_name',
                'customers.email as email',
                'customers.phone as phone',
                'sales_transactions.total'
                )
                ->whereNull('sales_transactions.deleted_at')
                ->where('sales_transactions.store_id', $store['id'])
                ->whereBetween('sales_transactions.date', [$tglAwal, $tglAkhir])
                ->where('sales_transactions.payment_status', 'paid')
                ->get();

                $groupedData = $data->groupBy('customer_id')->map(function ($rows) {
                    return [
                        'first_name' => $rows->first()->first_name,
                        'last_name'  => $rows->first()->last_name,
                        'email'      => $rows->first()->email,
                        'phone'      => $rows->first()->phone,
                        'total'      => $rows->sum(fn($r) => (float) str_replace('.', '', $r->total)), // handle varchar
                        ];
                });

                $dataRender = '<thead  style="background-color: #f3f6f9;" class="footer-label">
                <tr>
                    <th>No</th>
                    <th>Nama Pelanggan</th>
                    <th>Email</th>
                    <th>No HP</th>
                    <th>Total Transaksi</th>
                </tr>
                </thead>';

                $rowNumber = 1;

                foreach ($groupedData as $pelanggan) {
                    $dataRender .= '<tr>';
                    $dataRender .= '<td>' . $rowNumber++ . '</td>';
                    $dataRender .= '<td>' . $pelanggan['first_name'] . ' ' . $pelanggan['last_name'] . '</td>';
                    $dataRender .= '<td>' . $pelanggan['email'] . '</td>';
                    $dataRender .= '<td>' . $pelanggan['phone'] . '</td>';
                    $dataRender .= '<td>Rp. ' . $this->rupiah($pelanggan['total']) . '</td>';
                    $dataRender .= '</tr>';
                }

                return json_encode(['data' => $dataRender]);
            }       
}

    public function export_penjualan(Request $request)
    {
        if (!session()->has('store')) {
            return back()->with('error_message', 'Silakan membuat entitas bisnis terlebih dahulu!');
        }
        $dateLabel = str_replace(' to ', ' sampai ', $request->rentang_report);
        $split = explode(' to ', $request->rentang_report);
        $tglAwal = Carbon::parse($split[0])->format('Y-m-d') . ' 00:00:00';
        $tglAkhir = Carbon::parse($split[1] ?? $split[0])->format('Y-m-d') . ' 23:59:00';
        $store = session()->get('store');
        if ($request->jenis_report == 'penjualan') {
            // Ambil outlet dari session
            $outlet = session()->get('outlet');

            // Ambil semua jenis pembayaran berdasarkan outlet
            $jenisPembayaran = PaymentType::whereNull('deleted_at')
                ->select(['id', 'payment_type'])
                ->get();

            if ($jenisPembayaran->isEmpty()) {
                $data = null;
            } else {
                // Inisialisasi array untuk jenis pembayaran
                $arrayTipePembayaran = [];
                foreach ($jenisPembayaran as $value) {
                    $tmp = [];
                    $tmp['id'] = $value->id;
                    $tmp['payment_type'] = $value->payment_type;
                    $tmp['total'] = 0; // Inisialisasi total pembayaran ke 0
                    $arrayTipePembayaran[$value->id] = $tmp;
                }

                // Ambil data transaksi penjualan dengan join ke tabel pembayaran dan pelanggan
                $data = SalesTransaction::leftJoin('customers', 'customers.id', '=', 'sales_transactions.customer_id')
                ->leftJoin('payment_types', 'sales_transactions.payment_type', '=', 'payment_types.id')
                ->select([
                    'sales_transactions.id as transaction_id',
                    'sales_transactions.date',
                    'sales_transactions.invoice_id',
                    'sales_transactions.sub_total',
                    'sales_transactions.total',
                    // 'sales_transactions.user_payment',
                    'sales_transactions.channel',
                    'sales_transactions.payment_type',
                    'sales_transactions.payment_status',
                    'customers.first_name as customer_first_name',
                    'customers.last_name as customer_last_name',
                    'payment_types.payment_type as payment_type_name',
                    'sales_transactions.additional_cost',
                    'sales_transactions.courier',
                    ])
                    ->whereNull('sales_transactions.deleted_at')
                    ->where('sales_transactions.store_id', $store['id'])
                    // ->where('sales_transactions.payment_status', '!=', 'draft') // Tambahkan kondisi ini
                    ->whereBetween('sales_transactions.date', [$tglAwal, $tglAkhir])
                    ->get();

                // Kelompokkan data berdasarkan transaksi
                $groupedData = $data->groupBy('transaction_id');

                // Hitung total pembayaran untuk setiap jenis pembayaran
                foreach ($groupedData as $transactionId => $records) {
                    $totalPayments = 0;
                    foreach ($records as $record) {
                        // Menghindari duplikasi pembayaran pada transaksi yang sama
                        if (!is_null($record->payment_type)) {
                            // Jika pembayaran sudah ada, tidak ditambah lagi
                            if (!isset($arrayTipePembayaran[$record->payment_type]['total'])) {
                                $arrayTipePembayaran[$record->payment_type]['total'] = 0;
                            }
                            $arrayTipePembayaran[$record->payment_type]['total'] += $record->payment_amount;
                        } elseif (!is_null($record->main_payment_type_id)) {
                            // Gunakan pembayaran user jika pembayaran lainnya tidak ada
                            if (!isset($arrayTipePembayaran[$record->main_payment_type_id]['total'])) {
                                $arrayTipePembayaran[$record->main_payment_type_id]['total'] = 0;
                            }
                            $arrayTipePembayaran[$record->main_payment_type_id]['total'] += $record->user_payment;
                        }
                    }
                }
            }

            // Export data ke Excel
            return Excel::download(new PenjualanExport($jenisPembayaran, $data, $dateLabel, $arrayTipePembayaran), 'report_penjualan_' . $request->rentang_report . '.xlsx');
        }

        if ($request->jenis_report == 'detail_penjualan') {
            // Ambil outlet dari session
            $outlet = session()->get('outlet');

            // Ambil semua jenis pembayaran berdasarkan outlet
            $jenisPembayaran = PaymentType::whereNull('deleted_at')
                // ->where('outlet_id', $outlet->id)
                ->select(['id', 'payment_type'])
                ->get();

            if ($jenisPembayaran->isEmpty()) {
                $data = null;
            } else {
                // Inisialisasi array untuk jenis pembayaran
                $arrayTipePembayaran = [];
                foreach ($jenisPembayaran as $value) {
                    $tmp = [];
                    $tmp['id'] = $value->id;
                    $tmp['payment_type'] = $value->payment_type;
                    $tmp['total'] = 0; // Inisialisasi total pembayaran ke 0
                    $arrayTipePembayaran[$value->id] = $tmp;
                }

                // Ambil data transaksi penjualan dengan join ke tabel pembayaran dan pelanggan
                $data = SalesTransaction::leftJoin('customers', 'customers.id', '=', 'sales_transactions.customer_id')
                ->leftJoin('payment_types', 'sales_transactions.payment_type', '=', 'payment_types.id')
                ->select([
                    'sales_transactions.id as transaction_id',
                    'sales_transactions.date',
                    'sales_transactions.invoice_id',
                    'sales_transactions.sub_total',
                    'sales_transactions.total',
                    // 'sales_transactions.user_payment',
                    'sales_transactions.channel',
                    'sales_transactions.payment_status',
                    'sales_transactions.payment_type',
                    'customers.first_name as customer_first_name',
                    'customers.last_name as customer_last_name',
                    'payment_types.payment_type as payment_type_name',
                    ])
                    ->whereNull('sales_transactions.deleted_at')
                    ->where('sales_transactions.store_id', $store['id'])
                    ->whereIn('sales_transactions.payment_status', ['paid', 'completed', 'shipping'])
                    ->whereBetween('sales_transactions.date', [$tglAwal, $tglAkhir])
                    ->get();


                $groupedData = $data->groupBy('transaction_id');

                foreach ($groupedData as $transactionId => $records) {
                    $totalPayments = 0;
                    foreach ($records as $record) {
                        if (!is_null($record->payment_type_id)) {
                            if (!isset($arrayTipePembayaran[$record->payment_type_id]['total'])) {
                                $arrayTipePembayaran[$record->payment_type_id]['total'] = 0;
                            }
                            $arrayTipePembayaran[$record->payment_type_id]['total'] += $record->payment_amount;
                        } elseif (!is_null($record->main_payment_type_id)) {
                            if (!isset($arrayTipePembayaran[$record->main_payment_type_id]['total'])) {
                                $arrayTipePembayaran[$record->main_payment_type_id]['total'] = 0;
                            }
                            $arrayTipePembayaran[$record->main_payment_type_id]['total'] += $record->user_payment;
                        }
                    }
                }
            }
            // Export data ke Excel
            return Excel::download(new PenjualanExportDetail($jenisPembayaran, $data, $dateLabel, $arrayTipePembayaran), 'report_detail_penjualan_' . $request->rentang_report . '.xlsx');
        }


        if ($request->jenis_report == 'sales_person_report') {
            // Ambil outlet dari session
            $outlet = session()->get('outlet');

            $salesReport = DB::table('sales_transactions')
                ->join('sale_people', 'sales_transactions.sale_people_id', '=', 'sale_people.id')
                ->select(
                    'sale_people.name as nama_sales',
                    DB::raw('COUNT(sales_transactions.id) as jumlah_transaksi'),
                    DB::raw('SUM(CAST(sales_transactions.total AS numeric)) as total')
                )
                ->whereBetween('sales_transactions.date', [$tglAwal, $tglAkhir])
                ->whereIn('sales_transactions.payment_status', ['paid', 'completed', 'shipping'])
                ->where('sales_transactions.store_id', $store['id'])
                ->whereNull('sales_transactions.deleted_at')
                ->groupBy('sale_people.id', 'sale_people.name')
                ->orderBy('jumlah_transaksi', 'desc')
                ->get();


            // Export data ke Excel
            return Excel::download(new SalesReportExport($salesReport, $dateLabel), 'report_performa_sales_' . $request->rentang_report . '.xlsx');
        }

        if ($request->jenis_report == 'item_penjualan') {
            $store = session()->get('store');

            $data = SalesTransactionItem::leftJoin('sales_transactions', 'sales_transactions.id', '=', 'sales_transaction_items.sales_transaction_id')
            ->leftJoin('product_details', 'sales_transaction_items.product_detail_id', '=', 'product_details.id')
            ->leftJoin('products', 'product_details.product_id', '=', 'products.id')
            ->leftJoin('variants', 'product_details.variant_id', '=', 'variants.id')
            ->leftJoin('colors', 'product_details.color_id', '=', 'colors.id')
            ->select(
                'sales_transactions.date',
                'sales_transaction_items.qty',
                'products.title as product_name',
                'variants.name as variant',
                'colors.label as color'
                )
                ->whereNull('sales_transactions.deleted_at')
                ->where('sales_transactions.store_id', $store['id'])
                ->whereBetween('sales_transactions.date', [$tglAwal, $tglAkhir])
                ->get()
                ->groupBy(function ($item) {
                    return \Carbon\Carbon::parse($item->date)->format('Y-m-d');
                });

                return Excel::download(new ItemPenjualanExport($data, $dateLabel, $store), 'report_item_penjualan_' . $request->rentang_report . '.xlsx');
            }
        
            if ($request->jenis_report == 'penjualan_pelanggan') {
                $store = session()->get('store');

                $data = SalesTransaction::join('customers', 'customers.id', '=', 'sales_transactions.customer_id')
            ->select(
                'customers.first_name as first_name',
                'customers.id as customer_id',
                'customers.last_name as last_name',
                'customers.email as email',
                'customers.phone as phone',
                'sales_transactions.total'
                )
                ->whereNull('sales_transactions.deleted_at')
                ->where('sales_transactions.store_id', $store['id'])
                ->whereBetween('sales_transactions.date', [$tglAwal, $tglAkhir])
                ->where('sales_transactions.payment_status', 'paid')
                ->get();

                $groupedData = $data->groupBy('customer_id')->map(function ($rows) {
                    return [
                        'first_name' => $rows->first()->first_name,
                        'last_name'  => $rows->first()->last_name,
                        'email'      => $rows->first()->email,
                        'phone'      => $rows->first()->phone,
                        'total'      => $rows->sum(fn($r) => (float) str_replace('.', '', $r->total)), // handle varchar
                        ];
                });

                return Excel::download(new PenjualanPelangganExport($groupedData, $dateLabel, $store), 'report_penjualan_pelanggan' . $request->rentang_report . '.xlsx');
            }
    }

    private function rupiah($angka)
    {
        $hasil =  number_format($angka, 0, ',', '.');
        return $hasil;
    }

}
        