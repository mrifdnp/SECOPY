<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Informasi Pengiriman Pesanan</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'DM Sans', 'Cabinet Grotesk', 'Segoe UI', Arial, sans-serif;
            background-color: #F7F6EF;
            padding: 30px 0;
            margin: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 40px;
            border-top: 4px solid #4A3B32;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .title {
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #4A3B32;
            letter-spacing: -0.5px;
        }
        .subtitle {
            font-size: 16px;
            color: #555555;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .btn-track {
            display: inline-block;
            background-color: #4A3B32;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 4px;
            font-size: 15px;
            font-family: 'Arial', sans-serif;
            font-weight: bold;
            text-align: center;
        }
        .tracking-info {
            font-size: 14px;
            color: #777777;
            font-family: 'Arial', sans-serif;
            margin-top: 25px;
            margin-bottom: 40px;
        }
        .tracking-info a {
            color: #4A3B32;
            text-decoration: none;
            font-weight: bold;
        }
        .divider {
            border-top: 1px solid #eeeeee;
            margin: 30px 0;
        }
        .footer {
            text-align: center;
            font-size: 13px;
            color: #999999;
            font-family: 'Arial', sans-serif;
            line-height: 1.5;
        }
        .footer a {
            color: #4A3B32;
            text-decoration: none;
            font-weight: bold;
        }
        @media only screen and (max-width: 600px) {
            .container {
                padding: 20px !important;
                width: 100% !important;
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 40px;">
            <tr>
                <td align="left" valign="middle">
                    <a href="{{ url('/') }}" style="text-decoration:none;">
                        <img src="{{ asset('seco-logo.svg') }}" alt="Seco by Seken Living" style="height: 40px;">
                    </a>
                </td>
                <td align="right" valign="middle" style="color: #999; font-size: 13px; font-family: 'Arial', sans-serif; letter-spacing: 0.5px;">
                    ORDER #{{ $data['invoice'] }}
                </td>
            </tr>
        </table>

        <!-- CONTENT -->
        <h1 class="title">Your order is on the way</h1>
        
        <p class="subtitle">
            Hello <strong>{{ $data['name'] }}</strong>, your order is on the way. Track your shipment to see the delivery status.
        </p>

        <!-- BUTTON & LINK -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 20px;">
            <tr>
                <td align="left" valign="middle">
                    <a href="https://cekresi.com/?noresi={{ $data['resi'] }}" class="btn-track" target="_blank">Track order</a>
                    <span style="font-family: 'Arial', sans-serif; font-size:14px; color: #777; margin-left: 15px;">
                        or <a href="{{ url('/') }}" style="color: #4A3B32; text-decoration: none; font-weight: 600;">Visit our store</a>
                    </span>
                </td>
            </tr>
        </table>

        <div class="tracking-info">
            Tracking number {{ $data['courier'] }}: <br>
            <a href="https://cekresi.com/?noresi={{ $data['resi'] }}" target="_blank" style="font-size: 16px; margin-top: 5px; display: inline-block;">{{ $data['resi'] }}</a>
        </div>

        <div class="divider"></div>

        <!-- ITEMS IN SHIPMENT -->
        @if(isset($data['items']) && count($data['items']) > 0)
        <h2 style="font-size: 18px; color: #333; margin-bottom: 20px; font-family: 'Arial', sans-serif;">Items in this shipment</h2>
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 30px;">
            @foreach($data['items'] as $item)
                @php
                    $productName = $item['name'] ?? 'Produk Seco';
                    $imageUrl = $item['image'] ?? null;
                @endphp
                <tr>
                    <td width="70" valign="top" style="padding-bottom: 15px;">
                        @if($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $productName }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #eee;">
                        @else
                            <div style="width: 60px; height: 60px; background-color: #f4f4f4; border-radius: 8px; border: 1px solid #eee; text-align: center; line-height: 60px; color: #ccc; font-size: 12px; font-family: sans-serif;">Img</div>
                        @endif
                    </td>
                    <td valign="top" style="padding-bottom: 15px; padding-left: 15px; font-family: 'Arial', sans-serif;">
                        <div style="font-size: 15px; color: #4A3B32; font-weight: 600; line-height: 1.4;">{{ $productName }}</div>
                        <div style="font-size: 14px; color: #777; margin-top: 4px;">&times; {{ $item['qty'] }}</div>
                    </td>
                </tr>
            @endforeach
        </table>
        <div class="divider"></div>
        @endif

        <!-- FOOTER -->
        <div class="footer">
            Jika Anda memiliki pertanyaan, balas email ini atau hubungi kami di <br>
            <a href="mailto:admin@seco.id">admin@seco.id</a>
        </div>
    </div>
</body>
</html>
