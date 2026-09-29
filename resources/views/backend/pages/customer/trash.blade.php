@extends('layouts.master')
@section('title') List {{ $titleHeader->name }} @endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Systems @endslot
@slot('title') Data {{ $titleHeader->name }} @endslot
@endcomponent


<div class="row">
    <div class="col-lg-12">
        <form action="#" method="post">
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
        <div class="card">            
            <div class="card-body">
                @include('backend.includes.flash')
                <table id="dt_basic" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th class="th_no" data-hide="phone"><input id="main_checkbox" name="main_checkbox" type="checkbox" value=""></th>
                            <th class="th_no" data-hide="phone">No</th>
                            <th data-class="expand">First name</th>
<th data-class="expand">Last name</th>
<th data-class="expand">Email</th>
<th data-class="expand">Phone</th>
<th data-class="expand">Birthday</th>
<th data-class="expand">City</th>
<th data-class="expand">Province</th>
<th data-class="expand">Onboarding channel</th>

                            <th data-hide="phone">Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
        </form>
    </div>
    <!--end col-->
</div>
<!--end row-->
<div id="myModal"class="modal fade modal-primary">
    <div class="modal-dialog">
        <div class="modal-content" id="modal-delete" style="border: none !important;">

        </div>
    </div>
</div>	

<div id="myModalRestore"class="modal fade modal-primary">
    <div class="modal-dialog">
        <div class="modal-content" id="modal-restore" style="border: none !important;">	
        </div>
    </div>
</div>	
{{-- modal filter --}}
<div id="myFilterModal"class="modal fade modal-primary">
    <div class="modal-dialog">
        <div class="modal-content" id="modal-filter">
            <div class="modal-header p-3 bg-primary bg-gradient">                
                <h5 class="text-white"><i class="ri-filter-line"> </i> Filter Data</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form_filter" name="form_filter" action="" method="post">
                @csrf
                <div class="modal-body">
                    <div class="box-body indoit-form form-horizontal">
                        <div class="form-group mb-3">
        <label class="col-sm-12 control-label">First name</label>
                            <div class="col-sm-12">
                                <input value="" type="text" class="form-control" name="first_name" id="first_name" placeholder="" size="40">
</div>
        </div>
<div class="form-group mb-3">
        <label class="col-sm-12 control-label">Last name</label>
                            <div class="col-sm-12">
                                <input value="" type="text" class="form-control" name="last_name" id="last_name" placeholder="" size="40">
</div>
        </div>
<div class="form-group mb-3">
        <label class="col-sm-12 control-label">Email</label>
                            <div class="col-sm-12">
                                <input value="" type="text" class="form-control" name="email" id="email" placeholder="" size="40">
</div>
        </div>
<div class="form-group mb-3">
        <label class="col-sm-12 control-label">Phone</label>
                            <div class="col-sm-12">
                                <input value="" type="text" class="form-control" name="phone" id="phone" placeholder="" size="40">
</div>
        </div>
<div class="form-group mb-3">
        <label class="col-sm-12 control-label">Birthday</label>
                        <div class="col-sm-12"><div class="input-group date col-lg-10 col-md-10 col-sm-10 col-xs-10"><div class="input-group-addon"><i class="ri-calendar-2-fill"></i></div><input type="text" class="form-control pull-left" id="datepicker" name="birthday" id="birthday"></div>

</div>
        </div>
<div class="form-group mb-3">
        <label class="col-sm-12 control-label">City</label>
                            <div class="col-sm-12">
                                <input value="" type="text" class="form-control" name="city" id="city" placeholder="" size="40">
</div>
        </div>
<div class="form-group mb-3">
        <label class="col-sm-12 control-label">Province</label>
                            <div class="col-sm-12">
                                <input value="" type="text" class="form-control" name="province" id="province" placeholder="" size="40">
</div>
        </div>
