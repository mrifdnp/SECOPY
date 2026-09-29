<?php

namespace App\Http\Controllers\Generator;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use DB;
use App\Helpers\IndonesiaitLib;
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
use Yajra\DataTables\DataTables;
// use Illuminate\Support\Facades\Schema;

class LogGeneratorController extends BackendController
{
    function __construct()
    {
        parent::__construct();        
        $this->data['page_title'] = "Generator Log Page";
    }    

    public function index()
    {                
        return view('backend.pages.generator.log_index',$this->data);
    }

    public function get_datatable(Request $request)
    {       
       $data = LogGenerator::select([                                                
                                'id',
                                'module',                                
                                'created_at',
                                'updated_at'
                            ])->orderBy('created_at');
        $datatables = DataTables::of($data);
        
        return $datatables
            ->addColumn('checkboxs', function(LogGenerator $data) {
                $checkbox = " <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>";
                if($data->no_delete=="1"){
                    $checkbox = "<input class='checkbox_item' id='dtcheckbox[]' type='checkbox' name='dtcheckbox[]' disabled>";
                }

                return $checkbox;
            })
            ->addColumn('number', function(LogGenerator $data) {
                return "";
            })
            ->addColumn('action', function(LogGenerator $data) {
                $disabled = '';
                $btnEdit = '';
                $btnDelete = '';
                $btnEdit = "<a href='".route('generator-log.edit', $data->id)."' class='btn btn-sm btn-soft-secondary'><i class='las la-edit '></i></a>";
                $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='".$data->id."'><i class='las la-trash '></i></a>";                
                return 
                    $btnEdit.' '.$btnDelete;                
            })    
            ->editColumn('created_at', function ($data){
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })                     
            ->filterColumn('deleted_at', function($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('module')) {
                    $query->where('module', 'like', "%" . request('module') . "%");
                }            
            })
            ->rawColumns(['action', 'checkboxs','number'])
            ->escapeColumns([])
            ->toJson();
    }

    public function edit($id)
    {                    
        $module = LogGenerator::where('id',$id)->first();
        if (!$module) {
            return back()->with('error_message', 'Data not found!');
        }        
        $module->icon = json_decode($module->log_data)->icon;
        $this->data['module'] = $module;        

        return view('backend.pages.generator.log_edit', $this->data);
    }

    public function get_module(Request $request)
    {        
        $dataTables = $this->getTables();                
        $tables = DB::select('SELECT table_name FROM information_schema.tables WHERE table_schema = :schema', ['schema' => 'public']);
        $columnsException = [
                                'created_by', 'updated_by', 'restored_by', 'restored_at', 'deleted_by', 
                                'deleted_at', 'created_at', 'updated_at'];        
        $columnsName = [];
        $columnsType = [];
        $columnsValidation = [];
        $columnsKey = [];
        $data = new \StdClass();
        $data->migrated = null;        
        if (!$request->table) {
            $msg = 'Table not selected';
            $iconComponent = null;
            $dataComponen = null;
            $no = null;
            $num = null;
            $tables = null;
            $dataRender = [$iconComponent,$dataComponen,$no,$num,$tables, $msg];

            return json_encode(array('data' => $dataRender));
        }        
        $tabel = 'Tables_in_'.env('DB_DATABASE');                
        for ($i=0; $i < count($tables); $i++) { 
            if ($request->table == $tables[$i]->table_name) {
                $columnsRemanen = DB::select("
                    SELECT column_name, data_type, is_nullable, udt_name, ordinal_position
                    FROM information_schema.columns                 
                    WHERE table_name = ?
                    ORDER BY ordinal_position ASC",
                    [$request->table]
                );                
                for ($j=0; $j < count($columnsRemanen); $j++) { 
                    if (!in_array($columnsRemanen[$j]->column_name, $columnsException)) {
                        array_push($columnsName,$columnsRemanen[$j]->column_name);
                        array_push($columnsType,$columnsRemanen[$j]->udt_name);
                        array_push($columnsValidation,$columnsRemanen[$j]->is_nullable);
                        array_push($columnsKey,$columnsRemanen[$j]->ordinal_position);
                    }
                }
                $data->migrated = 1;
                $data->name = $columnsName;
                $data->type = $columnsType;
                $data->validation = $columnsValidation;
                $data->key = $columnsKey;
            }
            if ($data->migrated == 1) {
                $i = count($tables);
            }
        }
        
        $data->fileName = null;
        if ($data->migrated != 1) {
            $filesInMigration = \File::files(database_path("/migrations/"));
            foreach($filesInMigration as $file) {
                if (Str::contains(pathinfo($file)['filename'], $request->table)) {
                    $data->migrated = 0;            
                    $data->name = null;            
                    $data->fileName = pathinfo($file)['filename'];            
                }
            }
        }

        // check icon module
        $icon = '';
        if ($data->migrated) {
            if (str_word_count($request->table) > 1) {
                if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $request->table)){
                    $split = preg_split('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $request->table);
                }else {
                    $split = explode(" ", $request->table);
                }
                $moduleName = splitSingleWord($split);
            }else{
                $cekWords = preg_split('/(?=[A-Z])/',strtolower($request->table));
                if (count($cekWords) > 1) {
                    $moduleName = splitSingleWord($cekWords);
                }else {
                    $split = preg_split('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $request->table);
                    if (count($split) > 1) {
                        $moduleName = splitSingleWord($split);
                    }else{
                        $moduleName = ucwords(STR::singular($request->table));
                    }
                }
            }
            $cekModule = Module::where('name',$moduleName)->first();
            if ($cekModule) {
                $icon = $cekModule->icon;
            }
        }
        // end check icon module

        $iconComponent = '<div class="form-group">
                            <label for="" class="col-sm-2 control-label">Icon*</label>
                            <div class="col-sm-10">
                                <select class="form-control" style="width:100%" name="icon" id="choices-single-default"  placeholder="Icon" >
                                    <option value="'.$icon.'" selected>'.$icon.'</option>
                                </select>
                            </div>
                        </div>';
        $dataComponen = '<tr>
        <td class="col-sm-2"><b> Field Column* </b></td>
        <td class="col-sm-1">Label</td>
        <td class="col-sm-2">Component</td>
        <td class="col-sm-2">Type Data</td>
        <td class="col-sm-2">Option</td>
        <td class="col-sm-1">Nullable </td>
        <td class="col-sm-1">On List</td>
        <td class="col-sm-1">ActionBtn</td>
</tr>
<tr id="row_1">										
        <td class="col-sm-2">
        <input type="hidden" name="row_id[]" id="id_row_1" value="1">                
        <input value="id" type="text" class="form-control" name="field[]" placeholder="Primary Field" readonly></td>                
        <td class="col-sm-2"><input value="ID" type="text" class="form-control" name="label[]" placeholder="Label Name"> </td>
        <td class="col-sm-1 component" id="select_row_1">            
            <select name="component[]" class="form-control select2 component_id" data-id="1" id="component1" style="width: 144px;" required>
                <option value="text" selected="">Text Field</option>
                <option value="textCurrency">Text Field Currency</option>
                <option value="select">Select</option>
                <option value="password">Password</option>
                <option value="email">Email</option>
                <option value="radio">Radio</option>
                <option value="checkbox">Checkbox</option>
                <option value="selectjoin">Select Join</option>
                <option value="textarea" >Text Area</option>
                <option value="texteditor" >Text Editor</option>
                <option value="file" >File</option>
                <option value="image" >Image</option>
                <option value="datepicker">Datepicker</option>
            </select>
            <div>
                <div class="col-sm-8" style="padding-left:0">
                    <input id="row_1_1" value="" type="text" class="form-control display-none select-input" name="select_row_1[]" placeholder="Key, Value">
                </div>
                <div class="col-sm-4">
                    <span style="cursor:pointer" onclick="addSelect(\'row_1\')" class="select-input display-none ri-add-box-fill"></span>
                </div>
            </div>            
        </td>
        <td class="col-sm-1">
            <select name="typedata[]" class="form-control select2" id="type1" style="width:144px;" required>
                <option value="uuid" selected>uuid</option>
                <option value="integer">integer</option>
                <option value="string">string</option>
                <option value="text">text</option>
                <option value="boolean">boolean</option>
                <option value="date">date</option>
                <option value="datetime">datetime</option>
                <option value="decimal" >decimal</option>
                <option value="double" >double</option>
                <option value="enum" >enum</option>
                <option value="float" >float</option>
            </select>										
        </td>
        <td class="col-sm-2 join">
            <select data-baris="1" id="joinTable" name="join_table[]" class="form-control display-none join-table-input">
                <option value="0">Pilih tabel</option>
                @for ($i = 0; $i < count($tables); $i++)
                    <option value="{{ $tables[$i] }}">{{ $tables[$i] }}</option>
                @endfor
            </select>
            <input value="" id="joinId-1" type="text" class="form-control display-none join-id-input" name="join_id[]" placeholder="Join ID">
            <select id="selectTable-1" name="selectTable[]" class="form-control display-none join-table-input"></select>
            <!-- File -->
            <input value="" type="text" class="form-control display-none f_file" name="file_type[]" placeholder="type name,name">
            <input value="" type="text" class="form-control display-none f_file" name="file_size[]" placeholder="File Size (Kb)">
            <input value="" type="text" class="form-control display-none f_img" name="file_thumb[]" placeholder="Thumb H,W(Px)">
        </td>
        <td class="col-sm-1">
            <select class="form-control select2" name="validation[]" id="validation">
                <option value="1">Yes</option>
                <option value="0" selected>No</option>
            </select>                                        
        </td>        
        <td class="col-sm-1">
            <select name="onlist[]" class="form-control select2" style="width: 63px;">
                <option value="1">YES</option>
                <option value="0" selected>NO</option>
            </select>
        </td>                
        <td class="col-sm-1"> </td> 
</tr>';

        $opt = '';
        if ($data->migrated == 1) {
            for ($i=1; $i < count($data->name); $i++) {
                $opt = '';
                // create opsi for validation column
                $opsi = '<option value="1">Yes</option>
                            <option value="0" selected>No</option>';
                if ($data->validation[$i] == 'YES') {
                    $opsi = '<option value="1" selected>Yes</option>
                            <option value="0">No</option>';
                } 
                // end create opsi for validation column
                
                // create component for type of input ui
                $component = null;
                $optionComponent = null;
                $optionDataType = null;
                for ($a=0; $a <count($dataTables); $a++) { 
                    if ($request->table != $dataTables[$a]) {
                        $opt = $opt.'<option value="'.$dataTables[$a].'">'.$dataTables[$a].'</option>';
                    }
                }
                $join = '<select data-baris="'.($i+1).'" id="joinTable" name="join_table[]" class="form-control display-none join-table-input">
                <option value="0">Pilih tabel</option>
                '.$opt.'
            </select>
            <input value="" id="joinId-'.($i+1).'" type="text" class="form-control display-none join-id-input" name="join_id[]" placeholder="Join ID">
            <select id="selectTable-'.($i+1).'" name="selectTable[]" class="form-control display-none join-table-input">
            </select>';
                if ($data->type[$i] == 'uuid' && $data->key[$i] != 1) {
                    // get tables
                    $component = '<option value="text">Text Field</option>
                    <option value="textCurrency">Text Field Currency</option>
                    <option value="select">Select</option>
                    <option value="password">Password</option>
                    <option value="email">Email</option>
                    <option value="radio">Radio</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="selectjoin" selected>Select Join</option>
                    <option value="textarea" >Text Area</option>
                    <option value="texteditor" >Text Editor</option>
                    <option value="file" >File</option>
                    <option value="image" >Image</option>
                    <option value="datepicker">Datepicker</option>';
                    $optionComponent = '<input id="row_'.($i+1).'_1" value="" type="text" class="form-control display-none select-input" name="select_row_'.($i+1).'[]" placeholder="Key, Value">
                    <span style="cursor:pointer" onclick="addSelect(\'row_'.($i+1).'\')" class="select-input display-none ri-add-box-fill"></span>';
                    $optionDataType = '<option value="uuid" selected>uuid</option>
                    <option value="integer">integer</option>
                    <option value="string">string</option>
                    <option value="text">text</option>
                    <option value="boolean">boolean</option>
                    <option value="date">date</option>
                    <option value="datetime">datetime</option>
                    <option value="decimal" >decimal</option>
                    <option value="double" >double</option>
                    <option value="enum" >enum</option>
                    <option value="float" >float</option>';
                    $join = '<select data-baris="'.($i+1).'" id="joinTable" name="join_table[]" class="form-control display-none join-table-input" style="display: inline-block;">
                    <option value="0">Pilih tabel</option>
                    '.$opt.'
                </select>
                <input value="'.$data->name[$i].'" id="joinId-'.($i+1).'" type="text" class="form-control display-none join-id-input" name="join_id[]" placeholder="Join ID" disabled="disabled" style="display: inline-block;">
                                        <select id="selectTable-'.($i+1).'" name="selectTable[]" class="form-control display-none join-table-input" style="display: inline-block;">
                                        </select>';
                }elseif ($data->type[$i] == 'varchar' && $data->name[$i] == 'email') {
                    $component = '<option value="text">Text Field</option>
                    <option value="textCurrency">Text Field Currency</option>
                    <option value="select">Select</option>
                    <option value="password">Password</option>
                    <option value="email" selected>Email</option>
                    <option value="radio">Radio</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="selectjoin">Select Join</option>
                    <option value="textarea" >Text Area</option>
                    <option value="texteditor" >Text Editor</option>
                    <option value="file" >File</option>
                    <option value="image" >Image</option>
                    <option value="datepicker">Datepicker</option>';
                    $optionComponent = '<input id="row_'.($i+1).'_1" value="" type="text" class="form-control display-none select-input" name="select_row_'.($i+1).'[]" placeholder="Key, Value">
                    <span style="cursor:pointer" onclick="addSelect(\'row_'.($i+1).'\')" class="select-input display-none ri-add-box-fill"></span>';
                    $optionDataType = '<option value="uuid">uuid</option>
                    <option value="integer">integer</option>
                    <option value="string" selected>string</option>
                    <option value="text">text</option>
                    <option value="boolean">boolean</option>
                    <option value="date">date</option>
                    <option value="datetime">datetime</option>
                    <option value="decimal" >decimal</option>
                    <option value="double" >double</option>
                    <option value="enum" >enum</option>
                    <option value="float" >float</option>';
                }elseif ($data->type[$i] == 'varchar' && $data->name[$i] != 'email') {
                    $component = '<option value="text" selected>Text Field</option>
                    <option value="textCurrency">Text Field Currency</option>
                    <option value="select">Select</option>
                    <option value="password">Password</option>
                    <option value="email">Email</option>
                    <option value="radio">Radio</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="selectjoin">Select Join</option>
                    <option value="textarea" >Text Area</option>
                    <option value="texteditor" >Text Editor</option>
                    <option value="file" >File</option>
                    <option value="image" >Image</option>
                    <option value="datepicker">Datepicker</option>';
                    $optionComponent = '<input id="row_'.($i+1).'_1" value="" type="text" class="form-control display-none select-input" name="select_row_'.($i+1).'[]" placeholder="Key, Value">
                    <span style="cursor:pointer" onclick="addSelect(\'row_'.($i+1).'\')" class="select-input display-none ri-add-box-fill"></span>';
                    $optionDataType = '<option value="uuid">uuid</option>
                    <option value="integer">integer</option>
                    <option value="string" selected>string</option>
                    <option value="text">text</option>
                    <option value="boolean">boolean</option>
                    <option value="date">date</option>
                    <option value="datetime">datetime</option>
                    <option value="decimal" >decimal</option>
                    <option value="double" >double</option>
                    <option value="enum" >enum</option>
                    <option value="float" >float</option>';
                }elseif ($data->type[$i] == 'double(8,2)' || $data->type[$i] == 'float8' || $data->type[$i] == 'float(8,2)') {
                    $component = '<option value="text">Text Field</option>
                    <option value="textCurrency" selected>Text Field Currency</option>
                    <option value="select">Select</option>
                    <option value="password">Password</option>
                    <option value="email">Email</option>
                    <option value="radio">Radio</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="selectjoin">Select Join</option>
                    <option value="textarea" >Text Area</option>
                    <option value="texteditor" >Text Editor</option>
                    <option value="file" >File</option>
                    <option value="image" >Image</option>
                    <option value="datepicker">Datepicker</option>';
                    $optionComponent = '<input id="row_'.($i+1).'_1" value="" type="text" class="form-control display-none select-input" name="select_row_'.($i+1).'[]" placeholder="Key, Value">
                    <span style="cursor:pointer" onclick="addSelect(\'row_'.($i+1).'\')" class="select-input display-none ri-add-box-fill"></span>';
                    $optionDataType = '<option value="uuid">uuid</option>
                    <option value="integer">integer</option>
                    <option value="string">string</option>
                    <option value="text">text</option>
                    <option value="boolean">boolean</option>
                    <option value="date">date</option>
                    <option value="datetime">datetime</option>
                    <option value="decimal" >decimal</option>
                    <option value="double" selected>double</option>
                    <option value="enum" >enum</option>
                    <option value="float" >float</option>';
                }elseif ($data->type[$i] == 'integer') {
                    if ($data->key[$i] == 1) {
                        $component = '<option value="text" selected>Text Field</option>
                        <option value="textCurrency">Text Field Currency</option>
                        <option value="select">Select</option>
                        <option value="password">Password</option>
                        <option value="email">Email</option>
                        <option value="radio">Radio</option>
                        <option value="checkbox">Checkbox</option>
                        <option value="selectjoin">Select Join</option>
                        <option value="textarea" >Text Area</option>
                        <option value="texteditor" >Text Editor</option>
                        <option value="file" >File</option>
                        <option value="image" >Image</option>
                        <option value="datepicker">Datepicker</option>';
                        $optionComponent = '<input id="row_'.($i+1).'_1" value="" type="text" class="form-control display-none select-input" name="select_row_'.($i+1).'[]" placeholder="Key, Value">
                        <span style="cursor:pointer" onclick="addSelect(\'row_'.($i+1).'\')" class="select-input display-none ri-add-box-fill"></span>';
                        $optionDataType = '<option value="uuid">uuid</option>
                        <option value="integer" selected>integer</option>
                        <option value="string">string</option>
                        <option value="text">text</option>
                        <option value="boolean">boolean</option>
                        <option value="date">date</option>
                        <option value="datetime">datetime</option>
                        <option value="decimal" >decimal</option>
                        <option value="double">double</option>
                        <option value="enum" >enum</option>
                        <option value="float" >float</option>';
                    }else {
                        $component = '<option value="text">Text Field</option>
                        <option value="textCurrency" selected>Text Field Currency</option>
                        <option value="select">Select</option>
                        <option value="password">Password</option>
                        <option value="email">Email</option>
                        <option value="radio">Radio</option>
                        <option value="checkbox">Checkbox</option>
                        <option value="selectjoin">Select Join</option>
                        <option value="textarea" >Text Area</option>
                        <option value="texteditor" >Text Editor</option>
                        <option value="file" >File</option>
                        <option value="image" >Image</option>
                        <option value="datepicker">Datepicker</option>';
                        $optionComponent = '<input id="row_'.($i+1).'_1" value="" type="text" class="form-control display-none select-input" name="select_row_'.($i+1).'[]" placeholder="Key, Value">
                        <span style="cursor:pointer" onclick="addSelect(\'row_'.($i+1).'\')" class="select-input display-none ri-add-box-fill"></span>';
                        $optionDataType = '<option value="uuid">uuid</option>
                        <option value="integer" selected>integer</option>
                        <option value="string">string</option>
                        <option value="text">text</option>
                        <option value="boolean">boolean</option>
                        <option value="date">date</option>
                        <option value="datetime">datetime</option>
                        <option value="decimal" >decimal</option>
                        <option value="double">double</option>
                        <option value="enum" >enum</option>
                        <option value="float" >float</option>';
                    }
                }elseif ($data->type[$i] == 'bool') {                    
                    $component = '<option value="text">Text Field</option>
                    <option value="textCurrency">Text Field Currency</option>
                    <option value="select">Select</option>
                    <option value="password">Password</option>
                    <option value="email">Email</option>
                    <option value="radio" selected>Radio</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="selectjoin">Select Join</option>
                    <option value="textarea" >Text Area</option>
                    <option value="texteditor" >Text Editor</option>
                    <option value="file" >File</option>
                    <option value="image" >Image</option>
                    <option value="datepicker">Datepicker</option>';
                    for ($x=0; $x < 2; $x++) { 
                        $optionComponent = $optionComponent.'<input id="row_'.($i+1).'_1" value="'.$x.','.$x.'" type="text" class="form-control display-none select-input" name="select_row_'.($i+1).'[]" placeholder="Key, Value" style="display: inline-block;">
                    <span style="cursor: pointer; display: inline;" onclick="addSelect(\'row_'.($i+1).'\')" class="select-input display-none ri-add-box-fill"></span>';
                    }
                    $optionDataType = '<option value="uuid">uuid</option>
                    <option value="integer">integer</option>
                    <option value="string">string</option>
                    <option value="text">text</option>
                    <option value="boolean" selected>boolean</option>
                    <option value="date">date</option>
                    <option value="datetime">datetime</option>
                    <option value="decimal" >decimal</option>
                    <option value="double">double</option>
                    <option value="enum" >enum</option>
                    <option value="float" >float</option>';
                }elseif ($data->type[$i] == 'text') {
                    $component = '<option value="text">Text Field</option>
                    <option value="textCurrency">Text Field Currency</option>
                    <option value="select">Select</option>
                    <option value="password">Password</option>
                    <option value="email">Email</option>
                    <option value="radio">Radio</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="selectjoin">Select Join</option>
                    <option value="textarea">Text Area</option>
                    <option value="texteditor" selected>Text Editor</option>
                    <option value="file" >File</option>
                    <option value="image" >Image</option>
                    <option value="datepicker">Datepicker</option>';
                    $optionComponent = '<input id="row_'.($i+1).'_1" value="" type="text" class="form-control display-none select-input" name="select_row_'.($i+1).'[]" placeholder="Key, Value">
                    <span style="cursor:pointer" onclick="addSelect(\'row_'.($i+1).'\')" class="select-input display-none ri-add-box-fill"></span>';
                    $optionDataType = '<option value="uuid">uuid</option>
                    <option value="integer">integer</option>
                    <option value="string">string</option>
                    <option value="text" selected>text</option>
                    <option value="boolean">boolean</option>
                    <option value="date">date</option>
                    <option value="datetime">datetime</option>
                    <option value="decimal" >decimal</option>
                    <option value="double">double</option>
                    <option value="enum" >enum</option>
                    <option value="float" >float</option>';
                }elseif ($data->type[$i] == 'date') {
                    $component = '<option value="text">Text Field</option>
                    <option value="textCurrency">Text Field Currency</option>
                    <option value="select">Select</option>
                    <option value="password">Password</option>
                    <option value="email">Email</option>
                    <option value="radio">Radio</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="selectjoin">Select Join</option>
                    <option value="textarea">Text Area</option>
                    <option value="texteditor" >Text Editor</option>
                    <option value="file" >File</option>
                    <option value="image" >Image</option>
                    <option value="datepicker" selected>Datepicker</option>';
                    $optionComponent = '<input id="row_'.($i+1).'_1" value="" type="text" class="form-control display-none select-input" name="select_row_'.($i+1).'[]" placeholder="Key, Value">
                    <span style="cursor:pointer" onclick="addSelect(\'row_'.($i+1).'\')" class="select-input display-none ri-add-box-fill"></span>';
                    $optionDataType = '<option value="uuid">uuid</option>
                    <option value="integer">integer</option>
                    <option value="string">string</option>
                    <option value="text">text</option>
                    <option value="boolean">boolean</option>
                    <option value="date" selected>date</option>
                    <option value="datetime">datetime</option>
                    <option value="decimal" >decimal</option>
                    <option value="double">double</option>
                    <option value="enum" >enum</option>
                    <option value="float" >float</option>';
                }elseif ($data->type[$i] == 'timestamp') {
                    $component = '<option value="text">Text Field</option>
                    <option value="textCurrency">Text Field Currency</option>
                    <option value="select">Select</option>
                    <option value="password">Password</option>
                    <option value="email">Email</option>
                    <option value="radio">Radio</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="selectjoin">Select Join</option>
                    <option value="textarea">Text Area</option>
                    <option value="texteditor" >Text Editor</option>
                    <option value="file" >File</option>
                    <option value="image" >Image</option>
                    <option value="datepicker" selected>Datepicker</option>';
                    $optionComponent = '<input id="row_'.($i+1).'_1" value="" type="text" class="form-control display-none select-input" name="select_row_'.($i+1).'[]" placeholder="Key, Value">
                    <span style="cursor:pointer" onclick="addSelect(\'row_'.($i+1).'\')" class="select-input display-none ri-add-box-fill"></span>';
                    $optionDataType = '<option value="uuid">uuid</option>
                    <option value="integer">integer</option>
                    <option value="string">string</option>
                    <option value="text">text</option>
                    <option value="boolean">boolean</option>
                    <option value="date">date</option>
                    <option value="datetime" selected>datetime</option>
                    <option value="decimal" >decimal</option>
                    <option value="double">double</option>
                    <option value="enum" >enum</option>
                    <option value="float" >float</option>';
                }elseif (strpos($data->type[$i], 'enum') !== false) {
                    $split = preg_split('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', explode('enum',$data->type[$i])[1]);
                    $a = 0;
                    for ($j=0; $j < count($split); $j++) { 
                        if ($split[$j] != "") {
                            $optionComponent = $optionComponent.'<input id="row_'.($i+1).'_1" value="'.$split[$j].','.$split[$j].'" type="text" class="form-control display-none select-input" name="select_row_'.($i+1).'[]" placeholder="Key, Value" style="display: inline-block;">
                        <span style="cursor: pointer; display: inline;" onclick="addSelect(\'row_'.($i+1).'\')" class="select-input display-none ri-add-box-fill"></span>';
                        }
                    }
                    $component = '<option value="text">Text Field</option>
                    <option value="textCurrency">Text Field Currency</option>
                    <option value="select" selected>Select</option>
                    <option value="password">Password</option>
                    <option value="email">Email</option>
                    <option value="radio">Radio</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="selectjoin">Select Join</option>
                    <option value="textarea">Text Area</option>
                    <option value="texteditor" >Text Editor</option>
                    <option value="file" >File</option>
                    <option value="image" >Image</option>
                    <option value="datepicker">Datepicker</option>';
                    $optionDataType = '<option value="uuid">uuid</option>
                    <option value="integer">integer</option>
                    <option value="string">string</option>
                    <option value="text">text</option>
                    <option value="boolean">boolean</option>
                    <option value="date">date</option>
                    <option value="datetime">datetime</option>
                    <option value="decimal" >decimal</option>
                    <option value="double">double</option>
                    <option value="enum" selected>enum</option>
                    <option value="float" >float</option>';
                }
                $dataComponen = $dataComponen.'<tr id="row_'.($i+1).'">									
                <td class="col-sm-2"><input type="hidden" name="row_id[]" id="id_row_'.($i+1).'" value="'.($i+1).'"><input value="'.$data->name[$i].'" type="text" class="form-control" name="field[]" placeholder="Field Name"></td>
                <td class="col-sm-2"><input value="" type="text" class="form-control" name="label[]" placeholder="Label Name"> </td>
                <td class="col-sm-1 component" id="select_row_'.($i+1).'">
                    <select name="component[]" class="form-control select2 component_id" data-id="'.($i+1).'" id="component'.($i+1).'">
                        '.$component.'
                    </select>
                    '.$optionComponent.'
                </td>
                <td class="col-sm-1">
                    <select name="typedata[]" class="form-control select2 type_id" id="type'.($i+1).'">
                        '.$optionDataType.'
                    </select>
                        <input id="type_row_'.($i+1).'1" value="" type="text" class="form-control display-none custom_type" name="custom[1]" placeholder="type(value)">
                </td>
                <td class="col-sm-2 join">
                    '.$join.'
                    <!-- File -->
                    <input value="" type="text" class="form-control display-none f_file" name="file_type[]" placeholder="type name,name">
                    <input value="" type="text" class="form-control display-none f_file" name="file_size[]" placeholder="File Size (Kb)">
                    <input value="" type="text" class="form-control display-none f_img" name="file_thumb[]" placeholder="Thumb H,W(Px)">
                </td>
                <td class="col-sm-1">
                    <select class="form-control" name="validation[]" id="validation">
                        '.$opsi.'
                    </select>
                </td>
                <td class="col-sm-1">
                    <select name="onlist[]" class="form-control select2" >
                        <option value="1">YES</option>
                        <option value="0">NO</option>
                    </select>
                </td>
                <td>
                    <button type="button" class="btn-del btn-sm btn btn-danger btn-flat">                                            
                        <i class="ri-close-circle-fill"></i>
                    </button>                    
                    <button onclick="add_after('.($i+1).');" type="button" class="btn btn-sm btn-primary btn-flat">
                        <i class="ri-add-circle-fill"></i>
                    </button>
                </td>
        </tr>';
            }
            $no = count($data->name) + 1;
            $num = count($data->name) + 1;
        }else {
            for ($a=0; $a <count($dataTables); $a++) { 
                if ($request->table != $dataTables[$a]) {
                    $opt = $opt.'<option value="'.$dataTables[$a].'">'.$dataTables[$a].'</option>';
                }
            }
            $dataComponen = $dataComponen.'<input type="hidden" name="filename" id="filename" value="'.$data->fileName.'">
            <tr id="row_2">										
                        <td class="col-sm-2">
                        <input type="hidden" name="row_id[]" id="id_row_2" value="2">
                        <input value="" type="text" class="form-control" name="field[]" placeholder="Field Name"></td>
                        <td class="col-sm-2"><input value="" type="text" class="form-control" name="label[]" placeholder="Label Name"> </td>
                        <td class="col-sm-1 component" id="select_row_2">
                            <select name="component[]" class="form-control select2 component_id" data-id="'.($i+1).'" id="component'.($i+1).'" required>
                                <option value="text">Text Field</option>
                                <option value="textCurrency">Text Field Currency</option>
                                <option value="select">Select</option>
                                <option value="password">Password</option>
                                <option value="email">Email</option>
                                <option value="radio">Radio</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="selectjoin">Select Join</option>
                                <option value="textarea" >Text Area</option>
                                <option value="file" >File</option>
                                <option value="image" >Image</option>
                                <option value="datepicker">Datepicker</option>
                            </select>
                            <div>
                                <div class="col-sm-8" style="padding-left:0">
                                <input id="row_2_1" value="" type="text" class="form-control display-none select-input" name="select_row_2[]" placeholder="Key, Value">
                                </div>
                                <div class="col-sm-4">
                                <span style="cursor:pointer" onclick="addSelect(\'row_2\')" class="select-input display-none fa fa-plus"></span>
                                </div>
                            </div>
                        </td>
                        <td class="col-sm-1">
                            <select name="typedata[]" class="form-control select2" id="type'.($i+1).'" required>
                                <option value="uuid" selected="">uuid</option>
                                <option value="integer">integer</option>
                                <option value="string">string</option>
                                <option value="text">text</option>
                                <option value="boolean">boolean</option>
                                <option value="date" selected>date</option>
                                <option value="decimal" >decimal</option>
                                <option value="double" >double</option>
                                <option value="enum" >enum</option>
                                <option value="float" >float</option>
                            </select>										
                        </td>
                        <td class="col-sm-2 join">
                            <select data-baris="2" id="joinTable" name="join_table[]" class="form-control display-none join-table-input">
                                <option value="0">Pilih tabel</option>
                                '.$opt.'
                            </select>
                            <input value="" id="joinId-2" type="text" class="form-control display-none join-id-input" name="join_id[]" placeholder="Join ID">
                            <select id="selectTable-2" name="selectTable[]" class="form-control display-none join-table-input">

                            </select>
                            <!-- File -->
                            <input value="" type="text" class="form-control display-none f_file" name="file_type[]" placeholder="type name,name">
                            <input value="" type="text" class="form-control display-none f_file" name="file_size[]" placeholder="File Size (Kb)">
                            <input value="" type="text" class="form-control display-none f_img" name="file_thumb[]" placeholder="Thumb H,W(Px)">
                        </td>
                        <td class="col-sm-1">
                            <select class="form-control" name="validation[]" id="validation">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </td>
                        <td class="col-sm-1">
                            <select name="onlist[]" class="form-control select2" >
                                <option value="1" selected>YES</option>
                                <option value="0">NO</option>
                            </select>
                        </td>
                        <td>
                            <button type="button" class="btn-del btn-sm btn btn-danger btn-flat">
                                <i class="ri-close-circle-fill"></i>
                            </button> 
                            <button onclick="add_after(2);" type="button" class="btn-sm btn btn-primary btn-flat">
                                <i class="ri-add-circle-fill"></i>
                            </button>
                        </td> 
                </tr>';
            $no = 2;
            $num = 2;
        }
        $msg = null;
        $dataRender = [$iconComponent,$dataComponen,$no,$num,$tables, $msg];

        return json_encode(array('data' => $dataRender));
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

    public function delete(Request $request)
    {
        $table = "generator_logs";
        $column_visible = "module";
        $route = "generator-log.destroy";

        return $this->delete_lib->deleteConfirmation($request,$table,$column_visible,$route);
    }

    public function destroy($delete_type)
    {           
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

                $redirect_url = "generator-log";
                DB::table($row['table'])->where('id', $row['id'])->delete();                                     

                $index++;
            }

            if($delete_type=="softdelete"){
                $redirect_url = "generator-log";

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
