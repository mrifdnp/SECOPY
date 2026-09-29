@extends('layouts.master')
@section('title') Pengeluaran @endsection
@section('content')
<!-- start plan -->
<section>
    <div class="row">
        <div class="col-xl-8">
            @include('backend.includes.flash')
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
           
            <!-- end card -->
        </div>
        <!-- end col -->

      
        <!-- end col -->
        
    </div>
    <!-- end row -->

</section>
<!-- end plan -->

{{-- modal add customer --}}
{{-- finish modal add customer --}}
{{-- modal payment --}}
<div id="myPaymentModal" class="modal fade modal-primary show" aria-modal="true" role="dialog" style="display: block;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modal-filter">
            <div class="modal-header p-3 bg-primary bg-gradient">                
                <h5 class="text-white"><i class="ri-secure-payment-fill"> </i> Pengeluaran</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>    
            </div>
            <form id="formPayment" name="formPayment" action="{{ route('pengeluaran.store') }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="box-body indoit-form form-horizontal">
                        <div class="row">
                            <div class="col-md-3 col-lg-3">
                                <label for="" class="col-md-12" style="line-height:40px;">Metode Pembayaran</label>                                    
                            </div>
                            <div class="col-md-9 col-lg-9">
                                <div class="row">
                                    @php
                                        $count = 0;
                                    @endphp
                                    @foreach ($payments as $key => $item)
                                        @if ($count == 0)
                                            <div class="col-md-4 col-lg-4 mb-2">
                                                <div data-bs-toggle="collapse" data-bs-target="#paymentmethodCollapse" aria-expanded="true" aria-controls="paymentmethodCollapse">
                                                    <div class="form-check card-radio">
                                                        <input id="paymentMethod-{{ $key }}" name="paymentMethod" type="radio" class="form-check-input" value="{{ $item->id }}" checked>
                                                        <label class="form-check-label" for="paymentMethod-{{ $key }}" style="padding:0.6rem !important;">
                                                            <span class="fs-16 text-muted me-2">                                                            
                                                                @if ($item->icon)
                                                                    <img src="{{ asset('img/payment/'.$item->icon) }}" alt="" style="width: 35%;">
                                                                @else
                                                                    <img src="{{ asset('img/noimage.png') }}" alt="" style="width: 25%;">
                                                                @endif
                                                            </span>
                                                            <span class="fs-14 text-wrap">{{ $item->name }}</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="col-md-4 col-lg-4">
                                                <div data-bs-toggle="collapse" data-bs-target="#paymentmethodCollapse" aria-expanded="true" aria-controls="paymentmethodCollapse">
                                                    <div class="form-check card-radio">
                                                        <input id="paymentMethod-{{ $key }}" name="paymentMethod" type="radio" class="form-check-input" value="{{ $item->id }}">
                                                        <label class="form-check-label" for="paymentMethod-{{ $key }}" style="padding:0.6rem !important;">
                                                            <span class="fs-16 text-muted me-2">                                                            
                                                                @if ($item->icon)
                                                                    <img src="{{ asset('img/payment/'.$item->icon) }}" alt="" style="width: 35%;">
                                                                @else
                                                                    <img src="{{ asset('img/noimage.png') }}" alt="" style="width: 25%;">
                                                                @endif
                                                            </span>
                                                            <span class="fs-14 text-wrap">{{ $item->name }}</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif     
                                        @php
                                            $count++;
                                        @endphp                                   
                                    @endforeach                                                                        
                                </div>                                
                            </div>
                        </div>
                        

                        <div class="row" style="margin-top: 8px;">
                            <div class="col-md-3 col-lg-3">
                                <label for="" class="col-md-12" id="labelTotalItem" style="line-height:35px;">Jenis Pengeluaran</label>                                    
                            </div>
                            <div class="col-md-9 col-lg-9">
                                <div class="form-group">                                    
                                    <div class="mb-3" id="select-supplier">
                                        <select class="form-control" data-choices name="head_accounts_id" id="head_accounts_id" required>
                                            <option value="">Pilih Jenis Pengeluaran</option>
                                            @foreach ($headAccounts as $key => $item)
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                            @endforeach                                                                        
                                        </select>
                                        <div class="invalid-feedback"><i class="las   las la-exclamation-circle"></i> Jenis Pengeluaran harus di pilih terlebih dahulu.</div>
                                    </div>
                            </div>
                        </div>
                        
                        {{-- <br> --}}
                        <div class="row" style="margin-top: 0px;">
                            <div class="col-md-3 col-lg-3">
                                <label for="" class="col-md-12" style="line-height:35px;">Total Pengeluaran</label>                                    
                            </div>
                            <div class="col-md-9 col-lg-9">
                                <div class="form-group">
                                    <div class="input-group  mb-3">
                                        <span class="input-group-text" id="product-price-addon">Rp.</span>
                                        <input type="text" class="form-control" id="total_pengeluaran" name="total_pengeluaran" placeholder="Total Pengeluaran" aria-label="Price" aria-describedby="product-price-addon" >                                                
                                    </div>
                                    <div class="invalid-feedback">Silakan masukkan total pengeluaran.</div>
                                </div>
                            </div>
                        </div>                       

                        <div class="row" style="margin-top: 0px;">
                            <div class="col-md-3 col-lg-3">
                                <label for="" class="col-md-12" style="line-height:35px;">Remark</label>                                    
                            </div>
                            <div class="col-md-9 col-lg-9">
                                <div class="form-group">
                                    <div class="input-group  mb-3">
                                        <span class="input-group-text" id="product-price-addon">&nbsp; <i class="ri ri-file-edit-line"> </i> </span>
                                        <input type="text" class="form-control"  name="remark" placeholder="Remark/Catatan" >                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div><!-- /.box-body -->
                </div>
                <div class="modal-footer">                                        
                    <button type="submit" id="btnLanjutkan" class="btn btn-danger pull-left text-light">
                        <i class="las la-save"></i>
                        Simpan
                    </button>
                </div>               
            </form>
        </div>
    </div>
</div>	
{{-- end modal payment --}}
@endsection


@section('css')
<style>
    .error {
        border: 1px solid #f06548 !important;
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css" integrity="sha512-kq3FES+RuuGoBW3a9R2ELYKRywUEQv0wvPTItv3DSGqjpbNtGWVdvT8qwdKkqvPzT93jp8tSF4+oN4IeTEIlQA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

@endsection

@section('script')

<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/form-input-spin.init.js') }}"></script>

<script src="https://unpkg.com/gridjs/plugins/selection/dist/selection.umd.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ URL::asset('build/js/pages/select2.init.js') }}"></script>
<script>    
        
    $("#btnAddCustomer").on('click',function(){
        $("#myAddCustomerModal").modal('show');
    });

    $("#btnPayment").on('click',function(){
        $("#myPaymentModal").modal('show');
    });

    $('#total_pengeluaran').mask('000.000.000', {reverse: true});
</script>
@endsection
