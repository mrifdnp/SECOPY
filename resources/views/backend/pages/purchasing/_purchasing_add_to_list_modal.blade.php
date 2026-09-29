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
                                <div class="text-muted mb-4 fs-5">
                                    <span id="productSku">—</span>
                                    <span class="mx-2">|</span>
                                    <span id="productStock" class="fw-medium text-primary">Stok: -</span>
                                </div>
                            </div>
                            {{-- <div class="mb-0 fs-5 fw-semibold"><span id="pmPrice">Rp 0</span></div> --}}
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
                            <span class="fs-5">Qty</span>

                            <div id="qtyStepper" class="input-group pt-2 flex-nowrap w-100">
                                <!-- (-) -->
                                <button type="button" class="btn btn-primary" style="border-top-right-radius:0;border-bottom-right-radius:0;" data-step="-1" aria-label="Kurangi jumlah">
                                    <i class="ri-subtract-line"></i>
                                </button>

                                <!-- input -->
                                <input id="qtyInput" type="number" inputmode="numeric" pattern="\d*" class="form-control text-center rounded-0" min="1" step="1" value="1" aria-label="Jumlah">

                                <!-- (+) -->
                                <button type="button" class="btn btn-primary" style="border-top-left-radius:0;border-bottom-left-radius:0;" data-step="1" aria-label="Tambah jumlah">
                                    <i class="ri-add-line"></i>
                                </button>
                            </div>
                            <!-- Stock Alert -->
                            <div id="stockAlert" class="form-text text-danger mt-2 d-none d-inline-flex align-items-center gap-1" aria-live="polite" role="status">
                                <i class="ri-error-warning-line" aria-hidden="true"></i>
                                <span>Stok habis</span>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                            <div class="d-flex justify-content-between align-items-end">
                                <span class="fs-5">Harga Modal</span>
                                <small class="text-muted fw-medium" id="currentCogmLabel" style="font-size: 12px;">Modal : -</small>
                            </div>
                            <div class="input-group pt-2 w-100">
                                <span class="input-group-text bg-light border-end-0">Rp</span>
                                <input id="cogmInput" type="text" class="form-control" placeholder="Masukkan Harga" aria-label="Harga Beli">
                            </div>
                            <div id="cogmAlert" class="form-text text-danger mt-2 d-none d-inline-flex align-items-center gap-1">
                                <i class="ri-error-warning-line" aria-hidden="true"></i>
                                <span>Harga beli wajib diisi</span>
                            </div>
                        </div>
                    </div>
                    <!-- End stepper Qty -->
                </div>
            </div>

            <!-- Begin footer -->
            <div class="modal-footer px-3">
                <div class="d-flex flex-column flex-md-row w-100 align-items-stretch gap-3">
                    <button type="button" class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2 py-2 px-3" style="min-width: 150px; min-height: 50px;" data-bs-dismiss="modal" aria-label="Tutup modal tanpa menambah ke keranjang">
                        <i class="ri-close-circle-line fs-5" aria-hidden="true"></i>
                        <span>Batal</span>
                    </button>

                    <button type="button" id="btnAddCart" style="min-width: 150px; min-height: 50px;" class="btn btn-primary d-flex align-items-center justify-content-center gap-2 py-2 flex-fill">
                        <i class="ri-play-list-add-fill fs-5" aria-hidden="true"></i>
                        <span class="text-truncate">Tambah ke Daftar Pembelian</span>
                    </button>
                </div>
            </div>
            <!-- End footer -->

        </div>
    </div>
</div>

