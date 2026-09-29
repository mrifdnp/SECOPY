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
                                    <th class="th_no" data-hide="phone"><input id="main_checkbox" name="main_checkbox" type="checkbox" value=""></th>
                                    <th class="th_no" data-hide="phone">No</th>
                                    <th data-class="expand">Nama</th>
                                    <th data-class="expand">Tipe</th>
                                    <th data-class="expand">Waktu Mulai</th>
                                    <th data-class="expand">Waktu Berakhir</th>
                                    <th data-class="expand">Nilai Diskon</th>

                                    <th data-hide="phone">Created at</th>
                                    <th>Aksi</th>
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
                                <label class="col-sm-12 control-label">Nama Diskon</label>
                                <div class="col-sm-12">
                                    <input value="" type="text" class="form-control" name="name" id="name" placeholder="" size="40">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="col-sm-12 control-label">Tipe</label>
                                <div class="col-sm-12">
                                    <select class="form-control js-example-basic-single select2-type" name="type" id="type">
                                        <option value="" selected="selected">Semua</option>
                                        <option value="percent">Persentase</option>
                                        <option value="nominal">Nominal</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="col-sm-12 control-label">Status</label>
                                <div class="col-sm-12">
                                    <select class="form-control js-example-basic-single select2-type" name="status" id="status">
                                        <option value="" selected="selected">Semua</option>
                                        <option value="active">Sedang Berjalan</option>
                                        <option value="incoming">Mendatang</option>
                                        <option value="done">Done</option>
                                    </select>
                                </div>
                            </div>
                        </div><!-- /.box-body -->
                    </div>

                    <div class="modal-footer bg-opacity-75">
                        <button type="button" class="btn btn-outline btn-light pull-left" data-bs-dismiss="modal"><i class="las la-times-circle"></i> Close</button>
                        <button type="button" id="btn-reset" onClick="this.form.reset()" class="btn btn-warning pull-left text-light"><i class="las la-undo-alt"></i> Reset</button>
                        <button type="submit" id="btn-filter" class="btn btn-info pull-left text-light"><i class=" las la-share"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- end modal filter --}}
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
    <script src="{{ URL::asset('build/libs/velzon-dependencies/pdfmake-0.1.53/vfs_fonts.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/pdfmake-0.1.53/pdfmake.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jszip-3.1.3/jszip.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-1.2.4/buttons.colVis.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/datatables.net/extensions/export/buttons.bootstrap5.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js') }}"></script>

    <script>
        // State
        let state = {
            dataTable: null,
            rowNumber: 1,
        };

        // Helpers
        const AjaxHelpers = {
            postRequest: function(url, data, onSuccess, onError = this.handleAjaxError) {
                $.ajax({
                    type: "POST",
                    url: url,
                    data: data,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: onSuccess,
                    error: onError,
                });
            },

            handleAjaxError: function(xhr, status, error) {
                console.error("AJAX Error:", error);
                alert("An error occurred: " + error);
            },
        };

        // Event Listeners
        const EventListeners = {
            setupAllEvents: function() {
                this.setupModalSelect2();
                this.setupDataTable();
                this.setupFilterForm();
                this.setupCheckboxEvents();
                this.setupRowActions();
                this.injectCustomButtons();
            },

            setupModalSelect2: () => {
                $(document).on('shown.bs.modal', '.modal', function() {
                    $(this).find('.select2-type').select2();
                });
            },

            setupDataTable: function() {
                state.dataTable = new DataTable("#dt_basic", {
                    dom: "<'row'<'col-sm-8 col-md-8 btn-tbl'B><'col-sm-4 col-md-4'<'span dt-right'l>>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    processing: true,
                    serverSide: true,
                    autoWidth: false,
                    scrollX: true,
                    lengthMenu: [
                        [100, 250, 500, 1000, -1],
                        [100, 250, 500, 1000, "All"],
                    ],
                    order: [
                        [4, "asc"]
                    ],
                    language: {
                        lengthMenu: "Display _MENU_ ",
                    },
                    buttons: [{
                            text: '<i class="las la-undo-alt text-success"></i> Bulk Restore',
                            className: "child-button",
                            action: function(e) {
                                e.preventDefault();
                                let ids = $("#dt_basic tbody input:checkbox:checked")
                                    .map(function() {
                                        return $(this).val();
                                    })
                                    .get();

                                AjaxHelpers.postRequest(
                                    "{{ route('discount.restore') }}", {
                                        id_to_be_restored: ids
                                    },
                                    function(data) {
                                        $("#myModalRestore").modal("show");
                                        $("#modal-restore").html(data);
                                    }
                                );
                            },
                        },
                        {
                            text: '<i class="las la-trash text-danger"></i> Bulk Delete',
                            className: "child-button",
                            action: function(e) {
                                e.preventDefault();
                                let ids = $("#dt_basic tbody input:checkbox:checked")
                                    .map(function() {
                                        return $(this).val();
                                    })
                                    .get();

                                AjaxHelpers.postRequest(
                                    "{{ route('discount.delete') }}", {
                                        id_to_be_deleted: ids,
                                        delete_type: "harddelete"
                                    },
                                    function(data) {
                                        $("#myModal").modal("show");
                                        $("#modal-delete").html(data);
                                    }
                                );
                            },
                        },
                        {
                            extend: "collection",
                            text: '<i class="las la-file-excel"></i> Export',
                            className: "child-button",
                            buttons: [{
                                extend: "excel",
                                text: "excel",
                                exportOptions: {
                                    columns: [1, 2, 3],
                                },
                            }, ],
                        },
                        {
                            text: '<i class="las la-search"></i> Filter',
                            className: "child-button",
                            action: function() {
                                $("#myFilterModal").modal("show");
                            },
                        },
                    ],
                    ajax: {
                        url: "{{ route('discount.get_datatable_trash') }}",
                        type: "get",
                        data: function(data) {
                            data.name = $("#name").val();
                            data.type = $("#type option:selected").val();
                            data.status = $("#status option:selected").val();

                            state.rowNumber = data.start + 1;
                        },
                    },
                    columns: [{
                            data: "checkboxs",
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: "number",
                            render: function() {
                                return state.rowNumber++;
                            },
                        },
                        {
                            data: "name",
                            name: "name"
                        },
                        {
                            data: "type",
                            name: "type"
                        },
                        {
                            data: "start_date",
                            name: "start_date"
                        },
                        {
                            data: "end_date",
                            name: "end_date"
                        },
                        {
                            data: "value",
                            name: "value"
                        },
                        {
                            data: "created_at",
                            name: "created_at"
                        },
                        {
                            data: "action",
                            orderable: false,
                            searchable: false,
                            width: "280px",
                        },
                    ],
                });
            },

            setupFilterForm: () => {
                $('#form_filter').on('submit', function(e) {
                    e.preventDefault();

                    state.dataTable.ajax.reload();
                    $('#myFilterModal').modal('hide');
                });

                $('#btn-reset').click(function() {
                    $('#form_filter')[0].reset();
                    $('#name').val('');
                    $('#type').val('').trigger('change');
                    $('#status').val('').trigger('change');

                    state.dataTable.ajax.reload();
                });
            },

            setupCheckboxEvents: function() {
                $("body").on("click", "#main_checkbox", function() {
                    $(".checkbox_item").prop("checked", $(this).is(":checked"));
                });
            },

            setupRowActions: function() {
                $("#dt_basic tbody").on("click", ".btnDelete", function(e) {
                    e.preventDefault();
                    let id = $(this).attr("id");

                    AjaxHelpers.postRequest(
                        "{{ route('discount.delete') }}", {
                            id_to_be_deleted: [id],
                            delete_type: "harddelete"
                        },
                        function(data) {
                            $("#myModal").modal("show");
                            $("#modal-delete").html(data);
                        }
                    );
                });

                $("#dt_basic tbody").on("click", ".btnRestore", function(e) {
                    e.preventDefault();
                    let id = $(this).attr("id");

                    AjaxHelpers.postRequest(
                        "{{ route('discount.restore') }}", {
                            id_to_be_restored: [id]
                        },
                        function(data) {
                            $("#myModalRestore").modal("show");
                            $("#modal-restore").html(data);
                        }
                    );
                });
            },

            injectCustomButtons: function() {
                let customButtons = `
                <div class="dt-buttons btn-group btn-recycle">
                    <a class="btn btn-info btn-light" href="{{ route('discount.index') }}">
                        <span><i class="fa fa-archive"></i> Active</span>
                    </a>
                    <a href="{{ route('discount.trash') }}" class="btn btn-primary" style="margin-right:5px;">
                        <span><i class="bx bx-recycle"></i> Recycle Bin ({{ $discounts_count }})</span>
                    </a>
                </div>`;
                $(".btn-tbl").prepend(customButtons);
            },
        };

        // Init
        $(document).ready(function() {
            EventListeners.setupAllEvents();
        });
    </script>


    <!-- dashboard init -->
@endsection
