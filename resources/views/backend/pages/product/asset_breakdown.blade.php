@extends('layouts.master')
@section('title')
    Rincian Nilai Aset
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Systems
        @endslot
        @slot('title')
            Rincian Nilai Aset
        @endslot
    @endcomponent

    {{-- Summary Cards per Toko --}}
    <div class="row mb-3">
        @php $grandTotal = $assetPerStore->sum('total_asset'); @endphp
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate border-start border-primary border-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Aset (Semua Toko)</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                Rp{{ number_format($grandTotal, 0, ',', '.') }}
                            </h4>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-primary rounded fs-3">
                                <i class="bx bx-wallet text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @foreach ($assetPerStore as $storeAsset)
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">{{ $storeAsset->store_name ?? 'Tanpa Toko' }}</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                Rp{{ number_format($storeAsset->total_asset ?? 0, 0, ',', '.') }}
                            </h4>
                            <span class="text-muted fs-13">{{ number_format($storeAsset->total_qty ?? 0) }} unit &middot; {{ number_format($storeAsset->total_items ?? 0) }} SKU</span>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-soft-success rounded fs-3">
                                <i class="bx bx-store text-success"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Charts Row --}}
    <div class="row mb-3">
        <div class="col-xl-5 col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bx bx-pie-chart-alt-2 me-1"></i> Distribusi Aset per Toko</h5>
                </div>
                <div class="card-body">
                    <div id="donut_chart_store" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-7 col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bx bx-bar-chart me-1"></i> 10 Produk dengan Modal Tertinggi</h5>
                </div>
                <div class="card-body">
                    <div id="bar_chart_top10" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">
                        <i class="bx bx-list-ul me-1"></i> Detail Rincian Aset
                    </h5>
                    <div class="flex-shrink-0">
                        <a href="{{ route('product.index') }}" class="btn btn-sm btn-soft-secondary">
                            <i class="las la-arrow-left"></i> Kembali ke Produk
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="filter_store_id" class="form-label fw-medium">Filter Toko:</label>
                            <select id="filter_store_id" class="form-select">
                                <option value="">Semua Toko</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}" {{ ($activeStoreId ?? '') == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <table id="dt_asset" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width:50px">No</th>
                                <th>Toko</th>
                                <th>Nama Produk</th>
                                <th>SKU</th>
                                <th>Varian</th>
                                <th>Warna</th>
                                <th>Harga Modal (COGM)</th>
                                <th>Qty</th>
                                <th>Total (COGM × Qty)</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr class="table-active fw-bold">
                                <td colspan="8" class="text-center">GRAND TOTAL</td>
                                <td id="grand_total">Rp0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
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
    <script src="{{ URL::asset('build/libs/datatables.net/extensions/export/buttons.bootstrap5.js') }}"></script>
    <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // ========== DONUT CHART PER TOKO ==========
            var donutLabels = {!! json_encode($assetPerStore->pluck('store_name')->map(fn($n) => $n ?? 'Tanpa Toko')->values()) !!};
            var donutValues = {!! json_encode($assetPerStore->pluck('total_asset')->map(fn($v) => (float)$v)->values()) !!};

            var donutOptions = {
                series: donutValues,
                chart: { type: 'donut', height: 350 },
                labels: donutLabels,
                legend: { position: 'bottom' },
                dataLabels: { enabled: true },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return 'Rp' + val.toLocaleString('id-ID');
                        }
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '60%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Grand Total',
                                    formatter: function(w) {
                                        var total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                        return 'Rp' + total.toLocaleString('id-ID');
                                    }
                                }
                            }
                        }
                    }
                },
                colors: ['#405189', '#0ab39c', '#f7b84b', '#f06548', '#299cdb', '#6559cc', '#50c7b4', '#e36397']
            };

            if (donutValues.length > 0) {
                var donutChart = new ApexCharts(document.querySelector("#donut_chart_store"), donutOptions);
                donutChart.render();
            } else {
                document.querySelector("#donut_chart_store").innerHTML = '<div class="text-center text-muted py-5"><i class="bx bx-info-circle fs-1"></i><p>Belum ada data</p></div>';
            }

            // ========== BAR CHART TOP 10 PRODUK ==========
            var barLabels = {!! json_encode($top10Products->pluck('product_name')->values()) !!};
            var barValues = {!! json_encode($top10Products->pluck('total_asset')->map(fn($v) => (float)$v)->values()) !!};

            var barOptions = {
                series: [{
                    name: 'Nilai Aset',
                    data: barValues
                }],
                chart: { type: 'bar', height: 350, toolbar: { show: false } },
                plotOptions: {
                    bar: { horizontal: true, borderRadius: 4, barHeight: '60%' }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        if (val >= 1000000) return 'Rp' + (val / 1000000).toFixed(1) + 'jt';
                        if (val >= 1000) return 'Rp' + (val / 1000).toFixed(0) + 'rb';
                        return 'Rp' + val;
                    },
                    style: { fontSize: '11px' }
                },
                xaxis: {
                    categories: barLabels,
                    labels: {
                        formatter: function(val) {
                            if (val >= 1000000) return 'Rp' + (val / 1000000).toFixed(1) + 'jt';
                            if (val >= 1000) return 'Rp' + (val / 1000).toFixed(0) + 'rb';
                            return 'Rp' + val;
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return 'Rp' + val.toLocaleString('id-ID');
                        }
                    }
                },
                colors: ['#405189']
            };

            if (barValues.length > 0) {
                var barChart = new ApexCharts(document.querySelector("#bar_chart_top10"), barOptions);
                barChart.render();
            } else {
                document.querySelector("#bar_chart_top10").innerHTML = '<div class="text-center text-muted py-5"><i class="bx bx-info-circle fs-1"></i><p>Belum ada data</p></div>';
            }

            // ========== DATATABLE ==========
            var i = 1;

            var table = new DataTable('#dt_asset', {
                dom: "<'row'<'col-sm-8 col-md-8 btn-tbl'B><'col-sm-4 col-md-4'<'span dt-right'l>>>" +
                    "<'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                processing: true,
                serverSide: true,
                autoWidth: false,
                scrollX: true,
                lengthMenu: [
                    [50, 100, 250, 500, -1],
                    [50, 100, 250, 500, "All"]
                ],
                order: [[8, "desc"]],
                language: { "lengthMenu": "Display _MENU_ " },
                buttons: [
                    {
                        extend: 'collection',
                        text: '<i class="las la-file-excel"></i> Export',
                        className: 'child-button',
                        buttons: [
                            { extend: 'excel', text: 'Excel', exportOptions: { columns: [0,1,2,3,4,5,6,7,8] } },
                            { extend: 'print', text: 'Print', exportOptions: { columns: [0,1,2,3,4,5,6,7,8] } }
                        ]
                    },
                ],
                ajax: {
                    url: "{{ route('product.assetBreakdownDatatable') }}",
                    type: "get",
                    data: function(data) {
                        data.store_id = $('#filter_store_id').val();
                        i = data.start + 1;
                    }
                },
                columns: [
                    { data: 'number', name: 'number', width: '50px', orderable: false, searchable: false, render: function() { return i++; } },
                    { data: 'store_name', name: 'stores.name' },
                    { data: 'product_name', name: 'products.title' },
                    { data: 'sku', name: 'product_details.sku' },
                    { data: 'variant_name', name: 'variants.name' },
                    { data: 'color_name', name: 'colors.label' },
                    { data: 'cogm', name: 'product_details.cogm' },
                    { data: 'qty', name: 'product_details.qty' },
                    { data: 'total_asset', name: 'total_asset', orderable: true, searchable: false },
                ],
            });

            // Filter by store
            $('#filter_store_id').on('change', function() {
                table.ajax.reload();
                updateGrandTotal();
            });

            function updateGrandTotal() {
                var storeId = $('#filter_store_id').val();
                var query = storeId ? '?store_id=' + storeId : '';
                $.ajax({
                    url: "{{ url('/product/asset-breakdown') }}" + query,
                    type: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-Grand-Total': '1' },
                    success: function(data) {
                        if (data.grand_total !== undefined) {
                            $('#grand_total').text(data.grand_total);
                        }
                    }
                });
            }

            // Initial grand total
            updateGrandTotal();
        });
    </script>
@endsection
