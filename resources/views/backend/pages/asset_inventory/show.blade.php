@extends('layouts.master')
@section('title')
    Detail Asset
@endsection
@section('cssAdded')
    <style>
        .error {
            border: 1px solid red;
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Systems
        @endslot
        @slot('title')
            Detail Asset
        @endslot
    @endcomponent


    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="row gx-lg-5">
                        <div class="col-xl-2 col-md-8 mx-auto">
                            <div class="product-img-slider sticky-side-div">
                                <div class="swiper product-thumbnail-slider p-2 rounded bg-light">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            @if ($data->image)
                                                <img src="{{ asset('uploads/asset_inventories/' . $data->image) }}" alt=""
                                                    class="img-fluid d-block" />
                                            @else
                                                <img src="{{ asset('img/noimage.png') }}" alt=""
                                                    class="img-fluid d-block" />
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end col -->

                        <div class="col-xl-10">
                            <div class="mt-xl-0 mt-5">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        {{-- <h4>{{ $data->name }} <span style="font-size: 12px;font-style:italic;">({{ $data->product_code }})</span></h4> --}}
                                        <h4>{{ $data->name }} </h4>
                                        <div class="hstack gap-3 flex-wrap">
                                            <div class="text-muted">Code : <span
                                                    class="text-body fw-medium">#{{ $data->code }}</span></div>
                                            <div class="vr"></div>
                                            <div class="text-muted">Published : <span
                                                    class="text-body fw-medium">{{ \Carbon\Carbon::parse($data->created_at)->format('d M, Y') }}</span>
                                                by {{ $data->created_by }}</div>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <div>
                                            <a href="{{ route('asset_inventories.index') }}" class="btn btn-light"
                                                data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Kembali"
                                                data-bs-original-title="Kembali"><i
                                                    class="ri-arrow-left-circle-fill align-bottom"></i> Kembali</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 align-items-center mt-3">
                                    <div class="text-muted fs-16">
                                        <span class="badge bg-primary">Kategori:
                                            {{ implode(', ', $data->category) }}</span>&nbsp;
                                        <span class="badge bg-info"><i class="ri-store-2-line"></i> Toko / Store: {{ $data->store_name ?? '-' }}</span>
                                    </div>
                                </div>
                                <div class="row mt-4 g-2">
                                    <div class="col-md-3 col-sm-6">
                                        <div class="p-2 border border-dashed rounded bg-light">
                                            <p class="text-muted mb-1 fs-12">Merk :</p>
                                            <h6 class="mb-0 fw-bold">{{ $data->merk ?: '-' }}</h6>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="p-2 border border-dashed rounded bg-light">
                                            <p class="text-muted mb-1 fs-12">Qty & Harga Satuan :</p>
                                            <h6 class="mb-0 fw-bold">{{ $data->jumlah_unit ?? 1 }} Unit @ Rp {{ number_format($data->harga_per_unit ?? 0, 0, ',', '.') }}</h6>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="p-2 border border-dashed rounded bg-light">
                                            <p class="text-muted mb-1 fs-12">Nilai Perolehan :</p>
                                            <h6 class="mb-0 fw-bold text-primary">Rp {{ number_format($asset_model ? $asset_model->nilai_perolehan : 0, 0, ',', '.') }}</h6>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="p-2 border border-dashed rounded bg-light">
                                            <p class="text-muted mb-1 fs-12">Umur / Batas Susut :</p>
                                            <h6 class="mb-0 fw-bold">{{ (float) ($data->umur_manfaat ?? 4) }} Thn ({{ (int) ($data->masa ?? 48) }} Bln)</h6>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="p-2 border border-dashed rounded bg-light">
                                            <p class="text-muted mb-1 fs-12">Penyusutan Per Bulan :</p>
                                            <h6 class="mb-0 fw-bold text-warning">Rp {{ number_format($asset_model ? $asset_model->penyusutan_per_bulan : 0, 0, ',', '.') }}</h6>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="p-2 border border-dashed rounded bg-light">
                                            <p class="text-muted mb-1 fs-12">Jumlah Susut Terlewat :</p>
                                            <h6 class="mb-0 fw-bold">{{ $asset_model ? $asset_model->jumlah_susut : 0 }} Bulan</h6>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="p-2 border border-dashed rounded bg-light">
                                            <p class="text-muted mb-1 fs-12">Akumulasi Susut :</p>
                                            <h6 class="mb-0 fw-bold text-danger">Rp {{ number_format($asset_model ? $asset_model->akumulasi_penyusutan : 0, 0, ',', '.') }}</h6>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="p-2 border border-dashed rounded bg-light">
                                            <p class="text-muted mb-1 fs-12">Nilai Buku :</p>
                                            <h6 class="mb-0 fw-bold text-success">Rp {{ number_format($asset_model ? $asset_model->nilai_buku : 0, 0, ',', '.') }}</h6>
                                        </div>
                                    </div>
                                </div>


                                <div class="product-content mt-5">
                                    <div>
                                        <div class="mt-3">
                                            <h5 class="fs-14 mt-3">Perubahan Asset</h5>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <form action="#" method="post">
                                                        <input type="hidden" name="_token" id="token"
                                                            value="{{ csrf_token() }}">
                                                        <div class="card">
                                                            <div class="card-body">
                                                                @include('backend.includes.flash')
                                                                <table id="dt_basic"
                                                                    class="table table-bordered nowrap table-striped align-middle"
                                                                    style="width:100%">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="th_no" data-hide="phone"><input
                                                                                    id="main_checkbox" name="main_checkbox"
                                                                                    type="checkbox" value=""></th>
                                                                            <th class="th_no" data-hide="phone">No</th>
                                                                            <th data-class="expand">Tanggal</th>
                                                                            <th data-class="expand">Persentase Penyusutan
                                                                            </th>
                                                                            <th data-class="expand">Kondisi Akhir</th>
                                                                            <th data-class="expand">Categori</th>
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
                                                    <div class="modal-content" id="modal-delete"
                                                        style="border: none !important;">

                                                    </div>
                                                </div>
                                            </div>

                                            {{-- modal filter --}}
                                            <div id="myFilterModal"class="modal fade modal-primary">
                                                <div class="modal-dialog">
                                                    <div class="modal-content" id="modal-filter">
                                                        <div class="modal-header p-3 bg-primary bg-gradient">
                                                            <h5 class="text-white"><i class="ri-filter-line"> </i> Filter
                                                                Data
                                                            </h5>
                                                            <button type="button" class="btn-close text-white"
                                                                data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form id="form_filter" name="form_filter" action=""
                                                            method="post">
                                                            @csrf
                                                            <div class="modal-body">
                                                                <div class="box-body indoit-form form-horizontal">
                                                                    <div class="form-group">
                                                                        <label for=""
                                                                            class="col-sm-12 control-label">Nama
                                                                            Produk</label>
                                                                        <div class="col-sm-12">
                                                                            <input type="text" class="form-control"
                                                                                name="name" id="name"
                                                                                placeholder="nama produk">
                                                                        </div>
                                                                    </div>
                                                                    <br />
                                                                </div><!-- /.box-body -->
                                                            </div>

                                                            <div class="modal-footer bg-opacity-75">
                                                                <button type="button"
                                                                    class="btn btn-outline btn-light pull-left"
                                                                    data-bs-dismiss="modal"><i
                                                                        class="las la-times-circle"></i> Close</button>
                                                                <button type="button" id="btn-reset"
                                                                    onClick="this.form.reset()"
                                                                    class="btn btn-warning pull-left text-light"><i
                                                                        class="las la-undo-alt"></i>
                                                                    Reset</button>
                                                                <button type="button" id="btn-filter"
                                                                    class="btn btn-info pull-left text-light"><i
                                                                        class=" las la-share"></i> Filter</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- product-content -->
                        </div>
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end card body -->
        </div>
        <!-- end card -->
    </div>
    <!-- end col -->
    </div>
    <!--end row-->
    {{-- end modal filter --}}
    {{-- @include('layouts.modal_delete') --}}
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
    <link href="{{ URL::asset('build/libs/dropzone/dropzone.css') }}" rel="stylesheet">
@endsection

@section('script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.colVis.min.js"></script>
    {{-- <script src="{{ URL::asset('build/libs/datatables.net/extensions/export/buttons.bootstrap.min.js')}}"></script> --}}
    <script src="{{ URL::asset('build/libs/datatables.net/extensions/export/buttons.bootstrap5.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ URL::asset('build/js/pages/select2.init.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.10/jquery.mask.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>


    <script src="{{ URL::asset('build/libs/@ckeditor/ckeditor5-build-classic/build/ckeditor.js') }}"></script>

    <script src="{{ URL::asset('build/libs/dropzone/dropzone-min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/ecommerce-product-create.init.js') }}"></script>

    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
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
                buttons: [{
                        extend: 'collection',
                        text: '<i class="las la-file-excel"></i> Export',
                        className: 'child-button',
                        buttons: [{
                            extend: 'excel',
                            text: 'excel',
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6]
                            }
                        }]
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="las la-eye"></i> Show',
                        className: 'child-button'
                    },
                ],
                ajax: {
                    "url": "{{ route('asset_inventories.get_datatable_alat', $data->id) }}",
                    "type": "get",
                    "data": function(data) {
                        data.name = $('#name').val();
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
                        data: 'tanggal',
                        name: 'tanggal'
                    },
                    {
                        data: 'depreciation_percent',
                        name: 'depreciation_percent'
                    },
                    {
                        data: 'last_condition',
                        name: 'last_condition'
                    },
                    {
                        data: 'category',
                        name: 'category'
                    },
                ],

            });
            $('#btn-filter').click(function() { //button filter event click
                table.ajax.reload(); //just reload table
            });
            $('#btn-reset').click(function() { //button reset event click
                table.ajax.reload(); //just reload table
            });
            $('body').on('click', '#main_checkbox', function() {
                if ($(this).is(':checked')) {
                    $('.checkbox_item').prop('checked', true);
                } else {
                    $('.checkbox_item').prop('checked', false);
                }
            });
            $("#dt_basic tbody").on("click", ".btnDelete", function(e) {
                e.preventDefault();
                var id_to_be_deleted = $(this).attr("id");

                $.ajax({
                    type: "POST",
                    url: "{{ route('asset_inventories.delete') }}",
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
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        console.log(xhr.responseText);
                    }
                });
            });
            {{-- Recycle bin tidak digunakan di seco --}}
            table.buttons(1, null).container()
                .addClass('btn-group')
                .appendTo(table.table().container());
        });


        let listReq = ['name', 'product_code', 'qty', 'cogs', 'price_1'];
        let listSelectReq = ['unit_id', 'category_id'];
        $("#btnSubmit").on('click', function(event) {
            let inputCheck = null;
            let selectCheck = null;
            for (let i = 0; i < listReq.length; i++) {
                inputCheck = $("#" + listReq[i]).val();
                if (inputCheck == '') {
                    $("#" + listReq[i]).addClass("error");
                    $("#" + listReq[i] + '_error').empty();
                    $("#" + listReq[i] + '_error').append("Kolom ini wajib diisi!");
                    $("#ckeditor-classic").focus();
                }
            }

            for (let x = 0; x < listSelectReq.length; x++) {
                selectCheck = $("#" + listSelectReq[x] + ' option:selected').val();
                if (inputCheck == '') {
                    $("#" + listSelectReq[x]).addClass("error");
                    $("#" + listSelectReq[x] + '_error').empty();
                    $("#" + listSelectReq[x] + '_error').append("Kolom ini wajib diisi!");
                    $("#ckeditor-classic").focus();
                }
            }
        });

        function handleFileSelect(event) {
            const file = event.target.files[0];
            const img = document.getElementById('product-img');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
        document.getElementById('product-image-input').addEventListener('change', handleFileSelect);
        $('#qty').mask('000.000.000', {
            reverse: true
        });
    </script>
@endsection
