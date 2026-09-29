@extends('layouts.master')
@section('title') @lang('translation.dashboards') @endsection

@section('content')

<div class="row">
    <div class="col">

        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Analytics</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboards</a></li>
                    <li class="breadcrumb-item active">Analytics</li>
                </ol>
            </div>

        </div>

        <div class="h-100">
            <div class="row mb-3 pb-1">
                @include('backend.includes.flash')
               
            </div>
            
            <div class="row mb-3 pb-1">
                <div class="col-12">
                    <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                        <div class="flex-grow-1">
                            <h4 class="fs-16 mb-1">Insight Dashboard </h4>
                            <p class="text-muted mb-0">Cashflow sepanjang tahun {{$year}}, Bulan {{$month}}</p>
                        </div>
                        <div class="mt-3 mt-lg-0">
                            <form action="javascript:void(0);">
                                <div class="row g-3 mb-0 align-items-center">
                                    
                                    <!--end col-->
                                    <div class="col-auto">
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#settingPeriode"><i class=" ri-settings-4-fill align-middle me-1"></i>Ganti Periode</button>
                                    </div>
                                    <!--end col-->
                                    <div class="col-auto">
                                        @if(count($filter_account_exclude_revenue)>0 || count($filter_account_exclude_expense)>0)
                                        <button type="button" class="btn btn-soft-danger  waves-effect waves-light layout-rightside-btn" data-bs-toggle="modal" data-bs-target="#settingFilter"><i class="ri-filter-line"></i>
                                            Filter Akun Aktif
                                        </button>
                                        @else
                                            <button type="button" class="btn btn-soft-info btn-icon waves-effect waves-light layout-rightside-btn" data-bs-toggle="modal" data-bs-target="#settingFilter"><i class="ri-filter-line"></i></button>
                                        @endif
                                        
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                            </form>
                        </div>
                    </div><!-- end card header -->
                </div>
                <!--end col-->
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card crm-widget">
                        <div class="card-body p-0">
                            <div class="row row-cols-xxl-5 row-cols-md-3 row-cols-1 g-0">
                                <div class="col">
                                    <div class="py-4 px-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h5 class="text-muted text-uppercase fs-13">
                                                    Laba/Rugi {{$year}}
                                                </h5>
                                            </div>
                                            <div class="flex-shrink-0">
                                                @if($profitThisYearGrowth>0 && $profitThisYearGrowth<10000)
                                                <h5 class="text-success fs-13 ">
                                                    <i class="ri-arrow-right-up-line fs-13 align-middle"></i> {{number_format($profitThisYearGrowth,2)}} %
                                                </h5>
                                                @elseif($profitThisYearGrowth>10000)
                                                <h5 class="text-success fs-13 ">
                                                    <i class="ri-arrow-right-up-line fs-13 align-middle"></i> 10.000% ++
                                                </h5>
                                                @elseif($profitThisYearGrowth < (-10000))
                                                <h5 class="text-danger fs-13 ">
                                                    <i class="ri-arrow-right-down-line fs-13 align-middle"></i> 10.000% ++
                                                </h5>
                                                @elseif($profitThisYearGrowth<0)
                                                <h5 class="text-danger fs-13 ">
                                                    <i class="ri-arrow-right-down-line fs-13 align-middle"></i> {{number_format($profitThisYearGrowth,2)}} %
                                                </h5>
                                               
                                                @endif


                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="ri-space-ship-line display-6 text-muted"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h2 class="mb-0 fs-22 fw-semibold ff-secondary ">Rp. <span class="counter-value" data-target="{{$profitThisYear}}"></span></h2>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- end col -->
                                <div class="col">
                                    <div class="mt-3 mt-md-0 py-4 px-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h5 class="text-muted text-uppercase fs-13">
                                                    Pendapatan {{$year}} 
                                                </h5>
                                            </div>
                                            <div class="flex-shrink-0">
                                                @if($revenueThisYearGrowth>0 && $revenueThisYearGrowth<10000)
                                                <h5 class="text-success fs-13 ">
                                                    <i class="ri-arrow-right-up-line fs-13 align-middle"></i> {{number_format($revenueThisYearGrowth,2)}} %
                                                </h5>
                                                @elseif($revenueThisYearGrowth>10000)
                                                <h5 class="text-success fs-13 ">
                                                    <i class="ri-arrow-right-up-line fs-13 align-middle"></i> 10.000% ++
                                                </h5>
                                                @elseif($revenueThisYearGrowth<0)
                                                <h5 class="text-danger fs-13 ">
                                                    <i class="ri-arrow-right-down-line fs-13 align-middle"></i> {{number_format($revenueThisYearGrowth,2)}} %
                                                </h5>
                                                @elseif($revenueThisYearGrowth<-10000)
                                                <h5 class="text-danger fs-13 ">
                                                    <i class="ri-arrow-right-down-line fs-13 align-middle"></i> 10.000% ++
                                                </h5>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="ri-exchange-dollar-line display-6 text-muted"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h2 class="mb-0 fs-22 fw-semibold ff-secondary ">Rp. <span class="counter-value" data-target="{{$revenueThisYear}}"></span></h2>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- end col -->
                                <div class="col">
                                    <div class="mt-3 mt-md-0 py-4 px-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h5 class="text-muted text-uppercase fs-13">
                                                    Pengeluaran {{$year}}
                                                </h5>
                                            </div>
                                            <div class="flex-shrink-0">
                                                @if($expenseThisYearGrowth>0 && $expenseThisYearGrowth<10000)
                                                <h5 class="text-success fs-13 ">
                                                    <i class="ri-arrow-right-up-line fs-13 align-middle"></i> {{number_format($expenseThisYearGrowth,2)}} %
                                                </h5>
                                                @elseif($expenseThisYearGrowth>0)
                                                <h5 class="text-success fs-13 ">
                                                    <i class="ri-arrow-right-up-line fs-13 align-middle"></i> 10.000% ++
                                                </h5>
                                                @elseif($expenseThisYearGrowth<0)
                                                <h5 class="text-danger fs-13 ">
                                                    <i class="ri-arrow-right-down-line fs-13 align-middle"></i> {{number_format($expenseThisYearGrowth,2)}} %
                                                </h5>
                                                @elseif($expenseThisYearGrowth<-10000)
                                                <h5 class="text-danger fs-13 ">
                                                    <i class="ri-arrow-right-down-line fs-13 align-middle"></i> 10.000% ++
                                                </h5>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="ri-pulse-line display-6 text-muted"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h2 class="mb-0 fs-22 fw-semibold ff-secondary">Rp. <span class="counter-value" data-target="{{$expenseThisYear}}"></span></h2>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- end col -->
                                <div class="col">
                                    <div class="mt-3 mt-lg-0 py-4 px-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h5 class="text-muted text-uppercase fs-13">
                                                    Pendapatan {{$month}}
                                                </h5>
                                            </div>
                                            <div class="flex-shrink-0">
                                                @if($revenueThisMonthGrowth>0 && $revenueThisMonthGrowth<10000)
                                                <h5 class="text-success fs-13 ">
                                                    <i class="ri-arrow-right-up-line fs-13 align-middle"></i> {{number_format($revenueThisMonthGrowth,2)}} %
                                                </h5>
                                                @elseif($revenueThisMonthGrowth>0)
                                                <h5 class="text-success fs-13 ">
                                                    <i class="ri-arrow-right-up-line fs-13 align-middle"></i> 10.000% ++
                                                </h5>
                                                @elseif($revenueThisMonthGrowth<0)
                                                <h5 class="text-danger fs-13 ">
                                                    <i class="ri-arrow-right-down-line fs-13 align-middle"></i> {{number_format($revenueThisMonthGrowth,2)}} %
                                                </h5>
                                                @elseif($revenueThisMonthGrowth<-10000)
                                                <h5 class="text-danger fs-13 ">
                                                    <i class="ri-arrow-right-down-line fs-13 align-middle"></i> 10.000% ++
                                                </h5>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="ri-trophy-line display-6 text-muted"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h2 class="mb-0 fs-22 fw-semibold ff-secondary">Rp. <span class="counter-value" data-target="{{$revenueThisMonth}}"></span></h2>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- end col -->
                                <div class="col">
                                    <div class="mt-3 mt-lg-0 py-4 px-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h5 class="text-muted text-uppercase fs-13">
                                                    Pengeluaran {{$month}}
                                                </h5>
                                            </div>
                                            <div class="flex-shrink-0">
                                                @if($expenseThisMonthGrowth>0 && $expenseThisMonthGrowth<10000)
                                                <h5 class="text-success fs-13 ">
                                                    <i class="ri-arrow-right-up-line fs-13 align-middle"></i> {{number_format($expenseThisMonthGrowth,2)}} %
                                                </h5>
                                                @elseif($expenseThisMonthGrowth>0)
                                                <h5 class="text-success fs-13 ">
                                                    <i class="ri-arrow-right-up-line fs-13 align-middle"></i> 10.000% ++
                                                </h5>
                                                @elseif($expenseThisMonthGrowth<0)
                                                <h5 class="text-danger fs-13 ">
                                                    <i class="ri-arrow-right-down-line fs-13 align-middle"></i>  {{number_format($expenseThisMonthGrowth,2)}} %
                                                </h5>
                                                @elseif($expenseThisMonthGrowth<-10000)
                                                <h5 class="text-danger fs-13 ">
                                                    <i class="ri-arrow-right-down-line fs-13 align-middle"></i> 10.000% ++
                                                </h5>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="ri-service-line display-6 text-muted"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h2 class="mb-0 fs-22 fw-semibold ff-secondary">Rp. <span class="counter-value" data-target="{{$expenseThisMonth}}"></span></h2>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- end col -->
                            </div><!-- end row -->
                        </div><!-- end card body -->
                    </div><!-- end card -->
                </div><!-- end col -->
            </div>


            <div class="row">
                <div class="col-xxl-3 col-md-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Komparasi Per Tahun</h4>
                            
                        </div><!-- end card header -->
                        <div class="card-body pb-0">
                            <div id="column_chart" data-colors='["--vz-success", "--vz-danger"]' class="apex-charts" dir="ltr"></div>
                        </div>
                    </div><!-- end card -->
                </div><!-- end col -->

                <div class="col-xxl-4 col-md-12">
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Per Bulan di Tahun {{$year}}</h4>
                            
                        </div><!-- end card header -->
                        <div class="card-body px-0">
                            <ul class="list-inline main-chart text-center mb-0">
                                <li class="list-inline-item chart-border-left me-0 border-0">
                                    <h4 class="text-primary">Rp.{{$profitThisYearInMillion}} <span class="text-muted d-inline-block fs-13 align-middle ms-2">Revenue</span></h4>
                                </li>
                                <li class="list-inline-item chart-border-left me-0">
                                    <h4><span data-plugin="counterup">{{number_format($profitRatio,2)}}</span>%<span class="text-muted d-inline-block fs-13 align-middle ms-2">Profit Ratio</span></h4>
                                </li>
                            </ul>
        
                            <div id="revenue-expenses-charts" data-colors='["--vz-success", "--vz-danger"]' class="apex-charts" dir="ltr"></div>
                        </div>
                    </div><!-- end card -->
                </div><!-- end col -->
        
                <div class="col-xxl-5 col-md-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Pendapatan Per Hari di Bulan {{$month}} {{$year}}</h4>
                            
                        </div><!-- end card header -->
                        <div class="card-body pb-0">
                            <div id="column_chart_daily" data-colors='["--vz-primary", "--vz-success"]' class="apex-charts" dir="ltr"></div>
                        </div>
                    </div><!-- end card -->
                </div><!-- end col -->
        
                
            </div><!-- end row -->


            <div class="row">
                <div class="col-xxl-6 col-md-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Komparasi Kategori Pendapatan Tahun {{$year}}</h4>
                            
                        </div><!-- end card header -->
                        <div class="card-body px-0">
                            <div id="revenue-category-chart" data-colors='["--vz-info","--vz-secondary","--vz-primary", "--vz-success", "--vz-warning", "--vz-danger","--vz-black"]' class="apex-charts" dir="ltr"></div>
                        </div>
                    </div><!-- end card -->
                </div><!-- end col -->

                <div class="col-xxl-6 col-md-12">
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Komparasi Kategori Pengeluaran Tahun {{$year}}</h4>
                            
                        </div><!-- end card header -->
                        <div class="card-body px-0">
                            <div id="expense-category-chart" data-colors='["--vz-info","--vz-secondary","--vz-primary", "--vz-success", "--vz-warning", "--vz-danger","--vz-black"]' class="apex-charts" dir="ltr"></div>
                        </div>
                    </div><!-- end card -->
                </div><!-- end col -->
        
                
            </div><!-- end row -->


            <div class="row">
                <div class="col-xxl-6 col-md-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Detail Pendapatan Tahun {{$year}}</h4>
                            
                        </div><!-- end card header -->
                        <div class="card-body px-0">
                            <div id="revenue-category-chart2" data-colors='["--vz-info","--vz-secondary","--vz-primary", "--vz-success", "--vz-warning", "--vz-danger","--vz-black"]' class="apex-charts" dir="ltr"></div>
                        </div>
                    </div><!-- end card -->
                </div><!-- end col -->

                <div class="col-xxl-6 col-md-12">
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Detail Pengeluaran Tahun {{$year}}</h4>
                            
                        </div><!-- end card header -->
                        <div class="card-body px-0">
                            <div id="expense-category-chart2" data-colors='["--vz-info","--vz-secondary","--vz-primary", "--vz-success", "--vz-warning", "--vz-danger","--vz-black"]' class="apex-charts" dir="ltr"></div>
                        </div>
                    </div><!-- end card -->
                </div><!-- end col -->
        
                
            </div><!-- end row -->


            <div class="row">
                <div class="col-xxl-4 col-md-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Saldo Kas Per {{$month}} {{$year}}</h4>
                            
                        </div><!-- end card header -->
                        <div class="card-body p-2">
                        <!-- Table Head -->
                            <table class="table align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Akun Kas</th>
                                        <th scope="col" class="text-end">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $total_balance = 0;
                                    @endphp
                                    @foreach ($lastBalance as $row)
                                        @php
                                            $total_balance += $row->balance;
                                        @endphp
                                        <tr>
                                            <td>
                                               {{$row->name}}
                                            </td>
                                            <td class="text-end">
                                                Rp. {{number_format($row->balance,0)}}
                                            </td>
                                        </tr>
                                    @endforeach

                                    <tr  class="table-light">
                                        <td>
                                           Total
                                        </td>
                                        <td class="text-end">
                                            Rp. {{number_format( $total_balance,0)}}
                                        </td>
                                    </tr>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div><!-- end card -->
                </div><!-- end col -->

                <div class="col-xxl-8 col-md-12">
                    <div class="card card-height-100">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">List Pendapatan Terakhir {{$year}}</h4>
                            
                        </div><!-- end card header -->
                        <div class="card-body p-2 table-responsive">
                            <!-- Table Head -->
                                <table class="table align-middle table-nowrap mb-0"  style="min-width: 700px">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Tanggal</th>
                                            <th scope="col">Akun</th>
                                            <th scope="col">Remark</th>
                                            <th scope="col" class="text-end">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($lastIncome as $row)
                                            <tr>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($row->date)->translatedFormat('l, d F Y') }}
                                                 </td>
                                                 <td>
                                                    {{$row->name}}
                                                 </td>
                                                <td>
                                                   {{$row->naration}}
                                                </td>
                                                <td class="text-end">
                                                    Rp. {{number_format($row->kredit,0)}}
                                                </td>
                                            </tr>
                                        @endforeach
                                        
                                    </tbody>
                                </table>
                            </div>
                    </div><!-- end card -->
                </div><!-- end col -->
        
                
            </div><!-- end row -->

          

            

    </div> <!-- end col -->


