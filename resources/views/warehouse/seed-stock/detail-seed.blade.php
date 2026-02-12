@extends('layouts.app')

@section('title', 'Detail Benih - SIBESTI')

@section('content')
@php
    $plantName = $seed->plant?->name ?? $certificationReport?->certification?->harvest?->plant?->name ?? $certificationReport?->certification?->plant?->name ?? 'Benih';
    $plantVariety = $seed->plant?->variety ?? $certificationReport?->certification?->harvest?->plant?->variety ?? $certificationReport?->certification?->plant?->variety ?? null;
    $locationName = $seed->plantingLocation?->name ?? $certificationReport?->certification?->plantingLocation?->name ?? $certificationReport?->certification?->harvest?->location?->name ?? '-';
@endphp
<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('seed-stock.index') }}">Stok Benih</a></li>
        <li class="breadcrumb-item"><a href="{{ route('seed-stock.show', $inventoryType) }}">{{ $inventoryType->name }}</a></li>
        <li class="breadcrumb-item active">Detail Benih</li>
    </ol>
</nav>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Detail Benih</h4>
        <small class="text-muted">
            {{ $plantName }} -
            {{ $plantVariety ?: 'Tanpa Varietas' }}
        </small>
    </div>
    <div>
        <a href="{{ route('seed-stock.seed-history', ['inventoryType' => $inventoryType, 'seed' => $seed]) }}" class="btn btn-outline-info">
            <i class="fas fa-history me-2"></i>Riwayat
        </a>
        <a href="{{ route('seed-stock.show', $inventoryType) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<!-- Bagian 1: Data Stok Benih -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-seedling me-2"></i>Data Stok Benih</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Nama Benih</label>
                <p class="mb-0">{{ $plantName }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Varietas</label>
                <p class="mb-0">{{ $plantVariety ?: '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Asal Lokasi Penanaman/Produksi</label>
                <p class="mb-0">{{ $locationName }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Jenis Laporan BPSB</label>
                <p class="mb-0">{{ $seed->report_type ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Nomor Penyimpanan</label>
                <p class="mb-0">{{ $seed->storage_number ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Satuan Benih</label>
                <p class="mb-0">{{ ucfirst($seed->seed_unit ?? 'kg') }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Jumlah Inventaris</label>
                <p class="mb-0"><strong>{{ number_format($seed->total_seed_quantity ?? $seed->quantity, 2) }} {{ $seed->total_seed_unit ?? $seed->seed_unit ?? 'kg' }}</strong></p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Tanggal Kadaluarsa</label>
                <p class="mb-0">
                    @if($seed->expiry_date)
                        {{ $seed->expiry_date->format('d M Y') }}
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Tanggal Ditambahkan</label>
                <p class="mb-0">{{ $seed->created_at->format('d M Y H:i') }}</p>
            </div>
            @if($seed->estimated_sale_price_per_kg)
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Estimasi Penjualan per Unit</label>
                <p class="mb-0">Rp {{ number_format($seed->estimated_sale_price_per_kg, 2, ',', '.') }}</p>
            </div>
            @endif
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Pengisi Data</label>
                <p class="mb-0">{{ $seed->filledByUser->name ?? '-' }}</p>
            </div>
            @if($seed->edited_at)
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Terakhir Di Edit</label>
                <p class="mb-0">
                    {{ $seed->edited_at->format('d M Y H:i') }}
                    @if($seed->editor)
                        <br><small class="text-muted">Oleh: {{ $seed->editor->name }}</small>
                    @endif
                </p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Bagian 2: Data Sertifikasi -->
@if($certificationReport)
@php
    $harvest = $certificationReport->certification->harvest;
    $plant = $harvest->plant ?? null;
    $planting = $harvest->planting ?? null;
    $location = $harvest->location ?? null;
    $certification = $certificationReport->certification;
@endphp
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-certificate me-2"></i>Data Sertifikasi</h5>
    </div>
    <div class="card-body">
        <!-- Informasi Dasar Laporan -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Dasar Laporan</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nomor Laporan BPSB</label>
                            <p class="mb-0">{{ $certificationReport->report_number_bpsb ?: '-' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tanggal Laporan</label>
                            <p class="mb-0">{{ $certificationReport->report_date->format('d M Y') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Musim Tanam</label>
                            <p class="mb-0">{{ $certificationReport->growing_season ?: '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Fase Pemeriksaan</label>
                            <p class="mb-0"><span class="badge bg-info">{{ $certificationReport->inspection_phase }}</span></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Petugas Pengawas Mutu (BPSB)</label>
                            <p class="mb-0">{{ $certificationReport->inspector_name ?: '-' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kesimpulan</label>
                            <p class="mb-0">
                                <span class="badge {{ $certificationReport->conclusion_badge_class }}">
                                    {{ $certificationReport->conclusion ?: 'Belum Ditentukan' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagian B: Lot Produksi yang Diperiksa -->
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-seedling me-2"></i>Bagian B: Lot Produksi yang Diperiksa</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Produsen Benih</label>
                        <p class="mb-0">{{ $plant ? ($plant->type?->name ?: $plant->name) : '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Varietas</label>
                        <p class="mb-0">{{ $plant && $plant->variety ? $plant->variety : 'Tanpa Varietas' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Lokasi</label>
                        <p class="mb-0">{{ $location ? $location->name : '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Status Sertifikasi</label>
                        <p class="mb-0">
                            <span class="badge 
                                @if($certification->certification_status == 'lulus') bg-success
                                @elseif($certification->certification_status == 'tidak_lulus') bg-danger
                                @elseif($certification->certification_status == 'dalam_proses') bg-warning
                                @else bg-secondary
                                @endif">
                                {{ $certification->status_label }}
                            </span>
                        </p>
                    </div>
                    @if($planting)
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tanggal Tanam</label>
                        <p class="mb-0">{{ $planting->planting_date ? \Carbon\Carbon::parse($planting->planting_date)->format('d M Y') : '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Luas Tanam</label>
                        <p class="mb-0">
                            @if($planting->area && $planting->area_unit)
                                {{ number_format($planting->area, 2) }} {{ $planting->area_unit }}
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Hasil Pemeriksaan -->
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Bagian C: Hasil Pemeriksaan</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Kelas Benih yang Dihasilkan</label>
                        <p class="mb-0">
                            @if($certificationReport->seed_class_result)
                                <span class="badge bg-info">{{ $certificationReport->seed_class_result }}</span>
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Sifat Tanaman Sesuai Varietas</label>
                        <p class="mb-0">
                            @if($certificationReport->plant_characteristics_match !== null)
                                <span class="badge {{ $certificationReport->plant_characteristics_match ? 'bg-success' : 'bg-danger' }}">
                                    {{ $certificationReport->plant_characteristics_match ? 'Ya' : 'Tidak' }}
                                </span>
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Isolasi - Utara</label>
                        <p class="mb-0">{{ $certificationReport->isolation_north ?: '-' }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Isolasi - Timur</label>
                        <p class="mb-0">{{ $certificationReport->isolation_east ?: '-' }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Isolasi - Selatan</label>
                        <p class="mb-0">{{ $certificationReport->isolation_south ?: '-' }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Isolasi - Barat</label>
                        <p class="mb-0">{{ $certificationReport->isolation_west ?: '-' }}</p>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Keadaan Hama dan Penyakit</label>
                        <p class="mb-0">{{ $certificationReport->pest_disease_condition ?: '-' }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Keadaan Rerumputan</label>
                        <p class="mb-0">
                            @if($certificationReport->weed_condition)
                                <span class="badge bg-secondary">{{ $certificationReport->weed_condition }}</span>
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Populasi per Contoh Pemeriksaan</label>
                        <p class="mb-0">{{ $certificationReport->population_per_sample ?: '-' }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Jumlah Temuan Campuran Varietas Lain</label>
                        <p class="mb-0">{{ $certificationReport->other_variety_mix_count ?: '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Rata-rata Campuran Varietas Lain (%)</label>
                        <p class="mb-0">{{ $certificationReport->other_variety_mix_percentage ? number_format($certificationReport->other_variety_mix_percentage, 2) : '-' }}%</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Taksiran Hasil</label>
                        <p class="mb-0">{{ $certificationReport->estimated_yield ? number_format($certificationReport->estimated_yield, 2) : '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tanggal Masa Edar / Kadaluarsa</label>
                        <p class="mb-0">
                            @if($certificationReport->expiry_date)
                                {{ $certificationReport->expiry_date->format('d M Y') }}
                                @if($certificationReport->expiry_date->isPast())
                                    <span class="badge bg-danger ms-2">Melewati Masa Edar</span>
                                @elseif($certificationReport->expiry_date->diffInMonths(now()) <= 3)
                                    <span class="badge bg-warning ms-2">Mendekati Masa Edar</span>
                                @endif
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagian D: Jumlah Benih yang Lulus Sertifikasi -->
        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="fas fa-boxes me-2"></i>Bagian D: Jumlah Benih yang Lulus Sertifikasi</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Satuan Inventaris</label>
                        <p class="mb-0">
                            @if($certificationReport->seed_unit)
                                <span class="badge bg-info">{{ strtoupper($certificationReport->seed_unit) }}</span>
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Total Inventaris</label>
                        <p class="mb-0">
                            @if($certificationReport->certified_seed_quantity)
                                {{ number_format($certificationReport->certified_seed_quantity, 2) }} {{ $certificationReport->seed_unit ?? '' }}
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Estimasi Penjualan per Unit</label>
                        <p class="mb-0">
                            @if($certificationReport->estimated_sale_price_per_kg)
                                Rp {{ number_format($certificationReport->estimated_sale_price_per_kg, 2, ',', '.') }}
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Pengisi Data</label>
                        <p class="mb-0">{{ $certificationReport->reporter_name ?: '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagian E: Kesimpulan & Lampiran -->
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Bagian E: Kesimpulan & Lampiran</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Kesimpulan / Rekomendasi</label>
                        <p class="mb-0">
                            <span class="badge {{ $certificationReport->conclusion_badge_class }}">
                                {{ $certificationReport->conclusion ?: 'Belum Ditentukan' }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Lampiran</label>
                        <p class="mb-0">
                            @if($certificationReport->scan_file_path)
                                <a href="{{ asset('storage/' . $certificationReport->scan_file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-file-pdf me-2"></i>Lihat File
                                </a>
                            @else
                                <span class="text-muted">Tidak ada lampiran</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        @php $plantForRoute = $seed->plant ?? $plant ?? $certificationReport?->certification?->plant ?? $certificationReport?->certification?->harvest?->plant; @endphp
        @if($plantForRoute)
        <div class="mt-3">
            <a href="{{ route('certifications.by-plant', $plantForRoute) }}?show_report={{ $certificationReport->certification_report_id ?? $certificationReport->getKey() }}" class="btn btn-primary">
                <i class="fas fa-list me-2"></i>Lihat Data Sertifikasi
            </a>
        </div>
        @endif
    </div>
</div>
@else
<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0"><i class="fas fa-certificate me-2"></i>Data Sertifikasi</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Belum ada data sertifikasi yang terhubung dengan benih ini.
        </div>
        @if($seed->plant)
        <div class="mt-3">
            <a href="{{ route('certifications.by-plant', $seed->plant) }}" class="btn btn-primary">
                <i class="fas fa-list me-2"></i>Lihat Riwayat Sertifikasi
            </a>
        </div>
        @endif
    </div>
</div>
@endif


@endsection



