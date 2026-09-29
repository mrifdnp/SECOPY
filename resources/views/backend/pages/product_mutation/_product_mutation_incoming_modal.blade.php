<div class="modal fade" id="incomingProductModal" tabindex="-1" aria-labelledby="incomingProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 710px;">
        <div class="modal-content p-3">
            <!-- Begin modal header -->
            <div class="modal-header py-2">
                <h3 class="modal-title fw-semibold" id="incomingProductModalLabel">Pilih Mutasi</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <hr class="mx-n3 border border-primary-subtle opacity-100">

            <div class="modal-body" id="incomingProductModalBody">
                {{-- Content akan dimuat di sini melalui AJAX --}}
            </div>

            <div class="modal-footer px-3">
                <div class="d-flex flex-column flex-md-row w-100 align-items-stretch gap-3">
                    <button type="button" class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2 py-2 px-3" style="min-width: 150px; min-height: 50px;" data-bs-dismiss="modal" aria-label="Tutup modal tanpa menambah ke keranjang">
                        <i class="ri-close-circle-line fs-5" aria-hidden="true"></i>
                        <span>Batal</span>
                    </button>

                    <button type="button" disabled id="btnChooseMutation" style="min-width: 150px; min-height: 50px;" class="btn btn-primary d-flex align-items-center justify-content-center gap-2 py-2 flex-fill">
                        <i class="ri-play-list-add-fill fs-5" aria-hidden="true"></i>
                        <span class="text-truncate">Pilih Mutasi</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


