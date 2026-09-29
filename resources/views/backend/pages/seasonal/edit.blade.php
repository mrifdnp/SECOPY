@extends('layouts.master')

@section('title')
    Edit {{ $titleHeader->name }}
@endsection

@section('cssAdded')
    <style>
        .product-search-result {
            cursor: pointer;
            transition: background-color 0.15s;
        }
        .product-search-result:hover {
            background-color: #f3f6f9;
        }
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

    {{-- Seasonal Information --}}
    <div class="row">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">{{ $seasonal->name }}</h6>
                </div>
                <div class="card-body">
                    <div id="alert-container"></div>
                    <div class="mb-3">
                        <label for="seasonal-name" class="form-label">Nama Seasonal *</label>
                        <input type="text" class="form-control" id="seasonal-name" value="{{ $seasonal->name }}">
                        <div class="invalid-feedback">Nama seasonal wajib diisi.</div>
                    </div>
                    <div class="mb-3">
                        <label for="seasonal-slug" class="form-label">URL Slug Kustom (Opsional)</label>
                        <input type="text" class="form-control" id="seasonal-slug" value="{{ $seasonal->slug }}" placeholder="e.g. promo-lebaran-2026">
                        <small class="text-muted">Jika dikosongkan, slug akan digenerate otomatis dari Nama Seasonal.</small>
                    </div>
                    <div class="mb-3">
                        <label for="seasonal-description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="seasonal-description" rows="3">{{ $seasonal->description }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="seasonal-hover-description" class="form-label">Deskripsi Hover (Singkat)</label>
                        <textarea class="form-control" id="seasonal-hover-description" rows="2">{{ $seasonal->hover_description }}</textarea>
                        <small class="text-muted">Muncul saat menu seasonal di-hover di frontend.</small>
                    </div>
                    <h6 class="mt-4 border-bottom pb-2">Pengaturan Tampilan Koleksi Seasonal</h6>
                    <div class="mb-3">
                        <label for="seasonal-collection-title" class="form-label">Judul Koleksi (Collection Title)</label>
                        <input type="text" class="form-control" id="seasonal-collection-title" value="{{ $seasonal->collection_title }}" placeholder="e.g. Seasonal Collection">
                    </div>
                    <div class="mb-3">
                        <label for="seasonal-collection-subtitle" class="form-label">Subjudul Koleksi (Collection Subtitle)</label>
                        <textarea class="form-control" id="seasonal-collection-subtitle" rows="2" placeholder="e.g. Timeless pieces...">{{ $seasonal->collection_subtitle }}</textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-6">
                            <label for="seasonal-button-text" class="form-label">Teks Tombol Banner</label>
                            <input type="text" class="form-control" id="seasonal-button-text" value="{{ $seasonal->button_text }}" placeholder="e.g. Shop Now">
                        </div>
                        <div class="col-lg-6">
                            <label for="seasonal-button-link" class="form-label">Link Tombol Banner</label>
                            <input type="text" class="form-control" id="seasonal-button-link" value="{{ $seasonal->button_link }}" placeholder="e.g. #collection">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-6">
                            <label for="start-date" class="form-label">Tanggal Mulai *</label>
                            <input type="date" class="form-control" id="start-date" value="{{ $seasonal->start_date->format('Y-m-d') }}">
                            <div class="invalid-feedback">Tanggal mulai wajib diisi.</div>
                        </div>
                        <div class="col-lg-6">
                            <label for="end-date" class="form-label">Tanggal Berakhir *</label>
                            <input type="date" class="form-control" id="end-date" value="{{ $seasonal->end_date->format('Y-m-d') }}">
                            <div class="invalid-feedback">Tanggal berakhir wajib diisi.</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Banner Image</label>
                        @if($seasonal->banner_image)
                            <div class="mb-2">
                                <img src="{{ asset('storage/seasonal/' . $seasonal->banner_image) }}" alt="Banner" style="max-height: 150px; border-radius: 8px;">
                            </div>
                        @endif
                        <input type="file" class="form-control" id="banner-image" accept="image/*">
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah banner.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hover Image</label>
                        @if($seasonal->hover_image)
                            <div class="mb-2">
                                <img src="{{ asset('storage/seasonal/' . $seasonal->hover_image) }}" alt="Hover" style="max-height: 150px; border-radius: 8px;">
                            </div>
                        @endif
                        <input type="file" class="form-control" id="hover-image" accept="image/*">
                        <small class="text-muted">Muncul di menu dropdown. Kosongkan jika tidak ingin mengubah gambar hover.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_active" id="active" value="1" {{ $seasonal->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="active">Aktif</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_active" id="inactive" value="0" {{ !$seasonal->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="inactive">Tidak Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" id="btn-save-seasonal" class="btn btn-primary"><i class="las la-save"></i> Simpan Perubahan</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-5">
            <div class="card sticky-top" style="top: 80px; z-index: 10;">
                <div class="card-header">
                    <h6 class="card-title mb-0">Live Preview Banner & Collection</h6>
                </div>
                <div class="card-body p-0" style="background: #fff; overflow-y: auto; max-height: calc(100vh - 120px); border-radius: 0 0 0.25rem 0.25rem;">
                    
                    <!-- Preview Banner Area -->
                    <div style="position: relative; height: 250px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div id="preview-hero" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-size: cover; background-position: center; z-index: 1; @if($seasonal->banner_image) background-image: url('{{ asset('storage/seasonal/' . $seasonal->banner_image) }}'); @else background: linear-gradient(135deg, #2a2a2a 0%, #4a4a4a 100%); @endif">
                        </div>
                        
                        <!-- Overlay -->
                        <div style="position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.8) 100%); z-index: 2;"></div>
                        
                        <!-- Content -->
                        <div style="position: relative; z-index: 3; text-align: center; color: white; padding: 1rem; width: 100%;">
                            <h2 id="preview-title" style="font-size: 1.5rem; font-weight: 800; text-shadow: 0 2px 5px rgba(0,0,0,0.5); margin-bottom: 0.25rem; color: #fff;">{{ $seasonal->name ?: 'Nama Seasonal' }}</h2>
                            <p id="preview-subtitle" style="font-size: 0.85rem; opacity: 0.9; text-shadow: 0 1px 3px rgba(0,0,0,0.5); font-style: italic;">{{ $seasonal->description ?: 'Deskripsi event...' }}</p>
                            
                            <div id="preview-btn-container" style="{{ $seasonal->button_text ? '' : 'display: none;' }} margin-top: 1rem;">
                                <button id="preview-btn" style="padding: 4px 12px; border-radius: 2px; background: rgba(255,255,255,0.15); backdrop-filter: blur(8px); color: white; border: 1px solid white; display: inline-flex; align-items: center; font-size: 0.75rem;">
                                    <span id="preview-btn-text">{{ $seasonal->button_text }}</span>
                                </button>
                            </div>
                        </div>
                        
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 6px; background: rgba(72, 43, 27, 0.7); z-index: 3; text-align: center;">
                            <span style="color: white; font-size: 10px;">Ready to bring something home? <u>Shop Now</u></span>
                        </div>
                    </div>
                    
                    <!-- Preview Collection Text Area -->
                    <div style="padding: 1.5rem 1rem; text-align: center; background-color: #fcfcfc;">
                        <h2 id="preview-collection-title" style="font-size: 1.25rem; font-weight: 600; color: #1a1a1a; margin-bottom: 0.5rem;">{{ $seasonal->collection_title ?: 'Seasonal Collection' }}</h2>
                        <p id="preview-collection-subtitle" style="font-size: 0.85rem; color: #666; max-width: 100%; margin: 0 auto;">{{ $seasonal->collection_subtitle ?: ($seasonal->hover_description ?: 'Timeless pieces, thoughtfully chosen to inspire your space.') }}</p>
                    </div>

                    <!-- Preview Product Mockups -->
                    <div style="padding: 0 1.5rem 2rem 1.5rem; background-color: #fcfcfc;">
                        <div class="row g-2">
                            @for ($i = 0; $i < 4; $i++)
                            <div class="col-6">
                                <div style="border: 1px solid #dfd1b8; background: #fff; height: 100%; display: flex; flex-direction: column;">
                                    <div style="background: #f2f2f2; width: 100%; aspect-ratio: 1; position: relative;">
                                        <svg style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #d4d4d4;" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    </div>
                                    <div style="padding: 10px; display: flex; justify-content: space-between; align-items: flex-start; text-align: left;">
                                        <div>
                                            <div style="font-weight: 700; color: #000; font-size: 0.85rem; line-height: 1.2; margin-bottom: 4px;">Produk Contoh</div>
                                            <div style="font-weight: 700; color: #b48a58; font-size: 0.75rem;">IDR 135.000</div>
                                        </div>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 2px;"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                    </div>
                                </div>
                            </div>
                            @endfor
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Produk yang di-assign --}}
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">Daftar Produk Seasonal</h6>
            <span class="badge bg-primary" id="product-count">{{ count($assigned_products) }} Produk</span>
        </div>
        <div class="card-body">
            {{-- Search produk --}}
            <div class="row mb-3">
                <div class="col-lg-8">
                    <div class="input-group">
                        <input type="text" class="form-control" id="search-product" placeholder="Cari produk untuk ditambahkan...">
                        <button class="btn btn-outline-primary" type="button" id="btn-search"><i class="las la-search"></i> Cari</button>
                    </div>
                </div>
            </div>

            {{-- Hasil pencarian --}}
            <div id="search-results" class="mb-3" style="display: none;">
                <div class="card border">
                    <div class="card-header bg-light py-2">
                        <small class="fw-semibold">Hasil Pencarian</small>
                        <button type="button" class="btn-close float-end btn-sm" id="close-search"></button>
                    </div>
                    <div class="card-body p-0" id="search-results-body" style="max-height: 300px; overflow-y: auto;">
                    </div>
                </div>
            </div>

            {{-- Tabel produk yang sudah di-assign --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0" id="assigned-products-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th style="width: 70px;">Gambar</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th style="width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assigned_products as $index => $product)
                            <tr id="product-row-{{ $product->id }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if($product->main_pic)
                                        <img src="{{ $product->main_pic }}" alt="" class="avatar">
                                    @else
                                        <img src="https://placehold.co/58x58?text={{ strtoupper(substr($product->title, 0, 2)) }}" class="avatar">
                                    @endif
                                </td>
                                <td>{{ $product->title }}</td>
                                <td>{{ $product->categories->title ?? '-' }}</td>
                                <td>
                                    <button class="btn btn-sm btn-soft-danger btn-remove-product" data-product-id="{{ $product->id }}">
                                        <i class="las la-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr id="empty-row">
                                <td colspan="5" class="text-center text-muted py-4">
                                    Belum ada produk yang ditambahkan. Gunakan pencarian di atas untuk menambahkan produk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-start mb-3">
        <a href="{{ route('seasonal.index') }}" class="btn btn-light"><i class="las la-arrow-left"></i> Kembali</a>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0">
                <div class="modal-header p-3 bg-primary bg-gradient">
                    <h5 class="modal-title text-white"><i class="las la-info-circle"> </i> Delete Confirmation</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>        
                </div>
                <div class="modal-body">
                    <div class="box-body index-padding">
                        <table class="table table-striped mb-4">
                            <thead>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Item</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td id="deleteItemName"></td>
                                </tr>
                            </tbody>
                        </table>
                        <span>Are you sure you want to delete 1 record(s) above?</span>
                    </div>
                </div>
                <div class="modal-footer">        
                    <button type="button" class="btn btn-outline btn-light pull-left" data-bs-dismiss="modal"><i class="las la-times-circle"></i> Close</button>
                    <button type="button" class="btn btn-danger pull-left text-light" id="confirmDeleteBtn"><i class="las la-trash"></i> Delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js') }}"></script>
    <script>
        function showAlert(type, message) {
            $('#alert-container').html(`
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `);
        }

        // Live Preview Handlers
        $('#seasonal-name').on('input', function() {
            $('#preview-title').text($(this).val() || 'Nama Seasonal');
        });
        
        $('#seasonal-description').on('input', function() {
            $('#preview-subtitle').text($(this).val() || 'Deskripsi event...');
        });
        
        $('#seasonal-collection-title').on('input', function() {
            $('#preview-collection-title').text($(this).val() || 'Seasonal Collection');
        });

        $('#seasonal-collection-subtitle').on('input', function() {
            let val = $(this).val();
            let hoverVal = $('#seasonal-hover-description').val();
            $('#preview-collection-subtitle').text(val || hoverVal || 'Timeless pieces, thoughtfully chosen to inspire your space.');
        });
        
        $('#seasonal-hover-description').on('input', function() {
            let subtitleVal = $('#seasonal-collection-subtitle').val();
            if (!subtitleVal) {
                $('#preview-collection-subtitle').text($(this).val() || 'Timeless pieces, thoughtfully chosen to inspire your space.');
            }
        });

        $('#seasonal-button-text').on('input', function() {
            let val = $(this).val();
            if(val) {
                $('#preview-btn-text').text(val);
                $('#preview-btn-container').show();
            } else {
                $('#preview-btn-container').hide();
            }
        });
        
        $('#banner-image').on('change', function(e) {
            if(e.target.files && e.target.files[0]) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview-hero').css('background-image', 'url(' + e.target.result + ')');
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // Simpan perubahan seasonal
        $('#btn-save-seasonal').on('click', function() {
            let formData = new FormData();
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            formData.append('_method', 'PUT');
            formData.append('name', $('#seasonal-name').val());
            formData.append('slug', $('#seasonal-slug').val());
            formData.append('description', $('#seasonal-description').val());
            formData.append('hover_description', $('#seasonal-hover-description').val());
            formData.append('collection_title', $('#seasonal-collection-title').val());
            formData.append('collection_subtitle', $('#seasonal-collection-subtitle').val());
            formData.append('button_text', $('#seasonal-button-text').val());
            formData.append('button_link', $('#seasonal-button-link').val());
            formData.append('start_date', $('#start-date').val());
            formData.append('end_date', $('#end-date').val());
            formData.append('is_active', $('input[name="is_active"]:checked').val());

            let bannerFile = $('#banner-image')[0].files[0];
            if (bannerFile) {
                formData.append('banner_image', bannerFile);
            }

            let hoverFile = $('#hover-image')[0].files[0];
            if (hoverFile) {
                formData.append('hover_image', hoverFile);
            }

            $.ajax({
                url: '/seasonal/{{ $seasonal->id }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        Toastify({
                            text: "Data berhasil disimpan!",
                            duration: 3000,
                            close: true,
                            gravity: "bottom",
                            position: "right",
                            className: "bg-success",
                        }).showToast();
                        showAlert('success', 'Data berhasil diperbarui!');
                    } else {
                        showAlert('danger', response.message || 'Gagal menyimpan data.');
                    }
                },
                error: function() {
                    showAlert('danger', 'Gagal menyimpan data.');
                }
            });
        });

        // Cari produk
        function searchProduct() {
            let keyword = $('#search-product').val();
            $.ajax({
                url: '{{ route("seasonal.search_product") }}',
                type: 'GET',
                data: {
                    keyword: keyword,
                    seasonal_id: '{{ $seasonal->id }}'
                },
                success: function(response) {
                    let html = '';
                    if (response.data.length === 0) {
                        html = '<div class="p-3 text-center text-muted">Tidak ada produk ditemukan.</div>';
                    } else {
                        response.data.forEach(function(product) {
                            let img = product.main_pic
                                ? `<img src="${product.main_pic}" style="width:40px;height:40px;object-fit:cover;border-radius:4px;">`
                                : `<img src="https://placehold.co/40x40?text=${product.title.substring(0,2).toUpperCase()}" style="border-radius:4px;">`;
                            html += `
                                <div class="product-search-result d-flex align-items-center gap-3 px-3 py-2 border-bottom" data-product-id="${product.id}" data-product-title="${product.title}">
                                    ${img}
                                    <div>
                                        <div class="fw-semibold">${product.title}</div>
                                    </div>
                                    <button class="btn btn-sm btn-soft-success ms-auto btn-add-product" data-product-id="${product.id}">
                                        <i class="las la-plus"></i> Tambah
                                    </button>
                                </div>
                            `;
                        });
                    }
                    $('#search-results-body').html(html);
                    $('#search-results').show();
                }
            });
        }

        $('#btn-search').on('click', searchProduct);
        $('#search-product').on('keypress', function(e) {
            if (e.which === 13) searchProduct();
        });

        $('#close-search').on('click', function() {
            $('#search-results').hide();
        });

        // Tambah produk
        $(document).on('click', '.btn-add-product', function(e) {
            e.preventDefault();
            e.stopPropagation();
            let productId = $(this).data('product-id');
            let btn = $(this);

            $.ajax({
                url: '{{ route("seasonal.add_product") }}',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    seasonal_id: '{{ $seasonal->id }}',
                    product_id: productId
                },
                success: function(response) {
                    if (response.status === 'success') {
                        Toastify({
                            text: "Produk berhasil ditambahkan!",
                            duration: 2000,
                            close: true,
                            gravity: "bottom",
                            position: "right",
                            className: "bg-success",
                        }).showToast();

                        btn.closest('.product-search-result').fadeOut();
                        // Reload halaman untuk update tabel
                        setTimeout(function() { location.reload(); }, 500);
                    }
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || 'Gagal menambahkan produk.';
                    Toastify({
                        text: msg,
                        duration: 3000,
                        close: true,
                        gravity: "bottom",
                        position: "right",
                        className: "bg-danger",
                    }).showToast();
                }
            });
        });

        // Hapus produk dari seasonal
        let productToDeleteId = null;
        let rowToDelete = null;

        $(document).on('click', '.btn-remove-product', function(e) {
            e.preventDefault();
            productToDeleteId = $(this).data('product-id');
            rowToDelete = $(this).closest('tr');
            
            // Dapatkan nama produk untuk ditampilkan di modal
            let productName = rowToDelete.find('td:nth-child(3)').text().trim();
            $('#deleteItemName').text(productName);
            
            // Tampilkan modal konfirmasi
            $('#deleteConfirmationModal').modal('show');
        });

        $('#confirmDeleteBtn').on('click', function() {
            if (!productToDeleteId) return;
            
            let btn = $(this);
            let originalText = btn.html();
            btn.prop('disabled', true).html('<i class="las la-spinner la-spin"></i> Deleting...');

            $.ajax({
                url: '{{ route("seasonal.remove_product") }}',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    seasonal_id: '{{ $seasonal->id }}',
                    product_id: productToDeleteId
                },
                success: function(response) {
                    btn.prop('disabled', false).html(originalText);
                    $('#deleteConfirmationModal').modal('hide');
                    
                    if (response.status === 'success') {
                        rowToDelete.fadeOut(300, function() {
                            $(this).remove();
                            // Update count
                            let count = $('#assigned-products-table tbody tr:visible').length;
                            $('#product-count').text(count + ' Produk');
                            if (count === 0) {
                                $('#assigned-products-table tbody').html('<tr id="empty-row"><td colspan="5" class="text-center text-muted py-4">Belum ada produk yang ditambahkan.</td></tr>');
                            }
                        });
                        Toastify({
                            text: "Produk berhasil dihapus!",
                            duration: 2000,
                            close: true,
                            gravity: "bottom",
                            position: "right",
                            className: "bg-warning",
                        }).showToast();
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html(originalText);
                    $('#deleteConfirmationModal').modal('hide');
                    
                    Toastify({
                        text: "Gagal menghapus produk.",
                        duration: 3000,
                        close: true,
                        gravity: "bottom",
                        position: "right",
                        className: "bg-danger",
                    }).showToast();
                }
            });
        });
    </script>
@endsection
