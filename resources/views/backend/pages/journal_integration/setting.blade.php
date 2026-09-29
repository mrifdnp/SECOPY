@extends('layouts.master')
@section('title')
    Edit {{ $titleHeader->name }}
@endsection
@section('content')
    @component('components.breadcrumb')
    @slot('li_1')
    Systems
    @endslot
    @slot('title')
    Edit {{ $titleHeader->name }}
    @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"></i> Filter Outlet Konfigurasi</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <label class="form-label fw-bold mb-0">Pilih Outlet yang akan di-setting:</label>
                        </div>
                        <div class="col-md-5">
                            <select name="outlet_filter" id="outlet_filter" class="form-control select2">
                                <option value="">Global / Default</option>
                                @foreach ($outlets as $ot)
                                    <option value="{{ $ot->id }}" {{ request('outlet_id') == $ot->id ? 'selected' : '' }}>
                                        {{ $ot->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted"><i class="ri-information-line text-info"></i> Halaman akan refresh
                                otomatis saat outlet dipilih.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="dt_form" action="{{ route('journal-integration.edit') }}" class="form-horizontal" method="post"
        enctype="multipart/form-data">
        @csrf
        @method('put')

        {{-- HIDDEN OUTLET ID (Supaya saat SAVE, data masuk ke outlet yang benar) --}}
        <input type="hidden" name="outlet_id" value="{{ request('outlet_id') }}">

        <div class="row">
            <div class="col-lg-12">
                @include('backend.includes.flash')
                <div class="row">
                    <div class="col-sm-12">

                        {{-- CARD 1: METODE PEMBAYARAN --}}
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Mapping Metode Pembayaran dengan Akun</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="col-sm-12 table-responsive">
                                        <table class="table table-borderless table-striped" style="min-width: 700px">
                                            <thead>
                                                <tr>
                                                    <td><b>Data dari Aplikasi</b></td>
                                                    <td><b>Pilih Akun </b></td>
                                                    <td><b>Buat Akun jika belum ada</b></td>
                                                </tr>
                                            </thead>
                                            <tbody id="bodyData">
                                                @foreach ($payments as $key => $payment)
                                                    <tr>
                                                        <td class="align-middle">
                                                            <div class="row">
                                                                <div class="col-12 col-md-10">
                                                                    <input type="text" class="form-control bg-light"
                                                                        value="{{ $payment->outlet_name ? $payment->outlet_name . ' [' . $payment->name . ']' : $payment->name }}"
                                                                        disabled>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <select class="form-control js-example-basic-single forSelectDua"
                                                                name="mapping_payment_account[]">
                                                                <option value="">-------------------------</option>
                                                                @php
                                                                    $list = [
                                                                        'assets',
                                                                        'liability',
                                                                        'equity',
                                                                        'expense',
                                                                        'revenue',
                                                                        'hpp',
                                                                    ];
                                                                @endphp
                                                                @foreach ($dataAccount as $key_acc => $head_account)
                                                                    @if (in_array($key_acc, $list))
                                                                        <option value="{{ $key_acc }}" disabled>
                                                                            {{ strtoupper($head_account) }}
                                                                        </option>
                                                                    @else
                                                                        <option value="{{ $payment->id }}_{{ $key_acc }}" {{ $key_acc == $payment->head_account_id ? 'selected' : '' }}>
                                                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $head_account }}
                                                                        </option>
                                                                    @endif
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control"
                                                                name="mapping_payment_account_add[]"
                                                                placeholder="Masukan Nama Akun">
                                                            <input type="hidden" name="mapping_payment_account_id[]"
                                                                value="{{ $payment->id }}">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- CARD 2: INTEGRASI JURNAL PENJUALAN --}}
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Integrasi Jurnal Transaksi Penjualan</h5>
                                @if (request('outlet_id'))
                                    <div class="form-check form-switch">
                                        @php
                                            $toggleOffUuid = '00000000-0000-0000-0000-000000000003';
                                            $useGlobalSales = $head_account_mapping->firstWhere(
                                                'key',
                                                'use_global_sales',
                                            );
                                            $isGlobalSales =
                                                !$useGlobalSales || $useGlobalSales->debit != $toggleOffUuid;
                                        @endphp
                                        <input class="form-check-input" type="checkbox" id="toggle_global_sales"
                                            name="use_global_sales" value="1" {{ $isGlobalSales ? 'checked' : '' }}>
                                        <label class="form-check-label" for="toggle_global_sales">Ikut Setting
                                            Global</label>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body" id="sales_card_body">

                                @php
                                    $list = ['assets', 'liability', 'equity', 'expense', 'revenue', 'hpp'];

                                    // GROUP 1: PENJUALAN
                                    $sales_labels_penjualan = [
                                        'sales_revenue_before_discount' => 'Pendapatan sebelum diskon',
                                        'sales_discount_nominal' => 'Diskon Rp',
                                        'sales_discount_percent' => 'Diskon %',
                                        'sales_additional_cost' => 'Biaya Tambahan',
                                        'sales_revenue' => 'Total Setelah Diskon & Tambahan',
                                        'sales_tax' => 'Pajak',
                                        'sales_hpp' => 'Harga Pokok Penjualan (HPP)',
                                    ];

                                    // GROUP 2: RETUR
                                    $sales_labels_retur = [
                                        'sales_refund' => 'Retur Penjualan',
                                    ];

                                    // GROUP 3: PEMBAYARAN PIUTANG
                                    $sales_labels_piutang = [
                                        'sales_receivable' => 'Piutang Penjualan',
                                        'sales_payment_collection' => 'Penerimaan Cicilan',
                                    ];
                                @endphp

                                <!-- Nav tabs -->
                                <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#tab-sales" role="tab">
                                            <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                            <span class="d-none d-sm-block">Penjualan</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#tab-return" role="tab">
                                            <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                            <span class="d-none d-sm-block">Retur</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#tab-receivable" role="tab">
                                            <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                                            <span class="d-none d-sm-block">Pembayaran Piutang</span>
                                        </a>
                                    </li>
                                </ul>

                                <!-- Tab panes -->
                                <div class="tab-content text-muted">

                                    {{-- TAB 1: PENJUALAN --}}
                                    <div class="tab-pane active" id="tab-sales" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-borderless table-striped align-middle"
                                                style="min-width: 700px">
                                                <thead>
                                                    <tr>
                                                        <td><b>Data dari Aplikasi</b></td>
                                                        <td></td>
                                                        <td><b>Setting Akun di Debit</b></td>
                                                        <td><b>Setting Akun di Kredit</b></td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($sales_labels_penjualan as $key_label => $text_label)
                                                        @php $data = $head_account_mapping->firstWhere('key', $key_label); @endphp
                                                        <tr>
                                                            <td colspan="2">
                                                                <input type="text" class="form-control"
                                                                    value="{{ $text_label }}" readonly disabled>
                                                                <input type="hidden" name="label[]" value="{{ $key_label }}">
                                                            </td>
                                                            {{-- KOLOM DEBIT --}}
                                                            <td>
                                                                @php
                                                                    // HANYA MATIKAN DEBIT UNTUK: Biaya Tambahan & Pajak
                                                                    // Karena saldo normal mereka ada di KREDIT.
                                                                    $disableDebit = [
                                                                        'sales_additional_cost',
                                                                        'sales_tax',
                                                                    ];
                                                                    $isDebitDisabled = in_array(
                                                                        $key_label,
                                                                        $disableDebit,
                                                                    );
                                                                @endphp

                                                                @if ($isDebitDisabled)
                                                                    {{-- Tampilan Disabled (Garis Putus-putus) --}}
                                                                    <input type="text" class="form-control bg-light text-center"
                                                                        value="————————————————" disabled readonly>
                                                                    {{-- Input Hidden (Supaya urutan array di controller tidak
                                                                    error) --}}
                                                                    <input type="hidden" name="sales_debit[]" value="">
                                                                @else
                                                                    {{-- Tampilan Normal (Select Box) --}}
                                                                    <select class="form-control js-example-basic-single"
                                                                        name="sales_debit[]">
                                                                        <option value="">-------------------------
                                                                        </option>
                                                                        <option value="00000000-0000-0000-0000-000000000001" {{ isset($data->debit) && $data->debit == '00000000-0000-0000-0000-000000000001' ? 'selected' : '' }}>
                                                                            Sesuai Mapping Metode Pembayaran
                                                                        </option>
                                                                        @foreach ($dataAccount as $acc_id => $acc_name)
                                                                            @if (!in_array($acc_id, $list))
                                                                                <option value="{{ $acc_id }}" {{ isset($data->debit) && $data->debit == $acc_id ? 'selected' : '' }}>
                                                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $acc_name }}
                                                                                </option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                @endif
                                                            </td>

                                                            {{-- KOLOM KREDIT --}}
                                                            <td>
                                                                @php
                                                                    // HANYA MATIKAN KREDIT UNTUK: Diskon (Rp & %)
                                                                    // Karena saldo normal diskon ada di DEBIT (Mengurangi omzet).
                                                                    $disableCredit = [
                                                                        'sales_discount_nominal',
                                                                        'sales_discount_percent',
                                                                    ];
                                                                    $isCreditDisabled = in_array(
                                                                        $key_label,
                                                                        $disableCredit,
                                                                    );
                                                                @endphp

                                                                @if ($isCreditDisabled)
                                                                    {{-- Tampilan Disabled (Garis Putus-putus) --}}
                                                                    <input type="text" class="form-control bg-light text-center"
                                                                        value="————————————————" disabled readonly>
                                                                    {{-- Input Hidden --}}
                                                                    <input type="hidden" name="sales_credit[]" value="">
                                                                @else
                                                                    {{-- Tampilan Normal (Select Box) --}}
                                                                    <select class="form-control js-example-basic-single"
                                                                        name="sales_credit[]">
                                                                        <option value="">-------------------------
                                                                        </option>
                                                                        <option value="00000000-0000-0000-0000-000000000001" {{ isset($data->credit) && $data->credit == '00000000-0000-0000-0000-000000000001' ? 'selected' : '' }}>
                                                                            Sesuai Mapping Metode Pembayaran
                                                                        </option>
                                                                        @foreach ($dataAccount as $acc_id => $acc_name)
                                                                            @if (!in_array($acc_id, $list))
                                                                                <option value="{{ $acc_id }}" {{ isset($data->credit) && $data->credit == $acc_id ? 'selected' : '' }}>
                                                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $acc_name }}
                                                                                </option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- TAB 2: RETUR --}}
                                    <div class="tab-pane" id="tab-return" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-borderless table-striped align-middle"
                                                style="min-width: 700px">
                                                <thead>
                                                    <tr>
                                                        <td><b>Data dari Aplikasi</b></td>
                                                        <td></td>
                                                        <td><b>Setting Akun di Debit</b></td>
                                                        <td><b>Setting Akun di Kredit</b></td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($sales_labels_retur as $key_label => $text_label)
                                                        @php $data = $head_account_mapping->firstWhere('key', $key_label); @endphp
                                                        <tr>
                                                            <td colspan="2">
                                                                <input type="text" class="form-control"
                                                                    value="{{ $text_label }}" readonly disabled>
                                                                <input type="hidden" name="label[]" value="{{ $key_label }}">
                                                            </td>
                                                            <td>
                                                                <select class="form-control js-example-basic-single"
                                                                    name="sales_debit[]">
                                                                    <option value="">-------------------------
                                                                    </option>
                                                                    <option value="00000000-0000-0000-0000-000000000001" {{ isset($data->debit) && $data->debit == '00000000-0000-0000-0000-000000000001' ? 'selected' : '' }}>
                                                                        Sesuai Mapping Metode Pembayaran</option>
                                                                    @foreach ($dataAccount as $acc_id => $acc_name)
                                                                        @if (!in_array($acc_id, $list))
                                                                            <option value="{{ $acc_id }}" {{ isset($data->debit) && $data->debit == $acc_id ? 'selected' : '' }}>
                                                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $acc_name }}
                                                                            </option>
                                                                        @endif
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <select class="form-control js-example-basic-single"
                                                                    name="sales_credit[]">
                                                                    <option value="">-------------------------
                                                                    </option>
                                                                    <option value="00000000-0000-0000-0000-000000000001" {{ isset($data->credit) && $data->credit == '00000000-0000-0000-0000-000000000001' ? 'selected' : '' }}>
                                                                        Sesuai Mapping Metode Pembayaran</option>
                                                                    @foreach ($dataAccount as $acc_id => $acc_name)
                                                                        @if (!in_array($acc_id, $list))
                                                                            <option value="{{ $acc_id }}" {{ isset($data->credit) && $data->credit == $acc_id ? 'selected' : '' }}>
                                                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $acc_name }}
                                                                            </option>
                                                                        @endif
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- TAB 3: PEMBAYARAN PIUTANG --}}
                                    <div class="tab-pane" id="tab-receivable" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-borderless table-striped align-middle"
                                                style="min-width: 700px">
                                                <thead>
                                                    <tr>
                                                        <td><b>Data dari Aplikasi</b></td>
                                                        <td></td>
                                                        <td><b>Setting Akun di Debit</b></td>
                                                        <td><b>Setting Akun di Kredit</b></td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($sales_labels_piutang as $key_label => $text_label)
                                                        @php $data = $head_account_mapping->firstWhere('key', $key_label); @endphp
                                                        <tr>
                                                            <td colspan="2">
                                                                <input type="text" class="form-control"
                                                                    value="{{ $text_label }}" readonly disabled>
                                                                <input type="hidden" name="label[]" value="{{ $key_label }}">
                                                            </td>
                                                            <td>
                                                                <select
                                                                    class="form-control js-example-basic-single select-debit"
                                                                    name="sales_debit[]" data-key="{{ $key_label }}">
                                                                    <option value="">-------------------------
                                                                    </option>
                                                                    <option value="00000000-0000-0000-0000-000000000001" {{ isset($data->debit) && $data->debit == '00000000-0000-0000-0000-000000000001' ? 'selected' : '' }}>
                                                                        Sesuai Mapping Metode Pembayaran</option>
                                                                    @foreach ($dataAccount as $acc_id => $acc_name)
                                                                        @if (!in_array($acc_id, $list))
                                                                            <option value="{{ $acc_id }}" {{ isset($data->debit) && $data->debit == $acc_id ? 'selected' : '' }}>
                                                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $acc_name }}
                                                                            </option>
                                                                        @endif
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <select
                                                                    class="form-control js-example-basic-single select-credit"
                                                                    name="sales_credit[]" data-key="{{ $key_label }}">
                                                                    <option value="">-------------------------
                                                                    </option>
                                                                    <option value="00000000-0000-0000-0000-000000000001" {{ isset($data->credit) && $data->credit == '00000000-0000-0000-0000-000000000001' ? 'selected' : '' }}>
                                                                        Sesuai Mapping Metode Pembayaran</option>
                                                                    @foreach ($dataAccount as $acc_id => $acc_name)
                                                                        @if (!in_array($acc_id, $list))
                                                                            <option value="{{ $acc_id }}" {{ isset($data->credit) && $data->credit == $acc_id ? 'selected' : '' }}>
                                                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $acc_name }}
                                                                            </option>
                                                                        @endif
                                                                    @endforeach
                                                                </select>
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



                        {{-- CARD 3: INTEGRASI JURNAL PEMBELIAN --}}
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Integrasi Jurnal Transaksi Pembelian</h5>
                                @if (request('outlet_id'))
                                    <div class="form-check form-switch">
                                        @php
                                            $toggleOffUuid = '00000000-0000-0000-0000-000000000003';
                                            $useGlobalPurchase = $head_account_mapping->firstWhere(
                                                'key',
                                                'use_global_purchase',
                                            );
                                            $isGlobalPurchase =
                                                !$useGlobalPurchase || $useGlobalPurchase->debit != $toggleOffUuid;
                                        @endphp
                                        <input class="form-check-input" type="checkbox" id="toggle_global_purchase"
                                            name="use_global_purchase" value="1" {{ $isGlobalPurchase ? 'checked' : '' }}>
                                        <label class="form-check-label" for="toggle_global_purchase">Sesuai Setting
                                            Global</label>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body" id="purchase_card_body">

                                <div class="form-group">
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#tab-purchase-1"
                                                role="tab">
                                                <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                                <span class="d-none d-sm-block">Pembelian</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#tab-purchase-2" role="tab">
                                                <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                                <span class="d-none d-sm-block">Retur</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#tab-purchase-3" role="tab">
                                                <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                                                <span class="d-none d-sm-block">Pembayaran Hutang</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#tab-purchase-4" role="tab">
                                                <span class="d-block d-sm-none"><i class="ri-shopping-cart-line"></i></span>
                                                <span class="d-none d-sm-block">Purchase Order (PO)</span>
                                            </a>
                                        </li>
                                    </ul>

                                    <!-- Tab panes -->
                                    <div class="tab-content p-3 text-muted">
                                        @php
                                            // 1. PEMBELIAN
                                            $purchase_labels_pembelian = [
                                                'purchase_revenue_before_discount' => 'Pembelian sebelum diskon',
                                                'purchase_discount_nominal' => 'Diskon Rp',
                                                'purchase_discount_percent' => 'Diskon %',
                                                'purchase_additional_cost' => 'Biaya Tambahan',
                                                'purchase_revenue' => 'Total Setelah Diskon & Tambahan',
                                                'purchase_tax' => 'Pajak',
                                            ];

                                            // 2. RETUR
                                            $purchase_labels_retur = [
                                                'purchase_refund' => 'Retur Pembelian',
                                            ];

                                            // 3. PEMBAYARAN HUTANG
                                            $purchase_labels_hutang = [
                                                'purchase_payable' => 'Hutang Pembelian',
                                                'purchase_payment_disbursement' => 'Pembayaran Cicilan',
                                            ];

                                            // 4. PURCHASE ORDER (PO)
                                            $purchase_labels_po = [
                                                'purchase_po_advance' => 'Uang Muka PO (saat buat PO)',
                                                'purchase_po_receipt' => 'Penerimaan Barang PO',
                                                'purchase_po_advance_clear' => 'Pelunasan Uang Muka',
                                                'purchase_po_payable' => 'Sisa Hutang PO',
                                            ];
                                        @endphp

                                        {{-- TAB 1: PEMBELIAN --}}
                                        <div class="tab-pane active" id="tab-purchase-1" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-borderless table-striped align-middle"
                                                    style="min-width: 700px">
                                                    <thead>
                                                        <tr>
                                                            <td style="width: 25%"><b>Data dari Aplikasi</b></td>
                                                            <td style="width: 5%"></td>
                                                            <td style="width: 35%"><b>Setting Akun di Debit</b></td>
                                                            <td style="width: 35%"><b>Setting Akun di Kredit</b></td>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($purchase_labels_pembelian as $key_label => $text_label)
                                                            @php
                                                                $data = $head_account_mapping->firstWhere(
                                                                    'key',
                                                                    $key_label,
                                                                );

                                                                // LOGIC DISABLE KHUSUS PEMBELIAN
                                                                // Debit Mati untuk: Diskon & Total Bayar (Karena saldo normalnya Kredit)
                                                                $disableDebit = [
                                                                    'purchase_discount_nominal',
                                                                    'purchase_discount_percent',
                                                                ];

                                                                // Kredit Mati untuk: Pembelian Awal, Biaya Tambahan, Pajak (Karena saldo normalnya Debit)
                                                                $disableCredit = [
                                                                    'purchase_additional_cost',
                                                                    'purchase_tax',
                                                                ];

                                                                $isDebitDisabled = in_array($key_label, $disableDebit);
                                                                $isCreditDisabled = in_array(
                                                                    $key_label,
                                                                    $disableCredit,
                                                                );
                                                            @endphp
                                                            <tr>
                                                                <td colspan="2">
                                                                    <input type="text" class="form-control"
                                                                        value="{{ $text_label }}" readonly disabled>
                                                                    <input type="hidden" name="purchase_label[]"
                                                                        value="{{ $key_label }}">
                                                                </td>

                                                                {{-- KOLOM DEBIT --}}
                                                                <td>
                                                                    @if ($isDebitDisabled)
                                                                        <input type="text" class="form-control bg-light text-center"
                                                                            value="————————————————" disabled readonly>
                                                                        <input type="hidden" name="purchase_debit[]" value="">
                                                                    @else
                                                                        <select class="form-control js-example-basic-single"
                                                                            name="purchase_debit[]">
                                                                            <option value="">
                                                                                -------------------------</option>
                                                                            @foreach ($dataAccount as $acc_id => $acc_name)
                                                                                @if (!in_array($acc_id, $list))
                                                                                    <option value="{{ $acc_id }}" {{ isset($data->debit) && $data->debit == $acc_id ? 'selected' : '' }}>
                                                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $acc_name }}
                                                                                    </option>
                                                                                @endif
                                                                            @endforeach
                                                                        </select>
                                                                    @endif
                                                                </td>

                                                                {{-- KOLOM KREDIT --}}
                                                                <td>
                                                                    @if ($isCreditDisabled)
                                                                        <input type="text" class="form-control bg-light text-center"
                                                                            value="————————————————" disabled readonly>
                                                                        <input type="hidden" name="purchase_credit[]" value="">
                                                                    @else
                                                                        <select class="form-control js-example-basic-single"
                                                                            name="purchase_credit[]">
                                                                            <option value="">
                                                                                -------------------------</option>
                                                                            <option value="00000000-0000-0000-0000-000000000001" {{ isset($data->credit) && $data->credit == '00000000-0000-0000-0000-000000000001' ? 'selected' : '' }}>
                                                                                Sesuai Mapping Metode Pembayaran
                                                                            </option>
                                                                            @foreach ($dataAccount as $acc_id => $acc_name)
                                                                                @if (!in_array($acc_id, $list))
                                                                                    <option value="{{ $acc_id }}" {{ isset($data->credit) && $data->credit == $acc_id ? 'selected' : '' }}>
                                                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $acc_name }}
                                                                                    </option>
                                                                                @endif
                                                                            @endforeach
                                                                        </select>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        {{-- TAB 2: RETUR --}}
                                        <div class="tab-pane" id="tab-purchase-2" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-borderless table-striped align-middle"
                                                    style="min-width: 700px">
                                                    <thead>
                                                        <tr>
                                                            <td style="width: 25%"><b>Data dari Aplikasi</b></td>
                                                            <td style="width: 5%"></td>
                                                            <td style="width: 35%"><b>Setting Akun di Debit</b></td>
                                                            <td style="width: 35%"><b>Setting Akun di Kredit</b></td>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($purchase_labels_retur as $key_label => $text_label)
                                                            @php $data = $head_account_mapping->firstWhere('key', $key_label); @endphp
                                                            <tr>
                                                                <td colspan="2">
                                                                    <input type="text" class="form-control"
                                                                        value="{{ $text_label }}" readonly disabled>
                                                                    <input type="hidden" name="purchase_label[]"
                                                                        value="{{ $key_label }}">
                                                                </td>
                                                                <td>
                                                                    <select class="form-control js-example-basic-single"
                                                                        name="purchase_debit[]">
                                                                        <option value="">-------------------------
                                                                        </option>
                                                                        <option value="00000000-0000-0000-0000-000000000001" {{ isset($data->debit) && $data->debit == '00000000-0000-0000-0000-000000000001' ? 'selected' : '' }}>
                                                                            Sesuai Mapping Metode Pembayaran</option>
                                                                        @foreach ($dataAccount as $acc_id => $acc_name)
                                                                            @if (!in_array($acc_id, $list))
                                                                                <option value="{{ $acc_id }}" {{ isset($data->debit) && $data->debit == $acc_id ? 'selected' : '' }}>
                                                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $acc_name }}
                                                                                </option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <select class="form-control js-example-basic-single"
                                                                        name="purchase_credit[]">
                                                                        <option value="">-------------------------
                                                                        </option>
                                                                        <option value="00000000-0000-0000-0000-000000000001" {{ isset($data->credit) && $data->credit == '00000000-0000-0000-0000-000000000001' ? 'selected' : '' }}>
                                                                            Sesuai Mapping Metode Pembayaran</option>
                                                                        @foreach ($dataAccount as $acc_id => $acc_name)
                                                                            @if (!in_array($acc_id, $list))
                                                                                <option value="{{ $acc_id }}" {{ isset($data->credit) && $data->credit == $acc_id ? 'selected' : '' }}>
                                                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $acc_name }}
                                                                                </option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        {{-- TAB 3: PEMBAYARAN HUTANG --}}
                                        <div class="tab-pane" id="tab-purchase-3" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-borderless table-striped align-middle"
                                                    style="min-width: 700px">
                                                    <thead>
                                                        <tr>
                                                            <td style="width: 25%"><b>Data dari Aplikasi</b></td>
                                                            <td style="width: 5%"></td>
                                                            <td style="width: 35%"><b>Setting Akun di Debit</b></td>
                                                            <td style="width: 35%"><b>Setting Akun di Kredit</b></td>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($purchase_labels_hutang as $key_label => $text_label)
                                                            @php $data = $head_account_mapping->firstWhere('key', $key_label); @endphp
                                                            <tr>
                                                                <td colspan="2">
                                                                    <input type="text" class="form-control"
                                                                        value="{{ $text_label }}" readonly disabled>
                                                                    <input type="hidden" name="purchase_label[]"
                                                                        value="{{ $key_label }}">
                                                                </td>
                                                                <td>
                                                                    <select
                                                                        class="form-control js-example-basic-single select-purchase-debit"
                                                                        name="purchase_debit[]" data-key="{{ $key_label }}">
                                                                        <option value="">-------------------------
                                                                        </option>
                                                                        <option value="00000000-0000-0000-0000-000000000001" {{ isset($data->debit) && $data->debit == '00000000-0000-0000-0000-000000000001' ? 'selected' : '' }}>
                                                                            Sesuai Mapping Metode Pembayaran</option>
                                                                        @foreach ($dataAccount as $acc_id => $acc_name)
                                                                            @if (!in_array($acc_id, $list))
                                                                                <option value="{{ $acc_id }}" {{ isset($data->debit) && $data->debit == $acc_id ? 'selected' : '' }}>
                                                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $acc_name }}
                                                                                </option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <select
                                                                        class="form-control js-example-basic-single select-purchase-credit"
                                                                        name="purchase_credit[]" data-key="{{ $key_label }}">
                                                                        <option value="">-------------------------
                                                                        </option>
                                                                        <option value="00000000-0000-0000-0000-000000000001" {{ isset($data->credit) && $data->credit == '00000000-0000-0000-0000-000000000001' ? 'selected' : '' }}>
                                                                            Sesuai Mapping Metode Pembayaran</option>
                                                                        @foreach ($dataAccount as $acc_id => $acc_name)
                                                                            @if (!in_array($acc_id, $list))
                                                                                <option value="{{ $acc_id }}" {{ isset($data->credit) && $data->credit == $acc_id ? 'selected' : '' }}>
                                                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $acc_name }}
                                                                                </option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        {{-- TAB 4: Purchase ORDER (PO) --}}
                                        <div class="tab-pane" id="tab-purchase-4" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-borderless table-striped align-middle"
                                                    style="min-width: 700px">
                                                    <thead>
                                                        <tr>
                                                            <td style="width: 25%"><b>Data dari Aplikasi</b></td>
                                                            <td style="width: 5%"></td>
                                                            <td style="width: 35%"><b>Setting Akun di Debit</b></td>
                                                            <td style="width: 35%"><b>Setting Akun di Kredit</b></td>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($purchase_labels_po as $key_label => $text_label)
                                                            @php
                                                                $data = $head_account_mapping->firstWhere(
                                                                    'key',
                                                                    $key_label,
                                                                );

                                                                $disableDebit = [
                                                                    'purchase_po_advance_clear',
                                                                    'purchase_po_payable',
                                                                ];

                                                                $disableCredit = ['purchase_po_receipt'];

                                                                $isDebitDisabled = in_array($key_label, $disableDebit);
                                                                $isCreditDisabled = in_array(
                                                                    $key_label,
                                                                    $disableCredit,
                                                                );
                                                            @endphp
                                                            <tr>
                                                                <td colspan="2">
                                                                    <input type="text" class="form-control"
                                                                        value="{{ $text_label }}" readonly disabled>
                                                                    <input type="hidden" name="purchase_label[]"
                                                                        value="{{ $key_label }}">
                                                                </td>

                                                                {{-- KOLOM DEBIT --}}
                                                                <td>
                                                                    @if ($isDebitDisabled)
                                                                        <input type="text" class="form-control bg-light text-center"
                                                                            value="————————————————" disabled readonly>
                                                                        <input type="hidden" name="purchase_debit[]" value="">
                                                                    @else
                                                                        <select
                                                                            class="form-control js-example-basic-single select-purchase-debit"
                                                                            name="purchase_debit[]" data-key="{{ $key_label }}">
                                                                            <option value="">
                                                                                -------------------------</option>
                                                                            @foreach ($dataAccount as $acc_id => $acc_name)
                                                                                @if (!in_array($acc_id, $list))
                                                                                    <option value="{{ $acc_id }}" {{ isset($data->debit) && $data->debit == $acc_id ? 'selected' : '' }}>
                                                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $acc_name }}
                                                                                    </option>
                                                                                @endif
                                                                            @endforeach
                                                                        </select>
                                                                    @endif
                                                                </td>

                                                                {{-- KOLOM KREDIT --}}
                                                                <td>
                                                                    @if ($isCreditDisabled)
                                                                        <input type="text" class="form-control bg-light text-center"
                                                                            value="————————————————" disabled readonly>
                                                                        <input type="hidden" name="purchase_credit[]" value="">
                                                                    @else
                                                                        <select
                                                                            class="form-control js-example-basic-single select-purchase-credit"
                                                                            name="purchase_credit[]" data-key="{{ $key_label }}">
                                                                            <option value="">
                                                                                -------------------------</option>
                                                                            <option value="00000000-0000-0000-0000-000000000001" {{ isset($data->credit) && $data->credit == '00000000-0000-0000-0000-000000000001' ? 'selected' : '' }}>
                                                                                Sesuai Mapping Metode Pembayaran
                                                                            </option>
                                                                            @foreach ($dataAccount as $acc_id => $acc_name)
                                                                                @if (!in_array($acc_id, $list))
                                                                                    <option value="{{ $acc_id }}" {{ isset($data->credit) && $data->credit == $acc_id ? 'selected' : '' }}>
                                                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $acc_name }}
                                                                                    </option>
                                                                                @endif
                                                                            @endforeach
                                                                        </select>
                                                                    @endif
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
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="box">
                            <div class="indoit-box-action">
                                <div class="col-sm-offset-2">
                                    <div class="indoit-box-action-btn">
                                        <a href="{{ route('outletsetting.index') }}" class="btn btn-soft-secondary">Back
                                            to List</a>
                                        <button type="submit" class="btn btn-primary">Save Configuration</button>
                                    </div>
                                </div>
                            </div>
                            <div class="indoit-box-">
                                <hr>
                                <span class="">*</span> Field Required
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection

