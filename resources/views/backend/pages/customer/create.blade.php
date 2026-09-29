@extends('layouts.master')
@section('title') Add {{ $titleHeader->name }} @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Systems @endslot
@slot('title') Add {{ $titleHeader->name }} @endslot
@endcomponent


<div class="row">
    <div class="col-lg-12">                
        <div class="card">            
            <div class="card-body">
                @include('backend.includes.flash')
                <form id="dt_form" action="{{route('customer.store')}}" class="form-horizontal" method="post" enctype="multipart/form-data">
                    @csrf
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
<label class="col-sm-12 control-label">First name</label>
<div class="col-sm-12"><input value="{{ old('first_name') }}" type="text" class="form-control" name="first_name" id="first_name" placeholder="" size="50" >
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Last name</label>
<div class="col-sm-12"><input value="{{ old('last_name') }}" type="text" class="form-control" name="last_name" id="last_name" placeholder="" size="50" >
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Email</label>
<div class="col-sm-12"><input value="{{ old('email') }}" type="email" class="form-control" name="email" id="email" placeholder="" size="50" >
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Password</label>
<div class="col-sm-12"><input type="password" class="form-control" name="password" id="password" placeholder="" size="50" >
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Phone</label>
<div class="col-sm-12"><input value="{{ old('phone') }}" type="text" class="form-control" name="phone" id="phone" placeholder="" size="50" >
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Birthday</label>
<div class="col-sm-12"><div class="input-group date col-lg-10 col-md-10 col-sm-10 col-xs-10"><div class="input-group-addon"><i class="ri-calendar-2-fill"></i></div><input type="text" class="form-control pull-left" id="datepicker-birthday" name="birthday" value="{{ $dateNow }}" ></div>
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Postcode</label>
<div class="col-sm-12"><input value="{{ old('postcode') }}" type="text" class="form-control" name="postcode" id="postcode" placeholder="" size="50" >
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Sub district</label>
<div class="col-sm-12"><input value="{{ old('sub_district') }}" type="text" class="form-control" name="sub_district" id="sub_district" placeholder="" size="50" >
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">District</label>
<div class="col-sm-12"><input value="{{ old('district') }}" type="text" class="form-control" name="district" id="district" placeholder="" size="50" >
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">City</label>
<div class="col-sm-12"><input value="{{ old('city') }}" type="text" class="form-control" name="city" id="city" placeholder="" size="50" >
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Province</label>
<div class="col-sm-12"><input value="{{ old('province') }}" type="text" class="form-control" name="province" id="province" placeholder="" size="50" >
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Address</label>
<div class="col-sm-12"><textarea class="form-control" name="address" rows="3" style="width: 50%" placeholder="" >
</textarea>
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Onboarding channel</label>
<div class="col-sm-12"><input value="{{ old('onboarding_channel') }}" type="text" class="form-control" name="onboarding_channel" id="onboarding_channel" placeholder="" size="50" >
</div>
</div>

                            <br>                                                                                                                                                                                 
                        </div>
                        <span class="indoit-box-helper"></span>		
                        <div class="indoit-box-action">
                            <div class="col-sm-offset-2">
                                <div class="indoit-box-action-btn"> 
                                    <a href="{{route('customer.index')}}" class="btn btn-soft-secondary">Back to List</a>                                                                       
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
                        $('#datepicker-birthday').datepicker({
                            autoclose: true
                        })
                    });

    
</script>
@endsection
