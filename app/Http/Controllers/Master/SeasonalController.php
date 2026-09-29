<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use App\Models\Seasonal;
use App\Models\Product;
use App\Models\Module;

use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Request as Req;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

class SeasonalController extends BackendController
{
    function __construct()
    {
        parent::__construct();
        $this->data['page_title'] = "Seasonal Page";
        $this->data['titleHeader'] = Module::where('url', '=', Req::segment(1))->first(['icon', 'name', 'url']);
    }

    public function index(Request $request)
    {
        \RuleHelper::UserHasPermission('access');

        $this->data['seasonals_count'] = Seasonal::whereNotNull('deleted_at')->count();
        $this->data['url'] = 'seasonal';
        $tgl = null;
        $dateNow = Carbon::now()->toDateString();
        $this->data['dateNow'] = $this->formatDate($dateNow, $tgl, 3);

        return view('backend.pages.seasonal.index', $this->data);
    }

    public function create()
    {
        \RuleHelper::UserHasPermission('create');

        $tgl = null;
        $dateNow = Carbon::now()->toDateString();
        $this->data['dateNow'] = $this->formatDate($dateNow, $tgl, 3);

        return view('backend.pages.seasonal.create', $this->data);
    }

    public function store(Request $request)
    {
        \RuleHelper::UserHasPermission('create');

        $validator = Validator::make($request->all(), [
            'name'       => 'required|string',
            'slug'       => 'nullable|string|unique:seasonals,slug',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'is_active'  => 'required|in:0,1',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5000',
            'hover_description' => 'nullable|string',
            'hover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $bannerName = null;
        if ($request->hasFile('banner_image')) {
            $manager = new ImageManager(new Driver());
            try {
                $fileGambar = $request->file('banner_image');
                $bannerName = time() . '-' . $fileGambar->getClientOriginalName();
                $image = $manager->read($fileGambar->getRealPath());
                $path = 'seasonal/' . $bannerName;
                Storage::disk('public')->put($path, (string) $image->encodeByExtension($fileGambar->getClientOriginalExtension()));
            } catch (\Exception $e) {
                \Session::flash('error_message', 'Banner image failed to save: ' . $e->getMessage());
                return redirect()->route('seasonal.create')->withInput();
            }
        }

        $hoverName = null;
        if ($request->hasFile('hover_image')) {
            $manager = new ImageManager(new Driver());
            try {
                $fileGambar = $request->file('hover_image');
                $hoverName = 'hover-' . time() . '-' . $fileGambar->getClientOriginalName();
                $image = $manager->read($fileGambar->getRealPath());
                $path = 'seasonal/' . $hoverName;
                Storage::disk('public')->put($path, (string) $image->encodeByExtension($fileGambar->getClientOriginalExtension()));
            } catch (\Exception $e) {
                \Session::flash('error_message', 'Hover image failed to save: ' . $e->getMessage());
                return redirect()->route('seasonal.create')->withInput();
            }
        }

        $seasonal = new Seasonal();
        $seasonal->name         = $request->name;
        $seasonal->slug         = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);
        $seasonal->description  = $request->description;
        $seasonal->hover_description = $request->hover_description;
        $seasonal->collection_title  = $request->collection_title;
        $seasonal->collection_subtitle = $request->collection_subtitle;
        $seasonal->button_text       = $request->button_text;
        $seasonal->button_link       = $request->button_link;
        $seasonal->banner_image = $bannerName;
        $seasonal->hover_image  = $hoverName;
        $seasonal->start_date   = $request->start_date;
        $seasonal->end_date     = $request->end_date;
        $seasonal->is_active    = $request->is_active;
        $seasonal->created_by   = Auth::user()->email;
        $simpan = $seasonal->save();

        if ($simpan) {
            \Session::flash('flash_message', 'Seasonal berhasil dibuat');
            return redirect()->route('seasonal.edit', $seasonal->id);
        } else {
            \Session::flash('error_message', 'Seasonal gagal dibuat');
            return redirect()->route('seasonal.create');
        }
    }

    public function show($id)
    {
        \RuleHelper::UserHasPermission('read');
        $seasonal = Seasonal::findOrFail($id);
        $seasonal->products_count = $seasonal->products()->count();

        $this->data['seasonal'] = $seasonal;

        return view('backend.pages.seasonal.show', $this->data);
    }

    public function edit($id)
    {
        \RuleHelper::UserHasPermission('update');
        $seasonal = Seasonal::findOrFail($id);

        $this->data['seasonal'] = $seasonal;
        $this->data['assigned_products'] = $seasonal->products()->with('categories')->get();

        return view('backend.pages.seasonal.edit', $this->data);
    }

    public function update(Request $request, $id)
    {
        \RuleHelper::UserHasPermission('update');

        $validator = Validator::make($request->all(), [
            'name'       => 'required|string',
            'slug'       => 'nullable|string|unique:seasonals,slug,' . $id,
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'is_active'  => 'required|in:0,1',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5000',
            'hover_description' => 'nullable|string',
            'hover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error validation',
                'message' => 'Terdapat kesalahan pada inputan.',
                'errors'  => $validator->errors(),
            ]);
        }

        $seasonal = Seasonal::findOrFail($id);

        if ($request->hasFile('banner_image')) {
            $manager = new ImageManager(new Driver());
            try {
                $fileGambar = $request->file('banner_image');
                $bannerName = time() . '-' . $fileGambar->getClientOriginalName();
                $image = $manager->read($fileGambar->getRealPath());
                $path = 'seasonal/' . $bannerName;
                Storage::disk('public')->put($path, (string) $image->encodeByExtension($fileGambar->getClientOriginalExtension()));

                // Hapus banner lama
                if ($seasonal->banner_image) {
                    $oldPath = 'seasonal/' . $seasonal->banner_image;
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                $seasonal->banner_image = $bannerName;
            } catch (\Exception $e) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Banner image failed to save: ' . $e->getMessage(),
                ]);
            }
        }

        if ($request->hasFile('hover_image')) {
            $manager = new ImageManager(new Driver());
            try {
                $fileGambar = $request->file('hover_image');
                $hoverName = 'hover-' . time() . '-' . $fileGambar->getClientOriginalName();
                $image = $manager->read($fileGambar->getRealPath());
                $path = 'seasonal/' . $hoverName;
                Storage::disk('public')->put($path, (string) $image->encodeByExtension($fileGambar->getClientOriginalExtension()));

                // Hapus hover lama
                if ($seasonal->hover_image) {
                    $oldPath = 'seasonal/' . $seasonal->hover_image;
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                $seasonal->hover_image = $hoverName;
            } catch (\Exception $e) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Hover image failed to save: ' . $e->getMessage(),
                ]);
            }
        }

        $seasonal->name        = $request->name;
        $seasonal->slug        = Str::slug($request->name);
        $seasonal->description = $request->description;
        $seasonal->hover_description = $request->hover_description;
        $seasonal->collection_title  = $request->collection_title;
        $seasonal->collection_subtitle = $request->collection_subtitle;
        $seasonal->button_text       = $request->button_text;
        $seasonal->button_link       = $request->button_link;
        $seasonal->start_date  = $request->start_date;
        $seasonal->end_date    = $request->end_date;
        $seasonal->is_active   = $request->is_active;
        $seasonal->updated_by  = Auth::user()->email;
        $seasonal->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil diperbarui.',
        ], 200);
    }

    public function trash()
    {
        \RuleHelper::UserHasPermission('trash');
        $this->data['seasonals_count'] = DB::table('seasonals')->whereNotNull('deleted_at')->count();
        return view('backend.pages.seasonal.trash', $this->data);
    }

    public function get_datatable()
    {
        \RuleHelper::UserHasPermission('access');

        $data = Seasonal::select([
            'id',
            'name',
            'banner_image',
            'start_date',
            'end_date',
            'is_active',
            'created_at',
            'deleted_at',
            'deleted_by',
            'updated_at'
        ])->whereNull('deleted_at');

        $datatables = DataTables::of($data);

        return $datatables
            ->addColumn('checkboxs', function ($data) {
                return "<input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>";
            })
            ->addColumn('number', function ($data) {
                return "";
            })
            ->addColumn('action', function ($data) {
                $btnEdit = '';
                $btnDelete = '';

                if (\RuleHelper::UserHasPermission('update', false) == true) {
                    $btnEdit = "<a href='" . route('seasonal.edit', $data->id) . "' class='btn btn-sm btn-soft-secondary' title='Edit'><i class='las la-edit'></i></a>";
                }
                if (\RuleHelper::UserHasPermission('delete', false) == true) {
                    $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='" . $data->id . "' title='Delete'><i class='las la-trash'></i></a>";
                }
                $btnView = "<a href='" . route('seasonal.show', $data->id) . "' class='btn btn-sm btn-soft-secondary' title='Detail'><i class='las la-search-plus'></i></a>";

                return $btnView . ' ' . $btnEdit . ' ' . $btnDelete;
            })
            ->addColumn('banner', function ($data) {
                if ($data->banner_image && Storage::disk('public')->exists('seasonal/' . $data->banner_image)) {
                    return '<img src="' . asset('/storage/seasonal/' . $data->banner_image) . '" height="50px"/>';
                }
                $words = explode(' ', trim($data->name));
                $first = strtoupper(substr($words[0], 0, 1));
                $second = isset($words[1]) ? strtoupper(substr($words[1], 0, 1)) : '';
                $initials = $first . $second;
                return '<img src="https://placehold.co/110x50?text=' . $initials . '"/>';
            })
            ->addColumn('products_count', function ($data) {
                return $data->products()->count();
            })
            ->editColumn('start_date', function ($data) {
                return Carbon::parse($data->start_date)->format('d M Y');
            })
            ->editColumn('end_date', function ($data) {
                return Carbon::parse($data->end_date)->format('d M Y');
            })
            ->editColumn('is_active', function ($data) {
                $today = now()->toDateString();
                if ($data->is_active && $data->start_date <= $today && $data->end_date >= $today) {
                    return '<span class="badge bg-success">Aktif</span>';
                } elseif ($data->is_active) {
                    return '<span class="badge bg-warning">Terjadwal</span>';
                }
                return '<span class="badge bg-secondary">Tidak Aktif</span>';
            })
            ->editColumn('created_at', function ($data) {
                return Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->filter(function ($query) {
                if (request()->has('name') && !is_null(request('name'))) {
                    $query->where('name', 'like', "%" . request('name') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs', 'banner', 'is_active'])
            ->toJson();
    }

    public function get_datatable_trash()
    {
        \RuleHelper::UserHasPermission('trash');

        $data = Seasonal::select([
            'id',
            'name',
            'banner_image',
            'start_date',
            'end_date',
            'is_active',
            'created_at',
            'deleted_at',
            'deleted_by',
            'updated_at'
        ])->whereNotNull('deleted_at');

        $datatables = DataTables::of($data);

        return $datatables
            ->addColumn('checkboxs', function ($data) {
                return "<input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>";
            })
            ->addColumn('number', function ($data) {
                return "";
            })
            ->addColumn('action', function ($data) {
                $btnView = "<a href='" . route('seasonal.show', $data->id) . "' class='btn btn-sm btn-soft-secondary'><i class='las la-search-plus'></i> View</a>";
                $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='" . $data->id . "'><i class='las la-trash'></i> Permanent Delete</a>";
                $btnRestore = "<a href='#' class='btn btn-sm btn-soft-secondary btnRestore' id='" . $data->id . "'><i class='ri-restart-line'></i> Restore</a>";
                return $btnView . " " . $btnRestore . " " . $btnDelete;
            })
            ->editColumn('start_date', function ($data) {
                return Carbon::parse($data->start_date)->format('d M Y');
            })
            ->editColumn('end_date', function ($data) {
                return Carbon::parse($data->end_date)->format('d M Y');
            })
            ->editColumn('created_at', function ($data) {
                return Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->filter(function ($query) {
                if (request()->has('name') && !is_null(request('name'))) {
                    $query->where('name', 'like', "%" . request('name') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs'])
            ->toJson();
    }

    public function delete(Request $request)
    {
        \RuleHelper::UserHasPermission('delete');

        $table = "seasonals";
        $column_visible = "name";
        $route = "seasonal.destroy";

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

                $redirect_url = "seasonal/trash";
                $seasonal = Seasonal::findOrFail($row['id']);

                // Hapus banner image
                if ($seasonal->banner_image) {
                    $oldPath = 'seasonal/' . $seasonal->banner_image;
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                $seasonal->forceDelete();
                $index++;
            }

            if ($delete_type == "softdelete") {
                $redirect_url = "seasonal";

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

        $data = DB::table('seasonals')
            ->whereIn('id', $request->id_to_be_restored)
            ->orderBy('deleted_at', 'desc')
            ->get();

        $this->data['index'] = 0;
        $this->data['data'] = $data;
        $this->data['route'] = 'seasonal.restore_action';
        $this->data['column_name'] = 'name';

        \Session::flash('dependencies_data', $data);

        return view('backend.includes.restore_modal', $this->data);
    }

    public function restore_action(Request $request)
    {
        \RuleHelper::UserHasPermission('trash');

        $user = Auth::user();
        $dependencies_data = session('dependencies_data');
        $index = 0;
        foreach ($dependencies_data as $row) {
            Seasonal::where('id', $row->id)
                ->update([
                    'deleted_at'  => null,
                    'deleted_by'  => null,
                    'restored_at' => date('Y-m-d H:i:s'),
                    'restored_by' => $user->email
                ]);
            $index++;
        }

        \Session::flash('flash_message', $index . ' Record[s] restored successfully');

        return redirect('seasonal/trash');
    }

    /**
     * Search product untuk di-assign ke seasonal
     */
    public function search_product(Request $request)
    {
        $keyword    = $request->get('keyword');
        $seasonalId = $request->get('seasonal_id');

        // Cari produk yang sudah di-assign
        $existingProductIds = DB::table('seasonal_products')
            ->where('seasonal_id', $seasonalId)
            ->pluck('product_id')
            ->toArray();

        $products = Product::where('show_in_ecommerce', true)
            ->whereNull('deleted_at')
            ->whereNotIn('id', $existingProductIds)
            ->when($keyword, function ($query, $keyword) {
                $query->where('title', 'ILIKE', '%' . $keyword . '%');
            })
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $products,
        ]);
    }

    /**
     * Tambah produk ke seasonal
     */
    public function add_product(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'seasonal_id' => 'required|exists:seasonals,id',
            'product_id'  => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 400);
        }

        // Cek apakah sudah ada
        $exists = DB::table('seasonal_products')
            ->where('seasonal_id', $request->seasonal_id)
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Produk sudah ada di seasonal ini.',
            ], 409);
        }

        $maxSort = DB::table('seasonal_products')
            ->where('seasonal_id', $request->seasonal_id)
            ->max('sort_order') ?? 0;

        DB::table('seasonal_products')->insert([
            'id'          => Str::uuid()->toString(),
            'seasonal_id' => $request->seasonal_id,
            'product_id'  => $request->product_id,
            'sort_order'  => $maxSort + 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Produk berhasil ditambahkan.',
        ]);
    }

    /**
     * Hapus produk dari seasonal
     */
    public function remove_product(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'seasonal_id' => 'required|exists:seasonals,id',
            'product_id'  => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal.',
            ], 400);
        }

        DB::table('seasonal_products')
            ->where('seasonal_id', $request->seasonal_id)
            ->where('product_id', $request->product_id)
            ->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Produk berhasil dihapus dari seasonal.',
        ]);
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
}