<div class="form-group mb-3">
        <label class="col-sm-12 control-label">Onboarding channel</label>
                            <div class="col-sm-12">
                                <input value="" type="text" class="form-control" name="onboarding_channel" id="onboarding_channel" placeholder="" size="40">
</div>
        </div>

                    </div><!-- /.box-body -->
                </div>

                <div class="modal-footer bg-opacity-75">        
                    <button type="button" class="btn btn-outline btn-light pull-left" data-bs-dismiss="modal"><i class="las la-times-circle"></i> Close</button>
                    <button type="button" id="btn-reset" onClick="this.form.reset()" class="btn btn-warning pull-left text-light"><i class="las la-undo-alt"></i> Reset</button>
                    <button type="button" id="btn-filter" class="btn btn-info pull-left text-light"><i class=" las la-share"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>	
{{-- end modal filter --}}
{{-- @include('layouts.modal_delete') --}}
@endsection


@section('css')
<link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-responsive-2.2.9/responsive.bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.dataTables.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/velzon-dependencies/select2-bootstrap-theme-0.1.0-beta.10/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('script')

<script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/jquery.dataTables.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/dataTables.bootstrap5.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-responsive-2.2.9/dataTables.responsive.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/dataTables.buttons.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.print.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.html5.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/velzon-dependencies/pdfmake-0.1.53/vfs_fonts.js')}}"></script>
<script src="{{ URL::asset('build/libs/velzon-dependencies/pdfmake-0.1.53/pdfmake.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/velzon-dependencies/jszip-3.1.3/jszip.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-1.2.4/buttons.colVis.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/datatables.net/extensions/export/buttons.bootstrap5.js')}}"></script>
<script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js')}}"></script>


<script>
    $(function () {
                        $('#datepicker-birthday').datepicker({
                            autoclose: true
                        })
                    });

    
    $(function () {
        //Date picker
        $('#datepicker').datepicker({
        autoclose: true
        })
    });
