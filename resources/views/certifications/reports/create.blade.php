@extends('layouts.app')

@section('title', 'Form Input Laporan Sertifikasi Benih - SIBESTI')

@section('content')
@php
    $harvest = $certification->harvest;
    $plant = $harvest->plant ?? null;
@endphp
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('certifications.index') }}">Manajemen Sertifikasi</a></li>
        @if($plant)
            <li class="breadcrumb-item"><a href="{{ route('certifications.by-plant', $plant) }}">Data Riwayat Sertifikasi</a></li>
        @endif
        <li class="breadcrumb-item active">Form Input Laporan Sertifikasi Benih</li>
    </ol>
</nav>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>Terjadi kesalahan:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Form Input Laporan Sertifikasi Benih</h4>
        <small class="text-muted">
            Data panen: {{ $plant ? ($plant->type?->name ?? $plant->name) : '-' }} - {{ $plant->variety ?? 'Tanpa Varietas' }}
            @if($harvest->location)
                | Lokasi: {{ $harvest->location->name }}
            @endif
            @if($harvest->harvested_at)
                | Tanggal Panen: {{ $harvest->harvested_at->format('d M Y') }}
            @endif
        </small>
    </div>
    @if($plant)
        <a href="{{ route('certifications.by-plant', $plant) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Riwayat Sertifikasi
        </a>
    @else
        <a href="{{ route('certifications.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    @endif
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('certifications.reports.store', $certification) }}" method="POST" enctype="multipart/form-data">
            @csrf

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
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Bagian A: Informasi Dasar Laporan</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nomor Laporan BPSB <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="report_number_bpsb" value="{{ old('report_number_bpsb', 'BPSB-' . date('Y') . '-' . str_pad(\App\Models\CertificationReport::whereYear('report_date', date('Y'))->count() + 1, 6, '0', STR_PAD_LEFT)) }}" required>
                            <small class="text-muted">Kosongkan akan di-generate otomatis</small>
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

            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Bagian B: Lot Produksi yang Diperiksa</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Lot Produksi: <strong>{{ $harvest->plant->type?->name ?? $harvest->plant->name ?? '-' }} - {{ $harvest->plant->variety ?? 'Tanpa Varietas' }} ({{ $harvest->location?->name ?? '-' }})</strong>
                    </div>
                </div>
            </div>

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

            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Bagian D: Jumlah Benih yang Lulus Sertifikasi</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Satuan Inventaris</label>
                            <select class="form-select" name="seed_unit" id="seed_unit">
                                <option value="">Pilih Satuan</option>
                                <option value="kg" {{ old('seed_unit', 'kg') == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                <option value="ton" {{ old('seed_unit') == 'ton' ? 'selected' : '' }}>Ton</option>
                                <option value="gram" {{ old('seed_unit') == 'gram' ? 'selected' : '' }}>Gram</option>
                                <option value="butir" {{ old('seed_unit') == 'butir' ? 'selected' : '' }}>Butir/Biji</option>
                                <option value="pcs" {{ old('seed_unit') == 'pcs' ? 'selected' : '' }}>Pcs</option>
                                <option value="batang" {{ old('seed_unit') == 'batang' ? 'selected' : '' }}>Batang</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Inventaris</label>
                            <input type="number" class="form-control" name="certified_seed_quantity" id="certified_seed_quantity" value="{{ old('certified_seed_quantity') }}" step="0.01" min="0" placeholder="0.00">
                            <input type="hidden" name="certified_seed_unit" id="certified_seed_unit" value="{{ old('certified_seed_unit', 'kg') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estimasi Penjualan (Rp/kg)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" name="estimated_sale_price_per_kg" value="{{ old('estimated_sale_price_per_kg') }}" step="0.01" min="0" placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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

            <div class="d-flex justify-content-end gap-2">
                @if($plant)
                    <a href="{{ route('certifications.by-plant', $plant) }}" class="btn btn-secondary">Batal</a>
                @else
                    <a href="{{ route('certifications.index') }}" class="btn btn-secondary">Batal</a>
                @endif
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Simpan Laporan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const seedUnit = document.getElementById('seed_unit');
    const certifiedSeedUnit = document.getElementById('certified_seed_unit');
    function syncUnit() {
        if (seedUnit && certifiedSeedUnit) {
            certifiedSeedUnit.value = seedUnit.value || 'kg';
        }
    }
    if (seedUnit) {
        seedUnit.addEventListener('change', syncUnit);
        syncUnit();
    }
});
</script>
@endpush
@endsection
