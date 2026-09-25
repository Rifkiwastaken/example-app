@extends('layouts.app')

@section('title', 'Detail Benih Lulus Sertifikasi - SIBESTI')

@section('content')
@php
    $harvest = $certificationReport->harvest;
    $plant = $harvest?->plant;
    $location = $harvest?->location;
    $lulusUnit = $certificationReport->certified_seed_unit ?: $certificationReport->seed_unit ?: 'kg';
    $stokUnit = $certificationReport->seed_unit ?: $lulusUnit;
    $seedClassResultCode = $certificationReport->seed_class_result ?: $certificationReport->seed_class_requested;
    $seedClassRequestedCode = $certificationReport->seed_class_requested;
    $seedClassSuffix = function (?string $code): string {
        return match ($code) {
            'BS' => ' (Benih Dasar)',
            'BP' => ' (Benih Pokok)',
            'BR' => ' (Benih Sebar)',
            default => '',
        };
    };
    $plantMatch = $certificationReport->plant_characteristics_match;
@endphp
<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('seed-stock.index') }}">Stok Benih</a></li>
        @if(isset($inventoryType) && $inventoryType)
            <li class="breadcrumb-item"><a href="{{ route('seed-stock.show', $inventoryType) }}">{{ $inventoryType->name }}</a></li>
        @endif
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
        <h4 class="mb-1">Detail Benih Lulus Sertifikasi</h4>
        <small class="text-muted">
            {{ $plant?->name ?? '-' }}
            @if($plant?->variety)
                - {{ $plant->variety }}
            @else
                - Tanpa Varietas
            @endif
        </small>
    </div>
    @if(isset($inventoryType) && $inventoryType)
        <a href="{{ route('seed-stock.show', $inventoryType) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    @else
        <a href="{{ route('seed-stock.certified-seeds') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    @endif
</div>

