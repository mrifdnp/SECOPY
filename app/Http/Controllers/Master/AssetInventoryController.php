<?php

namespace App\Http\Controllers\Master;


use App\Http\Controllers\Controller;
use App\Models\AssetInventory;
use Illuminate\Http\Request;
use Request as Req;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use App\Models\Module;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Core\BackendController;

class AssetInventoryController extends BackendController
{

    function __construct()
    {
        parent::__construct();
        $this->data['page_title'] = "Transaction Movement Page";
        $this->data['titleHeader'] = Module::where('url', '=', Req::segment(1))->first(['icon', 'name', 'url']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        \RuleHelper::UserHasPermission('access');

        $activeStore = session('store');
        $activeStoreId = is_array($activeStore) ? ($activeStore['id'] ?? null) : ($activeStore->id ?? null);

        if (!$activeStoreId) {
            $user = Auth::user();
            $userStoreIdsRaw = $user ? $user->store_id : null;
            if (!is_array($userStoreIdsRaw)) {
                $userStoreIdsDecoded = $userStoreIdsRaw ? json_decode($userStoreIdsRaw, true) : [];
                $userStoreIdsRaw = is_array($userStoreIdsDecoded) ? $userStoreIdsDecoded : [];
            }
            $firstStoreId = !empty($userStoreIdsRaw) ? $userStoreIdsRaw[0] : null;
            $defaultStore = $firstStoreId ? Store::whereNull('deleted_at')->find($firstStoreId) : Store::whereNull('deleted_at')->first();
            if ($defaultStore) {
                session(['store' => ['id' => (string) $defaultStore->id, 'name' => $defaultStore->name]]);
                $activeStore = session('store');
                $activeStoreId = (string) $defaultStore->id;
            }
        }

        $this->data['categories'] = Category::whereNull('deleted_at')
            ->pluck('title', 'id')->toArray();
        $this->data['stores'] = Store::whereNull('deleted_at')->orderBy('name')->get();
        $this->data['activeStoreId'] = $activeStoreId;
        $this->data['activeStoreName'] = is_array($activeStore) ? ($activeStore['name'] ?? null) : ($activeStore->name ?? null);

        return view('backend.pages.asset_inventory.index', $this->data);
    }


    public function get_datatable()
    {
        \RuleHelper::UserHasPermission('access');

        $activeStore = session('store');
        $activeStoreId = is_array($activeStore) ? ($activeStore['id'] ?? null) : ($activeStore->id ?? null);

        if (!$activeStoreId) {
            $user = Auth::user();
            $userStoreIdsRaw = $user ? $user->store_id : null;
            if (!is_array($userStoreIdsRaw)) {
                $userStoreIdsDecoded = $userStoreIdsRaw ? json_decode($userStoreIdsRaw, true) : [];
                $userStoreIdsRaw = is_array($userStoreIdsDecoded) ? $userStoreIdsDecoded : [];
            }
            $firstStoreId = !empty($userStoreIdsRaw) ? $userStoreIdsRaw[0] : null;
            $defaultStore = $firstStoreId ? Store::whereNull('deleted_at')->find($firstStoreId) : Store::whereNull('deleted_at')->first();
            if ($defaultStore) {
                session(['store' => ['id' => (string) $defaultStore->id, 'name' => $defaultStore->name]]);
                $activeStoreId = (string) $defaultStore->id;
            }
        }

        $storeId = request('store_id') ?: $activeStoreId;

        $assets = AssetInventory::with('store')->select([
            'id',
            'store_id',
            'code',
            'name',
            'merk',
            'category',
            'depreciation_year',
            'masa',
            'nilai_residu',
            'purchase_price',
            'jumlah_unit',
            'harga_per_unit',
            'umur_manfaat',
            'last_condition',
            'tgl',
            'image',
            'created_at'
        ])->whereNull('deleted_at')
          ->where('store_id', $storeId);

        $datatables = DataTables::of($assets);

        return $datatables
            ->addColumn('checkboxs', function ($data) {
                $checkbox = "<input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>";
                return $checkbox;
            })
            ->addColumn('number', function ($data) {
                return "";
            })
            ->addColumn('store_name', function ($data) {
                return $data->store->name ?? '-';
            })
            ->addColumn('image', function ($data) {
                $img = $data->image ? '<img src="' . asset('uploads/asset_inventories/' . $data->image) . '" height="40px" class="rounded" style="text-align:center; object-fit:cover;"/>' : '<img src="' . asset('/img/noimage.png') . '" height="40px" class="rounded" style="text-align:center;"/>';
                return $img;
            })
            ->addColumn('kondisi', function ($data) {
                return ucfirst($data->last_condition ?? 'Aktif');
            })
            ->addColumn('umur_manfaat_tahun', function ($data) {
                $umur = $data->umur_manfaat ?: ($data->masa ? round($data->masa / 12, 1) : 0);
                return (float) $umur;
            })
            ->addColumn('tgl_perolehan', function ($data) {
                return $data->tgl ? Carbon::parse($data->tgl)->format('d/m/Y') : '-';
            })
            ->addColumn('jumlah_unit', function ($data) {
                return $data->jumlah_unit ?? 1;
            })
            ->addColumn('harga_per_unit', function ($data) {
                $harga = $data->harga_per_unit ?: ($data->purchase_price ? ((float) $data->purchase_price / max(1, (int) ($data->jumlah_unit ?? 1))) : 0);
                return 'Rp ' . number_format($harga, 0, ',', '.');
            })
            ->addColumn('nilai_perolehan', function ($data) {
                return 'Rp ' . number_format($data->nilai_perolehan, 0, ',', '.');
            })
            ->addColumn('presentase_susut', function ($data) {
                return round($data->presentase_susut * 100, 1) . '%';
            })
            ->addColumn('jumlah_susut', function ($data) {
                return $data->jumlah_susut;
            })
            ->addColumn('batas_susut', function ($data) {
                return $data->batas_susut;
            })
            ->addColumn('penyusutan_per_bulan', function ($data) {
                return 'Rp ' . number_format($data->penyusutan_per_bulan, 0, ',', '.');
            })
            ->addColumn('akumulasi_penyusutan', function ($data) {
                return 'Rp ' . number_format($data->akumulasi_penyusutan, 0, ',', '.');
            })
            ->addColumn('nilai_buku', function ($data) {
                return 'Rp ' . number_format($data->nilai_buku, 0, ',', '.');
            })
            ->addColumn('action', function ($data) {
                $btnEdit = '';
                $btnDelete = '';
                if (\RuleHelper::UserHasPermission('update', false)) {
                    $btnEdit = "<a href='" . route('asset_inventories.edit', $data->id) . "' class='btn btn-sm btn-soft-secondary' title='Edit'><i class='las la-edit '></i></a>";
                }

                if (\RuleHelper::UserHasPermission('delete', false)) {
                    $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='" . $data->id . "' title='Delete'><i class='las la-trash '></i></a>";
                }
                $btnView = "<a href='" . route('asset_inventories.show', $data->id) . "' class='btn btn-sm btn-soft-secondary' title='Detail'><i class='las la-search-plus '></i></a>";

                return $btnView . ' ' . $btnEdit . ' ' . $btnDelete;
            })
            ->filter(function ($query) {
                if (request()->has('name') && !is_null(request('name'))) {
                    $query->where('name', 'like', '%' . request('name') . '%'); // Filter berdasarkan nama
                }
            })
            ->rawColumns(['action', 'checkboxs', 'image'])
            ->toJson();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        \RuleHelper::UserHasPermission('create');

        $this->data['categories'] = Category::whereNull('deleted_at')->pluck('title', 'id')->toArray();
        $this->data['stores'] = Store::whereNull('deleted_at')->orderBy('name')->get();
        $activeStore = session('store');
        $this->data['selectedStoreId'] = is_array($activeStore) ? ($activeStore['id'] ?? null) : ($activeStore->id ?? null);

        return view('backend.pages.asset_inventory.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        \RuleHelper::UserHasPermission('create');

        $activeStore = session('store');
        $activeStoreId = is_array($activeStore) ? ($activeStore['id'] ?? null) : ($activeStore->id ?? null);
        $targetStoreId = $request->store_id ?: $activeStoreId;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'store_id' => 'nullable',
            'product_code' => [
                'required',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('asset_inventories', 'code')
                    ->where(function ($query) use ($targetStoreId) {
                        return $query->where('store_id', $targetStoreId)->whereNull('deleted_at');
                    }),
            ],
            'tanggal_asset' => 'required|date',
            'purchase_price' => 'nullable',
            'nilai_residu' => 'nullable',
            'masa' => 'nullable',
            'umur_manfaat' => 'nullable|numeric',
            'jumlah_unit' => 'nullable|numeric',
            'harga_per_unit' => 'nullable',
            'last_condition' => 'required|in:aktif,rusak,hilang,terjual',
            'category' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $existingAsset = AssetInventory::where('code', $request->product_code)
            ->where('store_id', $targetStoreId)
            ->whereNull('deleted_at')
            ->first();

        if ($existingAsset) {
            return back()->with('error_message', 'Kode aset sudah ada di store ini!')->withInput();
        }

        // Hapus tanda titik dari purchase_price agar hanya berupa angka
        $qtyUnit = max(1, (int) ($request->jumlah_unit ?: 1));
        $hargaPerUnit = (float) str_replace('.', '', $request->harga_per_unit ?: '0');
        $umurManfaat = (float) ($request->umur_manfaat ?: 0);
        $purchasePrice = $request->purchase_price ? (float) str_replace('.', '', $request->purchase_price) : ($qtyUnit * $hargaPerUnit);
        $masa = $request->masa ? (float) $request->masa : ($umurManfaat * 12);
        $residu = str_replace('.', '', $request->nilai_residu);

        $asset = new AssetInventory();
        $asset->parent_user_id = Auth::id();
        $asset->store_id = $targetStoreId;
        $asset->name = $request->name;
        $asset->merk = $request->merk;
        $asset->code = $request->product_code;
        $asset->tgl = $request->tanggal_asset;
        $asset->jumlah_unit = $qtyUnit;
        $asset->harga_per_unit = $hargaPerUnit;
        $asset->umur_manfaat = $umurManfaat;
        $asset->depreciation_year = -1;
        $asset->purchase_price = $purchasePrice; // Simpan purchase_price yang sudah dibersihkan
        $asset->masa = $masa;
        $asset->nilai_residu = $residu;
        $asset->last_condition = $request->last_condition;
        $asset->category = $request->category;
        $asset->created_by = Auth::user()->email;

        $simpan = $asset->save();

        if ($simpan) {
            $assetId = $asset->id;

            if ($request->hasFile('image')) {
                $extension = $request->file('image')->getClientOriginalExtension();
                $newFileName = $assetId . '_' . time() . '.' . $extension;
                
                $destinationPath = public_path('uploads/asset_inventories');
                $request->file('image')->move($destinationPath, $newFileName);

                $asset->image = $newFileName;
                $asset->save();
            }

            \Session::flash('flash_message', 'Aset baru berhasil dibuat!');
            return redirect()->route('asset_inventories.create');
        } else {
            \Session::flash('error_message', 'Aset baru gagal dibuat!');
            return redirect()->route('asset_inventories.create');
        }
    }




    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        \RuleHelper::UserHasPermission('read');

        $assetInventory = AssetInventory::leftJoin('users', DB::raw('users.id::text'), '=', 'asset_inventories.parent_user_id')
            ->leftJoin('stores', DB::raw('stores.id::text'), '=', DB::raw('asset_inventories.store_id::text'))
            ->select([
                'asset_inventories.id',
                'asset_inventories.store_id',
                'stores.name as store_name',
                'asset_inventories.code',
                'asset_inventories.name',
                'asset_inventories.merk',
                'asset_inventories.category',
                'asset_inventories.masa',
                'asset_inventories.nilai_residu',
                'asset_inventories.purchase_price',
                'asset_inventories.jumlah_unit',
                'asset_inventories.harga_per_unit',
                'asset_inventories.umur_manfaat',
                'asset_inventories.last_condition',
                'asset_inventories.tgl as tanggal_asset',
                'asset_inventories.image',
                'asset_inventories.created_at',
                'users.name as created_by',
            ])
            ->whereNull('asset_inventories.deleted_at')
            ->where('asset_inventories.id', $id)
            ->first();

        if (!$assetInventory) {
            return back()->with('error_message', 'Data aset tidak ditemukan!');
        }

        if (isset($assetInventory->purchase_price)) {
            $assetInventory->purchase_price = number_format((float) $assetInventory->purchase_price, 0, ',', '.');
        }

        $assetInventory->category = explode(',', $assetInventory->category);


        $nilaiPenyusutan = 'Rp 0';
        if (!is_null($assetInventory->purchase_price) && !is_null($assetInventory->masa) && $assetInventory->masa > 0) {
            $purchasePrice = (float) str_replace('.', '', $assetInventory->purchase_price);
            $nilaiResidu = (float) ($assetInventory->nilai_residu ?? 0);
            $masa = (float) $assetInventory->masa;

            if ($assetInventory->tanggal_asset) {
                $assetDate = \Carbon\Carbon::parse($assetInventory->tanggal_asset);
                $umurBulan = $assetDate->diffInMonths(\Carbon\Carbon::now());
                $penyusutan = $umurBulan * ($purchasePrice - $nilaiResidu) / $masa;
                $nilaiPenyusutan = 'Rp ' . number_format($penyusutan, 0, ',', '.');
            }
        }

        $yearsElapsed = 0;
        if ($assetInventory->tanggal_asset) {
            $assetDate = \Carbon\Carbon::parse($assetInventory->tanggal_asset);
            $currentDate = \Carbon\Carbon::now();
            $yearsElapsed = $assetDate->diffInYears($currentDate);
        }

        $this->data['data'] = $assetInventory;
        $this->data['nilai_penyusutan'] = $nilaiPenyusutan;
        $this->data['years_elapsed'] = $yearsElapsed;
        $this->data['asset_model'] = AssetInventory::find($id);

        return view('backend.pages.asset_inventory.show', $this->data);
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        \RuleHelper::UserHasPermission('update');

        $assetInventory = AssetInventory::leftJoin('users', DB::raw('users.id::text'), '=', 'asset_inventories.parent_user_id')
            ->select([
                'asset_inventories.id as asset_inventories_id',
                'asset_inventories.store_id',
                'asset_inventories.code',
                'asset_inventories.name',
                'asset_inventories.merk',
                'asset_inventories.category',
                'asset_inventories.purchase_price',
                'asset_inventories.jumlah_unit',
                'asset_inventories.harga_per_unit',
                'asset_inventories.umur_manfaat',
                'asset_inventories.depreciation_year',
                'asset_inventories.masa',
                'asset_inventories.nilai_residu',
                'asset_inventories.last_condition',
                'asset_inventories.tgl as tanggal_asset',
                'asset_inventories.image',
                'asset_inventories.created_at',
                'users.name as created_by',
            ])
            ->whereNull('asset_inventories.deleted_at')
            ->where('asset_inventories.id', $id)
            ->first();

        if (!$assetInventory) {
            return back()->with('error_message', 'Data aset tidak ditemukan!');
        }

        // Format purchase_price sebelum dikirim ke view
        $assetInventory->purchase_price = number_format($assetInventory->purchase_price, 0, ',', '.');
        if ($assetInventory->harga_per_unit) {
            $assetInventory->harga_per_unit = number_format($assetInventory->harga_per_unit, 0, ',', '.');
        }

        $this->data['data'] = $assetInventory;
        $this->data['stores'] = Store::whereNull('deleted_at')->orderBy('name')->get();
        $this->data['selectedStoreId'] = $assetInventory->store_id;

        return view('backend.pages.asset_inventory.edit', $this->data);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        \RuleHelper::UserHasPermission('update');

        $asset = AssetInventory::find($id);
        if (!$asset) {
            \Session::flash('error_message', 'Asset tidak ditemukan!');
            return redirect()->route('asset_inventories.edit', $id);
        }

        $targetStoreId = $request->store_id ?: $asset->store_id;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'store_id' => 'nullable',
            'product_code' => [
                'required',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('asset_inventories', 'code')
                    ->ignore($id)
                    ->where(function ($query) use ($targetStoreId) {
                        return $query->where('store_id', $targetStoreId)->whereNull('deleted_at');
                    }),
            ],
            'tanggal_asset' => 'required|date',
            'purchase_price' => 'nullable',
            'nilai_residu' => 'nullable',
            'masa' => 'nullable',
            'umur_manfaat' => 'nullable|numeric',
            'jumlah_unit' => 'nullable|numeric',
            'harga_per_unit' => 'nullable',
            'last_condition' => 'required|in:aktif,rusak,hilang,terjual',
            'category' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $qtyUnit = max(1, (int) ($request->jumlah_unit ?: $asset->jumlah_unit ?: 1));
        $hargaPerUnit = $request->harga_per_unit ? (float) str_replace('.', '', $request->harga_per_unit) : (float) ($asset->harga_per_unit ?: 0);
        $umurManfaat = $request->umur_manfaat ? (float) $request->umur_manfaat : (float) ($asset->umur_manfaat ?: 0);
        $purchasePrice = $request->purchase_price ? (float) str_replace('.', '', $request->purchase_price) : ($qtyUnit * $hargaPerUnit);
        $masa = $request->masa ? (float) $request->masa : ($umurManfaat * 12);
        $tanggalAsset = \Carbon\Carbon::parse($request->tanggal_asset);
        $depreciationYear = now()->diffInYears($tanggalAsset);

        if ($request->hasFile('image')) {
            if ($asset->image) {
                $oldImagePath = public_path('uploads/asset_inventories/' . $asset->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $extension = $request->file('image')->getClientOriginalExtension();
            $timestamp = time();
            $newFileName = $id . '_' . $timestamp . '.' . $extension;

            $destinationPath = public_path('uploads/asset_inventories');
            $request->file('image')->move($destinationPath, $newFileName);

            $asset->image = $newFileName;
        }

        $residu = str_replace('.', '', $request->nilai_residu);
        $asset->update([
            'name' => $request->name,
            'merk' => $request->merk ?: $asset->merk,
            'store_id' => $request->store_id ?: $asset->store_id,
            'code' => $request->product_code,
            'tgl' => $request->tanggal_asset,
            'jumlah_unit' => $qtyUnit,
            'harga_per_unit' => $hargaPerUnit,
            'umur_manfaat' => $umurManfaat,
            'purchase_price' => $purchasePrice,
            'depreciation_year' => $depreciationYear,
            'masa' => $masa,
            'nilai_residu' => $residu,
            'last_condition' => $request->last_condition,
            'category' => $request->category,
            'image' => $asset->image, // Perbarui dengan gambar baru jika ada
        ]);

        \Session::flash('flash_message', 'Data asset berhasil diperbarui!');

        return redirect()->route('asset_inventories.index');
    }



    public function get_datatable_alat($id)
    {
        \RuleHelper::UserHasPermission('access');
        // History table tidak tersedia di seco
        return response()->json(['data' => []]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request)
    {
        \RuleHelper::UserHasPermission('delete');

        $table = "asset_inventories";
        $column_visible = "name";
        $route = "asset_inventories.destroy";

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

                // $redirect_url = "product/trash";
                $assertInventory = AssetInventory::findOrFail($row['id']);
                if ($assertInventory->image) {
                    $destination_path = public_path('uploads/asset_inventories/');
                    $image = $destination_path . $assertInventory->image;
                    if (file_exists($image)) {
                        unlink($image);
                    }
                }

                $assertInventory->forceDelete();
                $index++;
            }

            if ($delete_type == "softdelete") {
                $redirect_url = "asset_inventories";

                DB::table($row['table'])
                    ->where('id', $row['id'])
                    ->update(['deleted_at' => date('Y-m-d H:i:s'), 'deleted_by' => $user->email, 'restored_at' => null, 'restored_by' => null]);

                $index++;
            }
        }

        \Session::flash('flash_message', $index . ' Record[s] berhasil dihapus!');

        return redirect($redirect_url);
    }

    public function export(Request $request)
    {
        \RuleHelper::UserHasPermission('access');

        $activeStore = session('store');
        $activeStoreId = is_array($activeStore) ? ($activeStore['id'] ?? null) : ($activeStore->id ?? null);
        $storeId = $request->store_id ?: $activeStoreId;

        $store = Store::find($storeId);
        $storeName = $store ? \Illuminate\Support\Str::slug($store->name, '_') : 'semua_store';
        $filename = 'Aset_Inventaris_' . $storeName . '_' . date('Ymd_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AssetInventoryFormulaExport($storeId, $request->cutoff_date),
            $filename
        );
    }
}
