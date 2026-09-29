<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Module;
use App\Models\RolePrivilege;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use DB;
use Request as Req;

class RoleController extends BackendController
{
    function __construct()
    {
        parent::__construct();
        $this->data['titleHeader'] = Module::where('url', '=',Req::segment(1))->first(['icon', 'name', 'url']);
        $this->data['page_title'] = "Role Page";
    }

    public function index()
    {
        \RuleHelper::UserHasPermission('access');

        $this->data['role_count'] = DB::table('roles')->whereNotNull('deleted_at')->count();
        
        return view('backend.pages.role.role_index', $this->data);
    }

    public function create()
    {
        \RuleHelper::UserHasPermission('create');

        $this->data['modules'] = Module::whereNull('deleted_at')
                                    ->orderBy('created_at', 'ASC')
                                    ->get();

        return view('backend.pages.role.role_create', $this->data);
    }

    public function show(Role $role)
    {
        \RuleHelper::UserHasPermission('read');

        $this->data['modules'] = DB::table('modules')
        ->whereNull('deleted_at')
        ->orderBy('created_at', 'ASC')
        ->get();


        $this->data['role'] = $role;
        return view('backend.pages.role.role_show',$this->data);
    }

    public function edit(Role $role)
    {
        \RuleHelper::UserHasPermission('update');

        $this->data['modules'] = DB::table('modules')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'ASC')
            ->get();

        $role_privileges = DB::table('role_privileges')
            ->where('role_privileges.roles_id', $role->id)
            ->orderBy('created_at', 'ASC')
            ->get();

        $role_privileges_return = null;        
        foreach($role_privileges as $row){

            $module_privileges = json_decode($row->privileges);            

            $role_privileges_return[$row->modules_id]=true;
            $role_privileges_return['access_'.$row->modules_id]=$module_privileges->access;
            $role_privileges_return['read_'.$row->modules_id]=$module_privileges->read;
            $role_privileges_return['create_'.$row->modules_id]=$module_privileges->create;
            $role_privileges_return['update_'.$row->modules_id]=$module_privileges->update;
            $role_privileges_return['delete_'.$row->modules_id]=$module_privileges->delete;
            $role_privileges_return['trash_'.$row->modules_id]=$module_privileges->trash; 
        }
        $this->data['role_privileges'] = $role_privileges_return;
        $this->data['role'] = $role;

