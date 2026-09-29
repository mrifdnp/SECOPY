<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use App\Models\SalesTransaction;
use App\Models\Module;

use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesTransactionExport;
use Request as Req;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use App\Models\SalesTransactionItem;
use App\Models\SalePerson;


class SalesTransactionEcommerceController extends BackendController
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

        $this->data['sale_transactions_count'] = SalesTransaction::whereNotNull('deleted_at')->where('channel','ecommerce')->count();
        $this->data['sale_people'] = SalePerson::whereNull('deleted_at')->orderBy('name')->get();


        $this->data['url'] = 'sales-transaction';
        $tgl = null;
        $dateNow = Carbon::now()->toDateString();
        $this->data['dateNow'] = $this->formatDate($dateNow, $tgl, 3);

        // Xendit Dormant Warning Logic
        $lastTrx = SalesTransaction::where('channel', 'ecommerce')->orderBy('created_at', 'desc')->value('created_at');
        $this->data['xendit_days_inactive'] = 0;
        if ($lastTrx) {
            $this->data['xendit_days_inactive'] = (int) Carbon::parse($lastTrx)->startOfDay()->diffInDays(Carbon::now()->startOfDay());
        }

        return view('backend.pages.sale_transaction_ecommerce.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // 
    }

    public function listFilteredProducts(Request $request)
    {
        $mainCategoryId = (string) $request->query('main_category_id', '');
        $mainCategory   = (string) $request->query('main_category', '');
        $keyword        = trim((string) $request->query('q', ''));

        $priceSub = DB::table('product_details')
            ->select('product_id', DB::raw('MIN(price) AS price'))
            ->whereNotNull('price')->where('price', '>', 0)
            ->groupBy('product_id');

        $q = DB::table('products')
            ->leftJoinSub($priceSub, 'pd', function ($j) { $j->on('pd.product_id', '=', 'products.id'); })
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
                $w->whereRaw('LOWER(products.title) LIKE ?', [$like])  // fokus: product title
                ->orWhereRaw('LOWER(c.title) LIKE ?',        [$like]) // opsional: subcategory
                ->orWhereRaw('LOWER(mc.title) LIKE ?',       [$like]); // opsional: main category
            });
        }

        $rows = $q->orderByDesc('products.top_pick')
            ->orderBy('products.title')->limit(100)->get([
            'products.id',
            'products.title AS product_title',
            'pd.price',
            'products.content_paths',
            'products.top_pick',
            'c.title  AS category_title',
            'mc.title AS main_category_title',
        ]);

        $products = $rows->map(function ($r) {
            $paths = json_decode($r->content_paths, true) ?: [];
            $find  = function ($name) use ($paths) {
                $item = collect($paths)->firstWhere('name', $name);
                return ($item && !empty($item['path'])) ? url(Storage::url($item['path'])) : null;
            };
            return [
                'id'             => $r->id,
                'product_title'  => $r->product_title,
                'category_title' => $r->category_title,
                'price'          => (int) ($r->price ?? 0),
                'top_pick'       => (int) ($r->top_pick ?? 0),
                'main_pic'       => $find('main_pic'),
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

        if ($productId === '') 
        { 
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

        // Detail kombinasi (sku, price, stok, varian, warna)
        $rows = DB::table('product_details as pd')
            ->leftJoin('variants as v', 'v.id', '=', 'pd.variant_id')
            ->leftJoin('colors   as c', 'c.id', '=', 'pd.color_id')
            ->where('pd.product_id', $productId)
            ->where('pd.price', '>', 0)
            ->orderBy('v.name')->orderBy('c.label')
            ->get([
                'pd.id               as product_detail_id',
                'pd.sku',
                'pd.price',
                'pd.qty              as quantity_available',
                'pd.variant_id',
                'pd.color_id',
                'v.name              as variant_name',
                'c.label             as color_label',
                'c.hexa              as color_hexa',
            ]);

        $items = $rows->map(fn($r) => [
            'product_detail_id'  => (string) $r->product_detail_id,
            'sku'                => (string) ($r->sku ?? ''),
            'price'              => (float)  $r->price,
            'quantity_available' => (int)    ($r->quantity_available ?? 0),
            'variant_id'         => (string) ($r->variant_id ?? ''),
            'variant_name'       => (string) ($r->variant_name ?? ''),
            'color_id'           => (string) ($r->color_id ?? ''),
            'color_label'        => (string) ($r->color_label ?? ''),
            'color_hexa'         => $r->color_hexa ? (string) $r->color_hexa : null,
        ]);

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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 
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

        // Informasi metode pembayaran & customer
        $paymentType = !empty($trx->payment_type) ? \App\Models\PaymentType::find($trx->payment_type) : null;
        $customer    = !empty($trx->customer_id) ? \App\Models\Customer::find($trx->customer_id) : null;

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
            'display_time'            => \Carbon\Carbon::parse($ts)->format('d F Y H:i:s'),
            'is_paid'                 => $isPaid,
        ];

        return view('backend.pages.sale_transaction.show', $this->data);
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


        return view('backend.pages.sale_transaction_ecommerce.edit', $this->data);
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
        //
    }

    public function updatePayment(Request $request, $id)
    {
        \RuleHelper::UserHasPermission('update');

        $transaction = SalesTransaction::findOrFail($id);

        $oldStatus = $transaction->getOriginal('payment_status');

        $transaction->payment_status = $request->payment_status;

        if ($request->filled('sale_people_id')) {
            $transaction->sale_people_id = $request->sale_people_id;
        }

        if ($request->payment_status === 'paid') {
            if (is_null($transaction->paid_at)) {
                $transaction->paid_at = $request->paid_at ?? now();
            }
            $transaction->resi = null;
        } elseif ($request->payment_status === 'shipping') {
            $transaction->resi = $request->resi;
            $transaction->paid_at = $request->paid_at ?? now();
        }
        elseif ($request->payment_status === 'completed') {
            $transaction->paid_at = $request->paid_at ?? now();
        }
        else {
            $transaction->paid_at = null;
            $transaction->resi = null;
        }

        $transaction->save();

        // Restore stok jika canceled dan sebelumnya belum pernah canceled/expired/failed
        if ($request->payment_status === 'canceled' && !in_array($oldStatus, ['canceled', 'EXPIRED', 'FAILED'])) {
            $items = SalesTransactionItem::where('sales_transaction_id', $transaction->id)->get();
            foreach ($items as $item) {
                $detail = \App\Models\ProductDetail::find($item->product_detail_id);
                if ($detail) {
                    $detail->increment('qty', $item->qty);
                }
            }
        }

        return response()->json(['success' => true]);
    }

    public function trash()
    {
        \RuleHelper::UserHasPermission('trash');


        $this->data['sale_transactions_count'] = DB::table('sales_transactions')->whereNotNull('deleted_at')->where('channel', 'ecommerce')->count();
        return view('backend.pages.sale_transaction_ecommerce.trash', $this->data);
    }

    public function get_datatable()
    {
        \RuleHelper::UserHasPermission('access');

        $user = Auth::user();
        $data = SalesTransaction::select([
            'id',
            'date',
            'invoice_id',
            'customer_id',
            'total',
            'channel',
            'payment_status',
            'created_at',
            'deleted_at',
            'deleted_by',
            'updated_at',
            'courier',
            'paid_at',
            'resi',
            'sale_people_id'
        ])->with('customer')->whereNull('deleted_at')->where('channel', 'ecommerce');

        $datatables = DataTables::of($data);

        return $datatables
            ->addColumn('checkboxs', function ($data) {
                $checkbox = " <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>";

                return $checkbox;
            })
            ->addColumn('customer', function ($data) {
                if ($data->customer) {
                    $name = $data->customer->first_name . ' ' . $data->customer->last_name;
                    $address = $data->customer->address ?? '-';
                    return "<div>$name<br><small class='text-muted'>$address</small></div>";
                }
                return '-';
            })

            ->addColumn('number', function ($data) {
                return "";
            })
            ->editColumn('courier', function ($data) {
                if(!empty($data->courier['name'])){
                    return $data->courier['name'];
                }else{
                    return '-';
                }
            })
            ->addColumn('action', function ($data) {
                $disabled = '';
                $btnEdit = '';
                $btnDelete = '';
                if (\RuleHelper::UserHasPermission('update', false) == false && \RuleHelper::UserHasPermission('delete', false) == false) {
                    $disabled = 'disabled';
                }

                if (\RuleHelper::UserHasPermission('update', false) == true) {
                    $btnEdit = "<a href='#' 
                    data-id='" . $data->id . "' 
                    data-status='" . $data->payment_status . "' 
                    data-paid='" . $data->paid_at . "' 
                    data-resi='" . $data->resi . "'
                    data-sale-people-id='" . ($data->sale_people_id ?? '') . "'
                    class='btn btn-sm btn-soft-secondary btnEditPayment' 
                    title='Edit Payment'>
                    <i class='las la-credit-card'></i></a>";

                }

                if (\RuleHelper::UserHasPermission('delete', false) == true) {
                    $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='" . $data->id . "' title='Delete'><i class='las la-trash '></i></a>";
                }
                $btnView = "<a href='" . route('ecommerce-transaction.show', $data->id) . "' class='btn btn-sm btn-soft-secondary' title='Detail'><i class='las la-search-plus '></i></a>";
                
                $btnNotify = '';
                if ($data->payment_status === 'shipping') {
                    $courierName = is_array($data->courier) && array_key_exists('name', $data->courier) ? $data->courier['name'] : '-';
                    $custPhone = $data->customer->phone ?? '';
                    $custEmail = $data->customer->email ?? '';
                    $custName = ($data->customer->first_name ?? '') . " " . ($data->customer->last_name ?? '');
                    $btnNotify = "<a href='#' class='btn btn-sm btn-soft-info btnNotifyCustomer' data-bs-toggle='modal' data-bs-target='#modalNotifyCustomer' data-invoice='" . $data->invoice_id . "' data-resi='" . $data->resi . "' data-courier='" . $courierName . "' data-phone='" . $custPhone . "' data-email='" . $custEmail . "' data-name='" . htmlspecialchars(trim($custName), ENT_QUOTES) . "' title='Email Customer'><i class='las la-paper-plane'></i></a>";
                }

                return
                    $btnView . ' ' . $btnEdit . ' ' . $btnNotify . ' ' . $btnDelete;
            })
            ->editColumn('total', function ($data) {
                if ($data->total == '' || $data->total == null || $data->total == 0)
                    return 'Rp. 0';
                return '<div class="list-group-item list-group-item-primary px-2"> Rp. ' . $this->rupiah($data->total, 0, ',', '.').' </div>';
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
                if (request()->has('total')  && !is_null(request('total'))) {
                    $total = str_replace('.', '', request('total'));

                    $query->where('total', 'like', "%" . $total . "%");
                }
                if (request()->has('channel')  && !is_null(request('channel'))) {
                    $query->where('channel', 'like', "%" . request('channel') . "%");
                }
                if (request()->has('payment_status')  && !is_null(request('payment_status'))) {
                    $query->where('payment_status', 'like', "%" . request('payment_status') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs', 'payment_status', 'customer','total'])
            ->toJson();
    }

    public function get_datatable_trash()
    {
        \RuleHelper::UserHasPermission('trash');
        $data = SalesTransaction::select([
            'id',
            'date',
            'invoice_id',
            'customer_id',
            'total',
            'channel',
            'payment_status',
            'created_at',
            'deleted_at',
            'deleted_by',
            'updated_at',
            'courier',
            'paid_at',
        ])->with('customer')->whereNotNull('deleted_at')->where('channel', 'ecommerce');
        $datatables = DataTables::of($data);

        return $datatables
            ->addColumn('checkboxs', function ($data) {
                return "
                    <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>
                ";
            })
            ->addColumn('customer', function ($data) {
                if ($data->customer) {
                    $name = $data->customer->first_name . ' ' . $data->customer->last_name;
                    $address = $data->customer->address ?? '-';
                    return "<div>$name<br><small class='text-muted'>$address</small></div>";
                }
                return '-';
            })
            ->addColumn('action', function ($data) {
                $btnView = "<a href='" . route('ecommerce-transaction.show', $data->id) . "' class='btn btn-sm btn-soft-secondary'><i class='las la-search-plus '></i> View</a>";
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
            ->editColumn('total', function ($data) {
                if ($data->total == '' || $data->total == null || $data->total == 0)
                    return 'Rp. 0';
                return 'Rp. ' . $this->rupiah($data->total, 0, ',', '.');
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
                            <span class='badge bg-success ms-1'>".$paidAt."</span>
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
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
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
                if (request()->has('total')  && !is_null(request('total'))) {
                    $total = str_replace('.', '', request('total'));

                    $query->where('total', 'like', "%" . $total . "%");
                }
                if (request()->has('payment_status')  && !is_null(request('payment_status'))) {
                    $query->where('payment_status', 'like', "%" . request('payment_status') . "%");
                }
            })
            ->rawColumns(['action', 'checkboxs', 'payment_status', 'customer'])
            ->toJson();
    }

    public function delete(Request $request)
    {
        \RuleHelper::UserHasPermission('delete');

        $table = "sales_transactions";
        $column_visible = "invoice_id";
        $route = "ecommerce-transaction.destroy";

        return $this->delete_lib->deleteConfirmation($request, $table, $column_visible, $route);
    }

    public function destroy($delete_type)
    {
        \RuleHelper::UserHasPermission('delete');
        $user = Auth::user();
        $dependencies_data = session('dependencies_data');
        if (empty($dependencies_data)) {
            \Session::flash('flash_message', 'Session expired, silakan ulangi proses delete.');
            return redirect()->route('ecommerce-transaction.index');
        }
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

                $redirect_url = "ecommerce-transaction.trash";
                $sale_transaction = SalesTransaction::findOrFail($row['id']);


                $sale_transaction->forceDelete();
                $index++;
            }

            if ($delete_type == "softdelete") {
                $redirect_url = "ecommerce-transaction";

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
        $this->data['route'] = 'ecommerce-transaction.restore_action';
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

        return redirect('ecommerce-transaction');
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

    public function sendEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'invoice' => 'required',
            'resi' => 'required',
            'courier' => 'required',
            'name' => 'required',
        ]);

        $key = 'send-email-fail-' . $request->ip() . '-' . $request->invoice;

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);
            return response()->json([
                'success' => false, 
                'message' => "Terlalu banyak percobaan gagal. Silakan coba lagi dalam $seconds detik."
            ], 429);
        }

        try {
            $data = $request->only(['invoice', 'resi', 'courier', 'name']);
            
            // Fetch transaction items for email body
            $transaction = \App\Models\SalesTransaction::with(['customer', 'paymentType'])
                ->where('invoice_id', $request->invoice)
                ->first();
            
            $data['transaction'] = $transaction;
            $data['items'] = [];
            
            if ($transaction) {
                $txItems = \App\Models\SalesTransactionItem::where('sales_transaction_id', $transaction->id)
                    ->whereNull('deleted_at')
                    ->get();
                    
                foreach ($txItems as $txItem) {
                    $snapshot = is_array($txItem->getAttributes()['product_detail'] ?? null) 
                        ? $txItem->getAttributes()['product_detail'] 
                        : json_decode($txItem->getAttributes()['product_detail'] ?? '{}', true);
                    
                    $pd = \App\Models\ProductDetail::withTrashed()->with('product')->find($txItem->product_detail_id);
                    $product = $pd ? $pd->product : null;
                    
                    $data['items'][] = [
                        'name' => $snapshot['product_title'] ?? ($product ? $product->title : 'Produk Seco'),
                        'qty' => $txItem->qty,
                        'price' => $txItem->price ?? 0,
                        'final_price' => $txItem->final_price ?? 0,
                        'image' => $product ? $product->main_pic : null,
                    ];
                }
            }

            if ($request->email_type === 'received') {
                \Illuminate\Support\Facades\Mail::to($request->email)->send(new \App\Mail\OrderReceivedMail($data));
            } else {
                \Illuminate\Support\Facades\Mail::to($request->email)->send(new \App\Mail\ShippingNotificationMail($data));
            }
            
            \Illuminate\Support\Facades\RateLimiter::clear($key);
            
            return response()->json(['success' => true, 'message' => 'Email berhasil dikirim']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\RateLimiter::hit($key, 60); // Block for 60 seconds after 3 fails
            return response()->json(['success' => false, 'message' => 'Gagal mengirim email: ' . $e->getMessage()], 500);
        }
    }
}
