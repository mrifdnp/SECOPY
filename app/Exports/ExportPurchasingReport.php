<?php
namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportPurchasingReport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $from       = null;
    protected $to         = null;
    protected $reportType = null;
    protected $storeIds   = [];

    public function __construct($from = null, $to = null, $reportType = null, $storeIds = [])
    {
        $this->from       = $from;
        $this->to         = $to;
        $this->reportType = $reportType;
        $this->storeIds   = $storeIds;
    }

    public function collection()
    {
        if ($this->reportType === 'total_purchases') {
            return DB::table('inventory_movements')
                ->join('stores', 'stores.id', '=', 'inventory_movements.store_id')
                ->leftJoin('parties', 'parties.id', '=', 'inventory_movements.source_party_id')
                ->leftJoin('inventory_movement_items', 'inventory_movement_items.inventory_movement_id', '=', 'inventory_movements.id')
                ->where('inventory_movements.movement_type', 'in')
                ->whereNull('inventory_movements.deleted_at')
                ->when(!empty($this->storeIds), fn($q) => $q->whereIn('inventory_movements.store_id', $this->storeIds))
                ->when($this->from, fn($q) => $q->where('inventory_movements.movement_date', '>=', $this->from))
                ->when($this->to, fn($q) => $q->where('inventory_movements.movement_date', '<=', $this->to))
                ->select([
                    'inventory_movements.movement_date',
                    'stores.name as store_name',
                    'parties.name as supplier_name',
                    DB::raw('COUNT(DISTINCT inventory_movements.id) as total_invoice'),
                    DB::raw('SUM(inventory_movement_items.qty) as total_qty'),
                    DB::raw('SUM(inventory_movement_items.qty * inventory_movement_items.cogm) as total_value')
                ])
                ->groupBy('inventory_movements.movement_date', 'stores.name', 'parties.name')
                ->orderBy('inventory_movements.movement_date', 'desc')
                ->get();
        }

        if ($this->reportType === 'report_items') {
            return DB::table('inventory_movement_items')
                ->join('inventory_movements', 'inventory_movements.id', '=', 'inventory_movement_items.inventory_movement_id')
                ->join('stores', 'stores.id', '=', 'inventory_movements.store_id')
                ->join('product_details', 'product_details.id', '=', 'inventory_movement_items.product_detail_id')
                ->join('products', 'products.id', '=', 'product_details.product_id')
                ->where('inventory_movements.movement_type', 'in')
                ->whereNull('inventory_movements.deleted_at')
                ->when(!empty($this->storeIds), fn($q) => $q->whereIn('inventory_movements.store_id', $this->storeIds))
                ->when($this->from, fn($q) => $q->where('inventory_movements.movement_date', '>=', $this->from))
                ->when($this->to, fn($q) => $q->where('inventory_movements.movement_date', '<=', $this->to))
                ->select([
                    'stores.name as store_name',
                    'products.title as product_name',
                    'product_details.sku',
                    DB::raw('SUM(inventory_movement_items.qty) as total_qty'),
                    DB::raw('SUM(inventory_movement_items.qty * inventory_movement_items.cogm) as total_value')
                ])
                ->groupBy('stores.name', 'products.title', 'product_details.sku')
                ->get();
        }

        // purchase_detail
        return DB::table('inventory_movement_items')
            ->join('inventory_movements', 'inventory_movements.id', '=', 'inventory_movement_items.inventory_movement_id')
            ->join('stores', 'stores.id', '=', 'inventory_movements.store_id')
            ->leftJoin('parties', 'parties.id', '=', 'inventory_movements.source_party_id')
            ->join('product_details', 'product_details.id', '=', 'inventory_movement_items.product_detail_id')
            ->join('products', 'products.id', '=', 'product_details.product_id')
            ->leftJoin('variants', 'variants.id', '=', 'product_details.variant_id')
            ->leftJoin('colors', 'colors.id', '=', 'product_details.color_id')
            ->where('inventory_movements.movement_type', 'in')
            ->whereNull('inventory_movements.deleted_at')
            ->when(!empty($this->storeIds), fn($q) => $q->whereIn('inventory_movements.store_id', $this->storeIds))
            ->when($this->from, fn($q) => $q->where('inventory_movements.movement_date', '>=', $this->from))
            ->when($this->to, fn($q) => $q->where('inventory_movements.movement_date', '<=', $this->to))
            ->select([
                'inventory_movements.movement_date',
                'stores.name as store_name',
                'parties.name as supplier_name',
                'products.title as product_name',
                'variants.name as variant',
                'colors.label as color',
                'inventory_movement_items.qty',
                'inventory_movement_items.cogm',
                DB::raw('(inventory_movement_items.qty * inventory_movement_items.cogm) as subtotal')
            ])
            ->orderBy('inventory_movements.movement_date', 'desc')
            ->get();
    }

    public function headings(): array
    {
        if ($this->reportType === 'total_purchases') {
            return ['Tanggal', 'Toko', 'Supplier', 'Total Transaksi', 'Kuantitas Masuk', 'Total Nilai (Rp)'];
        }
        if ($this->reportType === 'report_items') {
            return ['Toko', 'Nama Produk', 'SKU', 'Kuantitas Dibeli', 'Total Nilai (Rp)'];
        }
        
        return ['Tanggal', 'Toko', 'Supplier', 'Nama Produk', 'Varian', 'Warna', 'Qty', 'COGM', 'Subtotal'];
    }

    public function map($row): array
    {
        if ($this->reportType === 'total_purchases') {
            return [
                $row->movement_date ? Carbon::parse($row->movement_date)->translatedFormat('d F Y') : '-',
                $row->store_name ?? '-',
                $row->supplier_name ?? '-',
                $row->total_invoice,
                $row->total_qty,
                $row->total_value
            ];
        }

        if ($this->reportType === 'report_items') {
            return [
                $row->store_name ?? '-',
                $row->product_name ?? '-',
                $row->sku ?? '-',
                $row->total_qty,
                $row->total_value
            ];
        }

        return [
            $row->movement_date ? Carbon::parse($row->movement_date)->translatedFormat('d F Y') : '-',
            $row->store_name ?? '-',
            $row->supplier_name ?? '-',
            $row->product_name ?? '-',
            $row->variant ?? '-',
            $row->color ?? '-',
            $row->qty,
            $row->cogm,
            $row->subtotal
        ];
    }
}
