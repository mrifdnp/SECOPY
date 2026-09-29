@extends('layouts.master')

@section('title')
    Bulk Input COGM
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Systems
        @endslot
        @slot('title')
            Bulk Input COGM
        @endslot
    @endcomponent

    <div class="row">
        {{-- Step 1: Input IDs --}}
        <div class="col-lg-12">
            <div class="card" id="card-input">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">
                        <i class="ri-search-line me-1"></i> Step 1: Masukkan SKU Product Detail
                    </h5>
                    <div class="flex-shrink-0">
                        <a href="{{ route('product.index') }}" class="btn btn-sm btn-soft-secondary">
                            <i class="las la-arrow-left"></i> Kembali ke Produk
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="ids_input" class="form-label fw-medium">Opsi 1: Paste SKU</label>
                                <textarea id="ids_input" class="form-control" rows="5" placeholder="Contoh:
SKU-001
SKU-002"></textarea>
                                <small class="text-muted">Pisahkan dengan Enter atau koma.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="csv_file" class="form-label fw-medium">Opsi 2: Upload file CSV/Excel</label>
                                <input type="file" id="csv_file" class="form-control mb-2" accept=".csv,.txt">
                                <small class="text-muted">Sistem membaca kolom pertama sbg SKU.</small><br>
                                <button id="btn-load-csv" class="btn btn-sm btn-outline-secondary mt-1" type="button">
                                    <i class="ri-file-excel-line me-1"></i> Muat ke Textarea
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="name_input" class="form-label fw-medium">Opsi 3: Paste Nama</label>
                                <textarea id="name_input" class="form-control" rows="5" placeholder="Contoh:
