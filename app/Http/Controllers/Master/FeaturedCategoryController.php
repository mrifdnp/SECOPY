<?php
namespace App\Http\Controllers\Master;

use App\Exports\FeaturedCategoryExport;
use App\Http\Controllers\Core\BackendController;
use App\Models\FeaturedCategory;
use App\Models\Module;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Maatwebsite\Excel\Facades\Excel;
use Request as Req;
use Yajra\DataTables\DataTables;

class FeaturedCategoryController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['page_title']  = "Featured Category Page";
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

        $this->data['featured_categories_count'] = FeaturedCategory::whereNotNull('deleted_at')->count();

        $this->data['url']     = 'featured-category';
        $tgl                   = null;
        $dateNow               = Carbon::now()->toDateString();
        $this->data['dateNow'] = $this->formatDate($dateNow, $tgl, 3);

        return view('backend.pages.featured_category.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        \RuleHelper::UserHasPermission('create');

        $tgl                   = null;
        $dateNow               = Carbon::now()->toDateString();
        $this->data['dateNow'] = $this->formatDate($dateNow, $tgl, 3);

        return view('backend.pages.featured_category.create', $this->data);
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
            'title'        => 'required|string',
            'sub_title'    => 'nullable|string',
            'link_url'     => 'nullable|string',
            'content_path' => 'nullable|image|mimes:jpeg,png,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $imageName = null;
        $manager   = new ImageManager(new Driver());
        if ($request->hasFile('content_path')) {
            try {
                $fileGambar = $request->file('content_path');
                $imageName  = time() . '-' . $fileGambar->getClientOriginalName();

                // Baca file dengan Intervention
                $image = $manager->read($fileGambar->getRealPath());

                // Resize ukuran
                $image->cover(740, 840);

                // Simpan hasil resize ke storage (storage/app/public/category)
                $path  = 'featured_category/' . $imageName;
                $saved = Storage::disk('public')->put($path, (string) $image->encodeByExtension($fileGambar->getClientOriginalExtension()));

                if (! $saved) {
                    \Session::flash('error_message', 'Categories image failed to save');
                    return redirect()->route('featured-category.create')->withInput();
                }
            } catch (\Exception $e) {
                \Session::flash('error_message', 'featured category image failed to save');
                return redirect()->route('featured-category.create');
            }

        }

        $featured_category               = new FeaturedCategory();
        $featured_category->id           = ucwords($request->id);
        $featured_category->title        = ucwords($request->title);
        $featured_category->sub_title    = ucwords($request->sub_title);
        $featured_category->link_url     = ucwords($request->link_url);
        $featured_category->content_path = $imageName;
        $featured_category->created_by   = Auth::user()->email;
        $simpan                          = $featured_category->save();

        if ($simpan) {
            \Session::flash('flash_message', 'New record created successfully');

            return redirect()->route('featured-category.create');
        } else {
            \Session::flash('error_message', 'New record failed to created');

            return redirect()->route('featured-category.create');
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
        $featured_category = FeaturedCategory::findOrFail($id);

        $this->data['featured_category'] = $featured_category;

        return view('backend.pages.featured_category.show', $this->data);
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
        $featured_category = FeaturedCategory::findOrFail($id);

        $destination_path       = public_path('/img/featured_category/');
        $featured_categoryImage = $destination_path . $featured_category->content_path;
        if (! file_exists($featured_categoryImage) || is_null($featured_category->content_path)) {
            $featured_category->content_path = null;
        }

        $this->data['featured_category'] = $featured_category;

        return response()->json([
            'status' => 'success',
            'data'   => $featured_category,
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
        $featured_category = FeaturedCategory::findOrFail($id);
        $validator         = Validator::make($request->all(), [
            'title' => 'required|string', 'sub_title' => 'nullable|string', 'link_url' => 'nullable|string']);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $content_path = $request->content_path;
        $manager      = new ImageManager(new Driver());
        if ($request->hasFile('content_path')) {
            try {
                $fileGambar = $request->file('content_path');
                $imageName  = time() . '-' . $fileGambar->getClientOriginalName();

                // Baca file dengan Intervention
                $image = $manager->read($fileGambar->getRealPath());

                // Resize ukuran
                $image->cover(740, 840);

                // Simpan hasil resize ke storage (storage/app/public/category)
                $path  = 'featured_category/' . $imageName;
                $saved = Storage::disk('public')->put($path, (string) $image->encodeByExtension($fileGambar->getClientOriginalExtension()));

                $pathImage = 'featured_category';
                $path      = public_path('/storage/' . $pathImage . '/');
                $descImage = $path . $featured_category->content_path;
                if (file_exists($descImage) && ! is_null($featured_category->content_path)) {
                    @unlink($descImage);
                }
                $featured_category->content_path = $imageName;

                if (! $saved) {
                    \Session::flash('error_message', 'Categories image failed to save');
                    return redirect()->route('featured-category.create')->withInput();
                }
            } catch (\Exception $e) {
                \Session::flash('error_message', 'featured category image failed to save');
                return redirect()->route('featured-category.create');
            }

        }

        $featured_category->id         = $request->id;
        $featured_category->title      = ucwords($request->title);
        $featured_category->sub_title  = ucwords($request->sub_title);
        $featured_category->link_url   = ucwords($request->link_url);
        $featured_category->updated_by = Auth::user()->email;
        $simpan                        = $featured_category->save();

        if ($simpan) {
            \Session::flash('flash_message', 'The record updated successfully');

            return redirect()->route('featured-category.edit', $id);
        } else {
            \Session::flash('error_message', 'The record failed to updated');

            return redirect()->route('featured-category.edit', $id);
        }
    }

    public function trash()
    {
        \RuleHelper::UserHasPermission('trash');

        $this->data['featured_categories_count'] = DB::table('featured_categories')->whereNotNull('deleted_at')->count();
        return view('backend.pages.featured_category.trash', $this->data);
    }

    public function get_datatable()
    {
        \RuleHelper::UserHasPermission('access');

        $user = Auth::user();
        $data = FeaturedCategory::select(['id',
            'title',
            'sub_title',
            'content_path',
            'created_at',
            'deleted_at',
            'deleted_by',
            'updated_at'])->whereNull('deleted_at');

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
                $disabled  = '';
                $btnEdit   = '';
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
                $btnView = "<a href='" . route('featured-category.show', $data->id) . "' class='btn btn-sm btn-soft-secondary' title='Detail'><i class='las la-search-plus '></i></a>";

                return $btnView . ' ' . $btnEdit . ' ' . $btnDelete;
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->addColumn('content_path', function ($data) {
                $img                    = '';
                $destination_path       = public_path('/storage/featured_category/');
                $featured_categoryImage = $destination_path . $data->content_path;

                if (file_exists($featured_categoryImage) && ! is_null($data->content_path)) {
                    $img = '<img src="' . asset('/storage/featured_category/' . $data->content_path) . '"  height="70px" style="text-align:center;"/>';
                } else {
                    $words    = explode(' ', trim($data->title));
                    $first    = strtoupper(substr($words[0], 0, 1));
                    $second   = isset($words[1]) ? strtoupper(substr($words[1], 0, 1)) : '';
                    $initials = $first . $second;

                    $img = '<img src="https://placehold.co/110x70?text=' . $initials . '"/>';
                }
                return $img;
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('created_at', function ($data) {
            return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
        })->editColumn('created_at', function ($data) {
            return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
        })
            ->filterColumn('deleted_at', function ($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('title') && ! is_null(request('title'))) {
                    $query->where('title', 'like', "%" . request('title') . "%");
                }if (request()->has('sub_title') && ! is_null(request('sub_title'))) {
                    $query->where('sub_title', 'like', "%" . request('sub_title') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs', 'content_path'])
            ->toJson();
    }

    public function get_datatable_trash()
    {
        \RuleHelper::UserHasPermission('trash');
        $data = FeaturedCategory::select(['id',
            'title',
            'sub_title',
            'content_path',
            'created_at',
            'deleted_at',
            'deleted_by',
            'updated_at'])->whereNotNull('deleted_at');
        $datatables = DataTables::of($data);

        return $datatables
            ->addColumn('checkboxs', function ($data) {
                return "
                    <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>
                ";
            })
            ->addColumn('action', function ($data) {
                $btnView    = "<a href='" . route('featured-category.show', $data->id) . "' class='btn btn-sm btn-soft-secondary'><i class='las la-search-plus '></i> View</a>";
                $btnDelete  = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='" . $data->id . "'><i class='las la-trash'></i> Permanent Delete</a>";
                $btnRestore = "<a href='#' class='btn btn-sm btn-soft-secondary btnRestore' id='" . $data->id . "'><i class='ri-restart-line '></i> Restore</a>";
                return $btnView . " " . $btnRestore . " " . $btnDelete;
            })
            ->addColumn('number', function ($data) {
                return "";
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->addColumn('content_path', function ($data) {
                $img                    = '';
                $destination_path       = public_path('/storage/featured_category/');
                $featured_categoryImage = $destination_path . $data->content_path;

                if (file_exists($featured_categoryImage) && ! is_null($data->content_path)) {
                    $img = '<img src="' . asset('/storage/featured_category/' . $data->content_path) . '"  height="70px" style="text-align:center;"/>';
                } else {
                    $words    = explode(' ', trim($data->title));
                    $first    = strtoupper(substr($words[0], 0, 1));
                    $second   = isset($words[1]) ? strtoupper(substr($words[1], 0, 1)) : '';
                    $initials = $first . $second;

                    $img = '<img src="https://placehold.co/110x70?text=' . $initials . '"/>';
                }
                return $img;
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('created_at', function ($data) {
            return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
        })->editColumn('created_at', function ($data) {
            return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
        })
            ->filterColumn('deleted_at', function ($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('title') && ! is_null(request('title'))) {
                    $query->where('title', 'like', "%" . request('title') . "%");
                }if (request()->has('sub_title') && ! is_null(request('sub_title'))) {
                    $query->where('sub_title', 'like', "%" . request('sub_title') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs', 'content_path'])
            ->toJson();
    }

    public function delete(Request $request)
    {
        \RuleHelper::UserHasPermission('delete');

        $table          = "featured_categories";
        $column_visible = "title";
        $route          = "featured-category.destroy";

        return $this->delete_lib->deleteConfirmation($request, $table, $column_visible, $route);
    }

    public function destroy($delete_type)
    {
        \RuleHelper::UserHasPermission('delete');
        $user              = Auth::user();
        $dependencies_data = session('dependencies_data');
        $index             = 0;
        $redirect_url      = "";
        foreach ($dependencies_data as $row) {
            if ($row['delete_access_type'] != "restrict" && $delete_type == "harddelete") {
                $delete_data = array_reverse($row['data']);
                foreach ($delete_data as $delete_data_row) {
                    DB::table($delete_data_row['table'])
                        ->where($delete_data_row['field'], $delete_data_row['value'])
                        ->delete();
                }

                $redirect_url           = "featured-category/trash";
                $featured_category      = FeaturedCategory::findOrFail($row['id']);
                $destination_path       = public_path('/storage/featured_category/');
                $featured_categoryImage = $destination_path . $featured_category->content_path;
                if (file_exists($featured_categoryImage) && ! is_null($featured_category->content_path)) {
                    @unlink($featured_categoryImage);
                }

                $featured_category->forceDelete();
                $index++;
            }

            if ($delete_type == "softdelete") {
                $redirect_url = "featured-category";

                DB::table($row['table'])
                    ->where('id', $row['id'])
                    ->update(['deleted_at' => date('Y-m-d H:i:s'), 'deleted_by' => $user->email, 'restored_at' => null, 'restored_by' => null]);

                $index++;
            }
        }

        \Session::flash('flash_message', $index . ' Record[s] deleted successfully');

        return redirect($redirect_url);
    }

    public function restore(Request $request)
    {
        \RuleHelper::UserHasPermission('trash');

        if ($request->id_to_be_restored == "") {
            $request->id_to_be_restored = [];
        }

        $data = DB::table('featured_categories')
            ->whereIn('id', $request->id_to_be_restored)
            ->orderBy('deleted_at', 'desc')
            ->get();

        $this->data['index']       = 0;
        $this->data['data']        = $data;
        $this->data['route']       = 'featured-category.restore_action';
        $this->data['column_name'] = 'title';

        \Session::flash('dependencies_data', $data);

        return view('backend.includes.restore_modal', $this->data);
    }

    public function upload(Request $request)
    {

    }

    public function restore_action(Request $request)
    {
        \RuleHelper::UserHasPermission('trash');

        $user              = Auth::user();
        $dependencies_data = session('dependencies_data');
        $index             = 0;
        foreach ($dependencies_data as $row) {
            FeaturedCategory::where('id', $row->id)
                ->update(
                    ['deleted_at' => null, 'deleted_by' => null,
                        'restored_at' => date('Y-m-d H:i:s'),
                        'restored_by' => $user->email]);
            $index++;
        }

        \Session::flash('flash_message', $index . ' Record[s] restored successfully');

        return redirect('featured-category/trash');
    }

    public function get_template(Request $request)
    {
        $data              = [];
        $columns           = Schema::getColumnListing($request->tabel);
        $columnsRemanen    = DB::select(DB::raw('SHOW COLUMNS FROM `' . $request->tabel . '`'));
        $max               = (int) count($columns) - 8;
        $max2              = (int) count($columnsRemanen) - 8;
        $columnDepedencies = [];

        $columnExtraAttribute = [];
        for ($i = 1; $i < $max2; $i++) {
            array_push($columnDepedencies, $columnsRemanen[$i]->Field);
            if ($columnsRemanen[$i]->Type == 'tinyint(1)') {
                array_push($columnExtraAttribute, [0, 1]);
            } elseif (strpos($columnsRemanen[$i]->Type, 'enum') !== false) {
                $split = preg_split('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', explode('enum', $columnsRemanen[$i]->Type)[1]);
                $tmp   = [];
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
                $primaryTable   = explode('_', $columnsRemanen[$i]->Field)[0];
                $tabelPrimer    = DB::table($primaryTable)->get()->toArray();
                $allColumns     = Schema::getColumnListing($primaryTable);
                $primaryColumns = [$allColumns[0], $allColumns[1]];
                $tmp            = [];
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
        $data['columnDepedencies']    = $columnDepedencies;
        $data['columnExtraAttribute'] = $columnExtraAttribute;
        session()->put('columnExportData', $data);

        return true;
    }

    public function download()
    {
        $columnExportData = session()->get('columnExportData');
        session()->forget('columnExportData');

        return Excel::download(new FeaturedCategoryExport($columnExportData['columnDepedencies'], $columnExportData['columnExtraAttribute']), 'featured_categories.xlsx');
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
        $hasil = number_format($angka, 0, ',', '.');
        return $hasil;
    }

}
