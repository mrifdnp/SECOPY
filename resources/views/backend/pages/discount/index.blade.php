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
                                <label class="control-label col-sm-12" for="status-list">Status</label>
                                <div class="col-sm-12">
                                    <select class="form-control js-example-basic-single select2-type" name="status" id="status">
                                        <option value="" selected="selected">Semua</option>
                                        <option value="active">Sedang Berjalan</option>
                                        <option value="incoming">Mendatang</option>
                                        <option value="done">Selesai</option>
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

    {{-- Modal Create --}}
    <div id="createModal" class="modal fade modal-primary">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header p-3 bg-primary bg-gradient">
                    <h5 class="text-white"><i class="las la-plus-circle"></i> Create {{ $titleHeader->name }}</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form_create" method="POST" action="{{ route('discount.store') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="modal-body">
                        <div class="box-body indoit-form form-horizontal" novalidate>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Name</label>
                                <div class="col-sm-10 w-100"><input type="text" class="form-control" name="name" id="name" placeholder="" size="50" required>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Type</label>
                                <div class="col-sm-10 w-100"><select class="form-control js-example-basic-single select2-type" name="type" required>
                                        <option value="percent">Percent</option>
                                        <option value="nominal">Nominal</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Start date</label>
                                <div class="col-sm-10 w-100"><input type="date" class="form-control" name="start_date" id="start_date" placeholder="" size="50">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">End date</label>
                                <div class="col-sm-10 w-100"><input type="date" class="form-control" name="end_date" id="end_date" placeholder="" size="50">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Value</label>
                                <div class="col-sm-10 w-100"><input type="text" class="form-control" name="value" id="value" placeholder="" size="50">
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer bg-opacity-75">
                        <button type="button" class="btn btn-outline btn-light" data-bs-dismiss="modal">
                            <i class="las la-times-circle"></i> Close
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="las la-save"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Modal Create --}}
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
    <script src="{{ asset('build/js/pages/toast.init.js') }}"></script>

    <script>
        let state = {
            dataTable: null,
        }

        const AjaxHelpers = {
            postRequest: (url, data, processData = true, contentType = false, onSuccess, onError) => {
                $.ajax({
                    url: url,
                    type: 'POST',
                    processData: processData,
                    contentType: contentType,
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: onSuccess,
                    error: onError
                });
            },

            defaultErrorHandler: () => {
                showToast('error', 'Error', 'Terjadi kesalahan dalam proses request.', 'ri-alert-fill');
            }
        }

        const EventListeners = {
            setupAllEvents: () => {
                EventListeners.setupModalSelect2();
                EventListeners.setupInputMask();
                EventListeners.setupDownloadTemplateEvent();
                EventListeners.setupCreateForm();
                EventListeners.setupFilterForm();
                EventListeners.setupDataTable();
                EventListeners.setupDataTableButtonEvents();
            },

            setupInputMask: () => {
                $('.currency').mask('000.000.000.000', {
                    reverse: true
                });
            },

            setupDownloadTemplateEvent: () => {
                $(document).on('click', '#btnDownloadTemplate', function(event) {
                    event.preventDefault();
                    let tabel = $(this).data('tabel');
                    $.ajax({
                        url: "{{ route('get.discountsTemplate') }}",
                        type: "GET",
                        data: {
                            _token: '{{ csrf_token() }}',
                            tabel: tabel
                        },
                        cache: false,
                        dataType: 'json',
                        success: function() {
                            window.location = "{{ route('download.discountsTemplate') }}";
                        }
                    });
                });
            },

            setupCreateForm: () => {
                $('#form_create').on('submit', function(e) {
                    e.preventDefault();
                    let form = this;

                    if (!form.checkValidity()) {
                        $(form).addClass('was-validated');
                        Toastify({
                            text: "Please check your input!",
                            duration: 3000,
                            close: true,
                            gravity: "bottom",
                            position: "right",
                            className: "bg-danger",
                            stopOnFocus: true
                        }).showToast();
                        return;
                    }

                    $(form).removeClass('was-validated');
                    let url = $(form).attr('action');
                    let formData = new FormData(form);

                    AjaxHelpers.postRequest(
                        url,
                        formData,
                        false,
                        false,
                        function() {
                            $('#form_create')[0].reset();
                            state.dataTable.ajax.reload(null, false);
                            showToast('success', 'Berhasil', 'Diskon baru berhasil dibuat!', 'ri-check-fill');

                            // Close modal create
                            $('#createModal').modal('hide');
                        },
                        () => showToast('error', 'Gagal', 'Gagal membuat diskon baru!', 'ri-alert-fill')
                    );
                });
            },

            setupDataTable: () => {
                state.dataTable = new DataTable('#dt_basic', {
                    dom: "<'row'<'col-sm-8 col-md-8 btn-tbl'B><'col-sm-4 col-md-4'<'span dt-right'l>>>" +
                        "<'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    processing: true,
                    serverSide: true,
                    autoWidth: false,
                    scrollX: true,
                    lengthMenu: [
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
                                    $('#form_create')[0].reset();
                                    $('#createModal').modal('show');
                                },
                            },
                        @endif
                        @if (\RuleHelper::UserHasPermission('delete', false))
                            {
                                text: '<i class="las la-trash text-danger"></i> Bulk Delete',
                                className: 'child-button',
                                action: function(e) {
                                    e.preventDefault();
                                    var id_to_be_deleted = $("#dt_basic tbody input:checkbox:checked")
                                        .map(function() {
                                            return $(this).val();
                                        }).get();

                                    AjaxHelpers.postRequest(
                                        "{{ route('discount.delete') }}", {
                                            id_to_be_deleted: id_to_be_deleted,
                                            delete_type: 'softdelete'
                                        },
                                        true,
                                        'application/x-www-form-urlencoded; charset=UTF-8',
                                        function(data) {
                                            $('#myModal').modal('show');
                                            $('#modal-delete').html(data);
                                        },
                                        AjaxHelpers.defaultErrorHandler
                                    );
                                }
                            },
                        @endif {
                            extend: 'collection',
                            text: '<i class="las la-file-excel"></i> Export',
                            className: 'child-button',
                            buttons: [{
                                extend: 'excel',
                                text: 'excel',
                                exportOptions: {
                                    columns: [1, 2, 3]
                                }
                            }]
                        },
                        {
                            extend: 'colvis',
                            text: '<i class="las la-eye"></i> Show',
                            className: 'child-button'
                        },
                        {
                            text: '<i class="las la-filter"></i> Filter',
                            className: 'child-button',
                            action: () => $('#myFilterModal').modal('show')
                        }
                    ],
                    ajax: {
                        url: "{{ route('discount.get_datatable') }}",
                        type: "get",
                        data: function(data) {
                            data.name = $('#name').val();
                            data.type = $('#type option:selected').val();
                            data.status = $('#status option:selected').val();
                            i = data.start + 1;
                        }
                    },
                    columns: [{
                            data: 'checkboxs',
                            orderable: false,
                            searchable: false,
                            width: '20px'
                        },
                        {
                            data: 'number',
                            width: '20px',
                            render: () => i++
                        },
                        {
                            data: 'name'
                        },
                        {
                            data: 'type'
                        },
                        {
                            data: 'start_date'
                        },
                        {
                            data: 'end_date'
                        },
                        {
                            data: 'value'
                        },
                        {
                            data: 'created_at'
                        },
                        {
                            data: 'action',
                            orderable: false,
                            searchable: false,
                            width: '100px'
                        }
                    ]
                });
            },

            setupDataTableButtonEvents: () => {
                $('body').on('click', '#main_checkbox', function() {
                    $('.checkbox_item').prop('checked', $(this).is(':checked'));
                });

                $("#dt_basic tbody").on("click", ".btnDelete", function(e) {
                    e.preventDefault();
                    let id_to_be_deleted = $(this).attr("id");

                    AjaxHelpers.postRequest(
                        "{{ route('discount.delete') }}", {
                            id_to_be_deleted: [id_to_be_deleted],
                            delete_type: 'softdelete'
                        },
                        true,
                        'application/x-www-form-urlencoded; charset=UTF-8',
                        function(data) {
                            $('#myModal').modal('show');
                            $('#modal-delete').html(data);
                        },
                        AjaxHelpers.defaultErrorHandler
                    );
                });

                @if (\RuleHelper::UserHasPermission('trash', false))
                    var a = '<div class="dt-buttons btn-group btn-recycle">' +
                        '<a class="btn btn-primary" href="{{ route('discount.index') }}">' +
                        '<span><i class="fa fa-archive"></i> Active</span></a>' +
                        '<a href="{{ route('discount.trash') }}" class="btn btn-info btn-light" style="margin-right:5px;">' +
                        '<span><i class="bx bx-recycle"></i> Recycle Bin ({{ $discounts_count }})</span></a></div>';
                    $(".btn-tbl").prepend(a);
                @endif

                state.dataTable.buttons(1, null).container()
                    .addClass('btn-group')
                    .appendTo(state.dataTable.table().container());
            },

            setupModalSelect2: () => {
                $(document).on('shown.bs.modal', '.modal', function() {
                    $(this).find('.select2-type').select2();
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
            }
        }

        $(document).ready(function() {
            EventListeners.setupAllEvents();
        });
    </script>

    <!-- dashboard init -->
@endsection
