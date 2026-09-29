@extends('layouts.master')

@section('title')
    Print Barcode
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Systems
        @endslot
        @slot('title')
            Print Barcode
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-3">
        <div class="card-header">
            <h4 class="card-title mb-0">Preview Produk</h4>
        </div>
        <div class="card-body">
            <table id="preview-table" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 30px" class="text-center">
                            <div class="form-check form-check-md d-inline-block">
                                <input class="form-check-input" type="checkbox" id="check-all" title="Pilih Semua di Halaman Ini">
                            </div>
                        </th>
                        <th style="width: 50px" class="text-center">Jml</th>
                        <th>Nama Produk</th>
                        <th>Varian</th>
                        <th>Warna</th>
                        <th>SKU</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>

    </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Antrian Cetak <span class="badge bg-danger rounded-pill" id="queue-badge">0</span></h4>
                    <div class="flex-shrink-0">
                        <button type="button" class="btn btn-soft-danger btn-sm" id="btn-clear-queue">
                            <i class="ri-delete-bin-line align-bottom"></i> Kosongkan
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="queue-list" id="queue-container" style="max-height: 400px; overflow-y: auto;">
                        <div class="text-center text-muted py-4" id="empty-queue-msg">
                            <i class="ri-barcode-line display-4"></i>
                            <p class="mt-2">Belum ada produk yang dipilih untuk dicetak.</p>
                        </div>
                        <ul class="list-group list-group-flush" id="queue-list">
                            <!-- Items will be injected here -->
                        </ul>
                    </div>
                </div>
                <div class="card-footer bg-light border-top border-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Total Label:</span>
                        <span class="fw-bold fs-15" id="total-labels">0</span>
                    </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-success w-100" id="create-barcode-button" disabled><i class="ri-printer-fill me-1 align-bottom"></i> Print Label</button>
                        </div>
                </div>
            </div>
        </div>
    </div>

    </div>

    {{-- Modal Panduan Print --}}
    <div id="panduanModal" class="modal fade modal-primary" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="modal-panduan">
                <div class="modal-header p-3 bg-primary bg-gradient">
                    <h5 class="text-white mb-0"><i class="ri-information-line"> </i> Panduan Print Label</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-start">
                    <h6 class="fw-bold">1. Persiapan Kertas (Printer Settings)</h6>
                    <p>Sebelum mencetak, pastikan Anda sudah menambahkan jenis kertas khusus di pengaturan printer (Printer Preferences) pada komputer Anda:</p>
                    <ul>
                        <li>Buka <strong>Control Panel > Devices and Printers</strong>.</li>
                        <li>Klik kanan pada printer thermal Anda, pilih <strong>Printing Preferences</strong>.</li>
                        <li>Cari menu pengaturan ukuran kertas (Page Setup / Paper Size), lalu buat ukuran baru (New / Custom).</li>
                        <li>Beri nama kertas, misalnya: <strong>label (76.0 mm x 15.0 mm)</strong>.</li>
                        <li>Atur dimensi kertas menjadi:
                            <ul>
                                <li><strong>Lebar (Width):</strong> 76 mm</li>
                                <li><strong>Tinggi (Height):</strong> 15 mm</li>
                            </ul>
                        </li>
                        <li>Pastikan opsi <strong>Orientation</strong> diset ke <strong>Portrait</strong>.</li>
                        <li>Simpan pengaturan tersebut.</li>
                    </ul>

                    <h6 class="fw-bold mt-4">2. Cara Mencetak dari Halaman Ini</h6>
                    <ul>
                        <li>Gunakan tombol <strong>Filter Data</strong> untuk mencari produk yang ingin dicetak.</li>
                        <li>Centang produk-produk tersebut pada tabel sebelah kiri. Anda dapat mengetik jumlah yang ingin dicetak pada kolom "Jml Cetak".</li>
                        <li>Produk yang dicentang akan otomatis masuk ke panel <strong>Antrian Cetak</strong> di sebelah kanan.</li>
                        <li>Setelah semua produk yang diinginkan masuk ke antrian, klik tombol <strong>Print Label</strong> warna hijau di kanan bawah.</li>
                        <li>Sistem akan membuat file PDF otomatis. Saat jendela Print muncul (browser), pastikan opsi ukuran kertas <em>(Paper Size)</em> dipilih ke custom kertas yang dibuat di langkah 1 (Lebar 76mm x Tinggi 15mm).</li>
                        <li>Pastikan opsi <strong>Scale / Skala</strong> diatur ke <em>Default</em> atau <em>100%</em> (bukan Fit to Page).</li>
                        <li>Klik <strong>Print</strong>.</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Filter --}}
    <div id="myFilterModal" class="modal fade modal-primary" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" id="modal-filter">
                <div class="modal-header p-3 bg-primary bg-gradient">
                    <h5 class="text-white mb-0"><i class="ri-filter-line"> </i> Filter Data</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="filter-title" class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" id="filter-title" placeholder="Cari berdasarkan nama produk...">
                    </div>
                    <div class="mb-3">
                        <label for="filter-sku" class="form-label">SKU</label>
                        <input type="text" class="form-control" id="filter-sku" placeholder="Cari berdasarkan SKU...">
                    </div>
                    <div class="mb-3">
                        <label for="main-category-list" class="form-label">Main Kategori</label>
                        <select class="form-control select2" id="main-category-list" style="width: 100%">
                            <option value="" selected>Semua Main Kategori</option>
                            @foreach ($mainCategories as $mainCategory)
                                <option value="{{ $mainCategory->id }}">{{ $mainCategory->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="category-list" class="form-label">Kategori</label>
                        <select class="form-control select2" id="category-list" style="width: 100%">
                            <option value="" selected>Semua Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" data-main-category="{{ $category->main_categories_id }}">{{ $category->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="material-list" class="form-label">Material / Consignment</label>
                        <select class="form-control select2" id="material-list" style="width: 100%">
                            <option value="" selected>Semua Material</option>
                            @foreach ($materials as $material)
                                <option value="{{ $material->id }}">{{ $material->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="preview-button"><i class="ri-search-eye-line me-1 align-bottom"></i> Terapkan Filter</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/datatables-responsive-2.2.9/responsive.bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-1.11.5/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-responsive-2.2.9/dataTables.responsive.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/datatables-buttons-2.2.2/dataTables.buttons.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js') }}"></script>

    <script>
        let state = {
            dataTable: null,
            selectedProducts: {} // Format: { id: {qty, name, variant, color, sku} }
        }

        const EvenListener = {
            init: function() {
                this.setupMainCategoryEvent();
                this.setupTableSelections();
                this.setupQueuePanel();
                this.setupPreviewButton();
                this.setupCreateBarcodeButton();

                // Auto-load data on init
                this.loadPreviewTable();
            },

            setupMainCategoryEvent: function() {
                $('#main-category-list').on('change', function() {
                    let mainCatId = $(this).val();
                    let catSelect = $('#category-list');
                    
                    catSelect.val(''); // Reset
                    
                    if (mainCatId) {
                        catSelect.find('option').each(function() {
                            let optMainCat = $(this).data('main-category');
                            if ($(this).val() === "" || optMainCat == mainCatId) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                    } else {
                        catSelect.find('option').show();
                    }
                });
            },

            setupQueuePanel: function() {
                $('#btn-clear-queue').on('click', function() {
                    if (confirm('Kosongkan semua antrian cetak?')) {
                        state.selectedProducts = {};
                        $('.dt-checkbox').prop('checked', false);
                        $('.dt-qty').val(1);
                        EvenListener.updateCheckAllState();
                        EvenListener.updateQueueDisplay();
                    }
                });

                $('#queue-list').on('click', '.btn-remove-queue', function() {
                    let id = $(this).data('id');
                    delete state.selectedProducts[id];
                    
                    // Update checkbox in table if visible
                    let checkbox = $('.dt-checkbox[value="'+id+'"]');
                    if (checkbox.length) {
                        checkbox.prop('checked', false);
                    }
                    
                    EvenListener.updateCheckAllState();
                    EvenListener.updateQueueDisplay();
                });
                
                $('#queue-list').on('input change', '.queue-qty', function() {
                    let id = $(this).data('id');
                    let qty = parseInt($(this).val()) || 1;
                    if (state.selectedProducts[id]) {
                        state.selectedProducts[id].qty = qty;
                        
                        // Sync with table input if visible
                        let qtyInput = $('.dt-qty[data-id="'+id+'"]');
                        if (qtyInput.length) {
                            qtyInput.val(qty);
                        }
                    }
                    EvenListener.updateQueueDisplay(true); // true = skip render list, just update totals
                });
            },

            updateQueueDisplay: function(onlyTotals = false) {
                let totalItems = Object.keys(state.selectedProducts).length;
                let totalLabels = 0;
                
                if (!onlyTotals) {
                    let listHtml = '';
                    for (let id in state.selectedProducts) {
                        let item = state.selectedProducts[id];
                        totalLabels += item.qty;
                        
                        let varColor = [];
                        if(item.variant && item.variant !== '-') varColor.push(item.variant);
                        if(item.color && item.color !== '-') varColor.push(item.color);
                        let subText = varColor.length > 0 ? varColor.join(' - ') : 'No Variant';
                        
                        listHtml += `
                            <li class="list-group-item px-0">
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h5 class="fs-13 mb-1 text-truncate"><a href="#" class="text-body">${item.name}</a></h5>
                                        <div class="text-muted fs-12 mb-2">${item.sku} &bull; ${subText}</div>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2 fs-12">Qty:</span>
                                            <input type="number" class="form-control form-control-sm queue-qty" style="width: 70px; padding: 2px 5px;" value="${item.qty}" min="1" data-id="${id}">
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 ms-2">
                                        <button type="button" class="btn btn-sm btn-ghost-danger btn-remove-queue" data-id="${id}"><i class="ri-close-fill fs-16"></i></button>
                                    </div>
                                </div>
                            </li>
                        `;
                    }
                    
                    if (totalItems > 0) {
                        $('#empty-queue-msg').hide();
                        $('#queue-list').html(listHtml).show();
                        $('#create-barcode-button').prop('disabled', false);
                    } else {
                        $('#empty-queue-msg').show();
                        $('#queue-list').hide();
                        $('#create-barcode-button').prop('disabled', true);
                    }
                } else {
                    for (let id in state.selectedProducts) {
                        totalLabels += state.selectedProducts[id].qty;
                    }
                }
                
                $('#queue-badge').text(totalItems);
                $('#total-labels').text(totalLabels);
            },

            setupTableSelections: function() {
                // Check all
                $('#check-all').on('change', function() {
                    let isChecked = $(this).is(':checked');
                    $('.dt-checkbox').each(function() {
                        $(this).prop('checked', isChecked);
                        let id = $(this).val();
                        let qtyInput = $('.dt-qty[data-id="'+id+'"]');
                        let qty = parseInt(qtyInput.val()) || 1;
                        
                        if(isChecked) {
                            state.selectedProducts[id] = {
                                qty: qty,
                                name: $(this).data('name'),
                                variant: $(this).data('variant'),
                                color: $(this).data('color'),
                                sku: $(this).data('sku')
                            };
                        } else {
                            delete state.selectedProducts[id];
                        }
                    });
                    EvenListener.updateQueueDisplay();
                });

                // Individual check
                $('#preview-table').on('change', '.dt-checkbox', function() {
                    let id = $(this).val();
                    let qtyInput = $('.dt-qty[data-id="'+id+'"]');
                    let qty = parseInt(qtyInput.val()) || 1;
                    
                    if($(this).is(':checked')) {
                        state.selectedProducts[id] = {
                            qty: qty,
                            name: $(this).data('name'),
                            variant: $(this).data('variant'),
                            color: $(this).data('color'),
                            sku: $(this).data('sku')
                        };
                    } else {
                        delete state.selectedProducts[id];
                    }
                    EvenListener.updateCheckAllState();
                    EvenListener.updateQueueDisplay();
                });

                // Quantity change
                $('#preview-table').on('input change', '.dt-qty', function() {
                    let id = $(this).data('id');
                    let qty = parseInt($(this).val()) || 1;
                    let checkbox = $('.dt-checkbox[value="'+id+'"]');
                    
                    // Otomatis centang checkbox jika user mengubah qty > 0 dan belum tercentang
                    if (!checkbox.is(':checked') && qty > 0) {
                        checkbox.prop('checked', true).trigger('change');
                    } else if (checkbox.is(':checked') && qty > 0) {
                        // Update qty if already checked
                        state.selectedProducts[id].qty = qty;
                        // Sync UI right side
                        let queueQtyInput = $('.queue-qty[data-id="'+id+'"]');
                        if (queueQtyInput.length) {
                            queueQtyInput.val(qty);
                        }
                        EvenListener.updateQueueDisplay(true); // true = update totals only
                    } else if (qty <= 0) {
                        checkbox.prop('checked', false).trigger('change');
                    }
                });
            },

            updateCheckAllState: function() {
                let totalCheckboxes = $('.dt-checkbox').length;
                let checkedCheckboxes = $('.dt-checkbox:checked').length;
                
                if(totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes) {
                    $('#check-all').prop('checked', true);
                } else {
                    $('#check-all').prop('checked', false);
                }
            },

            setupDesigner: function() {
                // Buat draggable (Dimatikan sesuai request user)
                /*
                $('.draggable-element').draggable({
                    containment: "#label-canvas",
                    scroll: false,
                    stop: function(event, ui) {
                        // Ubah posisi px jadi % agar responsive
                        let l = (100 * parseFloat($(this).css("left")) / parseFloat($(this).parent().css("width")));
                        let t = (100 * parseFloat($(this).css("top")) / parseFloat($(this).parent().css("height")));
                        $(this).css("left", l + "%");
                        $(this).css("top", t + "%");
                    }
                });
                */

                // Toggle visibility elemen saat centang diubah
                $('.template-toggle').on('change', function() {
                    const target = $($(this).data('target'));
                    if ($(this).is(':checked')) {
                        target.show();
                    } else {
                        target.hide();
                    }
                    
                    // Khusus nama dan varian, cek parentnya jika keduanya hide
                    if ($(this).attr('id') === 'include-product-name' || $(this).attr('id') === 'include-variation') {
                        if (!$('#include-product-name').is(':checked') && !$('#include-variation').is(':checked')) {
                            $('#el-name').hide();
                        } else {
                            $('#el-name').show();
                        }
                    }
                    
                    // Khusus barcode, sembunyikan text pembantunya juga jika ada
                    if ($(this).attr('id') === 'include-barcode') {
                        if ($(this).is(':checked')) {
                            $('#el-barcode').show();
                        } else {
                            $('#el-barcode').hide();
                        }
                    }
                });

                // Update ukuran canvas jika ukuran label berubah
                $('#label-width, #label-height, #font-size, #barcode-height, #barcode-width').on('input change', function() {
                    EvenListener.updateCanvasSize();
                });

                // Tipe Barcode change
                $('#barcode-type').on('change', function() {
                    const val = $(this).val();
                    if (val === 'qrcode') {
                        $('#barcode-width').parent().prev().text('Ukuran QR');
                        $('#barcode-width').val(40);
                        $('#barcode-height').parent().hide();
                        $('#barcode-width').parent().next().hide(); // hide 'x'
                        
                        $('#dummy-barcode-wrapper').html(`
                            <svg width="40" height="40" xmlns="http://www.w3.org/2000/svg" id="canvas-barcode-img">
                                <rect width="100%" height="100%" fill="white"/>
                                <rect x="5" y="5" width="10" height="10" fill="black"/>
                                <rect x="25" y="5" width="10" height="10" fill="black"/>
                                <rect x="5" y="25" width="10" height="10" fill="black"/>
                                <rect x="20" y="20" width="15" height="15" fill="black"/>
                            </svg>
                        `);
                    } else {
                        $('#barcode-width').parent().prev().text('Dimensi Barcode');
                        $('#barcode-width').val(2);
                        $('#barcode-height').parent().show();
                        $('#barcode-width').parent().next().show(); // show 'x'
                        
                        $('#dummy-barcode-wrapper').html(`
                            <svg width="100" height="40" xmlns="http://www.w3.org/2000/svg" id="canvas-barcode-img">
                                <rect width="100%" height="100%" fill="white"/>
                                <rect x="5" y="0" width="4" height="100%" fill="black"/>
                                <rect x="12" y="0" width="2" height="100%" fill="black"/>
                                <rect x="18" y="0" width="6" height="100%" fill="black"/>
                                <rect x="28" y="0" width="2" height="100%" fill="black"/>
                                <rect x="34" y="0" width="8" height="100%" fill="black"/>
                                <rect x="44" y="0" width="3" height="100%" fill="black"/>
                                <rect x="52" y="0" width="5" height="100%" fill="black"/>
                            </svg>
                        `);
                    }
                    EvenListener.updateCanvasSize();
                });

                $('#label-preset').on('change', function() {
                    const preset = $(this).val();
                    
                    if (preset === 'thermal_2col') {
                        $('#paper-size').val('custom').trigger('change');
                        $('#paper-width').val('70');
                        $('#paper-height').val('15');
                        $('#paper-orientation').val('portrait');
                        $('#columns').val('2');
                        $('#rows-per-page').val('1');
                        $('#margin-top').val('0');
                        $('#margin-left').val('1'); // Margin kiri 1mm sesuai fisik label
                        $('#label-width').val('33');
                        $('#label-height').val('15');
                        $('#gap-h').val('2');
                        $('#gap-v').val('0');
                        $('#font-size').val('7');
                        $('#barcode-height').val('25');
                    }
                    EvenListener.updateCanvasSize();
                });

                // Init first time
                $('#label-preset').trigger('change');
                EvenListener.updateCanvasSize();
            },

            getPos: function(id) {
                return { left: 0, top: 0 };
            },

            setupPreviewButton: function() {
                $('#preview-button').on('click', function() {
                    $('#myFilterModal').modal('hide');
                    EvenListener.loadPreviewTable();
                });
            },

            setupCreateBarcodeButton: function() {
                $('#create-barcode-button').on('click', function() {
                    const button = $(this);
                    const originalText = button.html();

                    if (Object.keys(state.selectedProducts).length === 0) {
                        alert("Pilih minimal 1 produk untuk dicetak!");
                        return;
                    }

                    // Disable button dan tampilkan loading
                    button.prop('disabled', true);
                    button.html(`
                        <span class="d-flex align-items-center justify-content-center">
                            <span class="spinner-border spinner-border-sm flex-shrink-0" role="status"></span>
                            <span class="flex-grow-1 ms-2">
                                Mencetak...
                            </span>
                        </span>
                    `);

                    // Ambil nilai filter
                    const mainCategoryId = $('#main-category-list').val();
                    const categoryId = $('#category-list').val();
                    const materialId = $('#material-list').val();
                    
                    // Fixed settings for Thermal 2 Column (33x15mm each) on 76x15mm paper
                    const printSettings = {
                        paper_size: 'custom',
                        paper_orientation: 'portrait',
                        paper_width: 76,
                        paper_height: 15,
                        columns: 2,
                        rows_per_page: 1, // Karena tinggi kertas pas 15mm, 1 baris per halaman
                        margin_top: 0,
                        margin_left: 5, // geser ke kanan agar tidak kepotong di kiri
                        label_width: 33,
                        label_height: 15,
                        gap_h: 3,
                        gap_v: 0, // Tidak perlu gap V karena 1 baris per halaman (dikontrol printer)
                        font_size: 6,
                        barcode_height: 30,
                        barcode_width: 45,
                        barcode_type: 'qrcode',
                        rotate: $('#rotate-pdf').is(':checked') ? 1 : 0
                    };

                    // Kirim data ke server untuk membuat barcode
                    $.ajax({
                        url: "{{ route('print-barcode.print') }}",
                        method: 'POST',
                        data: {
                            main_category_id: mainCategoryId,
                            category_id: categoryId,
                            material_id: materialId,
                            
                            // Transform object back to { id: qty } format for backward compatibility with controller
                            selected_items: JSON.stringify(
                                Object.keys(state.selectedProducts).reduce((acc, key) => {
                                    acc[key] = state.selectedProducts[key].qty;
                                    return acc;
                                }, {})
                            ),
                            include_barcode: 1,
                            include_sku: 1,
                            include_product_name: 1,
                            include_variation: 1,
                            include_price: 1,
                            auto_paper_height: 0,
                            ...printSettings,
                            _token: '{{ csrf_token() }}'
                        },
                        xhrFields: {
                            responseType: 'blob'
                        },
                        success: function(response) {
                            const url = window.URL.createObjectURL(new Blob([response], {
                                type: 'application/pdf'
                            }));
                            window.open(url, '_blank');
                        },
                        error: function(xhr, status, error) {
                            console.error('Error creating barcodes:', error);
                            alert('Terjadi kesalahan saat membuat barcode. Silakan coba lagi.');
                        },
                        complete: function() {
                            button.prop('disabled', false);
                            button.html(originalText);
                        }
                    });
                });
            },

            loadPreviewTable: function() {
                // Ambil nilai filter
                const title = $('#filter-title').val();
                const sku = $('#filter-sku').val();
                const mainCategoryId = $('#main-category-list').val();
                const categoryId = $('#category-list').val();
                const materialId = $('#material-list').val();

                // Hancurkan DataTable yang sudah ada jika ada
                if (state.dataTable) {
                    state.dataTable.destroy();
                    $('#preview-table tbody').empty(); // Kosongkan tbody untuk menghindari duplikasi data
                }

                // Inisialisasi DataTable dengan parameter filter
                state.dataTable = $('#preview-table').DataTable({
                    dom: "<'row'<'col-sm-8 col-md-8 btn-tbl'B><'col-sm-4 col-md-4'<'span dt-right'l>>>" +
                        "<'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    processing: true,
                    serverSide: true,
                    lengthMenu: [
                        [100, 250, 500, 1000],
                        [100, 250, 500, 1000]
                    ],
                    buttons: [
                        {
                            text: '<i class="ri-filter-line"></i> Filter Data',
                            className: 'btn btn-primary btn-sm child-button',
                            action: function(e, dt, button, config) {
                                $('#myFilterModal').modal('show');
                            }
                        },
                        {
                            text: '<i class="ri-information-line"></i> Panduan Print',
                            className: 'btn btn-primary btn-sm child-button ms-2',
                            action: function(e, dt, button, config) {
                                $('#panduanModal').modal('show');
                            }
                        }
                    ],
                    ajax: {
                        url: "{{ route('print-barcode.preview') }}",
                        data: function(d) {
                            d.title = $('#filter-title').val();
                            d.sku = $('#filter-sku').val();
                            d.main_category_id = $('#main-category-list').val();
                            d.category_id = $('#category-list').val();
                            d.material_id = $('#material-list').val();
                        }
                    },
                    columns: [{
                            data: 'checkbox',
                            name: 'checkbox',
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        },
                        {
                            data: 'qty',
                            name: 'qty',
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        },
                        {
                            data: 'product_name',
                            name: 'product_name'
                        },
                        {
                            data: 'variant',
                            name: 'variant'
                        },
                        {
                            data: 'color',
                            name: 'color'
                        },
                        {
                            data: 'sku',
                            name: 'sku'
                        }
                    ],
                    scrollX: true,
                    drawCallback: function(settings) {
                        $('.dt-checkbox').each(function() {
                            let id = $(this).val();
                            if (state.selectedProducts[id] !== undefined) {
                                $(this).prop('checked', true);
                                $('.dt-qty[data-id="'+id+'"]').val(state.selectedProducts[id]);
                            }
                        });
                        EvenListener.updateCheckAllState();
                    }
                })
            },

            setupSelect2: () => {
                $('#product-list').select2({
                    ajax: {
                        url: "{{ route('print-barcode.searchProducts') }}",
                        dataType: 'json',
                        delay: 300,
                        data: function(params) {
                            return {
                                keyword: params.term
                            };
                        },
                        processResults: function(data) {
                            // ambil hanya produk untuk select2 utama
                            return {
                                results: data.products.map(function(p) {
                                    return {
                                        id: p.id,
                                        text: p.title,
                                        // simpan varian & color di data tambahan
                                        variants: data.variants,
                                        colors: data.colors
                                    };
                                })
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 2
                });

                $('#variation-list').select2();
                $('#color-list').select2();
                $('#store-list').select2();
                $('#category-list').select2();
                $('#material-list').select2();
            },

            setupProductSelectedEvent: () => {
                $('#product-list').on('select2:select', function(e) {
                    const selected = e.params.data;
                    const variants = selected.variants.filter(v => v.product_id == selected.id);
                    const colors = selected.colors.filter(c => c.product_id == selected.id);

                    // Reset varian
                    $('#variation-list').empty().append('<option value="">Semua Varian</option>');
                    if (variants && variants.length > 0) {
                        variants.forEach(function(v) {
                            $('#variation-list').append(
                                $('<option>', {
                                    value: v.id,
                                    text: v.name
                                })
                            );
                        });
                    }

                    // Refresh Select2
                    $('#variation-list').val('').trigger('change');

                    // Reset warna
                    $('#color-list').empty().append('<option value="">Semua Warna</option>');
                    if (colors && colors.length > 0) {
                        colors.forEach(function(c) {
                            $('#color-list').append(
                                $('<option>', {
                                    value: c.id,
                                    text: c.label
                                })
                            );
                        });
                    }

                    // Refresh Select2
                    $('#color-list').val('').trigger('change');
                });
            }
        }

        $(document).ready(function() {
            EvenListener.init();
        });
    </script>
@endsection
