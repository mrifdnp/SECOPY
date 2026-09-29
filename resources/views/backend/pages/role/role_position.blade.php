@extends('layouts.master')
@section('title') Position {{ $titleHeader->name }} @endsection
@section('cssAdded')
    <style>
        input[type="checkbox"]{
            visibility: hidden;
            position: absolute;
        }
        input[type="checkbox"] + label:before{
            height:12px;
            width:12px;
            margin-right: 2px;
            content: " ";
            display:inline-block;
            vertical-align: baseline;
            border:1px solid #777;
        }
        input[type="checkbox"]:checked + label:before{
            background-color: rgb(41, 123, 231);
        }
        input[type="checkbox"] + label:before{
            border-radius:50%;
        }

    </style>
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Systems @endslot
@slot('title') Position of {{ $titleHeader->name }} @endslot
@endcomponent


<div class="row">
    <div class="col-lg-12">                
        <div class="card">            
            <div class="card-body">
                <form id="dt_form" action="{{route('role.position_save', $role->id)}}" class="form-horizontal" method="post">
                    @csrf
                    @method('PUT')
                    @include('backend.includes.flash')
                    <div class="box">
                        <div class="box-body indoit-form">
                            {{-- content --}}
                            <table class="table table-bordered">
                                <tr>
                                    <td style="width: 200px"><b>Name</b></td>
                                    <td>{{ $role->name }}</td>
                                </tr>
                                <tr>
                                    <td><b>Description</b></td>
                                    <td>{{ $role->description }}</td>
                                </tr>
                                <tr>
                                    <td><b>Position</b></td>
                                    <td>                              
                                        <div class="dd" id="nestable">
                                            @php
                                                if($module_position != "") {
                                                    echo $module_position;
                                                }
                                            @endphp                                
                                            @if($module_position == "")
                                                <ol class="dd-list" style="list-style:none;">
                                                    @foreach($role_has_permissions as $row)
                                                        <li class="dd-item" data-id="{{$row->id}}" style="display: block;">   
                                                            <div class="dd-handle">
                                                                {{$row->name}} @if($row->url!='') <span class="label badge bg-info"> (url:/{{$row->url}})</span> @endif
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ol>
                                            @endif
                                        </div>
                                        <textarea style="display:none" name="role_position" id="nestable-output"></textarea>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <span class="indoit-box-helper"></span>		
                        <div class="indoit-box-action">
                            <div class="col-sm-offset-2">
                                <div class="indoit-box-action-btn">                                                                    
                                    <a href="{{route('role.index')}}" class="btn btn-soft-secondary">Back to List</a>                                
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </div>
                        </div>                    
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
    <link href="{{ URL::asset('build/libs/velzon-dependencies/nestable2-1.6.0/jquery.nestable.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('script')

    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js')}}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js')}}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js')}}"></script>

    <script src="{{ asset('assets/backend/extended/js/floating_action.js')}}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/nestable2-1.6.0/jquery.nestable.min.js') }}"></script>

    <script>

        $(document).ready(function()
        {
        
            var updateOutput = function(e)
            {
                var list   = e.length ? e : $(e.target),
                    output = list.data('output');
                if (window.JSON) {
                    output.val(window.JSON.stringify(list.nestable('serialize')));//, null, 2));
                } else {
                    output.val('JSON browser support required for this demo.');
                }
            };
        
            // activate Nestable for list 1
            $('#nestable').nestable({
                group: 1
            })
            .on('change', updateOutput);
        
            // activate Nestable for list 2
            $('#nestable2').nestable({
                group: 1
            })
            .on('change', updateOutput);
        
            // output initial serialised data
            updateOutput($('#nestable').data('output', $('#nestable-output')));
            updateOutput($('#nestable2').data('output', $('#nestable2-output')));
        
            $('#nestable-menu').on('click', function(e){
                var target = $(e.target),
                    action = target.data('action');
                if (action === 'expand-all') {
                    $('.dd').nestable('expandAll');
                }
                if (action === 'collapse-all') {
                    $('.dd').nestable('collapseAll');
                }
            });
        
            $('#nestable3').nestable();
        
        });
    </script>
@endsection
