@extends('layouts.master')
@section('title') Detail {{ $titleHeader->name }} @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Systems @endslot
@slot('title') Detail {{ $titleHeader->name }} @endslot
@endcomponent


<div class="row">
    <div class="col-lg-12">                
        <div class="card">            
            <div class="card-body">
                @include('backend.includes.flash')
                <div class="box">
                    <div class="box-body indoit-form">
                        <table class="table table-bordered">
                            <tr>
                        <td style="width: 200px"><b>Title</b></td>
                        <td>{{ $category->title }}</td>
                    </tr>
<tr>
                        <td style="width: 200px"><b>Main Category</b></td>
                        <td>{{ $category->main_categories?->title }}</td>
                    </tr>

                        </table>  
                    </div>
                    <span class="indoit-box-helper"></span>		
                    <div class="indoit-box-action">
                        <div class="col-sm-offset-2">
                            <div class="indoit-box-action-btn">                                    
                                <a href="{{ url()->previous() }}" class="btn btn-soft-secondary">Back to List</a>                                
                            </div>
                        </div>
                    </div>                    
                </div>                
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
@endsection
