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
                    {{-- <div class="card-header d-sm-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0"> <i class="ri-pages-line"></i> Basic Datatables</h5>
                @slot('li_1') Tables @endslot
                @slot('title')Datatables @endslot
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Master</a></li>
                        <li class="breadcrumb-item active">Datatables</li>
                    </ol>
                </div>
            </div> --}}
                    <div class="card-body">
                        @include('backend.includes.flash')
                        <table id="dt_basic" class="table table-bordered nowrap table-striped align-middle"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th class="th_no" data-hide="phone"><input id="main_checkbox" name="main_checkbox"
                                            type="checkbox" value=""></th>
                                    <th class="th_no" data-hide="phone">No</th>
                                    <th data-class="expand">Title</th>
                                    <th data-class="expand">Date start</th>
                                    <th data-class="expand">Date end</th>
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
                                <label class="col-sm-12 control-label">Title</label>
                                <div class="col-sm-12">
                                    <input value="" type="text" class="form-control" name="title" id="title"
                                        placeholder="" size="40">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Date start</label>
                                <div class="col-sm-10 w-100"><input type="date" class="form-control"
                                        name="date_start" id="date_start" placeholder="" size="50" required>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Date end</label>
                                <div class="col-sm-10 w-100"><input type="date" class="form-control" name="date_end"
                                        id="date_end" placeholder="" size="50" required>
                                </div>
                            </div>

                        </div><!-- /.box-body -->
                    </div>

                    <div class="modal-footer bg-opacity-75">
                        <button type="button" class="btn btn-outline btn-light pull-left" data-bs-dismiss="modal"><i
                                class="las la-times-circle"></i> Close</button>
                        <button type="button" id="btn-reset" onClick="this.form.reset()"
                            class="btn btn-warning pull-left text-light"><i class="las la-undo-alt"></i> Reset</button>
                        <button type="button" id="btn-filter" class="btn btn-info pull-left text-light"><i
                                class=" las la-share"></i> Filter</button>
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
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="form_create" method="POST" action="{{ route('stockopname.store') }}"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="modal-body">
                        <div class="box-body indoit-form form-horizontal" novalidate>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Title</label>
                                <div class="col-sm-10 w-100"><input type="text" class="form-control" name="title"
                                        id="title" placeholder="" size="50" required>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Date start</label>
                                <div class="col-sm-10 w-100"><input type="date" class="form-control"
                                        name="date_start" id="date_start" placeholder="" size="50" required>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Date end</label>
                                <div class="col-sm-10 w-100"><input type="date" class="form-control" name="date_end"
                                        id="date_end" placeholder="" size="50" required>
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

    {{-- Modal Edit --}}
    <div id="editModal" class="modal fade modal-primary">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header p-3 bg-primary bg-gradient">
                    <h5 class="text-white"><i class="las la-edit"></i> Edit {{ $titleHeader->name }}</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="form_edit" method="POST" action="" enctype="multipart/form-data"
                    class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="box-body indoit-form form-horizontal">
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Title</label>
                                <div class="col-sm-10 w-100"><input type="text" class="form-control" name="title"
                                        id="title" placeholder="" size="50" required>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Date start</label>
                                <div class="col-sm-10 w-100"><input type="date" class="form-control"
                                        name="date_start" id="date_start" placeholder="" size="50" required>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Date end</label>
                                <div class="col-sm-10 w-100"><input type="date" class="form-control" name="date_end"
                                        id="date_end" placeholder="" size="50" required>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Store id</label>
                                <div class="col-sm-10 w-100"><select class="form-control js-example-basic-single select2"
                                        name="store_id" required>
                                        @foreach ($stores as $store)
                                            {
                                            <option value="{{ $store->id }}">{{ $store->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline btn-light" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- End Modal Edit --}}

    <!-- form import -->
    <form id="form_import" name="form_import" action="" method="post">
        <div id="modalImport"class="modal fade modal-primary">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="fa fa-info-circle"> </i> Import Data</h4>
                    </div>
                    <div class="modal-body">
                        <div class="box-body indoit-form form-horizontal">
                            <div class="col-md-2"></div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <button class="btn btn-sm btn-success" id="btnDownloadTemplate"
                                        data-tabel="stockopnames"><i class="fa fa-download"></i> Download
                                        template</button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <button class="btn btn-sm btn-info" id="btnImportData"><i class="fa fa-upload"></i>
                                        Import Data</button>
                                </div>
                            </div>
                            <div class="col-md-2"></div>
                        </div><!-- /.box-body -->
                    </div>
                    <div class="modal-footer">
                        <button id="btn-reset" onClick="this.form.reset()" type="button"
                            class="btn btn-outline pull-left">Reset</button>
                        <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- end form import -->
    {{-- @include('layouts.modal_delete') --}}
@endsection


@section('css')
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/dataTables.bootstrap5.min.css') }}"
        rel="stylesheet" type="text/css" />
    <link
        href="{{ URL::asset('build/libs/velzon-dependencies/datatables-responsive-2.2.9/responsive.bootstrap.min.css') }}"
        rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.dataTables.min.css') }}"
        rel="stylesheet" type="text/css" />
    <link
        href="{{ URL::asset('build/libs/velzon-dependencies/select2-bootstrap-theme-0.1.0-beta.10/select2-bootstrap.min.css') }}"
        rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.css') }}" rel="stylesheet"
        type="text/css" />
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/dataTables.bootstrap5.min.js') }}">
    </script>
    <script
        src="{{ URL::asset('build/libs/velzon-dependencies/datatables-responsive-2.2.9/dataTables.responsive.min.js') }}">
    </script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/dataTables.buttons.min.js') }}">
    </script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.print.min.js') }}">
    </script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.html5.min.js') }}">
    </script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/pdfmake-0.1.53/vfs_fonts.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/pdfmake-0.1.53/pdfmake.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jszip-3.1.3/jszip.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-1.2.4/buttons.colVis.min.js') }}">
    </script>
    <script src="{{ URL::asset('build/libs/datatables.net/extensions/export/buttons.bootstrap5.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js') }}"></script>



    <script>
        function showAlert(type, message) {
            $('#alert-container').html(`
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `);
        }

        $(function() {
            $('#datepicker-date_start').datepicker({
                autoclose: true
            })
        });
        $(function() {
            $('#datepicker-date_end').datepicker({
                autoclose: true
            })
        });


        $('.currency').mask('000.000.000.000', {
            reverse: true
        });

        $(document).on('click', '#btnDownloadTemplate', function() {
            event.preventDefault();
            let tabel = $(this).data('tabel');
            $.ajax({
                url: "{{ route('get.stockopnamesTemplate') }}",
                type: "GET",
                data: {
                    _token: '{{ csrf_token() }}',
                    tabel: tabel
                },
                cache: false,
                dataType: 'json',
                success: function(data) {
                    var url = "{{ route('download.stockopnamesTemplate') }}";
                    window.location = url;
                }
            });
        });

        $(document).ready(function() {
            $('#form_create').on('submit', function(e) {
                e.preventDefault();

                let form = $(this)[0]; // native DOM element
                if (!form.checkValidity()) {
                    // Tambahkan class Bootstrap agar muncul pesan error
                    $(form).addClass('was-validated');

                    Toastify({
                        text: "Please check your input!",
                        duration: 3000,
                        close: true,
                        gravity: "bottom", // "top" or "bottom"
                        position: "right", // "left", "center" or "right"
                        className: "bg-danger",
                        stopOnFocus: true
                    }).showToast();

                    return; // stop jika form tidak valid
                }

                // Jika valid, hapus class error (opsional)
                $(form).removeClass('was-validated');

                let url = $(form).attr('action');
                let formData = new FormData(this);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Toastify({
                            text: "Data saved successfully!",
                            duration: 3000,
                            close: true,
                            gravity: "bottom", // "top" or "bottom"
                            position: "right", // "left", "center" or "right"
                            className: "bg-success",
                            stopOnFocus: true
                        }).showToast();

                        $('#form_create')[0].reset();
                        $('.dataTable').DataTable().ajax.reload(null,
                        false); // reload tanpa reset pagination
                        showAlert('success', 'Data saved successfully!');
                    },
                    error: function() {
                        showAlert('danger', 'Failed to save data');
                    }
                });
            });
        });

        $('body').on('click', '.btnEdit', function(e) {
            e.preventDefault();
            let id = $(this).data('id');

            $.ajax({
                url: "{{ route($url . '.edit', '') }}/" + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    let data = response.data;

                    $.each(data, function(key, value) {
                        if (key === 'password') return;

                        let $form = $('#form_edit'); // scope form
                        let $fields = $form.find(`[name="${key}"], [name="${key}[]"]`);

                        if ($form.find(`input[name="${key}[]"]`).length) {
                            $form.find(`input[name="${key}[]"]`).prop('checked', false);
                            if (value) {
                                value.split(',').forEach(function(v) {
                                    $form.find(
                                            `input[name="${key}[]"][value="${v.trim()}"]`
                                            )
                                        .prop('checked', true);
                                });
                            }
                        }
                        // Radio button
                        else if ($form.find(`input[type="radio"][name="${key}"]`).length) {
                            $form.find(`input[type="radio"][name="${key}"][value="${value}"]`)
                                .prop('checked', true);
                        }
                        // Select (termasuk Select2)
                        else if ($form.find(`select[name="${key}"]`).length) {
                            $form.find(`select[name="${key}"]`).val(value).trigger('change');
                        }
                        // Input biasa (skip file input)
                        else {
                            let $input = $form.find(`[name="${key}"]`);
                            if ($input.attr('type') !== 'file') {
                                $input.val(value);
                            }
                        }
                    });

                    $('#form_edit').data('id', id);
                    $('#editModal').modal('show');
                },
                error: function() {
                    showAlert('danger', 'Gagal mengambil data.');
                }
            });
        });

        $('#form_edit').on('submit', function(e) {
            e.preventDefault();

            let form = $(this)[0]; // native DOM element
            if (!form.checkValidity()) {
                // Tambahkan class Bootstrap agar muncul pesan error
                $(form).addClass('was-validated');

                Toastify({
                    text: "Please check your input!",
                    duration: 3000,
                    close: true,
                    gravity: "bottom", // "top" or "bottom"
                    position: "right", // "left", "center" or "right"
                    backgroundColor: "#ff4d4d",
                    className: "bg-danger",
                    stopOnFocus: true
                }).showToast();

                return; // stop jika form tidak valid
            }

            // Jika valid, hapus class error (opsional)
            $(form).removeClass('was-validated');

            let formData = new FormData(this);
            let id = $(this).data('id');
            let actionUrl = $(this).attr('action');
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            // Spoofing method PUT
            formData.append('_method', 'PUT');

            $.ajax({
                url: '/{{ $url }}/' + id,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {

                    Toastify({
                        text: "Data saved successfully!",
                        duration: 3000,
                        close: true,
                        gravity: "bottom", // "top" or "bottom"
                        position: "right", // "left", "center" or "right"
                        className: "bg-success",
                        stopOnFocus: true
                    }).showToast();


                    $('.dataTable').DataTable().ajax.reload(null, false);
                    showAlert('success', 'Data berhasil diperbarui!');
                },
                error: function() {
                    showAlert('danger', 'Gagal update data.');
                }
            });
        });


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
                                $('#form_create')[0].reset();
                                $('#createModal').modal('show');
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
                                    url: "{{ route('stockopname.delete') }}",
                                    data: {
                                        id_to_be_deleted: id_to_be_deleted,
                                        delete_type: 'softdelete'
                                    },
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                            'content')
                                    },
                                    success: function(data) {
                                        $('#myModal').modal('show');
                                        $('#modal-delete').html(data);
                                    }
                                });
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
                        action: function(e, dt, button, config) {
                            $('#myFilterModal').modal('show');
                        }
                    },
                ],
                ajax: {
                    "url": "{{ route('stockopname.get_datatable') }}",
                    "type": "get",
                    "data": function(data) {
                        data.title = $('#title').val();
                        data.date_start = $('#datepicker').val();
                        data.date_end = $('#datepicker').val();

                        i = data.start + 1;
                    }
                },
                columns: [{
                        data: 'checkboxs',
                        name: 'checkboxs',
                        orderable: false,
                        searchable: false,
                        width: '20px'
                    },
                    {
                        data: 'number',
                        name: 'number',
                        width: '20px',
                        render: function() {
                            return i++;
                        }
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'date_start',
                        name: 'date_start'
                    },
                    {
                        data: 'date_end',
                        name: 'date_end'
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
                        width: '100px'
                    }
                ],

            });
            $('#btn-filter').click(function() { //button filter event click
                table.ajax.reload(); //just reload table
            });
            $('#btn-reset').click(function() { //button reset event click
                table.ajax.reload(); //just reload table
            });
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
                    url: "{{ route('stockopname.delete') }}",
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
                var a =
                    '<div class="dt-buttons btn-group btn-recycle"><a class="btn btn-primary" tabindex="0" aria-controls="dt_basic" href="{{ route('stockopname.index') }}"><span><i class="fa fa-archive"></i>  Active</span></a><a href="{{ route('stockopname.trash') }}" class="btn btn-info btn-light" style="margin-right:5px;" tabindex="0" ><span><i class="bx bx-recycle"></i> Recycle Bin ({{ $stockopnames_count }}) </span> </a></div>';
                $(".btn-tbl").prepend(a);
            @endif
            table.buttons(1, null).container()
                .addClass('btn-group')
                .appendTo(table.table().container());
        });

        $('.modal').on('shown.bs.modal', function() {
            $(this).find('.js-example-basic-single').select2({
                dropdownParent: $(this)
            });
        });
    </script>


    <!-- dashboard init -->
@endsection
