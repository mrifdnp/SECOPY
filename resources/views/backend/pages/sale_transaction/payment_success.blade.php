@extends('layouts.master-without-nav')

@section('title') Pembayaran Berhasil @endsection

@section('content')
<div class="auth-page-wrapper pt-5">

    <!-- Background dengan overlay + shape -->
    <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
        <div class="bg-overlay"></div>
        <div class="shape">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120">
                <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
            </svg>
        </div>
    </div>

    <!-- Modal WhatsApp -->
    <div id="whatsappModal" class="modal fade modal-primary" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-3 text-center">
                    <lord-icon src="https://cdn.lordicon.com/tqywkdcz.json" trigger="loop"
                        colors="primary:#121331,secondary:#08a88a"
                        style="width:120px;height:120px"></lord-icon>

                    <div class="mt-4">
                        <h4 class="mb-3">WhatsApp Sender</h4>
                        <p class="text-muted mb-4">Pastikan nomor WhatsApp di bawah adalah milik customer.</p>

                        <form id="whatsapp_form" action="{{ route('sales-transaction.whatsapp_text', $ps['id']) }}" method="post" target="_blank">
                            @csrf
                            <div class="mb-3 text-start">
                                <label for="phone" class="form-label">No. WhatsApp</label>
                                <input type="text" class="form-control" name="phone" id="phone"
                                    placeholder="08xxxxxxxxxx" value="{{ $ps['wa'] !== '-' ? $ps['wa'] : '' }}" required>
                                <input type="hidden" name="id_order" value="{{ $ps['id'] }}">
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

    <!-- Konten utama -->
    <div class="auth-page-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card mt-4 shadow-sm">
                        <div class="card-body p-4 text-center">

                            <!-- Ikon sukses -->
                            <div class="avatar-lg mx-auto mt-2">
                                <div class="avatar-title bg-light text-success display-3 rounded-circle">
                                    <i class="ri-checkbox-circle-fill"></i>
                                </div>
                            </div>

                            <div class="mt-4 pt-2">
                                <h4 class="mb-2">Pembayaran Berhasil</h4>
                                <p class="text-muted mx-4 mb-2">ID Order: #{{ $ps['invoice'] }}</p>
                            </div>

                            <div class="p-2 d-flex justify-content-center mx-auto">
                                <span class="fs-2 fw-semibold bg-light rounded-2 p-2">{{ $ps['total'] }}</span>
                            </div>

                            <hr>

                            <!-- Tabel info transaksi -->
                            <table class="w-100">
                                <tbody>
                                    <tr style="line-height:30px">
                                        <td class="text-start">Tanggal Transaksi</td>
                                        <td class="text-end fw-semibold">{{ $ps['time'] }}</td>
                                    </tr>
                                    <tr style="line-height:30px">
                                        <td class="text-start">Metode Pembayaran</td>
                                        <td class="text-end fw-semibold">{{ $ps['method'] }}</td>
                                    </tr>
                                    <tr style="line-height:30px">
                                        <td class="text-start">No WA Customer</td>
                                        <td class="text-end fw-semibold">{{ $ps['wa'] }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <hr>

                            <!-- Tabel ringkasan nominal -->
                            <table class="w-100">
                                <tbody>
                                    <tr style="line-height:30px">
                                        <td class="text-start">Total Tagihan</td>
                                        <td class="text-end fw-semibold">{{ $ps['total'] }}</td>
                                    </tr>
                                    <tr style="line-height:30px">
                                        <td class="text-start">Total Bayar</td>
                                        <td class="text-end fw-semibold">{{ $ps['paid'] }}</td>
                                    </tr>
                                    <tr style="line-height:30px">
                                        <td class="text-start">Total Kembali</td>
                                        <td class="text-end fw-semibold">{{ $ps['change'] }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Aksi -->
                            <div class="mt-4">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <button id="print" type="button" class="btn btn-outline-warning w-100" onclick="window.open('{{ route('sales-transaction.print_thermal_58', $ps['id']) }}', '_blank')">
                                            <i class="ri-printer-fill align-middle fs-16 me-2"></i>Print
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button type="button" class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#whatsappModal">
                                            <i class="ri-whatsapp-fill align-middle fs-16 me-2"></i>WhatsApp
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <a href="{{ route('sales-transaction.download_pdf_58', $ps['id']) }}" class="btn btn-outline-primary w-100">
                                    <i class="ri-file-paper-2-fill align-middle fs-16 me-2"></i>Download PDF
                                </a>
                            </div>

                            <div class="mt-3">
                                <a href="{{ route('sales-transaction.create') }}" class="btn btn-danger w-100">
                                    <i class="ri-picture-in-picture-fill align-middle fs-16 me-2"></i>Selesai
                                </a>
                            </div>

                        </div>
                    </div>
                    <!-- /card -->
                </div>
            </div>
            <!-- /row -->
        </div>
        <!-- /container -->
    </div>

    <!-- Footer kecil -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center">
                        <p class="mb-0 text-muted">&copy; {{ date('Y') }} SECO INDONESIA</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</div>
@endsection

@section('script')

<script src="{{ URL::asset('build/libs/particles.js/particles.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/particles.app.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/password-addon.init.js') }}"></script>

@endsection