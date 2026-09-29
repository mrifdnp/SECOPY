@extends('layouts.master')

@section('title')
    Detail {{ $titleHeader->name }}
@endsection

@section('cssAdded')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Systems
        @endslot
        @slot('title')
            Detail {{ $titleHeader->name }}
        @endslot
    @endcomponent

    <div class="row"">
        {{-- Product variant --}}
        <div class="col-lg-3 d-flex">
            <div class="card flex-fill">
                <div class="card-body d-flex flex-column">
                    <p class="text-uppercase fw-medium text-muted text-truncate mb-3">Produk</p>
                    <div class="d-flex flex-grow-1">
                        <!-- Bagian teks -->
                        <div class="d-flex flex-column justify-content-between w-100">
                            <div>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-2">
                                    {{ $productDetail->productWithoutScope?->title ?? $productDetail->product?->title ?? 'Produk Dihapus' }}
                                </h4>
                                <p class="text-muted mb-0">
                                    Varian: {{ $productDetail->variant->name }},
                                    Warna: {{ $productDetail->color->label }}
                                </p>
                                <p class="text-muted">SKU: {{ $productDetail->sku }}</p>
                            </div>
                        </div>

                        <!-- Bagian icon -->
                        <div class="d-flex align-items-end ms-3">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title rounded fs-3" style="background-color: #daf4f0;">
                                    <i class="ri-dropbox-fill text-success"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Toko --}}
        <div class="col-lg-3 d-flex">
            <div class="card flex-fill">
                <div class="card-body d-flex flex-column">
                    <p class="text-uppercase fw-medium text-muted text-truncate mb-3">Toko</p>
                    <div class="d-flex flex-grow-1" style="height: 100%;">
                        <!-- Bagian teks -->
                        <div class="d-flex flex-column justify-content-between w-100">
                            <div>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-2">
                                    {{ $productDetail->store->name }}
                                </h4>
                            </div>
                        </div>
                        <!-- Bagian icon -->
                        <div class="d-flex align-items-end ms-3">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title rounded fs-3" style="background-color: #dff0fa;">
                                    <i class="ri-store-3-fill text-secondary"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stok --}}
        <div class="col-lg-3 d-flex">
            <div class="card flex-fill">
                <div class="card-body d-flex flex-column">
                    <p class="text-uppercase fw-medium text-muted text-truncate mb-3">Stok</p>
                    <div class="d-flex flex-grow-1" style="height: 100%;">
                        <!-- Bagian teks -->
                        <div class="d-flex flex-column justify-content-between w-100">
                            <div>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-2">
                                    {{ $productDetail->qty }}
                                </h4>
                            </div>
                        </div>
                        <!-- Bagian icon -->
                        <div class="d-flex align-items-end ms-3">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title rounded fs-3" style="background-color: #fef4e4;">
                                    <i class="ri-stack-fill text-warning"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Price --}}
        <div class="col-lg-3 d-flex">
            <div class="card flex-fill">
                <div class="card-body d-flex flex-column">
                    <p class="text-uppercase fw-medium text-muted text-truncate mb-3">Harga</p>
                    <div class="d-flex flex-grow-1" style="height: 100%;">
                        <!-- Bagian teks -->
                        <div class="d-flex flex-column justify-content-between w-100">
                            <div>
                                <h4 class="fs-22 fw-semibold ff-secondary mb-2">
                                    {{ 'Rp. ' . number_format($productDetail->price, 0, ',', '.') }}
                                </h4>
                            </div>
                        </div>
                        <!-- Bagian icon -->
                        <div class="d-flex align-items-end ms-3">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title rounded fs-3" style="background-color: #e2e5ed;">
                                    <i class="ri-money-dollar-circle-fill text-primary"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">Perpindahan Stok</h4>
        </div>
        <div class="card-body">
            <table id="stock-movement-table" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Jumlah</th>
                        <th>Tipe</th>
                        <th>Stok Terakhir</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
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
    <script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js') }}"></script>
    <script src="{{ asset('build/js/pages/toast.init.js') }}"></script>

    <script>
        let state = {
            dataTable: null,
        }

        const EventListeners = {
            setupAll: () => {
                EventListeners.setupDataTable();
            },

            setupDataTable: () => {
                state.dataTable = $('#stock-movement-table').DataTable({
                    dom: "<'row'<'col-sm-8 col-md-8 btn-tbl'B><'col-sm-4 col-md-4'<'span dt-right'l>>>" +
                        "<'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: "{{ route('product.productDetailStockMovements', ['id' => $productDetail->product_id, 'detailId' => $productDetail->id]) }}",
                        type: 'GET',
                    },
                    buttons: [],
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },
                        {
                            data: 'qty',
                            name: 'qty'
                        },
                        {
                            data: 'type',
                            name: 'type'
                        },
                        {
                            data: 'last_stock',
                            name: 'last_stock'
                        },
                        {
                            data: 'remark',
                            name: 'remark'
                        },
                    ],
                    order: [
                        [1, 'desc']
                    ],
                    lengthMenu: [10, 25, 50],
                    pageLength: 10,
                });
            },
        }

        $(document).ready(() => {
            EventListeners.setupAll();
        });
    </script>
@endsection
