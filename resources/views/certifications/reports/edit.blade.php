@extends('layouts.app')

@section('title', 'Edit Laporan Pemeriksaan - SIBESTI')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('certifications.index') }}">Manajemen Sertifikasi</a></li>
        <li class="breadcrumb-item"><a href="{{ route('certifications.show', $certification->harvest) }}">Kelola Sertifikasi</a></li>
        <li class="breadcrumb-item active">Edit Laporan</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Edit Laporan Pemeriksaan</h4>
        <small class="text-muted">Tanaman: {{ $harvest->plant->type?->name ?: $harvest->plant->name }} - {{ $harvest->plant->variety ?: 'Tanpa Varietas' }}</small>
    </div>
    <a href="{{ route('certifications.show', $certification->harvest) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('certifications.reports.update', $report) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nomor Laporan BPSB</label>
                    <input type="text" class="form-control" name="report_number_bpsb" value="{{ old('report_number_bpsb', $report->report_number_bpsb) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Laporan <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="report_date" value="{{ old('report_date', optional($report->report_date)->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Musim Tanam</label>
                    <input type="text" class="form-control" name="growing_season" value="{{ old('growing_season', $report->growing_season) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Fase Pemeriksaan <span class="text-danger">*</span></label>
                    <select class="form-select" name="inspection_phase" required>
                        <option value="">Pilih Fase</option>
                        @foreach(['Vegetatif','Generatif','Menjelang Panen','Lainnya'] as $phase)
                            <option value="{{ $phase }}" {{ old('inspection_phase', $report->inspection_phase) == $phase ? 'selected' : '' }}>{{ $phase }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Petugas Pengawas Mutu (BPSB)</label>
                    <input type="text" class="form-control" name="inspector_name" value="{{ old('inspector_name', $report->inspector_name) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kelas Benih yang Dihasilkan</label>
                    <select class="form-select" name="seed_class_result">
                        <option value="">Pilih Kelas</option>
                        @foreach(['BS','BP','BR'] as $cls)
                            <option value="{{ $cls }}" {{ old('seed_class_result', $report->seed_class_result) == $cls ? 'selected' : '' }}>{{ $cls }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Isolasi - Utara</label>
                    <input type="text" class="form-control" name="isolation_north" value="{{ old('isolation_north', $report->isolation_north) }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Isolasi - Timur</label>
                    <input type="text" class="form-control" name="isolation_east" value="{{ old('isolation_east', $report->isolation_east) }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Isolasi - Selatan</label>
                    <input type="text" class="form-control" name="isolation_south" value="{{ old('isolation_south', $report->isolation_south) }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Isolasi - Barat</label>
                    <input type="text" class="form-control" name="isolation_west" value="{{ old('isolation_west', $report->isolation_west) }}">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Keadaan Hama dan Penyakit</label>
                    <textarea class="form-control" name="pest_disease_condition" rows="2">{{ old('pest_disease_condition', $report->pest_disease_condition) }}</textarea>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Keadaan Rerumputan</label>
                    <select class="form-select" name="weed_condition">
                        <option value="">Pilih Kondisi</option>
                        @foreach(['Bersih','Cukup Bersih','Kotor'] as $weed)
                            <option value="{{ $weed }}" {{ old('weed_condition', $report->weed_condition) == $weed ? 'selected' : '' }}>{{ $weed }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Populasi per Contoh</label>
                    <input type="number" class="form-control" name="population_per_sample" value="{{ old('population_per_sample', $report->population_per_sample) }}" min="0">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jumlah Campuran Varietas Lain</label>
                    <input type="number" class="form-control" name="other_variety_mix_count" value="{{ old('other_variety_mix_count', $report->other_variety_mix_count) }}" min="0">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Campuran Varietas Lain (%)</label>
                    <input type="number" class="form-control" name="other_variety_mix_percentage" value="{{ old('other_variety_mix_percentage', $report->other_variety_mix_percentage) }}" step="0.01" min="0" max="100">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Taksiran Hasil</label>
                    <input type="number" class="form-control" name="estimated_yield" value="{{ old('estimated_yield', $report->estimated_yield) }}" step="0.01" min="0">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Satuan Inventaris</label>
                    <select class="form-select" name="seed_unit" id="seed_unit">
                        <option value="">Pilih Satuan</option>
                        <option value="kg" {{ old('seed_unit', $report->seed_unit) == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                        <option value="ton" {{ old('seed_unit', $report->seed_unit) == 'ton' ? 'selected' : '' }}>Ton</option>
                        <option value="gram" {{ old('seed_unit', $report->seed_unit) == 'gram' ? 'selected' : '' }}>Gram</option>
                        <option value="butir" {{ old('seed_unit', $report->seed_unit) == 'butir' ? 'selected' : '' }}>Butir/Biji</option>
                        <option value="pcs" {{ old('seed_unit', $report->seed_unit) == 'pcs' ? 'selected' : '' }}>Pcs</option>
                        <option value="batang" {{ old('seed_unit', $report->seed_unit) == 'batang' ? 'selected' : '' }}>Batang</option>
                    </select>
                    <small class="text-muted">Pilih satuan inventaris</small>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Total Inventaris</label>
                    <input type="number" class="form-control" name="certified_seed_quantity" id="certified_seed_quantity" value="{{ old('certified_seed_quantity', $report->certified_seed_quantity) }}" step="0.01" min="0">
                    <input type="hidden" name="certified_seed_unit" id="certified_seed_unit" value="{{ old('certified_seed_unit', $report->certified_seed_unit ?: 'kg') }}">
                    <small class="text-muted">Masukkan total inventaris</small>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Estimasi Penjualan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" name="estimated_sale_price_per_kg" value="{{ old('estimated_sale_price_per_kg', $report->estimated_sale_price_per_kg) }}" step="0.01" min="0">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kesimpulan / Rekomendasi <span class="text-danger">*</span></label>
                    <div class="mt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="conclusion" id="conclusion_lulus" value="LULUS" {{ old('conclusion', $report->conclusion) == 'LULUS' ? 'checked' : '' }} required>
                            <label class="form-check-label text-success fw-bold" for="conclusion_lulus">LULUS</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="conclusion" id="conclusion_tidak_lulus" value="TIDAK LULUS" {{ old('conclusion', $report->conclusion) == 'TIDAK LULUS' ? 'checked' : '' }} required>
                            <label class="form-check-label text-danger fw-bold" for="conclusion_tidak_lulus">TIDAK LULUS</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Unggah Pindaian Laporan (Scan)</label>
                    <input type="file" class="form-control" name="scan_file" accept=".pdf,.jpg,.jpeg,.png">
                    @if($report->scan_file_path)
                        <small class="text-muted d-block mt-1">
                            File sekarang: <a href="{{ asset('storage/'.$report->scan_file_path) }}" target="_blank">Lihat</a>
                        </small>
                    @endif
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('certifications.show', $certification->harvest) }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sync unit from seed_unit to certified_seed_unit (hidden field)
    const seedUnit = document.getElementById('seed_unit');
    const certifiedSeedUnit = document.getElementById('certified_seed_unit');

    function syncUnit() {
        if (seedUnit && certifiedSeedUnit) {
            const selectedUnit = seedUnit.value || certifiedSeedUnit.value || 'kg';
            certifiedSeedUnit.value = selectedUnit;
        }
    }

    if (seedUnit) {
        seedUnit.addEventListener('change', syncUnit);
        // Sync on page load
        syncUnit();
    }
});
</script>
@endpush
@endsection

