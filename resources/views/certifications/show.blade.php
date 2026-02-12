@extends('layouts.app')

@section('title', 'Kelola Sertifikasi - SIBESTI')

@section('content')
<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('certifications.index') }}">Manajemen Sertifikasi</a></li>
        <li class="breadcrumb-item active">Kelola Sertifikasi</li>
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
        <h4 class="mb-1">Kelola Sertifikasi: {{ $harvest->plant->type?->name ?: $harvest->plant->name }} - {{ $harvest->plant->variety ?: 'Tanpa Varietas' }}</h4>
        <small class="text-muted">
            Blok: {{ $harvest->location?->name ?: '-' }} | 
            @if($harvest->location?->name)
                {{ str_replace('-', '', $harvest->location->name) }}
            @else
                Blok Lahan
            @endif
        </small>
    </div>
    <div class="d-flex gap-2">
    <a href="{{ route('certifications.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
        @if(Route::has('certifications.edit'))
        <a href="{{ route('certifications.edit', $certification) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
        @endif
        @if(Route::has('certifications.destroy'))
        <form action="{{ route('certifications.destroy', $certification) }}" method="POST" onsubmit="return confirm('Hapus sertifikasi ini? Tindakan tidak dapat dibatalkan.')" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash me-2"></i>Hapus
            </button>
        </form>
        @endif
    </div>
</div>

