<div class="modal fade" id="processMutationModal" tabindex="-1" aria-labelledby="processMutationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content p-3">
            <!-- Begin modal header -->
            <div class="modal-header py-2">
                <h3 class="modal-title fw-semibold" id="processMutationModalLabel">Proses Mutasi</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <hr class="mx-n3 border border-primary-subtle opacity-100">

            <!-- Begin modal body -->
            <div class="modal-body py-2">

                <!-- Begin Summary -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-primary-subtle border-0 shadow-none mb-0">
                            <div class="card-body p-3">
                                <h6 class="fw-semibold mb-2">Ringkasan Mutasi</h6>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Total Item</span>
                                    <span class="fw-semibold" id="modalSummaryTotalItem">0 Pcs</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Total COGM</span>
                                    <span class="fw-semibold" id="modalSummaryTotalCOGM">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Total Harga</span>
                                    <span class="fw-semibold" id="modalSummaryTotalHarga">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Begin payment method -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Tipe Mutasi</label>
                            <small class="text-muted">Pilih salah satu</small>
                        </div>

                        <div class="row g-2 justify-content-start" id="mutationType">
                            @foreach ($mutationTypes ?? [] as $i => $pt)
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                    <button type="button" class="btn btn-outline-primary w-100 rounded-3 py-3 text-start overflow-hidden {{ $i === 0 ? 'active' : '' }}" data-bs-toggle="button" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" data-method-id="{{ $pt->id }}">
                                        <div class="fw-semibold text-truncate">{{ $pt->mutation_type }}</div>
                                        <div class="small text-muted text-truncate">{{ $pt->notes ?? '—' }}</div>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Origin -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="origin" class="form-label mb-0">Asal</label>
                        </div>
                        <div id="origin_select_container">
                            <select id="origin" class="form-select" aria-label="Pilih Sales Person">
                                <option value="" selected hidden>Pilih</option>
                                @foreach ($parties ?? [] as $party)
                                    <option value="{{ $party->id }}" {{ $party->store_id === $currentStoreId && str_contains($party->tags ?? '', 'Store') ? 'selected' : '' }}>{{ $party->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Tujuan (hanya muncul jika mutation type keluar --}}
                <div class="row mb-4" id="destination_row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="destination" class="form-label mb-0">Tujuan</label>
                        </div>
                        <div id="destination_select_container">
                            <select id="destination" class="form-select" aria-label="Pilih Sales Person">
                                <option value="" selected hidden>Pilih</option>
                                @foreach ($parties ?? [] as $party)
                                    <option value="{{ $party->id }}">{{ $party->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Waktu mutasi --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <label for="datetime" class="form-label mb-1">
                            Waktu Mutasi
                        </label>
                        <input type="datetime-local" id="datetime" class="form-control" value="{{ date('Y-m-d\TH:i') }}" aria-label="Waktu Mutasi">
                    </div>
                </div>

                {{-- Sales Person --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <label for="sale_people_id" class="form-label mb-1">Sales Person <span class="text-danger">*</span></label>
                        <select id="sale_people_id" class="form-select" aria-label="Pilih Sales Person">
                            <option value="" selected hidden>Pilih Sales Person</option>
                            @foreach ($salePeople ?? [] as $sp)
                                <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Begin Remark/Catatan -->
                <div class="row">
                    <div class="col-12">
                        <label for="remark" class="form-label mb-1">Remark/Catatan</label>
                        <textarea id="remark" name="remark" class="form-control" rows="3" placeholder="Tambahkan catatan (opsional)" aria-describedby="remarkHelp" maxlength="200" spellcheck="true"></textarea>
                        <div id="remarkHelp" class="form-text">Maks. 200 karakter.</div>
                    </div>
                </div>

            </div>

            <!-- Begin modal footer -->
            <div class="modal-footer px-3">
                <div class="d-flex flex-column flex-md-row w-100 align-items-stretch gap-3">
                    <button type="button" class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2 py-2 px-3" style="min-width:150px; min-height:50px;" data-bs-dismiss="modal" aria-label="Tutup modal dan jadikan draft">
                        <i class="ri-close-circle-line fs-5" aria-hidden="true"></i>
                        <span>Batalkan</span>
                    </button>

                    <button type="button" id="btnConfirmMutation" class="btn btn-primary d-flex align-items-center justify-content-center gap-2 py-2 flex-fill" style="min-width: 150px; min-height: 50px;">
                        <i class="ri-drag-move-2-fill fs-5" aria-hidden="true"></i>
                        <span class="text-truncate">Mutasi Produk</span>
                    </button>
                </div>
            </div>
            <!-- End modal footer -->
        </div>
    </div>
</div>


@push('script-mutation-process-modal')
    <script>
        $(document).ready(function() {
            // Variabel untuk menyimpan data mutasi
            let mutationData = {
                mutationType: null,
                origin: "{{ $parties->filter(function($p) use ($currentStoreId) { return $p->store_id == $currentStoreId && str_contains($p->tags ?? '', 'Store'); })->first()->id ?? null }}",
                destination: null,
                isOriginCustom: false,
                customOrigin: null,
                isDestinationCustom: false,
                customDestination: null,
                dateTime: null,
                remark: null,
                movementId: null,
                salePeopleId: null
            };

            // Event handler untuk modal show - init select2 dan set default values
            $('#processMutationModal').on('show.bs.modal', function(event) {
                // cari attribut button yang memanggil modal
                const button = event.relatedTarget;
                const fromIncoming = button?.getAttribute('data-from-incoming') === 'true';
                const movementId = button?.getAttribute('data-movement-id') || null;
                const originPartyId = button?.getAttribute('data-origin-party-id') || null;
                const destinationPartyId = button?.getAttribute('data-destination-party-id') || null;

                mutationData.movementId = movementId;

                // Initialize Select2 untuk origin dan destination
                $('#origin').select2({
                    placeholder: 'Pilih Asal',
                    allowClear: true,
                    dropdownParent: $('#processMutationModal')
                });

                $('#destination').select2({
                    placeholder: 'Pilih Tujuan',
                    allowClear: true,
                    dropdownParent: $('#processMutationModal')
                });

                // Ganti origin dan destination jika dari barang masuk
                if (fromIncoming) {
                    if (originPartyId) {
                        $('#origin').val(originPartyId).trigger('change');
                        mutationData.origin = originPartyId;
                    }

                    if (destinationPartyId) {
                        $('#destination').val(destinationPartyId).trigger('change');
                        mutationData.destination = destinationPartyId;
                    }
                } else {
                    // Reset origin dan destination ke default
                    $('#origin').val("{{ $parties->filter(function($p) use ($currentStoreId) { return $p->store_id == $currentStoreId && str_contains($p->tags ?? '', 'Store'); })->first()->id ?? '' }}").trigger('change');
                    mutationData.origin = "{{ $parties->filter(function($p) use ($currentStoreId) { return $p->store_id == $currentStoreId && str_contains($p->tags ?? '', 'Store'); })->first()->id ?? null }}";
                    $('#destination').val(null).trigger('change');
                    mutationData.destination = null;
                }

                // Jika fromIncoming, disable semua button kecuali masuk
                if (fromIncoming) {
                    $('#mutationType .btn').addClass('disabled').attr('aria-disabled', 'true');
                    $('#mutationType .btn[data-method-id="in"]').removeClass('disabled').attr('aria-disabled', 'false').addClass('active').attr('aria-pressed', 'true');
                    mutationData.mutationType = 'in';
                } else {
                    // Enable semua button
                    $('#mutationType .btn').removeClass('disabled').attr('aria-disabled', 'false');
                }

                // Set default mutation type (yang pertama aktif)
                const activeButton = $('#mutationType .btn.active');
                if (activeButton.length > 0) {
                    mutationData.mutationType = activeButton.data('method-id');
                }

                // Set default datetime
                mutationData.dateTime = $('#datetime').val();

                // Update visibility berdasarkan mutation type
                updateDestinationVisibility(fromIncoming);

                // Update mutation data attribute
                updateMutationDataAttribute();
            });

            // Event handler untuk modal hide - destroy select2
            $('#processMutationModal').on('hide.bs.modal', function() {
                $('#origin').select2('destroy');
                $('#destination').select2('destroy');
            });

            // Handler untuk mutation type buttons - hanya satu yang aktif
            $('#mutationType .btn').on('click', function() {
                // Remove active dari semua button
                $('#mutationType .btn').removeClass('active').attr('aria-pressed', 'false');

                // Add active ke button yang diklik
                $(this).addClass('active').attr('aria-pressed', 'true');

                // Update mutation data
                mutationData.mutationType = $(this).data('method-id');

                // Update visibility destination berdasarkan mutation type
                updateDestinationVisibility();

                // Reset destination jika tidak diperlukan
                if (mutationData.mutationType === 'used' || mutationData.mutationType === 'broken') {
                    mutationData.destination = null;
                    $('#destination').val(null).trigger('change');
                }

                // Update mutation data attribute
                updateMutationDataAttribute();
            });

            // Handler untuk origin select change
            $('#origin').on('change', function() {
                mutationData.origin = $(this).val();
                updateMutationDataAttribute();
            });

            // Handler untuk destination select change
            $('#destination').on('change', function() {
                mutationData.destination = $(this).val();
                updateMutationDataAttribute();
            });

            // Handler Checkbox & Text Input Custom
            $('#cbOriginCustom').on('change', function() {
                mutationData.isOriginCustom = $(this).is(':checked');
                if (mutationData.isOriginCustom) {
                    $('#origin_select_container').addClass('d-none');
                    $('#origin_custom_container').removeClass('d-none');
                } else {
                    $('#origin_select_container').removeClass('d-none');
                    $('#origin_custom_container').addClass('d-none');
                }
                updateMutationDataAttribute();
            });

            $('#cbDestinationCustom').on('change', function() {
                mutationData.isDestinationCustom = $(this).is(':checked');
                if (mutationData.isDestinationCustom) {
                    $('#destination_select_container').addClass('d-none');
                    $('#destination_custom_container').removeClass('d-none');
                } else {
                    $('#destination_select_container').removeClass('d-none');
                    $('#destination_custom_container').addClass('d-none');
                }
                updateMutationDataAttribute();
            });

            $('#origin_custom').on('input', function() {
                mutationData.customOrigin = $(this).val();
                updateMutationDataAttribute();
            });

            $('#destination_custom').on('input', function() {
                mutationData.customDestination = $(this).val();
                updateMutationDataAttribute();
            });

            // Handler untuk datetime input change
            $('#datetime').on('change', function() {
                mutationData.dateTime = $(this).val();
                updateMutationDataAttribute();
            });

            // Handler untuk remark textarea change
            $('#remark').on('input', function() {
                mutationData.remark = $(this).val();
                updateMutationDataAttribute();
            });

            // Handler untuk tombol konfirmasi
            $('#btnConfirmMutation').on('click', function() {
                const currentMutationData = $(this).attr('mutation-data');
                const mutationData = currentMutationData ? JSON.parse(currentMutationData) : null;
                if (!mutationData) {
                    showToast('warning', 'Perhatian', 'Mohon lengkapi semua data mutasi yang diperlukan.', 'ri-alert-fill');
                    return;
                }
                
                if (mutationData.mutationType === 'in' || mutationData.mutationType === 'out') {
                    if ((!mutationData.isOriginCustom && !mutationData.origin) || (mutationData.isOriginCustom && !mutationData.customOrigin)) {
                        showToast('warning', 'Perhatian', 'Mohon lengkapi data Asal terlebih dahulu.', 'ri-alert-fill');
                        return;
                    }
                    if ((!mutationData.isDestinationCustom && !mutationData.destination) || (mutationData.isDestinationCustom && !mutationData.customDestination)) {
                        showToast('warning', 'Perhatian', 'Mohon lengkapi data Tujuan terlebih dahulu.', 'ri-alert-fill');
                        return;
                    }
                } else if (mutationData.mutationType === 'used' || mutationData.mutationType === 'broken') {
                    if ((!mutationData.isOriginCustom && !mutationData.origin) || (mutationData.isOriginCustom && !mutationData.customOrigin)) {
                        showToast('warning', 'Perhatian', 'Mohon lengkapi data Asal terlebih dahulu.', 'ri-alert-fill');
                        return;
                    }
                }
                
                if (!mutationData.salePeopleId) {
                    showToast('warning', 'Perhatian', 'Mohon pilih Sales Person.', 'ri-alert-fill');
                    return;
                }


                const payload = {
                    movement_type: mutationData.mutationType,
                    source_party_id: mutationData.origin,
                    destination_party_id: mutationData.destination,
                    is_origin_custom: mutationData.isOriginCustom,
                    custom_origin: mutationData.customOrigin,
                    is_destination_custom: mutationData.isDestinationCustom,
                    custom_destination: mutationData.customDestination,
                    movement_date: mutationData.dateTime,
                    remarks: mutationData.remark,
                    movement_id: mutationData.movementId,
                    sale_people_id: mutationData.salePeopleId
                }

                $.post("{{ route('product-mutation.store') }}", payload)
                    .done(function(response) {
                        // Tutup modal
                        $('#processMutationModal').modal('hide');
                        // Redirect atau refresh halaman jika perlu
                        window.location.reload();
                    })
                    .fail(function(xhr, status, error) {
                        // Gagal membuat mutasi
                        const message = xhr.responseJSON?.message || 'Terjadi kesalahan saat memproses mutasi.';
                        showToast('danger', 'Gagal', message, 'ri-error-warning-fill');
                        // window.location.reload();
                    });
            });

            // Fungsi untuk mengupdate visibility destination
            function updateDestinationVisibility(fromIncoming = false) {
                const mutationType = mutationData.mutationType;
                const sourceRow = $('#origin');
                const destinationRow = $('#destination');
                const cbOrigin = $('#cbOriginCustom');
                const cbDest = $('#cbDestinationCustom');

                switch (mutationType) {
                    case 'in':
                        // disabled destination, enable origin
                        sourceRow.prop('disabled', false);
                        cbOrigin.prop('disabled', false);
                        $('#destination_row').show();
                        destinationRow.prop('disabled', true);
                        cbDest.prop('disabled', true);

                        // isi destination dengan current store
                        destinationRow.val("{{ $parties->filter(function($p) use ($currentStoreId) { return $p->store_id == $currentStoreId && str_contains($p->tags ?? '', 'Store'); })->first()->id ?? '' }}").trigger('change');
                        if (!fromIncoming) {
                            sourceRow.val(null).trigger('change');
                        }
                        break;
                    case 'out':
                        // disabled origin, enable destination
                        sourceRow.prop('disabled', true);
                        cbOrigin.prop('disabled', true);
                        $('#destination_row').show();
                        destinationRow.prop('disabled', false);
                        cbDest.prop('disabled', false);

                        // isi origin dengan current store
                        sourceRow.val("{{ $parties->filter(function($p) use ($currentStoreId) { return $p->store_id == $currentStoreId && str_contains($p->tags ?? '', 'Store'); })->first()->id ?? '' }}").trigger('change');
                        destinationRow.val(null).trigger('change');
                        break;
                    case 'used':
                        // Hide destination
                        $('#destination_row').hide();
                        sourceRow.prop('disabled', true);
                        cbOrigin.prop('disabled', true);

                        // isi origin dengan current store
                        sourceRow.val("{{ $parties->filter(function($p) use ($currentStoreId) { return $p->store_id == $currentStoreId && str_contains($p->tags ?? '', 'Store'); })->first()->id ?? '' }}").trigger('change');
                        destinationRow.val(null).trigger('change');
                        break;
                    case 'broken':
                        // Hide destination
                        $('#destination_row').hide();
                        sourceRow.prop('disabled', true);
                        cbOrigin.prop('disabled', true);

                        // isi origin dengan current store
                        sourceRow.val("{{ $parties->filter(function($p) use ($currentStoreId) { return $p->store_id == $currentStoreId && str_contains($p->tags ?? '', 'Store'); })->first()->id ?? '' }}").trigger('change');
                        destinationRow.val(null).trigger('change');
                        break;
                }
            }

            // Fungsi untuk mengupdate mutation-data attribute
            function updateMutationDataAttribute() {
                const dataToStore = {
                    mutationType: mutationData.mutationType,
                    origin: mutationData.origin,
                    destination: mutationData.destination,
                    isOriginCustom: mutationData.isOriginCustom,
                    customOrigin: mutationData.customOrigin,
                    isDestinationCustom: mutationData.isDestinationCustom,
                    customDestination: mutationData.customDestination,
                    dateTime: mutationData.dateTime,
                    remark: mutationData.remark,
                    movementId: mutationData.movementId,
                    salePeopleId: mutationData.salePeopleId
                };

                $('#btnConfirmMutation').attr('mutation-data', JSON.stringify(dataToStore));
            }

            // Handler untuk dropdown Sales Person
            $('#sale_people_id').on('change', function() {
                mutationData.salePeopleId = $(this).val();
                updateMutationDataAttribute();
            });
        });
    </script>
@endpush
