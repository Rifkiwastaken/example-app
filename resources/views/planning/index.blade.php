@extends('layouts.app')

@section('title', 'Modul Perencanaan - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Modul Perencanaan</h4>
        <small class="text-muted">Perencanaan anggaran dan target produksi untuk tahun anggaran</small>
    </div>
</div>

<div class="row">
    <!-- A. Rencana Anggaran (DPA) -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-file-invoice-dollar me-2"></i>
                    Rencana Anggaran (DPA)
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Mendigitalkan data dari file R. ANGGARAN.csv untuk memonitor Pagu (Batas Atas) vs Realisasi penyerapan dana.
                </p>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-primary me-2"></i>
                        Tabel hierarki sesuai Kode Rekening
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-primary me-2"></i>
                        Monitoring Pagu vs Realisasi
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-primary me-2"></i>
                        Perhitungan otomatis Total Pagu, Realisasi, dan Sisa
                    </li>
                </ul>
                <div class="mt-3">
                    <a href="{{ route('planning.budget.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-right me-2"></i>Buka Rencana Anggaran
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- B. Target Produksi -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-bullseye me-2"></i>
                    Target Produksi
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Mendigitalkan data dari file R. PRODUKSI.csv untuk menetapkan "Goal" yang harus dicapai oleh tim lapangan.
                </p>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Monitoring Target vs Realisasi Fisik
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Perhitungan capaian otomatis dari data tanam dan panen
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Filter berdasarkan komoditas dan lokasi
                    </li>
                </ul>
                <div class="mt-3">
                    <a href="{{ route('planning.production-target.index') }}" class="btn btn-success">
                        <i class="fas fa-arrow-right me-2"></i>Buka Target Produksi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info mt-4">
    <h6 class="alert-heading">
        <i class="fas fa-info-circle me-2"></i>
        Informasi Modul Perencanaan
    </h6>
    <p class="mb-2">
        <strong>Akses:</strong> Modul ini biasanya diakses di awal tahun anggaran oleh Kepala UPTD atau Admin Perencanaan.
    </p>
    <p class="mb-0">
        <strong>Fungsi Utama:</strong>
    </p>
    <ul class="mb-0">
        <li>Rencana Anggaran (DPA) - Mengelola pagu anggaran dan realisasi penyerapan dana</li>
        <li>Target Produksi - Menetapkan target produksi dan memantau capaian terhadap target</li>
    </ul>
</div>
@endsection


