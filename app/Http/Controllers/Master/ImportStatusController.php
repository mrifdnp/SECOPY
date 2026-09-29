<?php
namespace App\Http\Controllers\Master;

use App\Exports\ExportFailedImportData;
use App\Helpers\RuleHelper;
use App\Http\Controllers\Core\BackendController;
use App\Models\FailedImportData;
use App\Models\ImportStatus;
use App\Models\Module;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as Req;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class ImportStatusController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['page_title']  = "Import Status";
        $this->data['titleHeader'] = Module::where('url', '=', Req::segment(1))->first(['icon', 'name', 'url']);
    }

    public function index()
    {
        RuleHelper::UserHasPermission('access');
        return view('backend.pages.import_status.index', $this->data);
    }

    public function show($id)
    {
        RuleHelper::UserHasPermission('read');

        $importStatus     = ImportStatus::findOrFail($id);
        $failedImportData = FailedImportData::where('import_status_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        $failedImportData = $failedImportData->each(function ($data) {
            $row       = collect(json_decode($data->row, true));
            $data->row = $row->mapWithKeys(function ($item) {
                return [$item['column'] => $item];
            });
        });

        $failedDataColumns = [];
        if ($failedImportData->isNotEmpty()) {
            $failedDataColumns = $failedImportData->first()->row->pluck('column')->toArray();
        }

        $this->data['importStatus']      = $importStatus;
        $this->data['failedImportData']  = $failedImportData;
        $this->data['failedDataColumns'] = $failedDataColumns;

        return view('backend.pages.import_status.show', $this->data);
    }

    public function datatable()
    {
        RuleHelper::UserHasPermission('access');
        $importStatuses = ImportStatus::query();

        $dataTable = DataTables::of($importStatuses);
        return $dataTable
            ->addColumn('checkboxs', function ($data) {
                $checkbox = "<input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>";
                return $checkbox;
            })
            ->addColumn('number', function ($data) {
                return "";
            })
            ->editColumn('status', function ($data) {
                $badgeClass = '';
                $statusText = ucfirst($data->status);
                switch (strtolower($data->status)) {
                    case 'waiting':
                        $badgeClass = 'badge-soft-warning';
                        break;
                    case 'running':
                        $badgeClass = 'badge-soft-info';
                        break;
                    case 'done':
                        $badgeClass = 'badge-soft-success';
                        break;
                    case 'failed':
                        $badgeClass = 'badge-soft-danger';
                        break;
                    default:
                        $badgeClass = 'badge-soft-secondary';
                }
                return "<span class='badge {$badgeClass} fs-6'>{$statusText}</span>";
            })
            ->editColumn('success_row', function ($data) {
                return '<span class="badge bg-success fs-6">' . number_format($data->success_row) . '</span>';
            })
            ->editColumn('failed_row', function ($data) {
                return '<span class="badge bg-danger fs-6">' . number_format($data->failed_row) . '</span>';
            })
            ->editColumn('total_row', function ($data) {
                return '<span class="badge bg-primary fs-6">' . number_format($data->total_row) . '</span>';
            })
            ->addColumn('action', function ($data) {
                // View button - always available
                $btnView = "<a href='" . route('import_status.show', ['id' => $data->id]) . "' class='btn btn-sm btn-soft-secondary' title='Detail'><i class='las la-search-plus'></i></a>";
                if (RuleHelper::UserHasPermission('delete', false)) {
                    $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='" . $data->id . "' title='Delete'><i class='las la-trash '></i></a>";
                }
                $btnResult = '';
                if ($data->source_url) {
                    $btnResult = "<a href='" . $data->source_url . "' class='btn btn-sm btn-soft-secondary'>Lihat Hasil</a>";
                }
                $btnExport = '';
                if (strtolower($data->task) == 'import product' && $data->status == 'done') {
                    $btnExport = "<a href='" . route('import_status.export_product', ['id' => $data->id]) . "' class='btn btn-sm btn-soft-primary'>Export Detail Produk</a>";
                }
                return $btnView . ' ' . $btnDelete . ' ' . $btnResult . ' ' . $btnExport;
            })
            ->editColumn('created_at', function ($data) {
                return Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->filter(function ($query) {
                // Filter by name
                if (request()->has('name') && ! is_null(request('name'))) {
                    $query->where('import_statuses.name', 'like', "%" . request('name') . "%");
                }
                // Filter by status
                if (request()->has('status') && ! is_null(request('status'))) {
                    $query->where('import_statuses.status', request('status'));
                }
                // Filter by date range
                if (request()->has('date_from') && ! is_null(request('date_from'))) {
                    $query->whereDate('import_statuses.created_at', '>=', request('date_from'));
                }
                if (request()->has('date_to') && ! is_null(request('date_to'))) {
                    $query->whereDate('import_statuses.created_at', '<=', request('date_to'));
                }
            })
            ->rawColumns(['action', 'checkboxs', 'status', 'success_row', 'failed_row', 'total_row'])
            ->toJson();
    }

    public function delete(Request $request)
    {
        RuleHelper::UserHasPermission('delete');

        $table          = "import_statuses";
        $column_visible = "task";
        $route          = "import_status.destroy";
        return $this->delete_lib->deleteConfirmation($request, $table, $column_visible, $route);
    }

    public function destroy()
    {
        RuleHelper::UserHasPermission('delete');

        $dependencies_data = session('dependencies_data');
        $index             = 0;
        $redirect_url      = "import-status";
        foreach ($dependencies_data as $row) {
            $delete_data = array_reverse($row['data']);
            foreach ($delete_data as $delete_data_row) {
                DB::table($delete_data_row['table'])
                    ->where($delete_data_row['field'], $delete_data_row['value'])
                    ->delete();
            }
            $importStatus = ImportStatus::findOrFail($row['id']);
            $importStatus->forceDelete();
            $index++;
        }

        Session::flash('flash_message', $index . ' Record[s] deleted successfully');
        return redirect($redirect_url);
    }

    public function clear()
    {
        ImportStatus::query()->delete();
        return response()->json([
            'status'  => 'success',
            'message' => 'Semua status import telah dihapus.',
        ]);
    }

    public function delete_failed_import_data(Request $request)
    {
        RuleHelper::UserHasPermission('delete');

        $table          = "failed_import_data";
        $column_visible = "id";
        $route          = "failed_import_data.destroy";

        return $this->delete_lib->deleteConfirmation($request, $table, $column_visible, $route);
    }

    public function destroy_failed_import_data()
    {
        RuleHelper::UserHasPermission('delete');

        $dependencies_data = session('dependencies_data');
        $index             = 0;

        foreach ($dependencies_data as $row) {
            $delete_data = array_reverse($row['data']);
            foreach ($delete_data as $delete_data_row) {
                DB::table($delete_data_row['table'])
                    ->where($delete_data_row['field'], $delete_data_row['value'])
                    ->delete();
            }

            $importStatus = FailedImportData::findOrFail($row['id']);
            $importStatus->forceDelete();
            $index++;
        }

        Session::flash('flash_message', $index . ' Record[s] deleted successfully');
        return redirect()->back(302);
    }

    public function replace_failed_import_data(Request $request)
    {
        RuleHelper::UserHasPermission('update');

        $failedImportData = FailedImportData::find($request->input('failed_import_data_id'));
        if (! $failedImportData) {
            Session::flash('error_message', 'Failed import data not found.');
            return redirect()->back();
        }

        $replaceAction = json_decode($failedImportData->replace_action, true);
        if (! $replaceAction) {
            Session::flash('error_message', 'Invalid row data format.');
            return redirect()->back();
        }

        foreach ($replaceAction as $table => $operations) {
            $query = DB::table($table);

            // Apply where conditions
            if (isset($operations['where'])) {
                foreach ($operations['where'] as $column => $value) {
                    $query->where($column, $value);
                }
            }

            // Apply updates
            if (isset($operations['update'])) {
                $query->update($operations['update']);
            }
        }

        // Delete the failed import data after replacement
        $failedImportData->delete();
        Session::flash('flash_message', 'Data berhasil diganti dan data gagal impor telah dihapus.');
        return redirect()->back();
    }

    public function load_more_failed_import_data(Request $request)
    {
        RuleHelper::UserHasPermission('read');

        $importStatusId   = $request->input('import_status_id');
        $failedImportData = FailedImportData::where('import_status_id', $importStatusId)
            ->orderBy('created_at', 'desc')
            ->offset($request->input('offset', 0))
            ->limit(100)
            ->get();

        $failedImportData = $failedImportData->each(function ($data) {
            $row       = collect(json_decode($data->row, true));
            $data->row = $row->mapWithKeys(function ($item) {
                return [$item['column'] => $item];
            });
        });

        return response()->json([
            'data'    => $failedImportData,
            'hasMore' => $failedImportData->count() === 100,
        ]);
    }

    public function download_failed_import_data(Request $request)
    {
        $importStatusId = $request->input('import_status_id', null);

        $importStatus = ImportStatus::findOrFail($importStatusId);
        $filename     = '[DATA GAGAL] ' . $importStatus->task . '.csv';
        return Excel::download(new ExportFailedImportData($importStatus->id), $filename, \Maatwebsite\Excel\Excel::CSV, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function export_product($id)
    {
        $importStatus = ImportStatus::findOrFail($id);
        if (strtolower($importStatus->task) != 'import product') {
            abort(404);
        }

        // Ambil produk yang diimport dalam rentang waktu status import ini (dengan buffer beberapa detik)
        $products = \App\Models\Product::whereBetween('created_at', [
            Carbon::parse($importStatus->created_at)->subSeconds(5), 
            Carbon::parse($importStatus->updated_at)->addSeconds(5)
        ])->pluck('id')->toArray();
        
        $filename = "[Data Export Produk] " . Carbon::parse($importStatus->created_at)->format('Ymd_His') . ".csv";
        return Excel::download(new \App\Exports\ProductDetailExport('all', $products), $filename, \Maatwebsite\Excel\Excel::CSV, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
