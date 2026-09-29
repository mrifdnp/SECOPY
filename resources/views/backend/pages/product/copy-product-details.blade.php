@extends('layouts.master')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Salin Detail Produk Antar Store</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('product.index') }}">Product</a></li>
                    <li class="breadcrumb-item active">Salin Detail Produk</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-file-copy-2-line me-1"></i> Salin Detail Produk</h5>
            </div>
            <div class="card-body">
                @include('backend.includes.flash')

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="alert alert-info">
                    <h6 class="alert-heading"><i class="ri-information-line me-1"></i> Informasi</h6>
                    <ul class="mb-0">
                        <li><strong>SKU & Harga</strong> akan disalin dari store sumber</li>
                        <li><strong>Stok</strong> akan di-set <strong>0</strong> untuk store tujuan</li>
                        <li>Produk yang <strong>sudah ada</strong> di store tujuan akan <strong>ditimpa</strong> (SKU & harga diupdate)</li>
                    </ul>
                </div>

                <form action="{{ route('product.copyProductDetailsProcess') }}" method="POST" id="copyForm">
                    @csrf
                    <div class="mb-3">
                        <label for="source_store_id" class="form-label">Store Sumber (salin dari)</label>
                        <select class="form-select" name="source_store_id" id="source_store_id" required>
                            <option value="">-- Pilih Store Sumber --</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="text-center mb-3">
                        <i class="ri-arrow-down-line fs-24 text-primary"></i>
                    </div>

                    <div class="mb-3">
                        <label for="target_store_id" class="form-label">Store Tujuan (salin ke)</label>
                        <select class="form-select" name="target_store_id" id="target_store_id" required>
                            <option value="">-- Pilih Store Tujuan --</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="button" class="btn btn-primary" id="btnSubmitCopy">
                            <i class="ri-file-copy-2-line me-1"></i> Salin Detail Produk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-bar-chart-box-line me-1"></i> Ringkasan Per Store</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Store</th>
                                <th class="text-center">Jumlah Detail Produk</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stores as $store)
                                <tr>
                                    <td>{{ $store->name }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary fs-12">
                                            {{ \App\Models\ProductDetail::where('store_id', $store->id)->whereNull('deleted_at')->count() }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
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
        $('#btnSubmitCopy').on('click', function() {
            var source = $('#source_store_id option:selected').text();
            var target = $('#target_store_id option:selected').text();

            if (!$('#source_store_id').val() || !$('#target_store_id').val()) {
                alert('Silakan pilih store sumber dan store tujuan.');
                return;
            }

            if ($('#source_store_id').val() === $('#target_store_id').val()) {
                alert('Store sumber dan tujuan tidak boleh sama.');
                return;
            }

            var konfirmasi = confirm(
                'Apakah Anda yakin ingin menyalin detail produk?\n\n' +
                'Dari: ' + source + '\n' +
                'Ke: ' + target + '\n\n' +
                'Produk yang sudah ada di store tujuan akan ditimpa (SKU & harga diupdate).\n' +
                'Stok akan di-set 0.'
            );

            if (konfirmasi) {
                $('#copyForm').submit();
            }
        });
    });
</script>
@endsection