document.addEventListener('DOMContentLoaded', function () {    
    let table = new DataTable('#dt_basic',
    {
        dom: "<'row'<'col-sm-8 col-md-8 btn-tbl'B><'col-sm-4 col-md-4'<'span dt-right'l>>>" +
                "<'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                processing: true,
                serverSide: true,
                autoWidth:false,
                scrollX:true,
                "lengthMenu": [[100, 250, 500,1000, -1], [100, 250, 500,1000, "All"]],
                order: [[ 4, "asc" ]],
                language: {
                    "lengthMenu": "Display _MENU_ "
                },
                buttons: [                                   
                        {
                           text: '<i class=" las la-undo-alt text-success"></i> Bulk Restore',
                            className: 'child-button',
                            action: function ( e, dt, button, config ) {
                                e.preventDefault();
                                
                                var id_to_be_restored = $("#dt_basic tbody input:checkbox:checked").map(function(){
                                return $(this).val();
                                }).get();
                                
                                $.ajax({
                                    type:"POST",
                                    url:"{{route('customer.restore')}}",
                                    data:{
                                        id_to_be_restored: id_to_be_restored
                                    },
                                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                                    success:function(data){
                                        $('#myModalRestore').modal('show');
                                        $('#modal-restore').html(data);
                                    }
                                });
                            }        
                        },                       
                        {
                            text: '<i class=" las la-trash text-danger"></i> Bulk Delete',
                            className: 'child-button',
                            action: function ( e, dt, button, config ) {
                                e.preventDefault();
                                
                                var id_to_be_deleted = $("#dt_basic tbody input:checkbox:checked").map(function(){
                                    return $(this).val();
                                }).get();
                                
                                $.ajax({
                                    type:"POST",
                                    url:"{{route('customer.delete')}}",
                                    data:{
                                        id_to_be_deleted: id_to_be_deleted,
                                        delete_type: 'harddelete'
                                    },
                                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                                    success:function(data){
                                        $('#myModal').modal('show');
                                        $('#modal-delete').html(data);
                                    }
                                });
                            }        
                        },                   
                        {
                            extend: 'collection',
                            text: '<i class=" las la-file-excel"></i> Export',
                            className: 'child-button',
                            buttons: [
                                {
                                    extend: 'excel',
                                    text: 'excel',
                                    exportOptions: {
                                        columns :[ 1,2,3 ]
                                    }
                                }
                            ]
                        },
                        {
                            text: '<i class="las la-search"></i> Filter',
                            className: 'child-button',
                            action: function ( e, dt, button, config ) {
                                    $('#myFilterModal').modal('show');
                                }        
                        },
                    ],
                ajax: {
                    "url":  "{{route('customer.get_datatable_trash')}}",
                    "type": "get",
                    "data": function ( data ) {
                        data.first_name = $('#first_name').val();
data.last_name = $('#last_name').val();
data.email = $('#email').val();
data.phone = $('#phone').val();
data.birthday = $('#datepicker').val();
data.city = $('#city').val();
data.province = $('#province').val();
data.onboarding_channel = $('#onboarding_channel').val();

                        i = data.start+1;                    
                    }
                },
                columns: [
                    { 
                        data: 'checkboxs',
                        name: 'checkboxs',
                        orderable: false,
                        searchable: false
                    },
                    {             
                        data: 'number',
                        name: 'number',           
                        render: function() {
                            return i++;
                        }
                    },
                        {
data: 'first_name',
name: 'first_name'
},
{
data: 'last_name',
name: 'last_name'
},
{
data: 'email',
name: 'email'
},
{
data: 'phone',
name: 'phone'
},
{
data: 'birthday',
name: 'birthday'
},
{
data: 'city',
name: 'city'
},
{
data: 'province',
name: 'province'
},
{
data: 'onboarding_channel',
name: 'onboarding_channel'
},

                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        width:'280px'
                    }
                ],
            
    });
    $('#btn-filter').click(function(){ //button filter event click
        table.ajax.reload();  //just reload table
    });
    $('#btn-reset').click(function(){ //button reset event click
        table.ajax.reload();  //just reload table
    });
    $('body').on('click', '#main_checkbox', function () {
        if($(this).is(':checked')) {
            $('.checkbox_item').prop('checked', true);
        }
        else{
            $('.checkbox_item').prop('checked', false);
        }
    });
    $("#dt_basic tbody").on("click", ".btnDelete", function(e) {
        e.preventDefault();				
        var id_to_be_deleted =  $(this).attr("id");
        
        $.ajax({
            type:"POST",
            url:"{{route('customer.delete')}}",
            data:{
                id_to_be_deleted: [id_to_be_deleted],
                delete_type: 'harddelete'
            },
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success:function(data){
                $('#myModal').modal('show');
                $('#modal-delete').html(data);
            }
        });
    });

    $("#dt_basic tbody").on("click", ".btnRestore", function(e) {
        e.preventDefault();				
        var id_to_be_restored =  $(this).attr("id");
        
        $.ajax({
            type:"POST",
            url:"{{route('customer.restore')}}",
            data:{
                id_to_be_restored: [id_to_be_restored]
            },
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success:function(data){
                $('#myModalRestore').modal('show');
                $('#modal-restore').html(data);
            }
        });
    });
    var a = '<div class="dt-buttons btn-group btn-recycle"><a class="btn btn-info btn-light" tabindex="0" aria-controls="dt_basic" href="{{route('customer.index')}}"><span><i class="fa fa-archive"></i>  Active</span></a><a href="{{route('customer.trash')}}" class="btn btn-primary" style="margin-right:5px;" tabindex="0" aria-controls="dt_basic"><span><i class="bx bx-recycle"></i> Recycle Bin ({{$customers_count}}) </span> </a></div>';
    $(".btn-tbl" ).prepend(a);
});
</script>


<!-- dashboard init -->
@endsection
