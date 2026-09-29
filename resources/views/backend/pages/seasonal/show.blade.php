@extends('layouts.master')
@section('title')
    Detail {{ $titleHeader->name }}
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Systems
        @endslot
        @slot('title')
            Detail {{ $titleHeader->name }}
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">{{ $seasonal->name }}</h6>
                    @php
                        $today = now()->toDateString();
                        $isCurrentlyActive = $seasonal->is_active && $seasonal->start_date <= $today && $seasonal->end_date >= $today;
                    @endphp
                    @if($isCurrentlyActive)
                        <span class="badge bg-success">Aktif</span>
                    @elseif($seasonal->is_active)
                        <span class="badge bg-warning">Terjadwal</span>
                    @else
                        <span class="badge bg-secondary">Tidak Aktif</span>
                    @endif
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td style="width: 180px;" class="fw-semibold">Nama</td>
                            <td>{{ $seasonal->name }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Deskripsi</td>
                            <td>{{ $seasonal->description ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Tanggal Mulai</td>
                            <td>{{ \Carbon\Carbon::parse($seasonal->start_date)->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Tanggal Berakhir</td>
                            <td>{{ \Carbon\Carbon::parse($seasonal->end_date)->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Jumlah Produk</td>
                            <td>{{ $seasonal->products_count }} produk</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Banner</td>
                            <td>
                                @if($seasonal->banner_image)
                                    <img src="{{ asset('storage/seasonal/' . $seasonal->banner_image) }}" alt="Banner" style="max-height: 150px; border-radius: 8px;">
                                @else
                                    <span class="text-muted">Belum ada banner</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Dibuat oleh</td>
                            <td>{{ $seasonal->created_by ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Dibuat pada</td>
                            <td>{{ $seasonal->created_at ? $seasonal->created_at->format('d F Y H:i:s') : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <a href="{{ route('seasonal.index') }}" class="btn btn-light mb-3"><i class="las la-arrow-left"></i> Kembali</a>
        </div>
    </div>
@endsection
