<?php

namespace App\Http\Controllers\Generator;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use DB;
use App\Helpers\IndonesiaitLib;
use App\Helpers\QueryHelper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Schema;
use App\Models\Module;
use App\Models\LogGenerator;
use Illuminate\Support\Facades\Auth;
use App\Models\RolePrivilege;
use Request as Req;


class BasicController extends BackendController
{
    function __construct()
    {
        parent::__construct();
        $this->data['page_title'] = "Generator Page";
    }

    public function index()
    {
        $arrayTables = $this->getTables();
        $this->data['tables'] = $arrayTables;

        return view('backend.pages.generator.basic',$this->data);
    }

    public function generate(Request $request)
    {
        $needFullPage = false;
        if ($request->has('component') && $request->type == 'modal') {
            foreach ($request->input('component') as $component) {
                if (in_array($component, ['file', 'texteditor'])) {
                    $needFullPage = true;
                }
            }
        }else{
            $needFullPage = true;
        }
        
        $currentData = json_encode($request->except('_token'));
        if (str_word_count($request->primary_table) > 1) {
            if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $request->primary_table)){
                $split = preg_split('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $request->primary_table);
            }else {
                $split = explode(" ", $request->primary_table);
            }
            $dataSplit = $this->splitWords($split);
            $modelPlural = $dataSplit[0];
            $modelSingular = $dataSplit[1];
            $tablePlural = $dataSplit[2];
            $tableSingular = $dataSplit[3];
            $url = $dataSplit[4];
            $pesan = $dataSplit[5];
            $title = $dataSplit[6];
        }else{
            $cekWords = preg_split('/(?=[A-Z])/',strtolower($request->primary_table));
            if (count($cekWords) > 1) {
                $dataSplit = $this->splitWords($cekWords);
                $modelPlural = $dataSplit[0];
                $modelSingular = $dataSplit[1];
                $tablePlural = $dataSplit[2];
                $tableSingular = $dataSplit[3];
                $url = $dataSplit[4];
                $pesan = $dataSplit[5];
                $title = $dataSplit[6];
            }else {
                $split = preg_split('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $request->primary_table);
                if (count($split) > 1) {
                    $dataSplit = $this->splitWords($split);
                    $modelPlural = $dataSplit[0];
                    $modelSingular = $dataSplit[1];
                    $tablePlural = $dataSplit[2];
                    $tableSingular = $dataSplit[3];
                    $url = $dataSplit[4];
                    $pesan = $dataSplit[5];
                    $title = $dataSplit[6];
                }else{
                    $modelPlural = ucwords(STR::plural($request->primary_table));
                    $modelSingular = ucwords(STR::singular($request->primary_table));
                    $tablePlural = strtolower(STR::plural($request->primary_table));
                    $tableSingular = strtolower(STR::singular($request->primary_table));
                    $url = $tableSingular;
                    $pesan = $tablePlural;
                    $title = $modelSingular;
                }
            }
        }
        $arrayNames = [$modelPlural, $modelSingular, $tablePlural, $tableSingular, $url, $pesan, $title];
        $urlRedirect = $url;
        $modul = "Modul ".$modelSingular." created successfully";
        File::append(base_path('routes/web.php'), $this->createRoute($arrayNames, $request));
        // check db is exist
        $arrayTables = $this->getTables();
        if (in_array($tablePlural, $arrayTables)) {
            $modul = "Modul ".$modelSingular." updated successfully";
            // delete file migration
            $rootQuery = DB::table('migrations')->where('migration', 'like', '%'.$tablePlural.'%');
            $migration = $rootQuery->first();
            $migrationPath = database_path("/migrations/");
            $migrationFile = $migrationPath.$migration->migration.".php";

            if (file_exists($migrationFile) && !is_null($migration)) {
                unlink($migrationFile);
                $rootQuery->delete();
                Schema::dropIfExists($tablePlural);

                $rootModule = DB::table('modules')->where('name', $title);
                $module = $rootModule->first();
                $permissions = DB::table('role_privileges')->where('modules_id', $module->id)->delete();
                $rootModule->delete();
            }
        }
        // call function
        if (!File::exists($path = resource_path("views/backend/pages/{$tableSingular}"))) {
            mkdir($path,0777,true);
        }
        $this->migration($this->createModelMigration($arrayNames,$request)[0]);
        $this->model($this->createModelMigration($arrayNames,$request)[1]);
        $this->controller($this->createControllerView($arrayNames, $request, $needFullPage)[0]);
        $this->indexBlade($this->createControllerView($arrayNames, $request, $needFullPage)[1]);
        $this->trashBlade($this->createControllerView($arrayNames, $request)[2]);
        $this->createBlade($this->createEditShowView($arrayNames, $request)[0]);
        $this->editBlade($this->createEditShowView($arrayNames, $request)[1]);
        $this->showBlade($this->createEditShowView($arrayNames, $request)[2]);
        $this->createModule($arrayNames, $request);
        $this->export($modelSingular);
        $checkModule = LogGenerator::where('module',$tablePlural)->first();
        if ($checkModule) {
            $checkModule->module = $tablePlural;
            $checkModule->log_data = $currentData;
            $checkModule->created_by = Auth::user()->email;
            $checkModule->save();
        } else {
            LogGenerator::create([
                'module' => $tablePlural,
                'log_data' => $currentData,
                'created_by' => Auth::user()->email
            ]);
        }
        $resp = [
            'modul' => $modul,
            'url' => url('/').'/'.$urlRedirect,
            'needFullPage' => $needFullPage
        ];

        return response()->json([
            'dataResult' => $resp
        ]);
    }

    public function getAttribute(Request $request)
    {
        $columns = Schema::getColumnListing($request->tabel);

        return json_encode(array(
            'columns' => $columns
        ));
    }

    public function checkTable(Request $request)
    {
        if (str_word_count($request->newTable) > 1) {
            if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $request->newTable)){
                $split = preg_split('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $request->newTable);
            }else {
                $split = explode(" ", $request->newTable);
            }
            $table = $this->checkDB($split);
        }else{
            $cekWords = preg_split('/(?=[A-Z])/',strtolower($request->newTable));
            if (count($cekWords) > 1) {
                $table = $this->checkDB($cekWords);
            }else {
                $split = preg_split('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $request->newTable);
                if (count($split) > 1) {
                    $table = $this->checkDB($split);
                }else{
                    $table = strtolower(STR::plural($request->newTable));
                }
            }
        }
        $data = 0;
        $arrayTables = $this->getTables();
        if (in_array($table, $arrayTables)) {
            $data = 1;
        }

        return json_encode(array(
            'dataResult' => $data
        ));
    }

