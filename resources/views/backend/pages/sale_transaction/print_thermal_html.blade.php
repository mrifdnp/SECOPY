@php
    $rp = fn($n) => 'Rp' . number_format((int)($n ?? 0), 0, ',', '.');

    $invoice = $sale_transaction->invoice_id ?? $sale_transaction->id;
    $storeName = $store_name ?? (session('store.name') ?? 'Toko');

    $payName = strtoupper($payment_type->payment_type ?? 'cash');
    $adminName = $sale_transaction->user->first_name ?? 'admin';
    
    $grandTotal = (int)($sale_transaction->total ?? $computed_total ?? 0);

    /* Tanggal */
    $tsRaw = $sale_transaction->paid_at ?? $sale_transaction->date ?? $sale_transaction->created_at;
    $displayNumeric = \Carbon\Carbon::parse($tsRaw)->format('d F Y H:i:s');
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
			font-family: Arial, Helvetica, sans-serif;
		}

        #invoice-POS{
			padding:2mm;
			margin: 0 auto;
			width: 80mm;
			background: #FFF;
        }

		h1{
			font-size: 2em;
			color: #000;
		}

		h2{
			font-size: .8em;
		}

		h3{
			font-size: 1.6em;
			font-weight: 600;
			line-height: 2em;
		}

		p{
			font-size: 0.8em;
			color: #000;
			line-height: 1.2em;
		}
 
		#top, #mid,#bot{ /* Targets all id with 'col-' */
			border-bottom: 1px solid #EEE;
		}

		#top{
		    /*min-height: 100px;*/
		    
		}
		#mid{min-height: 80px;} 
		#bot{ min-height: 50px;}

		#top .logo{
			/*height: 60px;*/
			/*width: 60px;*/
			 /*background: url(http://michaeltruong.ca/images/logo1.png) no-repeat; */
			background-size: 60px 60px;
		}

		.clientlogo{
			float: left;
			height: 60px;
			width: 60px;
			 /*background: url(http://michaeltruong.ca/images/client.jpg) no-repeat; */
			background-size: 60px 60px;
			border-radius: 50px;
		}

		.info{
			display: block;
			margin-left: 0;
		}

		.title{
			float: right;
		}

		.title p{
			text-align: right;
		} 

		table{
			width: 100%;
			border-collapse: collapse;
		}

		td{
			
		}

		.tabletitle{
			font-size: 1em;
			background: #FFF;
			font-weight: 600;
		}

		.service{
			border-bottom: 1px solid #EEE;
		}

		.item{
			width: 24mm;
		}

		.itemtext{
			font-size: 0.8em;font-weight: 400;
			margin-bottom: 1px;margin-top:2px;padding-top:0px;padding-bottom:0px;
		}

		#legalcopy{
			margin-top: 5mm;
		}

    </style>
</head>
<body>
  <div id="invoice-POS">
    <center id="top">
      <div class="logo" style='text-align:center;'>
          @if(!empty($logo_src))
              <img src="{{ $logo_src }}" style="width:40%;" />
          @else
              <img src="{{ public_path('img/default/seco.png') }}" style="width:40%;" />
          @endif
      </div>
      <div class="info"> 
      </div>
    </center>
    
    <div id="mid">
      <div class="info">
        <p style="line-height:16px;"> 
            Jl. Ringroad Selatan, Giwangan, Kec. Umbulharjo<br>
            Kota Yogyakarta, Daerah Istimewa Yogyakarta 55163<br>
            Whatsapp : 0851-1707-8163<br>
            Instagram : @seco_bysekenliving
        </p>
      </div>
    </div>
    
    <div id="bot">
		<div id="table">
			<h2 style="text-align: center;">RECEIPT</h2>
			<table>	
                @php
                    $subTotal = (int)($computed_subtotal ?? 0);
                    $discNom = (int)($discount_nominal ?? 0);
                    $discPctAmount = (int)($discount_percent_amount ?? 0);
                    $totalDiscount = $discNom + $discPctAmount;
                @endphp
                @foreach($items as $it)
                    <tr class="service" style="margin-bottom: 0;margin-top:0;padding-top:0px;padding-bottom:0px;">
                        <td class="tableitem" style="padding-right: 20px;">
                            <p class="itemtext">{{ $it->product_title }} @if($it->variant_name || $it->color_label)<br><span style="font-size:0.75em;">{{ $it->variant_name ?: '' }}{{ ($it->variant_name && $it->color_label) ? ' / ' : '' }}{{ $it->color_label ?: '' }}</span>@endif <br>{{ $rp($it->final_unit_price ?? 0) }} x {{ (int)$it->qty }}</p>
                        </td>
                        <td class="tableitem" style="padding-right: 5px;">
                            <p class="itemtext">{{ $rp($it->line_subtotal ?? 0) }}</p>
                        </td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>
                @endforeach
                @if($totalDiscount > 0)
                    <tr class="service" style="margin-bottom: 0;margin-top:0;padding-top:0px;padding-bottom:0px;">
                        <td class="tableitem" style="padding-right: 20px;">
                            <p class="itemtext">Discount <br>-{{ $rp($totalDiscount) }} x 1</p>
                        </td>
                        <td class="tableitem" style="padding-right: 5px;">
                            <p class="itemtext">-{{ $rp($totalDiscount) }}</p>
                        </td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>
                @endif
				<tr class="tabletitle">					
					<td class="Rate"><h2 style="font-size: 0.8em;font-weight: 600 !important;">Sub Total</h2></td>
					<td class="payment"><h2 style="font-size: 0.8em;font-weight: 600 !important;">{{ $rp($subTotal) }}</h2></td>
					<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
				</tr>
				
				<tr class="tabletitle">					
					<td class="Rate" colspan="3"><h2 style="font-size:1.2em !important;">TOTAL&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $rp($grandTotal) }}</h2></td>
				</tr>
				<tr class="tabletitle">					
					<td class="Rate">
						<h2 style="font-size: 0.8em;font-weight: 600 !important;">Payment Method</h2>
					</td>
					<td class="payment">
						<h2 style="font-size: 0.8em;font-weight: 600 !important;">
							{{ $payName }}
						</h2>
					</td>
					<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
				</tr> 
				<tr class="tabletitle">					
					<td class="Rate">
						<h2 style="font-size: 0.8em;font-weight: 600 !important;">Admin</h2>
					</td>
					<td class="payment">
						<h2 style="font-size: 0.8em;font-weight: 600 !important;">
							{{ $adminName }}
						</h2>
					</td>
					<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
				</tr> 
			</table>
		</div>
		<div id="legalcopy">
			<p class="legal" style="text-align: center;"><strong>Thank you for shopping!</strong>&nbsp;
			</p>
			<p class="legal" style="text-align: center;">
                {{ $displayNumeric }}
			</p>
		</div>
	</div>
  </div>
  <script>
      window.onload = function() {
          window.print();
      };
  </script>
</body>
</html>
