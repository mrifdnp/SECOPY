@extends('layouts.master')
@section('title')
    Laporan Penyusutan Inventaris
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Laporan
        @endslot
        @slot('title')
            Laporan Penyusutan Inventaris
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    @include('backend.includes.flash')

                    {{-- FILTER CUTOFF DATE & EXPORT --}}
                    <div class="row mb-3 align-items-end g-2">
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label fw-bold mb-1"><i class="ri-calendar-event-line me-1"></i>Tanggal Cut-Off Laporan</label>
                            <input type="date" id="filter_cutoff_date" class="form-control" value="{{ $defaultCutoffDate }}">
                        </div>
                        <div class="col-md-5 col-sm-6 d-flex flex-wrap gap-2">
                            <button type="button" id="btn-apply-filter" class="btn btn-primary">
                                <i class="ri-filter-line align-bottom me-1"></i> Terapkan
                            </button>
                            <button type="button" id="btn-export-excel" class="btn btn-success">
                                <i class="ri-file-excel-2-line align-bottom me-1"></i> Export
                            </button>
                            <a href="{{ route('asset_inventories.create') }}" class="btn btn-info text-white">
                                <i class="ri-add-line align-bottom me-1"></i> Tambah
                            </a>
                        </div>
                    </div>

                    <table id="dt_report" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                        <thead>
                            <tr>
                                <th class="th_no">No</th>
                                <th>Nama Barang</th>
                                <th>Picture</th>
                                <th>Golongan</th>
                                <th>Merk</th>
                                <th>Kondisi</th>
                                <th>Umur Manfaat</th>
                                <th>Tgl Perolehan</th>
                                <th>Unit</th>
                                <th>Harga/Unit</th>
                                <th>Nilai Perolehan</th>
                                <th>% Susut/Thn</th>
                                <th>Jumlah Susut</th>
                                <th>Batas Susut</th>
                                <th>Susut/Bulan</th>
                                <th>Akumulasi Susut</th>
                                <th>Nilai Buku</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th class="text-end fw-bold text-nowrap">Total Penyusutan (Dalam Rp)</th>
                                <th id="foot_nilai_perolehan" class="text-end fw-bold foot_nilai_perolehan"></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th id="foot_susut_bulan" class="text-end fw-bold foot_susut_bulan"></th>
                                <th id="foot_akumulasi_susut" class="text-end fw-bold foot_akumulasi_susut"></th>
                                <th id="foot_nilai_buku" class="text-end fw-bold foot_nilai_buku"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
    <style>
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 15px !important;
        }
        .dataTables_wrapper .dataTables_info {
            margin-top: 15px !important;
        }
    </style>
@endsection

@section('script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.colVis.min.js"></script>

    <script>
        $(document).ready(function() {
            var i = 1;
            var table = $('#dt_report').DataTable({
                dom: 'lBfrtip',
                processing: true,
                serverSide: true,
                scrollX: true,
                lengthMenu: [
                    [25, 50, 100, 250, -1],
                    [25, 50, 100, 250, "All"]
                ],
                order: [
                    [1, "asc"]
                ],
                language: {
                    "lengthMenu": "Display _MENU_ "
                },
                buttons: [
                    {
                        extend: 'colvis',
                        text: '<i class="las la-eye"></i> Show Column',
                        className: 'child-button'
                    }
                ],
                ajax: {
                    url: "{{ route('asset_inventories_report.get_datatable') }}",
                    type: "GET",
                    data: function(d) {
                        d.cutoff_date = $('#filter_cutoff_date').val();
                        i = d.start + 1;
                    }
                },
                columns: [
                    {
                        data: 'number',
                        name: 'number',
                        width: '20px',
                        render: function() {
                            return i++;
                        }
                    },
                    { data: 'name', name: 'name' },
                    { data: 'image', name: 'image', orderable: false, searchable: false },
                    { data: 'category', name: 'category' },
                    { data: 'merk', name: 'merk', defaultContent: '-' },
                    { data: 'kondisi', name: 'kondisi' },
                    { data: 'umur_manfaat_tahun', name: 'umur_manfaat_tahun' },
                    { data: 'tgl_perolehan', name: 'tgl_perolehan' },
                    { data: 'jumlah_unit', name: 'jumlah_unit' },
                    { data: 'harga_per_unit', name: 'harga_per_unit' },
                    { data: 'nilai_perolehan', name: 'nilai_perolehan' },
                    { data: 'presentase_susut', name: 'presentase_susut' },
                    { data: 'jumlah_susut', name: 'jumlah_susut' },
                    { data: 'batas_susut', name: 'batas_susut' },
                    { data: 'penyusutan_per_bulan', name: 'penyusutan_per_bulan' },
                    { data: 'akumulasi_penyusutan', name: 'akumulasi_penyusutan' },
                    { data: 'nilai_buku', name: 'nilai_buku' }
                ],
                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();
                    var json = api.ajax.json();
                    if (json && json.totals) {
                        $(api.column(10).footer()).html('<span class="fw-bold">' + json.totals.nilai_perolehan + '</span>');
                        $(api.column(14).footer()).html('<span class="fw-bold">' + json.totals.penyusutan_per_bulan + '</span>');
                        $(api.column(15).footer()).html('<span class="fw-bold">' + json.totals.akumulasi_penyusutan + '</span>');
                        $(api.column(16).footer()).html('<span class="fw-bold">' + json.totals.nilai_buku + '</span>');
                    }
                },
                drawCallback: function(settings) {
                    var json = settings.json;
                    if (json && json.totals) {
                        $('.dataTables_scrollFoot .foot_nilai_perolehan, .foot_nilai_perolehan').html('<span class="fw-bold">' + json.totals.nilai_perolehan + '</span>');
                        $('.dataTables_scrollFoot .foot_susut_bulan, .foot_susut_bulan').html('<span class="fw-bold">' + json.totals.penyusutan_per_bulan + '</span>');
                        $('.dataTables_scrollFoot .foot_akumulasi_susut, .foot_akumulasi_susut').html('<span class="fw-bold">' + json.totals.akumulasi_penyusutan + '</span>');
                        $('.dataTables_scrollFoot .foot_nilai_buku, .foot_nilai_buku').html('<span class="fw-bold">' + json.totals.nilai_buku + '</span>');
                    }
                }
            });

            $('#btn-apply-filter').on('click', function() {
                table.ajax.reload();
            });

            $('#btn-export-excel').on('click', function() {
                var cutoffDate = $('#filter_cutoff_date').val();
                window.location.href = "{{ route('asset_inventories_report.export') }}?cutoff_date=" + cutoffDate;
            });
        });
    </script>
@endsection
