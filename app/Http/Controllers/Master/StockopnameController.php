<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use App\Models\Stockopname;
use App\Models\Module;
use App\Models\Store;

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
use App\Exports\StockopnameExport;
use Request as Req;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use App\Models\ProductDetail;
use App\Models\ProductStockMovement;

class StockopnameController extends BackendController
{
    function __construct()
    {
        parent::__construct();
        $this->data['page_title'] = "Stockopname Page";
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

        $this->data['stockopnames_count'] = Stockopname::whereNotNull('deleted_at')->where('store_id', session('store.id'))->count();
        $this->data['stores'] = DB::table('stores')->whereNull('deleted_at')->get();


        $this->data['url'] = 'stockopname';
        $tgl = null;
        $dateNow = Carbon::now()->toDateString();
        $this->data['dateNow'] = $this->formatDate($dateNow, $tgl, 1);

        return view('backend.pages.stockopname.index', $this->data);
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
        $this->data['dateNow'] = $this->formatDate($dateNow, $tgl, 1);
        $this->data['stores'] = DB::table('stores')->whereNull('deleted_at')->get();


        return view('backend.pages.stockopname.create', $this->data);
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
            'title' => 'required|string',
            'date_start' => 'required|date',
            'date_end' => 'required|date'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $stockopname = new Stockopname();
        $stockopname->id = ucwords($request->id);
        $stockopname->title = ucwords($request->title);
        $tglStockopname = null;
        $stockopname->date_start = $this->formatDate($request->date_start, $tglStockopname, 1);
        $tglStockopname = null;
        $stockopname->date_end = $this->formatDate($request->date_end, $tglStockopname, 1);
        $stockopname->store_id = session('store.id');
        $stockopname->created_by = Auth::user()->email;
        $simpan = $stockopname->save();

        if ($simpan) {
            \Session::flash('flash_message', 'New record created successfully');

            return redirect()->route('stockopname.create');
        } else {
            \Session::flash('error_message', 'New record failed to created');

            return redirect()->route('stockopname.create');
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
        $stockopname = Stockopname::findOrFail($id);


        $categories = Category::whereNull('deleted_at')->get(['id', 'title']);
        $this->data['categories'] = $categories;
       

        $this->data['stockopname'] = $stockopname;
       

       

        $product_list_opname = ProductStockMovement::join('product_details', 'product_stock_movements.product_detail_id', '=', 'product_details.id')
            ->join('products', 'products.id', '=', 'product_details.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->join('main_categories', 'main_categories.id', '=', 'categories.main_categories_id')
            ->join('colors', 'colors.id', '=', 'product_details.color_id')
            ->join('variants', 'variants.id', '=', 'product_details.variant_id')
            ->join('stores', 'stores.id', '=', 'product_details.store_id')
            ->select([
                'product_details.id as id',
                'product_details.sku',
                'products.title as product_title',
                'categories.title as category_title',
                'main_categories.title as main_category_title',
                'colors.label as color_title',
                'variants.name as variant_title',
                'product_details.qty',
                'product_stock_movements.qty as opname_qty',
                'products.content_paths',
                'product_stock_movements.stockopname_id',
                'stores.name as store_name',
            ])
            ->whereNull('product_details.deleted_at')
            ->where('product_stock_movements.stockopname_id',$stockopname->id)
            ->where('product_details.store_id', session('store.id'))
            ->orderby('products.title', 'asc')
            ->limit(100)
            ->get();


        $product_list_opname_pluck = ProductStockMovement::where('product_stock_movements.stockopname_id',$stockopname->id)->pluck('product_detail_id');

        $product_list = ProductDetail::join('products', 'products.id', '=', 'product_details.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->join('main_categories', 'main_categories.id', '=', 'categories.main_categories_id')
            ->join('colors', 'colors.id', '=', 'product_details.color_id')
            ->join('variants', 'variants.id', '=', 'product_details.variant_id')
            ->join('stores', 'stores.id', '=', 'product_details.store_id')
            ->select([
                'product_details.id as id',
                'product_details.sku',
                'products.title as product_title',
                'categories.title as category_title',
                'main_categories.title as main_category_title',
                'colors.label as color_title',
                'variants.name as variant_title',
                'product_details.qty',
                'products.content_paths',
                'stores.name as store_name',
            ])
            ->whereNull('product_details.deleted_at')
            ->where('product_details.store_id', session('store.id'))
            ->whereNotIn('product_details.id', $product_list_opname_pluck)
            ->orderby('products.title', 'asc')
            ->limit(100)
            ->get();

        $product_list_count = ProductDetail::join('products', 'products.id', '=', 'product_details.product_id')
            ->whereNull('product_details.deleted_at')
            ->where('product_details.store_id', session('store.id'))
            ->whereNotIn('product_details.id', $product_list_opname_pluck)
            ->count();

        $this->data['count_product'] = $product_list_count;
        $this->data['count_opname'] = count($product_list_opname_pluck);

    
        $this->data['product_list'] = $product_list;
        $this->data['product_list_opname'] = $product_list_opname;

        return view('backend.pages.stockopname.opname', $this->data);
    }

    public function update_qty(Request $request){
        \RuleHelper::UserHasPermission('update');

        $stockopname = Stockopname::where('id', $request->id_opname)->first();

        $qtyInput = str_replace('.', '', $request->Qty);
        $productDetail = ProductDetail::where('id', $request->id_detail_product)->first();
        $previousQty = $productDetail->qty;
        $lastQty = $qtyInput;
        $divQty = $lastQty - $previousQty;

        $productDetail->qty = $lastQty;
        $productDetail->save();

        $product_stock_movement = new ProductStockMovement();
        $product_stock_movement->product_detail_id = $request->id_detail_product;
        $product_stock_movement->qty = $divQty;
        $product_stock_movement->last_stock = $lastQty;
        $product_stock_movement->created_by = Auth::user()->email;
        $product_stock_movement->remark = "Stock Opname-". now()->format('ymd').' ('.$stockopname->title.')';
        $product_stock_movement->stockopname_id = $request->id_opname;

        $product_stock_movement->save();

        return redirect()->back()->with('success_message', 'Stok berhasil diperbarui!');
    }

    public function delete_item(request $request){
        \RuleHelper::UserHasPermission('delete');

        

        $product_stock_movement = ProductStockMovement::where('stockopname_id', $request->stockopname_id)
            ->where('product_detail_id', $request->id_detail_product)
            ->first();

            if (!$product_stock_movement) {
                return redirect()->back()->with('error_message', 'Stock movement sudah direvoke atau tidak ditemukan.');
            }

        $product_stock_movement_update = $product_stock_movement;


        $productDetail = ProductDetail::findOrFail($request->id_detail_product);
        $lastQty = $productDetail->qty;
        $lastQty = $lastQty + ($product_stock_movement_update->qty * -1);
        $productDetail->qty = $lastQty;
        $productDetail->save();
       
        $revokeRemark = 'revoke: '.$product_stock_movement_update->remark;

        $product_stock_movement->stockopname_id = null;
        $product_stock_movement->save();
        

        $product_stock_movement_revoke = new ProductStockMovement();
        $product_stock_movement_revoke->product_detail_id = $request->id_detail_product;
        $product_stock_movement_revoke->qty = ($product_stock_movement->qty*-1);
        $product_stock_movement_revoke->last_stock = $lastQty;
        $product_stock_movement_revoke->created_by = Auth::user()->email;
        $product_stock_movement_revoke->remark = $revokeRemark;
        $product_stock_movement_revoke->save();

        

        return redirect()->back()->with('success_message', 'Revoke pembaharuan berhasil');
    }

    public function filter(Request $request){
        \RuleHelper::UserHasPermission('read');

        $product_list_opname_pluck = ProductStockMovement::where('product_stock_movements.stockopname_id',$request->id_opname)->pluck('product_detail_id');
     
        $product_list = ProductDetail::join('products', 'products.id', '=', 'product_details.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->join('main_categories', 'main_categories.id', '=', 'categories.main_categories_id')
            ->join('colors', 'colors.id', '=', 'product_details.color_id')
            ->join('variants', 'variants.id', '=', 'product_details.variant_id')
            ->join('stores', 'stores.id', '=', 'product_details.store_id')
            ->select([
                'product_details.id as id',
                'product_details.sku',
                'products.title as product_title',
                'categories.title as category_title',
                'main_categories.title as main_category_title',
                'colors.label as color_title',
                'variants.name as variant_title',
                'product_details.qty',
                'products.content_paths',
                'stores.name as store_name',
            ])
            ->whereNull('product_details.deleted_at')
            ->where('product_details.store_id', session('store.id'))
            ->whereNotIn('product_details.id', $product_list_opname_pluck);

            if($request->searchText != ''){
                $product_list = $product_list->where(function ($query) use ($request) {
                    $query->where('products.title','like', '%'.$request->searchText.'%')
                          ->orWhere('product_details.sku', 'like', '%'.$request->searchText.'%');
                });
            }

            if($request->categoryId != ''){
                $product_list = $product_list->where('products.category_id', $request->categoryId);
            }
            $product_list = $product_list->orderby('products.title', 'asc')->limit(100)->get();

            return response()->json([
                'product_list' => $product_list,
            ]);

    }

     public function filter_opname(Request $request){
        \RuleHelper::UserHasPermission('read');
       
        $product_list_opname = ProductStockMovement::join('product_details', 'product_stock_movements.product_detail_id', '=', 'product_details.id')
            ->join('products', 'products.id', '=', 'product_details.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->join('main_categories', 'main_categories.id', '=', 'categories.main_categories_id')
            ->join('colors', 'colors.id', '=', 'product_details.color_id')
            ->join('variants', 'variants.id', '=', 'product_details.variant_id')
            ->join('stores', 'stores.id', '=', 'product_details.store_id')
            ->select([
                'product_details.id as id',
                'product_details.sku',
                'products.title as product_title',
                'categories.title as category_title',
                'main_categories.title as main_category_title',
                'colors.label as color_title',
                'variants.name as variant_title',
                'product_details.qty',
                'product_stock_movements.qty as opname_qty',
                'products.content_paths',
                'product_stock_movements.stockopname_id',
                'stores.name as store_name',
            ])
            ->whereNull('product_details.deleted_at')
            ->where('product_stock_movements.stockopname_id',$request->id_opname)
            ->where('product_details.store_id', session('store.id'));

            if($request->searchText != ''){
                $product_list_opname = $product_list_opname->where(function ($query) use ($request) {
                    $query->where('products.title','like', '%'.$request->searchText.'%')
                          ->orWhere('product_details.sku', 'like', '%'.$request->searchText.'%');
                });
            }

            if($request->categoryId != ''){
                $product_list_opname = $product_list_opname->where('products.category_id', $request->categoryId);
            }
            $product_list_opname = $product_list_opname->limit(100)->get();

            return response()->json([
                'product_list_opname' => $product_list_opname,
            ]);
    }


/**
 * Import template for stock opname.
 *
 * @param int $id_opname ID of stock opname.
 *
 * @return \Illuminate\Http\Response
 */
    function import_template($id_opname){
        $product_list_opname_pluck = ProductStockMovement::where('product_stock_movements.stockopname_id',$id_opname)->pluck('product_detail_id');
     
        $products = ProductDetail::join('products', 'products.id', '=', 'product_details.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->join('main_categories', 'main_categories.id', '=', 'categories.main_categories_id')
            ->join('colors', 'colors.id', '=', 'product_details.color_id')
            ->join('variants', 'variants.id', '=', 'product_details.variant_id')
            ->join('stores', 'stores.id', '=', 'product_details.store_id')
            ->select([
                'product_details.id as id',
                'product_details.sku',
                'products.title as product_title',
                'categories.title as category_title',
                'main_categories.title as main_category_title',
                'colors.label as color_title',
                'variants.name as variant_title',
                'product_details.qty',
                'products.content_paths',
                'stores.name as store_name',
            ])
            ->whereNull('product_details.deleted_at')
            ->where('product_details.store_id', session('store.id'))
            ->whereNotIn('product_details.id', $product_list_opname_pluck)
            ->orderby('products.title', 'asc')
            ->get();

        $fileName = 'stock_opname_template_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $columns = [
            'no',
            'download_date',
            'id',
            'sku',
            'title',
            'category',
            'variant',
            'color',
            'last_qty',
            'opname_qty',
            'opname_date'
        ];

        $callback = function () use ($products, $columns) {
            $handle = fopen('php://output', 'w');

            // Tambahkan BOM UTF-8 biar Excel bisa auto deteksi
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Tulis header kolom pakai delimiter ;
            fputcsv($handle, $columns, ';');

            $no = 1;
            $now = now()->format('Y-m-d H:i:s');

            foreach ($products as $product) {
                fputcsv($handle, [
                    $no++,
                    "'".$now."'",
                    $product->id,
                    $product->sku,
                    $product->product_title,
                    $product->category_title,
                    $product->variant_title,
                    $product->color_title,
                    $product->qty,
                    '', // qty_sesudah_opname dikosongkan
                    "'".$now."'",
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
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
        $stockopname = Stockopname::findOrFail($id);
        $dateSplit = explode('-', $stockopname->date_start);
        $stockopname->date_start = $dateSplit['1'] . '/' . $dateSplit['2'] . '/' . $dateSplit['0'];
        $dateSplit = explode('-', $stockopname->date_end);
        $stockopname->date_end = $dateSplit['1'] . '/' . $dateSplit['2'] . '/' . $dateSplit['0'];

        $stores = Store::whereNull('deleted_at')->get();

        $this->data['stockopname'] = $stockopname;
        $this->data['stores'] = $stores;


        return response()->json([
            'status' => 'success',
            'data' => $stockopname
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
        $stockopname = Stockopname::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'store_id' => 'required|uuid|exists:stores,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $stockopname->id = ucwords($request->id);
        $stockopname->title = ucwords($request->title);
        $tglStockopname = null;
        $stockopname->date_start = $this->formatDate($request->date_start, $tglStockopname, 1);
        $tglStockopname = null;
        $stockopname->date_end = $this->formatDate($request->date_end, $tglStockopname, 1);
        $stockopname->updated_by = Auth::user()->email;
        $simpan = $stockopname->save();

        if ($simpan) {
            \Session::flash('flash_message', 'The record updated successfully');

            return redirect()->route('stockopname.edit', $id);
        } else {
            \Session::flash('error_message', 'The record failed to updated');

            return redirect()->route('stockopname.edit', $id);
        }
    }

    public function trash()
    {
        \RuleHelper::UserHasPermission('trash');

        $this->data['stores'] = DB::table('stores')->whereNull('deleted_at')->get();

        $this->data['stockopnames_count'] = DB::table('stockopnames')->whereNotNull('deleted_at')->count();
        return view('backend.pages.stockopname.trash', $this->data);
    }

    public function get_datatable()
    {
        \RuleHelper::UserHasPermission('access');

        $user = Auth::user();
        $data = DB::table('stockopnames')->join('stores', 'stores.id', '=', 'stockopnames.store_id')
            ->select([
                'stockopnames.id',
                'stockopnames.title',
                'stockopnames.date_start',
                'stockopnames.date_end',
                'stores.name as store',

                'stockopnames.created_at',
                'stockopnames.updated_at'
            ])->whereNull('stockopnames.deleted_at')
            ->where('store_id', session('store.id'));

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
                $btnView = "<a href='" . route('stockopname.show', $data->id) . "' class='btn btn-sm btn-soft-secondary' title='Detail'><i class='las la-search-plus '></i></a>";

                return
                    $btnView . ' ' . $btnEdit . ' ' . $btnDelete;
            })
            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })

            ->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('date_start', function ($data) {
                return \Carbon\Carbon::parse($data->date_start)->format('d F Y');
            })->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('date_end', function ($data) {
                return \Carbon\Carbon::parse($data->date_end)->format('d F Y');
            })->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->filterColumn('deleted_at', function ($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('title') && !is_null(request('title'))) {
                    $query->where('stockopnames.title', 'like', "%" . request('title') . "%");
                }
                if (request()->has('date_start') && !is_null(request('date_start'))) {
                    $tglStockopname = null;
                    $date_start = $this->formatDate(request('date_start'), $tglStockopname, 1);

                    $query->whereDate('stockopnames.date_start', '=', $date_start);
                }
                if (request()->has('date_end') && !is_null(request('date_end'))) {
                    $tglStockopname = null;
                    $date_end = $this->formatDate(request('date_end'), $tglStockopname, 1);

                    $query->whereDate('stockopnames.date_end', '=', $date_end);
                }
            })
            ->rawColumns(['action', 'checkboxs',])
            ->toJson();
    }

    public function get_datatable_trash()
    {
        \RuleHelper::UserHasPermission('trash');
        $data = DB::table('stockopnames')->join('stores', 'stores.id', '=', 'stockopnames.store_id')
            ->select([
                'stockopnames.id',
                'stockopnames.title',
                'stockopnames.date_start',
                'stockopnames.date_end',
                'stores.name as store',

                'stockopnames.created_at',
                'stockopnames.updated_at'
            ])->whereNotNull('stockopnames.deleted_at')
            ->where('store_id', session('store.id'));

        $datatables = DataTables::of($data);

        return $datatables
            ->addColumn('checkboxs', function ($data) {
                return "
                    <input class='checkbox_item' id='dtcheckbox[]' type='checkbox' value='" . $data->id . "' name='dtcheckbox[]'>
                ";
            })
            ->addColumn('action', function ($data) {
                $btnView = "<a href='" . route('stockopname.show', $data->id) . "' class='btn btn-sm btn-soft-secondary'><i class='las la-search-plus '></i> View</a>";
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
            })->editColumn('date_start', function ($data) {
                return \Carbon\Carbon::parse($data->date_start)->format('d F Y');
            })->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })->editColumn('date_end', function ($data) {
                return \Carbon\Carbon::parse($data->date_end)->format('d F Y');
            })->editColumn('created_at', function ($data) {
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d F Y H:i:s');
            })
            ->filterColumn('deleted_at', function ($query, $keyword) {
                $sql = "@deleted_at = ?";
                $query->whereRaw($sql, "");
            })
            ->filter(function ($query) {
                if (request()->has('title') && !is_null(request('title'))) {
                    $query->where('stockopnames.title', 'like', "%" . request('title') . "%");
                }
                if (request()->has('date_start') && !is_null(request('date_start'))) {
                    $tglStockopname = null;
                    $date_start = $this->formatDate(request('date_start'), $tglStockopname, 1);

                    $query->whereDate('stockopnames.date_start', '=', $date_start);
                }
                if (request()->has('date_end') && !is_null(request('date_end'))) {
                    $tglStockopname = null;
                    $date_end = $this->formatDate(request('date_end'), $tglStockopname, 1);

                    $query->whereDate('stockopnames.date_end', '=', $date_end);
                }
            })
            ->rawColumns(['action', 'checkboxs',])
            ->toJson();
    }

    public function delete(Request $request)
    {
        \RuleHelper::UserHasPermission('delete');

        $table = "stockopnames";
        $column_visible = "title";
        $route = "stockopname.destroy";

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

                $redirect_url = "stockopname/trash";
                $stockopname = Stockopname::findOrFail($row['id']);


                $stockopname->forceDelete();
                $index++;
            }

            if ($delete_type == "softdelete") {
                $redirect_url = "stockopname";

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

        $data = DB::table('stockopnames')
            ->whereIn('id', $request->id_to_be_restored)
            ->orderBy('deleted_at', 'desc')
            ->get();

        $this->data['index'] = 0;
        $this->data['data'] = $data;
        $this->data['route'] = 'stockopname.restore_action';
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
            Stockopname::where('id', $row->id)
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

        return redirect('stockopname/trash');
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

        return Excel::download(new StockopnameExport($columnExportData['columnDepedencies'], $columnExportData['columnExtraAttribute']), 'stockopnames.xlsx');
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
            return $output[0][0] . '-' . $output[0][1] . '-' . $output[0][2];
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
