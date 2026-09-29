@extends('layouts.master')

@section('title')
    Proses Refund Transaksi
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Systems @endslot
        @slot('title') Proses Refund @endslot
    @endcomponent

    @php
        $rp = fn($n) => 'Rp ' . number_format((int)($n ?? 0), 0, ',', '.');
        $invoice = $trx->invoice_id ?? '-';
        $total   = (int)($trx->total ?? 0);
        $status  = strtoupper($trx->payment_status);
        $customerName = trim(($trx->customer->first_name ?? '') . ' ' . ($trx->customer->last_name ?? '')) ?: '—';
    @endphp

    <section>
        <div class="row">
            <div class="col-lg-9">
                @include('backend.includes.flash')
                
                <form action="{{ route('refund.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="sales_transaction_id" value="{{ $trx->id }}">
                    
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                <h5 class="card-title flex-grow-1 mb-0">
                                    Refund untuk Order <b>#{{ $invoice }}</b>
                                </h5>
                                <div class="flex-shrink-0">
                                    <span class="badge bg-warning fs-12">Total: {{ $rp($total) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">

                            <h6 class="fs-14 mb-3">Item yang akan dikembalikan:</h6>
                            <div class="table-responsive table-card mb-4">
                                <table class="table table-nowrap mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Produk</th>
                                            <th>Qty</th>
                                            <th class="text-end">Harga/Unit (Final)</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $idx => $it)
                                            <tr>
                                                <td>{{ $idx + 1 }}</td>
                                                <td>
                                                    <h5 class="fs-14 mb-1">{{ $it->product_title ?? '-' }}</h5>
                                                    <p class="text-muted mb-0">
                                                        #{{ $it->sku ?? '-' }}
                                                    </p>
                                                </td>
                                                <td>{{ $it->qty }}</td>
                                                <td class="text-end">{{ $rp($it->final_price) }}</td>
                                                <td class="text-end">{{ $rp($it->final_price * $it->qty) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="card-body border-top border-top-dashed">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label for="reason" class="form-label">Alasan Refund <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="3" required placeholder="Masukkan alasan mengapa transaksi ini di-refund...">{{ old('reason') }}</textarea>
                                        @error('reason')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer text-end">
                            <a href="{{ route('sales-transaction.index') }}" class="btn btn-light me-2">Batal</a>
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Apakah Anda yakin ingin memproses refund ini? Aksi ini tidak dapat dibatalkan.')">
                                <i class="ri-refund-2-line align-bottom me-1"></i> Proses Refund
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Detail Customer & Transaksi</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 vstack gap-3">
                            <li>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ri-user-3-line fs-20 text-muted"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="fs-14 mb-1">{{ $customerName }}</h6>
                                        <p class="text-muted mb-0">Customer</p>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ri-calendar-event-line fs-20 text-muted"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="fs-14 mb-1">{{ \Carbon\Carbon::parse($trx->date)->format('d M Y H:i') }}</h6>
                                        <p class="text-muted mb-0">Tgl Transaksi</p>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ri-wallet-3-line fs-20 text-muted"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="fs-14 mb-1">{{ $trx->paymentType ? $trx->paymentType->payment_type : ($trx->payment_type ?: '-') }}</h6>
                                        <p class="text-muted mb-0">Metode Pembayaran</p>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js') }}"></script>
@endsection