<!-- Bagian 1: Data Form Benih (selaras Bagian B & E form laporan) -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-seedling me-2"></i>Data Form Benih</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Nama Benih</label>
                <p class="mb-0">{{ $plant?->name ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Varietas</label>
                <p class="mb-0">{{ $plant?->variety ?: '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Jenis Tanaman</label>
                <p class="mb-0">{{ $plant?->type?->name ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Asal Lokasi Penanaman/Produksi</label>
                <p class="mb-0">{{ $location?->name ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Jumlah Benih yang Lulus Sertifikasi</label>
                <p class="mb-0"><strong>{{ number_format((float) ($certificationReport->certified_seed_quantity ?? 0), 2) }} {{ $lulusUnit }}</strong></p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Jumlah yang Ditambahkan ke Stok Benih</label>
                <p class="mb-0"><strong>{{ number_format((float) ($certificationReport->quantity_added_to_stock ?? 0), 2) }} {{ $stokUnit }}</strong></p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Satuan Inventaris (laporan)</label>
                <p class="mb-0">{{ $certificationReport->seed_unit ?: '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Isi per Kemasan</label>
                <p class="mb-0">
                    @if($certificationReport->package_content_per_pack !== null)
                        {{ number_format((float) $certificationReport->package_content_per_pack, 2) }} per kemasan
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Tanggal Kadaluarsa</label>
                <p class="mb-0">
                    @if($certificationReport->expiry_date)
                        {{ $certificationReport->expiry_date->format('d M Y') }}
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </p>
            </div>
            @if($certificationReport->estimated_sale_price_per_kg)
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Estimasi Penjualan per Kg</label>
                <p class="mb-0">Rp {{ number_format((float) $certificationReport->estimated_sale_price_per_kg, 2) }}</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Bagian 2: Data Sertifikasi Benih (selaras form laporan: A, C, F + mutu D) -->
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-certificate me-2"></i>Data Sertifikasi Benih</h5>
    </div>
    <div class="card-body">
        <h6 class="text-muted border-bottom pb-2 mb-3">Jenis sertifikasi &amp; informasi dasar (Bagian A)</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Jenis Sertifikasi</label>
                <p class="mb-0">{{ $certificationReport->report_type ?: '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Pelapor</label>
                <p class="mb-0">{{ $certificationReport->reporter_name ?: '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Nomor Laporan BPSB</label>
                <p class="mb-0">{{ $certificationReport->report_number_bpsb ?: '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Tanggal Laporan</label>
                <p class="mb-0">{{ $certificationReport->report_date?->format('d M Y') ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Musim Tanam</label>
                <p class="mb-0">{{ $certificationReport->growing_season ?: '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Fase Pemeriksaan</label>
                <p class="mb-0">{{ $certificationReport->inspection_phase ?: '-' }}</p>
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label fw-bold">Petugas Pengawas Mutu (BPSB)</label>
                <p class="mb-0">{{ $certificationReport->inspector_name ?: '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">No. Induk</label>
                <p class="mb-0">{{ $certificationReport->planting_batch_number ?: '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">No. Lot</label>
                <p class="mb-0">{{ $certificationReport->harvest_batch_number ?: '-' }}</p>
            </div>
        </div>

        <h6 class="text-muted border-bottom pb-2 mb-3 mt-4">Hasil pemeriksaan (Bagian C)</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Kelas Benih yang Diajukan</label>
                <p class="mb-0">
                    @if($seedClassRequestedCode)
                        {{ $seedClassRequestedCode }}{{ $seedClassSuffix($seedClassRequestedCode) }}
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Kelas Benih yang Dihasilkan</label>
                <p class="mb-0">
                    @if($seedClassResultCode)
                        {{ $seedClassResultCode }}{{ $seedClassSuffix($seedClassResultCode) }}
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Tanggal Selesai Uji</label>
                <p class="mb-0">{{ $certificationReport->test_completed_at?->format('d M Y') ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Kesimpulan</label>
                <p class="mb-0">
                    @if($certificationReport->conclusion)
                        <span class="{{ $certificationReport->conclusion_badge_class }}">{{ $certificationReport->conclusion }}</span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </p>
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label fw-bold">Sifat Tanaman Sesuai Varietas</label>
                <p class="mb-0">
                    @if($plantMatch === true || $plantMatch === 1 || $plantMatch === '1')
                        <span class="badge bg-success">Ya</span>
                    @elseif($plantMatch === false || $plantMatch === 0 || $plantMatch === '0')
                        <span class="badge bg-danger">Tidak</span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </p>
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label fw-bold">Isolasi</label>
                <div class="row">
                    <div class="col-md-3">
                        <small class="text-muted">Utara:</small>
                        <p class="mb-0">{{ $certificationReport->isolation_north ?: '-' }}</p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Timur:</small>
                        <p class="mb-0">{{ $certificationReport->isolation_east ?: '-' }}</p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Selatan:</small>
                        <p class="mb-0">{{ $certificationReport->isolation_south ?: '-' }}</p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Barat:</small>
                        <p class="mb-0">{{ $certificationReport->isolation_west ?: '-' }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label fw-bold">Keadaan Hama dan Penyakit</label>
                <p class="mb-0">{{ $certificationReport->pest_disease_condition ?: '-' }}</p>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Keadaan Rerumputan</label>
                <p class="mb-0">{{ $certificationReport->weed_condition ?: '-' }}</p>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Populasi per Contoh Pemeriksaan</label>
                <p class="mb-0">
                    @if($certificationReport->population_per_sample !== null && $certificationReport->population_per_sample !== '')
                        {{ number_format((float) $certificationReport->population_per_sample) }} (batang/rumpun)
                    @else
                        -
                    @endif
                </p>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Jumlah Temuan Campuran Varietas Lain</label>
                <p class="mb-0">
                    @if($certificationReport->other_variety_mix_count !== null && $certificationReport->other_variety_mix_count !== '')
                        {{ number_format((float) $certificationReport->other_variety_mix_count) }}
                    @else
                        -
                    @endif
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Rata-rata Campuran Varietas Lain (%)</label>
                <p class="mb-0">
                    @if($certificationReport->other_variety_mix_percentage !== null && $certificationReport->other_variety_mix_percentage !== '')
                        {{ number_format((float) $certificationReport->other_variety_mix_percentage, 2) }}%
                    @else
                        -
                    @endif
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Taksiran Hasil</label>
                <p class="mb-0">
                    @if($certificationReport->estimated_yield !== null && $certificationReport->estimated_yield !== '')
                        {{ number_format((float) $certificationReport->estimated_yield, 2) }} Ton/ha
                    @else
                        -
                    @endif
                </p>
            </div>
            @if($certificationReport->scan_file_path)
            <div class="col-md-12 mb-3">
                <label class="form-label fw-bold">Pindaian Laporan (Scan)</label>
                <p class="mb-0">
                    <a href="{{ asset('storage/' . $certificationReport->scan_file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-file-pdf me-1"></i>Lihat File
                    </a>
                </p>
            </div>
            @endif
        </div>

        <h6 class="text-muted border-bottom pb-2 mb-3 mt-4">Informasi benih / mutu (Bagian D)</h6>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Daya Berkecambah (%)</label>
                <p class="mb-0">{{ $certificationReport->daya_berkecambah !== null ? number_format((float) $certificationReport->daya_berkecambah, 2) : '-' }}</p>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">CVL (%)</label>
                <p class="mb-0">{{ $certificationReport->cvl !== null ? number_format((float) $certificationReport->cvl, 2) : '-' }}</p>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Kadar Air (%)</label>
                <p class="mb-0">{{ $certificationReport->kadar_air !== null ? number_format((float) $certificationReport->kadar_air, 2) : '-' }}</p>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Benih Murni (%)</label>
                <p class="mb-0">{{ $certificationReport->benih_murni !== null ? number_format((float) $certificationReport->benih_murni, 2) : '-' }}</p>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Kotoran Benih (%)</label>
                <p class="mb-0">{{ $certificationReport->kotoran_benih !== null ? number_format((float) $certificationReport->kotoran_benih, 2) : '-' }}</p>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Biji Gulma (%)</label>
                <p class="mb-0">{{ $certificationReport->biji_gulma !== null ? number_format((float) $certificationReport->biji_gulma, 2) : '-' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