<div id="settingPeriode" class="modal fade modal-primary">
    <div class="modal-dialog modal-md">
        <div class="modal-content" >
            <div class="modal-header p-3 bg-primary bg-gradient">
                <h5 class="text-white"><i class="ri-filter-fill"> </i>Ganti Periode</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="" name="formPayment" action="{{ url('/dashboard-finance') }}" method="get" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="box-body indoit-form form-horizontal">

                        <div class="row mb-3">
                            <div class="col-md-12 col-lg-12">
                                <div class="form-group">
                                    <label for="year" class="col-md-12">Tahun</label>
                                    <select class="form-control" name="year" id="year" required>
                                        <option value="">Pilih Tahun</option>
                                        @foreach ($yearList as $key => $item)
                                            <option @if($year==$key) selected @endif value="{{ $key }}">{{ $item }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12 col-lg-12">
                                <div class="form-group">
                                    <label class="col-md-12">Bulan</label>
                                    <select class="form-control" name="month" id="month" required>
                                        <option value="">Pilih Bulan</option>
                                        @foreach ($monthList as $key => $item)
                                            <option @if($monthNumeric==$key) selected @endif value="{{ $key }}">{{ $item }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

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

<div id="settingFilter" class="modal fade modal-primary">
    <div class="modal-dialog modal-md">
        <div class="modal-content" id="modal-filter">
            <div class="modal-header p-3 bg-primary bg-gradient">
                <h5 class="text-white"><i class="ri-filter-fill"> </i>Filter Akun</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="" name="formFilter" action="{{ url('/dashboard-filter') }}" method="post" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="box-body indoit-form form-horizontal">

                        <div class="row mb-3">
                            <div class="col-md-12 col-lg-12">
                                <div class="form-group">
                                    <label for="exclude_revenue" class="col-md-12">Pemasukan yang dikecualikan</label>
                                    <select  class="form-control" id="choices-multiple-remove-button" data-choices data-choices-removeItem name="exclude_revenue[]" multiple>
                                        @foreach ($head_account_revenue as $row)
                                            <option value="{{ $row->id }}" 
                                                @if (in_array($row->id,$filter_account_exclude_revenue))
                                                    selected
                                                @endif
                                                
                                                >{{ $row->name }}</option>
                                        @endforeach
                                    </select>

                                   
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12 col-lg-12">
                                <div class="form-group">
                                    <label class="col-md-12">Pengeluaran yang dikecualikan</label>
                                    <select  class="form-control" id="choices-multiple-remove-button" data-choices data-choices-removeItem name="exclude_expense[]" multiple>
                                        @foreach ($head_account_expense as $row)
                                            <option value="{{ $row->id }}"
                                                @if (in_array($row->id,$filter_account_exclude_expense))
                                                    selected
                                                @endif
                                                >{{ $row->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

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

</div>






@endsection

@section('css')
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet"
        type="text/css" />
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet"
        type="text/css" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css"
        integrity="sha512-kq3FES+RuuGoBW3a9R2ELYKRywUEQv0wvPTItv3DSGqjpbNtGWVdvT8qwdKkqvPzT93jp8tSF4+oN4IeTEIlQA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
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
<script src="{{ URL::asset('build/libs/chart.js/chart.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/chartjs.init.js') }}"></script>  

<script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ URL::asset('build/js/app.js') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

{{-- js --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

<!--datatable js-->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ URL::asset('build/js/pages/select2.init.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.10/jquery.mask.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>

    $(document).ready(function () {
        // Inisialisasi Select2 saat modal ditampilkan
        
            $('#year').select2({
                placeholder: "Pilih Tahun",
                width: '100%', // Pastikan lebar dropdown sesuai
            });

            $('#month').select2({
                placeholder: "Pilih Bulan",
                width: '100%', // Pastikan lebar dropdown sesuai
            });
    });
    
    function formatRupiah(angka, prefix) {
        var number_string = angka.toString().replace(/[^,\d]/g, ''),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? 'Rp ' + rupiah : '');
    }
    
    // get colors array from the string
    function getChartColorsArray(chartId) {
        if (document.getElementById(chartId) !== null) {
            var colors = document.getElementById(chartId).getAttribute("data-colors");
            if (colors) {
                colors = JSON.parse(colors);
                return colors.map(function(value) {
                    var newValue = value.replace(" ", "");
                    if (newValue.indexOf(",") === -1) {
                        var color = getComputedStyle(document.documentElement).getPropertyValue(newValue);
                        if (color) return color.trim();
                        else return newValue;;
                    } else {
                        var val = value.split(',');
                        if (val.length == 2) {
                            var rgbaColor = getComputedStyle(document.documentElement).getPropertyValue(val[0]);
                            rgbaColor = "rgba(" + rgbaColor + "," + val[1] + ")";
                            return rgbaColor;
                        } else {
                            return newValue;
                        }
                    }
                });
            }
        }
    }


    // Basic Column Chart
    var chartColumnColors = getChartColorsArray("column_chart");
    if (chartColumnColors) {
        var options = {
            chart: {
                height: 350,
                type: 'bar',
                toolbar: {
                    show: false,
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '45%',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 1,
                colors: ['transparent']
            },
            series: [{
                name: 'Pendapatan',
                data: {!! json_encode($revenueChartPerYear) !!},
            }, {
                name: 'Pengeluaran',
                data: {!! json_encode($expenseChartPerYear) !!},
            }],
            colors: chartColumnColors,
            xaxis: {
                categories: {!! json_encode($categoryChartPerYear) !!},
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return "Rp" + value + "";
                    }
                },
                tickAmount: 5,
            },
            grid: {
                borderColor: '#f1f1f1',
            },
            fill: {
                opacity: 1

            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return "Rp " + val + " "
                    }
                }
            }
        }

        var chart = new ApexCharts(
            document.querySelector("#column_chart"),
            options
        );

        chart.render();
    }

    

    // Basic Column Chart
    var chartColumnColors = getChartColorsArray("column_chart_daily");
    if (chartColumnColors) {
        var options = {
            chart: {
                height: 350,
                type: 'bar',
                toolbar: {
                    show: false,
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '45%',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 0,
                colors: ['transparent']
            },
            series: [{
                name: 'Pendapatan',
                data: {!! json_encode($revenueChartPerDay) !!},
            }],
            colors: chartColumnColors,
            xaxis: {
                categories: {!! json_encode($categoryChartPerDay) !!},
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return "Rp" + value + "";
                    }
                },
                tickAmount: 5,
            },
            grid: {
                borderColor: '#f1f1f1',
            },
            fill: {
                opacity: 1

            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return "Rp " + val + " "
                    }
                }
            }
        }

        var chart = new ApexCharts(
            document.querySelector("#column_chart_daily"),
            options
        );

        chart.render();
    }

    // Balance Overview charts
    var revenueExpensesChartsColors = getChartColorsArray("revenue-expenses-charts");
    if (revenueExpensesChartsColors) {
        var options = {
            series: [{
                name: 'Pendapatan',
                type: 'column',
                data: {!! json_encode($revenueChartPerMonth) !!}
            }, {
                name: 'Pengeluaran',
                type: 'line',
                data: {!! json_encode($expenseChartPerMonth) !!}
            }],
            chart: {
                height: 300,
                type: 'line',
                toolbar: {
                    show: false,
                }
            },
            stroke: {
                width: [0, 4]
            },
            dataLabels: {
                enabled: false,
                enabledOnSeries: [1]
            },
            xaxis: {
                categories: {!! json_encode($categoryChartPerMonth) !!}
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return "Rp" + value + "";
                    }
                },
                tickAmount: 5,
            },
            colors: revenueExpensesChartsColors,
            fill: {
                colors: revenueExpensesChartsColors,
                type: 'solid'
            }
        };
        var chart = new ApexCharts(document.querySelector("#revenue-expenses-charts"), options);
        chart.render();
    }



    var chartColumnStacked100Colors = getChartColorsArray("revenue-category-chart");
    if (chartColumnStacked100Colors) {
        var options = {
            series: {!! json_encode($revenueCategoryInYearChart) !!},
            chart: {
                type: 'bar',
                height: 400,
                stacked: true,
                stackType: '100%',
                toolbar: {
                    show: false,
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    legend: {
                        position: 'bottom',
                        offsetX: -10,
                        offsetY: 0
                    }
                }
            }],
            xaxis: {
                categories: {!! json_encode($categoryChartPerMonth) !!},
            },
            fill: {
                opacity: 1
            },
            legend: {
                position: 'bottom',
                        offsetX: -10,
                        offsetY: 0
            },
            colors: chartColumnStacked100Colors,
        };

        var chart = new ApexCharts(document.querySelector("#revenue-category-chart"), options);
        chart.render();
    }





    var chartColumnStacked100Colors = getChartColorsArray("expense-category-chart");
    if (chartColumnStacked100Colors) {
        var options = {
            series: {!! json_encode($expenseCategoryInYearChart) !!},
            chart: {
                type: 'bar',
                height: 400,
                stacked: true,
                stackType: '100%',
                toolbar: {
                    show: false,
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    legend: {
                        position: 'bottom',
                        offsetX: -10,
                        offsetY: 0
                    }
                }
            }],
            xaxis: {
                categories: {!! json_encode($categoryChartPerMonth) !!},
            },
            fill: {
                opacity: 1
            },
            legend: {
                position: 'bottom',
                        offsetX: -10,
                        offsetY: 0
            },
            colors: chartColumnStacked100Colors,
        };

        var chart = new ApexCharts(document.querySelector("#expense-category-chart"), options);
        chart.render();
    }



    var chartColumnStacked100Colors = getChartColorsArray("revenue-category-chart2");
    if (chartColumnStacked100Colors) {
        var options = {
            series: {!! json_encode($revenueCategoryInYearChart) !!},
            chart: {
                height: 350,
                type: 'bar',
                toolbar: {
                    show: false,
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '45%',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 1,
                colors: ['transparent']
            },
            colors: chartColumnStacked100Colors,
            xaxis: {
                categories: {!! json_encode($categoryChartPerMonth) !!},
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return "Rp" + value + "";
                    }
                },
                tickAmount: 5,
            },
            grid: {
                borderColor: '#f1f1f1',
            },
            fill: {
                opacity: 1

            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return "Rp " + val + " "
                    }
                }
            }

        };

        var chart = new ApexCharts(document.querySelector("#revenue-category-chart2"), options);
        chart.render();
    }





    var chartColumnStacked100Colors = getChartColorsArray("expense-category-chart2");
    if (chartColumnStacked100Colors) {
        var options = {
            series: {!! json_encode($expenseCategoryInYearChart) !!},
            chart: {
                height: 350,
                type: 'bar',
                toolbar: {
                    show: false,
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '45%',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 1,
                colors: ['transparent']
            },
            colors: chartColumnStacked100Colors,
            xaxis: {
                categories: {!! json_encode($categoryChartPerMonth) !!},
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return "Rp" + value + "";
                    }
                },
                tickAmount: 5,
            },
            grid: {
                borderColor: '#f1f1f1',
            },
            fill: {
                opacity: 1

            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return "Rp " + val + " "
                    }
                }
            }

        };

        var chart = new ApexCharts(document.querySelector("#expense-category-chart2"), options);
        chart.render();
    }



</script>
@endsection
