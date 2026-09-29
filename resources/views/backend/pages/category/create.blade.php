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
                <form id="dt_form" action="{{route('category.store')}}" class="form-horizontal" method="post" enctype="multipart/form-data">
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
<label class="col-sm-12 control-label">Title</label>
<div class="col-sm-12"><input value="{{ old('title') }}" type="text" class="form-control" name="title" id="title" placeholder="" size="50" >
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Main Category</label>
<div class="col-sm-12"><select class="form-control js-example-basic-single select2" name="main_categories_id" required>
                    @foreach ($main_categories as $main_category) {
                        <option value="{{ $main_category->id }}" >{{ $main_category->title }}</option>
                    @endforeach</select>
</div>
</div>

<div class="form-group mb-3">
<label class="col-sm-12 control-label">Meta Title (SEO)</label>
<div class="col-sm-12"><input value="{{ old('meta_title') }}" type="text" class="form-control" name="meta_title" id="meta_title" placeholder="Opsional">
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Meta Description (SEO)</label>
<div class="col-sm-12"><textarea class="form-control" name="meta_description" id="meta_description" rows="3" placeholder="Opsional">{{ old('meta_description') }}</textarea>
</div>
</div>
                            <br>                                                                                                                                                                                 
                        </div>
                        <span class="indoit-box-helper"></span>		
                        <div class="indoit-box-action">
                            <div class="col-sm-offset-2">
                                <div class="indoit-box-action-btn"> 
                                    <a href="{{route('category.index')}}" class="btn btn-soft-secondary">Back to List</a>                                                                       
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
    
    
</script>
@endsection
