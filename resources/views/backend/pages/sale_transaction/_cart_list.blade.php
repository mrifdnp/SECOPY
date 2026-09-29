<div class="card w-100 h-100 d-flex flex-column">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <span class="card-title fw-semibold fs-4 m-0">Daftar Pesanan</span>
            <div class="form-check form-switch m-0">
                <input class="form-check-input" type="checkbox" id="chkPaperbag" role="switch">
                <label class="form-check-label small" for="chkPaperbag">Pakai Paperbag</label>
            </div>
        </div>
        <div class="mt-2">
            <span class="badge bg-primary-subtle border border-primary-subtle fs-6">
                <i class="me-1"></i>{{ session('store.name', 'Cabang Belum Dipilih') }}
            </span>
        </div>
    </div>

    <!-- Begin List Cart -->
    <div class="card-body flex-grow-1 overflow-auto">
        <div id="cartList" class="vstack gap-2">
            <!-- Inject Data From Js -->
            <div class="text-center text-muted py-4">Belum ada produk yang dipilih.</div>
        </div>
    </div>

    <!-- Begin footer -->
    <div class="card-footer">
        <div class="border border-secondary-subtle rounded-3 mb-3 p-3 bg-light">
            <div class="mb-2">
                <span class="fw-semibold">Rincian Pesanan</span>
            </div>

            <div class="d-flex flex-column flex-sm-row justify-content-between gap-1">
                <span class="text-muted">Subtotal</span>
                <span class="fw-semibold" id="cartSubtotal">Rp 0</span>
            </div>

            <div class="d-flex flex-column flex-sm-row justify-content-between gap-1">
                <span class="text-muted">Diskon</span>
                <span class="fw-semibold" id="cartDiscount">- Rp 0</span>
            </div>

            <hr class="my-2 border-1" style="border: 1px dashed;">

            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center fw-semibold gap-1">
                <div class="d-flex align-items-center flex-wrap gap-1 lh-1">
                    <span id="cartSummaryText">Total 0 item • 0 Produk</span>
                </div>
                <span class="fw-semibold" id="cartTotalPay">Rp 0</span>
            </div>
        </div>

        <div class="d-grid overflow-hidden">
            <button
                id="btnOpenPayment"
                type="button"
                class="btn btn-primary d-flex align-items-center justify-content-center gap-2 lh-1 py-2 py-sm-3 px-3 text-nowrap"
                aria-label="Proses Pembayaran"
                data-bs-toggle="modal"
                data-bs-target="#paymentModal"
                disabled>
                <span class="d-none d-sm-inline fs-5">Proses Pembayaran</span>
                <span class="d-inline d-sm-none">Pembayaran</span>
                <i class="ri-bank-card-line fs-5" aria-hidden="true"></i>
            </button>
        </div>
    </div>

</div>

@push('scripts-cart-list')

