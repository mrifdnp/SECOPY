@extends('layouts.master')

@section('title')
    Detail Refund
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Systems @endslot
        @slot('title') Detail Refund @endslot
    @endcomponent

    @php
        $rp = fn($n) => 'Rp ' . number_format((int)($n ?? 0), 0, ',', '.');
        $invoice = $refund->refund_invoice_id ?? '-';
        $trxInvoice = $refund->salesTransaction->invoice_id ?? '-';
        $total   = (int)($refund->total_refund ?? 0);
        $customerName = trim(($refund->salesTransaction->customer->first_name ?? '') . ' ' . ($refund->salesTransaction->customer->last_name ?? '')) ?: '—';
    @endphp

    <section>
        <div class="row">
            <div class="col-lg-9">
                @include('backend.includes.flash')
                
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h5 class="card-title flex-grow-1 mb-0">
                                Invoice Refund <b>#{{ $invoice }}</b>
                            </h5>
                            <div class="flex-shrink-0">
                                <span>Tgl Refund: <b>{{ \Carbon\Carbon::parse($refund->refund_date)->format('d M Y H:i') }}</b></span>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive table-card mb-4">
                            <table class="table table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Produk</th>
                                        <th>Qty Dikembalikan</th>
                                        <th class="text-end">Harga/Unit</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($refundItems as $idx => $it)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>
                                                <h5 class="fs-14 mb-1">{{ $it->product_title ?? '-' }}</h5>
                                                <p class="text-muted mb-0">
                                                    #{{ $it->sku ?? '-' }}
                                                </p>
                                            </td>
                                            <td>{{ $it->qty }}</td>
                                            <td class="text-end">{{ $rp($it->price) }}</td>
                                            <td class="text-end">{{ $rp($it->refund_amount) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <th colspan="4" class="text-end">Total Refund:</th>
                                        <th class="text-end text-danger">{{ $rp($total) }}</th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <h6 class="fs-14 mb-2">Alasan Refund:</h6>
                            <div class="p-3 bg-light rounded">
                                {{ $refund->reason }}
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <a href="{{ route('refund.index') }}" class="btn btn-soft-secondary">Kembali ke List</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informasi Tambahan</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 vstack gap-3">
                            <li>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ri-file-list-3-line fs-20 text-muted"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="fs-14 mb-1">
                                            <a href="{{ route('sales-transaction.show', $refund->sales_transaction_id) }}">#{{ $trxInvoice }}</a>
                                        </h6>
                                        <p class="text-muted mb-0">Invoice Transaksi</p>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ri-wallet-3-line fs-20 text-muted"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="fs-14 mb-1">
                                            {{ $refund->salesTransaction->paymentType ? $refund->salesTransaction->paymentType->payment_type : ($refund->salesTransaction->payment_type ?: '-') }}
                                        </h6>
                                        <p class="text-muted mb-0">Metode Pembayaran</p>
                                    </div>
                                </div>
                            </li>
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
                                        <i class="ri-user-settings-line fs-20 text-muted"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="fs-14 mb-1">{{ $refund->created_by ?? '-' }}</h6>
                                        <p class="text-muted mb-0">Di-proses oleh</p>
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
