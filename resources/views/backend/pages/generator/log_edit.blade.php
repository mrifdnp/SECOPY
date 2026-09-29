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
                <form id="dt_form" action="" class="form-horizontal" method="post">
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
                            <div class="form-group">
                                <label for="" class="col-sm-2 control-label">Table Name*</label>
                                <div class="col-sm-10">
                                    <input value="{{ $module->module }}" type="text" class="form-control" name="primary_table" id="primary_table" placeholder="Table Name" size="50" readonly required>
                                </div>
                            </div>
                            <br>
                            <div class="form-group" id="iconRender">
                                <label for="" class="col-sm-2 control-label">Icon*</label>
                                <div class="col-sm-10">                                    
                                    <select class="form-control" style="width:100%" name="icon" id="choices-single-default"  placeholder="Icon" >
                                        <option value="{{ old('icon') ? old('icon') : $module->icon }}" selected><i class="{{ $module->icon }}"></i> {{ $module->icon }}</option>                                        
                                    </select>
                                </div>
                            </div>                            
                            <br>                                                                                                                                                                                 
                        </div>
                        <br>
                        <div class="col-sm-12">
                            <table class="table table-privileges col-sm-12">
                            <tbody id="table_field">
                                						
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
        let tabel = "{{ $module->module }}";
            $.ajax({
            url:"{{route('generator-log.getModule')}}",
            type: "GET",
            data:{ 
                _token:'{{ csrf_token() }}',
                table: tabel
            },
            cache: false,
            dataType: 'json',
            success: function(dataResult){
                if (dataResult.data[5] != null) {
                    var message = dataResult.data[5];
                    var messageData = `<div class="alert alert-warning"> <i class="icon fa fa-times"></i> ${message}</div>`;
                    $('#msg').empty();
                    $("#msg").append(messageData);
                }else {
                    no = dataResult.data[2];
                    num = dataResult.data[3];
                    // $('#iconRender').empty();
                    $('#table_field').empty();
                    // $('#iconRender').append(dataResult.data[0]);
                    $('#table_field').append(dataResult.data[1]);
                    $("#add").css("display", "inline");
                    $("#generate").css("display", "inline");
                    $(".select2").select2();	
                }
            }
        });
        // 
        const element = document.querySelector('#choices-single-default');
        const choices = new Choices(element, {
            choices: iconModule,
        });
    });

    $(".select2").select2();

    var num = 2;
    var no = 2;

    function remselect(key){
	    $("#"+key).remove();
    }   

    function addSelect(trId){
        alert('div'+trId+'_'+num);
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
					if (status) {
						$('#myFilterModal').modal('show');                     
					} else {
						sendData()
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
                    var message = dataResult.dataResult[0];
                    var urlRedirect = dataResult.dataResult[1];
					var messageData = `<div class="alert alert-success"> <i class="icon fa fa-check"></i> ${message}</div>`;
					$('#msg').empty();
        			$("#msg").append(messageData);
                    setTimeout(function() {
                        window.location.href = urlRedirect;                        
                    }, 2000);
                }
        	});
		}

    });

</script>
<script src="{{ URL::asset('/build/js/pages/remix-icons-listing.js')}}"></script>
@endsection
