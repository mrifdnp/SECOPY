<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice Baru</title>
    <style>
        body {
            font-family: 'Cabinet Grotesk', Arial, sans-serif;
            background-color: #f6f6f6;
            color: #3d3d3d;
            padding: 40px 0;
            margin: 0;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #b78f3b 0%, #9d7231 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 16px;
        }
        .content {
            padding: 30px;
        }
        .invoice-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #b78f3b;
        }
        .invoice-number {
            font-size: 20px;
            font-weight: 600;
            color: #b78f3b;
            margin-bottom: 10px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }
        .detail-row:last-child {
            margin-bottom: 0;
            border-top: 1px solid #e9ecef;
            padding-top: 12px;
            margin-top: 12px;
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
            font-size: 18px;
            color: #b78f3b;
        }
        .customer-section {
            margin-top: 25px;
        }
        .section-title {
            color: #b78f3b;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #f1f3f4;
        }
        .customer-info {
            background: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
        }
        .customer-name {
            font-size: 16px;
            font-weight: 600;
            color: #3d3d3d;
            margin-bottom: 8px;
        }
        .customer-email {
            color: #6D6D6D;
            font-size: 15px;
        }
        .action-button {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            background-color: #b78f3b;
            color: white;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            transition: background-color 0.2s ease-in-out;
        }
        .btn:hover {
            background-color: #9d7231;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px 30px;
            font-size: 13px;
            color: #6D6D6D;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .footer a {
            color: #b78f3b;
            text-decoration: none;
        }
        .status-badge {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📧 Invoice Baru Dibuat</h1>
            <p>Pemberitahuan Admin</p>
        </div>
        
        <div class="content">
            <div class="invoice-info">
                <div class="invoice-number">
                    Invoice #{{ $transaction->invoice_id }}
                    <span class="status-badge">Baru</span>
                </div>
                
                <div class="detail-row">
                    <span class="label">Tanggal:</span>
                    <span class="value">{{ $transaction->date }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="label">Status:</span>
                    <span class="value">Menunggu Pembayaran</span>
                </div>
                
                <div class="detail-row">
                    <span class="label">Total:</span>
                    <span class="value total">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="customer-section">
                <div class="section-title">Informasi Customer</div>
                <div class="customer-info">
                    <div class="customer-name">
                        👤 {{ $transaction->customer->first_name }} {{ $transaction->customer->last_name }}
                    </div>
                    <div class="customer-email">
                        ✉️ {{ $transaction->customer->email }}
                    </div>
                </div>
            </div>

            <p style="color: #6D6D6D; font-size: 14px; line-height: 1.6; margin-top: 25px;">
                Ini adalah notifikasi otomatis bahwa invoice baru telah dibuat dalam sistem. 
                Silakan login ke dashboard admin untuk melihat detail lengkap dan mengelola invoice ini.
            </p>
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} <a href="https://seco.id/" target="_blank">seco</a>. All rights reserved.<br>
            Email ini dikirim secara otomatis, mohon jangan membalas email ini.
        </div>
    </div>
</body>
</html>