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
                <form id="dt_form" action="{{route('product.store')}}" class="form-horizontal" method="post" enctype="multipart/form-data">
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
<label class="col-sm-12 control-label">Nama Produk</label>
<div class="col-sm-12"><input value="{{ old('title') }}" type="text" class="form-control" name="title" id="title" placeholder="Nama Produk" size="50" >
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Main Category</label>
<div class="col-sm-12"><select class="form-control js-example-basic-single select2" name="main_category_id" id="main_category_id" required>
                    <option value="">Pilih Main Category</option>
                    @foreach ($main_categories as $main_category)
                        <option value="{{ $main_category->id }}" >{{ $main_category->title }}</option>
                    @endforeach</select>
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Kategori</label>
<div class="col-sm-12"><select class="form-control js-example-basic-single select2" name="category_id" id="category_id" required>
                    <option value="">Pilih Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" data-main="{{ $category->main_categories_id }}">{{ $category->title }}</option>
                    @endforeach</select>
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Material / Consignment (Opsional)</label>
<div class="col-sm-12"><select class="form-control js-example-basic-single select2" name="material_id" id="material_id">
                    <option value="">Pilih Material</option>
                    @foreach ($materials as $material)
                        <option value="{{ $material->id }}">{{ $material->title }}</option>
                    @endforeach</select>
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Top Pick</label>
<div class="col-sm-12"><div class="radio">
<input type="radio" id="Tidak" name="top_pick" value="0" />
                                                    <label for="Tidak">Tidak</label>
<input type="radio" id="Ya" name="top_pick" value="1" />
                                                    <label for="Ya">Ya</label>
</div>
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Content Path</label>
<div class="col-sm-12"><input value="{{ old('content_paths') }}" type="text" class="form-control" name="content_paths" id="content_paths" placeholder="Content Path" size="50" >
</div>
</div>

                            <br>                                                                                                                                                                                 
                        </div>
                        <span class="indoit-box-helper"></span>		
                        <div class="indoit-box-action">
                            <div class="col-sm-offset-2">
                                <div class="indoit-box-action-btn"> 
                                    <a href="{{route('product.index')}}" class="btn btn-soft-secondary">Back to List</a>                                                                       
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
    $(document).ready(function() {
        var $categorySelect = $('#category_id');
        var $categoryOptions = $categorySelect.find('option').clone();

        $('#main_category_id').on('change', function() {
            var selectedMainId = $(this).val();
            $categorySelect.empty();
            
            var $filteredOptions = $categoryOptions.filter(function() {
                return $(this).val() === "" || $(this).data('main') == selectedMainId;
            });
            
            $categorySelect.append($filteredOptions);
            $categorySelect.val('').trigger('change');
        });
    });
</script>
@endsection
