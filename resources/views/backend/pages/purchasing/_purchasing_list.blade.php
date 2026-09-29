<div class="card w-100 h-100 d-flex flex-column">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between">
            <span class="card-title fw-semibold fs-4 m-0">Daftar Pembelian</span>
            <div id="mutation-list-button-container">
                <button id="incoming-product-button" type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#incomingProductModal">
                    <i class="ri-clockwise-2-fill me-0 me-sm-2"></i>
                    <span class="fw-medium text-nowrap d-none d-md-inline">Barang Masuk</span>
                </button>
            </div>
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
        <div class="d-grid overflow-hidden">
            <button id="btnOpenProcessMutation" type="button" class="btn btn-primary d-flex align-items-center justify-content-center gap-2 lh-1 py-2 py-sm-3 px-3 text-nowrap" aria-label="Proses Pembayaran" data-bs-toggle="modal" data-bs-target="#processMutationModal" disabled>
                <span class="d-none d-sm-inline fs-5">Proses Pembelian</span>
                <span class="d-inline d-sm-none">Pembelian</span>
                <i class="ri-drag-move-2-fill fs-5" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</div>

@push('script-purchasing-list')
    <script>
        $(() => {
            // URL Ajax
            const URL_ADD = "{{ route('purchasing-transaction.list.store') }}";
            const URL_LIST = "{{ route('purchasing-transaction.list') }}";
            const URL_UPD = "{{ route('purchasing-transaction.list.update') }}";
            const URL_DEL = "{{ route('purchasing-transaction.list.delete') }}";

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

            // ===== Render Cart (dari response JSON) =====
            function renderCart(res) {
                const lines = (res && res.lines) || [];
                if (!lines.length) {
                    $cartWrap.html('<div class="text-center text-muted py-4">Belum ada produk yang dipilih.</div>');
                } else {
                    let html = '';
                    let totalCogm = 0;
                    let totalPrice = 0;
                    let totalItem = 0;
                    
                    lines.forEach(l => {
                        const qty = Number(l.qty) || 0;
                        const lineTotal = (Number(l.unit_price) || 0) * qty;
                        totalCogm += (Number(l.cogm) || 0) * qty;
                        totalPrice += (Number(l.unit_price) || 0) * qty;
                        totalItem += qty;
                        html += `
                        <div class="p-3 border border-2 border-secondary-subtle rounded-3 overflow-hidden" data-id="${l.id}">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold fs-5 text-truncate">${l.title}</span>
                                <button class="btn btn-sm btn-outline-transparent text-danger btn-cart-remove" data-id="${l.id}">Remove</button>
                            </div>

                            <div class="d-flex gap-4 mt-2">
                                <div class="d-flex flex-column">
                                    <span class="text-muted">Varian:</span>
                                    <span class="fw-semibold fs-6 text-primary text-capitalize">${l.variant_name || '—'}</span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-muted">Warna:</span>
                                    <span class="fw-semibold fs-6 text-primary text-capitalize">${l.color_label || '—'}</span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-muted">SKU:</span>
                                    <span class="fw-semibold fs-6">${l.sku || '—'}</span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-muted">COGM:</span>
                                    <span class="fw-semibold fs-6">${rp(l.cogm)}</span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-muted">Harga:</span>
                                    <span class="fw-semibold fs-6">${rp(l.unit_price)}</span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center gap-2 mt-3">
                                <div>
                                    <span class="fw-semibold fs-6">${l.qty}x</span>
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
                        </div>`;
                    });

                    $cartWrap.html(html);
                    
                    $('#modalSummaryTotalItem').text(totalItem + ' Pcs');
                    $('#modalSummaryTotalCOGM').text(rp(totalCogm));
                    $('#modalSummaryTotalHarga').text(rp(totalPrice));
                }

                // ===== Disable/Enable tombol 'Proses Pembayaran' =====
                $('#btnOpenProcessMutation').prop('disabled', !(lines.length > 0));
            }

            // ===== Ambil Cart awal saat halaman create dibuka =====
            window.fetchCart = function() {
                $.getJSON(URL_LIST).done(renderCart);
            }

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
                const cogmRaw = $('#cogmInput').val() || '';
                const cogm = Number(cogmRaw.replace(/\D/g, '')); // Ambil hanya angka

                if (!cogmRaw.trim() || cogm === 0) {
                    $('#cogmAlert').removeClass('d-none');
                    return; // Hentikan AJAX request jika COGM kosong atau nol
                }

                $.post(URL_ADD, {
                        product_detail_id: current.product_detail_id,
                        qty,
                        cogm
                    })
                    .done(res => {
                        renderCart(res);
                        $('#productModal').modal('hide');
                    })
                    .fail(err => {
                        alert(err.responseJSON?.message || 'Terjadi kesalahan saat menambahkan data.');
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
            $(document).on('click', '.btn-cart-remove', function() {
                const id = $(this).data('id');
                $.post(URL_DEL, {
                    product_detail_id: id
                }).done(renderCart);
            });
        });
    </script>
@endpush
