@extends('layouts.master')
@section('title')
    {{ $page_title }}
@endsection

@section('cssAdded')
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-responsive-2.2.9/responsive.bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.dataTables.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Systems
        @endslot
        @slot('title')
            {{ $page_title }}
        @endslot
    @endcomponent
    <div class="row mb-3">
        <!-- Revenue -->
        <div class="col-xl-3 col-md-4 col-sm-6 mb-3">
            <div class="card card-animate h-100 mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Omzet Kotor</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h5 class="fs-16 fw-semibold ff-secondary mb-0">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        </div>
        @if(in_array(session()->get('current_role')->name, ['super', 'admin']))
        <!-- HPP -->
        <div class="col-xl-3 col-md-4 col-sm-6 mb-3">
            <div class="card card-animate h-100 mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total HPP</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h5 class="fs-16 fw-semibold ff-secondary mb-0">Rp {{ number_format($totalCogs, 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <!-- Diskon -->
        <div class="col-xl-3 col-md-4 col-sm-6 mb-3">
            <div class="card card-animate h-100 mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Beban Diskon</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h5 class="fs-16 fw-semibold ff-secondary mb-0 text-danger">Rp {{ number_format($totalDiskon, 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <!-- Laba Kotor -->
        <div class="col-xl-3 col-md-4 col-sm-6 mb-3">
            <div class="card card-animate bg-success-subtle h-100 mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-success text-truncate mb-0">Laba Kotor</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h5 class="fs-16 fw-semibold ff-secondary mb-0 {{ $totalLabaKotor >= 0 ? 'text-success' : 'text-danger' }}">
                            Rp {{ number_format($totalLabaKotor, 0, ',', '.') }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Pemakaian Internal -->
        <div class="col-xl-3 col-md-4 col-sm-6 mb-3">
            <div class="card card-animate h-100 mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Pemakaian Internal</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h5 class="fs-16 fw-semibold ff-secondary mb-0 text-danger">Rp {{ number_format($totalBebanUsed, 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <!-- Kerugian Rusak -->
        <div class="col-xl-3 col-md-4 col-sm-6 mb-3">
            <div class="card card-animate h-100 mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Rugi Brg Rusak</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h5 class="fs-16 fw-semibold ff-secondary mb-0 text-danger">Rp {{ number_format($totalKerugianRusak, 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <!-- Laba Bersih -->
        <div class="col-xl-3 col-md-4 col-sm-6 mb-3">
            <div class="card card-animate bg-primary-subtle h-100 mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-primary text-truncate mb-0">Laba Bersih</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h5 class="fs-16 fw-semibold ff-secondary mb-0 {{ $totalLabaBersih >= 0 ? 'text-primary' : 'text-danger' }}">
                            Rp {{ number_format($totalLabaBersih, 0, ',', '.') }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-0">
                    <div class="row g-4">
                          <div class="col-sm-auto w-100">
                                  <form id="filter-form" action="{{ route('profit-loss-report.index') }}" method="GET" class="d-flex gap-2 align-items-center flex-wrap">
                                      @csrf
                                      <select class="form-select" name="report_type" id="report_type" style="min-width: 150px;">
                                          <option value="per_transaction" {{ request('report_type') == 'per_item' ? '' : 'selected' }}>Laporan Per Transaksi</option>
                                          <option value="per_item" {{ request('report_type') == 'per_item' ? 'selected' : '' }}>Laporan Per Item</option>
                                      </select>
                                      
                                      <select class="form-select" name="store_id" id="store_id" style="min-width: 150px;">
                                          <option value="all" {{ ($selectedStore ?? 'all') == 'all' ? 'selected' : '' }}>Semua Toko</option>
                                          @foreach($stores as $store)
                                              <option value="{{ $store->id }}" {{ ($selectedStore ?? 'all') == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                                          @endforeach
                                      </select>
                                      
                                      <select class="form-select" name="channel" id="channel" style="min-width: 150px;">
                                          <option value="all" {{ ($selectedChannel ?? 'all') == 'all' ? 'selected' : '' }}>Semua Channel</option>
                                          <option value="offline" {{ ($selectedChannel ?? 'all') == 'offline' ? 'selected' : '' }}>Offline / POS</option>
                                          <option value="shopee" {{ ($selectedChannel ?? 'all') == 'shopee' ? 'selected' : '' }}>Shopee</option>
                                          <option value="tokopedia" {{ ($selectedChannel ?? 'all') == 'tokopedia' ? 'selected' : '' }}>Tokopedia</option>
                                          <option value="tiktok" {{ ($selectedChannel ?? 'all') == 'tiktok' ? 'selected' : '' }}>TikTok Shop</option>
                                          <option value="lazada" {{ ($selectedChannel ?? 'all') == 'lazada' ? 'selected' : '' }}>Lazada</option>
                                          <option value="whatsapp" {{ ($selectedChannel ?? 'all') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                      </select>
                                      
                                      <input type="text" class="form-control" name="daterange" id="daterange" data-provider="flatpickr" data-mode="range" data-date-format="d-m-Y" placeholder="Pilih Tanggal" value="{{ $tglAwal }} to {{ $tglAkhir }}" style="min-width: 200px;">
                                      
                                      <button type="submit" id="btn-filter" class="btn btn-primary">Preview Data</button>
                                      <button type="button" id="btn-export" class="btn btn-success"><i class="ri-file-excel-2-line align-bottom me-1"></i> Export Excel</button>
                                  </form>
                          </div>
                    </div>
                </div>
                  <div class="card-body">
                      <div class="table-responsive">
                          <table id="dt_profit_loss" class="table table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">
                              <thead id="table-header">
                                  <!-- Dynamic header -->
                              </thead>
                              <tbody>
                                  <!-- Data will be populated via AJAX -->
                              </tbody>
                          </table>
                      </div>
                  </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <!-- Load jQuery 3.6.0 untuk DataTables -->
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js')}}"></script>
    
    <!-- Load ulang Bootstrap Bundle agar plugin modal() ter-register ke jQuery baru -->
    <script src="{{ URL::asset('build/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Plugin DataTables -->
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/jquery.dataTables.min.js')}}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/dataTables.bootstrap5.min.js')}}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-responsive-2.2.9/dataTables.responsive.min.js')}}"></script>
    
    <script>
        $(document).ready(function() {
            let dataTable = null;

            // Pastikan flatpickr terinisialisasi
            flatpickr("#daterange", {
                mode: "range",
                dateFormat: "d-m-Y"
            });

            function loadTable() {
                var daterange = $('#daterange').val();
                var start_date = '';
                var end_date = '';
                if (daterange) {
                    var dates = daterange.split(' to ');
                    start_date = dates[0];
                    end_date = dates[1] ? dates[1] : dates[0];
                }
                var store_id = $('#store_id').val();
                var report_type = $('#report_type').val();
                var channel = $('#channel').val();

                if (dataTable) {
                    dataTable.destroy();
                    $('#dt_profit_loss tbody').empty();
                    $('#table-header').empty();
                }

                let columnsConfig = [];
                let theadHtml = '<tr><th>No</th><th>Invoice ID</th>';

                let isSuper = {{ in_array(session()->get('current_role')->name, ['super', 'admin']) ? 'true' : 'false' }};
                if (report_type === 'per_transaction') {
                    theadHtml += '<th>Rincian Barang</th><th>Tanggal</th><th>Cabang</th><th>Channel</th><th>Omzet Kotor</th><th>Diskon</th>';
                    if (isSuper) theadHtml += '<th>Total COGM (HPP)</th><th>Laba Kotor</th>';
                    theadHtml += '<th>Ongkir</th><th>Laba Bersih</th></tr>';
                    
                    columnsConfig = [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'invoice_id', name: 'sales_transactions.invoice_id' },
                        { data: 'rincian_barang', name: 'rincian_barang', orderable: false, searchable: false },
                        { data: 'date', name: 'sales_transactions.date' },
                        { data: 'store_name', name: 'stores.name', orderable: false, searchable: false },
                        { data: 'channel', name: 'sales_transactions.channel' },
                        { data: 'revenue', name: 'sales_transactions.sub_total', searchable: false },
                        { data: 'discount_nominal', name: 'sales_transactions.discount_nominal', searchable: false }
                    ];
                    if (isSuper) {
                        columnsConfig.push({ data: 'cogs', name: 'cogs', searchable: false });
                        columnsConfig.push({ data: 'laba_kotor', name: 'laba_kotor', searchable: false, orderable: false });
                    }
                    columnsConfig.push(
                        { data: 'ongkir', name: 'ongkir', searchable: false, orderable: false },
                        { data: 'laba_bersih', name: 'laba_bersih', searchable: false, orderable: false }
                    );
                } else {
                    theadHtml += '<th>Tanggal</th><th>Cabang</th><th>Channel</th><th>Nama Barang</th><th>Harga Jual</th><th>Qty</th><th>Diskon Item</th>';
                    if (isSuper) theadHtml += '<th>Total COGM</th><th>Laba Kotor Item</th>';
                    theadHtml += '</tr>';
                    
                    columnsConfig = [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'invoice_id', name: 'sales_transactions.invoice_id' },
                        { data: 'date', name: 'sales_transactions.date' },
                        { data: 'store_name', name: 'stores.name' },
                        { data: 'channel', name: 'sales_transactions.channel' },
                        { data: 'product_name', name: 'product_name', orderable: false, searchable: false },
                        { data: 'price', name: 'sales_transaction_items.price', searchable: false },
                        { data: 'qty', name: 'sales_transaction_items.qty', searchable: false },
                        { data: 'discount_nominal', name: 'sales_transaction_items.discount_nominal', searchable: false }
                    ];
                    if (isSuper) {
                        columnsConfig.push({ data: 'cogm', name: 'sales_transaction_items.cogm', searchable: false });
                        columnsConfig.push({ data: 'laba_kotor', name: 'laba_kotor', searchable: false, orderable: false });
                    }
                }

                $('#table-header').html(theadHtml);

                dataTable = $('#dt_profit_loss').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('profit-loss-report.get_datatable') }}",
                        data: {
                            start_date: start_date,
                            end_date: end_date,
                            store_id: store_id,
                            report_type: report_type,
                            channel: channel
                        }
                    },
                    columns: columnsConfig,
                    order: [[ (report_type === 'per_transaction' ? 3 : 2), 'desc']] // Sorting by Tanggal
                });
            }

            // Load on init
            loadTable();

            $('#btn-export').on('click', function(e) {
                e.preventDefault();
                var daterange = $('#daterange').val();
                var start_date = '';
                var end_date = '';
                if (daterange) {
                    var dates = daterange.split(' to ');
                    start_date = dates[0];
                    end_date = dates[1] ? dates[1] : dates[0];
                }
                var store_id = $('#store_id').val();
                var report_type = $('#report_type').val();
                var channel = $('#channel').val();

                // Create a dynamic form to submit POST request for file download
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('profit-loss-report.export') }}";

                var csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = "{{ csrf_token() }}";
                form.appendChild(csrfToken);

                var inputStart = document.createElement('input');
                inputStart.type = 'hidden';
                inputStart.name = 'start_date';
                inputStart.value = start_date;
                form.appendChild(inputStart);

                var inputEnd = document.createElement('input');
                inputEnd.type = 'hidden';
                inputEnd.name = 'end_date';
                inputEnd.value = end_date;
                form.appendChild(inputEnd);

                var inputStore = document.createElement('input');
                inputStore.type = 'hidden';
                inputStore.name = 'store_id';
                inputStore.value = store_id;
                form.appendChild(inputStore);
                
                var inputType = document.createElement('input');
                inputType.type = 'hidden';
                inputType.name = 'report_type';
                inputType.value = report_type;
                form.appendChild(inputType);
                
                var inputChannel = document.createElement('input');
                inputChannel.type = 'hidden';
                inputChannel.name = 'channel';
                inputChannel.value = channel;
                form.appendChild(inputChannel);

                document.body.appendChild(form);
                form.submit();
                setTimeout(() => form.remove(), 1000);
            });
        });
    </script>
@endsection
