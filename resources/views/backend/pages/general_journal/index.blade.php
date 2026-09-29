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
                        <table id="dt_basic" class="table table-bordered align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="th_no" data-hide="phone"><input id="main_checkbox" name="main_checkbox"
                                            type="checkbox" value=""></th>
                                    <th class="th_no" data-hide="phone">No</th>
                                    <th data-class="expand">Tanggal</th>
                                    <th data-class="expand">Nama Akun</th>
                                    <th data-class="expand">Debit</th>
                                    <th data-class="expand">Kredit</th>
                                    @if (\RuleHelper::UserHasPermission('update', false))
                                        <th data-hide="phone">Dibuat pada</th>
                                    @endif
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
                            <div class="form-group">
                                <label for="" class="col-sm-3 control-label">Tanggal</label>
                                <div class="col-sm-12">
                                    {{-- <input type="date" class="form-control" id="date" name="date"> --}}
                                    <input type="text" id="rentang_report" name="rentang"
                                        class="form-control flatpickr-input active" data-provider="flatpickr"
                                        data-date-format="d-m-Y" data-range-date="true" placeholder="Pilih periode laporan"
                                        required>
                                </div>
                            </div>
                            <br />
                            <div class="form-group">
                                <label for="" class="col-sm-3 control-label">Jenis Akun</label>
                                <div class="col-sm-12">
                                    <select class="form-control select2" name="head_accounts_id" id="head_accounts_id"
                                        style="width: 100%;">
                                        <option value="">Silahkan Pilih</option>
                                        @foreach ($head_accounts as $head_account)
                                            <option value="{{ $head_account->id }}">{{ $head_account->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <br />
                        </div><!-- /.box-body -->
                    </div>

                    <div class="modal-footer bg-opacity-75">
                        <button type="button" class="btn btn-outline btn-light pull-left" data-bs-dismiss="modal"><i
                                class="las la-times-circle"></i> Close</button>
                        <button type="button" id="btn-reset" class="btn btn-warning pull-left text-light"><i
                                class="las la-undo-alt"></i> Semua Data</button>
                        <button type="button" id="btn-filter" class="btn btn-info pull-left text-light"><i
                                class=" las la-share"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- end modal filter --}}
    <div id="importModal" class="modal fade modal-primary">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-3 bg-primary bg-gradient">
                    <h5 class="text-white"><i class="ri-upload-2-line"> </i> Import Jurnal Umum</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-steps">
                        <div class="step-arrow-nav mb-4">
                            <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#pane-template"
                                        type="button">Template</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pane-rules"
                                        type="button">Peraturan</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pane-upload"
                                        type="button">Upload</button>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="pane-template" role="tabpanel">
                                <div class="d-flex flex-column align-items-center justify-content-center text-center"
                                    style="min-height: 200px;">
                                    <i class="ri-file-excel-2-fill text-success" style="font-size: 4rem;"></i>
                                    <p class="mt-2">Download template Excel dengan daftar akun otomatis.</p>

                                    <a href="{{ route('general-journal.download_template') }}" class="btn btn-success">
                                        <i class="ri-download-2-line"></i> Download Template Dropdown
                                    </a>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="pane-rules" role="tabpanel">
                                @if (!is_null($import_template ?? null))
                                    <p>Ikuti aturan kolom berikut agar data berhasil diimport:</p>
                                    <table class="table table-sm table-bordered">
                                        @foreach ($import_template->rules as $rule)
                                            <tr>
                                                <th class="bg-light">{{ $rule['column'] }}</th>
                                                <td>{{ $rule['rule'] }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @endif
                            </div>

                            <div class="tab-pane fade" id="pane-upload" role="tabpanel">
                                <div class="d-flex flex-column align-items-center justify-content-center text-center"
                                    style="min-height: 200px;">
                                    <i class="ri-upload-2-fill text-info" style="font-size: 4rem;"></i>
                                    <form action="{{ route('import.general_journal') }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="template_id" value="{{ $import_template->id }}">
                                        <input type="file" name="file" class="form-control mb-2" accept=".xlsx, .csv">
                                        <button class="btn btn-primary w-100">Upload</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- form import -->
    <form id="form_import" name="form_import" action="" method="post">
        <div id="modalImport" class="modal fade modal-primary">
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
                                        data-tabel="general_journals"><i class="fa fa-download"></i> Download
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
    <input type="hidden" id="hidden_all" name="all" value="">
    <!-- end form import -->
    {{-- @include('layouts.modal_delete') --}}
@endsection


@section('css')
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet"
        type="text/css" />
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css"
        integrity="sha512-kq3FES+RuuGoBW3a9R2ELYKRywUEQv0wvPTItv3DSGqjpbNtGWVdvT8qwdKkqvPzT93jp8tSF4+oN4IeTEIlQA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.colVis.min.js"></script>
    {{--
    <script src="{{ URL::asset('build/libs/datatables.net/extensions/export/buttons.bootstrap.min.js')}}"></script> --}}
    <script src="{{ URL::asset('build/libs/datatables.net/extensions/export/buttons.bootstrap5.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ URL::asset('build/js/pages/select2.init.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.10/jquery.mask.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            flatpickr("#rentang_report", {
                mode: "range",
                dateFormat: "d-m-Y"
            });
        });

        $('#debit').mask('000.000.000', {
            reverse: true
        });
        $('#kredit').mask('000.000.000', {
            reverse: true
        });


        $(document).on('click', '#btnDownloadTemplate', function () {
            event.preventDefault();
            let tabel = $(this).data('tabel');
            $.ajax({
                url: "{{ route('get.general_journalsTemplate') }}",
                type: "GET",
                data: {
                    _token: '{{ csrf_token() }}',
                    tabel: tabel
                },
                cache: false,
                dataType: 'json',
                success: function (data) {
                    var url = "{{ route('download.general_journalsTemplate') }}";
                    window.location = url;
                }
            });
        });

        $(document).ready(function () {
            // Inisialisasi Select2 saat modal ditampilkan
            $('#myFilterModal').on('shown.bs.modal', function () {
                $('#head_accounts_id').select2({
                    placeholder: "Pilih Jenis Akun",
                    width: '100%', // Pastikan lebar dropdown sesuai
                    dropdownParent: $('#myFilterModal') // Agar dropdown tampil dalam modal
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
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
                    [2, "ASC"]
                ],
                language: {
                    "lengthMenu": "Display _MENU_ "
                },
                buttons: [
                    @if (\RuleHelper::UserHasPermission('create', false))
                                                    {
                            text: '<i class="las la-plus-square text-success"></i> Create',
                            className: 'child-button',
                            init: function (e, dt, button, config) {
                                $(dt).attr('href', "{{ route('general-journal.create') }}");
                            },
                            action: function (e, dt, button, config) {
                                window.location = "{{ route('general-journal.create') }}"
                            },
                        },
                    @endif
                    @if (\RuleHelper::UserHasPermission('delete', false))
                                                    {
                            text: '<i class="las la-trash text-danger"></i> Bulk Delete',
                            className: 'child-button',
                            action: function (e, dt, button, config) {
                                e.preventDefault();

                                var id_to_be_deleted = $("#dt_basic tbody input:checkbox:checked")
                                    .map(function () {
                                        return $(this).val();
                                    }).get();

                                $.ajax({
                                    type: "POST",
                                    url: "{{ route('general-journal.delete') }}",
                                    data: {
                                        id_to_be_deleted: id_to_be_deleted,
                                        delete_type: 'softdelete'
                                    },
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                            'content')
                                    },
                                    success: function (data) {
                                        $('#myModal').modal('show');
                                        $('#modal-delete').html(data);
                                    }
                                });
                            }
                        },
                    @endif{
                        text: '<i class="ri-upload-2-fill"></i> Import',
                        className: 'child-button',
                        action: function (e, dt, button, config) {
                            $('#importModal').modal('show'); // Memanggil modal tabbed import
                        }
                    },
                    {
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
                        action: function (e, dt, button, config) {
                            $('#myFilterModal').modal('show');
                        }
                    },
                ],
                ajax: {
                    "url": "{{ route('general-journal.get_datatable') }}",
                    "type": "get",
                    "data": function (data) {
                        data.date = $("#rentang_report").val();
                        data.head_accounts_id = $('#head_accounts_id option:selected').val();
                        data.all = $("#hidden_all").val();
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
                    render: function () {
                        return i++;
                    }
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'head_account',
                    name: 'head_account'
                },
                {
                    data: 'debit',
                    name: 'debit'
                },
                {
                    data: 'kredit',
                    name: 'kredit'
                },
                    @if (\RuleHelper::UserHasPermission('update', false))
                                                                {
                            data: 'created_at',
                            name: 'created_at'
                        },
                    @endif

                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    width: '100px'
                }
                ],
                drawCallback: function () {
                    var api = this.api();
                    var lastGroup = null;
                    var colorIndex = 0;
                    var colors = ['#ffffff', '#d4d4d4'];
                    api.rows({ page: 'current' }).every(function () {
                        var data = this.data();
                        var group = (data.date || '').split('<br>')[0].trim();
                        if (group !== lastGroup) {
                            colorIndex++;
                            lastGroup = group;
                        }
                        $(this.node()).css('background-color', colors[colorIndex % 2]);
                    });
                },
            });
            $('#btn-filter').click(function () { //button filter event click
                table.ajax.reload(); //just reload table
            });
            $('#btn-reset').click(function () {
                // Reset form
                $('#form_filter')[0].reset();

                // Reset Select2
                $('#head_accounts_id').val(null).trigger('change');
            });

            $("#btn-reset").on("click", function () {
                $("#hidden_all").val("all"); // Set value input hidden
                $("#rentang_report").val(""); // Kosongkan input tanggal
                $("#head_accounts_id").val("").trigger("change"); // Reset dropdown
                table.ajax.reload(); // Reload DataTables
            });

            $('body').on('click', '#main_checkbox', function () {
                if ($(this).is(':checked')) {
                    $('.checkbox_item').prop('checked', true);
                } else {
                    $('.checkbox_item').prop('checked', false);
                }
            });
            $("#dt_basic tbody").on("click", ".btnDelete", function (e) {
                e.preventDefault();
                var id_to_be_deleted = $(this).attr("id");

                $.ajax({
                    type: "POST",
                    url: "{{ route('general-journal.delete') }}",
                    data: {
                        id_to_be_deleted: [id_to_be_deleted],
                        delete_type: 'softdelete'
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data) {
                        $('#myModal').modal('show');
                        $('#modal-delete').html(data);
                    }
                });
            });
            @if (\RuleHelper::UserHasPermission('trash', false))
                var a =
                    '<div class="dt-buttons btn-group btn-recycle"><a class="btn btn-primary" tabindex="0" aria-controls="dt_basic" href="{{ route('general-journal.index') }}"><span><i class="fa fa-archive"></i>  Active</span></a><a href="{{ route('general-journal.trash') }}" class="btn btn-info btn-light" style="margin-right:5px;" tabindex="0" ><span><i class="bx bx-recycle"></i> Recycle Bin ({{ $general_journals_count }}) </span> </a></div>';
                $(".btn-tbl").prepend(a);
            @endif
            table.buttons(1, null).container()
                .addClass('btn-group')
                .appendTo(table.table().container());
        });
    </script>


    <!-- dashboard init -->
@endsection