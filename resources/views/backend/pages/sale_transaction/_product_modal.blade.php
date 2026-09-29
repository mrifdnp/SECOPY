<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 710px;">
        <div class="modal-content p-3">
            <!-- Begin modal header -->
            <div class="modal-header py-2">
                <h3 class="modal-title fw-semibold" id="productModalLabel">Pilih Varian Produk</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <hr class="mx-n3 border border-primary-subtle opacity-100">

            <div class="modal-body">

                <!-- Begin Produk Info -->
                <div class="border border-2 rounded p-3 mb-4">
                    <div class="row g-3">
                        <!-- Image Product -->
                        <div class="col-sm-4 col-lg-3">
                            <div class="overflow-hidden d-flex align-items-center justify-content-center rounded-3" style="max-height: 110px;">
                                <img id="pmImg" src="" alt="" class="w-100 h-100 d-block rounded-3" style="object-fit: cover; object-position:center" loading="lazy">
                            </div>
                        </div>
                        <!-- Product Info -->
                        <div class="col-8 col-lg-9 d-flex flex-column justify-content-between overflow-hidden">
                            <div>
                                <span id="pmTitle" class="mb-1 fs-4 fw-bold"></span>
                                <div class="text-muted mb-4 fs-5"><span id="productSku">—</span></div>
                            </div>
                            <!-- Badge Discount -->
                            <div id="pmDiscountWrap" class="mb-1 d-none">
                                <span id="pmDiscountBadge" class="badge bg-danger"></span>
                            </div>

                            <!-- Price -->
                            <div class="mb-0 fs-5 fw-semibold"><span id="pmPrice">Rp 0</span></div>
                        </div>
                    </div>
                </div>

                <div class="border border-2 rounded p-3 overflow-hidden">
                    <!-- Begin Varian -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <span class="fs-5">Varian</span>

                            <div id="sizeOptions">
                                <!-- data Inject Js -->
                            </div>
                        </div>
                    </div>

                    <!-- Begin Warna -->
                    <div class="row mb-4">
                        <div class="col-12 col-lg-8">
                            <span class="fs-5">Warna</span>

                            <div id="colorOptions" class="d-flex flex-wrap align-items-center gap-3 pt-2">
                                <!-- data Inject Js -->
                            </div>
                        </div>
                    </div>

                    <!-- Begin stepper Qty -->
                    <div class="row mb-4">
                        <div class="col-12 col-sm-6">
                            <span class="fs-5">Qty Beli</span>
                            <div id="qtyStepper" class="input-group pt-2 flex-nowrap w-100">
                                <button type="button" class="btn btn-primary" style="border-top-right-radius:0;border-bottom-right-radius:0;" data-step="-1"><i class="ri-subtract-line"></i></button>
                                <input id="qtyInput" type="number" inputmode="numeric" pattern="\d*" class="form-control text-center rounded-0" min="1" step="1" value="1">
                                <button type="button" class="btn btn-primary" style="border-top-left-radius:0;border-bottom-left-radius:0;" data-step="1"><i class="ri-add-line"></i></button>
                            </div>
                            <div id="stockAlert" class="form-text text-danger mt-2 d-none d-inline-flex align-items-center gap-1"><i class="ri-error-warning-line"></i><span>Stok habis</span></div>
                        </div>

                        <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                            <span class="fs-5">Update Master Stok (<span id="pmStockDisplay" class="fw-bold">0</span>)</span>
                            <div class="d-flex align-items-center gap-2 pt-2 w-100">
                                <div class="input-group flex-nowrap w-100">
                                    <button type="button" class="btn btn-primary" style="border-top-right-radius:0;border-bottom-right-radius:0;" id="btnMinMasterStock"><i class="ri-subtract-line"></i></button>
                                    <input id="masterStockInput" type="number" class="form-control text-center rounded-0" value="0">
                                    <button type="button" class="btn btn-primary" style="border-top-left-radius:0;border-bottom-left-radius:0;" id="btnAddMasterStock"><i class="ri-add-line"></i></button>
                                </div>
                                <button type="button" class="btn btn-primary" id="btnSaveMasterStock" disabled>Simpan</button>
                            </div>
                            <div id="updateStockMsg" class="form-text mt-1 d-none"></div>
                        </div>
                    </div>
                    <!-- End stepper Qty -->
                </div>
            </div>

            <!-- Begin footer -->
            <div class="modal-footer px-3">
                <div class="d-flex flex-column flex-md-row w-100 align-items-stretch gap-3">
                    <button
                        type="button"
                        class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2 py-2 px-3"
                        style="min-width: 150px; min-height: 50px;"
                        data-bs-dismiss="modal"
                        aria-label="Tutup modal tanpa menambah ke keranjang">
                        <i class="ri-close-circle-line fs-5" aria-hidden="true"></i>
                        <span>Batal</span>
                    </button>

                    <button
                        type="button"
                        id="btnAddCart"
                        style="min-width: 150px; min-height: 50px;"
                        class="btn btn-primary d-flex align-items-center justify-content-center gap-2 py-2 flex-fill">
                        <i class="ri-shopping-cart-2-line fs-5" aria-hidden="true"></i>
                        <span class="text-truncate">Tambah ke Keranjang</span>
                    </button>
                </div>
            </div>
            <!-- End footer -->

        </div>
    </div>