<!-- Bagian 2: Riwayat Pemeriksaan & Uji -->
<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Riwayat Pemeriksaan & Uji</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Jenis Laporan</th>
                        <th>Fase</th>
                        <th>Tanggal Laporan</th>
                        <th>Tanggal Masa Edar</th>
                        <th>Status</th>
                        <th>Status Stok</th>
                        <th>Tambahkan ke Stok</th>
                        <th width="220">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($certification->reports as $report)
                        @php
                            // Load inventoryTypes relation if not already loaded
                            if (!$report->relationLoaded('inventoryTypes')) {
                                $report->load('inventoryTypes');
                            }
                            // Check if this report has been added to any inventory type
                            $hasBeenAddedToStock = $report->inventoryTypes->count() > 0;
                            // Check if report is eligible for adding to stock
                            $isEligibleForStock = $report->conclusion === 'LULUS' 
                                && $report->certified_seed_quantity 
                                && $report->certified_seed_quantity > 0;
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
                                    @endphp
                                    @if($isExpired)
                                        <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Melewati masa edar</small>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $isExpired = $report->expiry_date && $report->expiry_date->isPast();
                                @endphp
                                @if($isExpired)
                                    <span class="badge bg-danger">Sudah Melewati Masa Edar</span>
                                @else
                                <span class="badge {{ $report->conclusion_badge_class }}">
                                    {{ $report->conclusion ?: 'Belum Ditentukan' }}
                                </span>
                                @endif
                            </td>
                            <td>
                                @if($hasBeenAddedToStock)
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
                                @if($hasBeenAddedToStock)
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
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addToStockModal{{ $report->id }}" title="Tambahkan ke Stok">
                                        <i class="fas fa-plus me-1"></i>Tambahkan ke Stok
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('certifications.reports.show', $report) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#renewCertificationModal{{ $report->id }}" title="Lakukan Sertifikasi Ulang">
                                        <i class="fas fa-redo me-1"></i>Sertifikasi Ulang
                                    </button>
                                    @if(Route::has('certifications.reports.edit'))
                                    <a href="{{ route('certifications.reports.edit', $report) }}" class="btn btn-sm btn-outline-warning" title="Edit Laporan">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
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
                        
                        @if(!$hasBeenAddedToStock && $isEligibleForStock)
                        <!-- Modal: Tambahkan Data Stok dari Sertifikasi -->
                        <div class="modal fade" id="addToStockModal{{ $report->id }}" tabindex="-1" aria-labelledby="addToStockModalLabel{{ $report->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addToStockModalLabel{{ $report->id }}">Tambahkan Data Stok dari Sertifikasi</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form id="addStockFromCertificationForm{{ $report->id }}" method="POST" action="">
                                        @csrf
                                        <input type="hidden" name="certification_report_id" value="{{ $report->id }}">
                                        <input type="hidden" name="redirect_to_certification" value="1">
                                        <div class="modal-body">
                                            <!-- Step 1: Pilih Stok Benih -->
                                            <div id="step1_{{ $report->id }}" class="step-content">
                                                <h6 class="mb-3">Langkah 1: Pilih Stok Benih</h6>
                                                <div class="mb-3">
                                                    <label class="form-label">Pilih Stok Benih <span class="text-danger">*</span></label>
                                                    <select name="inventory_type_id" id="inventory_type_id_{{ $report->id }}" class="form-select" required>
                                                        <option value="">-- Pilih Stok Benih --</option>
                                                        @foreach($inventoryTypes as $inventoryType)
                                                            <option value="{{ $inventoryType->id }}">{{ $inventoryType->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <small class="text-muted">Pilih stok benih tempat benih akan ditambahkan</small>
                                                </div>
                                                <div class="d-flex justify-content-end">
                                                    <button type="button" class="btn btn-primary" onclick="goToStep3FromCertification({{ $report->id }})">Lanjutkan</button>
                                                </div>
                                            </div>

                                            <!-- Step 2: Form Tambah Stok -->
                                            <div id="step2_{{ $report->id }}" class="step-content" style="display: none;">
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
                                                            <select class="form-select" name="plant_id" id="form_plant_id_{{ $report->id }}" required disabled>
                                                                <option value="{{ $report->certification->plant_id }}" selected>
                                                                    {{ $report->certification->plant->name }} @if($report->certification->plant->variety) - {{ $report->certification->plant->variety }} @endif
                                                                </option>
                                                            </select>
                                                            <input type="hidden" name="plant_id" value="{{ $report->certification->plant_id }}">
                                                        </div>

                                                        <!-- 2. Satuan Inventaris -->
                                                        <div class="mb-3">
                                                            <label class="form-label">Satuan Inventaris <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="seed_unit" id="form_seed_unit_{{ $report->id }}" required>
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
                                                                       name="estimated_sale_price_per_kg" id="form_estimated_sale_price_per_kg_{{ $report->id }}" 
                                                                       step="0.01" min="0" placeholder="0.00"
                                                                       value="{{ $report->estimated_sale_price_per_kg ?? '' }}">
                                                            </div>
                                                        </div>

                                                        <!-- 4. Pengisi Data -->
                                                        <div class="mb-3">
                                                            <label class="form-label">Pengisi Data <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="filled_by_user_id" id="form_filled_by_user_id_{{ $report->id }}" required>
                                                                <option value="">-- Pilih User --</option>
                                                                @foreach($users as $user)
                                                                    <option value="{{ $user->id }}" {{ auth()->id() == $user->id ? 'selected' : '' }}>
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
                                                            <select class="form-select" name="planting_location_id" id="form_planting_location_id_{{ $report->id }}" required disabled>
                                                                <option value="{{ $report->certification->planting_location_id }}" selected>
                                                                    {{ $report->certification->plantingLocation->name ?? '-' }}
                                                                </option>
                                                            </select>
                                                            <input type="hidden" name="planting_location_id" value="{{ $report->certification->planting_location_id }}">
                                                        </div>

                                                        <!-- 2. Jumlah Inventaris -->
                                                        <div class="mb-3">
                                                            <label class="form-label">Jumlah Inventaris <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control" 
                                                                   name="seed_quantity" id="form_seed_quantity_{{ $report->id }}" 
                                                                   step="0.01" min="0.01" required placeholder="0.00"
                                                                   value="{{ $report->certified_seed_quantity ?? '' }}">
                                                        </div>

                                                        <!-- 3. Tanggal Kadaluarsa -->
                                                        <div class="mb-3">
                                                            <label class="form-label">Tanggal Kadaluarsa</label>
                                                            <input type="date" class="form-control" 
                                                                   name="expiry_date" id="form_expiry_date_{{ $report->id }}"
                                                                   value="{{ $report->expiry_date ? $report->expiry_date->format('Y-m-d') : '' }}">
                                                        </div>

                                                        <!-- 4. Nomor Penyimpanan -->
                                                        <div class="mb-3">
                                                            <label class="form-label">Nomor Penyimpanan</label>
                                                            <input type="text" class="form-control" 
                                                                   name="storage_number" id="form_storage_number_{{ $report->id }}" 
                                                                   placeholder="Nomor penyimpanan" maxlength="50"
                                                                   value="{{ $report->report_number_bpsb ?? '' }}">
                                                            <small class="text-muted">Nomor penyimpanan (dapat diedit)</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between mt-3">
                                                    <button type="button" class="btn btn-secondary" onclick="goToStep1FromCertification({{ $report->id }})">Kembali</button>
                                                    <button type="submit" class="btn btn-success">Simpan</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                        
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
                                                            <input type="text" class="form-control" name="report_number_bpsb" value="{{ old('report_number_bpsb') }}" required>
                                                            <small class="text-muted">Nomor laporan harus unik</small>
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
                                                        Lot Produksi: <strong>{{ $certification->harvest->plant->type?->name ?: $certification->harvest->plant->name }} - {{ $certification->harvest->plant->variety ?: 'Tanpa Varietas' }} ({{ $certification->harvest->location?->name ?: '-' }})</strong>
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
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-clipboard fa-3x mb-3"></i>
                                    <p>Belum ada laporan pemeriksaan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form Tambah Laporan -->
<div class="modal fade" id="addReportModal" tabindex="-1" aria-labelledby="addReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addReportModalLabel">Form Input Laporan Pemeriksaan Pertanaman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('certifications.reports.store', $certification) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    
                    <!-- Field Jenis Sertifikasi -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Jenis Sertifikasi</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Jenis Sertifikasi <span class="text-danger">*</span></label>
                                <select class="form-select" name="report_type" required>
                                    <option value="Laporan Pemeriksaan Pertanaman" {{ old('report_type', 'Laporan Pemeriksaan Pertanaman') == 'Laporan Pemeriksaan Pertanaman' ? 'selected' : '' }}>Laporan Pemeriksaan Pertanaman</option>
                                    <option value="Laporan Sertifikasi Ulang" {{ old('report_type') == 'Laporan Sertifikasi Ulang' ? 'selected' : '' }}>Laporan Sertifikasi Ulang</option>
                                </select>
                                <small class="text-muted">Pilih jenis sertifikasi yang akan dibuat</small>
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
                                    <input type="text" class="form-control" name="report_number_bpsb" value="{{ old('report_number_bpsb') }}" required>
                                    <small class="text-muted">Nomor laporan harus unik</small>
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
                                Lot Produksi: <strong>{{ $certification->harvest->plant->type?->name ?: $certification->harvest->plant->name }} - {{ $certification->harvest->plant->variety ?: 'Tanpa Varietas' }} ({{ $certification->harvest->location?->name ?: '-' }})</strong>
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
                                            <input class="form-check-input" type="radio" name="plant_characteristics_match" id="match_yes" value="1" {{ old('plant_characteristics_match') == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="match_yes">Ya</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="plant_characteristics_match" id="match_no" value="0" {{ old('plant_characteristics_match') == '0' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="match_no">Tidak</label>
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
                                    <select class="form-select" name="seed_unit" id="seed_unit_modal">
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
                                    <input type="number" class="form-control" name="certified_seed_quantity" id="certified_seed_quantity_modal" value="{{ old('certified_seed_quantity') }}" step="0.01" min="0" placeholder="0.00">
                                    <input type="hidden" name="certified_seed_unit" id="certified_seed_unit_modal" value="{{ old('certified_seed_unit', 'kg') }}">
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
                                            <input class="form-check-input" type="radio" name="conclusion" id="conclusion_lulus_modal" value="LULUS" {{ old('conclusion') == 'LULUS' ? 'checked' : '' }} required>
                                            <label class="form-check-label text-success fw-bold" for="conclusion_lulus_modal">LULUS</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="conclusion" id="conclusion_tidak_lulus_modal" value="TIDAK LULUS" {{ old('conclusion') == 'TIDAK LULUS' ? 'checked' : '' }} required>
                                            <label class="form-check-label text-danger fw-bold" for="conclusion_tidak_lulus_modal">TIDAK LULUS</label>
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
                        <i class="fas fa-save me-2"></i>Simpan Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sync unit from seed_unit to certified_seed_unit (hidden field) in modal
    const seedUnitModal = document.getElementById('seed_unit_modal');
    const certifiedSeedUnitModal = document.getElementById('certified_seed_unit_modal');

    function syncUnitModal() {
        if (seedUnitModal && certifiedSeedUnitModal) {
            const selectedUnit = seedUnitModal.value || 'kg';
            certifiedSeedUnitModal.value = selectedUnit;
        }
    }

    if (seedUnitModal) {
        seedUnitModal.addEventListener('change', syncUnitModal);
        // Sync on page load
        syncUnitModal();
    }
});

// Functions for add-to-stock form from certification page
function goToStep1FromCertification(reportId) {
    document.getElementById('step1_' + reportId).style.display = 'block';
    document.getElementById('step2_' + reportId).style.display = 'none';
}

function goToStep3FromCertification(reportId) {
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
            goToStep1FromCertification(reportId);
        });
    });
});
</script>
@endpush
@endsection

