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
                            <th data-class="expand">Tanggal</th>
                            <th data-class="expand">Debit</th>
                            <th data-class="expand">Kredit</th>                            
                            <th data-hide="phone">Dibuat pada</th>
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
                        <div class="form-group">
                            <label for="" class="col-sm-3 control-label">Tanggal</label>
                            <div class="col-sm-12">
                                <input type="date" class="form-control" id="date" name="date">                                                                        
                            </div>
                        </div>
                        <br/>
                        <div class="form-group">        
                            <label for="" class="col-sm-3 control-label">Jenis Akun</label>                        
                            <div class="col-sm-12">
                                <select class="form-control" name="head_accounts_id" id="head_accounts_id">
                                    <option value="" selected>-- Silakan Pilih --</option>
                                    @foreach ($head_accounts as $head_account) 
                                        <option value="{{ $head_account->id }}" >{{ $head_account->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>                        
                        <br/>
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
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css" integrity="sha512-kq3FES+RuuGoBW3a9R2ELYKRywUEQv0wvPTItv3DSGqjpbNtGWVdvT8qwdKkqvPzT93jp8tSF4+oN4IeTEIlQA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('script')

<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
{{-- <script src="{{ URL::asset('build/libs/datatables.net/extensions/export/buttons.bootstrap.min.js')}}"></script> --}}
<script src="{{ URL::asset('build/libs/datatables.net/extensions/export/buttons.bootstrap5.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.10/jquery.mask.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/3.1.4/js/bootstrap-datetimepicker.min.js" integrity="sha512-r/mHP22LKVhxWFlvCpzqMUT4dWScZc6WRhBMVUQh+SdofvvM1BS1Hdcy94XVOod7QqQMRjLQn5w/AQOfXTPvVA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/3.1.4/css/bootstrap-datetimepicker.css" integrity="sha512-HWqapTcU+yOMgBe4kFnMcJGbvFPbgk39bm0ExFn0ks6/n97BBHzhDuzVkvMVVHTJSK5mtrXGX4oVwoQsNcsYvg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/3.1.4/css/bootstrap-datetimepicker.min.css" integrity="sha512-ve3HkfDj0yuNfwMyOMoyva1kd6FO3N07Ypbz4YEOnGLOScyYu719khU1yGdX9iUR4oyVT6p0jfcRbVWefwRmvg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ URL::asset('build/js/pages/select2.init.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.10/jquery.mask.js"></script>

<script>
    
    $(function () {
        $('#datetimepicker-date').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss'
        });
    });

    $('#debit').mask('000.000.000', {reverse: true});
    $('#kredit').mask('000.000.000', {reverse: true});

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
                                    url:"{{route('general-journal.restore')}}",
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
                                    url:"{{route('general-journal.delete')}}",
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
                    "url":  "{{route('general-journal.get_datatable_trash')}}",
                    "type": "get",
                    "data": function ( data ) {
                        data.date = $('#datetimepicker-date').val();
                        data.head_accounts_id = $('#head_accounts_id option:selected').val();
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
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'debit',
                        name: 'debit'
                    },
                    {
                        data: 'kredit',
                        name: 'kredit'
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
            url:"{{route('general-journal.delete')}}",
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
            url:"{{route('general-journal.restore')}}",
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
    var a = '<div class="dt-buttons btn-group btn-recycle"><a class="btn btn-info btn-light" tabindex="0" aria-controls="dt_basic" href="{{route('general-journal.index')}}"><span><i class="fa fa-archive"></i>  Active</span></a><a href="{{route('general-journal.trash')}}" class="btn btn-primary" style="margin-right:5px;" tabindex="0" aria-controls="dt_basic"><span><i class="bx bx-recycle"></i> Recycle Bin ({{$general_journals_count}}) </span> </a></div>';
    $(".btn-tbl" ).prepend(a);
});
</script>


<!-- dashboard init -->
@endsection
