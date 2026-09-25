@extends('layouts.app')
@section('title', 'Detail Stok Produk - SIBESTI')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Detail stok produk</h4>
        <small class="text-muted">{{ $packaging->no_label_seri }}</small>
    </div>
    <a href="javascript:history.back()" class="btn btn-secondary">Kembali</a>
</div>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-2"><strong>Tanaman:</strong> {{ $plant->displayName() }}</div>
            <div class="col-md-6 mb-2"><strong>No induk:</strong> {{ $packaging->stock?->nomor_induk ?: '-' }}</div>
            <div class="col-md-6 mb-2"><strong>No label:</strong> {{ $packaging->no_label_seri }}</div>
            <div class="col-md-6 mb-2"><strong>Isi kemasan:</strong> {{ number_format((float) $packaging->kapasitas_per_kemasan, 2) }} {{ $plant->satuanStok?->code }}</div>
            <div class="col-md-6 mb-2"><strong>Status:</strong> {{ $packaging->statusLabel() }}</div>
            <div class="col-md-6 mb-2"><strong>Lokasi:</strong> {{ $packaging->rack?->warehouse?->name ?: '-' }} · {{ $packaging->rack?->name ?: '-' }}</div>
            <div class="col-md-6 mb-2"><strong>Masa kadaluarsa:</strong> {{ $packaging->stock?->tgl_kedaluwarsa?->format('d M Y') ?: '-' }}</div>
            @if($packaging->alasan_penyesuaian)
                <div class="col-md-6 mb-2"><strong>Alasan tidak aktif:</strong> {{ $packaging->alasanPenyesuaianLabel() }}</div>
            @endif
            @php $soldSale = $packaging->status_kemasan === \App\Models\StockPackaging::STATUS_DISALURKAN ? $packaging->soldSale() : null; @endphp
            @if($soldSale)
                <div class="col-md-6 mb-2"><strong>No struk penjualan:</strong> <code>{{ $soldSale->receipt_number }}</code></div>
            @endif
        </div>
        <a href="{{ route('public.seed-label', $packaging) }}" class="btn btn-success mt-3" target="_blank">Lihat label benih</a>
        @if($soldSale)
            <a href="{{ route('sales.show', $soldSale) }}" class="btn btn-outline-info mt-3 ms-2">Lihat struk penjualan</a>
        @endif
    </div>
</div>
@endsection
