<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Peringatan Stok Rendah</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f9fafb;
            color: #374151;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .wrapper {
            width: 100%;
            padding: 40px 0;
            background-color: #f9fafb;
        }
        .container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .header {
            background-color: #111827;
            padding: 30px 40px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 1px;
        }
        .content {
            padding: 40px;
        }
        .content p {
            margin: 0 0 20px;
            font-size: 16px;
        }
        .alert-box {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 15px 20px;
            margin-bottom: 30px;
            border-radius: 0 4px 4px 0;
        }
        .alert-box p {
            margin: 0;
            color: #991b1b;
            font-weight: 500;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 14px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background-color: #f3f4f6;
            font-weight: 600;
            color: #4b5563;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.05em;
        }
        .stock-qty {
            color: #ef4444;
            font-weight: bold;
            text-align: center;
        }
        .btn-container {
            text-align: center;
            margin: 40px 0 20px;
        }
        .btn {
            display: inline-block;
            background-color: #111827;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #374151;
        }
        .more-items {
            text-align: center;
            color: #6b7280;
            font-style: italic;
            margin-bottom: 20px;
        }
        .footer {
            background-color: #f3f4f6;
            padding: 20px 40px;
            text-align: center;
            color: #6b7280;
            font-size: 13px;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>SECO by Sekenliving</h1>
            </div>
            
            <div class="content">
                <p>Halo Tim Admin,</p>
                
                <div class="alert-box">
                    <p>Notifikasi Sistem: Ditemukan {{ $lowStockProducts->count() }} produk yang memerlukan perhatian segera (Stok Menipis / Habis).</p>
                </div>
                
                <p>Untuk memastikan kelancaran operasional dan ketersediaan produk bagi pelanggan, mohon segera melakukan peninjauan dan pengisian ulang (<i>restock</i>) pada daftar produk berikut:</p>
                
                <table>
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Varian / Warna</th>
                            <th>SKU</th>
                            <th style="text-align: center;">Sisa Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockProducts->take(50) as $product)
                        <tr>
                            <td>{{ $product->product->title ?? '-' }}</td>
                            <td>{{ $product->variant->name ?? '-' }} / {{ $product->color->label ?? '-' }}</td>
                            <td>{{ $product->sku ?? '-' }}</td>
                            <td class="stock-qty">{{ $product->qty }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($lowStockProducts->count() > 50)
                <p class="more-items">... dan {{ $lowStockProducts->count() - 50 }} produk lainnya tidak ditampilkan agar email lebih ringan.</p>
                @endif

                <div class="btn-container">
                    <a href="{{ url('/login') }}" class="btn">Masuk ke Dashboard SECO</a>
                </div>
            </div>

            <div class="footer">
                <p>&copy; {{ date('Y') }} Seco by Sekenliving. All rights reserved.</p>
                <p>Email ini dikirimkan secara otomatis oleh sistem. Mohon tidak membalas email ini.</p>
            </div>
        </div>
    </div>
</body>
</html>
