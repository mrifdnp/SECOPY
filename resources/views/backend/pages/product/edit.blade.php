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
                <form id="dt_form" action="{{route('product.update', $product)}}" class="form-horizontal" method="post">
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
<label class="col-sm-12 control-label">Nama Produk</label>
<div class="col-sm-12"><input value="{{ old('title', $product->title) }}" type="text" class="form-control" name="title" id="title" placeholder="Nama Produk" size="50" >
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Kategori</label>
<div class="col-sm-12"><select class="form-control js-example-basic-single select2" name="category_id" required>
                    @foreach ($categories as $category) {
                        <option value="{{ $category->id }}" @if ($product->category_id == $category->id)
                        selected=""
                        @endif>{{ $category->id }}</option>
                    @endforeach<div class="form-group mb-3">
<label class="col-sm-12 control-label">Top Pick</label>
<div class="col-sm-12"><div class="radio">
<input type="radio" id="Tidak" name="top_pick" value="0"  @if ("0" == $product->top_pick)
                        checked="checked"
                    @endif />
                    <label for="Tidak">Tidak</label>
<input type="radio" id="Ya" name="top_pick" value="1"  @if ("1" == $product->top_pick)
                        checked="checked"
                    @endif />
                    <label for="Ya">Ya</label>
</div>
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Content Path</label>
<div class="col-sm-12"><input value="{{ old('content_paths', $product->content_paths) }}" type="text" class="form-control" name="content_paths" id="content_paths" placeholder="Content Path" size="50" >
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
    
    
</script>
@endsection
