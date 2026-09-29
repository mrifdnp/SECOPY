<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use App\Models\SalesTransaction;
use App\Models\Module;
use Yajra\DataTables\DataTables;
use DB;
use Carbon\Carbon;
use Request as Req;
use App\Exports\ExportProfitLossReport;
use Maatwebsite\Excel\Facades\Excel;

class ProfitLossReportController extends BackendController
{
    function __construct()
    {
        parent::__construct();
        $this->data['page_title'] = "Laporan Laba Rugi";
        $this->data['titleHeader'] = Module::where('url', '=', Req::segment(1))->first(['icon', 'name', 'url']);
    }

    public function index()
    {
        \RuleHelper::UserHasPermission('access');

        $currentDate = Carbon::now();
        $daysInMonth = $currentDate->daysInMonth;
        $monthYear = $currentDate->format('m-Y');
        
        $daterange = request()->get('daterange');
        if ($daterange) {
            $dates = explode(' to ', $daterange);
            $start = Carbon::parse($dates[0])->format('d-m-Y');
            $end = Carbon::parse($dates[1] ?? $dates[0])->format('d-m-Y');
        } else {
            $start = '01-' . $monthYear;
            $end = $daysInMonth . '-' . $monthYear;
        }
        
        $this->data['tglAwal'] = Carbon::parse($start)->format('d-m-Y');
        $this->data['tglAkhir'] = Carbon::parse($end)->format('d-m-Y');

        $tglAwalDb = Carbon::parse($start)->format('Y-m-d') . ' 00:00:00';
        $tglAkhirDb = Carbon::parse($end)->format('Y-m-d') . ' 23:59:59';

        $storeId = request()->get('store_id', 'all');
        $this->data['selectedStore'] = $storeId;
        
        $channel = request()->get('channel', 'all');
        $this->data['selectedChannel'] = $channel;

        // Summary Calculations
        $salesQuery = SalesTransaction::query()
            ->select([
                DB::raw("SUM(COALESCE(CAST(NULLIF(sales_transactions.sub_total, '') AS NUMERIC), 0)) as total_revenue"),
                DB::raw("SUM(COALESCE(CAST(NULLIF(sales_transactions.discount_nominal, '') AS NUMERIC), 0)) as total_diskon"),
                DB::raw("SUM(COALESCE(sales_transactions.xendit_fees, 0)) as total_biaya_admin"),
                DB::raw('SUM((SELECT SUM(qty * cogm) FROM sales_transaction_items WHERE sales_transaction_id = sales_transactions.id)) as total_cogs')
            ])
            ->whereBetween('sales_transactions.date', [$tglAwalDb, $tglAkhirDb])
            ->where('sales_transactions.payment_status', 'paid');
            
        if ($storeId && $storeId !== 'all') {
            $salesQuery->where('sales_transactions.store_id', $storeId);
        }
        if ($channel && $channel !== 'all') {
            $salesQuery->where('sales_transactions.channel', $channel);
        }
        $salesSummary = $salesQuery->first();

        $this->data['totalRevenue'] = $salesSummary->total_revenue ?? 0;
        $this->data['totalCogs'] = $salesSummary->total_cogs ?? 0;
        $this->data['totalDiskon'] = $salesSummary->total_diskon ?? 0;
        $this->data['totalLabaKotor'] = $this->data['totalRevenue'] - $this->data['totalCogs'] - $this->data['totalDiskon'];
        $this->data['totalBiayaAdmin'] = $salesSummary->total_biaya_admin ?? 0;

        $brokenQuery = DB::table('inventory_movements')
            ->join('inventory_movement_items', 'inventory_movements.id', '=', 'inventory_movement_items.inventory_movement_id')
            ->join('product_details', 'inventory_movement_items.product_detail_id', '=', 'product_details.id')
            ->where('inventory_movements.movement_type', 'broken')
            ->where('inventory_movements.status', 'completed')
            ->whereBetween('inventory_movements.movement_date', [$tglAwalDb, $tglAkhirDb]);
            
        if ($storeId && $storeId !== 'all') {
            $brokenQuery->where('inventory_movements.store_id', $storeId);
        }
        $this->data['totalKerugianRusak'] = $brokenQuery->selectRaw('SUM(inventory_movement_items.qty * product_details.cogm) as total_loss')
            ->value('total_loss') ?? 0;

        $usedQuery = DB::table('inventory_movements')
            ->join('inventory_movement_items', 'inventory_movements.id', '=', 'inventory_movement_items.inventory_movement_id')
            ->join('product_details', 'inventory_movement_items.product_detail_id', '=', 'product_details.id')
            ->where('inventory_movements.movement_type', 'used')
            ->where('inventory_movements.status', 'completed')
            ->whereBetween('inventory_movements.movement_date', [$tglAwalDb, $tglAkhirDb]);
            
        if ($storeId && $storeId !== 'all') {
            $usedQuery->where('inventory_movements.store_id', $storeId);
        }
        $this->data['totalBebanUsed'] = $usedQuery->selectRaw('SUM(inventory_movement_items.qty * product_details.cogm) as total_loss')
            ->value('total_loss') ?? 0;

        $this->data['totalLabaBersih'] = $this->data['totalLabaKotor'] - $this->data['totalBiayaAdmin'] - $this->data['totalKerugianRusak'] - $this->data['totalBebanUsed'];
        $this->data['stores'] = \App\Models\Store::whereNull('deleted_at')->get();

        return view('backend.pages.profit_loss.index', $this->data);
    }