</div>


@push('scripts-modal')

<script>
    /**
     * Script Stepper Qty
     */
    $(function() {
        const $stepper = $('#qtyStepper');
        const $qtyInput = $('#qtyInput');
        const MIN_QTY = parseInt($qtyInput.attr('min'), 10) || 1;
        let MAX_QTY = parseInt($qtyInput.attr('max'), 10);
        if (!Number.isFinite(MAX_QTY)) MAX_QTY = Infinity;
        const $btnAdd = $('#btnAddCart');
        const $stockAlert = $('#stockAlert');

        const clamp = n => Math.min(Math.max(Number(n) || MIN_QTY, MIN_QTY), MAX_QTY);

        function setQty(n) {
            const v = clamp(n);
            $qtyInput.val(v);
            $stepper.find('[data-step="-1"]').prop('disabled', v <= MIN_QTY);
            $stepper.find('[data-step="1"]').prop('disabled', v >= MAX_QTY);
        }

        function setControlsByStock() {
            const out = !(MAX_QTY > 0);
            $qtyInput.prop('disabled', out);
            $stepper.find('[data-step]').prop('disabled', out);
            $btnAdd.prop('disabled', out);
            $stockAlert.toggleClass('d-none', !out);
            if (out) {
                $qtyInput.val(0);
            }
        }

        // Dipanggil saat varian/warna berganti untuk mengatur MAX_QTY (sinkron real stok)
        window.__setQtyMax = function(n) {
            const max = Number(n);
            MAX_QTY = max;
            if (Number.isFinite(max)) {
                $qtyInput.attr('max', max);
            } else {
                $qtyInput.removeAttr('max');
            }
            setControlsByStock();
            
            const realStock = Number(n);
            $('#pmStockDisplay').text(realStock);
            $('#masterStockInput').val(realStock);
            $('#btnSaveMasterStock').prop('disabled', true);
            
            if (realStock <= 0) {
                $('#stockAlert').removeClass('d-none');
                $('#stockAlert span').text('Stok habis (' + realStock + ')');
            } else {
                $('#stockAlert').addClass('d-none');
            }

            if (MAX_QTY > 0) setQty(Math.min(Number($qtyInput.val()) || MIN_QTY, MAX_QTY));
        };

        function changeBy(delta) {
            setQty((Number($qtyInput.val()) || MIN_QTY) + delta);
        }

        $stepper.on('click', '[data-step]', e => changeBy(Number($(e.currentTarget).data('step')) || 0));
        $qtyInput.on('input', () => setQty(Number(String($qtyInput.val()).replace(/\D/g, '')) || MIN_QTY));
        $qtyInput.on('keydown', e => {
            if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                e.preventDefault();
                changeBy(e.key === 'ArrowUp' ? 1 : -1);
            }
        });
        $qtyInput.on('wheel', e => e.preventDefault());
        $('#productModal').on('show.bs.modal', () => setQty(MIN_QTY));
        setQty($qtyInput.val());

        // Handlers for Update Master Stock
        $('#btnMinMasterStock').on('click', function() {
            let val = parseInt($('#masterStockInput').val()) || 0;
            $('#masterStockInput').val(val - 1);
            $('#btnSaveMasterStock').prop('disabled', false);
        });
        $('#btnAddMasterStock').on('click', function() {
            let val = parseInt($('#masterStockInput').val()) || 0;
            $('#masterStockInput').val(val + 1);
            $('#btnSaveMasterStock').prop('disabled', false);
        });
        $('#masterStockInput').on('input', function() {
            $('#btnSaveMasterStock').prop('disabled', false);
        });
    });
</script>

