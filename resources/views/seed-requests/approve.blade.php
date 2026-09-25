@extends('layouts.app')
@section('title', 'Setujui Permintaan - SIBESTI')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">Setujui {{ $seedRequest->request_number }}</h4>
    <a href="{{ route('seed-requests.show', $seedRequest) }}" class="btn btn-secondary">Kembali</a>
</div>
@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<div class="card mb-3">
    <div class="card-header">Informasi pembeli</div>
    <div class="card-body">
        <p class="mb-1">{{ $seedRequest->buyer_name }} · {{ $seedRequest->organization }} · {{ $seedRequest->buyer_contact }}</p>
        <p class="mb-1">NIK: {{ $seedRequest->buyer_nik ?: '-' }} · Kategori: {{ $seedRequest->buyer_category }}</p>
        <p class="mb-0">Tujuan: {{ collect([$seedRequest->destination_village,$seedRequest->destination_district,$seedRequest->destination_city,$seedRequest->destination_province])->filter()->implode(', ') }}</p>
    </div>
</div>
<div class="card mb-3">
    <div class="card-header">Rincian item yang diminta</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Tanaman - varietas</th><th>Jumlah</th><th>Satuan</th><th>Harga per satuan</th><th>Subtotal</th></tr></thead>
            <tbody>
                @foreach($seedRequest->items as $item)
                    <tr>
                        <td>{{ $item->plant?->displayName() }}</td>
                        <td>{{ number_format((float) $item->quantity, 2) }}</td>
                        <td>{{ $item->unit }}</td>
                        <td>Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<form method="POST" action="{{ route('seed-requests.approve', $seedRequest) }}">
    @csrf
    <div class="card mb-3">
        <div class="card-header">Produk yang ditahan (FEFO, dapat diganti)</div>
        <div class="card-body">
            @foreach($seedRequest->packagings as $pkg)
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="packaging_ids[]" value="{{ $pkg->id }}" checked>
                    <label class="form-check-label">{{ $pkg->stock?->plant?->displayName() }} · {{ $pkg->stock?->nomor_induk }} · {{ $pkg->no_label_seri }} · {{ number_format((float) $pkg->kapasitas_per_kemasan, 2) }}</label>
                </div>
            @endforeach
            <hr>
            <p class="small text-muted">Atau pilih produk aktif lain dari jenis benih yang sama. Jumlah kemasan harus tetap {{ $seedRequest->packagings->count() }}.</p>
            @foreach($alternatives as $pkg)
                @if($seedRequest->packagings->contains('id', $pkg->id)) @continue @endif
                <div class="form-check mb-1">
                    <input class="form-check-input" type="checkbox" name="packaging_ids[]" value="{{ $pkg->id }}">
                    <label class="form-check-label">{{ $pkg->stock?->plant?->displayName() }} · {{ $pkg->stock?->nomor_induk }} · {{ $pkg->no_label_seri }} · {{ number_format((float) $pkg->kapasitas_per_kemasan, 2) }}</label>
                </div>
            @endforeach
        </div>
    </div>
    <button class="btn btn-success">Konfirmasi permintaan benih</button>
</form>
@endsection
