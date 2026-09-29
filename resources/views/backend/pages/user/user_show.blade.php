@extends('layouts.master')
@section('title') Detail {{ $titleHeader->name }} @endsection
@section('cssAdded')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Systems @endslot
@slot('title') Details {{ $titleHeader->name }} @endslot
@endcomponent


<div class="row">
    <div class="col-lg-12">                
        <div class="card">            
            <div class="card-body">
                <div class="box">
                    <div class="box-body indoit-form">
                        <table class="table table-bordered">
                            <tr>
                                <td style="width: 200px"><b>Name</b></td>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <td><b>Email</b></td>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <td><b>Privilage</b></td>
                                <td>
                                    <table style="width: 100%;">
                                        <tbody>                                   
                                            @foreach($roles as $row)
                                            <tr>
                                                <td class="col-sm-4">
                                                    <div class="checkbox checkbox-primary checkbox-circle">
                                                        <input type="checkbox" disabled name="groups[]" id="groups-{{$row->id}}" value="{{$row->id}}" @if(!is_array(old('groups')) && in_array($row->id, $user_privileges)) checked @endif @if(is_array(old('groups')) && in_array($row->id, old('groups'))) checked @endif /> 
                                                        <label for="groups-{{$row->id}}">{{$row->name}} - {{$row->description}}</label>
                                                    </div> 
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>
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
