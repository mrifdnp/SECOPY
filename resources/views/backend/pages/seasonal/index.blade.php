@extends('layouts.master')
@section('title')
    List {{ $titleHeader->name }}
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Systems
        @endslot
        @slot('title')
            Data {{ $titleHeader->name }}
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <form action="#" method="post">
                <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                <div class="card">
                    <div class="card-body">
                        @include('backend.includes.flash')
                        <table id="dt_basic" class="table table-bordered nowrap table-striped align-middle"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th class="th_no"><input id="main_checkbox" name="main_checkbox" type="checkbox" value=""></th>
                                    <th class="th_no">No</th>
                                    <th>Banner</th>
                                    <th>Nama</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Berakhir</th>
                                    <th>Produk</th>
                                    <th>Status</th>
                                    <th>Created At</th>
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
    </div>

    <div id="myModal" class="modal fade modal-primary">
        <div class="modal-dialog">
            <div class="modal-content" id="modal-delete" style="border: none !important;">
            </div>
        </div>
    </div>

    {{-- modal filter --}}
    <div id="myFilterModal" class="modal fade modal-primary">
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
                                <label class="col-sm-12 control-label">Nama</label>
                                <div class="col-sm-12">
                                    <input value="" type="text" class="form-control" name="name" id="name" placeholder="" size="40">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-opacity-75">
                        <button type="button" class="btn btn-outline btn-light pull-left" data-bs-dismiss="modal"><i class="las la-times-circle"></i> Close</button>
                        <button type="button" id="btn-reset" onClick="this.form.reset()" class="btn btn-warning pull-left text-light"><i class="las la-undo-alt"></i> Reset</button>
                        <button type="button" id="btn-filter" class="btn btn-info pull-left text-light"><i class="las la-share"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-responsive-2.2.9/responsive.bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.dataTables.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-responsive-2.2.9/dataTables.responsive.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/dataTables.buttons.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.print.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.html5.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/pdfmake-0.1.53/vfs_fonts.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/pdfmake-0.1.53/pdfmake.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jszip-3.1.3/jszip.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-1.2.4/buttons.colVis.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/datatables.net/extensions/export/buttons.bootstrap5.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let table = new DataTable('#dt_basic', {
                dom: "<'row'<'col-sm-8 col-md-8 btn-tbl'B><'col-sm-4 col-md-4'<'span dt-right'l>>>" +
                    "<'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                processing: true,
                serverSide: true,
                autoWidth: false,
                scrollX: true,
                "lengthMenu": [
                    [100, 250, 500, 1000, -1],
                    [100, 250, 500, 1000, "All"]
                ],
                order: [
                    [4, "asc"]
                ],
                language: {
                    "lengthMenu": "Display _MENU_ "
                },
                buttons: [
                    @if (\RuleHelper::UserHasPermission('create', false))
                        {
                            text: '<i class="las la-plus-square text-success"></i> Create',
                            className: 'child-button',
                            action: function() {
                                window.location.href = "{{ route('seasonal.create') }}";
                            },
                        },
                    @endif
                    @if (\RuleHelper::UserHasPermission('delete', false))
                        {
                            text: '<i class="las la-trash text-danger"></i> Bulk Delete',
                            className: 'child-button',
                            action: function(e, dt, button, config) {
                                e.preventDefault();
                                var id_to_be_deleted = $("#dt_basic tbody input:checkbox:checked")
                                    .map(function() {
                                        return $(this).val();
                                    }).get();

                                $.ajax({
                                    type: "POST",
                                    url: "{{ route('seasonal.delete') }}",
                                    data: {
                                        id_to_be_deleted: id_to_be_deleted,
                                        delete_type: 'softdelete'
                                    },
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    success: function(data) {
                                        $('#myModal').modal('show');
                                        $('#modal-delete').html(data);
                                    }
                                });
                            }
                        },
                    @endif
                    {
                        extend: 'colvis',
                        text: '<i class="las la-eye"></i> Show',
                        className: 'child-button'
                    },
                    {
                        text: '<i class="las la-filter"></i> Filter',
                        className: 'child-button',
                        action: function(e, dt, button, config) {
                            $('#myFilterModal').modal('show');
                        }
                    },
                ],
                ajax: {
                    "url": "{{ route('seasonal.get_datatable') }}",
                    "type": "get",
                    "data": function(data) {
                        data.name = $('#name').val();
                        i = data.start + 1;
                    }
                },
                columns: [
                    { data: 'checkboxs', name: 'checkboxs', orderable: false, searchable: false, width: '20px' },
                    { data: 'number', name: 'number', width: '20px', render: function() { return i++; } },
                    { data: 'banner', name: 'banner', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'start_date', name: 'start_date' },
                    { data: 'end_date', name: 'end_date' },
                    { data: 'products_count', name: 'products_count', orderable: false, searchable: false },
                    { data: 'is_active', name: 'is_active' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, width: '100px' }
                ],
            });

            $('#btn-filter').click(function() { table.ajax.reload(); });
            $('#btn-reset').click(function() { table.ajax.reload(); });

            $('body').on('click', '#main_checkbox', function() {
                if ($(this).is(':checked')) {
                    $('.checkbox_item').prop('checked', true);
                } else {
                    $('.checkbox_item').prop('checked', false);
                }
            });

            $("#dt_basic tbody").on("click", ".btnDelete", function(e) {
                e.preventDefault();
                var id_to_be_deleted = $(this).attr("id");
                $.ajax({
                    type: "POST",
                    url: "{{ route('seasonal.delete') }}",
                    data: {
                        id_to_be_deleted: [id_to_be_deleted],
                        delete_type: 'softdelete'
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        $('#myModal').modal('show');
                        $('#modal-delete').html(data);
                    }
                });
            });

            @if (\RuleHelper::UserHasPermission('trash', false))
                var a = '<div class="dt-buttons btn-group btn-recycle"><a class="btn btn-primary" tabindex="0" aria-controls="dt_basic" href="{{ route('seasonal.index') }}"><span><i class="fa fa-archive"></i>  Active</span></a><a href="{{ route('seasonal.trash') }}" class="btn btn-info btn-light" style="margin-right:5px;" tabindex="0"><span><i class="bx bx-recycle"></i> Recycle Bin ({{ $seasonals_count }}) </span></a></div>';
                $(".btn-tbl").prepend(a);
            @endif

            table.buttons(1, null).container()
                .addClass('btn-group')
                .appendTo(table.table().container());
        });
    </script>
@endsection
