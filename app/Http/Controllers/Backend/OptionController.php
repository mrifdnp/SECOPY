<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use DB;
use App\Models\Module;
use Request as Req;

class OptionController extends BackendController
{
    function __construct()
    {
        parent::__construct();
        $this->data['titleHeader'] = Module::where('url', '=',Req::segment(1))->first(['icon', 'name', 'url']);
        $this->data['page_title'] = "Setting Page";
    }

    public function index()
    {        
        \RuleHelper::UserHasPermission('access');

        $this->data['setting_count'] = DB::table('settings')->whereNotNull('deleted_at')->count();

        return view('backend.pages.setting.setting_index', $this->data);
    }

    public function create()
    {
        \RuleHelper::UserHasPermission('create');

        $this->data['modules'] = DB::table('modules')
                                    ->whereNull('deleted_at')
                                    ->orderBy('created_at', 'ASC')
                                    ->get();

        return view('backend.pages.setting.setting_create', $this->data);
    }

    public function show(Setting $setting)
    {
        \RuleHelper::UserHasPermission('read');

        $this->data['setting'] = $setting;

        return view('backend.pages.setting.setting_show',$this->data);
    }

    public function edit(Setting $setting)
    {        
        \RuleHelper::UserHasPermission('update');

        $this->data['setting'] = $setting;

        return view('backend.pages.setting.setting_edit',$this->data);   
    }

    public function trash()
    {
        \RuleHelper::UserHasPermission('trash');

        $this->data['setting_count'] = DB::table('settings')->whereNotNull('deleted_at')->count();

        return view('backend.pages.setting.setting_trash', $this->data);
    }

