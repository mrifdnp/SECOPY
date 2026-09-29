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
                <form id="dt_form" action="{{route('bottom-page.store')}}" class="form-horizontal" method="post" enctype="multipart/form-data">
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
<label class="col-sm-12 control-label">Parent</label>
<div class="col-sm-12"><div class="radio">
<input type="radio" id=" help" name="parent" value="help" />
                                                    <label for=" help"> help</label>
<input type="radio" id=" aboutus" name="parent" value="aboutus" />
                                                    <label for=" aboutus"> aboutus</label>
<input type="radio" id=" service" name="parent" value="service" />
                                                    <label for=" service"> service</label>
<input type="radio" id=" footer" name="parent" value="footer" />
                                                    <label for=" footer"> footer</label>
</div>
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Title</label>
<div class="col-sm-12"><input value="{{ old('title') }}" type="text" class="form-control" name="title" id="title" placeholder="" size="50" >
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Content</label>
<div class="col-sm-12"><textarea class="form-control" name="content" id="editor-content" rows="3" style="width: 50%" placeholder="" >
</textarea>
</div>
</div>

                            <br>                                                                                                                                                                                 
                        </div>
                        <span class="indoit-box-helper"></span>		
                        <div class="indoit-box-action">
                            <div class="col-sm-offset-2">
                                <div class="indoit-box-action-btn"> 
                                    <a href="{{route('bottom-page.index')}}" class="btn btn-soft-secondary">Back to List</a>                                                                       
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
                        ClassicEditor
                        .create(document.querySelector('#editor-content'), {
                            ckfinder: {
                                uploadUrl: "{{route('bottom_page.upload', ['_token' => csrf_token() ])}}",
                            }
                        })
                        .then(editor => {
                            console.log('Editor siap dipakai', editor);
                        })
                        .catch(error => {
                            console.error(error);
                        });
                     });
                 

    
</script>
@endsection
