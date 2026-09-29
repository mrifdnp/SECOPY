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
                <form id="dt_form" action="{{route('main-category.update', $main_category)}}" class="form-horizontal" method="post">
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
                            <div class="form-group mb-3">
<label class="col-sm-12 control-label">Title</label>
<div class="col-sm-12"><input value="{{ old('title', $main_category->title) }}" type="text" class="form-control" name="title" id="title" placeholder="" size="50" >
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Sub title</label>
<div class="col-sm-12"><input value="{{ old('sub_title', $main_category->sub_title) }}" type="text" class="form-control" name="sub_title" id="sub_title" placeholder="" size="50" >
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Link txt</label>
<div class="col-sm-12"><input value="{{ old('link_txt', $main_category->link_txt) }}" type="text" class="form-control" name="link_txt" id="link_txt" placeholder="" size="50" >
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Link url</label>
<div class="col-sm-12"><input value="{{ old('link_url', $main_category->link_url) }}" type="text" class="form-control" name="link_url" id="link_url" placeholder="" size="50" >
</div>
</div>
<div class="form-group row">
                        <label for="" class="col-sm-12 control-label"></label>
                        <div class="col-lg-6 col-md-8 col-sm-10">
                            @if (is_null($main_category->picture))
                                <img src="{{ asset('img/default-image.png') }}" alt="" class="img-detail" style="width: 200px;height: auto;padding: 10px;vertical-align: middle;border-style: none;" id="imgEntity-picture">
                            @else
                                <img src="{{ asset('img/main_category/'.$main_category->picture) }}" alt="" class="img-detail" style="width: 200px;height: auto;padding: 10px;vertical-align: middle;border-style: none;" id="imgEntity-picture">
                            @endif
                        </div>
                    </div>

<div class="form-group mb-3">
<label class="col-sm-12 control-label">Picture</label>
<div class="col-sm-12"><label class="label" for="inputImage">Choose file</label>
<input type="file" class="form-control @error('picture') is-invalid @enderror" name="picture" id="inputImage-picture" >
</div>
</div>

<div class="form-group mb-3">
<label class="col-sm-12 control-label">Meta Title (SEO)</label>
<div class="col-sm-12"><input value="{{ old('meta_title', $main_category->meta_title) }}" type="text" class="form-control" name="meta_title" id="meta_title" placeholder="Opsional">
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Meta Description (SEO)</label>
<div class="col-sm-12"><textarea class="form-control" name="meta_description" id="meta_description" rows="3" placeholder="Opsional">{{ old('meta_description', $main_category->meta_description) }}</textarea>
</div>
</div>
                            <br>                                                                                                                                                                                 
                        </div>
                        <span class="indoit-box-helper"></span>		
                        <div class="indoit-box-action">
                            <div class="col-sm-offset-2">
                                <div class="indoit-box-action-btn"> 
                                    <a href="{{route('main-category.index')}}" class="btn btn-soft-secondary">Back to List</a>                                                                       
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
    function getURL_picture(input) {
                        if (input.files && input.files[0]) {
                            var reader = new FileReader();
                            reader.onload = function(e) {
                                $('#imgEntity-picture').attr('src', e.target.result);
                            }
                            reader.readAsDataURL(input.files[0]);
                        }
                    }

                    $('#inputImage-picture').on('change',function(e){
                        getURL_picture(this);
                        var fileName = e.target.files[0].name;
                        $(this).next('.custom-file-label').html(fileName);
                    });

    
</script>
@endsection
