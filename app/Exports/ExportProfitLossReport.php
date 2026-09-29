<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use App\Models\SalesTransaction;
use App\Models\SalesTransactionItem;
use Carbon\Carbon;
use DB;

class ExportProfitLossReport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $start;
    protected $end;
    protected $storeId;
    protected $reportType;
    protected $channel;

    public function __construct($start, $end, $storeId, $reportType, $channel = 'all')
    {
        $this->start = $start;
        $this->end = $end;
        $this->storeId = $storeId;
        $this->reportType = $reportType;
        $this->channel = $channel;
    }

    public function collection()
    {
        $tglAwal = Carbon::parse($this->start)->format('Y-m-d') . ' 00:00:00';
        $tglAkhir = Carbon::parse($this->end)->format('Y-m-d') . ' 23:59:59';

        if ($this->reportType === 'per_item') {
            $query = SalesTransactionItem::query()
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
                ->where('sales_transactions.payment_status', 'paid')
                ->orderBy('sales_transactions.date', 'desc');

            if ($this->storeId && $this->storeId !== 'all') {
                $query->where('sales_transactions.store_id', $this->storeId);
            }
            if ($this->channel && $this->channel !== 'all') {
                $query->where('sales_transactions.channel', $this->channel);
            }

            return $query->get();
        } else {
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
                ->where('sales_transactions.payment_status', 'paid')
                ->orderBy('sales_transactions.date', 'desc');

            if ($this->storeId && $this->storeId !== 'all') {
                $query->where('sales_transactions.store_id', $this->storeId);
            }
            if ($this->channel && $this->channel !== 'all') {
                $query->where('sales_transactions.channel', $this->channel);
            }

            return $query->get();
        }
    }

    public function headings(): array
    {
        if ($this->reportType === 'per_item') {
            return [
                'Invoice ID',
                'Tanggal',
                'Cabang',
                'Channel',
                'Nama Barang',
                'Harga Jual',
                'Qty',
                'Diskon Item',
                'Total COGM',
                'Laba Kotor Item'
            ];
        } else {
            return [
                'Invoice ID',
                'Rincian Barang',
                'Tanggal',
                'Cabang',
                'Channel',
                'Omzet Kotor',
                'Diskon',
                'Total COGM (HPP)',
                'Laba Kotor',
                'Ongkir',
                'Laba Bersih'
            ];
        }
    }

    public function map($row): array
    {
        if ($this->reportType === 'per_item') {
            $productName = DB::table('product_details')
                ->join('products', 'products.id', '=', 'product_details.product_id')
                ->where('product_details.id', $row->product_detail_id)
                ->value('products.title');

            if (!$productName) {
                $rawDetail = $row->getAttributes()['product_detail'] ?? null;
                if ($rawDetail) {
                    $snapshot = is_string($rawDetail) ? json_decode($rawDetail, true) : (is_array($rawDetail) ? $rawDetail : []);
                    $productName = $snapshot['product_title'] ?? 'Item Unknown';
                } else {
                    $productName = 'Item Unknown';
                }
            }

            $labaKotorItem = ($row->price - $row->discount_nominal - $row->cogm) * $row->qty;

            return [
                $row->invoice_id,
                Carbon::parse($row->date)->format('d/m/Y H:i'),
                $row->store_name ?? '-',
                ucfirst($row->channel),
                $productName,
                $row->price,
                $row->qty,
                $row->discount_nominal,
                $row->cogm,
                $labaKotorItem
            ];
        } else {
            $rincianBarang = [];
            foreach ($row->items as $item) {
                $productName = DB::table('product_details')
                    ->join('products', 'products.id', '=', 'product_details.product_id')
                    ->where('product_details.id', $item->product_detail_id)
                    ->value('products.title');
                    
                if (!$productName) {
                    $rawDetail = $item->getAttributes()['product_detail'] ?? null;
                    if ($rawDetail) {
                        $snapshot = is_string($rawDetail) ? json_decode($rawDetail, true) : (is_array($rawDetail) ? $rawDetail : []);
                        $productName = $snapshot['product_title'] ?? 'Item Unknown';
                        if (!empty($snapshot['variant_name'])) {
                            $productName .= ' (' . $snapshot['variant_name'] . ')';
                        }
                    } else {
                        $productName = 'Item Unknown';
                    }
                }
                $rincianBarang[] = "- " . $productName . " x" . $item->qty;
            }

            $labaKotor = $row->revenue - ($row->cogs ?? 0) - ($row->discount_nominal ?? 0);
            $labaBersih = $labaKotor; // Xendit fees tidak memotong laba bersih karena ditanggung customer
            
            $courier = is_string($row->courier) ? json_decode($row->courier, true) : $row->courier;
            $ecommerceOngkir = $courier['cost'] ?? 0;
            $posOngkir = $row->ongkir_pos ?? 0;
            $totalOngkir = $ecommerceOngkir + $posOngkir;

            return [
                $row->invoice_id,
                implode("\n", $rincianBarang),
                Carbon::parse($row->date)->format('d/m/Y H:i'),
                $row->store ? $row->store->name : '-',
                ucfirst($row->channel),
                $row->revenue,
                $row->discount_nominal,
                $row->cogs ?? 0,
                $labaKotor,
                $totalOngkir,
                $labaBersih
            ];
        }
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [
            1    => ['font' => ['bold' => true]],
        ];
        
        if ($this->reportType !== 'per_item') {
            $sheet->getStyle('B')->getAlignment()->setWrapText(true);
        }
        
        return $styles;
    }

    public function columnFormats(): array
    {
        if ($this->reportType === 'per_item') {
            return [
                'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Harga Jual
                'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Diskon Item
                'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Total COGM
                'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Laba Kotor Item
            ];
        } else {
            return [
                'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Omzet Kotor
                'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Diskon
                'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Total COGM (HPP)
                'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Laba Kotor
                'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Ongkir
                'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Laba Bersih
            ];
        }
    }
}
