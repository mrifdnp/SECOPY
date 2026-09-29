@extends('layouts.master')
@section('title') Detail Change Log @endsection
@section('cssAdded')
    <style>       

    </style>
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Systems @endslot
@slot('title') Detail Change Log @endslot
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
                                    <td><b>Type</b></td>
                                    <td>
                                        @if ($data->type == 'Minor Update')
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 avatar-xs">
                                                    <div class="avatar-title bg-light text-muted rounded-circle">
                                                        <i class="ri-user-3-fill"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="fs-14 mb-1">
                                                        {{ $data->type }}
                                                    </h6>              
                                                    <small class="text-muted">
                                                        <span class="text-secondary">{{ $data->version }}</span> pada {{ \Carbon\Carbon::parse($data->created_date)->format('d F Y H:i:s') }}
                                                    </small>                      
                                                </div>
                                            </div>
                                        @elseif($data->type == 'Mayor Update')
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 avatar-xs">
                                                    <div class="avatar-title bg-primary text-light rounded-circle">
                                                        <i class="ri-user-3-fill"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="fs-14 mb-1">
                                                        {{ $data->type }}
                                                    </h6>                     
                                                    <small class="text-muted">
                                                        <span class="text-secondary">{{ $data->version }}</span> pada {{ \Carbon\Carbon::parse($data->created_date)->format('d F Y H:i:s') }}
                                                    </small>                                     
                                                </div>
                                            </div>
                                        @else
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 avatar-xs">
                                                    <div class="avatar-title bg-success text-light rounded-circle">
                                                        <i class="ri-user-3-fill"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="fs-14 mb-1">
                                                        {{ $data->type }}
                                                    </h6>              
                                                    <small class="text-muted">
                                                        <span class="text-secondary">{{ $data->version }}</span> pada {{ \Carbon\Carbon::parse($data->created_date)->format('d F Y H:i:s') }}
                                                    </small>                                            
                                                </div>
                                            </div>
                                        @endif                                        
                                    </td>
                                </tr>                                
                                <tr>
                                    <td><b>Description</b></td>
                                    <td>{!! $data->description !!}</td>
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
<script src="{{ URL::asset('/build/js/pages/remix-icons-listing.js')}}"></script>
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
