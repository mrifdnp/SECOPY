<?php
namespace App\Http\Controllers\Master;

use App\Helpers\RuleHelper;
use App\Http\Controllers\Core\BackendController;
use App\Models\InventoryMovement;
use App\Models\MainCategory;
use App\Models\Module;
use App\Models\Party;
use App\Models\Product;
use App\Models\ProductDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class PurchasingController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['page_title']  = 'Pembelian / Purchasing';
        $this->data['titleHeader'] = Module::where('url', FacadesRequest::segment(1))->first(['icon', 'name', 'url']);

        Carbon::setLocale('id');
    }

    public function index()
    {
        RuleHelper::UserHasPermission('read');

        $this->data['inventory_movement_count'] = InventoryMovement::whereNotNull('deleted_at')->count();

        return view('backend.pages.purchasing.index', $this->data);
    }

    public function get_datatable()
    {
        RuleHelper::UserHasPermission('access');

        $data = DB::table('inventory_movements')
            ->leftJoin('parties as source', 'source.id', '=', 'inventory_movements.source_party_id')
            ->leftJoin('parties as destination', 'destination.id', '=', 'inventory_movements.destination_party_id')
            ->leftJoin('stores', 'stores.id', '=', 'inventory_movements.store_id')
            ->leftJoin('sale_people', 'sale_people.id', '=', 'inventory_movements.sale_people_id')
            ->select([
                'inventory_movements.id',
                'inventory_movements.movement_date as movement_date',
                'inventory_movements.movement_type as flow',
                'inventory_movements.source_party_id',
                'inventory_movements.destination_party_id',
                'source.name as source_name',
                'destination.name as destination_name',
                'inventory_movements.remark',
                'stores.name as store',
                'inventory_movements.created_at',
                'inventory_movements.updated_at',
                'sale_people.name as sale_person_name',
            ])
            ->where('inventory_movements.store_id', session('store.id'))
            ->where(function ($query) {
                $query->where(function ($sq) {
                    $sq->whereNotNull('inventory_movements.source_party_id')
                       ->whereNull('source.store_id');
                })->orWhere(function ($sq) {
                    $sq->whereNotNull('inventory_movements.destination_party_id')
                       ->whereNull('destination.store_id');
                });
            })
            ->whereNull('inventory_movements.deleted_at')
            ->orderBy('inventory_movements.movement_date', 'desc')
            ->get();

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
                if (RuleHelper::UserHasPermission('update', false) == false && RuleHelper::UserHasPermission('delete', false) == false) {
                    $disabled = 'disabled';
                }

                if (RuleHelper::UserHasPermission('delete', false) == true) {
                    $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='" . $data->id . "' title='Delete'><i class='las la-trash '></i></a>";
                }
                $btnView = "<a href='#' id='" . $data->id . "' class='btn btn-sm btn-soft-secondary btnDetail' title='Detail'><i class='las la-search-plus '></i></a>";

                $btnPrint = "<a href='" . route('purchasing-transaction.print_thermal_58', $data->id) . "' target='_blank' class='btn btn-sm btn-soft-secondary' title='Print'><i class='las la-print'></i></a>";

                return $btnView . ' ' . $btnPrint . ' ' . $btnEdit . ' ' . $btnDelete;
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->addColumn('flow', function ($data) {
                $spText = '';
                if (!empty($data->sale_person_name)) {
                    $spText = '<br><small class="text-muted"><i class="las la-user me-1"></i>' . $data->sale_person_name . '</small>';
                }

                if ($data->flow == 'in') {
                    return '<span class="fw-medium"><i class="las la-arrow-down text-success fs-16 align-middle me-1"></i>Masuk</span>' . $spText;
                } else if ($data->flow == 'out') {
                    return '<span class="fw-medium"><i class="las la-arrow-up text-danger fs-16 align-middle me-1"></i>Keluar</span>' . $spText;
                } else if ($data->flow == 'broken') {
                    return '<span class="fw-medium"><i class="las la-unlink text-warning fs-16 align-middle me-1"></i>Rusak</span>' . $spText;
                } else {
                    return '<span class="fw-medium"><i class="las la-cog text-info fs-16 align-middle me-1"></i>Digunakan</span>' . $spText;
                }
            })
            ->addColumn('party', function ($data) {
                if ($data->flow == 'in') {
                    return '<span class="fw-medium">' . $data->source_name . '</span> <i class="las la-arrow-right text-success mx-1"></i> <span class="fw-medium">' . $data->store . '</span>';
                } else if ($data->flow == 'out') {
                    return '<span class="fw-medium">' . $data->store . '</span> <i class="las la-arrow-right text-danger mx-1"></i> <span class="fw-medium">' . $data->destination_name . '</span>';
                } else {
                    return '<span class="fw-medium">' . $data->store . '</span>';
                }
            })
            ->filter(function ($query) {
                if (request()->has('date') && ! is_null(request('date'))) {
                    $query->where('inventory_movements.date', 'like', "%" . request('date') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs', 'flow', 'party'])
            ->toJson();
    }

    // daatatable preview laporan mutasi produk
    public function detail($id)
    {
        $data = DB::table('inventory_movement_items')
            ->join('product_details', 'product_details.id', '=', 'inventory_movement_items.product_detail_id')
            ->join('products', 'products.id', '=', 'product_details.product_id')
            ->join('colors', 'colors.id', '=', 'product_details.color_id')
            ->join('variants', 'variants.id', '=', 'product_details.variant_id')
            ->select([
                'products.title',
                'colors.label as color',
                'variants.name as variant',
                'inventory_movement_items.qty',
                'product_details.cogm',
                'product_details.price',
            ])
            ->where('inventory_movement_items.inventory_movement_id', $id)
            ->get();

        return response()->json($data);
    }

    // Fungsi untuk mendapatkan detail informasi produk (untuk modal)
    public function product_detail_information(Request $request)
    {
        RuleHelper::UserHasPermission('create');

        $productId   = (string) $request->query('id', '');
        $activeStore = Session::get('store', null);

        if ($productId === '') {
            return response()->json(['message' => 'Invalid product id'], 422);
        }

        // Get Produk: hanya id + title
        $productRow = DB::table('products')
            ->whereNull('deleted_at')
            ->where('id', $productId)
            ->first(['id', 'title']);

        if (! $productRow) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product = [
            'id'    => (string) $productRow->id,
            'title' => (string) $productRow->title,
        ];

        // Detail kombinasi (sku, price, stok, varian, warna)
        $rows = DB::table('product_details as pd')
            ->leftJoin('variants as v', 'v.id', '=', 'pd.variant_id')
            ->leftJoin('colors   as c', 'c.id', '=', 'pd.color_id')
            ->where('pd.product_id', $productId)
            ->where('pd.store_id', $activeStore ? $activeStore['id'] : null)
            ->whereNull('pd.deleted_at')
            ->where(function ($query) {
                $query->whereNull('v.deleted_at')->orWhereNull('pd.variant_id');
            })
            ->where(function ($query) {
                $query->whereNull('c.deleted_at')->orWhereNull('pd.color_id');
            })
            ->orderBy('v.name')->orderBy('c.label')
            ->get([
                'pd.id               as product_detail_id',
                'pd.sku',
                'pd.price',
                'pd.cogm',
                'pd.qty              as quantity_available',
                'pd.variant_id',
                'pd.color_id',
                'pd.store_id',
                'v.name              as variant_name',
                'c.label             as color_label',
                'c.hexa              as color_hexa',
            ]);

        // Ambil data harga modal (COGM) terbesar dari seluruh toko (karena COGM seragam dimana-mana)
        $globalCogms = DB::table('product_details')
            ->where('product_id', $productId)
            ->where('cogm', '>', 0)
            ->select('variant_id', 'color_id', DB::raw('MAX(cogm) as max_cogm'))
            ->groupBy('variant_id', 'color_id')
            ->get();

        $items = $rows->map(function ($r) use ($globalCogms) {
            $globalCogm = $globalCogms->where('variant_id', $r->variant_id)
                                      ->where('color_id', $r->color_id)
                                      ->first();
            $bestCogm = $r->cogm > 0 ? $r->cogm : ($globalCogm ? $globalCogm->max_cogm : 0);

            return [
                'product_detail_id'  => (string) $r->product_detail_id,
                'sku'                => (string) ($r->sku ?? ''),
                'price'              => (float) $r->price,
                'cogm'               => (int) $bestCogm,
                'quantity_available' => (int) ($r->quantity_available ?? 0),
                'variant_id'         => (string) ($r->variant_id ?? ''),
                'variant_name'       => (string) ($r->variant_name ?? ''),
                'color_id'           => (string) ($r->color_id ?? ''),
                'color_label'        => (string) ($r->color_label ?? ''),
                'color_hexa'         => $r->color_hexa ? (string) $r->color_hexa : null,
            ];
        });

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
            'min_price'       => (float) ($items->min('price') ?? 0),
        ]);
    }

    public function product_mutation()
    {
        $activeStore = Session::get('store', null);

        $mainCategories = MainCategory::whereNull('deleted_at')
            ->orderBy('title')
            ->pluck('title', 'id');

        $priceSub = ProductDetail::select(
            'product_id',
            'store_id',
            DB::raw('MIN(price) AS price')
        )
            ->whereNotNull('price')
            ->where('price', '>', 0)
            ->groupBy('product_id', 'store_id');

        $products = Product::query()
            ->select([
                'products.id',
                'products.title AS product_title',
                'categories.title AS category_title',
                'pd.price',
                'pd.store_id',
                'products.content_paths',
                'products.top_pick',
            ])
            ->leftJoinSub($priceSub, 'pd', function ($join) use ($activeStore) {
                $join->on('pd.product_id', '=', 'products.id')
                    ->where('pd.store_id', '=', $activeStore ? $activeStore['id'] : null);
            })
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->whereNull('products.deleted_at')
            ->orderByDesc('products.top_pick')
            ->orderBy('products.title')
            ->limit(100)
            ->get();

        $partiesOrigin = Party::whereNull('deleted_at')
            ->whereNull('store_id')
            ->orderBy('name')
            ->get(['id', 'name', 'store_id', 'tags']);

        $partiesDestination = Party::whereNull('deleted_at')
            ->whereNotNull('store_id')
            ->orderBy('name')
            ->get(['id', 'name', 'store_id', 'tags']);

        $mutationTypes = [
            (object) ['id' => 'in', 'mutation_type' => 'Masuk', 'notes' => 'Produk masuk'],
            (object) ['id' => 'out', 'mutation_type' => 'Keluar', 'notes' => 'Produk keluar'],
        ];

        $currentStoreId = null;
        if (Session::has('store')) {
            $store = Session::get('store');
            if (is_array($store) && array_key_exists('id', $store)) {
                $currentStoreId = (string) $store['id'];
            }
        }

        $this->data['mainCategories'] = $mainCategories;
        $this->data['products']       = $products;
        $this->data['mutationTypes']  = $mutationTypes;
        $this->data['partiesOrigin']  = $partiesOrigin;
        $this->data['partiesDestination'] = $partiesDestination;
        $this->data['currentStoreId'] = $currentStoreId;
        $this->data['salePeople']     = \App\Models\SalePerson::whereNull('deleted_at')->orderBy('name')->get();

        return view('backend.pages.purchasing.purchasing', $this->data);
    }

    public function filter_product(Request $request)
    {
        $mainCategoryId = (string) $request->query('main_category_id', '');
        $mainCategory   = (string) $request->query('main_category', '');
        $keyword        = trim((string) $request->query('q', ''));

        $q = Product::with(['categories.mainCategory'])
            ->whereNull('products.deleted_at');

        // Filter berdasarkan main category id atau title
        if ($mainCategoryId !== '') {
            $q->whereHas('categories.mainCategory', function ($query) use ($mainCategoryId) {
                $query->where('id', $mainCategoryId);
            });
        } elseif ($mainCategory !== '' && strtolower($mainCategory) !== 'all') {
            $q->whereHas('categories.mainCategory', function ($query) use ($mainCategory) {
                $query->where('title', $mainCategory);
            });
        }

        // Filter keyword
        if ($keyword !== '') {
            $kw   = mb_strtolower($keyword, 'UTF-8');
            $like = '%' . $kw . '%';

            $q->where(function ($w) use ($like) {
                $w->whereRaw('LOWER(products.title) LIKE ?', [$like])
                    ->orWhereHas('categories', function ($cat) use ($like) {
                        $cat->whereRaw('LOWER(title) LIKE ?', [$like]);
                    })
                    ->orWhereHas('categories.mainCategory', function ($mc) use ($like) {
                        $mc->whereRaw('LOWER(title) LIKE ?', [$like]);
                    });
            });
        }

        $products = $q->orderByDesc('products.top_pick')
            ->orderBy('products.title')
            ->limit(100)
            ->get([
                'products.id',
                'products.title as product_title',
                'products.top_pick',
                'products.content_paths',
                'products.category_id',
            ])
            ->map(function ($p) {
                return [
                    'id'                  => $p->id,
                    'product_title'       => $p->product_title,
                    'top_pick'            => $p->top_pick,
                    'category_title'      => $p->categories?->title,
                    'main_category_title' => $p->categorie?->mainCategory?->title,
                    'main_pic'            => $p->main_pic,
                ];
            });

        return response()->json(['products' => $products]);
    }

    public function add_product_to_mutation_list(Request $request)
    {
        RuleHelper::UserHasPermission('create');

        // Cari produk detail berdasarkan product_detail_id
        $detailId = (string) $request->input('product_detail_id', '');
        $qty      = max(1, (int) $request->input('qty', 1));
        
        $cogm = $request->input('cogm');
        $cogmNum = (int) preg_replace('/\D/', '', (string) $cogm);
        if ($cogm === null || $cogm === '' || $cogmNum === 0) {
            return response()->json(['message' => 'Harga Modal wajib diisi dan tidak boleh 0'], 422);
        }
        $cogm = $cogmNum;

        if ($detailId === '') {
            return response()->json(['message' => 'Invalid product_detail_id'], 422);
        }

        $row = DB::table('product_details as pd')
            ->join('products as p', 'p.id', '=', 'pd.product_id')
            ->leftJoin('variants as v', 'v.id', '=', 'pd.variant_id')
            ->leftJoin('colors   as c', 'c.id', '=', 'pd.color_id')
            ->where('pd.id', $detailId)
            ->first([
                'pd.id   as product_detail_id',
                'p.title as product_title',
                'v.name  as variant_name',
                'c.label as color_label',
                'pd.sku', 'pd.price', 'pd.cogm', 'pd.qty as stock',
            ]);

        if (! $row) {
            return response()->json(['message' => 'Detail not found'], 404);
        }

        // Tambahkan ke session mutation_list
        $lines = Session::get('purchasing_list', []);
        $index = null; // Index menandakan produk sudah ada di cart atau belum
        foreach ($lines as $i => $line) {
            if ($line['id'] === (string) $row->product_detail_id) {
                $index = $i;
                break;
            }
        }

        // Cek stock
        $maxStock = (int) ($row->stock ?? 0);
        if ($index !== null) {
            // Produk sudah ada di cart, update qty
            $newQty               = (int) $lines[$index]['qty'] + $qty;
            $lines[$index]['qty'] = $newQty;
            $lines[$index]['cogm'] = $cogm;
        } else {
            // Produk belum ada di cart, tambahkan baru
            $lines[] = [
                'id'           => (string) $row->product_detail_id,
                'title'        => (string) $row->product_title,
                'variant_name' => (string) ($row->variant_name ?? ''),
                'color_label'  => (string) ($row->color_label ?? ''),
                'sku'          => (string) ($row->sku ?? ''),
                'unit_price'   => (int) ($row->price ?? 0),
                'cogm'         => $cogm,
                'qty'          => $qty,
                'maxQty'       => $maxStock,
            ];
        }

        // Simpan ke session
        Session::put('purchasing_list', array_values($lines));

        // Hitung subtotal, total units, total products
        $subtotal = 0;
        $units    = 0;
        foreach ($lines as $l) {
            $subtotal += ((int) $l['cogm']) * ((int) $l['qty']);
            $units += (int) $l['qty'];
        }

        // Kirim response
        return response()->json([
            'lines'   => array_values($lines),
            'summary' => ['subtotal' => (int) $subtotal, 'units' => (int) $units, 'products' => count($lines)],
        ]);
    }

    public function product_mutation_list()
    {
        RuleHelper::UserHasPermission('create');

        // Ambil data dari session
        $lines = Session::get('purchasing_list', []);

        // Hitung subtotal, total units, total products
        $subtotal = 0;
        $units    = 0;
        foreach ($lines as $l) {
            $subtotal += ((int) $l['cogm']) * ((int) $l['qty']);
            $units += (int) $l['qty'];
        }

        return response()->json([
            'lines'   => array_values($lines),
            'summary' => ['subtotal' => (int) $subtotal, 'units' => (int) $units, 'products' => count($lines)],
        ]);
    }

    public function update_product_mutation_list(Request $request)
    {
        RuleHelper::UserHasPermission('create');

        // Cek product_detail_id
        $id  = (string) $request->input('product_detail_id', '');
        $qty = max(1, (int) $request->input('qty', 1));
        if ($id === '') {
            return response()->json(['message' => 'Invalid product_detail_id'], 422);
        }

        // Ambil data dari session
        $lines = Session::get('purchasing_list', []);

        // Update qty pada product_detail_id yang sesuai
        foreach ($lines as &$l) {
            if ($l['id'] === $id) {
                $l['qty'] = $qty;
                break;
            }
        }

        // Update session
        Session::put('purchasing_list', array_values($lines));

        // Hitung subtotal, total units, total products
        $subtotal = 0;
        $units    = 0;
        foreach ($lines as $x) {
            $subtotal += ((int) $x['unit_price']) * ((int) $x['qty']);
            $units += (int) $x['qty'];
        }

        return response()->json([
            'lines'   => array_values($lines),
            'summary' => ['subtotal' => (int) $subtotal, 'units' => (int) $units, 'products' => count($lines)],
        ]);
    }

    public function remove_product_from_mutation_list(Request $request)
    {
        RuleHelper::UserHasPermission('create');

        // Cek product_detail_id
        $id = (string) $request->input('product_detail_id', '');
        if ($id === '') {
            return response()->json(['message' => 'Invalid product_detail_id'], 422);
        }

        // Hapus dari session yang sesuai dengan product_detail_id
        $lines = array_values(array_filter(Session::get('purchasing_list', []), fn($l) => $l['id'] !== $id));
        Session::put('purchasing_list', $lines);

        // Kalkulasi ulang subtotal, total units, total products
        $subtotal = 0;
        $units    = 0;
        foreach ($lines as $x) {
            $subtotal += ((int) $x['unit_price']) * ((int) $x['qty']);
            $units += (int) $x['qty'];
        }

        return response()->json([
            'lines'   => $lines,
            'summary' => ['subtotal' => (int) $subtotal, 'units' => (int) $units, 'products' => count($lines)],
        ]);
    }

    public function clear_product_mutation_list()
    {
        RuleHelper::UserHasPermission('create');

        // Hapus session
        Session::forget('purchasing_list');

        return response()->json([
            'lines'   => [],
            'summary' => ['subtotal' => 0, 'units' => 0, 'products' => 0],
        ]);
    }

    public function create_product_mutation(Request $request)
    {
        RuleHelper::UserHasPermission('create');

        $validator = Validator::make($request->all(), [
            'movement_date'        => 'required|date',
            'movement_type'        => 'required|string|in:in,out,used,broken',
            'source_party_id'      => 'nullable|string',
            'destination_party_id' => 'nullable|string',
            'is_origin_custom'     => 'nullable',
            'custom_origin'        => 'nullable|string|max:255',
            'is_destination_custom'=> 'nullable',
            'custom_destination'   => 'nullable|string|max:255',
            'remark'               => 'nullable|string|max:255',
            'movement_id'          => 'nullable|uuid|exists:inventory_movements,id',
            'sale_people_id'       => 'required|string|exists:sale_people,id',
        ]);

        if ($validator->fails()) {
            $errMessage = collect($validator->errors()->all())->join(', ');
            return response()->json(['message' => 'Validation error: ' . $errMessage, 'errors' => $validator->errors()], 422);
        }

        $movementDate       = $request->input('movement_date');
        $movementType       = $request->input('movement_type');
        $sourcePartyId      = $request->input('source_party_id', null);
        $destinationPartyId = $request->input('destination_party_id', null);

        $isOriginCustom = filter_var($request->input('is_origin_custom', false), FILTER_VALIDATE_BOOLEAN);
        $customOrigin = $request->input('custom_origin', null);
        $isDestinationCustom = filter_var($request->input('is_destination_custom', false), FILTER_VALIDATE_BOOLEAN);
        $customDestination = $request->input('custom_destination', null);

        if ($isOriginCustom && !empty($customOrigin)) {
            $newPartyId = Str::uuid()->toString();
            Party::create([
                'id' => $newPartyId,
                'name' => $customOrigin,
                'store_id' => null,
                'tags' => json_encode(['Supplier'])
            ]);
            $sourcePartyId = $newPartyId;
        }

        if ($isDestinationCustom && !empty($customDestination)) {
            $newPartyId = Str::uuid()->toString();
            Party::create([
                'id' => $newPartyId,
                'name' => $customDestination,
                'store_id' => null,
                'tags' => json_encode(['Supplier'])
            ]);
            $destinationPartyId = $newPartyId;
        }

        $remarks            = $request->input('remarks', null);
        $sourceParty        = Party::find($sourcePartyId);
        $destinationParty   = Party::find($destinationPartyId);

        // Ambil data dari session
        $lines = Session::get('purchasing_list', []);
        if (count($lines) === 0) {
            return response()->json(['message' => 'No products in mutation list'], 422);
        }

        // Cek stock untuk setiap item
        foreach ($lines as $l) {
            if (isset($l['maxQty']) &&
                $l['maxQty'] !== null &&
                (int) $l['qty'] > (int) $l['maxQty'] &&
                ! (isset($l['skipQuantityExceedChecking']) && $l['skipQuantityExceedChecking'] === true) &&
                $movementType !== 'in' // Mutasi masuk tidak perlu cek stock
            ) {
                return response()->json(['message' => "Stok tidak cukup untuk produk {$l['title']}, jumlah maksimal {$l['maxQty']}"], 422);
            }
        }

        // Simpan ke database
        DB::beginTransaction();
        try {
            // Update status inventory movement asal jika ada
            $movementId = (string) $request->input('movement_id', '');
            if ($movementId !== '') {
                DB::table('inventory_movements')
                    ->where('id', $movementId)
                    ->where('status', 'pending')
                    ->update(['status' => 'completed', 'updated_at' => now()]);
            }

            $inventoryMovementId = Str::uuid()->toString();
            DB::table('inventory_movements')->insert([
                'id'                   => $inventoryMovementId,
                'source_party_id'      => $sourcePartyId,
                'destination_party_id' => $destinationPartyId,
                'movement_date'        => $movementDate,
                'movement_type'        => $movementType,
                'remark'               => $remarks,
                'status'               => 'completed',
                'created_at'           => now(),
                'updated_at'           => now(),
                'store_id'             => session('store.id'),
                'sale_people_id'       => $request->input('sale_people_id', null),
            ]);

            foreach ($lines as $l) {
                $remarkForReport = '';
                $stockMoved      = 0;
                switch ($movementType) {
                    case 'in':
                        $stockMoved      = (int) $l['qty'];
                        $remarkForReport = "Mutasi Masuk dari {$sourceParty?->name}";
                        break;
                    case 'out':
                        $stockMoved      = -1 * (int) $l['qty'];
                        $remarkForReport = "Mutasi Keluar ke {$destinationParty?->name}";
                        break;
                    case 'used':
                        $stockMoved      = -1 * (int) $l['qty'];
                        $remarkForReport = "Produk Dipakai";
                        break;
                    case 'broken':
                        $stockMoved      = -1 * (int) $l['qty'];
                        $remarkForReport = "Produk Rusak/Hilang";
                        break;
                }

                DB::table('inventory_movement_items')->insert([
                    'id'                    => Str::uuid()->toString(),
                    'inventory_movement_id' => $inventoryMovementId,
                    'product_detail_id'     => $l['id'],
                    'qty'                   => $l['qty'],
                    'cogm'                  => isset($l['cogm']) ? (int) $l['cogm'] : null,
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);

                // Catat laporan perubahan stock
                DB::table('product_stock_movements')->insert([
                    'id'                => Str::uuid()->toString(),
                    'product_detail_id' => $l['id'],
                    'qty'               => $stockMoved,
                    'last_stock'        => (int) DB::table('product_details')->where('id', $l['id'])->value('qty') + $stockMoved,
                    'remark'            => $remarkForReport,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                // Kurangi stock di product_details
                if ($movementType === 'in') {
                    // Mutasi masuk, tambahkan stock
                    DB::table('product_details')
                        ->where('id', $l['id'])
                        ->update([
                            'qty' => DB::raw("qty + " . (int) $l['qty'])
                        ]);

                    // Timpa COGM master di *Semua Toko* (karena modal sama)
                    if (isset($l['cogm']) && (int) $l['cogm'] > 0) {
                        $pDetail = DB::table('product_details')->where('id', $l['id'])->first();
                        if ($pDetail) {
                            $qCogm = DB::table('product_details')->where('product_id', $pDetail->product_id);
                            
                            if ($pDetail->variant_id) { $qCogm->where('variant_id', $pDetail->variant_id); } 
                            else { $qCogm->whereNull('variant_id'); }
                            
                            if ($pDetail->color_id) { $qCogm->where('color_id', $pDetail->color_id); } 
                            else { $qCogm->whereNull('color_id'); }
                            
                            $qCogm->update(['cogm' => (int) $l['cogm']]);
                        }
                    }
                } else {
                    // Mutasi keluar/dipakai/rusak, kurangi stock
                    DB::table('product_details')
                        ->where('id', $l['id'])
                        ->decrement('qty', (int) $l['qty']);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed tocreateproductmutation', 'error' => $e->getMessage()], 500);
        }

        // Bersihkan session
        Session::forget('purchasing_list');

        return response()->json(['message' => 'Product mutationcreatedsuccessfully']);
    }

    public function get_incoming_product_for_current_store()
    {
        $activeStore = Session::get('store', null);
        if (! $activeStore) {
            return response()->json(['message' => 'No active store'], 400);
        }

        try {
            // Ambil semua inventory movements yang pending dan destination adalah store aktif
            $incomingMovements = DB::table('inventory_movements as im')
                ->join('parties as p', 'im.destination_party_id', '=', 'p.id')
                ->select([
                    'im.id',
                    'im.movement_date',
                    'im.remark',
                    'im.status',
                ])
                ->where('p.store_id', $activeStore['id'])
                ->where('im.status', 'pending')
                ->orderBy('im.movement_date', 'desc')
                ->get();

            $result = [];

            foreach ($incomingMovements as $movement) {
                // Ambil detail items untuk setiap movement
                $movementItems = DB::table('inventory_movement_items as imi')
                    ->join('product_details as pd', 'imi.product_detail_id', '=', 'pd.id')
                    ->join('products as p', 'pd.product_id', '=', 'p.id')
                    ->leftJoin('variants as v', 'pd.variant_id', '=', 'v.id')
                    ->leftJoin('colors as c', 'pd.color_id', '=', 'c.id')
                    ->select([
                        'p.title as product_name',
                        'pd.sku',
                        'v.name as variant_name',
                        'c.label as color_name',
                        'imi.qty',
                    ])
                    ->where('imi.inventory_movement_id', $movement->id)
                    ->get();

                // Hitung total produk dan item
                $totalProducts = $movementItems->count();
                $totalItems    = $movementItems->sum('qty');

                // Format tanggal
                $formattedDate = Carbon::parse($movement->movement_date)->translatedFormat('d F Y');

                $result[] = [
                    'id'             => $movement->id,
                    'movement_date'  => $movement->movement_date,
                    'formatted_date' => $formattedDate,
                    'total_products' => $totalProducts,
                    'total_items'    => $totalItems,
                    'remark'         => $movement->remark ?? '-',
                    'status'         => $movement->status,
                    'items'          => $movementItems->map(function ($item) {
                        return [
                            'product_name' => $item->product_name,
                            'sku'          => $item->sku,
                            'variant'      => $item->variant_name ?? '-',
                            'color'        => $item->color_name ?? '-',
                            'qty'          => $item->qty,
                        ];
                    })->toArray(),
                ];
            }

            return response()->json([
                'success' => true,
                'data'    => $result,
                'message' => 'Data incoming products retrieved successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving incoming products: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function add_incoming_product_to_mutation_list(Request $request)
    {
        RuleHelper::UserHasPermission('create');

        // Cari produk detail milik aktif store yang memiliki color_id atau variant_id sesuai
        $activeStore = Session::get('store', null);
        if (! $activeStore || ! isset($activeStore['id'])) {
            return response()->json(['message' => 'No active store'], 400);
        }

        // Cari inventory movement berdasarkan id
        $movementId = (string) $request->input('movement_id', '');
        if ($movementId === '') {
            return response()->json(['message' => 'Invalid movement_id'], 422);
        }

        $movement = DB::table('inventory_movements')
            ->where('id', $movementId)
            ->where('status', 'pending')
            ->first();

        if (! $movement) {
            return response()->json(['message' => 'Movement not found or not pending'], 404);
        }

        $originParty      = Party::find($movement->source_party_id);
        $destinationParty = Party::find($movement->destination_party_id);

        // Reset session mutation_list
        Session::forget('purchasing_list');

        // Ambil items dari movement dengan product_details dari toko asal (source)
        $sourceItems = DB::table('inventory_movement_items as imi')
            ->join('product_details as pd_source', 'imi.product_detail_id', '=', 'pd_source.id')
            ->join('products as p', 'pd_source.product_id', '=', 'p.id')
            ->leftJoin('variants as v', 'pd_source.variant_id', '=', 'v.id')
            ->leftJoin('colors as c', 'pd_source.color_id', '=', 'c.id')
            ->where('imi.inventory_movement_id', $movementId)
            ->get([
                'pd_source.product_id',
                'pd_source.color_id',
                'pd_source.variant_id',
                'p.title as product_title',
                'v.name as variant_name',
                'c.label as color_label',
                'pd_source.sku',
                'pd_source.price',
                'pd_source.cogm',
                'imi.qty as movement_qty',
            ]);

        $lines = [];

        foreach ($sourceItems as $sourceItem) {
            // Cari product_detail yang sesuai di toko tujuan (destination/active store)
            // berdasarkan kombinasi product_id, color_id, dan variant_id
            $destinationProductDetail = DB::table('product_details as pd')
                ->where('pd.store_id', $activeStore['id'])
                ->where('pd.product_id', $sourceItem->product_id)
                ->where(function ($query) use ($sourceItem) {
                    // Handle case dimana color_id atau variant_id bisa null
                    if ($sourceItem->color_id) {
                        $query->where('pd.color_id', $sourceItem->color_id);
                    } else {
                        $query->whereNull('pd.color_id');
                    }

                    if ($sourceItem->variant_id) {
                        $query->where('pd.variant_id', $sourceItem->variant_id);
                    } else {
                        $query->whereNull('pd.variant_id');
                    }
                })
                ->first(['pd.id', 'pd.qty', 'pd.price', 'pd.cogm']);

            // Jika tidak ditemukan product_detail di toko tujuan, skip item ini
            if (! $destinationProductDetail) {
                continue;
            }

            $lines[] = [
                'id'                         => (string) $destinationProductDetail->id,
                'title'                      => (string) $sourceItem->product_title,
                'variant_name'               => (string) ($sourceItem->variant_name ?? ''),
                'color_label'                => (string) ($sourceItem->color_label ?? ''),
                'sku'                        => (string) ($sourceItem->sku ?? ''),
                'unit_price'                 => (int) ($destinationProductDetail->price ?? 0), // harga dari toko tujuan
                'cogm'                       => (int) ($destinationProductDetail->cogm ?? 0),
                'qty'                        => (int) ($sourceItem->movement_qty ?? 0),        // qty dari movement (toko asal)
                'maxQty'                     => (int) ($destinationProductDetail->qty ?? 999), // stock saat ini di toko tujuan TODO
                'skipQuantityExceedChecking' => true,                                          // lewati cek qty melebihi stock karena ini mutasi masuk
            ];
        }

        // Simpan ke session
        Session::put('purchasing_list', array_values($lines));

        // Hitung subtotal, total units, total products
        $subtotal = 0;
        $units    = 0;
        foreach ($lines as $l) {
            $subtotal += ((int) $l['unit_price']) * ((int) $l['qty']);
            $units += (int) $l['qty'];
        }

        // Kirim response
        return response()->json([
            'lines'   => array_values($lines),
            'summary' => [
                'subtotal'          => (int) $subtotal,
                'units'             => (int) $units,
                'products'          => count($lines),
                'origin_party'      => $originParty,
                'destination_party' => $destinationParty,
                'movement_id'       => $movementId,
            ],
        ]);
    }

    public function trash()
    {
        RuleHelper::UserHasPermission('trash');

        $this->data['inventory_movement_count'] = DB::table('inventory_movements')->whereNotNull('deleted_at')->count();
        return view('backend.pages.purchasing.trash', $this->data);
    }

    public function get_datatable_trash()
    {
        RuleHelper::UserHasPermission('trash');

        $data = DB::table('inventory_movements')
            ->leftJoin('parties as source', 'source.id', '=', 'inventory_movements.source_party_id')
            ->leftJoin('parties as destination', 'destination.id', '=', 'inventory_movements.destination_party_id')
            ->leftJoin('stores', 'stores.id', '=', 'inventory_movements.store_id')
            ->select([
                'inventory_movements.id',
                'inventory_movements.movement_date as movement_date',
                'inventory_movements.movement_type as flow',
                'inventory_movements.source_party_id',
                'inventory_movements.destination_party_id',
                'source.name as source_name',
                'destination.name as destination_name',
                'inventory_movements.remark',
                'stores.name as store',
                'inventory_movements.created_at',
                'inventory_movements.updated_at',
            ])
            ->where('inventory_movements.store_id', session('store.id'))
            ->where(function ($query) {
                $query->where(function ($sq) {
                    $sq->whereNotNull('inventory_movements.source_party_id')
                       ->whereNull('source.store_id');
                })->orWhere(function ($sq) {
                    $sq->whereNotNull('inventory_movements.destination_party_id')
                       ->whereNull('destination.store_id');
                });
            })
            ->whereNotNull('inventory_movements.deleted_at')
            ->get();

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
                $btnView    = "<a href='#' class='btn btn-sm btn-soft-secondary btnDetail' id='" . $data->id . "'><i class='las la-search-plus '></i> View</a>";
                $btnDelete  = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='" . $data->id . "'><i class='las la-trash'></i> Permanent Delete</a>";
                $btnRestore = "<a href='#' class='btn btn-sm btn-soft-secondary btnRestore' id='" . $data->id . "'><i class='ri-restart-line '></i> Restore</a>";
                return $btnView . " " . $btnRestore . " " . $btnDelete;
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->addColumn('flow', function ($data) {
                if ($data->flow == 'in') {
                    return '<span class="badge bg-success">Masuk</span>';
                } else if ($data->flow == 'out') {
                    return '<span class="badge bg-danger">Keluar</span>';
                } else if ($data->flow == 'broken') {
                    return '<badge class="badge bg-warning">Rusak</badge>';
                } else {
                    return '<badge class="badge bg-info">Digunakan</badge>';
                }
            })
            ->addColumn('party', function ($data) {
                if ($data->flow == 'in') {
                    return '<span class="badge bg-warning">' . $data->source_name . '</span> <i class="las la-arrow-right"></i> <span class="badge bg-primary">' . $data->store . '</span>';
                } else if ($data->flow == 'out') {
                    return '<span class="badge bg-primary">' . $data->store . '</span> <i class="las la-arrow-right"></i> <span class="badge bg-warning">' . $data->destination_name . '</span>';
                } else {
                    return '<span class="badge bg-primary">' . $data->store . '</span>';
                }
            })
            ->filter(function ($query) {
                if (request()->has('date') && ! is_null(request('date'))) {
                    $query->where('inventory_movements.date', 'like', "%" . request('date') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs', 'flow', 'party'])
            ->toJson();
    }

    public function delete(Request $request)
    {
        RuleHelper::UserHasPermission('delete');

        $table          = "inventory_movements";
        $column_visible = "movement_date";
        $route          = "purchasing-transaction.destroy";

        return $this->delete_lib->deleteConfirmation($request, $table, $column_visible, $route);
    }

    public function destroy($delete_type)
    {
        RuleHelper::UserHasPermission('delete');

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

                $redirect_url       = "purchasing/trash";
                $inventory_movement = InventoryMovement::findOrFail($row['id']);
                $inventory_movement->forceDelete();

                DB::table('inventory_movement_items')->where('inventory_movement_id', $row['id'])->delete();

                $index++;
            }

            if ($delete_type == "softdelete") {
                $redirect_url = "product-mutation";

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

        $data = DB::table('inventory_movements')
            ->whereIn('id', $request->id_to_be_restored)
            ->orderBy('deleted_at', 'desc')
            ->get();

        $this->data['index']       = 0;
        $this->data['data']        = $data;
        $this->data['route']       = 'purchasing-transaction.restore_action';
        $this->data['column_name'] = 'movement_date';

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
            InventoryMovement::where('id', $row->id)
                ->update(
                    [
                        'deleted_at'  => null,
                        'deleted_by'  => null,
                        'restored_at' => date('Y-m-d H:i:s'),
                        'restored_by' => $user->email,
                    ]
                );
            $index++;
        }

        Session::flash('flash_message', $index . ' Record[s] restored successfully');

        return redirect('purchasing-transaction');
    }

    public function print_thermal_58($id)
    {
        \RuleHelper::UserHasPermission('read');

        // Transaksi purchasing utama (menggunakan model InventoryMovement)
        $movement = \App\Models\InventoryMovement::with(['sourceParty', 'destinationParty'])->findOrFail($id);

        // Logo SVG untuk header
        $logoSrc = null;
        $logoPath = public_path('seco-logo.svg');
        if (is_file($logoPath)) {
            $logoSrc = 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($logoPath));
        }

        // Footer Receipt
        $footerText = \App\Models\Setting::where('key', 'receipt_footer')
                        ->value('value') ?? 'Terima kasih';

        // Ambil item mutasi
        $items = DB::table('inventory_movement_items')
            ->join('product_details', 'product_details.id', '=', 'inventory_movement_items.product_detail_id')
            ->join('products', 'products.id', '=', 'product_details.product_id')
            ->join('colors', 'colors.id', '=', 'product_details.color_id')
            ->join('variants', 'variants.id', '=', 'product_details.variant_id')
            ->select([
                'products.title as product_title',
                'colors.label as color',
                'variants.name as variant',
                'inventory_movement_items.qty',
            ])
            ->where('inventory_movement_items.inventory_movement_id', $id)
            ->get();

        $data = [
            'movement'     => $movement,
            'items'        => $items,
            'logoSrc'      => $logoSrc,
            'footerText'   => $footerText,
        ];

        // Karena format cetaknya sama dengan mutasi, kita bisa re-use view dari mutasi, 
        // atau buat view tersendiri di purchasing
        return view('backend.pages.product_mutation.print_thermal_html', $data);
    }
}
