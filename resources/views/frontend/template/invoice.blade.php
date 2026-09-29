<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }

        .header {
            /* text-align: center; */
            margin-bottom: 30px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .customer-info,
        .invoice-details {
            width: 48%;
        }

        .invoice-info-table td {
            width: 33.3%;
            vertical-align: top;
            padding: 0 10px;
        }

        .info-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        /* Alternative menggunakan table layout */
        .invoice-info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .invoice-info-table td {
            width: 50%;
            vertical-align: top;
            padding: 0 10px 0 0;
        }

        .invoice-info-table td:last-child {
            padding: 0 0 0 10px;
        }

        .info-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .table th {
            background: #f8f9fa;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #333;
        }

        .table td {
            padding: 10px 8px;
            border-bottom: 1px solid #ddd;
        }

        .table tbody tr:hover {
            background-color: #f9f9f9;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .table th {
            background: #f8f9fa;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #333;
        }

        .table td {
            padding: 10px 8px;
            border-bottom: 1px solid #ddd;
        }

        .table tbody tr:hover {
            background-color: #f9f9f9;
        }

        .product-name {
            font-weight: bold;
            margin-bottom: 3px;
        }

        .product-variant {
            font-size: 10px;
            color: #666;
            font-style: italic;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .summary {
            margin-top: 30px;
            float: right;
            width: 300px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }

        .summary-total {
            border-top: 2px solid #333;
            font-weight: bold;
            font-size: 14px;
            padding-top: 10px;
            margin-top: 10px;
        }

        .payment-info {
            clear: both;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 10px;
            color: #666;
        }

        .currency {
            font-family: monospace;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2 style="margin: 10px 0;">INVOICE</h2>
        <p style="margin: 5px 0;">No: {{ $invoice->invoice_id }}</p>
        <p style="margin: 5px 0;">Date: {{ \Carbon\Carbon::parse($invoice->date)->format('d F Y') }}</p>
    </div>

    <table class="invoice-info-table">
        <tr>
            <!-- Kolom 1 -->
            <td>
                <div class="info-title">Bill To:</div>
                <p><strong>{{ $invoice->customer->first_name }} {{ $invoice->customer->last_name }}</strong></p>
                <p>{{ $invoice->customer->email }}</p>
                <p>{{ $invoice->customer->phone }}</p>
                <p>{{ $invoice->customer->address }}<br>
                    {{ $invoice->customer->city }}, {{ $invoice->customer->province }}</p>
            </td>

            <!-- Kolom 2 -->
            <td>
                <div class="info-title">Invoice Details:</div>
                <p><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $invoice->payment_type)) }}</p>
                <p><strong>Channel:</strong> {{ ucfirst($invoice->channel) }}</p>
                <p><strong>Status:</strong>
                    <span style="color: {{ $invoice->payment_status === 'paid' ? 'green' : 'red' }};">
                        {{ ucfirst($invoice->payment_status) }}
                    </span>
                </p>
                @if ($invoice->paid_at)
                    <p><strong>Paid At:</strong> {{ \Carbon\Carbon::parse($invoice->paid_at)->format('d F Y H:i') }}
                    </p>
                @endif
            </td>

        </tr>
    </table>


    <table class="table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 45%;">Product</th>
                <th style="width: 10%;" class="text-center">Qty</th>
                <th style="width: 15%;" class="text-right">Unit Price</th>
                <th style="width: 10%;" class="text-center">Discount</th>
                <th style="width: 15%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <div class="product-name">
                            {{ $item->product_detail['product_title'] ?? 'Product' }}
                        </div>
                        <div class="product-variant">
                            {{ $item->product_detail['variant_name'] ?? '' }}
                            @if (!empty($item->product_detail['color_label']))
                                - {{ $item->product_detail['color_label'] ?? '' }}
                            @endif
                        </div>
                    </td>
                    <td class="text-center">{{ $item->qty }}</td>
                    <td class="text-right currency">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if ($item->discount_percent > 0)
                            {{ $item->discount_percent }}%
                        @elseif($item->discount_nominal > 0)
                            Rp {{ number_format($item->discount_nominal, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right currency">Rp
                        {{ number_format($item->final_price * $item->qty, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-row">
            <span>Subtotal:</span>
            <span class="currency">Rp {{ number_format($invoice->sub_total, 0, ',', '.') }}</span>
        </div>

        @if ($invoice->discount_percent > 0 || $invoice->discount_nominal > 0)
            <div class="summary-row">
                <span>Discount:</span>
                <span class="currency">
                    @if ($invoice->discount_percent > 0)
                        ({{ $invoice->discount_percent }}%)
                    @endif
                    Rp {{ number_format($invoice->discount_nominal, 0, ',', '.') }}
                </span>
            </div>
        @endif

        @if ($invoice->courier && isset($invoice->courier['cost']) && $invoice->courier['cost'] > 0)
            <div class="summary-row">
                <span>Shipping Cost ({{ $invoice->courier['service'] ?? 'Courier' }}):</span>
                <span class="currency">Rp {{ number_format($invoice->courier['cost'], 0, ',', '.') }}</span>
            </div>
        @endif

        @if ($invoice->additional_cost > 0)
            <div class="summary-row">
                <span>Additional Cost:</span>
                <span class="currency">Rp {{ number_format($invoice->additional_cost, 0, ',', '.') }}</span>
            </div>
        @endif

        @if ($invoice->xendit_fees > 0)
            <div class="summary-row">
                <span>Payment Fees:</span>
                <span class="currency">Rp {{ number_format($invoice->xendit_fees, 0, ',', '.') }}</span>
            </div>
        @endif

        <div class="summary-row summary-total">
            <span>Total:</span>
            <span class="currency">Rp {{ number_format($invoice->total + ($invoice->xendit_fees ?? 0), 0, ',', '.') }}</span>
        </div>
    </div>

    @if ($invoice->payment_status === 'paid')
        <div class="payment-info">
            <div class="info-title">Payment Information:</div>
            @if ($invoice->amount_paid)
                <p><strong>Amount Paid:</strong> <span class="currency">Rp
                        {{ number_format($invoice->amount_paid, 0, ',', '.') }}</span></p>
            @endif
            @if ($invoice->change > 0)
                <p><strong>Change:</strong> <span class="currency">Rp
                        {{ number_format($invoice->change, 0, ',', '.') }}</span></p>
            @endif
        </div>
    @endif

    @if ($invoice->courier && is_array($invoice->courier))
        <div class="payment-info">
            <div class="info-title">Shipping Information:</div>
            <p><strong>Courier:</strong> {{ $invoice->courier['name'] ?? 'N/A' }}</p>
            <p><strong>Service:</strong> {{ $invoice->courier['service'] ?? 'N/A' }} -
                {{ $invoice->courier['description'] ?? '' }}</p>
            <p><strong>Estimated Delivery:</strong> {{ $invoice->courier['etd'] ?? 'N/A' }}</p>
            @if ($invoice->resi)
                <p><strong>Tracking Number:</strong> {{ $invoice->resi }}</p>
            @endif
        </div>
    @endif

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>This invoice was generated on {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</p>
    </div>
</body>

</html>
