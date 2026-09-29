@extends('layouts.master')
@section('title')
    Create {{ $titleHeader->name }}
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Systems
        @endslot
        @slot('title')
            Create {{ $titleHeader->name }}
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Buat Seasonal Baru</h6>
                </div>
                <div class="card-body">
                    @include('backend.includes.flash')
                    <form id="form_create" method="POST" action="{{ route('seasonal.store') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label">Nama Seasonal *</label>
                            <input type="text" class="form-control" name="name" placeholder="e.g. Christmas 2026, Idul Adha 2026" required value="{{ old('name') }}">
                            <div class="invalid-feedback">Nama seasonal wajib diisi.</div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">URL Slug Kustom (Opsional)</label>
                            <input type="text" class="form-control" name="slug" placeholder="e.g. promo-lebaran-2026 (kosongkan untuk otomatis)" value="{{ old('slug') }}">
                            <small class="text-muted">Jika dikosongkan, slug akan digenerate otomatis dari Nama Seasonal.</small>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Deskripsi singkat seasonal ini...">{{ old('description') }}</textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Deskripsi Hover (Singkat)</label>
                            <textarea class="form-control" name="hover_description" rows="2" placeholder="Deskripsi singkat untuk menu hover...">{{ old('hover_description') }}</textarea>
                            <small class="text-muted">Muncul saat menu seasonal di-hover di frontend.</small>
                        </div>
                        <h6 class="mt-4 border-bottom pb-2">Pengaturan Tampilan Koleksi Seasonal</h6>
                        <div class="form-group mb-3">
                            <label class="form-label">Judul Koleksi (Collection Title)</label>
                            <input type="text" class="form-control" name="collection_title" placeholder="e.g. Seasonal Collection" value="{{ old('collection_title') }}">
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Subjudul Koleksi (Collection Subtitle)</label>
                            <textarea class="form-control" name="collection_subtitle" rows="2" placeholder="e.g. Timeless pieces, thoughtfully chosen to inspire your space.">{{ old('collection_subtitle') }}</textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-lg-6">
                                <label class="form-label">Teks Tombol Banner</label>
                                <input type="text" class="form-control" name="button_text" placeholder="e.g. Shop Now" value="{{ old('button_text') }}">
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label">Link Tombol Banner</label>
                                <input type="text" class="form-control" name="button_link" placeholder="e.g. #collection atau URL spesifik" value="{{ old('button_link') }}">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-lg-6">
                                <label class="form-label">Tanggal Mulai *</label>
                                <input type="date" class="form-control" name="start_date" required value="{{ old('start_date') }}">
                                <div class="invalid-feedback">Tanggal mulai wajib diisi.</div>
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label">Tanggal Berakhir *</label>
                                <input type="date" class="form-control" name="end_date" required value="{{ old('end_date') }}">
                                <div class="invalid-feedback">Tanggal berakhir wajib diisi.</div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Banner Image</label>
                            <input type="file" class="form-control" name="banner_image" accept="image/*">
                            <small class="text-muted">Format: jpeg, png, jpg, webp. Max 5MB.</small>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Hover Image</label>
                            <input type="file" class="form-control" name="hover_image" accept="image/*">
                            <small class="text-muted">Muncul di kotak menu dropdown saat di-hover. Format: jpeg, png, jpg, webp. Max 5MB.</small>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Status *</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_active" id="active" value="1" checked>
                                    <label class="form-check-label" for="active">Aktif</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_active" id="inactive" value="0">
                                    <label class="form-check-label" for="inactive">Tidak Aktif</label>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('seasonal.index') }}" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="las la-save"></i> Simpan</button>
                        </div>
                    </form>
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
                        <div id="preview-hero" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-size: cover; background-position: center; z-index: 1; background: linear-gradient(135deg, #2a2a2a 0%, #4a4a4a 100%);">
                        </div>
                        
                        <!-- Overlay -->
                        <div style="position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.8) 100%); z-index: 2;"></div>
                        
                        <!-- Content -->
                        <div style="position: relative; z-index: 3; text-align: center; color: white; padding: 1rem; width: 100%;">
                            <h2 id="preview-title" style="font-size: 1.5rem; font-weight: 800; text-shadow: 0 2px 5px rgba(0,0,0,0.5); margin-bottom: 0.25rem; color: #fff;">Nama Seasonal</h2>
                            <p id="preview-subtitle" style="font-size: 0.85rem; opacity: 0.9; text-shadow: 0 1px 3px rgba(0,0,0,0.5); font-style: italic;">Deskripsi event...</p>
                            
                            <div id="preview-btn-container" style="display: none; margin-top: 1rem;">
                                <button id="preview-btn" style="padding: 4px 12px; border-radius: 2px; background: rgba(255,255,255,0.15); backdrop-filter: blur(8px); color: white; border: 1px solid white; display: inline-flex; align-items: center; font-size: 0.75rem;">
                                    <span id="preview-btn-text">Shop Now</span>
                                </button>
                            </div>
                        </div>
                        
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 6px; background: rgba(72, 43, 27, 0.7); z-index: 3; text-align: center;">
                            <span style="color: white; font-size: 10px;">Ready to bring something home? <u>Shop Now</u></span>
                        </div>
                    </div>
                    
                    <!-- Preview Collection Text Area -->
                    <div style="padding: 1.5rem 1rem; text-align: center; background-color: #fcfcfc;">
                        <h2 id="preview-collection-title" style="font-size: 1.25rem; font-weight: 600; color: #1a1a1a; margin-bottom: 0.5rem;">Seasonal Collection</h2>
                        <p id="preview-collection-subtitle" style="font-size: 0.85rem; color: #666; max-width: 100%; margin: 0 auto;">Timeless pieces, thoughtfully chosen to inspire your space.</p>
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
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js') }}"></script>
    <script>
        // Live Preview Handlers
        $('input[name="name"]').on('input', function() {
            $('#preview-title').text($(this).val() || 'Nama Seasonal');
        });
        
        $('textarea[name="description"]').on('input', function() {
            $('#preview-subtitle').text($(this).val() || 'Deskripsi event...');
        });
        
        $('input[name="collection_title"]').on('input', function() {
            $('#preview-collection-title').text($(this).val() || 'Seasonal Collection');
        });

        $('textarea[name="collection_subtitle"]').on('input', function() {
            let val = $(this).val();
            let hoverVal = $('textarea[name="hover_description"]').val();
            $('#preview-collection-subtitle').text(val || hoverVal || 'Timeless pieces, thoughtfully chosen to inspire your space.');
        });
        
        $('textarea[name="hover_description"]').on('input', function() {
            let subtitleVal = $('textarea[name="collection_subtitle"]').val();
            if (!subtitleVal) {
                $('#preview-collection-subtitle').text($(this).val() || 'Timeless pieces, thoughtfully chosen to inspire your space.');
            }
        });

        $('input[name="button_text"]').on('input', function() {
            let val = $(this).val();
            if(val) {
                $('#preview-btn-text').text(val);
                $('#preview-btn-container').show();
            } else {
                $('#preview-btn-container').hide();
            }
        });
        
        $('input[name="banner_image"]').on('change', function(e) {
            if(e.target.files && e.target.files[0]) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview-hero').css('background-image', 'url(' + e.target.result + ')');
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
@endsection
