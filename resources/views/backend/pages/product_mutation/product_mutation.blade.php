@extends('layouts.master')

@section('title')
    {{ $titleHeader->name }}
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Systems
        @endslot
        @slot('title')
            {{ $titleHeader->name }}
        @endslot
    @endcomponent

    <div class="row align-items-stretch mb-3">
        {{-- Product list column --}}
        <div class="col-lg-8">
            {{-- Title section --}}
            <div class="card mb-3">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Daftar Produk</h4>
                    </div>
                    <div class="d-flex align-items-center">
                        <!-- Search Produk -->
                        <div class="input-group me-2">
                            <input type="text" class="form-control border-end-0 pe-0" id="searchEntitasList" placeholder="Cari Produk">
                            <span class="input-group-text bg-transparent border-start-0">
                                <i class="ri-search-line"></i>
                            </span>
                        </div>

                        <!-- Scan Barcode -->
                        <button id="scanBarcodeBtn" type="button" class="btn btn-primary d-flex align-items-center">
                            <i class="mdi mdi-barcode-scan me-0 me-sm-2"></i>
                            <span class="fw-medium text-nowrap d-none d-sm-inline d-md-none">Scan</span>
                            <span class="fw-medium text-nowrap d-none d-md-inline">Scan Barcode</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Filter by main categories --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div id="categoryList" class="d-flex align-items-center gap-2 overflow-auto flex-nowrap py-1" role="group" aria-label="Category filter">

                        <button type="button" class="btn btn-primary d-flex align-items-center" data-category="All" aria-pressed="true">
                            <span class="fw-medium text-nowrap">All</span>
                        </button>

                        @foreach ($mainCategories as $mcId => $mcTitle)
                            <button type="button" class="btn btn-outline-primary d-flex align-items-center" data-id="{{ $mcId }}" data-category="{{ $mcTitle }}" aria-pressed="false">
                                <span class="fw-medium text-nowrap">{{ $mcTitle }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Product list --}}
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

                        <div class="col-6 col-md-4 col-lg-3 {{ $hidden ? 'd-none extra-product' : '' }}" data-title="{{ strtolower($p->product_title) }}" data-category="{{ strtolower($p->category_title ?? '') }}" data-top-pick="{{ (int) ($p->top_pick ?? 0) }}">
                            <div class="card h-100 overflow-hidden cursor-pointer shadow" data-id="{{ $p->id }}" data-title="{{ $p->product_title }}" data-category="{{ $p->category_title ?? '' }}" data-price="{{ (int) ($p->price ?? 0) }}" data-img="{{ $p->main_pic ?? $noImage }}" data-bs-toggle="modal" data-bs-target="#productModal" tabindex="0">
                                <div class="p-2">
                                    <div class="ratio ratio-4x3 bg-light overflow-hidden">
                                        <img src="{{ $p->main_pic ?? $noImage }}" alt="{{ $p->product_title }}" class="w-100 h-100" style="object-fit: cover; object-position: center;" loading="lazy">
                                    </div>
                                </div>
                                <div class="card-body overflow-hidden p-2 d-flex flex-column justify-content-between">
                                    <div>
                                        <h6 class="card-title mb-1 fw-semibold text-truncate" title="{{ $p->product_title }}">
                                            {{ $p->product_title }}
                                        </h6>

                                        <div class="small text-muted text-truncate" title="{{ $p->category_title }}">
                                            {{ $p->category_title ?? '—' }}
                                        </div>
                                    </div>
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

        {{-- Product mutation list column --}}
        <div class="col-lg-4">
            @include('backend.pages.product_mutation._product_mutation_list')
        </div>
    </div>

    {{-- Modal add to list --}}
    @include('backend.pages.product_mutation._product_mutation_add_to_list_modal')

    {{-- Proses mutasi modal --}}
    @include('backend.pages.product_mutation._product_mutation_process_modal')

    {{-- Modal pilih mutasi --}}
    @include('backend.pages.product_mutation._product_mutation_incoming_modal')
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

    <!-- Fungsi Pencarian & Filter Kategori Produk -->
    <script>
        $(() => {
            const ENDPOINT = "{{ route('product-mutation.list.filter') }}";
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
                const notice = (products.length >= LIMIT) ?
                    '<div id="productLimitNotice" class="alert alert-warning py-2 px-3 mb-2">Menampilkan 100 produk pertama. Gunakan pencarian untuk mempersempit hasil.</div>' :
                    '';

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

    {{-- Script untuk bagian daftar produk yang dipilih untuk di mutasi --}}
    @stack('script-product-mutation-list')

    {{-- Script untuk bagian modal --}}
    @stack('script-modal-add-to-list')

    {{-- Script untuk bagian modal proses mutasi --}}
    @stack('script-mutation-process-modal')

    {{-- Script untuk modal produk yang akan masuk --}}
    @stack('script-mutation-incoming-product-modal')
@endsection
