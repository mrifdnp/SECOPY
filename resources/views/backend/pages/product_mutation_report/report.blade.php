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

    <div class="card mb-3">
        <div class="card-header">
            <h4 class="card-title mb-0">Laporan Mutasi Produk</h4>
            <small class="text-muted fst-italic">Buat laporan mutasi produk berdasarkan rentang tanggal, toko, dan tipe mutasi.</small>
        </div>
        <div class="card-body">
            <div class="row">
                {{-- Rentang tanggal --}}
                <div class="col-lg-3">
                    <div>
                        <label for="date-range" class="form-label">Rentang Tanggal</label>
                        <input type="text" class="form-control" id="date-range" data-provider="flatpickr" data-mode="range" data-altFormat="j F, Y" placeholder="Pilih rentang tanggal" />
                    </div>
                </div>

                {{-- Opsi toko --}}
                <div class="col-lg-5">
                    <div>
                        <label for="store" class="form-label">Toko</label>
                        <select class="form-control" multiple data-choices data-choices-search-false data-choices-removeItem id="store">
                            @foreach ($stores as $store)
                                <option selected value="{{ $store->id }}">{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Tipe --}}
                <div class="col-lg-2">
                    <div>
                        <label for="type" class="form-label">Tipe</label>
                        <select class="form-control" data-choices id="type">
                            <option value="all" selected>Semua</option>
                            <option value="in">Masuk</option>
                            <option value="out">Keluar</option>
                        </select>
                    </div>
                </div>

                {{-- Aksi (Preview dan Buat Laporan) --}}
                <div class="col-lg-2 align-self-start">
                    <div>
                        <button type="button" class="btn btn-primary w-100" id="preview-button"><i class="ri-eye-fill me-1 align-bottom"></i> Preview</button>
                        <button type="button" class="btn btn-success w-100 mt-2" id="create-report-button"><i class="ri-file-download-fill me-1 align-bottom"></i> Buat Laporan</button>
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
            <table id="preview-table" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 30px">No</th>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Jumlah</th>
                        <th>Sisa Stok</th>
                        <th>Nama Produk</th>
                        <th>Varian</th>
                        <th>Warna</th>
                        <th>Toko</th>
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
                    // Ambil nilai filter
                    const dateRange = $('#date-range').val();
                    const storeIds = $('#store').val();
                    const type = $('#type').val();

                    // Validasi input
                    if (!dateRange) {
                        showToast('warning', 'Peringatan', 'Silakan pilih rentang tanggal', 'ri-alert-fill');
                        return;
                    }

                    if (!storeIds || storeIds.length === 0) {
                        showToast('warning', 'Peringatan', 'Pilih setidaknya satu toko', 'ri-alert-fill');
                        return;
                    }

                    // Pecah rentang tanggal menjadi tanggal mulai dan tanggal akhir
                    const dates = dateRange.split(' to ');
                    const startDate = dates[0];
                    const endDate = dates[1] || dates[0];

                    // Buat form dinamis untuk POST
                    const form = $('<form>', {
                        method: 'POST',
                        action: "{{ route('product-mutation.export') }}",
                        target: '_blank'
                    });

                    // Tambahkan CSRF token
                    form.append($('<input>', {
                        type: 'hidden',
                        name: '_token',
                        value: '{{ csrf_token() }}'
                    }));

                    form.append($('<input>', {
                        type: 'hidden',
                        name: 'from',
                        value: startDate
                    }));

                    form.append($('<input>', {
                        type: 'hidden',
                        name: 'to',
                        value: endDate
                    }));

                    form.append($('<input>', {
                        type: 'hidden',
                        name: 'store_ids',
                        value: storeIds.join(',')
                    }));

                    form.append($('<input>', {
                        type: 'hidden',
                        name: 'mutation_type',
                        value: type
                    }));

                    // Tambahkan form ke body dan submit
                    $('body').append(form);
                    form.submit();
                    form.remove();
                });
            },

            loadPreviewTable: function() {
                // Ambil nilai filter
                const dateRange = $('#date-range').val();
                const storeIds = $('#store').val();
                const type = $('#type').val();

                // Validasi input
                if (!dateRange) {
                    showToast('warning', 'Peringatan', 'Silakan pilih rentang tanggal', 'ri-alert-fill');
                    return;
                }

                if (!storeIds || storeIds.length === 0) {
                    showToast('warning', 'Peringatan', 'Pilih setidaknya satu toko', 'ri-alert-fill');
                    return;
                }

                // Pecah rentang tanggal menjadi tanggal mulai dan tanggal akhir
                const dates = dateRange.split(' to ');
                const startDate = dates[0];
                const endDate = dates[1] || dates[0]; // Jika hanya satu tanggal, gunakan sebagai tanggal akhir juga

                // Hancurkan DataTable yang sudah ada jika ada
                if (state.dataTable) {
                    state.dataTable.destroy();
                    $('#preview-table tbody').empty(); // Kosongkan tbody untuk menghindari duplikasi data
                }

                // Inisialisasi DataTable dengan parameter filter
                state.dataTable = $('#preview-table').DataTable({
                    dom: "<'row'<'col-sm-8 col-md-8 btn-tbl'B><'col-sm-4 col-md-4'<'span dt-right'l>>>" +
                        "<'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    processing: true,
                    serverSide: true,
                    lengthMenu: [
                        [100, 250, 500],
                        [100, 250, 500]
                    ],
                    ajax: {
                        url: "{{ route('product-mutation.preview') }}",
                        data: {
                            from: startDate,
                            to: endDate,
                            store_ids: storeIds,
                            mutation_type: type,
                        },
                    },
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
                            data: 'mutation_type',
                            name: 'mutation_type'
                        },
                        {
                            data: 'qty',
                            name: 'qty'
                        },
                        {
                            data: 'last_stock',
                            name: 'last_stock'
                        },
                        {
                            data: 'product_name',
                            name: 'product_name'
                        },
                        {
                            data: 'variant',
                            name: 'variant'
                        },
                        {
                            data: 'color',
                            name: 'color'
                        },
                        {
                            data: 'store',
                            name: 'store'
                        },
                        {
                            data: 'remark',
                            name: 'remark'
                        },
                    ],
                    responsive: true,
                })
            },
        }

        $(document).ready(function() {
            EvenListener.setupAll();
        });
    </script>
@endsection
