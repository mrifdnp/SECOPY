<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use App\Models\ChangeLog;
use App\Models\Role;
use App\Models\RolePrivilege;
use App\Models\UserPrivilege;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use DB;
use App\Helpers\IndonesiaitLib;
use Request as Req;

class ChangeLogController extends BackendController
{
    function __construct()
    {
        parent::__construct();
        // $this->data['titleHeader'] = ChangeLog::where('url', '=',Req::segment(1))->first(['icon', 'name', 'url']);
        $this->data['page_title'] = "Change Log Page";
    }

    public function index()
    {
    //    \RuleHelper::UserHasPermission('access');
        $this->data["title"]= "Change Log Page";
        $this->data['changeLogCount'] = ChangeLog::count();

        return view('backend.pages.change_log.index', $this->data);
    }

    public function get_datatable(Request $request)
    {
    //    \RuleHelper::UserHasPermission('access');

       $data = ChangeLog::select([
                                'id',
                                'type',
                                'version',
                                'description',
                                'created_date',
                                'created_at',
                                'updated_at'
                            ]);

        $datatables = DataTables::of($data);

        return $datatables
            ->addColumn('checkboxs', function(ChangeLog $data) {
                $checkbox = " <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>";
                // if($data->no_delete=="1"){
                //     $checkbox = "<input class='checkbox_item' id='dtcheckbox[]' type='checkbox' name='dtcheckbox[]' disabled>";
                // }

                return $checkbox;
            })
            ->addColumn('number', function(ChangeLog $data) {
                return "";
            })
            ->addColumn('action', function(ChangeLog $data) {
                $disabled = '';
                $btnEdit = '';
                $btnDelete = '';
                if(\RuleHelper::UserHasPermission('update',false)==false && \RuleHelper::UserHasPermission('delete',false)==false){
                    $disabled = 'disabled';
                }

                // if(\RuleHelper::UserHasPermission('update',false)==true){
                    $btnEdit = "<a href='".route('change-log.edit', $data->id)."' class='btn btn-sm btn-soft-secondary'><i class='las la-edit '></i></a>";
                // }

                // if(\RuleHelper::UserHasPermission('delete',false)==true && $data->no_delete!="1"){
                    $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='".$data->id."'><i class='las la-trash '></i></a>";
                // }
                $btnView = "<a href='".route('change-log.show', $data->id)."' class='btn btn-sm btn-soft-secondary'><i class='las la-search-plus '></i></a>";
                return
                    $btnView.' '.$btnEdit.' '.$btnDelete;
            })
            ->editColumn('created_date', function(ChangeLog $data){
                return \Carbon\Carbon::parse($data->created_date)->format('d F Y H:i:s');
            })
            ->filterColumn('deleted_at', function($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('type') && request('type')) {
                    $query->where('type', request('type'));
                }
            })
            ->rawColumns(['action', 'checkboxs','number'])
            ->escapeColumns([])
            ->toJson();
    }

    public function create()
    {
        // \RuleHelper::UserHasPermission('create');
        $this->data['dateNow'] = \Carbon\Carbon::now()->format('Y-m-d H:i');

        return view('backend.pages.change_log.create',$this->data);
    }

    public function store(Request $request)
    {
        // \RuleHelper::UserHasPermission('create');

        $validator = Validator::make($request->all(), [
            'type' => 'required|IN:Minor Update,Mayor Update,New Release',
            'version' => 'required',
            'created_date' => 'required',
            'description' => 'nullable',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }
        ChangeLog::create([
                            'type' => $request->type,
                            'version' => $request->version,
                            'description' => $request->description ? $request->description : null,
                            'created_date' => $request->created_date.\Carbon\Carbon::now()->format(':s')
                        ]);

        \Session::flash('flash_message', 'New record created successfully');

        return redirect()->route('change-log.create');
    }

    public function show($id)
    {
        // \RuleHelper::UserHasPermission('read');
        $this->data['data'] = ChangeLog::findOrFail($id);

        return view('backend.pages.change_log.show',$this->data);
    }

    public function edit($id)
    {
        // \RuleHelper::UserHasPermission('update');

        $data = ChangeLog::where('id',$id)->first();
        if (!$data) {
            return back()->with('error_message', 'Data not found!');
        }
        $data->date_edit = \Carbon\Carbon::parse($data->created_date)->format('Y-m-d H:i');

        $this->data['data'] = $data;

        return view('backend.pages.change_log.edit', $this->data);
    }

    public function update(Request $request, $id)
    {
        // \RuleHelper::UserHasPermission('update');
        $data = ChangeLog::where('id',$id)->first();
        if (!$data) {
            return back()->with('error_message','Data not found!');
        }
        $validator = Validator::make($request->all(), [
            'version' => 'required',
            'created_date' => 'required',
            'type' => 'required|IN:Minor Update,Mayor Update,New Release',
            'description' => 'nullable'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }
        $data->version = $request->version;
        $data->description = $request->description ? $request->description : null;
        $data->type = $request->type;
        $data->created_date = $request->created_date.\Carbon\Carbon::now()->format(':s');
        $data->save();
        \Session::flash('flash_message', 'The record updated successfully');

        return redirect()->route('change-log.edit',$id);
    }

    public function delete(Request $request)
    {
        // \RuleHelper::UserHasPermission('delete');

        $table = "change_log";
        $column_visible = "version";
        $route = "change-log.destroy";
        return $this->delete_lib->deleteConfirmation($request,$table,$column_visible,$route);
    }

    public function destroy($delete_type)
    {
        // \RuleHelper::UserHasPermission('delete');

        $user = Auth::user();
        $dependencies_data = session('dependencies_data');

        $index = 0;
        $redirect_url = "";
        foreach($dependencies_data as $row){
            if($row['delete_access_type']!="restrict" && $delete_type=="harddelete"){
                $delete_data = array_reverse($row['data']);
                foreach($delete_data as $delete_data_row){
                    DB::table($delete_data_row['table'])
                        ->where($delete_data_row['field'], $delete_data_row['value'])
                        ->delete();
                }

                $redirect_url = "change-log";

                DB::table($row['table'])
                ->where('id', $row['id'])
                ->delete();

                $index++;
            }

            if($delete_type=="softdelete"){
                $redirect_url = "change-log";

                DB::table($row['table'])
                ->where('id', $row['id'])
                ->update(['deleted_at' => date('Y-m-d H:i:s'),'deleted_by' => $user->email,'restored_at' => null,'restored_by' => null]);

                $index++;
            }
        }

        \Session::flash('flash_message',  $index.' Record[s] deleted successfully');

        return redirect($redirect_url);
    }
}