    public function get_datatable(Request $request)
    {
        \RuleHelper::UserHasPermission('access');

        $user = Auth::user();
        if($this->adminIsSuper('super')){            
            $data = Setting::select([                
                                    'id',
                                    'key',
                                    'no_delete',
                                    'value',
                                    'created_at',
                                    'updated_at'
                                    ])
                            ->where('key','!=','theme')
                            ->whereNull('deleted_at');
            $datatables = DataTables::of($data);
        }
        else{            
            $data = Setting::select([                
                                    'id',
                                    'key',
                                    'no_delete',
                                    'only_super',
                                    'value',
                                    'created_at',
                                    'updated_at'
                                    ])
                            ->where('key','!=','theme')
                            ->whereNull('deleted_at')
                            ->where('only_super','<>',1)
                            ->orWhere('only_super', null);
            $datatables = DataTables::of($data);
        }
        
        return $datatables
            ->addColumn('checkboxs', function(Setting $data) {
                $checkbox = " <input class='checkbox_item candelete' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>";
                if($data->no_delete == "1"){
                    $checkbox = "<input class='checkbox_item' id='dtcheckbox[]' type='checkbox' name='dtcheckbox[]' disabled>";
                }

                return $checkbox;
            })
            ->addColumn('number', function(Setting $data) {
                return "";
            })
            ->editColumn('created_at', function ($data){
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->addColumn('action', function(Setting $data) {
                $disabled = '';
                $btnDelete = '';
                $btnEdit = '';                
                if(\RuleHelper::UserHasPermission('update',false)==false && \RuleHelper::UserHasPermission('delete',false)==false){
                    $disabled = 'disabled';
                }            
                if(\RuleHelper::UserHasPermission('update',false)==true){
                    $btnEdit = "<a href='".route('setting.edit', $data->id)."' class='btn btn-sm btn-soft-secondary' title='Edit'><i class='las la-edit '></i></a>";                    
                }

                if(\RuleHelper::UserHasPermission('delete',false)==true && $data->no_delete!="1"){
                    $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='".$data->id."' title='Delete'><i class='las la-trash '></i></a>";
                }
                $btnView = "<a href='".route('setting.show', $data->id)."' class='btn btn-sm btn-soft-secondary' title='Detail'><i class='las la-search-plus '></i></a>";
                return 
                $btnView.' '.$btnEdit.' '.$btnDelete;                
            })            
            ->filterColumn('deleted_at', function($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('key')) {
                    $query->where('key', 'like', "%" . request('key') . "%");
                }

                if (request()->has('value')) {
                    $query->where('value', 'like', "%" . request('value') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs','number'])
            ->toJson();
    }

    public function get_datatable_trash(Request $request)
    {
        \RuleHelper::UserHasPermission('trash');        
        $data = Setting::select([
                                'id',
                                'key',
                                'value',
                                'created_at',
                                'deleted_at',
                                'deleted_by',
                                'updated_at'
                                ])
            ->whereNotNull('deleted_at');
        $datatables = DataTables::of($data);
        
        return $datatables
            ->addColumn('checkboxs', function(Setting $data) {
                return "
                    <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>
                ";
            })
            ->addColumn('action', function(Setting $data) {
                $btnView = "<a href='".route('setting.show', $data->id)."' class='btn btn-sm btn-soft-secondary'><i class='las la-search-plus '></i> View</a>";
                $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='".$data->id."'><i class='las la-trash'></i> Permanent Delete</a>";
                $btnRestore = "<a href='#' class='btn btn-sm btn-soft-secondary btnRestore' id='".$data->id."'><i class='ri-restart-line '></i> Restore</a>";
                return 
                $btnView."                                
                ".$btnRestore."                                
                ".$btnDelete;
            })
            ->addColumn('number', function(Setting $data) {
                return "";
            })
            ->editColumn('created_at', function ($data){
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->filterColumn('deleted_at', function($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('key')) {
                    $query->where('key', 'like', "%" . request('key') . "%");
                }

                if (request()->has('value')) {
                    $query->where('value', 'like', "%" . request('value') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs','number'])
            ->toJson();
    }

    public function store(Request $request)
    {
        \RuleHelper::UserHasPermission('update');
        
        $validator = Validator::make($request->all(), [
            'setting_key' =>  'required|unique:App\Models\Setting,key',
            'setting_value' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('setting/create')
                        ->withErrors($validator)
                        ->withInput();
        }

        $user = Auth::user();
        $data = new Setting;
        $data->key = $request->setting_key;
        $data->value = $request->setting_value;
        $data->created_by = $user->email;

        $data->save();

        \Session::flash('flash_message', 'New record created successfully'); 

        return redirect('/setting/create');
    }

    public function update(Request $request, Setting $setting)
    { 
        \RuleHelper::UserHasPermission('update');

        $validator = Validator::make($request->all(), [
            'setting_key' => 'required|unique:App\Models\Setting,key,'.$setting->id,
            'setting_value' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('setting/'.$setting->id.'/edit')
                        ->withErrors($validator)
                        ->withInput();
        }

        $user = Auth::user();
        Setting::where('id', $setting->id)
          ->update([
                    'key' => $request->setting_key,
                    'value' => $request->setting_value,
                    'updated_by' => $user->email
          ]);

        \Session::flash('flash_message', 'The record updated successfully'); 
        return redirect('setting/'.$setting->id.'/edit');
    }

    public function delete(Request $request)
    {
        \RuleHelper::UserHasPermission('delete');

        $table = "settings";
        $column_visible = "key";
        $route = "setting.destroy";
        return $this->delete_lib->deleteConfirmation($request,$table,$column_visible,$route);
    }

    public function destroy($delete_type)
    {   
        \RuleHelper::UserHasPermission('delete');

        $user = Auth::user();
        $dependencies_data = session('dependencies_data');
       
        $index = 0;
        $redirect_url = "";
        foreach($dependencies_data as $row){
            if($row['delete_access_type']!="restrict" && $delete_type=="harddelete"){
                if(count($row['data'])>0){
                    $delete_data = array_reverse($row['data']);
                    foreach($delete_data as $delete_data_row){
                        DB::table($delete_data_row['table'])
                            ->where($delete_data_row['field'], $delete_data_row['value'])
                            ->delete();
                    }
                }

                $redirect_url = "setting/trash";

                DB::table($row['table'])
                ->where('id', $row['id'])
                ->delete();

                $index++;
            }

            if($delete_type=="softdelete"){
                $redirect_url = "setting";

                DB::table($row['table'])
                ->where('id', $row['id'])
                ->update(['deleted_at' => date('Y-m-d H:i:s'),'deleted_by' => $user->email,'restored_at' => null,'restored_by' => null]);

                $index++;
            }
        }

        \Session::flash('flash_message',  $index.' Record[s] deleted successfully'); 
        return redirect($redirect_url);
    }

    public function restore(Request $request)
    {
        \RuleHelper::UserHasPermission('trash');

        if($request->id_to_be_restored==""){ $request->id_to_be_restored = []; }

        $data = DB::table('settings')
        ->whereIn('id', $request->id_to_be_restored)
        ->orderBy('deleted_at', 'desc')
        ->get();

        $this->data['index'] = 0; 
        $this->data['data'] = $data; 
        $this->data['route'] = 'setting.restore_action'; 
        $this->data['column_name'] = 'key'; 
        
        \Session::flash('dependencies_data',  $data); 
        return view('backend.includes.restore_modal', $this->data);
    }

    public function restore_action(Request $request)
    {
        \RuleHelper::UserHasPermission('trash');

        $user = Auth::user();
        $dependencies_data = session('dependencies_data');
        $index = 0;
        foreach($dependencies_data as $row){
            DB::table('settings')
                ->where('id', $row->id)
                ->update(
                    ['deleted_at' => null,'deleted_by' => null,
                    'restored_at' => date('Y-m-d H:i:s'),
                    'restored_by' => $user->email]);
            $index++;
        }

        \Session::flash('flash_message',  $index.' Record[s] restored successfully'); 
        return redirect('setting/trash');
    }
}
