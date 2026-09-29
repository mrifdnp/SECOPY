<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use App\Models\HeadAccount;
use App\Models\AccountingBusinessGroup;
use App\Models\Module;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Request as Req;

class HeadAccountController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['page_title'] = "Head Account Page";
        $titleHeader = Module::where('url', '=', Req::segment(1))->first(['icon', 'name', 'url']);
        if (!$titleHeader) {
            $titleHeader = (object)[
                'name' => 'Akun',
                'icon' => 'ri-account-pin-circle-fill',
                'url'  => 'head-account'
            ];
        }
        $this->data['titleHeader'] = $titleHeader;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        \RuleHelper::UserHasPermission('access');

        $this->data['accounting_business_groups'] = AccountingBusinessGroup::whereNull('deleted_at')->pluck('name', 'id')->toArray();
        $this->data['head_accounts_count'] = HeadAccount::whereNotNull('deleted_at')->count();
        $this->data['import_template'] = (object)['id' => '1', 'rules' => []];

        return view('backend.pages.head_account.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        \RuleHelper::UserHasPermission('create');

        $this->data['accounting_business_groups'] = AccountingBusinessGroup::whereNull('deleted_at')->pluck('name', 'id')->toArray();

        return view('backend.pages.head_account.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \RuleHelper::UserHasPermission('create');

        $validator = Validator::make($request->all(), [
            'name'         => 'required|string',
            'nature'       => 'required|in:assets,liability,equity,expense,revenue,hpp',
            'type'         => 'required|in:asset lancar,asset tetap,bukan asset',
            'expense_type' => 'required|in:beban kas,beban non kas,beban barang,bukan beban',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        $head_account = new HeadAccount();
        $head_account->name = $request->name;
        $head_account->nature = $request->nature;
        $head_account->type = $request->type;
        $head_account->kas = $request->kas;
        $head_account->aktifitas = $request->aktifitas;
        $head_account->expense_type = $request->expense_type;
        $head_account->sn = $request->saldo_normal ?? $request->sn;
        $head_account->pos = $request->pos;
        $head_account->users_id = $user->id;
        $head_account->parent_user_id = $user->id;
        $head_account->created_by = $user->email;
        $head_account->business_type = $request->has('business_type') ? json_encode($request->business_type) : null;
        $simpan = $head_account->save();

        if ($simpan) {
            \Session::flash('flash_message', 'Data baru berhasil ditambahkan');
        } else {
            \Session::flash('error_message', 'Error, Data baru gagal ditambahkan');
        }

        return redirect()->route('head-account.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        \RuleHelper::UserHasPermission('read');
        $head_account = HeadAccount::findOrFail($id);
        $this->data['head_account'] = $head_account;

        return view('backend.pages.head_account.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        \RuleHelper::UserHasPermission('update');
        $data = HeadAccount::findOrFail($id);

        if ($request->ajax()) {
            $res = $data->toArray();
            if ($data->business_type) {
                $decoded = json_decode($data->business_type, true);
                $res['business_type'] = is_array($decoded) ? $decoded : $data->business_type;
            } else {
                $res['business_type'] = [];
            }
            return response()->json($res);
        }

        $this->data['data'] = $data;
        $this->data['accounting_business_groups'] = AccountingBusinessGroup::whereNull('deleted_at')->pluck('name', 'id')->toArray();

        return view('backend.pages.head_account.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        \RuleHelper::UserHasPermission('update');
        $head_account = HeadAccount::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'         => 'required|string',
            'nature'       => 'required|in:assets,liability,equity,expense,revenue,hpp',
            'type'         => 'required|in:asset lancar,asset tetap,bukan asset',
            'expense_type' => 'required|in:beban kas,beban non kas,beban barang,bukan beban'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        $head_account->name = $request->name;
        $head_account->nature = $request->nature;
        $head_account->type = $request->type;
        $head_account->kas = $request->kas;
        $head_account->aktifitas = $request->aktifitas;
        $head_account->expense_type = $request->expense_type;
        $head_account->sn = $request->saldo_normal ?? $request->sn;
        $head_account->pos = $request->pos;
        $head_account->updated_by = $user->email;
        if ($request->has('business_type')) {
            $head_account->business_type = json_encode($request->business_type);
        }
        $simpan = $head_account->save();

        if ($simpan) {
            \Session::flash('flash_message', 'Data berhasil diubah');
        } else {
            \Session::flash('error_message', 'Error, Data gagal diubah');
        }

        return redirect()->route('head-account.index');
    }

    public function trash()
    {
        \RuleHelper::UserHasPermission('trash');
        $this->data['head_accounts_count'] = DB::table('head_accounts')->whereNotNull('deleted_at')->count();

        return view('backend.pages.head_account.trash', $this->data);
    }

    public function get_datatable()
    {
        \RuleHelper::UserHasPermission('access');

        $data = DB::table('head_accounts')
            ->select([
                'head_accounts.id',
                'head_accounts.name',
                'head_accounts.nature',
                'head_accounts.type',
                'head_accounts.aktifitas',
                'head_accounts.expense_type',
                'head_accounts.kas',
                'head_accounts.sn',
                'head_accounts.pos',
                'head_accounts.business_type',
                'head_accounts.created_at',
                'head_accounts.updated_at'
            ])
            ->whereNull('head_accounts.deleted_at');

        $datatables = DataTables::of($data);

        return $datatables
            ->addColumn('checkboxs', function ($data) {
                return "<input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>";
            })
            ->addColumn('number', function ($data) {
                return "";
            })
            ->addColumn('action', function ($data) {
                $btnEdit = '';
                $btnDelete = '';

                if (\RuleHelper::UserHasPermission('update', false)) {
                    $btnEdit = "<button type='button' class='btn btn-sm btn-soft-secondary btnEdit' data-id='{$data->id}' title='Edit'><i class='las la-edit'></i></button>";
                }

                if (\RuleHelper::UserHasPermission('delete', false)) {
                    $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='" . $data->id . "' title='Delete'><i class='las la-trash'></i></a>";
                }
                $btnView = "<a href='" . route('head-account.show', $data->id) . "' class='btn btn-sm btn-soft-secondary' title='Detail'><i class='las la-search-plus'></i></a>";

                return $btnView . ' ' . $btnEdit . ' ' . $btnDelete;
            })
            ->editColumn('created_at', function ($data) {
                return Carbon::parse($data->created_at)->format('d F Y H:i:s');
            })
            ->editColumn('nature', function ($data) {
                return ($data->nature == 'hpp') ? 'HPP' : ucwords($data->nature);
            })
            ->editColumn('type', function ($data) {
                return ucwords($data->type);
            })
            ->editColumn('business_type', function ($data) {
                if (!$data->business_type) return '';
                try {
                    $ids = json_decode($data->business_type, true);
                    if (is_array($ids)) {
                        $names = AccountingBusinessGroup::whereIn('id', $ids)->pluck('name')->toArray();
                        return !empty($names) ? implode(', ', $names) : implode(', ', $ids);
                    }
                    return $data->business_type;
                } catch (\Exception $e) {
                    return $data->business_type;
                }
            })
            ->filter(function ($query) {
                if (request()->has('name') && !is_null(request('name')) && request('name') !== '') {
                    $query->where('head_accounts.name', 'like', "%" . request('name') . "%");
                }
                if (request()->has('nature') && !is_null(request('nature')) && request('nature') !== '') {
                    $query->where('head_accounts.nature', 'like', "%" . request('nature') . "%");
                }
                if (request()->has('type') && !is_null(request('type')) && request('type') !== '') {
                    $query->where('head_accounts.type', 'like', "%" . request('type') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs'])
            ->toJson();
    }

    public function get_datatable_trash()
    {
        \RuleHelper::UserHasPermission('trash');

        $data = DB::table('head_accounts')
            ->select([
                'head_accounts.id',
                'head_accounts.name',
                'head_accounts.nature',
                'head_accounts.type',
                'head_accounts.created_at',
                'head_accounts.updated_at'
            ])
            ->whereNotNull('head_accounts.deleted_at');

        $datatables = DataTables::of($data);

        return $datatables
            ->addColumn('checkboxs', function ($data) {
                return "<input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>";
            })
            ->addColumn('action', function ($data) {
                $btnView = "<a href='" . route('head-account.show', $data->id) . "' class='btn btn-sm btn-soft-secondary'><i class='las la-search-plus'></i> View</a>";
                $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='" . $data->id . "'><i class='las la-trash'></i> Permanent Delete</a>";
                $btnRestore = "<a href='#' class='btn btn-sm btn-soft-secondary btnRestore' id='" . $data->id . "'><i class='ri-restart-line'></i> Restore</a>";
                return $btnView . ' ' . $btnRestore . ' ' . $btnDelete;
            })
            ->addColumn('number', function ($data) {
                return "";
            })
            ->editColumn('created_at', function ($data) {
                return Carbon::parse($data->created_at)->format('d F Y H:i:s');
            })
            ->editColumn('nature', function ($data) {
                return ($data->nature == 'hpp') ? 'HPP' : ucwords($data->nature);
            })
            ->editColumn('type', function ($data) {
                return ucwords($data->type);
            })
            ->rawColumns(['action', 'checkboxs'])
            ->toJson();
    }

    public function delete(Request $request)
    {
        \RuleHelper::UserHasPermission('delete');

        $table = "head_accounts";
        $column_visible = "name";
        $route = "head-account.destroy";

        return $this->delete_lib->deleteConfirmation($request, $table, $column_visible, $route);
    }

    public function destroy($delete_type)
    {
        \RuleHelper::UserHasPermission('delete');
        $user = Auth::user();
        $dependencies_data = session('dependencies_data');
        $index = 0;

        foreach ($dependencies_data as $row) {
            if ($row['delete_access_type'] != "restrict" && $delete_type == "harddelete") {
                $delete_data = array_reverse($row['data']);
                foreach ($delete_data as $delete_data_row) {
                    DB::table($delete_data_row['table'])
                        ->where($delete_data_row['field'], $delete_data_row['value'])
                        ->delete();
                }
                HeadAccount::where('id', $row['id'])->forceDelete();
                $index++;
            }

            if ($delete_type == "softdelete") {
                DB::table($row['table'])
                    ->where('id', $row['id'])
                    ->update([
                        'deleted_at'  => date('Y-m-d H:i:s'),
                        'deleted_by'  => $user->email,
                        'restored_at' => null,
                        'restored_by' => null
                    ]);
                $index++;
            }
        }

        \Session::flash('flash_message', $index . ' Record[s] deleted successfully');
        return redirect()->route($delete_type == 'harddelete' ? 'head-account.trash' : 'head-account.index');
    }

    public function restore(Request $request)
    {
        \RuleHelper::UserHasPermission('trash');

        $table = "head_accounts";
        $column_visible = "name";
        $route = "head-account.restore_action";

        return $this->delete_lib->restoreConfirmation($request, $table, $column_visible, $route);
    }

    public function restore_action()
    {
        \RuleHelper::UserHasPermission('trash');
        $user = Auth::user();
        $dependencies_data = session('dependencies_data');
        $index = 0;

        foreach ($dependencies_data as $row) {
            DB::table($row['table'])
                ->where('id', $row['id'])
                ->update([
                    'deleted_at'  => null,
                    'deleted_by'  => null,
                    'restored_at' => date('Y-m-d H:i:s'),
                    'restored_by' => $user->email
                ]);
            $index++;
        }

        \Session::flash('flash_message', $index . ' Record[s] restored successfully');
        return redirect()->route('head-account.trash');
    }
}
