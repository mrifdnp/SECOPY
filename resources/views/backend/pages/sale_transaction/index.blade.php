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
                                    <th data-class="expand">Date</th>
                                    <th data-class="expand">Invoice id</th>
                                    <th data-class="expand">Sub total</th>
                                    <th data-class="expand">Discount percent</th>
                                    <th data-class="expand">Discount nominal</th>
                                    <th data-class="expand">Additional cost</th>
                                    <th data-class="expand">Total</th>
                                    <th>Action</th>
                                    <th data-class="expand">Payment status</th>

                                    <th data-class="expand">Channel</th>
                                    <th data-hide="phone">Created At</th>
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
                                <label class="col-sm-12 control-label">Date Range</label>
                                <div class="col-sm-12">
                                    <input value="" type="text" class="form-control flatpickr-input" data-provider="flatpickr" data-date-format="Y-m-d" data-range-date="true" name="date" id="date"
                                        placeholder="Pilih rentang tanggal">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="col-sm-12 control-label">Invoice id</label>
                                <div class="col-sm-12">
                                    <input value="" type="text" class="form-control" name="invoice_id"
                                        id="invoice_id" placeholder="Contoh: INV-..." size="40">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="col-sm-12 control-label">Payment Status</label>
                                <div class="col-sm-12">
                                    <select name="payment_status" id="payment_status" class="form-select">
                                        <option value="">Semua Status</option>
                                        <option value="unpaid">Unpaid</option>
                                        <option value="paid">Paid</option>
                                        <option value="shipping">Shipping</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                        <option value="refunded">Refunded</option>
                                    </select>
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

    {{-- modal notify customer --}}
    <div id="modalNotifyCustomer" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-3 bg-primary bg-gradient">
                    <h5 class="modal-title text-white"><i class="las la-envelope"></i> Email Customer</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Metode Pengiriman Pesan</label>
                        <select class="form-select" id="notifyMethod">
                            <option value="wa">WhatsApp</option>
                            <option value="email">Email</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Preview Pesan</label>
                        <textarea class="form-control bg-light" id="notifyPreview" rows="8" readonly></textarea>
                    </div>
                    <input type="hidden" id="notifyPhone">
                    <input type="hidden" id="notifyEmail">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="btnSendNotification"><i class="las la-paper-plane"></i> Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>


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
                                        data-tabel="sale_transactions"><i class="fa fa-download"></i> Download
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


        $('.sub_total').mask('000.000.000', {
            reverse: true
        });
        $('.discount_nominal').mask('000.000.000', {
            reverse: true
        });
        $('.additional_cost').mask('000.000.000', {
            reverse: true
        });
        $('.total').mask('000.000.000', {
            reverse: true
        });
        $('.sub_total').mask('000.000.000', {
            reverse: true
        });
        $('.discount_nominal').mask('000.000.000', {
            reverse: true
        });
        $('.additional_cost').mask('000.000.000', {
            reverse: true
        });
        $('.total').mask('000.000.000', {
            reverse: true
        });

        $('.currency').mask('000.000.000.000', {
            reverse: true
        });

        $(document).on('click', '#btnDownloadTemplate', function() {
            event.preventDefault();
            let tabel = $(this).data('tabel');
            $.ajax({
                url: "{{ route('get.sale_transactionsTemplate') }}",
                type: "GET",
                data: {
                    _token: '{{ csrf_token() }}',
                    tabel: tabel
                },
                cache: false,
                dataType: 'json',
                success: function(data) {
                    var url = "{{ route('download.sale_transactionsTemplate') }}";
                    window.location = url;
                }
            });
        });

        $(document).ready(function() {
            $('#form_create').on('submit', function(e) {
                e.preventDefault();

                let form = $(this);
                let url = form.attr('action');
                let formData = new FormData(this);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#createModal').modal('hide');
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
                    [2, "desc"]
                ],
                language: {
                    "lengthMenu": "Display _MENU_ "
                },
                buttons: [
                    @if (\RuleHelper::UserHasPermission('create', false))
                        {
                            text: '<i class="las la-plus-square text-success"></i> Create',
                            className: 'child-button',
                            init: function(e, dt, button, config) {
                                $(dt).attr('href', "{{ route('sales-transaction.create') }}");
                            },
                            action: function(e, dt, button, config) {
                                window.location = "{{ route('sales-transaction.create') }}"
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
                                    url: "{{ route('sales-transaction.delete') }}",
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
                    "url": "{{ isset($ecommerce_transaction) ? route('ecommerce-transaction.get_datatable') : route('sales-transaction.get_datatable') }}",
                    "type": "get",
                    "data": function(data) {
                        data.date = $('#date').val();
                        data.invoice_id = $('#invoice_id').val();
                        data.payment_status = $('#payment_status').val();

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
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'invoice_id',
                        name: 'invoice_id'
                    },
                    {
                        data: 'sub_total',
                        name: 'sub_total'
                    },
                    {
                        data: 'discount_percent',
                        name: 'discount_percent'
                    },
                    {
                        data: 'discount_nominal',
                        name: 'discount_nominal'
                    },
                    {
                        data: 'additional_cost',
                        name: 'additional_cost'
                    },
                    {
                        data: 'total',
                        name: 'total'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        width: '100px'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },

                    {
                        data: 'channel',
                        name: 'channel'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
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

            $('#modalNotifyCustomer').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var invoice = button.data('invoice');
                var resi = button.data('resi') || '-';
                var courier = button.data('courier') || '-';
                var phone = button.data('phone') || '';
                var email = button.data('email') || '';
                var name = button.data('name') || 'Customer';

                let message = `Halo ${name},\n\nPesanan Anda dengan nomor invoice *${invoice}* sedang dalam pengiriman.\n\nKurir: ${courier}\nNomor Resi: ${resi}\n\nTerima kasih telah berbelanja di Seco!`;

                var modal = $(this);
                modal.find('#notifyPreview').val(message);
                modal.find('#notifyPhone').val(phone);
                modal.find('#notifyEmail').val(email);
            });

            $('#btnSendNotification').click(function() {
                let method = $('#notifyMethod').val();
                let message = encodeURIComponent($('#notifyPreview').val());
                let phone = $('#notifyPhone').val();
                let email = $('#notifyEmail').val();

                if (method === 'wa') {
                    if (!phone) {
                        alert('Nomor WhatsApp customer tidak tersedia.');
                        return;
                    }
                    if (phone.startsWith('0')) {
                        phone = '62' + phone.substring(1);
                    }
                    window.open(`https://wa.me/${phone}?text=${message}`, '_blank');
                } else {
                    if (!email) {
                        alert('Email customer tidak tersedia.');
                        return;
                    }
                    window.open(`mailto:${email}?subject=Informasi Pengiriman Pesanan Seco&body=${message}`);
                }
                $('#modalNotifyCustomer').modal('hide');
            });

            $("#dt_basic tbody").on("click", ".btnDelete", function(e) {
                e.preventDefault();
                var id_to_be_deleted = $(this).attr("id");

                $.ajax({
                    type: "POST",
                    url: "{{ route('sales-transaction.delete') }}",
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
                    '<div class="dt-buttons btn-group btn-recycle"><a class="btn btn-primary" tabindex="0" aria-controls="dt_basic" href="{{ route('sales-transaction.index') }}"><span><i class="fa fa-archive"></i>  Active</span></a><a href="{{ route('sales-transaction.trash') }}" class="btn btn-info btn-light" style="margin-right:5px;" tabindex="0" ><span><i class="bx bx-recycle"></i> Recycle Bin ({{ $sale_transactions_count }}) </span> </a></div>';
                $(".btn-tbl").prepend(a);
            @endif
            table.buttons(1, null).container()
                .addClass('btn-group')
                .appendTo(table.table().container());
        });
    </script>


    <!-- dashboard init -->
@endsection
