<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Core\BackendController;
use App\Models\Module;
use App\Models\InventoryMovement;
use App\Models\InventoryMovementItem;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as Req;
use App\Exports\ExportPurchasingReport;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class PurchasingReportController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['page_title']  = "Halaman Laporan Pembelian (Purchasing)";
        $this->data['titleHeader'] = Module::where('url', '=', Req::segment(1))->first(['icon', 'name', 'url']);

        Carbon::setLocale('id');
    }

    public function report(Request $request)
    {
        $user = Auth::user();
        $userStoreIds = json_decode($user->store_id ?? '[]', true);
        $this->data['stores'] = Store::whereIn('id', $userStoreIds)->get();

        // --- Statistik Pembelian Bulan Ini (Default Load) ---
        $currentMonth = \Carbon\Carbon::now()->month;
        $currentYear = \Carbon\Carbon::now()->year;

        // Query Dasar Mutasi Purchasing Masuk ("in") DARI SUPPLIER SAJA
        $purchasingQuery = DB::table('inventory_movements')
            ->leftJoin('parties as source_party', 'source_party.id', '=', 'inventory_movements.source_party_id')
            ->leftJoin('parties as dest_party', 'dest_party.id', '=', 'inventory_movements.destination_party_id')
            ->where(function ($query) {
                $query->where(function ($sq) {
                    $sq->whereNotNull('inventory_movements.source_party_id')
                       ->whereNull('source_party.store_id');
                })->orWhere(function ($sq) {
                    $sq->whereNotNull('inventory_movements.destination_party_id')
                       ->whereNull('dest_party.store_id');
                });
            })
            ->whereNull('inventory_movements.deleted_at')
            ->whereMonth('inventory_movements.movement_date', $currentMonth)
            ->whereYear('inventory_movements.movement_date', $currentYear);

        // 1. Total Transaksi (Faktur)
        $this->data['total_transactions_month'] = (clone $purchasingQuery)->count();

        // 2 & 3. Total Pembelian (Rp) dan Total Barang (Pcs)
        $itemsQuery = DB::table('inventory_movement_items')
            ->join('inventory_movements', 'inventory_movement_items.inventory_movement_id', '=', 'inventory_movements.id')
            ->leftJoin('parties as source_party', 'source_party.id', '=', 'inventory_movements.source_party_id')
            ->leftJoin('parties as dest_party', 'dest_party.id', '=', 'inventory_movements.destination_party_id')
            ->where(function ($query) {
                $query->where(function ($sq) {
                    $sq->whereNotNull('inventory_movements.source_party_id')
                       ->whereNull('source_party.store_id');
                })->orWhere(function ($sq) {
                    $sq->whereNotNull('inventory_movements.destination_party_id')
                       ->whereNull('dest_party.store_id');
                });
            })
            ->whereNull('inventory_movements.deleted_at')
            ->whereMonth('inventory_movements.movement_date', $currentMonth)
            ->whereYear('inventory_movements.movement_date', $currentYear);

        $this->data['total_purchases_rp_month'] = $itemsQuery->sum(DB::raw('inventory_movement_items.qty * inventory_movement_items.cogm'));
        $this->data['total_items_qty_month'] = $itemsQuery->sum('inventory_movement_items.qty');

        // 4. Supplier Teraktif
        $topSupplier = (clone $purchasingQuery)
            ->groupBy('inventory_movements.source_party_id', 'inventory_movements.destination_party_id', 'inventory_movements.movement_type', 'source_party.name', 'dest_party.name')
            ->select(DB::raw("CASE WHEN inventory_movements.movement_type = 'in' THEN source_party.name ELSE dest_party.name END as name"), DB::raw('count(*) as total'))
            ->orderByDesc('total')
            ->first();

        $this->data['top_supplier_month'] = $topSupplier && $topSupplier->name ? $topSupplier->name : 'Belum Ada';

        return view('backend.pages.purchasing_report.report', $this->data);
    }

    public function preview_datatable(Request $request)
    {
        $from       = $request->get('from');
        $to         = $request->get('to');
        $store_ids  = $request->get('store_ids', []);
        $reportType = $request->get('report_type', 'purchase_detail'); // purchase_detail, total_purchases, report_items

        // --- Perhitungan Statistik Berdasarkan Filter ---
        $purchasingQuery = DB::table('inventory_movements')
            ->leftJoin('parties as source_party', 'source_party.id', '=', 'inventory_movements.source_party_id')
            ->leftJoin('parties as dest_party', 'dest_party.id', '=', 'inventory_movements.destination_party_id')
            ->where(function ($query) {
                $query->where(function ($sq) {
                    $sq->whereNotNull('inventory_movements.source_party_id')
                       ->whereNull('source_party.store_id');
                })->orWhere(function ($sq) {
                    $sq->whereNotNull('inventory_movements.destination_party_id')
                       ->whereNull('dest_party.store_id');
                });
            })
            ->whereNull('inventory_movements.deleted_at')
            ->when(!empty($store_ids), fn($q) => $q->whereIn('inventory_movements.store_id', $store_ids))
            ->when($from, fn($q) => $q->where('inventory_movements.movement_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn($q) => $q->where('inventory_movements.movement_date', '<=', $to . ' 23:59:59'));

        $totalTransactions = (clone $purchasingQuery)->count();

        $itemsQuery = DB::table('inventory_movement_items')
            ->join('inventory_movements', 'inventory_movement_items.inventory_movement_id', '=', 'inventory_movements.id')
            ->leftJoin('parties as source_party', 'source_party.id', '=', 'inventory_movements.source_party_id')
            ->leftJoin('parties as dest_party', 'dest_party.id', '=', 'inventory_movements.destination_party_id')
            ->where(function ($query) {
                $query->where(function ($sq) {
                    $sq->whereNotNull('inventory_movements.source_party_id')
                       ->whereNull('source_party.store_id');
                })->orWhere(function ($sq) {
                    $sq->whereNotNull('inventory_movements.destination_party_id')
                       ->whereNull('dest_party.store_id');
                });
            })
            ->whereNull('inventory_movements.deleted_at')
            ->when(!empty($store_ids), fn($q) => $q->whereIn('inventory_movements.store_id', $store_ids))
            ->when($from, fn($q) => $q->where('inventory_movements.movement_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn($q) => $q->where('inventory_movements.movement_date', '<=', $to . ' 23:59:59'));

        $totalPurchasesRp = $itemsQuery->sum(DB::raw('inventory_movement_items.qty * inventory_movement_items.cogm'));
        $totalItemsQty = $itemsQuery->sum('inventory_movement_items.qty');

        $topSupplier = (clone $purchasingQuery)
            ->groupBy('inventory_movements.source_party_id', 'inventory_movements.destination_party_id', 'inventory_movements.movement_type', 'source_party.name', 'dest_party.name')
            ->select(DB::raw("CASE WHEN inventory_movements.movement_type = 'in' THEN source_party.name ELSE dest_party.name END as name"), DB::raw('count(*) as total'))
            ->orderByDesc('total')
            ->first();

        $stats = [
            'total_purchases_rp' => number_format((float) $totalPurchasesRp, 0, ',', '.'),
            'total_items_qty' => number_format((float) $totalItemsQty, 0, ',', '.'),
            'total_transactions' => number_format((float) $totalTransactions, 0, ',', '.'),
            'top_supplier' => $topSupplier && $topSupplier->name ? $topSupplier->name : 'Belum Ada',
        ];

        if ($reportType === 'total_purchases') {
            // Group by Invoice (1 kali pembelian / 1 movement)
            $query = DB::table('inventory_movements')
                ->leftJoin('parties as source_party', 'source_party.id', '=', 'inventory_movements.source_party_id')
            ->leftJoin('parties as dest_party', 'dest_party.id', '=', 'inventory_movements.destination_party_id')
                ->join('inventory_movement_items', 'inventory_movement_items.inventory_movement_id', '=', 'inventory_movements.id')
                ->join('stores', 'stores.id', '=', 'inventory_movements.store_id')
                ->where(function ($query) {
                $query->where(function ($sq) {
                    $sq->whereNotNull('inventory_movements.source_party_id')
                       ->whereNull('source_party.store_id');
                })->orWhere(function ($sq) {
                    $sq->whereNotNull('inventory_movements.destination_party_id')
                       ->whereNull('dest_party.store_id');
                });
            })
                ->whereNull('inventory_movements.deleted_at')
                ->when(!empty($store_ids), fn($q) => $q->whereIn('inventory_movements.store_id', $store_ids))
                ->when($from, fn($q) => $q->where('inventory_movements.movement_date', '>=', $from . ' 00:00:00'))
                ->when($to, fn($q) => $q->where('inventory_movements.movement_date', '<=', $to . ' 23:59:59'))
                ->select([
                    'inventory_movements.id',
                    'inventory_movements.movement_date',
                    'stores.name as store_name',
                    DB::raw("CASE WHEN inventory_movements.movement_type = 'in' THEN source_party.name ELSE dest_party.name END as supplier_name"),
                    'inventory_movements.remark',
                    DB::raw('SUM(inventory_movement_items.qty) as total_qty'),
                    DB::raw('SUM(inventory_movement_items.qty * inventory_movement_items.cogm) as total_value')
                ])
                ->groupBy('inventory_movements.id', 'inventory_movements.movement_date', 'stores.name', 'source_party.name', 'dest_party.name', 'inventory_movements.movement_type', 'inventory_movements.remark')
                ->orderBy('inventory_movements.movement_date', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('movement_date', fn($row) => $row->movement_date ? Carbon::parse($row->movement_date)->translatedFormat('d F Y') : '-')
                ->editColumn('total_qty', fn($row) => number_format((float) $row->total_qty, 0, ',', '.') . ' Pcs')
                ->editColumn('total_value', fn($row) => 'Rp ' . number_format((float) $row->total_value, 0, ',', '.'))
                ->editColumn('remark', fn($row) => $row->remark ? $row->remark : '-')
                ->with('stats', $stats)
                ->make(true);
        }

        if ($reportType === 'report_items') {
            // Group by Product
            $query = DB::table('inventory_movement_items')
                ->join('inventory_movements', 'inventory_movements.id', '=', 'inventory_movement_items.inventory_movement_id')
                ->leftJoin('parties as source_party', 'source_party.id', '=', 'inventory_movements.source_party_id')
            ->leftJoin('parties as dest_party', 'dest_party.id', '=', 'inventory_movements.destination_party_id')
                ->join('product_details', 'product_details.id', '=', 'inventory_movement_items.product_detail_id')
                ->join('products', 'products.id', '=', 'product_details.product_id')
                ->join('stores', 'stores.id', '=', 'inventory_movements.store_id')
                ->where(function ($query) {
                $query->where(function ($sq) {
                    $sq->whereNotNull('inventory_movements.source_party_id')
                       ->whereNull('source_party.store_id');
                })->orWhere(function ($sq) {
                    $sq->whereNotNull('inventory_movements.destination_party_id')
                       ->whereNull('dest_party.store_id');
                });
            })
                ->whereNull('inventory_movements.deleted_at')
                ->when(!empty($store_ids), fn($q) => $q->whereIn('inventory_movements.store_id', $store_ids))
                ->when($from, fn($q) => $q->where('inventory_movements.movement_date', '>=', $from . ' 00:00:00'))
                ->when($to, fn($q) => $q->where('inventory_movements.movement_date', '<=', $to . ' 23:59:59'))
                ->select([
                    'stores.name as store_name',
                    'products.title as product_name',
                    'product_details.sku',
                    DB::raw('SUM(inventory_movement_items.qty) as total_qty'),
                    DB::raw('SUM(inventory_movement_items.qty * inventory_movement_items.cogm) as total_value')
                ])
                ->groupBy('stores.name', 'products.title', 'product_details.sku');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('total_qty', fn($row) => number_format((float) $row->total_qty, 0, ',', '.') . ' Pcs')
                ->editColumn('total_value', fn($row) => 'Rp ' . number_format((float) $row->total_value, 0, ',', '.'))
                ->with('stats', $stats)
                ->make(true);
        }

        // Default: purchase_detail (Detail per transaksi barang)
        $query = DB::table('inventory_movement_items')
            ->join('inventory_movements', 'inventory_movements.id', '=', 'inventory_movement_items.inventory_movement_id')
            ->leftJoin('parties as source_party', 'source_party.id', '=', 'inventory_movements.source_party_id')
            ->leftJoin('parties as dest_party', 'dest_party.id', '=', 'inventory_movements.destination_party_id')
            ->join('product_details', 'product_details.id', '=', 'inventory_movement_items.product_detail_id')
            ->join('products', 'products.id', '=', 'product_details.product_id')
            ->join('stores', 'stores.id', '=', 'inventory_movements.store_id')
            ->leftJoin('variants', 'variants.id', '=', 'product_details.variant_id')
            ->leftJoin('colors', 'colors.id', '=', 'product_details.color_id')
            ->where(function ($query) {
                $query->where(function ($sq) {
                    $sq->whereNotNull('inventory_movements.source_party_id')
                       ->whereNull('source_party.store_id');
                })->orWhere(function ($sq) {
                    $sq->whereNotNull('inventory_movements.destination_party_id')
                       ->whereNull('dest_party.store_id');
                });
            })
            ->whereNull('inventory_movements.deleted_at')
            ->when(!empty($store_ids), fn($q) => $q->whereIn('inventory_movements.store_id', $store_ids))
            ->when($from, fn($q) => $q->where('inventory_movements.movement_date', '>=', $from . ' 00:00:00'))
            ->when($to, fn($q) => $q->where('inventory_movements.movement_date', '<=', $to . ' 23:59:59'))
            ->select([
                'inventory_movements.movement_date',
                'stores.name as store_name',
                DB::raw("CASE WHEN inventory_movements.movement_type = 'in' THEN source_party.name ELSE dest_party.name END as supplier_name"),
                'products.title as product_name',
                'variants.name as variant',
                'colors.label as color',
                'inventory_movement_items.qty',
                'inventory_movement_items.cogm',
                DB::raw('(inventory_movement_items.qty * inventory_movement_items.cogm) as subtotal')
            ])
            ->orderBy('inventory_movements.movement_date', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('movement_date', fn($row) => $row->movement_date ? Carbon::parse($row->movement_date)->translatedFormat('d F Y') : '-')
            ->editColumn('qty', fn($row) => number_format((float) $row->qty, 0, ',', '.') . ' Pcs')
            ->editColumn('cogm', fn($row) => 'Rp ' . number_format((float) $row->cogm, 0, ',', '.'))
            ->editColumn('subtotal', fn($row) => 'Rp ' . number_format((float) $row->subtotal, 0, ',', '.'))
            ->with('stats', $stats)
            ->make(true);
    }

    public function export(Request $request)
    {
        $from       = $request->input('from');
        $to         = $request->input('to');
        $storeIds   = explode(',', $request->input('store_ids', ''));
        $reportType = $request->input('report_type');

        $period = '';
        if ($from && $to) {
            $period = Carbon::parse($from)->translatedFormat('d F Y') . ' - ' . Carbon::parse($to)->translatedFormat('d F Y');
        } elseif ($from) {
            $period = 'Mulai ' . Carbon::parse($from)->translatedFormat('d F Y');
        } elseif ($to) {
            $period = 'Sampai ' . Carbon::parse($to)->translatedFormat('d F Y');
        } else {
            $period = 'Semua Periode';
        }

        $fileName = 'Laporan Pembelian - ' . $period . '.xlsx';

        return Excel::download(
            new ExportPurchasingReport($from, $to, $reportType, $storeIds), 
            $fileName,
            \Maatwebsite\Excel\Excel::XLSX
        );
    }
}
