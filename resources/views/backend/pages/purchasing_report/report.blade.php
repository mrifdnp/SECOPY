@extends('layouts.master')

@section('title')
    Laporan Pembelian (Purchasing)
@endsection

@section('cssAdded')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Systems
        @endslot
        @slot('title')
            Laporan Pembelian (Purchasing)
        @endslot
    @endcomponent

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
            <div class="card card-animate h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Pembelian</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-3">
                        <div>
                            <h4 id="stat_total_purchases" class="fs-22 fw-semibold ff-secondary mb-3">Rp {{ number_format($total_purchases_rp_month ?? 0, 0, ',', '.') }}</h4>
                            <span class="text-muted fs-13"><i class="ri-money-dollar-circle-line me-1 align-bottom"></i> Total COGM</span>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="ri-wallet-3-line display-6 text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
            <div class="card card-animate h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Barang Masuk</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-3">
                        <div>
                            <h4 id="stat_total_items" class="fs-22 fw-semibold ff-secondary mb-3">{{ number_format($total_items_qty_month ?? 0, 0, ',', '.') }} <span class="fs-14 text-muted fw-normal">Pcs</span></h4>
                            <span class="text-muted fs-13"><i class="ri-shopping-cart-2-line me-1 align-bottom"></i> Kuantitas Fisik</span>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="ri-box-3-line display-6 text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3 mb-md-0">
            <div class="card card-animate h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Faktur</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-3">
                        <div>
                            <h4 id="stat_total_transactions" class="fs-22 fw-semibold ff-secondary mb-3">{{ number_format($total_transactions_month ?? 0, 0, ',', '.') }} <span class="fs-14 text-muted fw-normal">Nota</span></h4>
                            <span class="text-muted fs-13"><i class="ri-file-text-line me-1 align-bottom"></i> Dokumen Pembelian</span>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="ri-file-list-3-line display-6 text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-animate h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Supplier Teraktif</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-3">
                        <div>
                            <h4 id="stat_top_supplier" class="fs-18 fw-semibold ff-secondary mb-3 text-truncate" style="max-width: 150px;" title="{{ $top_supplier_month ?? 'Belum Ada' }}">
                                {{ $top_supplier_month ?? 'Belum Ada' }}
                            </h4>
                            <span class="text-muted fs-13"><i class="ri-store-2-line me-1 align-bottom"></i> Vendor Favorit</span>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="ri-building-4-line display-6 text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h4 class="card-title mb-0">Laporan Pembelian (Purchasing)</h4>
            <small class="text-muted fst-italic">Buat laporan transaksi pembelian aset (restock) berdasarkan rentang tanggal, toko, dan jenis laporan.</small>
        </div>
        <div class="card-body">
            <div class="row">
                {{-- Rentang tanggal --}}
                <div class="col-lg-3">
                    <div>
                        <label for="date-range" class="form-label">Rentang Tanggal</label>
                        <input type="text" class="form-control" id="date-range" data-provider="flatpickr" data-mode="range" data-altFormat="j F, Y" placeholder="Pilih rentang tanggal" value="{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }} to {{ \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}" />
                    </div>
                </div>

                {{-- Opsi toko --}}
                <div class="col-lg-4">
                    <div>
                        <label for="store" class="form-label">Toko Gudang</label>
                        <select class="form-control" multiple data-choices data-choices-search-false data-choices-removeItem id="store">
                            @foreach ($stores as $store)
                                <option selected value="{{ $store->id }}">{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Tipe Laporan --}}
                <div class="col-lg-3">
                    <div>
                        <label for="report_type" class="form-label">Jenis Laporan</label>
                        <select class="form-control" data-choices id="report_type">
                            <option value="total_purchases">Total Purchases Report</option>
                            <option value="report_items">Report Items</option>
                            <option value="purchase_detail" selected>Purchase Detail Report</option>
                        </select>
                    </div>
                </div>

                {{-- Aksi (Preview dan Buat Laporan) --}}
                <div class="col-lg-2 align-self-end">
                    <div>
                        <button type="button" class="btn btn-primary w-100 mb-2" id="preview-button"><i class="ri-eye-fill me-1 align-bottom"></i> Preview</button>
                        <button type="button" class="btn btn-success w-100" id="create-report-button"><i class="ri-file-excel-2-fill me-1 align-bottom"></i> Export Laporan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h4 class="card-title mb-0">Preview Laporan</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="preview-table" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                    <thead id="table-header">
                        <!-- Dynamic Header -->
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-responsive-2.2.9/responsive.bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-responsive-2.2.9/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('build/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('build/js/pages/toast.init.js') }}"></script>

    <script>
        let state = {
            dataTable: null,
        }

        const EvenListener = {
            setupAll: function() {
                EvenListener.setupPreviewButton();
                EvenListener.setupCreateReportButton();
            },

            setupPreviewButton: function() {
                $('#preview-button').on('click', function() {
                    EvenListener.loadPreviewTable();
                });
            },

            setupCreateReportButton: function() {
                $('#create-report-button').on('click', function() {
                    const dateRange = $('#date-range').val();
                    const storeIds = $('#store').val();
                    const reportType = $('#report_type').val();

                    if (!dateRange) {
                        showToast('warning', 'Peringatan', 'Silakan pilih rentang tanggal', 'ri-alert-fill');
                        return;
                    }
                    if (!storeIds || storeIds.length === 0) {
                        showToast('warning', 'Peringatan', 'Pilih setidaknya satu toko', 'ri-alert-fill');
                        return;
                    }

                    const dates = dateRange.split(' to ');
                    const startDate = dates[0];
                    const endDate = dates[1] || dates[0];

                    const form = $('<form>', {
                        action: "{{ route('purchasing-report.export') }}",
                        method: 'POST',
                        style: 'display: none;'
                    });

                    form.append($('<input>', { type: 'hidden', name: '_token', value: '{{ csrf_token() }}' }));
                    form.append($('<input>', { type: 'hidden', name: 'from', value: startDate }));
                    form.append($('<input>', { type: 'hidden', name: 'to', value: endDate }));
                    form.append($('<input>', { type: 'hidden', name: 'store_ids', value: storeIds.join(',') }));
                    form.append($('<input>', { type: 'hidden', name: 'report_type', value: reportType }));

                    $('body').append(form);
                    form.submit();
                    setTimeout(() => form.remove(), 1000);
                });
            },

            loadPreviewTable: function() {
                const dateRange = $('#date-range').val();
                const storeIds = $('#store').val();
                const reportType = $('#report_type').val();

                if (!dateRange) {
                    showToast('warning', 'Peringatan', 'Silakan pilih rentang tanggal', 'ri-alert-fill');
                    return;
                }
                if (!storeIds || storeIds.length === 0) {
                    showToast('warning', 'Peringatan', 'Pilih setidaknya satu toko', 'ri-alert-fill');
                    return;
                }

                const dates = dateRange.split(' to ');
                const startDate = dates[0];
                const endDate = dates[1] || dates[0];

                if (state.dataTable) {
                    state.dataTable.destroy();
                    $('#preview-table tbody').empty();
                    $('#table-header').empty();
                }

                let columnsConfig = [];
                let theadHtml = '<tr><th style="width: 30px">No</th>';

                if (reportType === 'total_purchases') {
                    theadHtml += '<th>Tanggal</th><th>Toko</th><th>Supplier</th><th>Catatan Pembelian</th><th>Kuantitas Masuk</th><th>Total Nilai (Rp)</th></tr>';
                    
                    columnsConfig = [
                        {data: 'DT_RowIndex', name: 'id', orderable: false, searchable: false},
                        {data: 'movement_date', name: 'movement_date'},
                        {data: 'store_name', name: 'stores.name'},
                        {data: 'supplier_name', name: 'parties.name'},
                        {data: 'remark', name: 'remark', searchable: false},
                        {data: 'total_qty', name: 'total_qty', searchable: false},
                        {data: 'total_value', name: 'total_value', searchable: false}
                    ];
                } else if (reportType === 'report_items') {
                    theadHtml += '<th>Toko</th><th>Nama Produk</th><th>SKU</th><th>Kuantitas Dibeli</th><th>Total Nilai (Rp)</th></tr>';
                    
                    columnsConfig = [
                        {data: 'DT_RowIndex', name: 'id', orderable: false, searchable: false},
                        {data: 'store_name', name: 'stores.name'},
                        {data: 'product_name', name: 'products.title'},
                        {data: 'sku', name: 'product_details.sku'},
                        {data: 'total_qty', name: 'total_qty', searchable: false},
                        {data: 'total_value', name: 'total_value', searchable: false}
                    ];
                } else {
                    // purchase_detail
                    theadHtml += '<th>Tanggal</th><th>Toko</th><th>Supplier</th><th>Nama Produk</th><th>Varian</th><th>Warna</th><th>Qty</th><th>COGM</th><th>Subtotal</th></tr>';
                    
                    columnsConfig = [
                        {data: 'DT_RowIndex', name: 'id', orderable: false, searchable: false},
                        {data: 'movement_date', name: 'movement_date'},
                        {data: 'store_name', name: 'stores.name'},
                        {data: 'supplier_name', name: 'parties.name'},
                        {data: 'product_name', name: 'products.title'},
                        {data: 'variant', name: 'product_variants.name'},
                        {data: 'color', name: 'product_colors.label'},
                        {data: 'qty', name: 'qty'},
                        {data: 'cogm', name: 'cogm'},
                        {data: 'subtotal', name: 'subtotal', searchable: false}
                    ];
                }

                $('#table-header').html(theadHtml);

                state.dataTable = $('#preview-table').DataTable({
                    dom: "<'row'<'col-sm-8 col-md-8 btn-tbl'B><'col-sm-4 col-md-4'<'span dt-right'l>>>" +
                        "<'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    processing: true,
                    serverSide: true,
                    lengthMenu: [[25, 50, 100, 250], [25, 50, 100, 250]],
                    ajax: {
                        url: "{{ route('purchasing-report.preview') }}",
                        data: {
                            from: startDate,
                            to: endDate,
                            store_ids: storeIds,
                            report_type: reportType,
                        },
                    },
                    columns: columnsConfig,
                    order: reportType === 'report_items' ? [] : [[1, 'desc']],
                    drawCallback: function(settings) {
                        let json = settings.json;
                        if(json && json.stats) {
                            $('#stat_total_purchases').text('Rp ' + json.stats.total_purchases_rp);
                            $('#stat_total_items').html(json.stats.total_items_qty + ' <span class="fs-14 text-muted fw-normal">Pcs</span>');
                            $('#stat_total_transactions').html(json.stats.total_transactions + ' <span class="fs-14 text-muted fw-normal">Nota</span>');
                            
                            $('#stat_top_supplier')
                                .text(json.stats.top_supplier)
                                .attr('title', json.stats.top_supplier);
                        }
                    }
                });
            }
        };

        $(document).ready(function() {
            EvenListener.setupAll();
            
            // Auto-load data on first open
            setTimeout(() => {
                EvenListener.loadPreviewTable();
            }, 500);
        });
    </script>
@endsection
