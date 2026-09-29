@extends('layouts.master')
@section('title')
    Edit {{ $titleHeader->name }}
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Systems
        @endslot
        @slot('title')
            Edit {{ $titleHeader->name }}
        @endslot
    @endcomponent


    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    @include('backend.includes.flash')
                    <form id="dt_form" action="{{ route('sales-transaction.update', $sale_transaction) }}"
                        class="form-horizontal" method="post">
                        @csrf
                        @method('put')
                        <div class="box">
                            <!-- /.box-header -->
                            <div class="box-body indoit-form">
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <div class="form-group mb-3">
                                    <label class="col-sm-12 control-label">Store id</label>
                                    <div class="col-sm-12"><input value="{{ old('store_id', $sale_transaction->store_id) }}"
                                            type="text" class="form-control" name="store_id" id="store_id"
                                            placeholder="" size="50">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-sm-12 control-label">Date</label>
                                    <div class="col-sm-12"><input value="{{ old('date', $sale_transaction->date) }}"
                                            type="text" class="form-control" name="date" id="date" placeholder=""
                                            size="50">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-sm-12 control-label">Invoice id</label>
                                    <div class="col-sm-12"><input
                                            value="{{ old('invoice_id', $sale_transaction->invoice_id) }}" type="text"
                                            class="form-control" name="invoice_id" id="invoice_id" placeholder=""
                                            size="50">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-sm-12 control-label">Customer id</label>
                                    <div class="col-sm-12"><input
                                            value="{{ old('customer_id', $sale_transaction->customer_id) }}" type="text"
                                            class="form-control" name="customer_id" id="customer_id" placeholder=""
                                            size="50">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-sm-12 control-label">Sub total</label>
                                    <div class="col-sm-12"><input
                                            value="{{ old('sub_total', $sale_transaction->sub_total) }}" type="text"
                                            class="form-control currency" name="sub_total" id="sub_total" placeholder=""
                                            size="50">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-sm-12 control-label">Discount percent</label>
                                    <div class="col-sm-12"><input
                                            value="{{ old('discount_percent', $sale_transaction->discount_percent) }}"
                                            type="text" class="form-control" name="discount_percent"
                                            id="discount_percent" placeholder="" size="50">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-sm-12 control-label">Discount nominal</label>
                                    <div class="col-sm-12"><input
                                            value="{{ old('discount_nominal', $sale_transaction->discount_nominal) }}"
                                            type="text" class="form-control currency" name="discount_nominal"
                                            id="discount_nominal" placeholder="" size="50">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-sm-12 control-label">Additional cost</label>
                                    <div class="col-sm-12"><input
                                            value="{{ old('additional_cost', $sale_transaction->additional_cost) }}"
                                            type="text" class="form-control currency" name="additional_cost"
                                            id="additional_cost" placeholder="" size="50">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-sm-12 control-label">Total</label>
                                    <div class="col-sm-12"><input value="{{ old('total', $sale_transaction->total) }}"
                                            type="text" class="form-control currency" name="total" id="total"
                                            placeholder="" size="50">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-sm-12 control-label">Payment type</label>
                                    <div class="col-sm-12"><input
                                            value="{{ old('payment_type', $sale_transaction->payment_type) }}"
                                            type="text" class="form-control" name="payment_type" id="payment_type"
                                            placeholder="" size="50">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-sm-12 control-label">Channel</label>
                                    <div class="col-sm-12"><input
                                            value="{{ old('channel', $sale_transaction->channel) }}" type="text"
                                            class="form-control" name="channel" id="channel" placeholder=""
                                            size="50">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-sm-12 control-label">Payment status</label>
                                    <div class="col-sm-12"><input
                                            value="{{ old('payment_status', $sale_transaction->payment_status) }}"
                                            type="text" class="form-control" name="payment_status"
                                            id="payment_status" placeholder="" size="50">
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="col-sm-12 control-label">Sales person</label>
                                    <div class="col-sm-12"><input
                                            value="{{ old('sales_person', $sale_transaction->sales_person) }}"
                                            type="text" class="form-control" name="sales_person" id="sales_person"
                                            placeholder="" size="50">
                                    </div>
                                </div>

                                <br>
                            </div>
                            <span class="indoit-box-helper"></span>
                            <div class="indoit-box-action">
                                <div class="col-sm-offset-2">
                                    <div class="indoit-box-action-btn">
                                        <a href="{{ route('sales-transaction.index') }}"
                                            class="btn btn-soft-secondary">Back to List</a>
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </div>
                            </div><!-- /.box-footer -->
                            <div class="indoit-box-required">
                                <hr>
                                <span class="required">*</span>
                                Field Required
                            </div><!-- /.box-footer -->
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>
    <!--end row-->


@endsection


@section('css')
    <link
        href="{{ URL::asset('build/libs/velzon-dependencies/select2-bootstrap-theme-0.1.0-beta.10/select2-bootstrap.min.css') }}"
        rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.css') }}" rel="stylesheet"
        type="text/css" />
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/ckeditor5-classic-40.2.0/ckeditor.js') }}"></script>

    <script>
        $('.sub_total').mask('000.000.000', {
            reverse: true
        });
        $('.discount_nominal').mask('000.000.000', {
            reverse: true
        });
        $('.additional_cost').mask('000.000.000', {
            reverse: true
        });
        $('.total').mask('000.000.000', {
            reverse: true
        });
    </script>
@endsection
