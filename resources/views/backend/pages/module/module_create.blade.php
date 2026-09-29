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
                <form id="dt_form" action="{{route('module.store')}}" class="form-horizontal" method="post">
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
                            <div class="form-group">
                                <label for="" class="col-sm-12 control-label">Name*</label>
                                <div class="col-sm-8">
                                    <input value="{{ old('name') }}" type="text" class="form-control" name="name" id="name" placeholder="Module Name" width="100%" required>
                                </div>
                            </div>                
                            <br>
                            <div class="form-group">
                                <label for="" class="col-sm-12 control-label">Icon</label>
                                <div class="col-sm-8">
                                    <select class="form-control" style="width:100%" name="icon" id="choices-single-default"  placeholder="Icon" >
                                        <option value="{{ old('icon') }}">{{ old('icon') }}</option>
                                    </select>
                                   
                                </div>
                            </div>
                            <br>

                            

                          
                            <br>
                            <div class="form-group">
                                <label for="" class="col-sm-12 control-label">URL* (begin without /)</label>
                                <div class="col-sm-8">
                                    <input value="{{ old('url') }}" type="text" class="form-control" name="url" id="url" placeholder="example : author not /author">
                                </div>
                            </div>               
                            <br>             
                            <table class="table table-privileges">
                                <tr>
                                    <td style="width: 20%;">
                                        <div class="form-group">
                                            <label for="" class="control-label" style="font-weight: bold;">User Privilege</label>
                                        </div>    
                                    </td>    
                                    <td style="width:30%">
                                        <div class="form-group">
                                            <label for="" class="control-label" style="font-weight: bold;">Groups Privilege</label>
                                        </div>    
                                    </td>    
                                    <td style="width: 50%;">
                                        <div class="form-group">
                                            <label for="" class="control-label" style="font-weight: bold;">Description</label>
                                        </div>
                                    </td>    
                                </tr>     
                                @foreach($roles as $key => $row)
                                    <tr>
                                        <td style="width: 20%;">
                                            <div class="form-group">
                                                &nbsp;
                                            </div>    
                                        </td>    
                                        <td style="width:30%">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" name="groups[]" id="groups-{{$row->id}}" value="{{$row->id}}" @if(is_array(old('groups')) && in_array($row->id, old('groups'))) checked @endif/> 
                                                <label for="groups-{{$row->id}}"><b> {{$row->name}} </b></label>
                                            </div> 
                                            {{-- <div class="form-group">
                                                <input type="radio" name="default_role" onChange="check('{{$row->id}}')" id="groups_default-{{$row->id}}" value="{{$row->id}}" /> 
                                                <label for="groups_default-{{$row->id}}"><b> {{$row->name}} </b></label>
                                            </div>     --}}
                                        </td>    
                                        <td style="width: 50%;">
                                            <div class="form-group">
                                                <label for="">{{$row->description}}</label>
                                            </div>
                                        </td>    
                                    </tr>                                    
                                @endforeach                                
                            </table>  
                            <br>
                            <table class="table table-privileges">
                                <tr>
                                    <td style="width: 20%;">
                                        <div class="form-group">
                                            <label for="" class="control-label" style="font-weight: bold;">Support</label>
                                        </div>    
                                    </td>    
                                    <td style="width:80%">
                                        <div class="form-group">
                                            <div class="mt-12 mt-lg-9">
                                                <div class="form-check  form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="inlineCheckbox6" name="a" value="1">
                                                    <label class="form-check-label" for="inlineCheckbox6">Access</label>
                                                </div>
                                                <div class="form-check  form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="inlineCheckbox7" name="r" value="1">
                                                    <label class="form-check-label" for="inlineCheckbox7">Read</label>
                                                </div>
                                                <div class="form-check  form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="inlineCheckbox8" name="c" value="1">
                                                    <label class="form-check-label" for="inlineCheckbox8">Create</label>
                                                </div>
                                                <div class="form-check  form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="inlineCheckbox9" name="u" value="1">
                                                    <label class="form-check-label" for="inlineCheckbox9">Update</label>
                                                </div>
                                                <div class="form-check  form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="inlineCheckbox10" name="d" value="1">
                                                    <label class="form-check-label" for="inlineCheckbox10">Delete</label>
                                                </div>
                                                <div class="form-check  form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="inlineCheckbox11" name="t" value="1">
                                                    <label class="form-check-label" for="inlineCheckbox11">Trash</label>
                                                </div>
                                            </div>
                                        </div>    
                                    </td>                                            
                                </tr>                                     
                            </table>                            
                            <br>                                                                                                                                                                                 
                        </div>
                        <span class="indoit-box-helper"></span>		
                        <div class="indoit-box-action">
                            <div class="col-sm-offset-2">
                                <div class="indoit-box-action-btn">                                    
                                    <a href="{{route('module.index')}}" class="btn btn-soft-secondary">Back to List</a>
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
<script src="{{ URL::asset('/build/js/pages/remix-icons-listing.js')}}"></script>

@endsection