@push('script-mutation-incoming-product-modal')
    <script>
        $(document).ready(function() {
            // Event listener untuk modal bootstrap
            $('#incomingProductModal').on('show.bs.modal', function(e) {
                $('#incomingProductModalBody').html(''); // Clear previous content
                fetchIncomingProducts();
            });

            // Event listener saat accordion dibuka
            $('#incomingProductModal').on('show.bs.collapse', '.accordion-collapse', function() {
                $('#btnChooseMutation').prop('disabled', false); // enable button
            });

            // Saat accordion selesai tertutup
            $('#incomingProductModal').on('hidden.bs.collapse', '.accordion-collapse', function() {
                // tunggu sedikit biar accordion lain sempat kebuka
                setTimeout(() => {
                    const anyOpen = $('#incomingProductModal').find('.accordion-collapse.show').length > 0;
                    $('#btnChooseMutation').prop('disabled', !anyOpen);
                }, 100);
            });

            // Event listener untuk tombol pilih mutasi
            $('#btnChooseMutation').on('click', function() {
                const selectedAccordion = $('#incomingProductModal').find('.accordion-collapse.show');
                if (selectedAccordion.length) {
                    const movementId = selectedAccordion.data('movement-id');
                    // Panggil fungsi untuk menambahkan produk ke keranjang
                    addIncomingMutationToList(movementId);
                    // Tutup modal
                    $('#incomingProductModal').modal('hide');
                }
            });

            // Fungsi untuk mengambil data produk yang akan masuk dari server
            function fetchIncomingProducts() {
                // Show loading state
                showLoading();

                $.ajax({
                    url: "{{ route('product-mutation.incoming') }}",
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            renderIncomingProducts(response.data);
                        } else {
                            showNoData();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching incoming products:', error);
                        showError();
                    }
                });
            }

            // Fungsi untuk menampilkan loading state
            function showLoading() {
                const loadingHtml = `
                    <div class="text-center text-muted py-5">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="mb-2">Memuat data produk yang akan masuk...</div>
                    </div>
                `;

                $('#incomingProductModalBody').html(loadingHtml);
            }

            // Fungsi untuk menampilkan pesan tidak ada data
            function showNoData() {
                const noDataHtml = `
                    <div class="text-center text-muted py-5">
                        <i class="ri-information-line fs-1 mb-3" aria-hidden="true"></i>
                        <div class="mb-2">Tidak ada produk yang akan masuk.</div>
                    </div>
                `;

                $('#incomingProductModalBody').html(noDataHtml);
            }

            // Fungsi untuk menampilkan pesan error
            function showError() {
                const errorHtml = `
                    <div class="text-center text-danger py-5">
                        <i class="ri-error-warning-line fs-1 mb-3" aria-hidden="true"></i>
                        <div class="mb-2">Terjadi kesalahan saat memuat data.</div>
                    </div>
                `;

                $('#incomingProductModalBody').html(errorHtml);
            }

            // Fungsi render untuk setiap item
            function renderMovementItem(item) {
                return `
                    <div class="p-3 border border-2 border-secondary-subtle rounded-3 overflow-hidden">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold fs-5 text-truncate text-dark">${item.product_name}</span>
                            <span class="fw-semibold fs-6">${item.qty}x</span>
                        </div>
                        <div class="d-flex gap-4 mt-2">
                            <div class="d-flex flex-column">
                                <span class="text-muted">Varian:</span>
                                <span class="fw-semibold fs-6 text-primary text-capitalize">${item.variant || '-'}</span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-muted">Warna:</span>
                                <span class="fw-semibold fs-6 text-primary text-capitalize">${item.color || '-'}</span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-muted">SKU:</span>
                                <span class="fw-semibold fs-6">${item.sku}</span>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Fungsi utama render accordion movements
            function renderIncomingProducts(data) {
                let accordionHtml = `
                    <label class="form-label mb-3">Daftar Produk yang Akan Masuk</label>
                    <div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box accordion-secondary" id="mutation-accordion">
                `;

                data.forEach((movement, index) => {
                    const accordionId = `accor_borderedExamplecollapse${index + 1}`;
                    const headerId = `accordionborderedExample${index + 1}`;

                    accordionHtml += `
                        <div class="accordion-item material-shadow">
                            <h2 class="accordion-header" id="${headerId}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#${accordionId}">
                                    <div class="d-flex flex-column fs-16 gap-0">
                                        <p>${movement.formatted_date} - ${movement.total_products} Produk ${movement.total_items} Item</p>
                                        <p class="text-muted fs-12">${movement.remark}</p>
                                    </div>
                                </button>
                            </h2>
                            <div id="${accordionId}" class="accordion-collapse collapse" data-movement-id="${movement.id}" data-bs-parent="#mutation-accordion">
                                <div class="accordion-body">
                                    <div class="d-flex flex-column gap-3">
                                        ${movement.items.map(renderMovementItem).join('')}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                accordionHtml += `</div>`;

                // Render ke dalam modal body
                $('#incomingProductModalBody').html(accordionHtml);
            }

            // Fungsi untuk menambahkan produk mutasi yang dipilih ke daftar mutasi
            function addIncomingMutationToList(movementId) {
                if (!movementId) return;

                $.ajax({
                    url: "{{ route('product-mutation.incoming.addToList') }}",
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        movement_id: movementId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.lines.length > 0) {
                            // Ganti tombol di header daftar mutasi menjadi tombol hapus
                            changeMutationListHeaderButtonToClear();

                            // Tandai tombol proses mutasi bahwa ini dari barang masuk
                            markProcessMutationButtonAsFromIncoming(response.summary?.origin_party?.id, response.summary?.destination_party?.id, movementId);

                            // Jika berhasil, refresh daftar mutasi
                            window.fetchCart(); // ada di file _product_mutation_list.blade.php
                        } else {
                            console.error('Error adding incoming mutation to list:', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error adding incoming mutation to list:', error);
                        alert('Terjadi kesalahan saat menambahkan produk ke daftar mutasi.');
                    }
                });
            }

            // Mengganti tombol yang ada di header daftar mutasi menjadi tombol Hapus untuk mengosongkan daftar mutasi
            function changeMutationListHeaderButtonToClear() {
                // id mutation-list-button-container ada di _product_mutation_list.blade.php
                const $buttonContainer = $('#mutation-list-button-container');
                if ($buttonContainer.length && !$buttonContainer.find('#clear-mutation-list-button').length) {
                    const clearButtonHtml = `
                        <button id="clear-mutation-list-button" type="button" class="btn btn-danger d-flex align-items-center" title="Kosongkan Daftar Mutasi">
                            <i class="ri-delete-bin-2-fill me-0 me-sm-2"></i>
                            <span class="fw-medium text-nowrap d-none d-md-inline">Kosongkan</span>
                        </button>
                    `;

                    $buttonContainer.html(clearButtonHtml);

                    // Event listener untuk tombol hapus
                    $('#clear-mutation-list-button').on('click', function() {
                        $.ajax({
                            url: "{{ route('product-mutation.list.clear') }}",
                            method: 'POST',
                            dataType: 'json',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                // Hapus tanda dari tombol proses mutasi untuk menandakan bukan dari barang masuk
                                unmarkProcessMutationButtonAsFromIncoming();

                                // Jika berhasil, ganti tombol kembali ke tombol pilih mutasi
                                const chooseButtonHtml = `
                                    <button id="incoming-product-button" type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#incomingProductModal">
                                        <i class="ri-clockwise-2-fill me-0 me-sm-2"></i>
                                        <span class="fw-medium text-nowrap d-none d-md-inline">Barang Masuk</span>
                                    </button>
                                `;

                                $buttonContainer.html(chooseButtonHtml);
                                // Re-attach event listener to the new button
                                $('#incoming-product-button').on('click', function() {
                                    $('#incomingProductModal').modal('show');
                                });

                                // Refresh daftar mutasi
                                window.fetchCart();
                            },
                            error: function(xhr, status, error) {
                                console.error('Error clearing mutation list:', error);
                            }
                        });
                    });
                }
            }

            // Menambahkan attribut untuk tombol proses mutasi untuk menandakan kalau moda yang dipilih adalah ambil dari barang masuk
            function markProcessMutationButtonAsFromIncoming(originPartyId, destinationPartyId, movementId) {
                const $processButton = $('#btnOpenProcessMutation');
                if ($processButton.length) {
                    $processButton.attr('data-from-incoming', 'true');
                    $processButton.attr('data-movement-id', movementId || '');
                    $processButton.attr('data-origin-party-id', originPartyId);
                    $processButton.attr('data-destination-party-id', destinationPartyId);
                }
            }

            // Hapus attribut untuk tombol proses mutasi untuk menandakan kalau moda yang dipilih adalah ambil dari produk biasa
            function unmarkProcessMutationButtonAsFromIncoming() {
                const $processButton = $('#btnOpenProcessMutation');
                if ($processButton.length) {
                    $processButton.removeAttr('data-from-incoming');
                    $processButton.removeAttr('data-movement-id');
                    $processButton.removeAttr('data-origin-party-id');
                    $processButton.removeAttr('data-destination-party-id');
                }
            }
        });
    </script>
@endpush
