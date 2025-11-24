@extends('layouts.app')

@section('title', 'Kelola Sertifikasi - SIBIT')

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
    <a href="{{ route('certifications.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<!-- Bagian 1: Detail Lot Produksi -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Detail Lot Produksi</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label fw-bold">Produsen Benih</label>
                    <p class="mb-0">{{ $harvest->plant->plantingLocation?->baseLocation?->name ?: 'BBI Tanaman Padi' }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Alamat</label>
                    <p class="mb-0">
                        @if($harvest->location?->baseLocation)
                            {{ $harvest->location->baseLocation->district ?: '-' }}; 
                            {{ $harvest->location->baseLocation->city ?: '-' }}; 
                            {{ $harvest->location->baseLocation->name ?: '-' }}
                        @else
                            Sungai Darah; Pulau Punjung; Dharmasraya
                        @endif
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Varietas</label>
                    <p class="mb-0">{{ $harvest->plant->variety ?: '-' }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tanggal Tanam</label>
                    <p class="mb-0">
                        @if($harvest->planting?->planted_at)
                            {{ $harvest->planting->planted_at->format('d-M-y') }}
                        @elseif($harvest->planting?->created_at)
                            {{ $harvest->planting->created_at->format('d-M-y') }}
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Luas Tanam</label>
                    <p class="mb-0">
                        @php
                            $plantingLocation = $harvest->location;
                            $area = null;
                            if ($plantingLocation && $plantingLocation->bed_length_m && $plantingLocation->bed_width_m && $plantingLocation->num_beds) {
                                // Calculate area in square meters
                                $areaM2 = $plantingLocation->bed_length_m * $plantingLocation->bed_width_m * $plantingLocation->num_beds;
                                // Convert to hectares (1 hectare = 10000 m²)
                                $area = number_format($areaM2 / 10000, 2);
                            }
                        @endphp
                        {{ $area ? $area . ' ha' : '1.00 ha' }}
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Kelas Benih (Diajukan)</label>
                    <p class="mb-0">
                        <span class="badge bg-info">{{ $certification->seed_class_requested ?: 'BP (Benih Pokok)' }}</span>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Status Sertifikasi</label>
                    <p class="mb-0">
                        <span class="badge {{ match($certification->certification_status) {
                            'dalam_proses' => 'bg-warning',
                            'lulus' => 'bg-success',
                            'tidak_lulus' => 'bg-danger',
                            'selesai' => 'bg-info',
                            default => 'bg-secondary',
                        } }}">{{ $certification->status_label }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bagian 2: Riwayat Pemeriksaan & Uji -->
<div class="card">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Riwayat Pemeriksaan & Uji</h5>
        <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addReportModal">
            <i class="fas fa-plus me-1"></i>Tambah Laporan Pemeriksaan
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Jenis Laporan</th>
                        <th>Fase</th>
                        <th>Tanggal Laporan</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($certification->reports as $report)
                        <tr>
                            <td>
                                <i class="fas fa-file-alt me-2"></i>Laporan Pemeriksaan Pertanaman
                                @if($report->report_number_bpsb)
                                    <br><small class="text-muted">No: {{ $report->report_number_bpsb }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $report->inspection_phase }}</span>
                            </td>
                            <td>{{ $report->report_date->format('d M Y') }}</td>
                            <td>
                                <span class="badge {{ $report->conclusion_badge_class }}">
                                    {{ $report->conclusion ?: 'Belum Ditentukan' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('certifications.reports.show', $report) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-clipboard fa-3x mb-3"></i>
                                    <p>Belum ada laporan pemeriksaan.</p>
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addReportModal">
                                        <i class="fas fa-plus me-1"></i>Tambah Laporan Pertama
                                    </button>
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
                    
                    <!-- Bagian A: Informasi Dasar Laporan -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Bagian A: Informasi Dasar Laporan</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nomor Laporan BPSB</label>
                                    <input type="text" class="form-control" name="report_number_bpsb" value="{{ old('report_number_bpsb') }}">
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
                            </div>
                        </div>
                    </div>

                    <!-- Bagian D: Kesimpulan & Lampiran -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Bagian D: Kesimpulan & Lampiran</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kesimpulan / Rekomendasi <span class="text-danger">*</span></label>
                                    <div class="mt-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="conclusion" id="conclusion_lulus" value="LULUS" {{ old('conclusion') == 'LULUS' ? 'checked' : '' }} required>
                                            <label class="form-check-label text-success fw-bold" for="conclusion_lulus">LULUS</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="conclusion" id="conclusion_tidak_lulus" value="TIDAK LULUS" {{ old('conclusion') == 'TIDAK LULUS' ? 'checked' : '' }} required>
                                            <label class="form-check-label text-danger fw-bold" for="conclusion_tidak_lulus">TIDAK LULUS</label>
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
@endsection

