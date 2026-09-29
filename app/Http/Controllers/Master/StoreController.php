<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Module;

use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use DB;
use App\Helpers\IndonesiaitLib;
use Illuminate\Support\Facades\File; 
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StoreExport;
use Request as Req;
use Illuminate\Support\Facades\Storage;

class StoreController extends BackendController
{
    function __construct()
    {
        parent::__construct();
        $this->data['page_title'] = "Store Page";
        $this->data['titleHeader'] = Module::where('url', '=',Req::segment(1))->first(['icon', 'name', 'url']);        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        \RuleHelper::UserHasPermission('access');

        if ($request->ajax() && $request->has('id')) {
        $data = Textedit::findOrFail($request->id);
        return response()->json($data);
        }

        $this->data['stores_count'] = Store::whereNotNull('deleted_at')->count();
        

        $this->data['url'] = 'store';
        $tgl = null;
        $dateNow = Carbon::now()->toDateString();
        $this->data['dateNow'] = $this->formatDate($dateNow,$tgl,3);
        
        return view('backend.pages.store.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        \RuleHelper::UserHasPermission('create');

        $tgl = null;
        $dateNow = Carbon::now()->toDateString();
        $this->data['dateNow'] = $this->formatDate($dateNow,$tgl,3);
        

        return view('backend.pages.store.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        \RuleHelper::UserHasPermission('create');
        
        $validator = Validator::make($request->all(), [
'name' => 'nullable|string']);

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }

        
        

        $store = new Store();
$store->id = ucwords($request->id);
$store->name = ucwords($request->name);
$store->address = $request->address;
$store->ecommerce = $request->ecommerce;
$store->created_by = Auth::user()->email;
$simpan = $store->save();

        if ($simpan) {
            \Session::flash('flash_message', 'New record created successfully'); 
            
            return redirect()->route('store.create');
        } else {
            \Session::flash('error_message', 'New record failed to created'); 
            
            return redirect()->route('store.create');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        \RuleHelper::UserHasPermission('read');
        $store = Store::findOrFail($id);
        
        
        $ecommerceKey = "0,1";
                    $ecommerceValue = "Store Only,Ecommerce";
                    $ecommerceArrayKey = explode(',',$ecommerceKey);
                    $ecommerceArrayValue = explode(',',$ecommerceValue);
                    foreach ($ecommerceArrayKey as $kunci => $nil) {
                        if ($nil == $store->ecommerce) {
                            $store->ecommerce = $ecommerceArrayValue[$kunci];
                        }
                    }
        

        $this->data['store'] = $store;


        return view('backend.pages.store.show',$this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        \RuleHelper::UserHasPermission('update');
        $store = Store::findOrFail($id);
        
        

        

        

        

        $this->data['store'] = $store;


        return response()->json([
            'status' => 'success',
            'data' => $store
            ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        \RuleHelper::UserHasPermission('update');
        $store = Store::findOrFail($id);
        $validator = Validator::make($request->all(), [
'name' => 'nullable|string']);

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }

        
        

        $store->id = ucwords($request->id);
$store->name = ucwords($request->name);
$store->address = $request->address;
$store->ecommerce = $request->ecommerce;
$store->updated_by = Auth::user()->email;
$simpan = $store->save();

        if ($simpan) {
            \Session::flash('flash_message', 'The record updated successfully'); 
            
            return redirect()->route('store.edit',$id);
        } else {
            \Session::flash('error_message', 'The record failed to updated'); 
            
            return redirect()->route('store.edit',$id);
        }
    }

    public function trash()
    {    
        \RuleHelper::UserHasPermission('trash');
        
        
        $this->data['stores_count'] = DB::table('stores')->whereNotNull('deleted_at')->count();
        return view('backend.pages.store.trash', $this->data);
    }

    public function get_datatable()
    {
        \RuleHelper::UserHasPermission('access');

        $user = Auth::user();
        $data = Store::select(['id',
'name',
'address',
'ecommerce',
'created_at',
'deleted_at',
'deleted_by',
'updated_at'])->whereNull('deleted_at');
        
        $datatables = DataTables::of($data);

        return $datatables
            ->addColumn('checkboxs', function($data) {
                $checkbox = " <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>";

                return $checkbox;
            })
            ->addColumn('number', function($data) {
                return "";
            })
            ->addColumn('action', function($data){
                $disabled = '';
                $btnEdit = '';
                $btnDelete = '';
                if(\RuleHelper::UserHasPermission('update',false)==false && \RuleHelper::UserHasPermission('delete',false)==false){
                    $disabled = 'disabled';
                }

                if(\RuleHelper::UserHasPermission('update',false)==true){
                    $btnEdit = "<a href='#' class='btn btn-sm btn-soft-secondary btnEdit' data-id='".$data->id."' title='Edit'><i class='las la-edit '></i></a>";
                }

                if(\RuleHelper::UserHasPermission('delete',false)==true){
                    $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='".$data->id."' title='Delete'><i class='las la-trash '></i></a>";                    
                }
                $btnView = "<a href='".route('store.show', $data->id)."' class='btn btn-sm btn-soft-secondary' title='Detail'><i class='las la-search-plus '></i></a>";

                return 
                $btnView.' '.$btnEdit.' '.$btnDelete;                
            })
            ->editColumn('created_at', function ($data){
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
           
           ->editColumn('created_at', function ($data){
                    return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
                })->editColumn('created_at', function ($data){
                    return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
                })->editColumn('ecommerce', function($data) {
                        $ecommerce = null;
if ('0' == $data->ecommerce) {
                                $ecommerce = 'Store Only';
                            }if ('1' == $data->ecommerce) {
                                $ecommerce = 'Ecommerce';
                            }return $ecommerce;
})->editColumn('created_at', function ($data){
                    return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
                })            
            ->filterColumn('deleted_at', function($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
if (request()->has('name')  && !is_null(request('name'))) {
                                        $query->where('name', 'like', "%" . request('name') . "%");
                                    }if (request()->has('address') && !is_null(request('address'))) {
                                    $query->where('address', 'like', "%" . request('address') . "%");
                                }if (request()->has('ecommerce') && !is_null(request('ecommerce'))) {
                                    $query->where('ecommerce', 'like', "%" . request('ecommerce') . "%");
                                }})
            ->rawColumns(['action', 'checkboxs', ])
            ->toJson();
    }

    public function get_datatable_trash()
    {
        \RuleHelper::UserHasPermission('trash');
        $data = Store::select(['id',
'name',
'address',
'ecommerce',
'created_at',
'deleted_at',
'deleted_by',
'updated_at'])->whereNotNull('deleted_at');
        $datatables = DataTables::of($data);
        
        return $datatables
            ->addColumn('checkboxs', function($data) {
                return "
                    <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>
                ";
            })       
            ->addColumn('action', function($data) {
                $btnView = "<a href='".route('store.show', $data->id)."' class='btn btn-sm btn-soft-secondary'><i class='las la-search-plus '></i> View</a>";
                $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='".$data->id."'><i class='las la-trash'></i> Permanent Delete</a>";
                $btnRestore = "<a href='#' class='btn btn-sm btn-soft-secondary btnRestore' id='".$data->id."'><i class='ri-restart-line '></i> Restore</a>";
                return 
                $btnView."                                
                ".$btnRestore."                                
                ".$btnDelete;
            })
            ->addColumn('number', function($data) {
                return "";
            })
            ->editColumn('created_at', function ($data){
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })            
            
            ->editColumn('created_at', function ($data){
                    return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
                })->editColumn('created_at', function ($data){
                    return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
                })->editColumn('ecommerce', function($data) {
                        $ecommerce = null;
if ('0' == $data->ecommerce) {
                                $ecommerce = 'Store Only';
                            }if ('1' == $data->ecommerce) {
                                $ecommerce = 'Ecommerce';
                            }return $ecommerce;
})->editColumn('created_at', function ($data){
                    return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
                })            
            ->filterColumn('deleted_at', function($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
if (request()->has('name')  && !is_null(request('name'))) {
                                        $query->where('name', 'like', "%" . request('name') . "%");
                                    }if (request()->has('address') && !is_null(request('address'))) {
                                    $query->where('address', 'like', "%" . request('address') . "%");
                                }if (request()->has('ecommerce') && !is_null(request('ecommerce'))) {
                                    $query->where('ecommerce', 'like', "%" . request('ecommerce') . "%");
                                }})
            ->rawColumns(['action', 'checkboxs', ])
            ->toJson();
    }

    public function delete(Request $request)
    {
        \RuleHelper::UserHasPermission('delete');

        $table = "stores";
        $column_visible = "name";
        $route = "store.destroy";

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

                $redirect_url = "store/trash";
                $store = Store::findOrFail($row['id']);
                
                
                $store->forceDelete();
                $index++;
            }

            if($delete_type=="softdelete"){
                $redirect_url = "store";

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

        if($request->id_to_be_restored==""){ 
            $request->id_to_be_restored = []; 
        }

        $data = DB::table('stores')
        ->whereIn('id', $request->id_to_be_restored)
        ->orderBy('deleted_at', 'desc')
        ->get();

        $this->data['index'] = 0; 
        $this->data['data'] = $data; 
        $this->data['route'] = 'store.restore_action'; 
        $this->data['column_name'] = 'name'; 
        
        \Session::flash('dependencies_data',  $data); 

        return view('backend.includes.restore_modal', $this->data);
    }

    public function upload(Request $request)
    {
        
    }

    public function restore_action(Request $request)
    {
        \RuleHelper::UserHasPermission('trash');

        $user = Auth::user();
        $dependencies_data = session('dependencies_data');
        $index = 0;
        foreach($dependencies_data as $row){
            Store::where('id', $row->id)
                ->update(
                    ['deleted_at' => null,'deleted_by' => null,
                    'restored_at' => date('Y-m-d H:i:s'),
                    'restored_by' => $user->email]);
            $index++;
        }

        \Session::flash('flash_message',  $index.' Record[s] restored successfully'); 
        
        return redirect('store/trash');
    }

    public function get_template(Request $request)
    {
        $data = [];
        $columns = Schema::getColumnListing($request->tabel);
        $columnsRemanen = DB::select( DB::raw('SHOW COLUMNS FROM `'.$request->tabel.'`'));
        $max = (int)count($columns) - 8;
        $max2 = (int)count($columnsRemanen) - 8;
        $columnDepedencies = [];
    
        $columnExtraAttribute = [];
        for ($i=1; $i <$max2 ; $i++) { 
            array_push($columnDepedencies,$columnsRemanen[$i]->Field);
            if ($columnsRemanen[$i]->Type == 'tinyint(1)') {
                array_push($columnExtraAttribute, [0,1]);
            }elseif (strpos($columnsRemanen[$i]->Type, 'enum') !== false) {
                $split = preg_split('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', explode('enum',$columnsRemanen[$i]->Type)[1]);
                $tmp = [];
                for ($j=0; $j < count($split); $j++) { 
                    if ($split[$j] != "") {
                        array_push($tmp, $split[$j]);
                    }
                }
                array_push($columnExtraAttribute, $tmp);
            }elseif ($columnsRemanen[$i]->Type == 'date') {
                array_push($columnExtraAttribute,'format : 2021-09-30');
            }elseif ($columnsRemanen[$i]->Type == 'datetime') {
                array_push($columnExtraAttribute,'format : 2021-09-30 23:59:00');
            }elseif ($columnsRemanen[$i]->Type == 'double(8,2)' || $columnsRemanen[$i]->Type == 'decimal(8,2)' || $columnsRemanen[$i]->Type == 'float(8,2)') {
                array_push($columnExtraAttribute,'format decimal with dots (.), ex : 17.05');
            }elseif ($this->isFK($request->tabel, $columnsRemanen[$i]->Field)) {
                $primaryTable = explode('_',$columnsRemanen[$i]->Field)[0];
                $tabelPrimer = DB::table($primaryTable)->get()->toArray();
                $allColumns = Schema::getColumnListing($primaryTable);
                $primaryColumns = [$allColumns[0],$allColumns[1]];
                $tmp = [];
                foreach ($tabelPrimer as $key => $tp) {
                    $newTp = get_object_vars($tp);
                    $field = '('.$newTp[$primaryColumns[1]].') '.$newTp[$primaryColumns[0]];
                    array_push($tmp,$field);
                }
                array_push($columnExtraAttribute,$tmp);
            }else {
                array_push($columnExtraAttribute,null);
            }
       }
       $data['columnDepedencies'] = $columnDepedencies;
       $data['columnExtraAttribute'] = $columnExtraAttribute;
       session()->put('columnExportData', $data);

       return true;
    }

    public function download()
    {
        $columnExportData = session()->get('columnExportData');
        session()->forget('columnExportData');

        return Excel::download(new StoreExport($columnExportData['columnDepedencies'],$columnExportData['columnExtraAttribute']), 'stores.xlsx');
    }

    private function isFK(string $table, string $column): bool
    { 
        $fkColumns = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableForeignKeys($table);
 
        return collect($fkColumns)->map(function ($fkColumn) {
                    return $fkColumn->getColumns();
                })->flatten()->contains($column);
    }

    private function formatDate($input, $output, $type)
    {
        preg_match_all('!\d+!', $input, $output);
        if ($type == 1) {
            return $output[0][2].'-'.$output[0][0].'-'.$output[0][1];
        } elseif ($type == 2) {
            return $output[0][1].'/'.$output[0][0].'/'.$output[0][2];
        } else {
            return $output[0][1].'/'.$output[0][2].'/'.$output[0][0];
        }
    }

    private function formatDateTime($input)
    {
        $data = explode(' ',$input);

        return $data[0];    
    }

    private function rupiah($angka)
    { 
        $hasil =  number_format($angka,0, ',' , '.'); 
        return $hasil; 
    }

    public function ganti_store(Request $request) {

        $store = Store::where('id', $request->change_store_id)
                    ->whereNull('deleted_at')
                    ->first();

        if (!$store) {
            return back()->with('flash_message', 'Store tidak valid atau sudah dihapus.');
        }

        session(['store' => ['id' => $store->id, 'name' => $store->name]]);

        \Session::flash('flash_message', 'Store Default Berhasil Diganti!');
        
        return back();
    }

    

}
