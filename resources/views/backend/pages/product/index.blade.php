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

    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header align-items-center d-flex" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse" style="cursor: pointer;">
                    <h4 class="card-title mb-0 flex-grow-1">
                        <i class="ri-dashboard-line"></i> Ringkasan & Filter Produk 
                        <span class="fs-12 text-muted fw-normal fst-italic ms-1">(Menampilkan data sesuai toko yang aktif)</span>
                    </h4>
                    <i class="ri-arrow-down-s-line fs-18"></i>
                </div>
                <div id="filterCollapse" class="collapse" aria-labelledby="filterCard">
                    <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <!-- Stat 1: Total Produk -->
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><i class="bx bx-box text-primary me-1"></i> Total Produk</h6>
                                <p class="text-muted mb-0 fs-12">Keseluruhan produk & varian</p>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary fs-14"><span class="counter-value" data-target="{{ $totalProducts ?? 0 }}">0</span> Produk / <span class="counter-value" data-target="{{ $totalProductVariants ?? 0 }}">0</span> Varian</span>
                            </div>
                        </li>
                        
                        <!-- Stat 2: Nilai Aset -->
                        @if(in_array(session()->get('current_role')->name, ['super', 'admin']))
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><i class="bx bx-wallet text-primary me-1"></i> Total Nilai Aset</h6>
                                <p class="text-muted mb-0 fs-12">Estimasi total nilai aset stok</p>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="{{ route('product.assetBreakdown') }}" class="btn btn-sm btn-soft-primary fw-bold">
                                    Rp <span class="counter-value" data-target="{{ $totalAssetValue ?? 0 }}">0</span> <i class="ri-arrow-right-line ms-1"></i>
                                </a>
                            </div>
                        </li>
                        @endif
                        
                        <!-- Filter 1: Marketplace -->
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><i class="bx bx-store text-primary me-1"></i> Tersinkronisasi Marketplace</h6>
                                <p class="text-muted mb-0 fs-12">Tampilkan produk yang sudah tayang di E-Commerce</p>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary me-3" id="marketplaceCountText"><span class="counter-value" data-target="{{ $ecommerceProductsCount ?? 0 }}">0</span> / {{ $totalProducts ?? 0 }} Produk</span>
                                <div class="form-check form-switch form-switch-primary form-switch-md">
                                    <input class="form-check-input" type="checkbox" role="switch" id="toggleMarketplaceState">
                                </div>
                            </div>
                        </li>

                        <!-- Filter 2: Harga Terisi -->
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><i class="bx bx-money text-primary me-1"></i> Harga Terisi</h6>
                                <p class="text-muted mb-0 fs-12">Tampilkan produk yang sudah memiliki harga jual</p>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary me-3" id="priceCountBadge"><span class="counter-value" data-target="{{ $filledPriceCount ?? 0 }}">0</span> / {{ $totalProductVariants ?? 0 }} Varian</span>
                                <div class="form-check form-switch form-switch-primary form-switch-md">
                                    <input class="form-check-input" type="checkbox" role="switch" id="toggleFilterPrice">
                                </div>
                            </div>
                        </li>

                        <!-- Filter 3: Stok Ada -->
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><i class="bx bx-package text-primary me-1"></i> Stok Ada</h6>
                                <p class="text-muted mb-0 fs-12">Tampilkan produk yang stoknya tersedia (>0)</p>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary me-3" id="stockCountBadge">{{ $inStockCount ?? 0 }} Varian Ada Stok</span>
                                <div class="form-check form-switch form-switch-primary form-switch-md">
                                    <input class="form-check-input" type="checkbox" role="switch" id="toggleFilterStock">
                                </div>
                            </div>
                        </li>

                        <!-- Filter 4: Barang Minus -->
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><i class="bx bx-error-circle text-primary me-1"></i> Barang Minus</h6>
                                <p class="text-muted mb-0 fs-12">Tampilkan produk yang stoknya kurang dari nol (<0)</p>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary me-3" id="minusStockCountBadge">{{ $minusStockCount ?? 0 }} Varian Minus</span>
                                <div class="form-check form-switch form-switch-primary form-switch-md">
                                    <input class="form-check-input" type="checkbox" role="switch" id="toggleFilterMinus">
                                </div>
                            </div>
                        </li>

                        <!-- Filter 5: SKU Belum -->
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><i class="bx bx-barcode text-primary me-1"></i> SKU Belum Lengkap</h6>
                                <p class="text-muted mb-0 fs-12">Tampilkan produk yang belum memiliki SKU</p>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary me-3"><span class="counter-value" data-target="{{ $incompleteSkuProductsCount ?? 0 }}">0</span> / {{ $totalProducts ?? 0 }} Produk</span>
                                <div class="form-check form-switch form-switch-primary form-switch-md">
                                    <input class="form-check-input" type="checkbox" role="switch" id="toggleFilterSku">
                                </div>
                            </div>
                        </li>

                        <!-- Filter 6: COGM Belum -->
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><i class="bx bx-calculator text-primary me-1"></i> COGM Belum Terisi</h6>
                                <p class="text-muted mb-0 fs-12">Tampilkan produk yang belum memiliki nilai COGM</p>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary me-3"><span class="counter-value" data-target="{{ $incompleteCogmCount ?? 0 }}">0</span> / {{ $totalProducts ?? 0 }} Produk</span>
                                <div class="form-check form-switch form-switch-primary form-switch-md">
                                    <input class="form-check-input" type="checkbox" role="switch" id="toggleFilterCogm">
                                </div>
                            </div>
                        </li>

                        <!-- Filter 7: Berat Belum -->
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><i class="bx bx-dumbbell text-primary me-1"></i> Berat Belum Terisi</h6>
                                <p class="text-muted mb-0 fs-12">Tampilkan produk yang belum memiliki data berat</p>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary me-3"><span class="counter-value" data-target="{{ $incompleteWeightCount ?? 0 }}">0</span> / {{ $totalProducts ?? 0 }} Produk</span>
                                <div class="form-check form-switch form-switch-primary form-switch-md">
                                    <input class="form-check-input" type="checkbox" role="switch" id="toggleFilterWeight">
                                </div>
                            </div>
                        </li>

                    </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="#" method="post">
                <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                <div class="card">
                    <div class="card-body">
                        @include('backend.includes.flash')
                        <table id="dt_basic" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="th_no" data-hide="phone"><input id="main_checkbox" name="main_checkbox" type="checkbox" value=""></th>
                                    <th class="th_no" data-hide="phone">No</th>
                                    <th data-class="expand">Title</th>
                                    <th>Aksi</th>
                                    <th data-class="expand">Top Pick</th>
                                    <th data-class="expand">Ecommerce</th>
                                    <th data-class="expand">Status Foto</th>
                                    <th data-class="expand">Main Category</th>
                                    <th data-class="expand">Category</th>
                                    <th data-class="expand">Material / Consignment</th>
                                    <th data-class="expand">SKU Terisi</th>
                                    <th data-hide="phone">Created at</th>
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
                                <label class="col-sm-12 control-label">Title</label>
                                <div class="col-sm-12">
                                    <input value="" type="text" class="form-control" name="title" id="title" placeholder="" size="40">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="col-sm-12 control-label">SKU</label>
                                <div class="col-sm-12">
                                    <input value="" type="text" class="form-control" name="sku" id="filter_sku" placeholder="Cari berdasarkan SKU..." size="40">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="col-sm-12 control-label">Main Category</label>
                                <div class="col-sm-12">
                                    <select name="main_category_id" id="main_category_id" class="form-control js-example-basic-single select2">
                                        <option value="" selected>Silakan Pilih</option>
                                        @foreach ($main_categories as $main_category)
                                            <option value="{{ $main_category->id }}">{{ $main_category->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="col-sm-12 control-label">Category</label>
                                <div class="col-sm-12">
                                    <select name="category_id" id="category_id" class="form-control js-example-basic-single select2">
                                        <option value="" selected>Silakan Pilih</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="col-sm-12 control-label">Material / Consignment</label>
                                <div class="col-sm-12">
                                    <select name="material_id" id="filter_material_id" class="form-control js-example-basic-single select2">
                                        <option value="" selected>Silakan Pilih</option>
                                        @foreach ($materials as $material)
                                            <option value="{{ $material->id }}">{{ $material->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="col-sm-12 control-label">Top Pick</label>
                                <div class="col-sm-12">
                                    <select class="form-control" name="top_pick" id="top_pick">
                                        <option value="" selected="selected">Silakan Pilih Top Pick</option>
                                        <option value="0">Tidak</option>
                                        <option value="1">Ya</option>
                                    </select>
                                </div>
                            </div>

                        </div><!-- /.box-body -->
                    </div>

                    <div class="modal-footer bg-opacity-75">
                        <button type="button" class="btn btn-outline btn-light pull-left" data-bs-dismiss="modal"><i class="las la-times-circle"></i> Close</button>
                        <button type="button" id="btn-reset" onClick="this.form.reset()" class="btn btn-warning pull-left text-light"><i class="las la-undo-alt"></i> Reset</button>
                        <button type="submit" id="btn-filter" class="btn btn-info pull-left text-light"><i class=" las la-share"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- end modal filter --}}

    {{-- Modal Create --}}
    <div id="createModal" class="modal fade modal-primary">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header p-3 bg-primary bg-gradient">
                    <h5 class="text-white"><i class="las la-plus-circle"></i> Create {{ $titleHeader->name }}</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form_create" method="POST" action="{{ route('product.store') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="modal-body">
                        <div class="box-body indoit-form form-horizontal" novalidate>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Title</label>
                                <div class="col-sm-10 w-100"><input type="text" class="form-control" name="title" id="title" placeholder="Nama Produk" size="50" required>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Main Category</label>
                                <div class="col-sm-10 w-100">
                                    <select class="form-control js-example-basic-single select2" name="main_category_id" id="modal_main_category_id" required>
                                        <option value="">Pilih Main Category</option>
                                        @foreach ($main_categories as $main_category)
                                            <option value="{{ $main_category->id }}">{{ $main_category->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Category</label>
                                <div class="col-sm-10 w-100">
                                    <select class="form-control js-example-basic-single select2" name="category_id" id="modal_category_id" required>
                                        <option value="">Pilih Kategori</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" data-main="{{ $category->main_categories_id }}">{{ $category->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Material / Consignment (Opsional)</label>
                                <div class="col-sm-10 w-100">
                                    <select class="form-control js-example-basic-single select2" name="material_id" id="modal_material_id">
                                        <option value="">Pilih Material</option>
                                        @foreach ($materials as $material)
                                            <option value="{{ $material->id }}">{{ $material->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="" class="col-sm-12 control-label">Top Pick</label>
                                <div class="col-sm-10 w-100">
                                    <div class="radio">
                                        <input class="form-check-input" type="radio" id="Tidak" name="top_pick" value="0" />
                                        <label for="Tidak">Tidak</label>
                                        <input class="form-check-input" type="radio" id="Ya" name="top_pick" value="1" />
                                        <label for="Ya">Ya</label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer bg-opacity-75">
                        <button type="button" class="btn btn-outline btn-light" data-bs-dismiss="modal">
                            <i class="las la-times-circle"></i> Close
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="las la-save"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Modal Create --}}

    <!-- form import -->
    <form id="form_import" name="form_import" action="" method="post">
        <div id="modalImport"class="modal fade modal-primary">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="fa fa-info-circle"> </i> Import Data</h4>
                    </div>
                    <div class="modal-body">
                        <div class="box-body indoit-form form-horizontal">
                            <div class="col-md-2"></div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <button class="btn btn-sm btn-success" id="btnDownloadTemplate" data-tabel="products"><i class="fa fa-download"></i> Download template</button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <button class="btn btn-sm btn-info" id="btnImportData"><i class="fa fa-upload"></i> Import Data</button>
                                </div>
                            </div>
                            <div class="col-md-2"></div>
                        </div><!-- /.box-body -->
                    </div>
                    <div class="modal-footer">
                        <button id="btn-reset" onClick="this.form.reset()" type="button" class="btn btn-outline pull-left">Reset</button>
                        <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- end form import -->

    {{-- Import Product Modal --}}
    <x-modal id="import-product-modal" title="Import Product">
        <div class="form-steps">
            {{-- Navigation --}}
            <div class="step-arrow-nav mb-4">
                <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-product-import-template" data-bs-toggle="pill" data-bs-target="#content-product-import-template"">Template</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-product-import-rules" data-bs-toggle="pill" data-bs-target="#content-product-import-rules">Peraturan</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-product-import-download" data-bs-toggle="pill" data-bs-target="#content-product-import-download">Upload</button>
                    </li>
                </ul>
            </div>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="content-product-import-template">
                    <div class="d-flex flex-column align-items-center justify-content-center text-center" style="min-height: 200px;">
                        <i class="ri-download-2-fill" style="font-size: 4rem;"></i>
                        <form action="{{ route('import.downloadTemplate') }}" method="POST" class="mt-2" id="form_download_template">
                            @csrf
                            <input type="hidden" name="type" value="product">
                            <button class="btn btn-primary">Download Template</button>
                        </form>
                    </div>
                </div>
                <!-- end tab pane -->
                <div class="tab-pane fade" id="content-product-import-rules">
                    <p>Setiap kolom mempunyai peraturannya tersendiri, ikuti aturan untuk setiap kolom untuk memastikan data bisa terimport</p>
                    <table class="table table-bordered">
                        @foreach ($importProductRules as $field => $rule)
                            <tr>
                                <th>{{ $field }}</th>
                                <td>{{ $rule }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <!-- end tab pane -->
                <div class="tab-pane fade" id="content-product-import-download" role="tabpanel">
                    <div class="d-flex flex-column align-items-center justify-content-center text-center" style="min-height: 200px; width:100%;">
                        <i class="ri-upload-2-fill" style="font-size: 4rem;"></i>
                        <form action="{{ route('import.product') }}" method="POST" class="mt-2 w-100" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="file" id="file" class="form-control mb-2 w-100" accept=".csv">
                            <button class="btn btn-primary w-100">Upload</button>
                        </form>
                    </div>
                </div>
                <!-- end tab pane -->
            </div>
        </div>
    </x-modal>
    {{-- End Import Product Modal --}}

    {{-- Import Product Detail Modal --}}
    <x-modal id="import-product-detail-modal" title="Import Product Detail">
        <div class="form-steps">
            {{-- Navigation --}}
            <div class="step-arrow-nav mb-4">
                <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-product-detail-import-template" data-bs-toggle="pill" data-bs-target="#content-product-detail-import-template"">Template</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-product-detail-import-rules" data-bs-toggle="pill" data-bs-target="#content-product-detail-import-rules">Peraturan</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-product-detail-import-download" data-bs-toggle="pill" data-bs-target="#content-product-detail-import-download">Upload</button>
                    </li>
                </ul>
            </div>

            {{-- Content --}}
            <div class="tab-content">
                <div class="tab-pane fade show active" id="content-product-detail-import-template">
                    <form action="{{ route('import.downloadProductDetailTemplate') }}" method="POST" class="mt-3" id="form_download_template_product_detail">
                        @csrf
                        <div class="mb-2 text-start">
                            <label for="store-select-list" class="form-label">Pilih Toko</label>
                            <select class="form-select select2" id="store-select-list" name="store_id" required>
                                <option value="">Pilih Toko</option>
                                <option value="all">Semua Cabang</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2 text-start">
                            <label for="import_main_category_id" class="form-label">Main Category (Opsional)</label>
                            <select name="main_category_id" id="import_main_category_id" class="form-select select2">
                                <option value="">Semua Main Category</option>
                                @foreach ($main_categories as $main_category)
                                    <option value="{{ $main_category->id }}">{{ $main_category->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2 text-start">
                            <label for="import_category_id" class="form-label">Category (Opsional)</label>
                            <select name="category_id" id="import_category_id" class="form-select select2">
                                <option value="">Semua Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" data-main="{{ $category->main_categories_id }}">{{ $category->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 text-start">
                            <label for="import_material_id" class="form-label">Material / Consignment (Opsional)</label>
                            <select name="material_id" id="import_material_id" class="form-select select2">
                                <option value="">Semua Material</option>
                                @foreach ($materials as $material)
                                    <option value="{{ $material->id }}">{{ $material->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 text-start form-check ms-1">
                            <input type="checkbox" name="empty_sku_only" value="1" class="form-check-input" id="empty_sku_only">
                            <label class="form-check-label" for="empty_sku_only">Hanya download produk yang belum memiliki SKU</label>
                        </div>
                        <div class="mb-3 text-start form-check ms-1">
                            <input type="checkbox" name="empty_cogm_only" value="1" class="form-check-input" id="empty_cogm_only">
                            <label class="form-check-label" for="empty_cogm_only">Hanya download produk yang belum memiliki COGM</label>
                        </div>
                        <button id="btn_download_product_detail" class="btn btn-primary" disabled style="width: 100%">Download Template</button>
                    </form>
                </div>

                <div class="tab-pane fade" id="content-product-detail-import-rules">
                    <p>Setiap kolom mempunyai peraturannya tersendiri, ikuti aturan untuk setiap kolom untuk memastikan data bisa terimport</p>
                    <table class="table table-bordered">
                        @foreach ($importProductDetailRules as $field => $rule)
                            <tr>
                                <th>{{ $field }}</th>
                                <td>{{ $rule }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>

                <div class="tab-pane fade" id="content-product-detail-import-download" role="tabpanel">
                    <form id="upload-product-detail" action="{{ route('import.productDetail') }}" method="POST" class="mt-2 w-100" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2 text-start">
                            <label for="store-select-list" class="form-label">Pilih Toko</label>
                            <select id="store-select-list-upload" class="form-select select2" name="store_id" required>
                                <option value="">Pilih Toko</option>
                                <option value="all">Semua Cabang</option>
                                @foreach ($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="file" name="file" id="file" class="form-control mb-2 w-100" accept=".csv" required>
                        <button class="btn btn-primary w-100 text-center">Upload</button>
                    </form>
                </div>
            </div>
        </div>
    </x-modal>
    {{-- End Import Product Detail Modal --}}

    {{-- Import Image Modal --}}
    <x-modal id="import-image-modal" title="Import Gambar Produk">
        <div class="form-steps">
            <div class="step-arrow-nav mb-4">
                <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-image-import-template" data-bs-toggle="pill" data-bs-target="#content-image-import-template">Template</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-image-import-download" data-bs-toggle="pill" data-bs-target="#content-image-import-download">Upload</button>
                    </li>
                </ul>
            </div>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="content-image-import-template">
                    <div class="d-flex flex-column align-items-center justify-content-center text-center" style="min-height: 200px;">
                        <i class="ri-image-add-fill" style="font-size: 4rem;"></i>
                        <form action="{{ route('product.download_image_template') }}" method="GET" class="mt-2 w-100 px-4">
                            <div class="mb-3 text-start">
                                <label for="download_type" class="form-label">Tipe Data Produk:</label>
                                <select name="download_type" id="download_type" class="form-select">
                                    <option value="all">Semua Produk (Total)</option>
                                    <option value="empty_only">Hanya Produk Tanpa Gambar Utama</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Download Template CSV</button>
                        </form>
                        <p class="text-muted mt-2 small px-4">Template ini akan otomatis terisi dengan ID dan Nama produk. Anda tinggal mengisi kolom gambar yang diinginkan (main_pic, hover_pic, pic_1, dll).</p>
                    </div>
                </div>
                <div class="tab-pane fade" id="content-image-import-download" role="tabpanel">
                    <div class="d-flex flex-column align-items-center justify-content-center text-center" style="min-height: 200px; width:100%;">
                        <i class="ri-upload-2-fill" style="font-size: 4rem;"></i>
                        <form action="{{ route('product.import_images') }}" method="POST" class="mt-2 w-100" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="file" id="file_image" class="form-control mb-2 w-100" accept=".csv" required>
                            <button class="btn btn-primary w-100">Upload CSV</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </x-modal>
    {{-- End Import Image Modal --}}

    {{-- @include('layouts.modal_delete') --}}
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
    <script src="{{ URL::asset('build/libs/velzon-dependencies/pdfmake-0.1.53/vfs_fonts.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/pdfmake-0.1.53/pdfmake.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jszip-3.1.3/jszip.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-1.2.4/buttons.colVis.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/datatables.net/extensions/export/buttons.bootstrap5.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js') }}"></script>
    <script src="{{ asset('build/js/pages/toast.init.js') }}"></script>

    <script>
        var state = {
            dataTable: null,
            filter_incomplete_sku: false,
            filter_marketplace: false,
            filter_incomplete_price: false,
            filter_incomplete_cogm: false
        }

        function newExportAction(e, dt, button, config) {
            var self = this;
            var oldStart = dt.settings()[0]._iDisplayStart;
            dt.one('preXhr', function (e, s, data) {
                // Just this once, load all data from the server...
                data.start = 0;
                data.length = -1;
                dt.one('preDraw', function (e, settings) {
                    // Call the original action function
                    if (button[0].className.indexOf('buttons-excel') >= 0) {
                        $.fn.dataTable.ext.buttons.excelHtml5.available(dt, config) ?
                            $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config) :
                            $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
                    }
                    dt.one('preXhr', function (e, s, data) {
                        // DataTables thinks the first item displayed is index 0, but we're not drawing that.
                        // Set the property to what it was before exporting.
                        settings._iDisplayStart = oldStart;
                        data.start = oldStart;
                    });
                    // Reload the grid with the original page. Otherwise, API functions like table.cell(this) don't work properly.
                    setTimeout(dt.ajax.reload, 0);
                    // Prevent rendering of the full data to the DOM
                    return false;
                });
            });
            // Requery the server with the new one-time export settings
            dt.ajax.reload();
        }

        const AjaxHelpers = {
            postRequest: (url, data, processData = true, contentType = false, onSuccess, onError) => {
                $.ajax({
                    url: url,
                    type: 'POST',
                    processData: processData,
                    contentType: contentType,
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: onSuccess,
                    error: onError
                });
            },

            defualtErrorHandler: (jqXHR, textStatus, errorThrown) => {
                let message = 'An error occurred';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    message = jqXHR.responseJSON.message;
                }

                showToast('danger', 'Gagal', 'Terjadi kesalahan dalam memproses permintaan: ' + message, 'ri-alert-fill');
            }
        }

        const EventListeners = {
            setupAllEvents: () => {
                EventListeners.setupProductCreateEvents()
                EventListeners.setupStoreSelectListEvent()
                EventListeners.setupDataTable()
                EventListeners.setupDataTableButtonEvents()
                EventListeners.setupExportEvents()
            },

            setupStoreSelectListEvent: () => {
                $('#store-select-list').on('change', function() {
                    let storeId = $(this).val();
                    if (storeId) {
                        $('#btn_download_product_detail').prop('disabled', false);
                    } else {
                        $('#btn_download_product_detail').prop('disabled', true);
                    }
                });

                var $importCategorySelect = $('#import_category_id');
                var $importCategoryOptions = $importCategorySelect.find('option').clone();

                $('#import_main_category_id').on('change', function() {
                    var selectedMainId = $(this).val();
                    $importCategorySelect.empty();
                    
                    var $filteredOptions = $importCategoryOptions.filter(function() {
                        return $(this).val() === "" || $(this).data('main') == selectedMainId;
                    });
                    
                    $importCategorySelect.append($filteredOptions);
                    $importCategorySelect.val('').trigger('change.select2');
                });

                // Toggle Filter Events
                $('#toggleFilterPrice').on('change', function() {
                    if (state.dataTable) state.dataTable.ajax.reload();
                });
                $('#toggleFilterStock').on('change', function() {
                    if (state.dataTable) state.dataTable.ajax.reload();
                });
                $('#toggleFilterMinus').on('change', function() {
                    if (state.dataTable) state.dataTable.ajax.reload();
                });
                $('#toggleMarketplaceState').on('change', function() {
                    if (state.dataTable) state.dataTable.ajax.reload();
                });
                $('#toggleFilterSku').on('change', function() {
                    if (state.dataTable) state.dataTable.ajax.reload();
                });
                $('#toggleFilterCogm').on('change', function() {
                    if (state.dataTable) state.dataTable.ajax.reload();
                });
                $('#toggleFilterWeight').on('change', function() {
                    if (state.dataTable) state.dataTable.ajax.reload();
                });
            },

            setupExportEvents: () => {
                // Attach product ids when downloading product detail template
                $('#form_download_template_product_detail').on('submit', function(e) {
                    var selected_ids = $("#dt_basic tbody input.checkbox_item:checked").map(function() {
                        return $(this).val();
                    }).get();
                    
                    // remove any existing hidden inputs for product_ids to avoid duplicates
                    $(this).find('input[name="product_ids[]"]').remove();
                    
                    if (selected_ids.length > 0) {
                        var form = this;
                        selected_ids.forEach(function(id) {
                            $(form).append('<input type="hidden" name="product_ids[]" value="' + id + '">');
                        });
                    }
                });

                // Export to Excel
                $(document).on('click', '#btnDownloadTemplate', function() {
                    event.preventDefault();
                    let tabel = $(this).data('tabel');
                    $.ajax({
                        url: "{{ route('get.productsTemplate') }}",
                        type: "GET",
                        data: {
                            _token: '{{ csrf_token() }}',
                            tabel: tabel
                        },
                        cache: false,
                        dataType: 'json',
                        success: function(data) {
                            var url = "{{ route('download.productsTemplate') }}";
                            window.location = url;
                        }
                    });
                });
            },

            setupProductCreateEvents: () => {
                $('#form_create').on('submit', function(e) {
                    e.preventDefault();

                    let form = $(this)[0]; // native DOM element
                    if (!form.checkValidity()) {
                        // Tambahkan class Bootstrap agar muncul pesan error
                        $(form).addClass('was-validated');

                        showToast('warning', 'Peringatan', 'Harap periksa input Anda!', 'ri-error-warning-fill');
                        return; // stop jika form tidak valid
                    }

                    // Jika valid, hapus class error (opsional)
                    $(form).removeClass('was-validated');

                    let url = $(form).attr('action');
                    let formData = new FormData(this);

                    AjaxHelpers.postRequest(url, formData, false, false,
                        function(response) {
                            $('#form_create')[0].reset();
                            $('#modal_main_category_id').val('').trigger('change');
                            state.dataTable.ajax.reload(null, false); // reload tanpa reset pagination
                            showToast('success', 'Sukses', 'Data berhasil disimpan!', 'ri-check-double-fill');
                        },
                        AjaxHelpers.defualtErrorHandler
                    );
                });

                var $modalCategorySelect = $('#modal_category_id');
                var $modalCategoryOptions = $modalCategorySelect.find('option').clone();

                $('#modal_main_category_id').on('change', function() {
                    var selectedMainId = $(this).val();
                    $modalCategorySelect.empty();
                    
                    var $filteredOptions = $modalCategoryOptions.filter(function() {
                        return $(this).val() === "" || $(this).data('main') == selectedMainId;
                    });
                    
                    $modalCategorySelect.append($filteredOptions);
                    $modalCategorySelect.val('').trigger('change.select2');
                });

                // Deleted old filter card click events
            },

            setupDataTable: () => {
                state.dataTable = new DataTable('#dt_basic', {
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
                        [10, "desc"]
                    ],
                    language: {
                        "lengthMenu": "Display _MENU_ "
                    },
                    buttons: [
                        @if (\RuleHelper::UserHasPermission('create', false))
                            {
                                text: '<i class="las la-plus-square text-success"></i> Create',
                                className: 'child-button',
                                action: function() {
                                    $('#form_create')[0].reset();
                                    $('#createModal').modal('show');
                                },
                            },
                        @endif
                        @if (\RuleHelper::UserHasPermission('delete', false))
                            {
                                text: '<i class="las la-trash text-danger"></i> Bulk Delete',
                                className: 'child-button',
                                action: function(e, dt, button, config) {
                                    e.preventDefault();

                                    var id_to_be_deleted = $("#dt_basic tbody input.checkbox_item:checked").map(function() {
                                        return $(this).val();
                                    }).get();

                                    AjaxHelpers.postRequest("{{ route('product.delete') }}", {
                                            id_to_be_deleted: id_to_be_deleted,
                                            delete_type: 'softdelete'
                                        },
                                        true,
                                        'application/x-www-form-urlencoded; charset=UTF-8',
                                        function(data) {
                                            $('#myModal').modal('show');
                                            $('#modal-delete').html(data);
                                        },
                                        AjaxHelpers.defualtErrorHandler
                                    );
                                }
                            },
                        @endif {
                            extend: 'collection',
                            text: '<i class="las la-file-excel"></i> Export',
                            className: 'child-button',
                            buttons: [{
                                extend: 'excel',
                                text: 'excel',
                                action: newExportAction,
                                exportOptions: {
                                    columns: [1, 2, 3]
                                }
                            }]
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
                        {
                            extend: 'collection',
                            text: '<i class="ri-file-excel-2-fill"></i> Import',
                            className: 'child-button',
                            buttons: [{
                                    text: 'Import Produk',
                                    className: 'child-button',
                                    action: function(e, dt, button, config) {
                                        $('body').trigger('click');
                                        $('#import-product-modal').modal('show');
                                    }
                                },
                                {
                                    text: 'Import Detail Produk',
                                    className: 'child-button',
                                    action: function(e, dt, button, config) {
                                        $('body').trigger('click');
                                        $('#import-product-detail-modal').modal('show');
                                    }
                                },
                                {
                                    text: 'Import Gambar Produk',
                                    className: 'child-button',
                                    action: function(e, dt, button, config) {
                                        $('body').trigger('click');
                                        $('#import-image-modal').modal('show');
                                    }
                                }
                            ]
                        },
                    ],
                    ajax: {
                        "url": "{{ route('product.get_datatable') }}",
                        "type": "get",
                        "data": function(data) {
                            data.title = $('#title').val();
                            data.sku = $('#filter_sku').val();
                            data.main_category_id = $('#main_category_id option:selected').val();
                            data.category_id = $('#category_id option:selected').val();
                            data.material_id = $('#filter_material_id option:selected').val();
                            data.top_pick = $('#top_pick option:selected').val();
                            data.filter_incomplete_sku = $('#toggleFilterSku').is(':checked') ? 1 : 0;
                            data.filter_marketplace = $('#toggleMarketplaceState').is(':checked') ? 'sudah' : 'semua';
                            data.filter_incomplete_price = $('#toggleFilterPrice').is(':checked') ? 'complete' : 0;
                            data.filter_stock = $('#toggleFilterStock').is(':checked') ? 'in_stock' : 0;
                            data.filter_minus_stock = $('#toggleFilterMinus').is(':checked') ? 1 : 0;
                            data.filter_incomplete_cogm = $('#toggleFilterCogm').is(':checked') ? 1 : 0;
                            data.filter_incomplete_weight = $('#toggleFilterWeight').is(':checked') ? 1 : 0;

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
                            data: 'title',
                            name: 'products.title'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            width: '100px'
                        },
                        {
                            data: 'top_pick',
                            name: 'products.top_pick'
                        },
                        {
                            data: 'show_in_ecommerce',
                            name: 'products.show_in_ecommerce'
                        },
                        {
                            data: 'status_foto',
                            name: 'status_foto',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'main_category_title',
                            name: 'main_categories.title'
                        },
                        {
                            data: 'category_title',
                            name: 'categories.title'
                        },
                        {
                            data: 'material_title',
                            name: 'material_title',
                            render: function(data, type, row) {
                                return data ? data : '-';
                            }
                        },
                        {
                            data: 'sku_status',
                            name: 'sku_status',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'created_at',
                            name: 'products.created_at'
                        }
                    ],
                    order: [[11, 'desc']],
                });
            },

            setupDataTableButtonEvents: () => {
                // Mencegah form filter refresh page saat ditekan enter, alihkan ke reload datatable
                $('#form_filter').on('submit', function(e) {
                    e.preventDefault();
                    $('#myFilterModal').modal('hide');
                    state.dataTable.ajax.reload();
                });

                // Reset filter dan reload datatable saat tombol reset diklik pada modal filter
                $('#btn-reset').click(function() {
                    state.dataTable.ajax.reload();
                });

                // Event untuk mencentang semua checkbox ketika checkbox utama diklik
                $('body').on('click', '#main_checkbox', function() {
                    if ($(this).is(':checked')) {
                        $('.checkbox_item').prop('checked', true);
                    } else {
                        $('.checkbox_item').prop('checked', false);
                    }
                });

                // Event untuk menghapus produk yang terpilih
                $("#dt_basic tbody").on("click", ".btnDelete", function(e) {
                    e.preventDefault();
                    var id_to_be_deleted = $(this).attr("id");

                    AjaxHelpers.postRequest("{{ route('product.delete') }}", {
                            id_to_be_deleted: [id_to_be_deleted],
                            delete_type: 'softdelete'
                        },
                        true,
                        'application/x-www-form-urlencoded; charset=UTF-8',
                        function(data) {
                            $('#myModal').modal('show');
                            $('#modal-delete').html(data);
                        },
                        AjaxHelpers.defualtErrorHandler
                    );
                });

                // Tambahkan button untuk menuju halaman trash jika user memiliki izin
                @if (\RuleHelper::UserHasPermission('trash', false))
                    var a = '<div class="dt-buttons btn-group btn-recycle"><a class="btn btn-primary" tabindex="0" aria-controls="dt_basic" href="{{ route('product.index') }}"><span><i class="fa fa-archive"></i>  Active</span></a><a href="{{ route('product.trash') }}" class="btn btn-info btn-light" style="margin-right:5px;" tabindex="0" ><span><i class="bx bx-recycle"></i> Recycle Bin ({{ $products_count }}) </span> </a></div>';
                    $(".btn-tbl").prepend(a);
                @endif

                try {
                    state.dataTable.buttons(1, null).container()
                        .addClass('btn-group')
                        .appendTo(state.dataTable.table().container());
                } catch (e) {
                    console.log("DataTables buttons container error:", e);
                }
            }
        }

        $(document).ready(function() {
            $(document).on('shown.bs.modal', '.modal', function() {
                $(this).find('.select2').select2({
                    dropdownParent: $(this)
                });
            });

            EventListeners.setupAllEvents();
        });

        // Fungsi global untuk toggle status produk (top_pick / show_in_ecommerce)
        function toggleProductStatus(el, id, field) {
            var status = el.checked ? 1 : 0;
            $.ajax({
                url: "{{ route('product.toggle_status') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    field: field,
                    status: status
                },
                success: function(response) {
                    showToast('success', 'Sukses', 'Status berhasil diperbarui!', 'ri-check-double-fill');
                },
                error: function(jqXHR) {
                    el.checked = !el.checked;
                    var message = 'Terjadi kesalahan';
                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                        message = jqXHR.responseJSON.message;
                    }
                    showToast('danger', 'Gagal', message, 'ri-alert-fill');
                }
            });
        }
    </script>
    <!-- dashboard init -->
@endsection
