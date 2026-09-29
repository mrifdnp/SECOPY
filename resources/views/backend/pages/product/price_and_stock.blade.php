@extends('layouts.master')

@section('title')
    Detail {{ $titleHeader->name }}
@endsection

@section('cssAdded')
    <style>
        .sticky-table {
            border-collapse: separate;
        }

        .sticky-col {
            position: sticky;
            z-index: 5;
        }

        .first-col {
            left: 0;
            min-width: 150px;
            max-width: 150px;
            background-color: var(--vz-card-bg-custom) !important;
        }

        .second-col {
            left: 150px;
            min-width: 100px;
            max-width: 100px;
            background-color: var(--vz-card-bg-custom) !important;
        }

        .last-col {
            right: 0;
            min-width: 60px;
            max-width: 60px;
            text-align: center;
            background-color: var(--vz-card-bg-custom) !important;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Systems
        @endslot
        @slot('title')
            Detail {{ $titleHeader->name }}
        @endslot
    @endcomponent

    <div class="d-flex mb-3 align-items-start justify-content-between">
        <div>
            <h4 id="product-name" class="mb-2">{{ $product->title }}</h4>
            @if(isset($pivotStock) && $pivotStock->count() > 0)
                <div class="mt-2 table-responsive">
                    <table class="table table-sm table-bordered table-striped mb-0" style="font-size: 13px;">
                        <thead class="table-light text-center align-middle">
                            <tr>
                                <th class="py-1" rowspan="2">Varian</th>
                                <th class="py-1" rowspan="2">Warna</th>
                                <th class="py-1" colspan="{{ $pivotStoreNames->count() }}">Stok per Cabang / Toko</th>
                            </tr>
                            <tr>
                                @foreach($pivotStoreNames as $sName)
                                    <th class="py-1">{{ $sName }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="text-center align-middle">
                            @foreach($pivotStock as $row)
                                <tr>
                                    <td class="py-1 text-start">{{ $row['variant'] }}</td>
                                    <td class="py-1 text-start">{{ $row['color'] }}</td>
                                    @foreach($pivotStoreNames as $sName)
                                        <td class="py-1 fw-semibold {{ $row['stocks'][$sName]['qty'] > 0 ? 'text-success' : 'text-muted' }}">
                                            @if($row['stocks'][$sName]['detail_id'])
                                                <a href="{{ route('product.productDetailShow', ['id' => $product->id, 'detailId' => $row['stocks'][$sName]['detail_id']]) }}" class="text-decoration-underline {{ $row['stocks'][$sName]['qty'] > 0 ? 'text-success' : 'text-muted' }}" title="Lihat Riwayat Stok" target="_blank">
                                                    {{ $row['stocks'][$sName]['qty'] }} <i class="ri-eye-line ms-1"></i>
                                                </a>
                                            @else
                                                {{ $row['stocks'][$sName]['qty'] }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        <div class="btn-group" role="group">
            <a href="{{ route('product.basicInformation', ['id' => $product->id]) }}" class="btn btn-primary material-shadow-none">Informasi Dasar</a>
            <a href="#" class="btn btn-primary material-shadow-none active">Stok dan Harga</a>
            <a href="{{ route('product.productDetail', ['id' => $product->id]) }}" class="btn btn-primary material-shadow-none">Detail Produk</a>
        </div>
    </div>

    @foreach ($stores as $storeName => $productVariants)
        <div class="card mb-3" id="store-section" data-store="{{ $storeName }}" data-store-id="{{ $productVariants->first()->store_id }}">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">{{ $storeName }}</h6>
                    <div class="btn-group dropstart" role="group">
                        <button id="generate-sku" type="button" class="btn btn-primary dropdown-toggle material-shadow-none" data-bs-toggle="dropdown" aria-expanded="false">
                            Isi SKU Otomatis
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="generate-sku">
                            <li><a class="dropdown-item" href="#">Isi Semua</a></li>
                            <li><a class="dropdown-item" href="#">Hanya Isi yang Kosong</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive" style="overflow-x:auto; max-width:100%;">
                    <table class="table table-bordered table-striped table-hover sticky-table">
                        <thead>
                            <tr>
                                <th class="sticky-col first-col">Varian</th>
                                <th class="sticky-col second-col">Warna</th>
                                <th style="min-width:150px;">Harga Jual*</th>
                                <th style="min-width:150px;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>Harga Tambahan</span>
                                        <i class="ri-information-line" data-bs-toggle="tooltip" data-bs-placement="top" title="Harga tambahan untuk kebutuhan packing dan lainnya"></i>
                                    </div>
                                </th>
                                @if(in_array(session()->get('current_role')->name, ['super', 'admin']))
                                <th style="min-width:150px;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>COGM (Cost)</span>
                                        <i class="ri-information-line" data-bs-toggle="tooltip" data-bs-placement="top" title="Harga Pokok Produksi / Cost of Goods Manufactured"></i>
                                    </div>
                                </th>
                                @endif
                                <th style="min-width:90px;">Stok*</th>
                                <th style="min-width:150px;">SKU Penjualan</th>
                                <th style="min-width:90px;">Berat (g)*</th>
                                <th class="sticky-col last-col"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productVariants as $productVariant)
                                <tr data-id="{{ $productVariant->id }}" data-variant-id="{{ $productVariant->variant_id }}" data-color-id="{{ $productVariant->color_id }}">
                                    <td class="sticky-col first-col">{{ $productVariant->variant->name }}</td>
                                    <td class="sticky-col second-col">{{ $productVariant->color->label }}</td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" id="price-{{ $productVariant->id }}" class="form-control number-format" value="{{ $productVariant->price }}">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" id="additional-price-{{ $productVariant->id }}" class="form-control number-format" value="{{ $productVariant->additional_price }}">
                                        </div>
                                    </td>
                                    @if(in_array(session()->get('current_role')->name, ['super', 'admin']))
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" id="cogm-{{ $productVariant->id }}" class="form-control number-format" value="{{ $productVariant->cogm }}">
                                        </div>
                                    </td>
                                    @endif
                                    <td><input type="number" id="stock-{{ $productVariant->id }}" class="form-control" value="{{ $productVariant->qty }}"></td>
                                    <td><input type="text" id="sku-{{ $productVariant->id }}" class="form-control" value="{{ $productVariant->sku }}"></td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">g</span>
                                            <input type="text" id="weight-{{ $productVariant->id }}" class="form-control number-format" value="{{ $productVariant->weight }}">
                                        </div>
                                    </td>
                                    <td class="sticky-col last-col">
                                        <a href="{{ route('product.productDetailShow', ['id' => $product->id, 'detailId' => $productVariant->id]) }}" class="btn btn-soft-secondary"><i class="ri-eye-fill"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-end">
        <button class="btn btn-primary mb-3" id="submit-button">Simpan dan Lanjutkan</button>
    </div>
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-bootstrap-theme-0.1.0-beta.10/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js') }}"></script>
    <script src="{{ asset('build/js/pages/toast.init.js') }}"></script>

    <script>
        const Utils = {
            parseNumericValue: (value) => {
                if (!value || value === '') return 0;
                return parseFloat(value.toString().split('.').join(''));
            },
        }

        const ProductDetail = {
            setupProductDetailEvents: () => {
                // validasi real-time pada input
                $('input[data-type="numeric"]').on('blur', function() {
                    const input = $(this);
                    const value = Utils.parseNumericValue(input.val());

                    if (input.attr('id').includes('price-') || input.attr('id').includes('cogm-') || input.attr('id').includes('weight-')) {
                        if (isNaN(value) || value < 0) {
                            input.addClass('is-invalid');
                        } else {
                            input.removeClass('is-invalid');
                        }
                    } else if (input.attr('id').includes('stock-')) {
                        if (isNaN(value)) {
                            input.addClass('is-invalid');
                        } else {
                            input.removeClass('is-invalid');
                        }
                    }
                });

                $('.number-format').each(function() {
                    const input = $(this);
                    input.mask('000.000.000.000.000', {
                        reverse: true,
                    });
                });

                // Sync harga antar toko (Kecuali Seco Exhibition)
                $(document).on('input keyup', 'input[id^="price-"]', function() {
                    const $input = $(this);
                    const value = $input.val();
                    
                    const $row = $input.closest('tr');
                    const variantId = $row.data('variant-id');
                    const colorId = $row.data('color-id');
                    
                    const $currentStoreSection = $input.closest('[data-store]');
                    const currentStoreName = $currentStoreSection.data('store');

                    // Jika yang diubah adalah toko Seco Exhibition, jangan sync ke toko lain
                    if (currentStoreName && currentStoreName.match(/Seco Exhibition/i)) return;

                    // Cari row di toko lain dengan varian dan warna yang sama
                    $(`tr[data-variant-id="${variantId}"][data-color-id="${colorId}"]`).each(function() {
                        const $targetRow = $(this);
                        if ($targetRow.data('id') === $row.data('id')) return; // Skip row sendiri

                        const $targetStoreSection = $targetRow.closest('[data-store]');
                        const targetStoreName = $targetStoreSection.data('store');

                        // Jangan sync ke toko Seco Exhibition
                        if (targetStoreName && targetStoreName.match(/Seco Exhibition/i)) return;

                        // Update value
                        const $targetInput = $targetRow.find('input[id^="price-"]');
                        if ($targetInput.length > 0 && $targetInput.val() !== value) {
                            $targetInput.val(value);
                        }
                    });
                });

                // Sync Harga Tambahan antar toko (Kecuali Seco Exhibition)
                $(document).on('input keyup', 'input[id^="additional-price-"]', function() {
                    const $input = $(this);
                    const value = $input.val();
                    
                    const $row = $input.closest('tr');
                    const variantId = $row.data('variant-id');
                    const colorId = $row.data('color-id');
                    
                    const $currentStoreSection = $input.closest('[data-store]');
                    const currentStoreName = $currentStoreSection.data('store');

                    // Jika yang diubah adalah toko Seco Exhibition, jangan sync ke toko lain
                    if (currentStoreName && currentStoreName.match(/Seco Exhibition/i)) return;

                    // Cari row di toko lain dengan varian dan warna yang sama
                    $(`tr[data-variant-id="${variantId}"][data-color-id="${colorId}"]`).each(function() {
                        const $targetRow = $(this);
                        if ($targetRow.data('id') === $row.data('id')) return; // Skip row sendiri

                        const $targetStoreSection = $targetRow.closest('[data-store]');
                        const targetStoreName = $targetStoreSection.data('store');

                        // Jangan sync ke toko Seco Exhibition
                        if (targetStoreName && targetStoreName.match(/Seco Exhibition/i)) return;

                        // Update value
                        const $targetInput = $targetRow.find('input[id^="additional-price-"]');
                        if ($targetInput.length > 0 && $targetInput.val() !== value) {
                            $targetInput.val(value);
                        }
                    });
                });

                // Sync COGM antar toko (SEMUA Toko)
                $(document).on('input keyup', 'input[id^="cogm-"]', function() {
                    const $input = $(this);
                    const value = $input.val();
                    
                    const $row = $input.closest('tr');
                    const variantId = $row.data('variant-id');
                    const colorId = $row.data('color-id');

                    $(`tr[data-variant-id="${variantId}"][data-color-id="${colorId}"]`).each(function() {
                        const $targetRow = $(this);
                        if ($targetRow.data('id') === $row.data('id')) return;

                        const $targetInput = $targetRow.find('input[id^="cogm-"]');
                        if ($targetInput.length > 0 && $targetInput.val() !== value) {
                            $targetInput.val(value);
                        }
                    });
                });

                // Sync Berat antar toko (SEMUA Toko)
                $(document).on('input keyup', 'input[id^="weight-"]', function() {
                    const $input = $(this);
                    const value = $input.val();
                    
                    const $row = $input.closest('tr');
                    const variantId = $row.data('variant-id');
                    const colorId = $row.data('color-id');

                    $(`tr[data-variant-id="${variantId}"][data-color-id="${colorId}"]`).each(function() {
                        const $targetRow = $(this);
                        if ($targetRow.data('id') === $row.data('id')) return;

                        const $targetInput = $targetRow.find('input[id^="weight-"]');
                        if ($targetInput.length > 0 && $targetInput.val() !== value) {
                            $targetInput.val(value);
                        }
                    });
                });

                // Sync SKU antar toko (SEMUA Toko)
                $(document).on('input keyup', 'input[id^="sku-"]', function() {
                    const $input = $(this);
                    const value = $input.val();
                    
                    const $row = $input.closest('tr');
                    const variantId = $row.data('variant-id');
                    const colorId = $row.data('color-id');

                    $(`tr[data-variant-id="${variantId}"][data-color-id="${colorId}"]`).each(function() {
                        const $targetRow = $(this);
                        if ($targetRow.data('id') === $row.data('id')) return;

                        const $targetInput = $targetRow.find('input[id^="sku-"]');
                        if ($targetInput.length > 0 && $targetInput.val() !== value) {
                            $targetInput.val(value);
                        }
                    });
                });
            },

            validateForm: () => {
                let isValid = true;

                $('.sticky-table tbody tr').each(function(index) {
                    const row = $(this);
                    const priceInput = row.find('[id^="price-"]');
                    const additionalPriceInput = row.find('[id^="additional-price-"]');
                    const cogmInput = row.find('[id^="cogm-"]');
                    const stockInput = row.find('[id^="stock-"]');
                    const weightInput = row.find('[id^="weight-"]');
                    const skuInput = row.find('[id^="sku-"]');

                    // Validasi price (required, numeric, min:0)
                    const price = Utils.parseNumericValue(priceInput.val());
                    if (isNaN(price) || price < 0) {
                        isValid = false;
                        priceInput.addClass('is-invalid');
                    } else {
                        priceInput.removeClass('is-invalid');
                    }

                    // Validasi additional price (nullable, numeric, min:0)
                    const additionalPrice = Utils.parseNumericValue(additionalPriceInput.val());
                    if (additionalPriceInput.val() !== '' && (isNaN(additionalPrice) || additionalPrice < 0)) {
                        isValid = false;
                        additionalPriceInput.addClass('is-invalid');
                    } else {
                        additionalPriceInput.removeClass('is-invalid');
                    }

                    // Validasi cogm (nullable/required, numeric, min:0)
                    const cogmPrice = Utils.parseNumericValue(cogmInput.val());
                    if (cogmInput.val() !== '' && (isNaN(cogmPrice) || cogmPrice < 0)) {
                        isValid = false;
                        cogmInput.addClass('is-invalid');
                    } else {
                        cogmInput.removeClass('is-invalid');
                    }

                    // Validasi stock (required, integer)
                    const stock = Utils.parseNumericValue(stockInput.val());
                    if (isNaN(stock)) {
                        isValid = false;
                        stockInput.addClass('is-invalid');
                    } else {
                        stockInput.removeClass('is-invalid');
                    }

                    // Validasi sku, harus unik disetiap store
                    // Jika ada sku yang sama di store yang berbeda, maka akan dianggap valid
                    // Tapi SKU boleh kosong
                    const sku = skuInput.val().trim();
                    if (sku !== '') {
                        const duplicateSku = $(`input[id^="sku-"]`).filter(function() {
                            return $(this).val().trim() === sku && $(this).attr('id') !== skuInput.attr('id');
                        });

                        // Cek apakah duplicateSku ada di store yang sama
                        let isDuplicateInSameStore = false;
                        duplicateSku.each(function() {
                            const duplicateRow = $(this).closest('tr');
                            if (duplicateRow.closest('#store-section').data('store') === row.closest('#store-section').data('store')) {
                                isDuplicateInSameStore = true;
                                return false; // break loop
                            }
                        });

                        if (isDuplicateInSameStore) {
                            isValid = false;
                            skuInput.addClass('is-invalid');
                        } else {
                            skuInput.removeClass('is-invalid');
                        }
                    } else {
                        skuInput.removeClass('is-invalid');
                    }

                    // Validasi weight (nullable, numeric, min:0)
                    const weight = Utils.parseNumericValue(weightInput.val());
                    if (weightInput.val() !== '' && (isNaN(weight) || weight < 0)) {
                        isValid = false;
                        weightInput.addClass('is-invalid');
                    } else {
                        weightInput.removeClass('is-invalid');
                    }
                });

                if (!isValid) {
                    showToast('danger', 'Validasi Gagal', 'Mohon periksa kembali input yang bertanda merah.', 'ri-error-warning-line');
                }

                return isValid;
            },

            generateSKU: (productName, variant, color) => {
                // Helper function untuk membersihkan dan normalize text
                function normalizeText(text) {
                    return text.trim().replace(/\s+/g, ' ').toUpperCase();
                }

                // Helper function untuk generate kode produk (6 huruf)
                function generateProductCode(name) {
                    const cleanName = normalizeText(name).replace(/[^A-Z\s]/g, '');
                    const allChars = cleanName.replace(/\s+/g, ''); // gabungkan semua kata

                    if (allChars.length === 0) return 'PRODUK';

                    if (allChars.length <= 6) {
                        // kalau nama pendek, isi dengan X sampai 6
                        return (allChars + 'XXXXXX').substring(0, 6);
                    }

                    // ambil huruf pertama dan terakhir
                    let code = allChars[0];
                    const lastChar = allChars[allChars.length - 1];

                    // ambil bagian tengah
                    const middle = allChars.slice(1, -1);

                    // hitung step untuk sampling huruf tengah (biar total = 6)
                    const step = Math.max(1, Math.floor(middle.length / 4));

                    for (let i = 0; i < middle.length && code.length < 5; i += step) {
                        code += middle[i];
                    }

                    // tambahkan huruf terakhir
                    code += lastChar;

                    // pastikan panjangnya tepat 6
                    return code.substring(0, 6);
                }

                // Helper function untuk generate kode variant
                function generateVariantCode(variant) {
                    const cleanVariant = normalizeText(variant);

                    // Jika variant berupa angka, return semua angka
                    if (/^\d+$/.test(cleanVariant.replace(/\s/g, ''))) {
                        return cleanVariant.replace(/\s/g, '');
                    }

                    // Jika variant berupa huruf, ambil huruf pertama dari setiap kata
                    const words = cleanVariant.split(' ').filter(word => word.length > 0);
                    return words.map(word => word[0]).join('');
                }

                // Helper function untuk generate kode warna
                function generateColorCode(color) {
                    const cleanColor = normalizeText(color);
                    const words = cleanColor.split(' ').filter(word => word.length > 0);

                    // Ambil huruf pertama dari setiap kata
                    return words.map(word => word[0]).join('');
                }

                // Generate masing-masing komponen
                const productCode = generateProductCode(productName);
                const variantCode = generateVariantCode(variant);
                const colorCode = generateColorCode(color);

                // Gabungkan menjadi SKU final
                return `${productCode}-${variantCode}${colorCode}`;
            },

            fillSKUForStore: ($storeSection, mode = 'all') => {
                let productName = $('#product-name').text();
                if (!productName || productName.trim() === '') {
                    console.error('Product name is empty. Cannot generate SKU.');
                    return;
                }

                const storeName = $storeSection.data('store');

                // Cari semua baris dalam tabel toko tersebut
                const $rows = $storeSection.find('tbody tr');
                let processedCount = 0;

                $rows.each(function() {
                    const $row = $(this);
                    const productId = $row.data('id');
                    const $skuInput = $(`#sku-${productId}`);

                    if ($skuInput.length === 0) return;

                    // Jika mode 'empty', skip jika SKU sudah ada
                    if (mode === 'empty' && $skuInput.val().trim() !== '') {
                        return;
                    }

                    // Ambil data variant dan warna dari tabel
                    const $cells = $row.find('td');
                    const variant = $cells.eq(0).text().trim();
                    const color = $cells.eq(1).text().trim();

                    // Generate SKU
                    const sku = ProductDetail.generateSKU(productName, variant, color);

                    // Isi ke input field dengan animasi
                    $skuInput.val(sku).trigger('change');

                    processedCount++;
                });
            }
        }

        const EventListener = {
            setupAll: () => {
                ProductDetail.setupProductDetailEvents();
                EventListener.setupSubmitButtonEvent();
                EventListener.setupSKUGenerationEvent();
            },

            setupSubmitButtonEvent: () => {
                $('#submit-button').on('click', function() {
                    // Validasi form sebelum mengirim data
                    if (!ProductDetail.validateForm()) {
                        // Jika validasi gagal, reset button dan keluar dari fungsi
                        $('#submit-button').prop('disabled', false).text('Simpan dan Lanjutkan');
                        return false; // Keluar dari each loop
                    }

                    // Disable button dan tampilkan loading
                    $(this).prop('disabled', true).text('Menyimpan...');

                    // Kumpulkan semua data dari form
                    const productDetails = [];

                    // Loop melalui semua row dalam tabel untuk mengumpulkan data
                    $('.sticky-table tbody tr').each(function() {

                        const row = $(this);
                        const productId = row.data('id');

                        // Ekstrak ID dari input field (ambil dari id attribute)
                        const priceInput = row.find('[id^="price-"]');
                        const additionalPriceInput = row.find('[id^="additional-price-"]');
                        const cogmInput = row.find('[id^="cogm-"]');
                        const stockInput = row.find('[id^="stock-"]');
                        const skuInput = row.find('[id^="sku-"]');
                        const weightInput = row.find('[id^="weight-"]');

                        if (priceInput.length > 0) {
                            const productData = {
                                id: productId,
                                price: Utils.parseNumericValue(priceInput.val()) || 0,
                                additional_price: Utils.parseNumericValue(additionalPriceInput.val()) || 0,
                                cogm: Utils.parseNumericValue(cogmInput.val()) || 0,
                                stock: Utils.parseNumericValue(stockInput.val()) || 0,
                                sku: skuInput.val() || '',
                                weight: Utils.parseNumericValue(weightInput.val()) || 0
                            };

                            productDetails.push(productData);
                        }
                    });

                    // Kirim AJAX request
                    $.ajax({
                        url: "{{ route('product.updatePriceAndStock', ['id' => $product->id]) }}",
                        type: 'POST',
                        contentType: 'application/json',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: JSON.stringify({
                            product_details: productDetails
                        }),
                        dataType: 'json',
                        success: function(response) {
                            // Tampilkan pesan sukses (sesuaikan dengan UI Anda)
                            showToast('success', 'Sukses', response.message, 'ri-check-double-fill');

                            // Reset button
                            $('#submit-button').prop('disabled', false).text('Simpan dan Lanjutkan');
                        },
                        error: function(xhr, status, error) {
                            // Handle validation errors
                            if (xhr.status === 422 && xhr.responseJSON.errors) {
                                showToast('danger', 'Validasi Gagal', 'Terjadi kegagalan validasi data', 'ri-error-warning-line');
                            } else if (xhr.status === 409 && xhr.responseJSON.message) {
                                // Berikan class is-invalid pada input SKU yang bermasalah
                                if (xhr.responseJSON.duplicated_skus) {
                                    xhr.responseJSON.duplicated_skus.forEach(function(duplicatedSku) {
                                        $(`input[id^="sku-"]`).filter(function() {
                                            let store = $(this).closest('#store-section').data('store-id') === duplicatedSku.store_id;
                                            let value = $(this).val().trim() === duplicatedSku.sku;
                                            return store && value;
                                        }).addClass('is-invalid');
                                    });
                                }

                                showToast('danger', 'Error', xhr.responseJSON.message, 'ri-error-warning-line');
                            } else {
                                showToast('danger', 'Error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.', 'ri-error-warning-line');
                            }

                            // Reset button
                            $('#submit-button').prop('disabled', false).text('Simpan dan Lanjutkan');
                        }
                    });
                });
            },

            setupSKUGenerationEvent: () => {
                // Event handler untuk dropdown "Isi Semua"
                $(document).on('click', '.dropdown-item:contains("Isi Semua")', function(e) {
                    e.preventDefault();

                    // Cari store section terdekat dari button yang diklik
                    const $storeSection = $(this).closest('[data-store]');

                    if ($storeSection.length > 0) {
                        ProductDetail.fillSKUForStore($storeSection, 'all');
                    } else {
                        console.error('Store section not found');
                    }
                });

                // Event handler untuk dropdown "Hanya Isi yang Kosong"
                $(document).on('click', '.dropdown-item:contains("Hanya Isi yang Kosong")', function(e) {
                    e.preventDefault();

                    // Cari store section terdekat dari button yang diklik
                    const $storeSection = $(this).closest('[data-store]');

                    if ($storeSection.length > 0) {
                        ProductDetail.fillSKUForStore($storeSection, 'empty');
                    } else {
                        console.error('Store section not found');
                    }
                });
            }
        }

        $(document).ready(function() {
            EventListener.setupAll();
        });
    </script>
@endsection
