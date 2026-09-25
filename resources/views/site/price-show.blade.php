@extends('layouts.public')
@section('title', $plant->commodityVarietyLabel().' - Informasi Publik')

@push('styles')
<style>
    .seed-hero { border-radius: 18px; overflow: hidden; box-shadow: 0 10px 28px rgba(15,23,42,.08); }
    .seed-hero img { width: 100%; aspect-ratio: 16/9; object-fit: cover; }
    .info-table th { width: 32%; color: #64748b; font-weight: 600; }
</style>
@endpush

@section('content')
<p class="page-kicker mb-1">Informasi Publik</p>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <h1 class="h3 mb-1">{{ $plant->commodityVarietyLabel() }}</h1>
        <div class="text-muted">{{ $row['category'] }}</div>
    </div>
    <a href="{{ route('site.prices') }}" class="btn btn-outline-success"><i class="fas fa-arrow-left me-1"></i>Kembali ke daftar</a>
</div>

<div class="seed-hero mb-4">
    <img src="{{ $plant->publicPhotoUrl() }}" alt="{{ $plant->commodityVarietyLabel() }}">
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Informasi Katalog Benih</div>
            <div class="card-body p-0">
                <table class="table info-table mb-0">
                    <tbody>
                        <tr>
                            <th>Nama komoditas - varietas</th>
                            <td>{{ $plant->commodityVarietyLabel() }}</td>
                        </tr>
                        <tr>
                            <th>Kelas benih</th>
                            <td>{{ $row['kelas'] }}</td>
                        </tr>
                        <tr>
                            <th>Satuan resmi</th>
                            <td>{{ $plant->satuanStok?->code ?: '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tarif retribusi (PAD)</th>
                            <td>{{ $plant->harga_jual !== null ? 'Rp '.number_format((float) $plant->harga_jual, 0, ',', '.') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Ketersediaan gudang</th>
                            <td>
                                @if($row['qty'] > 0)
                                    Tersedia ({{ number_format($row['qty'], 2) }} {{ $plant->satuanStok?->code }})
                                    @if($row['lokasi'])
                                        <div class="small text-muted">{{ $row['lokasi'] }}</div>
                                    @endif
                                @else
                                    Stok Kosong
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Deskripsi Informasi Publik</div>
            <div class="card-body">
                @if($plant->public_description)
                    <p class="mb-0" style="white-space: pre-line;">{{ $plant->public_description }}</p>
                @elseif($plant->description)
                    <p class="mb-0" style="white-space: pre-line;">{{ $plant->description }}</p>
                @else
                    <p class="text-muted mb-0">Belum ada deskripsi publik untuk varietas ini.</p>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Lokasi Penanaman</div>
            <div class="card-body p-0">
                @if($locations->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama lokasi</th>
                                    <th>Alamat / ringkasan</th>
                                    <th>Wilayah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($locations as $location)
                                    <tr>
                                        <td class="fw-semibold">{{ $location->name ?: '-' }}</td>
                                        <td>{{ $location->location_summary ?: ($location->administrative_address ?: '-') }}</td>
                                        <td>
                                            {{ collect([$location->village, $location->district, $location->city, $location->province])->filter()->implode(', ') ?: '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0 p-3">Belum ada data lokasi penanaman untuk varietas ini.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                @if($row['qty'] > 0)
                    <a class="btn btn-emerald w-100 mb-2" href="{{ route('public.seed-requests.create', ['plant' => $plant->getKey()]) }}">
                        <i class="fas fa-file-signature me-1"></i>Ajukan Permintaan
                    </a>
                @else
                    <button type="button" class="btn btn-outline-secondary w-100 mb-2" disabled>Ajukan Permintaan</button>
                @endif
                <p class="small text-muted mb-0">Tarif mengikuti Pergub retribusi daerah yang berlaku.</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Informasi Stok per Nomor Induk</div>
            <div class="card-body p-0">
                @if($stockRows->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No. induk</th>
                                    <th>Stok</th>
                                    <th>Kelas</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stockRows as $stockRow)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $stockRow['nomor_induk'] }}</div>
                                            <div class="small text-muted">
                                                Masa edar:
                                                {{ $stockRow['expired_at'] ? $stockRow['expired_at']->translatedFormat('d M Y') : '-' }}
                                            </div>
                                            @if($stockRow['lokasi'])
                                                <div class="small text-muted">{{ $stockRow['lokasi'] }}</div>
                                            @endif
                                        </td>
                                        <td>{{ number_format($stockRow['qty'], 2) }} {{ $plant->satuanStok?->code }}</td>
                                        <td>{{ $stockRow['kelas'] }}</td>
                                        <td class="text-end">
                                            @if($stockRow['can_certificate'] && $stockRow['stock'])
                                                <a class="btn btn-sm btn-outline-success" href="{{ route('public.seed-certificate', $stockRow['stock']) }}" target="_blank">
                                                    Lihat sertifikat
                                                </a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0 p-3">Belum ada stok benih siap salur untuk varietas ini.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
