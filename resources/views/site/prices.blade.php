@extends('layouts.public')
@section('title', 'Informasi Publik - '.$situs->office_name)

@push('styles')
<style>
    .seed-price-card { border: 0; border-radius: 18px; overflow: hidden; box-shadow: 0 10px 28px rgba(15,23,42,.08); height: 100%; }
    .seed-price-card img { width: 100%; aspect-ratio: 4/3; object-fit: cover; background: #ecfdf5; }
    .seed-price-card .card-body { display: flex; flex-direction: column; }
    .seed-meta { font-size: .9rem; }
    .seed-meta dt { color: #64748b; font-weight: 600; width: 42%; }
    .seed-meta dd { margin-bottom: .4rem; }
    .category-title { font-size: 1.15rem; font-weight: 800; color: #065f46; border-left: 4px solid #f59e0b; padding-left: .75rem; }
</style>
@endpush

@section('content')
<p class="page-kicker mb-1">Informasi Publik</p>
<h1 class="h3 mb-3">Daftar Retribusi Harga Komoditas Benih</h1>
<div class="card border-0 shadow-sm mb-4" style="background:#ecfdf5;">
    <div class="card-body">
        <div class="fw-bold text-success mb-1">Pergub Retribusi Daerah</div>
        <p class="mb-0">{{ $situs->retribusi_note }}</p>
    </div>
</div>

@forelse($groups as $category => $rows)
    <div class="mb-5">
        <h2 class="category-title mb-3">{{ $category }}</h2>
        <div class="row g-4">
            @foreach($rows as $row)
                @php $plant = $row['plant']; @endphp
                <div class="col-md-6 col-xl-4">
                    <div class="card seed-price-card">
                        <img src="{{ $plant->publicPhotoUrl() }}" alt="{{ $plant->commodityVarietyLabel() }}">
                        <div class="card-body">
                            <h3 class="h5 mb-3">{{ $plant->commodityVarietyLabel() }}</h3>
                            <dl class="row seed-meta mb-3">
                                <dt class="col-5">Kelas benih</dt>
                                <dd class="col-7">{{ $row['kelas'] }}</dd>
                                <dt class="col-5">Satuan benih</dt>
                                <dd class="col-7">{{ $plant->satuanStok?->code ?: '-' }}</dd>
                                <dt class="col-5">Tarif retribusi</dt>
                                <dd class="col-7">{{ $plant->harga_jual !== null ? 'Rp '.number_format((float) $plant->harga_jual, 0, ',', '.') : '-' }}</dd>
                                <dt class="col-5">Ketersediaan</dt>
                                <dd class="col-7">
                                    @if($row['qty'] > 0)
                                        <span class="text-success fw-semibold">Tersedia</span>
                                        @if($row['lokasi'])
                                            <div class="small text-muted">{{ $row['lokasi'] }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted">Stok Kosong</span>
                                    @endif
                                </dd>
                            </dl>
                            <div class="mt-auto d-grid gap-2">
                                @if($row['qty'] > 0)
                                    <a class="btn btn-emerald" href="{{ route('public.seed-requests.create', ['plant' => $plant->getKey()]) }}">
                                        <i class="fas fa-file-signature me-1"></i>Ajukan Permintaan
                                    </a>
                                @else
                                    <button type="button" class="btn btn-outline-secondary" disabled>Ajukan Permintaan</button>
                                @endif
                                <a class="btn btn-outline-success" href="{{ route('site.prices.show', $plant) }}">
                                    <i class="fas fa-circle-info me-1"></i>Lihat Detail Informasi Benih
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@empty
    <div class="alert alert-light border">Belum ada data komoditas benih.</div>
@endforelse
@endsection
