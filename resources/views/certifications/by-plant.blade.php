@extends('layouts.app')

@section('title', 'Kelola Sertifikasi - ' . $plant->name . ' - SIBESTI')

@section('content')
<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('certifications.index') }}">Manajemen Sertifikasi</a></li>
        <li class="breadcrumb-item active">Kelola Sertifikasi</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Kelola Sertifikasi: {{ $plant->name }}</h4>
        <small class="text-muted">Semua sertifikasi dari semua lokasi penanaman untuk komoditas/tanaman ini</small>
    </div>
    <a href="{{ route('certifications.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <strong>Nama Tanaman:</strong> {{ $plant->name }}
            </div>
            <div class="col-md-4">
                <strong>Kategori:</strong> {{ $plant->type?->name ?: '-' }}
            </div>
            <div class="col-md-4">
                <strong>Varietas:</strong> {{ $plant->variety ?: '-' }}
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('certifications.by-plant', $plant) }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Lokasi Penanaman</label>
                <select name="location_filter" class="form-select">
                    <option value="">Semua Lokasi</option>
                    @foreach($allPlantingLocations as $location)
                        <option value="{{ $location->id }}" {{ request('location_filter') == $location->id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status Sertifikasi</label>
                <select name="status_filter" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="dalam_proses" {{ request('status_filter') == 'dalam_proses' ? 'selected' : '' }}>Dalam Proses</option>
                    <option value="lulus" {{ request('status_filter') == 'lulus' ? 'selected' : '' }}>Lulus</option>
                    <option value="tidak_lulus" {{ request('status_filter') == 'tidak_lulus' ? 'selected' : '' }}>Tidak Lulus</option>
                    <option value="selesai" {{ request('status_filter') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status Stok</label>
                <select name="stock_status_filter" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="telah_ditambahkan" {{ request('stock_status_filter') == 'telah_ditambahkan' ? 'selected' : '' }}>Telah Ditambahkan ke Stok</option>
                    <option value="telah_dihapus" {{ request('stock_status_filter') == 'telah_dihapus' ? 'selected' : '' }}>Data Stok Telah Dihapus</option>
                    <option value="belum_ditambahkan" {{ request('stock_status_filter') == 'belum_ditambahkan' ? 'selected' : '' }}>Belum Ditambahkan ke Stok</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
                <a href="{{ route('certifications.by-plant', $plant) }}" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Riwayat Sertifikasi -->
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Riwayat Sertifikasi ({{ $allReports->count() }})</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Jenis Laporan</th>
                        <th>Fase</th>
                        <th>Tanggal Laporan</th>
                        <th>Tanggal Batas Masa Edar</th>
                        <th>Lokasi Penanaman</th>
                        <th>Status</th>
                        <th>Status Stok</th>
                        <th>Tambahkan ke Stok</th>
                        <th width="250">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allReports as $report)
                        @php
                            // Load inventoryTypes relation if not already loaded
                            if (!$report->relationLoaded('inventoryTypes')) {
                                $report->load('inventoryTypes');
                            }
                            // Pernah ditambahkan ke stok = masih terhubung ke inventory type (pivot)
                            $hasBeenAddedToStock = $report->inventoryTypes->count() > 0;
                            // Data stok telah dihapus = pernah ditambahkan tapi record seed-nya sudah tidak ada
                            $stockWasDeleted = false;
                            if ($hasBeenAddedToStock) {
                                $linkedTypeIds = $report->inventoryTypes->pluck('inventory_type_id')->toArray();
                                $seedStillExists = \App\Models\InventoryTypeSeed::where('certification_report_id', $report->certification_report_id)
                                    ->whereIn('inventory_type_id', $linkedTypeIds)
                                    ->exists();
                                $stockWasDeleted = !$seedStillExists;
                            }
                            // Check if report is eligible for adding to stock
                            $isEligibleForStock = $report->conclusion === 'LULUS' 
                                && $report->certified_seed_quantity 
                                && $report->certified_seed_quantity > 0;
                            
                            // Get certification data
                            $certification = $report->certification;
                            $status = $certification->status_label;
                            $statusClass = match($certification->certification_status) {
                                'dalam_proses' => 'bg-warning',
                                'lulus' => 'bg-success',
                                'tidak_lulus' => 'bg-danger',
                                'selesai' => 'bg-info',
                                default => 'bg-secondary',
                            };
                            // Status mengikuti masa edar: mendekati 3 bulan = Mendekati masa edar, sudah lewat = Melewati masa edar
                            if ($report->expiry_date) {
                                if ($report->expiry_date->isPast()) {
                                    $status = 'Melewati masa edar';
                                    $statusClass = 'bg-danger';
                                } elseif ($report->expiry_date->isFuture() && $report->expiry_date->diffInMonths(now()) <= 3) {
                                    $status = 'Mendekati masa edar';
                                    $statusClass = 'bg-warning';
                                }
                            }
                            $locationName = $certification->plantingLocation?->name ?: $certification->harvest?->location?->name ?: '-';
                        @endphp
                        <tr>
                            <td>
                                <i class="fas fa-file-alt me-2"></i>{{ $report->report_type ?? 'Laporan Pemeriksaan Pertanaman' }}
                                @if($report->report_number_bpsb)
                                    <br><small class="text-muted">No: {{ $report->report_number_bpsb }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $report->inspection_phase }}</span>
                            </td>
                            <td>{{ $report->report_date->format('d M Y') }}</td>
                            <td>
                                @if($report->expiry_date)
                                    {{ $report->expiry_date->format('d M Y') }}
                                    @php
                                        $isExpired = $report->expiry_date->isPast();
                                        $isNearExpiry = $report->expiry_date->isFuture() && $report->expiry_date->diffInMonths(now()) <= 3;
                                    @endphp
                                    @if($isExpired)
                                        <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Melewati masa edar</small>
                                    @elseif($isNearExpiry)
                                        <br><small class="text-warning"><i class="fas fa-clock"></i> Mendekati masa edar</small>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $locationName }}</td>
                            <td>
                                <span class="badge {{ $statusClass }}">{{ $status }}</span>
                            </td>
                            <td>
                                @if($stockWasDeleted)
                                    <span class="badge bg-danger">
                                        <i class="fas fa-trash me-1"></i>Data Stok Telah Dihapus
                                    </span>
                                @elseif($hasBeenAddedToStock)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Telah Ditambahkan ke Stok
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-clock me-1"></i>Belum Ditambahkan ke Stok
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($stockWasDeleted)
                                    <span class="text-muted small">Stok telah dihapus</span>
                                @elseif($hasBeenAddedToStock)
                                    @php
                                        // Get the first inventory type that this report is linked to
                                        $linkedInventoryType = $report->inventoryTypes->first();
                                    @endphp
                                    @if($linkedInventoryType)
                                        <a href="{{ route('seed-stock.show', $linkedInventoryType) }}?tab=certified-seeds" class="btn btn-sm btn-info" title="Lihat Data Stok">
                                            <i class="fas fa-eye me-1"></i>Lihat Data Stok
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                @elseif($isEligibleForStock)
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addToStockModal{{ $report->certification_report_id }}" title="Tambahkan ke Stok">
                                        <i class="fas fa-plus me-1"></i>Tambahkan ke Stok
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewReportDetailModal{{ $report->id }}" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" onclick="viewHarvestDetailFromCertification('{{ $certification->harvest_id ?? $certification->harvest?->harvest_id ?? '' }}')" title="Detail Panen">
                                        <i class="fas fa-seedling me-1"></i>Detail Panen
                                    </button>
                                    {{-- Tombol Sertifikasi Ulang selalu muncul --}}
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#renewCertificationModal{{ $report->id }}" title="Lakukan Sertifikasi Ulang">
                                        <i class="fas fa-redo me-1"></i>Sertifikasi Ulang
                                    </button>
                                    @if(Route::has('certifications.reports.destroy'))
                                    <form action="{{ route('certifications.reports.destroy', $report) }}" method="POST" onsubmit="return confirm('Hapus laporan ini? Tindakan tidak dapat dibatalkan.')" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Laporan">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Modal: Lihat Detail Laporan -->
                        <div class="modal fade" id="viewReportDetailModal{{ $report->id }}" tabindex="-1" aria-labelledby="viewReportDetailModalLabel{{ $report->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="viewReportDetailModalLabel{{ $report->id }}">
                                            Detail {{ $report->report_type ?? 'Laporan Pemeriksaan Pertanaman' }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                                        @php
                                            $harvest = $report->certification->harvest;
                                            $plant = $harvest->plant ?? null;
                                            $planting = $harvest->planting ?? null;
                                            $location = $harvest->location ?? null;
                                            $certification = $report->certification;
                                        @endphp
                                        
                                        <!-- Informasi Dasar -->
                                        <div class="card mb-3">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Dasar Laporan</h6>
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
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label fw-bold">Tanggal Masa Edar / Kadaluarsa</label>
                                                        <p class="mb-0">
                                                            @if($report->expiry_date)
                                                                {{ $report->expiry_date->format('d M Y') }}
                                                                @if($report->expiry_date->isPast())
                                                                    <span class="badge bg-danger ms-2">Melewati Masa Edar</span>
                                                                @elseif($report->expiry_date->diffInMonths(now()) <= 3)
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
                                                            @if($report->seed_unit)
                                                                <span class="badge bg-info">{{ strtoupper($report->seed_unit) }}</span>
                                                            @else
                                                                -
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label fw-bold">Total Inventaris</label>
                                                        <p class="mb-0">
                                                            @if($report->certified_seed_quantity)
                                                                {{ number_format($report->certified_seed_quantity, 2) }} {{ $report->seed_unit ?? '' }}
                                                            @else
                                                                -
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label fw-bold">Estimasi Penjualan per Unit</label>
                                                        <p class="mb-0">
                                                            @if($report->estimated_sale_price_per_kg)
                                                                Rp {{ number_format($report->estimated_sale_price_per_kg, 2, ',', '.') }}
                                                            @else
                                                                -
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label fw-bold">Pengisi Data</label>
                                                        <p class="mb-0">{{ $report->reporter_name ?: '-' }}</p>
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
                                                            <span class="badge {{ $report->conclusion_badge_class }}">
                                                                {{ $report->conclusion ?: 'Belum Ditentukan' }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label fw-bold">Lampiran</label>
                                                        <p class="mb-0">
                                                            @if($report->scan_file_path)
                                                                <a href="{{ asset('storage/' . $report->scan_file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
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
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if(!$hasBeenAddedToStock && $isEligibleForStock)
                        <!-- Modal: Tambahkan Data Stok dari Sertifikasi -->
                        @php $reportPk = $report->certification_report_id; @endphp
                        <div class="modal fade" id="addToStockModal{{ $reportPk }}" tabindex="-1" aria-labelledby="addToStockModalLabel{{ $reportPk }}" aria-hidden="true">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addToStockModalLabel{{ $reportPk }}">Tambahkan Data Stok dari Sertifikasi</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form id="addStockFromCertificationForm{{ $reportPk }}" method="POST" action="">
                                        @csrf
                                        <input type="hidden" name="certification_report_id" value="{{ $report->certification_report_id }}">
                                        <input type="hidden" name="redirect_to_certification_by_plant" value="1">
                                        <div class="modal-body">
                                            <!-- Step 1: Pilih Stok Benih -->
                                            <div id="step1_{{ $reportPk }}" class="step-content">
                                                <h6 class="mb-3">Langkah 1: Pilih Stok Benih</h6>
                                                <div class="mb-3">
                                                    <label class="form-label">Pilih Stok Benih <span class="text-danger">*</span></label>
                                                    <select name="inventory_type_id" id="inventory_type_id_{{ $reportPk }}" class="form-select" required>
                                                        <option value="">-- Pilih Stok Benih --</option>
                                                        @foreach($inventoryTypes as $inventoryType)
                                                            <option value="{{ $inventoryType->inventory_type_id }}">{{ $inventoryType->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <small class="text-muted">Pilih stok benih tempat benih akan ditambahkan</small>
                                                </div>
                                                <div class="d-flex justify-content-end">
                                                    <button type="button" class="btn btn-primary" onclick="goToStep3FromCertificationByPlant('{{ $reportPk }}')">Lanjutkan</button>
                                                </div>
                                            </div>

                                            <!-- Step 2: Form Tambah Stok -->
                                            <div id="step2_{{ $reportPk }}" class="step-content" style="display: none;">
                                                <h6 class="mb-3">Langkah 2: Form Tambah Stok</h6>
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    <strong>Data dari Sertifikasi:</strong> Form ini akan diisi otomatis dengan data dari sertifikasi yang dipilih.
                                                </div>
                                                <div class="row">
                                                    <!-- Left Column -->
                                                    <div class="col-md-6">
                                                        <!-- 1. Pilih Benih -->
                                                        <div class="mb-3">
                                                            <label class="form-label">Pilih Benih <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="plant_id" id="form_plant_id_{{ $reportPk }}" required disabled>
                                                                <option value="{{ $report->certification->plant_id }}" selected>
                                                                    {{ $report->certification->plant?->name }} @if($report->certification->plant?->variety) - {{ $report->certification->plant->variety }} @endif
                                                                </option>
                                                            </select>
                                                            <input type="hidden" name="plant_id" value="{{ $report->certification->plant_id }}">
                                                        </div>

                                                        <!-- 2. Satuan Inventaris -->
                                                        <div class="mb-3">
                                                            <label class="form-label">Satuan Inventaris <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="seed_unit" id="form_seed_unit_{{ $reportPk }}" required>
                                                                <option value="">-- Pilih Satuan --</option>
                                                                @foreach(['kg'=>'Kilogram (kg)','ton'=>'Ton','gram'=>'Gram','butir'=>'Butir/Biji','pcs'=>'Pcs','batang'=>'Batang'] as $val => $label)
                                                                    <option value="{{ $val }}" {{ ($report->seed_unit ?? 'kg') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <!-- 3. Estimasi Penjualan per Unit -->
                                                        <div class="mb-3">
                                                            <label class="form-label">Estimasi Penjualan per Unit</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">Rp</span>
                                                                <input type="number" class="form-control" 
                                                                       name="estimated_sale_price_per_kg" id="form_estimated_sale_price_per_kg_{{ $reportPk }}" 
                                                                       step="0.01" min="0" placeholder="0.00"
                                                                       value="{{ $report->estimated_sale_price_per_kg ?? '' }}">
                                                            </div>
                                                        </div>

                                                        <!-- 4. Pengisi Data -->
                                                        <div class="mb-3">
                                                            <label class="form-label">Pengisi Data <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="filled_by_user_id" id="form_filled_by_user_id_{{ $reportPk }}" required>
                                                                <option value="">-- Pilih User --</option>
                                                                @foreach($users as $user)
                                                                    <option value="{{ $user->user_id }}" {{ (auth()->user()?->user_id ?? '') == $user->user_id ? 'selected' : '' }}>
                                                                        {{ $user->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <!-- Right Column -->
                                                    <div class="col-md-6">
                                                        <!-- 1. Lokasi Penanaman -->
                                                        <div class="mb-3">
                                                            <label class="form-label">Lokasi Penanaman <span class="text-danger">*</span></label>
                                                            @php
                                                                $certLocId = $report->certification->planting_location_id ?? null;
                                                                $certLocName = $report->certification->plantingLocation?->name
                                                                    ?? ($certLocId ? ($allPlantingLocations->firstWhere('planting_location_id', $certLocId)?->name ?? $plantingLocations->firstWhere('planting_location_id', $certLocId)?->name) : null)
                                                                    ?? '-';
                                                            @endphp
                                                            <select class="form-select" name="planting_location_id" id="form_planting_location_id_{{ $reportPk }}" required disabled>
                                                                <option value="{{ $certLocId ?? '' }}" selected>{{ $certLocName }}</option>
                                                            </select>
                                                            <input type="hidden" name="planting_location_id" value="{{ $certLocId ?? '' }}">
                                                        </div>

                                                        <!-- 2. Jumlah Inventaris -->
                                                        <div class="mb-3">
                                                            <label class="form-label">Jumlah Inventaris <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control" 
                                                                   name="seed_quantity" id="form_seed_quantity_{{ $reportPk }}" 
                                                                   step="0.01" min="0.01" required placeholder="0.00"
                                                                   value="{{ $report->certified_seed_quantity ?? '' }}">
                                                        </div>

                                                        <!-- 3. Tanggal Kadaluarsa -->
                                                        <div class="mb-3">
                                                            <label class="form-label">Tanggal Kadaluarsa</label>
                                                            <input type="date" class="form-control" 
                                                                   name="expiry_date" id="form_expiry_date_{{ $reportPk }}"
                                                                   value="{{ $report->expiry_date ? $report->expiry_date->format('Y-m-d') : '' }}">
                                                        </div>

                                                        <!-- 4. Nomor Penyimpanan (wajib, unik, terisi otomatis saat form dibuka, dapat diedit) -->
                                                        <div class="mb-3">
                                                            <label class="form-label">Nomor Penyimpanan <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" 
                                                                   name="storage_number" id="form_storage_number_{{ $reportPk }}" 
                                                                   placeholder="NOP-YYYY-NNNNNN (akan terisi otomatis)" maxlength="50"
                                                                   value="{{ old('storage_number', '') }}">
                                                            <small class="text-muted">Wajib dan unik. Nomor akan muncul otomatis saat form dibuka; dapat diubah jika perlu.</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between mt-3">
                                                    <button type="button" class="btn btn-secondary" onclick="goToStep1FromCertificationByPlant('{{ $reportPk }}')">Kembali</button>
                                                    <button type="submit" class="btn btn-success">Simpan</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        {{-- Modal untuk Sertifikasi Ulang selalu tersedia --}}
                        <!-- Modal untuk Sertifikasi Ulang -->
                        <div class="modal fade" id="renewCertificationModal{{ $report->id }}" tabindex="-1" aria-labelledby="renewCertificationModalLabel{{ $report->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="renewCertificationModalLabel{{ $report->id }}">Form Sertifikasi Ulang</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('certifications.reports.store', $certification) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="report_type" value="Laporan Sertifikasi Ulang">
                                        <input type="hidden" name="renew_from_report_id" value="{{ $report->id }}">
                                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Anda akan membuat laporan sertifikasi ulang berdasarkan laporan pemeriksaan sebelumnya.
                                            </div>
                                            
                                            <!-- Field Jenis Sertifikasi -->
                                            <div class="card mb-3">
                                                <div class="card-header bg-light">
                                                    <h6 class="mb-0">Jenis Sertifikasi</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Jenis Sertifikasi <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" value="Laporan Sertifikasi Ulang" readonly>
                                                        <input type="hidden" name="report_type" value="Laporan Sertifikasi Ulang">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Bagian A: Informasi Dasar Laporan -->
                                            <div class="card mb-3">
                                                <div class="card-header bg-light">
                                                    <h6 class="mb-0">Bagian A: Informasi Dasar Laporan</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Nomor Laporan BPSB <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="report_number_bpsb" value="{{ old('report_number_bpsb', 'BPSB-' . date('Y') . '-' . str_pad(\App\Models\CertificationReport::whereYear('report_date', date('Y'))->count() + 1, 6, '0', STR_PAD_LEFT)) }}" required>
                                                            <small class="text-muted">Nomor batch akan otomatis terisi, namun dapat diubah jika diperlukan</small>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Tanggal Laporan <span class="text-danger">*</span></label>
                                                            <input type="date" class="form-control" name="report_date" value="{{ old('report_date', date('Y-m-d')) }}" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Musim Tanam</label>
                                                            <input type="text" class="form-control" name="growing_season" value="{{ old('growing_season') }}" placeholder="Contoh: Musim Tanam 2024">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Fase Pemeriksaan <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="inspection_phase" required>
                                                                <option value="">Pilih Fase</option>
                                                                <option value="Vegetatif" {{ old('inspection_phase') == 'Vegetatif' ? 'selected' : '' }}>Vegetatif</option>
                                                                <option value="Generatif" {{ old('inspection_phase') == 'Generatif' ? 'selected' : '' }}>Generatif</option>
                                                                <option value="Menjelang Panen" {{ old('inspection_phase') == 'Menjelang Panen' ? 'selected' : '' }}>Menjelang Panen</option>
                                                                <option value="Lainnya" {{ old('inspection_phase') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-12 mb-3">
                                                            <label class="form-label">Petugas Pengawas Mutu (BPSB)</label>
                                                            <input type="text" class="form-control" name="inspector_name" value="{{ old('inspector_name') }}" placeholder="Nama Petugas">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Bagian B: Tautkan ke Lot Produksi -->
                                            <div class="card mb-3">
                                                <div class="card-header bg-light">
                                                    <h6 class="mb-0">Bagian B: Lot Produksi yang Diperiksa</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="alert alert-info mb-0">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        Lot Produksi: <strong>{{ $certification->harvest?->plant?->type?->name ?: $certification->harvest?->plant?->name ?? '-' }} - {{ $certification->harvest?->plant?->variety ?: 'Tanpa Varietas' }} ({{ $certification->harvest?->location?->name ?: '-' }})</strong>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Bagian C: Hasil Pemeriksaan -->
                                            <div class="card mb-3">
                                                <div class="card-header bg-light">
                                                    <h6 class="mb-0">Bagian C: Hasil Pemeriksaan</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Kelas Benih yang Dihasilkan</label>
                                                            <select class="form-select" name="seed_class_result">
                                                                <option value="">Pilih Kelas</option>
                                                                <option value="BS" {{ old('seed_class_result') == 'BS' ? 'selected' : '' }}>BS (Benih Dasar)</option>
                                                                <option value="BP" {{ old('seed_class_result') == 'BP' ? 'selected' : '' }}>BP (Benih Pokok)</option>
                                                                <option value="BR" {{ old('seed_class_result') == 'BR' ? 'selected' : '' }}>BR (Benih Sebar)</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Sifat Tanaman Sesuai Varietas</label>
                                                            <div class="mt-2">
                                                                <div class="form-check form-check-inline">
                                                                    <input class="form-check-input" type="radio" name="plant_characteristics_match" id="match_yes_renew{{ $report->id }}" value="1" {{ old('plant_characteristics_match') == '1' ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="match_yes_renew{{ $report->id }}">Ya</label>
                                                                </div>
                                                                <div class="form-check form-check-inline">
                                                                    <input class="form-check-input" type="radio" name="plant_characteristics_match" id="match_no_renew{{ $report->id }}" value="0" {{ old('plant_characteristics_match') == '0' ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="match_no_renew{{ $report->id }}">Tidak</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 mb-3">
                                                            <label class="form-label">Isolasi - Utara</label>
                                                            <input type="text" class="form-control" name="isolation_north" value="{{ old('isolation_north') }}" placeholder="Contoh: 10m">
                                                        </div>
                                                        <div class="col-md-3 mb-3">
                                                            <label class="form-label">Isolasi - Timur</label>
                                                            <input type="text" class="form-control" name="isolation_east" value="{{ old('isolation_east') }}" placeholder="Contoh: 10m">
                                                        </div>
                                                        <div class="col-md-3 mb-3">
                                                            <label class="form-label">Isolasi - Selatan</label>
                                                            <input type="text" class="form-control" name="isolation_south" value="{{ old('isolation_south') }}" placeholder="Contoh: 10m">
                                                        </div>
                                                        <div class="col-md-3 mb-3">
                                                            <label class="form-label">Isolasi - Barat</label>
                                                            <input type="text" class="form-control" name="isolation_west" value="{{ old('isolation_west') }}" placeholder="Contoh: 10m">
                                                        </div>
                                                        <div class="col-md-12 mb-3">
                                                            <label class="form-label">Keadaan Hama dan Penyakit</label>
                                                            <textarea class="form-control" name="pest_disease_condition" rows="3" placeholder="Deskripsi keadaan hama dan penyakit">{{ old('pest_disease_condition') }}</textarea>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Keadaan Rerumputan</label>
                                                            <select class="form-select" name="weed_condition">
                                                                <option value="">Pilih Kondisi</option>
                                                                <option value="Bersih" {{ old('weed_condition') == 'Bersih' ? 'selected' : '' }}>Bersih</option>
                                                                <option value="Cukup Bersih" {{ old('weed_condition') == 'Cukup Bersih' ? 'selected' : '' }}>Cukup Bersih</option>
                                                                <option value="Kotor" {{ old('weed_condition') == 'Kotor' ? 'selected' : '' }}>Kotor</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Populasi per Contoh Pemeriksaan</label>
                                                            <input type="number" class="form-control" name="population_per_sample" value="{{ old('population_per_sample') }}" min="0" placeholder="Jumlah">
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label class="form-label">Jumlah Temuan Campuran Varietas Lain</label>
                                                            <input type="number" class="form-control" name="other_variety_mix_count" value="{{ old('other_variety_mix_count') }}" min="0" placeholder="Jumlah">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Rata-rata Campuran Varietas Lain (%)</label>
                                                            <input type="number" class="form-control" name="other_variety_mix_percentage" value="{{ old('other_variety_mix_percentage') }}" step="0.01" min="0" max="100" placeholder="0.00">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Taksiran Hasil</label>
                                                            <input type="number" class="form-control" name="estimated_yield" value="{{ old('estimated_yield') }}" step="0.01" min="0" placeholder="0.00">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Tanggal Masa Edar / Kadaluarsa <span class="text-danger">*</span></label>
                                                            <input type="date" class="form-control" name="expiry_date" value="{{ old('expiry_date') }}" required>
                                                            <small class="text-muted">Diisi berdasarkan sertifikat yang dikeluarkan oleh BPSB</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Bagian D: Jumlah Benih yang Lulus Sertifikasi -->
                                            <div class="card mb-3">
                                                <div class="card-header bg-light">
                                                    <h6 class="mb-0">Bagian D: Jumlah Benih yang Lulus Sertifikasi</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Satuan Inventaris</label>
                                                            <select class="form-select" name="seed_unit" id="seed_unit_renew{{ $report->id }}">
                                                                <option value="">Pilih Satuan</option>
                                                                <option value="kg" {{ old('seed_unit') == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                                                <option value="ton" {{ old('seed_unit') == 'ton' ? 'selected' : '' }}>Ton</option>
                                                                <option value="gram" {{ old('seed_unit') == 'gram' ? 'selected' : '' }}>Gram</option>
                                                                <option value="butir" {{ old('seed_unit') == 'butir' ? 'selected' : '' }}>Butir/Biji</option>
                                                                <option value="pcs" {{ old('seed_unit') == 'pcs' ? 'selected' : '' }}>Pcs</option>
                                                                <option value="batang" {{ old('seed_unit') == 'batang' ? 'selected' : '' }}>Batang</option>
                                                            </select>
                                                            <small class="text-muted">Pilih satuan inventaris</small>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Total Inventaris</label>
                                                            <input type="number" class="form-control" name="certified_seed_quantity" id="certified_seed_quantity_renew{{ $report->id }}" value="{{ old('certified_seed_quantity') }}" step="0.01" min="0" placeholder="0.00">
                                                            <input type="hidden" name="certified_seed_unit" id="certified_seed_unit_renew{{ $report->id }}" value="{{ old('certified_seed_unit', 'kg') }}">
                                                            <small class="text-muted">Masukkan total inventaris</small>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Estimasi Penjualan</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">Rp</span>
                                                                <input type="number" class="form-control" name="estimated_sale_price_per_kg" value="{{ old('estimated_sale_price_per_kg') }}" step="0.01" min="0" placeholder="0.00">
                                                            </div>
                                                            <small class="text-muted">Masukkan estimasi harga penjualan</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Bagian E: Kesimpulan & Lampiran -->
                                            <div class="card mb-3">
                                                <div class="card-header bg-light">
                                                    <h6 class="mb-0">Bagian E: Kesimpulan & Lampiran</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Kesimpulan / Rekomendasi <span class="text-danger">*</span></label>
                                                            <div class="mt-2">
                                                                <div class="form-check form-check-inline">
                                                                    <input class="form-check-input" type="radio" name="conclusion" id="conclusion_lulus_renew{{ $report->id }}" value="LULUS" {{ old('conclusion') == 'LULUS' ? 'checked' : '' }} required>
                                                                    <label class="form-check-label text-success fw-bold" for="conclusion_lulus_renew{{ $report->id }}">LULUS</label>
                                                                </div>
                                                                <div class="form-check form-check-inline">
                                                                    <input class="form-check-input" type="radio" name="conclusion" id="conclusion_tidak_lulus_renew{{ $report->id }}" value="TIDAK LULUS" {{ old('conclusion') == 'TIDAK LULUS' ? 'checked' : '' }} required>
                                                                    <label class="form-check-label text-danger fw-bold" for="conclusion_tidak_lulus_renew{{ $report->id }}">TIDAK LULUS</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label">Unggah Pindaian Laporan (Scan)</label>
                                                            <input type="file" class="form-control" name="scan_file" accept=".pdf,.jpg,.jpeg,.png">
                                                            <small class="text-muted">Format: PDF, JPG, PNG (Maks: 10MB)</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-save me-2"></i>Simpan Laporan Sertifikasi Ulang
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-certificate fa-3x mb-3"></i>
                                    <p>Belum ada riwayat sertifikasi untuk tanaman ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Panen yang Belum Disertifikasi -->
@if($harvestsWithoutCertification->count() > 0)
<div class="card">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Panen yang Belum Disertifikasi ({{ $harvestsWithoutCertification->count() }})</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Lokasi Penanaman</th>
                        <th>Tanggal Panen</th>
                        <th>Jumlah Panen</th>
                        <th>Batch No</th>
                        <th width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($harvestsWithoutCertification as $harvest)
                        <tr>
                            <td>
                                {{ $harvest->location?->name ?: '-' }}
                            </td>
                            <td>
                                @if($harvest->harvested_at)
                                    <i class="fas fa-calendar me-2"></i>{{ $harvest->harvested_at->format('d M Y') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($harvest->quantity)
                                    {{ number_format($harvest->quantity, 2) }} {{ $harvest->unit ?: 'kg' }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($harvest->batch_no)
                                    <span class="badge bg-info">{{ $harvest->batch_no }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($harvest->certification)
                                    <a href="{{ route('certifications.reports.create', $harvest->certification) }}" class="btn btn-sm btn-primary" title="Lanjutkan Sertifikasi">
                                        <i class="fas fa-play me-1"></i>Lanjutkan Sertifikasi
                                    </a>
                                @else
                                    <a href="{{ route('certifications.show', $harvest) }}" class="btn btn-sm btn-success" title="Buat Sertifikasi">
                                        <i class="fas fa-plus me-1"></i>Buat Sertifikasi
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
// Functions for add-to-stock form from certification by-plant page
function goToStep1FromCertificationByPlant(reportId) {
    document.getElementById('step1_' + reportId).style.display = 'block';
    document.getElementById('step2_' + reportId).style.display = 'none';
}

function goToStep3FromCertificationByPlant(reportId) {
    const inventoryTypeId = document.getElementById('inventory_type_id_' + reportId).value;
    
    if (!inventoryTypeId) {
        alert('Silakan pilih stok benih terlebih dahulu');
        return;
    }
    
    // Fetch inventory type data to get estimated_value_per_unit
    fetch('/api/inventory-types/' + inventoryTypeId, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.inventoryType) {
            // Auto-fill estimated sale price from inventory type
            const estimatedPriceField = document.getElementById('form_estimated_sale_price_per_kg_' + reportId);
            if (estimatedPriceField && data.inventoryType.estimated_value_per_unit) {
                estimatedPriceField.value = data.inventoryType.estimated_value_per_unit;
            }
        }
    })
    .catch(error => {
        console.error('Error fetching inventory type data:', error);
    });

    // Fetch suggested storage number and pre-fill (user can still edit)
    fetch('{{ route("seed-stock.suggest-storage-number") }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        const storageInput = document.getElementById('form_storage_number_' + reportId);
        if (storageInput && data.suggested) {
            storageInput.value = data.suggested;
        }
    })
    .catch(error => {
        console.error('Error fetching suggested storage number:', error);
    });
    
    // Update form action
    const form = document.getElementById('addStockFromCertificationForm' + reportId);
    form.action = '/seed-stock/' + inventoryTypeId + '/add-certified-seed';
    
    // Show step 2 (form)
    document.getElementById('step1_' + reportId).style.display = 'none';
    document.getElementById('step2_' + reportId).style.display = 'block';
}

// Reset modal when closed
document.addEventListener('DOMContentLoaded', function() {
    // Get all modals with id starting with 'addToStockModal'
    const modals = document.querySelectorAll('[id^="addToStockModal"]');
    
    modals.forEach(function(modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            // Extract report ID from modal ID
            const reportId = modal.id.replace('addToStockModal', '');
            
            // Reset form
            const inventoryTypeSelect = document.getElementById('inventory_type_id_' + reportId);
            if (inventoryTypeSelect) {
                inventoryTypeSelect.value = '';
            }
            
            // Reset to step 1
            goToStep1FromCertificationByPlant(reportId);
        });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if URL has show_report parameter to auto-open modal
    const urlParams = new URLSearchParams(window.location.search);
    const showReportId = urlParams.get('show_report');
    
    if (showReportId) {
        // Remove the parameter from URL
        const newUrl = window.location.pathname + window.location.search.replace(/[?&]show_report=\d+/, '');
        window.history.replaceState({}, '', newUrl);
        
        // Open the modal for the specified report
        const modalId = 'viewReportDetailModal' + showReportId;
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }
    }
    
    // Sync unit from seed_unit to certified_seed_unit (hidden field) for all renew modals
    const renewModals = document.querySelectorAll('[id^="renewCertificationModal"]');
    renewModals.forEach(function(modal) {
        const modalId = modal.id;
        const reportId = modalId.replace('renewCertificationModal', '');
        const seedUnit = document.getElementById('seed_unit_renew' + reportId);
        const certifiedSeedUnit = document.getElementById('certified_seed_unit_renew' + reportId);

        if (seedUnit && certifiedSeedUnit) {
            function syncUnit() {
                const selectedUnit = seedUnit.value || 'kg';
                certifiedSeedUnit.value = selectedUnit;
            }

            seedUnit.addEventListener('change', syncUnit);
            // Sync on page load
            syncUnit();
        }
    });
});

// Function to view harvest detail from certification page
function viewHarvestDetailFromCertification(harvestId) {
    if (!harvestId || String(harvestId).trim() === '') {
        alert('Data panen tidak tersedia untuk sertifikasi ini.');
        return;
    }
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('harvestDetailModalCertification'));
    modal.show();
    
    // Show loading
    document.getElementById('harvestDetailContentCertification').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    // Fetch harvest detail (use route with harvest_id)
    fetch(`/harvests/${encodeURIComponent(harvestId)}/detail`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
        .then(async response => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const msg = data.message || data.error || 'Network response was not ok';
                throw new Error(msg);
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                const harvest = data.harvest;
                const planting = data.planting;
                const tasks = data.tasks || [];
                const treatments = data.treatments || [];
                const nutrients = data.nutrients || [];
                const notes = data.notes || [];
                const expenses = data.expenses || [];
                
                let content = '';
                
                // Section 1: Informasi Penanaman (data dari Form Tanam Baru)
                content += `
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-seedling me-2"></i>Informasi Penanaman
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2"><strong>Nama Tanaman:</strong> ${planting.plant_name || '-'}</div>
                                <div class="mb-2"><strong>Varietas:</strong> ${planting.variety || '-'}</div>
                                <div class="mb-2"><strong>Nomor Batch Tanam:</strong> ${planting.planting_batch_number || '-'}</div>
                                <div class="mb-2"><strong>Lokasi Tanam:</strong> ${planting.bed_label || '-'}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2"><strong>Jumlah Tanam:</strong> ${planting.quantity_planted ? number_format(planting.quantity_planted, 0) + ' tanaman' : '-'}</div>
                                <div class="mb-2"><strong>Tanggal Tanam:</strong> ${planting.planted_at || '-'}</div>
                                <div class="mb-2"><strong>Estimasi Panen:</strong> ${planting.estimated_harvest_date || '-'}</div>
                            </div>
                        </div>
                        ${planting.notes ? `<div class="mt-2"><strong>Catatan Penanaman:</strong> ${planting.notes}</div>` : ''}
                    </div>
                `;

                // Section 2: Informasi Panen
                content += `
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-clipboard-check me-2"></i>Informasi Panen
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2"><strong>Nomor Batch Panen:</strong> <span class="badge bg-info">${harvest.batch_no || '-'}</span></div>
                                <div class="mb-2"><strong>Tanggal Panen:</strong> ${harvest.harvested_at_formatted || '-'}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2"><strong>Jumlah Panen:</strong> <strong>${harvest.quantity_formatted || '0.00'} ${harvest.unit || 'kg'}</strong></div>
                                <div class="mb-2"><strong>Kualitas Panen:</strong> ${harvest.quality || '-'}</div>
                            </div>
                        </div>
                        ${harvest.note ? `<div class="mt-2"><strong>Catatan Panen:</strong> ${harvest.note}</div>` : ''}
                    </div>
                `;
                
                // Section 3: Riwayat Laporan
                content += `
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-clipboard-list me-2"></i>Riwayat Laporan
                        </h6>
                `;
                if (tasks.length > 0) {
                    content += `
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Judul Laporan</th>
                                        <th>Status</th>
                                        <th>Prioritas</th>
                                        <th>Ditugaskan</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    tasks.forEach(task => {
                        const statusClass = task.new_status === 'selesai' ? 'success' : (task.new_status === 'dalam_progress' ? 'info' : 'danger');
                        const statusLabel = task.new_status === 'selesai' ? 'Selesai' : (task.new_status === 'dalam_progress' ? 'Dalam Progress' : 'Tidak Selesai');
                        const priorityClass = task.new_priority === 'tertinggi' || task.new_priority === 'tinggi' ? 'danger' : (task.new_priority === 'medium' ? 'warning' : 'secondary');
                        content += `
                            <tr>
                                <td>${task.due_date || '-'}</td>
                                <td><strong>${task.title}</strong></td>
                                <td><span class="badge bg-${statusClass}">${statusLabel}</span></td>
                                <td><span class="badge bg-${priorityClass}">${task.new_priority ? task.new_priority.charAt(0).toUpperCase() + task.new_priority.slice(1) : 'Medium'}</span></td>
                                <td>${task.assigned_user_name || '-'}</td>
                            </tr>
                        `;
                    });
                    content += `
                                </tbody>
                            </table>
                        </div>
                    `;
                } else {
                    content += '<p class="text-muted mb-0">Belum ada riwayat laporan untuk penanaman ini.</p>';
                }
                content += '</div>';
                
                // Section 3: Riwayat Perawatan
                content += `
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-first-aid me-2"></i>Riwayat Perawatan
                        </h6>
                `;
                if (treatments.length > 0) {
                    content += `
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Nama Perawatan</th>
                                        <th>Tipe</th>
                                        <th>Produk</th>
                                        <th>Metode</th>
                                        <th>Biaya</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    treatments.forEach(treatment => {
                        content += `
                            <tr>
                                <td>${treatment.treatment_date || '-'}</td>
                                <td><strong>${treatment.treatment_name}</strong></td>
                                <td>${treatment.treatment_type}</td>
                                <td>${treatment.product_detail || '-'}</td>
                                <td>${treatment.application_method || '-'}</td>
                                <td>${treatment.total_cost ? 'Rp ' + number_format(treatment.total_cost, 0) : '-'}</td>
                            </tr>
                        `;
                    });
                    content += `
                                </tbody>
                            </table>
                        </div>
                    `;
                } else {
                    content += '<p class="text-muted mb-0">Belum ada riwayat perawatan untuk penanaman ini.</p>';
                }
                content += '</div>';
                
                // Section 4: Riwayat Nutrisi
                content += `
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-flask me-2"></i>Riwayat Nutrisi
                        </h6>
                `;
                if (nutrients.length > 0) {
                    content += `
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Produk</th>
                                        <th>Jumlah</th>
                                        <th>Metode</th>
                                        <th>Biaya</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    nutrients.forEach(nutrient => {
                        content += `
                            <tr>
                                <td>${nutrient.application_date || '-'}</td>
                                <td><strong>${nutrient.product_applied}</strong></td>
                                <td>${number_format(nutrient.amount_applied, 2)} ${nutrient.unit}</td>
                                <td>${nutrient.application_method || '-'}</td>
                                <td>${nutrient.total_cost ? 'Rp ' + number_format(nutrient.total_cost, 0) : '-'}</td>
                            </tr>
                        `;
                    });
                    content += `
                                </tbody>
                            </table>
                        </div>
                    `;
                } else {
                    content += '<p class="text-muted mb-0">Belum ada riwayat nutrisi untuk penanaman ini.</p>';
                }
                content += '</div>';
                
                // Section 5: Catatan
                content += `
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-sticky-note me-2"></i>Catatan
                        </h6>
                `;
                if (notes.length > 0) {
                    content += `
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Judul</th>
                                        <th>Deskripsi</th>
                                        <th>Pembuat</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    notes.forEach(note => {
                        content += `
                            <tr>
                                <td>${note.note_date || '-'}</td>
                                <td><strong>${note.title || 'Catatan'}</strong></td>
                                <td>${note.description_short || '-'}</td>
                                <td>${note.user_name || '-'}</td>
                            </tr>
                        `;
                    });
                    content += `
                                </tbody>
                            </table>
                        </div>
                    `;
                } else {
                    content += '<p class="text-muted mb-0">Belum ada catatan untuk lokasi penanaman ini.</p>';
                }
                content += '</div>';
                
                // Section 6: Total Pengeluaran
                if (data.totalExpenses !== undefined) {
                    content += `
                        <div class="mb-3">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-money-bill-wave me-2"></i>Total Pengeluaran
                            </h6>
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center p-2">
                                            <small class="text-muted">Perawatan</small>
                                            <h6 class="mb-0">Rp ${number_format(data.totalTreatmentCost || 0, 0)}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center p-2">
                                            <small class="text-muted">Nutrisi</small>
                                            <h6 class="mb-0">Rp ${number_format(data.totalNutrientCost || 0, 0)}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center p-2">
                                            <small class="text-muted">Lainnya</small>
                                            <h6 class="mb-0">Rp ${number_format(data.totalOtherExpenses || 0, 0)}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center p-2">
                                            <small>Total Keseluruhan</small>
                                            <h6 class="mb-0">Rp ${number_format(data.totalExpenses || 0, 0)}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    `;
                    
                    if (expenses.length > 0) {
                        content += `
                            <h6 class="mt-3 mb-2">Rincian Pengeluaran</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Nama Pengeluaran</th>
                                            <th>Tipe</th>
                                            <th>Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        expenses.forEach(expense => {
                            content += `
                                <tr>
                                    <td>${expense.expense_date || '-'}</td>
                                    <td><strong>${expense.expense_name}</strong></td>
                                    <td><span class="badge bg-secondary">${expense.expense_type_label || '-'}</span></td>
                                    <td>Rp ${number_format(expense.amount, 0)}</td>
                                </tr>
                            `;
                        });
                        content += `
                                    </tbody>
                                </table>
                            </div>
                        `;
                    }
                    content += '</div>';
                }
                
                document.getElementById('harvestDetailContentCertification').innerHTML = content;
            } else {
                throw new Error('Invalid response format');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            let errorMessage = 'Terjadi kesalahan saat memuat data. Silakan refresh halaman dan coba lagi.';
            if (error.message) {
                errorMessage += '<br><small class="text-muted">Detail: ' + error.message + '</small>';
            }
            document.getElementById('harvestDetailContentCertification').innerHTML = 
                '<div class="alert alert-danger">' + errorMessage + '</div>';
        });
}

// Helper function for number formatting
function number_format(number, decimals) {
    if (number === null || number === undefined) return '0.00';
    return parseFloat(number).toFixed(decimals || 2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
</script>
@endpush

<!-- Modal: Detail Panen -->
<div class="modal fade" id="harvestDetailModalCertification" tabindex="-1" aria-labelledby="harvestDetailModalCertificationLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="harvestDetailModalCertificationLabel">
                    <i class="fas fa-info-circle me-2"></i>Detail Panen
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="harvestDetailContentCertification" style="max-height: 70vh; overflow-y: auto;">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

