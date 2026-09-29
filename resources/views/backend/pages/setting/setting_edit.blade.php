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
                <form id="dt_form" action="{{route('setting.update',$setting->id)}}" class="form-horizontal" method="post">
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
                            <div class="form-group">
                                <label for="" class="col-sm-2 control-label">Key*</label>
                                <div class="col-sm-10">
                                    <input value="{{ old('setting_key',$setting->key) }}" type="text" class="form-control" name="setting_key" id="setting_key" placeholder="Key" size="50">
                                </div>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="" class="col-sm-2 control-label">Value*</label>
                                <div class="col-sm-10">
                                    <input value="{{ old('setting_value',$setting->value) }}" type="text" class="form-control" name="setting_value" id="setting_value" placeholder="Value" size="50">
                                </div>
                            </div>
                            <br>                                                                                                                                                                                 
                        </div>
                        <span class="indoit-box-helper"></span>		
                        <div class="indoit-box-action">
                            <div class="col-sm-offset-2">
                                <div class="indoit-box-action-btn"> 
                                    <a href="{{route('setting.index')}}" class="btn btn-soft-secondary">Back to List</a>                                                                       
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
@endsection
