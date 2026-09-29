<?php
namespace App\Http\Controllers\Master;

use App\Exports\ExportProductMutationReport;
use App\Http\Controllers\Core\BackendController;
use App\Models\Module;
use App\Models\ProductStockMovement;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as Req;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class ProductMutationReportController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['page_title']  = "Halaman Laporan Mutasi Produk";
        $this->data['titleHeader'] = Module::where('url', '=', Req::segment(1))->first(['icon', 'name', 'url']);

        Carbon::setLocale('id');
    }

    // Halaman Laporan Mutasi Produk
    public function report(Request $request)
    {
        $user = Auth::user();

        // Cari store yang bisa diakses user
        $userStoreIds = json_decode($user->store_id ?? '[]', true);
        $userStores   = Store::whereIn('id', $userStoreIds)->get();

        $this->data['stores'] = $userStores;

        return view('backend.pages.product_mutation_report.report', $this->data);
    }

    // daatatable preview laporan mutasi produk
    public function preview_datatable(Request $request)
    {
        // Persiapan filter
        $from          = $request->get('from', null);
        $to            = $request->get('to', null);
        $store_ids     = $request->get('store_ids', []);
        $mutation_type = $request->get('mutation_type', null);

        // Cari data mutasi stok produk
        $stockMovements = ProductStockMovement::with([
            'product_detail',
            'product_detail.product',
            'product_detail.variant',
            'product_detail.color',
            'product_detail.store',
        ])
            ->whereHas('product_detail.store', function ($query) use ($store_ids) {
                $query->whereIn('id', $store_ids);
            })
            ->when($from, function ($query) use ($from) {
                $query->where('created_at', '>=', $from . ' 00:00:00');
            })
            ->when($to, function ($query) use ($to) {
                $query->where('created_at', '<=', $to . ' 23:59:59');
            })
            ->when($mutation_type, function ($query) use ($mutation_type) {
                if ($mutation_type === 'in') {
                    $query->where('qty', '>', 0);
                } elseif ($mutation_type === 'out') {
                    $query->where('qty', '<', 0);
                }
            })
            ->orderBy('created_at', 'desc');

        return DataTables::of($stockMovements)
            ->addIndexColumn()
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? Carbon::parse($row->created_at)->translatedFormat('d F Y H:i') : '-';
            })
            ->addColumn('mutation_type', function ($row) {
                return $row->qty > 0 ? 'Masuk' : 'Keluar';
            })
            ->addColumn('last_stock', function ($row) {
                return $row->last_stock ?? 0;
            })
            ->addColumn('product_name', function ($row) {
                return $row->product_detail?->product?->title ?? '-';
            })
            ->addColumn('variant', function ($row) {
                return $row->product_detail?->variant?->name ?? '-';
            })
            ->addColumn('color', function ($row) {
                return $row->product_detail?->color?->label ?? '-';
            })
            ->addColumn('store', function ($row) {
                return $row->product_detail?->store?->name ?? '-';
            })
            ->addColumn('remark', function ($row) {
                return $row->remark ?? '-';
            })
            ->toJson();
    }

    // export laporan mutasi produk
    public function export(Request $request)
    {
        // Persiapan filter
        $from          = $request->get('from', null);
        $to            = $request->get('to', null);
        $store_ids     = explode(',', $request->get('store_ids', ''));
        $mutation_type = $request->get('mutation_type', null);

        // Setup nama file export
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

        $fileName = 'Laporan Mutasi Produk - ' . $period . '.xlsx';

        return Excel::download(new ExportProductMutationReport($from, $to, $mutation_type, $store_ids), $fileName, \Maatwebsite\Excel\Excel::XLSX, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
