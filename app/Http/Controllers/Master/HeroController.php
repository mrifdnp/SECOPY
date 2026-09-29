<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use App\Models\Hero;
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
use App\Exports\HeroExport;
use Request as Req;
use Illuminate\Support\Facades\Storage;

class HeroController extends BackendController
{
    function __construct()
    {
        parent::__construct();
        $this->data['page_title'] = "Hero Page";
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

        $this->data['heroes_count'] = Hero::whereNotNull('deleted_at')->count();


        $this->data['url'] = 'hero';
        $tgl = null;
        $dateNow = Carbon::now()->toDateString();
        $this->data['dateNow'] = $this->formatDate($dateNow, $tgl, 3);

        return view('backend.pages.hero.index', $this->data);
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


        return view('backend.pages.hero.create', $this->data);
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

        $rules = [
            'title' => 'required|string',
            'subtitle' => 'required|string',
            'link_txt' => 'nullable|string',
            'link_url' => 'nullable|string',
            'type' => 'required|in:1,2',
            'status' => 'required'
        ];


        if ($request->type == 1) {
            $rules['content'] = 'required|image|mimes:jpeg,png,svg|max:5000';
        } elseif ($request->type == 2) {
            $rules['content'] = 'required|url';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $imageName = null;
        $contentValue = null;

        if ($request->type == 1 && $request->hasFile('content')) {
            $manager = new ImageManager(new Driver());
            try {
                $fileGambar = $request->file('content');
                $imageName = time() . '-' . $fileGambar->getClientOriginalName();

                $image = $manager->read($fileGambar->getRealPath());

                $path = 'hero/' . $imageName;
                $saved = Storage::disk('public')->put($path, (string) $image->encodeByExtension($fileGambar->getClientOriginalExtension()));

                if (!$saved) {
                    \Session::flash('error_message', 'Heroes image failed to save');
                    return redirect()->route('hero.create')->withInput();
                }

                $contentValue = $imageName;
            } catch (\Exception $e) {
                \Session::flash('error_message', 'Heroes image failed to save: ' . $e->getMessage());
                return redirect()->route('hero.create')->withInput();
            }
        }

        if ($request->type == 2) {
            $contentValue = $request->content;
        }

        $hero = new Hero();
        $hero->id = ucwords($request->id);
        $hero->title = ucwords($request->title);
        $hero->subtitle = ucwords($request->subtitle);
        $hero->link_txt = ucwords($request->link_txt);
        $hero->link_url = ucwords($request->link_url);
        $hero->content = $contentValue;
        $hero->type = $request->type;
        $hero->status = $request->status;
        $hero->created_by = Auth::user()->email;

        if ($request->status == 1) {
            Hero::where('status', 1)->update(['status' => 2]);
        }

        $simpan = $hero->save();

        if ($simpan) {
            \Session::flash('flash_message', 'New record created successfully');
            return redirect()->route('hero.create');
        } else {
            \Session::flash('error_message', 'New record failed to created');
            return redirect()->route('hero.create');
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
        $hero = Hero::findOrFail($id);


        $typeKey = "1,2";
        $typeValue = "Gambar,Video";
        $typeArrayKey = explode(',', $typeKey);
        $typeArrayValue = explode(',', $typeValue);
        foreach ($typeArrayKey as $kunci => $nil) {
            if ($nil == $hero->type) {
                $hero->type = $typeArrayValue[$kunci];
            }
        }
        $statusKey = "1,2";
        $statusValue = "Aktif,Tidak Aktif";
        $statusArrayKey = explode(',', $statusKey);
        $statusArrayValue = explode(',', $statusValue);
        foreach ($statusArrayKey as $kunci => $nil) {
            if ($nil == $hero->status) {
                $hero->status = $statusArrayValue[$kunci];
            }
        }

        $this->data['hero'] = $hero;


        return view('backend.pages.hero.show', $this->data);
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
        $hero = Hero::findOrFail($id);

        $destination_path = public_path('/img/hero/');
        $heroImage = $destination_path . $hero->content;
        if (!file_exists($heroImage) || is_null($hero->content)) {
            $hero->content = null;
        }







        $this->data['hero'] = $hero;


        return response()->json([
            'status' => 'success',
            'data' => $hero
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
        $hero = Hero::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'subtitle' => 'required|string',
            'link_txt' => 'nullable|string',
            'link_url' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $content = $request->content;
        $manager = new ImageManager(new Driver());
        if ($request->hasFile('content')) {
            try {
                $fileGambar = $request->file('content');
                $imageName = time() . '-' . $fileGambar->getClientOriginalName();

                // Baca file dengan Intervention
                $image = $manager->read($fileGambar->getRealPath());

                // Resize ke ukuran max 300x300
                // $image->resize(300, null, function ($constraint) {
                //     $constraint->aspectRatio();
                //     $constraint->upsize();   
                // });

                // Simpan hasil resize ke storage (storage/app/public/category)
                $path = 'hero/' . $imageName;
                $saved = Storage::disk('public')->put($path, (string) $image->encodeByExtension($fileGambar->getClientOriginalExtension()));

                $pathImage = 'hero';
                $path = public_path('/storage/' . $pathImage . '/');
                $descImage = $path . $hero->content;
                if (file_exists($descImage) && !is_null($hero->content)) {
                    @unlink($descImage);
                }
                $hero->content = $imageName;

                if (!$saved) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Heroes image failed to save'
                    ], 500);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Heroes image failed to save: ' . $e->getMessage()
                ], 500);
            }
        } elseif ($request->type == 2) {
            $hero->content = $request->content;
        }


        $hero->title = ucwords($request->title);
        $hero->subtitle = ucwords($request->subtitle);
        $hero->link_txt = ucwords($request->link_txt);
        $hero->link_url = ucwords($request->link_url);
        $hero->type = $request->type;
        $hero->status = $request->status;
        $hero->updated_by = Auth::user()->email;
        $simpan = $hero->save();

        if ($simpan) {
            return response()->json([
                'status' => 'success',
                'message' => 'The record updated successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'The record failed to update'
            ], 500);
        }
    }

