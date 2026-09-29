<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Core\BackendController;
use Illuminate\Http\Request;
use App\Models\SalesTransaction;
use App\Models\SalesTransactionItem;
use App\Models\Refund;
use App\Models\RefundItem;
use App\Models\ProductDetail;
use App\Models\ProductStockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Request as Req;

class RefundController extends BackendController
{
    function __construct()
    {
        parent::__construct();
        $this->data['page_title'] = "Refund Page";
    }

    public function index()
    {
        \RuleHelper::UserHasPermission('access');
        return view('backend.pages.refund.index', $this->data);
    }

    public function get_datatable()
    {
        \RuleHelper::UserHasPermission('access');

        $data = Refund::with(['salesTransaction'])
            ->whereNull('deleted_at')
            ->where('store_id', session('store.id'))
            ->orderBy('created_at', 'desc');

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                return "<a href='" . route('refund.show', $row->id) . "' class='btn btn-sm btn-soft-secondary' title='Detail'><i class='las la-search-plus '></i></a>";
            })
            ->editColumn('refund_date', function ($row) {
                return Carbon::parse($row->refund_date)->format('d M Y H:i:s');
            })
            ->editColumn('total_refund', function ($row) {
                return 'Rp ' . number_format($row->total_refund, 0, ',', '.');
            })
            ->addColumn('invoice_id', function ($row) {
                return $row->salesTransaction ? $row->salesTransaction->invoice_id : '-';
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function create($salesTransactionId)
    {
        \RuleHelper::UserHasPermission('create');

        $trx = SalesTransaction::with(['customer', 'paymentType'])
            ->where('id', $salesTransactionId)
            ->where('store_id', session('store.id'))
            ->firstOrFail();

        // Check if already refunded
        if ($trx->payment_status === 'refunded') {
            return redirect()->route('sales-transaction.index')->with('error_message', 'Transaksi ini sudah pernah di-refund.');
        }

        if ($trx->payment_status !== 'paid' || $trx->channel !== 'store') {
            return redirect()->route('sales-transaction.index')->with('error_message', 'Transaksi tidak valid untuk refund.');
        }

        // Load items via JOIN (sama seperti SalesTransactionController)
        $items = SalesTransactionItem::query()
            ->join('product_details as pd', 'pd.id', '=', 'sales_transaction_items.product_detail_id')
            ->leftJoin('products as p', 'p.id', '=', 'pd.product_id')
            ->where('sales_transaction_items.sales_transaction_id', $trx->id)
            ->get([
                'sales_transaction_items.*',
                'pd.sku as sku',
                'p.title as product_title',
            ]);

        $this->data['trx'] = $trx;
        $this->data['items'] = $items;
        
        return view('backend.pages.refund.create', $this->data);
    }

    public function store(Request $request)
    {
        \RuleHelper::UserHasPermission('create');

        $request->validate([
            'sales_transaction_id' => 'required',
            'reason' => 'required|string',
        ]);

        $trxId = $request->sales_transaction_id;

        $trx = SalesTransaction::with(['items'])->where('id', $trxId)->firstOrFail();

        if ($trx->payment_status === 'refunded') {
            return redirect()->route('sales-transaction.index')->with('error_message', 'Transaksi sudah di-refund.');
        }

        try {
            DB::beginTransaction();

            $now = Carbon::now();
            $refundInvoice = 'REF-' . $now->format('YmdHis');

            $refund = new Refund();
            $refund->sales_transaction_id = $trx->id;
            $refund->refund_invoice_id = $refundInvoice;
            $refund->refund_date = $now;
            $refund->total_refund = $trx->total;
            $refund->reason = $request->reason;
            $refund->store_id = $trx->store_id;
            $refund->created_by = Auth::user()->email ?? null;
            $refund->save();

            foreach ($trx->items as $item) {
                $pd = ProductDetail::lockForUpdate()->find($item->product_detail_id);
                if (!$pd) continue;

                // Create Refund Item
                $refundItem = new RefundItem();
                $refundItem->refund_id = $refund->id;
                $refundItem->sales_transaction_item_id = $item->id;
                $refundItem->product_detail_id = $item->product_detail_id;
                $refundItem->qty = $item->qty;
                $refundItem->price = $item->final_price;
                $refundItem->refund_amount = $item->final_price * $item->qty;
                $refundItem->save();

                // Return stock
                $newQty = $pd->qty + $item->qty;
                $pd->qty = $newQty;
                $pd->save();

                // Record stock movement
                $mv = new ProductStockMovement();
                $mv->product_detail_id = $pd->id;
                $mv->qty = $item->qty;
                $mv->last_stock = $newQty;
                $mv->remark = "Refund: " . $refundInvoice;
                $mv->created_by = Auth::user()->email ?? null;
                $mv->save();
            }

            // Update Transaction Status
            $trx->payment_status = 'refunded';
            $trx->save();

            DB::commit();

            return redirect()->route('refund.show', $refund->id)->with('flash_message', 'Refund berhasil diproses.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error_message', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        \RuleHelper::UserHasPermission('read');

        $refund = Refund::with(['salesTransaction.customer'])->findOrFail($id);

        // Load refund items via JOIN untuk ambil nama produk
        $refundItems = RefundItem::query()
            ->join('sales_transaction_items as sti', 'sti.id', '=', 'refund_items.sales_transaction_item_id')
            ->join('product_details as pd', 'pd.id', '=', 'sti.product_detail_id')
            ->leftJoin('products as p', 'p.id', '=', 'pd.product_id')
            ->where('refund_items.refund_id', $refund->id)
            ->get([
                'refund_items.*',
                'pd.sku as sku',
                'p.title as product_title',
            ]);

        $this->data['refund'] = $refund;
        $this->data['refundItems'] = $refundItems;
        return view('backend.pages.refund.show', $this->data);
    }
}
