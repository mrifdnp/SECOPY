<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice Terbayar</title>
    <style>
        body {
            font-family: 'Cabinet Grotesk', Arial, sans-serif;
            background-color: #f6f6f6;
            color: #3d3d3d;
            padding: 40px 0;
            margin: 0;
        }
        .container {
            max-width: 500px;
            margin: auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }
        h1 {
            color: #b78f3b;
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        p {
            line-height: 1.6;
            margin-bottom: 16px;
            font-size: 15px;
        }
        .invoice-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #b78f3b;
        }
        .invoice-number {
            font-size: 18px;
            font-weight: 600;
            color: #b78f3b;
            margin-bottom: 15px;
        }
        .detail {
            margin-bottom: 8px;
        }
        .label {
            color: #6D6D6D;
            font-weight: 500;
        }
        .value {
            font-weight: 600;
            color: #3d3d3d;
        }
        .total {
            font-size: 16px;
            color: #b78f3b;
        }
        .status {
            display: inline-block;
            background: #e9ecef;
            color: #495057;
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        .customer-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        .section-title {
            color: #b78f3b;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .footer {
            margin-top: 30px;
            font-size: 13px;
            color: #6D6D6D;
            text-align: center;
        }
        .footer a {
            color: #b78f3b;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Invoice Terbayar</h1>
        
        <p>Invoice telah dibayar lunas oleh customer.</p>
        
        <div class="invoice-info">
            <div class="invoice-number">
                Invoice #{{ $transaction->invoice_id }}
                <span class="status">Terbayar</span>
            </div>
            
            <div class="detail">
                <span class="label">Tanggal:</span>
                <span class="value">{{ $transaction->date }}</span>
            </div>
            
            <div class="detail">
                <span class="label">Total Dibayarkan:</span>
                <span class="value total">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="customer-section">
            <div class="section-title">Customer</div>
            <p><strong>{{ $transaction->customer->first_name }} {{ $transaction->customer->last_name }}</strong></p>
            <p>{{ $transaction->customer->email }}</p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} <a href="https://seco.id/" target="_blank">seco</a>. All rights reserved.
        </div>
    </div>
</body>
</html>