@extends('layouts.master')
@section('title')
    Tambah Aset Inventaris
@endsection
@section('cssAdded')
    <style>
        .error {
            border: 1px solid #f06548;
        }
        .calc-preview-card {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 15px;
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Aset Inventaris
        @endslot
        @slot('title')
            Tambah Aset Baru
        @endslot
    @endcomponent

    <form action="{{ route('asset_inventories.store') }}" method="post" enctype="multipart/form-data" id="assetForm">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                @include('backend.includes.flash')
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- CARD 1: IDENTITAS BARANG --}}
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0"><i class="ri-information-line me-1 text-primary"></i> Data Barang</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="name">Nama Barang*</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ old('name') }}" placeholder="Contoh: AC, Kursi Kantor, Laptop" required>
                                <span class="invalid-feedback-custom" id="name_error"></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="category">Golongan Barang*</label>
                                <input type="text" class="form-control" id="category" name="category" list="golonganList"
                                    value="{{ old('category') }}" placeholder="Contoh: ELEKTRONIK, FURNITURE" required>
                                <datalist id="golonganList">
                                    <option value="ELEKTRONIK">
                                    <option value="FURNITURE">
                                    <option value="PERALATAN">
                                    <option value="KENDARAAN">
                                    <option value="MESIN">
                                    <option value="INVENTARIS KANTOR">
                                </datalist>
                                <span class="invalid-feedback-custom" id="category_error"></span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="merk">Merk</label>
                                <input type="text" class="form-control" id="merk" name="merk"
                                    value="{{ old('merk') }}" placeholder="Contoh: DAIKIN, IKEA, APPLE">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="last_condition">Kondisi*</label>
                                <select class="form-select" name="last_condition" id="last_condition" required>
                                    <option value="aktif" {{ old('last_condition') == 'aktif' ? 'selected' : '' }}>Baik / Aktif (B)</option>
                                    <option value="rusak" {{ old('last_condition') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                                    <option value="hilang" {{ old('last_condition') == 'hilang' ? 'selected' : '' }}>Hilang</option>
                                    <option value="terjual" {{ old('last_condition') == 'terjual' ? 'selected' : '' }}>Terjual</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="product_code">Kode Aset*</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="product_code" name="product_code"
                                        value="{{ old('product_code') }}" placeholder="Contoh: AST-001" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="generateAssetCode()">
                                        <i class="ri-refresh-line"></i> Auto
                                    </button>
                                </div>
                                <span class="invalid-feedback-custom" id="product_code_error"></span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="store_id">Store / Toko*</label>
                                <select class="form-select" name="store_id" id="store_id" required>
                                    <option value="" disabled>-- Silakan Pilih Store --</option>
                                    @foreach ($stores ?? [] as $st)
                                        <option value="{{ $st->id }}" {{ old('store_id', $selectedStoreId) == $st->id ? 'selected' : '' }}>
                                            {{ $st->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 2: NILAI PEROLEHAN & UMUR MANFAAT --}}
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0"><i class="ri-money-dollar-circle-line me-1 text-success"></i> Nilai Perolehan & Umur Manfaat</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="tanggal_asset">Tanggal Perolehan*</label>
                                <input type="date" class="form-control" name="tanggal_asset" id="tanggal_asset"
                                    value="{{ old('tanggal_asset', date('Y-m-d')) }}" required onchange="calculateDepreciation()">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="jumlah_unit">Jumlah Unit*</label>
                                <input type="number" class="form-control" id="jumlah_unit" name="jumlah_unit"
                                    value="{{ old('jumlah_unit', 1) }}" min="1" placeholder="1" required oninput="calculateDepreciation()">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="harga_per_unit">Harga Per Unit (Rp)*</label>
                                <input type="text" class="form-control" id="harga_per_unit" name="harga_per_unit"
                                    value="{{ old('harga_per_unit') }}" placeholder="0" required oninput="formatRupiah(this); calculateDepreciation();">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="purchase_price">Jumlah Nilai Perolehan (Rp)*</label>
                                <input type="text" class="form-control bg-light fw-bold text-primary" id="purchase_price" name="purchase_price"
                                    value="{{ old('purchase_price') }}" placeholder="0" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="umur_manfaat">Umur Manfaat (Tahun)*</label>
                                <input type="number" step="0.5" class="form-control" id="umur_manfaat" name="umur_manfaat"
                                    value="{{ old('umur_manfaat', 4) }}" placeholder="Contoh: 8" required oninput="calculateDepreciation()">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">% Susut / Tahun</label>
                                <input type="text" class="form-control bg-light text-center" id="persen_susut_preview" value="25%" readonly>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Batas Susut (Bulan)</label>
                                <input type="text" class="form-control bg-light text-center" id="masa" name="masa"
                                    value="{{ old('masa', 48) }}" readonly>
                            </div>
                        </div>

                        {{-- Hidden field residu default 0 --}}
                        <input type="hidden" name="nilai_residu" id="nilai_residu" value="0">
                    </div>
                </div>

                {{-- CARD 3: LIVE PREVIEW KALKULASI EXCEL --}}
                <div class="card border-info">
                    <div class="card-header bg-info-subtle text-info">
                        <h5 class="card-title mb-0"><i class="ri-calculator-line me-1"></i> Estimasi Perhitungan Akuntansi (Hari Ini)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 text-center">
                            <div class="col-md-3 col-6">
                                <div class="p-2 border rounded bg-white">
                                    <small class="text-muted d-block">Jumlah Susut</small>
                                    <h6 class="mb-0 fw-bold" id="preview_jumlah_susut">0 Bulan</h6>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-2 border rounded bg-white">
                                    <small class="text-muted d-block">Susut / Bulan</small>
                                    <h6 class="mb-0 fw-bold text-warning" id="preview_susut_bulan">Rp 0</h6>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-2 border rounded bg-white">
                                    <small class="text-muted d-block">Akumulasi Penyusutan</small>
                                    <h6 class="mb-0 fw-bold text-danger" id="preview_akumulasi_susut">Rp 0</h6>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="p-2 border rounded bg-white">
                                    <small class="text-muted d-block">Nilai Buku Saat Ini</small>
                                    <h6 class="mb-0 fw-bold text-success" id="preview_nilai_buku">Rp 0</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- end col-lg-8 -->

            {{-- SIDEBAR KANAN: FOTO & ACTION --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0"><i class="ri-image-line me-1"></i> Foto Barang</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <img src="{{ asset('img/noimage.png') }}" id="product-img" class="img-fluid rounded border p-1" style="max-height: 200px; width: auto;" alt="Asset Image" />
                        </div>
                        <input class="form-control" type="file" name="image" id="product-image-input" accept="image/png, image/jpeg, image/jpg" onchange="previewImage(this)">
                        <small class="text-muted d-block mt-2">Format: JPG, PNG, JPEG. Maks 2MB.</small>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="btnSubmit">
                                <i class="ri-save-line align-middle me-1"></i> Simpan Aset
                            </button>
                            <a href="{{ route('asset_inventories.index') }}" class="btn btn-soft-secondary">
                                <i class="ri-arrow-left-line align-middle me-1"></i> Kembali ke Daftar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end col-lg-4 -->
        </div>
    </form>
@endsection

@section('script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function formatRupiah(input) {
            let value = input.value.replace(/[^0-9]/g, '');
            input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#product-img').attr('src', e.target.result);
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function generateAssetCode() {
            let prefix = 'AST';
            let date = new Date();
            let y = date.getFullYear().toString().substr(-2);
            let m = ('0' + (date.getMonth() + 1)).slice(-2);
            let rand = Math.floor(1000 + Math.random() * 9000);
            $('#product_code').val(prefix + y + m + '-' + rand);
        }

        function calculateDepreciation() {
            let unit = parseFloat($('#jumlah_unit').val()) || 0;
            let hargaStr = ($('#harga_per_unit').val() || '').replace(/\./g, '');
            let harga = parseFloat(hargaStr) || 0;

            // 1. Nilai Perolehan (K = I * J)
            let totalNilai = unit * harga;
            $('#purchase_price').val(totalNilai.toLocaleString('id-ID'));

            // 2. Umur Manfaat (G) & Batas Susut (N = G * 12)
            let umur = parseFloat($('#umur_manfaat').val()) || 0;
            let batasSusut = Math.round(umur * 12);
            $('#masa').val(batasSusut);

            // % Susut per Tahun (L = 1 / G)
            let persenSusut = umur > 0 ? (1 / umur) : 0;
            let persenDisplay = (persenSusut * 100).toFixed(1) + '%';
            $('#persen_susut_preview').val(persenDisplay);

            // 3. Jumlah Susut Bulan Berjalan (M = (YEAR(NOW)-YEAR(TGL))*12 + MONTH(NOW)-MONTH(TGL))
            let tglVal = $('#tanggal_asset').val();
            let jumlahSusut = 0;
            if (tglVal) {
                let tgl = new Date(tglVal);
                let now = new Date();
                jumlahSusut = (now.getFullYear() - tgl.getFullYear()) * 12 + (now.getMonth() - tgl.getMonth());
            }
            if (jumlahSusut < 0) jumlahSusut = 0;

            // 4. Susut per Bulan (O)
            let susutPerBulan = 0;
            let susutBulanPenuh = (totalNilai * persenSusut) / 12;

            if (jumlahSusut <= 0) {
                susutPerBulan = 0;
            } else if (jumlahSusut === batasSusut) {
                susutPerBulan = Math.max(0, susutBulanPenuh - 1);
            } else if (jumlahSusut > batasSusut) {
                susutPerBulan = 0;
            } else {
                susutPerBulan = susutBulanPenuh;
            }

            // 5. Akumulasi Susut (P)
            let akumulasiSusut = 0;
            if (jumlahSusut <= 0) {
                akumulasiSusut = 0;
            } else if (jumlahSusut === batasSusut) {
                akumulasiSusut = Math.max(0, (jumlahSusut * susutBulanPenuh) - 1);
            } else if (jumlahSusut > batasSusut) {
                akumulasiSusut = Math.max(0, (batasSusut * susutBulanPenuh) - 1);
            } else {
                akumulasiSusut = jumlahSusut * susutBulanPenuh;
            }

            // 6. Nilai Buku (Q = K - P)
            let nilaiBuku = Math.max(0, totalNilai - akumulasiSusut);

            // Update UI Previews
            $('#preview_jumlah_susut').text(jumlahSusut + ' Bulan');
            $('#preview_susut_bulan').text('Rp ' + Math.round(susutPerBulan).toLocaleString('id-ID'));
            $('#preview_akumulasi_susut').text('Rp ' + Math.round(akumulasiSusut).toLocaleString('id-ID'));
            $('#preview_nilai_buku').text('Rp ' + Math.round(nilaiBuku).toLocaleString('id-ID'));
        }

        $(document).ready(function() {
            if (!$('#product_code').val()) {
                generateAssetCode();
            }
            calculateDepreciation();
        });
    </script>
@endsection
