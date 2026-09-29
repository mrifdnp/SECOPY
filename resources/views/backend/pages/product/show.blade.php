@extends('layouts.master')

@section('title')
    Detail {{ $titleHeader->name }}
@endsection

@section('cssAdded')
    <style>
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
        <button type="button" class="btn btn-primary material-shadow-none active">Informasi Dasar</button>
        <button type="button" class="btn btn-primary material-shadow-none">Stok dan Harga</button>
        <button type="button" class="btn btn-primary material-shadow-none">Detail Produk</button>
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
                                        <img class="preview d-none" alt="preview">
                                        <i class="ri-image-2-fill"></i>
                                        <div class="image-label">Gambar Utama</div>
                                    </div>
                                </div>
                                <!-- Gambar Hover -->
                                <div class="col-md-6 mb-3">
                                    <div class="image-box main" data-name="hover_pic">
                                        <img class="preview d-none" alt="preview">
                                        <i class="ri-image-2-fill"></i>
                                        <div class="image-label">Gambar Hover (Saat Disorot)</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Baris 1 -->
                                <div class="col-md-3 mb-3">
                                    <div class="image-box small" data-name="pic_1">
                                        <img class="preview d-none" alt="preview">
                                        <i class="ri-image-2-fill"></i>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="image-box small" data-name="pic_2">
                                        <img class="preview d-none" alt="preview">
                                        <i class="ri-image-2-fill"></i>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="image-box small" data-name="pic_3">
                                        <img class="preview d-none" alt="preview">
                                        <i class="ri-image-2-fill"></i>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3" data-name="pic_4">
                                    <div class="image-box small">
                                        <img class="preview d-none" alt="preview">
                                        <i class="ri-image-2-fill"></i>
                                    </div>
                                </div>

                                <!-- Baris 2 -->
                                <div class="col-md-3 mb-3">
                                    <div class="image-box small" data-name="pic_5">
                                        <img class="preview d-none" alt="preview">
                                        <i class="ri-image-2-fill"></i>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="image-box small" data-name="pic_6">
                                        <img class="preview d-none" alt="preview">
                                        <i class="ri-image-2-fill"></i>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="image-box small" data-name="pic_7">
                                        <img class="preview d-none" alt="preview">
                                        <i class="ri-image-2-fill"></i>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="image-box small" data-name="pic_8">
                                        <img class="preview d-none" alt="preview">
                                        <i class="ri-image-2-fill"></i>
                                    </div>
                                </div>
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
                        <input type="text" class="form-control" id="product-name" name="product_name">
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
                            <select class="form-control select2" name="icon" id="category">
                                <option value="">Pilih Kategori</option>
                            </select>
                        </div>
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
                        <div class="d-flex mb-3 variant-row">
                            <input type="text" class="form-control variant-input-name" placeholder="Nama Varian">
                            <button class="btn btn-outline-danger ms-2 btn-remove">
                                <i class="ri-delete-bin-fill"></i>
                            </button>
                        </div>
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
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-bootstrap-theme-0.1.0-beta.10/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js') }}"></script>

    <script>
        $(document).ready(function() {
            let uploadedFiles = {};
            let colorsArray = []

            $('#department').on('change', function() {
                let deptId = $(this).val();
                let $category = $('#category');

                $category.empty().append('<option value="">Loading...</option>');

                if (deptId) {
                    // Gunakan route dari Laravel
                    let url = "{{ route('product.getCategoryByMain', ':id') }}";
                    url = url.replace(':id', deptId);

                    $.getJSON(url, function(data) {
                        $category.empty().append('<option value="">Pilih Kategori</option>');
                        $.each(data, function(i, category) {
                            $category.append(`<option value="${category.id}">${category.title}</option>`);
                        });
                    });
                } else {
                    $category.empty().append('<option value="">Pilih Kategori</option>');
                }
            });

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
                    if (file) handleFile(file, box);
                });

                box.on("click", function() {
                    let input = $("<input type='file' accept='image/*' style='display:none;'>");
                    $("body").append(input);

                    input.on("change", function(e) {
                        let file = e.target.files[0];
                        if (file) handleFile(file, box);
                        input.remove();
                    });

                    input.click();
                });
            });

            function handleFile(file, box) {
                if (file.type.startsWith("image/")) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        let preview = box.find("img.preview");
                        preview.attr("src", e.target.result).removeClass("d-none");

                        // ikon hilang
                        box.find("i").addClass("d-none");
                    };

                    reader.readAsDataURL(file);

                    uploadedFiles[box.data("name")] = file;
                    console.log(box.data("name"))
                } else {
                    alert("Harap masukkan file gambar!");
                }
            }

            $("#submit-button").on("click", function(e) {
                e.preventDefault();

                // Validasi basic
                let productName = $("#product-name").val().trim();
                let categoryId = $("#category").val();

                if (!productName) {
                    alert("Nama produk harus diisi!");
                    return;
                }

                if (!categoryId) {
                    alert("Kategori harus dipilih!");
                    return;
                }

                // Buat FormData untuk mengirim file dan informasi
                let formData = new FormData();

                // Tambahkan data dasar
                formData.append('product_name', productName);
                formData.append('category_id', categoryId);

                // Tambahkan variants
                let variants = [];
                $(".variant-input-name").each(function(index) {
                    let variantName = $(this).val().trim();
                    if (variantName) {
                        variants.push({
                            id: null, // untuk variant baru
                            name: variantName
                        });
                    }
                });

                // Cek apakah varian kosong
                if (variants.length === 0) {
                    alert("Setidaknya satu varian harus diisi!");
                    return;
                }

                // Cek apakah color kosong
                if (colorsArray.length === 0) {
                    alert("Setidaknya satu warna harus diisi!");
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
                if (colorsArray.length > 0) {
                    colorsArray.forEach(function(color, index) {
                        formData.append(`colors[${index}][id]`, color.id || '');
                        formData.append(`colors[${index}][label]`, color.label);
                        formData.append(`colors[${index}][hexa]`, color.hexa);
                    });
                }

                // Loop melalui uploadedFiles dan tambahkan ke FormData
                Object.keys(uploadedFiles).forEach(function(imageName) {
                    let file = uploadedFiles[imageName];

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

                // Debug: Log FormData content
                console.log("FormData contents:");
                for (let [key, value] of formData.entries()) {
                    console.log(key, value);
                }

                // Kirim data ke server
                // $.ajax({
                //     url: '/your-endpoint', // Ganti dengan URL endpoint Anda
                //     method: 'POST',
                //     data: formData,
                //     processData: false,
                //     contentType: false,
                //     beforeSend: function() {
                //         // Disable submit button dan show loading
                //         $("#submit-button").prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');
                //     },
                //     success: function(response) {
                //         console.log('Success:', response);
                //         alert('Produk berhasil disimpan!');

                //         // Redirect atau reset form sesuai kebutuhan
                //         // window.location.href = '/products';
                //     },
                //     error: function(xhr, status, error) {
                //         console.error('Error:', xhr.responseText);

                //         if (xhr.status === 422) {
                //             // Validation errors
                //             let errors = xhr.responseJSON.errors;
                //             let errorMessages = [];

                //             for (let field in errors) {
                //                 errorMessages.push(errors[field].join(', '));
                //             }

                //             alert('Validation Error:\n' + errorMessages.join('\n'));
                //         } else {
                //             alert('Terjadi kesalahan saat menyimpan data!');
                //         }
                //     },
                //     complete: function() {
                //         // Re-enable submit button
                //         $("#submit-button").prop('disabled', false).html('Simpan dan Lanjutkan');
                //     }
                // });
            });

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
            });

            $('#colorPicker').on('input', function() {
                $('#hexInput').val($(this).val());
            });

            // Sinkronisasi hex -> color picker
            $('#hexInput').on('input', function() {
                let val = $(this).val();
                if (/^#([0-9A-F]{3}){1,2}$/i.test(val)) {
                    $('#colorPicker').val(val);
                }
            });

            $("#btnAddColor").on("click", function() {
                let colorName = $("#colorName").val().trim();
                let hexValue = $("#hexInput").val();

                if (!colorName) {
                    alert("Nama warna harus diisi!");
                    return;
                }

                if (!/^#[0-9A-F]{6}$/i.test(hexValue)) {
                    alert("Kode hex tidak valid!");
                    return;
                }

                // Cek duplikasi
                let isDuplicate = colorsArray.some(color =>
                    color.label.toLowerCase() === colorName.toLowerCase() ||
                    color.hexa.toLowerCase() === hexValue.toLowerCase()
                );

                if (isDuplicate) {
                    alert("Warna dengan nama atau kode hex tersebut sudah ada!");
                    return;
                }

                // Tambah ke array
                let newColor = {
                    id: null, // untuk warna baru, id akan null
                    label: colorName,
                    hexa: hexValue
                };

                colorsArray.push(newColor);

                // Tampilkan di UI
                displayColor(newColor, colorsArray.length - 1);

                // Reset input
                $("#colorName").val("");
                $("#colorPicker").val("#000000");
                $("#hexInput").val("#000000");
            });

            function displayColor(color, index) {
                let colorItem = `
                    <div class="color-tag me-2 mb-2" data-index="${index}">
                        <div class="color-box" style="background:${color.hexa};"></div>
                        <span class="color-name">${color.label}</span>
                        <button class="color-remove">&times;</button>
                    </div>
                `;

                $("#colorList").append(colorItem);
            }

            // Hapus warna (delegasi event karena element baru)
            $(document).on('click', '.color-remove', function() {
                let index = $(this).closest('.color-tag').data('index');
                colorsArray.splice(index, 1);
                $(this).closest('.color-tag').remove();

                // Update index pada UI
                $("#colorList .color-tag").each(function(newIndex) {
                    $(this).attr("data-index", newIndex);
                });
            });

            $('.select2').select2();
        })
    </script>
@endsection
