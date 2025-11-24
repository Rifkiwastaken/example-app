@extends('layouts.app')

@section('title', 'Buat Target Produksi - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Buat Target Produksi</h4>
        <small class="text-muted">Definisikan target di awal tahun anggaran</small>
    </div>
    <a href="{{ route('planning.production-target.index', ['year' => $year]) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('planning.production-target.store') }}" method="POST">
            @csrf

            <!-- Bagian 1: Identitas Target -->
            <h6 class="mb-3 border-bottom pb-2">
                <i class="fas fa-info-circle me-2"></i>Identitas Target
            </h6>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tahun Anggaran <span class="text-danger">*</span></label>
                    <input type="number" name="fiscal_year" class="form-control @error('fiscal_year') is-invalid @enderror" 
                           value="{{ old('fiscal_year', $year) }}" min="2020" max="2100" required>
                    @error('fiscal_year')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Komoditas <span class="text-danger">*</span></label>
                    <select name="commodity" class="form-select @error('commodity') is-invalid @enderror" required>
                        <option value="">-- Pilih Komoditas --</option>
                        @foreach($commodities as $comm)
                            <option value="{{ $comm }}" {{ old('commodity') == $comm ? 'selected' : '' }}>{{ $comm }}</option>
                        @endforeach
                    </select>
                    @error('commodity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Uraian / Varietas <span class="text-danger">*</span></label>
                    <input type="text" name="variety_name" class="form-control @error('variety_name') is-invalid @enderror" 
                           value="{{ old('variety_name') }}" placeholder="Contoh: Padi Anak Daro" required>
                    @error('variety_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kelas Benih Target <span class="text-danger">*</span></label>
                    <div class="mt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="seed_class" id="seed_class_BS" value="BS" 
                                   {{ old('seed_class') == 'BS' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="seed_class_BS">BS (Benih Dasar)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="seed_class" id="seed_class_BP" value="BP" 
                                   {{ old('seed_class') == 'BP' || !old('seed_class') ? 'checked' : '' }} required>
                            <label class="form-check-label" for="seed_class_BP">BP (Benih Pokok)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="seed_class" id="seed_class_BR" value="BR" 
                                   {{ old('seed_class') == 'BR' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="seed_class_BR">BR (Benih Sebar)</label>
                        </div>
                    </div>
                    @error('seed_class')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Lokasi Kebun/Balai</label>
                <select name="planting_location_id" class="form-select @error('planting_location_id') is-invalid @enderror">
                    <option value="">-- Pilih Lokasi --</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ old('planting_location_id') == $loc->id ? 'selected' : '' }}>
                            {{ $loc->name }}
                        </option>
                    @endforeach
                </select>
                @error('planting_location_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Bagian 2: Kuantitas Target -->
            <h6 class="mb-3 mt-4 border-bottom pb-2">
                <i class="fas fa-calculator me-2"></i>Kuantitas Target
            </h6>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Target Luas Tanam (Ha) <span class="text-danger">*</span></label>
                    <input type="number" name="target_planting_area" id="target_planting_area" 
                           class="form-control @error('target_planting_area') is-invalid @enderror" 
                           value="{{ old('target_planting_area') }}" step="0.01" min="0" required>
                    @error('target_planting_area')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Contoh: 5.0</small>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Target Volume Produksi (Ton) <span class="text-danger">*</span></label>
                    <input type="number" name="target_production_volume" id="target_production_volume" 
                           class="form-control @error('target_production_volume') is-invalid @enderror" 
                           value="{{ old('target_production_volume') }}" step="0.01" min="0" required>
                    @error('target_production_volume')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Contoh: 15.0</small>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Estimasi Produktivitas (Provitas)</label>
                    <div class="input-group">
                        <input type="number" name="estimated_productivity" id="estimated_productivity" 
                               class="form-control @error('estimated_productivity') is-invalid @enderror" 
                               value="{{ old('estimated_productivity') }}" step="0.01" min="0" readonly>
                        <span class="input-group-text">Ton/Ha</span>
                    </div>
                    @error('estimated_productivity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Dihitung otomatis</small>
                </div>
            </div>

            <!-- Bagian 3: Keterangan -->
            <h6 class="mb-3 mt-4 border-bottom pb-2">
                <i class="fas fa-sticky-note me-2"></i>Keterangan
            </h6>

            <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" 
                          placeholder="Contoh: Sesuai DPA No....">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('planning.production-target.index', ['year' => $year]) }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const targetArea = document.getElementById('target_planting_area');
    const targetVolume = document.getElementById('target_production_volume');
    const estimatedProductivity = document.getElementById('estimated_productivity');

    function calculateProductivity() {
        const area = parseFloat(targetArea.value) || 0;
        const volume = parseFloat(targetVolume.value) || 0;
        
        if (area > 0 && volume > 0) {
            estimatedProductivity.value = (volume / area).toFixed(2);
        } else {
            estimatedProductivity.value = '';
        }
    }

    targetArea.addEventListener('input', calculateProductivity);
    targetVolume.addEventListener('input', calculateProductivity);
});
</script>
@endpush
@endsection


