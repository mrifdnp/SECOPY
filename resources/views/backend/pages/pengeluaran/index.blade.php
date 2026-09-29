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
                                    <th data-class="expand">Tanggal</th>
                                    <th data-class="expand">Nama Akun</th>
                                    <th data-class="expand">Remark</th>
                                    <th data-class="expand">Nominal</th>
                                    {{-- <th>Action</th> --}}
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
                                        <option value="" selected> Silakan Pilih </option>
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

    <div id="myPaymentModal" class="modal fade modal-primary">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="modal-filter">
                <div class="modal-header p-3 bg-primary bg-gradient">
                    <h5 class="text-white"><i class="ri-secure-payment-fill"></i> Pengeluaran</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formPayment" name="formPayment" action="{{ route('pengeluaran.store') }}" method="post"
                    novalidate>
                    @csrf
                    <div class="modal-body">
                        <div class="box-body indoit-form form-horizontal">

                            <div class="form-group mb-3">
                                <label for="" class="col-sm-3 control-label">Tanggal</label>
                                <div class="col-sm-12">
                                    {{-- <input type="date" class="form-control" id="date" name="date"> --}}
                                    <input type="date" id="date" name="date" class="form-control "
                                        placeholder="Pilih Tanggal Pengeluaran" required>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="pemasukanke" class="col-md-12">Keluar dari </label>
                                <div class="form-group">
                                    <select class="form-control js-example-basic-single" name="paymentMethod"
                                        id="paymentMethod" required>
                                        <option value="">Pilih Akun Pengeluaran</option>
                                        @foreach ($head_accounts_payment as $key => $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="head_accounts_id" class="col-md-12">Jenis Pengeluaran</label>
                                <div class="form-group">
                                    <select class="form-control js-example-basic-single" name="head_accounts_id"
                                        id="head_accounts_id" required>
                                        <option value="">Pilih Jenis Pengeluaran</option>
                                        @foreach ($head_accounts as $key => $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="total_pemasukan" class="col-md-12">Total Pemasukan</label>
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">Rp.</span>
                                        <input type="text" class="form-control" id="total_pengeluaran"
                                            name="total_pengeluaran" placeholder="Total Pengeluaran" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="remark" class="col-md-12">Remark</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="ri ri-file-edit-line"></i></span>
                                    <input type="text" class="form-control" name="remark" id="remark"
                                        placeholder="Remark/Catatan">
                                </div>
                            </div>

                            <div class="alert alert-danger" id="validasi-alert" style="display: none"> <i
                                    class="las la-times-circle"></i> Semua isian form harus di isi terlebih dahulu.</div>


                        </div>
                    </div>
                    <div class="modal-footer bg-opacity-75">
                        <button type="button" class="btn btn-outline btn-light pull-left" data-bs-dismiss="modal">
                            <i class="las la-times-circle"></i> Close
                        </button>
                        <button type="submit" id="btn-create" class="btn btn-primary pull-left text-light">
                            <i class="las la-save"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
@endsection

@section('script')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    <script src="{{ URL::asset('build/libs/datatables.net/extensions/export/buttons.bootstrap5.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ URL::asset('build/js/pages/select2.init.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.10/jquery.mask.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            // Inisialisasi Select2 saat modal ditampilkan
            $('#myFilterModal').on('shown.bs.modal', function () {
                $('#head_accounts_id').select2({
                    placeholder: "Silakan Pilih",
                    width: '100%', // Pastikan lebar dropdown sesuai
                    dropdownParent: $('#myFilterModal') // Agar dropdown tampil dalam modal
                });
            });
        });

        $(document).ready(function () {
            // Inisialisasi ulang Select2 setiap kali modal dibuka
            $('#myPaymentModal').on('shown.bs.modal', function () {
                $('.js-example-basic-single').select2({
                    dropdownParent: $('#myPaymentModal') // Mengatur modal sebagai parent dropdown
                });
            });
        });


        $(function () {
            $('#datetimepicker-date').datetimepicker({
                format: 'YYYY-MM-DD HH:mm:ss'
            });
        });

        $('#total_pengeluaran').mask('000.000.000', { reverse: true });

        document.addEventListener('DOMContentLoaded', function () {
            let table = new DataTable('#dt_basic',
                {
                    dom: "<'row'<'col-sm-8 col-md-8 btn-tbl'B><'col-sm-4 col-md-4'<'span dt-right'l>>>" +
                        "<'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    processing: true,
                    serverSide: true,
                    autoWidth: false,
                    scrollX: true,
                    "lengthMenu": [[100, 250, 500, 1000, -1], [100, 250, 500, 1000, "All"]],
                    order: [[2, "ASC"]],
                    language: {
                        "lengthMenu": "Display _MENU_ "
                    },
                    buttons: [
                        @if(\RuleHelper::UserHasPermission('create', false))
                                {
                                text: '<i class="las la-plus-square text-success"></i> Create',
                                className: 'child-button',
                                action: function (e, dt, button, config) {
                                    $('#myPaymentModal').modal('show');
                                }
                            },
                        @endif
                        {
                            extend: 'collection',
                            text: '<i class="las la-file-excel"></i> Export',
                            className: 'child-button',
                            buttons: [
                                {
                                    extend: 'excel',
                                    text: 'excel',
                                    exportOptions: {
                                        columns: [2, 3, 4, 5]
                                    }
                                }
                            ]
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
                        "url": "{{route('pengeluaran.get_datatable')}}",
                        "type": "get",
                        "data": function (data) {
                            data.date = $("#rentang_report").val();
                            data.head_accounts_id = $('#head_accounts_id option:selected').val();
                            i = data.start + 1;
                        }
                    },
                    columns: [
                        {
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
                            data: 'account_name',
                            name: 'account_name'
                        },
                        {
                            data: 'remark',
                            name: 'remark'
                        },
                        {
                            data: 'nominal',
                            name: 'nominal',
                            orderable: false,
                            searchable: false,
                            width: '100px'
                        },

                    ],

                });
            $('#btn-filter').click(function () { //button filter event click
                table.ajax.reload();  //just reload table
            });
            $('#btn-reset').click(function () {
                // Reset form
                $('#form_filter')[0].reset();

                // Reset Select2
                $('#head_accounts_id').val(null).trigger('change');

                // Reload DataTable
                table.ajax.reload();
            });
            $('body').on('click', '#main_checkbox', function () {
                if ($(this).is(':checked')) {
                    $('.checkbox_item').prop('checked', true);
                }
                else {
                    $('.checkbox_item').prop('checked', false);
                }
            });
            $("#dt_basic tbody").on("click", ".btnDelete", function (e) {
                e.preventDefault();
                var id_to_be_deleted = $(this).attr("id");

                $.ajax({
                    type: "POST",
                    url: "{{route('pengeluaran.delete')}}",
                    data: {
                        id_to_be_deleted: [id_to_be_deleted],
                        delete_type: 'softdelete'
                    },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (data) {
                        $('#myModal').modal('show');
                        $('#modal-delete').html(data);
                    }
                });
            });

            table.buttons(1, null).container()
                .addClass('btn-group')
                .appendTo(table.table().container());
        });
        $(document).ready(function () {
            $('#formPayment').on('submit', function (e) {
                e.preventDefault();

                let formData = $(this).serialize();

                // Reset error feedback
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                $.ajax({
                    url: "{{ route('pengeluaran.store') }}",
                    type: "POST",
                    data: formData,
                    success: function (response) {
                        // Jika berhasil, redirect atau tampilkan pesan sukses
                        window.location.href = "{{ route('pengeluaran.index') }}";
                    },
                    error: function (response) {
                        // Tampilkan error pada field yang gagal divalidasi
                        let errors = response.responseJSON?.errors;

                        if (errors) {
                            let errorMessage = "";
                            if (errors.message) {
                                errorMessage = errors.message; // Error dari Exception message (misal: Periode Closed)
                            } else {
                                // Error mandatory check form Validator
                                errorMessage = "Semua isian form harus di isi dengan benar:<br>";
                                for (let key in errors) {
                                    errorMessage += "• " + errors[key][0] + "<br>";
                                }
                            }

                            $('#validasi-alert').html('<i class="las la-times-circle"></i> ' + errorMessage).show();

                            setTimeout(function () {
                                $('#validasi-alert').hide();
                            }, 5000);
                        }
                    }
                });
            });
        });

    </script>


    <!-- dashboard init -->
@endsection