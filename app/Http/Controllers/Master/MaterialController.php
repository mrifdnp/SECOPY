<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use App\Models\Material;
use App\Models\Module;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use DB;
use Carbon\Carbon;
use Request as Req;

class MaterialController extends BackendController
{
    function __construct()
    {
        parent::__construct();
        $this->data['page_title'] = "Material Page";
        $this->data['titleHeader'] = Module::where('url', '=', Req::segment(1))->first(['icon', 'name', 'url']);
    }

    public function index(Request $request)
    {
        \RuleHelper::UserHasPermission('access');

        $this->data['materials_count'] = Material::whereNotNull('deleted_at')->count();
        $this->data['url'] = 'material';

        $tgl = null;
        $dateNow = Carbon::now()->toDateString();
        $this->data['dateNow'] = $this->formatDate($dateNow, $tgl, 3);

        return view('backend.pages.material.index', $this->data);
    }

    public function create()
    {
        \RuleHelper::UserHasPermission('create');

        $tgl = null;
        $dateNow = Carbon::now()->toDateString();
        $this->data['dateNow'] = $this->formatDate($dateNow, $tgl, 3);

        return view('backend.pages.material.create', $this->data);
    }

    public function store(Request $request)
    {
        \RuleHelper::UserHasPermission('create');

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $material = new Material();
        $material->id = ucwords($request->id);
        $material->title = ucwords($request->title);
        $material->is_consignment = $request->boolean('is_consignment');
        $material->created_by = Auth::user()->email;
        $simpan = $material->save();

        if ($simpan) {
            \Session::flash('flash_message', 'New record created successfully');
            if ($request->ajax()) {
                return response()->json(['status' => 'success']);
            }
            return redirect()->route('material.create');
        } else {
            \Session::flash('error_message', 'New record failed to created');
            if ($request->ajax()) {
                return response()->json(['status' => 'error'], 500);
            }
            return redirect()->route('material.create');
        }
    }

    public function show($id)
    {
        \RuleHelper::UserHasPermission('read');
        $material = Material::findOrFail($id);
        $this->data['material'] = $material;
        return view('backend.pages.material.show', $this->data);
    }

    public function edit($id)
    {
        \RuleHelper::UserHasPermission('update');
        $material = Material::findOrFail($id);
        $this->data['material'] = $material;

        return response()->json([
            'status' => 'success',
            'data' => $material
        ]);
    }

    public function update(Request $request, $id)
    {
        \RuleHelper::UserHasPermission('update');
        $material = Material::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $material->id = ucwords($request->id);
        $material->title = ucwords($request->title);
        $material->is_consignment = $request->boolean('is_consignment');
        $material->updated_by = Auth::user()->email;
        $simpan = $material->save();

        if ($simpan) {
            \Session::flash('flash_message', 'The record updated successfully');
            if ($request->ajax()) {
                return response()->json(['status' => 'success']);
            }
            return redirect()->route('material.edit', $id);
        } else {
            \Session::flash('error_message', 'The record failed to updated');
            if ($request->ajax()) {
                return response()->json(['status' => 'error'], 500);
            }
            return redirect()->route('material.edit', $id);
        }
    }

    public function trash()
    {
        \RuleHelper::UserHasPermission('trash');
        $this->data['materials_count'] = DB::table('materials')->whereNotNull('deleted_at')->count();
        return view('backend.pages.material.trash', $this->data);
    }

