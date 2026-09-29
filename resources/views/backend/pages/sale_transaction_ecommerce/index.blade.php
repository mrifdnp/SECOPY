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

                        @if(isset($xendit_days_inactive) && $xendit_days_inactive >= 76)
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <strong>⚠️ Peringatan Xendit Dorman!</strong> Sudah <b>{{ $xendit_days_inactive }} hari</b> tidak ada transaksi di Ecommerce. Batas maksimal sebelum akun Xendit menjadi dorman adalah 90 hari (tersisa <b>{{ 90 - $xendit_days_inactive }} hari</b> lagi). Segera lakukan setidaknya 1 transaksi agar akun tidak dibekukan.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        <table id="dt_basic" class="table table-bordered nowrap table-striped align-middle"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th class="th_no" data-hide="phone"><input id="main_checkbox" name="main_checkbox"
                                            type="checkbox" value=""></th>
                                    <th class="th_no" data-hide="phone">No</th>
                                    <th data-class="expand">Date</th>
                                    <th data-class="expand">Invoice id</th>
                                    <th data-class="expand">Customer</th>
                                    <th data-class="expand">Kurir</th>
                                    <th data-class="expand">Total</th>
                                    <th data-class="expand">Payment status</th>

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

    <!-- Modal Edit Payment Status -->
    <div id="editPaymentModal" class="modal fade modal-primary">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-3 bg-primary bg-gradient">
                    <h5 class="text-white"><i class="ri-edit-box-line"></i> Edit Payment Status</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form_edit_payment" method="post">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" name="id" id="payment_id">

                        <div class="form-group mb-3">
                            <label for="payment_status" class="form-label">Payment Status</label>
                            <select class="form-control select2" name="payment_status" id="payment_status_edit" required>
                                <option value="paid">Paid</option>
                                <option value="shipping">Shipping</option>
                                <option value="completed">Completed</option>
                                <option value="canceled">Cancel</option>
                            </select>
                        </div>

                        <div class="form-group mb-3" id="resi_group" style="display:none;">
                            <label for="resi" class="form-label">Nomor Resi</label>
                            <input type="text" class="form-control" name="resi" id="resi_edit"
                                placeholder="Masukkan nomor resi">
                        </div>

                        <div class="form-group mb-3">
                            <label for="sale_people_id" class="form-label">Sales Person</label>
                            <select class="form-control select2" name="sale_people_id" id="sale_people_id_edit" required>
                                <option value="">-- Pilih Sales --</option>
                                @foreach($sale_people as $sp)
                                    <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer bg-opacity-75">
                        <button type="button" class="btn btn-outline btn-light" data-bs-dismiss="modal">
                            <i class="las la-times-circle"></i> Close
                        </button>
                        <button type="submit" class="btn btn-primary text-light">
                            <i class="las la-save"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                            <option value="email">Email</option>
                            <option value="wa">WhatsApp</option>
                        </select>
                    </div>
                    <div class="mb-3" id="emailTypeContainer">
                        <label class="form-label">Jenis Email (Untuk Testing)</label>
                        <select class="form-select" id="notifyEmailType">
                            <option value="shipped">Pesanan Dikirim</option>
                            <option value="received">Pesanan Diterima</option>
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
                                <label class="col-sm-12 control-label">Date</label>
                                <div class="col-sm-12">
                                    <input value="" type="text" class="form-control" name="date"
                                        id="date" placeholder="" size="40">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="col-sm-12 control-label">Invoice id</label>
                                <div class="col-sm-12">
                                    <input value="" type="text" class="form-control" name="invoice_id"
                                        id="invoice_id" placeholder="" size="40">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="col-sm-12 control-label">Total</label>
                                <div class="col-sm-12">
                                    <input value="" type="text" class="form-control" name="total"
                                        id="total" placeholder="" size="40">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="col-sm-12 control-label">Kurir</label>
                                <div class="col-sm-12">
                                    <input value="" type="text" class="form-control" name="courier"
                                        id="courier" placeholder="" size="40">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="col-sm-12 control-label">Payment status</label>
                                <div class="col-sm-12">
                                    <input value="" type="text" class="form-control" name="payment_status"
                                        id="payment_status" placeholder="" size="40">
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
        $(document).ready(function () {
            // Cek parameter dari URL untuk auto-filter
            const urlParams = new URLSearchParams(window.location.search);
            const statusParam = urlParams.get('payment_status');
            if (statusParam) {
                $('#payment_status').val(statusParam);
            }

            $('#payment_status_edit').select2({
                width: '100%',
                minimumResultsForSearch: Infinity
            });
            $('#sale_people_id_edit').select2({
                width: '100%',
                dropdownParent: $('#editPaymentModal')
            });
        });
    </script>
    <script>
        $('body').on('click', '.btnEditPayment', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            let status = $(this).data('status');
            let resi = $(this).data('resi');
            let salePeopleId = $(this).data('sale-people-id');

            $('#payment_id').val(id);
            $('#payment_status_edit').val(status);
            $('#sale_people_id_edit').val(salePeopleId || '').trigger('change');

            if (status === 'shipping') {
                $('#resi_group').show();
                $('#resi_edit').val(resi ?? '');
            } else {
                $('#resi_group').hide();
                $('#resi_edit').val('');
            }

            $('#editPaymentModal').modal('show');
        });

        $('#payment_status_edit').on('change', function() {
            let val = $(this).val();
            if (val === 'shipping') {
                $('#resi_group').show();
            } else {
                $('#resi_group').hide();
            }
        });


        $('#form_edit_payment').on('submit', function(e) {
            e.preventDefault();
            let id = $('#payment_id').val();
            let url = "{{ url('ecommerce-transaction/update-payment') }}/" + id;

            $.ajax({
                url: url,
                type: 'PUT',
                data: $(this).serialize(),
                success: function(response) {
                    $('#editPaymentModal').modal('hide');
                    $('.dataTable').DataTable().ajax.reload(null, false);
                    showAlert('success', 'Payment status updated!');
                },
                error: function() {
                    showAlert('danger', 'Failed to update payment status');
                }
            });
        });
    </script>
    <script>
        function showAlert(type, message) {
            $('#alert-container').html(`
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `);
        }


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
                    [4, "asc"]
                ],
                language: {
                    "lengthMenu": "Display _MENU_ "
                },
                buttons: [
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
                                    url: "{{ route('ecommerce-transaction.delete') }}",
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
                    "url": "{{ route('ecommerce-transaction.get_datatable') }}",
                    "type": "get",
                    "data": function(data) {
                        const urlParams = new URLSearchParams(window.location.search);
                        data.date = $('#date').val() || urlParams.get('filter_month');
                        data.invoice_id = $('#invoice_id').val();
                        data.total = $('#total').val();
                        data.channel = $('#channel').val();
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
                        data: 'customer',
                        name: 'customer'
                    },
                    {
                        data: 'courier',
                        name: 'courier'
                    },
                    {
                        data: 'total',
                        name: 'total'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
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
            $('#notifyMethod').change(function() {
                if ($(this).val() === 'wa') {
                    $('#emailTypeContainer').hide();
                    $('#notifyPhone').parent().show();
                    $('#notifyEmail').parent().hide();
                } else {
                    $('#emailTypeContainer').show();
                    $('#notifyPhone').parent().hide();
                    $('#notifyEmail').parent().show();
                }
            });

            $('#modalNotifyCustomer').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                buttonSource = button;
                var invoice = button.data('invoice');
                var resi = button.data('resi');
                var courier = button.data('courier');
                var phone = button.data('phone');
                var email = button.data('email');
                
                var text = `Halo Kak,\n\nPesanan kakak dengan invoice *${invoice}* telah kami kirimkan menggunakan kurir *${courier}* dengan nomor resi *${resi}*.\n\nSilakan lacak pesanan kakak melalui link berikut:\nhttps://cekresi.com/?noresi=${resi}\n\nTerima kasih telah berbelanja di Seco by Seken Living!`;
                
                var modal = $(this);
                modal.find('#notifyPreview').val(text);
                modal.find('#notifyPhone').val(phone);
                modal.find('#notifyEmail').val(email);
                
                if (modal.find('#notifyMethod').val() === 'wa') {
                    modal.find('#emailTypeContainer').hide();
                } else {
                    modal.find('#emailTypeContainer').show();
                }
            });

            $('#btnSendNotification').click(function() {
                let btn = $(this);
                let method = $('#notifyMethod').val();
                let emailType = $('#notifyEmailType').val();
                let message = $('#notifyPreview').val();
                let phone = $('#notifyPhone').val();
                let email = $('#notifyEmail').val();

                if (method === 'wa') {
                    if (!phone) {
                        Swal.fire('Oops...', 'Nomor WhatsApp customer tidak tersedia.', 'error');
                        return;
                    }
                    if (phone.startsWith('0')) {
                        phone = '62' + phone.substring(1);
                    }
                    window.open(`https://wa.me/${phone}?text=${encodeURIComponent(message)}`, '_blank');
                    $('#modalNotifyCustomer').modal('hide');
                } else {
                    if (!email) {
                        Swal.fire('Oops...', 'Email customer tidak tersedia.', 'error');
                        return;
                    }

                    btn.html('<i class="las la-spinner la-spin"></i> Mengirim...');
                    btn.prop('disabled', true);

                    $.ajax({
                        url: "{{ route('ecommerce-transaction.send_email') }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            invoice: buttonSource.data('invoice'),
                            resi: buttonSource.data('resi'),
                            courier: buttonSource.data('courier'),
                            email: email,
                            email_type: emailType,
                            name: buttonSource.data('name') || 'Customer'
                        },
                        success: function(response) {
                            try {
                                if (typeof Toastify !== 'undefined') {
                                    Toastify({
                                        text: response.message || 'Email terkirim',
                                        duration: 3000,
                                        close: true,
                                        gravity: "top",
                                        position: "right",
                                        className: "bg-success",
                                    }).showToast();
                                } else if (typeof showAlert !== 'undefined') {
                                    showAlert('success', response.message || 'Email terkirim');
                                } else {
                                    alert('Berhasil! ' + (response.message || 'Email terkirim'));
                                }
                                $('#modalNotifyCustomer').modal('hide');
                            } catch (e) {
                                console.error(e);
                            }
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Server Error';
                            try {
                                if (typeof Toastify !== 'undefined') {
                                    Toastify({
                                        text: 'Terjadi kesalahan: ' + msg,
                                        duration: 3000,
                                        close: true,
                                        gravity: "top",
                                        position: "right",
                                        className: "bg-danger",
                                    }).showToast();
                                } else if (typeof showAlert !== 'undefined') {
                                    showAlert('danger', 'Terjadi kesalahan: ' + msg);
                                } else {
                                    alert('Gagal! ' + msg);
                                }
                            } catch (e) {
                                console.error(e);
                            }
                        },
                        complete: function() {
                            btn.html('<i class="las la-paper-plane"></i> Lanjutkan');
                            btn.prop('disabled', false);
                        }
                    });
                }
            });

            $("#dt_basic tbody").on("click", ".btnDelete", function(e) {
                e.preventDefault();
                var id_to_be_deleted = $(this).attr("id");

                $.ajax({
                    type: "POST",
                    url: "{{ route('ecommerce-transaction.delete') }}",
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
                    '<div class="dt-buttons btn-group btn-recycle"><a class="btn btn-primary" tabindex="0" aria-controls="dt_basic" href="{{ route('ecommerce-transaction.index') }}"><span><i class="fa fa-archive"></i>  Active</span></a><a href="{{ route('ecommerce-transaction.trash') }}" class="btn btn-info btn-light" style="margin-right:5px;" tabindex="0" ><span><i class="bx bx-recycle"></i> Recycle Bin ({{ $sale_transactions_count }}) </span> </a></div>';
                $(".btn-tbl").prepend(a);
            @endif
            table.buttons(1, null).container()
                .addClass('btn-group')
                .appendTo(table.table().container());
        });
    </script>


    <!-- dashboard init -->
@endsection
