@extends('layouts.master')
@section('title') Add Change Log @endsection
@section('cssAdded')
    <script src="{{ URL::asset('build/libs/velzon-dependencies/ckeditor5-classic-40.2.0/ckeditor.js') }}"></script>
@endsection
@section('cssAdded')

<style>
    .input-group-append {
        cursor: pointer;
    }
    body{
        margin:80px !important;
    }
</style>
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Systems @endslot
@slot('title') Add Change Log @endslot
@endcomponent


<div class="row">
    <div class="col-lg-12">                
        <div class="card">            
            <div class="card-body">
                @include('backend.includes.flash')
                <form id="dt_form" action="{{route('change-log.store')}}" class="form-horizontal" method="post">
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
                                <label for="" class="col-sm-12 control-label">Type of Change Log*</label>
                                <div class="col-sm-8">
                                    <select name="type" id="type" class="form-control select2" required>
                                        <option value="">-- Choose Type --</option>
                                        <option value="Minor Update">Minor Update</option>
                                        <option value="Mayor Update">Mayor Update</option>
                                        <option value="New Release">New Release</option>
                                    </select>                                
                                </div>
                            </div>                                                    
                            <br>                            
                            <div class="form-group">
                                <label for="" class="col-sm-12 control-label">Version*</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" name="version" id="version" placeholder="version" required>                                    
                                </div>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="datetimepicker" class="col-sm-12 control-label">Date</label>
                                <div class="col-sm-8">
                                    <input type="text" value="{{ old('created_date') ? old('created_date') : $dateNow }}" name="created_date" class="form-control flatpickr-input active" data-provider="flatpickr" data-date-format="Y-m-d" data-enable-time="" readonly="readonly">
                                </div>                                
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="" class="col-sm-12 control-label">Description</label>
                                <div class="col-sm-8">
                                    {{-- <textarea name="description" id="description" cols="30" rows="10" placeholder="description" class="form-control"></textarea> --}}
                                    <textarea name="description" id="description" cols="30" rows="10" placeholder="Description"></textarea>
                                </div>
                            </div>
                            <br>                                                                                                                                                                               
                        </div>
                        <span class="indoit-box-helper"></span>		
                        <div class="indoit-box-action">
                            <div class="col-sm-offset-2">
                                <div class="indoit-box-action-btn">                                    
                                    <a href="{{route('change-log.index')}}" class="btn btn-soft-secondary">Back to List</a>
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
<script src="{{ URL::asset('/build/js/pages/remix-icons-listing.js')}}"></script>


<script>
	ClassicEditor
    .create( document.querySelector( '#description' ) )
    .then( editor => {
            console.log( editor );
    } )
    .catch( error => {
            console.error( error );
    } );    
</script>
<script>
    $(document).ready(function () {
        $('#datetimePicker').datetimepicker();
    });
</script>
@endsection