    public function get_datatable()
    {
        \RuleHelper::UserHasPermission('access');

        $data = DB::table('materials')
            ->select([
                'id',
                'title',
                'is_consignment',
                'created_at',
                'updated_at'
            ])->whereNull('deleted_at');

        $datatables = DataTables::of($data);

        return $datatables
            ->addColumn('checkboxs', function ($data) {
                return " <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>";
            })
            ->addColumn('number', function ($data) {
                return "";
            })
            ->addColumn('action', function ($data) {
                $btnEdit = '';
                $btnDelete = '';

                if (\RuleHelper::UserHasPermission('update', false) == true) {
                    $btnEdit = "<a href='#' class='btn btn-sm btn-soft-secondary btnEdit' data-id='" . $data->id . "' title='Edit'><i class='las la-edit '></i></a>";
                }

                if (\RuleHelper::UserHasPermission('delete', false) == true) {
                    $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='" . $data->id . "' title='Delete'><i class='las la-trash '></i></a>";
                }

                $btnView = "<a href='" . route('material.show', $data->id) . "' class='btn btn-sm btn-soft-secondary' title='Detail'><i class='las la-search-plus '></i></a>";

                return $btnView . ' ' . $btnEdit . ' ' . $btnDelete;
            })
            ->editColumn('is_consignment', function ($data) {
                if ($data->is_consignment) {
                    return '<span class="badge badge-soft-info fs-6">Consignment</span>';
                }
                return '<span class="badge badge-soft-secondary fs-6">Regular</span>';
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->filterColumn('deleted_at', function ($query, $keyword) {
                $query->whereRaw("@deleted_at = ?", [""]);
            })
            ->filter(function ($query) {
                if (request()->has('title') && !is_null(request('title'))) {
                    $query->where('title', 'like', "%" . request('title') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs', 'is_consignment'])
            ->toJson();
    }

    public function get_datatable_trash()
    {
        \RuleHelper::UserHasPermission('trash');
        $data = DB::table('materials')
            ->select([
                'id',
                'title',
                'created_at',
                'updated_at'
            ])->whereNotNull('deleted_at');

        $datatables = DataTables::of($data);

        return $datatables
            ->addColumn('checkboxs', function ($data) {
                return "<input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>";
            })
            ->addColumn('action', function ($data) {
                $btnView = "<a href='" . route('material.show', $data->id) . "' class='btn btn-sm btn-soft-secondary'><i class='las la-search-plus '></i> View</a>";
                $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='" . $data->id . "'><i class='las la-trash'></i> Permanent Delete</a>";
                $btnRestore = "<a href='#' class='btn btn-sm btn-soft-secondary btnRestore' id='" . $data->id . "'><i class='ri-restart-line '></i> Restore</a>";
                return $btnView . " " . $btnRestore . " " . $btnDelete;
            })
            ->addColumn('number', function ($data) {
                return "";
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->filter(function ($query) {
                if (request()->has('title') && !is_null(request('title'))) {
                    $query->where('title', 'like', "%" . request('title') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs'])
            ->toJson();
    }

    public function delete(Request $request)
    {
        \RuleHelper::UserHasPermission('delete');

        $table = "materials";
        $column_visible = "title";
        $route = "material.destroy";

        return $this->delete_lib->deleteConfirmation($request, $table, $column_visible, $route);
    }

    public function destroy($delete_type)
    {
        \RuleHelper::UserHasPermission('delete');
        $user = Auth::user();
        $dependencies_data = session('dependencies_data');
        $index = 0;
        $redirect_url = "";

        foreach ($dependencies_data as $row) {
            if ($row['delete_access_type'] != "restrict" && $delete_type == "harddelete") {
                $delete_data = array_reverse($row['data']);
                foreach ($delete_data as $delete_data_row) {
                    DB::table($delete_data_row['table'])
                        ->where($delete_data_row['field'], $delete_data_row['value'])
                        ->delete();
                }

                $redirect_url = "material/trash";
                $material = Material::findOrFail($row['id']);
                $material->forceDelete();
                $index++;
            }

            if ($delete_type == "softdelete") {
                $redirect_url = "material";
                DB::table($row['table'])
                    ->where('id', $row['id'])
                    ->update(['deleted_at' => date('Y-m-d H:i:s'), 'deleted_by' => $user->email, 'restored_at' => null, 'restored_by' => null]);
                $index++;
            }
        }

        \Session::flash('flash_message', $index . ' Record[s] deleted successfully');
        return redirect($redirect_url);
    }

    public function restore(Request $request)
    {
        \RuleHelper::UserHasPermission('trash');

        if ($request->id_to_be_restored == "") {
            $request->id_to_be_restored = [];
        }

        $data = DB::table('materials')
            ->whereIn('id', $request->id_to_be_restored)
            ->orderBy('deleted_at', 'desc')
            ->get();

        $this->data['index'] = 0;
        $this->data['data'] = $data;
        $this->data['route'] = 'material.restore_action';
        $this->data['column_name'] = 'title';

        \Session::flash('dependencies_data', $data);

        return view('backend.includes.restore_modal', $this->data);
    }

    public function restore_action(Request $request)
    {
        \RuleHelper::UserHasPermission('trash');

        $user = Auth::user();
        $dependencies_data = session('dependencies_data');
        $index = 0;
        foreach ($dependencies_data as $row) {
            Material::where('id', $row->id)
                ->update(
                    [
                        'deleted_at' => null,
                        'deleted_by' => null,
                        'restored_at' => date('Y-m-d H:i:s'),
                        'restored_by' => $user->email
                    ]
                );
            $index++;
        }

        \Session::flash('flash_message', $index . ' Record[s] restored successfully');
        return redirect('material/trash');
    }

    private function formatDate($input, $output, $type)
    {
        preg_match_all('!\d+!', $input, $output);
        if ($type == 1) {
            return $output[0][2] . '-' . $output[0][0] . '-' . $output[0][1];
        } elseif ($type == 2) {
            return $output[0][1] . '/' . $output[0][0] . '/' . $output[0][2];
        } else {
            return $output[0][1] . '/' . $output[0][2] . '/' . $output[0][0];
        }
    }
}
