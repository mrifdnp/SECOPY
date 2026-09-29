<?php
namespace App\Http\Controllers\Master;

use App\Exports\ProductExport;
use App\Helpers\RuleHelper;
use App\Http\Controllers\Core\BackendController;
use App\Models\Category;
use App\Models\Color;
use App\Models\Module;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\Store;
use App\Models\Variant;
use App\Models\Material;
use App\Models\MainCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request as Req;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class ProductController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['page_title']  = "Product Page";
        $this->data['titleHeader'] = Module::where('url', '=', Req::segment(1))->first(['icon', 'name', 'url']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        RuleHelper::UserHasPermission('access');

        $authUser = Auth::user();

        // Ambil store yang dimiliki user
        $userStore = json_decode($authUser->store_id ?? '[]', true);

        $activeStore = session()->get('store');
        $storeId = $activeStore ? $activeStore['id'] : null;

        $cacheKey = 'product_summary_stats_' . ($storeId ?? 'all');
        $stats = Cache::remember($cacheKey, 600, function() use ($storeId) {
            $pdQuery = DB::table('product_details')->whereNull('product_details.deleted_at');
            if ($storeId) {
                $pdQuery->where('product_details.store_id', $storeId);
            }

            return [
                'products_count' => Product::whereNotNull('deleted_at')->count(),
                'totalProducts' => Product::whereNull('deleted_at')->count(),
                'totalProductVariants' => (clone $pdQuery)
                    ->join('products', 'products.id', '=', 'product_details.product_id')
                    ->whereNull('products.deleted_at')
                    ->selectRaw('DISTINCT product_details.product_id, product_details.variant_id, product_details.color_id')
                    ->get()->count(),
                'incompleteSkuProductsCount' => Product::whereNull('deleted_at')
                    ->whereHas('productDetail', function($q) use ($storeId) {
                        $q->whereNull('product_details.deleted_at')
                          ->where(function($q2) {
                              $q2->whereNull('sku')->orWhere('sku', '');
                          });
                        if ($storeId) {
                            $q->where('product_details.store_id', $storeId);
                        }
                    })->count(),
                'ecommerceProductsCount' => Product::whereNull('deleted_at')
                    ->where('show_in_ecommerce', 1)->count(),
                'filledPriceCount' => (clone $pdQuery)
                    ->join('products', 'products.id', '=', 'product_details.product_id')
                    ->whereNull('products.deleted_at')
                    ->where('product_details.price', '>', 0)
                    ->selectRaw('DISTINCT product_details.product_id, product_details.variant_id, product_details.color_id')
                    ->get()->count(),
                'totalAssetValue' => (clone $pdQuery)
                    ->selectRaw('SUM(cogm * qty) as total')
                    ->value('total') ?? 0,
                'inStockCount' => (clone $pdQuery)
                    ->join('products', 'products.id', '=', 'product_details.product_id')
                    ->whereNull('products.deleted_at')
                    ->where('product_details.qty', '>', 0)
                    ->selectRaw('DISTINCT product_details.product_id, product_details.variant_id, product_details.color_id')
                    ->get()->count(),
                'minusStockCount' => (clone $pdQuery)
                    ->join('products', 'products.id', '=', 'product_details.product_id')
                    ->whereNull('products.deleted_at')
                    ->where('product_details.qty', '<', 0)
                    ->selectRaw('DISTINCT product_details.product_id, product_details.variant_id, product_details.color_id')
                    ->get()->count(),
                'incompleteCogmCount' => Product::whereNull('deleted_at')
                    ->whereHas('productDetail', function($q) use ($storeId) {
                        $q->whereNull('product_details.deleted_at')
                          ->where(function($q2) {
                              $q2->whereNull('cogm')->orWhere('cogm', 0);
                          });
                        if ($storeId) {
                            $q->where('product_details.store_id', $storeId);
                        }
                    })->count(),
                'incompleteWeightCount' => Product::whereNull('deleted_at')
                    ->whereHas('productDetail', function($q) use ($storeId) {
                        $q->whereNull('product_details.deleted_at')
                          ->where(function($q2) {
                              $q2->whereNull('weight')->orWhere('weight', 0);
                          });
                        if ($storeId) {
                            $q->where('product_details.store_id', $storeId);
                        }
                    })->count(),
            ];
        });

        $this->data = array_merge($this->data, $stats);

        $this->data['categories']     = DB::table('categories')->whereNull('deleted_at')->get();
        $this->data['main_categories'] = DB::table('main_categories')->whereNull('deleted_at')->get();

        $this->data['importProductRules']       = Product::importProductRules();
        $this->data['importProductDetailRules'] = Product::importProductDetailRules();
        $this->data['stores']                   = Store::whereIn('id', $userStore)->select(['id', 'name'])->get();
        $this->data['materials']                = Material::whereNull('deleted_at')->get();

        $this->data['url']     = 'product';
        $tgl                   = null;
        $dateNow               = Carbon::now()->toDateString();
        $this->data['dateNow'] = $this->formatDate($dateNow, $tgl, 3);

        return view('backend.pages.product.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        RuleHelper::UserHasPermission('create');

        $tgl                      = null;
        $dateNow                  = Carbon::now()->toDateString();
        $this->data['dateNow']    = $this->formatDate($dateNow, $tgl, 3);
        $this->data['categories'] = DB::table('categories')->whereNull('deleted_at')->get();
        $this->data['main_categories'] = DB::table('main_categories')->whereNull('deleted_at')->get();
        $this->data['materials'] = Material::whereNull('deleted_at')->get();

        return view('backend.pages.product.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        RuleHelper::UserHasPermission('create');

        $validator = Validator::make($request->all(), [
            'title'        => 'required|string',
            'top_pick'     => 'required|string',
            'category_id'  => 'required|uuid|exists:categories,id',
            'material_id'  => 'nullable|uuid|exists:materials,id',
            'content_paths' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $product                = new Product();
        $product->id            = ucwords($request->id);
        $product->title         = ucwords($request->title);
        $product->top_pick      = $request->top_pick;
        $product->category_id   = $request->category_id;
        $product->material_id   = $request->material_id;
        $product->content_paths = ucwords($request->content_paths);
        $product->created_by    = Auth::user()->email;
        $simpan                 = $product->save();

        if ($simpan) {
            Session::flash('flash_message', 'New record created successfully');

            return redirect()->route('product.index');
        } else {
            Session::flash('error_message', 'New record failed to created');

            return redirect()->route('product.index');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function product_basic_information($id)
    {
        RuleHelper::UserHasPermission('read');

        $product = Product::with(['variants', 'colors'])->findOrFail($id);

        $departments = MainCategory::whereNull('deleted_at')
            ->get(['id', 'title']);

        $materials = Material::whereNull('deleted_at')->get();

        $this->data['product']     = $product;
        $this->data['departments'] = $departments;
        $this->data['materials']   = $materials;

        return view('backend.pages.product.basic_information', $this->data);
    }

    public function update_product_basic_information(Request $request, $id)
    {
        RuleHelper::UserHasPermission('update');

        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'product_name'       => 'required|string',
            'category_id'        => 'required|uuid|exists:categories,id',
            'material_id'        => 'nullable|uuid|exists:materials,id',
            'top_pick'           => 'nullable|in:0,1',
            'show_in_ecommerce'  => 'nullable|in:0,1',

            'deleted_variants'   => 'nullable|array',
            'deleted_variants.*' => 'nullable|uuid|exists:variants,id',
            'deleted_colors'     => 'nullable|array',
            'deleted_colors.*'   => 'nullable|uuid|exists:colors,id',
            'deleted_images'     => 'nullable|array',
            'deleted_images.*'   => 'nullable|string',

            'variants'           => 'nullable|array',
            'variants.*.id'      => 'nullable|uuid',
            'variants.*.name'    => 'required|string',

            'colors'             => 'nullable|array',
            'colors.*.id'        => 'nullable|uuid',
            'colors.*.label'     => 'required|string',
            'colors.*.hexa'      => 'required|string',

            'images'             => 'nullable|array',
            'images.*'           => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'image_names'        => 'nullable|array',
            'image_names.*'      => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
        // Kumpulkan path file yang perlu dihapus (dihapus setelah transaction berhasil)
        $filesToDelete = [];

        $result = DB::transaction(function () use ($request, $product, $id, &$filesToDelete) {
        // Hapus gambar yang ditandai untuk dihapus
        $deletedImages = $request->input('deleted_images', []);
        foreach ($deletedImages as $imageName) {
            $contentPaths = $product->content_paths;
            $existingImages = collect(is_array($contentPaths) ? $contentPaths : []);
            $imageToDelete  = $existingImages->firstWhere('name', $imageName);

            if ($imageToDelete) {
                // Tandai file untuk dihapus nanti (setelah transaction berhasil)
                if (! empty($imageToDelete['path'])) {
                    $filesToDelete[] = $imageToDelete['path'];
                }

                // Hapus dari array content_paths
                $updatedImages = $existingImages->filter(function ($img) use ($imageName) {
                    if (is_array($img) && isset($img['name'])) {
                        return $img['name'] !== $imageName;
                    }
                    return true; // abaikan format yang salah / keep it
                });

                $product->content_paths = $updatedImages->values()->toArray();
            }
        }

        // Ambil outlet yang dimiliki
        $stores   = Store::whereNull('deleted_at')->get(['id']);
        $variants = $request->variants ?? [];
        $colors   = $request->colors ?? [];

        $this->createOrUpdateProductDetailOnEachStore($id, $stores, $variants, $colors);

        // Hapus varian dan warna yang dihapus
        $deletedVariantIds = $request->input('deleted_variants', []);
        $deletedColorIds   = $request->input('deleted_colors', []);
        $this->deleteProductDetailOnEachStore($deletedVariantIds, $deletedColorIds);

        $images     = $request->file('images') ?? [];
        $imageNames = $request->input('image_names', []);

        $uploadedImages     = [];
        $failedUploadImages = [];

        // Mapping untuk pesan error berdasarkan nama gambar
        $imageErrorMessages = [
            'main_pic'  => 'Gagal mengupload gambar utama',
            'hover_pic' => 'Gagal mengupload gambar hover',
            'pic_1'     => 'Gagal mengupload gambar ke-satu',
            'pic_2'     => 'Gagal mengupload gambar ke-dua',
            'pic_3'     => 'Gagal mengupload gambar ke-tiga',
            'pic_4'     => 'Gagal mengupload gambar ke-empat',
            'pic_5'     => 'Gagal mengupload gambar ke-lima',
            'pic_6'     => 'Gagal mengupload gambar ke-enam',
            'pic_7'     => 'Gagal mengupload gambar ke-tujuh',
            'pic_8'     => 'Gagal mengupload gambar ke-delapan',
        ];

        foreach ($images as $index => $image) {
            // Generate nama file unik
            $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            try {
                // Upload ke storage (public disk)
                $path = $image->storeAs('images', $fileName, 'public');

                // Cek apakah file benar-benar berhasil diupload
                if ($path && Storage::disk('public')->exists($path)) {
                    // Dapatkan nama gambar dari array image_names
                    $imageName = $imageNames[$index] ?? 'pic_' . $index;

                    $imageObject         = [];
                    $imageObject['name'] = $imageName;
                    $imageObject['path'] = $path;

                    $uploadedImages[] = $imageObject;
                } else {
                    // Jika upload gagal
                    $imageName                      = $imageNames[$index] ?? 'pic_' . $index;
                    $errorMessage                   = $imageErrorMessages[$imageName] ?? 'Gagal mengupload gambar ' . $imageName;
                    $failedUploadImages[$imageName] = $errorMessage;
                }
            } catch (\Exception $e) {
                // Jika terjadi exception saat upload
                $imageName                      = $imageNames[$index] ?? 'pic_' . $index;
                $errorMessage                   = $imageErrorMessages[$imageName] ?? 'Gagal mengupload gambar ' . $imageName;
                $failedUploadImages[$imageName] = $errorMessage;
            }
        }

        // Replace content_paths berdasarkan key 'name' yang sama
        $contentPaths = $product->content_paths;
        $existingImages = collect(is_array($contentPaths) ? $contentPaths : []);
        foreach ($uploadedImages as $newImage) {
            $oldImage = $existingImages->firstWhere('name', $newImage['name']);

            Log::debug('images', [
                'existingImages' => $existingImages,
                'newImage'       => $newImage,
                'oldImage'       => $oldImage,
            ]);

            // Cari index dari existing image
            $existingIndex = null;
            foreach ($existingImages as $idx => $existing) {
                if (is_array($existing) && isset($existing['name']) && isset($newImage['name'])) {
                    if ($existing['name'] === $newImage['name']) {
                        $existingIndex = $idx;
                        break;
                    }
                }
            }

            if ($oldImage && $existingIndex !== null) {
                // Tandai file lama untuk dihapus nanti
                if (! empty($oldImage['path'])) {
                    $filesToDelete[] = $oldImage['path'];
                }

                // Replace dengan data baru
                $existingImages[$existingIndex] = $newImage;
            } else {
                $existingImages->push($newImage);
            }
        }

        $product->content_paths     = $existingImages->values()->toArray();
        $product->title             = $request->product_name;
        $product->category_id       = $request->category_id;
        $product->material_id       = $request->material_id;
        $product->top_pick          = $request->top_pick ?? 0;
        $product->show_in_ecommerce = $request->show_in_ecommerce ?? 0;
        $product->save();

        return $failedUploadImages;
        });

        // Hapus file dari storage SETELAH transaction berhasil
        foreach ($filesToDelete as $filePath) {
            Storage::disk('public')->delete($filePath);
        }

        $response = [
            'success' => true,
            'message' => 'Informasi produk berhasil diperbarui',
        ];

        // Tambahkan failed_upload_images jika ada yang gagal
        if (! empty($result)) {
            $response['failed_upload_images'] = $result;
        }

        return response()->json($response);

        } catch (\Exception $e) {
            Log::error('update_product_basic_information error: ' . $e->getMessage(), [
                'product_id' => $id,
                'trace'      => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function getSizeWeight($string)
    {
        $str = strtolower(trim($string));
        
        $sizes = [
            ['pattern' => '/\b(xxs|extra extra small)\b/', 'weight' => 10],
            ['pattern' => '/\b(xs|extra small)\b/', 'weight' => 20],
            ['pattern' => '/\b(s|small)\b/', 'weight' => 30],
            ['pattern' => '/\b(m|medium)\b/', 'weight' => 40],
            ['pattern' => '/\b(l|large)\b/', 'weight' => 50],
            ['pattern' => '/\b(xl|extra large)\b/', 'weight' => 60],
            ['pattern' => '/\b(xxl|2xl|extra extra large)\b/', 'weight' => 70],
            ['pattern' => '/\b(xxxl|3xl)\b/', 'weight' => 80],
        ];

        foreach ($sizes as $size) {
            if (preg_match($size['pattern'], $str)) {
                return $size['weight'];
            }
        }

        return 999;
    }

    public function product_price_and_stock($id)
    {
        RuleHelper::UserHasPermission('read');

        $authUser = Auth::user();

        // Ambil store yang dimiliki user
        $userStore = json_decode($authUser->store_id ?? '[]', true);

        // Ambil store aktif dari session
        $activeStoreSession = session('store');
        $activeStoreId = is_array($activeStoreSession) ? ($activeStoreSession['id'] ?? null) : (is_object($activeStoreSession) ? ($activeStoreSession->id ?? null) : null);


        $product = Product::findOrFail($id);

        $stores = ProductDetail::with(['color', 'variant', 'store'])
            ->where('product_id', $id)
            ->get()
            ->sortBy(function ($item) {
                $variantName = $item->variant->name ?? '-';
                return sprintf('%03d-%s', $this->getSizeWeight($variantName), $variantName);
            })
            ->groupBy(function ($item) {
                return $item->store->name ?? 'Unknown';
            })
            ->filter(function ($group) use ($userStore) {
                // Hanya tampilkan group yang store_id-nya ada di userStore
                return $group->first() && in_array($group->first()->store_id, $userStore);
            })
            ->sortBy(function ($group) use ($userStore, $activeStoreId) {
                $storeId = $group->first()->store_id;
                if ($activeStoreId && $storeId == $activeStoreId) {
                    return -1; // Prioritas utama untuk store aktif
                }
                $index = is_array($userStore) ? array_search($storeId, $userStore) : false;
                return $index !== false ? $index : 9999;
            });

        if ($stores->isEmpty()) {
            return redirect()->route('product.basicInformation', ['id' => $id])
                ->with('error', 'Tidak ada detail produk yang ditemukan untuk produk ini.');
        }

        $departments = MainCategory::whereNull('deleted_at')
            ->get(['id', 'title']);

        $rawStock = ProductDetail::with(['store', 'variant', 'color'])
            ->where('product_id', $id)
            ->whereNull('deleted_at')
            ->get();

        $storeMap = [];
        foreach ($rawStock as $item) {
            if ($item->store) {
                $storeMap[$item->store->name] = $item->store_id;
            }
        }

        $pivotStoreNames = $rawStock->pluck('store.name')->filter()->unique()->values()
            ->sortBy(function ($storeName) use ($storeMap, $userStore, $activeStoreId) {
                $storeId = $storeMap[$storeName] ?? null;
                if ($activeStoreId && $storeId == $activeStoreId) {
                    return -1; // Prioritas utama untuk store aktif
                }
                $index = is_array($userStore) ? array_search($storeId, $userStore) : false;
                return $index !== false ? $index : 9999;
            })->values();
        $pivotStock = [];

        foreach ($rawStock as $item) {
            $variantName = $item->variant->name ?? '-';
            $colorLabel = $item->color->label ?? '-';
            $key = $variantName . '|' . $colorLabel;
            
            if (!isset($pivotStock[$key])) {
                $pivotStock[$key] = [
                    'variant' => $variantName,
                    'color' => $colorLabel,
                    'stocks' => []
                ];
                foreach ($pivotStoreNames as $sName) {
                    $pivotStock[$key]['stocks'][$sName] = [
                        'qty' => 0,
                        'detail_id' => null
                    ];
                }
            }
            
            $storeName = $item->store->name ?? 'Unknown';
            if (isset($pivotStock[$key]['stocks'][$storeName])) {
                $pivotStock[$key]['stocks'][$storeName]['qty'] += $item->qty;
                $pivotStock[$key]['stocks'][$storeName]['detail_id'] = $item->id;
            }
        }

        $pivotStock = collect($pivotStock)->sortBy(function($item) {
            return sprintf('%03d-%s', $this->getSizeWeight($item['variant']), $item['variant']);
        })->values();

        $this->data['product']     = $product;
        $this->data['stores']      = $stores;
        $this->data['departments'] = $departments;
        $this->data['pivotStoreNames'] = $pivotStoreNames;
        $this->data['pivotStock'] = $pivotStock;

        return view('backend.pages.product.price_and_stock', $this->data);
    }

    public function update_product_price_and_stock(Request $request)
    {
        RuleHelper::UserHasPermission('update');

        $validator = Validator::make($request->all(), [
            'product_details'                    => 'nullable|array',
            'product_details.*.id'               => 'required|uuid|exists:product_details,id',
            'product_details.*.price'            => 'required|numeric|min:0',
            'product_details.*.additional_price' => 'nullable|numeric|min:0',
            'product_details.*.cogm'             => 'nullable|numeric|min:0',
            'product_details.*.stock'            => 'required|integer',
            'product_details.*.sku'              => 'nullable',
            'product_details.*.weight'           => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $details = $request->input('product_details', []);

        // Validasi SKU unik per store
        $duplicatedSkus = $this->checkDuplicatedSku($details);
        if (! empty($duplicatedSkus)) {
            return response()->json([
                'message'         => 'Terdapat duplikasi SKU, sudah terdaftar di toko yang sama.',
                'duplicated_skus' => $duplicatedSkus,
            ], 409);
        }

        DB::transaction(function () use ($details) {
            // Ambil semua ID yang akan diupdate dalam 1 query
            $productDetailIds = collect($details)->pluck('id')->toArray();

            // Ambil semua data existing dalam 1 query
            $existingProducts = ProductDetail::whereIn('id', $productDetailIds)
                ->pluck('qty', 'id')
                ->toArray();

            $stockMovements = [];
            $updatesData    = [];

            // Prepare data untuk bulk operations
            foreach ($details as $item) {
                $productId = $item['id'];
                $oldStock  = $existingProducts[$productId] ?? 0;
                $newStock  = $item['stock'];

                // Prepare data untuk update
                $updatesData[] = [
                    'id'               => $productId,
                    'price'            => $item['price'],
                    'additional_price' => $item['additional_price'] ?? 0,
                    'cogm'             => $item['cogm'] ?? 0,
                    'qty'              => $newStock,
                    'sku'              => $item['sku'],
                    'weight'           => $item['weight'],
                ];

                // Jika ada perubahan stock, prepare data untuk stock movements
                if ($oldStock != $newStock) {
                    $stockChange = $newStock - $oldStock;

                    $stockMovements[] = [
                        'id'                => Str::uuid()->toString(),
                        'product_detail_id' => $productId,
                        'qty'               => $stockChange,
                        'last_stock'        => $newStock, // hasil akhir stock pada saat itu
                        'remark'            => 'Diubah melalui halaman edit',
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }
            }

            // Bulk update menggunakan upsert untuk lebih efisien
            foreach ($updatesData as $updateData) {
                $updatePayload = [
                    'price'            => $updateData['price'],
                    'additional_price' => $updateData['additional_price'],
                    'qty'              => $updateData['qty'],
                    'sku'              => $updateData['sku'],
                    'weight'           => $updateData['weight'],
                ];
                
                $cogmToUpdate = null;
                if (in_array(session()->get('current_role')->name, ['super', 'admin'])) {
                    $updatePayload['cogm'] = $updateData['cogm'];
                    $cogmToUpdate = $updateData['cogm'];
                }

                ProductDetail::where('id', $updateData['id'])->update($updatePayload);

                // Timpa harga, COGM, berat, dll di semua cabang dengan product_id, variant_id, color_id yang sama
                $pDetail = ProductDetail::with('store')->find($updateData['id']);
                if ($pDetail) {
                    $isExhibition = $pDetail->store && preg_match('/Seco Exhibition/i', $pDetail->store->name);

                    // Base query builder: same product, variant, color, exclude self
                    $buildBaseQuery = function() use ($pDetail) {
                        $q = ProductDetail::where('product_id', $pDetail->product_id)
                            ->where('id', '!=', $pDetail->id);
                        if ($pDetail->variant_id) { $q->where('variant_id', $pDetail->variant_id); } 
                        else { $q->whereNull('variant_id'); }
                        if ($pDetail->color_id) { $q->where('color_id', $pDetail->color_id); } 
                        else { $q->whereNull('color_id'); }
                        return $q;
                    };

                    // 1. Sync SKU, berat, COGM ke SEMUA toko (termasuk Exhibition)
                    $globalPayload = [
                        'sku'    => $updateData['sku'],
                        'weight' => $updateData['weight'],
                    ];
                    if ($cogmToUpdate !== null) {
                        $globalPayload['cogm'] = $cogmToUpdate;
                    }
                    $buildBaseQuery()->update($globalPayload);

                    // 2. Sync harga hanya dari non-Exhibition ke non-Exhibition (sesuai logika frontend)
                    if (!$isExhibition) {
                        $exhibitionStoreIds = Store::where('name', 'ilike', '%Seco Exhibition%')->pluck('id');
                        $buildBaseQuery()
                            ->whereNotIn('store_id', $exhibitionStoreIds)
                            ->update([
                                'price'            => $updateData['price'],
                                'additional_price' => $updateData['additional_price'],
                            ]);
                    }
                }
            }

            // Bulk insert stock movements jika ada
            if (! empty($stockMovements)) {
                DB::table('product_stock_movements')->insert($stockMovements);
            }
        });

        return response()->json(['message' => 'Product detail berhasil diperbarui'], 200);
    }

    public function product_detail($id)
    {
        RuleHelper::UserHasPermission('read');

        $product = Product::with(['variants' => function ($query) {
            $query->orderByDesc('created_at');
        }])->findOrFail($id);

        if ($product->variants->isEmpty()) {
            return redirect()->route('product.basicInformation', ['id' => $id])
                ->with('error', 'Tidak ada varian yang ditemukan untuk produk ini. Silakan tambahkan varian terlebih dahulu di halaman Informasi Dasar.');
        }

        $this->data['product'] = $product;

        return view('backend.pages.product.product_detail', $this->data);
    }

    public function update_product_detail(Request $request)
    {
        RuleHelper::UserHasPermission('update');

        $validator = Validator::make($request->all(), [
            'variants'                          => 'nullable|array',
            'variants.*.variationId'            => 'nullable|uuid|exists:variants,id',

            'variants.*.sections'               => 'nullable|array',
            'variants.*.sections.*.title'       => 'required|string',
            'variants.*.sections.*.description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $variants = $request->variants ?? [];
        foreach ($variants as $variant) {
            Variant::where('id', $variant['variationId'])->update([
                'json_description' => $variant['sections'] ?? [],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail produk berhasil diperbarui',
        ]);
    }

    public function product_detail_show($productId, $detailId)
    {
        RuleHelper::UserHasPermission('read');

        $productDetail = ProductDetail::with(['variant', 'color', 'store', 'productWithoutScope'])
            ->where('id', $detailId)
            ->where('product_id', $productId)
            ->firstOrFail();

        $this->data['productDetail'] = $productDetail;

        return view('backend.pages.product.product_detail_show', $this->data);
    }

    public function product_detail_stock_movement_datatable($id, $detailId)
    {
        $stockMovements = DB::table('product_stock_movements')
            ->where('product_detail_id', $detailId)
            ->orderByDesc('created_at')
            ->orderByDesc('updated_at');

        return DataTables::of($stockMovements)
            ->addIndexColumn()
            ->addColumn('type', function ($data) {
                // Jika qty positif, berarti stock masuk
                return $data->qty > 0 ? 'Masuk' : 'Keluar';
            })
            ->editColumn('created_at', function ($data) {
                return Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->translatedFormat('d F Y H:i:s');
            })
            ->toJson();
    }

    public function trash()
    {
        RuleHelper::UserHasPermission('trash');

        $this->data['categories'] = DB::table('categories')->whereNull('deleted_at')->get();

        $this->data['products_count'] = DB::table('products')->whereNotNull('deleted_at')->count();
        return view('backend.pages.product.trash', $this->data);
    }

    public function get_datatable()
    {
        RuleHelper::UserHasPermission('access');

        $data = DB::table('products')->join('categories', 'categories.id', '=', 'products.category_id')
            ->join('main_categories', 'main_categories.id', '=', 'categories.main_categories_id')
            ->select([
                'products.id',
                'products.title',
                'products.top_pick',
                'products.show_in_ecommerce',
                'products.content_paths',
                'categories.id as category',
                'main_categories.title as main_category_title',
                'categories.title as category_title',
                'products.created_at',
                'products.updated_at',
                'materials.title as material_title'
            ])
            ->leftJoin('materials', 'materials.id', '=', 'products.material_id')
            ->addSelect([
                'total_sku' => DB::table('product_details')
                    ->selectRaw('count(*)')
                    ->whereColumn('product_details.product_id', 'products.id')
                    ->whereNull('deleted_at'),
                'filled_sku' => DB::table('product_details')
                    ->selectRaw('count(*)')
                    ->whereColumn('product_details.product_id', 'products.id')
                    ->whereNotNull('sku')
                    ->where('sku', '!=', '')
                    ->whereNull('deleted_at')
            ])
            ->whereNull('products.deleted_at')
            ->orderBy('products.created_at', 'desc');

        $activeStore = session()->get('store');
        $storeId = request('store_id') ?: ($activeStore ? $activeStore['id'] : null);

        if (Req::input('filter_incomplete_sku') == 1) {
            $data->whereExists(function($query) use ($storeId) {
                $query->select(DB::raw(1))
                      ->from('product_details')
                      ->whereColumn('product_details.product_id', 'products.id')
                      ->whereNull('product_details.deleted_at')
                      ->where(function($q) {
                          $q->whereNull('sku')->orWhere('sku', '');
                      });
                if ($storeId && $storeId !== 'all') {
                    $query->where('store_id', $storeId);
                }
            });
        }

        if (Req::input('filter_incomplete_price') == 1) {
            $data->whereExists(function($query) use ($storeId) {
                $query->select(DB::raw(1))
                      ->from('product_details as pd1')
                      ->whereColumn('pd1.product_id', 'products.id')
                      ->whereNull('pd1.deleted_at')
                      ->whereNotExists(function($q2) {
                          $q2->select(DB::raw(1))
                             ->from('product_details as pd2')
                             ->whereColumn('pd2.product_id', 'pd1.product_id')
                             ->whereColumn('pd2.variant_id', 'pd1.variant_id')
                             ->whereColumn('pd2.color_id', 'pd1.color_id')
                             ->where('pd2.price', '>', 0);
                      });
                if ($storeId && $storeId !== 'all') {
                    $query->where('pd1.store_id', $storeId);
                }
            });
        } elseif (Req::input('filter_incomplete_price') === 'complete') {
            $data->whereExists(function($query) use ($storeId) {
                $query->select(DB::raw(1))
                      ->from('product_details as pd1')
                      ->whereColumn('pd1.product_id', 'products.id')
                      ->whereNull('pd1.deleted_at')
                      ->where('pd1.price', '>', 0);
                if ($storeId && $storeId !== 'all') {
                    $query->where('pd1.store_id', $storeId);
                }
            });
        }

        if (Req::input('filter_stock') === 'in_stock') {
            $data->whereExists(function($query) use ($storeId) {
                $query->select(DB::raw(1))
                      ->from('product_details')
                      ->whereColumn('product_details.product_id', 'products.id')
                      ->whereNull('product_details.deleted_at')
                      ->where('product_details.qty', '>', 0);
                if ($storeId && $storeId !== 'all') {
                    $query->where('product_details.store_id', $storeId);
                }
            });
        } else if (Req::input('filter_stock') === 'out_of_stock') {
            $data->whereExists(function($query) use ($storeId) {
                $query->select(DB::raw(1))
                      ->from('product_details')
                      ->whereColumn('product_details.product_id', 'products.id')
                      ->whereNull('product_details.deleted_at')
                      ->where('product_details.qty', '<=', 0);
                if ($storeId && $storeId !== 'all') {
                    $query->where('product_details.store_id', $storeId);
                }
            });
        }

        if (Req::input('filter_minus_stock') == 1) {
            $data->whereExists(function($query) use ($storeId) {
                $query->select(DB::raw(1))
                      ->from('product_details')
                      ->whereColumn('product_details.product_id', 'products.id')
                      ->whereNull('product_details.deleted_at')
                      ->where('product_details.qty', '<', 0);
                if ($storeId && $storeId !== 'all') {
                    $query->where('product_details.store_id', $storeId);
                }
            });
        }

        if (Req::input('filter_marketplace') === 'sudah') {
            $data->where('products.show_in_ecommerce', 1);
        } else if (Req::input('filter_marketplace') === 'belum') {
            $data->where('products.show_in_ecommerce', 0);
        }

        if (Req::input('filter_incomplete_cogm') == 1) {
            $data->whereExists(function($query) use ($storeId) {
                $query->select(DB::raw(1))
                      ->from('product_details')
                      ->whereColumn('product_details.product_id', 'products.id')
                      ->whereNull('product_details.deleted_at')
                      ->where(function($q) {
                          $q->whereNull('product_details.cogm')->orWhere('product_details.cogm', 0);
                      });
                if ($storeId && $storeId !== 'all') {
                    $query->where('product_details.store_id', $storeId);
                }
            });
        }

        if (Req::input('filter_incomplete_weight') == 1) {
            $data->whereExists(function($query) use ($storeId) {
                $query->select(DB::raw(1))
                      ->from('product_details')
                      ->whereColumn('product_details.product_id', 'products.id')
                      ->whereNull('product_details.deleted_at')
                      ->where(function($q) {
                          $q->whereNull('product_details.weight')->orWhere('product_details.weight', 0);
                      });
                if ($storeId && $storeId !== 'all') {
                    $query->where('product_details.store_id', $storeId);
                }
            });
        }

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
                $btnEdit   = '';
                $btnDelete = '';

                if (RuleHelper::UserHasPermission('update', false) == true) {
                    $btnEdit = "<a href='" . route('product.basicInformation', ['id' => $data->id]) . "' class='btn btn-sm btn-soft-secondary btnEdit' title='Edit'><i class='las la-edit '></i></a>";
                }

                if (RuleHelper::UserHasPermission('delete', false) == true) {
                    $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='" . $data->id . "' title='Delete'><i class='las la-trash '></i></a>";
                }

                return $btnEdit . ' ' . $btnDelete;
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->editColumn('top_pick', function ($data) {
                $checked = $data->top_pick == 1 ? 'checked' : '';
                return '<div class="form-check form-switch form-switch-md" dir="ltr">
                            <input type="checkbox" class="form-check-input" onclick="toggleProductStatus(this, \''.$data->id.'\', \'top_pick\')" '.$checked.'>
                        </div>';
            })
            ->editColumn('show_in_ecommerce', function ($data) {
                $checked = $data->show_in_ecommerce == 1 ? 'checked' : '';
                return '<div class="form-check form-switch form-switch-md" dir="ltr">
                            <input type="checkbox" class="form-check-input" onclick="toggleProductStatus(this, \''.$data->id.'\', \'show_in_ecommerce\')" '.$checked.'>
                        </div>';
            })
            ->addColumn('status_foto', function ($data) {
                $contentPaths = json_decode($data->content_paths, true);
                $hasMainPic = false;
                if (is_array($contentPaths)) {
                    foreach ($contentPaths as $cp) {
                        if (is_array($cp) && isset($cp['name']) && $cp['name'] === 'main_pic' && !empty($cp['path'])) {
                            // Cek file benar-benar ada di storage
                            if (Storage::disk('public')->exists($cp['path'])) {
                                $hasMainPic = true;
                            }
                            break;
                        } elseif (is_string($cp) && strpos($cp, 'main_pic') !== false) {
                            $hasMainPic = true;
                            break;
                        }
                    }
                }
                
                if ($hasMainPic) {
                    return '<span class="badge bg-success" title="Foto Tersedia"><i class="las la-image"></i> Ada</span>';
                } else {
                    return '<span class="badge bg-danger" title="Foto Tidak Tersedia"><i class="las la-image"></i> Kosong</span>';
                }
            })
            ->addColumn('sku_status', function ($data) {
                if ($data->total_sku > 0 && $data->filled_sku == $data->total_sku) {
                    return '<span class="badge bg-success">' . $data->filled_sku . ' / ' . $data->total_sku . ' SKU</span>';
                } elseif ($data->filled_sku > 0) {
                    return '<span class="badge bg-warning">' . $data->filled_sku . ' / ' . $data->total_sku . ' SKU</span>';
                } else {
                    return '<span class="badge bg-danger">' . $data->filled_sku . ' / ' . $data->total_sku . ' SKU</span>';
                }
            })
            ->filterColumn('deleted_at', function ($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) use ($storeId) {
                if (request()->has('title') && ! is_null(request('title'))) {
                    $query->where('products.title', 'ilike', "%" . request('title') . "%");
                }
                if (request()->has('sku') && ! is_null(request('sku'))) {
                    $query->whereExists(function($q) use ($storeId) {
                        $q->select(DB::raw(1))
                          ->from('product_details')
                          ->whereColumn('product_details.product_id', 'products.id')
                          ->whereNull('product_details.deleted_at')
                          ->where('product_details.sku', 'ilike', '%' . request('sku') . '%');
                        if ($storeId && $storeId !== 'all') {
                            $q->where('product_details.store_id', $storeId);
                        }
                    });
                }
                if (request()->has('main_category_id') && ! is_null(request('main_category_id'))) {
                    $query->where('categories.main_categories_id', request('main_category_id'));
                }
                if (request()->has('category_id') && ! is_null(request('category_id'))) {
                    $query->where('products.category_id', request('category_id'));
                }
                if (request()->has('top_pick') && ! is_null(request('top_pick'))) {
                    $query->where('products.top_pick', 'ilike', "%" . request('top_pick') . "%");
                }
                if (request()->has('material_id') && ! is_null(request('material_id'))) {
                    $query->where('products.material_id', request('material_id'));
                }
                
                // Handle global search from DataTables
                if (request()->has('search') && isset(request('search')['value']) && !is_null(request('search')['value'])) {
                    $search = request('search')['value'];
                    $query->where(function($q) use ($search) {
                        $q->where('products.title', 'ilike', "%" . $search . "%")
                          ->orWhere('categories.title', 'ilike', "%" . $search . "%")
                          ->orWhere('materials.title', 'ilike', "%" . $search . "%")
                          ->orWhere('main_categories.title', 'ilike', "%" . $search . "%");
                    });
                }
            })
            ->rawColumns(['action', 'checkboxs', 'top_pick', 'show_in_ecommerce', 'status_foto', 'sku_status'])
            ->toJson();
    }

    public function get_datatable_trash()
    {
        RuleHelper::UserHasPermission('trash');
        $data = DB::table('products')->join('categories', 'categories.id', '=', 'products.category_id')
            ->select([
                'products.id',
                'products.title',
                'products.top_pick',
                'categories.id as category',
                'categories.title as category_title',
                'products.created_at',
                'products.updated_at',
            ])->whereNotNull('products.deleted_at')
            ->orderByDesc('products.deleted_at');

        $datatables = DataTables::of($data);

        return $datatables
            ->addColumn('checkboxs', function ($data) {
                return "
                    <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>
                ";
            })
            ->addColumn('action', function ($data) {
                $btnEdit    = "<a href='" . route('product.basicInformation', ['id' => $data->id]) . "' class='btn btn-sm btn-soft-secondary' title='Edit'><i class='las la-edit '></i></a>";
                $btnDelete  = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='" . $data->id . "'><i class='las la-trash'></i> Permanent Delete</a>";
                $btnRestore = "<a href='#' class='btn btn-sm btn-soft-secondary btnRestore' id='" . $data->id . "'><i class='ri-restart-line '></i> Restore</a>";
                return $btnEdit . " " . $btnRestore . " " . $btnDelete;
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
            ->editColumn('top_pick', function ($data) {
                $top_pick = null;
                if ('0' == $data->top_pick) {
                    $top_pick = 'Tidak';
                }if ('1' == $data->top_pick) {
                    $top_pick = 'Ya';
                }return $top_pick;
            })
            ->filterColumn('deleted_at', function ($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('title') && ! is_null(request('title'))) {
                    $query->where('products.title', 'ilike', "%" . request('title') . "%");
                }
                if (request()->has('category_id') && ! is_null(request('category_id'))) {
                    $query->where('products.category_id', request('category_id'));
                }
                if (request()->has('top_pick') && ! is_null(request('top_pick'))) {
                    $query->where('products.top_pick', 'ilike', "%" . request('top_pick') . "%");
                }

                // Handle global search from DataTables
                if (request()->has('search') && isset(request('search')['value']) && !is_null(request('search')['value'])) {
                    $search = request('search')['value'];
                    $query->where(function($q) use ($search) {
                        $q->where('products.title', 'ilike', "%" . $search . "%")
                          ->orWhere('categories.title', 'ilike', "%" . $search . "%");
                    });
                }
            })
            ->rawColumns(['action', 'checkboxs'])
            ->toJson();
    }

    public function delete(Request $request)
    {
        RuleHelper::UserHasPermission('delete');

        $table          = "products";
        $column_visible = "title";
        $route          = "product.destroy";

        return $this->delete_lib->deleteConfirmation($request, $table, $column_visible, $route);
    }

    public function destroy($delete_type)
    {
        RuleHelper::UserHasPermission('delete');
        $user              = Auth::user();
        $dependencies_data = session('dependencies_data', []);
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

                $redirect_url = "product/trash";
                $product      = Product::findOrFail($row['id']);

                $product->forceDelete();
                $index++;
            }

            if ($delete_type == "softdelete") {
                $redirect_url = "product";

                DB::table($row['table'])
                    ->where('id', $row['id'])
                    ->update(['deleted_at' => date('Y-m-d H:i:s'), 'deleted_by' => $user->email, 'restored_at' => null, 'restored_by' => null]);

                $index++;
            }
        }

        Session::flash('flash_message', $index . ' Record[s] deleted successfully');

        return redirect($redirect_url);
    }

    public function restore(Request $request)
    {
        RuleHelper::UserHasPermission('trash');

        if ($request->id_to_be_restored == "") {
            $request->id_to_be_restored = [];
        }

        $data = DB::table('products')
            ->whereIn('id', $request->id_to_be_restored)
            ->orderBy('deleted_at', 'desc')
            ->get();

        $this->data['index']       = 0;
        $this->data['data']        = $data;
        $this->data['route']       = 'product.restore_action';
        $this->data['column_name'] = 'title';

        Session::flash('dependencies_data', $data);

        return view('backend.includes.restore_modal', $this->data);
    }

    public function restore_action(Request $request)
    {
        RuleHelper::UserHasPermission('trash');

        $user              = Auth::user();
        $dependencies_data = session('dependencies_data');
        $index             = 0;
        foreach ($dependencies_data as $row) {
            Product::where('id', $row->id)
                ->update(
                    [
                        'deleted_at'  => null, 'deleted_by' => null,
                        'restored_at' => date('Y-m-d H:i:s'),
                        'restored_by' => $user->email,
                    ]
                );

            $index++;
        }

        Session::flash('flash_message', $index . ' Record[s] restored successfully');

        return redirect('product/trash');
    }

    public function get_category_by_main($mainCategoryId)
    {
        $categories = Category::where('main_categories_id', $mainCategoryId)
            ->whereNull('deleted_at')
            ->get(['id', 'title']);

        return response()->json($categories);
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

        return Excel::download(new ProductExport($columnExportData['columnDepedencies'], $columnExportData['columnExtraAttribute']), 'products.xlsx');
    }

    private function createOrUpdateProductDetailOnEachStore($productId, $stores, $variants, $colors)
    {
        // === Simpan / Update Variants ===
        $variantIds = [];
        foreach ($variants as $variant) {
            $model = Variant::updateOrCreate(
                [
                    'id'         => $variant['id'] ?? null,
                    'product_id' => $productId,
                ],
                [
                    'name' => $variant['name'],
                ]
            );
            $variantIds[] = $model->id;
        }

        // Kalau kosong, tetap butuh kombinasi null
        if (empty($variantIds)) {
            $variantIds[] = null;
        }

        // === Simpan / Update Colors ===
        $colorIds = [];
        foreach ($colors as $color) {
            $model = Color::updateOrCreate(
                [
                    'id'         => $color['id'] ?? null,
                    'product_id' => $productId,
                ],
                [
                    'label' => $color['label'],
                    'hexa'  => $color['hexa'],
                ]
            );
            $colorIds[] = $model->id;
        }

        if (empty($colorIds)) {
            $colorIds[] = null;
        }

        // === Buat / Update Product Details ===
        foreach ($stores as $store) {
            foreach ($variantIds as $variantId) {
                foreach ($colorIds as $colorId) {
                    ProductDetail::firstOrCreate([
                        'product_id' => $productId,
                        'store_id'   => $store->id,
                        'variant_id' => $variantId,
                        'color_id'   => $colorId,
                    ]);
                }
            }
        }
    }

    private function deleteProductDetailOnEachStore($deletedVariantIds, $deletedColorIds)
    {
        if (empty($deletedVariantIds) && empty($deletedColorIds)) {
            return;
        }

        Color::whereIn('id', $deletedColorIds)->delete();
        Variant::whereIn('id', $deletedVariantIds)->delete();

        ProductDetail::whereIn('variant_id', $deletedVariantIds)
            ->orWhereIn('color_id', $deletedColorIds)
            ->delete();
    }

    private function checkDuplicatedSku($details): array
    {
        // Filter hanya item yang ada SKU-nya
        $itemsWithSku = array_filter($details, fn($item) => ! empty($item['sku']));

        if (empty($itemsWithSku)) {
            return [];
        }

        // Ambil semua product details yang diperlukan dalam satu query
        $productDetailIds = array_column($itemsWithSku, 'id');
        $currentDetails   = ProductDetail::whereIn('id', $productDetailIds)
            ->select('id', 'store_id')
            ->get()
            ->keyBy('id');

        $skuByStore     = [];
        $skusToCheck    = [];
        $duplicatedSkus = [];

        foreach ($itemsWithSku as $item) {
            $currentDetail = $currentDetails->get($item['id']);
            if (! $currentDetail) {
                continue;
            }

            $storeId = $currentDetail->store_id;
            $sku     = $item['sku'];

            // Cek duplikat dalam request
            if (isset($skuByStore[$storeId][$sku])) {
                $duplicatedSkus[] = [
                    'store_id' => $storeId,
                    'sku'      => $sku,
                ];
            }

            $skuByStore[$storeId][$sku] = $item['id'];
            $skusToCheck[$storeId][]    = $sku;
        }

        // Cek duplikat di database per store
        foreach ($skusToCheck as $storeId => $skus) {
            $excludeIds = array_values($skuByStore[$storeId]);

            $existingSkus = ProductDetail::where('store_id', $storeId)
                ->whereIn('sku', $skus)
                ->whereNotIn('id', $excludeIds)
                ->whereNull('deleted_at')
                ->pluck('sku')
                ->toArray();

            foreach ($existingSkus as $sku) {
                $duplicatedSkus[] = [
                    'store_id' => $storeId,
                    'sku'      => $sku,
                ];
            }
        }

        // Hapus duplikat dari hasil
        $duplicatedSkus = collect($duplicatedSkus)
            ->unique(fn($item) => $item['store_id'] . '|' . $item['sku'])
            ->values()
            ->toArray();

        return $duplicatedSkus;
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

    public function download_image_template(Request $request)
    {
        $downloadType = $request->input('download_type', 'all');
        
        $query = Product::whereNull('deleted_at');

        $products = $query->get();

        if ($downloadType === 'empty_only') {
            $products = $products->filter(function($product) {
                $contentPaths = collect($product->content_paths ?? []);
                
                // Jika belum punya array content_paths
                if ($contentPaths->isEmpty()) {
                    return true;
                }
                
                // Cari apakah sudah punya main_pic
                $hasMainPic = $contentPaths->contains(function($img) {
                    if (is_array($img) && isset($img['name']) && $img['name'] === 'main_pic' && !empty($img['path'])) {
                        return true;
                    }
                    if (is_string($img) && strpos($img, 'main_pic') !== false) {
                        return true;
                    }
                    return false;
                });

                // Tampilkan hanya jika belum punya main_pic
                return !$hasMainPic;
            });
        }

        $filename = "product_image_template_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'product_id', 
            'product_name', 
            'main_pic', 
            'hover_pic', 
            'pic_1', 'pic_2', 'pic_3', 'pic_4', 'pic_5', 'pic_6', 'pic_7', 'pic_8'
        ];

        $callback = function() use($products, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($products as $product) {
                $row = [
                    $product->id,
                    $product->title,
                    '', '', '', '', '', '', '', '', '', ''
                ];
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function toggle_status(Request $request)
    {
        RuleHelper::UserHasPermission('update');

        $field = $request->field;
        
        if (!in_array($field, ['top_pick', 'show_in_ecommerce'])) {
            return response()->json(['success' => false, 'message' => 'Field tidak valid'], 400);
        }

        $status = (int) $request->status;

        DB::table('products')
            ->where('id', $request->id)
            ->update([$field => $status]);
        
        return response()->json(['success' => true, 'message' => 'Status berhasil diperbarui']);
    }
    public function sync_old_sku_view()
    {
        $userId = auth()->id() ?? 0;
        $tempFile = storage_path("app/temp_sku_sync_{$userId}.json");
        if (file_exists($tempFile)) {
            $this->data['preview'] = json_decode(file_get_contents($tempFile), true);
        }
        return view('backend.pages.product.sync-sku', $this->data);
    }

    public function sync_old_sku_preview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, "r");
        
        // Cek delimiter yang paling banyak di baris pertama
        $firstLine = fgets($handle);
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        rewind($handle);
        
        $headers = fgetcsv($handle, 0, $delimiter);
        if (!$headers) {
            return redirect()->back()->with('error_message', 'Gagal membaca file CSV.');
        }

        $headers = array_map('trim', $headers);
        $headers = array_map('strtolower', $headers);
        
        $namaIdx = -1;
        $kodeIdx = -1;
        
        foreach ($headers as $i => $h) {
            if (str_contains($h, 'nama produk') || str_contains($h, 'nama') || $h === 'nama produk' || $h === 'name') $namaIdx = $i;
            if (str_contains($h, 'kode produk') || str_contains($h, 'kode') || str_contains($h, 'sku')) $kodeIdx = $i;
        }
        
        if ($namaIdx === -1 || $kodeIdx === -1) {
            fclose($handle);
            return redirect()->back()->with('error_message', 'Kolom NAMA PRODUK atau KODE PRODUK tidak ditemukan di CSV.');
        }
        
        // Load all unique Product Details
        $allDetails = \App\Models\ProductDetail::with(['productWithoutScope', 'variant', 'color'])
            ->get()
            ->unique(function($item) {
                return $item->product_id . '-' . $item->variant_id . '-' . $item->color_id;
            });
        
        $preview = [];
        
        while (($data = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
            if (count($data) <= max($namaIdx, $kodeIdx)) continue;
            
            $oldName = trim($data[$namaIdx]);
            $oldSku = trim($data[$kodeIdx]);
            if (empty($oldName) || empty($oldSku)) continue;
            
            // Find match
            $matchedDetail = null;
            $bestScore = -1;
            
            foreach ($allDetails as $detail) {
                // Check if SKU is already identical (perfect match)
                if (!empty($detail->sku) && $detail->sku === $oldSku) {
                    $matchedDetail = $detail;
                    break;
                }
                
                $productTitle = $detail->productWithoutScope ? $detail->productWithoutScope->title : '';
                if (empty($productTitle)) continue;
                
                // Check if oldName contains product title
                if (stripos($oldName, $productTitle) !== false) {
                    $variantName = $detail->variant ? $detail->variant->name : '';
                    $colorLabel = $detail->color ? $detail->color->label : '';
                    
                    $score = strlen($productTitle);
                    $matchVariant = true;
                    
                    // If variant or color exist, they must be part of the oldName
                    if ($variantName && stripos($oldName, $variantName) === false) $matchVariant = false;
                    if ($colorLabel && stripos($oldName, $colorLabel) === false) $matchVariant = false;
                    
                    if ($matchVariant) {
                        if ($variantName) $score += strlen($variantName);
                        if ($colorLabel) $score += strlen($colorLabel);
                        
                        if ($score > $bestScore) {
                            $bestScore = $score;
                            $matchedDetail = $detail;
                        }
                    }
                }
            }
            
            $preview[] = [
                'old_name' => $oldName,
                'old_sku' => $oldSku,
                'matched_product' => $matchedDetail ? $matchedDetail->productWithoutScope->title : '-',
                'matched_variant' => $matchedDetail && $matchedDetail->variant ? $matchedDetail->variant->name : '-',
                'matched_color' => $matchedDetail && $matchedDetail->color ? $matchedDetail->color->label : '-',
                'matched_product_id' => $matchedDetail ? $matchedDetail->product_id : null,
                'matched_variant_id' => $matchedDetail ? $matchedDetail->variant_id : null,
                'matched_color_id' => $matchedDetail ? $matchedDetail->color_id : null,
                'is_match' => $matchedDetail ? true : false
            ];
        }
        fclose($handle);
        
        session(['sync_old_sku_data' => $preview]);
        
        $userId = auth()->id() ?? 0;
        $tempFile = storage_path("app/temp_sku_sync_{$userId}.json");
        file_put_contents($tempFile, json_encode($preview));
        
        $this->data['preview'] = $preview;
        return view('backend.pages.product.sync-sku', $this->data);
    }

    public function search_product_ajax(Request $request)
    {
        $keyword = $request->get('q', '');
        $keywords = explode(' ', strtolower(trim($keyword)));
        
        $query = \App\Models\ProductDetail::with(['productWithoutScope', 'variant', 'color']);
        
        foreach ($keywords as $kw) {
            if (empty($kw)) continue;
            
            $query->where(function ($q) use ($kw) {
                $q->whereHas('productWithoutScope', function ($q2) use ($kw) {
                    $q2->where('title', 'ilike', '%' . $kw . '%');
                })
                ->orWhereHas('variant', function ($q2) use ($kw) {
                    $q2->where('name', 'ilike', '%' . $kw . '%');
                })
                ->orWhereHas('color', function ($q2) use ($kw) {
                    $q2->where('label', 'ilike', '%' . $kw . '%');
                });
            });
        }
        
        $products = $query->limit(20)->get();
            
        $results = [];
        foreach ($products as $detail) {
            $productTitle = $detail->productWithoutScope ? $detail->productWithoutScope->title : '-';
            $variantName = $detail->variant ? $detail->variant->name : '';
            $colorLabel = $detail->color ? $detail->color->label : '';
            
            $text = $productTitle;
            if ($variantName) $text .= ' | Varian: ' . $variantName;
            if ($colorLabel) $text .= ' | Warna: ' . $colorLabel;
            
            $results[] = [
                'id' => $detail->id, // not used for saving, just for select2
                'product_id' => $detail->product_id,
                'variant_id' => $detail->variant_id,
                'color_id' => $detail->color_id,
                'text' => $text,
                'product_title' => $productTitle,
                'variant_name' => $variantName ?: '-',
                'color_label' => $colorLabel ?: '-',
            ];
        }
        
        return response()->json(['results' => $results]);
    }

    public function sync_old_sku_process_single(Request $request)
    {
        $productId = $request->input('product_id');
        $variantId = $request->input('variant_id');
        $colorId = $request->input('color_id');
        $oldSku = $request->input('old_sku');

        if (!$productId || !$oldSku) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak lengkap.'], 400);
        }

        $query = \App\Models\ProductDetail::where('product_id', $productId);
        
        if (!empty($variantId)) $query->where('variant_id', $variantId);
        else $query->whereNull('variant_id');
        
        if (!empty($colorId)) $query->where('color_id', $colorId);
        else $query->whereNull('color_id');
        
        $updated = $query->update(['sku' => $oldSku]);
        
        if ($updated) {
            $userId = auth()->id() ?? 0;
            $tempFile = storage_path("app/temp_sku_sync_{$userId}.json");
            if (file_exists($tempFile)) {
                $preview = json_decode(file_get_contents($tempFile), true);
                if (is_array($preview)) {
                    foreach ($preview as &$item) {
                        if ($item['old_sku'] === $oldSku) {
                            $item['is_processed'] = true;
                            $item['matched_product_id'] = $productId;
                            $item['matched_variant_id'] = $variantId;
                            $item['matched_color_id'] = $colorId;
                        }
                    }
                    file_put_contents($tempFile, json_encode($preview));
                }
            }
            return response()->json(['status' => 'success', 'message' => 'SKU berhasil diperbarui.']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Produk detail tidak ditemukan.'], 404);
        }
    }

    public function sync_old_sku_process(Request $request)
    {
        $syncDataStr = $request->input('sync_data');
        if (!$syncDataStr) {
            return redirect()->route('import.syncOldSkuView')->with('error_message', 'Data pratinjau tidak ditemukan atau tidak ada data yang dipilih. Silakan coba lagi.');
        }
        
        $syncData = json_decode($syncDataStr, true);
        if (!is_array($syncData)) {
            return redirect()->route('import.syncOldSkuView')->with('error_message', 'Format data tidak valid.');
        }
        
        $updatedCount = 0;
        foreach ($syncData as $row) {
            if (!empty($row['product_id']) && !empty($row['old_sku'])) {
                $query = \App\Models\ProductDetail::where('product_id', $row['product_id']);
                
                if (!empty($row['variant_id'])) $query->where('variant_id', $row['variant_id']);
                else $query->whereNull('variant_id');
                
                if (!empty($row['color_id'])) $query->where('color_id', $row['color_id']);
                else $query->whereNull('color_id');
                
                $query->update(['sku' => $row['old_sku']]);
                $updatedCount++;
            }
        }
        
        $userId = auth()->id() ?? 0;
        $tempFile = storage_path("app/temp_sku_sync_{$userId}.json");
        if (file_exists($tempFile)) {
            $preview = json_decode(file_get_contents($tempFile), true);
            if (is_array($preview)) {
                $processedSkus = array_column($syncData, 'old_sku');
                foreach ($preview as &$item) {
                    if (in_array($item['old_sku'], $processedSkus)) {
                        $item['is_processed'] = true;
                        // Find the matching data from syncData to update matched IDs if needed
                        foreach ($syncData as $sd) {
                            if ($sd['old_sku'] === $item['old_sku']) {
                                $item['matched_product_id'] = $sd['product_id'];
                                $item['matched_variant_id'] = $sd['variant_id'] ?? null;
                                $item['matched_color_id'] = $sd['color_id'] ?? null;
                                break;
                            }
                        }
                    }
                }
                file_put_contents($tempFile, json_encode($preview));
            }
        }
        
        session()->forget('sync_old_sku_data');
        return redirect()->route('product.index')->with('flash_message', "Berhasil mengupdate SKU untuk $updatedCount kombinasi produk.");
    }
    public function copy_product_details_view()
    {
        $this->data['stores'] = Store::whereNull('deleted_at')->get(['id', 'name']);
        return view('backend.pages.product.copy-product-details', $this->data);
    }

    public function copy_product_details_process(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'source_store_id' => 'required|exists:stores,id',
            'target_store_id' => 'required|exists:stores,id|different:source_store_id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $sourceStoreId = $request->input('source_store_id');
        $targetStoreId = $request->input('target_store_id');

        $sourceDetails = ProductDetail::where('store_id', $sourceStoreId)
            ->whereNull('deleted_at')
            ->get();

        $copiedCount = 0;
        $overwrittenCount = 0;

        DB::transaction(function () use ($sourceDetails, $targetStoreId, &$copiedCount, &$overwrittenCount) {
            foreach ($sourceDetails as $source) {
                $existing = ProductDetail::where('product_id', $source->product_id)
                    ->where('store_id', $targetStoreId)
                    ->where(function($q) use ($source) {
                        if ($source->variant_id) $q->where('variant_id', $source->variant_id);
                        else $q->whereNull('variant_id');
                    })
                    ->where(function($q) use ($source) {
                        if ($source->color_id) $q->where('color_id', $source->color_id);
                        else $q->whereNull('color_id');
                    })
                    ->whereNull('deleted_at')
                    ->first();

                if ($existing) {
                    $existing->update([
                        'sku' => $source->sku,
                        'price' => $source->price,
                        'additional_price' => $source->additional_price,
                        'cogm' => $source->cogm,
                        'weight' => $source->weight,
                    ]);
                    $overwrittenCount++;
                } else {
                    ProductDetail::create([
                        'product_id' => $source->product_id,
                        'store_id' => $targetStoreId,
                        'variant_id' => $source->variant_id,
                        'color_id' => $source->color_id,
                        'sku' => $source->sku,
                        'price' => $source->price,
                        'additional_price' => $source->additional_price,
                        'cogm' => $source->cogm,
                        'weight' => $source->weight,
                        'qty' => 0,
                    ]);
                    $copiedCount++;
                }
            }
        });

        $sourceStore = Store::find($sourceStoreId);
        $targetStore = Store::find($targetStoreId);

        return redirect()->back()->with('flash_message', 
            "Berhasil menyalin produk dari \"{$sourceStore->name}\" ke \"{$targetStore->name}\". " .
            "Disalin: {$copiedCount} | Ditimpa: {$overwrittenCount} | Total: " . ($copiedCount + $overwrittenCount)
        );
    }

    public function asset_breakdown(Request $request)
    {
        // Handle AJAX request for grand total
        if ($request->ajax() && $request->header('X-Grand-Total') == '1') {
            $query = DB::table('product_details')
                ->whereNull('product_details.deleted_at')
                ->join('products', 'products.id', '=', 'product_details.product_id')
                ->whereNull('products.deleted_at');

            if ($request->has('store_id') && !is_null($request->store_id) && $request->store_id !== '') {
                $query->where('product_details.store_id', $request->store_id);
            }

            $total = $query->selectRaw('SUM(product_details.cogm * product_details.qty) as total')->value('total') ?? 0;

            return response()->json([
                'grand_total' => 'Rp' . number_format($total, 0, ',', '.')
            ]);
        }

        $this->data['page_title'] = 'Rincian Nilai Aset';

        $authUser = Auth::user();
        $userStore = json_decode($authUser->store_id ?? '[]', true);
        $this->data['stores'] = Store::whereIn('id', $userStore)->select(['id', 'name'])->get();

        $activeStore = session()->get('store');
        $this->data['activeStoreId'] = $activeStore ? $activeStore['id'] : null;

        // Data per toko untuk Donut Chart + Summary Cards
        $assetPerStore = DB::table('product_details')
            ->join('products', 'products.id', '=', 'product_details.product_id')
            ->leftJoin('stores', 'stores.id', '=', 'product_details.store_id')
            ->whereNull('product_details.deleted_at')
            ->whereNull('products.deleted_at')
            ->select([
                'stores.name as store_name',
                DB::raw('SUM(product_details.cogm * product_details.qty) as total_asset'),
                DB::raw('SUM(product_details.qty) as total_qty'),
                DB::raw('COUNT(DISTINCT product_details.id) as total_items'),
            ])
            ->groupBy('stores.id', 'stores.name')
            ->orderByDesc('total_asset')
            ->get();

        $this->data['assetPerStore'] = $assetPerStore;

        // Top 10 produk dengan nilai aset tertinggi
        $top10Query = DB::table('product_details')
            ->join('products', 'products.id', '=', 'product_details.product_id')
            ->whereNull('product_details.deleted_at')
            ->whereNull('products.deleted_at')
            ->select([
                'products.title as product_name',
                DB::raw('SUM(product_details.cogm * product_details.qty) as total_asset'),
            ])
            ->groupBy('products.id', 'products.title')
            ->orderByDesc('total_asset')
            ->limit(10)
            ->get();

        $this->data['top10Products'] = $top10Query;

        return view('backend.pages.product.asset_breakdown', $this->data);
    }

    public function asset_breakdown_datatable()
    {
        $data = DB::table('product_details')
            ->join('products', 'products.id', '=', 'product_details.product_id')
            ->leftJoin('variants', 'variants.id', '=', 'product_details.variant_id')
            ->leftJoin('colors', 'colors.id', '=', 'product_details.color_id')
            ->leftJoin('stores', 'stores.id', '=', 'product_details.store_id')
            ->select([
                'product_details.id',
                'products.title as product_name',
                'variants.name as variant_name',
                'colors.label as color_name',
                'stores.name as store_name',
                'product_details.sku',
                'product_details.cogm',
                'product_details.qty',
                DB::raw('(product_details.cogm * product_details.qty) as total_asset'),
            ])
            ->whereNull('product_details.deleted_at')
            ->whereNull('products.deleted_at');

        // Removed the invalid direct store_id where clause on non-joined product_details table

        return DataTables::of($data)
            ->addColumn('number', function ($data) {
                return '';
            })
            ->editColumn('cogm', function ($data) {
                return 'Rp' . number_format($data->cogm ?? 0, 0, ',', '.');
            })
            ->editColumn('total_asset', function ($data) {
                return 'Rp' . number_format($data->total_asset ?? 0, 0, ',', '.');
            })
            ->rawColumns([])
            ->toJson();
    }

    public function bulk_cogm_view()
    {
        if (!in_array(session()->get('current_role')->name, ['super', 'admin'])) {
            abort(403, 'Akses Ditolak.');
        }
        $this->data['page_title'] = 'Bulk Input COGM';
        $this->data['stores'] = \App\Models\Store::whereNull('deleted_at')->orderBy('name')->get(['id', 'name']);
        return view('backend.pages.product.bulk_cogm', $this->data);
    }

    public function bulk_cogm_lookup(Request $request)
    {
        if (!in_array(session()->get('current_role')->name, ['super', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak.']);
        }
        $idsRaw = $request->input('ids', '');
        $nameStr = $request->input('name', '');

        // Split by newline, comma, or space and clean up
        $skus = collect(preg_split('/[\r\n\t,]+/', $idsRaw))
            ->map(fn($s) => trim($s))
            ->filter(fn($s) => $s !== '')
            ->unique()
            ->values();

        // Split names by newline or comma (since space is part of the name)
        $names = collect(preg_split('/[\r\n,]+/', $nameStr))
            ->map(fn($s) => trim($s))
            ->filter(fn($s) => $s !== '')
            ->unique()
            ->values();

        if ($skus->isEmpty() && $names->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada SKU atau Nama yang dimasukkan.']);
        }

        $query = DB::table('product_details')
            ->join('products', 'products.id', '=', 'product_details.product_id')
            ->leftJoin('variants', 'variants.id', '=', 'product_details.variant_id')
            ->leftJoin('colors', 'colors.id', '=', 'product_details.color_id')
            ->leftJoin('stores', 'stores.id', '=', 'product_details.store_id')
            ->whereNull('product_details.deleted_at');

        if ($request->has('store_id') && $request->store_id !== 'all') {
            $query->where('product_details.store_id', $request->store_id);
        }

        $query->where(function ($q) use ($skus, $names) {
                if ($skus->isNotEmpty()) {
                    $q->whereIn('product_details.sku', $skus->toArray());
                }
                if ($names->isNotEmpty()) {
                    $q->orWhere(function ($subQ) use ($names) {
                        foreach ($names as $name) {
                            $words = explode(' ', $name);
                            if (count($words) >= 2) {
                                // Ambil 2 kata pertama
                                $twoWords = $words[0] . ' ' . $words[1];
                                $subQ->orWhere('products.title', 'ILIKE', '%' . $twoWords . '%');
                            } else {
                                $subQ->orWhere('products.title', 'ILIKE', '%' . $name . '%');
                            }
                        }
                    });
                }
            })
            ->select([
                'product_details.id',
                'products.title as product_name',
                'variants.name as variant_name',
                'colors.label as color_name',
                'stores.name as store_name',
                'product_details.sku',
                'product_details.cogm',
                'product_details.price',
                'product_details.qty',
            ]);

        $results = $query->get();

        if ($names->isNotEmpty()) {
            foreach ($results as $item) {
                $item->matched_name_input = '';
                foreach ($names as $name) {
                    $words = explode(' ', $name);
                    if (count($words) >= 2) {
                        $twoWords = $words[0] . ' ' . $words[1];
                        if (stripos($item->product_name, $twoWords) !== false) {
                            $item->matched_name_input = $name;
                            break;
                        }
                    } else {
                        if (stripos($item->product_name, $name) !== false) {
                            $item->matched_name_input = $name;
                            break;
                        }
                    }
                }
            }
        }

        $skuOrder = $skus->flip();
        $nameOrder = $names->flip();

        $results = $results->sortBy(function ($item) use ($skuOrder, $nameOrder) {
            // Urutkan berdasarkan SKU input pertama
            if ($item->sku && $skuOrder->has($item->sku)) {
                return $skuOrder->get($item->sku);
            }
            // Jika pencarian menggunakan nama, letakkan setelah SKU berdasarkan urutan nama input
            if (isset($item->matched_name_input) && $item->matched_name_input !== '' && $nameOrder->has($item->matched_name_input)) {
                return $skuOrder->count() + $nameOrder->get($item->matched_name_input);
            }
            return 999999;
        })->values();

        $foundSkus = $results->pluck('sku')->unique()->toArray();
        $notFoundSkus = $skus->diff($foundSkus)->values();

        return response()->json([
            'success' => true,
            'data' => $results,
            'not_found' => $notFoundSkus,
            'found_count' => $results->count(),
            'not_found_count' => count($notFoundSkus),
        ]);
    }

    public function bulk_cogm_save(Request $request)
    {
        if (!in_array(session()->get('current_role')->name, ['super', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'Akses Ditolak.']);
        }
        $items = $request->input('items', []);

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data untuk disimpan.']);
        }

        $updatedCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($items as $item) {
                $id = $item['id'] ?? null;
                $cogm = $item['cogm'] ?? null;
                $price = $item['price'] ?? null;
                $sku = $item['sku'] ?? null;

                if (!$id || ($cogm === null && $sku === null && $price === null)) {
                    $skippedCount++;
                    continue;
                }

                $updateData = ['updated_at' => now()];
                if ($cogm !== null) $updateData['cogm'] = (float) $cogm;
                if ($price !== null) $updateData['price'] = (float) $price;
                if ($sku !== null) $updateData['sku'] = $sku;

                DB::table('product_details')
                    ->where('id', $id)
                    ->whereNull('deleted_at')
                    ->update($updateData);

                $updatedCount++;
            }
            DB::commit();

            // Clear cache
            Cache::forget('product_summary_stats');

            return response()->json([
                'success' => true,
                'message' => "Berhasil update {$updatedCount} COGM. Dilewati: {$skippedCount}.",
                'updated' => $updatedCount,
                'skipped' => $skippedCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

}
