@extends('layouts.app')

@section('title', 'Detail Laporan Pemeriksaan - SIBIT')

@section('content')
<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('certifications.index') }}">Manajemen Sertifikasi</a></li>
        <li class="breadcrumb-item"><a href="{{ route('certifications.show', $report->certification->harvest) }}">Kelola Sertifikasi</a></li>
        <li class="breadcrumb-item active">Detail Laporan</li>
    </ol>
</nav>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Detail Laporan Pemeriksaan Pertanaman</h4>
        <small class="text-muted">No. Laporan: {{ $report->report_number_bpsb ?: '-' }} | Tanggal: {{ $report->report_date->format('d M Y') }}</small>
    </div>
    <a href="{{ route('certifications.show', $report->certification->harvest) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

@php
    $harvest = $report->certification->harvest;
@endphp

<!-- Informasi Dasar -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Dasar Laporan</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nomor Laporan BPSB</label>
                    <p class="mb-0">{{ $report->report_number_bpsb ?: '-' }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Tanggal Laporan</label>
                    <p class="mb-0">{{ $report->report_date->format('d M Y') }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Musim Tanam</label>
                    <p class="mb-0">{{ $report->growing_season ?: '-' }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label fw-bold">Fase Pemeriksaan</label>
                    <p class="mb-0"><span class="badge bg-info">{{ $report->inspection_phase }}</span></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Petugas Pengawas Mutu (BPSB)</label>
                    <p class="mb-0">{{ $report->inspector_name ?: '-' }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Kesimpulan</label>
                    <p class="mb-0">
                        <span class="badge {{ $report->conclusion_badge_class }}">
                            {{ $report->conclusion ?: 'Belum Ditentukan' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hasil Pemeriksaan -->
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Hasil Pemeriksaan</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Kelas Benih yang Dihasilkan</label>
                <p class="mb-0">
                    @if($report->seed_class_result)
                        <span class="badge bg-info">{{ $report->seed_class_result }}</span>
                    @else
                        -
                    @endif
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Sifat Tanaman Sesuai Varietas</label>
                <p class="mb-0">
                    @if($report->plant_characteristics_match !== null)
                        <span class="badge {{ $report->plant_characteristics_match ? 'bg-success' : 'bg-danger' }}">
                            {{ $report->plant_characteristics_match ? 'Ya' : 'Tidak' }}
                        </span>
                    @else
                        -
                    @endif
                </p>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">Isolasi - Utara</label>
                <p class="mb-0">{{ $report->isolation_north ?: '-' }}</p>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">Isolasi - Timur</label>
                <p class="mb-0">{{ $report->isolation_east ?: '-' }}</p>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">Isolasi - Selatan</label>
                <p class="mb-0">{{ $report->isolation_south ?: '-' }}</p>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">Isolasi - Barat</label>
                <p class="mb-0">{{ $report->isolation_west ?: '-' }}</p>
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label fw-bold">Keadaan Hama dan Penyakit</label>
                <p class="mb-0">{{ $report->pest_disease_condition ?: '-' }}</p>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Keadaan Rerumputan</label>
                <p class="mb-0">
                    @if($report->weed_condition)
                        <span class="badge bg-secondary">{{ $report->weed_condition }}</span>
                    @else
                        -
                    @endif
                </p>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Populasi per Contoh Pemeriksaan</label>
                <p class="mb-0">{{ $report->population_per_sample ?: '-' }}</p>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Jumlah Temuan Campuran Varietas Lain</label>
                <p class="mb-0">{{ $report->other_variety_mix_count ?: '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Rata-rata Campuran Varietas Lain (%)</label>
                <p class="mb-0">{{ $report->other_variety_mix_percentage ? number_format($report->other_variety_mix_percentage, 2) : '-' }}%</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Taksiran Hasil</label>
                <p class="mb-0">{{ $report->estimated_yield ? number_format($report->estimated_yield, 2) : '-' }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Lampiran -->
@if($report->scan_file_path)
<div class="card">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-file me-2"></i>Lampiran</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label fw-bold">Pindaian Laporan (Scan)</label>
            <div>
                <a href="{{ asset('storage/' . $report->scan_file_path) }}" target="_blank" class="btn btn-outline-primary">
                    <i class="fas fa-file-pdf me-2"></i>Lihat File
                </a>
            </div>
        </div>
    </div>
</div>
@endif
@endsection











