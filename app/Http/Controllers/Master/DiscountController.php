<?php
namespace App\Http\Controllers\Master;

use App\Exports\DiscountExport;
use App\Helpers\RuleHelper;
use App\Http\Controllers\Core\BackendController;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Module;
use App\Models\ProductDetail;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as Req;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class DiscountController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['page_title']  = "Discount Page";
        $this->data['titleHeader'] = Module::where('url', '=', Req::segment(1))->first(['icon', 'name', 'url']);

        // Set locale for Carbon
        Carbon::setLocale('id');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        RuleHelper::UserHasPermission('access');

        $this->data['discounts_count'] = Discount::whereNotNull('deleted_at')->count();

        $this->data['url']     = 'discount';
        $tgl                   = null;
        $dateNow               = Carbon::now()->toDateString();
        $this->data['dateNow'] = $this->formatDate($dateNow, $tgl, 3);

        return view('backend.pages.discount.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        RuleHelper::UserHasPermission('create');

        $tgl                   = null;
        $dateNow               = Carbon::now()->toDateString();
        $this->data['dateNow'] = $this->formatDate($dateNow, $tgl, 3);

        return view('backend.pages.discount.create', $this->data);
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
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $discount             = new Discount();
        $discount->id         = ucwords($request->id);
        $discount->name       = ucwords($request->name);
        $discount->type       = $request->type;
        $tglDiscount          = null;
        $discount->start_date = $this->formatDate($request->start_date, $tglDiscount, 3);
        $tglDiscount          = null;
        $discount->end_date   = $this->formatDate($request->end_date, $tglDiscount, 3);
        $discount->value      = ucwords($request->value);
        $discount->created_by = Auth::user()->email;
        $simpan               = $discount->save();

        if ($simpan) {
            Session::flash('flash_message', 'New record created successfully');

            return redirect()->route('discount.create');
        } else {
            Session::flash('error_message', 'New record failed to created');

            return redirect()->route('discount.create');
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
        RuleHelper::UserHasPermission('read');
        $discount = Discount::findOrFail($id);

        $typeKey        = "percent,nominal";
        $typeValue      = "Percent,Nominal";
        $typeArrayKey   = explode(',', $typeKey);
        $typeArrayValue = explode(',', $typeValue);
        foreach ($typeArrayKey as $kunci => $nil) {
            if ($nil == $discount->type) {
                $discount->type = $typeArrayValue[$kunci];
            }
        }

        $this->data['discount'] = $discount;

        return view('backend.pages.discount.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        RuleHelper::UserHasPermission('update');

        $authUser = Auth::user();

        // Ambil store yang dimiliki user
        $userStore = json_decode($authUser->store_id ?? '[]', true);

        $discount   = Discount::findOrFail($id);
        $categories = Category::whereNull('deleted_at')->get(['id', 'title']);
        $stores     = Store::whereNull('deleted_at')
            ->whereIn('id', $userStore)
            ->get(['id', 'name']);

        $this->data['discount']   = $discount;
        $this->data['categories'] = $categories;
        $this->data['stores']     = $stores;

        return view('backend.pages.discount.edit', $this->data);
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
        RuleHelper::UserHasPermission('update');

        $validator = Validator::make($request->all(), [
            'name'       => 'required|string',
            'type'       => 'required|string|in:percent,nominal',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'value'      => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error validation',
                'message' => 'Terdapat kesalahan pada inputan.',
                'errors'  => $validator->errors(),
            ]);
        }

        $discount             = Discount::findOrFail($id);
        $discount->name       = ucwords($request->name);
        $discount->type       = $request->type;
        $discount->start_date = $request->start_date;
        $discount->end_date   = $request->end_date;
        $discount->value      = $request->value;
        $discount->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil diperbarui.',
        ], 200);
    }

    public function search_product(Request $request)
    {
        $keyword    = $request->get('keyword');
        $categoryId = $request->get('category_id');
        $storeId    = $request->get('store_id');
        $discountId = $request->get('discount_id');

        $authUser = Auth::user();

        // Ambil store yang dimiliki user
        $userStore = json_decode($authUser->store_id ?? '[]', true);

        // Cari produk yang sudah di tambahkan ke diskon
        $existingProductIds = DB::table('discount_products')
            ->where('discount_id', $discountId)
            ->pluck('product_detail_id')
            ->toArray();

        $products = ProductDetail::with(['product', 'variant', 'color', 'store' => fn($query) => $query->whereIn('id', $userStore)])
            ->whereIn('store_id', $userStore)
            ->whereNotIn('id', $existingProductIds)
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('product', function ($q2) use ($keyword) {
                        $q2->whereRaw('title ILIKE ?', ['%' . $keyword . '%']);
                    })
                        ->orWhereRaw('sku ILIKE ?', ['%' . $keyword . '%']);
                });
            })
            ->when($categoryId, function ($query, $categoryId) {
                $query->whereHas('product', function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            })
            ->when($storeId, function ($query, $storeId) {
                $query->where('store_id', $storeId);
            })
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get();

        return response()->json([
            'status'   => 'success',
            'products' => $products,
        ], 200);
    }

    public function add_product_discount(Request $request)
    {
        RuleHelper::UserHasPermission('update');

        $validator = Validator::make($request->all(), [
            'discount_products'                     => 'required|array|min:1',
            'discount_products.*.discount_id'       => 'required|uuid|exists:discounts,id',
            'discount_products.*.product_detail_id' => 'required|uuid|exists:product_details,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error validation',
                'message' => 'Terdapat kesalahan pada inputan.',
                'errors'  => $validator->errors(),
            ]);
        }

        $discountProducts = $request->input('discount_products', []);

        // Menambahkan uuuid, created_at, dan updated_at
        $discountProducts = collect($discountProducts)->map(function ($item) {
            return array_merge($item, [
                'id'         => Str::uuid()->toString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        })->toArray();

        // Bulk insert while ignoring duplicates
        DB::table('discount_products')->insertOrIgnore($discountProducts);

        return response()->json([
            'status'  => 'success',
            'message' => 'Produk berhasil ditambahkan ke diskon.',
        ], 201);
    }

    public function remove_product_discount(Request $request, $id)
    {
        RuleHelper::UserHasPermission('update');

        $validator = Validator::make($request->all(), [
            'product_detail_ids'   => 'required|array|min:1',
            'product_detail_ids.*' => 'required|uuid|exists:product_details,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error validation',
                'message' => 'Terdapat kesalahan pada inputan.',
                'errors'  => $validator->errors(),
            ]);
        }

        $productDetailIds = $request->input('product_detail_ids', []);

        // Hapus data dari tabel discount_products berdasarkan discount_id dan product_detail_id
        DB::table('discount_products')
            ->where('discount_id', $id)
            ->whereIn('product_detail_id', $productDetailIds)
            ->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Produk berhasil dihapus dari diskon.',
        ], 200);
    }

    public function get_product_discount_datatable($id)
    {
        RuleHelper::UserHasPermission('update');

        $productDetailIds = DB::table('discount_products')
            ->where('discount_id', $id)
            ->pluck('product_detail_id')
            ->toArray();

        $data = ProductDetail::with(['product', 'variant', 'color', 'store', 'discounts' => function ($query) use ($id) {
            $query->byId($id);
        }])->whereIn('id', $productDetailIds);

        $datatables = DataTables::of($data);

        return $datatables
            ->addColumn('checkboxs', function ($data) {
                $checkbox = <<< Checkbox
                    <input class='product-datatable-sub-checkbox' type='checkbox' data-product-detail-id='{$data->id}' data-product-name='{$data->product->title}' data-variant-name='{$data->variant->name}' data-color-label='{$data->color->label}' data-store-name='{$data->store->name}' data-sku='{$data->sku}'>
                Checkbox;

                return $checkbox;
            })
            ->addColumn('number', function ($data) {
                return "";
            })
            ->editColumn('name', function ($data) {
                $product = <<< Product
                    <div class="d-flex align-items-center gap-3">
                        <img src="{$data->product->main_pic}" alt="Product Image" class="avatar">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{$data->product->title}</h6>
                            <p class="text-muted mb-0" style="font-size: 11px">Varian: {$data->variant->name}, Warna: {$data->color->label}</p>
                            <p class="text-muted mb-0" style="font-size: 11px">SKU: {$data->sku}</p>
                            <p class="text-muted mb-0" style="font-size: 11px">Toko: {$data->store->name}</p>
                        </div>
                    </div>
                Product;

                return $product;
            })
            ->addColumn('action', function ($data) {
                $discount = $data->discounts->first();

                $btnDelete = '';
                if (RuleHelper::UserHasPermission('update', false) == true) {
                    $btnDelete = <<<DeleteButton
                        <a href='#' class='btn btn-sm btn-soft-secondary delete-product-discount' data-product-detail-id='{$data->id}' data-discount-id='{$discount->id}' data-product-name='{$data->product->title}' data-variant-name='{$data->variant->name}' data-color-label='{$data->color->label}' data-store-name='{$data->store->name}' data-sku='{$data->sku}'>
                            <i class='las la-trash '></i>
                        </a>
                    DeleteButton;
                }

                return $btnDelete;
            })
            ->editColumn('price', function ($data) {
                return 'Rp. ' . $this->rupiah($data->price);
            })
            ->addColumn('discounted_price', function ($data) use ($id) {
                $discount = $data->discounts->first();
                if ($discount) {
                    if ($discount->type == 'percent') {
                        $discountedPrice = $data->price - ($data->price * $discount->value / 100);
                    } elseif ($discount->type == 'nominal') {
                        $discountedPrice = $data->price - $discount->value;
                    } else {
                        $discountedPrice = $data->price;
                    }

                    return 'Rp. ' . $this->rupiah($discountedPrice);
                }
                return 'Rp. ' . $this->rupiah($data->price);
            })
            ->rawColumns(['action', 'checkboxs', 'name'])
            ->toJson();
    }

    public function trash()
    {
        RuleHelper::UserHasPermission('trash');

        $this->data['discounts_count'] = DB::table('discounts')->whereNotNull('deleted_at')->count();
        return view('backend.pages.discount.trash', $this->data);
    }

    public function get_datatable()
    {
        RuleHelper::UserHasPermission('access');

        $data = Discount::select([
            'id',
            'name',
            'type',
            'start_date',
            'end_date',
            'value',
            'created_at',
            'deleted_at',
            'deleted_by',
            'updated_at',
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
                $btnEdit   = '';
                $btnDelete = '';

                if (RuleHelper::UserHasPermission('update', false) == true) {
                    $btnEdit = "<a href='" . route('discount.edit', ['id' => $data->id]) . "' class='btn btn-sm btn-soft-secondary btnEdit' data-id='" . $data->id . "' title='Edit'><i class='las la-edit '></i></a>";
                }

                if (RuleHelper::UserHasPermission('delete', false) == true) {
                    $btnDelete = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='" . $data->id . "' title='Delete'><i class='las la-trash '></i></a>";
                }

                return $btnEdit . ' ' . $btnDelete;
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->translatedFormat('d F Y H:i:s');
            })
            ->editColumn('type', function ($data) {
                $type = null;
                if ('percent' == $data->type) {
                    $type = 'Persentase';
                }
                if ('nominal' == $data->type) {
                    $type = 'Nominal';
                }
                return $type;
            })
            ->editColumn('value', function ($data) {
                if ('percent' == $data->type) {
                    return "<span class='badge bg-primary' >" . $data->value . "%</span>";
                }

                if ('nominal' == $data->type) {
                    return "<span class='badge bg-secondary' >Rp. " . $this->rupiah($data->value) . "</span>";
                }

                return $data->value;
            })
            ->editColumn('start_date', function ($data) {
                return \Carbon\Carbon::parse($data->start_date)->translatedFormat('d F Y');
            })
            ->editColumn('end_date', function ($data) {
                return \Carbon\Carbon::parse($data->end_date)->translatedFormat('d F Y');
            })
            ->filterColumn('deleted_at', function ($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('name') && ! is_null(request('name'))) {
                    $query->where('name', 'like', "%" . request('name') . "%");
                }

                if (request()->has('type') && ! is_null(request('type'))) {
                    $query->where('type', 'like', "%" . request('type') . "%");
                }

                if (request()->has('status') && ! is_null(request('status'))) {
                    switch (request('status')) {
                        case 'active':
                            $query->whereDate('start_date', '<=', now())
                                ->whereDate('end_date', '>=', now());
                            break;
                        case 'incoming':
                            $query->whereDate('start_date', '>', now());
                            break;
                        case 'done':
                            $query->whereDate('end_date', '<', now());
                            break;
                    }
                }
            })
            ->rawColumns(['action', 'checkboxs', 'value'])
            ->toJson();
    }

    public function get_datatable_trash()
    {
        RuleHelper::UserHasPermission('trash');
        $data = Discount::select([
            'id',
            'name',
            'type',
            'start_date',
            'end_date',
            'value',
            'created_at',
            'deleted_at',
            'deleted_by',
            'updated_at',
        ])->whereNotNull('deleted_at');
        $datatables = DataTables::of($data);

        return $datatables
            ->addColumn('checkboxs', function ($data) {
                return "
                    <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>
                ";
            })
            ->addColumn('action', function ($data) {
                $btnView    = "<a href='" . route('discount.show', $data->id) . "' class='btn btn-sm btn-soft-secondary'><i class='las la-search-plus '></i> View</a>";
                $btnDelete  = "<a href='#' class='btn btn-sm btn-soft-secondary btnDelete' id='" . $data->id . "'><i class='las la-trash'></i> Permanent Delete</a>";
                $btnRestore = "<a href='#' class='btn btn-sm btn-soft-secondary btnRestore' id='" . $data->id . "'><i class='ri-restart-line '></i> Restore</a>";
                return $btnView . " " . $btnRestore . " " . $btnDelete;
            })
            ->addColumn('number', function ($data) {
                return "";
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->translatedFormat('d F Y H:i:s');
            })
            ->editColumn('type', function ($data) {
                $type = null;
                if ('percent' == $data->type) {
                    $type = 'Persentase';
                }
                if ('nominal' == $data->type) {
                    $type = 'Nominal';
                }
                return $type;
            })
            ->editColumn('value', function ($data) {
                if ('percent' == $data->type) {
                    return "<span class='badge bg-primary' style='font-size: 14px;'>" . $data->value . "%</span>";
                }

                if ('nominal' == $data->type) {
                    return "<span class='badge bg-secondary' style='font-size: 14px;'>Rp. " . $this->rupiah($data->value) . "</span>";
                }

                return $data->value;
            })
            ->editColumn('start_date', function ($data) {
                return \Carbon\Carbon::parse($data->start_date)->translatedFormat('d F Y');
            })
            ->editColumn('end_date', function ($data) {
                return \Carbon\Carbon::parse($data->end_date)->translatedFormat('d F Y');
            })
            ->filterColumn('deleted_at', function ($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('name') && ! is_null(request('name'))) {
                    $query->where('name', 'like', "%" . request('name') . "%");
                }

                if (request()->has('type') && ! is_null(request('type'))) {
                    $query->where('type', 'like', "%" . request('type') . "%");
                }

                if (request()->has('status') && ! is_null(request('status'))) {
                    switch (request('status')) {
                        case 'active':
                            $query->whereDate('start_date', '<=', now())
                                ->whereDate('end_date', '>=', now());
                            break;
                        case 'incoming':
                            $query->whereDate('start_date', '>', now());
                            break;
                        case 'done':
                            $query->whereDate('end_date', '<', now());
                            break;
                    }
                }
            })
            ->rawColumns(['action', 'checkboxs', 'value'])
            ->toJson();
    }

    public function delete(Request $request)
    {
        RuleHelper::UserHasPermission('delete');

        $table          = "discounts";
        $column_visible = "name";
        $route          = "discount.destroy";

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

                $redirect_url = "discount/trash";
                $discount     = Discount::findOrFail($row['id']);

                $discount->forceDelete();
                $index++;
            }

            if ($delete_type == "softdelete") {
                $redirect_url = "discount";

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

        $data = DB::table('discounts')
            ->whereIn('id', $request->id_to_be_restored)
            ->orderBy('deleted_at', 'desc')
            ->get();

        $this->data['index']       = 0;
        $this->data['data']        = $data;
        $this->data['route']       = 'discount.restore_action';
        $this->data['column_name'] = 'name';

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
            Discount::where('id', $row->id)
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

        return redirect('discount/trash');
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

        return Excel::download(new DiscountExport($columnExportData['columnDepedencies'], $columnExportData['columnExtraAttribute']), 'discounts.xlsx');
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