    public function get_join(Request $request)
    {
        $arrayTables = $this->getTables();

        $dataTables = [];
        foreach ($arrayTables as $key => $value) {
            if($value != $request->table){
                array_push($dataTables,$value);
            }
        }
        $option = '';
        for ($i=0; $i < count($dataTables); $i++) {
            $option = $option.'<option value="'.$dataTables[$i].'">'.$dataTables[$i].'</option>';
        }

        $component = '<td class="col-sm-2"><input type="hidden" name="row_id[]" id="id_row_'.$request->seq.'" value="'.$request->seq.'"><input value="" type="text" class="form-control" name="field[]" placeholder="Field Name"></td><td class="col-sm-2"><input value="" type="text" class="form-control" name="label[]" placeholder="Label Name"> </td><td class="col-sm-1 component" id="select_row_'.$request->seq.'"><select data-id="'.$request->seq.'" id="component'.$request->seq.'" name="component[]" class="form-control select2 component_id"><option value="text">Text Field</option><option value="textCurrency">Text Field Currency</option><option value="select">Select</option><option value="password">Password</option><option value="email">Email</option><option value="radio">Radio</option><option value="checkbox">Checkbox</option><option value="selectjoin">Select Join</option><option value="textarea" >Text Area</option><option value="texteditor" >Text Editor</option><option value="file" >File</option><option value="image" >Image</option><option value="datepicker">Datepicker</option></select><input id="row_'.$request->seq.'1" value="" type="text" class="form-control display-none select-input" name="select_row_'.$request->seq.'[]" placeholder="Key, Value">	<span style="cursor:pointer" onclick="'.$request->javOnclikData.'" class=" select-input display-none ri-add-circle-fill"></span></td><td class="col-sm-1"><select name="typedata[]" class="form-control select2 type_id" id="type'.$request->seq.'"><option value="uuid">uuid</option> <option value="integer">integer</option> <option value="string" selected>string</option><option value="text">text</option><option value="boolean">boolean</option><option value="date">date</option><option value="datetime">datetime</option><option value="decimal" >decimal</option><option value="double" >double</option><option value="enum" >enum</option><option value="float" >float</option></select> <input id="type_row_'.$request->seq.'1" value="" type="text" class="form-control display-none custom_type" name="custom[]" placeholder="type(value)"></td><td class="col-sm-2 join"><select data-baris="'.$request->seq.'" id="joinTable" name="join_table[]" class="form-control display-none join-table-input"><option value="0">Pilih tabel</option>'.$option.'</select><input value="" id="joinId-'.$request->seq.'" type="text" class="form-control display-none join-id-input" name="join_id[]" placeholder="Join ID"><select id="selectTable-'.$request->seq.'" name="selectTable[]" class="form-control display-none join-table-input"></select><input value="" type="text" class="form-control display-none f_file" name="file_type[]" placeholder="type name,name"><input value="" type="text" class="form-control display-none f_file" name="file_size[]" placeholder="File Size (Kb)"><input value="" type="text" class="form-control display-none f_img" name="file_thumb[]" placeholder="Thumb H,W(Px)"></td><td class="col-sm-1"><select class="form-control" name="validation[]"><option value="1">Yes</option><option value="0">No</option></select></td><td class="col-sm-1"><select name="onlist[]" class="form-control select2"><option value="1">YES</option><option value="0">NO</option></select></td><td class="col-sm-1"><button type="button" class="btn-del btn-sm btn btn-danger btn-flat"><i class="ri-close-circle-fill"></i></button> <button onclick="add_after('.$request->seq.');" type="button" class="btn-sm btn btn-primary btn-flat"><i class="ri-add-circle-fill"></i></button> </td>';

        return json_encode(['data' => $component]);
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

    private function checkDB($data)
    {
        $table = '';
        for ($i=0; $i <count($data) ; $i++) {
            $table = $table.strtolower(STR::singular($data[$i])).'_';
            if ($i = (count($data) - 1)) {
                $table = $table.strtolower(STR::plural($data[$i])).'_';
            }
        }
        $table = rtrim($table, '_');

        return $table;
    }

    private function createRoute($names, $request)
    {
        $intro = PHP_EOL.PHP_EOL.'//route for '.$names[4];
        $r1 = PHP_EOL.'Route::post(\'/'.$names[4]."/delete',[App\Http\Controllers\Master\\".$names[1]."Controller::class,'delete'])->name('".$names[4].".delete');";
        $r2 = PHP_EOL.'Route::post(\'/'.$names[4]."/restore',[App\Http\Controllers\Master\\".$names[1]."Controller::class,'restore'])->name('".$names[4].".restore');";
        $r3 = PHP_EOL.'Route::post(\'/'.$names[4]."/restore_action',[App\Http\Controllers\Master\\".$names[1]."Controller::class,'restore_action'])->name('".$names[4].".restore_action');";
        $r4 = PHP_EOL.'Route::get(\'/'.$names[4]."/get_datatable',[App\Http\Controllers\Master\\".$names[1]."Controller::class,'get_datatable'])->name('".$names[4].".get_datatable');";
        $r5 = PHP_EOL.'Route::get(\'/'.$names[4]."/get_datatable_trash',[App\Http\Controllers\Master\\".$names[1]."Controller::class,'get_datatable_trash'])->name('".$names[4].".get_datatable_trash');";
        $r6 = PHP_EOL.'Route::get(\'/'.$names[4]."/trash',[App\Http\Controllers\Master\\".$names[1]."Controller::class,'trash'])->name('".$names[4].".trash');";
        $r7 = PHP_EOL.'Route::resource(\''.$names[4]."',App\Http\Controllers\Master\\".$names[1]."Controller::class);";
        $r10 = PHP_EOL.'Route::get(\'/download-'.$names[2]."-template',[App\Http\Controllers\Master\\".$names[1]."Controller::class,'download'])->name('download.".$names[2]."Template');";
        $r11 = PHP_EOL.'Route::get(\'/get-'.$names[2]."-template',[App\Http\Controllers\Master\\".$names[1]."Controller::class,'get_template'])->name('get.".$names[2]."Template');";
        $r12 = PHP_EOL.'Route::get(\'/'.$names[4]."/edit/{id}',[App\Http\Controllers\Master\\".$names[1]."Controller::class,'edit'])->name('".$names[4].".edit');";
        $r8 = PHP_EOL."";
        $r9 = PHP_EOL."";
        foreach ($request->component as $key => $value) {
            if ($value == 'file') {
                $r8 = PHP_EOL."Route::get('/".$names[4]."/download-file/{".$names[3]."}',[App\Http\Controllers\Master\\".$names[1]."Controller::class,'downloadFile'])->name('".$names[3].".download-file');";
            }
            if ($value == 'texteditor') {
                $r9 = PHP_EOL."Route::post('/".$names[4]."/upload',[App\Http\Controllers\Master\\".$names[1]."Controller::class,'upload'])->name('".$names[3].".upload');";
            }
        }

        $tablePlural = $names[2];
        $arrayTables = $this->getTables();
        $donlot = "'{".$names[3]."}'";
        if (in_array($tablePlural, $arrayTables)) {
            $dataRoutes = $this->dataRoute($names[4]);
            $modelSingular = $dataRoutes[0];
            $tablePlural = $dataRoutes[1];
            $tableSingular = $dataRoutes[2];
            $url = $dataRoutes[3];
            $this->removeRoutes($url,$modelSingular,$tablePlural,$tableSingular);
        }
        return $intro.$r1.$r2.$r3.$r4.$r5.$r6.$r7.$r10.$r11.$r12.$r8.$r9;
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
        $r12 = PHP_EOL.'Route::get(\'/'.$url."/edit/{id}',[App\Http\Controllers\Master\\".$modelSingular."Controller::class,'edit'])->name('".$modelSingular.".edit');";
        $arrayRoutes = [$intro,$r1,$r2,$r3,$r4,$r5,$r6,$r7,$r10,$r11,$r12,$r8, $r9];
        $filename = base_path('routes/web.php');
        $str=file_get_contents($filename);
        $a = 1;
        for ($i=0; $i < count($arrayRoutes); $i++) {
            if (Str::contains($str, $arrayRoutes[$i])) {
                $str = str_replace($arrayRoutes[$i], '',$str);
            }
        }
        file_put_contents($filename, $str);

        return 1;
    }

    private function createModelMigration($names,$request)
    {
        $data = new \stdClass();
        $dataModel = new \stdClass();
        $now = Carbon::now();
        $data->ClassName = 'Create'.$names[0].'Table';
        $data->table = $names[2];
        $data->namaFile = $now->format('Y').'_'.$now->format('m').'_'.$now->format('d').'_'.$now->format('His').'_create_'.$names[2].'_table';
        $dataKolom = null;
        $data->join = null;
        $tmpRelasi = [];
        $tableRelasi = [];
        foreach ($request->field as $key => $value) {
            $dataKolom = $dataKolom."'".$value."'".",".PHP_EOL;
            $tmp = null;
            if (in_array($request->typedata[$key],['decimal','float','double'])) {
                $tmp = "\$table->".$request->typedata[$key]."('".$request->field[$key]."', 8, 2)";
            }elseif (($request->typedata[$key] == 'uuid' || $request->typedata[$key] == 'integer') && ($request->component[$key] == 'text' || $request->component[$key] == 'selectjoin')) {
                if ($request->join_table[$key] != "0") {
                    if ($request->typedata[$key] == 'uuid') {
                        $tmp = "\$table->".$request->typedata[$key]."('".$request->field[$key]."')";
                    }else{
                        $tmp = "\$table->foreignId('".$request->field[$key]."')";
                    }
                    $remanen = "public function ".$request->join_table[$key]."(){ ".PHP_EOL."\t\treturn \$this->belongsTo('App\Models\\".STR::singular(ucwords($request->join_table[$key]))."', '".$request->field[$key]."');".PHP_EOL."}";
                    array_push($tmpRelasi,$remanen);
                    $data->join[$key] = "\$table->foreign('".$request->field[$key]."')->references('id')->on('".$request->join_table[$key]."')->onDelete('cascade')->onUpdate('cascade');";
                    array_push($tableRelasi,$request->join_table[$key]);
                }else{
                    if ($request->typedata[$key] == 'integer' && $request->field[$key] == 'id') {
                        $tmp = "\$table->id()";
                    }else{
                        $tmp = "\$table->".$request->typedata[$key]."('".$request->field[$key]."')->primary()";
                    }
                }
            }elseif ($request->typedata[$key] == 'enum') {
                $tmp2 = 'select_row_'.($key+1);
                $tmp3 = null;
                foreach ($request->$tmp2 as $key2 => $value2) {
                    $kunci = null;
                    $kunci = explode(",",$value2)[0];
                    $tmp3 = $tmp3."'".$kunci."',";
                }
                $opsi = rtrim($tmp3, ',');
                $tmp = "\$table->".$request->typedata[$key]."('".$request->field[$key]."', [".$opsi."])";
            }elseif ($request->component[$key] == 'email') {
                $tmp = "\$table->".$request->typedata[$key]."('".$request->field[$key]."')->unique()";
            }elseif ($request->component[$key] == 'texteditor' && ($request->typedata[$key] == 'text' || $request->typedata[$key] == 'string')) {
                $tmp = "\$table->text('".$request->field[$key]."')";
            }else{
                $tmp = "\$table->".$request->typedata[$key]."('".$request->field[$key]."')";
            }
            if ($request->validation[$key] == 1) {
                $tmp = $tmp.'->nullable();';
            }else{
                $tmp = $tmp.';';
            }
            $tmp = $tmp;
            $data->tmp[$key] = $tmp;
        }

        $data->migrationData = null;
        $dataModel->relations = null;
        foreach ($data->tmp as $key2 => $value2) {
            $data->migrationData = $data->migrationData.$value2.PHP_EOL;
        }

        // merge join table
        if (!is_null($data->join)) {
            $counter = 0;
            foreach ($data->join as $key3 => $value3) {
                $data->migrationData = $data->migrationData.PHP_EOL.$value3;
            }
            foreach ($tmpRelasi as $k => $nil) {
                $dataModel->relations = $dataModel->relations.PHP_EOL.PHP_EOL.$nil;
            }
        }

        $dataModel->name = $names[1];
        $dataModel->dataKolom = $dataKolom;
        $dataModel->namaTable = $names[2];

        $arrayData = [$data,$dataModel];

        return $arrayData;
    }

    private function createControllerView($names, $request, $needFullPage = false)
    {
        $dataController = new \stdClass();
        $dataController->fileFacade = '';
        $dataController->ckEditorCode = '';
        $dataController->queryActive = '';
        $dataController->queryShow = '';
        $dataController->url = $names[4];
        $queryShow = "$".$names[3]." = ".$names[1]."::findOrFail(\$id);";
        $dataController->title = $names[6];
        $dataController->queryTrash = '';
        $dataController->getImage = '';
        $dataController->getFile = '';
        $dataController->getJoinData = '';
        $dataController->modelRelation = '';
        $dataController->checkbox = '';
        $dataController->checkboxShow = '';
        $dataController->queryGetData = "$".$names[3]." = ".$names[1]."::findOrFail(\$id);";
        $dataController->queryGetDataDestroy = "$".$names[3]." = ".$names[1]."::findOrFail(\$row['id']);";
        $dataController->queryGetDeleteData = "$".$names[3]." = ".$names[1]."::findOrFail(\$row['id']);";
        $dataController->thisData = "\$this->data['".$names[3]."'] = $".$names[3].";".PHP_EOL;
        $dataController->thisDataShow = "\$this->data['".$names[3]."'] = $".$names[3].";".PHP_EOL;
        $dataController->queryFilter = $this->createEditShowView($names,$request)[0]->queryFilter;
        $dataController->nama = $names[3];
        $dataView = new \stdClass();
        $dataTrash = new \stdClass();
        $dataView->needFullPage = $needFullPage;
        $dataTrash->needFullPage = $needFullPage;
        $dataController->needFullPage = $needFullPage;
        $dataView->title = $names[6];
        $dataView->icon = $request->icon;
        $dataView->url = $names[4];
        $dataTrash->icon = $request->icon;
        $dataTrash->title = $names[6];
        $dataTrash->url = $names[4];
        $dataView->tablePlural = $names[2];
        $dataTrash->tablePlural = $names[2];
        $dataView->nama = $names[3];
        $dataTrash->nama = $names[3];
        $dataView->fields = '';
        $dataTrash->fields = '';
        $dataView->dataJs = '';
        $dataTrash->dataJs = '';
        $dataView->namaTabel = $names[2];
        $dataView->fieldsFilter = $this->createEditShowView($names,$request)[0]->fieldsFilter;
        $dataTrash->fieldsFilter = $this->createEditShowView($names,$request)[0]->fieldsFilter;
        $dataView->jsAddOns = $this->createEditShowView($names,$request)[0]->jsAddOnsIndexTrash;
        $dataTrash->jsAddOns = $this->createEditShowView($names,$request)[0]->jsAddOnsIndexTrash;
        $dataView->dataFilter = $this->createEditShowView($names,$request)[0]->dataFilter;
        $dataTrash->dataFilter = $this->createEditShowView($names,$request)[0]->dataFilter;
        $dataView->currencyMask = $this->createEditShowView($names,$request)[0]->currencyMask;
        $dataTrash->currencyMask = $this->createEditShowView($names,$request)[0]->currencyMask;
        $dataView->fieldsList = ''; 
        $dataTrash->fieldsList = ''; 

        // foreach ($request->field as $key => $field) {
        //     if($field === 'id') continue;

        //     $label = $request->label[$key] ?? ucwords(str_replace('_', ' ', $field));

        //     $inputHtml = '<div class="form-group mb-3">
        //     <label for="'.$field.'">'.$label.'</label>
        //     <input type="text" name="'.$field.'" id="'.$field.'" class="form-control" />
        //     </div>';

        //     $dataView->fieldsList .= $inputHtml;
        //     $dataTrash->fieldsList .= $inputHtml;
        // }
        $imageUpload = "";
        $fileUpload = "";
        $simpanData = "$".$names[3]." = new ".$names[1]."();".PHP_EOL;
        $simpanDataEdit = "";
        $joinKey = [];
        $joinField = [];
        $joinTable = [];
        $counter = 0;
        $joinSelect = '';
        $imageUploadEdit = '';
        $fileUploadEdit = '';
        $query = null;
        $dataKolom = "'id',".PHP_EOL;
        $dataKolomJoin = "'".$names[2].".id',".PHP_EOL;
        $tmpQuery = '';
        $fieldValidation = ['double','integer','float','decimal', 'string', 'boolean'];
        $validation = "\$validator = Validator::make(\$request->all(), [".PHP_EOL;
        $validationEdit = "\$validator = Validator::make(\$request->all(), [".PHP_EOL;
        $cek = 0;
        $cekJoin = 0;
        $columnshowNoJoin = '';
        $columnshowJoin = '';
        $arrayCheckboxKey = "";
        $arrayCheckboxValue = "";
        $arrayRadioKey = "";
        $arrayRadioValue = "";
        $arraySelectKey = "";
        $arraySelectValue = "";
        $editColumn = "";
        $addColumn = "";
        $addColumnRegister = "";
        $downloadFile = "";
        $dataController->arrayCheckbox = '';
        $dataController->imageDelete = '';
        $dataController->arrayRadio = '';
        $dataController->arraySelect = '';
        $dataController->convertDate = '';
        $dataController->thisDataJoin = '';
        $dataController->hasMedia = 0;
        $deleteRestoreCek = 1;
        $template1 = '<div class="form-group mb-3">'.PHP_EOL.'<label for="" class="col-sm-12 control-label">';
        $template2 = '</label>'.PHP_EOL.'<div class="col-sm-10 w-100">';
        $template3 = '</div>'.PHP_EOL.'</div>'.PHP_EOL.'';
        $fieldInput = '';
        $fieldInputEdit = '';
        $counter = 0;
        $join = 0;
        foreach ($request->field as $key => $value) {
            $name = "'".$value."'"; // hasilnya 'nama_field'
            // set label default
            $label = ucwords($request->label[$key]);
            if (is_null($request->label[$key])) {
                $labelValue = ucwords($value);
                $checkUnderscore = preg_match('/^[^\W_]+_[^\W_]+$/', $value);
                if ($checkUnderscore) {
                    $pembagian = explode('_',$value);
                    $labelValue = ucwords($pembagian[0]);
                    for ($a=1; $a <count($pembagian) ; $a++) {
                        $labelValue = $labelValue.' '.$pembagian[$a];
                    }
                }
                $label = $labelValue;
            }
            // end set label default

            if ($key == $deleteRestoreCek) {
                if ($request->join_table[$key] == '0') {
                    $dataController->deleteRestore = $value;
                }else {
                    if (count($request->field) > 2) {
                        $dataController->deleteRestore = $request->field[$key+1];
                    }else{
                        $dataController->deleteRestore = $value;
                    }
                }
            }
            // for show detail data
            if ($request->join_table[$key] != 0 || $request->join_table[$key] != '0') {
                // for join
                $columnshowJoin = $columnshowJoin."'".$request->join_table[$key].".".$request->selectTable[$cekJoin]." as ".STR::singular($request->join_table[$key])."',".PHP_EOL;
            }else {
                // for no join
                $columnshowNoJoin = $columnshowNoJoin."'".$value."',".PHP_EOL;
                $columnshowJoin = $columnshowJoin."'".$names[2].".".$value."',".PHP_EOL;
            }
            // end for show detail data
            $req = 'nullable|';
            if ($request->validation[$key] == 0 || $request->validation[$key] == '0') {
                $req = 'required|';
            }
            if ($request->onlist[$key] == '1' && $request->join_table[$key] == '0' ) {
                // controller
                $dataKolom = $dataKolom."'".$value."'".",".PHP_EOL;
                $dataKolomJoin = $dataKolomJoin."'".$names[2].".".$value."'".",".PHP_EOL;

                // view index
                $dataView->fields = $dataView->fields.'<th data-class="expand">'.$label.'</th>'.PHP_EOL;
                $dataView->dataJs = $dataView->dataJs."{".PHP_EOL."data: '".$value."',".PHP_EOL."name: '".$value."'".PHP_EOL."},".PHP_EOL;
                // view trash
                $dataTrash->fields = $dataTrash->fields.'<th data-class="expand">'.$label.'</th>'.PHP_EOL;
                $dataTrash->dataJs = $dataTrash->dataJs."{".PHP_EOL."data: '".$value."',".PHP_EOL."name: '".$value."'".PHP_EOL."},".PHP_EOL;

                // for add column
                if ($request->component[$key] == 'image') {
                    $addColumn = $addColumn."->addColumn('".$value."', function (\$data) {
                        \$img = '';
                        \$destination_path = public_path('/storage/".$names[3]."/');
                        $".$names[3]."Image = \$destination_path.\$data->".$value.";

                        if (file_exists($".$names[3]."Image) && !is_null(\$data->".$value.")) {
                            \$img = '<img src=\"'.asset('/storage/".$names[3]."/'.\$data->".$value.").'\"  height=\"70px\" style=\"text-align:center;\"/>';
                        } else {
                            \$words = explode(' ', trim(\$data->".$request->field[1]."));
                            \$first = strtoupper(substr(\$words[0], 0, 1));
                            \$second = isset(\$words[1]) ? strtoupper(substr(\$words[1], 0, 1)) : '';
                            \$initials = \$first.\$second;

                            \$img = '<img src=\"https://placehold.co/110x70?text='.\$initials.'\"/>';
                        }
                        return \$img;
                    })";
                    $addColumnRegister = $addColumnRegister."'".$value."',";

                    

                }elseif ($request->component[$key] == 'file') {
                    $addColumn = $addColumn."->addColumn('".$value."', function (\$data) {
                        \$download = '<a href=\"'.route('".$names[3].".download-file', ['".$names[3]."' => \$data->id]).'\" >'.\$data->".$value.".'</a>';
                        return \$download;
                    })";
                    $addColumnRegister = $addColumnRegister."'".$value."',";

                    $downloadFile = $downloadFile."public function downloadFile($".$names[3].")
                    {
                        $".$names[3]." = ".$names[1]."::findOrFail($".$names[3].");

                        \$file_path = public_path('file/".$names[3]."/'.$".$names[3]."->".$value.");

                        return response()->download(\$file_path);
                    }";
                }
                // end for add column