    public function get_datatable(Request $request)
    {
        $reportType = $request->get('report_type', 'per_transaction'); // per_transaction, per_item
        $start = $request->get('start_date');
        $end = $request->get('end_date');
        $storeId = $request->get('store_id');
        $channel = $request->get('channel');
        
        $tglAwal = Carbon::parse($start)->format('Y-m-d') . ' 00:00:00';
        $tglAkhir = Carbon::parse($end)->format('Y-m-d') . ' 23:59:59';

        if ($reportType === 'per_item') {
            return $this->get_datatable_items($request, $tglAwal, $tglAkhir, $storeId, $channel);
        }

        $query = SalesTransaction::with(['items.productDetail.product', 'store'])
            ->select([
                'sales_transactions.id',
                'sales_transactions.store_id',
                'sales_transactions.invoice_id',
                'sales_transactions.date',
                'sales_transactions.sub_total as revenue',
                'sales_transactions.discount_nominal',
                'sales_transactions.additional_cost as ongkir_pos',
                'sales_transactions.xendit_fees',
                'sales_transactions.channel',
                'sales_transactions.courier',
                DB::raw('(SELECT SUM(qty * cogm) FROM sales_transaction_items WHERE sales_transaction_id = sales_transactions.id) as cogs')
            ])
            ->whereBetween('sales_transactions.date', [$tglAwal, $tglAkhir])
            ->where('sales_transactions.payment_status', 'paid');
            
        if ($storeId && $storeId !== 'all') {
            $query->where('sales_transactions.store_id', $storeId);
        }
        if ($channel && $channel !== 'all') {
            $query->where('sales_transactions.channel', $channel);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->orderColumn('cogs', function ($query, $order) {
                $query->orderBy(DB::raw('(SELECT SUM(qty * cogm) FROM sales_transaction_items WHERE sales_transaction_id = sales_transactions.id)'), $order);
            })
            ->editColumn('date', function ($data) {
                return Carbon::parse($data->date)->format('d/m/Y H:i');
            })
            ->editColumn('invoice_id', function ($data) {
                $itemCount = $data->items->count();
                return $data->invoice_id . ' <br><small class="text-muted">(' . $itemCount . ' Jenis Item)</small>';
            })
            ->addColumn('revenue', function ($data) {
                return 'Rp ' . number_format($data->revenue, 0, ',', '.');
            })
            ->addColumn('cogs', function ($data) {
                return 'Rp ' . number_format($data->cogs ?? 0, 0, ',', '.');
            })
            ->addColumn('diskon', function ($data) {
                return 'Rp ' . number_format($data->discount_nominal ?? 0, 0, ',', '.');
            })
            ->addColumn('laba_kotor', function ($data) {
                $labaKotor = $data->revenue - ($data->cogs ?? 0) - ($data->discount_nominal ?? 0);
                return 'Rp ' . number_format($labaKotor, 0, ',', '.');
            })
            ->addColumn('ongkir', function ($data) {
                $courier = is_string($data->courier) ? json_decode($data->courier, true) : $data->courier;
                $ecommerceOngkir = $courier['cost'] ?? 0;
                $posOngkir = $data->ongkir_pos ?? 0;
                $totalOngkir = $ecommerceOngkir + $posOngkir;
                return 'Rp ' . number_format($totalOngkir, 0, ',', '.');
            })
            ->addColumn('laba_bersih', function ($data) {
                $labaKotor = $data->revenue - ($data->cogs ?? 0) - ($data->discount_nominal ?? 0);
                $labaBersih = $labaKotor; // Xendit fees tidak memotong laba bersih karena ditanggung customer
                $color = $labaBersih >= 0 ? 'text-success' : 'text-danger';
                return '<span class="'.$color.' fw-bold">Rp ' . number_format($labaBersih, 0, ',', '.') . '</span>';
            })
            ->addColumn('rincian_barang', function ($data) {
                $html = '<div style="max-height: 100px; overflow-y: auto;">';
                foreach ($data->items as $item) {
                    // Pakai query DB langsung untuk mencegah bug relasi Eloquent yang misterius
                    $productName = \DB::table('product_details')
                        ->join('products', 'products.id', '=', 'product_details.product_id')
                        ->where('product_details.id', $item->product_detail_id)
                        ->value('products.title');
                        
                    if (!$productName) {
                        // Coba dari raw atribut JSON jika master tidak ketemu
                        $rawDetail = $item->getAttributes()['product_detail'] ?? null;
                        if ($rawDetail) {
                            $snapshot = is_string($rawDetail) ? json_decode($rawDetail, true) : (is_array($rawDetail) ? $rawDetail : []);
                            $productName = $snapshot['product_title'] ?? 'Item Unknown';
                        } else {
                            $productName = 'Item Unknown';
                        }
                    }
                    
                    $html .= '<div class="mb-1" style="font-size: 11px;">';
                    $html .= '- ' . $productName . ' <span class="badge bg-soft-primary text-primary">x' . $item->qty . '</span>';
                    $html .= '</div>';
                }
                $html .= '</div>';
                return $html;
            })
            ->addColumn('store_name', function ($data) {
                return $data->store ? $data->store->name : '-';
            })
            ->rawColumns(['laba_bersih', 'invoice_id', 'rincian_barang'])
            ->make(true);
    }
    
    private function get_datatable_items(Request $request, $tglAwal, $tglAkhir, $storeId, $channel)
    {
        $query = \App\Models\SalesTransactionItem::query()
            ->join('sales_transactions', 'sales_transactions.id', '=', 'sales_transaction_items.sales_transaction_id')
            ->leftJoin('stores', 'stores.id', '=', 'sales_transactions.store_id')
            ->select([
                'sales_transaction_items.*',
                'sales_transactions.invoice_id',
                'sales_transactions.date',
                'sales_transactions.channel',
                'stores.name as store_name'
            ])
            ->whereBetween('sales_transactions.date', [$tglAwal, $tglAkhir])
            ->where('sales_transactions.payment_status', 'paid');
            
        if ($storeId && $storeId !== 'all') {
            $query->where('sales_transactions.store_id', $storeId);
        }
        if ($channel && $channel !== 'all') {
            $query->where('sales_transactions.channel', $channel);
        }
        
        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('date', function ($data) {
                return Carbon::parse($data->date)->format('d/m/Y H:i');
            })
            ->addColumn('product_name', function ($data) {
                $productName = \DB::table('product_details')
                    ->join('products', 'products.id', '=', 'product_details.product_id')
                    ->where('product_details.id', $data->product_detail_id)
                    ->value('products.title');
                    
                if (!$productName) {
                    $rawDetail = $data->getAttributes()['product_detail'] ?? null;
                    if ($rawDetail) {
                        $snapshot = is_string($rawDetail) ? json_decode($rawDetail, true) : (is_array($rawDetail) ? $rawDetail : []);
                        $productName = $snapshot['product_title'] ?? 'Item Unknown';
                    } else {
                        $productName = 'Item Unknown';
                    }
                }
                return $productName;
            })
            ->editColumn('price', function ($data) {
                return 'Rp ' . number_format($data->price, 0, ',', '.');
            })
            ->editColumn('discount_nominal', function ($data) {
                return 'Rp ' . number_format($data->discount_nominal, 0, ',', '.');
            })
            ->editColumn('cogm', function ($data) {
                return 'Rp ' . number_format($data->cogm, 0, ',', '.');
            })
            ->addColumn('laba_kotor', function ($data) {
                $labaKotorItem = ($data->price - $data->discount_nominal - $data->cogm) * $data->qty;
                $color = $labaKotorItem >= 0 ? 'text-success' : 'text-danger';
                return '<span class="'.$color.' fw-bold">Rp ' . number_format($labaKotorItem, 0, ',', '.') . '</span>';
            })
            ->rawColumns(['laba_kotor'])
            ->make(true);
    }
    
    public function export(Request $request)
    {
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        $storeId = $request->input('store_id', 'all');
        $channel = $request->input('channel', 'all');
        $reportType = $request->input('report_type', 'per_transaction');
        
        $period = '';
        if ($start && $end) {
            $period = Carbon::parse($start)->translatedFormat('d F Y') . ' - ' . Carbon::parse($end)->translatedFormat('d F Y');
        } elseif ($start) {
            $period = 'Mulai ' . Carbon::parse($start)->translatedFormat('d F Y');
        } elseif ($end) {
            $period = 'Sampai ' . Carbon::parse($end)->translatedFormat('d F Y');
        } else {
            $period = 'Semua Periode';
        }
        
        $fileName = 'Laporan Laba Rugi - ' . $period . '.xlsx';
        
        return Excel::download(
            new ExportProfitLossReport($start, $end, $storeId, $reportType, $channel),
            $fileName,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }
}
