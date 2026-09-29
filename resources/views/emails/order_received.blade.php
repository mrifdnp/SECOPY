<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Confirmation</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', 'Cabinet Grotesk', 'Segoe UI', Arial, sans-serif; background-color: #F7F6EF; padding: 20px; margin: 0; color: #482B1B; }
        .container { background-color: #ffffff; max-width: 600px; margin: 0 auto; padding: 40px; border-radius: 4px; box-shadow: 0 2px 4px rgba(72,43,27,0.08); }
        .order-id { color: #7E552A; font-size: 14px; }
        .title { font-size: 24px; margin-bottom: 10px; color: #482B1B; font-weight: normal; }
        .subtitle { color: #6a4629; font-size: 15px; line-height: 1.5; margin-bottom: 30px; }
        .btn { display: inline-block; background-color: #482B1B; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 4px; font-weight: bold; font-size: 14px; }
        .link { color: #7E552A; text-decoration: none; font-size: 14px; margin-left: 10px; }
        
        .section-title { font-size: 18px; margin-top: 40px; margin-bottom: 20px; font-weight: normal; color: #482B1B; }
        
        table { width: 100%; border-collapse: collapse; }
        .item-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #EDE7D4; }
        .item-name { font-size: 14px; font-weight: 600; color: #482B1B; }
        .item-qty { font-size: 13px; color: #7E552A; }
        .item-price { font-size: 14px; color: #482B1B; text-align: right; }
        
        .summary-row td { padding: 10px 0; font-size: 14px; color: #5b3c28; }
        .summary-row.total td { font-size: 20px; color: #482B1B; font-weight: normal; border-top: 1px solid #EDE7D4; padding-top: 20px; margin-top: 10px; }
        .text-right { text-align: right; }
        
        .info-title { font-size: 14px; font-weight: bold; color: #482B1B; margin-bottom: 10px; }
        .info-text { font-size: 14px; color: #6a4629; line-height: 1.5; }
        
        .footer { text-align: center; margin-top: 40px; font-size: 12px; color: #9d7231; border-top: 1px solid #EDE7D4; padding-top: 20px; }
        .footer a { color: #7E552A; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        @php
            $trx = $data['transaction'] ?? null;
            $customer = $trx ? $trx->customer : null;
            $shipping_address = $customer ? ($customer->address ?? '') . ', ' . ($customer->city ?? '') . ', ' . ($customer->province ?? '') : '';
        @endphp

        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 30px;">
            <tr>
                <td>
                    <a href="{{ url('/') }}" style="text-decoration:none;">
                        <img src="{{ asset('seco-logo.svg') }}" alt="Seco by Seken Living" style="height: 40px;">
                    </a>
                </td>
                <td align="right" class="order-id" style="color:#777; font-size:14px;">
                    ORDER #{{ $data['invoice'] ?? '' }}
                </td>
            </tr>
        </table>

        <h1 class="title">Thank you for your purchase!</h1>
        <p class="subtitle">We're getting your order ready to be shipped. We will notify you when it has been sent.</p>
        
        <div style="margin-bottom: 40px;">
            <a href="{{ route('loginForm.customer') }}" class="btn">View your order</a>
            <span style="font-size: 14px; color: #777; margin: 0 10px;">or</span>
            <a href="{{ url('/') }}" class="link">Visit our store</a>
        </div>

        <h2 class="section-title">Order summary</h2>
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">
            @if(isset($data['items']) && count($data['items']) > 0)
                @foreach($data['items'] as $item)
                    @php
                        $productName = $item['name'] ?? 'Produk Seco';
                        $imageUrl = $item['image'] ?? null;
                        $price = $item['price'] ?? 0;
                    @endphp
                    <tr>
                        <td width="70" valign="top" style="padding-bottom: 15px;">
                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ $productName }}" class="item-img">
                            @else
                                <div class="item-img" style="background-color: #f4f4f4; text-align: center; line-height: 60px; color: #ccc; font-size: 12px;">Img</div>
                            @endif
                        </td>
                        <td valign="top" style="padding-bottom: 15px; padding-left: 15px;">
                            <div class="item-name">{{ $productName }} &times; {{ $item['qty'] }}</div>
                        </td>
                        <td valign="top" align="right" style="padding-bottom: 15px;" class="item-price">
                            Rp {{ number_format($price * $item['qty'], 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            @endif
        </table>

        @php
            $courierData = $trx ? $trx->courier : null;
            $shippingCost = is_array($courierData) ? ($courierData['cost'] ?? 0) : 0;
        @endphp
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr class="summary-row">
                <td width="50%"></td>
                <td>Subtotal</td>
                <td class="text-right">Rp {{ number_format($trx ? $trx->sub_total : 0, 0, ',', '.') }}</td>
            </tr>
            <tr class="summary-row">
                <td width="50%"></td>
                <td>Shipping</td>
                <td class="text-right">
            <tr class="summary-row total">
                <td width="50%"></td>
                <td>Total</td>
                <td class="text-right"><b>Rp {{ number_format($trx ? $trx->total : 0, 0, ',', '.') }} IDR</b></td>
            </tr>
        </table>

        <h2 class="section-title">Customer information</h2>
        
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td valign="top" style="padding-bottom: 20px;">
                    <div class="info-title">Shipping address</div>
                    <div class="info-text">
                        {{ $data['name'] ?? '' }}<br>
                        {{ $customer->address ?? '' }}<br>
                        {{ $customer->city ?? '' }}<br>
                        {{ $customer->province ?? '' }}<br>
                        Indonesia
                    </div>
                </td>
            </tr>
            <tr>
                <td width="50%" valign="top">
                    <div class="info-title">Payment</div>
                    <div class="info-text">
                        {{ $trx && $trx->paymentType ? $trx->paymentType->name : 'Transfer' }}
                    </div>
                </td>
                <td width="50%" valign="top">
                    <div class="info-title">Shipping method</div>
                    <div class="info-text">
                        {{ strtoupper($data['courier'] ?? 'REGULER') }}
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer">
            If you have any questions, reply to this email or contact us at<br>
            <a href="mailto:admin@seco.id">admin@seco.id</a>
        </div>
    </div>
</body>
</html>
