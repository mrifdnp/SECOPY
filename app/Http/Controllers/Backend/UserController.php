<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Module;
use Request as Req;
use App\Models\UserPrivilege;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use DB;
use App\Helpers\IndonesiaitLib;
use App\Models\Store;

class UserController extends BackendController
{
    function __construct()
    {
        parent::__construct();
        $this->data['titleHeader'] = Module::where('url', '=',Req::segment(1))->first(['icon', 'name', 'url']);
        $this->data['page_title'] = "User Page";        
    }
    

    public function index()
    {
       \RuleHelper::UserHasPermission('access');              
        $this->data["title"]= "User Page";        
        $this->data['user_count'] = DB::table('users')->whereNotNull('deleted_at')->count();
        return view('backend.pages.user.user_index', $this->data);
    }

    public function create()
    {
        \RuleHelper::UserHasPermission('create');        
        $this->data['roles'] = DB::table('roles')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'ASC')
            ->get();

        $this->data['stores'] = Store::whereNull('deleted_at')
                                    ->pluck('name', 'id');

        return view('backend.pages.user.user_create',$this->data);
    }

    public function show(User $user)
    {
        \RuleHelper::UserHasPermission('read');

        $this->data['user'] = $user;
        $this->data['roles'] = DB::table('roles')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'ASC')
            ->get();

        $this->data['user_privileges'] = DB::table('user_privileges')
            ->where('users_id', $user->id)
            ->pluck('roles_id')->toArray();

        return view('backend.pages.user.user_show',$this->data);
    }

    public function edit(User $user)
    {
        \RuleHelper::UserHasPermission('update');

        $this->data['user'] = $user;
        $this->data['roles'] = DB::table('roles')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'ASC')
            ->get();

        $this->data['user_privileges'] = DB::table('user_privileges')
            ->where('users_id', $user->id)
            ->pluck('roles_id')->toArray();

        $this->data['stores'] = Store::whereNull('deleted_at')
                                    ->orderBy('name')
                                    ->pluck('name','id');
        
        $userStoreIds = $user->store_id;

        if(!is_array($userStoreIds)) {
            $userStoreIds = $userStoreIds ? json_decode($userStoreIds, true) : [];
            if(!is_array($userStoreIds)) $userStoreIds = [];
        }

        $this->data['user_store_ids'] = array_map('strval', array_values($userStoreIds));

        return view('backend.pages.user.user_edit',$this->data);
    }

    public function trash()
    {
        \RuleHelper::UserHasPermission('trash');

        $this->data['user_count'] = DB::table('users')->whereNotNull('deleted_at')->count();
        return view('backend.pages.user.user_trash', $this->data);
    }

    public function get_datatable(Request $request)
    {
       \RuleHelper::UserHasPermission('access');

        $user = Auth::user();
        if($this->adminIsSuper()){
           
            $data = User::select([
                'id',
                'name',
                'email',
                'no_delete',
                'created_at',
                'updated_at'])
                ->whereNull('deleted_at');
            $datatables = DataTables::of($data);
        }else{          
            $data = User::select([
                'id',
                'name',
                'email',
                'no_delete',
                'created_at',
                'updated_at'])
                ->whereNull('deleted_at')
                ->where('only_super','<>',1)
                ->orWhere('only_super', null);
            $datatables = DataTables::of($data);
        }
        
        return $datatables
            ->addColumn('checkboxs', function(User $data) {
                $checkbox = " <input class='checkbox_item candelete'  type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>";
                if($data->no_delete=="1"){
                    $checkbox = "<input class='checkbox_item'  type='checkbox' name='dtcheckbox[]' disabled>";
                }

                return $checkbox;
            })
            ->addColumn('number', function(User $data) {
                return "";
            })
            ->addColumn('action', function(User $data) {
                $disabled = '';
                $btnDelete = '';
                $btnEdit = '';
                if(\RuleHelper::UserHasPermission('update',false)==false && \RuleHelper::UserHasPermission('delete',false)==false){
                    $disabled = 'disabled';
                }

                if(\RuleHelper::UserHasPermission('update',false)==true){
                    $btnEdit = "<a href='".route('user.edit', $data->id)."' class='btn btn-sm btn-soft-secondary'><i class='las la-edit '></i></a>";
                }

                if(\RuleHelper::UserHasPermission('delete',false)==true && $data->no_delete!="1"){
                    $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='".$data->id."'><i class='las la-trash '></i></a>";
                }
                $btnView = "<a href='".route('user.show', $data->id)."' class='btn btn-sm btn-soft-secondary'><i class='las la-search-plus'></i></a>";
                return 
                $btnView.' '.$btnEdit.' '.$btnDelete;
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

                if (request()->has('email')) {
                    $query->where('email', 'like', "%" . request('email') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs','number'])
            ->toJson();
    }

    public function get_datatable_trash(Request $request)
    {
        \RuleHelper::UserHasPermission('trash');
        
        $data = User::select([        
                            'id',
                            'name',
                            'email',
                            'created_at',
                            'deleted_at',
                            'deleted_by',
                            'updated_at'
                            ])
            ->whereNotNull('deleted_at');            
        $datatables = DataTables::of($data);
        
        return $datatables                
            ->addColumn('checkboxs', function(User $data) {
                return "
                    <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>
                ";
            })                        
            ->addColumn('action', function(User $data) {
                $btnView = "<a href='".route('user.show', $data->id)."' class='btn btn-sm btn-soft-secondary'><i class='las la-search-plus '></i> View</a>";
                $btnDelete = '';
                if(\RuleHelper::UserHasPermission('delete',false)==true && $data->no_delete!="1"){
                    $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='".$data->id."'><i class='las la-trash'></i> Permanent Delete</a>";
                }                
                $btnRestore = "<a href='#' class='btn btn-sm btn-soft-secondary btnRestore' id='".$data->id."'><i class='ri-restart-line '></i> Restore</a>";
                return 
                $btnView."                                
                ".$btnRestore."                                
                ".$btnDelete;
            })
            ->addColumn('number', function(User $data) {
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
                if (request()->has('name')) {
                    $query->where('name', 'like', "%" . request('name') . "%");
                }

                if (request()->has('email')) {
                    $query->where('email', 'like', "%" . request('email') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs','number'])
            ->toJson();
    }

    public function store(Request $request)
    {
        \RuleHelper::UserHasPermission('create');

        $validator = Validator::make($request->all(), [
            'user_name' => 'required',
            'user_email' => 'required|email:rfc,dns|unique:App\Models\User,email',
            'user_password' => 'required',
            'user_confirm_password' => 'same:user_password',
            'store_id' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return redirect('user/create')
                        ->withErrors($validator)
                        ->withInput();
        }    
        $user_login = Auth::user();
        $data = new User;
        $data->name = $request->user_name;
        $data->email = $request->user_email;
        $data->default_role = $request->default_role;
        $data->password = Hash::make($request->user_password);
        $data->created_by = $user_login->email;

        $selectedStoreIds = $request->input('store_id');
        $data->store_id = (is_array($selectedStoreIds) && count($selectedStoreIds) > 0)
            ? json_encode(array_values($selectedStoreIds))
            : null;

        $data->save();

        foreach($request->groups as $row){
            $userPrivilege = new UserPrivilege;
            $userPrivilege->users_id = $data->id;
            $userPrivilege->roles_id = $row;

            $userPrivilege->save();
        }
       
        \Session::flash('flash_message', 'New record created successfully'); 

        return redirect()->route('user.create');
    }

    public function update(Request $request, User $user)
    {
        \RuleHelper::UserHasPermission('update');

        $validator = Validator::make($request->all(), [
            'user_name' => 'required',
            'user_email' => 'required|email:rfc,dns|unique:users,email,'.$user->id,',id',
            'user_password' => 'nullable',
            'user_confirm_password' => 'nullable|same:user_password',
            'store_id' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }
        
        $user_login = Auth::user();

        $selectedStoreIds = $request->input('store_id');
        $storeIdsJson = (is_array($selectedStoreIds) && count($selectedStoreIds) > 0) 
            ? json_encode(array_values($selectedStoreIds))
            : null; 

        if($request->user_password==""){
            User::where('id', $user->id)
            ->update([
                      'name' => $request->user_name,
                      'email' => $request->user_email,
                      'default_role' => $request->default_role,
                      'store_id' => $storeIdsJson,
                      'updated_by' => $user_login->email
            ]);
        }else{
            User::where('id', $user->id)
            ->update([
                      'name' => $request->user_name,
                      'email' => $request->user_email,
                      'default_role' => $request->default_role,
                      'store_id' => $storeIdsJson,
                      'password' => Hash::make($request->user_password),
                      'updated_by' => $user_login->email
            ]);
        }

        DB::table('user_privileges')
            ->where('users_id', $user->id)
            ->delete();

        foreach($request->groups as $row){
            $userPrivilege = new UserPrivilege;
            $userPrivilege->users_id = $user->id;
            $userPrivilege->roles_id = $row;

            $userPrivilege->save();
        }

        \Session::flash('flash_message', 'The record updated successfully'); 

        return redirect()->route('user.edit',$user->id);
    }

    public function delete(Request $request)
    {
        \RuleHelper::UserHasPermission('delete');

        $table = "users";
        $column_visible = "name";
        $route = "user.destroy";
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
                $delete_data = array_reverse($row['data']);
                foreach($delete_data as $delete_data_row){
                    DB::table($delete_data_row['table'])
                        ->where($delete_data_row['field'], $delete_data_row['value'])
                        ->delete();
                }

                $redirect_url = "user/trash";

                DB::table($row['table'])
                ->where('id', $row['id'])
                ->delete();

                $index++;
            }

            if($delete_type=="softdelete"){
                $redirect_url = "user";

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

        $data = DB::table('users')
        ->whereIn('id', $request->id_to_be_restored)
        ->orderBy('deleted_at', 'desc')
        ->get();

        $this->data['index'] = 0; 
        $this->data['data'] = $data; 
        $this->data['route'] = 'user.restore_action'; 
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
            DB::table('users')
                ->where('id', $row->id)
                ->update(
                    ['deleted_at' => null,'deleted_by' => null,
                    'restored_at' => date('Y-m-d H:i:s'),
                    'restored_by' => $user->email]);
            $index++;
        }

        \Session::flash('flash_message',  $index.' Record[s] restored successfully'); 
        return redirect('user/trash');
    }
}
