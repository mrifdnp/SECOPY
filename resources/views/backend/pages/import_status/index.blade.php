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
                        <table id="dt_basic" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="th_no"><input id="main_checkbox" name="main_checkbox" type="checkbox" value=""></th>
                                    <th class="th_no">No</th>
                                    <th data-class="expand">Task </th>
                                    <th data-class="expand">Status</th>
                                    <th data-class="expand">Data Sukses </th>
                                    <th data-class="expand">Data Gagal</th>
                                    <th data-class="expand">Total Data</th>
                                    <th data-class="expand">Created at</th>
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

    <div id="myModal"class="modal fade modal-primary">
        <div class="modal-dialog">
            <div class="modal-content" id="modal-delete" style="border: none !important;">

            </div>
        </div>
    </div>
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-responsive-2.2.9/responsive.bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.dataTables.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-bootstrap-theme-0.1.0-beta.10/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-responsive-2.2.9/dataTables.responsive.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/dataTables.buttons.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.print.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.html5.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-1.2.4/buttons.colVis.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/datatables.net/extensions/export/buttons.bootstrap5.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js') }}"></script>
    <script src="{{ asset('build/js/pages/toast.init.js') }}"></script>

    <script>
        let state = {
            dataTable: null
        }

        const EventListeners = {
            setupAll: () => {
                EventListeners.setupDataTable();
                EventListeners.setupDataTableContentEvents();
            },

            setupDataTable: () => {
                state.dataTable = new DataTable('#dt_basic', {
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
                        [7, "desc"] // Order by created_at descending
                    ],
                    language: {
                        "lengthMenu": "Display _MENU_ "
                    },
                    ajax: {
                        "url": "{{ route('import_status.datatable') }}",
                        "type": "get",
                        "data": function(data) {
                            data.name = $('#name').val();
                            data.status = $('#status').val();
                            data.date_from = $('#date_from').val();
                            data.date_to = $('#date_to').val();
                        }
                    },
                    buttons: [
                        @if (\RuleHelper::UserHasPermission('delete', false))
                            {
                                text: '<i class="las la-trash text-danger"></i> Bulk Delete',
                                className: 'child-button',
                                action: function(e, dt, button, config) {
                                    var id_to_be_deleted = $("#dt_basic tbody input:checkbox:checked")
                                        .map(function() {
                                            return $(this).val();
                                        }).get();

                                    $.ajax({
                                        type: "POST",
                                        url: "{{ route('import_status.delete') }}",
                                        data: {
                                            id_to_be_deleted: id_to_be_deleted,
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
                            }, {
                                text: '<i class="ri-brush-2-fill"></i> Clear',
                                className: 'child-button',
                                action: function(e, dt, button, config) {
                                    $.ajax({
                                        type: "DELETE",
                                        url: "{{ route('import_status.clear') }}",
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        success: function(data) {
                                            // Refresh the DataTable
                                            state.dataTable.ajax.reload(null, false);
                                        }
                                    });
                                }
                            },
                        @endif
                    ],
                    columns: [{
                            data: 'checkboxs',
                            name: 'checkboxs',
                            orderable: false,
                            searchable: false,
                            width: '20px'
                        },
                        {
                            data: null,
                            name: 'number',
                            width: '20px',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {
                            data: 'task',
                            name: 'task'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'success_row',
                            name: 'success_row',
                            className: 'text-center'
                        },
                        {
                            data: 'failed_row',
                            name: 'failed_row',
                            className: 'text-center'
                        },
                        {
                            data: 'total_row',
                            name: 'total_row',
                            className: 'text-center'
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
                            width: '100px',
                            className: 'text-center'
                        }
                    ],
                    "drawCallback": function(settings) {
                        var api = this.api();
                        var start = api.page.info().start;
                        api.column(1, {
                            search: 'applied',
                            order: 'applied'
                        }).nodes().each(function(cell, i) {
                            cell.innerHTML = start + i + 1;
                        });
                    }
                });
            },

            setupDataTableContentEvents: () => {
                // Handle main checkbox
                $('#main_checkbox').on('click', function() {
                    if (this.checked) {
                        $('.checkbox_item').each(function() {
                            this.checked = true;
                        });
                    } else {
                        $('.checkbox_item').each(function() {
                            this.checked = false;
                        });
                    }
                });

                // Handle individual checkboxes
                $(document).on('change', '.checkbox_item', function() {
                    if ($('.checkbox_item:checked').length == $('.checkbox_item').length) {
                        $('#main_checkbox').prop('checked', true);
                    } else {
                        $('#main_checkbox').prop('checked', false);
                    }
                });

                $("#dt_basic tbody").on("click", ".btnDelete", function(e) {
                    e.preventDefault();
                    var id_to_be_deleted = $(this).attr("id");

                    $.ajax({
                        type: "POST",
                        url: "{{ route('import_status.delete') }}",
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
            }
        }

        $(document).ready(function() {
            EventListeners.setupAll();
        });
    </script>
@endsection
