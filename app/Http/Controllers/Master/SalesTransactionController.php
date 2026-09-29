<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use App\Models\SalesTransaction;
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
use App\Exports\SalesTransactionExport;
use App\Models\Customer;
use App\Models\MainCategory;
use App\Models\PaymentType;
use App\Models\SalePerson;
use App\Models\SalesTransactionItem;
use App\Models\ProductDetail;
use App\Models\ProductStockMovement;
use App\Models\Setting;
use Request as Req;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesTransactionController extends BackendController
{
    function __construct()
    {
        parent::__construct();
        $this->data['page_title'] = "Sale Transaction Page";
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

        $this->data['sale_transactions_count'] = SalesTransaction::whereNotNull('deleted_at')
                                                    ->where('store_id', session('store.id'))
                                                    ->count();


        $this->data['url'] = 'sales-transaction';
        $tgl = null;
        $dateNow = Carbon::now()->toDateString();
        $this->data['dateNow'] = $this->formatDate($dateNow, $tgl, 3);

        return view('backend.pages.sale_transaction.index', $this->data);
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

        // Semua MainCategory (title)
        $this->data['main_category_titles'] = MainCategory::query()
            ->whereNull('deleted_at')
            ->orderBy('title')
            ->pluck('title', 'id');

        $storeId = session('store.id');

        // Get Produk + kategori + content_paths image
        $products = DB::table('products')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->whereNull('products.deleted_at')
            ->orderByDesc('products.top_pick')
            ->orderBy('products.title')
            ->limit(100)
            ->get([
                'products.id',
                'products.title AS product_title',
                'categories.title AS category_title',
                'products.content_paths',
                'products.top_pick',
            ]);

        $products = $products->map(function ($row) use ($storeId) {
            // Ambil SEMUA product_detail untuk produk ini (tiap detail sudah berisi diskon terbaik via scope)
            $details = ProductDetail::query()
                ->where('product_id', $row->id)
                ->when($storeId, fn($q) => $q->where('product_details.store_id', $storeId))
                ->withBestActiveDiscountAndFinalPrice()
                ->orderBy('final_price', 'asc') // urutkan berdasarkan harga terendah
                ->get();

            // pilih product_detail dg nominal potongan TERBESAR
            $bestDetail = $details->filter(fn($d) => (float)($d->discount_value ?? 0) > 0)
                ->sortByDesc('discount_value')
                ->first();

            // fallback: final_price > 0 termurah
            if (! $bestDetail) {
                $bestDetail = $details->filter(fn($d) => (float)($d->final_price ?? 0) > 0)
                    ->sortBy('final_price')
                    ->first();
            }

            if ($bestDetail) {
                $row->price                  = (float) ($bestDetail->final_price ?? 0);
                $row->original_price         = (float) ($bestDetail->price ?? 0);
                $row->best_discount_type     = $bestDetail->best_discount_type ?? null;
                $row->best_discount_value    = isset($bestDetail->discount_value) ? (float) $bestDetail->discount_value : null;
                $row->raw_percent_value      = isset($bestDetail->raw_percent_value) ? (float) $bestDetail->raw_percent_value : null;
                $row->raw_nominal_value      = isset($bestDetail->raw_nominal_value) ? (float) $bestDetail->raw_nominal_value : null;
                $row->best_product_detail_id = $bestDetail->id ?? null;
            } else {
                // Tanpa detail/diskon
                $row->price                  = 0.0;
                $row->original_price         = null;
                $row->best_discount_type     = null;
                $row->best_discount_value    = null;
                $row->raw_percent_value      = null;
                $row->raw_nominal_value      = null;
                $row->best_product_detail_id = null;
            }

            // Tambahkan daftar "diskon terbaik per product_detail"
            $row->details_best_discounts = $details->map(function ($d) {
                return [
                    'product_detail_id'   => (string) $d->id,
                    'original_price'      => isset($d->price) ? (float) $d->price : null,
                    'final_price'         => isset($d->final_price) ? (float) $d->final_price : null,
                    'best_discount_type'  => $d->best_discount_type ?? null,
                    'best_discount_value' => isset($d->discount_value) ? (float) $d->discount_value : null,
                    'raw_percent_value'   => isset($d->raw_percent_value) ? (float) $d->raw_percent_value : null,
                    'raw_nominal_value'   => isset($d->raw_nominal_value) ? (float) $d->raw_nominal_value : null,
                ];
            })->values();

            // Gambar
            $paths = json_decode($row->content_paths, true) ?: [];
            $findUrl = function (string $name) use ($paths) {
                $item = collect($paths)->firstWhere('name', $name);
                if (!$item || empty($item['path'])) return null;
                return env('APP_URL') . Storage::url($item['path']);
            };

            $row->main_pic  = $findUrl('main_pic');
            $row->hover_pic = $findUrl('hover_pic');
            $row->pictures  = collect(range(1, 8))
                ->map(fn($i) => $findUrl('pic_' . $i))
                ->filter()
                ->values();

            unset($row->content_paths);

            return $row;
        });

        $this->data['products']       = $products;
        $this->data['product_titles'] = $products->pluck('product_title', 'id');

        // Get Payment Type
        $this->data['payment_types'] = PaymentType::query()
            ->whereNull('deleted_at')
            ->where('status', '!=', 0)
            ->orderBy('payment_type')
            ->get(['id', 'payment_type', 'notes', 'status']);

        // Get Sale Person
        $this->data['sale_people'] = SalePerson::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name', 'note']);

        // Get Customer
        $this->data['customers'] = Customer::query()
            ->whereNull('deleted_at')
            ->orderBy('first_name')
            ->get(['id','first_name','last_name','email','phone']);

        return view('backend.pages.sale_transaction.create', $this->data);
    }

    public function listFilteredProducts(Request $request)
    {
        $mainCategoryId = (string) $request->query('main_category_id', '');
        $mainCategory   = (string) $request->query('main_category', '');
        $keyword        = trim((string) $request->query('q', ''));

        $storeId = session('store.id');

        $q = DB::table('products')
            ->leftJoin('categories as c', 'c.id', '=', 'products.category_id')
            ->leftJoin('main_categories as mc', 'mc.id', '=', 'c.main_categories_id')
            ->whereNull('products.deleted_at');

        if ($mainCategoryId !== '') {
            $q->where('mc.id', $mainCategoryId);
        } elseif ($mainCategory !== '' && strtolower($mainCategory) !== 'all') {
            $q->where('mc.title', $mainCategory);
        }

        // Pencarian case-insensitive & contains
        if ($keyword !== '') {
            $kw   = mb_strtolower($keyword, 'UTF-8');
            $like = '%'.$kw.'%';
            $q->where(function ($w) use ($like) {
                $w->whereRaw('LOWER(products.title) LIKE ?', [$like])
                ->orWhereRaw('LOWER(c.title) LIKE ?',        [$like])
                ->orWhereRaw('LOWER(mc.title) LIKE ?',       [$like]);
            });
        }

        $rows = $q->orderByDesc('products.top_pick')
            ->orderBy('products.title')
            ->limit(100)
            ->get([
                'products.id',
                'products.title AS product_title',
                'products.content_paths',
                'products.top_pick',
                'c.title  AS category_title',
                'mc.title AS main_category_title',
            ]);

        $products = $rows->map(function ($r) use ($storeId) {
            // Ambil SEMUA product_detail dengan diskon terbaik masing-masing
            $details = ProductDetail::query()
                ->where('product_id', $r->id)
                ->when($storeId, fn($q) => $q->where('product_details.store_id', $storeId))
                ->withBestActiveDiscountAndFinalPrice()
                ->orderBy('final_price', 'asc') // Urutkan berdasarkan harga terendah
                ->get();

            // nominal potongan terbesar
            $bestDetail = $details->filter(fn($d) => (float)($d->discount_value ?? 0) > 0)
                ->sortByDesc('discount_value')
                ->first();

            // fallback final_price > 0 termurah
            if (! $bestDetail) {
                $bestDetail = $details->filter(fn($d) => (float)($d->final_price ?? 0) > 0)
                    ->sortBy('final_price')
                    ->first();
            }

            $paths = json_decode($r->content_paths, true) ?: [];
            $find  = function ($name) use ($paths) {
                $item = collect($paths)->firstWhere('name', $name);
                return ($item && !empty($item['path'])) ? url(Storage::url($item['path'])) : null;
            };

            return [
                'id'                     => $r->id,
                'product_title'          => $r->product_title,
                'category_title'         => $r->category_title,

                // render dari detail dg potongan TERBESAR (atau fallback)
                'price'                  => $bestDetail ? (float) $bestDetail->final_price : 0.0,
                'original_price'         => $bestDetail ? (float) ($bestDetail->price ?? 0) : null,
                'best_discount_type'     => $bestDetail->best_discount_type ?? null,
                'best_discount_value'    => isset($bestDetail->discount_value) ? (float) $bestDetail->discount_value : null,
                'raw_percent_value'      => isset($bestDetail->raw_percent_value) ? (float) $bestDetail->raw_percent_value : null,
                'raw_nominal_value'      => isset($bestDetail->raw_nominal_value) ? (float) $bestDetail->raw_nominal_value : null,
                'best_product_detail_id' => $bestDetail->id ?? null,

                // Sertakan semua detail beserta diskon terbaiknya
                'details_best_discounts' => $details->map(function ($d) {
                    return [
                        'product_detail_id'   => (string) $d->id,
                        'original_price'      => isset($d->price) ? (float) $d->price : null,
                        'final_price'         => isset($d->final_price) ? (float) $d->final_price : null,
                        'best_discount_type'  => $d->best_discount_type ?? null,
                        'best_discount_value' => isset($d->discount_value) ? (float) $d->discount_value : null,
                        'raw_percent_value'   => isset($d->raw_percent_value) ? (float) $d->raw_percent_value : null,
                        'raw_nominal_value'   => isset($d->raw_nominal_value) ? (float) $d->raw_nominal_value : null,
                    ];
                })->values(),

                'top_pick'               => (int) ($r->top_pick ?? 0),
                'main_pic'               => $find('main_pic'),
            ];
        })->values();

        return response()->json(['products' => $products]);
    }

    /**
     * Begin Get Data Produk Detail `modal popup add to cart`
     */
    public function productDetail(Request $request)
    {
        \RuleHelper::UserHasPermission('create');

        $productId = (string) $request->query('id', '');

        if ($productId === '') {
            return response()->json(['message' => 'Invalid product id'], 422);
        }

        // Get Produk: hanya id + title
        $productRow = DB::table('products')
            ->whereNull('deleted_at')
            ->where('id', $productId)
            ->first(['id', 'title']);

        if (!$productRow) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product = [
            'id'    => (string) $productRow->id,
            'title' => (string) $productRow->title,
        ];

        // Ambil store_id dari session, untuk filter `produk_detail` per store
        $storeId = session('store.id');

        // Detail kombinasi dengan scope diskon (sku, price, stok, varian, warna)
        $rows = ProductDetail::query()
            ->withBestActiveDiscountAndFinalPrice()
            ->leftJoin('variants as v', 'v.id', '=', 'product_details.variant_id')
            ->leftJoin('colors   as c', 'c.id', '=', 'product_details.color_id')
            ->where('product_details.product_id', $productId)
            ->when($storeId, fn($q) => $q->where('product_details.store_id', $storeId)) // filter store dari session
            ->where('product_details.price', '>', 0)
            ->orderBy('v.name')->orderBy('c.label')
            ->addSelect([
                'product_details.id   as product_detail_id',
                'product_details.sku',
                'product_details.price as base_price',
                'product_details.additional_price',
                'product_details.qty  as quantity_available',
                'product_details.variant_id',
                'product_details.color_id',
                'v.name               as variant_name',
                'c.label              as color_label',
                'c.hexa               as color_hexa',
            ])
            ->get();

        // Bentuk items: sertakan harga asli, final price, dan info diskon
        $items = $rows->map(function ($r) {
            return [
                'product_detail_id'   => (string) $r->product_detail_id,
                'sku'                 => (string) ($r->sku ?? ''),

                // HARGA
                'original_price'      => (float) (($r->base_price ?? 0) + ($r->additional_price ?? 0)), 
                'final_price'         => (float) ($r->final_price ?? (($r->base_price ?? 0) + ($r->additional_price ?? 0))),
                'price'               => (float) ($r->final_price ?? (($r->base_price ?? 0) + ($r->additional_price ?? 0))),

                // DISKON
                'best_discount_type'  => $r->best_discount_type ?? null,
                'best_discount_value' => isset($r->discount_value) ? (float) $r->discount_value : null,
                'raw_percent_value'   => isset($r->raw_percent_value) ? (float) $r->raw_percent_value : null,
                'raw_nominal_value'   => isset($r->raw_nominal_value) ? (float) $r->raw_nominal_value : null,

                // Stok & opsi
                'quantity_available'  => (int)    ($r->quantity_available ?? 0),
                'variant_id'          => (string) ($r->variant_id ?? ''),
                'variant_name'        => (string) ($r->variant_name ?? ''),
                'color_id'            => (string) ($r->color_id ?? ''),
                'color_label'         => (string) ($r->color_label ?? ''),
                'color_hexa'          => $r->color_hexa ? (string) $r->color_hexa : null,
            ];
        });

        // min_price : pakai final_price > 0, kalau tidak ada fallback ke original_price > 0
        $minFinal = $items->where(fn($i) => ($i['final_price'] ?? 0) > 0)->min('final_price');
        $minPrice = $minFinal ?? $items->where(fn($i) => ($i['original_price'] ?? 0) > 0)->min('original_price') ?? 0;

        $variant_options = $items
            ->map(fn($i) => ['id' => $i['variant_id'], 'name' => $i['variant_name']])
            ->filter(fn($v) => $v['id'] !== '' && $v['name'] !== '')
            ->unique(fn($v) => $v['id'])
            ->values()
            ->all();

        $color_options = $items
            ->map(fn($i) => ['id' => $i['color_id'], 'label' => $i['color_label'], 'hexa' => $i['color_hexa']])
            ->filter(fn($c) => $c['id'] !== '' && $c['label'] !== '')
            ->unique(fn($c) => $c['id'])
            ->values()
            ->all();

        return response()->json([
            'product'         => $product,
            'variant_options' => $variant_options,
            'color_options'   => $color_options,
            'items'           => $items->all(),
            'min_price'       => (float) ($minPrice ?? 0), // berbasis final_price bila ada
        ]);
    }

    public function updateStock(Request $request)
    {
        \RuleHelper::UserHasPermission('update');

        $detailId = $request->input('product_detail_id');
        $qty = max(0, (int) $request->input('qty', 0));

        $detail = \App\Models\ProductDetail::find($detailId);
        if (!$detail) {
            return response()->json(['message' => 'Detail not found'], 404);
        }

        $detail->qty = $qty;
        $detail->save();

        return response()->json(['message' => 'Stock updated successfully', 'qty' => $qty]);
    }

    /**
     * GET /sales-transaction/cart/list
     * Kembalikan isi keranjang + ringkasan.
     */
    public function cartList(Request $request)
    {
        \RuleHelper::UserHasPermission('create');

        $storeId     = session('store.id');
        $cartStoreId = Session::get('cart_store_id');

        if (!empty($storeId) && !empty($cartStoreId) && $cartStoreId !== $storeId) {
            Session::forget('sale_cart'); // reset cart list lama bila ganti store
            Session::put('cart_store_id', $storeId); // set penanda baru
        }

        $lines = array_values(Session::get('sale_cart', []));

        $units = 0;
        $gross = 0; // total sebelum diskon
        $net   = 0; // total setelah diskon

        foreach ($lines as $l) {
            $qty       = (int)($l['qty'] ?? 0);
            $units    += $qty;

            // -> final_price jika ada, else unit_price
            // -> original_price jika ada, else final
            $unitFinal = isset($l['final_price']) ? (int)$l['final_price'] : (int)($l['unit_price'] ?? 0);
            $unitOrig  = isset($l['original_price']) ? (int)$l['original_price'] : $unitFinal;

            $net   += $unitFinal * $qty;
            $gross += $unitOrig  * $qty;
        }

        $discountTotal = max($gross - $net, 0);

        return response()->json([
            'lines'   => $lines,
            'summary' => [
                'subtotal'       => (int)$net,
                'units'          => (int)$units,
                'products'       => count($lines),

                // Untuk UI diskon
                'gross'          => (int)$gross,
                'net'            => (int)$net,
                'discount_total' => (int)$discountTotal,
            ],
        ]);
    }

    /**
     * POST /sales-transaction/cart/add-by-sku
     * Body: sku
     */
    public function cartAddBySku(Request $request)
    {
        \RuleHelper::UserHasPermission('create');

        $sku = (string) $request->input('sku', '');
        $storeId = session('store.id');

        if ($sku === '') {
            return response()->json(['message' => 'Barcode tidak valid'], 422);
        }

        $detail = ProductDetail::where('sku', $sku)
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->whereNull('deleted_at')
            ->first();

        if (!$detail) {
            return response()->json(['message' => 'Produk dengan Barcode tersebut tidak ditemukan di cabang ini'], 404);
        }

        // Reuse cartAdd logic
        $request->merge(['product_detail_id' => $detail->id, 'qty' => 1]);
        return $this->cartAdd($request);
    }

    /**
     * POST /sales-transaction/cart/add
     * Body: product_detail_id, qty
     */
    public function cartAdd(Request $request)
    {
        \RuleHelper::UserHasPermission('create');

        $storeId     = session('store.id');
        $cartStoreId = Session::get('cart_store_id');

        if (!empty($storeId) && !empty($cartStoreId) && $cartStoreId !== $storeId) {
            Session::forget('sale_cart'); // reset cart list bila ganti store
        }

        Session::put('cart_store_id', $storeId); 

        $detailId = (string) $request->input('product_detail_id', '');
        $qty      = max(1, (int) $request->input('qty', 1));
        if ($detailId === '') return response()->json(['message' => 'Invalid product_detail_id'], 422);

        // query: pakai scope diskon agar ada final_price + disc.*
        $row = ProductDetail::query()
            ->withBestActiveDiscountAndFinalPrice()
            ->join('products as p', 'p.id', '=', 'product_details.product_id')
            ->leftJoin('variants as v', 'v.id', '=', 'product_details.variant_id')
            ->leftJoin('colors   as c', 'c.id', '=', 'product_details.color_id')
            ->where('product_details.id', $detailId)
            ->addSelect([
                'product_details.id  as product_detail_id',
                'p.title             as product_title',
                'v.name              as variant_name',
                'c.label             as color_label',
                'product_details.qty as stock',

                // product_details.sku & product_details.price sudah ada dari addSelect('product_details.*') di scope
            ])
            ->first();

        if (!$row) return response()->json(['message' => 'Detail not found'], 404);

        $lines = Session::get('sale_cart', []);
        $index = null;
        foreach ($lines as $i => $line) if ($line['id'] === (string)$row->product_detail_id) { $index = $i; break; }

        $maxStock    = max(0, (int)($row->stock ?? 0));
        $existingQty = $index !== null ? (int)$lines[$index]['qty'] : 0;
        $remaining   = $maxStock - $existingQty;

        // Bypassing stock validation to allow transactions when out of stock
        // if ($maxStock <= 0)       return response()->json(['message' => 'Stok habis'], 422);
        // if ($remaining <= 0)      return response()->json(['message' => 'Stok habis'], 422);
        // if ($qty > $remaining)    return response()->json(['message' => 'Qty melebihi stok. Sisa stok: '.$remaining], 422);

        // Ambil harga & diskon dari scope
        $originalPrice = (int) ($row->price ?? 0);
        $finalPrice    = (int) ($row->final_price ?? $originalPrice);
        $discType      = $row->best_discount_type ?? null;
        $discValue     = isset($row->discount_value) ? (int) $row->discount_value : 0;
        $rawPercent    = isset($row->raw_percent_value) ? (float) $row->raw_percent_value : null;
        $rawNominal    = isset($row->raw_nominal_value) ? (float) $row->raw_nominal_value : null;

        if ($index !== null) {
            // Tambah qty (harga biarkan yang tersimpan)
            $lines[$index]['qty'] = $existingQty + $qty;
        } else {
            $lines[] = [
                'id'               => (string)$row->product_detail_id,
                'title'            => (string)$row->product_title,
                'variant_name'     => (string)($row->variant_name ?? ''),
                'color_label'      => (string)($row->color_label  ?? ''),
                'sku'              => (string)($row->sku ?? ''),

                // simpan harga & diskon:
                'original_price'      => $originalPrice,
                'final_price'         => $finalPrice,
                'best_discount_type'  => $discType,
                'best_discount_value' => $discValue,
                'raw_percent_value'   => $rawPercent,
                'raw_nominal_value'   => $rawNominal,

                // unit_price dipakai perhitungan subtotal => pakai final_price
                'unit_price'       => $finalPrice,
                'qty'              => $qty,
                'maxQty'           => $maxStock,
            ];
        }

        Session::put('sale_cart', array_values($lines));

        $subtotal = 0; $units = 0; foreach ($lines as $l) { $subtotal += ((int)$l['unit_price'])*((int)$l['qty']); $units += (int)$l['qty']; }
        return response()->json([
            'lines'   => array_values($lines),
            'summary' => ['subtotal'=>(int)$subtotal,'units'=>(int)$units,'products'=>count($lines)],
        ]);
    }

    /**
     * POST /sales-transaction/cart/update
     * Body: product_detail_id, qty
     */
    public function cartUpdate(Request $request)
    {
        \RuleHelper::UserHasPermission('create');

        $id  = (string) $request->input('product_detail_id', '');
        $qty = max(1, (int) $request->input('qty', 1));
        if ($id === '') return response()->json(['message' => 'Invalid product_detail_id'], 422);

        $lines = Session::get('sale_cart', []);
        foreach ($lines as &$l) {
            if ($l['id'] === $id) {
                // Allow unlimited qty
                // $max = $l['maxQty'] ?? null;
                // $l['qty'] = $max ? min($qty, (int)$max) : $qty;
                $l['qty'] = $qty;
                break;
            }
        } unset($l);
        Session::put('sale_cart', array_values($lines));

        $subtotal = 0; $units = 0; foreach ($lines as $x) { $subtotal += ((int)$x['unit_price'])*((int)$x['qty']); $units += (int)$x['qty']; }
        return response()->json([
            'lines'   => array_values($lines),
            'summary' => ['subtotal'=>(int)$subtotal,'units'=>(int)$units,'products'=>count($lines)],
        ]);
    }

    /**
     * POST /sales-transaction/cart/remove
     * Body: product_detail_id
     */
    public function cartRemove(Request $request)
    {
        \RuleHelper::UserHasPermission('create');

        $id = (string) $request->input('product_detail_id', '');
        if ($id === '') return response()->json(['message' => 'Invalid product_detail_id'], 422);

        $lines = array_values(array_filter(Session::get('sale_cart', []), fn($l) => $l['id'] !== $id));
        Session::put('sale_cart', $lines);

        $subtotal = 0; $units = 0; foreach ($lines as $x) { $subtotal += ((int)$x['unit_price'])*((int)$x['qty']); $units += (int)$x['qty']; }
        return response()->json([
            'lines'   => $lines,
            'summary' => ['subtotal'=>(int)$subtotal,'units'=>(int)$units,'products'=>count($lines)],
        ]);
    }

    /**
     * GET /sales-transaction/cart/paperbag-detail
     * Cari product_detail paperbag sesuai store aktif
     */
    public function getPaperbagDetail()
    {
        $productId = '25b62288-068d-4feb-b67e-5df6603eaf9d'; // product_id paperbag
        $storeId = session('store.id');

        $detail = ProductDetail::where('product_id', $productId)
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->whereNull('deleted_at')
            ->first();

        if (!$detail) {
            return response()->json(['message' => 'Paperbag tidak tersedia di cabang ini'], 404);
        }

        return response()->json([
            'product_detail_id' => (string) $detail->id,
        ]);
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
            'discount_percent'  => 'nullable|integer|min:0|max:100',
            'discount_nominal'  => 'nullable|string',
            'additional_cost'   => 'nullable|string',
            'payment_type'      => 'nullable|string',
            'sales_person_id'   => 'required|string',
            'customer_id'       => 'nullable|string',
            'amount_paid'       => 'nullable|string',
            'remark'            => 'nullable|string|max:200',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return $request->ajax()
                ? response()->json(['ok' => false, 'errors' => $errors], 422)
                : redirect()->back()->withErrors($errors)->withInput();
        }

        // Get data from session cart
        $cart = Session::get('sale_cart', []);
        if (!$cart) {
            $msg = ['message' => 'Keranjang kosong'];
            Session::flash('error_message', $msg['message']);
            return $request->ajax()
                ? response()->json(['ok' => false] + $msg, 422)
                : redirect()->back()->withErrors($msg)->withInput();
        }

        // Set timezone
        $tz  = 'Asia/Jakarta';
        $now = \Carbon\Carbon::now($tz);

        // Helper Int
        $toInt = fn($v) => (int) preg_replace('/[^\d-]/', '', (string)($v ?? '0'));

        $subtotal = 0; 
        $units = 0;
        foreach ($cart as $line) { $subtotal += ((int)$line['unit_price']) * ((int)$line['qty']); $units += (int)$line['qty']; }

        $discPercent = max(0, min(100, (int)$request->input('discount_percent', 0)));
        $discNominal = max(0, $toInt($request->input('discount_nominal', 0)));
        $addCost     = max(0, $toInt($request->input('additional_cost', 0)));
        $paid        = max(0, $toInt($request->input('amount_paid', 0)));

        $discountFromPercent = (int) floor($subtotal * $discPercent / 100);
        $discountTotal       = min($subtotal, $discountFromPercent + $discNominal);
        $grandTotal          = max(0, $subtotal - $discountTotal + $addCost);

        $change = max(0, $paid - $grandTotal);  // Jumlah kembalian

        if ($paid < $grandTotal) {
            $kurang = $grandTotal - $paid;
            $msg = ['message' => 'Jumlah dibayarkan kurang Rp ' . number_format($kurang, 0, ',', '.')];
            Session::flash('error_message', $msg['message']);
            return $request->ajax()
                ? response()->json(['ok' => false] + $msg, 422)
                : redirect()->back()->withErrors($msg)->withInput();
        }

        $storeId    = session('store.id');
        if (!$storeId) {
            $msg = ['message' => 'Store belum dipilih / session store hilang'];
            Session::flash('error_message', $msg['message']);
            return $request->ajax()
                ? response()->json(['ok' => false] + $msg, 422)
                : redirect()->back()->withErrors($msg)->withInput();
        }

        $invoiceId  = 'INV-'.$now->format('YmdHis');
        $trxAt      = $now->copy();

        $customerId = $request->input('customer_id');
        if (!$customerId) {
            $customerId = Customer::query()
                ->where('is_default', 1)
                ->whereNull('deleted_at')
                ->value('id');
        }
        
        $salesId    = $request->input('sales_person_id');
        $payTypeId  = $request->input('payment_type') ?: null;
        $channel    = 'store';
        $remark     = trim((string) $request->input('remark', ''));

        try {
            DB::beginTransaction();

            $detailIds = array_map(fn($l) => (string)$l['id'], $cart);
            $details   = ProductDetail::lockForUpdate()->whereIn('id', $detailIds)->get()->keyBy('id');
            foreach ($cart as $line) {
                $pd = $details->get((string) $line['id']);
                if (!$pd) throw new \RuntimeException('Produk tidak ditemukan: '.$line['id']);
                // validasi stok tidak cukup dihapus agar bisa self-correcting (minus)
            }

            $st = new SalesTransaction();
            $st->store_id         = $storeId;
            $st->date             = $trxAt;
            $st->invoice_id       = $invoiceId;
            $st->customer_id      = $customerId;
            $st->sub_total        = $subtotal;
            $st->discount_percent = $discPercent;
            $st->discount_nominal = $discNominal;
            $st->additional_cost  = $addCost;
            $st->total            = $grandTotal;
            $st->payment_type     = $payTypeId;
            $st->channel          = $channel;
            $st->remark           = ($remark === '') ? null : $remark;
            $st->payment_status   = 'paid';
            $st->sale_people_id   = $salesId;
            $st->created_by       = Auth::user()->email ?? null;

            $st->amount_paid      = $paid; // Jumlah dibayarkan
            $st->change           = $change; // Jumlah kembalian

            $st->paid_at = $now;
            $st->setCreatedAt($now);
            $st->setUpdatedAt($now);

            $st->save();

            foreach ($cart as $line) {
                $pd = $details->get((string)$line['id']);
                $qty = (int) $line['qty'];
                $newQty = (int)$pd->qty - $qty; // diubah menjadi minus tanpa max(0)

                $pd->qty = $newQty;
                $pd->setUpdatedAt($now);
                $pd->save();

                $mv = new ProductStockMovement();
                $mv->product_detail_id = $pd->id;
                $mv->qty               = -$qty;
                $mv->last_stock        = $newQty;
                $mv->remark            = 'Penjualan ' . $st->invoice_number;
                $mv->created_by        = Auth::user()->email ?? null;
                $mv->setCreatedAt($now);
                $mv->setUpdatedAt($now);
                $mv->save();

                $item = new SalesTransactionItem();
                $item->sales_transaction_id = $st->id;
                $item->product_detail_id    = $pd->id;

                // Ambil harga+diskon dari cart (sudah diset saat add to cart)
                $orig = (int)($line['original_price'] ?? $line['unit_price'] ?? 0);
                $final = (int)($line['final_price']   ?? $line['unit_price'] ?? $orig);

                // Jika tipe diskon persen, simpan persen-nya; nominal simpan selisih
                $discType   = (string)($line['best_discount_type'] ?? '');
                $discPct    = $discType === 'percent' ? (float)($line['raw_percent_value'] ?? 0) : 0.0;
                $discNom    = max(0, $orig - $final); // per unit

                $item->price            = (float)$orig;   // harga asli/unit
                $item->cogm             = (float)$pd->cogm; // HPP/unit
                $item->discount_percent = $discPct;       // 0 jika bukan persen
                $item->discount_nominal = (float)$discNom; // nominal/unit (selisih)
                $item->final_price      = (float)$final;  // harga akhir/unit
                $item->qty              = $qty;

                $item->created_by = Auth::user()->email ?? null;
                $item->setCreatedAt($now);
                $item->setUpdatedAt($now);
                $item->save();
            }

            DB::commit();
            Session::forget('sale_cart');

            return $request->ajax()
                ? response()->json(['ok' => true, 'id' => $st->id, 'invoice_id' => $st->invoice_id, 'redirect' => route('sales-transaction.payment_success', $st->id)])
                : redirect()->route('sales-transaction.payment_success', $st->id)->with('flash_message', 'Payment successful');

        } catch (\Throwable $e) {
            DB::rollBack();
            Session::flash('error_message', $e->getMessage());
            return $request->ajax()
                ? response()->json(['ok' => false, 'message' => $e->getMessage()], 422)
                : redirect()->route('sales-transaction.create')->withErrors(['message' => $e->getMessage()])->with('error_message', 'New record failed to created');
        }
    }

    public function payment_success($id)
    {
        \RuleHelper::UserHasPermission('read');

        $trx   = SalesTransaction::findOrFail($id);
        $units = DB::table('sales_transaction_items')->where('sales_transaction_id', $id)->sum('qty');

        $methodName = null;
        if (!empty($trx->payment_type)) {
            $methodName = PaymentType::where('id', $trx->payment_type)->value('payment_type');
        }
        $customerPhone = null;
        if (!empty($trx->customer_id)) {
            $customerPhone = Customer::where('id', $trx->customer_id)->value('phone');
        }

        $ts   = $trx->paid_at ?: $trx->date ?: now();
        $time = \Carbon\Carbon::parse($ts)->format('d-m-Y H:i:s');

        $qPaid   = request()->query('paid');
        $qChange = request()->query('change');

        $paidInt   = is_numeric($qPaid)   ? (int)$qPaid   : (int)($trx->amount_paid ?? $trx->total);
        $changeInt = is_numeric($qChange) ? (int)$qChange : (int)($trx->change ?? max(0, $paidInt - (int)$trx->total));

        $fmt = fn (int $n) => 'Rp. ' . number_format($n, 0, ',', '.');

        $this->data['ps'] = [
            'id'      => $trx->id,
            'invoice' => (string) ($trx->invoice_id ?? '-'),
            'time'    => $time,
            'method'  => (string) ($methodName ?? '-'),
            'wa'      => (string) ($customerPhone ?? '-'),
            'total'   => $fmt((int) $trx->total),
            'paid'    => $fmt($paidInt),
            'change'  => $fmt($changeInt),
        ];
        $this->data['trx']   = $trx;
        $this->data['units'] = (int) $units;

        return view('backend.pages.sale_transaction.payment_success', $this->data);
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

        // Ambil transaksi utama
        $trx = SalesTransaction::findOrFail($id);

        // Ambil item transaksi + konteks produk/kategori/varian/warna
        $items = SalesTransactionItem::query()
            ->join('product_details as pd', 'pd.id', '=', 'sales_transaction_items.product_detail_id')
            ->leftJoin('products as p', 'p.id', '=', 'pd.product_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('variants as v', 'v.id', '=', 'pd.variant_id')
            ->leftJoin('colors as col', 'col.id', '=', 'pd.color_id')
            ->where('sales_transaction_items.sales_transaction_id', $trx->id)
            ->get([
                // Kolom item (harga asli/unit, diskon/unit, harga akhir/unit, qty)
                'sales_transaction_items.*',

                // Atribut konteks produk untuk tampilan
                'pd.sku as sku',
                'p.title as product_title',
                'c.title as category_title',
                'v.name as variant_name',
                'col.label as color_label',
                'p.content_paths',
            ])

            ->map(function ($row) {
                // Gambar utama (fallback placeholder jika kosong)
                $paths = json_decode($row->content_paths ?? '[]', true) ?: [];
                $main  = collect($paths)->firstWhere('name', 'main_pic');
                $row->main_pic = ($main && !empty($main['path']))
                    ? env('APP_URL') . \Storage::url($main['path'])
                    : 'https://via.placeholder.com/48x48?text=NO';

                // Subtotal baris = harga akhir setelah diskon × qty
                $final = (float) ($row->final_price ?? 0);
                $qty   = (int) ($row->qty ?? 0);
                $row->line_subtotal = $final * $qty;

                // Simpan harga asli per unit untuk kolom "Harga"
                $row->original_price = (float) ($row->price ?? 0);
                return $row;
            });

        // Ringkasan item: total unit & subtotal akhir (setelah diskon per item)
        $units    = (int) $items->sum('qty');
        $subtotal = (float) $items->sum('line_subtotal');

        // Diskon global (persen & nominal) + biaya tambahan + total akhir terhitung
        $discPct         = (int) ($trx->discount_percent ?? 0);
        $discNom         = (int) ($trx->discount_nominal ?? 0);
        $discPctAmount   = (int) floor($subtotal * $discPct / 100);
        $discountTotal   = min($subtotal, $discPctAmount + $discNom);
        $additionalCost  = (int) ($trx->additional_cost ?? 0);
        $computedTotal   = max(0, $subtotal - $discountTotal + $additionalCost);

        // Informasi metode pembayaran & customer & sales person
        $paymentType = !empty($trx->payment_type) ? \App\Models\PaymentType::find($trx->payment_type) : null;
        $customer    = !empty($trx->customer_id) ? \App\Models\Customer::find($trx->customer_id) : null;
        $salesPerson = !empty($trx->sale_people_id) ? \App\Models\SalePerson::find($trx->sale_people_id) : null;

        // Tanggal/timestamp yang ditampilkan (prioritas: paid_at → date → created_at)
        $ts = $trx->paid_at ?: $trx->date ?: $trx->created_at;

        // Status pembayaran (lunas bila amount_paid ≥ total)
        $amountPaid = (int) ($trx->amount_paid ?? 0);
        $isPaid     = $amountPaid >= (int) $trx->total;

        // Header modul (fallback jika belum di-set)
        $this->data['titleHeader'] = $this->data['titleHeader'] ?? \App\Models\Module::where('url', '=', \Request::segment(1))->first(['icon','name','url']);

        // Data untuk view
        $this->data += [
            'sale_transaction'        => $trx,
            'items'                   => $items,
            'units'                   => $units,
            'computed_subtotal'       => (int) $subtotal,
            'discount_percent'        => $discPct,
            'discount_percent_amount' => (int) $discPctAmount,
            'discount_nominal'        => (int) $discNom,
            'discount_total'          => (int) $discountTotal,
            'additional_cost'         => (int) $additionalCost,
            'computed_total'          => (int) $computedTotal,
            'payment_type'            => $paymentType,
            'customer'                => $customer,
            'sales_person'            => $salesPerson,
            'display_time'            => \Carbon\Carbon::parse($ts)->format('d F Y H:i:s'),
            'is_paid'                 => $isPaid,
        ];

        return view('backend.pages.sale_transaction.show', $this->data);
    }

    public function download_pdf_58($id)
    {
        \RuleHelper::UserHasPermission('read');

        // Transaksi utama
        $trx = SalesTransaction::findOrFail($id);

        // Logo SVG untuk header
        $logoSrc = null;
        $logoPath = public_path('seco-logo.svg');
        if (is_file($logoPath)) {
            $logoSrc = 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($logoPath));
        }

        // Footer Receipt
        $footerText = Setting::where('key', 'receipt_footer')
                        ->value('value') ?? 'Terima kasih atas kunjungan anda';

        // Item + detail produk (pakai final_price untuk tampilan & perhitungan)
        $items = SalesTransactionItem::query()
            ->join('product_details as pd', 'pd.id', '=', 'sales_transaction_items.product_detail_id')
            ->leftJoin('products as p', 'p.id', '=', 'pd.product_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('variants as v', 'v.id', '=', 'pd.variant_id')
            ->leftJoin('colors as col', 'col.id', '=', 'pd.color_id')
            ->where('sales_transaction_items.sales_transaction_id', $trx->id)
            ->get([
                'sales_transaction_items.*',    // kolom item: qty, final_price, dsb.
                'pd.sku as sku',
                'p.title as product_title',
                'c.title as category_title',
                'v.name as variant_name',
                'col.label as color_label',
            ])
            ->map(function ($row) {
                // Hitung harga final per unit & subtotal baris (final × qty)
                $final = (float) ($row->final_price ?? $row->price ?? 0);
                $qty   = (int) ($row->qty ?? 0);
                $row->final_unit_price = $final;          // tampil: "qty x harga"
                $row->line_subtotal    = $final * $qty;   // subtotal setelah diskon per item
                return $row;
            });

        // Ringkasan item: total qty & subtotal (akumulasi subtotal baris)
        $units    = (int) $items->sum('qty');
        $subtotal = (float) $items->sum('line_subtotal');

        // Diskon global (persen & nominal) + biaya tambahan → total terhitung
        $discPct         = (int) ($trx->discount_percent ?? 0);
        $discNom         = (int) ($trx->discount_nominal ?? 0);
        $discPctAmount   = (int) floor($subtotal * $discPct / 100);
        $discountTotal   = min($subtotal, $discPctAmount + $discNom);
        $additionalCost  = (int) ($trx->additional_cost ?? 0);
        $computedTotal   = max(0, $subtotal - $discountTotal + $additionalCost);

        // Relasi pembayaran & customer
        $paymentType = !empty($trx->payment_type) ? PaymentType::find($trx->payment_type) : null;
        $customer    = !empty($trx->customer_id) ? Customer::find($trx->customer_id) : null;

        // Timestamp tampil & status pembayaran
        $ts         = $trx->paid_at ?: $trx->date ?: $trx->created_at;
        $amountPaid = (int) ($trx->amount_paid ?? 0);
        $isPaid     = $amountPaid >= (int) $trx->total;

        // Nama toko aktif (dari session)
        $storeName  = session('store.name', 'Toko');

        // Payload untuk view PDF
        $data = [
            'sale_transaction'        => $trx,
            'items'                   => $items,
            'units'                   => $units,
            'computed_subtotal'       => (int) $subtotal,
            'discount_percent'        => $discPct,
            'discount_percent_amount' => (int) $discPctAmount,
            'discount_nominal'        => (int) $discNom,
            'discount_total'          => (int) $discountTotal,
            'additional_cost'         => (int) $additionalCost,
            'computed_total'          => (int) $computedTotal,
            'payment_type'            => $paymentType,
            'customer'                => $customer,
            'display_time'            => \Carbon\Carbon::parse($ts)->format('d F Y H:i:s'),
            'is_paid'                 => $isPaid,
            'store_name'              => $storeName,
            'logo_src'                => $logoSrc,
            'footer_text'             => $footerText,
        ];

        // Render & kirim PDF ke browser (stream)
        $pdf = Pdf::loadView('backend.pages.sale_transaction.pdf.58mm', $data)
            ->setPaper('a4', 'portrait');

        $fileName = 'INV-' . ($trx->invoice_id ?? $trx->id) . '-58mm.pdf';
        return $pdf->stream($fileName);
    }

    public function print_thermal_58($id)
    {
        \RuleHelper::UserHasPermission('read');

        // Transaksi utama
        $trx = SalesTransaction::findOrFail($id);

        // Logo SVG untuk header
        $logoSrc = null;
        $logoPath = public_path('seco-logo.svg');
        if (is_file($logoPath)) {
            $logoSrc = 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($logoPath));
        }

        // Footer Receipt
        $footerText = Setting::where('key', 'receipt_footer')
                        ->value('value') ?? 'Terima kasih atas kunjungan anda';

        // Item + detail produk (pakai final_price untuk tampilan & perhitungan)
        $items = SalesTransactionItem::query()
            ->join('product_details as pd', 'pd.id', '=', 'sales_transaction_items.product_detail_id')
            ->leftJoin('products as p', 'p.id', '=', 'pd.product_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('variants as v', 'v.id', '=', 'pd.variant_id')
            ->leftJoin('colors as col', 'col.id', '=', 'pd.color_id')
            ->where('sales_transaction_items.sales_transaction_id', $trx->id)
            ->get([
                'sales_transaction_items.*',    // kolom item: qty, final_price, dsb.
                'pd.sku as sku',
                'p.title as product_title',
                'c.title as category_title',
                'v.name as variant_name',
                'col.label as color_label',
            ])
            ->map(function ($row) {
                // Hitung harga final per unit & subtotal baris (final × qty)
                $final = (float) ($row->final_price ?? $row->price ?? 0);
                $qty   = (int) ($row->qty ?? 0);
                $row->final_unit_price = $final;          // tampil: "qty x harga"
                $row->line_subtotal    = $final * $qty;   // subtotal setelah diskon per item
                return $row;
            });

        // Ringkasan item: total qty & subtotal (akumulasi subtotal baris)
        $units    = (int) $items->sum('qty');
        $subtotal = (float) $items->sum('line_subtotal');

        // Diskon global (persen & nominal) + biaya tambahan → total terhitung
        $discPct         = (int) ($trx->discount_percent ?? 0);
        $discNom         = (int) ($trx->discount_nominal ?? 0);
        $discPctAmount   = (int) floor($subtotal * $discPct / 100);
        $discountTotal   = min($subtotal, $discPctAmount + $discNom);
        $additionalCost  = (int) ($trx->additional_cost ?? 0);
        $computedTotal   = max(0, $subtotal - $discountTotal + $additionalCost);

        // Relasi pembayaran & customer
        $paymentType = !empty($trx->payment_type) ? PaymentType::find($trx->payment_type) : null;
        $customer    = !empty($trx->customer_id) ? Customer::find($trx->customer_id) : null;

        // Timestamp tampil & status pembayaran
        $ts         = $trx->paid_at ?: $trx->date ?: $trx->created_at;
        $amountPaid = (int) ($trx->amount_paid ?? 0);
        $isPaid     = $amountPaid >= (int) $trx->total;

        // Nama toko aktif (dari session)
        $storeName  = session('store.name', 'Toko');

        // Payload untuk view PDF
        $data = [
            'sale_transaction'        => $trx,
            'items'                   => $items,
            'units'                   => $units,
            'computed_subtotal'       => (int) $subtotal,
            'discount_percent'        => $discPct,
            'discount_percent_amount' => (int) $discPctAmount,
            'discount_nominal'        => (int) $discNom,
            'discount_total'          => (int) $discountTotal,
            'additional_cost'         => (int) $additionalCost,
            'computed_total'          => (int) $computedTotal,
            'payment_type'            => $paymentType,
            'customer'                => $customer,
            'display_time'            => \Carbon\Carbon::parse($ts)->format('d F Y H:i:s'),
            'is_paid'                 => $isPaid,
            'store_name'              => $storeName,
            'logo_src'                => $logoSrc,
            'footer_text'             => $footerText,
        ];

        return view('backend.pages.sale_transaction.print_thermal_html', $data);
    }

    public function sendWhatsAppReceiptText(Request $request, $id)
    {
        \RuleHelper::UserHasPermission('read');

        // Ambil transaksi + item terkait
        $trx = SalesTransaction::findOrFail($id);

        // Footer Receipt
        $footerText = Setting::where('key', 'receipt_footer')
                        ->value('value') ?? 'Terima kasih atas kunjungan anda';

        $items = SalesTransactionItem::query()
            ->join('product_details as pd', 'pd.id', '=', 'sales_transaction_items.product_detail_id')
            ->leftJoin('products as p', 'p.id', '=', 'pd.product_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->leftJoin('variants as v', 'v.id', '=', 'pd.variant_id')
            ->leftJoin('colors as col', 'col.id', '=', 'pd.color_id')
            ->where('sales_transaction_items.sales_transaction_id', $trx->id)
            ->get([
                'sales_transaction_items.*',    // qty, price/discount/final_price per unit
                'p.title as product_title',
                'c.title as category_title',
                'v.name as variant_name',
                'col.label as color_label',
            ])
            ->map(function ($row) {
                $final = (float)($row->final_price ?? $row->price ?? 0);
                $qty   = (int)($row->qty ?? 0);
                $row->final_unit_price = $final; // harga/unit setelah diskon
                $row->line_subtotal    = $final * $qty; // subtotal baris (final × qty)
                return $row;
            });

        // Hitung ringkasan pembayaran
        $subtotal        = (int)$items->sum('line_subtotal');
        $discPct         = (int)($trx->discount_percent ?? 0);
        $discNom         = (int)($trx->discount_nominal ?? 0);
        $discPctAmount   = (int)floor($subtotal * $discPct / 100);
        $additionalCost  = (int)($trx->additional_cost ?? 0);
        $grandTotal      = max(0, $subtotal - ($discPctAmount + $discNom) + $additionalCost);

        // Konteks: metode bayar, customer, waktu, toko
        $payName   = strtoupper(optional(PaymentType::find($trx->payment_type))->payment_type ?? '-');
        $customer  = !empty($trx->customer_id) ? Customer::find($trx->customer_id) : null;
        $tanggal   = \Carbon\Carbon::parse($trx->paid_at ?: $trx->date ?: $trx->created_at)->format('d/m/Y H:i:s');
        $storeName = session('store.name', 'Toko');

        // Helper format teks
        $rp   = fn($n) => 'Rp ' . number_format((int)($n ?? 0), 0, ',', '.');
        $bold = fn($s) => '*' . trim((string)$s) . '*';
        $ital = fn($s) => '_' . trim((string)$s) . '_';
        $sep  = str_repeat('-', 34);

        // Susun pesan WhatsApp (bagian penting diberi bold)
        $lines = [
            $bold(strtoupper($storeName)),                                  // Nama toko
            'Invoice ' . $bold('#' . ($trx->invoice_id ?? $trx->id)),       // Nomor invoice
            $sep,
            'Tanggal : ' . $bold($tanggal),                                 // Tanggal transaksi
            'Metode : ' . $payName,                                         // Metode pembayaran
            'Dibayar : ' . $rp($trx->amount_paid ?? 0),                     // Nominal dibayar
            'Status : ' . $bold((($trx->amount_paid ?? 0) >= (int)($trx->total ?? $grandTotal)) ? 'Lunas' : 'Belum Lunas'), // Status bayar
            $sep,
            'Customer : ' . $bold(trim(($customer->first_name ?? '').' '.($customer->last_name ?? '')) ?: '—'), // Nama customer
            'No.Telp : ' . ($customer->phone ?? '—'),                       // Telepon
            $sep,
        ];

        // Detail item (judul item bold)
        foreach ($items as $it) {
            $lines[] = $bold($it->product_title); // Nama produk
            $lines[] = ($it->variant_name ?: '—') . ' • ' . ($it->color_label ?: '—') . ' • ' . ($it->category_title ?: '—'); // Varian • Warna • Kategori
            $lines[] = (int)$it->qty . ' x ' . $rp($it->final_unit_price) . ' = ' . $rp($it->line_subtotal); // Qty × Harga = Subtotal
            $lines[] = ''; // Spasi antar item
        }

        // Ringkasan akhir
        $lines[] = $sep;
        $lines[] = 'Subtotal : ' . $bold($rp($subtotal));
        if ($discNom > 0)      $lines[] = 'Diskon (Rp) : - ' . $rp($discNom);
        if ($discPct > 0)      $lines[] = 'Diskon (' . $discPct . '%) : - ' . $rp($discPctAmount);
        if ($additionalCost>0) $lines[] = 'Biaya Tambahan : ' . $rp($additionalCost);
        $lines[] = 'Grand Total : ' . $bold($rp($grandTotal));

        // Catatan transaksi (jika tersedia)
        if (!empty($trx->remark)) {
            $lines[] = $sep;
            $lines[] = $bold('Catatan:');   // Label catatan
            $lines[] = $trx->remark;    // Isi catatan
        }

        // Penutup (beri jarak + italic)
        $lines[] = $sep;
        $lines[] = ''; // Jarak sebelum penutup
        $lines[] = 'Unduh / Lihat PDF Invoice Resmi Anda pada tautan berikut:';
        $lines[] = route('sales-transaction.download_pdf_58', $trx->id);
        $lines[] = '';
        $lines[] = $ital($footerText);

        $text = implode("\n", $lines);

        // Normalisasi nomor WA → 62xxxxxxxxx
        $raw = preg_replace('/\D+/', '', (string)$request->input('phone', '')); // Hanya digit
        if ($raw === '') abort(422, 'Nomor WhatsApp wajib diisi.');
        if (\Illuminate\Support\Str::startsWith($raw, '0'))     $raw = '62' . substr($raw, 1); // 08… → 62…
        elseif (\Illuminate\Support\Str::startsWith($raw, '8')) $raw = '62' . $raw;            // 8…  → 62…

        // Redirect ke WhatsApp (buka WA dengan pesan tersusun)
        $waUrl = 'https://wa.me/' . $raw . '?text=' . rawurlencode($text);
        return redirect()->away($waUrl);
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
        $sale_transaction = SalesTransaction::findOrFail($id);

        $this->data['sale_transaction'] = $sale_transaction;


        return view('backend.pages.sale_transaction.edit', $this->data);
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
        $sale_transaction = SalesTransaction::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'store_id' => 'nullable|string',
            'invoice_id' => 'nullable|string',
            'customer_id' => 'nullable|string',
            'sub_total' => 'nullable|string',
            'discount_percent' => 'nullable|string',
            'discount_nominal' => 'nullable|string',
            'additional_cost' => 'nullable|string',
            'total' => 'nullable|string',
            'payment_type' => 'nullable|string',
            'channel' => 'nullable|string',
            'payment_status' => 'nullable|string',
            'sales_person' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }




        $sale_transaction->id = ucwords($request->id);
        $sale_transaction->store_id = ucwords($request->store_id);
        $sale_transaction->date = ucwords($request->date);
        $sale_transaction->invoice_id = ucwords($request->invoice_id);
        $sale_transaction->customer_id = ucwords($request->customer_id);
        $sub_total = str_replace('.', '', $request->sub_total);
        $sale_transaction->sub_total = $sub_total;
        $sale_transaction->discount_percent = ucwords($request->discount_percent);
        $discount_nominal = str_replace('.', '', $request->discount_nominal);
        $sale_transaction->discount_nominal = $discount_nominal;
        $additional_cost = str_replace('.', '', $request->additional_cost);
        $sale_transaction->additional_cost = $additional_cost;
        $total = str_replace('.', '', $request->total);
        $sale_transaction->total = $total;
        $sale_transaction->payment_type = ucwords($request->payment_type);
        $sale_transaction->channel = ucwords($request->channel);
        $sale_transaction->payment_status = ucwords($request->payment_status);
        $sale_transaction->sales_person = ucwords($request->sales_person);
        $sale_transaction->updated_by = Auth::user()->email;
        $simpan = $sale_transaction->save();

        if ($simpan) {
            \Session::flash('flash_message', 'The record updated successfully');

            return redirect()->route('sales-transaction.edit', $id);
        } else {
            \Session::flash('error_message', 'The record failed to updated');

            return redirect()->route('sales-transaction.edit', $id);
        }
    }

    public function trash()
    {
        \RuleHelper::UserHasPermission('trash');


        $this->data['sale_transactions_count'] = DB::table('sales_transactions')->whereNotNull('deleted_at')->count();
        return view('backend.pages.sale_transaction.trash', $this->data);
    }

    public function get_datatable()
    {
        \RuleHelper::UserHasPermission('access');

        $user = Auth::user();
        $data = SalesTransaction::select([
            'sales_transactions.id',
            'sales_transactions.date',
            'sales_transactions.invoice_id',
            'sales_transactions.sub_total',
            'sales_transactions.discount_percent',
            'sales_transactions.discount_nominal',
            'sales_transactions.additional_cost',
            'sales_transactions.total',
            'sales_transactions.channel',
            'sales_transactions.payment_status',
            'sales_transactions.created_at',
            'sales_transactions.deleted_at',
            'sales_transactions.deleted_by',
            'sales_transactions.updated_at',
            'sales_transactions.paid_at',
            'sale_people.name as sales_name',
        ])
        ->leftJoin('sale_people', 'sale_people.id', '=', 'sales_transactions.sale_people_id')
        ->whereNull('sales_transactions.deleted_at')
        ->where('sales_transactions.store_id', session('store.id')); // store_id diambil dari session (store aktif)

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
                    //$btnEdit = "<a href='" . route('sales-transaction.edit', $data->id) . "' class='btn btn-sm btn-soft-secondary' title='Edit'><i class='las la-edit '></i></a>";
                }

                if (\RuleHelper::UserHasPermission('delete', false) == true) {
                    $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='" . $data->id . "' title='Delete'><i class='las la-trash '></i></a>";
                }
                $btnView = "<a href='" . route('sales-transaction.show', $data->id) . "' class='btn btn-sm btn-soft-secondary' title='Detail'><i class='las la-search-plus '></i></a>";
                $btnRefund = "";
                if ($data->payment_status === 'paid' && $data->channel === 'store') {
                    $btnRefund = "<a href='" . route('refund.create', $data->id) . "' class='btn btn-sm btn-soft-warning' title='Refund'><i class='las la-undo-alt'></i></a>";
                }

                return
                    $btnView . ' ' . $btnEdit . ' ' . $btnDelete . ' ' . $btnRefund;
            })
            ->editColumn('invoice_id', function ($data) {
                $sales = $data->sales_name ? '<br><small class="text-muted">' . e($data->sales_name) . '</small>' : '';
                return $data->invoice_id . $sales;
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->editColumn('date', function ($data) {
                return \Carbon\Carbon::parse($data->date)->format('d F Y H:i:s');
            })
            ->editColumn('discount_percent', function ($data) {
                return $data->discount_percent ? $data->discount_percent . '%' : '0%';
            })->editColumn('sub_total', function ($data) {
                if ($data->sub_total == '' || $data->sub_total == null || $data->sub_total == 0)
                    return 'Rp. 0';
                return 'Rp. ' . $this->rupiah($data->sub_total, 0, ',', '.');
            })->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('discount_nominal', function ($data) {
                if ($data->discount_nominal == '' || $data->discount_nominal == null || $data->discount_nominal == 0)
                    return 'Rp. 0';
                return 'Rp. ' . $this->rupiah($data->discount_nominal, 0, ',', '.');
            })->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('additional_cost', function ($data) {
                if ($data->additional_cost == '' || $data->additional_cost == null || $data->additional_cost == 0)
                    return 'Rp. 0';
                return 'Rp. ' . $this->rupiah($data->additional_cost, 0, ',', '.');
            })->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('total', function ($data) {
                if ($data->total == '' || $data->total == null || $data->total == 0)
                    return 'Rp. 0';
                return '<div class="list-group-item list-group-item-primary px-2"> Rp. ' . $this->rupiah($data->total, 0, ',', '.').' </div>';
            })->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('channel', function ($data) {
                if ($data->channel == 'ecommerce') {
                    return '<span class="badge badge-soft-danger"><i class="las la-shopping-cart"></i> '.$data->channel .'</span>';
                }
                if ($data->channel != '') {
                    return '<span class="badge badge-soft-success"><i class="las la-building"></i> '.$data->channel .'</span>';
                }else {
                    return '<span class="badge badge-soft-secondary">'.$data->channel .'</span>';
                } 
            })
            ->editColumn('payment_status', function ($data) {
                $status = ($data->payment_status);

                if ($status === 'paid') {
                    return "<span class='badge bg-success'>Paid</span>";
                }

                if ($status === 'shipping') {
                    $resi = $data->resi ?? '-';

                    return "
                        <div>
                            <span class='badge bg-info'>Shipping</span>
                            <span class='badge bg-info ms-1'>".$resi."</span>
                        </div>
                    ";
                }

                switch ($status) {
                    case 'pending':
                        return "<span class='badge bg-warning text-dark'>Pending</span>";
                    case 'canceled':
                        return "<span class='badge bg-danger'>Cancelled</span>";
                    case 'refunded':
                        return "<span class='badge bg-danger'>Refunded</span>";
                    default:
                        return "<span class='badge bg-secondary'>$status</span>";
                }
            })
            ->filterColumn('deleted_at', function ($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('date') && !empty(request('date'))) {
                    $dateInput = request('date');
                    if (strpos($dateInput, ' to ') !== false) {
                        $dates = explode(' to ', $dateInput);
                        $query->whereBetween('date', [$dates[0] . ' 00:00:00', $dates[1] . ' 23:59:59']);
                    } else {
                        $query->whereDate('date', $dateInput);
                    }
                }
                if (request()->has('invoice_id')  && !empty(request('invoice_id'))) {
                    $query->where('invoice_id', 'like', "%" . request('invoice_id') . "%");
                }
                if (request()->has('payment_status') && !empty(request('payment_status'))) {
                    $query->where('payment_status', request('payment_status'));
                }
            })
            ->rawColumns(['action', 'checkboxs','total','channel','payment_status','invoice_id'])
            ->toJson();
    }

    public function get_datatable_trash()
    {
        \RuleHelper::UserHasPermission('trash');
        $data = SalesTransaction::select([
            'id',
            'date',
            'invoice_id',
            'sub_total',
            'discount_percent',
            'discount_nominal',
            'additional_cost',
            'total',
            'channel',
            'payment_status',
            'created_at',
            'deleted_at',
            'deleted_by',
            'updated_at',
            'paid_at',
        ])->whereNotNull('deleted_at')
            ->where('store_id', session('store.id')); // store_id diambil dari session (store aktif);

        $datatables = DataTables::of($data);

        return $datatables
            ->addColumn('checkboxs', function ($data) {
                return "
                    <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>
                ";
            })
            ->addColumn('action', function ($data) {
                $btnView = "<a href='" . route('sales-transaction.show', $data->id) . "' class='btn btn-sm btn-soft-secondary'><i class='las la-search-plus '></i> View</a>";
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
            })->editColumn('sub_total', function ($data) {
                if ($data->price == '' || $data->price == null || $data->price == 0)
                    return 'Rp. 0';
                return 'Rp. ' . $this->rupiah($data->sub_total, 0, ',', '.');
            })->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('discount_nominal', function ($data) {
                if ($data->price == '' || $data->price == null || $data->price == 0)
                    return 'Rp. 0';
                return 'Rp. ' . $this->rupiah($data->discount_nominal, 0, ',', '.');
            })->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('additional_cost', function ($data) {
                if ($data->price == '' || $data->price == null || $data->price == 0)
                    return 'Rp. 0';
                return 'Rp. ' . $this->rupiah($data->additional_cost, 0, ',', '.');
            })->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('total', function ($data) {
                if ($data->price == '' || $data->price == null || $data->price == 0)
                    return 'Rp. 0';
                return 'Rp. ' . $this->rupiah($data->total, 0, ',', '.');
            })->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('channel', function ($data) {
                if ($data->channel == 'ecommerce') {
                    return '<span class="badge badge-soft-danger"><i class="las la-shopping-cart"></i> '.$data->channel .'</span>';
                }
                if ($data->channel != '') {
                    return '<span class="badge badge-soft-success"><i class="las la-building"></i> '.$data->channel .'</span>';
                }else {
                    return '<span class="badge badge-soft-secondary">'.$data->channel .'</span>';
                } 
            })
            ->editColumn('payment_status', function ($data) {
                $status = ($data->payment_status);

                if ($status === 'paid') {
                    $paidAt = $data->paid_at 
                        ? date('d M Y H:i', strtotime($data->paid_at)) 
                        : '-';

                    return "
                        <div>
                            <span class='badge bg-success'>Paid</span>
                            <span class='badge bg-soft-primary ms-1 text-primary'>".$paidAt."</span>
                        </div>
                    ";
                }

                if ($status === 'shipping') {
                    $resi = $data->resi ?? '-';

                    return "
                        <div>
                            <span class='badge bg-info'>Shipping</span>
                            <span class='badge bg-info ms-1'>".$resi."</span>
                        </div>
                    ";
                }

                switch ($status) {
                    case 'pending':
                        return "<span class='badge bg-warning text-dark'>Pending</span>";
                    case 'canceled':
                        return "<span class='badge bg-danger'>Cancelled</span>";
                    default:
                        return "<span class='badge bg-secondary'>$status</span>";
                }
            })
            ->editColumn('channel', function ($data) {
                if ($data->channel == 'ecommerce') {
                    return '<span class="badge badge-soft-danger"><i class="las la-shopping-cart"></i> '.$data->channel .'</span>';
                }
                if ($data->channel != '') {
                    return '<span class="badge badge-soft-success"><i class="las la-building"></i> '.$data->channel .'</span>';
                }else {
                    return '<span class="badge badge-soft-secondary">'.$data->channel .'</span>';
                } 
            })
            ->filterColumn('deleted_at', function ($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('date')  && !is_null(request('date'))) {
                    $query->where('date', 'like', "%" . request('date') . "%");
                }
                if (request()->has('invoice_id')  && !is_null(request('invoice_id'))) {
                    $query->where('invoice_id', 'like', "%" . request('invoice_id') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs','channel', 'payment_status'])
            ->toJson();
    }

    public function delete(Request $request)
    {
        \RuleHelper::UserHasPermission('delete');

        $table = "sales_transactions";
        $column_visible = "invoice_id";
        $route = "sales-transaction.destroy";

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

                $redirect_url = "sales-transaction/trash";
                $sale_transaction = SalesTransaction::findOrFail($row['id']);


                $sale_transaction->forceDelete();
                $index++;
            }

            if ($delete_type == "softdelete") {
                $redirect_url = "sales-transaction";

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

        $data = DB::table('sales_transactions')
            ->whereIn('id', $request->id_to_be_restored)
            ->orderBy('deleted_at', 'desc')
            ->get();

        $this->data['index'] = 0;
        $this->data['data'] = $data;
        $this->data['route'] = 'sales-transaction.restore_action';
        $this->data['column_name'] = 'invoice_id';

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
            SalesTransaction::where('id', $row->id)
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

        return redirect('sales-transaction/trash');
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

        return Excel::download(new SalesTransactionExport($columnExportData['columnDepedencies'], $columnExportData['columnExtraAttribute']), 'sale_transactions.xlsx');
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
