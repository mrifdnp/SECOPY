@extends('layouts.master')

@section('title')
    Detail {{ $titleHeader->name }}
@endsection

@section('cssAdded')
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

    <div class="d-flex mb-3 align-items-center justify-content-between">
        <h4 class="mb-0">{{ $product->title }}</h4>
        <div class="btn-group" role="group">
            <a href="{{ route('product.basicInformation', ['id' => $product->id]) }}" class="btn btn-primary material-shadow-none">Informasi Dasar</a>
            <a href="{{ route('product.priceAndStock', ['id' => $product->id]) }}" class="btn btn-primary material-shadow-none">Stok dan Harga</a>
            <a href="#" class="btn btn-primary material-shadow-none active">Detail Produk</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h6 class="card-title mb-0">Deskripsi</h6>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <!-- List ukuran -->
                <ul class="nav nav-tabs border-0" id="variant-tabs">
                    @foreach ($product->variants as $index => $variant)
                        <li class="nav-item">
                            <a class="nav-link @if ($index == 0) active @endif px-3" data-bs-toggle="pill" data-variant-id="{{ $variant->id }}" href="#tab-content-{{ $variant->id }}">
                                {{ $variant->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <!-- Radio button -->
                <div class="form-check form-switch">
                    <label class="form-check-label" for="same-content">Samakan konten untuk semua varian</label>
                    <input class="form-check-input" type="checkbox" id="same-content">
                </div>
            </div>

            <div class="tab-content mb-3">
                @foreach ($product->variants as $index => $variant)
                    <div class="tab-pane fade @if ($index == 0) show active @endif" id="tab-content-{{ $variant->id }}">
                        <div class="variant-content"></div>
                        <button class="btn btn-primary btn-add-section">Tambah Bagian</button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button class="btn btn-primary mb-3" id="submit-button">Simpan</button>
    </div>

    {{-- <div id="toast-container" style=" position: fixed; top: 1rem; right: 1rem; z-index: 1080; display: flex; flex-direction: column; gap: 0.5rem;">
    </div> --}}
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-bootstrap-theme-0.1.0-beta.10/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js') }}"></script>
    <script src="{{ asset('build/libs/@ckeditor/ckeditor5-build-classic/build/ckeditor.js') }}"></script>
    <script src="{{ asset('build/js/pages/toast.init.js') }}"></script>

    <script>
        const variationRaw = @json($product->variants ?? []);
        let state = {
            activeTabIdToCopyContent: '',
            variationOptions: variationRaw.map(v => {
                return {
                    variationId: v.id,
                    sections: v.json_description || [{
                        title: '',
                        description: ''
                    }]
                }
            })
        }

        const Editors = {
            destroyEditors: (container) => {
                container.find('textarea.ckeditor-classic').each(function() {
                    const editorInstance = $(this).data('editor');
                    if (editorInstance) {
                        editorInstance.destroy().catch(error => console.error(error));
                        $(this).removeData('editor');
                    }
                });
            },

            initEditors: (container) => {
                container.find('textarea.ckeditor-classic').each(function() {
                    const textarea = this;
                    ClassicEditor.create(textarea, {
                        toolbar: {
                            items: [
                                'undo', 'redo', '|', 'heading', '|',
                                'bold', 'italic', 'strikethrough', '|',
                                'link', 'blockQuote', '|',
                                'bulletedList', 'numberedList'
                            ]
                        }
                    }).then(editor => {
                        editor.ui.view.editable.element.style.height = '200px';
                        $(textarea).data('editor', editor);

                        // Event ketika data pada editor berubah
                        editor.model.document.on('change:data', () => {
                            const variantId = $(textarea).closest('.tab-pane').attr('id').replace('tab-content-', '');
                            const index = $(textarea).closest('.card-body').index();
                            const value = editor.getData();
                            const variantData = state.variationOptions.find(v => v.variationId === variantId);

                            if (variantData && variantData.sections[index]) {
                                variantData.sections[index].description = value;
                            }
                        });
                    }).catch(error => console.error(error));
                });
            },

            renderSections: (variantId) => {
                const variantData = state.variationOptions.find(v => v.variationId === variantId);
                const container = $('#tab-content-' + variantId + ' .variant-content');

                Editors.destroyEditors(container);
                container.empty();

                variantData.sections.forEach((section, index) => {
                    const html = `
                        <div class="card-body border mb-3" style="border-width: 1px;">
                            <label>Judul</label>
                            <div class="d-flex mb-3">
                                <input type="text" class="form-control section-title" value="${section.title}" placeholder="Masukkan judul...">
                                <button class="btn btn-outline-danger ms-2 btn-remove" ${index === 0 ? 'disabled' : ''}>
                                    <i class="ri-delete-bin-fill"></i>
                                </button>
                            </div>
                            <textarea class="ckeditor-classic section-description">${section.description}</textarea>
                        </div>
                    `;

                    container.append(html);
                });

                Editors.initEditors(container);
            },

            validate: () => {
                // Setiap title dan description pada section harus diisi
                for (const variant of state.variationOptions) {
                    for (const section of variant.sections) {
                        if (section.title.trim() === '' || section.description.trim() === '') {
                            return false;
                        }
                    }
                }

                return true;
            },

            setupAllEditorEvents: () => {
                Editors.setupTitleOnChangeEvent();
                Editors.setupAddSectionEvent();
                Editors.setupRemoveSectionEvent();
            },

            setupTitleOnChangeEvent: () => {
                $(document).on('input', '.section-title', function() {
                    const variantId = $(this).closest('.tab-pane').attr('id').replace('tab-content-', '');
                    const index = $(this).closest('.card-body').index();
                    const value = $(this).val();
                    const variantData = state.variationOptions.find(v => v.variationId === variantId);
                    variantData.sections[index].title = value;
                });
            },

            setupAddSectionEvent: () => {
                $(document).on('click', '.btn-add-section', function() {
                    const variantId = $(this).closest('.tab-pane').attr('id').replace('tab-content-', '');
                    const variantData = state.variationOptions.find(v => v.variationId === variantId);
                    variantData.sections.push({
                        title: '',
                        description: ''
                    });

                    Editors.renderSections(variantId);
                });
            },

            setupRemoveSectionEvent: () => {
                $(document).on('click', '.btn-remove', function() {
                    const variantId = $(this).closest('.tab-pane').attr('id').replace('tab-content-', '');
                    const index = $(this).closest('.card-body').index();
                    const variantData = state.variationOptions.find(v => v.variationId === variantId);
                    variantData.sections.splice(index, 1);
                    Editors.renderSections(variantId);
                });
            }
        }

        const SameContents = {
            toggleCheckbox: (variantId) => {
                if (variantId === state.activeTabIdToCopyContent) {
                    $('#same-content').prop('checked', true);
                } else {
                    $('#same-content').prop('checked', false);
                }
            },

            setupAllSameContentEvents: () => {
                $('#same-content').on('change', function() {
                    if (this.checked) {
                        const activeTab = $('#variant-tabs .nav-link.active');
                        state.activeTabIdToCopyContent = activeTab.data('variant-id');

                        // Copy content ke semua tab lain
                        const activeVariantData = state.variationOptions.find(v => v.variationId === state.activeTabIdToCopyContent);
                        state.variationOptions.forEach(v => {
                            if (v.variationId !== state.activeTabIdToCopyContent) {
                                v.sections = JSON.parse(JSON.stringify(activeVariantData.sections));
                                Editors.renderSections(v.variationId);
                            }
                        });
                    } else {
                        state.activeTabIdToCopyContent = '';
                    }
                })
            }
        }

        const EventListeners = {
            setupAllEvents: () => {
                Editors.setupAllEditorEvents();
                SameContents.setupAllSameContentEvents();

                EventListeners.setupActiveTab();
                EventListeners.setupOnTabChangeEvent();
                EventListeners.setupSubmitButtonEvent();
            },

            setupActiveTab: () => {
                // Render pertama (tab aktif)
                const firstTab = $('#variant-tabs .nav-link.active').data('variant-id');
                SameContents.toggleCheckbox(firstTab);
                Editors.renderSections(firstTab);
            },

            setupOnTabChangeEvent: () => {
                // Saat pindah tab
                $('#variant-tabs .nav-link').on('shown.bs.tab', function() {
                    const variantId = $(this).data('variant-id');

                    // Cek ulang apakah "samakan konten" dicentang untuk tab ini
                    SameContents.toggleCheckbox(variantId);

                    Editors.renderSections(variantId);
                });
            },

            setupSubmitButtonEvent: () => {
                $('#submit-button').on('click', function() {
                    if (!Editors.validate()) {
                        showToast('danger', 'Validasi Gagal', 'Semua judul dan deskripsi pada bagian harus diisi!', 'ri-error-warning-line');
                        return;
                    }

                    // Disable button dan tampilkan loading
                    $(this).prop('disabled', true).text('Menyimpan...');

                    $.ajax({
                        url: "{{ route('product.updateProductDetail', ['id' => $product->id]) }}",
                        method: 'POST',
                        data: JSON.stringify({
                            variants: state.variationOptions
                        }),
                        contentType: 'application/json',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            showToast('success', 'Sukses', res.message, 'ri-check-double-fill');
                            $('#submit-button').prop('disabled', false).text('Simpan');
                        },
                        error: function(xhr, status, error) {
                            $('#submit-button').prop('disabled', false).text('Simpan');
                            if (xhr.status === 422) {
                                showToast('danger', 'Validasi Gagal', 'Terjadi kegagalan validasi data', 'ri-error-warning-line');
                            } else {
                                showToast('danger', 'Error', 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.', 'ri-error-warning-line');
                            }
                        }
                    });
                });
            }
        }

        $(document).ready(function() {
            EventListeners.setupAllEvents();
        });
    </script>
@endsection
