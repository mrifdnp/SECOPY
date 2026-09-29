<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RuleHelper {
    public static function UserHasPermission($code,$redirect=true) {
        $module_url = \Request::segment(1);

        $user_login = Auth::user();

        $userPrivilegesArr = DB::table('user_privileges')
        ->where('users_id', $user_login->id)
        ->pluck('roles_id')->toArray();


        $modulePrivileges = DB::table('modules')
            ->join('role_privileges', 'role_privileges.modules_id', '=', 'modules.id')
            ->select('modules.id','role_privileges.privileges')
            ->whereNull('deleted_at')
            ->whereIn('role_privileges.roles_id', $userPrivilegesArr)
            ->where('modules.url', $module_url)->get();
            // dd($modulePrivileges);        
        $modulePrivilegesArr = array(
            'access' => 0,
            'create' => 0,
            'read' => 0,
            'update' => 0,
            'delete' => 0,
            'trash' => 0
        );


       foreach($modulePrivileges as $modulePrivileges_row){
          
            $privileges = json_decode($modulePrivileges_row->privileges,true);
           
            if($privileges['access']==1){
                $modulePrivilegesArr['access'] = 1 ;
            }

            if($privileges['create']==1){
                $modulePrivilegesArr['create'] = 1 ;
            }

            if($privileges['read']==1){
                $modulePrivilegesArr['read'] = 1 ;
            }

            if($privileges['update']==1){
                $modulePrivilegesArr['update'] = 1 ;
            }

            if($privileges['delete']==1){
                $modulePrivilegesArr['delete'] = 1 ;
            }

            if($privileges['trash']==1){
                $modulePrivilegesArr['trash'] = 1 ;
            }
       }
         
        if(empty($modulePrivileges)){
            return abort(404); 
        }

        if($modulePrivilegesArr[$code]==1){
            return true;
        }else{
            if($redirect==false){
                return false;
            }else{
                return abort(404);
            }
        }
    }
}