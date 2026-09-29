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
                <form id="dt_form" action="{{route('top-page.update', $top_page)}}" class="form-horizontal" method="post">
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
<div class="col-sm-12"><input value="{{ old('title', $top_page->title) }}" type="text" class="form-control" name="title" id="title" placeholder="" size="50" >
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Icon</label>
<div class="col-sm-12">
    <select class="form-control" style="width:100%" name="icon" id="choices-single-default"  placeholder="Icon" >
        <option value="{{ old('icon') ? old('icon') : $top_page->icon }}" selected><i class="{{ strtolower($top_page->icon) }}"></i> {{ strtolower($top_page->icon) }}</option>                                        
    </select>
</div>
</div>
<div class="form-group mb-3">
<label class="col-sm-12 control-label">Content</label>
<div class="col-sm-12"><textarea class="form-control" name="content" id="editor-content" rows="3" style="width: 50%" placeholder="" >{!! $top_page->content!!}
</textarea>
</div>
</div>

                            <br>                                                                                                                                                                                 
                        </div>
                        <span class="indoit-box-helper"></span>		
                        <div class="indoit-box-action">
                            <div class="col-sm-offset-2">
                                <div class="indoit-box-action-btn"> 
                                    <a href="{{route('top-page.index')}}" class="btn btn-soft-secondary">Back to List</a>                                                                       
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
<script src="{{ URL::asset('/build/js/pages/remix-icons-listing.js')}}"></script>

<script>
    $(document).ready(function() {
                        ClassicEditor
                        .create(document.querySelector('#editor-content'), {
                            ckfinder: {
                                uploadUrl: "{{route('top_page.upload', ['_token' => csrf_token() ])}}",
                            }
                        })
                        .then(editor => {
                            console.log('Editor siap dipakai', editor);
                        })
                        .catch(error => {
                            console.error(error);
                        });
                     });
                 
                     $(document).ready(function(){
                        console.log('icon ready');
                        const element = document.querySelector('#choices-single-default');
                        const choices = new Choices(element, {
                            choices: iconModule,
                            searchResultLimit: 20,
                        });
                    });

    
</script>
@endsection
