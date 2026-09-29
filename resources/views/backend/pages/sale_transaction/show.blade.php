@extends('layouts.master')

@section('title')
    Detail {{ $titleHeader->name }}
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Systems @endslot
        @slot('title') Detail {{ $titleHeader->name }} @endslot
    @endcomponent

    @php
        $rp = fn($n) => 'Rp ' . number_format((int)($n ?? 0), 0, ',', '.');
        $invoice = $sale_transaction->invoice_id ?? '-';
        $payName = $payment_type->payment_type ?? '-';
        $total   = (int)($sale_transaction->total ?? $computed_total ?? 0);
        $paid    = (int)($sale_transaction->amount_paid ?? 0);
        $status  = strtoupper($sale_transaction->payment_status);
        $customerName = trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? '')) ?: '—';
        $customerPhone = $customer->phone ?? '—';
        $customerEmail = $customer->email ?? '—';
        $customerAddr  = trim(($customer->address ?? '')) ?: '—';
    @endphp

    <section>
        <div class="row">
            <!-- Begin Sisi Kiri -->
            <div class="col-lg-9">
                @include('backend.includes.flash')
                <div class="card">
                    <!-- Begin Card Header -->
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h5 class="card-title flex-grow-1 mb-0">
                                <!-- Order Number -->
                                Order <b>#{{ $invoice }}</b>
                            </h5>
                            <div class="flex-shrink-0">
                                <!-- Transaction Date -->
                                <span>Tanggal Transaksi: <b>{{ $display_time }}</b></span>
                            </div>
                        </div>
                    </div>

                    <!-- Begin Card Body -->
                    <div class="card-body">
                        <!-- Begin Table -->
                        <div class="table-responsive table-card">
                            <table class="table table-nowrap mb-0">
                                <!-- Begin Table Header -->
                                <thead class="table-light">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nama</th>
                                    <th scope="col">Qty</th>
                                    <th scope="col">Diskon (%)</th>
                                    <th scope="col">Diskon (Rp)</th>
                                    <th scope="col" class="text-end">Harga</th>
                                    <th scope="col" class="text-end">Subtotal</th>
                                </tr>
                                </thead>

                                <!-- Begin Table Body -->
                                <tbody id="contentProduct">
                                    @php 
                                        $rowNo = 1; 
                                    @endphp

                                    @forelse($items as $it)
                                        <tr>
                                            <!-- Item -->
                                            <td>{{ $rowNo++ }}</td>
                                            <td data-column-id="product">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar-sm bg-light rounded p-1">
                                                            <img src="{{ $it->main_pic }}" alt="{{ $it->product_title }}"
                                                                class="img-fluid d-block"/>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h5 class="fs-14 mb-1" title="{{ $it->product_title }}">
                                                            <span class="text-dark">{{ $it->product_title }}</span>
                                                        </h5>
                                                        <p class="text-muted mb-0">
                                                            #{{ $it->sku ?: '—' }}
                                                            • Category: <span class="fw-medium">{{ $it->category_title ?: '—' }}</span>
                                                        </p>
                                                        <p class="text-muted mb-0">
                                                            Varian: <span class="fw-medium">{{ $it->variant_name ?: '—' }}</span>
                                                            • Warna: <span class="fw-medium">{{ $it->color_label ?: '—' }}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>{{ (int)$it->qty }}</td>

                                            <!-- Diskon persen (per unit) -->
                                            <td>
                                                {{ $it->discount_percent ? rtrim(rtrim(number_format((float)$it->discount_percent, 2, ',', '.'), '0'), ',') : 0 }}%
                                            </td>

                                            <!-- Diskon nominal -->
                                            <td>{{ $rp($it->discount_nominal ?? 0) }}</td>

                                            <!-- Harga asli sebelum diskon (per unit) -->
                                            <td class="text-end">{{ $rp($it->original_price ?? $it->price ?? 0) }}</td>

                                            <!-- Subtotal -->
                                            <td class="text-end">{{ $rp($it->line_subtotal ?? 0) }}</td>
                                        </tr>

                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">Tidak ada item.</td>
                                        </tr>
                                        
                                    @endforelse

                                    <!-- Begin Ringkasan -->
                                    <tr>
                                        <th colspan="2" class="text-start">SUB TOTAL:</th>
                                        <th>{{ (int)($units ?? 0) }} items</th>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <th class="text-end">{{ $rp($computed_subtotal ?? 0) }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="4">&nbsp;</th>
                                        <th>Diskon (Rp):</th>
                                        <td></td>
                                        <td class="text-end">- ({{ $rp($discount_nominal ?? 0) }})</td>
                                    </tr>
                                    <tr>
                                        <th colspan="4">&nbsp;</th>
                                        <th>Diskon (%):</th>
                                        <td></td>
                                        <td class="text-end">
                                            - ({{ (int)($discount_percent ?? 0) }}%) ({{ $rp($discount_percent_amount ?? 0) }})
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colspan="4">&nbsp;</th>
                                        <th>Biaya Tambahan:</th>
                                        <td></td>
                                        <td class="text-end">{{ $rp($additional_cost ?? 0) }}</td>
                                    </tr>

                                    @if(!empty($sale_transaction->courier['cost']))
                                    <tr>
                                        <th colspan="4">&nbsp;</th>
                                        <th>Biaya Kirim:</th>
                                        <td></td>
                                        <td class="text-end">{{ $rp($sale_transaction->courier['cost'])}}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th colspan="4">&nbsp;</th>
                                        <th>Total:</th>
                                        <td></td>
                                        <td class="text-end">{{ $rp($sale_transaction->total ?? $computed_total ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <th colspan="4">&nbsp;</th>
                                        <th>Grand Total:</th>
                                        <td></td>
                                        <td class="text-end">{{ $rp($sale_transaction->total ?? $computed_total ?? 0) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!--  Begin Card Footer -->
                    <div class="card-footer">
                        <a href="{{ redirect()->back()->getTargetUrl() }}" class="btn btn-soft-secondary">Back to List</a>
                    </div>
                </div>

                
            </div>
            <!-- End Sisi Kiri -->

            <!-- Begin Sisi Kanan -->
            <div class="col-lg-3">
                <!-- Begin Card 1 -->
                <div class="card">
                    <!-- Begin Card Header -->
                    <div class="card-header">
                        <div class="d-flex">
                            <h5 class="card-title flex-grow-1 mb-0">Customer Details</h5>
                            <div class="flex-shrink-0">
                                <a href="{{ isset($customer->id) ? route('customer.show', $customer->id) : '#' }}" class="link-secondary" target="_blank">Lihat Profil</a>
                            </div>
                        </div>
                    </div>

                    <!-- Begin Card Body -->
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 vstack gap-3">
                            <li>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <img src="{{ asset('build/images/user.gif') }}" alt="" class="avatar-sm rounded">
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="fs-14 mb-1">{{ $customerName }}</h6>
                                        <p class="text-muted mb-0">Customer</p>
                                    </div>
                                </div>
                            </li>
                            <li><i class="ri-phone-line me-2 align-middle text-muted fs-16"></i>{{ $customerPhone }}</li>
                            <li><i class="ri-mail-line me-2 align-middle text-muted fs-16"></i>{{ $customerEmail }}</li>
                            <li><i class="ri-map-pin-line me-2 align-middle text-muted fs-16"></i>{{ $customerAddr }}</li>
                        </ul>
                    </div>
                </div>

                <!-- Begin Card 2 -->
                @if($sale_transaction->channel == 'store')
                <div class="card">
                    <!-- Begin Card Header -->
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-secure-payment-line align-bottom me-1 text-muted"></i> Payment Details
                        </h5>
                    </div>

                    <!-- Begin Card Body -->
                    <div class="card-body">
                        <!-- ID Order -->
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <p class="text-muted mb-0">ID Order:</p>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-0"><b>#{{ $invoice }}</b></h6>
                            </div>
                        </div>
                        <!-- Sales Person -->
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <p class="text-muted mb-0">Sales Person:</p>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-0"><b>{{ $sales_person->name ?? '-' }}</b></h6>
                            </div>
                        </div>
                        <!-- Metode Pembayaran -->
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <p class="text-muted mb-0">Metode Pembayaran:</p>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-0"><b>{{ strtoupper($payName) }}</b></h6>
                            </div>
                        </div>
                        <!-- Total Bayar -->
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <p class="text-muted mb-0">Total Bayar:</p>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-0"><b>{{ $rp($total) }}</b></h6>
                            </div>
                        </div>
                        <!-- Dibayarkan -->
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <p class="text-muted mb-0">Dibayarkan:</p>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-0"><b>{{ $rp($paid) }}</b></h6>
                            </div>
                        </div>
                        <!-- Status Pembayaran -->
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <p class="text-muted mb-0">Status Pembayaran:</p>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-0"><b>{{ $status }}</b></h6>
                            </div>
                        </div>
                        @if($sale_transaction->payment_status === 'refunded' && $sale_transaction->refunds->count() > 0)
                        <!-- Alasan Refund -->
                        <div class="d-flex align-items-start mt-3 pt-3 border-top border-top-dashed">
                            <div class="flex-shrink-0">
                                <p class="text-muted mb-0">Alasan Refund:</p>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-0 text-warning-emphasis"><b>{{ $sale_transaction->refunds->first()->reason }}</b></h6>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                @if($sale_transaction->channel == 'ecommerce')
                <div class="card">
                    <!-- Begin Card Header -->
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-secure-payment-line align-bottom me-1 text-muted"></i> Payment Details
                        </h5>
                    </div>

                    <!-- Begin Card Body -->
                    <div class="card-body">
                        <!-- ID Order -->
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <p class="text-muted mb-0">ID Order:</p>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-0"><b>#{{ $invoice }}</b></h6>
                            </div>
                        </div>
                        <!-- Metode Pembayaran -->
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <p class="text-muted mb-0">Metode Pembayaran:</p>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-0"><b>XENDIT PAYMENT</b></h6>
                            </div>
                        </div>
                        <!-- Total Bayar -->
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <p class="text-muted mb-0">Total Bayar:</p>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-0"><b>{{ $rp($total) }}</b></h6>
                            </div>
                        </div>
                        <!-- Status Pembayaran -->
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <p class="text-muted mb-0">Status Pembayaran:</p>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-0"><b>{{ $status }}</b></h6>
                            </div>
                        </div>
                        @if($sale_transaction->payment_status === 'refunded' && $sale_transaction->refunds->count() > 0)
                        <!-- Alasan Refund -->
                        <div class="d-flex align-items-start mt-3 pt-3 border-top border-top-dashed">
                            <div class="flex-shrink-0">
                                <p class="text-muted mb-0">Alasan Refund:</p>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-0 text-warning-emphasis"><b>{{ $sale_transaction->refunds->first()->reason }}</b></h6>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                 @if(!empty($sale_transaction->courier['cost']))
                <div class="card">
                    <!-- Begin Card Header -->
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-truck-line align-bottom me-1 text-muted"></i> Kurir
                        </h5>
                    </div>

                    
                    <!-- Begin Card Body -->
                    <div class="card-body">

                        <!-- ID Order -->
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <p class="text-muted mb-0">Name:</p>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-0"><b>{{ $sale_transaction->courier['name'] }}</b></h6>
                            </div>
                        </div>
                        <!-- Metode Pembayaran -->
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <p class="text-muted mb-0">Service:</p>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-0"><b>{{ $sale_transaction->courier['service'] }}</b></h6>
                            </div>
                        </div>
                        <!-- Total Bayar -->
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0">
                                <p class="text-muted mb-0">Resi Number:</p>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="mb-0"><b>{{ $sale_transaction->resi ? $sale_transaction->resi : 'belum di input' }}</b></h6>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @endif

                <!-- Begin Card 3 -->
                @if($sale_transaction->remark!="")
                <div class="card">
                    <!-- Begin Card Header -->
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-file-edit-line align-bottom me-1 text-muted"></i> Remark/Catatan
                        </h5>
                    </div>
                    <!-- Begin Card Body -->
                    
                    <div class="card-body collapse show">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="ri-checkbox-circle-fill text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-2 text-muted">
                                {{ $sale_transaction->remark }}
                            </div>
                        </div>
                    </div>
                   
                </div>
                 @endif

                <!-- Begin Card 4 -->
                <div class="card">
                    <div class="card-body collapse show">
                        <a href="{{ route('sales-transaction.print_thermal_58', $sale_transaction->id) }}" target="_blank" rel="noopener" class="btn btn-outline-warning w-100 mt-3">
                            <i class="ri-printer-fill label-icon align-middle fs-16 me-2"></i> Print
                        </a>
                        <a href="#" id="waBtn" class="btn btn-outline-success w-100 mt-3" data-bs-toggle="modal" data-bs-target="#whatsappModal">
                            <i class="ri-whatsapp-fill label-icon align-middle fs-16 me-2"></i> WhatsApp
                        </a>
                        <a href="{{ route('sales-transaction.download_pdf_58', $sale_transaction->id) }}" target="_blank" rel="noopener" class="btn btn-outline-primary w-100 mt-3">
                            <i class="ri-file-paper-2-fill label-icon align-middle fs-16 me-2"></i> Download PDF
                        </a>
                        @if($sale_transaction->payment_status === 'paid' && $sale_transaction->channel === 'store')
                        <a href="{{ route('refund.create', $sale_transaction->id) }}" class="btn btn-warning w-100 mt-3">
                            <i class="ri-refund-2-line label-icon align-middle fs-16 me-2"></i> Refund Transaksi
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Modal WhatsApp -->
                <div id="whatsappModal" class="modal fade modal-primary" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-body p-3 text-center">
                                <lord-icon
                                    src="https://cdn.lordicon.com/tqywkdcz.json"
                                    trigger="loop"
                                    colors="primary:#121331,secondary:#08a88a"
                                    style="width:120px;height:120px">
                                </lord-icon>

                                <div class="mt-4">
                                    <h4 class="mb-3">WhatsApp Sender</h4>
                                    <p class="text-muted mb-4">Pastikan nomor WhatsApp di bawah adalah milik customer.</p>

                                    <form id="whatsapp_form" action="{{ route('sales-transaction.whatsapp_text', $sale_transaction->id) }}" method="post" target="_blank">
                                        @csrf
                                        <div class="mb-3 text-start">
                                            <label for="phone" class="form-label">No. WhatsApp</label>
                                            <input type="text" class="form-control" name="phone" id="phone"
                                                placeholder="08xxxxxxxxxx"
                                                value="{{ $customerPhone !== '—' ? $customerPhone : '' }}" required>
                                            <input type="hidden" name="id_order" value="{{ $invoice }}">
                                        </div>
                                        <div class="hstack gap-2">
                                            <button type="submit" class="btn btn-success w-100">
                                                <i class="ri-whatsapp-fill align-middle me-2"></i>Kirim ke WhatsApp
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Modal WhatsApp -->
                
            </div>
            <!-- End Sisi Kanan -->
        </div>
    </section>
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-bootstrap-theme-0.1.0-beta.10/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.css') }}" rel="stylesheet" type="text/css"/>
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/select2-4.1.0-rc.0/select2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/velzon-dependencies/jquery-mask-1.14.10/jquery.mask.js') }}"></script>
@endsection
