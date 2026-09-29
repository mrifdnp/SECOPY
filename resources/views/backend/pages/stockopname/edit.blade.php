@extends('layouts.master')
@section('title') Edit {{ $titleHeader->name }} @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Systems @endslot
@slot('title') Edit {{ $titleHeader->name }} @endslot
@endcomponent


<div class="row">
    <div class="col-lg-12">                
        <div class="card">            
            <div class="card-body">
                @include('backend.includes.flash')
                <form id="dt_form" action="{{route('stockopname.update', $stockopname)}}" class="form-horizontal" method="post">
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
<label class="col-sm-12 control-label">Title</label>
<div class="col-sm-12"><input value="{{ old('title', $stockopname->title) }}" type="text" class="form-control" name="title" id="title" placeholder="" size="50" >
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Date start</label>
<div class="col-sm-12"><div class="input-group date col-lg-10 col-md-10 col-sm-10 col-xs-10"><div class="input-group-addon"><i class="ri-calendar-2-fill"></i></div><input type="text" class="form-control pull-left" id="datepicker-date_start" name="date_start" value="{{ $stockopname->date_start }}" ></div>
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Date end</label>
<div class="col-sm-12"><div class="input-group date col-lg-10 col-md-10 col-sm-10 col-xs-10"><div class="input-group-addon"><i class="ri-calendar-2-fill"></i></div><input type="text" class="form-control pull-left" id="datepicker-date_end" name="date_end" value="{{ $stockopname->date_end }}" ></div>
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Store id</label>
<div class="col-sm-12"><select class="form-control js-example-basic-single select2" name="store_id" required>
                    @foreach ($stores as $store) {
                        <option value="{{ $store->id }}" @if ($stockopname->store_id == $store->id)
                        selected=""
                        @endif>{{ $store->name }}</option>
                    @endforeach
                            <br>                                                                                                                                                                                 
                        </div>
                        <span class="indoit-box-helper"></span>		
                        <div class="indoit-box-action">
                            <div class="col-sm-offset-2">
                                <div class="indoit-box-action-btn"> 
                                    <a href="{{route('stockopname.index')}}" class="btn btn-soft-secondary">Back to List</a>                                                                       
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
<link href="{{ URL::asset('build/libs/velzon-dependencies/select2-bootstrap-theme-0.1.0-beta.10/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('script')


<script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js')}}"></script>
<script src="{{ URL::asset('build/libs/velzon-dependencies/ckeditor5-classic-40.2.0/ckeditor.js') }}"></script>

<script>
    $(function () {
                        $('#datepicker-date_start').datepicker({
                            autoclose: true
                        })
                    });
$(function () {
                        $('#datepicker-date_end').datepicker({
                            autoclose: true
                        })
                    });

    
</script>
@endsection