        return view('backend.pages.role.role_edit',$this->data);   
    }

    public function position(Role $role)
    {
        \RuleHelper::UserHasPermission('update');

        $this->data['role'] = $role;
        $role_privileges = DB::table('role_privileges')
                                ->join('modules', 'modules.id', '=', 'role_privileges.modules_id')
                                ->whereNull('deleted_at')
                                ->where('role_privileges.roles_id', $role->id)
                                ->orderBy('modules.created_at', 'ASC')
                                ->get();

        $role_privileges_array = [];
        foreach($role_privileges as $role_privileges_row){
            $role_privileges_array[$role_privileges_row->id] = $role_privileges_row;
        }

        $this->data['modules_schema'] = json_decode($role->modules_schema);
        $this->data['role_privileges'] = $role_privileges;
        $this->data['role_privileges_array'] = $role_privileges_array;
        $this->data['role_has_permissions'] = RolePrivilege::join('modules','modules.id','=','role_privileges.modules_id')
                                                            ->where('role_privileges.roles_id',$role->id)
                                                            ->select([
                                                                'modules.*'
                                                            ])->get();        
        $this->data['module_position'] = $this->menu_lib->generateModulePosition($this->data['modules_schema'],$this->data['role_privileges_array']);
        
        return view('backend.pages.role.role_position',$this->data);

    }

    public function position_save(Request $request, Role $role)
    {
        \RuleHelper::UserHasPermission('update');

        Role::where('id', $role->id)
          ->update([
                'modules_schema' => $request->role_position
          ]);

        \Session::flash('flash_message', 'The record updated successfully'); 
        return redirect('role/'.$role->id.'/position');
    }

    public function trash()
    {
        \RuleHelper::UserHasPermission('trash');

        $this->data['role_count'] = DB::table('roles')->whereNotNull('deleted_at')->count();
        
        return view('backend.pages.role.role_trash', $this->data);
    }

    public function get_datatable(Request $request)
    {
        \RuleHelper::UserHasPermission('access');

        $user = Auth::user();

        if($this->adminIsSuper()){            
            $data = Role::select([                
                                    'id',
                                    'name',
                                    'description',
                                    'no_delete',
                                    'created_at',
                                    'updated_at'
                                ])->whereNull('deleted_at');
            $datatables = DataTables::of($data);
        }
        else{            
            $data = Role::select([                
                                    'id',
                                    'name',
                                    'description',
                                    'no_delete',
                                    'created_at',
                                    'updated_at'
                                ])->whereNull('deleted_at')
                                ->where('only_super','<>',1)
                                ->orWhere('only_super', null);
            $datatables = DataTables::of($data);
        }
        
        
        return $datatables
            ->addColumn('checkboxs', function(Role $data) {
                $checkbox = " <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>";
                if($data->no_delete=="1"){
                    $checkbox = "<input class='checkbox_item' id='dtcheckbox[]' type='checkbox' name='dtcheckbox[]' disabled>";
                }

                return $checkbox;
            })
            ->addColumn('number', function(Role $data) {
                return "";
            })
            ->addColumn('action', function(Role $data) {
                $disabled = '';
                $btnDelete = '';
                $btnEdit = '';
                $btnTheme = '';
                $btnPosition = '';
                if(\RuleHelper::UserHasPermission('update',false)==false && \RuleHelper::UserHasPermission('delete',false)==false){
                    $disabled = 'disabled';
                }

                if(\RuleHelper::UserHasPermission('update',false)==true){
                    $btnEdit = "<a href='".route('role.edit', $data->id)."' class='btn btn-sm btn-soft-secondary' title='Edit'><i class='las la-edit '></i></a>";
                    $btnTheme = "<a href='".route('role.settingTheme', $data->id)."' class='btn btn-sm btn-soft-secondary' title='Setting Theme'><i class='ri-settings-2-fill'></i></a>";
                    $btnPosition = "<a href='".route('role.position', $data->id)."' class='btn btn-sm btn-soft-secondary' title='Position'><i class='ri-list-unordered '></i></a>";
                }

                if(\RuleHelper::UserHasPermission('delete',false)==true && $data->no_delete!="1"){
                    $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='".$data->id."' title='Delete'><i class='las la-trash'></i></a>";
                }
                $btnView = "<a href='".route('role.show', $data->id)."' class='btn btn-sm btn-soft-secondary' title='Detail'><i class='las la-search-plus '></i></a>";
                return 
                $btnTheme.' '.$btnView.' '.$btnEdit.' '.$btnPosition.' '.$btnDelete;
                // .
                // "                
                //     <a href='#' class='btn btn-sm btn-soft-secondary'><i class='ri-more-2-fill  '></i></a>
                // ";
            })       
            ->editColumn('created_at', function ($data){
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })     
            ->filterColumn('deleted_at', function($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('name')) {
                    $query->where('name', 'like', "%" . request('name') . "%");
                }

                if (request()->has('description')) {
                    $query->where('description', 'like', "%" . request('description') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs','number'])
            ->toJson();
    }

    public function setting_theme($id)
    {        
        $role = Role::where('id',$id)->first();
        if (json_decode($role->theme_setting)) {
            $this->data['currentTheme'] = json_decode($role->theme_setting);
        }else {
            $this->data['currentTheme'] = null;
        }
        $this->data['role'] = $role;
        
        return view('backend.pages.role.setting_theme',$this->data);
    }

    public function reset_theme($id)
    {
        $role = Role::where('id',$id)->first();
        if (!$role) {
            \Session::flash('error_message', 'The data is not found!'); 

            return back();            
        }
        $role->theme_setting = null;
        $role->save();
        \Session::flash('flash_message', 'Theme Setting in '.$role->name.' reset successfully!'); 

        return redirect()->route('role.settingTheme',$id);
    }

    public function get_datatable_trash(Request $request)
    {
        \RuleHelper::UserHasPermission('trash');
        
        $data = Role::select([
            'id',
            'name',
            'description',
            'created_at',
            'deleted_at',
            'deleted_by',
            'updated_at'])
            ->whereNotNull('deleted_at');
        $datatables = DataTables::of($data);
        
        return $datatables
            ->addColumn('checkboxs', function(Role $data) {
                return "
                    <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>
                ";
            })                        
            ->addColumn('action', function(Role $data) {
                $btnView = "<a href='".route('role.show', $data->id)."' class='btn btn-sm btn-soft-secondary'><i class='las la-search-plus '></i> View</a>";
                $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='".$data->id."'><i class='las la-trash'></i> Permanent Delete</a>";
                $btnRestore = "<a href='#' class='btn btn-sm btn-soft-secondary btnRestore' id='".$data->id."'><i class='ri-restart-line '></i> Restore</a>";
                return 
                $btnView."                                
                ".$btnRestore."                                
                ".$btnDelete;
            })
            ->addColumn('number', function(Role $data) {
                return "";
            })
            ->filterColumn('deleted_at', function($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('name')) {
                    $query->where('name', 'like', "%" . request('name') . "%");
                }

                if (request()->has('description')) {
                    $query->where('description', 'like', "%" . request('description') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs','number'])
            ->toJson();
    }

    public function store(Request $request)
    {
        \RuleHelper::UserHasPermission('create');
        
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
        ]);        
        if ($validator->fails()) {
            return redirect('role/create')
                        ->withErrors($validator)
                        ->withInput();
        }          
        if (!isset($request->is_module) && !isset($request->roles)) {
            \Session::flash('error_message','Silakan memilih salah satu module terlebih dahulu!');            
            
            return back();
        }                        
        $defaultModuleSchema = [];
        foreach ($request->roles as $key => $value) {
            $tmp = [];
            $tmp['id'] = $value;
            array_push($defaultModuleSchema,$tmp);
        }        
        $user = Auth::user();
        $dataRole = new Role;
        $dataRole->name = $request->name;
        $dataRole->description = $request->description;
        $dataRole->guard_name = 'web';
        $dataRole->created_by = $user->email;
        $dataRole->modules_schema = json_encode($defaultModuleSchema);
        $dataRole->save();

        $dataRolePrivilege = $request->roles;
		$a = $request->access;
		$c = $request->create;
		$r = $request->read;
		$u = $request->update;
        $d = $request->delete;
        $t = $request->trash;

        if (!empty($dataRolePrivilege)) {
            for($i=0;$i<count($dataRolePrivilege);$i++){
                $arrPrivilege = array(
                    'access' => 0,
                    'create' => 0,
                    'read' => 0,
                    'update' => 0,
                    'delete' => 0,
                    'trash' => 0
                );

                if ($a[$i]==1) {
                    $arrPrivilege['access']=1;
                }

                if ($c[$i]==1) {
                    $arrPrivilege['create']=1;
                }

                if ($r[$i]==1) {
                    $arrPrivilege['read']=1;
                }

                if ($u[$i]==1) {
                    $arrPrivilege['update']=1;
                }

                if ($d[$i]==1) {
                    $arrPrivilege['delete']=1;
                }

                if ($t[$i]==1) {
                    $arrPrivilege['trash']=1;
                }
                $rolePrivilege = new RolePrivilege;
                $rolePrivilege->roles_id = $dataRole->id;
                $rolePrivilege->modules_id = $dataRolePrivilege[$i];
                $rolePrivilege->privileges = json_encode($arrPrivilege);
                $rolePrivilege->remark = $request->name;

                $rolePrivilege->save();
            }
        }

        \Session::flash('flash_message', 'New record created successfully'); 

        return redirect()->route('role.create');
    }

    public function update(Request $request, Role $role)
    { 
        \RuleHelper::UserHasPermission('update');

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('role/'.$role->id.'/edit')
                        ->withErrors($validator)
                        ->withInput();
        }
        if (!isset($request->is_module) && !isset($request->roles)) {
            \Session::flash('error_message','Silakan memilih salah satu module terlebih dahulu!');            
            
            return back();
        }
        $user = Auth::user();
        Role::where('id', $role->id)
          ->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'guard_name' => 'web',
                    'updated_by' => $user->email
          ]);

          $dataRole = $role;
          $dataRolePrivilege = $request->roles;

          $a = $request->access;
          $c = $request->create;
          $r = $request->read;
          $u = $request->update;
          $d = $request->delete;
          $t = $request->trash;
  
          DB::table('role_privileges')
            ->where('roles_id', $role->id)
            ->delete();
        
        if(!empty($dataRolePrivilege)){
            for($i=0;$i<count($dataRolePrivilege);$i++){

                $arrPrivilege = array(
                    'access' => 0,
                    'create' => 0,
                    'read' => 0,
                    'update' => 0,
                    'delete' => 0,
                    'trash' => 0
                );

                if($a[$i]==1){
                    $arrPrivilege['access']=1;
                }

                if($c[$i]==1){
                    $arrPrivilege['create']=1;
                }

                if($r[$i]==1){
                    $arrPrivilege['read']=1;
                }

                if($u[$i]==1){
                    $arrPrivilege['update']=1;
                }

                if($d[$i]==1){
                    $arrPrivilege['delete']=1;
                }

                if($t[$i]==1){
                    $arrPrivilege['trash']=1;
                }


                $rolePrivilege = new RolePrivilege;
                $rolePrivilege->roles_id = $dataRole->id;
                $rolePrivilege->modules_id = $dataRolePrivilege[$i];
                $rolePrivilege->privileges = json_encode($arrPrivilege);
                $rolePrivilege->remark = $request->name;

                $rolePrivilege->save();
            }
        }

        \Session::flash('flash_message', 'The record updated successfully'); 

        return redirect()->route('role.edit',$role->id);
    }

    public function delete(Request $request)
    {
        \RuleHelper::UserHasPermission('delete');

        $table = "roles";
        $column_visible = "name";
        $route = "role.destroy";
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

                $redirect_url = "role/trash";

                DB::table($row['table'])
                ->where('id', $row['id'])
                ->delete();

                $index++;
            }

            if($delete_type=="softdelete"){
                $redirect_url = "role";

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

        $data = DB::table('roles')
        ->whereIn('id', $request->id_to_be_restored)
        ->orderBy('deleted_at', 'desc')
        ->get();

        $this->data['index'] = 0; 
        $this->data['data'] = $data; 
        $this->data['route'] = 'role.restore_action'; 
        $this->data['column_name'] = 'name'; 
        
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
            DB::table('roles')
                ->where('id', $row->id)
                ->update(
                    ['deleted_at' => null,'deleted_by' => null,
                    'restored_at' => date('Y-m-d H:i:s'),
                    'restored_by' => $user->email]);
            $index++;
        }

        \Session::flash('flash_message',  $index.' Record[s] restored successfully'); 
        return redirect('role/trash');
    }
}
