@extends('layouts.master')

@section('title')
    Detail {{ $titleHeader->name }}
@endsection

@section('cssAdded')
    <style>
        .image-box .remove-btn {
            position: absolute;
            top: 6px;
            right: 6px;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 14px;
            line-height: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 4;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .image-box .remove-btn:hover {
            opacity: 1;
        }

        .img-container {
            max-height: 400px;
            overflow: hidden;
        }

        .img-container img {
            max-width: 100%;
            display: block;
        }

        .image-box {
            border: 2px dashed #ccc;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            background-color: #f8f9fa;
            width: 100%;
            cursor: pointer;
            overflow: hidden;
            /* biar gambar tidak keluar */
        }

        /* Rasio persegi panjang 3:4 */
        .image-box.main {
            aspect-ratio: 3 / 4;
        }

        /* Rasio kotak 1:1 */
        .image-box.small {
            aspect-ratio: 1 / 1;
        }

        /* Label tetap di atas gambar */
        .image-label {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            font-size: 0.7rem;
            text-align: center;
            padding: 3px 0;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            z-index: 3;
            /* supaya di atas gambar */
        }

        /* Ikon tetap di tengah */
        .image-box i {
            font-size: 2rem;
            color: #aaa;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
            /* supaya tetap di atas gambar */
            pointer-events: none;
            /* klik tetap ke input */
        }

        /* Gambar di belakang ikon dan label */
        .image-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1;
            /* gambar di bawah */
        }

        .color-tag {
            display: inline-flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: .25rem;
            margin-right: .5rem;
            margin-bottom: .5rem;
            background: #f8f9fa;
        }

        .color-box {
            width: 30px;
            height: 30px;
            border-right: 1px solid #ddd;
        }

        .color-name {
            padding: 0 .5rem;
        }

        .color-remove {
            border: none;
            background: transparent;
            color: #666;
            font-size: .9rem;
            margin-right: .3rem;
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

    <div class="btn-group mb-3" role="group">
        <a href="#" class="btn btn-primary material-shadow-none active">Informasi Dasar</a>
        <a href="{{ route('product.priceAndStock', ['id' => $product->id]) }}" class="btn btn-primary material-shadow-none">Stok dan Harga</a>
        <a href="{{ route('product.productDetail', ['id' => $product->id]) }}" class="btn btn-primary material-shadow-none">Detail Produk</a>
    </div>
    <div class="row">
        {{-- Gambar section --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="box">
                        <h5 class="mb-3">Gambar</h5>
                        <div class="">
                            <div class="row mb-3">
                                <!-- Gambar Utama -->
                                <div class="col-md-6 mb-3">
                                    <div class="image-box main" data-name="main_pic">
                                        <img class="preview {{ isset($product->main_pic) ? '' : 'd-none' }}" src="{{ isset($product->main_pic) ? $product->main_pic : '' }}" alt="preview">
                                        <i class="ri-image-2-fill {{ isset($product->main_pic) ? 'd-none' : '' }}"></i>
                                        <button type="button" class="remove-btn {{ isset($product->main_pic) ? '' : 'd-none' }}">&times;</button>
                                        <div class="image-label">Gambar Utama (770x865 px)</div>
                                    </div>
                                </div>
                                <!-- Gambar Hover -->
                                <div class="col-md-6 mb-3">
                                    <div class="image-box main" data-name="hover_pic">
                                        <img class="preview {{ isset($product->hover_pic) ? '' : 'd-none' }}" src="{{ isset($product->hover_pic) ? $product->hover_pic : '' }}" alt="preview">
                                        <i class="ri-image-2-fill {{ isset($product->hover_pic) ? 'd-none' : '' }}"></i>
                                        <button type="button" class="remove-btn {{ isset($product->hover_pic) ? '' : 'd-none' }}">&times;</button>
                                        <div class="image-label">Gambar Saat Disorot (770x865 px)</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                @foreach ($product->pictures as $picture)
                                    <div class="col-md-3 mb-3">
                                        <div class="image-box small" data-name="{{ $picture['name'] }}">
                                            <img class="preview {{ isset($picture['path']) ? '' : 'd-none' }}" src="{{ isset($picture['path']) ? $picture['path'] : '' }}" alt="preview">
                                            <i class="ri-image-2-fill {{ isset($picture['path']) ? 'd-none' : '' }}"></i>
                                            <button type="button" class="remove-btn {{ isset($picture['path']) ? '' : 'd-none' }}">&times;</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            {{-- Informasi dasar section --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Informasi Dasar</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="product-name" class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" id="product-name" name="product_name" value="{{ $product->title }}">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="department" class="form-label">Departemen</label>
                            <select class="form-control select2" name="icon" id="department">
                                <option value="">Pilih Departemen</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="category" class="form-label">Kategori</label>
                            <select class="form-control select2" name="category_id" id="category">
                                <option value="">Pilih Kategori</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="material" class="form-label">Material / Consignment (Opsional)</label>
                            <select class="form-control select2" name="material_id" id="material">
                                <option value="">Pilih Material</option>
                                @foreach ($materials as $material)
                                    <option value="{{ $material->id }}" {{ ($product->material_id ?? '') == $material->id ? 'selected' : '' }}>{{ $material->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="top-pick" {{ $product->top_pick ? 'checked' : '' }}>
                        <label class="form-check-label" for="top-pick">Jadikan produk Top Pick</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="ecommerce-list" {{ $product->show_in_ecommerce ? 'checked' : '' }}>
                        <label class="form-check-label" for="ecommerce-list">Tampilkan di Ecommerce</label>
                    </div>
                </div>
            </div>

            {{-- Varian section --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Varian</h6>
                </div>
                <div class="card-body">
                    <label class="form-label">Nama Varian</label>

                    <!-- Container untuk varian -->
                    <div id="variant-container">
                        @forelse ($product->variants as $variant)
                            <div class="d-flex mb-3 variant-row" data-id="{{ $variant->id }}">
                                <input type="text" class="form-control variant-input-name" placeholder="Nama Varian" value="{{ $variant->name }}">
                                <button class="btn btn-outline-danger ms-2 btn-remove" data-id="{{ $variant->id }}">
                                    <i class="ri-delete-bin-fill"></i>
                                </button>
                            </div>
                        @empty
                            <div class="d-flex mb-3 variant-row">
                                <input type="text" class="form-control variant-input-name" placeholder="Nama Varian">
                                <button class="btn btn-outline-danger ms-2 btn-remove">
                                    <i class="ri-delete-bin-fill"></i>
                                </button>
                            </div>
                        @endforelse
                    </div>

                    <div class="d-flex justify-content-end">
                        <button id="btn-add-variant" type="button" class="btn btn-primary d-flex align-items-center justify-content-center">
                            <i class="ri-add-fill me-2"></i>
                            <span>Tambah Varian</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Warna section --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Pilihan Warna</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-2">
                            <label class="form-label">Warna</label>
                            <input type="color" id="colorPicker" class="form-control form-control-color" style="width: 100%" value="#000000">
                        </div>
                        <div class="col-10">
                            <label class="form-label">Kode Hex</label>
                            <input type="text" id="hexInput" class="form-control" value="#000000">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Warna</label>
                        <div class="d-flex">
                            <input type="hidden" id="colorId" value="">
                            <input type="text" id="colorName" class="form-control" placeholder="cth: Gold, Neutral">
                            <button id="btnAddColor" type="button" class="btn btn-outline-primary ms-2">
                                <i class="ri-add-fill"></i>
                            </button>
                        </div>
                    </div>

                    <div id="colorList" class="d-flex flex-wrap">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button class="btn btn-primary mb-3" id="submit-button">Simpan dan Lanjutkan</button>
        </div>
    </div>

    {{-- Crop image modal --}}
    <div class="modal fade" id="cropImageModal" tabindex="-1" aria-labelledby="cropImageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cropImageModalLabel">Crop Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="img-container">
                        <img id="imageToCrop" src="" alt="Image to Crop">
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">Drag untuk memindahkan, gunakan sudut untuk mengubah ukuran crop area</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="cropButton">Crop & Apply</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-bootstrap-theme-0.1.0-beta.10/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/toast.init.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

    <script>
        // Global state
        let state = {
            uploadedFiles: {},
            colorsArray: @json($product->colors ?? []),
            $department: $('#department'),
            $category: $('#category'),
            selectedDepartment: "{{ $product->categories->mainCategory->id ?? '' }}",
            selectedCategory: "{{ $product->categories->id ?? '' }}",
            deletedVariants: [],
            deletedColors: [],
            currentBox: null,
            cropper: null,
            currentFile: null,
            deletedImages: []
        }

        const Category = {
            setupCategoryEvents: () => {
                // event ketika departemen berubah
                state.$department.on('change', function() {
                    let deptId = $(this).val();
                    state.$category.empty().append('<option value="">Loading...</option>');

                    if (deptId) {
                        let url = "{{ route('product.getCategoryByMain', ':id') }}".replace(':id', deptId);

                        $.getJSON(url, function(data) {
                            state.$category.empty().append('<option value="">Pilih Kategori</option>');

                            $.each(data, function(i, category) {
                                let isSelected = (category.id == state.selectedCategory) ? 'selected' : '';
                                state.$category.append(
                                    `<option value="${category.id}" ${isSelected}>${category.title}</option>`
                                );
                            });
                        });
                    } else {
                        state.$category.empty().append('<option value="">Pilih Kategori</option>');
                    }
                });

                if (state.selectedDepartment) {
                    state.$department.val(state.selectedDepartment).trigger('change');
                }
            }
        }

        const ImageCropper = {
            initCropper: (box) => {
                const $image = $('#imageToCrop');
                // const dimensions = $(box).data('dimensions');
                let aspectRatio = 780 / 865; // default square

                // Parse dimensions jika ada
                // if (dimensions) {
                //     const [width, height] = dimensions.split('x').map(Number);
                //     aspectRatio = width / height;
                // }

                // Destroy cropper lama kalau ada
                if (state.cropper) {
                    state.cropper.destroy();
                }

                // Inisialisasi cropper baru
                state.cropper = new Cropper($image[0], {
                    aspectRatio: aspectRatio,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.8,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                    responsive: true,
                    modal: true,
                    background: true
                });
            },

            cropAndApply: () => {
                if (!state.cropper || !state.currentBox) {
                    showToast('error', 'Error', 'Tidak ada gambar untuk di-crop.', 'ri-error-warning-fill');
                    return;
                }

                const canvas = state.cropper.getCroppedCanvas({
                    width: 800,
                    height: 800 * (state.cropper.getData().height / state.cropper.getData().width),
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high'
                });

                canvas.toBlob((blob) => {
                    const fileName = state.currentFile.name;
                    const croppedFile = new File([blob], fileName, {
                        type: state.currentFile.type,
                        lastModified: Date.now()
                    });

                    // Update preview
                    let $preview = $(state.currentBox).find("img.preview");
                    $preview.attr("src", canvas.toDataURL()).removeClass("d-none");

                    // Hide icon
                    $(state.currentBox).find("i").addClass("d-none");

                    // Simpan file hasil crop
                    state.uploadedFiles[$(state.currentBox).data("name")] = croppedFile;

                    // Tutup modal
                    bootstrap.Modal.getInstance($('#cropImageModal')[0]).hide();

                    showToast('success', 'Berhasil!', 'Gambar berhasil di-crop dan diterapkan.', 'ri-check-fill');
                }, state.currentFile.type, 0.9);
            },

            setupCropButton: () => {
                $('#cropButton').on('click', function() {
                    ImageCropper.cropAndApply();
                });
            }
        };


        const Image = {
            handleFile: (file, box) => {
                const maxSize = 2 * 1024 * 1024; // 2MB dalam byte

                if (!file.type.startsWith("image/")) {
                    showToast('warning', 'Tipe file tidak didukung', 'Silakan unggah file gambar yang valid.', 'ri-alert-fill');
                    return;
                }

                if (file.size > maxSize) {
                    showToast('warning', 'Ukuran file terlalu besar', 'Maksimal ukuran gambar adalah 2MB.', 'ri-alert-fill');
                    return;
                }

                // Simpan context untuk cropper
                state.currentBox = box;
                state.currentFile = file;

                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#imageToCrop').attr('src', e.target.result);

                    // Tampilkan modal crop
                    const modal = new bootstrap.Modal($('#cropImageModal')[0]);
                    modal.show();

                    // Inisialisasi cropper ketika modal ditampilkan
                    $('#cropImageModal').one('shown.bs.modal', function() {
                        ImageCropper.initCropper(box);
                    });
                };

                reader.readAsDataURL(file);
            },

            setupImageEvents: () => {
                // drag & drop + klik fallback
                $(".image-box").each(function() {
                    let box = $(this);

                    box.on("dragover", function(e) {
                        e.preventDefault();
                        box.addClass("dragover");
                    });

                    box.on("dragleave", function(e) {
                        e.preventDefault();
                        box.removeClass("dragover");
                    });

                    box.on("drop", function(e) {
                        e.preventDefault();
                        box.removeClass("dragover");

                        let file = e.originalEvent.dataTransfer.files[0];
                        if (file) Image.handleFile(file, box);
                    });

                    box.on("click", function(e) {
                        if ($(e.target).hasClass("remove-btn")) {
                            return;
                        }

                        let input = $("<input type='file' accept='image/*' style='display:none;'>");
                        $("body").append(input);

                        input.on("change", function(e) {
                            let file = e.target.files[0];
                            if (file) Image.handleFile(file, box);
                            input.remove();
                        });

                        input.click();
                    });
                });
            },


            setupDeleteImageEvent: () => {
                $(document).on("click", ".remove-btn", function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const $box = $(this).closest(".image-box");
                    const $img = $box.find("img.preview");
                    const $icon = $box.find("i");

                    // reset tampilan
                    $img.attr("src", "").addClass("d-none");
                    $icon.removeClass("d-none");
                    $(this).addClass("d-none");

                    // simpan data-name
                    const name = $box.data("name");
                    if (name && !state.deletedImages.includes(name)) {
                        state.deletedImages.push(name);
                    }
                });
            }
        }

        const Variant = {
            setupVariantEvents: () => {
                // Tambah varian
                $('#btn-add-variant').click(function() {
                    let newRow = `
                        <div class="d-flex mb-3 variant-row">
                            <input type="text" class="form-control variant-input-name" placeholder="Nama Varian">
                            <button class="btn btn-outline-danger ms-2 btn-remove">
                                <i class="ri-delete-bin-fill"></i>
                            </button>
                        </div>
                    `;

                    $('#variant-container').append(newRow);
                });

                // Hapus varian (gunakan event delegation)
                $(document).on('click', '.btn-remove', function() {
                    $(this).closest('.variant-row').remove();

                    // Simpan id varian yang dihapus
                    let variantId = $(this).data('id');
                    if (variantId) {
                        // Jika ada id, berarti ini varian yang sudah ada di database
                        state.deletedVariants.push(variantId);
                    }
                });
            },
        }

        const Color = {
            loadColorsFromDB: () => {
                $("#colorList").empty();
                state.colorsArray.forEach((color, index) => {
                    Color.displayColor(color, index);
                });
            },

            displayColor: (color, index) => {
                let colorItem = `
                    <div class="color-tag" data-index="${index}" style="cursor:pointer;">
                        <div class="color-box" style="background:${color.hexa};"></div>
                        <span class="color-name">${color.label}</span>
                        <button type="button" class="color-remove">&times;</button>
                    </div>
                `;

                $("#colorList").append(colorItem);
            },

            resetInputs: () => {
                $("#colorId").val("");
                $("#colorName").val("");
                $("#colorPicker").val("#000000");
                $("#hexInput").val("#000000");
            },

            setupColorEvents: () => {
                Color.loadColorsFromDB();

                // Sinkronisasi picker <-> hex
                $('#colorPicker').on('input', function() {
                    $('#hexInput').val($(this).val());
                });

                $('#hexInput').on('input', function() {
                    let val = $(this).val();
                    if (/^#([0-9A-F]{3}){1,2}$/i.test(val)) {
                        $('#colorPicker').val(val);
                    }
                });

                // Tambah atau update warna
                $("#btnAddColor").on("click", function() {
                    let colorId = $("#colorId").val() || null;
                    let colorName = $("#colorName").val().trim();
                    let hexValue = $("#hexInput").val();

                    if (!colorName) {
                        showToast('warning', 'Validasi Gagal', 'Silakan masukkan nama warna.', 'ri-alert-fill');
                        return;
                    }

                    if (!/^#[0-9A-F]{6}$/i.test(hexValue)) {
                        showToast('warning', 'Validasi Gagal', 'Silakan masukkan kode hex yang valid (cth: #A1B2C3).', 'ri-alert-fill');
                        return;
                    }

                    if (colorId) {
                        // === Mode Edit ===
                        let index = state.colorsArray.findIndex(c => String(c.id) === String(colorId));
                        if (index !== -1) {
                            // Jika id ditemukan, update warna
                            state.colorsArray[index].label = colorName;
                            state.colorsArray[index].hexa = hexValue;
                        } else {
                            // kalau id tidak ditemukan, anggap warna baru
                            state.colorsArray.push({
                                id: colorId,
                                label: colorName,
                                hexa: hexValue
                            });
                        }
                    } else {
                        // === Mode Tambah ===
                        // Cek duplikasi
                        let isDuplicate = state.colorsArray.some(color =>
                            color.label.toLowerCase() === colorName.toLowerCase() ||
                            color.hexa.toLowerCase() === hexValue.toLowerCase()
                        );

                        if (isDuplicate) {
                            showToast('warning', 'Duplikasi Warna', 'Warna dengan nama atau kode hex yang sama sudah ada.', 'ri-alert-fill');
                            return;
                        }

                        state.colorsArray.push({
                            id: null, // warna baru
                            label: colorName,
                            hexa: hexValue
                        });
                    }

                    // Refresh tampilan
                    Color.loadColorsFromDB();
                    Color.resetInputs();
                });

                // Klik tombol "×" untuk hapus
                $(document).on('click', '.color-remove', function(e) {
                    e.stopPropagation();
                    let index = $(this).closest('.color-tag').data('index');

                    // Simpan id warna yang dihapus
                    let colorId = state.colorsArray[index].id;
                    if (colorId) {
                        // Jika ada id, berarti ini warna yang sudah ada di database
                        state.deletedColors.push(colorId);
                    }

                    // Hapus dari array
                    state.colorsArray.splice(index, 1);
                    Color.loadColorsFromDB();
                });

                // Klik tag warna (kecuali tombol hapus) → edit
                $(document).on('click', '.color-tag', function(e) {
                    // Karen klik pada tombol hapus juga memicu event ini, kita cek apakah targetnya adalah tombol hapus
                    if ($(e.target).hasClass('color-remove')) return;

                    let index = $(this).data('index');
                    let color = state.colorsArray[index];

                    // Set input fields dengan data warna yang dipilih untuk diedit
                    $("#colorId").val(color.id ?? "");
                    $("#colorName").val(color.label);
                    $("#hexInput").val(color.hexa);
                    $("#colorPicker").val(color.hexa);

                    // Hapus color tag, dari tampilan saja
                    // Data masih ada di colorsArray
                    $(this).remove();
                });
            }
        }

        const EventListeners = {
            setupAll: () => {
                Category.setupCategoryEvents();
                Image.setupDeleteImageEvent();
                Image.setupImageEvents();
                Variant.setupVariantEvents();
                Color.setupColorEvents();
                ImageCropper.setupCropButton();

                EventListeners.setupSubmitButtonEvent();
                EventListeners.setupSelect2();
            },

            setupSubmitButtonEvent: () => {
                $("#submit-button").on("click", function(e) {
                    e.preventDefault();

                    // Validasi basic
                    let productName = $("#product-name").val().trim();
                    let categoryId = $("#category").val();

                    if (!productName) {
                        showToast('warning', 'Validasi Gagal', 'Silakan masukkan nama produk.', 'ri-alert-fill');
                        return;
                    }

                    if (!categoryId) {
                        showToast('warning', 'Validasi Gagal', 'Silakan pilih kategori produk.', 'ri-alert-fill');
                        return;
                    }

                    // Buat FormData untuk mengirim file dan informasi
                    let formData = new FormData();

                    // Tambahkan data dasar
                    formData.append('product_name', productName);
                    formData.append('category_id', categoryId);
                    formData.append('material_id', $('#material').val());
                    formData.append('top_pick', $('#top-pick').is(':checked') ? 1 : 0);
                    formData.append('show_in_ecommerce', $('#ecommerce-list').is(':checked') ? 1 : 0);

                    // Tambahkan informasi yang dihapus
                    state.deletedVariants.forEach(function(variantId) {
                        formData.append('deleted_variants[]', variantId);
                    });

                    state.deletedColors.forEach(function(colorId) {
                        formData.append('deleted_colors[]', colorId);
                    });

                    state.deletedImages.forEach(function(imageName) {
                        formData.append('deleted_images[]', imageName);
                    });

                    // Tambahkan variants
                    let variants = [];
                    $(".variant-input-name").each(function(index) {
                        let variantName = $(this).val().trim();
                        let variantId = $(this).closest('.variant-row').data('id');
                        if (variantName) {
                            variants.push({
                                id: variantId || null,
                                name: variantName
                            });
                        }
                    });

                    // Cek apakah varian kosong
                    if (variants.length === 0) {
                        showToast('warning', 'Validasi Gagal', 'Setidaknya satu varian harus diisi.', 'ri-alert-fill');
                        return;
                    }

                    // Cek apakah color kosong
                    if (state.colorsArray.length === 0) {
                        showToast('warning', 'Validasi Gagal', 'Setidaknya satu warna harus diisi.', 'ri-alert-fill');
                        return;
                    }

                    // Append variants ke FormData
                    if (variants.length > 0) {
                        variants.forEach(function(variant, index) {
                            formData.append(`variants[${index}][id]`, variant.id || '');
                            formData.append(`variants[${index}][name]`, variant.name);
                        });
                    }

                    // Tambahkan colors
                    if (state.colorsArray.length > 0) {
                        state.colorsArray.forEach(function(color, index) {
                            formData.append(`colors[${index}][id]`, color.id || '');
                            formData.append(`colors[${index}][label]`, color.label);
                            formData.append(`colors[${index}][hexa]`, color.hexa);
                        });
                    }

                    // Loop melalui uploadedFiles dan tambahkan ke FormData
                    Object.keys(state.uploadedFiles).forEach(function(imageName) {
                        let file = state.uploadedFiles[imageName];

                        // Tambahkan file ke FormData
                        formData.append('images[]', file);

                        // Tambahkan informasi mapping nama gambar
                        formData.append('image_names[]', imageName);
                    });

                    // Tambahkan CSRF token jika ada
                    let csrfToken = $('meta[name="csrf-token"]').attr('content');
                    if (csrfToken) {
                        formData.append('_token', csrfToken);
                    }

                    // Kirim data ke server
                    $.ajax({
                        url: "{{ route('product.updateBasicInformation', ['id' => $product->id]) }}",
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        beforeSend: function() {
                            // Disable submit button dan show loading
                            $("#submit-button").prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');

                            // Kembalikan border kotak gambar ke default
                            $(".image-box").css('border', '2px dashed #ccc');
                        },
                        success: function(response) {
                            showToast('success', 'Sukses', response.message, 'ri-check-double-fill');

                            // Reset state setelah berhasil
                            state.deletedImages = [];
                            state.deletedVariants = [];
                            state.deletedColors = [];
                            state.uploadedFiles = {};

                            // Cek apakah ada gambar yang gagal diupload
                            if (response.failed_upload_images && Object.keys(response.failed_upload_images).length > 0) {
                                Object.keys(response.failed_upload_images).forEach(function(imageName) {
                                    let errorMsg = response.failed_upload_images[imageName] || 'Gagal mengupload gambar.';
                                    showToast('warning', 'Upload Gagal', errorMsg, 'ri-alert-fill');

                                    // Buat border merah solid pada kotak gambar yang gagal
                                    $(`.image-box[data-name="${imageName}"]`).css('border', '2px solid red');
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            if (xhr.status === 422) {
                                // Validation errors
                                let errors = xhr.responseJSON.errors;
                                let errorMessages = [];

                                for (let field in errors) {
                                    errorMessages.push(errors[field].join(', '));
                                }

                                showToast('danger', 'Validasi Gagal', 'Terjadi Kegagalan Validasi', 'ri-alert-fill');
                            } else {
                                let errorMsg = 'Terjadi kesalahan saat menyimpan data.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                }
                                showToast('danger', 'Error', errorMsg, 'ri-alert-fill');
                            }
                        },
                        complete: function() {
                            // Re-enable submit button
                            $("#submit-button").prop('disabled', false).html('Simpan dan Lanjutkan');
                        }
                    });
                });
            },

            setupSelect2: () => {
                $('.select2').select2();
            }
        }

        $(document).ready(function() {
            EventListeners.setupAll();
        })
    </script>
@endsection
