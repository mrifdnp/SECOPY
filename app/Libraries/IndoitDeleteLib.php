<?php

namespace App\Libraries;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndoitDeleteLib {
    private $dependencies_data;
    private $delete_access_type;


    public function delete_check($data_relations,$id){
       foreach($data_relations as $row_data_relation){
            $data_exist = DB::table($row_data_relation->relation_table)
            ->where($row_data_relation->relation_id, $id)
            ->get();

            foreach($data_exist as $row_data_exist){
                $this->dependencies_data[]= array(
                    "table" => $row_data_relation->relation_table,
                    "field" => $row_data_relation->relation_id,
                    "value" => $id,
                    "type" => $row_data_relation->on_delete
                );

                if($row_data_relation->on_delete=="cascade" && $this->delete_access_type=="free"){
                    $this->delete_access_type = "cascade";
                }

                if($row_data_relation->on_delete=="restrict"){
                    $this->delete_access_type = "restrict";
                }

                $data_relations = DB::table('data_relations')
                ->where('primary_table', $row_data_relation->relation_table)
                ->get(); 

                $this->delete_check($data_relations,$row_data_exist->{$row_data_relation->relation_id});
            }
       }
    }

    public function deleteConfirmation($request,$table,$column_visible,$route)
    {
        if($request->id_to_be_deleted==""){ $request->id_to_be_deleted = [];}

        $data = DB::table($table)
                    ->whereIn('id', $request->id_to_be_deleted)
                    ->orderBy('created_at', 'asc')
                    ->get();
        
        $dependencies_data = [];
        $index = 0;
        foreach($data as $row){
            $this->dependencies_data = [];
            $this->delete_access_type = "free";

            $data_relations = DB::table('data_relations')
            ->where('primary_table', $table)
            ->get(); 
    
            $this->delete_check($data_relations,$row->id);

            $input = array_map("unserialize", array_unique(array_map("serialize", $this->dependencies_data)));
            $dependencies_data[$index]['id'] = $row->id;
            $dependencies_data[$index]['table'] = $table;
            $dependencies_data[$index]['data'] = $input;
            $dependencies_data[$index]['delete_access_type'] = $this->delete_access_type;
            $index++;
        }

        $this->data['index'] = 0; 
        $this->data['data'] = $data; 
        $this->data['delete_type'] = $request->delete_type; 
        $this->data['route'] = $route; 
        $this->data['column_name'] = $column_visible; 
        // dd($dependencies_data);
        $this->data['dependencies_data'] = $dependencies_data; 
        
        \Session::flash('dependencies_data',  $dependencies_data); 
        
        return view('backend.includes.delete_modal', $this->data);
    }

    
}