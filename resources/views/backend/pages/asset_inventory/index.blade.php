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
            <form action="#" method="post">
                <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                <div class="card">
                    <div class="card-body">
                        @include('backend.includes.flash')



                        <table id="dt_basic" class="table table-bordered nowrap table-striped align-middle"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th class="th_no" data-hide="phone"><input id="main_checkbox" name="main_checkbox"
                                            type="checkbox" value=""></th>
                                    <th class="th_no" data-hide="phone">No</th>
                                    <th data-class="expand">Picture</th>
                                    <th data-class="expand">Nama Barang</th>
                                    <th data-class="expand">Golongan</th>
                                    <th data-class="expand">Merk</th>
                                    <th data-class="expand">Kondisi</th>
                                    <th data-class="expand">Umur Manfaat</th>
                                    <th data-class="expand">Tgl Perolehan</th>
                                    <th data-class="expand">Unit</th>
                                    <th data-class="expand">Harga/Unit</th>
                                    <th data-class="expand">Total Nilai</th>
                                    <th>Action</th>
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
            <div class="modal-content" id="modal-delete" style="border: none !important;">

            </div>
        </div>
    </div>

    {{-- modal filter --}}
    <div id="myFilterModal"class="modal fade modal-primary">
        <div class="modal-dialog">
            <div class="modal-content" id="modal-filter">
                <div class="modal-header p-3 bg-primary bg-gradient">
                    <h5 class="text-white"><i class="ri-filter-line"> </i> Filter Data</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form_filter" name="form_filter" action="" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="box-body indoit-form form-horizontal">
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Nama Alat</label>
                                <div class="col-sm-12">
                                    <input type="text" class="form-control" name="name" id="name"
                                        placeholder="nama produk">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Store / Toko</label>
                                <div class="col-sm-12">
                                    <select class="form-control" id="modal_store_id" name="modal_store_id">
                                        @foreach ($stores ?? [] as $st)
                                            <option value="{{ $st->id }}" {{ ($activeStoreId == $st->id) ? 'selected' : '' }}>
                                                {{ $st->name }} {{ ($activeStoreId == $st->id) ? '(Store Aktif)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- <div class="form-group mt-3">
                                <label for="" class="col-sm-12 control-label">Category</label>
                                <div class="col-sm-12">
                                    <select class="form-control js-example-basic-single" id="category_id"
                                        name="category_id">
                                        <option value="" selected>-- Silakan Pilih --</option>
                                        @foreach ($categories as $item => $value)
                                            <!-- Updated this loop -->
                                            <option value="{{ $item }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> --}}
                            <br />
                        </div>
                    </div>

                    <div class="modal-footer bg-opacity-75">
                        <button type="button" class="btn btn-outline btn-light pull-left" data-bs-dismiss="modal"><i
                                class="las la-times-circle"></i> Close</button>
                        <button type="button" id="btn-reset" onClick="this.form.reset()"
                            class="btn btn-warning pull-left text-light"><i class="las la-undo-alt"></i> Reset</button>
                        <button type="button" id="btn-filter" class="btn btn-info pull-left text-light"><i
                                class=" las la-share"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- end modal filter --}}
    {{-- <div id="myFilterModalPdf" class="modal fade modal-primary">
        <div class="modal-dialog">
            <div class="modal-content" id="modal-filter">
                <div class="modal-header p-3 bg-primary bg-gradient">
                    <h5 class="text-white"><i class="ri-filter-line"> </i>Filter Show Scanner</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form_filter" name="form_filter" action="" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="box-body indoit-form form-horizontal">
                            <div class="form-group">
                                <label for="" class="col-sm-12 control-label">Tampilan</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="kode_produk" name="filter_option[]"
                                        value="1" checked>
                                    <!-- Checkbox ini aktif secara default -->
                                    <label class="form-check-label" for="kode_produk">Kode Produk</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="nama_produk"
                                        name="filter_option[]" value="2" checked>
                                    <!-- Checkbox ini aktif secara default -->
                                    <label class="form-check-label" for="nama_produk">Nama Produk</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="harga_produk"
                                        name="filter_option[]" value="3" checked>
                                    <label class="form-check-label" for="harga_produk">Harga Produk</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-opacity-75">
                        <button type="button" class="btn btn-outline btn-light pull-left" data-bs-dismiss="modal"><i
                                class="las la-times-circle"></i> Close</button>
                        <button type="button" id="btn-reset" onClick="this.form.reset()"
                            class="btn btn-warning pull-left text-light"><i class="las la-undo-alt"></i> Reset</button>
                        <button type="button" id="btn-export" class="btn btn-danger pull-left text-light"
                            data-bs-dismiss="modal"><i class="las la-print"></i> Export</button>
                    </div>
                </form>
            </div>
        </div>
    </div> --}}


    <div id="myPeringatanModal" class="modal fade modal-primary">
        <div class="modal-dialog"> <!-- Ukuran default, tanpa modal-lg -->
            <div class="modal-content" id="modal-filter">
                <div class="modal-header p-3 bg-primary bg-gradient">
                    <h5 class="text-white"><i class="bx bx-error p-2"> </i>Peringatan</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="box-body indoit-form form-horizontal">
                        <div class="alert alert-danger alert-borderless mb-xl-0" role="alert">
                            <strong>Silahkan pilih barang terlebih dahulu.</strong>
                        </div>
                    </div><!-- /.box-body -->
                </div>
                <div class="modal-footer bg-opacity-75">
                    <button type="button" class="btn btn-outline btn-light pull-left" data-bs-dismiss="modal"><i
                            class="las la-times-circle"></i> Close</button>
                </div>
            </div>
        </div>
    </div>


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
                buttons: [
                    @if (\RuleHelper::UserHasPermission('create', false))
                        {
                            text: '<i class="las la-plus-square text-success"></i> Create',
                            className: 'child-button',
                            init: function(e, dt, button, config) {
                                $(dt).attr('href', "{{ route('asset_inventories.create') }}");
                            },
                            action: function(e, dt, button, config) {
                                window.location = "{{ route('asset_inventories.create') }}"
                            },
                        },
                    @endif
                    @if (\RuleHelper::UserHasPermission('delete', false))
                        {
                            text: '<i class="las la-trash text-danger"></i> Bulk Delete',
                            className: 'child-button',
                            action: function(e, dt, button, config) {
                                e.preventDefault();

                                var id_to_be_deleted = $("#dt_basic tbody input:checkbox:checked")
                                    .map(function() {
                                        return $(this).val();
                                    }).get();

                                $.ajax({
                                    type: "POST",
                                    url: "{{ route('asset_inventories.delete') }}",
                                    data: {
                                        id_to_be_deleted: id_to_be_deleted,
                                        delete_type: 'softdelete'
                                    },
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                            'content')
                                    },
                                    success: function(data) {
                                        $('#myModal').modal('show');
                                        $('#modal-delete').html(data);
                                    }
                                });
                            }
                        },
                    @endif {
                        text: '<i class="las la-file-excel text-success"></i> Export',
                        className: 'child-button',
                        action: function(e, dt, button, config) {
                            var currentStore = $('#filter_store_id').val();
                            window.location.href = "{{ route('asset_inventories.export') }}?store_id=" + currentStore;
                        }
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="las la-eye"></i> Show',
                        className: 'child-button'
                    },
                    {
                        text: '<i class="las la-filter"></i> Filter',
                        className: 'child-button',
                        action: function(e, dt, button, config) {
                            $('#myFilterModal').modal('show');
                        }
                    },

                ],
                ajax: {
                    "url": "{{ route('asset_inventories.get_datatable') }}",
                    "type": "get",
                    "data": function(data) {
                        data.name = $('#name').val();
                        data.category_id = $('#category_id option:selected').val();
                        data.store_id = "{{ $activeStoreId }}";
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
                        data: 'image',
                        name: 'image',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'category',
                        name: 'category'
                    },
                    {
                        data: 'merk',
                        name: 'merk',
                        defaultContent: '-'
                    },
                    {
                        data: 'kondisi',
                        name: 'kondisi'
                    },
                    {
                        data: 'umur_manfaat_tahun',
                        name: 'umur_manfaat_tahun'
                    },
                    {
                        data: 'tgl_perolehan',
                        name: 'tgl_perolehan'
                    },
                    {
                        data: 'jumlah_unit',
                        name: 'jumlah_unit'
                    },
                    {
                        data: 'harga_per_unit',
                        name: 'harga_per_unit'
                    },
                    {
                        data: 'nilai_perolehan',
                        name: 'nilai_perolehan'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        width: '100px'
                    }
                ],


            });

            $('#btn-filter').click(function() {
                table.ajax.reload();
                $('#myFilterModal').modal('hide');
            });

            $('#btn-reset').click(function() {
                $('#name').val('');
                table.ajax.reload();
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
                    }
                });
            });
            {{-- Recycle bin tidak digunakan di seco --}}
            table.buttons(1, null).container()
                .addClass('btn-group')
                .appendTo(table.table().container());
        });

        function exportExcelRumus() {
            var currentStore = $('#filter_store_id').val();
            window.location.href = "{{ route('asset_inventories.export') }}?store_id=" + currentStore;
        }
    </script>


    <!-- dashboard init -->
@endsection
