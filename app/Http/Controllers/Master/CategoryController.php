<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Module;
use App\Models\MainCategory;

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
use App\Exports\CategoryExport;
use Request as Req;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends BackendController
{
    function __construct()
    {
        parent::__construct();
        $this->data['page_title'] = "Category Page";
        $this->data['titleHeader'] = Module::where('url', '=', Req::segment(1))->first(['icon', 'name', 'url']);
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

        $this->data['categories_count'] = Category::whereNotNull('deleted_at')->count();
        $this->data['main_categories'] = DB::table('main_categories')->whereNull('deleted_at')->get();


        $this->data['url'] = 'category';
        $tgl = null;
        $dateNow = Carbon::now()->toDateString();
        $this->data['dateNow'] = $this->formatDate($dateNow, $tgl, 3);

        return view('backend.pages.category.index', $this->data);
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
        $this->data['dateNow'] = $this->formatDate($dateNow, $tgl, 3);
        $this->data['main_categories'] = DB::table('main_categories')->whereNull('deleted_at')->get();


        return view('backend.pages.category.create', $this->data);
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
            'title' => 'nullable|string',
            'main_categories_id' => 'required|uuid|exists:main_categories,id',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }




        $category = new Category();
        $category->id = ucwords($request->id);
        $category->title = ucwords($request->title);
        $category->main_categories_id = $request->main_categories_id;
        $category->slug = Str::slug($request->title);
        $category->meta_title = $request->meta_title;
        $category->meta_description = $request->meta_description;
        $category->created_by = Auth::user()->email;
        $simpan = $category->save();

        if ($simpan) {
            \Session::flash('flash_message', 'New record created successfully');

            return redirect()->route('category.create');
        } else {
            \Session::flash('error_message', 'New record failed to created');

            return redirect()->route('category.create');
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
        $category = Category::findOrFail($id);





        $this->data['category'] = $category;


        return view('backend.pages.category.show', $this->data);
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
        $category = Category::findOrFail($id);





        $main_categories = MainCategory::whereNull('deleted_at')->get();



        $this->data['category'] = $category;
        $this->data['main_categories'] = $main_categories;


        return response()->json([
            'status' => 'success',
            'data' => $category
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
        $category = Category::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string',
            'main_categories_id' => 'required|uuid|exists:main_categories,id',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }




        $category->id = ucwords($request->id);
        $category->title = ucwords($request->title);
        $category->main_categories_id = $request->main_categories_id;
        $category->slug = Str::slug($request->title);
        $category->meta_title = $request->meta_title;
        $category->meta_description = $request->meta_description;
        $category->updated_by = Auth::user()->email;
        $simpan = $category->save();

        if ($simpan) {
            \Session::flash('flash_message', 'The record updated successfully');

            return redirect()->route('category.edit', $id);
        } else {
            \Session::flash('error_message', 'The record failed to updated');

            return redirect()->route('category.edit', $id);
        }
    }

    public function trash()
    {
        \RuleHelper::UserHasPermission('trash');

        $this->data['main_categories'] = DB::table('main_categories')->whereNull('deleted_at')->get();

        $this->data['categories_count'] = DB::table('categories')->whereNotNull('deleted_at')->count();
        return view('backend.pages.category.trash', $this->data);
    }

    public function get_datatable()
    {
        \RuleHelper::UserHasPermission('access');

        $user = Auth::user();
        $data = DB::table('categories')->join('main_categories', 'main_categories.id', '=', 'categories.main_categories_id')
            ->select([
                'categories.id',
                'categories.title',
                'main_categories.title as main_category',

                'categories.created_at',
                'categories.updated_at'
            ])->whereNull('categories.deleted_at');

        $datatables = DataTables::of($data);

        return $datatables
            ->addColumn('checkboxs', function ($data) {
                $checkbox = " <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>";

                return $checkbox;
            })
            ->addColumn('number', function ($data) {
                return "";
            })
            ->addColumn('action', function ($data) {
                $disabled = '';
                $btnEdit = '';
                $btnDelete = '';
                if (\RuleHelper::UserHasPermission('update', false) == false && \RuleHelper::UserHasPermission('delete', false) == false) {
                    $disabled = 'disabled';
                }

                if (\RuleHelper::UserHasPermission('update', false) == true) {
                    $btnEdit = "<a href='#' class='btn btn-sm btn-soft-secondary btnEdit' data-id='" . $data->id . "' title='Edit'><i class='las la-edit '></i></a>";
                }

                if (\RuleHelper::UserHasPermission('delete', false) == true) {
                    $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='" . $data->id . "' title='Delete'><i class='las la-trash '></i></a>";
                }
                $btnView = "<a href='" . route('category.show', $data->id) . "' class='btn btn-sm btn-soft-secondary' title='Detail'><i class='las la-search-plus '></i></a>";

                return
                    $btnView . ' ' . $btnEdit . ' ' . $btnDelete;
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })

            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->filterColumn('deleted_at', function ($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('title') && !is_null(request('title'))) {
                    $query->where('categories.title', 'like', "%" . request('title') . "%");
                }
                if (request()->has('main_categories_id') && !is_null(request('main_categories_id'))) {
                    $query->where('categories.main_categories_id', request('main_categories_id'));
                }
            })
            ->rawColumns(['action', 'checkboxs',])
            ->toJson();
    }

    public function get_datatable_trash()
    {
        \RuleHelper::UserHasPermission('trash');
        $data = DB::table('categories')->join('main_categories', 'main_categories.id', '=', 'categories.main_categories_id')
            ->select([
                'categories.id',
                'categories.title',
                'main_categories.title as main_category',

                'categories.created_at',
                'categories.updated_at'
            ])->whereNotNull('categories.deleted_at');
        $datatables = DataTables::of($data);

        return $datatables
            ->addColumn('checkboxs', function ($data) {
                return "
                    <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>
                ";
            })
            ->addColumn('action', function ($data) {
                $btnView = "<a href='" . route('category.show', $data->id) . "' class='btn btn-sm btn-soft-secondary'><i class='las la-search-plus '></i> View</a>";
                $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='" . $data->id . "'><i class='las la-trash'></i> Permanent Delete</a>";
                $btnRestore = "<a href='#' class='btn btn-sm btn-soft-secondary btnRestore' id='" . $data->id . "'><i class='ri-restart-line '></i> Restore</a>";
                return
                    $btnView . "                                
                " . $btnRestore . "                                
                " . $btnDelete;
            })
            ->addColumn('number', function ($data) {
                return "";
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })

            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->filterColumn('deleted_at', function ($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('title') && !is_null(request('title'))) {
                    $query->where('categories.title', 'like', "%" . request('title') . "%");
                }
                if (request()->has('main_categories_id') && !is_null(request('main_categories_id'))) {
                    $query->where('categories.main_categories_id', request('main_categories_id'));
                }
            })
            ->rawColumns(['action', 'checkboxs',])
            ->toJson();
    }

    public function delete(Request $request)
    {
        \RuleHelper::UserHasPermission('delete');

        $table = "categories";
        $column_visible = "title";
        $route = "category.destroy";

        return $this->delete_lib->deleteConfirmation($request, $table, $column_visible, $route);
    }

    public function destroy($delete_type)
    {
        \RuleHelper::UserHasPermission('delete');
        $user = Auth::user();
        $dependencies_data = session('dependencies_data');
        $index = 0;
        $redirect_url = "";
        foreach ($dependencies_data as $row) {
            if ($row['delete_access_type'] != "restrict" && $delete_type == "harddelete") {
                $delete_data = array_reverse($row['data']);
                foreach ($delete_data as $delete_data_row) {
                    DB::table($delete_data_row['table'])
                        ->where($delete_data_row['field'], $delete_data_row['value'])
                        ->delete();
                }

                $redirect_url = "category/trash";
                $category = Category::findOrFail($row['id']);


                $category->forceDelete();
                $index++;
            }

            if ($delete_type == "softdelete") {
                $redirect_url = "category";

                DB::table($row['table'])
                    ->where('id', $row['id'])
                    ->update(['deleted_at' => date('Y-m-d H:i:s'), 'deleted_by' => $user->email, 'restored_at' => null, 'restored_by' => null]);

                $index++;
            }
        }

        \Session::flash('flash_message',  $index . ' Record[s] deleted successfully');

        return redirect($redirect_url);
    }

    public function restore(Request $request)
    {
        \RuleHelper::UserHasPermission('trash');

        if ($request->id_to_be_restored == "") {
            $request->id_to_be_restored = [];
        }

        $data = DB::table('categories')
            ->whereIn('id', $request->id_to_be_restored)
            ->orderBy('deleted_at', 'desc')
            ->get();

        $this->data['index'] = 0;
        $this->data['data'] = $data;
        $this->data['route'] = 'category.restore_action';
        $this->data['column_name'] = 'title';

        \Session::flash('dependencies_data',  $data);

        return view('backend.includes.restore_modal', $this->data);
    }

    public function upload(Request $request) {}

    public function restore_action(Request $request)
    {
        \RuleHelper::UserHasPermission('trash');

        $user = Auth::user();
        $dependencies_data = session('dependencies_data');
        $index = 0;
        foreach ($dependencies_data as $row) {
            Category::where('id', $row->id)
                ->update(
                    [
                        'deleted_at' => null,
                        'deleted_by' => null,
                        'restored_at' => date('Y-m-d H:i:s'),
                        'restored_by' => $user->email
                    ]
                );
            $index++;
        }

        \Session::flash('flash_message',  $index . ' Record[s] restored successfully');

        return redirect('category/trash');
    }

    public function get_template(Request $request)
    {
        $data = [];
        $columns = Schema::getColumnListing($request->tabel);
        $columnsRemanen = DB::select(DB::raw('SHOW COLUMNS FROM `' . $request->tabel . '`'));
        $max = (int)count($columns) - 8;
        $max2 = (int)count($columnsRemanen) - 8;
        $columnDepedencies = [];

        $columnExtraAttribute = [];
        for ($i = 1; $i < $max2; $i++) {
            array_push($columnDepedencies, $columnsRemanen[$i]->Field);
            if ($columnsRemanen[$i]->Type == 'tinyint(1)') {
                array_push($columnExtraAttribute, [0, 1]);
            } elseif (strpos($columnsRemanen[$i]->Type, 'enum') !== false) {
                $split = preg_split('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', explode('enum', $columnsRemanen[$i]->Type)[1]);
                $tmp = [];
                for ($j = 0; $j < count($split); $j++) {
                    if ($split[$j] != "") {
                        array_push($tmp, $split[$j]);
                    }
                }
                array_push($columnExtraAttribute, $tmp);
            } elseif ($columnsRemanen[$i]->Type == 'date') {
                array_push($columnExtraAttribute, 'format : 2021-09-30');
            } elseif ($columnsRemanen[$i]->Type == 'datetime') {
                array_push($columnExtraAttribute, 'format : 2021-09-30 23:59:00');
            } elseif ($columnsRemanen[$i]->Type == 'double(8,2)' || $columnsRemanen[$i]->Type == 'decimal(8,2)' || $columnsRemanen[$i]->Type == 'float(8,2)') {
                array_push($columnExtraAttribute, 'format decimal with dots (.), ex : 17.05');
            } elseif ($this->isFK($request->tabel, $columnsRemanen[$i]->Field)) {
                $primaryTable = explode('_', $columnsRemanen[$i]->Field)[0];
                $tabelPrimer = DB::table($primaryTable)->get()->toArray();
                $allColumns = Schema::getColumnListing($primaryTable);
                $primaryColumns = [$allColumns[0], $allColumns[1]];
                $tmp = [];
                foreach ($tabelPrimer as $key => $tp) {
                    $newTp = get_object_vars($tp);
                    $field = '(' . $newTp[$primaryColumns[1]] . ') ' . $newTp[$primaryColumns[0]];
                    array_push($tmp, $field);
                }
                array_push($columnExtraAttribute, $tmp);
            } else {
                array_push($columnExtraAttribute, null);
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

        return Excel::download(new CategoryExport($columnExportData['columnDepedencies'], $columnExportData['columnExtraAttribute']), 'categories.xlsx');
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
            return $output[0][2] . '-' . $output[0][0] . '-' . $output[0][1];
        } elseif ($type == 2) {
            return $output[0][1] . '/' . $output[0][0] . '/' . $output[0][2];
        } else {
            return $output[0][1] . '/' . $output[0][2] . '/' . $output[0][0];
        }
    }

    private function formatDateTime($input)
    {
        $data = explode(' ', $input);

        return $data[0];
    }

    private function rupiah($angka)
    {
        $hasil =  number_format($angka, 0, ',', '.');
        return $hasil;
    }
}