    public function trash()
    {
        \RuleHelper::UserHasPermission('trash');


        $this->data['heroes_count'] = DB::table('heroes')->whereNotNull('deleted_at')->count();
        return view('backend.pages.hero.trash', $this->data);
    }

    public function get_datatable()
    {
        \RuleHelper::UserHasPermission('access');

        $user = Auth::user();
        $data = Hero::select([
            'id',
            'title',
            'subtitle',
            'content',
            'type',
            'status',
            'created_at',
            'deleted_at',
            'deleted_by',
            'updated_at'
        ])->whereNull('deleted_at');

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
                $btnView = "<a href='" . route('hero.show', $data->id) . "' class='btn btn-sm btn-soft-secondary' title='Detail'><i class='las la-search-plus '></i></a>";

                return
                    $btnView . ' ' . $btnEdit . ' ' . $btnDelete;
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->addColumn('content', function ($data) {
                $img = '';
                $destination_path = public_path('/storage/hero/');
                $heroImage = $destination_path . $data->content;

                if (file_exists($heroImage) && !is_null($data->content)) {
                    $img = '<img src="' . asset('/storage/hero/' . $data->content) . '"  height="70px" style="text-align:center;"/>';
                } else {
                    $words = explode(' ', trim($data->title));
                    $first = strtoupper(substr($words[0], 0, 1));
                    $second = isset($words[1]) ? strtoupper(substr($words[1], 0, 1)) : '';
                    $initials = $first . $second;

                    $img = '<img src="https://placehold.co/110x70?text=' . $initials . '"/>';
                }
                return $img;
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('type', function ($data) {
                $type = null;
                if ('1' == $data->type) {
                    $type = 'Gambar';
                }
                if ('2' == $data->type) {
                    $type = 'Video';
                }
                return $type;
            })->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('status', function ($data) {
                $status = null;
                if ('1' == $data->status) {
                    $status = 'Aktif';
                }
                if ('2' == $data->status) {
                    $status = 'Tidak Aktif';
                }
                return $status;
            })->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->filterColumn('deleted_at', function ($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('title')  && !is_null(request('title'))) {
                    $query->where('title', 'like', "%" . request('title') . "%");
                }
                if (request()->has('type') && !is_null(request('type'))) {
                    $query->where('type', 'like', "%" . request('type') . "%");
                }
                if (request()->has('status') && !is_null(request('status'))) {
                    $query->where('status', 'like', "%" . request('status') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs', 'content'])
            ->toJson();
    }

    public function get_datatable_trash()
    {
        \RuleHelper::UserHasPermission('trash');
        $data = Hero::select([
            'id',
            'title',
            'content',
            'type',
            'status',
            'created_at',
            'deleted_at',
            'deleted_by',
            'updated_at'
        ])->whereNotNull('deleted_at');
        $datatables = DataTables::of($data);

        return $datatables
            ->addColumn('checkboxs', function ($data) {
                return "
                    <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>
                ";
            })
            ->addColumn('action', function ($data) {
                $btnView = "<a href='" . route('hero.show', $data->id) . "' class='btn btn-sm btn-soft-secondary'><i class='las la-search-plus '></i> View</a>";
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
            ->addColumn('content', function ($data) {
                $img = '';
                $destination_path = public_path('/storage/hero/');
                $heroImage = $destination_path . $data->content;

                if (file_exists($heroImage) && !is_null($data->content)) {
                    $img = '<img src="' . asset('/storage/hero/' . $data->content) . '"  height="70px" style="text-align:center;"/>';
                } else {
                    $words = explode(' ', trim($data->title));
                    $first = strtoupper(substr($words[0], 0, 1));
                    $second = isset($words[1]) ? strtoupper(substr($words[1], 0, 1)) : '';
                    $initials = $first . $second;

                    $img = '<img src="https://placehold.co/110x70?text=' . $initials . '"/>';
                }
                return $img;
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('type', function ($data) {
                $type = null;
                if ('1' == $data->type) {
                    $type = 'Gambar';
                }
                if ('2' == $data->type) {
                    $type = 'Video';
                }
                return $type;
            })->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('status', function ($data) {
                $status = null;
                if ('1' == $data->status) {
                    $status = 'Aktif';
                }
                if ('2' == $data->status) {
                    $status = 'Tidak Aktif';
                }
                return $status;
            })->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->filterColumn('deleted_at', function ($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('title')  && !is_null(request('title'))) {
                    $query->where('title', 'like', "%" . request('title') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs', 'content'])
            ->toJson();
    }

    public function delete(Request $request)
    {
        \RuleHelper::UserHasPermission('delete');

        $table = "heroes";
        $column_visible = "title";
        $route = "hero.destroy";

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

                $redirect_url = "hero/trash";
                $hero = Hero::findOrFail($row['id']);
                $destination_path = public_path('/storage/hero/');
                $heroImage = $destination_path . $hero->content;
                if (file_exists($heroImage) && !is_null($hero->content)) {
                    @unlink($heroImage);
                }

                $hero->forceDelete();
                $index++;
            }

            if ($delete_type == "softdelete") {
                $redirect_url = "hero";

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

        $data = DB::table('heroes')
            ->whereIn('id', $request->id_to_be_restored)
            ->orderBy('deleted_at', 'desc')
            ->get();

        $this->data['index'] = 0;
        $this->data['data'] = $data;
        $this->data['route'] = 'hero.restore_action';
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
            Hero::where('id', $row->id)
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

        return redirect('hero/trash');
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

        return Excel::download(new HeroExport($columnExportData['columnDepedencies'], $columnExportData['columnExtraAttribute']), 'heroes.xlsx');
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
