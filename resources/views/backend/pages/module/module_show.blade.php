@extends('layouts.master')
@section('title') Detail {{ $titleHeader->name }} @endsection
@section('cssAdded')
    <style>       

    </style>
@endsection
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
                            <table class="table table-bordered">
                                <tr>
                                    <td style="width: 200px"><b>Name</b></td>
                                    <td>{{ $module->name }}</td>
                                </tr>
                                <tr>
                                    <td><b>Icon</b></td>
                                    <td>{{ $module->icon }} <i class="{{ $module->icon }}"></i></td>
                                </tr>
                                <tr>
                                    <td><b>Url</b></td>
                                    <td>{{ $module->url }}</td>
                                </tr>
                                <tr>
                                    <td><b>Support</b></td>
                                    <td>
                                        @php                                                                                    
                                            $support = json_decode(stripslashes($module->support));                                                                                     
                                        @endphp
                                        <div class="form-check form-check-inline">
                                            <input disabled class="form-check-input" type="checkbox" id="inlineCheckbox6" name="a" value="1" 
                                                @if ($errors->any()) @if(old('a')=="1") checked @endif @else @if($support->a=='1') checked @endif @endif >
                                            <label class="form-check-label" for="inlineCheckbox6">Access</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input disabled class="form-check-input" type="checkbox" id="inlineCheckbox7" name="r" value="1"
                                            @if ($errors->any()) @if(old('r')=="1") checked @endif @else @if($support->r=='1') checked @endif @endif >
                                            <label class="form-check-label" for="inlineCheckbox7">Read</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input disabled class="form-check-input" type="checkbox" id="inlineCheckbox8" name="c" value="1"
                                            @if ($errors->any()) @if(old('c')=="1") checked @endif @else @if($support->c=='1') checked @endif @endif >
                                            <label class="form-check-label" for="inlineCheckbox8">Create</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input disabled class="form-check-input" type="checkbox" id="inlineCheckbox9" name="u" value="1"
                                            @if ($errors->any()) @if(old('u')=="1") checked @endif @else @if($support->u=='1') checked @endif @endif >
                                            <label class="form-check-label" for="inlineCheckbox9">Update</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input disabled class="form-check-input" type="checkbox" id="inlineCheckbox10" name="d" value="1"
                                            @if ($errors->any()) @if(old('d')=="1") checked @endif @else @if($support->d=='1') checked @endif @endif >
                                            <label class="form-check-label" for="inlineCheckbox10">Delete</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input disabled class="form-check-input" type="checkbox" id="inlineCheckbox11" name="t" value="1"
                                            @if ($errors->any()) @if(old('a')=="t") checked @endif @else @if($support->t=='1') checked @endif @endif >
                                            <label class="form-check-label" for="inlineCheckbox11">Trash</label>
                                        </div>                                        
                                    </td>
                                </tr>
                            </table>                                                                                                                                                                                
                        </div>
                        <span class="indoit-box-helper"></span>		
                        <div class="indoit-box-action">
                            <div class="col-sm-offset-2">
                                <div class="indoit-box-action-btn">                                    
                                    <a href="{{ url()->previous() }}" class="btn btn-soft-secondary">Back to List</a>                                    
                                </div>
                            </div>
                        </div><!-- /.box-footer -->	                                
                        <div class="indoit-box-required">
                            <hr>
                                <span class="required">*</span>
                                Field Required
                        </div><!-- /.box-footer -->
                    </div>
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
<script>

    function check(param){
        $('#groups-'+param)[0].checked = true;
    }

    function check2(param){
        if($("#groups_default-"+param).is(":checked")){
            $('#groups-'+param)[0].checked = true;
        } 
    }
</script>
@endsection
