<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content p-3">
            <!-- Begin modal header -->
            <div class="modal-header py-2">
                <h3 class="modal-title fw-semibold" id="paymentModalLabel">Pembayaran</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <hr class="mx-n3 border border-primary-subtle opacity-100">

            <!-- Begin modal body -->
            <div class="modal-body py-2">

                <!-- Begin payment method -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Metode Pembayaran</label>
                            <small class="text-muted">Pilih salah satu</small>
                        </div>

                        <div class="row g-2 justify-content-start" id="paymentMethodGroup">
                            @forelse(($payment_types ?? []) as $i => $pt)
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                <button
                                    type="button"
                                    class="btn btn-outline-primary w-100 rounded-3 py-3 text-start overflow-hidden {{ $i === 0 ? 'active' : '' }}"
                                    data-bs-toggle="button"
                                    aria-pressed="{{ $i === 0 ? 'true' : 'false' }}"
                                    data-method="{{ Str::slug($pt->payment_type) }}"
                                    data-method-id="{{ $pt->id }}">
                                    <div class="fw-semibold text-truncate">{{ $pt->payment_type }}</div>
                                    <div class="small text-muted text-truncate">{{ $pt->notes ?? '—' }}</div>
                                </button>
                            </div>
                            @empty
                            <div class="col-12">
                                <div class="alert alert-warning mb-0">Belum ada metode pembayaran. Tambahkan di menu Payment Types.</div>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Begin Sales Person -->
                <div class="row mb-4">
                    <div class="col-12">
                        <label for="salesPerson" class="form-label mb-1">
                            Sales Person <span class="text-danger">*</span>
                        </label>
                        <select id="salesPerson" class="form-select" aria-label="Pilih Sales Person">
                            <option value="" selected hidden>Pilih Sales Person…</option>
                            @foreach(($sale_people ?? []) as $sp)
                            <option
                                value="{{ $sp->id }}"
                                data-note="{{ $sp->note }}"
                                title="{{ $sp->note }}">{{ $sp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Begin Customer -->
                <div class="row mb-4">
                    <div class="col-12">
                        <label for="customerSelect" class="form-label mb-1">
                            Customer <span class="text-muted">(opsional)</span>
                        </label>
                        <select id="customerSelect" class="form-select" aria-label="Pilih Customer">
                            <option value="" selected hidden>Pilih Customer…</option>
                            @foreach(($customers ?? []) as $c)
                                @php
                                    $fullName = trim(($c->first_name ?? '').' '.($c->last_name ?? ''));
                                    $note = implode(' • ', array_filter([$c->email ?? '', $c->phone ?? '']));
                                @endphp
                                <option
                                    value="{{ $c->id }}"
                                    data-note="{{ $note }}"
                                    title="{{ $note }}">{{ $fullName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Begin subtotal -->
                <div class="row mb-4">
                    <div class="col-12">
                        <label for="paySubtotalDisplayRow" class="form-label mb-1">
                            Subtotal <span class="text-muted">(0 item)</span>
                        </label>

                        <div class="input-group">
                            <span class="input-group-text" id="">Rp</span>
                            <input
                                type="text"
                                class="form-control text-start fw-semibold bg-light"
                                id="paySubtotalDisplayRow"
                                value="0"
                                inputmode="numeric"
                                aria-describedby=""
                                readonly>
                        </div>
                    </div>
                </div>

                <!-- Begin diskon -->
                <div class="row mb-4">
                    <!-- Diskon (%) -->
                    <div class="col-6">
                        <label for="discountInputPercent" class="form-label mb-1">Diskon (%)</label>

                        <div class="input-group">
                            <span class="input-group-text" id="">% </span>
                            <input
                                type="text"
                                name="discount_percent"
                                id="discountInputPercent"
                                class="form-control"
                                placeholder="Diskon dalam Persentase"
                                inputmode="numeric"
                                aria-describedby=""
                                min="0"
                                max="100"
                                step="1"
                                autocomplete="off">
                        </div>
                    </div>

                    <!-- Diskon (Rp) -->
                    <div class="col-6">
                        <label for="discountInputNominal" class="form-label mb-1">Diskon (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text" id="">Rp</span>
                            <input
                                type="text"
                                name="discount_nominal"
                                id="discountInputNominal"
                                class="form-control"
                                placeholder="Diskon dalam Rupiah"
                                inputmode="numeric"
                                aria-describedby=""
                                min="0"
                                step="1"
                                autocomplete="off">
                        </div>
                    </div>
                </div>

                <!-- Begin total -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="bg-light rounded-3 p-3">
                            <div class="d-flex align-items-center flex-nowrap">
                                <span class="fw-bold fs-4 text-capitalize">Total</span>
                                <hr class="flex-grow-1 mx-3 border-1" style="border: 1px dashed;" aria-hidden="true">
                                <span class="fw-bold fs-4 text-truncate d-block" id="payTotalDisplayRow" aria-live="polite">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Begin additional cost -->
                <div class="row mb-4">
                    <div class="col-12">
                        <label for="additionalCostInput" class="form-label mb-1">
                            Biaya Tambahan <span class="text-muted">(opsional)</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input
                                type="text"
                                name="additional_cost"
                                id="additionalCostInput"
                                class="form-control"
                                placeholder="Biaya Tambahan"
                                inputmode="numeric"
                                min="0"
                                step="1"
                                autocomplete="off">
                        </div>
                        <div class="form-text">Mis. ongkir/biaya layanan.</div>
                    </div>
                </div>

                <!-- Begin Pembayaran -->
                <div class="row">
                    <!-- Dibayarkan (Rp) -->
                    <div class="col-6">
                        <label for="payAmountInput" class="form-label mb-1">Dibayarkan</label>

                        <div class="input-group">
                            <span class="input-group-text" id="">Rp</span>
                            <input
                                type="text"
                                name="amount_paid"
                                id="payAmountInput"
                                class="form-control"
                                placeholder="0"
                                inputmode="numeric"
                                min="0"
                                value="0"
                                aria-describedby=""
                                autocomplete="off">
                        </div>
                    </div>

                    <!-- Kembalian (Rp) -->
                    <div class="col-6">
                        <label for="payChangeDisplay" class="form-label mb-1">Kembalian</label>
                        <div class="input-group">
                            <span class="input-group-text" id="">Rp</span>
                            <input
                                type="text"
                                id="payChangeDisplay"
                                class="form-control bg-light fw-semibold"
                                placeholder="0"
                                inputmode="numeric"
                                min="0"
                                value="0"
                                aria-describedby=""
                                autocomplete="off"
                                readonly>
                        </div>
                    </div>
                </div>

                <!-- Begin Remark/Catatan -->
                <div class="row">
                    <div class="col-12">
                        <label for="remark" class="form-label mb-1">Remark/Catatan</label>
                        <textarea
                            id="remark"
                            name="remark"
                            class="form-control"
                            rows="3"
                            placeholder="Tambahkan catatan (opsional)"
                            aria-describedby="remarkHelp"
                            maxlength="200"
                            spellcheck="true"></textarea>
                        <div id="remarkHelp" class="form-text">Maks. 200 karakter.</div>
                    </div>
                </div>

            </div>

            <!-- Begin modal footer -->
            <div class="modal-footer px-3">
                <div class="d-flex flex-column flex-md-row w-100 align-items-stretch gap-3">
                    <button
                        type="button"
                        class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2 py-2 px-3"
                        style="min-width:150px; min-height:50px;"
                        data-bs-dismiss="modal"
                        aria-label="Tutup modal dan jadikan draft">
                        <i class="ri-file-list-3-line fs-5" aria-hidden="true"></i>
                        <span>Jadikan Draft</span>
                    </button>

                    <button
                        type="button"
                        id="btnConfirmPayment"
                        class="btn btn-primary d-flex align-items-center justify-content-center gap-2 py-2 flex-fill"
                        style="min-width: 150px; min-height: 50px;">
                        <i class="ri-bank-card-line fs-5" aria-hidden="true"></i>
                        <span class="text-truncate">Proses Pembayaran</span>
                    </button>
                </div>
            </div>
            <!-- End modal footer -->



        </div>
    </div>
</div>


@push('scripts-payment-modal')

<script>
    /**
     * ====
     * Main Script Payment Modal
     * ====
     */

    (function() {
        const URL_LIST = "{{ route('sales-transaction.cart.list') }}";

        // Elemen utama
        const $subtotalField = $('#paySubtotalDisplayRow');
        const $totalField = $('#payTotalDisplayRow');
        const $discountPct = $('#discountInputPercent');
        const $discountNom = $('#discountInputNominal');
        const $additionalCost = $('#additionalCostInput');
        const $amountPaid = $('#payAmountInput');
        const $changeField = $('#payChangeDisplay');
        const $confirmBtn = $('#btnConfirmPayment');
        const $changeRow = $changeField.closest('.row');
        const $methodGroup = $('#paymentMethodGroup');

        // State
        const state = {
            subtotal: 0,
            discPct: 0,
            discNom: 0,
            addCost: 0, // biaya tambahan
        };

        // Utils
        const toInt = v => (v = String(v ?? '').replace(/[^\d]/g, '')) ? parseInt(v, 10) : 0;
        const idr = n => Number(n || 0).toLocaleString('id-ID');
        const rp = n => 'Rp ' + idr(n);
        const setNumericField = $el => {
            const n = toInt($el.val());
            $el.val(idr(n));
            return n;
        };

        // Underpaid note
        let $underpaidNote;

        function ensureUnderpaidNote() {
            if (!$underpaidNote || !$underpaidNote.length) {
                $underpaidNote = $('<div id="payUnderpaidNote" class="form-text text-danger mt-1 d-none"></div>');
                $changeField.closest('.input-group').after($underpaidNote);
            }
        }

        function setItemCount(units) {
            const $span = $('label[for="paySubtotalDisplayRow"]').find('.text-muted');
            if ($span.length) $span.text(`(${Number(units || 0)} item)`);
        }

        // --- Pembayaran: single-select ---
        function getSelectedPayment() {
            const $active = $methodGroup.find('[data-method-id].active').first();
            return {
                id: $active.data('methodId') || null,
                code: String($active.data('method') || '')
            };
        }

        function activateButton($btn) {
            const $all = $methodGroup.find('[data-method-id]');
            $all.removeClass('active').attr('aria-pressed', 'false');
            $btn.addClass('active').attr('aria-pressed', 'true');
        }

        $methodGroup.on('click', '[data-method-id]', function() {
            activateButton($(this));
            recalc();
        });

        // --- Hitung & simpan ke data(payment_calc) ---
        function recalc() {
            const discFromPct = Math.floor(state.subtotal * Math.min(100, Math.max(0, state.discPct)) / 100);
            const discFromNom = Math.max(0, state.discNom);
            const discount = Math.min(state.subtotal, discFromPct + discFromNom);

            const addCost = Math.max(0, state.addCost); // biaya tambahan
            const total   = Math.max(0, state.subtotal - discount + addCost);
            const paid = toInt($amountPaid.val());
            const change = Math.max(0, paid - total);
            const deficit = total - paid;

            $totalField.text(rp(total));
            $changeField.val(idr(change));

            ensureUnderpaidNote();
            if (deficit > 0) {
                $underpaidNote.text('Jumlah dibayarkan kurang ' + rp(deficit)).removeClass('d-none');
                $changeRow.removeClass('mb-3').addClass('mb-2');
            } else {
                $underpaidNote.addClass('d-none').text('');
                $changeRow.removeClass('mb-2').addClass('mb-3');
            }

            const pay = getSelectedPayment();

            // validasi tombol 'submit payment'
            const canSubmit = deficit <= 0 && !!pay.id; // harus cukup bayar & ada metode pembayaran
            $confirmBtn
                .prop('disabled', !canSubmit)
                .toggleClass('disabled', !canSubmit)
                .attr('aria-disabled', !canSubmit)
                .attr('title', canSubmit ? '' : (deficit > 0 ? ('Kurang ' + rp(deficit)) : 'Pilih metode pembayaran'));

            // simpan kalkulasi utk dipakai saat submit
            $confirmBtn.data('payment_calc', {
                subtotal: state.subtotal,
                discount_percent: discFromPct,
                discount_nominal: discFromNom,
                discount_total: discount,
                additional_cost: addCost, // biaya tambahan
                total,
                paid,
                change,
                payment_type_id: pay.id,
                payment_type_code: pay.code,
                sales_person_id: $('#salesPerson').val() || null,
                customer_id: $('#customerSelect').val() || null,
                remark: $('#remark').val() || ''
            });
            $confirmBtn.data('can_submit', canSubmit); // flag
        }


        // --- Events ---
        $(function() {
            // mask input 'diskon persen' (only numeric)
            if ($.fn.mask) $('#discountInputPercent').mask('000');

            // Diskon & pembayaran
            $discountPct.on('input', () => {
                const v = parseInt($discountPct.val(), 10);
                state.discPct = Number.isFinite(v) ? Math.min(100, Math.max(0, v)) : 0;
                recalc();
            });
            $discountNom.on('input', () => {
                state.discNom = setNumericField($discountNom);
                recalc();
            });
            $additionalCost.on('input', () => {
                state.addCost = setNumericField($additionalCost);
                recalc();
            });
            $amountPaid.on('input', () => {
                setNumericField($amountPaid);
                recalc();
            });

            // Modal lifecycle
            $('#paymentModal').on('show.bs.modal', function() {
                ensureUnderpaidNote();

                // Reset field
                $discountPct.val('');
                $discountNom.val('');
                $additionalCost.val('');
                $amountPaid.val('0');
                $changeField.val(idr(0));
                $underpaidNote.addClass('d-none').text('');
                state.discPct = 0;
                state.discNom = 0;
                state.addCost = 0;  // biaya tambahan

                // Pastikan ada satu metode aktif
                const $active = $methodGroup.find('[data-method-id].active');
                if (!$active.length) {
                    const $first = $methodGroup.find('[data-method-id]').first();
                    if ($first.length) activateButton($first);
                }

                // Ambil subtotal + units
                $.getJSON(URL_LIST)
                    .done(res => {
                        const st = res?.summary?.subtotal ?? 0;
                        const units = res?.summary?.units ?? 0;
                        state.subtotal = Math.max(0, Number(st || 0));
                        $subtotalField.val(idr(state.subtotal));
                        setItemCount(units);
                        recalc();
                    })
                    .fail(() => {
                        const domSubtotal = toInt($('#cartSubtotal').text());
                        const unitsMatch = ($('#cartSummaryText').text() || '').match(/Total\s+(\d+)\s+item/i);
                        state.subtotal = Math.max(0, Number(domSubtotal || 0));
                        $subtotalField.val(idr(state.subtotal));
                        setItemCount(unitsMatch ? Number(unitsMatch[1]) : 0);
                        recalc();
                    });
            });

        });
    })();
</script>

<script>
    /**
     * ====
     * Submit Data 'Payment Modal' ke controller
     * ====
     */
    $(function() {
        const URL_STORE = "{{ route('sales-transaction.store') }}";

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        const toInt = v => (v = String(v ?? '').replace(/[^\d]/g, '')) ? parseInt(v, 10) : 0;

        $('#btnConfirmPayment').on('click', function() {
            const $btn = $(this);
            const calc = $btn.data('payment_calc') || {};

            if (!$btn.data('can_submit') || (calc.paid || 0) < (calc.total || 0)) {
                return;
            }

            const $activePay = $('#paymentMethodGroup [data-method-id].active').first();
            const payload = {
                discount_percent: toInt($('#discountInputPercent').val()) || 0,
                discount_nominal: toInt($('#discountInputNominal').val()) || 0,
                additional_cost: toInt($('#additionalCostInput').val()) || 0,
                payment_type: $activePay.data('methodId') || '',
                sales_person_id: $('#salesPerson').val() || '',
                customer_id: $('#customerSelect').val() || '',
                amount_paid: toInt($('#payAmountInput').val()) || 0,
                remark: ($('#remark').val() || '').trim()
            };

            if (!payload.sales_person_id) {
                if (typeof Toastify === 'function') {
                    Toastify({
                        text: "Sales Person wajib dipilih!",
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        stopOnFocus: true,
                        className: "bg-warning",
                    }).showToast();
                } else {
                    alert('Sales Person wajib dipilih!');
                }
                return;
            }

            $.post(URL_STORE, payload)
                .done(res => {
                    if (res?.ok && res.redirect) {
                        window.location = res.redirect; // sukses -> redirect
                    } else {
                        window.location.reload();
                    }
                })
                .fail(() => {
                    window.location.reload();
                });
        });
    });
</script>

<script>
    $(function() {
        const $modal = $('#paymentModal');

        const escapeHtml = (s) =>
            (s || '').toString().replace(/[&<>"']/g, (m) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            } [m]));

        /** Init Select2 dengan badge + search (text + data-note) */
        function initSelect2Badge($select, {
            placeholder,
            searchNote = true
        } = {}) {
            if (!$.fn.select2 || !$select.length) return;

            $select.select2({
                width: '100%',
                minimumResultsForSearch: 0,
                dropdownParent: $modal, // penting: agar fokus/ketik jalan di dalam modal
                allowClear: false,
                placeholder: placeholder || $select.attr('placeholder') || 'Pilih…',
                // Cari di nama (text) + data-note (email/phone/catatan)
                matcher: function(params, data) {
                    const term = (params.term || '').toLowerCase().trim();
                    if (!term) return data;
                    if (!data.id) return null;
                    const text = (data.text || '').toLowerCase();
                    const note = (data.element?.dataset?.note || '').toLowerCase();
                    return (text + ' ' + (searchNote ? note : '')).includes(term) ? data : null;
                },
                templateResult: function(d) {
                    if (!d.id) return d.text || '';
                    const note = d.element?.dataset?.note || '';
                    const name = d.text || '';
                    return `
                        <div class="d-flex flex-column">
                            <span>${escapeHtml(name)}</span>
                            ${note ? `<span class="text-muted small">${escapeHtml(note)}</span>` : ''}
                        </div>
                    `;
                },
                templateSelection: ({
                    id,
                    text
                }) => {
                    if (!id) return text || '';
                    const name = String(text || '').split('—')[0].trim();
                    const target = `#${$select.attr('id')}`;
                    return $(`
                        <span class="badge text-bg-primary rounded d-inline-flex align-items-center gap-2 px-3 py-2 fs-6">
                            <span class="text-truncate">${escapeHtml(name)}</span>
                            <span class="vr opacity-75"></span>
                            <button type="button"
                                    class="p-0 m-0 text-white text-decoration-none bg-transparent border-0"
                                    data-action="clear-select" data-target="${target}"
                                    aria-label="Hapus">&times;</button>
                        </span>
                    `);
                },
                escapeMarkup: (m) => m
            });

            const $container = $select.data('select2').$container;

            function syncSelectionBox() {
                const hasValue = !!$select.val();
                const $box = $container.find('.select2-selection');
                const $rendered = $container.find('.select2-selection__rendered');
                const $arrow = $container.find('.select2-selection__arrow');
                if (hasValue) {
                    $box.addClass('d-flex align-items-center justify-content-start py-2 px-3')
                        .css({
                            height: 'auto',
                            minHeight: 0
                        });
                    $rendered.addClass('w-100 d-flex align-items-center justify-content-start text-start p-0');
                    $arrow.css({
                        top: '50%',
                        transform: 'translateY(-50%)'
                    });
                } else {
                    $box.removeClass('d-flex align-items-center justify-content-start py-2 px-3')
                        .css({
                            height: '',
                            minHeight: ''
                        });
                    $rendered.removeClass('w-100 d-flex align-items-center justify-content-start text-start p-0');
                    $arrow.css({
                        top: '',
                        transform: ''
                    });
                }
            }

            // Init + update
            syncSelectionBox();
            $select.on('change select2:open select2:close', syncSelectionBox);

            // Fokuskan field pencarian saat dropdown dibuka
            $select.on('select2:open', () => {
                document.querySelector('.select2-container--open .select2-search__field')?.focus();
            });

            // Clear pilihan via tombol × pada badge (delegasi)
            $container.on('mousedown click', '[data-action="clear-select"]', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const target = $(this).data('target');
                const $t = $(target);
                if ($t.length) {
                    $t.val(null).trigger('change');
                    $t.select2('close');
                    $t.data('select2').$container.find('.select2-selection__rendered').blur();
                }
            });
        }

        // Terapkan ke keduanya: Sales Person & Customer
        initSelect2Badge($('#salesPerson'), {
            placeholder: 'Pilih Sales Person…',
            searchNote: true
        });
        initSelect2Badge($('#customerSelect'), {
            placeholder: 'Pilih Customer…',
            searchNote: true
        });
    });
</script>

@endpush