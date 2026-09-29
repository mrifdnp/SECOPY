@extends('layouts.master')

@section('css')
<link href="{{ URL::asset('build/libs/velzon-dependencies/select2-bootstrap-theme-0.1.0-beta.10/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.css') }}" rel="stylesheet" type="text/css" />
<style>
    .select2-container { z-index: 9999; } /* Fix select2 in modal */
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Sinkronisasi SKU Lama (Sementara)</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('product.index') }}">Product</a></li>
                    <li class="breadcrumb-item active">Sync SKU</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Upload File CSV</h5>
            </div>
            <div class="card-body">
                @if(session('error_message'))
                    <div class="alert alert-danger">{{ session('error_message') }}</div>
                @endif
                
                <form action="{{ route('import.syncOldSkuPreview') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="file" class="form-label">Pilih file .csv dari Web Lama</label>
                        <input class="form-control" type="file" id="file" name="file" accept=".csv" required>
                        <small class="text-muted">Pastikan file CSV memiliki kolom header <b>NAMA PRODUK</b> dan <b>KODE PRODUK</b>.</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Pratinjau / Preview Data</button>
                </form>
            </div>
        </div>
    </div>
</div>

@if(isset($preview))
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <form action="{{ route('import.syncOldSkuProcess') }}" method="POST" id="syncForm">
                @csrf
                <input type="hidden" name="sync_data" id="sync_data">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Pratinjau Hasil Pencocokan</h5>
                    <button type="button" id="btnSubmitSync" class="btn btn-success"><i class="ri-save-line align-middle me-1"></i> Lanjutkan Sinkronisasi</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle" id="previewTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px; text-align: center;">
                                        <input type="checkbox" class="form-check-input" id="checkAll">
                                    </th>
                                    <th>NAMA PRODUK LAMA</th>
                                    <th>KODE PRODUK LAMA (SKU)</th>
                                    <th>PRODUK WEB BARU</th>
                                    <th>VARIAN</th>
                                    <th>WARNA</th>
                                    <th>SKU BARU</th>
                                    <th class="text-center">STATUS</th>
                                    <th class="text-center">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $matchedCount = 0;
                                @endphp
                                @foreach($preview as $idx => $row)
                                    @if($row['is_match'])
                                        @php $matchedCount++; @endphp
                                    @endif
                                    <tr class="sync-row" data-idx="{{ $idx }}" data-old-sku="{{ $row['old_sku'] }}" data-product-id="{{ $row['matched_product_id'] }}" data-variant-id="{{ $row['matched_variant_id'] }}" data-color-id="{{ $row['matched_color_id'] }}">
                                        <td class="text-center">
                                            @if(!empty($row['is_processed']) && $row['is_processed'])
                                                <input type="checkbox" class="form-check-input row-checkbox" value="1" disabled>
                                            @else
                                                <input type="checkbox" class="form-check-input row-checkbox" value="1" {{ $row['is_match'] ? 'checked' : '' }} {{ !$row['is_match'] ? 'disabled' : '' }}>
                                            @endif
                                        </td>
                                        <td>{{ $row['old_name'] }}</td>
                                        <td><strong>{{ $row['old_sku'] }}</strong></td>
                                        <td class="text-product">{{ $row['matched_product'] }}</td>
                                        <td class="text-variant">{{ $row['matched_variant'] }}</td>
                                        <td class="text-color">{{ $row['matched_color'] }}</td>
                                        <td>
                                            @if(!empty($row['is_processed']) && $row['is_processed'])
                                                <input type="text" class="form-control form-control-sm input-new-sku" value="{{ $row['old_sku'] }}" disabled>
                                            @else
                                                <input type="text" class="form-control form-control-sm input-new-sku" value="{{ $row['old_sku'] }}">
                                            @endif
                                        </td>
                                        <td class="text-center text-status">
                                            @if(!empty($row['is_processed']) && $row['is_processed'])
                                                <span class="badge bg-success">Tersimpan</span>
                                            @elseif($row['is_match'])
                                                <span class="badge bg-success">Cocok</span>
                                            @else
                                                <span class="badge bg-danger">Tidak Cocok</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-2 justify-content-center">
                                                @if(!empty($row['is_processed']) && $row['is_processed'])
                                                    <button type="button" class="btn btn-sm btn-success" disabled>
                                                        <i class="ri-check-double-line"></i> Selesai
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-manual" data-idx="{{ $idx }}">
                                                        <i class="ri-edit-2-line"></i> Pilih Manual
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-success btn-process-single" data-idx="{{ $idx }}" {{ !$row['is_match'] ? 'disabled' : '' }}>
                                                        <i class="ri-check-line"></i> Proses
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <strong>Total Data:</strong> {{ count($preview) }} | <strong>Berhasil Dicocokkan:</strong> <span id="matchedCount">{{ $matchedCount }}</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Pilih Manual -->
<div class="modal fade" id="manualEditModal" tabindex="-1" aria-labelledby="manualEditModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manualEditModalLabel">Pilih Produk Baru Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editRowIdx">
                <div class="mb-3">
                    <label class="form-label">Cari Produk Web Baru</label>
                    <select class="form-control select2-ajax" id="selectManualProduct" style="width: 100%;"></select>
                    <small class="text-muted">Ketik nama produk, varian, atau warna.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSaveManual">Ganti Produk</button>
            </div>
        </div>
    </div>