@section('script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            // Inisialisasi Select2
            $('.select2').select2();
            $('.js-example-basic-single').select2();

            // LOGIKA AUTO-RELOAD SAAT GANTI OUTLET
            $('#outlet_filter').on('change', function () {
                let outletId = $(this).val();
                let url = "{{ route('journal-integration.index') }}";
                window.location.href = outletId ? url + '?outlet_id=' + outletId : url;
            });

            // === TOGGLE IKUT GLOBAL - SALES ===
            function toggleSalesFields() {
                let isGlobal = $('#toggle_global_sales').is(':checked');
                $('#sales_card_body select').prop('disabled', isGlobal);
                if (isGlobal) {
                    $('#sales_card_body').css('opacity', '0.6');
                } else {
                    $('#sales_card_body').css('opacity', '1');
                }
            }

            // === TOGGLE IKUT GLOBAL - PURCHASE ===
            function togglePurchaseFields() {
                let isGlobal = $('#toggle_global_purchase').is(':checked');
                $('#purchase_card_body select').prop('disabled', isGlobal);
                if (isGlobal) {
                    $('#purchase_card_body').css('opacity', '0.6');
                } else {
                    $('#purchase_card_body').css('opacity', '1');
                }
            }

            // Initial state
            toggleSalesFields();
            togglePurchaseFields();

            // On toggle change
            $('#toggle_global_sales').on('change', toggleSalesFields);
            $('#toggle_global_purchase').on('change', togglePurchaseFields);
        });
        $('.source-piutang').on('change', function () {
            let selectedValue = $(this).val();

            // Set value di select yang disabled (untuk tampilan)
            $('.target-penerimaan').val(selectedValue).trigger('change');

            // Set value di hidden input (untuk data yang dikirim ke server)
            $('.hidden-penerimaan').val(selectedValue);
        });

        // Trigger saat halaman pertama kali dimuat agar sinkron jika sudah ada data
        if ($('.source-piutang').val() !== "") {
            let initialVal = $('.source-piutang').val();
            $('.target-penerimaan').val(initialVal).trigger('change');
            $('.hidden-penerimaan').val(initialVal);
        }
    </script>
    <script>
        $(document).ready(function () {
            // Definisi Key sesuai array Bapak
            var keyPiutang = 'sales_receivable'; // Baris 1
            var keyCicilan = 'sales_payment_collection'; // Baris 2

            // Ambil elemen spesifik
            // 1. Ambil Dropdown DEBIT milik sales_receivable
            var $sourceDropdown = $('.select-debit[data-key="' + keyPiutang + '"]');

            // 2. Ambil Dropdown KREDIT milik sales_payment_collection
            var $targetDropdown = $('.select-credit[data-key="' + keyCicilan + '"]');

            // Fungsi Sinkronisasi
            function syncMirroring() {
                var value = $sourceDropdown.val(); // Ambil nilai dari Debit Piutang

                // Masukkan nilai ke Kredit Cicilan
                // .trigger('change') wajib ada karena Bapak pakai Select2
                $targetDropdown.val(value).trigger('change');
            }

            // Jalankan fungsi saat Debit Piutang diganti user
            $sourceDropdown.on('change', function () {
                syncMirroring();
            });


            var $select2Container = $targetDropdown.next('.select2');

            // Beri gaya "Disabled"
            $select2Container.css({
                'pointer-events': 'none', // Gak bisa diklik
                'opacity': '0.6', // Agak transparan
                'background-color': '#e9ecef' // Warna abu-abu
            });


            syncMirroring();



            // Definisi Key sesuai array Controller
            var keyHutang = 'purchase_payable'; // Hutang Pembelian
            var keyBayar = 'purchase_payment_disbursement'; // Pembayaran Cicilan


            var $sourceHutang = $('.select-purchase-credit[data-key="' + keyHutang + '"]');

            // TARGET: Pembayaran ada di sisi DEBIT
            var $targetBayar = $('.select-purchase-debit[data-key="' + keyBayar + '"]');

            function syncMirroringHutang() {
                var value = $sourceHutang.val();
                $targetBayar.val(value).trigger('change');
            }

            // Event Listener
            $sourceHutang.on('change', function () {
                syncMirroringHutang();
            });

            // Disable Visual untuk Target
            var $select2ContainerHutang = $targetBayar.next('.select2');
            $select2ContainerHutang.css({
                'pointer-events': 'none',
                'opacity': '0.6',
                'background-color': '#e9ecef'
            });

            // Jalankan saat load
            syncMirroringHutang();

            var keyUangMuka = 'purchase_po_advance'; // Sumber: Debit (Saat Buat PO)
            var keyPelunasan = 'purchase_po_advance_clear'; // Target: Kredit (Saat Pelunasan)

            // Ambil Elemen
            // Sumber: Dropdown DEBIT pada baris Uang Muka
            var $sourcePO = $('.select-purchase-debit[data-key="' + keyUangMuka + '"]');

            // Target: Dropdown KREDIT pada baris Pelunasan
            var $targetPO = $('.select-purchase-credit[data-key="' + keyPelunasan + '"]');

            // Fungsi Sinkronisasi PO
            function syncMirroringPO() {
                var value = $sourcePO.val();
                // Set value ke target dan trigger change agar Select2 update visualnya
                $targetPO.val(value).trigger('change');
            }

            // Event Listener: Jika Sumber diganti, Target ikut ganti
            $sourcePO.on('change', function () {
                syncMirroringPO();
            });

            // Matikan akses user ke Target (Visual Disabled)
            var $select2ContainerPO = $targetPO.next('.select2');
            $select2ContainerPO.css({
                'pointer-events': 'none', // Tidak bisa diklik
                'opacity': '0.6', // Tampilan redup
                'background-color': '#e9ecef' // Warna abu-abu
            });

            // Jalankan saat halaman pertama kali dimuat (untuk load data saved)
            syncMirroringPO();
        });
    </script>
@endsection