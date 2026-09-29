@extends('layouts.master')
@section('title') {{ $titleHeader->name }} @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Data Relation @endslot
@slot('title') Add Data Relation @endslot
@endcomponent
@section('cssAdded')
    
    
@endsection

<div class="row">
    <div class="col-lg-4 col-md-4">                
        <div class="card">            
            <div class="card-body">                
                <form id="dt_form" action="{{route('datarelation.store')}}" class="form-horizontal" method="post">
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
                            <span class="label label-success">Primary Table :</span><br><br>
						    <div class="form-group">
							    <label for="" class="col-sm-12 control-label">Table*</label>
                                <div class="col-sm-10">
							        <select name="primary_table" id="primary_table" class="form-control js-example-basic-single" style="width: 100%;">
                                        @foreach($tables as $row)
                                            <option value="{{$row}}">{{$row}}</option>
                                        @endforeach
								
							        </select>
							    </div>
						    </div>
                            <br>
                            <div class="form-group">
							    <label for="" class="col-sm-12 control-label">Key*</label>
							    <div class="col-sm-10">
							        <select name="primary_id" id="primary_id" class="form-control js-example-basic-single" style="width: 100%;">
							        </select>
							    </div>
						    </div>
                            <br>
						    <span class="label label-success">Relation Table :</span><br><br>
                            <div class="form-group">
                                <label for="" class="col-sm-12 control-label">Table*</label>
                                <div class="col-sm-10">
                                    <select name="relation_table" id="relation_table" class="form-control js-example-basic-single" style="width: 100%;">
                                        @foreach($tables as $row)
                                            <option value="{{$row}}">{{$row}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <br>
						    <div class="form-group">
							    <label for="" class="col-sm-12 control-label">Key*</label>
							    <div class="col-sm-10">
							        <select name="relation_id" id="relation_id" class="form-control js-example-basic-single" style="width: 100%;">
							        </select>
							    </div>	
						    </div>
                        </div>                                                                                                                                                                                 
                    </div>
                    <br>
                    <span class="indoit-box-helper"></span>		
                    <div class="indoit-box-action">
                        <div class="col-sm-offset-2">
                            <div class="indoit-box-action-btn">   
                                @if(\RuleHelper::UserHasPermission('create',false))                                                                                                    
                                    <button type="submit" class="btn btn-primary">Save</button>
                                @endif
                            </div>
                        </div>
                    </div><!-- /.box-footer -->	                                
                    <div class="indoit-box-required">
                        <hr>
                            <span class="required">*</span>
                            Field Required
                    </div><!-- /.box-footer -->                    
                </form>
            </div>
        </div>
    </div>
    <!--end col-->
    <div class="col-lg-8 col-md-8">                
        <div class="card">            
            <div class="card-body">
                {{-- @include('backend.includes.flash')                                     --}}
                    <div class="box">
                        <!-- /.box-header -->
                        <div class="box-body indoit-form">
                            @include('backend.includes.flash')
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Primary Table</th>
                                    <th>On Delete <br> <small>[click to change]</small></th>
                                    <th width="45%">Relation Table <br> <small>[click to delete]</small></th>
                                </tr>
                                @foreach($relations as $row)
                                    <tr>
                                        <td>{{++$index}}.</td>
                                        <td>{{$row->primary_table}}({{$row->primary_id}})</td>
                                        <td>
                                        @if ($row->on_delete === "cascade")
                                            <form action="{{route('datarelation.update',$row->id)}}" method="post">
                                                @csrf
                                                @method('PUT')                                                
                                                <input type="hidden" name="on_delete" value="restrict">
                                                <input type="hidden" name="primary_table" value="{{$row->primary_table}}">
                                                <button type="submit" class="btn badge on_delete text-success "> 
                                                    <i class="ri-checkbox-circle-fill"></i> {{$row->on_delete}}
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{route('datarelation.update',$row->id)}}" method="post">
                                                @csrf
                                                @method('PUT')                                                
                                                <input type="hidden" name="on_delete" value="cascade">
                                                <input type="hidden" name="primary_table" value="{{$row->primary_table}}">
                                                <button type="submit" class="btn badge on_delete text-danger"> 
                                                    <i class="ri-lock-line"></i> {{$row->on_delete}}
                                                </button>
                                            </form>
                                        @endif
                                        
                                        
                                        </td>
                                        <td>
                                        @foreach($detail_relations[$index-1] as $row)
                                            <a href="#" id="{{$row->id}}" class="btn badge relation_table bg-warning">
                                                {{$row->relation_table}} ({{$row->relation_id}}) <i class="ri-delete-bin-fill"></i>
                                            </a>
                                        @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>                                                                                                                                                                                 
                    </div>                    
                </div>
            </div>
        </div>
    </div>
<!--end row-->
<div id="myModal"class="modal fade modal-primary">
    <div class="modal-dialog">
        <div class="modal-content" id="modal-delete" style="border: none !important;">

        </div>
    </div>
</div>

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
    $(function () {   
        
        
        $("#primary_table").change(function() {
            $.get("{{url('datarelation')}}/"+$("#primary_table").val(), function( data ) {
                $("#primary_id").html(data);
                $("#primary_id").select2();	
            });
        });
        $("#primary_table" ).trigger("change");
        
        $("#relation_table").change(function() {
            $.get("{{url('datarelation')}}/"+$("#relation_table").val(), function( data ) {
                $("#relation_id").html(data);
                $("#relation_id").select2();		
            });
        });
        $("#relation_table" ).trigger("change");
        

        $(".box-body").on("click", ".relation_table", function(e) {
            e.preventDefault();				
            var a = $(this).closest("a");
            var delete_id = a.attr("id");
            
            $.ajax({
                type:"POST",
                url:"{{route('datarelation.delete')}}",
                data:{
                    delete_id: delete_id
                },
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success:function(data){
                    console.log(data);
                    $('#myModal').modal('show');
                    $('.modal-content').html(data);
                }
            });
        });
    });

</script>
<script src="{{ URL::asset('/build/js/pages/remix-icons-listing.js')}}"></script>

@endsection
