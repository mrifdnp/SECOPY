@extends('layouts.master')
@section('title') {{ $page_title }} @endsection
@section('cssAdded')
    <style>
        .display-none {
            display: none;
        }

        .join input[type="text"] {
            margin: 3px 0px;
        }
    </style>
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Basic @endslot
@slot('title') {{ $page_title }} @endslot
@endcomponent


<div class="row">
    <div class="col-lg-12">                
        <div class="card">            
            <div class="card-body">
                @include('backend.includes.flash')
                <div id="msg">					
				</div>
                <form id="dt_form" action="{{route('setting.store')}}" class="form-horizontal" method="post">
                    @csrf
                    <div class="box">
                        <!-- /.box-header -->
                        <div class="box-body indoit-form row gy-4">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif  
                            <div class="col-xxl-3 col-md-6">
                                <div>
                                    <label for="basiInput" class="form-label">Table Name*</label>
                                    <input value="{{ old('primary_table') }}" type="text" class="form-control" name="primary_table" id="primary_table" placeholder="Table Name" size="50" required>
                                </div>
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <div>
                                    <label for="basiInput" class="form-label">Icon*</label>
                                    <select class="form-control" style="width:100%" name="icon" id="choices-single-default"  placeholder="Icon" >
                                        <option value="{{ old('icon') }}">{{ old('icon') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-xxl-3 col-md-6">
                                <div>
                                    <label for="basiInput" class="form-label">Type*</label>
                                    <div class="radio" style="margin-top: 10px;">
                                        <input class="form-check-input" type="radio" id="CRUD Full Page" name="type" value="fullpage">
                                        <label for="CRUD Full Page">CRUD Full Page</label>
                                        <input class="form-check-input" type="radio" id="CRUD Modal" name="type" value="modal" checked>
                                        <label for="CRUD Modal">CRUD Modal</label>
                                    </div>
                                </div>
                            </div>
                           
                                                                                                                                                                                                      
                        </div>
                        <br>
                        <div class="col-sm-12">
                            <table class="table table-privileges col-sm-12">
                            <tbody id="table_field">
                                <tr>
                                    <td class="col-sm-2"><b> Field Column* </b></td>
                                    <td class="col-sm-1">Label</td>
                                    <td class="col-sm-2">Component</td>
                                    <td class="col-sm-2">Type Data</td>
                                    <td class="col-sm-2">Option</td>
                                    <td class="col-sm-1">Nullable </td>
                                    <td class="col-sm-1">On List</td>
                                    <td class="col-sm-1">ActionBtn</td>
                                </tr>
                                <tr id="row_1">										
                                    <td class="col-sm-2">
                                    <input type="hidden" name="row_id[]" id="id_row_1" value="1">
                                    <input value="id" type="text" class="form-control" name="field[]" placeholder="Primary Field" readonly></td>
                                    <td class="col-sm-2"><input value="" type="text" class="form-control" name="label[]" placeholder="Label Name"> </td>
                                    <td class="col-sm-1 component" id="select_row_1">
                                        <select name="component[]" class="form-control select2 component_id" data-id="1" id="component1" style="width: 144px;" required>
                                            <option value="text">Text Field</option>
                                            <option value="textCurrency">Text Field Currency</option>
                                            <option value="select">Select</option>
                                            <option value="password">Password</option>
                                            <option value="email">Email</option>
                                            <option value="radio">Radio</option>
                                            <option value="checkbox">Checkbox</option>
                                            <option value="selectjoin">Select Join</option>
                                            <option value="textarea" >Text Area</option>
                                            <option value="texteditor" >Text Editor</option>
                                            <option value="file" >File</option>
                                            <option value="image" >Image</option>
                                            <option value="datepicker">Datepicker</option>
                                        </select>
                                        <div>
                                            <div class="col-sm-8" style="padding-left:0">
                                                <input id="row_1_1" value="" type="text" class="form-control display-none select-input" name="select_row_1[]" placeholder="Key, Value">
                                            </div>
                                            <div class="col-sm-4">
                                                <span style="cursor:pointer" onclick="addSelect('row_1')" class="select-input display-none ri-add-box-fill"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="col-sm-1">
                                        <select name="typedata[]" class="form-control select2" id="type1" style="width: 144px;" required>
                                            <option value="uuid" selected>uuid</option>
                                            <option value="integer">integer</option>
                                            <option value="string">string</option>
                                            <option value="text">text</option>
                                            <option value="boolean">boolean</option>
                                            <option value="date">date</option>
                                            <option value="datetime">datetime</option>
                                            <option value="decimal" >decimal</option>
                                            <option value="double" >double</option>
                                            <option value="enum" >enum</option>
                                            <option value="float" >float</option>
                                        </select>										
                                    </td>
                                    <td class="col-sm-2 join">
                                        <select data-baris="1" id="joinTable" name="join_table[]" class="form-control display-none join-table-input">
                                            <option value="0">Pilih tabel</option>
                                            @for ($i = 0; $i < count($tables); $i++)
                                                <option value="{{ $tables[$i] }}">{{ $tables[$i] }}</option>
                                            @endfor
                                        </select>
                                        <input value="" id="joinId-1" type="text" class="form-control display-none join-id-input" name="join_id[]" placeholder="Join ID">
                                        <select id="selectTable-1" name="selectTable[]" class="form-control display-none join-table-input"></select>
                                        <!-- File -->
                                        <input value="" type="text" class="form-control display-none f_file" name="file_type[]" placeholder="type name,name">
                                        <input value="" type="text" class="form-control display-none f_file" name="file_size[]" placeholder="File Size (Kb)">
                                        <input value="" type="text" class="form-control display-none f_img" name="file_thumb[]" placeholder="Thumb H,W(Px)">
                                    </td>
                                    <td class="col-sm-1">
                                        <select class="form-control select2" name="validation[]">
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>                                        
                                    </td>
                                    <td class="col-sm-1">
                                        <select name="onlist[]" class="form-control select2" style="width: 63px;">
                                            <option value="1">YES</option>
                                            <option value="0" selected>NO</option>
                                        </select>
                                    </td>
                                    <td class="col-sm-1"> </td> 
                                </tr>
                                
                                <tr id="row_2">									
                                    <td class="col-sm-2"><input type="hidden" name="row_id[]" id="id_row_2" value="2"><input value="" type="text" class="form-control" name="field[]" placeholder="Field Name"></td>
                                    <td class="col-sm-2"><input value="" type="text" class="form-control" name="label[]" placeholder="Label Name"> </td>
                                    <td class="col-sm-1 component" id="select_row_2">
                                        <select name="component[]" class="form-control select2 component_id" data-id="2" id="component2">
                                            <option value="text">Text Field</option>
                                            <option value="textCurrency">Text Field Currency</option>
                                            <option value="select">Select</option>
                                            <option value="password">Password</option>
                                            <option value="email">Email</option>
                                            <option value="radio">Radio</option>
                                            <option value="checkbox">Checkbox</option>
                                            <option value="selectjoin">Select Join</option>
                                            <option value="textarea" >Text Area</option>
                                            <option value="texteditor" >Text Editor</option>
                                            <option value="file" >File</option>
                                            <option value="image" >Image</option>
                                            <option value="datepicker">Datepicker</option>
                                        </select>
                                        <input id="row_2_1" value="" type="text" class="form-control display-none select-input" name="select_row_2[]" placeholder="Key, Value">
                                        <span style="cursor:pointer" onclick="addSelect('row_2')" class="select-input display-none ri-add-box-fill"></span>
                                    </td>
                                    <td class="col-sm-1">
                                        <select name="typedata[]" id="type2" class="form-control select2 type_id">
                                            <option value="uuid">uuid</option>
                                            <option value="integer">integer</option>
                                            <option value="string" selected>string</option>
                                            <option value="text">text</option>
                                            <option value="boolean">boolean</option>
                                            <option value="date">date</option>
                                            <option value="datetime">datetime</option>
                                            <option value="decimal" >decimal</option>
                                            <option value="double" >double</option>
                                            <option value="enum" >enum</option>
                                            <option value="float" >float</option>
                                        </select>
                                        <input id="type_row_21" value="" type="text" class="form-control display-none custom_type" name="custom[1]" placeholder="type(value)">
                                    </td>
                                    <td class="col-sm-2 join">
                                        <select data-baris="2" id="joinTable" name="join_table[]" class="form-control display-none join-table-input">
                                            <option value="0">Pilih tabel</option>
                                            @for ($i = 0; $i < count($tables); $i++)
                                                <option value="{{ $tables[$i] }}">{{ $tables[$i] }}</option>
                                            @endfor
                                        </select>
                                        <input value="" id="joinId-2" type="text" class="form-control display-none join-id-input" name="join_id[]" placeholder="Join ID">
                                        <select id="selectTable-2" name="selectTable[]" class="form-control display-none join-table-input"></select>
                                        <!-- File -->
                                        <input value="" type="text" class="form-control display-none f_file" name="file_type[]" placeholder="type name,name">
                                        <input value="" type="text" class="form-control display-none f_file" name="file_size[]" placeholder="File Size (Kb)">
                                        <input value="" type="text" class="form-control display-none f_img" name="file_thumb[]" placeholder="Thumb H,W(Px)">
                                    </td>
                                    <td class="col-sm-1">
                                        <select class="form-control select2" name="validation[]">
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </td>
                                    <td class="col-sm-1">
                                        <select name="onlist[]" class="form-control select2" >
                                            <option value="1">YES</option>
                                            <option value="0">NO</option>
                                        </select>
                                    </td>
                                    <td>
                                        <button type="button" class="btn-del btn-sm btn btn-danger btn-flat">                                            
                                            <i class="ri-close-circle-fill"></i>
                                        </button> 
                                        <button onclick="add_after(2);" type="button" class="btn btn-sm btn-primary btn-flat">                                            
                                            <i class="ri-add-circle-fill"></i>
                                        </button>
                                    </td>
                                </tr>						
                            </tbody>
                            </table>
                        </div>
                        <span class="indoit-box-helper"></span>		
                        <div class="indoit-box-action">
                            <div class="col-sm-offset-2">
                                <div class="indoit-box-action-btn"> 
                                    <button type="button" id="add" class="btn btn-warning btn-add-field">Add Field</button> 						
                                    <button type="button" class="btn btn-info" id="generate">Generate</button>                                                                        
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

<div id="myFilterModal" class="modal fade modal-primary">
    <div class="modal-dialog">
        <div class="modal-content" id="modal-filter">
            <div class="modal-header p-3 bg-primary bg-gradient">                
                <h5 class="modal-title text-white"><i class="las la-info-circle"> </i> Replace Confirmation</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>    
            </div>
            <div class="modal-body">
                <div class="box-body index-padding">
                    <span>Are you sure you want to replace this module?</span>                    
                </div>
            </div>
            <div class="modal-footer">        
                <button type="button" class="btn btn-outline btn-danger pull-left text-light" id="btnReplaceClick">
                    <i class="las la-trash"></i> Replace
                </button>
                <button type="button" class="btn btn-outline btn-light pull-left" data-bs-dismiss="modal"><i class="las la-times-circle"></i> Close</button>                
            </div>
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
    $( document ).ready(function() {
        console.log( "ready!" );
        
        const element = document.querySelector('#choices-single-default');
        const choices = new Choices(element, {
            choices: iconModule,
            searchResultLimit: 20, 
        });
    });

    $(".select2").select2();

    var num = 2;
    var no = 2;

    function remselect(key){
	    $("#"+key).remove();
    }   

    function addSelect(trId){
        //alert('div'+trId+'_'+num);
        var div = "remselect('div"+trId+'_'+num+"')";
        $("#select_"+trId).append('<div id="div'+trId+'_'+num+'" class="select-list"><div class="col-sm-8" style="padding-left:0"><input id="'+trId+'_'+num+'" value="" type="text" class="form-control" name="select_'+trId+'[]" placeholder="Key, Value"></div><div class="col-sm-4"><span style="cursor:pointer" class="ri-subtract-fill" onclick="'+div+'"></span></div></div>');
        num++;
    }

    function add_after(id_row){
        let tabel = $('#primary_table').val();
        no++;
        var javOnclick = "addSelect('row_"+no+"')";
        $.ajax({
            url:"{{route('basic.getJoin')}}",
            type: "GET",
            data:{ 
                _token:'{{ csrf_token() }}',
                table: tabel,
                seq: no,
                javOnclikData: javOnclick
            },
            cache: false,
            dataType: 'json',
            success: function(dataResult){
                let component = dataResult.data;
                $('<tr id=row_'+no+'></tr>').insertAfter($("#row_"+id_row));
                $("#row_"+no).append(component);
                $(".select2").select2();
                $("#idrow_"+no).val(no);
            }
        });
    }


    $(function() {
		$(".select2").select2();
	
		$('#add').click(function () {
            let tabel = $('#primary_table').val();
            no++;
            var javOnclick = "addSelect('row_"+no+"')";
            $.ajax({
                url:"{{route('basic.getJoin')}}",
                type: "GET",
                data:{ 
                    _token:'{{ csrf_token() }}',
                    table: tabel,
                    seq: no,
                    javOnclikData: javOnclick
                },
                cache: false,
                dataType: 'json',
                success: function(dataResult){
                    let component = dataResult.data;
                    $("#table_field").append('<tr id=row_'+no+'></tr>');
                    $("#row_"+no).append(component);
                    $(".select2").select2();
                    $("#idrow_"+no).val(no);
                }
            });
  		});
		
		$("tbody").on("click", ".btn-del", function() {
			var tr = $(this).closest("tr");
			var recordId = tr.attr("id");
			
			$("#"+recordId).remove();
		});

		$("tbody").on("change", ".type_id", function() {
			var tr = $(this).closest("tr");
			var recordId = tr.attr("id");
			
			var type_val = $("#"+recordId+" .type_id").val();
			if (type_val=="custom") {				
				$("#"+recordId+" .custom_type").show();
				$("#"+recordId+" .custom_type").attr("required","true");
			}else{
				$("#"+recordId+" .custom_type").hide();
				$("#"+recordId+" .custom_type").removeAttr("required");
			}

		});
	
		$("tbody").on("change", ".component_id", function() {
            var thisID = $(this).data('id');            
			var tr = $(this).closest("tr");
			var recordId = tr.attr("id");
			var component_val = $("#"+recordId+" .component_id").val();
			
			switch(component_val){
				case "select":
					$("#"+recordId+" .select-input").show();
					$("#"+recordId+" .join-table-input").hide();
					$("#"+recordId+" .join-id-input").hide();
					$("#"+recordId+" .join-select-input").hide();
					$("#"+recordId+" .select-list").remove();
					$("#"+recordId+" .f_file").hide();
					$("#"+recordId+" .f_img").hide();
					$("#"+recordId+" .f_file").removeAttr("required");
					$("#"+recordId+" .f_img").removeAttr("required");		
					break;
				case "radio":
					$("#"+recordId+" .select-input").show();
					$("#"+recordId+" .join-table-input").hide();
					$("#"+recordId+" .join-id-input").hide();
					$("#"+recordId+" .join-select-input").hide();
					$("#"+recordId+" .select-list").remove();
					$("#"+recordId+" .f_file").hide();
					$("#"+recordId+" .f_img").hide();
					$("#"+recordId+" .f_file").removeAttr("required");
					$("#"+recordId+" .f_img").removeAttr("required");
					break;
				case "checkbox":
					$("#"+recordId+" .select-input").show();
					$("#"+recordId+" .join-table-input").hide();
					$("#"+recordId+" .join-id-input").hide();
					$("#"+recordId+" .join-select-input").hide();
					$("#"+recordId+" .select-list").remove();
					$("#"+recordId+" .f_file").hide();
					$("#"+recordId+" .f_img").hide();
					$("#"+recordId+" .f_file").removeAttr("required");
					$("#"+recordId+" .f_img").removeAttr("required");
					break;
				case "selectjoin":
					var a = recordId.split("_");
					var angka = a[1];
					var idJoin = "#joinId-"+angka;
					var nilai = null;
					// console.log($(recordId+"[input #id"));
					tr.find("input[type=text]").each(function() {
						if (this.name == 'field[]') {
							nilai = this.value;
						}
					});
					$(idJoin).val(nilai);
					$(idJoin).attr('disabled','disabled');
					$("#"+recordId+" .select-input").hide();
					$("#"+recordId+" .join-table-input").show();
					$("#"+recordId+" .join-id-input").show();
					$("#"+recordId+" .join-select-input").show();
					$("#"+recordId+" .select-list").remove();
					$("#"+recordId+" .f_file").hide();
					$("#"+recordId+" .f_img").hide();
					$("#"+recordId+" .f_file").removeAttr("required");
					$("#"+recordId+" .f_img").removeAttr("required");
                    $("#type"+thisID).val("uuid").trigger('change');                    
					break;
				case "file":
					$("#"+recordId+" .select-input").hide();
					$("#"+recordId+" .join-table-input").hide();
					$("#"+recordId+" .join-id-input").hide();
					$("#"+recordId+" .join-select-input").hide();
					$("#"+recordId+" .select-list").remove();
					$("#"+recordId+" .f_file").show();
					$("#"+recordId+" .f_img").hide();
					$("#"+recordId+" .f_file").attr("required","true");
					$("#"+recordId+" .f_img").removeAttr("required");

					break;
				case "image":
					$("#"+recordId+" .select-input").hide();
					$("#"+recordId+" .join-table-input").hide();
					$("#"+recordId+" .join-id-input").hide();
					$("#"+recordId+" .join-select-input").hide();
					$("#"+recordId+" .select-list").remove();
					$("#"+recordId+" .f_file").show();
					$("#"+recordId+" .f_img").show();
					$("#"+recordId+" .f_file").attr("required","true");
					$("#"+recordId+" .f_img").attr("required","true");
					break;	
				default:
					$("#"+recordId+" .select-input").hide();
					$("#"+recordId+" .join-table-input").hide();
					$("#"+recordId+" .join-id-input").hide();
					$("#"+recordId+" .join-select-input").hide();
					$("#"+recordId+" .select-list").remove();
					$("#"+recordId+" .f_file").hide();
					$("#"+recordId+" .f_img").hide();
					$("#"+recordId+" .f_file").removeAttr("required");
					$("#"+recordId+" .f_img").removeAttr("required");
					break;
			}
		});
		$(document).on('change','#joinTable',function(){
			var baris = $(this).data("baris");
			var getTabel = this.value;
            var bodyData = '';
            $.ajax({
                url:"{{route('get.attribute')}}",
                type: "GET",
                data:{ 
                    _token:'{{ csrf_token() }}',
                    tabel: getTabel
                },
                cache: false,
                dataType: 'json',
                success: function(dataResult){
					var koloms = dataResult.columns;
					$("#selectTable-"+baris).empty();
                    $.each(koloms,function(index,row){
                        bodyData+=`<option value="${row}">${row}</option>`;
						$("#selectTable-"+baris).append($('<option>', { 
							value: row,
							text : row 
						}));
                    });                      
                }
        });
    });		
		$('#generate').click(function () {
     		let tabel = $('#primary_table').val();
			 $.ajax({
                url:"{{route('check.table')}}",
                type: "GET",
                data:{ 
                    _token:'{{ csrf_token() }}',
                    newTable: tabel
                },
                cache: false,
                dataType: 'json',
                success: function(dataResult){
                    var status = dataResult.dataResult;
                    console.log(dataResult)
                    console.log(status)
					if (status) {
						$('#myFilterModal').modal('show');                     
					} else {
						sendData()
                        console.log('harusnya send data')
					}
                }
        	});
        }); 

		$('#btnReplaceClick').click(function (){
			$('#myFilterModal').modal('hide');
			sendData()
		}); 		

		function sendData() {
			$.ajax({
                url:"{{route('generator_basic.generate')}}",
                type: "POST",
                data : $('#dt_form').serialize(),
				cache: false,
                dataType: 'json',
                success: function(dataResult){
                    console.log('data result di send data adalah: ', dataResult)
                    var message = dataResult.dataResult.modul;
                    var urlRedirect = dataResult.dataResult.url;
                    console.log(urlRedirect)
					var messageData = `<div class="alert alert-success"> <i class="icon fa fa-check"></i> ${message}</div>`;
					$('#msg').empty();
        			$("#msg").append(messageData);
                    // setTimeout(function() {
                    //     window.location.href = urlRedirect;                        
                    // }, 2000);
                    console.log('data send data', dataResult)
                }
        	});
		}

    });

</script>

<script src="{{ URL::asset('/build/js/pages/remix-icons-listing.js')}}"></script>
@endsection
