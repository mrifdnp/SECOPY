@extends('layouts.master')
@section('title')
    Transaksi
@endsection
@section('content')
    {{-- Import modal --}}
    <div id="importModal" class="modal fade" style="display: none;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Import Stok Opname</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> </button>
                </div>
                <div class="modal-body">
                    <div class="form-steps">
                        <div class="step-arrow-nav mb-4">
                            <ul class="nav nav-pills custom-nav nav-justified" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="steparrow-gen-info-tab" data-bs-toggle="pill" data-bs-target="#steparrow-gen-info" type="button" role="tab" aria-controls="steparrow-gen-info" aria-selected="true">Template</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="steparrow-description-info-tab" data-bs-toggle="pill" data-bs-target="#steparrow-description-info" type="button" role="tab" aria-controls="steparrow-description-info" aria-selected="false">Peraturan</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-experience-tab" data-bs-toggle="pill" data-bs-target="#pills-experience" type="button" role="tab" aria-controls="pills-experience" aria-selected="false">Upload</button>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="steparrow-gen-info" role="tabpanel" aria-labelledby="steparrow-gen-info-tab">
                                <div class="d-flex flex-column align-items-center justify-content-center text-center" style="min-height: 200px;">
                                    <i class="ri-download-2-fill" style="font-size: 4rem;"></i>
                                    <form action="{{ route('stockopname.importTemplate', ['id_opname' => $stockopname->id])}}" method="POST" class="mt-2" id="form_download_template">
                                        @csrf
                                        <button class="btn btn-primary">Download Template</button>
                                    </form>
                                </div>
                            </div>
                            <!-- end tab pane -->

                            <div class="tab-pane fade" id="steparrow-description-info" role="tabpanel" aria-labelledby="steparrow-description-info-tab">
                                    <p>Selain kolom qty_sesudah_opname dan tanggal_opname tidak boleh dirubah. Ikuti aturan untuk setiap kolom untuk memastikan data bisa terimport.</p>
                                    <table class="table table-bordered">
                                            <tr>
                                                <th>qty_sesudah_opname</th>
                                                <td>di isi dengan angka</td>
                                            </tr>
                                            <tr>
                                                <th>tanggal_opname</th>
                                                <td>wajib di isi dengan tanggal dan jam stok selesai dihitung</td>
                                            </tr>
                                    </table>
                            </div>
                            <!-- end tab pane -->

                            <div class="tab-pane fade" id="pills-experience" role="tabpanel">
                                <div class="d-flex flex-column align-items-center justify-content-center text-center" style="min-height: 200px; width:100%;">
                                    <i class="ri-upload-2-fill" style="font-size: 4rem;"></i>
                                    <form action="{{ route('import.opnameproduct') }}" method="POST" class="mt-2 w-100" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="stockOpname" value="{{ $stockopname->id }}">
                                        <input type="hidden" name="store_id" value="{{ session('store.id') }}">
                                        <input type="file" name="file" id="file" class="form-control mb-2 w-100" accept=".csv">
                                        <button class="btn btn-primary w-100">Upload</button>
                                    </form>
                                </div>
                            </div>
                            <!-- end tab pane -->
                        </div>
                        <!-- end tab content -->
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
    {{-- End import modal --}}

    <!-- start plan -->
    @include('backend.includes.flash')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <section>
        <div class="row">
            <div class="col-xl-6">
                
                <div class="card">
                    <div class="row my-0 mt-3 mx-2">
                        <div class="col-6">
                            <h4 class="fs-14 font-weight-normal ff-secondary mb-2">
                                {{ number_format($count_product, 0, ',', '.') }} Barang belum di Opname
                            </h4>
                            <span class="text-muted" style="font-size: 12px;"></span>
                        </div>
                        <div class="col-6 text-end">
                            <h4 class="fs-14 fw-normal ff-secondary mb-2">
                                {{ \Carbon\Carbon::parse($stockopname->date_start)->format('d M Y') }} -
                                {{ \Carbon\Carbon::parse($stockopname->date_end)->format('d M Y') }}
                            </h4>
                        </div>
                    </div>
                    <div class="card-header border-0">
                        <div class="row g-4">
                            <div class="col-sm">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="bg-light rounded fs-3 px-2">
                                            <i class="mdi mdi-barcode-scan" id="toggleQRCode" style="color: red;"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <div class="search-box ms-2">
                                            <input type="text" class="form-control" id="searchProductList"
                                                placeholder="Search Products...">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                        <div class="search-box ms-2 selectProduct">
                                            <select class="form-control selectCategory"
                                                data-placeholder="Filter by kategori" id="category_id" data-choices
                                                data-choices-removeItem name="category_id">
                                                <option value="">Pilih Category</option>
                                                @foreach ($categories as $item)
                                                    <option value="{{ $item['id'] }}">{{ $item['title'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-card">
                            <table class="table table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 5%;">#</th>
                                        <th scope="col" style="width: 40%;">Nama</th>
                                        <th scope="col" class="text-center" style="width: 15%;">Qty</th>
                                        <th scope="col" style="width: 10%;">Update</th>
                                        <th scope="col" class="text-center" style="width: 15%;">Add to Cart</th>
                                    </tr>
                                </thead>
                                <tbody id="contentProduct">
                                    @foreach ($product_list as $key => $product)
                                        <tr class="text-center align-middle">
                                            <td><a href="#" class="fw-semibold">{{ (int) $key + 1 }}.</a></td>
                                            <td data-column-id="product" class="text-start">
                                                <span>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-3">
                                                            <div class="avatar-sm bg-light rounded p-1">
                                                                @php
                                                                    $picture = json_decode($product->content_paths);
                                                                    $img = '';
                                                                    if(!empty($picture[0]->path)){
                                                                        $img = $picture[0]->path;
                                                                    }
                                                                @endphp 
                                                                @if ($img != '')
                                                                    <img src="{{ env('APP_URL') . '/storage/' . $img}}"
                                                                        alt="" class="img-fluid d-block">
                                                                @else
                                                                    <img src="{{ asset('img/noimage.png') }}" alt=""
                                                                        class="img-fluid d-block">
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h5 class="fs-14 mb-1">
                                                                <a href="#" class="text-dark"
                                                                    style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                                    {{ $product->product_title }}
                                                                </a>
                                                            </h5>
                                                            <p class="text-muted mb-0"
                                                                style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                                <span
                                                                    class="list-group-item-success px-2"> {{ $product->store_name }}</span>

                                                                <span
                                                                    class="list-group-item-secondary px-2"> {{ $product->category_title }}</span></br>

                                                                <span
                                                                    class="list-group-item-primary px-2"> #{{ $product->sku }}</span>

                                                                <span
                                                                    class="list-group-item-primary px-2">{{ $product->variant_title }}</span>
                                                                
                                                                <span
                                                                    class="list-group-item-primary px-2">{{ $product->color_title }}</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </span>
                                            </td>
                                            <td>{{ number_format($product->qty, 0, ',', '.') }}</td>
                                            <td>
                                                <input type="text" class="form-control qty-input" name="Qty"
                                                    style="width: 100px; text-align: center;">
                                            </td>
                                            <td class="text-center">
                                                <form id="form_create" action="{{ route('stockopname.updateQty') }}"
                                                    method="post">
                                                    @csrf
                                                    <input type="text" name="id_opname" value="{{ $stockopname->id }}"
                                                        hidden>
                                                    <input type="hidden" name="Qty">
                                                    <input type="text" name="id_detail_product" value="{{ $product->id }}"
                                                        hidden>
                                                    <button type="submit"
                                                        class="btnAddChart btn rounded-pill btn-sm btn-soft-info">
                                                        <i class="ri ri-arrow-right-s-line"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- end card -->
            </div>
            <!-- end col -->
            <div class="col-xl-6">
                @if (session('error_message_opname'))
                    <div class="alert alert-danger">
                        {{ session('error_message_opname') }}
                    </div>
                @endif
                @if (session('success_message_opname'))
                    <div class="alert alert-success">
                        {{ session('success_message_opname') }}
                    </div>
                @endif
                <div class="card">
                    <div class="row my-0 mt-3 mx-2">
                        <div class="col-6">
                            <h4 class="fs-14 font-weight-normal ff-secondary mb-2">
                                {{ number_format($count_opname, 0, ',', '.') }} Barang sudah di Opname
                            </h4>
                            <span class="text-muted" style="font-size: 12px;"></span>
                        </div>
                        <div class="col-6 text-end">
                            <h4 class="fs-14 fw-normal ff-secondary mb-2">
                                {{ \Carbon\Carbon::parse($stockopname->date_start)->format('d M Y') }} -
                                {{ \Carbon\Carbon::parse($stockopname->date_end)->format('d M Y') }}
                            </h4>
                        </div>
                    </div>
                    <div class="card-header border-0">
                        <div class="row g-4">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex gap-2">
                                    <div class="bg-light rounded fs-3 px-2">
                                        <i class="mdi mdi-barcode-scan" id="toggleQRCode_opname" style="color: red;"></i>
                                    </div>
                                    <div class="bg-light rounded fs-3 px-2" data-bs-toggle="modal" data-bs-target="#importModal">
                                        <i class="mdi mdi-file-excel text-success"></i> 
                                    </div>
                                </div>
                                
                                <div class="d-flex">
                                    <div class="search-box ms-2">
                                        <input type="text" class="form-control" id="searchProductList_opname"
                                            placeholder="Search Products...">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                    <div class="search-box ms-2 selectProductOpname">
                                        <select class="form-control selectCategory" data-placeholder="Filter by kategori"
                                            id="category_id_opname" data-choices data-choices-removeItem
                                            name="category_id_opname">
                                            <option value="">Pilih Category</option>
                                             @foreach ($categories as $item)
                                                <option value="{{ $item['id'] }}">{{ $item['title'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-card">
                            <table class="table table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 5%;">#</th>
                                        <th scope="col" style="width: 40%;">Nama</th>
                                        <th scope="col" style="width: 20%;">Selisih</th>
                                        <th scope="col" style="width: 20%;">Qty </th>
                                        <th scope="col" class="text-center" style="width: 15%;">Delete</th>
                                    </tr>
                                </thead>
                                <tbody id="contentProduct_opname">
                                    @foreach ($product_list_opname as $key => $product)
                                        <tr class="align-middle">
                                            <td><a href="#" class="fw-semibold">{{ $key + 1 }}.</a></td>
                                            <td data-column-id="product" class="">
                                                <span>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-3">
                                                            <div class="avatar-sm bg-light rounded p-1">
                                                                @php
                                                                    $picture = json_decode($product->content_paths);
                                                                    $img = '';
                                                                    if(!empty($picture[0]->path)){
                                                                        $img = $picture[0]->path;
                                                                    }
                                                                @endphp 
                                                                @if ($img != '')
                                                                    <img src="{{ env('APP_URL') . '/storage/' . $img}}"
                                                                        alt="" class="img-fluid d-block">
                                                                @else
                                                                    <img src="{{ asset('img/noimage.png') }}" alt=""
                                                                        class="img-fluid d-block">
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h5 class="fs-14 mb-1">
                                                                <a href="#" class="text-dark"
                                                                    style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                                    {{ $product->product_title }}
                                                                </a>
                                                            </h5>
                                                            <p class="text-muted mb-0"
                                                                style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                                <span
                                                                    class="list-group-item-success px-2"> {{ $product->store_name }}</span>
                                                                <span
                                                                    class="list-group-item-secondary px-2"> {{ $product->category_title }}</span></br>
                                                                <span
                                                                    class="list-group-item-primary px-2"> #{{ $product->sku }}</span>

                                                                <span
                                                                    class="list-group-item-primary px-2">{{ $product->variant_title }}</span>
                                                                
                                                                <span
                                                                    class="list-group-item-primary px-2">{{ $product->color_title }}</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </span>
                                            </td>
                                            <td>
                                                @if($product->opname_qty>0)
                                                    +{{ number_format($product->opname_qty, 0, ',', '.') }}
                                                @else
                                                    {{ number_format($product->opname_qty, 0, ',', '.') }}
                                                @endif
                                            </td>
                                            <td>{{ number_format($product->qty, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                <div>
                                                    <form action="{{ route('stockopname.deleteItem') }}" method="POST"
                                                        class="d-inline-block formDelete">
                                                        @csrf
                                                        <input type="hidden" name="stockopname_id"
                                                            value="{{ $product->stockopname_id }}" hidden>
                                                        <input type="text" name="id_detail_product"
                                                            value="{{ $product->id }}" hidden>
                                                        <button type="submit"
                                                            class="text-danger border-0 bg-transparent p-0 remove-item-btn btnDelete"
                                                            data-id="{{ $product->stockopname_id }}" data-bs-toggle="tooltip"
                                                            data-bs-trigger="hover" data-bs-placement="top"
                                                            aria-label="Remove" data-bs-original-title="Remove">
                                                            <i class="ri-delete-bin-5-fill fs-16"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- end card -->
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->
    </section>
    <!-- end plan -->
    <div id="myAlertUpdate" class="modal fade modal-primary">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="modal-filter">
                <div class="modal-header p-3 bg-primary bg-gradient">
                    <h5 class="text-white">Peringatan</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="box-body indoit-form form-horizontal">
                        <div class="alert alert-danger alert-borderless mb-xl-0" role="alert">
                            <strong>Masukan nominal Update terlebih dahulu</strong>
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
    <div class="toast-container position-fixed bottom-0 start-50 translate-middle-x p-3 mb-5">
        <div class="toast" id="liveToast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success bg-gradient text-white">
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
            <div class="toast-body" id="scanStatusMessage" style="min-height: 50px;">
                <strong>Scanner diaktifkan</strong>
            </div>
        </div>
    </div>

@endsection


@section('css')
    <style>
        .error {
            border: 1px solid #f06548 !important;
        }
    </style>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css"
        integrity="sha512-kq3FES+RuuGoBW3a9R2ELYKRywUEQv0wvPTItv3DSGqjpbNtGWVdvT8qwdKkqvPzT93jp8tSF4+oN4IeTEIlQA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/form-input-spin.init.js') }}"></script>
    <script src="https://unpkg.com/gridjs/plugins/selection/dist/selection.umd.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ URL::asset('build/js/pages/select2.init.js') }}"></script>
    <script>
        let idProductAdded = null;
        let categoryId = null;
        let id_product_opname = null;
        let searchProductList = null;
        let searchProductList_opname = null;

        $(document).ready(function() {
            let isScannerActive = localStorage.getItem('isScannerActive') === 'true';
            let isScannerActiveOpname = localStorage.getItem('isScannerActiveOpname') === 'true';
            let scannedCode = "";
            let scanTimeout;

            updateScannerVisuals();

            document.getElementById('toggleQRCode').addEventListener('click', function() {
                toggleScanner('searchProduct', 'toggleQRCode',
                    'Scanner diaktifkan untuk Produk',
                    'Scanner dinonaktifkan untuk Produk');
            });

            document.getElementById('toggleQRCode_opname').addEventListener('click', function() {
                toggleScanner('searchOpname', 'toggleQRCode_opname',
                    'Scanner diaktifkan untuk Opname',
                    'Scanner dinonaktifkan untuk Opname');
            });

            function toggleScanner(type, iconId, activatedMessage, deactivatedMessage) {
                if (type === 'searchProduct') {
                    isScannerActive = !isScannerActive;
                    localStorage.setItem('isScannerActive', isScannerActive);
                    showToast(isScannerActive ? activatedMessage : deactivatedMessage, isScannerActive ?
                        "bg-sukses" : "bg-danger");

                    if (isScannerActive) {
                        if (isScannerActiveOpname) {
                            isScannerActiveOpname = false;
                            localStorage.setItem('isScannerActiveOpname', isScannerActiveOpname);
                            showToast('Scanner untuk Opname dinonaktifkan', 'bg-danger');
                            document.getElementById('toggleQRCode_opname').style.color = "red";
                            performAjaxSearch("", 'searchOpname');
                        }
                    } else {
                        performAjaxSearch("", 'searchProduct');
                    }
                } else if (type === 'searchOpname') {
                    isScannerActiveOpname = !isScannerActiveOpname;
                    localStorage.setItem('isScannerActiveOpname', isScannerActiveOpname);
                    showToast(isScannerActiveOpname ? activatedMessage : deactivatedMessage, isScannerActiveOpname ?
                        "bg-sukses" : "bg-danger");

                    if (isScannerActiveOpname) {
                        if (isScannerActive) {
                            isScannerActive = false;
                            localStorage.setItem('isScannerActive', isScannerActive);
                            showToast('Scanner untuk Produk dinonaktifkan', 'bg-danger');
                            document.getElementById('toggleQRCode').style.color = "red";
                            performAjaxSearch("", 'searchProduct');
                        }
                    } else {
                        performAjaxSearch("", 'searchOpname');
                    }
                }

                updateScannerVisuals();
            }

            function updateScannerVisuals() {
                const productIcon = document.getElementById('toggleQRCode');
                const opnameIcon = document.getElementById('toggleQRCode_opname');

                if (isScannerActive) {
                    productIcon.style.color = "green"; 
                    productIcon.parentElement.style.backgroundColor =
                        "lightgreen"; 
                } else {
                    productIcon.style.color = "red";
                    productIcon.parentElement.style.backgroundColor = "lightcoral";
                }

                if (isScannerActiveOpname) {
                    opnameIcon.style.color = "green"; 
                    opnameIcon.parentElement.style.backgroundColor =
                        "lightgreen"; 
                } else {
                    opnameIcon.style.color = "red";
                    opnameIcon.parentElement.style.backgroundColor = "lightcoral";
                }
            }

            $("#searchProductList").on('focus', function() {
                if (isScannerActive) {
                    isScannerActive = false;
                    localStorage.setItem('isScannerActive', isScannerActive);
                    showToast('Scanner untuk Produk dinonaktifkan', 'bg-danger');
                    document.getElementById('toggleQRCode').style.color = "red";
                    updateScannerVisuals();
                }
            });


            $(".selectProduct").on('click', function() {
                if (isScannerActive) {
                    isScannerActive = false;
                    localStorage.setItem('isScannerActive', isScannerActive);
                    showToast('Scanner untuk Produk dinonaktifkan', 'bg-danger');
                    document.getElementById('toggleQRCode').style.color = "red";
                    updateScannerVisuals();
                }
            });

            $("#searchProductList_opname").on('focus', function() {
                if (isScannerActiveOpname) {
                    isScannerActiveOpname = false;
                    localStorage.setItem('isScannerActiveOpname', isScannerActiveOpname);
                    showToast('Scanner untuk Opname dinonaktifkan', 'bg-danger');
                    document.getElementById('toggleQRCode_opname').style.color = "red";
                    updateScannerVisuals();
                }
            });

            $(".selectProductOpname").on('click', function() {
                if (isScannerActiveOpname) {
                    isScannerActiveOpname = false;
                    localStorage.setItem('isScannerActiveOpname', isScannerActiveOpname);
                    showToast('Scanner untuk Opname dinonaktifkan', 'bg-danger');
                    document.getElementById('toggleQRCode_opname').style.color = "red";
                    updateScannerVisuals();
                }
            });

            document.addEventListener('keydown', function(event) {
                if ((isScannerActive || isScannerActiveOpname) && event.key.length === 1) {
                    const activeElement = document.activeElement;
                    if (activeElement.classList.contains('qty-input')) {
                        return;
                    }

                    scannedCode += event.key;
                    if (scanTimeout) {
                        clearTimeout(scanTimeout);
                    }
                    scanTimeout = setTimeout(function() {
                        const type = isScannerActive ? 'searchProduct' : 'searchOpname';
                        processScannedCode(scannedCode, type);
                        scannedCode = "";
                    }, 300);
                }
            });

            function processScannedCode(qrCode, type) {
                console.log("QR Code scanned: ", qrCode);
                performAjaxSearch(qrCode, type);
            }

            function performAjaxSearch(qrCode, type) {
                if (type === 'searchProduct') {
                    $("#searchProductList").val(qrCode).trigger('input');
                } else {
                    $("#searchProductList_opname").val(qrCode).trigger('input');
                }
            }

            function showToast(message, headerClass) {
                const toastLiveExample = document.getElementById("liveToast");
                const toastBody = document.getElementById("scanStatusMessage");
                const toastHeader = document.querySelector('.toast-header');

                toastBody.innerHTML = `<strong>${message}</strong>`; 

                toastHeader.classList.remove("bg-sukses", "bg-danger");
                toastHeader.classList.add(headerClass);

                const toast = new bootstrap.Toast(toastLiveExample);
                toast.show();

                setTimeout(() => {
                    toast.hide();
                }, 5000);
            }
        });




        $(document).ready(function() {
            $('.btnAddChart').on('click', function() {
                var productId = $(this).data('product-id');
                var form = $('#form_create_' + productId);
                var formData = form.serialize();

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                });
            });
        });

        $(document).ready(function() {
            $('.btnDelete').on('click', function() {
                var movementId = $(this).data('id');
                var form = $(this).closest('form');
                var formData = form.serialize();

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    success: function(response) {},
                });
            });
        });



        $(document).ready(function() {
            $(document).on('keyup', '.qty-input', function() {
                let qty = $(this).val();
                $(this).closest('tr').find('input[name="Qty"]').val(qty);
            });

            $(document).on('submit', '#form_create', function(e) {
                e.preventDefault();

                var qtyUpdate = $(this).find('input[name="Qty"]').val();
                if (!qtyUpdate || isNaN(qtyUpdate) || qtyUpdate.trim() === "") {
                    $('#myAlertUpdate').modal('show');
                    return;
                }
                this.submit();
            });
        });

        $(document).on('change', '#category_id', function(event) {
            event.preventDefault();
            categoryId = $('#category_id option:selected').val();
            searchProductList = $('#searchProductList').val();

            var id_opname = '{{ $stockopname->id }}';

            if (searchProductList === '') {
                searchProductList = null;
            }
            $.ajax({
                url: "{{ route('stockopname.filter') }}",
                type: "GET",
                data: {
                    _token: '{{ csrf_token() }}',
                    searchText: searchProductList,
                    categoryId: categoryId,
                    id_opname: id_opname
                },
                cache: false,
                dataType: 'json',
                success: function(dataResult) {
                    $("#contentProduct").empty();
                    if (dataResult.product_list && dataResult.product_list.length > 0) {
                            $.each(dataResult.product_list, function(index, product) {
                                let pictures = [];
                                let img = "";

                                try {
                                    pictures = JSON.parse(product.content_paths);
                                    if (pictures.length > 0 && pictures[0].path) {
                                        img = pictures[0].path;
                                    }
                                } catch (e) {
                                    console.error("Invalid JSON for content_paths", e);
                                }

                                let imgTag = img
                                    ? `<img src="{{ env('APP_URL') }}/storage/${img}" class="img-fluid d-block">`
                                    : ``;

                                let row = `
                                    <tr class="text-center align-middle">
                                        <td><a href="#" class="fw-semibold">${index + 1}.</a></td>
                                        <td data-column-id="product" class="text-start">
                                            <span>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar-sm bg-light rounded p-1">
                                                            ${imgTag}
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h5 class="fs-14 mb-1">
                                                            <a href="#" class="text-dark"
                                                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                            ${product.product_title ?? ""}
                                                            </a>
                                                        </h5>
                                                        <p class="text-muted mb-0"
                                                        style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                            <span class="list-group-item-success px-2">${product.store_name ?? ""}</span>
                                                            <span class="list-group-item-secondary px-2">${product.category_title ?? ""}</span></br>
                                                            <span class="list-group-item-primary px-2">#${product.sku ?? ""}</span>
                                                            <span class="list-group-item-primary px-2">${product.variant_title ?? ""}</span>
                                                            <span class="list-group-item-primary px-2">${product.color_title ?? ""}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </span>
                                        </td>
                                        <td>${Number(product.qty).toLocaleString("id-ID")}</td>
                                        <td>
                                            <input type="text" class="form-control qty-input" name="Qty"
                                                style="width: 100px; text-align: center;">
                                        </td>
                                        <td class="text-center">
                                            <form id="form_create" action="{{ route('stockopname.updateQty') }}" method="post">
                                                @csrf
                                                <input type="text" name="id_opname" value="{{ $stockopname->id }}" hidden>
                                                <input type="hidden" name="Qty">
                                                <input type="text" name="id_detail_product" value="${product.id}" hidden>
                                                <button type="submit" class="btnAddChart btn rounded-pill btn-sm btn-soft-info">
                                                    <i class="ri ri-arrow-right-s-line"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                `;

                                $("#contentProduct").append(row);
                            });
                        } else {
                            $("#contentProduct").append(`
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada produk ditemukan</td>
                                </tr>
                            `);
                        }
                }
            });
        });

        $(document).ready(function() {
            $("#searchProductList").on("input", function(event) {
                event.preventDefault();
                categoryId = $('#category_id option:selected').val();
                searchProductList = $('#searchProductList').val();
                var id_opname = '{{ $stockopname->id }}';

                if (searchProductList === '') {
                    searchProductList = null;
                }
                $.ajax({
                    url: "{{ route('stockopname.filter') }}",
                    type: "GET",
                    data: {
                        _token: '{{ csrf_token() }}',
                        searchText: searchProductList,
                        categoryId: categoryId,
                        id_opname: id_opname
                    },
                    cache: false,
                    dataType: 'json',
                    success: function(dataResult) {
                        $("#contentProduct").empty();
                        
                        if (dataResult.product_list && dataResult.product_list.length > 0) {
                            $.each(dataResult.product_list, function(index, product) {
                                let pictures = [];
                                let img = "";

                                try {
                                    pictures = JSON.parse(product.content_paths);
                                    if (pictures.length > 0 && pictures[0].path) {
                                        img = pictures[0].path;
                                    }
                                } catch (e) {
                                    console.error("Invalid JSON for content_paths", e);
                                }

                                let imgTag = img
                                    ? `<img src="{{ env('APP_URL') }}/storage/${img}" class="img-fluid d-block">`
                                    : ``;

                                let row = `
                                    <tr class="text-center align-middle">
                                        <td><a href="#" class="fw-semibold">${index + 1}.</a></td>
                                        <td data-column-id="product" class="text-start">
                                            <span>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar-sm bg-light rounded p-1">
                                                            ${imgTag}
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h5 class="fs-14 mb-1">
                                                            <a href="#" class="text-dark"
                                                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                            ${product.product_title ?? ""}
                                                            </a>
                                                        </h5>
                                                        <p class="text-muted mb-0"
                                                        style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                            <span class="list-group-item-success px-2">${product.store_name ?? ""}</span>
                                                            <span class="list-group-item-secondary px-2">${product.category_title ?? ""}</span></br>
                                                            <span class="list-group-item-primary px-2">#${product.sku ?? ""}</span>
                                                            <span class="list-group-item-primary px-2">${product.variant_title ?? ""}</span>
                                                            <span class="list-group-item-primary px-2">${product.color_title ?? ""}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </span>
                                        </td>
                                        <td>${Number(product.qty).toLocaleString("id-ID")}</td>
                                        <td>
                                            <input type="text" class="form-control qty-input" name="Qty"
                                                style="width: 100px; text-align: center;">
                                        </td>
                                        <td class="text-center">
                                            <form id="form_create" action="{{ route('stockopname.updateQty') }}" method="post">
                                                @csrf
                                                <input type="text" name="id_opname" value="{{ $stockopname->id }}" hidden>
                                                <input type="hidden" name="Qty">
                                                <input type="text" name="id_detail_product" value="${product.id}" hidden>
                                                <button type="submit" class="btnAddChart btn rounded-pill btn-sm btn-soft-info">
                                                    <i class="ri ri-arrow-right-s-line"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                `;

                                $("#contentProduct").append(row);
                            });
                        } else {
                            $("#contentProduct").append(`
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada produk ditemukan</td>
                                </tr>
                            `);
                        }
                    }
                });
            });
        });

        $(document).on('change', '#category_id_opname', function(event) {
            event.preventDefault();
            categoryId = $('#category_id_opname option:selected').val();
            searchProductList = $('#searchProductList_opname').val();
            var id_opname = '{{ $stockopname->id }}';

            if (searchProductList === '') {
                searchProductList = null;
            }
            $.ajax({
                url: "{{ route('stockopname.filterOpname') }}",
                type: "GET",
                data: {
                    _token: '{{ csrf_token() }}',
                    searchText: searchProductList,
                    categoryId: categoryId,
                    id_opname: id_opname
                },
                cache: false,
                dataType: 'json',
                success: function(dataResult) {
                    $("#contentProduct_opname").empty();
                        if (dataResult.product_list_opname && dataResult.product_list_opname.length > 0) {
                            $.each(dataResult.product_list_opname, function(index, product) {
                                let pictures = [];
                                let img = "";

                                try {
                                    pictures = JSON.parse(product.content_paths);
                                    if (pictures.length > 0 && pictures[0].path) {
                                        img = pictures[0].path;
                                    }
                                } catch (e) {
                                    console.error("Invalid JSON for content_paths", e);
                                }

                                let imgTag = img
                                    ? `<img src="{{ env('APP_URL') }}/storage/${img}" class="img-fluid d-block">`
                                    : ``;

                                // format opname_qty
                                let opnameQty = Number(product.opname_qty);
                                let opnameQtyFormatted = opnameQty > 0 
                                    ? `+${opnameQty.toLocaleString("id-ID")}` 
                                    : opnameQty.toLocaleString("id-ID");

                                let qtyFormatted = Number(product.qty).toLocaleString("id-ID");

                                let row = `
                                    <tr class="align-middle">
                                        <td><a href="#" class="fw-semibold">${index + 1}.</a></td>
                                        <td data-column-id="product">
                                            <span>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar-sm bg-light rounded p-1">
                                                            ${imgTag}
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h5 class="fs-14 mb-1">
                                                            <a href="#" class="text-dark"
                                                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                            ${product.product_title ?? ""}
                                                            </a>
                                                        </h5>
                                                        <p class="text-muted mb-0"
                                                        style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                            <span class="list-group-item-success px-2">${product.store_name ?? ""}</span>
                                                            <span class="list-group-item-secondary px-2">${product.category_title ?? ""}</span></br>
                                                            <span class="list-group-item-primary px-2">#${product.sku ?? ""}</span>
                                                            <span class="list-group-item-primary px-2">${product.variant_title ?? ""}</span>
                                                            <span class="list-group-item-primary px-2">${product.color_title ?? ""}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </span>
                                        </td>
                                        <td>${opnameQtyFormatted}</td>
                                        <td>${qtyFormatted}</td>
                                        <td class="text-center">
                                            <div>
                                                <form action="{{ route('stockopname.deleteItem') }}" method="POST"
                                                    class="d-inline-block formDelete">
                                                    @csrf
                                                    <input type="hidden" name="stockopname_id" value="${product.stockopname_id}">
                                                    <input type="hidden" name="id_detail_product" value="${product.id}">
                                                    <button type="submit"
                                                            class="text-danger border-0 bg-transparent p-0 remove-item-btn btnDelete"
                                                            data-id="${product.stockopname_id}" data-bs-toggle="tooltip"
                                                            data-bs-trigger="hover" data-bs-placement="top"
                                                            aria-label="Remove" data-bs-original-title="Remove">
                                                        <i class="ri-delete-bin-5-fill fs-16"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                `;

                                $("#contentProduct_opname").append(row);
                            });
                        } else {
                            $("#contentProduct_opname").append(`
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada produk opname ditemukan</td>
                                </tr>
                            `);
                        }
                }
            });
        });

        $(document).ready(function() {
            $("#searchProductList_opname").on("input", function(event) {
                event.preventDefault();
                categoryId = $('#category_id_opname option:selected').val();
                searchProductList = $('#searchProductList_opname').val();
                var id_opname = '{{ $stockopname->id }}';

                if (searchProductList === '') {
                    searchProductList = null;
                }
                $.ajax({
                    url: "{{ route('stockopname.filterOpname') }}",
                    type: "GET",
                    data: {
                        _token: '{{ csrf_token() }}',
                        searchText: searchProductList,
                        categoryId: categoryId,
                        id_opname: id_opname
                    },
                    cache: false,
                    dataType: 'json',
                    success: function(dataResult) {
                        $("#contentProduct_opname").empty();
                        if (dataResult.product_list_opname && dataResult.product_list_opname.length > 0) {
                            $.each(dataResult.product_list_opname, function(index, product) {
                                let pictures = [];
                                let img = "";

                                try {
                                    pictures = JSON.parse(product.content_paths);
                                    if (pictures.length > 0 && pictures[0].path) {
                                        img = pictures[0].path;
                                    }
                                } catch (e) {
                                    console.error("Invalid JSON for content_paths", e);
                                }

                                let imgTag = img
                                    ? `<img src="{{ env('APP_URL') }}/storage/${img}" class="img-fluid d-block">`
                                    : ``;

                                // format opname_qty
                                let opnameQty = Number(product.opname_qty);
                                let opnameQtyFormatted = opnameQty > 0 
                                    ? `+${opnameQty.toLocaleString("id-ID")}` 
                                    : opnameQty.toLocaleString("id-ID");

                                let qtyFormatted = Number(product.qty).toLocaleString("id-ID");

                                let row = `
                                    <tr class="align-middle">
                                        <td><a href="#" class="fw-semibold">${index + 1}.</a></td>
                                        <td data-column-id="product">
                                            <span>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar-sm bg-light rounded p-1">
                                                            ${imgTag}
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h5 class="fs-14 mb-1">
                                                            <a href="#" class="text-dark"
                                                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                            ${product.product_title ?? ""}
                                                            </a>
                                                        </h5>
                                                        <p class="text-muted mb-0"
                                                        style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                            <span class="list-group-item-success px-2">${product.store_name ?? ""}</span>
                                                            <span class="list-group-item-secondary px-2">${product.category_title ?? ""}</span></br>
                                                            <span class="list-group-item-primary px-2">#${product.sku ?? ""}</span>
                                                            <span class="list-group-item-primary px-2">${product.variant_title ?? ""}</span>
                                                            <span class="list-group-item-primary px-2">${product.color_title ?? ""}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </span>
                                        </td>
                                        <td>${opnameQtyFormatted}</td>
                                        <td>${qtyFormatted}</td>
                                        <td class="text-center">
                                            <div>
                                                <form action="{{ route('stockopname.deleteItem') }}" method="POST"
                                                    class="d-inline-block formDelete">
                                                    @csrf
                                                    <input type="hidden" name="stockopname_id" value="${product.stockopname_id}">
                                                    <input type="hidden" name="id_detail_product" value="${product.id}">
                                                    <button type="submit"
                                                            class="text-danger border-0 bg-transparent p-0 remove-item-btn btnDelete"
                                                            data-id="${product.stockopname_id}" data-bs-toggle="tooltip"
                                                            data-bs-trigger="hover" data-bs-placement="top"
                                                            aria-label="Remove" data-bs-original-title="Remove">
                                                        <i class="ri-delete-bin-5-fill fs-16"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                `;

                                $("#contentProduct_opname").append(row);
                            });
                        } else {
                            $("#contentProduct_opname").append(`
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada produk opname ditemukan</td>
                                </tr>
                            `);
                        }
                    }
                });
            });
        });

        $(document).ready(function() {
            function addQtyInputHandlers() {
                $(document).on('input', '.qty-input', function() {
                    let value = $(this).val();
                    value = value.replace(/[^0-9]/g, '');
                    if (value) {
                        value = Number(value).toLocaleString('id-ID');
                    }
                    $(this).val(value);
                });
                $(document).on('keypress', '.qty-input', function(e) {
                    let charCode = (e.which) ? e.which : e.keyCode;
                    if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
                        return false;
                    }
                });
            }
            addQtyInputHandlers();
        });
    </script>

   @if(session('success_message'))
    <button type="button"
        data-toast
        data-toast-text="{{ session('success_message') }}"
        data-toast-gravity="top"
        data-toast-position="right"
        data-toast-duration="3000"
        data-toast-close="close"
        id="autoToastBtn"
        style="display:none;">
    </button>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("autoToastBtn").click();
        });
    </script>
    @endif
@endsection
