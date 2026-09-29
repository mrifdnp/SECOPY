@extends('layouts.master')

@section('title')
    Edit {{ $titleHeader->name }}
@endsection

@section('cssAdded')
    <style>
        .avatar {
            width: 58px;
            height: 58px;
            object-fit: cover;
            border-radius: 0.375rem;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Systems
        @endslot
        @slot('title')
            Edit {{ $titleHeader->name }}
        @endslot
    @endcomponent

    {{-- Discount information section --}}
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="card-title mb-0">{{ $discount->name }}</h6>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="discount-name" class="form-label">Nama Diskon*</label>
                <input type="text" class="form-control" id="discount-name" value="{{ $discount->name }}">
                <div class="invalid-feedback">
                    Nama diskon wajib diisi.
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-lg-6">
                    <label for="start-date" class="form-label">Tanggal Mulai*</label>
                    <input type="date" class="form-control" id="start-date" value="{{ $discount->start_date }}">
                    <div class="invalid-feedback">
                        Tanggal mulai wajib diisi.
                    </div>
                </div>
                <div class="col-lg-6">
                    <label for="end-date" class="form-label">Tanggal Berakhir*</label>
                    <input type="date" class="form-control" id="end-date" value="{{ $discount->end_date }}">
                    <div class="invalid-feedback">
                        Tanggal berakhir wajib diisi dan tidak boleh kurang dari tanggal mulai.
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="discount-value" class="form-label">Nilai Diskon*</label>
                <input type="text" class="form-control" id="discount-value" value="{{ $discount->value }}">
                <div class="invalid-feedback">
                    Nilai diskon wajib diisi dan harus berupa angka positif, jika tipe diskon adalah persentase, nilai harus antara 1-100.
                </div>
            </div>
            <div>
                <label class="form-label">Jenis Diskon*</label>
                <div class="d-flex gap-3" style="width: 50%;">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="discount-type" id="percentage-type" {{ $discount->type == 'percent' ? 'checked' : '' }}>
                        <label class="form-check-label" for="percentage-type">
                            Diskon Persentase
                        </label>
                        <p class="text-muted">Potongan harga dihitung dari persentase yang kamu tentukan.</p>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="discount-type" id="nominal-type" {{ $discount->type == 'nominal' ? 'checked' : '' }}>
                        <label class="form-check-label" for="nominal-type">
                            Diskon Nominal
                        </label>
                        <p class="text-muted">Harga akhir produk setelah diskon ditetapkan secara langsung.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Product discount section --}}
    <div class="card mb-3">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">Produk</h6>
                <button class="btn btn-primary" id="add-product">Tambah Produk</button>
            </div>
        </div>
        <div class="card-body">
            <table id="product-discount-table" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 30px;">
                            <input id="product-datatable-main-checkbox" type="checkbox">
                        </th>
                        <th style="width: 30px;">No</th>
                        <th>Nama</th>
                        <th>Harga Awal</th>
                        <th>Harga Setelah Diskon</th>
                        <th>Stok</th>
                        <th style="width: 80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>

    {{-- Submit button --}}
    <div class="d-flex justify-content-end gap-4 align-items-center mb-3">
        <a href="{{ route('discount.index') }}" class="text-primary">Tutup</a>
        <button class="btn btn-primary" id="submit-button">Simpan & Publikasi</button>
    </div>

    {{-- Modal add product --}}
    <x-modal id="add-product-modal" title="Tambah Produk" size="xl" color="primary">
        {{-- Filter section --}}
        <div class="row mb-3">
            <div class="col-lg-6">
                <div>
                    <label for="keyword" class="form-label">Nama/Sku Produk</label>
                    <input type="text" class="form-control" id="keyword">
                </div>
            </div>
            <div class="col-lg-3">
                <label for="product-select">Kategori</label>
                <select id="product-select" class="form-select select2-type">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <label for="store-select">Toko</label>
                <select id="store-select" class="form-select select2-type">
                    <option value="">Semua Toko</option>
                    @foreach ($stores as $store)
                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Product list --}}
        <table id="product-list-table" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
            <thead>
                <tr>
                    <th style="width: 30px;">
                        <input id="main-checkbox" name="main-checkbox" type="checkbox" value="">
                    </th>
                    <th>Nama Produk</th>
                    <th>Harga</th>
                    <th>Stok</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>

        @slot('footer')
            <div class="d-flex justify-content-end gap-2 align-items-center">
                <button type="button" class="btn btn-ghost-danger waves-effect waves-light material-shadow-none" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="add-product-button">Simpan</button>
            </div>
        @endslot
    </x-modal>

    {{-- Modal delete product --}}
    <x-modal id="delete-product-discount-modal" title="Hapus Produk dari Diskon" size="md">
        <p>Daftar produk yang akan dihapus:</p>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                </tr>
            </thead>
            <tbody id="product-list-to-delete">
                {{-- Daftar produk akan di load di sini --}}
            </tbody>
        </table>

        @slot('footer')
            <div class="d-flex justify-content-end gap-2 align-items-center">
                <button type="button" class="btn btn-ghost-secondary waves-effect waves-light material-shadow-none" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-product-discount-button">Hapus</button>
            </div>
        @endslot
    </x-modal>
