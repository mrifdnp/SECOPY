<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use App\Models\Module;
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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use Illuminate\Support\Facades\File; 
use Schema;

class ModuleController extends BackendController
{
    function __construct()
    {
        parent::__construct();
        $this->data['titleHeader'] = Module::where('url', '=',Req::segment(1))->first(['icon', 'name', 'url']);
        $this->data['page_title'] = "Module Page";
    }

    public function index()
    {
       \RuleHelper::UserHasPermission('access');
        
        $this->data["title"]= "Module Page";
        $this->data['module_count'] = Module::whereNotNull('deleted_at')->count();

        return view('backend.pages.module.module_index', $this->data);
    }
    private $order = 1;
    public function get_datatable(Request $request)
    {
       \RuleHelper::UserHasPermission('access');

       $data = Module::select([                                                
                                'id',
                                'name',
                                'icon',
                                'url',
                                'no_delete',
                                'created_at',
                                'updated_at'
                            ])
                        ->whereNull('deleted_at');
        $datatables = DataTables::of($data);
        
        return $datatables
            ->addColumn('checkboxs', function(Module $data) {
                $checkbox = " <input class='checkbox_item candelete checkdis-".$this->order."' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>";
                if($data->no_delete=="1"){
                    $checkbox = "<input class='checkbox_item checkdis-".$this->order."' id='dtcheckbox[]' type='checkbox' name='dtcheckbox[]' disabled>";
                }
                $this->order++;

                return $checkbox;
            })
            ->addColumn('number', function(Module $data) {
                return "";
            })
            ->addColumn('action', function(Module $data) {
                $disabled = '';
                $btnEdit = '';
                $btnDelete = '';
                if(\RuleHelper::UserHasPermission('update',false)==false && \RuleHelper::UserHasPermission('delete',false)==false){
                    $disabled = 'disabled';
                }

                if(\RuleHelper::UserHasPermission('update',false)==true){
                    $btnEdit = "<a href='".route('module.edit', $data->id)."' class='btn btn-sm btn-soft-secondary'><i class='las la-edit '></i></a>";
                }

                if(\RuleHelper::UserHasPermission('delete',false)==true && $data->no_delete!="1"){
                    $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='".$data->id."'><i class='las la-trash '></i></a>";
                }
                $btnView = "<a href='".route('module.show', $data->id)."' class='btn btn-sm btn-soft-secondary'><i class='las la-search-plus '></i></a>";
                return 
                    $btnView.' '.$btnEdit.' '.$btnDelete;                
            })          
            ->editColumn('created_at', function ($data){
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })  
            ->editColumn('icon', '<i class="{{ $icon }}"></i>')
            ->filterColumn('deleted_at', function($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('name')) {
                    $query->where('name', 'like', "%" . request('name') . "%");
                }            
            })
            ->rawColumns(['action', 'checkboxs','number'])
            ->escapeColumns([])
            ->toJson();
    }

    public function create()
    {
        \RuleHelper::UserHasPermission('create');

        $this->data['roles'] = Role::whereNull('deleted_at')
                                ->orderBy('created_at', 'ASC')
                                ->get();

        return view('backend.pages.module.module_create',$this->data);
    }

    public function store(Request $request)
    {
        \RuleHelper::UserHasPermission('create');
        
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'url' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('module/create')
                        ->withErrors($validator)
                        ->withInput();
        }        
        if($request->icon == ""){
            $request->icon = "fa fa-circle-o";
        }

        $user = Auth::user();
        $data = new Module;

        $data->name = $request->name;
        $data->url = $request->url;
        $data->icon = $request->icon;

        $rule['a'] = $request->a; 
		$rule['c'] = $request->c; 
		$rule['r'] = $request->r; 
		$rule['u'] = $request->u;  
        $rule['d'] = $request->d; 
        $rule['t'] = $request->t;
        $data->support = json_encode($rule);
        $data->created_by = $user->email;
        $data->save();
        $data->id;

        $arrPrivilege = array(
            'access' => $request->a,
            'create' => $request->c,
            'read' => $request->r,
            'update' => $request->u,
            'delete' => $request->d,
            'trash' => $request->t
        );
        $arrPrivilege = json_encode($arrPrivilege);

        if (is_array($request->groups)) {
            foreach ($request->groups as $row_group) {

                $rolePrivilege = new RolePrivilege;
                $rolePrivilege->roles_id = $row_group;
                $rolePrivilege->modules_id = $data->id;
                $rolePrivilege->privileges = $arrPrivilege;
                $rolePrivilege->remark = '';

                $rolePrivilege->save();
            }
        }

        \Session::flash('flash_message', 'New record created successfully'); 

        return redirect()->route('module.create');
    }

    public function show($id)
    {
        \RuleHelper::UserHasPermission('read');
        $this->data['module'] = Module::findOrFail($id);

        return view('backend.pages.module.module_show',$this->data);
    }

    public function edit($id)
    {
        \RuleHelper::UserHasPermission('update');

        $module = Module::where('id',$id)->first();
        if (!$module) {
            return back()->with('error_message', 'Module not found!');
        }
        $this->data['module'] = $module;

        return view('backend.pages.module.module_edit', $this->data);
    }

    public function update(Request $request, $id)
    {
        \RuleHelper::UserHasPermission('update');
        $module = Module::where('id',$id)->first();
        if (!$module) {
            return back()->with('error_message','Data not found!');
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'url' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect('module/'.$module->id.'/edit')
                        ->withErrors($validator)
                        ->withInput();
        }

        if ($request->icon == "") {
            $request->icon = "fa fa-circle-o";
        }

        $rule['a'] = $request->a; 
		$rule['c'] = $request->c; 
		$rule['r'] = $request->r; 
		$rule['u'] = $request->u;  
        $rule['d'] = $request->d; 
        $rule['t'] = $request->t;
        
        $rule = json_encode($rule);

        $user = Auth::user();
        Module::where('id', $module->id)
          ->update([
                    'name' => $request->name,
                    'url' => $request->url,
                    'icon' => $request->icon,
                    'support' => $rule,
                    'updated_by' => $user->email
          ]);

        \Session::flash('flash_message', 'The record updated successfully'); 

        return redirect()->route('module.edit',$id);
    }

    public function delete(Request $request)
    {
        \RuleHelper::UserHasPermission('delete');

        $table = "modules";
        $column_visible = "name";
        $route = "module.destroy";
        
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

                $redirect_url = "module/trash";

                $module = DB::table($row['table'])
                            ->where('id', $row['id'])->first();
                if ($module->is_generated == 1) {
                    $urlRoute = $module->url;
                    $moduleName = $module->name;
                    $dataRoutes = $this->dataRoute($urlRoute);
                    $modelSingular = $dataRoutes[0];
                    $tablePlural = $dataRoutes[1];
                    $tableSingular = $dataRoutes[2];
                    $url = $dataRoutes[3];
                    // remove route
                    $arrayTables = $this->getTables();

                    
                    if (in_array($tablePlural, $arrayTables)) {
                        $this->removeRoutes($url,$modelSingular,$tablePlural,$tableSingular);
                        // end remove route
                        // delete file migration
                        $rootQuery = DB::table('migrations')->where('migration', 'like', '%'.$tablePlural.'%')->orderBy('migration', 'desc');
                        $migration = $rootQuery->first();
                        $migrationPath = database_path("/migrations/");
                        $migrationFile = $migrationPath.$migration->migration.".php";
                    
                        if (file_exists($migrationFile) && !is_null($migration)) {
                            try {
                                Schema::dropIfExists($tablePlural);
                            } catch (\Illuminate\Database\QueryException $e) {
                                \Session::flash('error_message', $moduleName.' module failed to delete ('.$e->errorInfo[2].')'); 

                                return redirect('/module');
                            }
                            unlink($migrationFile);
                            $rootQuery->delete();                            
                        }
                    }

                    $permissions = DB::table('role_privileges')->where('modules_id', $module->id)->delete();
                    DB::table($row['table'])->where('id', $row['id'])->delete();
                    

                    if($tableSingular != '#' && $tableSingular != "" && $tableSingular != null){
                        // delete file depedencies
                        $exportFile = app_path("/Exports/{$modelSingular}Export.php");
                        if (file_exists($exportFile)) {
                            unlink($exportFile);
                        }
                        $model = app_path("/Models/{$modelSingular}.php");
                        if (file_exists($model)) {
                            unlink($model);
                        }
                        $controller = app_path("/Http/Controllers/Master/{$modelSingular}Controller.php");
                        if (file_exists($controller)) {
                            unlink($controller);
                        }
                        $pathImage = public_path("/img/{$tableSingular}/");
                        if (File::exists($pathImage)) {
                            File::deleteDirectory($pathImage);
                        }
                        $pathFile = public_path("/file/{$tableSingular}/");
                        if (File::exists($pathFile)) {
                            File::deleteDirectory($pathFile);
                        }
                        $viewPath = resource_path("views/backend/pages/{$tableSingular}");
                        if (File::exists($viewPath)) {
                            File::deleteDirectory($viewPath);
                        }
                    }

                } else {
                    DB::table($row['table'])->where('id', $row['id'])->delete();
                }                            

                $index++;
            }

            if($delete_type=="softdelete"){
                $redirect_url = "module";

                DB::table($row['table'])
                ->where('id', $row['id'])
                ->update(['deleted_at' => date('Y-m-d H:i:s'),'deleted_by' => $user->email,'restored_at' => null,'restored_by' => null]);

                $index++;
            }
        }

        \Session::flash('flash_message',  $index.' Record[s] deleted successfully'); 

        return redirect($redirect_url);
    }

    private function dataRoute($url)
    {
        if (str_word_count($url) > 1) {
            if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $url)){
                $split = preg_split('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $url);
            }else {
                $split = explode(" ", $url);
            }
            $dataSplit = $this->splitModuleName($split);
            $modelSingular = $dataSplit[0];
            $tablePlural = $dataSplit[1];
            $tableSingular = $dataSplit[2];
            $url = $dataSplit[3];
        }else{
            $cekWords = preg_split('/(?=[A-Z])/',strtolower($url));
            if (count($cekWords) > 1) {
                $dataSplit = $this->splitModuleName($cekWords);
                $modelSingular = $dataSplit[0];
                $tablePlural = $dataSplit[1];
                $tableSingular = $dataSplit[2];
                $url = $dataSplit[3];
            }else {
                $split = preg_split('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $url);
                if (count($split) > 1) {
                    $dataSplit = $this->splitModuleName($split);
                    $modelSingular = $dataSplit[0];
                    $tablePlural = $dataSplit[1];
                    $tableSingular = $dataSplit[2];
                    $url = $dataSplit[3];
                }else{
                    $modelSingular = ucwords(STR::singular($url));
                    $tablePlural = strtolower(STR::plural($url));
                    $tableSingular = strtolower(STR::singular($url));
                    $url = $tableSingular;
                }
            }
        }
        return [$modelSingular,$tablePlural,$tableSingular,$url];
    }

    private function getTables()
    {
        // $tables = DB::select('SHOW TABLES');
        $tables = DB::select('SELECT table_name FROM information_schema.tables WHERE table_schema = :schema', ['schema' => 'public']);
        $arrayTablesException = [
            'data_relations',
            'failed_jobs',
            'migrations',
            'model_has_roles',
            'modules',
            'password_resets',
            'role_privileges',
            'user_privileges',
            'roles',
            'settings',
            'data_relations',
            'transactions'
        ];
        $arrayTables = [];
        $tableName = 'Tables_in_'.env('DB_DATABASE');
        foreach ($tables as $key => $value) {
            // dd($tables[0]->table_name);
            if(!in_array($value->table_name, $arrayTablesException)){
                array_push($arrayTables,$value->table_name);
            }
        }
    
        return $arrayTables;
    }

    private function splitModuleName($data)
    {
        $modelSingular = '';
        $tablePlural = '';
        $tableSingular = '';
        $url = '';
        for ($i=0; $i <count($data) ; $i++) { 
            $modelSingular = $modelSingular.ucwords(STR::singular($data[$i]));
            $tablePlural = $tablePlural.strtolower(STR::singular($data[$i])).'_';
            $tableSingular = $tableSingular.strtolower(STR::singular($data[$i])).'_';
            $url = $url.strtolower(STR::singular($data[$i])).'-';
            if ($i = (count($data) - 1)) {
                $modelSingular = $modelSingular.ucwords(STR::singular($data[$i]));
                $tablePlural = $tablePlural.strtolower(STR::plural($data[$i])).'_';
                $tableSingular = $tableSingular.strtolower(STR::singular($data[$i])).'_';
                $url = $url.strtolower(STR::singular($data[$i])).'-';
            }
        }
        $tablePlural = rtrim($tablePlural, '_');
        $tableSingular = rtrim($tableSingular, '_');
        $url = rtrim($url, '-');
    
        return [$modelSingular,$tablePlural,$tableSingular,$url];
    }

    private function removeRoutes($url,$modelSingular,$tablePlural,$tableSingular)
    {
        $intro = PHP_EOL.PHP_EOL.'//route for '.$url;
        $r1 = PHP_EOL.'Route::post(\'/'.$url."/delete',[App\Http\Controllers\Master\\".$modelSingular."Controller::class,'delete'])->name('".$url.".delete');";        
        $r2 = PHP_EOL.'Route::post(\'/'.$url."/restore',[App\Http\Controllers\Master\\".$modelSingular."Controller::class,'restore'])->name('".$url.".restore');";        
        $r3 = PHP_EOL.'Route::post(\'/'.$url."/restore_action',[App\Http\Controllers\Master\\".$modelSingular."Controller::class,'restore_action'])->name('".$url.".restore_action');";                        
        $r4 = PHP_EOL.'Route::get(\'/'.$url."/get_datatable',[App\Http\Controllers\Master\\".$modelSingular."Controller::class,'get_datatable'])->name('".$url.".get_datatable');";                                
        $r5 = PHP_EOL.'Route::get(\'/'.$url."/get_datatable_trash',[App\Http\Controllers\Master\\".$modelSingular."Controller::class,'get_datatable_trash'])->name('".$url.".get_datatable_trash');";                                        
        $r6 = PHP_EOL.'Route::get(\'/'.$url."/trash',[App\Http\Controllers\Master\\".$modelSingular."Controller::class,'trash'])->name('".$url.".trash');";                                                
        $r7 = PHP_EOL.'Route::resource(\''.$url."',App\Http\Controllers\Master\\".$modelSingular."Controller::class);";                                                           
        $r8 = PHP_EOL."Route::get('/".$url."/download-file/{".$tableSingular."}',[App\Http\Controllers\Master\\".$modelSingular."Controller::class,'downloadFile'])->name('".$tableSingular.".download-file');";                        
        $r9 = PHP_EOL."Route::post('/".$url."/upload',[App\Http\Controllers\Master\\".$modelSingular."Controller::class,'upload'])->name('".$tableSingular.".upload');";                                        
        $r10 = PHP_EOL.'Route::get(\'/download-'.$tablePlural."-template',[App\Http\Controllers\Master\\".$modelSingular."Controller::class,'download'])->name('download.".$tablePlural."Template');";                                     
        $r11 = PHP_EOL.'Route::get(\'/get-'.$tablePlural."-template',[App\Http\Controllers\Master\\".$modelSingular."Controller::class,'get_template'])->name('get.".$tablePlural."Template');";                                 
        $r12 = PHP_EOL.'Route::get(\'/'.$url."/edit/{id}',[App\Http\Controllers\Master\\".$modelSingular."Controller::class,'edit'])->name('".$url.".edit');";                                        
        
        $arrayRoutes = [$intro,$r1,$r2,$r3,$r4,$r5,$r6,$r7,$r10,$r11,$r8,$r9,$r12];
        $filename = base_path('routes/web.php');
        $str = file_get_contents($filename);
        $a = 1;
        for ($i=0; $i < count($arrayRoutes); $i++) { 
            if (Str::contains($str, $arrayRoutes[$i])) { 
                $str = str_replace($arrayRoutes[$i], '',$str);
            }
        }
        file_put_contents($filename, $str);
    
        return 1;
    }

    public function trash()
    {
        \RuleHelper::UserHasPermission('trash');

        $this->data['module_count'] = Module::whereNotNull('deleted_at')->count();

        return view('backend.pages.module.module_trash', $this->data);
    }
    
    public function get_datatable_trash(Request $request)
    {
        \RuleHelper::UserHasPermission('trash');
        
        $data = Module::select([                                                
                                'id',
                                'name',
                                'icon',
                                'url',
                                'no_delete',
                                'created_at',
                                'updated_at'
                            ])
            ->whereNotNull('deleted_at');            
        $datatables = DataTables::of($data);
        
        return $datatables                
            ->addColumn('checkboxs', function(Module $data) {
                return "
                    <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>
                ";
            })                        
            ->addColumn('action', function(Module $data) {
                $btnView = "<a href='".route('module.show', $data->id)."' class='btn btn-sm btn-soft-secondary'><i class='las la-search-plus'></i> View</a>";
                $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='".$data->id."'><i class='las la-trash'></i> Permanent Delete</a>";
                $btnRestore = "<a href='#' class='btn btn-sm btn-soft-secondary btnRestore' id='".$data->id."'><i class='ri-restart-line '></i> Restore</a>";
                return 
                $btnView."                                
                ".$btnRestore."                                
                ".$btnDelete;
            })
            ->addColumn('number', function(Module $data) {
                return "";
            })
            ->editColumn('created_at', function ($data){
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })            
            ->editColumn('icon', '<i class="{{ $icon }}"></i>')
            ->filterColumn('deleted_at', function($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('name')) {
                    $query->where('name', 'like', "%" . request('name') . "%");
                }            
            })
            ->rawColumns(['action', 'checkboxs','number'])
            ->escapeColumns([])
            ->toJson();
    }

    public function restore(Request $request)
    {
        \RuleHelper::UserHasPermission('trash');

        if($request->id_to_be_restored==""){ $request->id_to_be_restored = []; }

        $data = DB::table('modules')
        ->whereIn('id', $request->id_to_be_restored)
        ->orderBy('deleted_at', 'desc')
        ->get();

        $this->data['index'] = 0; 
        $this->data['data'] = $data; 
        $this->data['route'] = 'module.restore_action'; 
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
            DB::table('modules')
                ->where('id', $row->id)
                ->update(
                    ['deleted_at' => null,'deleted_by' => null,
                    'restored_at' => date('Y-m-d H:i:s'),
                    'restored_by' => $user->email]);
            $index++;
        }

        \Session::flash('flash_message',  $index.' Record[s] restored successfully'); 
        return redirect()->route('module.trash');
    }
}
