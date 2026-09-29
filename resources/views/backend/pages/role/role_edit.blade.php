@extends('layouts.master')
@section('title') Edit {{ $titleHeader->name }} @endsection
@section('cssAdded')
    
@endsection
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
                <form id="dt_form" action="{{route('role.update',$role->id)}}" class="form-horizontal" method="post">
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
                                <label for="" class="col-sm-12 control-label">Name*</label>
                                <div class="col-sm-12">
                                    <input value="{{ old('name', $role->name) }}" type="text" class="form-control" name="name" id="name" placeholder="Name" width="100%" required>
                                </div>
                            </div>   
                            <br/> 
                            <div class="form-group">
                                <label for="" class="col-sm-12 control-label">Description*</label>
                                <div class="col-sm-12">
                                    <input value="{{ old('description', $role->description) }}" type="text" class="form-control" name="description" id="description" placeholder="Description" width="100%" required>
                                </div>
                            </div>    
                            <br/>                           
                            {{-- role --}}
                            <div class="form-group">
                                <label for="" class="col-sm-2 control-label">Access/Role*</label>
                                <div class="col-sm-12">                                 
                                    <table class="table table-privileges col-sm-12">
                                        <tbody>
                                            <tr>
                                                <td class="col-sm-6"><b> Module </b></td>
                                                <td class="col-sm-1">
                                                    Access
                                                </td>
                                                <td class="col-sm-1">
                                                    Read
                                                </td>
                                                <td class="col-sm-1">
                                                    Create
                                                </td>
                                                <td class="col-sm-1">
                                                    Update
                                                </td>
                                                <td class="col-sm-1">
                                                    Delete
                                                </td>
                                                <td class="col-sm-1">
                                                    Trash
                                                </td>
                                            </tr>
                                    @php 
                                        $i=0; 
                                        $index=0;                                        
                                    @endphp
                                    @foreach ($modules as $row)
                                    @php
                                        $wrongFormat = str_replace("\\","",$row->support);                                            
                                        $supports = json_decode($wrongFormat);                                              
                                    @endphp
                                    <tr>
                                        <td class="col-sm-6">
                                            <div class="checkbox checkbox-primary checkbox-circle"> 
                                                <input id_value="m-{{$row->id}}" type="hidden" value="@php if(!empty($role_privileges[$row->id])){ echo '1';}else{echo '0';}@endphp">
                                                <input class="form-check-input" type="checkbox" name="roles[]" id="roles-{{$row->id}}" value="{{$row->id}}" no="{{$row->id}}" parent="" @php if(!empty($role_privileges[$row->id])){ echo 'checked';}@endphp/>
                                                <label for="roles-{{$row->id}}"><b>{{$row->name}}</b></label>
                                              </div> 
                                        </td>
                                        <td class="col-sm-1">
                                            @if ($supports->a == '1')
                                                <div class="checkbox checkbox-primary checkbox-circle checkbox-only">  
                                                    <input id_value="a-{{$row->id}}" type="hidden" value="@php if(!empty($role_privileges['access_'.$row->id])){ echo '1';}else{echo '0';}@endphp" @php if(!empty($role_privileges[$row->id])){ echo 'name="access[]"';}@endphp>
                                                    <input class="form-check-input" type="checkbox" id="a-{{$row->id}}" value="access[]" no="{{$row->id}}" parent="" @php if(!empty($role_privileges['access_'.$row->id])){ echo 'checked';}@endphp/>
                                                    <label for="a-{{$row->id}}"></label>
                                                </div>
                                            @else
                                                <div class="checkbox checkbox-primary checkbox-circle checkbox-only" style="display: none;">  
                                                    <input id_value="a-{{$row->id}}" type="hidden" value="@php if(!empty($role_privileges['access_'.$row->id])){ echo '1';}else{echo '0';}@endphp" @php if(!empty($role_privileges[$row->id])){ echo 'name="access[]"';}@endphp>
                                                    <input class="form-check-input" type="checkbox" id="a-{{$row->id}}" value="access[]" no="{{$row->id}}" parent="" @php if(!empty($role_privileges['access_'.$row->id])){ echo 'checked';}@endphp/>
                                                    <label for="a-{{$row->id}}"></label>
                                                </div>
                                            @endif                                                                                         
                                        </td>
                                        
                                        <td class="col-sm-1">
                                            @if ($supports->r == '1')
                                                <div class="checkbox checkbox-primary checkbox-circle checkbox-only">  
                                                    <input id_value="r-{{$row->id}}" type="hidden" value="@php if(!empty($role_privileges['read_'.$row->id])){ echo '1';}else{echo '0';}@endphp" @php if(!empty($role_privileges[$row->id])){ echo 'name="read[]"';}@endphp>
                                                    <input class="form-check-input" type="checkbox"  id="r-{{$row->id}}" value="read[]" no="{{$row->id}}" parent="" @php if(!empty($role_privileges['read_'.$row->id])){ echo 'checked';}@endphp/>
                                                    <label for="r-{{$row->id}}"></label>
                                                </div>
                                            @else
                                                <div class="checkbox checkbox-primary checkbox-circle checkbox-only" style="display: none;">  
                                                    <input id_value="r-{{$row->id}}" type="hidden" value="@php if(!empty($role_privileges['read_'.$row->id])){ echo '1';}else{echo '0';}@endphp" @php if(!empty($role_privileges[$row->id])){ echo 'name="read[]"';}@endphp>
                                                    <input class="form-check-input" type="checkbox"  id="r-{{$row->id}}" value="read[]" no="{{$row->id}}" parent="" @php if(!empty($role_privileges['read_'.$row->id])){ echo 'checked';}@endphp/>
                                                    <label for="r-{{$row->id}}"></label>
                                                </div>
                                            @endif                                            
                                        </td>
                                        
                                        <td class="col-sm-1">
                                            @if ($supports->c == '1')
                                                <div class="checkbox checkbox-primary checkbox-circle checkbox-only">  
                                                    <input id_value="c-{{$row->id}}" type="hidden" value="@php if(!empty($role_privileges['create_'.$row->id])){ echo '1';}else{echo '0';}@endphp" @php if(!empty($role_privileges[$row->id])){ echo 'name="create[]"';}@endphp>
                                                    <input class="form-check-input" type="checkbox" id="c-{{$row->id}}" value="create[]" no="{{$row->id}}" parent="" @php if(!empty($role_privileges['create_'.$row->id])){ echo 'checked';}@endphp/>
                                                    <label for="c-{{$row->id}}"></label>
                                                </div>
                                            @else
                                                <div class="checkbox checkbox-primary checkbox-circle checkbox-only" style="display: none;">  
                                                    <input id_value="c-{{$row->id}}" type="hidden" value="@php if(!empty($role_privileges['create_'.$row->id])){ echo '1';}else{echo '0';}@endphp" @php if(!empty($role_privileges[$row->id])){ echo 'name="create[]"';}@endphp>
                                                    <input class="form-check-input" type="checkbox" id="c-{{$row->id}}" value="create[]" no="{{$row->id}}" parent="" @php if(!empty($role_privileges['create_'.$row->id])){ echo 'checked';}@endphp/>
                                                    <label for="c-{{$row->id}}"></label>
                                                </div>
                                            @endif                                            
                                        </td>
                                        
                                        <td class="col-sm-1"> 
                                            @if ($supports->u == '1')
                                                <div class="checkbox checkbox-primary checkbox-circle checkbox-only">  
                                                    <input id_value="u-{{$row->id}}" type="hidden" value="@php if(!empty($role_privileges['update_'.$row->id])){ echo '1';}else{echo '0';}@endphp" @php if(!empty($role_privileges[$row->id])){ echo 'name="update[]"';}@endphp>
                                                    <input class="form-check-input" type="checkbox" id="u-{{$row->id}}" value="update[]" no="{{$row->id}}" parent="" @php if(!empty($role_privileges['update_'.$row->id])){ echo 'checked';}@endphp/>
                                                    <label for="u-{{$row->id}}"></label>
                                                </div>
                                            @else
                                                <div class="checkbox checkbox-primary checkbox-circle checkbox-only" style="display: none;">  
                                                    <input id_value="u-{{$row->id}}" type="hidden" value="@php if(!empty($role_privileges['update_'.$row->id])){ echo '1';}else{echo '0';}@endphp" @php if(!empty($role_privileges[$row->id])){ echo 'name="update[]"';}@endphp>
                                                    <input class="form-check-input" type="checkbox" id="u-{{$row->id}}" value="update[]" no="{{$row->id}}" parent="" @php if(!empty($role_privileges['update_'.$row->id])){ echo 'checked';}@endphp/>
                                                    <label for="u-{{$row->id}}"></label>
                                                </div>
                                            @endif                                                                                       
                                        </td>
                                        <td class="col-sm-1">
                                            @if ($supports->d == '1')
                                                <div class="checkbox checkbox-primary checkbox-circle checkbox-only">  
                                                    <input id_value="d-{{$row->id}}" type="hidden" value="@php if(!empty($role_privileges['delete_'.$row->id])){ echo '1';}else{echo '0';}@endphp" @php if(!empty($role_privileges[$row->id])){ echo 'name="delete[]"';}@endphp>
                                                    <input class="form-check-input" type="checkbox" id="d-{{$row->id}}" value="delete[]" no="{{$row->id}}" parent="" @php if(!empty($role_privileges['delete_'.$row->id])){ echo 'checked';}@endphp/>
                                                    <label for="d-{{$row->id}}"></label>
                                                </div>
                                            @else
                                                <div class="checkbox checkbox-primary checkbox-circle checkbox-only" style="display: none;">  
                                                    <input id_value="d-{{$row->id}}" type="hidden" value="@php if(!empty($role_privileges['delete_'.$row->id])){ echo '1';}else{echo '0';}@endphp" @php if(!empty($role_privileges[$row->id])){ echo 'name="delete[]"';}@endphp>
                                                    <input class="form-check-input" type="checkbox" id="d-{{$row->id}}" value="delete[]" no="{{$row->id}}" parent="" @php if(!empty($role_privileges['delete_'.$row->id])){ echo 'checked';}@endphp/>
                                                    <label for="d-{{$row->id}}"></label>
                                                </div>
                                            @endif                                                                                       
                                        </td>
                                        <td class="col-sm-1">
                                            @if ($supports->t == '1')
                                                <div class="checkbox checkbox-primary checkbox-circle checkbox-only">  
                                                    <input id_value="t-{{$row->id}}" type="hidden" value="@php if(!empty($role_privileges['trash_'.$row->id])){ echo '1';}else{echo '0';}@endphp" @php if(!empty($role_privileges[$row->id])){ echo 'name="trash[]"';}@endphp>
                                                    <input class="form-check-input" type="checkbox" id="t-{{$row->id}}" value="trash[]" no="{{$row->id}}" parent="" @php if(!empty($role_privileges['trash_'.$row->id])){ echo 'checked';}@endphp/>
                                                    <label for="t-{{$row->id}}"></label>
                                                </div>
                                            @else
                                                <div class="checkbox checkbox-primary checkbox-circle checkbox-only" style="display: none;">  
                                                    <input id_value="t-{{$row->id}}" type="hidden" value="@php if(!empty($role_privileges['trash_'.$row->id])){ echo '1';}else{echo '0';}@endphp" @php if(!empty($role_privileges[$row->id])){ echo 'name="trash[]"';}@endphp>
                                                    <input class="form-check-input" type="checkbox" id="t-{{$row->id}}" value="trash[]" no="{{$row->id}}" parent="" @php if(!empty($role_privileges['trash_'.$row->id])){ echo 'checked';}@endphp/>
                                                    <label for="t-{{$row->id}}"></label>
                                                </div>
                                            @endif                                            
                                        </td>                                        
                                    </tr>
                                    @endforeach
                                    @php
                                        if (is_array(old('roles')) && in_array($row->id, old('roles')))
                                        {
                                           $index++; 
                                        }
                                        $i++;
                                    @endphp
                                    
                                </tbody>
        
                            </table>
                                
                        </div>
                    </div>                            {{-- end role --}}
                </div>
                <span class="indoit-box-helper"></span>		
                <div class="indoit-box-action">
                    <div class="col-sm-offset-2">
                        <div class="indoit-box-action-btn">                                    
                            <a href="{{route('role.index')}}" class="btn btn-soft-secondary">Back to List</a>
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

    $(document).ready(function () {        
        var flag=1;
	
        $('.col-sm-6 .checkbox input:checkbox').change(function () {
                flag = 0;
                var input = $(this);
                var recordId = input.attr("no");
                var parentId = input.attr("parent");
                
                if ($("#roles-"+recordId).is(":checked")) {                    
                    $("#a-"+recordId).prop( "checked", true );
                    $("#c-"+recordId).prop( "checked", true );
                    $("#r-"+recordId).prop( "checked", true );
                    $("#u-"+recordId).prop( "checked", true );
                    $("#d-"+recordId).prop( "checked", true );  
                    $("#t-"+recordId).prop( "checked", true );  
                    
                    $("#roles-"+parentId).prop( "checked", true ); 
                    $("#roles-"+parentId).trigger("change");
                }else{                    
                    $("#a-"+recordId).prop( "checked", false );
                    $("#c-"+recordId).prop( "checked", false );
                    $("#r-"+recordId).prop( "checked", false );
                    $("#u-"+recordId).prop( "checked", false );
                    $("#d-"+recordId).prop( "checked", false ); 
                    $("#t-"+recordId).prop( "checked", false );                       
                    $("input:checkbox[parent='"+recordId+"']").prop( "checked", false );
                    $("input:checkbox[parent='"+recordId+"']").trigger("change");
                }
                $("#a-"+recordId).trigger("change");
                $("#c-"+recordId).trigger("change");
                $("#r-"+recordId).trigger("change");
                $("#u-"+recordId).trigger("change");
                $("#d-"+recordId).trigger("change");
                $("#t-"+recordId).trigger("change");                    
            });
        
            $('.col-sm-1 .checkbox input:checkbox').click(function () {
                    //untuk membatasi looping triger
                    flag=1;  
            });
        
            $('.col-sm-1 .checkbox input:checkbox').change(function () {
                var input = $(this);
                var recordId = input.attr("no");
                var id_value = input.attr("id");
                var parentId = input.attr("parent");
                
                
                if ($("#a-"+recordId).is(":checked") || $("#c-"+recordId).is(":checked") || $("#r-"+recordId).is(":checked") || $("#u-"+recordId).is(":checked") || $("#d-"+recordId).is(":checked") || $("#t-"+recordId).is(":checked")) {
                    $("#roles-"+recordId).prop( "checked", true );
                    
                    //Otomatis mengaktifkan akses read jika user checklist create update delete
                    if($("#a-"+recordId).is(":checked") || $("#c-"+recordId).is(":checked") || $("#u-"+recordId).is(":checked") || $("#d-"+recordId).is(":checked") || $("#t-"+recordId).is(":checked") ){
                        $("#r-"+recordId).prop( "checked", true );
                        $("input[id_value='r-"+recordId+"']").val("1");
                    } 
                }
                    
                if (!$("#a-"+recordId).is(":checked") && !$("#c-"+recordId).is(":checked") && !$("#r-"+recordId).is(":checked") && !$("#u-"+recordId).is(":checked") && !$("#d-"+recordId).is(":checked")) {
                    $("#roles-"+recordId).prop( "checked", false );
                    
                    //Menghapus name Hidden Value jika tidak ada satupun privilage yang dipilih
                    $("input[id_value='m-"+recordId+"']").attr('name', "");
                    $("input[id_value='a-"+recordId+"']").attr('name', "");
                    $("input[id_value='r-"+recordId+"']").attr('name', "");
                    $("input[id_value='c-"+recordId+"']").attr('name', "");
                    $("input[id_value='u-"+recordId+"']").attr('name', "");
                    $("input[id_value='d-"+recordId+"']").attr('name', "");
                    $("input[id_value='t-"+recordId+"']").attr('name', "");
                }
                
                //Mengisi name Hidden Value 
                if ($("input[id='"+id_value+"']").is(":checked")) {
                    $("input[id_value='"+id_value+"']").val("1");
                    $("input[id_value='m-"+recordId+"']").attr('name', "is_module[]");
                    $("input[id_value='a-"+recordId+"']").attr('name', "access[]");
                    $("input[id_value='r-"+recordId+"']").attr('name', "read[]");
                    $("input[id_value='c-"+recordId+"']").attr('name', "create[]");
                    $("input[id_value='u-"+recordId+"']").attr('name', "update[]");
                    $("input[id_value='d-"+recordId+"']").attr('name', "delete[]");
                    $("input[id_value='t-"+recordId+"']").attr('name', "trash[]");
                }
                else{
                    $("input[id_value='"+id_value+"']").val("0");
                }
                
                if(flag==1){
                    $("#roles-"+parentId).prop( "checked", true ); 
                    $("#roles-"+parentId).trigger("change");
                }                
            });
        });    
</script>
@endsection