@endsection


@section('css')
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-bootstrap-theme-0.1.0-beta.10/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-responsive-2.2.9/responsive.bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.dataTables.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-responsive-2.2.9/dataTables.responsive.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/dataTables.buttons.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.print.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/buttons.html5.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/ckeditor5-classic-40.2.0/ckeditor.js') }}"></script>
    <script src="{{ asset('build/js/pages/toast.init.js') }}"></script>

    <script>
        let state = {
            selectedProducts: [],
            selectedProductsToBeDeleted: [],
            dataTable: null,
        }

        const Utils = {
            debounce: (fn, delay) => {
                let debounceTimer;
                return function(...args) {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => fn.apply(this, args), delay);
                }
            }
        }

        const Discount = {
            validateForm: () => {
                let nameInput = $('#discount-name');
                let startDateInput = $('#start-date');
                let endDateInput = $('#end-date');
                let valueInput = $('#discount-value');
                let typeInput = $('input[name="discount-type"]');

                let isValid = true;

                // Validasi nama diskon, harus diisi dan tidak boleh kosong
                if (nameInput.val().trim() === '') {
                    nameInput.addClass('is-invalid');
                    isValid = false;
                } else {
                    nameInput.removeClass('is-invalid');
                }

                // Validasi tanggal mulai, harus diisi, 
                if (startDateInput.val() === '') {
                    startDateInput.addClass('is-invalid');
                    isValid = false;
                } else {
                    startDateInput.removeClass('is-invalid');
                }

                // Validasi tanggal berakhir, harus diisi, tidak boleh kurang dari tanggal mulai
                if (endDateInput.val() === '' || endDateInput.val() < startDateInput.val()) {
                    endDateInput.addClass('is-invalid');
                    isValid = false;
                } else {
                    endDateInput.removeClass('is-invalid');
                }

                // Validasi nilai diskon, harus diisi, harus berupa angka positif
                // Jika tipe diskon adalah persentase, nilai harus antara 1-100
                let value = valueInput.cleanVal();
                if (valueInput.val().trim() === '' || isNaN(value) || value <= 0) {
                    valueInput.addClass('is-invalid');
                    isValid = false;
                } else if ($('#percentage-type').is(':checked') && (value < 1 || value > 100)) {
                    valueInput.addClass('is-invalid');
                    isValid = false;
                } else {
                    valueInput.removeClass('is-invalid');
                }

                // Validasi tipe diskon, harus dipilih salah satu
                if (!typeInput.is(':checked')) {
                    typeInput.addClass('is-invalid');
                    isValid = false;
                } else {
                    typeInput.removeClass('is-invalid');
                }

                if (!isValid) {
                    showToast('warning', 'Peringatan', 'Mohon periksa kembali inputan Anda.', 'ri-alert-fill');
                    return false;
                }

                return true;
            }
        }

        const Product = {
            setupAddProductModal: () => {
                // Event listener untuk tombol "Tambah Produk"
                $('#add-product').on('click', function() {
                    $('#add-product-modal').modal('show');
                });

                // Event listener untuk filter produk
                $('#add-product-modal').on('shown.bs.modal', function() {
                    Product.initAddProductModalData();
                });

                // Event listener ketika modal ditutup
                $('#add-product-modal').on('hidden.bs.modal', function() {
                    // Reset filter
                    $('#keyword').val('');
                    $('#product-select').val('').trigger('change');
                    $('#store-select').val('').trigger('change');

                    // Kosongkan tabel produk
                    $('#product-list-table tbody').empty();

                    // Update state main checkbox
                    Product.updateProductMainCheckboxState();

                    // Reset state selectedProducts
                    state.selectedProducts = [];
                });

                // Event listener untuk input pencarian
                $('#keyword').on('input', Utils.debounce(function() {
                    let keyword = $(this).val().trim();
                    let categoryId = $('#product-select').val();
                    let storeId = $('#store-select').val();

                    Product.initAddProductModalData(keyword, categoryId, storeId);
                }, 1000));

                // Event listener untuk perubahan kategori
                $('#product-select').on('change', function() {
                    let categoryId = $(this).val();
                    let keyword = $('#keyword').val().trim();
                    let storeId = $('#store-select').val();

                    Product.initAddProductModalData(keyword, categoryId, storeId);
                });

                // Event listener untuk perubahan toko
                $('#store-select').on('change', function() {
                    let storeId = $(this).val();
                    let keyword = $('#keyword').val().trim();
                    let categoryId = $('#product-select').val();

                    Product.initAddProductModalData(keyword, categoryId, storeId);
                });

                // Event listener untuk checkbox utama
                $(document).on('change', '#main-checkbox', function() {
                    let isChecked = $(this).is(':checked');
                    $('.sub-checkbox').prop('checked', isChecked);

                    // Update selectedProducts
                    if (isChecked) {
                        const newSelections = $('.sub-checkbox').map(function() {
                            return $(this).val();
                        }).get();

                        state.selectedProducts = Array.from(new Set([...state.selectedProducts, ...newSelections]));
                    } else {
                        const unchecked = $('.sub-checkbox').map(function() {
                            return $(this).val();
                        }).get();

                        state.selectedProducts = state.selectedProducts.filter(id => !unchecked.includes(id));
                    }
                });

                // Event listener untuk checkbox sub
                $(document).on('change', '.sub-checkbox', function() {
                    Product.updateProductMainCheckboxState();

                    let productId = $(this).val();
                    if ($(this).is(':checked')) {
                        if (!state.selectedProducts.includes(productId)) {
                            state.selectedProducts.push(productId);
                        }
                    } else {
                        state.selectedProducts = state.selectedProducts.filter(id => id !== productId);
                    }
                });

                // Event listener untuk tombol "Simpan" di modal
                $('#add-product-button').on('click', function() {
                    if (state.selectedProducts.length === 0) {
                        showToast('warning', 'Peringatan', 'Silahkan pilih minimal satu produk.', 'ri-alert-fill');
                        return;
                    }

                    // Disable button dan tampilkan loading
                    $(this).prop('disabled', true).text('Menyimpan...');

                    const discountProducts = state.selectedProducts.map(productDetailId => ({
                        discount_id: '{{ $discount->id }}',
                        product_detail_id: productDetailId
                    }));

                    $.ajax({
                        url: `{{ route('discount.addProduct', ['id' => $discount->id]) }}`,
                        method: 'POST',
                        data: {
                            discount_products: discountProducts
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            showToast('success', 'Sukses', response.message, 'ri-check-fill');
                            $('#add-product-modal').modal('hide');
                            state.dataTable.ajax.reload();

                            // Reset state selectedProducts
                            state.selectedProducts = [];
                            $('#add-product-button').prop('disabled', false).text('Simpan');
                        },
                        error: function(xhr) {
                            showToast('danger', 'Error', 'Terjadi kesalahan saat menambahkan produk ke diskon', 'ri-error-warning-fill');
                            $('#add-product-button').prop('disabled', false).text('Simpan');
                        }
                    });
                });
            },

            updateProductMainCheckboxState: () => {
                // Cek apakah tidak ada sub-checkbox, case ketika tabel kosong
                if ($('.sub-checkbox').length === 0) {
                    $('#main-checkbox').prop('checked', false);
                    return;
                }

                let allChecked = $('.sub-checkbox').length === $('.sub-checkbox:checked').length;
                $('#main-checkbox').prop('checked', allChecked);
            },

            initAddProductModalData: (keyword = '', categoryId = '', storeId = '') => {
                // Fetch data produk dari server menggunakan ajax
                $.ajax({
                    url: `{{ route('discount.searchProduct') }}`,
                    method: 'GET',
                    data: {
                        keyword: keyword,
                        category_id: categoryId,
                        store_id: storeId,
                        discount_id: '{{ $discount->id }}'
                    },
                    beforeSend: function() {
                        let tableBody = $('#product-list-table tbody');
                        let loadingRow = `
                            <tr>
                                <td colspan="4" class="text-center">Loading...</td>
                            </tr>
                        `;

                        tableBody.append(loadingRow);
                    },
                    success: function(response) {
                        let products = response.products;
                        let tableBody = $('#product-list-table tbody');
                        tableBody.empty(); // clear loading row

                        if (products.length > 0) {
                            products.forEach((product, index) => {
                                let row = `
                                    <tr>
                                        <td>
                                            <input class="sub-checkbox" name="sub-checkbox" type="checkbox" value="${product.id}" ${Product.isProductSelected(product.id) ? 'checked' : ''}>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="${product.product.main_pic}" alt="Product Image" class="avatar">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">${product.product.title}</h6>
                                                    <p class="text-muted mb-0" style="font-size: 11px">Varian: ${product.variant.name}, Warna: ${product.color.label}</p>
                                                    <p class="text-muted mb-0" style="font-size: 11px">SKU: ${product.sku}</p>
                                                    <p class="text-muted mb-0" style="font-size: 11px">Toko: ${product.store.name}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>${Product.formatPrice(product.price)}</td>
                                        <td>${Product.formatNumber(product.qty)}</td>
                                    </tr>
                                `;

                                tableBody.append(row);
                            });
                        } else {
                            let row = `
                                <tr>
                                    <td colspan="4" class="text-center">Produk tidak ditemukan!</td>
                                </tr>
                            `;

                            tableBody.append(row);
                        }

                        // Update state main checkbox
                        Product.updateProductMainCheckboxState();
                    },
                    error: function(xhr) {
                        tableBody.empty();
                        showToast('error', 'Error', 'Terjadi kesalahan saat mengambil data produk', 'ri-error-warning-fill');
                    }
                });
            },

            formatPrice: (price) => {
                return 'Rp. ' + Number(price).toLocaleString("id-ID");
            },

            formatNumber: (num) => {
                return Number(num).toLocaleString("id-ID");
            },

            isProductSelected: (productId) => {
                return state.selectedProducts.includes(productId);
            },

            setupProductDatatable: () => {
                state.dataTable = new DataTable('#product-discount-table', {
                    dom: "<'row'<'col-sm-8 col-md-8 btn-tbl'B><'col-sm-4 col-md-4'<'span dt-right'l>>>" +
                        "<'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    processing: true,
                    serverSide: true,
                    autoWidth: false,
                    scrollX: true,
                    lengthMenu: [
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
                        @if (\RuleHelper::UserHasPermission('delete', false))
                            {
                                text: '<i class="las la-trash text-danger"></i> Bulk Delete',
                                className: 'child-button',
                                action: function(e) {
                                    state.selectedProductsToBeDeleted = [];
                                    $('.product-datatable-sub-checkbox:checked').each(function() {
                                        state.selectedProductsToBeDeleted.push($(this).data('product-detail-id'));
                                    });

                                    if (state.selectedProductsToBeDeleted.length === 0) {
                                        showToast('warning', 'Peringatan', 'Tidak ada produk yang dipilih untuk dihapus.', 'ri-alert-fill');
                                        return;
                                    }

                                    // Buka modal konfirmasi dan tampilkan daftar produk yang akan dihapus
                                    let productList = $('#product-list-to-delete');
                                    productList.empty();

                                    $('.product-datatable-sub-checkbox:checked').each(function(index) {
                                        let productName = $(this).data('product-name');
                                        let variantName = $(this).data('variant-name');
                                        let colorLabel = $(this).data('color-label');
                                        let storeName = $(this).data('store-name');
                                        let sku = $(this).data('sku');

                                        productList.append(`
                                            <tr>
                                                <td>${index + 1}</td>
                                                <td>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">${productName}</h6>
                                                        <p class="text-muted mb-0" style="font-size: 11px">Varian: ${variantName}, Warna: ${colorLabel}</p>
                                                        <p class="text-muted mb-0" style="font-size: 11px">SKU: ${sku}</p>
                                                        <p class="text-muted mb-0" style="font-size: 11px">Toko: ${storeName}</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        `);
                                    });

                                    $('#delete-product-discount-modal').modal('show');
                                }
                            },
                        @endif
                    ],
                    ajax: {
                        url: "{{ route('discount.productDatatable', ['id' => $discount->id]) }}",
                        type: "GET",
                        data: function(data) {
                            i = data.start + 1;
                        }
                    },
                    columns: [{
                            data: 'checkboxs',
                            orderable: false,
                            searchable: false,
                            width: '20px'
                        },
                        {
                            data: 'number',
                            width: '20px',
                            render: () => i++
                        },
                        {
                            data: 'name'
                        },
                        {
                            data: 'price'
                        },
                        {
                            data: 'discounted_price'
                        },
                        {
                            data: 'qty'
                        },
                        {
                            data: 'action',
                            orderable: false,
                            searchable: false,
                            width: '80px'
                        }
                    ]
                });
            },

            setupProductDatatableEvents: () => {
                // Event listener untuk tombol hapus pada setiap baris
                $('#product-discount-table tbody').on('click', '.delete-product-discount', function(e) {
                    e.preventDefault();
                    state.selectedProductsToBeDeleted = [];
                    state.selectedProductsToBeDeleted.push($(this).data('product-detail-id'));

                    let discountId = $(this).data('discount-id');
                    let productName = $(this).data('product-name');
                    let variantName = $(this).data('variant-name');
                    let colorLabel = $(this).data('color-label');
                    let storeName = $(this).data('store-name');
                    let sku = $(this).data('sku');

                    // Buka modal konfirmasi dan tampilkan nama produk
                    let productList = $('#product-list-to-delete');
                    productList.empty();
                    productList.append(`
                        <tr>
                            <td>1</td>
                            <td>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${productName}</h6>
                                    <p class="text-muted mb-0" style="font-size: 11px">Varian: ${variantName}, Warna: ${colorLabel}</p>
                                    <p class="text-muted mb-0" style="font-size: 11px">SKU: ${sku}</p>
                                    <p class="text-muted mb-0" style="font-size: 11px">Toko: ${storeName}</p>
                                </div>
                            </td>
                        </tr>
                    `);

                    $('#delete-product-discount-modal').modal('show');
                })

                // Event listener ketika sub-checkbox diubah
                $('#product-discount-table tbody').on('change', '.product-datatable-sub-checkbox', function() {
                    // Update state main checkbox
                    let allChecked = $('.product-datatable-sub-checkbox').length === $('.product-datatable-sub-checkbox:checked').length;
                    $('#product-datatable-main-checkbox').prop('checked', allChecked);

                    let productDetailId = $(this).data('product-detail-id');
                    if ($(this).is(':checked')) {
                        if (!state.selectedProductsToBeDeleted.includes(productDetailId)) {
                            state.selectedProductsToBeDeleted.push(productDetailId);
                        }
                    } else {
                        state.selectedProductsToBeDeleted = state.selectedProductsToBeDeleted.filter(id => id !== productDetailId);
                    }
                });
            },

            setupRemoveProductFromDiscountEvent: () => {
                // Event listener ketika modal hapus produk ditutup
                $('#delete-product-discount-modal').on('hidden.bs.modal', function() {
                    // Reset state selectedProductsToBeDeleted
                    state.selectedProductsToBeDeleted = [];
                    $('#product-list-to-delete').empty();
                });

                // Event listener ketika main checkbox di header diubah
                $('#product-discount-table thead').on('change', '#product-datatable-main-checkbox', function() {
                    let isChecked = $(this).is(':checked');
                    $('.product-datatable-sub-checkbox').prop('checked', isChecked);

                    // Update selectedProductsToBeDeleted
                    if (isChecked) {
                        const newSelections = $('.product-datatable-sub-checkbox').map(function() {
                            return $(this).data('product-detail-id');
                        }).get();

                        state.selectedProductsToBeDeleted = Array.from(new Set([...state.selectedProductsToBeDeleted, ...newSelections]));
                    } else {
                        const unchecked = $('.product-datatable-sub-checkbox').map(function() {
                            return $(this).data('product-detail-id');
                        }).get();

                        state.selectedProductsToBeDeleted = state.selectedProductsToBeDeleted.filter(id => !unchecked.includes(id));
                    }
                });

                // Event listener untuk tombol konfirmasi hapus
                $('#confirm-delete-product-discount-button').on('click', function() {
                    if (state.selectedProductsToBeDeleted.length === 0) {
                        showToast('warning', 'Peringatan', 'Tidak ada produk yang dipilih untuk dihapus.', 'ri-alert-fill');
                        return;
                    }

                    // Disable button dan tampilkan loading
                    $(this).prop('disabled', true).text('Menghapus...');

                    $.ajax({
                        url: `{{ route('discount.removeProduct', ['id' => $discount->id]) }}`,
                        method: 'DELETE',
                        data: {
                            product_detail_ids: state.selectedProductsToBeDeleted
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            showToast('success', 'Sukses', response.message, 'ri-check-fill');
                            $('#delete-product-discount-modal').modal('hide');
                            state.dataTable.ajax.reload();

                            // Reset state selectedProductsToBeDeleted
                            state.selectedProductsToBeDeleted = [];
                            $('#confirm-delete-product-discount-button').prop('disabled', false).text('Hapus');
                        },
                        error: function(xhr) {
                            state.selectedProductsToBeDeleted = [];
                            showToast('danger', 'Error', 'Terjadi kesalahan saat menghapus produk dari diskon', 'ri-error-warning-fill');
                            $('#confirm-delete-product-discount-button').prop('disabled', false).text('Hapus');
                        }
                    });
                });
            }
        }

        const EventListeners = {
            setupAllEvents: () => {
                EventListeners.setupSelect2();
                EventListeners.setupInputMask();
                EventListeners.setupDiscountTypeChange();
                EventListeners.setupSubmitButton();
                Product.setupAddProductModal();
                Product.setupRemoveProductFromDiscountEvent();
                Product.setupProductDatatable();
                Product.setupProductDatatableEvents();
            },

            setupInputMask: () => {
                $('#discount-value').mask('000.000.000', {
                    reverse: true
                });
            },

            setupSelect2: () => {
                $(document).on('shown.bs.modal', '.modal', function() {
                    $('.select2-type').select2();
                });
            },

            setupDiscountTypeChange: () => {
                $('input[name="discount-type"]').on('change', function() {
                    $('#discount-value').val('');
                });
            },

            setupSubmitButton: () => {
                $('#submit-button').on('click', function() {
                    if (!Discount.validateForm()) {
                        $('#submit-button').prop('disabled', false).text('Simpan & Publikasi');
                        return;
                    }

                    // Disable button dan tampilkan loading
                    $(this).prop('disabled', true).text('Menyimpan...');

                    let updatedDiscount = {
                        name: $('#discount-name').val().trim(),
                        start_date: $('#start-date').val(),
                        end_date: $('#end-date').val(),
                        value: $('#discount-value').cleanVal(),
                        type: $('input[name="discount-type"]:checked').attr('id') === 'percentage-type' ? 'percent' : 'nominal',
                    };

                    $.ajax({
                        url: `{{ route('discount.update', ['discount' => $discount->id]) }}`,
                        method: 'PUT',
                        data: updatedDiscount,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            showToast('success', 'Sukses', response.message, 'ri-check-fill');
                            $('#submit-button').prop('disabled', false).text('Simpan & Publikasi');
                            state.dataTable.ajax.reload();
                        },
                        error: function(xhr) {
                            showToast('error', 'Error', 'Terjadi kesalahan saat memperbarui data', 'ri-error-warning-fill');
                        }
                    });
                });
            },
        }

        $(document).ready(function() {
            EventListeners.setupAllEvents();
        });
    </script>
@endsection
