<?php
namespace App\Http\Controllers\Core;

use App\Libraries\IndoitDeleteLib;
use App\Libraries\IndoitMenuLib;
use App\Models\ChangeLog;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class BackendController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $data;
    public $menu_lib;
    public $delete_lib;
    public $user;

    public function __construct()
    {
        $this->menu_lib   = new IndoitMenuLib();
        $this->delete_lib = new IndoitDeleteLib();

        $this->middleware(function ($request, $next) {
            if (Auth::check()) {
                $this->user = Auth::user();
            } else {
                return abort(404);
            }

            $default_role = Role::where('id', $this->user->default_role)->first();

            // Timpa default role jika sudah ada role yang dipilih di session
            if (session()->has('current_role') && session()->get('current_role')->id) {
                $default_role = Role::where('id', session()->get('current_role')->id)->first();
            }

            $this->data['default_role'] = $default_role;
            $role                       = Role::where('name', $default_role->name)->first();

            if ($role->theme_setting != "" && json_decode($role->theme_setting)) {
                $this->data['themes'] = json_decode($role->theme_setting);
            } else {
                $themes = Setting::where('key', 'theme')->first();
                if (! $themes) {
                    Setting::create([
                        'key'   => 'theme',
                        'value' => '[]',
                    ]);

                    $themes = Setting::where('key', 'theme')->first();
                }

                $this->data['themes'] = json_decode($themes->value);
            }

            $this->data['lastVersion'] = ChangeLog::latest('created_date')->first();
            $this->loadSidebar($this->user);
            $this->loadTheme();

            $this->data['userPrivilegesArrAll'] = DB::table('user_privileges')
                ->join('roles', 'roles.id', '=', 'user_privileges.roles_id')
                ->where('users_id', $this->user->id)
                ->select(['roles.id', 'roles.name'])
                ->get()
                ->toArray();

            // store akses user login
            $userStoreIdsRaw = $this->user->store_id;

            if (!is_array($userStoreIdsRaw)) {
                $userStoreIdsDecoded = $userStoreIdsRaw ? json_decode($userStoreIdsRaw, true) : [];
                $userStoreIdsRaw = is_array($userStoreIdsDecoded) ? $userStoreIdsDecoded : [];
            }

            $userStoreIds = array_map('strval', array_values($userStoreIdsRaw));

            // daftar akses store milik user login
            $this->data['storeOptionsById'] = Store::whereNull('deleted_at')
                                                ->whereIn('id', $userStoreIds)
                                                ->orderBy('name')
                                                ->pluck('name', 'id');

            return $next($request);
        });
    }

    public function loadSidebar($user)
    {
        if (session()->has('current_role') && session()->get('current_role')->id) {
            $userPrivilegesArr[] = session()->get('current_role')->id;
        } else {
            $userPrivilegesArr = DB::table('user_privileges')
                ->where('users_id', $user->id)
                ->pluck('roles_id')
                ->toArray();
        }

        $default_role        = $user->default_role;
        $role_has_privileges = DB::table('role_privileges')
            ->join('modules', 'modules.id', '=', 'role_privileges.modules_id')
            ->whereIn('role_privileges.roles_id', $userPrivilegesArr)
            ->whereNull('modules.deleted_at')
            ->select('modules.id', 'modules.name', 'modules.url', 'modules.icon')
            ->orderBy('modules.created_at', 'ASC')
            ->groupBy('modules.id')
            ->get();

        $super = DB::table('roles')->where('name', 'super')->first();

        $isAllowed = 0;
        if (in_array($super->id, $userPrivilegesArr)) {
            $isAllowed = 1;
        }

        $role_has_privileges_array            = [];
        $role_has_privileges_array_with_index = [];
        $idx                                  = 0;

        foreach ($role_has_privileges as $role_has_privileges_row) {
            $role_has_privileges_array[$role_has_privileges_row->id] = $role_has_privileges_row;
            $role_has_privileges_array_with_index[$idx]              = $role_has_privileges_row;
            $idx++;
        }

        $role = DB::table('roles')
            ->where('roles.id', $default_role)
            ->orderBy('roles.created_at', 'ASC')
            ->first();

        $modules = DB::table('modules')
            ->select('modules.id')
            ->where('modules.url', Request::segment(1))->first();

        $active_module_id = "";
        if (! empty($modules->id)) {
            $active_module_id = $modules->id;
        }

        $modules_schema = json_decode($role->modules_schema);

        $array_parent            = $this->menu_lib->get_array_parent($modules_schema);
        $active_menu             = $this->menu_lib->get_active_menu($array_parent, $active_module_id);
        $this->data['user_role'] = $userPrivilegesArr;

        $children = [];
        foreach ($modules_schema as $key => $value) {
            if (! empty($value->children)) {
                array_push($children, $value->id);
                if (! empty($value->children[0]->children)) {
                    array_push($children, $value->children[0]->id);
                }
            }
        }

        $arrExlude = [];
        foreach ($modules_schema as $key => $value) {
            if (isset($value->children)) {
                foreach ($value->children as $key2 => $value2) {
                    array_push($arrExlude, $value2->id);
                    if (isset($value2->children)) {
                        foreach ($value2->children as $key3 => $value3) {
                            array_push($arrExlude, $value3->id);
                        }
                    }
                }
            }
        }

        $this->data['activeMenu']          = $role_has_privileges_array;
        $this->data['sidebar']             = $this->menu_lib->generateSidebarMenu($modules_schema, $role_has_privileges_array, $role_has_privileges_array_with_index, $active_menu, $children, $arrExlude, 0, false);
        $this->data['adminIsSuper']        = $this->adminIsSuper();
        $this->data['role_has_privileges'] = $role_has_privileges;
        $this->data['isAllowed']           = $isAllowed;
    }

    public function loadTheme()
    {
        $this->data['setting_theme'] = DB::table('settings')
            ->where('key', 'theme')
            ->first();
    }

    public function adminIsSuper()
    {
        $adminIsSuper = DB::table('user_privileges')
            ->join('roles', 'roles.id', '=', 'user_privileges.roles_id')
            ->where('user_privileges.users_id', '=', $this->user->id)
            ->where('roles.name', '=', 'super')
            ->get();

        if (count($adminIsSuper) == 0) {
            return false;
        }

        return true;
    }

}