@push('script-modal-add-to-list')
    <script>
        /**
         * Script Stepper Qty (hanya validasi min qty, tanpa max qty)
         */
        $(function() {
            const $stepper = $('#qtyStepper');
            const $qtyInput = $('#qtyInput');
            const MIN_QTY = parseInt($qtyInput.attr('min'), 10) || 0; // default min 0
            const $btnAdd = $('#btnAddCart');
            const $stockAlert = $('#stockAlert');

            // clamp hanya cek min saja
            const clamp = n => Math.max(Number(n) || MIN_QTY, MIN_QTY);

            function setQty(n) {
                const v = clamp(n);
                $qtyInput.val(v);
                // disable tombol minus kalau sudah min
                $stepper.find('[data-step="-1"]').prop('disabled', v <= MIN_QTY);
                // tombol plus tidak perlu disable
                $stepper.find('[data-step="1"]').prop('disabled', false);
            }

            window.checkBtnAddState = function() {
                const isOut = false; // Karena max tidak dipakai lagi
                const cogmRaw = $('#cogmInput').val() || '';
                const cogmVal = Number(cogmRaw.replace(/\D/g, ''));
                const isCogmEmpty = cogmRaw.trim() === '' || cogmVal === 0;
                $('#btnAddCart').prop('disabled', isOut || isCogmEmpty);
            };

            function setControlsByStock() {
                // karena max tidak dipakai lagi, cukup cek kalau stok = 0
                const out = false;
                $qtyInput.prop('disabled', out);
                $stepper.find('[data-step]').prop('disabled', out);
                $stockAlert.toggleClass('d-none', !out);
                if (out) {
                    $qtyInput.val(0);
                }
                window.checkBtnAddState();
            }

            // fungsi ini tidak dipakai lagi, tapi kalau masih dipanggil biar aman
            window.__setQtyMax = function(n) {
                setControlsByStock();
                setQty(Number($qtyInput.val()) || MIN_QTY);
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
        });
    </script>

    <script>
        /**
         * Script Get Data `Modal Produk add to cart`
         * fetch data via ajax
         */
        $(() => {
            /*** 1) Helpers ***/
            const DETAIL_URL = "{{ route('purchasing-transaction.productDetail') }}";
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

            function updateSummary(minFallback = 0) {
                const item = pickBestDetail(getVariantId(), getColorId());
                $skuEl.text(item?.sku || '—');
                $priceEl.text(toRupiah(item?.price ?? minFallback));
                
                const stock = item?.quantity_available ?? 0;
                $('#productStock').text('Stok: ' + stock);
                
                // Set default COGM
                const defaultCogm = item?.cogm ?? 0;
                $('#cogmInput').val(defaultCogm > 0 ? toRupiah(defaultCogm).replace('Rp ', '') : '');
                
                if (defaultCogm > 0) {
                    $('#currentCogmLabel').text('Modal : ' + toRupiah(defaultCogm));
                    $('#cogmAlert').addClass('d-none');
                } else {
                    $('#currentCogmLabel').text('Modal : -');
                    $('#cogmAlert').removeClass('d-none');
                }

                window.__setQtyMax?.(item?.quantity_available ?? null); // batasi qty sesuai stok aktif
                window.checkBtnAddState?.(); // cek state tombol setelah update summary
            }

            // Validasi Input COGM
            $('#cogmInput').mask('0.000.000.000', { reverse: true });
            $('#cogmInput').on('input keyup change', function() {
                const valRaw = $(this).val() || '';
                const valNum = Number(valRaw.replace(/\D/g, ''));
                if (!valRaw.trim() || valNum === 0) {
                    $('#cogmAlert').removeClass('d-none');
                } else {
                    $('#cogmAlert').addClass('d-none');
                }
                window.checkBtnAddState?.();
            });

            /*** 6) Modal lifecycle ***/
            $modal.on('show.bs.modal', e => {
                const trigger = $(e.relatedTarget).data();
                const productId = String(trigger.id || '');
                if (!productId) return;

                // Fallback awal dari kartu
                $titleEl.text(trigger.title || '');
                $skuEl.text('—');
                $priceEl.text(toRupiah(trigger.price || 0));
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

            // UX: pointer untuk label warna
            $colBox.on('mouseenter', 'label', function() {
                this.style.cursor = 'pointer';
            });
        });
    </script>
@endpush
