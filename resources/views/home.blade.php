@extends('layouts.master')
@section('title') @lang('translation.dashboards') @endsection
@section('css')
    <link href="{{ URL::asset('build/libs/jsvectormap/css/jsvectormap.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/flatpickr/plugins/monthSelect/style.css') }}" rel="stylesheet" type="text/css" />
    --}}
@endsection
@section('content')

    <!-- Modal Calendar Bulan & Tahun -->
    <div class="modal fade" id="periodeModal" tabindex="-1" aria-labelledby="periodeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="GET" action="{{ route('dashboard.index') }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="periodeModalLabel">Pilih Periode</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <input type="text" id="periodeCalendar" class="form-control mb-3" placeholder="Pilih Bulan & Tahun"
                            readonly>
                        <input type="hidden" name="period" id="periodValue">
                        @if($storeId)
                            <input type="hidden" name="store_id" value="{{ $storeId }}">
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="h-100">
                <div class="row mb-3 pb-1">
                    <div class="col-12">
                        <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                            <div class="flex-grow-1">
                                <h4 class="fs-16 mb-1">Insight Dashboard</h4>
                                <p class="text-muted mb-1">
                                    Cashflow sepanjang tahun {{ $year }}, Bulan
                                    {{ $month ? DateTime::createFromFormat('!m', $month)->format('F') : '-' }}.
                                </p>
                                @if(Auth::check())
                                    <p class="text-muted mb-0" style="font-size: 13px;">
                                        <i class="ri-time-line align-middle me-1"></i> Login Terakhir:
                                        <strong>
                                            {{ Auth::user()->last_login_at ? \Carbon\Carbon::parse(Auth::user()->last_login_at)->translatedFormat('d F Y, H:i') . ' WIB' : 'Sesi saat ini' }}
                                        </strong>
                                        @if(Auth::user()->last_login_ip)
                                            <span class="ms-2"><i class="ri-map-pin-line align-middle me-1"></i> IP:
                                                {{ Auth::user()->last_login_ip }}</span>
                                        @endif
                                    </p>
                                @endif
                            </div>
                            <div class="mt-3 mt-lg-0">
                                <form action="{{ route('dashboard.index') }}" method="GET">
                                    <div class="row g-3 mb-0 align-items-center">
                                        @if(request('period'))
                                            <input type="hidden" name="period" value="{{ request('period') }}">
                                        @endif
                                        <div class="col-auto">
                                            <select name="store_id" class="form-select" onchange="this.form.submit()">
                                                <option value="">Semua Toko</option>
                                                @foreach($stores as $store)
                                                    <option value="{{ $store->id }}" {{ $storeId == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-auto">
                                            <div class="col-auto">
                                                <button type="button" class="btn btn-success" id="btnGantiPeriode"
                                                    data-bs-toggle="modal" data-bs-target="#periodeModal">
                                                    <i class="ri-calendar-2-line align-middle me-1"></i>
                                                    Ganti Periode
                                                </button>
                                            </div>
                                        </div>
                                        <!--end col-->
                                        <div class="col-auto">
                                            <button type="button"
                                                class="btn btn-soft-info btn-icon waves-effect waves-light layout-rightside-btn"><i
                                                    class="ri-filter-2-fill"></i></button>
                                        </div>
                                        <!--end col-->
                                    </div>
                                </form>
                            </div>
                        </div><!-- end card header -->
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->

                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <a href="{{ route('sale-transaction.report') }}?start_date=01-01-{{ $year }}&end_date=31-12-{{ $year }}"
                            class="text-decoration-none">
                            <!-- card -->
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                                Pendapatan {{ $year }}</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            @php
                                                if ($prevTotal == 0 && $currTotal > 0) {
                                                    $growth = '100';
                                                } elseif ($prevTotal > 0) {
                                                    $growth = number_format((($currTotal - $prevTotal) / $prevTotal) * 100, 2);
                                                } else {
                                                    $growth = '0';
                                                }

                                                $isMinus = $growth < 0;
                                            @endphp
                                            <h5 class="{{ $isMinus ? 'text-danger' : 'text-success' }} fs-14 mb-0">
                                                <i
                                                    class="ri-arrow-right-{{ $isMinus ? 'down' : 'up' }}-line fs-13 align-middle"></i>
                                                {{ $growth }}%
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">Rp. <span class="counter-value"
                                                    data-target="{{ collect($monthlyEarnings)->sum('total') }}">0</span>
                                            </h4>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-success rounded fs-3">
                                                <i class="bx bx-dollar-circle text-success"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div><!-- end card body -->
                            </div><!-- end card -->
                        </a>
                    </div><!-- end col -->

                    <div class="col-xl-3 col-md-6">
                        <a href="{{ route('sale-transaction.report') }}?start_date=01-{{ sprintf('%02d', $month) }}-{{ $year }}&end_date={{ \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('d-m-Y') }}"
                            class="text-decoration-none">
                            <!-- card -->
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                                Pendapatan
                                                {{ $month ? DateTime::createFromFormat('!m', $month)->format('F') : '-' }}
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            @php
                                                $curr = (float) ($monthlyEarning->total ?? 0);
                                                $prev = (float) ($prevMonthlyEarning ?? 0);
                                                $diff = $curr - $prev;
                                                $percent = $prev > 0 ? number_format(($diff / $prev) * 100, 2) : ($curr > 0 ? 100 : 0);
                                                $isMinus = $diff < 0;
                                            @endphp
                                            <h5 class="{{ $isMinus ? 'text-danger' : 'text-success' }} fs-14 mb-0">
                                                <i
                                                    class="ri-arrow-right-{{ $isMinus ? 'down' : 'up' }}-line fs-13 align-middle"></i>
                                                {{ $percent }}%
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">Rp. <span class="counter-value"
                                                    data-target="{{ $monthlyEarning->total }}">0</span>
                                            </h4>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-success rounded fs-3">
                                                <i class="bx bx-dollar-circle text-success"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div><!-- end card body -->
                            </div><!-- end card -->
                        </a>
                    </div><!-- end col -->

                    <div class="col-xl-3 col-md-6">
                        <a href="{{ route('sale-transaction.report') }}?start_date={{ date('d-m-Y') }}&end_date={{ date('d-m-Y') }}"
                            class="text-decoration-none">
                            <!-- card -->
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                                Pendapatan Hari ini</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-danger fs-14 mb-0">
                                                <i class="ri-arrow-right-down-line fs-13 align-middle"></i>
                                                {{ $todayEarning ? number_format((($todayEarning - $monthlyEarning->total) / $monthlyEarning->total) * 100, 2) : '0' }}%
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value"
                                                    data-target="{{ $todayEarning }}">0</span></h4>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-info rounded fs-3">
                                                <i class="bx bx-wallet text-info"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div><!-- end card body -->
                            </div><!-- end card -->
                        </a>
                    </div><!-- end col -->

                    <div class="col-xl-3 col-md-6">
                        <a href="{{ route('sale-transaction.report') }}?start_date={{ date('d-m-Y') }}&end_date={{ date('d-m-Y') }}"
                            class="text-decoration-none">
                            <!-- card -->
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">
                                                Total Transaksi Hari ini</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-muted fs-14 mb-0">
                                                <i class="ri-arrow-right-down-line fs-13 align-middle"></i>
                                                {{ $totalTransactions >= 0 ? '0' : '' }}%
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value"
                                                    data-target="{{ $totalTransactions }}">0</span>
                                            </h4>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-primary rounded fs-3">
                                                <i class="bx bx-receipt text-primary"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div><!-- end card body -->
                            </div><!-- end card -->
                        </a>
                    </div><!-- end col -->
                </div> <!-- end row-->

                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <a href="{{ route('ecommerce-transaction.index') }}?payment_status=paid&filter_month={{ sprintf('%04d-%02d', $year, $month) }}"
                            class="text-decoration-none">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Pengiriman
                                                Perlu Diproses</p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value"
                                                    data-target="{{ $shippingNeedsProcess }}">0</span></h4>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-warning rounded fs-3">
                                                <i class="bx bx-package text-warning"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <a href="{{ route('ecommerce-transaction.index') }}?payment_status=shipping&filter_month={{ sprintf('%04d-%02d', $year, $month) }}"
                            class="text-decoration-none">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Pengiriman
                                                Telah Diproses</p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value"
                                                    data-target="{{ $shippingProcessed }}">0</span></h4>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-info rounded fs-3">
                                                <i class="bx bx-send text-info"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <a href="{{ route('ecommerce-transaction.index') }}?payment_status=canceled&filter_month={{ sprintf('%04d-%02d', $year, $month) }}"
                            class="text-decoration-none">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Pengembalian /
                                                Pembatalan</p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value"
                                                    data-target="{{ $shippingCanceled }}">0</span></h4>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-danger rounded fs-3">
                                                <i class="bx bx-x-circle text-danger"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <a href="{{ route('product.index') }}" class="text-decoration-none">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Varian
                                                Produk Aktif</p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value"
                                                    data-target="{{ $totalProductVariants ?? 0 }}">0</span></h4>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-primary rounded fs-3">
                                                <i class="bx bx-box text-primary"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div> <!-- end row-->

                <div class="row">
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header border-0 align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Pendapatan Perbulan di Tahun {{ $year }}</h4>
                            </div><!-- end card header -->

                            <div class="card-header p-0 border-0 bg-soft-light">
                                <div class="row g-0 text-center">
                                    <div class="col-6 col-sm-12">
                                        <div class="p-3 border border-dashed border-start-0">
                                            @php
                                                $total = collect($monthlyEarnings)->sum('total');
                                                if ($total >= 1000000000) {
                                                    $displayTotal = ' Miliar';
                                                } elseif ($total >= 1000000) {
                                                    $displayTotal = ' Juta';
                                                } elseif ($total >= 1000) {
                                                    $displayTotal = ' Ribu';
                                                } else {
                                                    $displayTotal = ' Rupiah';
                                                }
                                            @endphp

                                            <h5 class="mb-1">
                                                <span class="counter-value text-success" data-target="{{ $total }}">0</span>
                                                {{-- <span class="ms-1">{{ $displayTotal }}</span> --}}
                                            </h5>
                                            <p class="text-muted mb-0">Revenue</p>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end card header -->

                            <div class="card-body p-0 pb-2">
                                <div class="w-100">
                                    <div id="monthly_earnings" data-colors='["--vz-primary", "--vz-success", "--vz-danger"]'
                                        class="apex-charts" dir="ltr"></div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->

                    <div class="col-xl-6">
                        <!-- card -->
                        <div class="card card-height-100">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Pendapatan Per Hari di Bulan
                                    {{ $month ? DateTime::createFromFormat('!m', $month)->format('F') : '-' }}</h4>
                            </div><!-- end card header -->

                            <!-- card body -->
                            <div class="card-body">

                                <div id="earningPerDays" data-colors='["--vz-light", "--vz-success", "--vz-primary"]'
                                    style="height: 269px" dir="ltr"></div>
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->
                </div>

                <div class="row">
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">
                                    Top Items Sales in
                                    {{ $month ? DateTime::createFromFormat('!m', $month)->format('F') : '-' }} {{ $year }}
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table
                                        class="table table-striped table-centered align-middle text-sm table-nowrap table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th scope="col">No</th>
                                                <th scope="col">Product</th>
                                                <th scope="col">Variant</th>
                                                <th scope="col">Color</th>
                                                <th scope="col">Last Price</th>
                                                <th scope="col">Qty Sold</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($topItems as $i => $item)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td><a href="{{ route('product.basicInformation', $item->product_id) }}"
                                                            class="text-primary text-decoration-underline">{{ $item->product_title }}</a>
                                                    </td>
                                                    <td>{{ $item->variant_name }}</td>
                                                    <td>{{ $item->color_label }}</td>
                                                    <td>Rp {{ number_format($item->last_price, 0, ',', '.') }}</td>
                                                    <td>{{ $item->total_qty }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">Tidak ada data.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Detail Penjualan Perbulan Di Tahun {{ $year }}</h4>
                            </div><!-- end card header -->

                            <div class="card-body">
                                <div class="table-responsive table-card">
                                    <table
                                        class="table table-striped table-centered align-middle table-nowrap table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th scope="col">No</th>
                                                <th scope="col">Bulan</th>
                                                <th scope="col">Pendapatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $months = [
                                                    1 => 'Januari',
                                                    2 => 'Februari',
                                                    3 => 'Maret',
                                                    4 => 'April',
                                                    5 => 'Mei',
                                                    6 => 'Juni',
                                                    7 => 'Juli',
                                                    8 => 'Agustus',
                                                    9 => 'September',
                                                    10 => 'Oktober',
                                                    11 => 'November',
                                                    12 => 'Desember'
                                                ];
                                                $monthlyData = collect($monthlyEarnings)->keyBy('month');
                                            @endphp
                                            @foreach($months as $num => $name)
                                                <tr>
                                                    <td>{{ $num }}</td>
                                                    <td>{{ $name }} {{ $year }}</td>
                                                    <td>
                                                        Rp
                                                        {{ number_format((float) ($monthlyData[$num]->total ?? 0), 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div> <!-- .card-->
                    </div> <!-- .col-->
                </div> <!-- end row-->

                <div class="row">
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Top Consignment Terlaris di Bulan
                                    {{ $month ? DateTime::createFromFormat('!m', $month)->format('F') : '-' }} {{ $year }}
                                </h4>
                            </div>
                            <div class="card-body">
                                @if(count($topConsignmentItems) > 0)
                                    <div id="consignment_pie_chart"
                                        data-colors='["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info", "--vz-dark", "--vz-secondary"]'
                                        class="apex-charts" dir="ltr"></div>
                                @else
                                    <div class="text-center text-muted py-5">
                                        Tidak ada data penjualan Consignment.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">
                                    <i class="ri-time-line align-middle me-1"></i> Latest login :
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table
                                        class="table table-striped table-centered align-middle table-nowrap table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th scope="col">No</th>
                                                <th scope="col">Name</th>
                                                <th scope="col">Last Login</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($latestLogins as $i => $login)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td class="fw-medium">{{ $login->name }}</td>
                                                    <td>
                                                        {{ \Carbon\Carbon::parse($login->last_login_at)->format('d M Y, H:i:s') }}
                                                        <span
                                                            class="text-muted fs-12 ms-2">({{ \Carbon\Carbon::parse($login->last_login_at)->diffForHumans() }})</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">Belum ada data login.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- end row-->

            </div> <!-- end .h-100-->

        </div> <!-- end col -->
    </div>
    @php
        $chartData = array_fill(1, 12, 0);
        foreach ($monthlyEarnings as $earning) {
            $chartData[(int) $earning->month] = (float) $earning->total;
        }

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $dailyChartData = array_fill(1, $daysInMonth, 0);
        foreach ($dailyEarnings as $earning) {
            $dailyChartData[(int) $earning->day] = (float) $earning->total;
        }
    @endphp

@endsection

@section('script')

    <!-- apexcharts -->
    <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/jsvectormap/js/jsvectormap.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/jsvectormap/maps/world-merc.js') }}"></script>
    <script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js')}}"></script>
    <!-- dashboard init -->
    <script src="{{ URL::asset('build/js/pages/dashboard-ecommerce.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>

    <!-- flatpickr js -->
    <script src="{{ URL::asset('build/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/flatpickr/plugins/monthSelect/index.js') }}"></script>
    <script src="{{ URL::asset('build/libs/flatpickr/l10n/id.js') }}"></script>

    <script>
        let selectedPeriod = null;
        flatpickr("#periodeCalendar", {
            locale: 'id',
            plugins: [
                new monthSelectPlugin({
                    shorthand: false,
                    dateFormat: "Y-m",
                    altFormat: "F Y"
                })
            ],
            altInput: true,
            onChange: function (selectedDates, dateStr, instance) {
                selectedPeriod = dateStr;
                document.getElementById('periodValue').value = dateStr;
            }
        });

        document.getElementById('periodeModal').addEventListener('shown.bs.modal', function () {
            // Optionally, you can clear selection or set focus
        });

        var monthlyEarningsData = @json(array_values($chartData));

        var options = {
            chart: { type: 'bar', height: 350 },
            series: [{
                name: 'Pendapatan',
                data: monthlyEarningsData // ini harus dari Blade
            }],
            xaxis: {
                categories: [
                    'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                    'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
                ]
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return 'Rp ' + val.toLocaleString('id-ID');
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return 'Rp ' + val.toLocaleString('id-ID');
                    }
                }
            },
            dataLabels: {
                enabled: false
            }
        };
        var chart = new ApexCharts(document.querySelector("#monthly_earnings"), options);
        chart.render();

        var dailyEarningsData = @json(array_values($dailyChartData));

        var daysInMonth = {{ $daysInMonth }};
        var dayCategories = [];
        for (var i = 1; i <= daysInMonth; i++) {
            dayCategories.push(i.toString());
        }

        var perDay = {
            chart: { type: 'bar', height: 350 },
            series: [{
                name: 'Pendapatan',
                data: dailyEarningsData
            }],
            xaxis: {
                categories: dayCategories
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return 'Rp ' + val.toLocaleString('id-ID');
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return 'Rp ' + val.toLocaleString('id-ID');
                    }
                }
            },
            dataLabels: {
                enabled: false
            }
        };

        var chartPerDay = new ApexCharts(document.querySelector("#earningPerDays"), perDay);
        chartPerDay.render();

        // Pie Chart Top Konsinyasi
        var consignmentData = @json($topConsignmentItems->pluck('total_qty'));
        var consignmentLabels = @json($topConsignmentItems->pluck('material_title'));

        if (consignmentData.length > 0) {
            var consignmentOptions = {
                series: consignmentData.map(Number),
                chart: {
                    type: 'pie',
                    height: 350
                },
                labels: consignmentLabels,
                dataLabels: {
                    enabled: true,
                    formatter: function (val, opts) {
                        return opts.w.config.series[opts.seriesIndex] + " pcs"
                    }
                },
                legend: {
                    position: 'bottom'
                }
            };
            var consignmentChart = new ApexCharts(document.querySelector("#consignment_pie_chart"), consignmentOptions);
            consignmentChart.render();
        }
    </script>
@endsection