@php
    $rp = fn($n) => 'Rp' . number_format((int)($n ?? 0), 0, ',', '.');
    
    $storeName = session('store.name') ?? 'Toko';
    $invoice = $movement->number ?? 'Mutasi';
    
    $flowName = 'Mutasi';
    if ($movement->flow == 'in') {
        $flowName = 'Barang Masuk';
    } elseif ($movement->flow == 'out') {
        $flowName = 'Barang Keluar';
    } elseif ($movement->flow == 'broken') {
        $flowName = 'Barang Rusak';
    } else {
        $flowName = 'Mutasi Produk';
    }
    
    $source = $movement->sourceParty->name ?? '-';
    $destination = $movement->destinationParty->name ?? '-';

    $displayNumeric = \Carbon\Carbon::parse($movement->created_at)->format('d M Y H:i:s');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $invoice }}</title>
    <style>
		@media print {
            #printPageButton {
                display: none;
            }            
            #btnBack {
                display: none;
            }
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px;
            width: 58mm; /* Ukuran thermal 58mm standar */
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header img {
            max-width: 40px;
            margin-bottom: 5px;
        }
        .header h2 {
            margin: 0;
            font-size: 14px;
        }
        .header p {
            margin: 2px 0;
            font-size: 12px;
            font-weight: bold;
        }
        .divider {
            border-bottom: 1px dashed #000;
            margin: 5px 0;
        }
        .info {
            margin-bottom: 10px;
        }
        .info table {
            width: 100%;
            font-size: 10px;
        }
        .info td {
            padding: 1px 0;
            vertical-align: top;
        }
        .items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .items th {
            border-bottom: 1px dashed #000;
            text-align: left;
            font-size: 10px;
            padding: 3px 0;
        }
        .items td {
            font-size: 10px;
            padding: 3px 0;
            vertical-align: top;
        }
        .items .price-col {
            text-align: right;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 10px;
        }
        .footer p {
            margin: 2px 0;
        }
    </style>
</head>
<body onload="window.print()">
    <!-- Tombol back/print (disembunyikan saat print) -->
    <div style="margin-bottom: 10px; text-align: center;">
        <button id="btnBack" onclick="window.close()" style="padding:5px 10px; cursor:pointer;">&laquo; Tutup</button>
        <button id="printPageButton" onclick="window.print()" style="padding:5px 10px; cursor:pointer; font-weight:bold;">Print Nota</button>
    </div>

    <div class="header">
        @if(!empty($logoSrc))
            <img src="{{ $logoSrc }}" alt="Logo">
        @endif
        <h2>{{ $storeName }}</h2>
        <p>{{ $flowName }}</p>
    </div>

    <div class="divider"></div>

    <div class="info">
        <table>
            <tr>
                <td width="30%">Tgl</td>
                <td width="5%">:</td>
                <td>{{ $displayNumeric }}</td>
            </tr>
            @if(!empty($source) && $source != '-')
            <tr>
                <td>Sumber</td>
                <td>:</td>
                <td>{{ $source }}</td>
            </tr>
            @endif
            @if(!empty($destination) && $destination != '-')
            <tr>
                <td>Tujuan</td>
                <td>:</td>
                <td>{{ $destination }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="divider"></div>

    <table class="items">
        <thead>
            <tr>
                <th>Produk</th>
                <th style="text-align:center;">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>
                        {{ $item->product_title ?? 'Unknown' }}<br>
                        <small style="color:#555;">{{ $item->color ?? '' }} - {{ $item->variant ?? '' }}</small>
                    </td>
                    <td style="text-align:center;">{{ $item->qty }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <div class="footer">
        <p>{{ $footerText ?? 'Terima kasih' }}</p>
    </div>
</body>
</html>
