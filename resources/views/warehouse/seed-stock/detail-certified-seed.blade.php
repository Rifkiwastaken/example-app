@extends('layouts.app')

@section('title', 'Detail Benih Lulus Sertifikasi - SIBESTI')

@section('content')
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
        <h4 class="mb-1">Detail Benih Lulus Sertifikasi</h4>
        <small class="text-muted">
            {{ $certificationReport->certification->plant->name }} - 
            {{ $certificationReport->certification->plant->variety ?: 'Tanpa Varietas' }}
        </small>
    </div>
    <a href="{{ route('seed-stock.show', $inventoryType) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<!-- Bagian 1: Data Form Benih -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-seedling me-2"></i>Data Form Benih</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Nama Benih</label>
                <p class="mb-0">{{ $certificationReport->certification->plant->name }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Varietas</label>
                <p class="mb-0">{{ $certificationReport->certification->plant->variety ?: '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Jenis Tanaman</label>
                <p class="mb-0">{{ $certificationReport->certification->plant->type->name ?? '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Asal Lokasi Penanaman/Produksi</label>
                <p class="mb-0">
                    {{ $certificationReport->certification->plantingLocation->name }}
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Jumlah Benih yang Lulus Sertifikasi</label>
                <p class="mb-0"><strong>{{ number_format($certificationReport->certified_seed_quantity, 2) }} kg</strong></p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Jumlah yang Ditambahkan ke Stok Benih</label>
                <p class="mb-0"><strong>{{ number_format($certificationReport->pivot->quantity, 2) }} kg</strong></p>
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
                <p class="mb-0">Rp {{ number_format($certificationReport->estimated_sale_price_per_kg, 2) }}</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Bagian 2: Data Sertifikasi Benih -->
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-certificate me-2"></i>Data Sertifikasi Benih</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Nomor Laporan BPSB</label>
                <p class="mb-0">{{ $certificationReport->report_number_bpsb ?: '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Tanggal Laporan</label>
                <p class="mb-0">{{ $certificationReport->report_date->format('d M Y') }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Musim Tanam</label>
                <p class="mb-0">{{ $certificationReport->growing_season ?: '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Fase Pemeriksaan</label>
                <p class="mb-0">{{ $certificationReport->inspection_phase }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Petugas Pengawas Mutu (BPSB)</label>
                <p class="mb-0">{{ $certificationReport->inspector_name ?: '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Kelas Benih yang Diajukan</label>
                <p class="mb-0">
                    @if($certificationReport->certification->seed_class_requested)
                        {{ $certificationReport->certification->seed_class_requested }}
                        @if($certificationReport->certification->seed_class_requested == 'BS')
                            (Benih Dasar)
                        @elseif($certificationReport->certification->seed_class_requested == 'BP')
                            (Benih Pokok)
                        @elseif($certificationReport->certification->seed_class_requested == 'BR')
                            (Benih Sebar)
                        @endif
                    @else
                        -
                    @endif
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Kelas Benih yang Dihasilkan</label>
                <p class="mb-0">
                    @if($certificationReport->seed_class_result)
                        {{ $certificationReport->seed_class_result }}
                        @if($certificationReport->seed_class_result == 'BS')
                            (Benih Dasar)
                        @elseif($certificationReport->seed_class_result == 'BP')
                            (Benih Pokok)
                        @elseif($certificationReport->seed_class_result == 'BR')
                            (Benih Sebar)
                        @endif
                    @else
                        -
                    @endif
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Kesimpulan</label>
                <p class="mb-0">
                    <span class="badge bg-success">{{ $certificationReport->conclusion }}</span>
                </p>
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label fw-bold">Sifat Tanaman Sesuai Varietas</label>
                <p class="mb-0">
                    @if($certificationReport->plant_characteristics_match === true)
                        <span class="badge bg-success">Ya</span>
                    @elseif($certificationReport->plant_characteristics_match === false)
                        <span class="badge bg-danger">Tidak</span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </p>
            </div>
            @if($certificationReport->isolation_north || $certificationReport->isolation_east || $certificationReport->isolation_south || $certificationReport->isolation_west)
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
            @endif
            @if($certificationReport->pest_disease_condition)
            <div class="col-md-12 mb-3">
                <label class="form-label fw-bold">Keadaan Hama dan Penyakit</label>
                <p class="mb-0">{{ $certificationReport->pest_disease_condition }}</p>
            </div>
            @endif
            @if($certificationReport->weed_condition)
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Keadaan Rerumputan</label>
                <p class="mb-0">{{ $certificationReport->weed_condition }}</p>
            </div>
            @endif
            @if($certificationReport->population_per_sample)
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Populasi per Contoh Pemeriksaan</label>
                <p class="mb-0">{{ number_format($certificationReport->population_per_sample) }} (batang/rumpun)</p>
            </div>
            @endif
            @if($certificationReport->other_variety_mix_count !== null)
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Jumlah Temuan Campuran Varietas Lain</label>
                <p class="mb-0">{{ number_format($certificationReport->other_variety_mix_count) }}</p>
            </div>
            @endif
            @if($certificationReport->other_variety_mix_percentage !== null)
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Rata-rata Campuran Varietas Lain (%)</label>
                <p class="mb-0">{{ number_format($certificationReport->other_variety_mix_percentage, 2) }}%</p>
            </div>
            @endif
            @if($certificationReport->estimated_yield)
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Taksiran Hasil</label>
                <p class="mb-0">{{ number_format($certificationReport->estimated_yield, 2) }} Ton/ha</p>
            </div>
            @endif
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
    </div>
</div>
@endsection

