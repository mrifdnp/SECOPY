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
            <div class="card">
                <div class="card-body">
                    @include('backend.includes.flash')
                    <div class="box">
                        <!-- /.box-header -->
                        <div class="box-body indoit-form">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('sale-transaction.exportPenjualan') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="firstNameinput" class="form-label">Jenis Laporan</label>
                                            <select name="jenis_report" id="jenis_report" class="form-select selectreport"
                                                style="margin-top:-10px !important;" required>
                                                <option value="" disabled>Pilih Jenis Report</option>
                                                <option value="penjualan" selected>Penjualan</option>
                                                <option value="detail_penjualan">Detail Penjualan</option>
                                                <option value="sales_person_report">Performa Tim Sales</option>
                                                <option value="item_penjualan">Data Penjualan Barang</option>
                                                <option value="penjualan_pelanggan">Penjualan Per Pelanggan</option>
                                            </select>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="lastNameinput" class="form-label">Periode</label>
                                            <input type="text" id="rentang_report" name="rentang_report"
                                                class="form-control flatpickr-input active" data-provider="flatpickr"
                                                data-date-format="d-m-Y" data-range-date="true"
                                                placeholder="Pilih periode laporan" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="" class="form-label" style="width: 100%;">&nbsp;</label>

                                            <button type="button" class="btn btn-primary" id="btnPreview">
                                                <i class="las la-file-alt"></i> Preview
                                            </button>
                                            <button type="submit" class="btn btn-success" id="btnExport">
                                                <i class="las la-file-excel"></i> Export Excel
                                            </button>

                                        </div>
                                    </div>

                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>

    {{-- hasil --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="box">
                        <!-- /.box-header -->
                        <div class="box-body indoit-form">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive tabeldata">
                                        <table class="table table-nowrap tabeldata2" style="position: relative;"
                                            id="tableRender">
                                            {!! $dataRender !!}
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>
    <!--end row-->


@endsection

@section('css')
    <!-- <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet"
        type="text/css" />
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" /> -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css"
        integrity="sha512-kq3FES+RuuGoBW3a9R2ELYKRywUEQv0wvPTItv3DSGqjpbNtGWVdvT8qwdKkqvPzT93jp8tSF4+oN4IeTEIlQA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .tabeldata {
            overflow-y: auto;
            overflow-x: auto;
            height: 100vh;
        }
    </style>
@endsection

@section('script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <!-- <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ URL::asset('build/js/pages/select2.init.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.10/jquery.mask.js"></script>
    {{-- <script src="https://cdn-script.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script> --}}
    <script>
        $('.selectreport').select2();
        let jenis = null;
        let rentang = null;
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr("#rentang_report", {
                mode: "range",
                dateFormat: "d-m-Y",
                defaultDate: ["{{ $tglAwal }}", "{{ $tglAkhir }}"]
            });
        });

        $(document).on('click', '#btnPreview', function() {
            event.preventDefault();
            jenis = $("#jenis_report option:selected").val() != '' ? $("#jenis_report option:selected").val() :
                null;
            rentang = $("#rentang_report").val() != '' ? $("#rentang_report").val() : null;
            if (!jenis || !rentang) {
                alert('Silakan pilih jenis terlebih dahulu dan rentang report!');
            } else {
                $.ajax({
                    url: "{{ route('sale-transaction.previewPenjualan') }}",
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        jenis: jenis,
                        rentang: rentang
                    },
                    cache: false,
                    dataType: 'json',
                    success: function(response) {
                        $("#tableRender").empty();
                        $("#tableRender").append(response.data);
                    }
                });
            }
        });
    </script>
@endsection
