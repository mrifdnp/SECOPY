<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Core\BackendController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Models\DataRelation;
use App\Models\Module;
use Request as Req;

class DataRelationController extends BackendController
{
    function __construct()
    {
        parent::__construct();
        $this->data['titleHeader'] = Module::where('url', '=',Req::segment(1))->first(['icon', 'name', 'url']);
        $this->data['page_title'] = "Data Relation Page";
    }

    public function index()
    {
        \RuleHelper::UserHasPermission('access');       
        // $tables = DB::select('SELECT '.env('DB_DATABASE').' FROM information_schema.tables WHERE table_schema = :schema', ['schema' => 'public']); 
        // $tables = DB::select('SHOW TABLES'); 
        $tables = DB::select('SELECT table_name FROM information_schema.tables WHERE table_schema = :schema', ['schema' => 'public']);
        $tables = array_map('current',$tables);
        $this->data['tables'] = $tables; 

        $relations =  DataRelation::orderBy("created_at")->get();
        $relations = $relations->unique('primary_table');
       
        $detail_relations = [];
        foreach($relations as $row){
            $detail_relations[] =  DataRelation::where('primary_table', '=', $row->primary_table)->get();
        }
        $this->data['relations'] = $relations; 
        $this->data['detail_relations'] = $detail_relations; 
        $this->data['index'] = 0; 

        return view('backend.pages.datarelation.index',$this->data);
    }

    public function getcolumn($table)
    {
        \RuleHelper::UserHasPermission('access');

        $columns = DB::getSchemaBuilder()->getColumnListing($table);
        $str = "";
        foreach($columns as $row){
            $str .= "<option value='".$row."'>".$row."</option>"; 
        }
        echo $str;
    }

    public function store(Request $request)
    {
        \RuleHelper::UserHasPermission('access');

        $role = new DataRelation;

        $role->primary_table = $request->primary_table;
        $role->primary_id = $request->primary_id;
        $role->relation_table = $request->relation_table;
        $role->relation_id = $request->relation_id;
        $role->on_delete = "cascade";

        $role->save();
        $request->session()->flash('flash_message', 'New record created successfully');

        return redirect('/datarelation');
    }

    public function destroy($datarelation)
    {   
        \RuleHelper::UserHasPermission('access');

        DataRelation::findOrFail($datarelation)->delete();

        \Session::flash('flash_message', ' 1 Record deleted successfully'); 
        return redirect('datarelation');
    }

    public function delete(Request $request)
    {
        \RuleHelper::UserHasPermission('access');

        $data = DB::table('data_relations')
        ->where('id', $request->delete_id)
        ->get();

        $this->data['index'] = 0; 
        $this->data['data'] = $data; 
        $this->data['delete_type'] = $request->delete_id; 
        $this->data['route'] = 'datarelation.destroy'; 
        $this->data['column_name'] = 'relation_table'; 
        $this->data['dependencies_data'] = []; 

        return view('backend.includes.delete_modal', $this->data);   
    }

    public function update(Request $request, DataRelation $datarelation)
    {
        \RuleHelper::UserHasPermission('access');

        DataRelation::where('primary_table', $request->primary_table)
          ->update([
                'on_delete' => $request->on_delete
          ]);

        $request->session()->flash('flash_message', 'The record updated successfully');
        return redirect('datarelation');
    }
}
