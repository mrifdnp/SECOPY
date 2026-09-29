<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\RajaOngkirService;
use App\Services\AgenWebsiteService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\ProductDetail;
use App\Models\Courier;
use App\Models\Store;
use App\Models\SalesTransaction;
use App\Models\SalesTransactionItem;
use App\Models\PaymentType;
use App\Models\Discount;
use Illuminate\Support\Facades\Validator;
use App\Services\XenditService;
use App\Services\InvoiceService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Notifications\InvoiceCreatedNotification;
use App\Notifications\InvoicePaidNotification;
use Illuminate\Support\Facades\Notification;

class ShippingController extends Controller
{
    //
    protected $rajaOngkir;
    protected $agenWebsite;

    public function __construct(RajaOngkirService $rajaOngkir, AgenWebsiteService $agenWebsite)
    {
        $this->rajaOngkir = $rajaOngkir;
        $this->agenWebsite = $agenWebsite;
    }

    public function calculate(Request $request)
    {
        if(!Auth::guard('customer')->check()){
            return response()->json([
                'status' => 401,
                'message' => 'Please login to update cart',
                'data' => []
            ], 401);
        }

        $customerId = Auth::guard('customer')->id();
        $sessionKey = 'profile_' . $customerId;
        $cartKey = 'cart_' . $customerId;

        $profile = Session::get($sessionKey, []);
        $cart = Session::get($cartKey, []);

        $detailIds = collect($cart)->pluck('detail_product_id')->toArray();
        $products = ProductDetail::whereIn('id', $detailIds)->get(['id', 'weight']);

        $totalWeight = 0;
        foreach ($cart as $item) {
            $productDetail = $products->firstWhere('id', $item['detail_product_id']);
            if ($productDetail) {
                $qty = $item['qty'] ?? 1;
                $totalWeight += ((float)$productDetail->weight * $qty);
            }
        }

        if ($totalWeight <= 0) {
            $totalWeight = 1000; // Default to 1kg if weight is missing
        }

        $districtId = $profile['districtId'] ?? null;
        if (!$districtId) {
            return response()->json([
                'status' => 400,
                'message' => 'Silakan isi alamat lengkap di halaman Contact/Shipping terlebih dahulu.',
                'data' => []
            ], 400);
        }

        $activeCouriersArray = Courier::where('status', 1)
            ->pluck('code')
            ->map(fn($c) => strtolower($c))
            ->toArray();
            
        $wantsJtc = in_array('jtc', $activeCouriersArray);
        
        // Remove jtc from RajaOngkir couriers to prevent errors
        $rajaOngkirCouriers = collect($activeCouriersArray)
            ->filter(fn($c) => $c !== 'jtc')
            ->implode(':');

        try {
            $data = [];

            if (!empty($rajaOngkirCouriers)) {
                $roData = $this->rajaOngkir->calculateCost(
                    destination: $districtId,
                    weight: $totalWeight,
                    couriers: $rajaOngkirCouriers,
                    priceType: $request->price ?? 'lowest',
                );
                
                $data = $roData;
            }
            
            if ($wantsJtc) {
                $postcode = $profile['postcode'] ?? null;
                $jtcCost = $this->agenWebsite->getJtcCost($postcode, $totalWeight);
                if ($jtcCost) {
                    if (isset($data['data']) && is_array($data['data'])) {
                        $data['data'][] = $jtcCost;
                    } else if (isset($data) && is_array($data) && !isset($data['meta'])) {
                        // fallback if no meta
                        $data[] = $jtcCost;
                    }
                }
            }

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menghitung ongkos kirim: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function storeSelected(Request $request)
    {
         if(!Auth::guard('customer')->check()){
            return response()->json([
                'status' => 401,
                'message' => 'Please login to update cart',
                'data' => []
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'code' => 'required',
            'service' => 'required',
            'description' => 'nullable',
            'cost' => 'required',
            'etd' => 'nullable',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'input yang diharapkan tidak sesuai',
                'data' => []
            ], 400);
        }

        $customerId = Auth::guard('customer')->id();
        $sessionKey = 'shipment_method_' . $customerId;

        session([$sessionKey => $request->all()]);

        $data = Session::get($sessionKey, []);

        return response()->json([
            'status' => 200,
            'message' => 'data berhasil disimpan',
            'data' => [
                'data' => $data,
                'redirect_url' => route('checkout.review')
            ]
        ], 200);
    }


    public function createInvoiceController(
        Request $request,
        XenditService $xenditService,
        InvoiceService $invoiceService
    ) {
        if (!Auth::guard('customer')->check()) {
            return response()->json([
                'status'  => 401,
                'message' => 'Please login to update cart',
                'data'    => []
            ], 401);
        }

        try {
            $response = DB::transaction(function () use ($request, $xenditService, $invoiceService) {
                $customerId = Auth::guard('customer')->id();
                $profileKey = 'profile_' . $customerId;
                $cartKey    = 'cart_' . $customerId;
                $methodKey  = 'shipment_method_' . $customerId;

                $profile = Session::get($profileKey, []);
                $cart    = Session::get($cartKey, []);
                $method  = Session::get($methodKey, []);

                $detailIds = collect($cart)->pluck('detail_product_id')->toArray();

                $details = ProductDetail::withPricesAndDiscounts()
                    ->whereIn('id', $detailIds)
                    ->get()
                    ->keyBy('id');

                $storeId = Store::where('ecommerce', true)->first()->id;

                $subTotal = 0;
                foreach ($cart as $item) {
                    if (isset($details[$item['detail_product_id']])) {
                        $detail       = $details[$item['detail_product_id']];
                        $base_price   = $detail->price + ($detail->additional_price ?? 0);
                        $final_price  = $detail->final_price;
                        $qty          = $item['qty'];

                        $subTotal += $final_price * $qty;
                    }
                }

                $total     = $subTotal + ($method['cost'] ?? 0);
                $invoiceId = $invoiceService->generateInvoiceId();

                $payment_type = PaymentType::where('payment_type', 'Xendit')->first();
                
                $payment_type_id = null;
                if ($payment_type) {
                    $payment_type_id = $payment_type->id;
                }
                
                $transaction = SalesTransaction::create([
                    'invoice_id'     => $invoiceId,
                    'customer_id'    => $customerId,
                    'date'           => now(),
                    'payment_status' => 'pending',
                    'sub_total'      => $subTotal,
                    'total'          => $total,
                    'channel'        => "ecommerce",
                    'payment_type'   => $payment_type_id,
                    'store_id'       => $storeId,
                    'courier'        => $method
                ]);

                $items = [];
                foreach ($cart as $item) {
                    if (isset($details[$item['detail_product_id']])) {
                        $detail       = $details[$item['detail_product_id']];
                        $base_price   = $detail->price + ($detail->additional_price ?? 0);
                        $final_price  = $detail->final_price;
                        $qty          = $item['qty'];

                        if ($detail->qty < $qty) {
                            throw new \Exception("Stok tidak mencukupi untuk item " . ($detail->product->title ?? ''));
                        }

                        $detail->decrement('qty', $qty);

                        $discountPercent = null;
                        $discountNominal = null;

                        if ($detail->max_discount_type === 'percent') {
                            $discountPercent = $detail->max_discount;
                        } elseif ($detail->max_discount_type === 'nominal') {
                            $discountNominal = $detail->max_discount;
                        }

                        $detailSnapshot = $detail->toArray();
                        $detailSnapshot['product_title'] = $detail->product->title ?? null;
                        $detailSnapshot['variant_name']  = $detail->variant->name ?? null;
                        $detailSnapshot['color_label']   = $detail->color->label ?? null;

                        $items[] = [
                            'id'                   => Str::uuid()->toString(),
                            'sales_transaction_id' => $transaction->id,
                            'product_detail_id'    => $detail->id,
                            'price'                => $base_price,
                            'cogm'                 => $detail->cogm,
                            'discount_percent'     => $discountPercent,
                            'discount_nominal'     => $discountNominal,
                            'final_price'          => $final_price,
                            'qty'                  => $qty,
                            'product_detail'       => json_encode($detailSnapshot),
                        ];
                    }
                }

                SalesTransactionItem::insert($items);

                Notification::route('mail', env('MAIL_TO_ADDRESS', 'secobysekenliving.adm@gmail.com'))
                    ->notify(new InvoiceCreatedNotification($transaction));

                if (isset($profile['email']) && !empty($profile['email'])) {
                    try {
                        $transaction->load(['customer', 'paymentType']);
                        
                        $mailItems = [];
                        $txItems = \App\Models\SalesTransactionItem::where('sales_transaction_id', $transaction->id)
                            ->whereNull('deleted_at')
                            ->get();
                        foreach ($txItems as $txItem) {
                            $snapshot = is_array($txItem->getAttributes()['product_detail'] ?? null)
                                ? $txItem->getAttributes()['product_detail']
                                : json_decode($txItem->getAttributes()['product_detail'] ?? '{}', true);
                            $pd = \App\Models\ProductDetail::withTrashed()->with('product')->find($txItem->product_detail_id);
                            $product = $pd ? $pd->product : null;
                            $mailItems[] = [
                                'name' => $snapshot['product_title'] ?? ($product ? $product->title : 'Produk Seco'),
                                'qty' => $txItem->qty,
                                'price' => $txItem->price ?? 0,
                                'final_price' => $txItem->final_price ?? 0,
                                'image' => $product ? $product->main_pic : null,
                            ];
                        }
                        
                        $mailData = [
                            'invoice' => $transaction->invoice_id,
                            'name' => $profile['first_name'] ?? 'Customer',
                            'transaction' => $transaction,
                            'items' => $mailItems,
                            'courier' => is_array($transaction->courier) ? ($transaction->courier['code'] ?? 'REGULER') : 'REGULER',
                        ];
                        // \Illuminate\Support\Facades\Mail::to($profile['email'])->send(new \App\Mail\OrderReceivedMail($mailData));
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Failed to send OrderReceivedMail: ' . $e->getMessage());
                    }
                }

                $invoice = $xenditService->createInvoice([
                    'external_id' => $transaction->id,
                    'description' => 'Pembayaran Pesanan',
                    'amount'      => $transaction->total,
                    'payer_email' => $profile['email'] ?? null,
                    'customer_name' => $profile['first_name'],
                    'customer_phone' => $profile['phone'],
                    'customer_id' => $customerId,
                    'address' => $profile['address'] ?? null
                ]);

                $transaction->update([
                    'xendit_metadata' => json_encode($invoice),
                    'xendit_url' => $invoice['invoice_url'] ?? null,
                    'xendit_expired_invoice' => $invoice['expiry_date'] ?? null
                ]);

                Session::forget($profileKey);
                Session::forget($cartKey);
                Session::forget($methodKey);

                return [
                    'invoice' => $invoice,
                ];
            });

            Log::info('Xendit invoice created', $response);

            return response()->json([
                'status'  => 200,
                'message' => 'Invoice created successfully',
                'data'    => $response
            ]);

        } catch (\Throwable $e) {
            \Log::error('Invoice creation failed: ' . $e->getMessage());

            if (isset($response['invoice']['id'])) {
                try {
                    $xenditService->cancelInvoice($response['invoice']['id']);
                } catch (\Throwable $ex) {
                    \Log::error('Failed to cancel Xendit invoice: ' . $ex->getMessage());
                }
            }

            return response()->json([
                'status'  => 500,
                'message' => 'Failed to create invoice',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function paymentCallback(Request $request)
    {
        $payload = $request->all();
        $callbackToken = $request->header('x-callback-token');

        if ($callbackToken !== config('services.xendit.callback_token')) {
            Log::warning('Invalid Xendit callback token', ['header' => $callbackToken]);
            return response()->json(['message' => 'Invalid token'], 403);
        }

        Log::info('Xendit callback received', $payload);

        $externalId = $payload['external_id'] ?? null;
        $status = isset($payload['status']) ? strtolower($payload['status']) : null;
        $xenditFees = $payload['fees'][0]['value'] ?? 0;
        $xenditPaymentMethod = $payload['payment_method'] ?? null;
        $xenditChannel = $payload['payment_channel'] ?? null;
        $paidAt = $payload['paid_at'] ?? null;

        if (!$externalId || !$status) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $transaction = SalesTransaction::where('id', $externalId)->first();
        if ($transaction) {
            $transaction->update([
                'payment_status' => $status,
                'xendit_metapaid' => json_encode($payload),
                'xendit_fees' =>  $xenditFees,
                'xendit_payment_method' => $xenditPaymentMethod,
                'xendit_channel' => $xenditChannel,
                'paid_at' => $paidAt,
            ]);

            if (in_array($status, ['EXPIRED', 'FAILED', 'canceled'])) {
                $items = SalesTransactionItem::where('sales_transaction_id', $transaction->id)->get();

                foreach ($items as $item) {
                    $detail = ProductDetail::find($item->product_detail_id);
                    if ($detail) {
                        $detail->increment('qty', $item->qty);
                    }
                }
            }

            if ($status === 'paid') {
                $transaction->customer->notify(new InvoicePaidNotification($transaction));
            }
        } else {
            Log::warning('Transaction not found for Xendit callback', ['external_id' => $externalId]);
        }

        return response()->json(['message' => 'Callback processed']);

    }

    public function getInvoiceById($id)
    {
        if (!Auth::guard('customer')->check()) {
            return response()->json([
                'status'  => 401,
                'message' => 'Please login to view invoice',
                'data'    => []
            ], 401);
        }

        try {
            $customer = Auth::guard('customer')->user();

            $invoice = SalesTransaction::with(['items'])
                ->where('id', $id)
                ->where('customer_id', $customer->id)
                ->whereNull('deleted_at')
                ->first();

            if (!$invoice) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Invoice not found or you do not have permission to access it',
                    'data'    => []
                ], 404);
            }

            $pdf = Pdf::loadView('frontend.template.invoice', compact('invoice'))
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => true,
                    'defaultFont' => 'DejaVu Sans',
                    'dpi' => 110,
                    'defaultMediaType' => 'print',
                    'isFontSubsettingEnabled' => true,
                    'isRemoteEnabled' => false,
                    'chroot' => storage_path('app/public'),
                    'logOutputFile' => storage_path('logs/dompdf.html'),
                    'tempDir' => storage_path('temp')
                ]);

            return $pdf->stream("invoice-{$invoice->invoice_id}.pdf");

        } catch (\Exception $e) {
            \Log::error('Error generating invoice PDF: ' . $e->getMessage());

            return response()->json([
                'status'  => 500,
                'message' => 'Error generating invoice PDF',
                'data'    => []
            ], 500);
        }
    }
}