</div>

@endif

@endsection

@section('script')
<script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js')}}"></script>
<script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js')}}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('checkAll');
        if (checkAll) {
            checkAll.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.row-checkbox:not([disabled])');
                checkboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
            });
            
            // Initial checkAll status
            const allEnabled = document.querySelectorAll('.row-checkbox:not([disabled])');
            const allChecked = document.querySelectorAll('.row-checkbox:not([disabled]):checked');
            if (allEnabled.length > 0 && allEnabled.length === allChecked.length) {
                checkAll.checked = true;
            }
        }

        // Setup Select2 Ajax in Modal
        $('#selectManualProduct').select2({
            dropdownParent: $('#manualEditModal'),
            placeholder: "Cari produk...",
            minimumInputLength: 2,
            ajax: {
                url: "{{ route('import.syncOldSkuSearch') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return { results: data.results };
                }
            }
        });

        // Open Modal
        $('.btn-edit-manual').on('click', function() {
            const idx = $(this).data('idx');
            $('#editRowIdx').val(idx);
            $('#selectManualProduct').val(null).trigger('change');
            $('#manualEditModal').modal('show');
        });

        // Auto-open select2 when modal is shown
        $('#manualEditModal').on('shown.bs.modal', function () {
            $('#selectManualProduct').select2('open');
        });

        // Force focus to search field when select2 opens
        $('#selectManualProduct').on('select2:open', function () {
            setTimeout(function() {
                const searchField = document.querySelector('.select2-container--open .select2-search__field');
                if(searchField) searchField.focus();
            }, 50);
        });

        // Save Manual Edit
        $('#btnSaveManual').on('click', function() {
            const idx = $('#editRowIdx').val();
            const selectedData = $('#selectManualProduct').select2('data');
            
            if (!selectedData || selectedData.length === 0) {
                alert('Silakan pilih produk terlebih dahulu!');
                return;
            }
            
            const data = selectedData[0];
            const row = $('.sync-row[data-idx="'+idx+'"]');
            
            // Update Data Attributes
            row.attr('data-product-id', data.product_id);
            row.attr('data-variant-id', data.variant_id || '');
            row.attr('data-color-id', data.color_id || '');
            
            // Update UI Text
            row.find('.text-product').text(data.product_title);
            row.find('.text-variant').text(data.variant_name);
            row.find('.text-color').text(data.color_label);
            
            // Update Status Badge
            row.find('.text-status').html('<span class="badge bg-primary">Manual (Cocok)</span>');
            
            // Enable and Check the Checkbox
            const checkbox = row.find('.row-checkbox');
            checkbox.prop('disabled', false);
            checkbox.prop('checked', true);
            
            // Enable Process Single button
            row.find('.btn-process-single').prop('disabled', false);
            
            $('#manualEditModal').modal('hide');
        });

        // Process Single Row via AJAX
        $('.btn-process-single').on('click', function() {
            const btn = $(this);
            const idx = btn.data('idx');
            const row = $('.sync-row[data-idx="'+idx+'"]');
            
            const payload = {
                _token: '{{ csrf_token() }}',
                old_sku: row.find('.input-new-sku').val(),
                product_id: row.attr('data-product-id'),
                variant_id: row.attr('data-variant-id') || null,
                color_id: row.attr('data-color-id') || null
            };
            
            btn.prop('disabled', true).html('<i class="ri-loader-4-line bx-spin"></i> Proses...');
            
            $.ajax({
                url: "{{ route('import.syncOldSkuProcessSingle') }}",
                type: "POST",
                data: payload,
                success: function(response) {
                    if (response.status === 'success') {
                        btn.removeClass('btn-outline-success').addClass('btn-success').html('<i class="ri-check-double-line"></i> Selesai');
                        row.find('.text-status').html('<span class="badge bg-success">Tersimpan</span>');
                        
                        // Disable checkbox so it's not processed again in bulk
                        const checkbox = row.find('.row-checkbox');
                        checkbox.prop('checked', false);
                        checkbox.prop('disabled', true);
                    }
                },
                error: function(xhr) {
                    alert('Gagal menyimpan SKU. Pastikan produk ini valid.');
                    btn.prop('disabled', false).html('<i class="ri-check-line"></i> Proses');
                }
            });
        });

        // Handle Form Submit via JSON
        $('#btnSubmitSync').on('click', function() {
            const syncData = [];
            $('.sync-row').each(function() {
                const checkbox = $(this).find('.row-checkbox');
                if (checkbox.is(':checked') && !checkbox.is(':disabled')) {
                    syncData.push({
                        old_sku: $(this).find('.input-new-sku').val(),
                        product_id: $(this).attr('data-product-id'),
                        variant_id: $(this).attr('data-variant-id') || null,
                        color_id: $(this).attr('data-color-id') || null
                    });
                }
            });
            
            if (syncData.length === 0) {
                alert('Pilih setidaknya satu baris untuk disinkronisasi.');
                return;
            }
            
            $('#sync_data').val(JSON.stringify(syncData));
            $('#syncForm').submit();
        });
    });
</script>
@endsection
