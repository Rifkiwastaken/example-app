@extends('layouts.public')
@section('title', 'Status '.$seedRequest->request_number.' - SIBESTI')
@section('content')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h1 class="h4">{{ $seedRequest->request_number }}</h1>
        <p>Status: <span class="badge bg-{{ $seedRequest->statusColor() }}">{{ $seedRequest->statusLabel() }}</span></p>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($seedRequest->status === \App\Models\SeedRequest::STATUS_PENDING)
            <div class="alert alert-warning">Transaksi sedang diproses. Silakan pantau informasi di website.</div>
        @elseif($seedRequest->status === \App\Models\SeedRequest::STATUS_REJECTED)
            <div class="alert alert-danger">Ditolak: {{ $seedRequest->rejection_reason ?: '-' }}</div>
        @elseif(in_array($seedRequest->status, [\App\Models\SeedRequest::STATUS_APPROVED, \App\Models\SeedRequest::STATUS_READY], true))
            <div class="alert alert-success">Benih telah disertifikasi. Silakan jemput benih di UPTD BBI SUMBAR.</div>
        @endif
        <h5 class="mt-3">Informasi pembeli</h5>
        <p class="mb-1">Nama: {{ $seedRequest->buyer_name }} · Instansi: {{ $seedRequest->organization ?: '-' }}</p>
        <p class="mb-1">Kontak: {{ $seedRequest->buyer_contact }} · NIK: {{ $seedRequest->buyer_nik }}</p>
        <p class="mb-3">Tujuan: {{ collect([$seedRequest->destination_village,$seedRequest->destination_district,$seedRequest->destination_city,$seedRequest->destination_province])->filter()->implode(', ') }}</p>
        <h5>Rincian item</h5>
        <div class="table-responsive mb-3">
            <table class="table table-sm">
                <thead><tr><th>Varietas</th><th>Jumlah</th><th>Satuan</th><th>Subtotal</th></tr></thead>
                <tbody>
                    @foreach($seedRequest->items as $item)
                        <tr>
                            <td>{{ $item->plant?->displayName() }}</td>
                            <td>{{ number_format((float) $item->quantity, 2) }}</td>
                            <td>{{ $item->unit }}</td>
                            <td>Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(in_array($seedRequest->status, [\App\Models\SeedRequest::STATUS_APPROVED, \App\Models\SeedRequest::STATUS_READY, \App\Models\SeedRequest::STATUS_TAKEN], true))
            <a href="{{ route('public.seed-requests.receipt', $seedRequest) }}" class="btn btn-success">Lihat / simpan struk</a>
            <h6 class="mt-4">Label benih</h6>
            @foreach($seedRequest->packagings as $pkg)
                <a href="{{ route('public.seed-label', $pkg) }}" class="btn btn-sm btn-outline-success mb-1" target="_blank">Label {{ $pkg->no_label_seri }}</a>
            @endforeach
        @endif
        <a href="{{ route('public.seed-requests.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>
@endsection
