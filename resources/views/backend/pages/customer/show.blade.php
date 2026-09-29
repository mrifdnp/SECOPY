@extends('layouts.master')
@section('title') Detail {{ $titleHeader->name }} @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Systems @endslot
@slot('title') Detail {{ $titleHeader->name }} @endslot
@endcomponent


<div class="row">
    <div class="col-lg-12">                
        <div class="card">            
            <div class="card-body">
                @include('backend.includes.flash')
                <div class="box">
                    <div class="box-body indoit-form">
                        <table class="table table-bordered">
                            <tr>
                        <td style="width: 200px"><b>First name</b></td>
                        <td>{{ $customer->first_name }}</td>
                    </tr>
<tr>
                        <td style="width: 200px"><b>Last name</b></td>
                        <td>{{ $customer->last_name }}</td>
                    </tr>
<tr>
                        <td style="width: 200px"><b>Email</b></td>
                        <td>{{ $customer->email }}</td>
                    </tr>
<tr>
                        <td style="width: 200px"><b>Phone</b></td>
                        <td>{{ $customer->phone }}</td>
                    </tr>
<tr>
                        <td style="width: 200px"><b>Birthday</b></td>
                        <td>{{ $customer->birthday }}</td>
                    </tr>
<tr>
                        <td style="width: 200px"><b>Postcode</b></td>
                        <td>{{ $customer->postcode }}</td>
                    </tr>
<tr>
                        <td style="width: 200px"><b>Sub district</b></td>
                        <td>{{ $customer->sub_district }}</td>
                    </tr>
<tr>
                        <td style="width: 200px"><b>District</b></td>
                        <td>{{ $customer->district }}</td>
                    </tr>
<tr>
                        <td style="width: 200px"><b>City</b></td>
                        <td>{{ $customer->city }}</td>
                    </tr>
<tr>
                        <td style="width: 200px"><b>Province</b></td>
                        <td>{{ $customer->province }}</td>
                    </tr>
<tr>
                        <td style="width: 200px"><b>Address</b></td>
                        <td>{{ $customer->address }}</td>
                    </tr>
<tr>
                        <td style="width: 200px"><b>Onboarding channel</b></td>
                        <td>{{ $customer->onboarding_channel }}</td>
                    </tr>

                        </table>  
                    </div>
                    <span class="indoit-box-helper"></span>		
                    <div class="indoit-box-action">
                        <div class="col-sm-offset-2">
                            <div class="indoit-box-action-btn d-flex gap-2">                                    
                                <a href="{{ url()->previous() }}" class="btn btn-soft-secondary">Back to List</a>                                
                                <button type="button" class="btn btn-primary" onclick="printLabel()">
                                    <i class="ri-printer-line align-middle me-1"></i> Print Label Pengiriman
                                </button>
                            </div>
                        </div>
                    </div>                    
                </div>                
            </div>
        </div>
    </div>
    <!--end col-->
</div>
<!--end row-->

<!-- Area Print Label (Sembunyi dari UI normal) -->
<div id="printArea" style="display: none;">
    <div style="font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-size: 11pt; line-height: 1.4; max-width: 400px; padding: 0 10px 10px 10px; color: #000;">
        <div style="text-align: center; margin-bottom: 15px; border-bottom: 2px dashed #ccc; padding-bottom: 10px;">
            <img src="{{ asset('seco-logo.svg') }}" alt="Seco Logo" style="width: 15%; max-width: 50px; margin-bottom: 8px;">
            <div>Jl. Ringroad Selatan - Giwangan Umbulharjo Yogyakarta</div>
        </div>

        <div style="margin-bottom: 5px;">
            <div style="font-weight: bold; text-decoration: underline; margin-bottom: 5px;">PENERIMA :</div>
            <div><strong>{{ strtoupper($customer->first_name . ' ' . $customer->last_name) }}</strong></div>
            <div>{{ $customer->phone }}</div>
        </div>

        <div>
            @if($customer->address) <div>{!! nl2br(e($customer->address)) !!}</div> @endif
            @if($customer->sub_district) <div>{{ $customer->sub_district }}</div> @endif
            @if($customer->district) <div>{{ $customer->district }}</div> @endif
            @if($customer->city) <div>{{ $customer->city }}</div> @endif
            @if($customer->province) <div>{{ $customer->province }}</div> @endif
            @if($customer->postcode) <div>{{ $customer->postcode }}</div> @endif
        </div>

        <div>
            <div style="font-weight: bold; text-decoration: underline; margin-bottom: 10px;">PENGIRIM :</div>
            <div>SECO by Seken Living</div>
            <div>0851-1707-8163</div>
        </div>
    </div>
</div>


@endsection


@section('css')
<link href="{{ URL::asset('build/libs/velzon-dependencies/select2-bootstrap-theme-0.1.0-beta.10/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js')}}"></script>
<script>
    function printLabel() {
        var printContent = document.getElementById('printArea').innerHTML;
        var printWindow = window.open('', '', 'height=600,width=400');
        printWindow.document.write('<html><head><title>Print Label Pengiriman</title>');
        printWindow.document.write('<style>@page { margin: 1cm; } body { margin: 0; }</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(printContent);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(function() {
            printWindow.print();
            printWindow.close();
        }, 250);
    }
</script>
@endsection
