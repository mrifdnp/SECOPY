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
                <form id="dt_form" action="{{route('user.store')}}" class="form-horizontal" method="post">
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
                            <div class="mb-12">
                                <div class="form-group">
                                    <label for="" class="mb-12 control-label">Name*</label>
                                    <div class="mb-12">
                                        <input value="{{ old('user_name') }}" type="text" class="form-control" name="user_name" id="user_name" width="50%">
                                    </div>
                                </div>   
                                <br>
                                <div class="form-group">
                                    <label for="" class="mb-12 control-label">Email*</label>
                                    <div class="mb-12">
                                        <input value="{{ old('user_email') }}" type="email" class="form-control" name="user_email" id="user_email" placeholder="Email" width="50%">
                                    </div>
                                </div> 
                                <br>
                                <div class="form-group">
                                    <label for="" class="mb-12 control-label">Password*</label>
                                    <div class="mb-12">
                                        <input value="{{ old('user_password') }}" type="password" class="form-control" name="user_password" id="user_password" placeholder="Password" width="50%">
                                    </div>
                                </div>
                                <br>
                                <div class="form-group">
                                    <label for="" class="mb-12 control-label">Confirm Password*</label>
                                    <div class="mb-12">
                                        <input value="{{ old('user_confirm_password') }}" type="password" class="form-control" name="user_confirm_password" id="user_confirm_password" placeholder="Confirm Password" width="50%">
                                    </div>
                                </div>                                
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="choices-text-remove-button" class="form-label">Store*</label>                                
                                <div class="mb-12">
                                    <select  class="form-control" id="choices-multiple-remove-button" data-choices data-choices-removeItem name="store_id[]" multiple data-placeholder="Akses Store">
                                        @foreach ($stores as $s => $str)
                                            <option value="{{ $s }}">{{ $str }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <br>                            
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-2">
                                        <label for="" class="control-label" style="">Default Privilege</label>
                                    </div>
                                    <div class="col-sm-10 mb-10">
                                        <table class="table mb-12">
                                            <tr>                                           
                                                <td class="mb-4">                                                
                                                    <label for="" class="control-label" style="font-weight: bold;">Groups Privilege</label>                                                
                                                </td>    
                                                <td class="mb-8">
                                                    <label for="" class="control-label" style="font-weight: bold;">Description</label>                                                
                                                </td>    
                                            </tr>     
                                            @foreach($roles as $key => $row)
                                                <tr>                                                
                                                    <td class="mb-4">
                                                        <div class="form-check mb-12">
                                                            <input class="form-check-input" type="radio" name="default_role" onChange="check('{{$row->id}}')" id="groups_default-{{$row->id}}" value="{{$row->id}}">
                                                            <label class="form-check-label" for="groups_default-{{$row->id}}">
                                                                <b> {{$row->name}} </b>
                                                            </label>
                                                        </div>
                                                    </td>    
                                                    <td class="mb-8">
                                                        <label for="">{{$row->description}}</label>                                                    
                                                    </td>    
                                                </tr>                                    
                                            @endforeach                                        
                                        </table>         
                                    </div>
                                </div>                                    
                            </div>                                                                                                                                                                                                                                                            
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-2">
                                        <label for="" class="control-label" style="">Add On Privilege</label>
                                    </div>                                
                                    <div class="col-sm-10 mb-10">
                                        <table class="table mb-12">
                                            <tr>                                           
                                                <td class="mb-4">                                                
                                                    <label for="" class="control-label" style="font-weight: bold;">Groups Privilege</label>                                                
                                                </td>    
                                                <td class="mb-6">
                                                    <label for="" class="control-label" style="font-weight: bold;">Description</label>                                                
                                                </td>    
                                            </tr>                                             
                                            @foreach($roles as $key => $row)
                                                <tr>
                                                    <td class="mb-4">                                                    
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="checkbox" name="groups[]" id="groups-{{$row->id}}" onChange="check2('{{$row->id}}')" value="{{$row->id}}" @if(is_array(old('groups')) && in_array($row->id, old('groups'))) checked @endif/> 
                                                            <label class="form-check-label" for="groups-{{$row->id}}"><b> {{$row->name}} </b></label>
                                                        </div>
                                                    </td>    
                                                    <td class="mb-8">                                                    
                                                        <label for="">{{$row->description}}</label>
                                                    </td>    
                                                </tr>                                    
                                            @endforeach                                        
                                        </table>         
                                    </div>
                                </div>                                    
                            </div>                                                                                                                                                                                                                                                            
                        </div>
                        <span class="indoit-box-helper"></span>		
                        <div class="indoit-box-action">
                            <div class="col-sm-offset-2">
                                <div class="indoit-box-action-btn">                                    
                                    <a href="{{ route('user.index') }}" class="btn btn-soft-secondary">Back to List</a>
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
<script>

    function check(param){
        $('#groups-'+param)[0].checked = true;
    }

    function check2(param){
        if($("#groups_default-"+param).is(":checked")){
            $('#groups-'+param)[0].checked = true;
        } 
    }

    $( document ).ready(function() {
        console.log( "ready!" );
        
        const element = document.querySelector('#choices-single-default');
        const choices = new Choices(element, {
            choices: iconModule,
        });
    });

</script>
@endsection