Earing Vase Fume S
Candle Loros Fume S"></textarea>
                                <small class="text-muted">Akan dicocokkan 2 kata pertamanya.</small>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="store_filter" class="form-label fw-medium">Filter Toko</label>
                            <select id="store_filter" class="form-select">
                                <option value="all">Semua Toko (Cari di semua gudang)</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ session('store.id') == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Batasi hasil pencarian hanya pada toko tertentu.</small>
                        </div>
                    </div>
                    <button id="btn-lookup" class="btn btn-primary">
                        <i class="ri-search-line me-1"></i> Cari & Cocokkan
                    </button>
                </div>
            </div>
        </div>

        {{-- Step 2: Result Table --}}
        <div class="col-lg-12" id="result-section" style="display: none;">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">
                        <i class="ri-edit-line me-1"></i> Step 2: Input COGM Baru
                    </h5>
                    <div class="flex-shrink-0">
                        <span id="result-summary" class="text-muted me-3"></span>
                        <button id="btn-fill-all-cogm" class="btn btn-sm btn-soft-info me-2" title="Isi semua COGM baru dengan nilai yang sama">
                            <i class="ri-pencil-fill me-1"></i> Isi Semua COGM
                        </button>
                        <button id="btn-fill-all-price" class="btn btn-sm btn-soft-info me-2" title="Isi semua Harga baru dengan nilai yang sama">
                            <i class="ri-pencil-fill me-1"></i> Isi Semua Harga
                        </button>
                        <button id="btn-save" class="btn btn-success">
                            <i class="ri-save-line me-1"></i> Simpan Semua
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Not Found Alert --}}
                    <div id="not-found-alert" class="alert alert-warning" style="display: none;">
                        <i class="ri-alert-line me-1"></i> <strong>SKU tidak ditemukan:</strong>
                        <span id="not-found-ids"></span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle" id="cogm-table">
                            <thead class="table-active">
                                <tr>
                                    <th style="width:40px">No</th>
                                    <th>Toko</th>
                                    <th>Nama Pencarian</th>
                                    <th>Nama Produk (Sistem)</th>
                                    <th>SKU</th>
                                    <th>Varian</th>
                                    <th>Warna</th>
                                    <th>Qty</th>
                                    <th>COGM Lama</th>
                                    <th style="width:180px">COGM Baru</th>
                                    <th>Harga Lama</th>
                                    <th style="width:180px">Harga Baru</th>
                                    <th style="width:80px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="cogm-tbody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            var lookupData = [];

            // Handle paste from Excel - convert clipboard data
            $('#ids_input').on('paste', function(e) {
                var $textarea = $(this);
                setTimeout(function() {
                    var text = $textarea.val();
                    // Replace tabs with newlines (Excel copies cells with tabs)
                    text = text.replace(/\t/g, '\n');
                    // Remove empty lines
                    text = text.split('\n').filter(function(line) { return line.trim() !== ''; }).join('\n');
                    $textarea.val(text);
                }, 50);
            });

            // Load CSV file
            $('#btn-load-csv').on('click', function() {
                var fileInput = document.getElementById('csv_file');
                if (!fileInput.files || !fileInput.files[0]) {
                    Swal.fire('Peringatan', 'Silakan pilih file CSV terlebih dahulu.', 'warning');
                    return;
                }

                var reader = new FileReader();
                reader.onload = function(e) {
                    var lines = e.target.result.split(/[\r\n]+/);
                    var skus = [];
                    lines.forEach(function(line) {
                        // Ambil kolom pertama (sebelum koma/tab/semicolon)
                        var cols = line.split(/[,;\t]/);
                        var val = (cols[0] || '').trim();
                        // Skip header jika mengandung kata "sku"
                        if (val && val.toLowerCase() !== 'sku' && val.toLowerCase() !== 'kode') {
                            skus.push(val);
                        }
                    });

                    if (skus.length > 0) {
                        var existing = $('#ids_input').val().trim();
                        var newVal = existing ? existing + '\n' + skus.join('\n') : skus.join('\n');
                        $('#ids_input').val(newVal);
                        Swal.fire('Berhasil', skus.length + ' SKU berhasil dimuat dari file.', 'success');
                    } else {
                        Swal.fire('Peringatan', 'Tidak ada SKU yang ditemukan di file.', 'warning');
                    }
                };
                reader.readAsText(fileInput.files[0]);
            });

            // Remove row from list
            $(document).on('click', '.btn-remove-row', function() {
                $(this).closest('tr').remove();
            });

            // Save single row
            $(document).on('click', '.btn-save-row', function() {
                var $btn = $(this);
                var $tr = $btn.closest('tr');
                var $cogm = $tr.find('.cogm-input');
                var $price = $tr.find('.price-input');
                var $sku = $tr.find('.sku-input');
                
                var valCogm = $cogm.val();
                var valPrice = $price.val();
                var valSku = $sku.val().trim();
                var origSku = $sku.attr('placeholder');
                
                var item = { id: $cogm.data('id') };
                var hasUpdate = false;
                
                if (valCogm !== '') {
                    item.cogm = valCogm;
                    hasUpdate = true;
                }
                if (valPrice !== '') {
                    item.price = valPrice;
                    hasUpdate = true;
                }
                if (valSku !== '' && valSku !== origSku) {
                    item.sku = valSku;
                    hasUpdate = true;
                }
                
                if (!hasUpdate) {
                    Swal.fire('Info', 'Tidak ada perubahan pada COGM, Harga, atau SKU.', 'info');
                    return;
                }

                $btn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin"></i>');

                $.ajax({
                    url: "{{ route('product.bulkCogmSave') }}",
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        _token: '{{ csrf_token() }}',
                        items: [item]
                    }),
                    success: function(resp) {
                        if (resp.success) {
                            $btn.removeClass('btn-soft-success').addClass('btn-success').html('<i class="ri-check-double-line"></i>');
                            if (valCogm !== '') {
                                var formatted = parseFloat(valCogm).toLocaleString('id-ID');
                                $tr.find('td:eq(8)').text('Rp' + formatted);
                                $cogm.attr('placeholder', valCogm).val('');
                            }
                            if (valPrice !== '') {
                                var formattedPrice = parseFloat(valPrice).toLocaleString('id-ID');
                                $tr.find('td:eq(10)').text('Rp' + formattedPrice);
                                $price.attr('placeholder', valPrice).val('');
                            }
                            if (valSku !== '' && valSku !== origSku) {
                                $sku.attr('placeholder', valSku);
                            }
                            setTimeout(function() {
                                $btn.prop('disabled', false).removeClass('btn-success').addClass('btn-soft-success').html('<i class="ri-check-line"></i>');
                            }, 2000);
                        } else {
                            $btn.prop('disabled', false).html('<i class="ri-check-line"></i>');
                            Swal.fire('Error', resp.message || 'Gagal menyimpan', 'error');
                        }
                    },
                    error: function() {
                        $btn.prop('disabled', false).html('<i class="ri-check-line"></i>');
                        Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
                    }
                });
            });

            // Step 1: Lookup
            $('#btn-lookup').on('click', function() {
                var ids = $('#ids_input').val().trim();
                var nameStr = $('#name_input').val().trim();
                
                if (!ids && !nameStr) {
                    Swal.fire('Peringatan', 'Silakan masukkan SKU atau Nama Produk terlebih dahulu.', 'warning');
                    return;
                }

                var $btn = $(this);
                $btn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i> Mencari...');

                $.ajax({
                    url: "{{ route('product.bulkCogmLookup') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: ids,
                        name: nameStr,
                        store_id: $('#store_filter').val()
                    },
                    success: function(resp) {
                        $btn.prop('disabled', false).html('<i class="ri-search-line me-1"></i> Cari & Cocokkan');

                        if (!resp.success) {
                            Swal.fire('Error', resp.message, 'error');
                            return;
                        }

                        lookupData = resp.data;

                        // Show summary
                        $('#result-summary').html(
                            '<span class="badge bg-success me-1">' + resp.found_count + ' ditemukan</span>' +
                            (resp.not_found_count > 0 ? '<span class="badge bg-danger">' + resp.not_found_count + ' tidak ditemukan</span>' : '')
                        );

                        // Show not found IDs
                        if (resp.not_found_count > 0) {
                            $('#not-found-ids').text(resp.not_found.join(', '));
                            $('#not-found-alert').show();
                        } else {
                            $('#not-found-alert').hide();
                        }

                        // Render table
                        var html = '';
                        $.each(resp.data, function(i, item) {
                            var cogmFormatted = item.cogm ? parseFloat(item.cogm).toLocaleString('id-ID') : '0';
                            var priceFormatted = item.price ? parseFloat(item.price).toLocaleString('id-ID') : '0';
                            var matchedInput = item.matched_name_input ? '<span class="text-primary fw-medium">' + item.matched_name_input + '</span>' : '<span class="text-muted">-</span>';
                            html += '<tr>' +
                                '<td>' + (i + 1) + '</td>' +
                                '<td>' + (item.store_name || '-') + '</td>' +
                                '<td><small>' + matchedInput + '</small></td>' +
                                '<td><strong>' + (item.product_name || '-') + '</strong></td>' +
                                '<td><input type="text" class="form-control form-control-sm sku-input" value="' + (item.sku || '') + '" placeholder="' + (item.sku || '') + '"></td>' +
                                '<td>' + (item.variant_name || '-') + '</td>' +
                                '<td>' + (item.color_name || '-') + '</td>' +
                                '<td>' + (item.qty || 0) + '</td>' +
                                '<td>Rp' + cogmFormatted + '</td>' +
                                '<td><input type="number" class="form-control form-control-sm cogm-input" data-id="' + item.id + '" value="" placeholder="' + (item.cogm || 0) + '" min="0" step="any"></td>' +
                                '<td>Rp' + priceFormatted + '</td>' +
                                '<td><input type="number" class="form-control form-control-sm price-input" value="" placeholder="' + (item.price || 0) + '" min="0" step="any"></td>' +
                                '<td><div class="d-flex gap-1"><button type="button" class="btn btn-sm btn-soft-success btn-save-row" title="Simpan baris ini"><i class="ri-check-line"></i></button><button type="button" class="btn btn-sm btn-soft-danger btn-remove-row" title="Hapus dari daftar"><i class="ri-close-line"></i></button></div></td>' +
                                '</tr>';
                        });

                        $('#cogm-tbody').html(html);
                        $('#result-section').slideDown();

                        // Scroll ke result
                        $('html, body').animate({
                            scrollTop: $('#result-section').offset().top - 80
                        }, 500);
                    },
                    error: function() {
                        $btn.prop('disabled', false).html('<i class="ri-search-line me-1"></i> Cari & Cocokkan');
                        Swal.fire('Error', 'Terjadi kesalahan saat mencari data.', 'error');
                    }
                });
            });

            // Fill All buttons
            $('#btn-fill-all-cogm').on('click', function() {
                Swal.fire({
                    title: 'Isi Semua COGM',
                    input: 'number',
                    inputLabel: 'Masukkan nilai COGM yang ingin diisi ke semua baris:',
                    inputAttributes: { min: 0, step: 'any' },
                    showCancelButton: true,
                    confirmButtonText: 'Terapkan',
                    cancelButtonText: 'Batal',
                }).then(function(result) {
                    if (result.isConfirmed && result.value !== '') {
                        $('.cogm-input').val(result.value);
                    }
                });
            });

            $('#btn-fill-all-price').on('click', function() {
                Swal.fire({
                    title: 'Isi Semua Harga',
                    input: 'number',
                    inputLabel: 'Masukkan nilai Harga yang ingin diisi ke semua baris:',
                    inputAttributes: { min: 0, step: 'any' },
                    showCancelButton: true,
                    confirmButtonText: 'Terapkan',
                    cancelButtonText: 'Batal',
                }).then(function(result) {
                    if (result.isConfirmed && result.value !== '') {
                        $('.price-input').val(result.value);
                    }
                });
            });

            // Step 2: Save All
            $('#btn-save').on('click', function() {
                var items = [];
                $('#cogm-tbody tr').each(function() {
                    var $cogm = $(this).find('.cogm-input');
                    var $price = $(this).find('.price-input');
                    var $sku = $(this).find('.sku-input');
                    
                    var valCogm = $cogm.val();
                    var valPrice = $price.val();
                    var valSku = $sku.val().trim();
                    var origSku = $sku.attr('placeholder');
                    
                    if (valCogm !== '' || valPrice !== '' || (valSku !== '' && valSku !== origSku)) {
                        items.push({
                            id: $cogm.data('id'),
                            cogm: valCogm !== '' ? valCogm : null,
                            price: valPrice !== '' ? valPrice : null,
                            sku: valSku !== '' && valSku !== origSku ? valSku : null
                        });
                    }
                });

                if (items.length === 0) {
                    Swal.fire('Peringatan', 'Silakan isi minimal satu COGM atau Harga baru.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Anda akan mengupdate COGM untuk ' + items.length + ' item. Lanjutkan?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan',
                    cancelButtonText: 'Batal',
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    var $btn = $('#btn-save');
                    $btn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i> Menyimpan...');

                    $.ajax({
                        url: "{{ route('product.bulkCogmSave') }}",
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            _token: '{{ csrf_token() }}',
                            items: items
                        }),
                        success: function(resp) {
                            $btn.prop('disabled', false).html('<i class="ri-save-line me-1"></i> Simpan Semua');

                            if (resp.success) {
                                Swal.fire('Berhasil!', resp.message, 'success').then(function() {
                                    // Update displayed old COGM and Price values
                                    $('.cogm-input').each(function() {
                                        var val = $(this).val();
                                        if (val !== '') {
                                            var formatted = parseFloat(val).toLocaleString('id-ID');
                                            $(this).closest('tr').find('td:eq(8)').text('Rp' + formatted);
                                            $(this).attr('placeholder', val).val('');
                                        }
                                    });
                                    $('.price-input').each(function() {
                                        var val = $(this).val();
                                        if (val !== '') {
                                            var formatted = parseFloat(val).toLocaleString('id-ID');
                                            $(this).closest('tr').find('td:eq(10)').text('Rp' + formatted);
                                            $(this).attr('placeholder', val).val('');
                                        }
                                    });
                                });
                            } else {
                                Swal.fire('Error', resp.message, 'error');
                            }
                        },
                        error: function() {
                            $btn.prop('disabled', false).html('<i class="ri-save-line me-1"></i> Simpan Semua');
                            Swal.fire('Error', 'Terjadi kesalahan saat menyimpan.', 'error');
                        }
                    });
                });
            });
        });
    </script>
@endsection
