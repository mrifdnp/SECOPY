@extends('layouts.master')
@section('title')
    Add {{ $titleHeader->name }}
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Systems
        @endslot
        @slot('title')
            Add {{ $titleHeader->name }}
        @endslot
    @endcomponent

    @include('backend.includes.flash')

    <div class="row align-items-stretch">
        <div class="col-lg-8">
            <!-- begin card 1 -->
            <div class="card mb-3">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <h4 class="mb-0">Daftar Produk</h4>
                        </div>
                        <div class="d-flex align-items-center">
                            <!-- Search Produk -->
                            <div class="input-group me-2">
                                <input
                                    type="text"
                                    class="form-control border-end-0 pe-0"
                                    id="searchEntitasList"
                                    placeholder="Cari Produk">
                                <span class="input-group-text bg-transparent border-start-0">
                                    <i class="ri-search-line"></i>
                                </span>
                            </div>

                            <!-- Scan Barcode Input -->
                            <div class="input-group">
                                <input type="text" id="barcodeScan" class="form-control border-end-0 pe-0" placeholder="Scan/Input Barcode..." autofocus>
                                <button class="btn btn-primary" id="barcodeScanAdded" type="button" title="Scan Barcode">
                                    <i class="mdi mdi-barcode-scan"></i>
                                </button>
                            </div>
                        </div>
                </div>
            </div>

            <!-- begin card 2 -->
            <div class="card mb-3">
                <div class="card-header">
                    <div id="categoryList"
                        class="d-flex align-items-center gap-2 overflow-auto flex-nowrap py-1"
                        role="group" aria-label="Category filter">

                        <button type="button" class="btn btn-primary d-flex align-items-center"
                                data-category="All" aria-pressed="true">
                            <span class="fw-medium text-nowrap">All</span>
                        </button>

                        @foreach ($main_category_titles as $mcId => $mcTitle)
                            <button type="button" class="btn btn-outline-primary d-flex align-items-center"
                                    data-id="{{ $mcId }}" data-category="{{ $mcTitle }}" aria-pressed="false">
                                <span class="fw-medium text-nowrap">{{ $mcTitle }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- begin list produk card -->
            <div class="mb-3" id="productGrid">
                @php
                    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="640" height="480" viewBox="0 0 640 480">
                                <rect width="100%" height="100%" fill="#e9ecef"/>
                                <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
                                    font-family="Arial, Helvetica, sans-serif" font-size="28" fill="#6c757d">
                                    No Image
                                </text>
                            </svg>';
                    $noImage = 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
                    $maxVisible = 12;
                @endphp
                
                <div class="row g-3">
                    @forelse ($products as $p)
                        @php $hidden = $loop->iteration > $maxVisible; @endphp

                        <div class="col-6 col-md-4 col-lg-3 {{ $hidden ? 'd-none extra-product' : '' }}"
                            data-title="{{ strtolower($p->product_title) }}"
                            data-category="{{ strtolower($p->category_title ?? '') }}"
                            data-top-pick="{{ (int)($p->top_pick ?? 0) }}">
                            <div class="card h-100 overflow-hidden cursor-pointer shadow"
                                data-id="{{ $p->id }}"
                                data-title="{{ $p->product_title }}"
                                data-category="{{ $p->category_title ?? '' }}"
                                data-price="{{ (int)($p->price ?? 0) }}"
                                data-img="{{ $p->main_pic ?? $noImage }}"
                                data-bs-toggle="modal" data-bs-target="#productModal"
                                tabindex="0">
                                <div class="p-2">
                                    <div class="ratio ratio-4x3 bg-light overflow-hidden">
                                        <img
                                            src="{{ $p->main_pic ?? $noImage }}"
                                            alt="{{ $p->product_title }}"
                                            class="w-100 h-100"
                                            style="object-fit: cover; object-position: center;"
                                            loading="lazy">
                                    </div>
                                </div>

                                <div class="card-body overflow-hidden p-2 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="card-title mb-1 fw-semibold text-truncate" title="{{ $p->product_title }}">
                                            {{ $p->product_title }}
                                        </h6>

                                        <div class="small text-muted text-truncate mb-2" title="{{ $p->category_title }}">
                                            {{ $p->category_title ?? '—' }}
                                        </div>
                                    </div>

                                    <!-- Badge diskon di atas harga (jika ada) -->
                                    @if(isset($p->best_discount_value) && (float)$p->best_discount_value > 0)
                                        <div class="mb-0">
                                            <span class="badge bg-danger">
                                                @if(($p->best_discount_type ?? null) === 'percent' && isset($p->raw_percent_value))
                                                    -{{ rtrim(rtrim(number_format((float)$p->raw_percent_value, 2, ',', '.'), '0'), ',') }}%
                                                @else
                                                    -{{ 'Rp ' . number_format((int)$p->best_discount_value, 0, ',', '.') }}
                                                @endif
                                            </span>
                                        </div>
                                    @endif

                                    <!--  Harga: coret original + tampilkan final bila ada diskon -->
                                    @if(isset($p->best_discount_value) && (float)$p->best_discount_value > 0 && isset($p->original_price))
                                        <div class="fw-semibold text-nowrap">
                                            <span>
                                                {{ 'Rp ' . number_format((int)($p->price ?? 0), 0, ',', '.') }}
                                            </span>
                                            <span class="text-muted text-decoration-line-through me-1">
                                                {{ 'Rp ' . number_format((int)($p->original_price ?? 0), 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="fw-semibold text-nowrap">
                                            {{ 'Rp ' . number_format((int)($p->price ?? 0), 0, ',', '.') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @empty
                            <div class="col-12">
                                <div class="text-center text-muted py-5">Tidak ada produk.</div>
                            </div>
                            
                    @endforelse
                </div>
            </div>
        </div>
        
        <!-- begin col 2 -->
        <div class="col-lg-4 d-flex vh-100 mb-4">
            <!-- begin cart list -->
            @include('backend.pages.sale_transaction._cart_list')
        </div>
        
    </div>

    <!-- modal add to cart -->
    @include('backend.pages.sale_transaction._product_modal')

    <!-- modal proses pembayaran / diskon -->
    @include('backend.pages.sale_transaction._payment_modal')

@endsection


@section('css')
    <link
        href="{{ URL::asset('build/libs/velzon-dependencies/select2-bootstrap-theme-0.1.0-beta.10/select2-bootstrap.min.css') }}"
        rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.css') }}" rel="stylesheet"
        type="text/css" />
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/ckeditor5-classic-40.2.0/ckeditor.js') }}"></script>


    <script>
        $('.sub_total').mask('000.000.000', {
            reverse: true
        });
        $('.discount_nominal').mask('000.000.000', {
            reverse: true
        });
        $('.additional_cost').mask('000.000.000', {
            reverse: true
        });
        $('.total').mask('000.000.000', {
            reverse: true
        });
    </script>

    <!-- Fungsi Pencarian & Filter Kategori Produk -->
    <script>
        $(() => {
            const ENDPOINT = "{{ route('sales-transaction.products.filter-search') }}";
            const LIMIT = 100;
            const NOIMG = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="640" height="480" viewBox="0 0 640 480"><rect width="100%" height="100%" fill="#e9ecef"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="28" fill="#6c757d">No Image</text></svg>');

            const $grid = $('#productGrid'),
                $cats = $('#categoryList'),
                $search = $('#searchEntitasList');

            const esc = s => (s || '').toString()
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            const rp = n => 'Rp ' + Number(n || 0).toLocaleString('id-ID');

            function card(p, hide) {
                const hasDiscount = Number(p?.best_discount_value || 0) > 0;
                const discountBadge = hasDiscount ?
                    `<div class="mb-0">
                        <span class="badge bg-danger">${
                            (p?.best_discount_type === 'percent' && p?.raw_percent_value != null)
                                ? `-${String(p.raw_percent_value).replace('.', ',')}%`
                                : `-${rp(p.best_discount_value)}`
                        }</span>
                    </div>` : '';

                const priceHtml = hasDiscount && p?.original_price != null ?
                    `<div class="fw-semibold text-nowrap">
                        <span>${rp(p.price)}</span>
                        <span class="text-muted text-decoration-line-through me-1">${rp(p.original_price)}</span>
                    </div>` :
                    `<div class="fw-semibold text-nowrap">${rp(p.price)}</div>`;

                return `
                <div class="col-6 col-md-4 col-lg-3${hide ? ' d-none extra-product' : ''}">
                    <div class="card h-100 overflow-hidden cursor-pointer shadow"
                        data-id="${p.id}"
                        data-title="${esc(p.product_title)}"
                        data-category="${esc(p.category_title)||''}"
                        data-price="${Number(p.price||0)}"
                        data-img="${p.main_pic || NOIMG}"
                        data-bs-toggle="modal" data-bs-target="#productModal"
                        tabindex="0">
                        <div class="p-2">
                            <div class="ratio ratio-4x3 bg-light overflow-hidden">
                                <img src="${p.main_pic || NOIMG}" alt="${esc(p.product_title)}" class="w-100 h-100" style="object-fit:cover;object-position:center;" loading="lazy">
                            </div>
                        </div>
                        <div class="card-body overflow-hidden p-2 d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="card-title mb-1 fw-semibold text-truncate" title="${esc(p.product_title)}">${esc(p.product_title)}</h6>
                                <div class="small text-muted text-truncate mb-2" title="${esc(p.category_title)}">${esc(p.category_title) || '—'}</div>
                            </div>
                            ${discountBadge}
                            ${priceHtml}
                        </div>
                    </div>
                </div>`;
            }

            function lockGridViewport() {
                const $inner = $grid.find('.row.g-3');
                if (!$inner.length) return;

                // Tetapkan tinggi tetap + scroll ketika konten melebihi
                const FIXED_HEIGHT = '82vh';
                $inner.css({
                    maxHeight: FIXED_HEIGHT,
                    overflowY: 'auto'
                });
            }

            function render(products, activeTitle) {
                if (!products.length) {
                    $grid.html('<div class="row g-3"><div class="col-12"><div class="text-center text-muted py-5">Tidak ada produk.</div></div></div>');
                    return;
                }

                 // Notifikasi bila mencapai batas 100
                const notice = (products.length >= LIMIT)
                ? '<div id="productLimitNotice" class="alert alert-warning py-2 px-3 mb-2">Menampilkan 100 produk pertama. Gunakan pencarian untuk mempersempit hasil.</div>'
                : '';
                
                let html = '<div class="row g-3">';
                products.forEach(p => html += card(p, false));
                html += '</div>';

                $grid.html(notice + html);

                lockGridViewport();
                $grid.find('img').each(function() {
                    this.complete || $(this).one('load error', () => lockGridViewport());
                });
                setTimeout(lockGridViewport, 50);
                setTimeout(lockGridViewport, 200);
            }

            const activeCategory = () => {
                const $btn = $cats.find('.btn-primary');
                return {
                    id: $btn.data('id') || '',
                    title: $btn.data('category') || 'All'
                };
            };

            function fetchAndRender() {
                const {
                    id,
                    title
                } = activeCategory();
                $.getJSON(ENDPOINT, {
                        main_category_id: id,
                        main_category: title,
                        q: $search.val() || ''
                    })
                    .done(res => render((res && res.products) || [], title))
                    .fail(() => $grid.html('<div class="text-center text-muted py-5">Gagal memuat produk.</div>'));
            }

            // kategori: toggle filter
            $cats.on('click', 'button[data-category]', function() {
                $cats.find('button[data-category]').removeClass('btn-primary').addClass('btn-outline-primary').attr('aria-pressed', false);
                $(this).addClass('btn-primary').removeClass('btn-outline-primary').attr('aria-pressed', true);
                fetchAndRender();
            });
            
            // search: debounce
            let timer;
            $search.on('input', () => {
                clearTimeout(timer);
                timer = setTimeout(fetchAndRender, 200);
            });

            $(window).on('resize', lockGridViewport);

            fetchAndRender();
        });
    </script>

    <!-- Produk Modal Add To Cart -->
    <script>
        $(() => {
            const rp = n => 'Rp ' + Number(n || 0).toLocaleString('id-ID');

            $('#productModal').on('show.bs.modal', e => {
                const d = $(e.relatedTarget).data();

                $('#pmTitle').text(d.title || '');
                $('#pmCategory').text(d.category || '—');
                $('#pmPrice').text(rp(d.price));
                $('#pmImg').attr({
                    src: d.img || '',
                    alt: d.title || ''
                });
            });
        });

        // Efek hover card produk
        const $productGrid = $('#productGrid');

        $productGrid.on('mouseenter focusin', '.card', function () {
            $(this)
                .removeClass('shadow')
                .addClass('shadow-lg border border-2 border-dark-subtle');
        });

        $productGrid.on('mouseleave focusout', '.card', function () {
            $(this)
                .removeClass('shadow-lg border border-2 border-dark-subtle')
                .addClass('shadow');
        });

        // Fitur Input Barcode / Scanner
        function playScanSound(type) {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                const ctx = new AudioContext();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                
                osc.connect(gain);
                gain.connect(ctx.destination);
                
                if (type === 'success') {
                    // High short beep (sukses)
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(1000, ctx.currentTime);
                    gain.gain.setValueAtTime(0.2, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.1);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.1);
                } else if (type === 'error') {
                    // Low double buzz (error/tetot)
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(200, ctx.currentTime);
                    gain.gain.setValueAtTime(0.2, ctx.currentTime);
                    
                    // Beep 1
                    gain.gain.setValueAtTime(0.2, ctx.currentTime);
                    gain.gain.linearRampToValueAtTime(0.01, ctx.currentTime + 0.15);
                    // Beep 2
                    gain.gain.setValueAtTime(0.2, ctx.currentTime + 0.2);
                    gain.gain.linearRampToValueAtTime(0.01, ctx.currentTime + 0.35);
                    
                    osc.start();
                    osc.stop(ctx.currentTime + 0.4);
                }
            } catch (e) {
                console.error("Audio not supported");
            }
        }

        $(document).ready(function() {
            let statusKlik = 1;

            // Deteksi input dari hardware scanner atau ketikan manual + Enter
            $("#barcodeScan").on("keypress", function(e) {
                if (e.which === 13) { // 13 is Enter key
                    e.preventDefault();
                    if ($(this).val().trim() !== "") {
                        $("#barcodeScanAdded").trigger("click");
                    }
                }
            });

            // Aksi tambah barang via SKU
            $(document).on('click', '#barcodeScanAdded', function() {
                if (statusKlik == 1) {
                    statusKlik = 0;
                    
                    let sku = $("#barcodeScan").val().trim();
                    if (!sku) {
                        statusKlik = 1;
                        return;
                    }

                    $.ajax({
                        url: "{{ route('sales-transaction.cart.add-by-sku') }}",
                        type: "POST",
                        data: {
                            _token: '{{ csrf_token() }}',
                            sku: sku
                        },
                        cache: false,
                        dataType: 'json',
                        success: function(res) {
                            // Update UI cart
                            if (typeof window.refreshCartList === 'function') {
                                window.refreshCartList();
                            }
                            // Bunyi sukses
                            playScanSound('success');
                            
                            // Reset input
                            $("#barcodeScan").val('').focus();
                            statusKlik = 1;
                        },
                        error: function(xhr) {
                            // Bunyi error
                            playScanSound('error');

                            let msg = "Gagal menambah barang";
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            alert(msg);
                            $("#barcodeScan").val('').focus();
                            statusKlik = 1;
                        }
                    });
                }
            });
        });

    </script>
    
    <!-- Script from Product Modal / Child View -->
    @stack('scripts-modal')

    <!-- Script from Cart List / Child View -->
    @stack('scripts-cart-list')

    <!-- Script from Payment Modal / Child View -->
    @stack('scripts-payment-modal')

@endsection