<script>
    /**
     * Script Get Data `Modal Produk add to cart`
     * fetch data via ajax
     */
    $(() => {
        /*** 1) Helpers ***/
        const DETAIL_URL = "{{ route('sales-transaction.products.detail') }}";
        const toRupiah = n => 'Rp ' + Number(n || 0).toLocaleString('id-ID');
        const escapeHtml = s => (s || '').toString().replace(/[&<>"']/g, m => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        } [m]));
        const toHex = h => {
            if (!h) return '';
            const x = ('' + h).trim().toLowerCase();
            return x.startsWith('#') ? x : '#' + x;
        };

        /*** 2) DOM cache & state ***/
        const $modal = $('#productModal');
        const $titleEl = $('#pmTitle');
        const $skuEl = $('#productSku');
        const $priceEl = $('#pmPrice');
        const $imgEl = $('#pmImg');
        const $varBox = $('#sizeOptions');
        const $colBox = $('#colorOptions');
        const $qtyInput = $('#qtyInput');
        const $btnAdd = $('#btnAddCart');
        const $stockAlert = $('#stockAlert');

        let detailItems = []; // [{variant_id, color_id, sku, price, quantity_available, ...}]

        /*** 3) Renderers ***/
        function renderVariantOptions(variantOptions) {
            if (!Array.isArray(variantOptions) || variantOptions.length === 0) {
                $varBox.html('<div class="col-12 text-muted pt-2">Tidak ada varian.</div>');
                return;
            }

            // config grid 'variant options'
            const GRID_MIN = '140px';
            $varBox
                .addClass('d-grid gap-3 pt-2')
                .css('grid-template-columns', `repeat(auto-fill, minmax(${GRID_MIN}, 1fr))`);


            const html = variantOptions.map((v, i) => `
                <div>
                    <input class="btn-check" type="radio" name="variant_size" id="size_${v.id}" value="${escapeHtml(v.id)}" ${i===0?'checked':''}>
                    <label class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center fs-5 fw-semibold py-3" for="size_${v.id}">
                        <span class="text-truncate w-100 d-block text-center text-capitalize" title="${escapeHtml(v.name||'')}">
                            ${escapeHtml(v.name || '')}
                        </span>
                    </label>
                </div>
                `).join('');
            $varBox.html(html);
        }

        function renderColorOptions(colorOptions) {
            if (!Array.isArray(colorOptions) || colorOptions.length === 0) {
                $colBox.html('<div class="col-12 text-muted">Tidak ada warna.</div>');
                return;
            }
            const html = colorOptions.map((c, i) => {
                const hex = toHex(c.hexa);
                const id = `color_${c.id}`;
                const sw = hex ?
                    `<span class="d-inline-block rounded-circle p-3" style="background:${hex}"></span>` :
                    `<span class="d-inline-block rounded-circle p-3 bg-secondary"></span>`;
                return `
                    <div class="d-flex flex-column flex-md-row align-items-center text-center text-md-start gap-1 gap-md-2 me-3">
                        <input type="radio" class="visually-hidden color-radio" name="variant_color" id="${id}" value="${escapeHtml(hex)}" data-color-id="${escapeHtml(c.id)}" ${i===0?'checked':''}>
                        <label for="${id}" class="color-chip d-inline-flex align-items-center justify-content-center rounded-circle p-2">${sw}</label>
                        <label for="${id}" class="mb-0 text-muted fs-5 text-capitalize">${escapeHtml(c.label || '')}</label>
                    </div>
                `;
            }).join('');
            $colBox.html(html);
            paintSelectedColorRing(); // langsung tampilkan ring warna aktif
        }

        /*** 4) Warna: ring border hanya untuk yang terpilih ***/
        function paintSelectedColorRing() {
            const $selected = $colBox.find('input.color-radio:checked');
            // hilangkan semua border agar fokus hanya pada warna aktif
            $colBox.find('label.color-chip').each(function() {
                this.style.setProperty('border', 'none', 'important');
                this.style.removeProperty('border-color');
                this.style.removeProperty('border-width');
            });
            if (!$selected.length) return;
            let hex = toHex($selected.val());
            if (!/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(hex)) hex = '#212529';
            const label = $colBox.find(`label.color-chip[for="${$selected.attr('id')}"]`)[0];
            if (label) label.style.setProperty('border', `2px solid ${hex}`, 'important');
        }

        /*** 5) Selection helpers ***/
        const getVariantId = () => $('input[name="variant_size"]:checked').val() || '';
        const getColorId = () => String($('input[name="variant_color"]:checked').data('colorId') || '');

        function pickBestDetail(variantId, colorId) {
            return detailItems.find(x => String(x.variant_id) === String(variantId) && String(x.color_id) === String(colorId)) ||
                detailItems.find(x => String(x.variant_id) === String(variantId)) ||
                detailItems.find(x => String(x.color_id) === String(colorId)) ||
                detailItems[0] ||
                null;
        }

        // Update summary render dinamis
        function updateSummary(minFallback = 0) {
            const item = pickBestDetail(getVariantId(), getColorId());

            $skuEl.text(item?.sku || '—');

            const finalPrice = Number(item?.final_price ?? item?.price ?? minFallback) || 0;
            const originalPrice = Number(item?.original_price ?? 0) || 0;
            const discountValue = Number(item?.best_discount_value ?? 0) || 0;
            const hasDiscount = discountValue > 0 && originalPrice > 0 && finalPrice < originalPrice;

            // Badge diskon
            if (hasDiscount) {
                const isPercent = String(item?.best_discount_type) === 'percent';
                const badgeText = isPercent && item?.raw_percent_value != null ?
                    `-${String(item.raw_percent_value).replace('.', ',')}%` :
                    `-${toRupiah(discountValue)}`;
                $('#pmDiscountBadge').text(badgeText);
                $('#pmDiscountWrap').removeClass('d-none');
            } else {
                $('#pmDiscountWrap').addClass('d-none');
                $('#pmDiscountBadge').text('');
            }

            // Harga (final + coret original bila ada diskon)
            if (hasDiscount) {
                $priceEl.html(`${toRupiah(finalPrice)} <span class="text-muted text-decoration-line-through ms-2">${toRupiah(originalPrice)}</span>`);
            } else {
                $priceEl.text(toRupiah(finalPrice || originalPrice || minFallback));
            }

            // Batasi qty sesuai stok
            window.__setQtyMax?.(item?.quantity_available ?? null);
        }

        /*** 6) Modal lifecycle ***/
        $modal.on('show.bs.modal', e => {
            const trigger = $(e.relatedTarget).data();
            const productId = String(trigger.id || '');
            if (!productId) return;

            // Fallback awal dari kartu
            $titleEl.text(trigger.title || '');
            $skuEl.text('—');

            $priceEl.text(toRupiah(trigger.price || 0));
            $('#pmDiscountWrap').addClass('d-none'); // sembunyikan badge diskon saat awal buka
            $imgEl.attr({
                src: trigger.img || '',
                alt: trigger.title || ''
            });

            $varBox.empty();
            $colBox.empty();
            
            detailItems = [];
            window.detailItems = detailItems;

            // Ambil detail produk
            $.getJSON(DETAIL_URL, {
                id: productId
            }).done(res => {
                $titleEl.text(res.product?.title || trigger.title || '');
                detailItems = Array.isArray(res.items) ? res.items : [];
                window.detailItems = detailItems;

                renderVariantOptions(res.variant_options || []);
                renderColorOptions(res.color_options || []);
                updateSummary(res.min_price || trigger.price || 0);
            }).fail(jq => {
                console.error('productDetail failed', jq.status, jq.responseText);
            });
        });

        // Opsi berubah → ring & summary
        $modal.on('change', 'input[name="variant_size"], input[name="variant_color"]', () => {
            paintSelectedColorRing();
            const currentMin = Number($priceEl.text().replace(/[^\d]/g, '')) || 0;
            updateSummary(currentMin);
        });

        // Guard sebelum tambah ke keranjang
        $btnAdd.on('click.precheck', function(e) {
            const item = pickBestDetail(getVariantId(), getColorId());
            const qty = Number($qtyInput.val() || 0);
            if (!item || qty <= 0 || item.quantity_available <= 0) {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }
        });

        // AJAX update stock
        $('#btnSaveMasterStock').on('click', function() {
            const btn = $(this);
            const newStock = parseInt($('#masterStockInput').val()) || 0;
            const item = pickBestDetail(getVariantId(), getColorId());
            
            if (!item) return;
            
            btn.prop('disabled', true).text('...');
            
            $.ajax({
                url: "{{ route('sales-transaction.products.update-stock') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    product_detail_id: item.product_detail_id,
                    qty: newStock
                },
                success: function(res) {
                    $('#updateStockMsg').removeClass('d-none text-danger').addClass('text-success').text('Stok berhasil diupdate!');
                    setTimeout(() => $('#updateStockMsg').addClass('d-none'), 3000);
                    btn.text('Simpan');
                    
                    // Update item data in memory
                    item.quantity_available = res.qty;
                    // Re-trigger summary update to reset inputs
                    updateSummary(Number($priceEl.text().replace(/[^\d]/g, '')) || 0);
                },
                error: function(err) {
                    $('#updateStockMsg').removeClass('d-none text-success').addClass('text-danger').text('Gagal update stok.');
                    btn.prop('disabled', false).text('Simpan');
                }
            });
        });

        // UX: pointer untuk label warna
        $colBox.on('mouseenter', 'label', function() {
            this.style.cursor = 'pointer';
        });
    });
</script>

@endpush