                // for edit column
                $row = 'select_row_'.$request->row_id[$key];
                if ($request->component[$key] == 'select' || $request->component[$key] == 'radio') {
                    $editColumn = $editColumn."->editColumn('".$value."', function(\$data) {
                        $".$value." = null;".PHP_EOL;
                    for ($i=0; $i < count($request->$row); $i++) {
                        $pilihan = explode(',',$request->$row[$i]);
                        $editColumn = $editColumn."if ('".$pilihan[0]."' == \$data->".$value.") {
                                $".$value." = '".$pilihan[1]."';
                            }";
                    }
                    $editColumn = $editColumn."return $".$value.";".PHP_EOL."})";

                    

                }elseif ($request->component[$key] == 'datepicker') {
                    $required = '';
                    if ($request->validation[$key] == 0 || $request->validation[$key] == '0') {
                        $required = 'required';
                    }

                    // view input
                    $fieldInput = $fieldInput.$template1.$label.$template2;
                    $fieldInput = $fieldInput.'<input type="date" class="form-control" name="'.$value.'" id="'.$value.'" placeholder="'.$request->label[$key].'" size="50" '.$required.'>'.PHP_EOL;
                    $fieldInput = $fieldInput.$template3;


                    if ($request->typedata[$key] == 'date') {
                        $editColumn = $editColumn."->editColumn('".$value."', function(\$data) {
                            return \Carbon\Carbon::parse(\$data->".$value.")->format('d F Y');
                        })";
                    }elseif ($request->typedata[$key] == 'datetime') {
                        $editColumn = $editColumn."->editColumn('".$value."', function(\$data) {
                            return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', \$data->".$value.")->format('d F Y H:i:s');
                        })";
                    }
                }elseif ($request->component[$key] == 'textCurrency') {
                    $editColumn = $editColumn."->editColumn('".$value."', function(\$data) {
                        if(\$data->price =='' || \$data->price == null || \$data->price == 0) 
                            return 'Rp. 0';
                        return 'Rp. '.\$this->rupiah(\$data->".$value.",0, ',' , '.');
                    })";
                }
                $editColumn = $editColumn."->editColumn('created_at', function (\$data){
                    return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', \$data->created_at)->format('d F Y H:i:s');
                })";
                // end for edit column
            }
            // upload ck editor
            if ($request->component[$key] == 'texteditor' && ($request->typedata[$key] == 'string' || $request->typedata[$key] == 'text')) {
                $dataController->ckEditorCode = $dataController->ckEditorCode."if(\$request->hasFile('upload')) {
                    if (\$request->hasFile('upload')) {
                    \$originName = \$request->file('upload')->getClientOriginalName();
                    \$fileName = pathinfo(\$originName, PATHINFO_FILENAME);
                    \$extension = \$request->file('upload')->getClientOriginalExtension();
                    \$fileName = \$fileName . '_' . time() . '.' . \$extension;

                    // Pastikan folder ada
                    \$path = storage_path('app/public/".$names[3]."');
                    if (!File::exists(\$path)) {
                        mkdir(\$path, 0777, true);
                    }

                    // Simpan file
                    Storage::disk('public')->put('".$names[3]."/' . \$fileName, File::get(\$request->file('upload')));

                    // Return format JSON yang diminta CKEditor 5
                    return response()->json([
                        'url' => asset('storage/".$names[3]."/' . \$fileName),
                        'uploaded' => true,
                    ]);
                }

                return response()->json(['error' => 'No file uploaded','uploaded' => false], 400);
                }";
            }
            // end upload ck editor
            // validation create
            if (in_array($request->typedata[$key], $fieldValidation) && ($request->component[$key] != 'textCurrency' && $request->component[$key] != 'image' && $request->component[$key] != 'file' && $request->component[$key] != 'checkbox' && $request->component[$key] != 'email')) {
                $required = '';
                 if ($request->validation[$key] == 0 || $request->validation[$key] == '0') {
                        $required = 'required';
                }

                if ($request->component[$key] == 'password') {
                    if ($req == 'required|') {
                        $validation = $validation."'".$value."' => '".$req.$request->typedata[$key]."|min:1',";
                        $validationEdit = $validationEdit."'".$value."' => 'nullable|".$request->typedata[$key]."',";
                    }else{
                        $validation = $validation."'".$value."' => 'nullable|".$request->typedata[$key]."|min:1',";
                        $validationEdit = $validationEdit."'".$value."' => 'nullable|".$request->typedata[$key]."',";
                    }
                }elseif($request->component[$key] == 'textarea') {
                    // for create blade
                    $fieldInput = $fieldInput.$template1.$label.$template2;
                    $fieldInput = $fieldInput.'<textarea class="form-control w-100" name="'.$value.'" rows="3" style="width: 50%" placeholder="'.$request->label[$key].'" '.$required.'>'.PHP_EOL;
                    $fieldInput = $fieldInput.'</textarea>'.PHP_EOL.$template3;

                }else{
                    if ($request->typedata[$key] == 'integer' && ($request->join_table[$key] != '0' || $request->join_table[$key] != 0)) {
                        $validation = $validation."'".$value."' => 'required|".$request->typedata[$key]."|exists:".$request->join_table[$key].",id',";
                        $validationEdit = $validationEdit."'".$value."' => 'required|".$request->typedata[$key]."|exists:".$request->join_table[$key].",id',";
                    }elseif ($request->typedata[$key] == 'integer' && $request->field[$key] != 'id') {
                        $validation = $validation."'".$value."' => '".$req.$request->typedata[$key]."',";
                        $validationEdit = $validationEdit."'".$value."' => '".$req.$request->typedata[$key]."',";
                    }elseif ($request->typedata[$key] == 'string' && $request->component[$key] == 'text') {
                        $validation = $validation."'".$value."' => '".$req."string',";
                        $validationEdit = $validationEdit."'".$value."' => '".$req."string',";
                    }
                }

                // fow show select and radio
                if ($request->component[$key] == 'select') {
                    // view input
                    $fieldInput = $fieldInput.$template1.$label.$template2;
                    $fieldInput = $fieldInput.'<select class="form-control js-example-basic-single select2-'.$value.'" name="'.$value.'" required>'.PHP_EOL;

                    $selectRow = 'select_row_'.$request->row_id[$key];
                    for ($i=0; $i < count($request->$selectRow); $i++) {
                        $pilihan = explode(',',$request->$selectRow[$i]);
                        $fieldInput = $fieldInput.'<option value="'.$pilihan[0].'">'.$pilihan[1].'</option>'.PHP_EOL;
                        $arraySelectKey = $arraySelectKey.$pilihan[0].",";
                        $arraySelectValue = $arraySelectValue.$pilihan[1].",";
                    }
                    $fieldInput = $fieldInput.'</select>'.PHP_EOL.$template3;

                    $arraySelectKey = rtrim($arraySelectKey,',');
                    $arraySelectValue = rtrim($arraySelectValue,',');

                    $dataController->arraySelect = $dataController->arraySelect."$".$value."Key = \"".$arraySelectKey."\";
                    $".$value."Value = \"".$arraySelectValue."\";
                    $".$value."ArrayKey = explode(',',$".$value."Key);
                    $".$value."ArrayValue = explode(',',$".$value."Value);
                    foreach ($".$value."ArrayKey as \$kunci => \$nil) {
                        if (\$nil == $".$names[3]."->".$value.") {
                            $".$names[3]."->".$value." = $".$value."ArrayValue[\$kunci];
                        }
                    }";
                }elseif($request->component[$key] == 'text') {
                    $required = '';

                    if ($request->validation[$key] == 0 || $request->validation[$key] == '0') {
                        $required = 'required';
                    }

                    $fieldInput = $fieldInput.$template1.$label.$template2;
                    $fieldInput = $fieldInput.'<input type="'.$request->component[$key].'" class="form-control" name="'.$value.'" id="'.$value.'" placeholder="'.$request->label[$key].'" size="50" '.$required.'>'.PHP_EOL;
                    $fieldInput = $fieldInput.$template3;

                  

                }elseif($request->component[$key] == 'password'){
                    $required = '';
                    if ($request->validation[$key] == 0 || $request->validation[$key] == '0') {
                        $required = 'required';
                    }

                    $fieldInput = $fieldInput.$template1.$label.$template2;
                    $fieldInput = $fieldInput.'<input type="'.$request->component[$key].'" class="form-control" name="'.$value.'" id="'.$value.'" placeholder="'.$request->label[$key].'" size="50" '.$required.'>'.PHP_EOL;
                    $fieldInput = $fieldInput.$template3;

                }elseif(($request->typedata[$key] == 'uuid' || $request->typedata[$key] == 'integer') && ($request->component[$key] == 'text' || $request->component[$key] == 'selectjoin')) {
                    if ($request->join_table[$key] != '0' || $request->join_table[$key] != 0) {
                        // for create blade
                    $fieldInput = $fieldInput.$template1.$label.$template2;
                    $selek = $request->selectTable[$counter];
                    $fieldInput = $fieldInput.'<select class="form-control js-example-basic-single select2" name="'.$value.'" required>
                    @foreach ($'.$request->join_table[$key].' as $'.STR::singular($request->join_table[$key]).') {
                        <option value="{{ $'.STR::singular($request->join_table[$key]).'->id }}" >{{ $'.STR::singular($request->join_table[$key]).'->'.$selek.' }}</option>
                    @endforeach';
                    $fieldInput = $fieldInput.'</select>'.PHP_EOL.$template3;

                    
                    $counter++;
                    $join++;
                    }
                }elseif ($request->component[$key] == 'radio') {
                    $selectRow = 'select_row_'.$request->row_id[$key];

                    $required = '';
                    if ($request->validation[$key] == 0 || $request->validation[$key] == '0') {
                        $required = 'required';
                    }

                    // view input
                    $fieldInput = $fieldInput.$template1.$label.$template2;
                    $fieldInput = $fieldInput.'<div class="radio">'.PHP_EOL;
                    $selectRow = 'select_row_'.$request->row_id[$key];
                    for ($i=0; $i < count($request->$selectRow); $i++) {
                        $pilihan = explode(',',$request->$selectRow[$i]);
                        $fieldInput = $fieldInput.'<input class="form-check-input" type="radio" id="'.$pilihan[1].'" name="'.$value.'" value="'.$pilihan[0].'" '.$required.' />
                                                    <label for="'.$pilihan[1].'">'.$pilihan[1].'</label>'.PHP_EOL;
                    }
                    $fieldInput = $fieldInput.'</div>'.PHP_EOL.$template3;

                    

                    for ($i=0; $i < count($request->$selectRow); $i++) {
                        $pilihan = explode(',',$request->$selectRow[$i]);
                        $arrayRadioKey = $arrayRadioKey.$pilihan[0].",";
                        $arrayRadioValue = $arrayRadioValue.$pilihan[1].",";
                    }
                    $arrayRadioKey = rtrim($arrayRadioKey,',');
                    $arrayRadioValue = rtrim($arrayRadioValue,',');

                    $dataController->arrayRadio = $dataController->arrayRadio."$".$value."Key = \"".$arrayRadioKey."\";
                    $".$value."Value = \"".$arrayRadioValue."\";
                    $".$value."ArrayKey = explode(',',$".$value."Key);
                    $".$value."ArrayValue = explode(',',$".$value."Value);
                    foreach ($".$value."ArrayKey as \$kunci => \$nil) {
                        if (\$nil == $".$names[3]."->".$value.") {
                            $".$names[3]."->".$value." = $".$value."ArrayValue[\$kunci];
                        }
                    }";
                }
                // end
            }elseif ($request->typedata[$key] == 'uuid' && ($request->join_table[$key] != '0' || $request->join_table[$key] != 0)) {
                $validation = $validation."'".$value."' => 'required|".$request->typedata[$key]."|exists:".$request->join_table[$key].",id',";
                $validationEdit = $validationEdit."'".$value."' => 'required|".$request->typedata[$key]."|exists:".$request->join_table[$key].",id',";
                // Create Input
                $fieldInput = $fieldInput.$template1.$label.$template2;
                $selek = $request->selectTable[$counter];
                $fieldInput = $fieldInput.'<select class="form-control js-example-basic-single select2" name="'.$value.'" required>
                @foreach($'.$request->join_table[$key].' as $'.STR::singular($request->join_table[$key]).') {
                    <option value="{{ $'.STR::singular($request->join_table[$key]).'->id }}" >{{ $'.STR::singular($request->join_table[$key]).'->'.$selek.' }}</option>
                @endforeach';
                $fieldInput = $fieldInput.'</select>'.PHP_EOL.$template3;

                
            }elseif ($request->component[$key] == 'textCurrency') {
                $required = '';
                if ($request->validation[$key] == 0 || $request->validation[$key] == '0') {
                    $required = 'required';
                }

                $validation = $validation."'".$value."' => '".$req."string',";
                $validationEdit = $validationEdit."'".$value."' => '".$req."string',";
                // View
                $fieldInput = $fieldInput.$template1.$label.$template2;
                $fieldInput = $fieldInput.'<input value="{{ old('.$name.') }}" type="'.$request->component[$key].'" class="form-control currency" name="'.$value.'" id="'.$value.'" placeholder="'.$request->label[$key].'" size="50" '.$required.'>'.PHP_EOL;
                $fieldInput = $fieldInput.$template3;
                $dataView->currencyMask .= "$('.".$value."').mask('000.000.000', {reverse: true});".PHP_EOL;
                
            }elseif ($request->typedata[$key] == 'text' || $request->component[$key] == 'texteditor') {
                $validation = $validation."'".$value."' => '".$req."string',";
                $validationEdit = $validationEdit."'".$value."' => '".$req."string',";
            }elseif ($request->component[$key] == 'email') {
                if ($request->validation[$key] == 0 || $request->validation[$key] == '0') {
                    $required = 'required';
                }

                $validation = $validation."'".$value."' => '".$req."email|unique:".$names[2].",".$value."',";
                $validationEdit = $validationEdit."'".$value."' => '".$req."email|unique:".$names[2].",".$value.",'.$".$names[3]."->id,";

                $fieldInput = $fieldInput.$template1.$label.$template2;
                $fieldInput = $fieldInput.'<input type="'.$request->component[$key].'" class="form-control" name="'.$value.'" id="'.$value.'" placeholder="'.$request->label[$key].'" size="50" '.$required.'>'.PHP_EOL;
                $fieldInput = $fieldInput.$template3;
               
            }elseif ($request->component[$key] == "image") {
                $dataController->hasMedia = 1;
                $validation = $validation."'".$value."' => '".$req."image|mimes:".$request->file_type[$key]."|max:".$request->file_size[$key]."',";
                // for image upload
                $pathImage = $names[3];
                // check folder is exists
                if (!File::exists($path = public_path("/storage/{$pathImage}/"))) {
                    mkdir($path,0777,true);
                }

                $imageUpload = $imageUpload."\$imageName = null;
                \$manager = new ImageManager(new Driver());
                if (\$request->hasFile('".$value."')) {
                    try {
                        \$fileGambar = \$request->file('".$value."');
                        \$imageName = time() . '-' . \$fileGambar->getClientOriginalName();

                        // Baca file dengan Intervention
                        \$image = \$manager->read(\$fileGambar->getRealPath());

                        // Resize ke ukuran max 300x300
                        // \$image->resize(300, null, function (\$constraint) {
                        //     \$constraint->aspectRatio();
                        //     \$constraint->upsize();   
                        // });

                        // Simpan hasil resize ke storage (storage/app/public/category)
                        \$path = '".$names[3]."/' . \$imageName;
                        \$saved = Storage::disk('public')->put(\$path, (string) \$image->encodeByExtension(\$fileGambar->getClientOriginalExtension()));

                        if (!\$saved) {
                            \Session::flash('error_message', 'Categories image failed to save');
                            return redirect()->route('".$names[4].".create')->withInput();
                        }
                    } catch (\Exception \$e) {
                        \Session::flash('error_message', '".$names[5]." image failed to save');
                        return redirect()->route('".$names[4].".create');
                    }
                    
                }";

                // for image edit get image
                $dataController->getImage = $dataController->getImage."\$destination_path = public_path('/img/".$names[3]."/');
                $".$names[3]."Image = \$destination_path.$".$names[3]."->".$value.";
                if (!file_exists($".$names[3]."Image) || is_null($".$names[3]."->".$value.")) {
                    $".$names[3]."->".$value." = null;
                }";

                $imageUploadEdit = $imageUploadEdit."$".$value." = \$request->".$value.";
                \$manager = new ImageManager(new Driver());
                if (\$request->hasFile('".$value."')) {
                    try {
                        \$fileGambar = \$request->file('".$value."');
                        \$imageName = time() . '-' . \$fileGambar->getClientOriginalName();

                        // Baca file dengan Intervention
                        \$image = \$manager->read(\$fileGambar->getRealPath());

                        // Resize ke ukuran max 300x300
                        // \$image->resize(300, null, function (\$constraint) {
                        //     \$constraint->aspectRatio();
                        //     \$constraint->upsize();   
                        // });

                        // Simpan hasil resize ke storage (storage/app/public/category)
                        \$path = '".$names[3]."/' . \$imageName;
                        \$saved = Storage::disk('public')->put(\$path, (string) \$image->encodeByExtension(\$fileGambar->getClientOriginalExtension()));

                        \$pathImage = '".$names[3]."';
                        \$path = public_path('/storage/' . \$pathImage . '/');
                        \$descImage = \$path . \$".$names[3]."->".$value.";
                        if (file_exists(\$descImage) && !is_null(\$".$names[3]."->".$value.")) {
                            unlink(\$descImage);
                        }
                        \$".$names[3]."->".$value." = \$imageName;

                        if (!\$saved) {
                            \Session::flash('error_message', 'Categories image failed to save');
                            return redirect()->route('".$names[4].".create')->withInput();
                        }
                    } catch (\Exception \$e) {
                        \Session::flash('error_message', '".$names[5]." image failed to save');
                        return redirect()->route('".$names[4].".create');
                    }
                    
                }";

                // for delete and destroy that contain image
                $dataController->imageDelete = $dataController->imageDelete."\$destination_path = public_path('/storage/".$names[3]."/');
                $".$names[3]."Image = \$destination_path.$".$names[3]."->".$value.";
                if (file_exists($".$names[3]."Image) && !is_null($".$names[3]."->".$value.")) {
                    unlink($".$names[3]."Image);
                }";


                



               
                $fieldInput = $fieldInput.$template1.$label.$template2;
                $fieldInput = $fieldInput.'<input value="'.$value.'" type="file" class="form-control" name="'.$value.'"  '.$required.'>'.PHP_EOL;
                $fieldInput = $fieldInput.$template3;

                $cek++;



            }elseif ($request->component[$key] == 'file') {
                $dataController->hasMedia = 1;

                $validation = $validation."'".$value."' => '".$req."mimes:".$request->file_type[$key]."',";

                $filePath = $names[3];

                // check folder is exists
                if (!File::exists($pathFile = public_path("/file/{$filePath}/"))) {
                    mkdir($pathFile,0777,true);
                }

                $fileUpload = $fileUpload."\$fileName = null;
                if (!is_null(\$request->".$value.")) {
                    \$pathFile = '".$names[3]."';
                    \$fullPathFile = public_path('/file/'.\$pathFile.'/');
                    \$dataFile = \$request->file('".$value."');
                    \$fileName = time().'-'.\$dataFile->getClientOriginalName();
                    \$simpanFile = \$dataFile->move(\$fullPathFile, \$fileName);

                    if (!\$simpanFile) {
                        \Session::flash('error_message', '".$names[5]." file failed to save');

                        return redirect()->route('".$names[4].".create');
                    }
                }";

                // for file edit
                $dataController->getFile = $dataController->getFile."\$destinationFilePath = public_path('/file/".$names[3]."/');
                $".$names[3]."File = \$destinationFilePath.$".$names[3]."->".$value.";
                if (!file_exists($".$names[3]."File) || is_null($".$names[3]."->".$value.")) {
                    $".$names[3]."->".$value." = null;
                }";

                $fileUploadEdit = $fileUploadEdit."$".$value." = \$request->".$value.";
                if (!is_null($".$value.")) {
                    \$fileArray = array('".$value."' => $".$value.");
                    \$rules = array(
                        '".$value."' => '".$req."mimes:".$request->file_type[$key]."'
                    );
                    \$validatorFile = Validator::make(\$fileArray, \$rules);
                    if (\$validatorFile->fails()){
                        return redirect()->back()
                                    ->withErrors(\$validatorFile)
                                    ->withInput();
                    }

                    \$pathFile = '".$names[3]."';
                    \$fullPathFile = public_path('/file/'.\$pathFile.'/');
                    $".$value."File = \$fullPathFile.$".$names[3]."->".$value.";
                    if (file_exists($".$value."File) && !is_null($".$names[3]."->".$value.")) {
                        unlink($".$value."File);
                    }
                    \$dataFile = \$request->file('".$value."');
                    \$fileName = time().'-'.\$dataFile->getClientOriginalName();
                    \$simpanFile = \$dataFile->move(\$fullPathFile, \$fileName);

                    $".$names[3]."->".$value." = \$fileName;

                    if (!\$simpanFile) {
                        \Session::flash('error_message', '".$names[5]." file failed to save');

                        return redirect()->route('".$names[4].".edit',\$id);
                    }
                }";
                $cek++;
            }elseif ($request->component[$key] == 'checkbox') {

                $required = '';
                if ($request->validation[$key] == 0 || $request->validation[$key] == '0') {
                    $required = 'required';
                }

                $validation = $validation."'".$value."' => '".$req."array',";
                $validationEdit = $validationEdit."'".$value."' => '".$req."array',";

                // View Input
                $fieldInput = $fieldInput.$template1.$label.$template2;
                $selectRow = 'select_row_'.$request->row_id[$key];
                $fieldInput = $fieldInput.'<div class="checkbox">'.PHP_EOL;
                for ($i=0; $i < count($request->$selectRow); $i++) {
                    $pilihan = explode(',',$request->$selectRow[$i]);
                    $fieldInput = $fieldInput.'<input class="form-check-input" id="'.$pilihan[1].'" type="checkbox" value="'.$pilihan[0].'" name="'.$value.'[]" '.$required.' />
                                                    <label for="'.$pilihan[1].'">'.$pilihan[1].'</label>'.PHP_EOL;
                }
                $fieldInput = $fieldInput.'</div>'.PHP_EOL.$template3;

                

                // for edit
                $dataController->checkbox = $dataController->checkbox."$".$value." = null;
                if (!is_null($".$names[3]."->".$value.")) {
                    $".$value." = explode(',',$".$names[3]."->".$value.");
                }".PHP_EOL;
                $dataController->thisData = $dataController->thisData."\$this->data['".$value."'] = $".$value.";".PHP_EOL;
                // end for edit

                // for show
                $dataController->checkboxShow = $dataController->checkboxShow."$".$value." = null;
                if (!is_null($".$names[3]."->".$value.")) {
                    $".$value." = explode(',',$".$names[3]."->".$value.");
                }".PHP_EOL;
                $selectRow = 'select_row_'.$request->row_id[$key];
                for ($i=0; $i < count($request->$selectRow); $i++) {
                    $pilihan = explode(',',$request->$selectRow[$i]);
                    $arrayCheckboxKey = $arrayCheckboxKey.$pilihan[0].",";
                    $arrayCheckboxValue = $arrayCheckboxValue.$pilihan[1].",";
                }
                $arrayCheckboxKey = rtrim($arrayCheckboxKey, ',');
                $arrayCheckboxValue = rtrim($arrayCheckboxValue, ',');
                $dataController->arrayCheckbox = $dataController->arrayCheckbox."$".$value."Key = \"".$arrayCheckboxKey."\";
                $".$value."Value = \"".$arrayCheckboxValue."\";$".$value."ArrayKey = explode(',',$".$value."Key);
                $".$value."ArrayValue = explode(',',$".$value."Value);";
                $dataController->thisDataShow = $dataController->thisDataShow."\$this->data['".$value."ArrayKey'] = $".$value."ArrayKey;
                \$this->data['".$value."ArrayValue'] = $".$value."ArrayValue;".PHP_EOL;
                $dataController->thisDataShow = $dataController->thisDataShow."\$this->data['".$value."'] = $".$value.";".PHP_EOL;
                // end for show
            }elseif ($request->component[$key] == 'textarea') {
                    // for create blade
                    $fieldInput = $fieldInput.$template1.$label.$template2;
                    $fieldInput = $fieldInput.'<textarea class="form-control" name="'.$value.'" rows="3" style="width: 50%" placeholder="'.$request->label[$key].'" '.$required.'>'.PHP_EOL;
                    $fieldInput = $fieldInput.'</textarea>'.PHP_EOL.$template3;

            }

            // for saveData
            if ($request->typedata[$key] == 'date') {
                $simpanData = $simpanData."\$tgl".$names[1]." = null;".
                "$".$names[3]."->".$value." = \$this->formatDate(\$request->".$value.", \$tgl".$names[1].", 3);".PHP_EOL;

                $simpanDataEdit = $simpanDataEdit."\$tgl".$names[1]." = null;".
                "$".$names[3]."->".$value." = \$this->formatDate(\$request->".$value.", \$tgl".$names[1].", 3);".PHP_EOL;

                // for edit convert date

                $dataController->convertDate = $dataController->convertDate."\$dateSplit = explode('-', $".$names[3]."->".$value.");$".$names[3]."->".$value." = \$dateSplit['1'].'/'.\$dateSplit['2'].'/'.\$dateSplit['0'];".PHP_EOL;

            }elseif ($request->component[$key] == 'image') {
                $simpanData = $simpanData."$".$names[3]."->".$value." = \$imageName;".PHP_EOL;
            }elseif ($request->component[$key] == 'file') {
                $simpanData = $simpanData."$".$names[3]."->".$value." = \$fileName;".PHP_EOL;
            }elseif ($request->component[$key] == 'text') {
                $simpanData = $simpanData."$".$names[3]."->".$value." = ucwords(\$request->".$value.");".PHP_EOL;
                $simpanDataEdit = $simpanDataEdit."$".$names[3]."->".$value." = ucwords(\$request->".$value.");".PHP_EOL;
            }elseif($request->component[$key] == 'password' && $request->typedata[$key] == 'string') {
                $simpanData = $simpanData."$".$names[3]."->".$value." = app('hash')->make(\$request->".$value.");".PHP_EOL;
                $simpanDataEdit = $simpanDataEdit."if (!is_null(\$request->".$value.")) {
                    $".$names[3]."->".$value." = app('hash')->make(\$request->".$value.");
                }".PHP_EOL;
            }elseif ($request->component[$key] == 'textCurrency') {
                $simpanData = $simpanData."$".$value." = str_replace('.', '', \$request->".$value.");
                $".$names[3]."->".$value." = $".$value.";".PHP_EOL;
                $simpanDataEdit = $simpanDataEdit."$".$value." = str_replace('.', '', \$request->".$value.");
                $".$names[3]."->".$value." = $".$value.";".PHP_EOL;
            }elseif ($request->component[$key] == 'checkbox') {
                $simpanData = $simpanData."\$sementara = null;
                if (!is_null(\$request->".$value.")) {
                    foreach (\$request->".$value." as \$k => \$nilai) {
                        \$sementara .= \$nilai.',';
                    }
                    \$sementara = rtrim(\$sementara, ',');
                }";
                $simpanData = $simpanData."$".$names[3]."->".$value." = \$sementara;".PHP_EOL;

                // edit checkbox
                $simpanDataEdit = $simpanDataEdit."\$sementara = null;
                if (!is_null(\$request->".$value.")) {
                    foreach (\$request->".$value." as \$k => \$nilai) {
                        \$sementara = \$sementara.\$nilai.',';
                    }
                    \$sementara = rtrim(\$sementara, ',');
                }";
                $simpanDataEdit = $simpanDataEdit."$".$names[3]."->".$value." = \$sementara;".PHP_EOL;
            }else{
                if ($value != 'id') {
                    $simpanData = $simpanData."$".$names[3]."->".$value." = \$request->".$value.";".PHP_EOL;
                    $simpanDataEdit = $simpanDataEdit."$".$names[3]."->".$value." = \$request->".$value.";".PHP_EOL;
                }
            }
            // end saveData
        }
        $validation = rtrim($validation, ',');
        $validationEdit = rtrim($validationEdit, ',');
        $simpanData = $simpanData."$".$names[3]."->created_by = Auth::user()->email;".PHP_EOL."\$simpan = $".$names[3]."->save();";
        $simpanDataEdit = $simpanDataEdit."$".$names[3]."->updated_by = Auth::user()->email;".PHP_EOL."\$simpan = $".$names[3]."->save();";
        $validation = $validation."]);";
        $validationEdit = $validationEdit."]);";

        foreach ($request->join_table as $key2 => $join) {
            if ($join != 0 || $join != "0") {
                array_push($joinKey,$request->field[$key2]);
                array_push($joinTable,$join);
                array_push($joinField, $request->selectTable[$counter]);
                $tmpQuery = $tmpQuery."\$this->data['".$join."'] = DB::table('".$join."')->whereNull('deleted_at')->get();".PHP_EOL;
                $dataController->thisDataJoin = $dataController->thisDataJoin."\$this->data['".$join."'] = DB::table('".$join."')->whereNull('deleted_at')->get();".PHP_EOL;
                $counter++;
            }
        }

        if (count($joinField) > 0 && count($joinKey) > 0) {
            // ada join
            // controller
            $query = "\$data = DB::table('".$names[2]."')";
            for ($i=0; $i <count($joinKey) ; $i++) {
                $joinSelect = $joinSelect."'".$joinTable[$i].".".$joinField[$i]." as ".STR::singular($joinTable[$i])."',".PHP_EOL;
                $query = $query."->join('".$joinTable[$i]."', '".$joinTable[$i].".id', '=', '".$names[2].".".$joinKey[$i]."')".PHP_EOL;

                // view index
                $dataView->fields = $dataView->fields.'<th data-class="expand">'.ucwords(STR::singular($joinTable[$i])).'</th>'.PHP_EOL;
                $dataView->dataJs = $dataView->dataJs."{".PHP_EOL."data: '".STR::singular($joinTable[$i])."',".PHP_EOL."name: '".STR::singular($joinTable[$i])."'".PHP_EOL."},".PHP_EOL;
                // view trash
                $dataTrash->fields = $dataTrash->fields.'<th data-class="expand">'.ucwords(STR::singular($joinTable[$i])).'</th>'.PHP_EOL;
                $dataTrash->dataJs = $dataTrash->dataJs."{".PHP_EOL."data: '".STR::singular($joinTable[$i])."',".PHP_EOL."name: '".STR::singular($joinTable[$i])."'".PHP_EOL."},".PHP_EOL;

                // for data edit join
                $dataController->getJoinData = $dataController->getJoinData."$".$joinTable[$i]." = ".ucwords(STR::singular($joinTable[$i]))."::whereNull('deleted_at')->get();";
                $dataController->thisData = $dataController->thisData."\$this->data['".$joinTable[$i]."'] = $".$joinTable[$i].";".PHP_EOL;
                $dataController->modelRelation = $dataController->modelRelation.'use App\Models\\'.ucwords(STR::singular($joinTable[$i])).';'.PHP_EOL;
            }
            $query = $query."->select([".rtrim($dataKolomJoin, ',').$joinSelect.PHP_EOL."'".$names[2].".created_at',".PHP_EOL."'".$names[2].".updated_at'])";

            $dataController->queryActive = $query."->whereNull('".$names[2].".deleted_at');";
            $dataController->queryTrash = $query."->whereNotNull('".$names[2].".deleted_at');";
        }else{
            // tidak ada join
            $query = "\$data = ".$names[1]."::select([".$dataKolom.
                                        "'created_at',".PHP_EOL."'deleted_at',".PHP_EOL."'deleted_by',".PHP_EOL."'updated_at'])";
            $dataController->queryActive = $query."->whereNull('deleted_at');";
            $dataController->queryTrash = $query."->whereNotNull('deleted_at');";
        }
        $dataView->fieldInput = $fieldInput;
        $dataView->fieldInputEdit = $fieldInput;
        $dataController->controllerName = $names[1].'Controller';
        $dataController->model = $names[1];
        $dataController->tables = $names[2];
        $dataController->table = $names[3];
        $dataController->view = $names[3];
        $dataController->validation = $validation;
        $dataController->queryShow = $queryShow;
        $dataController->validationEdit = $validationEdit;
        $dataController->tmpQuery = $tmpQuery;
        $dataController->imageUpload = $imageUpload;
        $dataController->imageUploadEdit = $imageUploadEdit;
        $dataController->fileUpload = $fileUpload;
        $dataController->fileUploadEdit = $fileUploadEdit;
        $dataController->simpanData = $simpanData;
        $dataController->simpanDataEdit = $simpanDataEdit;
        $dataController->editColumn = $editColumn;
        $dataController->addColumn = $addColumn;
        $dataController->downloadFile = $downloadFile;
        $addColumnRegister = rtrim($addColumnRegister,',');
        $dataController->addColumnRegister = $addColumnRegister;
        $dataController->dateNow = "\$tgl = null;
        \$dateNow = Carbon::now()->toDateString();
        \$this->data['dateNow'] = \$this->formatDate(\$dateNow,\$tgl,3);";
        $dataView->dateNow = "\$tgl = null;
        \$dateNow = Carbon::now()->toDateString();
        \$this->data['dateNow'] = \$this->formatDate(\$dateNow,\$tgl,3);";
        $arrayData = [$dataController, $dataView, $dataTrash];

        return $arrayData;
    }

    private function createEditShowView($names, $request)
    {
        $dataCreate = new \StdClass();
        $dataEdit = new \StdClass();
        $dataShow = new \StdClass();
        $dataCreate->nama = $names[3];
        $dataCreate->url = $names[4];
        $dataCreate->title = $names[6];
        $dataEdit->nama = $names[3];
        $dataEdit->url = $names[4];
        $dataEdit->title = $names[6];
        $dataShow->nama = $names[3];
        $dataShow->title = $names[6];
        if (is_null($request->icon) || $request->icon == '') {
            $request->icon = 'ri-checkbox-blank-circle-fill';
        }
        $dataCreate->icon = $request->icon;
        $dataCreate->currencyMask = '';
        $dataEdit->icon = $request->icon;
        $dataEdit->currencyMask = '';
        $dataShow->icon = $request->icon;
        $dataShow->currencyMask = '';
        $currencyMask = '';
        $dataCreate->tablePlural = $names[2];
        $dataEdit->tablePlural = $names[2];
        $dataShow->tablePlural = $names[2];
        $dataEdit->tableSingular = $names[3];
        $fieldsShow = '';
        $template1 = '<div class="form-group mb-3">'.PHP_EOL.'<label class="col-sm-12 control-label">';
        $template2 = '</label>'.PHP_EOL.'<div class="col-sm-12">';
        $template3 = '</div>'.PHP_EOL.'</div>'.PHP_EOL.'';
        $fieldsTmp = '';
        $fieldsTmpEdit = '';
        $fieldsFilter = '';
        $dataFilter = '';
        $queryFilter = '->filter(function ($query) {'.PHP_EOL;
        $counter = 0;
        $templateFilter1 = '<div class="form-group mb-3">
        <label class="col-sm-12 control-label">';
        $templateFilter2 = '</div>
        </div>'.PHP_EOL.'';
        $jsAddOns = null;
        $jsAddOnsIndexTrash = null;
        $join = 0;
        foreach ($request->field as $key => $kolom) {
            if (is_null($request->label[$key]) || $request->label[$key] == '') {
                $labelValue = ucwords($kolom);
                $checkUnderscore = preg_match('/^[^\W_]+_[^\W_]+$/', $kolom);
                if ($checkUnderscore) {
                    $pembagian = explode('_',$kolom);
                    $labelValue = ucwords($pembagian[0]);
                    for ($a=1; $a <count($pembagian) ; $a++) {
                        $labelValue = $labelValue.' '.$pembagian[$a];
                    }
                }
                $label = $labelValue;
            }else{
                $label = ucwords($request->label[$key]);
            }
            if (($request->typedata[$key] == 'uuid' || $request->typedata[$key] == 'integer') && ($request->component[$key] == 'text' || $request->component[$key] == 'selectjoin')) {
                if ($request->join_table[$key] != '0' || $request->join_table[$key] != 0) {
                    // for create blade
                    $fieldsTmp = $fieldsTmp.$template1.$label.$template2;
                    $selek = $request->selectTable[$counter];
                    $fieldsTmp = $fieldsTmp.'<select class="form-control js-example-basic-single select2" name="'.$kolom.'" required>
                    @foreach ($'.$request->join_table[$key].' as $'.STR::singular($request->join_table[$key]).') {
                        <option value="{{ $'.STR::singular($request->join_table[$key]).'->id }}" >{{ $'.STR::singular($request->join_table[$key]).'->'.$selek.' }}</option>
                    @endforeach';
                    $fieldsTmp = $fieldsTmp.'</select>'.PHP_EOL.$template3;

                    // for edit blade
                    $fieldsTmpEdit = $fieldsTmpEdit.$template1.$label.$template2;
                    $selek = $request->selectTable[$counter];
                    $fieldsTmpEdit = $fieldsTmpEdit.'<select class="form-control js-example-basic-single select2" name="'.$kolom.'" required>
                    @foreach ($'.$request->join_table[$key].' as $'.STR::singular($request->join_table[$key]).') {
                        <option value="{{ $'.STR::singular($request->join_table[$key]).'->id }}" @if ($'.$names[3].'->'.$kolom.' == $'.STR::singular($request->join_table[$key]).'->id)
                        selected=""
                        @endif>{{ $'.STR::singular($request->join_table[$key]).'->'.$selek.' }}</option>
                    @endforeach';$fieldsTmpEdit.'</select>'.PHP_EOL.$template3;

                    // fow show blade
                    $fieldsShow = $fieldsShow.'<tr>
                        <td style="width: 200px"><b>'.$label.'</b></td>
                        <td>{{ $'.$names[3].'->'.$request->join_table[$key].'->'.$request->selectTable[$join].' }}</td>
                    </tr>'.PHP_EOL;
                    // end for show blade
                    if ($request->onlist[$key] == 1 || $request->onlist == '1') {
                        // for filter
                        $fieldsFilter = $fieldsFilter.$templateFilter1.$label.'</label>
                        <div class="col-sm-12">
                            <select  name="'.$kolom.'" id="'.$kolom.'" class="form-control js-example-basic-single select2">
                                <option value="" selected>Silakan Pilih</option>
                                @foreach ($'.$request->join_table[$key].' as $'.STR::singular($request->join_table[$key]).') {
                                    <option value="{{ $'.STR::singular($request->join_table[$key]).'->id }}" >{{ $'.STR::singular($request->join_table[$key]).'->'.$selek.' }}</option>
                                @endforeach';
                        $fieldsFilter = $fieldsFilter.'</select>'.PHP_EOL.$templateFilter2;
                        // end for filter
                        $dataFilter = $dataFilter."data.".$kolom." = $('#".$kolom." option:selected').val();".PHP_EOL;
                        // data js filter

                        // query filter for controller
                        if (!is_null($request->selectTable)) {
                            $queryFilter = $queryFilter."if (request()->has('".$kolom."') && !is_null(request('".$kolom."'))) {
                                    \$query->where('".$names[2].".".$kolom."', request('".$kolom."'));
                                }";
                        } else {
                            $queryFilter = $queryFilter."if (request()->has('".$kolom."') && !is_null(request('".$kolom."'))) {
                                    \$query->where('".$kolom."', request('".$kolom."'));
                                }";
                        }
                        // end query filter for controller
                    }
                    $counter++;
                    $join++;
                }
            }else {
                $name = "'".$kolom."'";
                $arrayField = ['text', 'email', 'password'];
                $required = '';
                if ($request->validation == 0 || $request->validation == '0') {
                    $required = 'required';
                }
                if (in_array($request->component[$key],$arrayField)) {
                    if ($request->component[$key] != 'password') {
                        // for create blade
                        $fieldsTmp = $fieldsTmp.$template1.$label.$template2;
                        $fieldsTmp = $fieldsTmp.'<input value="{{ old('.$name.') }}" type="'.$request->component[$key].'" class="form-control" name="'.$kolom.'" id="'.$kolom.'" placeholder="'.$request->label[$key].'" size="50" '.$required.'>'.PHP_EOL;
                        $fieldsTmp = $fieldsTmp.$template3;
                        // for edit blade
                        $fieldsTmpEdit = $fieldsTmpEdit.$template1.$label.$template2;
                        $fieldsTmpEdit = $fieldsTmpEdit.'<input value="{{ old('.$name.', $'.$names[3].'->'.$kolom.') }}" type="'.$request->component[$key].'" class="form-control" name="'.$kolom.'" id="'.$kolom.'" placeholder="'.$request->label[$key].'" size="50" '.$required.'>'.PHP_EOL;
                        $fieldsTmpEdit = $fieldsTmpEdit.$template3;

                        // fow show blade
                    $fieldsShow = $fieldsShow.'<tr>
                        <td style="width: 200px"><b>'.$label.'</b></td>
                        <td>{{ $'.$names[3].'->'.$kolom.' }}</td>
                    </tr>'.PHP_EOL;
                        // end for show blade

                        // for filter
                        if ($request->onlist[$key] == 1 || $request->onlist == '1') {
                            $fieldsFilter = $fieldsFilter.$templateFilter1.$label.'</label>
                            <div class="col-sm-12">
                                <input value="" type="text" class="form-control" name="'.$kolom.'" id="'.$kolom.'" placeholder="" size="40">';
                            $fieldsFilter = $fieldsFilter.PHP_EOL.$templateFilter2;

                            // data js
                            $dataFilter = $dataFilter."data.".$kolom." = $('#".$kolom."').val();".PHP_EOL;
                            // end data js

                            // query filter for controller
                            if (!is_null($request->selectTable)) {
                                $queryFilter = $queryFilter."if (request()->has('".$kolom."') && !is_null(request('".$kolom."'))) {
                                        \$query->where('".$names[2].".".$kolom."', 'like', \"%\" . request('".$kolom."') . \"%\");
                                    }";
                            } else {
                                $queryFilter = $queryFilter."if (request()->has('".$kolom."')  && !is_null(request('".$kolom."'))) {
                                        \$query->where('".$kolom."', 'like', \"%\" . request('".$kolom."') . \"%\");
                                    }";
                            }
                            // end query filter for controller
                        }
                        // end filter
                    }else{
                        $fieldsTmp = $fieldsTmp.$template1.$label.$template2;
                        $fieldsTmp = $fieldsTmp.'<input type="'.$request->component[$key].'" class="form-control" name="'.$kolom.'" id="'.$kolom.'" placeholder="'.$request->label[$key].'" size="50" '.$required.'>'.PHP_EOL;
                        $fieldsTmp = $fieldsTmp.$template3;

                        $fieldsTmpEdit = $fieldsTmpEdit.$template1.$label.$template2;
                        $fieldsTmpEdit = $fieldsTmpEdit.'<input type="'.$request->component[$key].'" class="form-control" name="'.$kolom.'" id="'.$kolom.'" value="{{ $'.$names[3].'->'.$kolom.' }}" size="50" '.$required.'>'.PHP_EOL;
                        $fieldsTmpEdit = $fieldsTmpEdit.$template3;
                    }
                }elseif ($request->component[$key] == 'file') {
                    // for create blade
                    $fieldsTmp = $fieldsTmp.$template1.$label.$template2;
                    $fieldsTmp = $fieldsTmp.'<input value="{{ old('.$name.') }}" type="file" class="form-control @error('.$name.') is-invalid @enderror" name="'.$kolom.'" id="'.$kolom.'" '.$required.'>'.PHP_EOL;
                    $fieldsTmp = $fieldsTmp.'<label class="label" for="inputFile">Choose file</label>'.PHP_EOL;
                    $fieldsTmp = $fieldsTmp.$template3;

                    // for edit blade
                    $fieldsTmpEdit = $fieldsTmpEdit.$template1.$label.$template2;
                    $fieldsTmpEdit = $fieldsTmpEdit.'<input value="{{ old('.$name.') }}" type="file" class="form-control @error('.$name.') is-invalid @enderror" name="'.$kolom.'" id="'.$kolom.'" >'.PHP_EOL;
                    $fieldsTmpEdit = $fieldsTmpEdit.'<label class="label" for="inputFile">Choose file</label>'.PHP_EOL;
                    $fieldsTmpEdit = $fieldsTmpEdit.$template3;

                    // for show blade
                    $fieldsShow = $fieldsShow.'<tr>
                        <td style="width: 200px"><b>'.$label.'</b></td>
                        <td><a href="{{route(\''.$names[4].'.download-file\',$'.$names[3].'->id)}}">{{ $'.$names[3].'->'.$kolom.' }}</a></td>
                    </tr>'.PHP_EOL;
                }elseif ($request->component[$key] == 'image') {
                    // for create blade
                    $fieldsTmp = $fieldsTmp.'<div class="form-group row">
                        <label for="" class="col-sm-12 control-label"></label>
                        <div class="col-lg-6 col-md-8 col-sm-10">
                            <img src="{{ asset(\'img/default-image.png\') }}" alt="" class="img-detail" style="width: 200px;height: auto;padding: 10px;vertical-align: middle;border-style: none;" id="imgEntity-'.$kolom.'">
                        </div>
                    </div>'.PHP_EOL.PHP_EOL;
                    $fieldsTmp = $fieldsTmp.$template1.$label.$template2;
                    $fieldsTmp = $fieldsTmp.'<label class="label" for="inputImage">Choose file</label>'.PHP_EOL;
                    $fieldsTmp = $fieldsTmp.'<input value="{{ old('.$name.') }}" type="file" class="form-control @error('.$name.') is-invalid @enderror" name="'.$kolom.'" id="inputImage-'.$kolom.'" '.$required.'>'.PHP_EOL;
                    $fieldsTmp = $fieldsTmp.$template3;

                    // for edit blade
                    $fieldsTmpEdit = $fieldsTmpEdit.'<div class="form-group row">
                        <label for="" class="col-sm-12 control-label"></label>
                        <div class="col-lg-6 col-md-8 col-sm-10">
                            @if (is_null($'.$names[3].'->'.$kolom.'))
                                <img src="{{ asset(\'img/default-image.png\') }}" alt="" class="img-detail" style="width: 200px;height: auto;padding: 10px;vertical-align: middle;border-style: none;" id="imgEntity-'.$kolom.'">
                            @else
                                <img src="{{ asset(\'img/'.$names[3].'/\'.$'.$names[3].'->'.$kolom.') }}" alt="" class="img-detail" style="width: 200px;height: auto;padding: 10px;vertical-align: middle;border-style: none;" id="imgEntity-'.$kolom.'">
                            @endif
                        </div>
                    </div>'.PHP_EOL.PHP_EOL;
                    $fieldsTmpEdit = $fieldsTmpEdit.$template1.$label.$template2;
                    $fieldsTmpEdit = $fieldsTmpEdit.'<label class="label" for="inputImage">Choose file</label>'.PHP_EOL;
                    $fieldsTmpEdit = $fieldsTmpEdit.'<input type="file" class="form-control @error('.$name.') is-invalid @enderror" name="'.$kolom.'" id="inputImage-'.$kolom.'" >'.PHP_EOL;
                    $fieldsTmpEdit = $fieldsTmpEdit.$template3;

                    // for show blade
                    $fieldsShow = $fieldsShow.'<tr>
                        <td style="width: 200px"><b>'.$label.'</b></td>
                        <td>
                            @if (!is_null($'.$names[3].'->'.$kolom.'))
                                <img src="{{ asset(\'img/'.$names[3].'/\'.$'.$names[3].'->'.$kolom.') }}" alt="{{ $'.$names[3].'->'.$kolom.' }}" style="width: 50%;">
                            @else
                                <img src="{{ asset(\'img/default-image.png\') }}" alt="{{ $'.$names[3].'->'.$kolom.' }}" style="width: 50%;">
                            @endif
                        </td>
                    </tr>'.PHP_EOL;
                    $jsAddOns = $jsAddOns."function getURL_".$kolom."(input) {
                        if (input.files && input.files[0]) {
                            var reader = new FileReader();
                            reader.onload = function(e) {
                                $('#imgEntity-".$kolom."').attr('src', e.target.result);
                            }
                            reader.readAsDataURL(input.files[0]);
                        }
                    }

                    $('#inputImage-".$kolom."').on('change',function(e){
                        getURL_".$kolom."(this);
                        var fileName = e.target.files[0].name;
                        $(this).next('.custom-file-label').html(fileName);
                    });".PHP_EOL;
                }elseif ($request->component[$key] == 'textarea') {
                    // for create blade
                    $fieldsTmp = $fieldsTmp.$template1.$label.$template2;
                    $fieldsTmp = $fieldsTmp.'<textarea class="form-control" name="'.$kolom.'" rows="3" style="width: 50%" placeholder="'.$request->label[$key].'" '.$required.'>'.PHP_EOL;
                    $fieldsTmp = $fieldsTmp.'</textarea>'.PHP_EOL.$template3;

                    // for edit blade
                    $fieldsTmpEdit = $fieldsTmpEdit.$template1.$label.$template2;
                    $fieldsTmpEdit = $fieldsTmpEdit.'<textarea class="form-control" name="'.$kolom.'" rows="3" style="width: 50%" placeholder="'.$request->label[$key].'" '.$required.'>{{$'.$names[3].'->'.$kolom.'}}'.PHP_EOL;
                    $fieldsTmpEdit = $fieldsTmpEdit.'</textarea>'.PHP_EOL.$template3;

                    // for show blade
                    $fieldsShow = $fieldsShow.'<tr>
                        <td style="width: 200px"><b>'.$label.'</b></td>
                        <td>{{ $'.$names[3].'->'.$kolom.' }}</td>
                    </tr>'.PHP_EOL;

                    // for filter
                    if ($request->onlist[$key] == 1 || $request->onlist == '1') {
                        $fieldsFilter = $fieldsFilter.$templateFilter1.$label.'</label>
                        <div class="col-sm-12">
                            <input value="" type="text" class="form-control" name="'.$kolom.'" id="'.$kolom.'" placeholder="" size="40">';
                        $fieldsFilter = $fieldsFilter.PHP_EOL.$templateFilter2;

                        // js data filter
                        $dataFilter = $dataFilter."data.".$kolom." = $('#".$kolom."').val();".PHP_EOL;
                        // end js data filter

                        // query filter for controller
                        if (!is_null($request->selectTable)) {
                            $queryFilter = $queryFilter."if (request()->has('".$kolom."') && !is_null(request('".$kolom."'))) {
                                    \$query->where('".$names[2].".".$kolom."', 'like', \"%\" . request('".$kolom."') . \"%\");
                                }";
                        } else {
                            $queryFilter = $queryFilter."if (request()->has('".$kolom."') && !is_null(request('".$kolom."'))) {
                                    \$query->where('".$kolom."', 'like', \"%\" . request('".$kolom."') . \"%\");
                                }";
                        }
                        // end query filter for controller
                    }
                    // end filter
                }elseif ($request->component[$key] == 'texteditor') {
                    // for create blade
                    $fieldsTmp = $fieldsTmp.$template1.$label.$template2;
                    $fieldsTmp = $fieldsTmp.'<textarea class="form-control" name="'.$kolom.'" id="editor-'.$kolom.'" rows="3" style="width: 50%" placeholder="'.$request->label[$key].'" '.$required.'>'.PHP_EOL;
                    $fieldsTmp = $fieldsTmp.'</textarea>'.PHP_EOL.$template3;

                    // for edit blade
                    $fieldsTmpEdit = $fieldsTmpEdit.$template1.$label.$template2;
                    $fieldsTmpEdit = $fieldsTmpEdit.'<textarea class="form-control" name="'.$kolom.'" id="editor-'.$kolom.'" rows="3" style="width: 50%" placeholder="'.$request->label[$key].'" '.$required.'>{!! $'.$names[3].'->'.$kolom.'!!}'.PHP_EOL;
                    $fieldsTmpEdit = $fieldsTmpEdit.'</textarea>'.PHP_EOL.$template3;

                    // for show blade
                    $fieldsShow = $fieldsShow.'<tr>
                        <td style="width: 200px"><b>'.$label.'</b></td>
                        <td>{!! $'.$names[3].'->'.$kolom.' !!}</td>
                    </tr>'.PHP_EOL;

                    // for filter
                    if ($request->onlist[$key] == 1 || $request->onlist == '1') {
                        $fieldsFilter = $fieldsFilter.$templateFilter1.$label.'</label>
                        <div class="col-sm-12">
                            <input value="" type="text" class="form-control" name="'.$kolom.'" id="'.$kolom.'" placeholder="" size="40">';
                        $fieldsFilter = $fieldsFilter.PHP_EOL.$templateFilter2;

                        // js data filter
                        $dataFilter = $dataFilter."data.".$kolom." = $('#".$kolom."').val();".PHP_EOL;
                        // end js data filter

                        // query filter for controller
                        if (!is_null($request->selectTable)) {
                            $queryFilter = $queryFilter."if (request()->has('".$kolom."') && !is_null(request('".$kolom."'))) {
                                    \$query->where('".$names[2].".".$kolom."', 'like', \"%\" . request('".$kolom."') . \"%\");
                                }";
                        } else {
                            $queryFilter = $queryFilter."if (request()->has('".$kolom."') && !is_null(request('".$kolom."'))) {
                                    \$query->where('".$kolom."', 'like', \"%\" . request('".$kolom."') . \"%\");
                                }";
                        }
                        // end query filter for controller
                    }
                    // end filter
                    $jsAddOns = $jsAddOns."$(document).ready(function() {
                        ClassicEditor
                        .create(document.querySelector('#editor-".$kolom."'), {
                            ckfinder: {
                                uploadUrl: \"{{route('".$names[3].".upload', ['_token' => csrf_token() ])}}\",
                            }
                        })
                        .then(editor => {
                            console.log('Editor siap dipakai', editor);
                        })
                        .catch(error => {
                            console.error(error);
                        });
                     });
                 ".PHP_EOL;
                }elseif ($request->component[$key] == 'textCurrency') {
                    // for create blade
                    $fieldsTmp = $fieldsTmp.$template1.$label.$template2;
                    $fieldsTmp = $fieldsTmp.'<input value="{{ old('.$name.') }}" type="text" class="form-control currency" name="'.$kolom.'" id="'.$kolom.'" placeholder="'.$request->label[$key].'" size="50" '.$required.'>'.PHP_EOL;
                    $fieldsTmp = $fieldsTmp.$template3;
                    $dataCreate->currencyMask = $dataCreate->currencyMask."$('.".$kolom."').mask('000.000.000', {reverse: true});".PHP_EOL;
                    // for edit blade
                    $fieldsTmpEdit = $fieldsTmpEdit.$template1.$label.$template2;
                    $fieldsTmpEdit = $fieldsTmpEdit.'<input value="{{ old('.$name.', $'.$names[3].'->'.$kolom.') }}" type="text" class="form-control currency" name="'.$kolom.'" id="'.$kolom.'" placeholder="'.$request->label[$key].'" size="50" '.$required.'>'.PHP_EOL;
                    $fieldsTmpEdit = $fieldsTmpEdit.$template3;
                    $dataEdit->currencyMask = $dataEdit->currencyMask."$('.".$kolom."').mask('000.000.000', {reverse: true});".PHP_EOL;

                    // fow show blade
                $fieldsShow = $fieldsShow.'<tr>
                    <td style="width: 200px"><b>'.$label.'</b></td>
                    <td>@format_currency($'.$names[3].'->'.$kolom.')</td>
                </tr>'.PHP_EOL;
                    // end for show blade
                    // for filter
                    if ($request->onlist[$key] == 1 || $request->onlist == '1') {
                        $fieldsFilter = $fieldsFilter.$templateFilter1.$label.'</label>
                        <div class="col-sm-12">
                            <input value="" type="text" class="form-control" name="'.$kolom.'" id="'.$kolom.'" placeholder="" size="40">';
                        $fieldsFilter = $fieldsFilter.PHP_EOL.$templateFilter2;

                        // data js
                        $dataFilter = $dataFilter."data.".$kolom." = $('#".$kolom."').val();".PHP_EOL;
                        // end data js
                        $currencyMask = $currencyMask."$('.".$kolom."').mask('000.000.000', {reverse: true});".PHP_EOL;
                        // query filter for controller
                        if (!is_null($request->selectTable)) {
                            $queryFilter = $queryFilter."if (request()->has('".$kolom."') && !is_null(request('".$kolom."'))) {
                                    $".$kolom." = str_replace('.', '', request('".$kolom."'));".PHP_EOL."
                                    \$query->where('".$names[2].".".$kolom."', 'like', \"%\" . $".$kolom." . \"%\");
                                }";
                        } else {
                            $queryFilter = $queryFilter."if (request()->has('".$kolom."')  && !is_null(request('".$kolom."'))) {
                                    $".$kolom." = str_replace('.', '', request('".$kolom."'));".PHP_EOL."
                                    \$query->where('".$kolom."', 'like', \"%\" . $".$kolom." . \"%\");
                                }";
                        }
                        // end query filter for controller
                    }
                    // end filter
                }elseif ($request->component[$key] == 'select') {
                    // for create blade
                    $fieldsTmp = $fieldsTmp.$template1.$label.$template2;
                    $fieldsTmp = $fieldsTmp.'<select class="form-control js-example-basic-single select2-'.$kolom.'" name="'.$kolom.'" required>'.PHP_EOL;
                    $selectRow = 'select_row_'.$request->row_id[$key];
                    for ($i=0; $i < count($request->$selectRow); $i++) {
                        $pilihan = explode(',',$request->$selectRow[$i]);
                        $fieldsTmp = $fieldsTmp.'<option value="'.$pilihan[0].'">'.$pilihan[1].'</option>'.PHP_EOL;
                    }
                    $fieldsTmp = $fieldsTmp.'</select>'.PHP_EOL.$template3;

                    // for edit blade
                    $fieldsTmpEdit = $fieldsTmpEdit.$template1.$label.$template2;
                    $fieldsTmpEdit = $fieldsTmpEdit.'<select class="form-control js-example-basic-single select2-'.$kolom.'" name="'.$kolom.'" required>'.PHP_EOL;
                    for ($i=0; $i < count($request->$selectRow); $i++) {
                        $pilihan = explode(',',$request->$selectRow[$i]);
                        $fieldsTmpEdit = $fieldsTmpEdit.'<option value="'.$pilihan[0].'" @if ("'.$pilihan[0].'" == $'.$names[3].'->'.$kolom.')
                        selected=""
                    @endif>'.$pilihan[1].'</option>'.PHP_EOL;
                    }
                    $fieldsTmpEdit = $fieldsTmpEdit.'</select>'.PHP_EOL.$template3;

                    // for show blade
                    $fieldsShow = $fieldsShow.'<tr>
                        <td style="width: 200px"><b>'.$label.'</b></td>
                        <td>{{ $'.$names[3].'->'.$kolom.' }}</td>
                    </tr>'.PHP_EOL;
                    // for filter
                    if ($request->onlist[$key] == 1 || $request->onlist == '1') {
                        $fieldsFilter = $fieldsFilter.$templateFilter1.$label.'</label>
                        <div class="col-sm-12">
                            <select class="form-control js-example-basic-single select2" name="'.$kolom.'" id="'.$kolom.'">
                                <option value="" selected="selected">Silakan Pilih '.$label.'</option>'.PHP_EOL;
                        $selectRow = 'select_row_'.$request->row_id[$key];
                        for ($i=0; $i < count($request->$selectRow); $i++) {
                            $pilihan = explode(',',$request->$selectRow[$i]);
                            $fieldsFilter = $fieldsFilter.'<option value="'.$pilihan[0].'">'.$pilihan[1].'</option>'.PHP_EOL;
                        }
                        $fieldsFilter = $fieldsFilter.'</select>'.PHP_EOL.$templateFilter2;
                        // js data filter
                        $dataFilter = $dataFilter."data.".$kolom." = $('#".$kolom." option:selected').val();".PHP_EOL;

                        // end js data filter

                        // query filter for controller
                        if (!is_null($request->selectTable)) {
                            $queryFilter = $queryFilter."if (request()->has('".$kolom."') && !is_null(request('".$kolom."'))) {
                                    \$query->where('".$names[2].".".$kolom."', 'like', \"%\" . request('".$kolom."') . \"%\");
                                }";
                        } else {
                            $queryFilter = $queryFilter."if (request()->has('".$kolom."') && !is_null(request('".$kolom."'))) {
                                    \$query->where('".$kolom."', 'like', \"%\" . request('".$kolom."') . \"%\");
                                }";
                        }
                        // end query filter for controller
                    }
                    // end filter
                    $jsAddOns = $jsAddOns."$(\".select2-".$kolom."\").select2();".PHP_EOL;
                    $jsAddOnsIndexTrash = $jsAddOnsIndexTrash."$(\".select2-".$kolom."\").select2();".PHP_EOL;
                }elseif ($request->component[$key] == 'radio') {
                    // for create blade
                    $fieldsTmp = $fieldsTmp.$template1.$label.$template2;
                    $fieldsTmp = $fieldsTmp.'<div class="radio">'.PHP_EOL;
                    $selectRow = 'select_row_'.$request->row_id[$key];
                    for ($i=0; $i < count($request->$selectRow); $i++) {
                        $pilihan = explode(',',$request->$selectRow[$i]);
                        $fieldsTmp = $fieldsTmp.'<input type="radio" id="'.$pilihan[1].'" name="'.$kolom.'" value="'.$pilihan[0].'" />
                                                    <label for="'.$pilihan[1].'">'.$pilihan[1].'</label>'.PHP_EOL;
                    }
                    $fieldsTmp = $fieldsTmp.'</div>'.PHP_EOL.$template3;

                    // for edit blade
                    $fieldsTmpEdit = $fieldsTmpEdit.$template1.$label.$template2;
                    $fieldsTmpEdit = $fieldsTmpEdit.'<div class="radio">'.PHP_EOL;
                    $selectRow = 'select_row_'.$request->row_id[$key];
                    for ($i=0; $i < count($request->$selectRow); $i++) {
                        $pilihan = explode(',',$request->$selectRow[$i]);
                        $fieldsTmpEdit = $fieldsTmpEdit.'<input type="radio" id="'.$pilihan[1].'" name="'.$kolom.'" value="'.$pilihan[0].'"  @if ("'.$pilihan[0].'" == $'.$names[3].'->'.$kolom.')
                        checked="checked"
                    @endif />
                    <label for="'.$pilihan[1].'">'.$pilihan[1].'</label>'.PHP_EOL;
                    }
                    $fieldsTmpEdit = $fieldsTmpEdit.'</div>'.PHP_EOL.$template3;

                    // for show blade
                    $fieldsShow = $fieldsShow.'<tr>
                        <td style="width: 200px"><b>'.$label.'</b></td>
                        <td>{{ $'.$names[3].'->'.$kolom.' }}</td>
                    </tr>'.PHP_EOL;
                    // for filter
                    if ($request->onlist[$key] == 1 || $request->onlist == '1') {
                        $fieldsFilter = $fieldsFilter.$templateFilter1.$label.'</label>
                        <div class="col-sm-12">
                            <select class="form-control" name="'.$kolom.'" id="'.$kolom.'">
                                <option value="" selected="selected">Silakan Pilih '.$label.'</option>'.PHP_EOL;
                        $selectRow = 'select_row_'.$request->row_id[$key];
                        for ($i=0; $i < count($request->$selectRow); $i++) {
                            $pilihan = explode(',',$request->$selectRow[$i]);
                            $fieldsFilter = $fieldsFilter.'<option value="'.$pilihan[0].'">'.$pilihan[1].'</option>'.PHP_EOL;
                        }
                        $fieldsFilter = $fieldsFilter.'</select>'.PHP_EOL.$templateFilter2;
                        // js data filter
                        $dataFilter = $dataFilter."data.".$kolom." = $('#".$kolom." option:selected').val();".PHP_EOL;
                        // end js data filter

                        // query filter for controller
                        if (!is_null($request->selectTable)) {
                            $queryFilter = $queryFilter."if (request()->has('".$kolom."') && !is_null(request('".$kolom."'))) {
                                    \$query->where('".$names[2].".".$kolom."', 'like', \"%\" . request('".$kolom."') . \"%\");
                                }";
                        } else {
                            $queryFilter = $queryFilter."if (request()->has('".$kolom."') && !is_null(request('".$kolom."'))) {
                                    \$query->where('".$kolom."', 'like', \"%\" . request('".$kolom."') . \"%\");
                                }";
                        }
                        // end query filter for controller
                    }
                    // end filter
                }elseif ($request->component[$key] == 'checkbox') {
                    // for create blade
                    $fieldsTmp = $fieldsTmp.$template1.$label.$template2;
                    $selectRow = 'select_row_'.$request->row_id[$key];
                    $fieldsTmp = $fieldsTmp.'<div class="checkbox">'.PHP_EOL;
                    for ($i=0; $i < count($request->$selectRow); $i++) {
                        $pilihan = explode(',',$request->$selectRow[$i]);
                        $fieldsTmp = $fieldsTmp.'<input id="'.$pilihan[1].'" type="checkbox" value="'.$pilihan[0].'" name="'.$kolom.'[]">
                                                    <label for="'.$pilihan[1].'">'.$pilihan[1].'</label>'.PHP_EOL;
                    }
                    $fieldsTmp = $fieldsTmp.'</div>'.PHP_EOL.$template3;
                    // for edit blade
                    $fieldsTmpEdit = $fieldsTmpEdit.$template1.$label.$template2;
                    $selectRow = 'select_row_'.$request->row_id[$key];
                    $fieldsTmpEdit = $fieldsTmpEdit.'<div class="checkbox">'.PHP_EOL;
                    for ($i=0; $i < count($request->$selectRow); $i++) {
                        $pilihan = explode(',',$request->$selectRow[$i]);
                        $fieldsTmpEdit = $fieldsTmpEdit.'';
                        $fieldsTmpEdit = $fieldsTmpEdit.'<input id="'.$pilihan[1].'" type="checkbox" value="'.$pilihan[0].'" name="'.$kolom.'[]" @if (!is_null($'.$kolom.'))
                            @if (in_array("'.$pilihan[0].'",$'.$kolom.'))
                                checked
                            @endif
                    @endif>
                    <label for="'.$pilihan[1].'">'.$pilihan[1].'</label>'.PHP_EOL;
                    }
                    $fieldsTmpEdit = $fieldsTmpEdit.'</div>'.PHP_EOL.$template3;

                    // for show blade
                    $fieldsShow = $fieldsShow.'<tr>
                        <td style="width: 200px"><b>'.$label.'</b></td>
                        <td>
                            @foreach ($'.$kolom.'ArrayKey as $kunci => $val)
                                @if (in_array($val,$'.$kolom.'))
                                    <small class="label bg-yellow">{{ $'.$kolom.'ArrayValue[$kunci] }}</small>
                                @endif
                            @endforeach
                        </td>
                    </tr>'.PHP_EOL;
                }elseif ($request->component[$key] == 'datepicker' && $request->typedata[$key] == 'date') {
                    // for create blade
                    $fieldsTmp = $fieldsTmp.$template1.$label.$template2;
                    $fieldsTmp = $fieldsTmp.'<div class="input-group date col-lg-10 col-md-10 col-sm-10 col-xs-10"><div class="input-group-addon"><i class="ri-calendar-2-fill"></i></div><input type="text" class="form-control pull-left" id="datepicker-'.$kolom.'" name="'.$kolom.'" value="{{ $dateNow }}" ' .$required.'></div>'.PHP_EOL;
                    $fieldsTmp = $fieldsTmp.$template3;

                    // for edit blade
                    $fieldsTmpEdit = $fieldsTmpEdit.$template1.$label.$template2;
                    $fieldsTmpEdit = $fieldsTmpEdit.'<div class="input-group date col-lg-10 col-md-10 col-sm-10 col-xs-10"><div class="input-group-addon"><i class="ri-calendar-2-fill"></i></div><input type="text" class="form-control pull-left" id="datepicker-'.$kolom.'" name="'.$kolom.'" value="{{ $'.$names[3].'->'.$kolom.' }}" ' .$required.'></div>'.PHP_EOL;
                    $fieldsTmpEdit = $fieldsTmpEdit.$template3;

                    // for show blade
                    $fieldsShow = $fieldsShow.'<tr>
                        <td style="width: 200px"><b>'.$label.'</b></td>
                        <td>{{ $'.$names[3].'->'.$kolom.' }}</td>
                    </tr>'.PHP_EOL;
                    // end for show blade

                    // for filter
                    if ($request->onlist[$key] == 1 || $request->onlist == '1') {
                        $fieldsFilter = $fieldsFilter.$templateFilter1.$label.'</label>
                        <div class="col-sm-12"><div class="input-group date col-lg-10 col-md-10 col-sm-10 col-xs-10"><div class="input-group-addon"><i class="ri-calendar-2-fill"></i></div><input type="text" class="form-control pull-left" id="datepicker" name="'.$kolom.'" id="'.$kolom.'"></div>'.PHP_EOL;
                        $fieldsFilter = $fieldsFilter.PHP_EOL.$templateFilter2;

                        // js data filter
                        $dataFilter = $dataFilter."data.".$kolom." = $('#datepicker').val();".PHP_EOL;
                        // end js data filter

                        // query filter for controller
                        if (!is_null($request->selectTable)) {
                            $queryFilter = $queryFilter."if (request()->has('".$kolom."') && !is_null(request('".$kolom."'))) {
                                    \$tgl".$names[1]." = null;".
                                    "$".$kolom." = \$this->formatDate(request('".$kolom."'), \$tgl".$names[1].", 1);".PHP_EOL."
                                    \$query->whereDate('".$names[2].".".$kolom."', '=', \$".$kolom.");
                                }";
                        } else {
                            $queryFilter = $queryFilter."if (request()->has('".$kolom."') && !is_null(request('".$kolom."'))) {
                                    \$tgl".$names[1]." = null;".
                                    "$".$kolom." = \$this->formatDate(request('".$kolom."'), \$tgl".$names[1].", 1);".PHP_EOL."
                                    \$query->whereDate('".$kolom."', '=', \$".$kolom.");
                                }";
                        }
                        // end query filter for controller
                    }
                    // end for filter
                    $jsAddOns = $jsAddOns."$(function () {
                        $('#datepicker-".$kolom."').datepicker({
                            autoclose: true
                        })
                    });".PHP_EOL;

                    $jsAddOnsIndexTrash = $jsAddOnsIndexTrash."$(function () {
                        $('#datepicker-".$kolom."').datepicker({
                            autoclose: true
                        })
                    });".PHP_EOL;
                }elseif ($request->component[$key] == 'datepicker' && $request->typedata[$key] == 'datetime') {
                    // for create blade
                    $fieldsTmp = $fieldsTmp.$template1.$label.$template2;
                    $fieldsTmp = $fieldsTmp.'<div class="input-group date col-lg-10 col-md-10 col-sm-10 col-xs-10"><div class="input-group-addon"><i class="ri-calendar-2-fill"></i></div><input type="text" class="form-control pull-left" id="datetimepicker-'.$kolom.'" name="'.$kolom.'"' .$required.'></div>'.PHP_EOL;
                    $fieldsTmp = $fieldsTmp.$template3;

                    // for edit blade
                    $fieldsTmpEdit = $fieldsTmpEdit.$template1.$label.$template2;
                    $fieldsTmpEdit = $fieldsTmpEdit.'<div class="input-group date col-lg-10 col-md-10 col-sm-10 col-xs-10"><div class="input-group-addon"><i class="ri-calendar-2-fill"></i></div><input type="text" class="form-control pull-left" id="datetimepicker-'.$kolom.'" name="'.$kolom.'" value="{{ $'.$names[3].'->'.$kolom.' }}" ' .$required.'></div>'.PHP_EOL;
                    $fieldsTmpEdit = $fieldsTmpEdit.$template3;

                    // for show blade
                    $fieldsShow = $fieldsShow.'<tr>
                        <td style="width: 200px"><b>'.$label.'</b></td>
                        <td>{{ $'.$names[3].'->'.$kolom.' }}</td>
                    </tr>'.PHP_EOL;
                    // end for show blade

                    // for filter
                    if ($request->onlist[$key] == 1 || $request->onlist == '1') {
                        $fieldsFilter = $fieldsFilter.$templateFilter1.$label.'</label>
                        <div class="col-sm-12"><div class="input-group date col-lg-10 col-md-10 col-sm-10 col-xs-10"><div class="input-group-addon"><i class="ri-calendar-2-fill"></i></div><input type="text" class="form-control pull-left" id="datetimepicker-'.$kolom.'" name="'.$kolom.'"></div>'.PHP_EOL;
                        $fieldsFilter = $fieldsFilter.PHP_EOL.$templateFilter2;

                        // js data filter
                        $dataFilter = $dataFilter."data.".$kolom." = $('#datetimepicker-".$kolom."').val();".PHP_EOL;
                        // end js data filter

                        // query filter for controller
                        if (!is_null($request->selectTable)) {
                            $queryFilter = $queryFilter."if (request()->has('".$kolom."') && !is_null(request('".$kolom."'))) {
                                    \$".$kolom." = \$this->formatDateTime(request('".$kolom."'));".PHP_EOL."
                                    \$query->where('".$names[2].".".$kolom."', '>=', \$".$kolom.".' 00:00:00');
                                    \$query->where('".$names[2].".".$kolom."', '<=', \$".$kolom.".' 23:59:55');
                                }";
                        } else {
                            $queryFilter = $queryFilter."if (request()->has('".$kolom."') && !is_null(request('".$kolom."'))) {
                                    \$".$kolom." = \$this->formatDateTime(request('".$kolom."'));".PHP_EOL."
                                    \$query->where('".$kolom."', '>=', \$".$kolom.".' 00:00:00');
                                    \$query->where('".$kolom."', '<=', \$".$kolom.".' 23:59:55');
                                }";
                        }
                        // end query filter for controller
                    }
                    // end for filter

                    $jsAddOns = $jsAddOns."$(function () {
                        $('#datetimepicker-".$kolom."').datetimepicker({
                             format: 'YYYY-MM-DD HH:mm:ss'
                         });
                   });".PHP_EOL;

                    $jsAddOnsIndexTrash = $jsAddOnsIndexTrash."$(function () {
                        $('#datetimepicker-".$kolom."').datetimepicker({
                             format: 'YYYY-MM-DD HH:mm:ss'
                         });
                   });".PHP_EOL;
                }
            }
        }

        $dataCreate->inputFields = $fieldsTmp;
        $dataCreate->fieldsFilter = $fieldsFilter;
        $dataCreate->dataFilter = $dataFilter;
        $dataCreate->currencyMask = $currencyMask;
        $dataCreate->queryFilter = $queryFilter.'})';
        $dataEdit->inputFields = $fieldsTmpEdit;
        $dataCreate->jsAddOns = $jsAddOns;
        $dataCreate->jsAddOnsIndexTrash = $jsAddOnsIndexTrash;
        $dataShow->fieldsShow = $fieldsShow;
        $dataEdit->jsAddOns = $jsAddOns;
        $dataShow->jsAddOns = $jsAddOnsIndexTrash;

        return [$dataCreate,$dataEdit,$dataShow];
    }

    private function migration($data)
    {
        $template = str_replace(
            [
                '{{ClassName}}',
                '{{table}}',
                '{{migrationData}}'
            ],
            [
                $data->ClassName,
                $data->table,
                $data->migrationData
            ],
            $this->getStub('migration.create')
        );
        file_put_contents(database_path("/migrations/{$data->namaFile}.php"), $template);
        $migrationFile = 'database/migrations/'.$data->namaFile.'.php';
        Artisan::call('migrate', [
            '--path' => $migrationFile
        ]);
    }

    private function getStub($type)
    {
        return file_get_contents(resource_path("stubs/$type.stub"));
    }

    private function model($dataModel)
    {
        $template = str_replace(
            [
                '{{modelName}}',
                '{{tableColumns}}',
                '{{tableName}}',
                '{{relations}}'
            ],
            [
                $dataModel->name,
                $dataModel->dataKolom,
                $dataModel->namaTable,
                $dataModel->relations
            ],
            $this->getStub('Model')
        );

        file_put_contents(app_path("/Models/{$dataModel->name}.php"), $template);
    }

    private function controller($dataController)
    {
        $template = str_replace(
            [
                '{{controllerName}}','{{model}}','{{tables}}','{{table}}','{{query}}','{{queryTrash}}','{{view}}','{{dateNow}}',
                '{{tmpQuery}}','{{imageUpload}}','{{fileUpload}}','{{simpanData}}','{{validation}}','{{queryGetData}}','{{queryGetDataDestroy}}',
                '{{getImage}}','{{getFile}}','{{getJoinData}}','{{checkbox}}','{{thisData}}','{{modelRelation}}','{{validationEdit}}',
                '{{imageUploadEdit}}','{{fileUploadEdit}}','{{simpanDataEdit}}','{{queryShow}}','{{arrayCheckbox}}','{{arraySelect}}',
                '{{arrayRadio}}','{{thisDataShow}}','{{checkboxShow}}','{{convertDate}}','{{queryFilter}}','{{thisDataJoin}}',
                '{{imageDelete}}','{{editColumn}}','{{addColumn}}','{{addColumnRegister}}','{{downloadFile}}','{{deleteRestore}}',
                '{{url}}','{{title}}','{{ckEditorCode}}','{{needFullPage}}', '{{nama}}'
            ],
            [
                $dataController->controllerName,$dataController->model,$dataController->tables,$dataController->table,$dataController->queryActive,
                $dataController->queryTrash,$dataController->view,$dataController->dateNow,$dataController->tmpQuery,$dataController->imageUpload,
                $dataController->fileUpload,$dataController->simpanData,$dataController->validation,$dataController->queryGetData,
                $dataController->queryGetDataDestroy,$dataController->getImage,$dataController->getFile,$dataController->getJoinData,
                $dataController->checkbox,$dataController->thisData,$dataController->modelRelation,$dataController->validationEdit,
                $dataController->imageUploadEdit,$dataController->fileUploadEdit,$dataController->simpanDataEdit,$dataController->queryShow,
                $dataController->arrayCheckbox,$dataController->arraySelect,$dataController->arrayRadio,$dataController->thisDataShow,
                $dataController->checkboxShow,$dataController->convertDate,$dataController->queryFilter,$dataController->thisDataJoin,
                $dataController->imageDelete,$dataController->editColumn,$dataController->addColumn,$dataController->addColumnRegister,
                $dataController->downloadFile,$dataController->deleteRestore,$dataController->url,$dataController->title,$dataController->ckEditorCode,
                $dataController->needFullPage, $dataController->nama

            ],
            $dataController->needFullPage == 1 ?
            $this->getStub('Controller') :
            $this->getStub('Controller-modal')
        );
        if (!File::exists($pathFile = app_path("/Http/Controllers/Master/"))) {
            mkdir($pathFile,0777,true);
        }
        file_put_contents(app_path("/Http/Controllers/Master/{$dataController->controllerName}.php"), $template);

        if ($dataController->hasMedia) {
            Artisan::call('storage:link');
        }
    }

    private function indexBlade($data)
    {
        $template = str_replace(
            [
                '{{tablePlural}}','{{fields}}','{{dataJs}}','{{icon}}','{{fieldsFilter}}','{{jsAddOns}}','{{dataFilter}}',
                '{{url}}','{{title}}','{{currencyMask}}','{{namaTabel}}','{{needFullPage}}','{{fieldsList}}', '{{fieldInput}}',
                '{{fieldInputEdit}}', '{{dateNow}}'
            ],
            [
                $data->tablePlural,$data->fields,$data->dataJs,$data->icon,$data->fieldsFilter,$data->jsAddOns,
                $data->dataFilter,$data->url,$data->title,$data->currencyMask,$data->namaTabel,$data->needFullPage ? '1' : '0',$data->fieldsList,
                $data->fieldInput, $data->fieldInputEdit, $data->dateNow
            ],
            $data->needFullPage == 1 ?
            $this->getStub('index.blade') :
            $this->getStub('index-modal.blade')
        );
        file_put_contents(resource_path("views/backend/pages/{$data->nama}/index.blade.php"), $template);
    }

    private function trashBlade($data)
    {
        $template = str_replace(
            [
                '{{tablePlural}}','{{fields}}','{{dataJs}}','{{icon}}','{{fieldsFilter}}','{{jsAddOns}}','{{dataFilter}}',
                '{{url}}','{{title}}','{{currencyMask}}'
            ],
            [
                $data->tablePlural,$data->fields,$data->dataJs,$data->icon,$data->fieldsFilter,$data->jsAddOns,$data->dataFilter,
                $data->url,$data->title,$data->currencyMask
            ],
            $this->getStub('trash.blade')
        );
        file_put_contents(resource_path("views/backend/pages/{$data->nama}/trash.blade.php"), $template);
    }

    private function createBlade($data)
    {
        $template = str_replace(
            [
                '{{tablePlural}}','{{icon}}','{{inputFields}}','{{jsAddOns}}','{{url}}','{{title}}','{{currencyMask}}'
            ],
            [
                $data->tablePlural,$data->icon,$data->inputFields,$data->jsAddOns,$data->url,$data->title,$data->currencyMask
            ],
            $this->getStub('create.blade')
        );

        file_put_contents(resource_path("views/backend/pages/{$data->nama}/create.blade.php"), $template);
    }

    private function editBlade($data)
    {
        $template = str_replace(
            [
                '{{tablePlural}}','{{tableSingular}}','{{icon}}','{{inputFields}}','{{jsAddOns}}','{{url}}','{{title}}','{{currencyMask}}'
            ],
            [
                $data->tablePlural,$data->tableSingular,$data->icon,$data->inputFields,$data->jsAddOns,$data->url,$data->title,$data->currencyMask
            ],
            $this->getStub('edit.blade')
        );

        file_put_contents(resource_path("views/backend/pages/{$data->nama}/edit.blade.php"), $template);
    }

    private function showBlade($data)
    {
        $template = str_replace(
            [
                '{{icon}}','{{fieldsShow}}','{{jsAddOns}}','{{title}}'
            ],
            [
                $data->icon,$data->fieldsShow,$data->jsAddOns,$data->title
            ],
            $this->getStub('show.blade')
        );

        file_put_contents(resource_path("views/backend/pages/{$data->nama}/show.blade.php"), $template);
    }

    private function createModule($names, $request)
    {
        $data = new Module;
        $data->name = $names[6];
        $data->url = $names[4];
        if (is_null($request->icon) || $request->icon == '') {
            $request->icon = 'ri-checkbox-blank-circle-fill';
        }
        $data->icon = $request->icon;

        $rule['a'] = "1";
        $rule['c'] = "1";
        $rule['r'] = "1";
        $rule['u'] = "1";
        $rule['d'] = "1";
        $rule['t'] = "1";

        $data->support = json_encode($rule);
        $data->created_by = Auth::user()->email;
        $data->is_generated = 1;
        $data->save();
        $data->id;

        $arrPrivilege = array(
            'access' => 1,'create' => 1,'read' => 1,'update' => 1,'delete' => 1,'trash' => 1
        );

        $rolePrivilege = new RolePrivilege;
        $rolePrivilege->roles_id = '018da67e-0dc7-70ce-b1a9-d888ed780384';
        $rolePrivilege->modules_id = $data->id;
        $rolePrivilege->privileges = json_encode($arrPrivilege);
        $rolePrivilege->remark = 'super';
        $rolePrivilege->save();

        $rolePrivilege = new RolePrivilege;
        $rolePrivilege->roles_id = '018da67e-0dc7-742b-886a-270afb6fe3b7';
        $rolePrivilege->modules_id = $data->id;
        $rolePrivilege->privileges = json_encode($arrPrivilege);
        $rolePrivilege->remark = 'admin';
        $rolePrivilege->save();

        return true;
    }

    private function export($modelName)
    {
        $template = str_replace(
            [
                '{{namaFile}}'
            ],
            [
                $modelName.'Export'
            ],
            $this->getStub('Export')
        );
        if (!File::exists($pathFile = app_path("/Exports/"))) {
            mkdir($pathFile,0777,true);
        }
        file_put_contents(app_path("/Exports/{$modelName}Export.php"), $template);
    }

    private function splitWords($data){
        $modelPlural = '';
        $modelSingular = '';
        $tablePlural = '';
        $tableSingular = '';
        $url = '';
        $pesan = '';
        $title = '';
        for ($i=0; $i <count($data) ; $i++) {
            $modelPlural = $modelPlural.ucwords(STR::singular($data[$i]));
            $modelSingular = $modelSingular.ucwords(STR::singular($data[$i]));
            $tablePlural = $tablePlural.strtolower(STR::singular($data[$i])).'_';
            $tableSingular = $tableSingular.strtolower(STR::singular($data[$i])).'_';
            $url = $url.strtolower(STR::singular($data[$i])).'-';
            $pesan = $pesan.strtolower(STR::singular($data[$i])).' ';
            $title = $title.ucwords(STR::singular($data[$i])).' ';
            if ($i = (count($data) - 1)) {
                $modelPlural = $modelPlural.ucwords(STR::plural($data[$i]));
                $modelSingular = $modelSingular.ucwords(STR::singular($data[$i]));
                $tablePlural = $tablePlural.strtolower(STR::plural($data[$i])).'_';
                $tableSingular = $tableSingular.strtolower(STR::singular($data[$i])).'_';
                $url = $url.strtolower(STR::singular($data[$i])).'-';
                $pesan = $pesan.strtolower(STR::singular($data[$i])).' ';
                $title = $title.ucwords(STR::singular($data[$i])).' ';
            }
        }
        $tablePlural = rtrim($tablePlural, '_');
        $tableSingular = rtrim($tableSingular, '_');
        $url = rtrim($url, '-');
        $pesan = rtrim($pesan, ' ');
        $title = rtrim($title, ' ');

        return [$modelPlural,$modelSingular,$tablePlural,$tableSingular,$url,$pesan,$title];
    }
}
