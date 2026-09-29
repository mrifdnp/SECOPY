<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>INVOICE</title>
    <style>
        .page-break {
            page-break-after: always;
        }
    
        body {
            margin-top: 47px;
            font-size: 11px;
            font-family: Arial, Helvetica, sans-serif;
        }
    
        .kop-atas {
            text-align: left;
            padding-left: 500px;
        }
    
        table {
            border-spacing: 0px;
            border-collapse: separate;
        }
    
        .alamat {
            display: block;
            min-height: 300px;
        }
    
        .margin_bottom {
            margin-bottom: 1000px !important;
        }
    </style>
  </head>
  <body>
      <table width="100%" cellspacing="0" cellpadding="0" style="margin-top:-50px;">
          <tr>
            <td style="width: auto;padding-right:430px;font-size:20px;font-weight:bold;font-family:Arial, Helvetica, sans-serif;">
                INVOICE
            </td>
            <td rowspan="5" style="width:auto;text-align:right;">
                @if(!empty($logo_src))
                    <img src="{{ $logo_src }}" alt="Logo" style="width: 90px;height: auto;padding: 10px;border-style: none;">
                @endif
            </td>
          </tr>
          <tr>
            <td style="width: auto;padding-right:430px;margin-bottom:-5px;margin-top:10px;padding-top:10px;font-size:12px;font-weight:bold;">
                {{ strtoupper($store_name) }}
            </td>
          </tr>
          <tr>
            <td style="width: 300px;padding-right:250px;margin-top:-5px;padding-bottom:-5px !important;font-size:11px;">
                Jl. Ringroad Selatan, Giwangan, Kec. Umbulharjo
            </td>
          </tr>
          <tr>
            <td style="width: 300px;padding-right:250px;margin-top:-5px !important;padding-top:-5px !important;font-size:11px;">
                Yogyakarta 55163
            </td>
          </tr>
          <tr>
            <td style="width: 300px;padding-right:250px;margin-top:-10px !important;padding-top:-10px !important;font-size:11px; white-space: nowrap;">
                <b>Phone:</b> 0851-1707-8163
            </td>
          </tr>
          <hr>
          <tr>
            <br>
          </tr>
      </table>
    <table class="table table-bordered" style="font-size: 11px;">
    <thead>
      <tr>
        <td style="width: 50px;"><b>Bill To:</b></td>
        <td style="width: 100px;">{{ trim(($customer->first_name ?? '').' '.($customer->last_name ?? '')) ?: '—' }}</td>
        <td style="width:370px;text-align:right;"><b>Invoice No.:</b></td>     
        <td style="padding-left: 35px;"><b>{{ $sale_transaction->invoice_id ?? $sale_transaction->id }}</b></td>     
      </tr>
      <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        <td style="width:370px;text-align:right;"><b>Date:</b></td>     
        <td style="padding-left: 35px;">{{ \Carbon\Carbon::parse($sale_transaction->date ?? $sale_transaction->created_at)->format('d F Y') }}</td>
      </tr>
      <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        <td style="width:370px;text-align:right;"><b>Due date:</b></td>     
        <td style="padding-left: 35px;">{{ \Carbon\Carbon::parse($sale_transaction->date ?? $sale_transaction->created_at)->format('d F Y') }}</td>
      </tr>
      <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        <td style="width:370px;text-align:right;"><b>Payment status:</b></td>     
        <td style="padding-left: 35px;"><b>{{ $is_paid ? 'Paid' : 'Unpaid' }}</b></td>
      </tr>      
      </thead>
    </table>
    <table style="margin-top:20px;width:100%;" border="1">
        <thead>
            <tr>
                <th style="width: 5%;padding-top:5px;padding-bottom:5px;">No</th>
                <th style="width: 35%;padding-top:5px;padding-bottom:5px;">Item</th>
                <th style="width: 8%;padding-top:5px;padding-bottom:5px;">Quantity</th>
                <th style="width: 15%;padding-top:5px;padding-bottom:5px;">Unit price</th>
                <th style="width: 7%;padding-top:5px;padding-bottom:5px;">Tax</th>
                <th style="width: 10%;padding-top:5px;padding-bottom:5px;">Discount</th>
                <th style="width: 20%;padding-top:5px;padding-bottom:5px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $rp = fn($n) => 'Rp ' . number_format((int)($n ?? 0), 0, ',', '.'); @endphp
            @foreach ($items as $key => $si)
                <tr>
                    <td style="width: 5%;padding-top:5px;padding-bottom:5px;padding-left:5px;">{{ $key+1 }}</td>
                    <td style="width: 35%;padding-top:5px;padding-bottom:5px;padding-left:5px;">{{ $si->product_title }}@if($si->variant_name || $si->color_label)<br><span style="font-size:9px;color:#555;">{{ $si->variant_name ?: '' }}{{ ($si->variant_name && $si->color_label) ? ' / ' : '' }}{{ $si->color_label ?: '' }}</span>@endif</td>
                    <td style="width: 8%;text-align: center;padding-top:5px;padding-bottom:5px;">{{ (int)$si->qty }}</td>
                    <td style="width: 15%;text-align: right;padding-top:5px;padding-bottom:5px;padding-right:5px;">{{ $rp($si->final_unit_price ?? $si->price) }}</td>
                    <td style="width: 7%;text-align: center;padding-top:5px;padding-bottom:5px;">---</td>
                    <td style="width: 10%;text-align: right;padding-top:5px;padding-bottom:5px;padding-right:5px;">{{ $si->discount_percent ? rtrim(rtrim(number_format((float)$si->discount_percent, 2, ',', '.'), '0'), ',') . ' %' : '0 %' }}</td>
                    <td style="width: 20%;text-align: right;padding-top:5px;padding-bottom:5px;padding-right:5px;">{{ $rp($si->line_subtotal) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <table style="margin-top:20px; width:300px; margin-left:auto;" border="1">
        <tbody>
            <tr>
                <td style="text-align: left;padding-top:5px;padding-bottom:5px;padding-left:5px;">Discount:</td>
                <td style="text-align: right;padding-top:5px;padding-bottom:5px;padding-right:5px;"><b>{{ $rp($discount_total) }}</b></td>
            </tr>
            <tr>
                <td style="text-align: left;padding-top:5px;padding-bottom:5px;padding-left:5px;">Additional Cost:</td>
                <td style="text-align: right;padding-top:5px;padding-bottom:5px;padding-right:5px;"><b>{{ $rp($additional_cost) }}</b></td>
            </tr>
        </tbody>
    </table>
    <div style="width: 100%; text-align: right;">
        <hr style="border: 1px dashed; width:300px; margin-right:0; margin-top:10px;">
    </div>
    <table style="margin-top:10px; width:300px; margin-left:auto;" border="1">
        <tbody>
            <tr>
                <td style="padding-top:5px;padding-bottom:5px;padding-left:5px;">Grand Total:</td>
                <td style="text-align: right;padding-top:5px;padding-bottom:5px;padding-right:5px;"><b>{{ $rp($computed_total) }}</b></td>
            </tr>
            <tr>
                <td style="padding-top:5px;padding-bottom:5px;padding-left:5px;">
                    Payment method:<br>
                    <span style="font-weight:normal; font-size:10px;">{{ strtoupper($payment_type->payment_type ?? '-') }}</span>
                </td>
                <td style="text-align: right;padding-top:5px;padding-bottom:5px;padding-right:5px; vertical-align:bottom;">
                    <b>{{ $rp($sale_transaction->amount_paid ?? 0) }}</b>
                </td>
            </tr>
            <tr>
                <td style="padding-top:5px;padding-bottom:5px;padding-left:5px;">Paid amount:</td>
                <td style="text-align: right;padding-top:5px;padding-bottom:5px;padding-right:5px;"><b>{{ $rp($sale_transaction->amount_paid ?? 0) }}</b></td>
            </tr>
        </tbody>
    </table>
  </body>
</html>