<script>
    $(() => {
        // URL Ajax
        const URL_ADD = "{{ route('sales-transaction.cart.add') }}";
        const URL_LIST = "{{ route('sales-transaction.cart.list') }}";
        const URL_UPD = "{{ route('sales-transaction.cart.update') }}";
        const URL_DEL = "{{ route('sales-transaction.cart.remove') }}";

        // CSRF
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Formatter & cache elemen cart
        const rp = n => 'Rp ' + Number(n || 0).toLocaleString('id-ID');
        const $cartWrap = $('#cartList'); // container list item
        const $cartSub = $('#cartSubtotal'); // subtotal text
        const $cartSumRow = $('#cartSummaryText'); // "Total X item • Y Produk"
        const $cartDisc = $('#cartDiscount');

        // ===== Helper discount =====
        const hasDisc = l => Number(l?.original_price || 0) > 0 && Number(l?.final_price || l?.unit_price || 0) < Number(l?.original_price || 0);
        const discBadge = l => {
            if (!hasDisc(l)) return '';
            if (String(l?.best_discount_type) === 'percent' && l?.raw_percent_value != null) {
                return `<span class="badge bg-danger">-${String(l.raw_percent_value).replace('.', ',')}%</span>`;
            }
            return `<span class="badge bg-danger">-${rp(l.best_discount_value||0)}</span>`;
        };

        // ===== Render Cart (dari response JSON) =====
        function renderCart(res) {
            const lines = (res && res.lines) || [];
            if (!lines.length) {
                $cartWrap.html('<div class="text-center text-muted py-4">Belum ada produk yang dipilih.</div>');
            } else {
                let html = '';
                lines.forEach(l => {
                    const unitFinal = Number(l.final_price ?? l.unit_price ?? 0) || 0;
                    const unitOrig = Number(l.original_price ?? unitFinal) || 0;
                    const isDisc = hasDisc(l);
                    const lineTotal = unitFinal * (Number(l.qty) || 0);

                    html += `
                        <div class="p-3 border border-2 border-secondary-subtle rounded-3 overflow-hidden" data-id="${l.id}">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold fs-5 text-truncate">${l.title}</span>
                                <div class="d-flex align-items-center gap-2">
                                    ${discBadge(l)}
                                    <button class="btn btn-sm btn-outline-transparent text-danger btn-cart-remove" data-id="${l.id}">Remove</button>
                                </div>
                            </div>

                            <div class="d-flex gap-4 mt-2 flex-nowrap">
                                <div class="d-flex flex-column overflow-hidden" style="min-width:0">
                                    <span class="text-muted d-block text-truncate">Varian:</span>
                                    <span class="fw-semibold fs-6 text-primary text-capitalize d-block text-truncate">${l.variant_name || '—'}</span>
                                </div>
                                <div class="d-flex flex-column overflow-hidden" style="min-width:0">
                                    <span class="text-muted d-block text-truncate">Warna:</span>
                                    <span class="fw-semibold fs-6 text-primary text-capitalize d-block text-truncate">${l.color_label || '—'}</span>
                                </div>
                                <div class="d-flex flex-column overflow-hidden" style="min-width:0">
                                    <span class="text-muted d-block text-truncate">SKU:</span>
                                    <span class="fw-semibold fs-6 d-block text-truncate">${l.sku || '—'}</span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center gap-3 mt-3">
                                <div>
                                    <span class="fw-semibold fs-6">${l.qty}x</span>
                                    ${
                                        isDisc
                                        ? `<span class="text-muted ms-2 text-decoration-line-through">${rp(unitOrig)}</span>
                                        <span class="ms-2 fw-semibold text-primary">${rp(unitFinal)}</span>`
                                        : `<span class="text-muted ms-2">${rp(unitFinal)}</span>`
                                    }
                                </div>

                                <div class="d-flex align-items-center" style="max-width: 110px;">
                                    <div class="input-group input-group-sm flex-nowrap w-100" role="group" aria-label="Qty">
                                        <button type="button" class="btn btn-primary btn-q-minus" data-id="${l.id}" aria-label="Kurangi">−</button>
                                        <input type="number" class="form-control text-center w-auto flex-grow-0 flex-shrink-1 px-1 cart-qty-input"
                                            min="1" step="1" value="${l.qty}" data-id="${l.id}" aria-label="Jumlah">
                                        <button type="button" class="btn btn-primary btn-q-plus" data-id="${l.id}" aria-label="Tambah">+</button>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-3 mx-n3 border-top border-2 border-secondary-subtle opacity-100">

                            <div class="d-flex flex-column">
                                <span class="text-muted">Harga Akhir:</span>
                                <span class="fw-semibold fs-5">${rp(lineTotal)}</span>
                            </div>
                        </div>`;
                });

                $cartWrap.html(html);
            }

            // ===== Ringkasan: gunakan server kalau ada, fallback ke hitung client =====
            let clientGross = 0;
            let clientNet = 0;

            lines.forEach(l => {
                const qty = Number(l.qty) || 0;
                const unitFinal = Number(l.final_price ?? l.unit_price ?? 0) || 0; // harga setelah diskon
                const unitOrig = Number(l.original_price ?? unitFinal) || 0; // harga asli (fallback ke final jika null)
                clientNet += unitFinal * qty;
                clientGross += (unitOrig > 0 ? unitOrig : unitFinal) * qty;
            });

            const s = (res && res.summary) || {};

            // Ambil dari server bila tersedia; kalau tidak, pakai hasil perhitungan client
            const gross = (typeof s.gross === 'number') ? s.gross : clientGross;
            const net = (typeof s.net === 'number') ? s.net : (typeof s.subtotal === 'number' ? s.subtotal : clientNet);
            const discountTot = (typeof s.discount_total === 'number') ? s.discount_total : Math.max(gross - net, 0);

            // Tampilkan ke UI
            $cartSub.text(rp(gross));
            if ($('#cartTotalPay').length) $('#cartTotalPay').text(rp(net));
            if ($('#cartDiscount').length) $('#cartDiscount').text(`- ${rp(discountTot)}`);

            // Teks jumlah item & produk tetap pakai server kalau ada
            const units = (typeof s.units === 'number') ? s.units : lines.reduce((a, l) => a + (Number(l.qty) || 0), 0);
            const products = (typeof s.products === 'number') ? s.products : lines.length;
            $cartSumRow.text(`Total ${units} item • ${products} Produk`);

            // ===== Disable/Enable tombol 'Proses Pembayaran' bila net > 0 =====
            $('#btnOpenPayment').prop('disabled', !(lines.length > 0 && net > 0));

        }

        // ===== Ambil Cart awal saat halaman create dibuka =====
        function fetchCart() {
            $.getJSON(URL_LIST).done(renderCart);
        }
        window.refreshCartList = fetchCart;
        fetchCart();

        // ======= Tambah dari Modal =======
        // Catatan: fungsi-fungsi di script modal sudah punya detailItems, getVariantId, getColorId, pickBestDetail, dst.
        // Cukup akses dari scope yang sama, atau duplikasi kecil jika diperlukan.
        $('#btnAddCart').on('click', function() {
            // Ambil kombinasi aktif dari modal:
            const vId = $('input[name="variant_size"]:checked').val() || '';
            const cId = String($('input[name="variant_color"]:checked').data('colorId') || '');
            // Cari item detail aktif (detailItems didefinisikan di script modal add to cart):
            const pick = (arr, v, c) =>
                arr.find(x => String(x.variant_id) === String(v) && String(x.color_id) === String(c)) ||
                arr.find(x => String(x.variant_id) === String(v)) ||
                arr.find(x => String(x.color_id) === String(c)) ||
                arr[0] || null;

            const current = (window.detailItems || window.detail_items || []).length ?
                pick((window.detailItems || window.detail_items), vId, cId) :
                null;

            if (!current || !current.product_detail_id) {
                return;
            }

            const qty = Math.max(1, parseInt($('#qtyInput').val(), 10) || 1);

            $.post(URL_ADD, {
                    product_detail_id: current.product_detail_id,
                    qty
                })
                .done(res => {
                    renderCart(res);
                    $('#productModal').modal('hide');
                });
        });

        // ======= Aksi Qty (+/-) & input langsung =======
        $(document).on('click', '.btn-q-minus, .btn-q-plus', function() {
            const id = $(this).data('id');
            const $in = $(`.cart-qty-input[data-id="${id}"]`);
            let v = Math.max(1, parseInt($in.val(), 10) || 1);
            v += $(this).hasClass('btn-q-plus') ? 1 : -1;
            v = Math.max(1, v);
            $.post(URL_UPD, {
                product_detail_id: id,
                qty: v
            }).done(renderCart);
        });

        $(document).on('input', '.cart-qty-input', function() {
            const id = $(this).data('id');
            const v = Math.max(1, parseInt($(this).val(), 10) || 1);
            $.post(URL_UPD, {
                product_detail_id: id,
                qty: v
            }).done(renderCart);
        });

        // ======= Remove line / item cart =======
        let paperbagDetailId = null; // akan diisi dari endpoint
        $(document).on('click', '.btn-cart-remove', function() {
            const id = $(this).data('id');
            $.post(URL_DEL, {
                product_detail_id: id
            }).done(res => {
                renderCart(res);
                // Jika yang dihapus adalah paperbag, uncheck checkbox
                if (paperbagDetailId && id === paperbagDetailId) {
                    $('#chkPaperbag').prop('checked', false);
                }
            });
        });

        // ======= Checkbox Paperbag (dinamis per store) =======
        const URL_PAPERBAG = "{{ route('sales-transaction.cart.paperbag-detail') }}";
        $('#chkPaperbag').on('change', function() {
            const $chk = $(this);
            if ($chk.is(':checked')) {
                // Step 1: ambil product_detail_id paperbag sesuai store aktif
                $.getJSON(URL_PAPERBAG)
                .done(function(data) {
                    paperbagDetailId = data.product_detail_id;
                    // Step 2: add ke cart
                    $.post(URL_ADD, {
                        product_detail_id: paperbagDetailId,
                        qty: 1
                    }).done(renderCart)
                    .fail(() => {
                        $chk.prop('checked', false);
                        alert('Gagal menambahkan paperbag');
                    });
                })
                .fail(function(xhr) {
                    $chk.prop('checked', false);
                    let msg = 'Paperbag tidak tersedia';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    alert(msg);
                });
            } else {
                if (paperbagDetailId) {
                    $.post(URL_DEL, {
                        product_detail_id: paperbagDetailId
                    }).done(renderCart);
                }
            }
        });
    });
</script>

@endpush