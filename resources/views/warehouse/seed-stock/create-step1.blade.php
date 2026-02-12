@extends('layouts.app')

@section('title', 'Tambah Tipe Benih Baru - Langkah 1 - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Tipe Inventaris Baru (Langkah 1 dari 3)</h4>
    <a href="{{ route('seed-stock.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('seed-stock.store-step1') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="plant_id" class="form-label">Komoditas/Tanaman <span class="text-danger">*</span></label>
                <select class="form-select @error('plant_id') is-invalid @enderror" 
                        id="plant_id" name="plant_id" required>
                    <option value="">-- Pilih Komoditas/Tanaman --</option>
                    @foreach($plants as $plant)
                        <option value="{{ $plant->plant_id }}" {{ old('plant_id', request('plant_id')) == $plant->plant_id ? 'selected' : '' }}>
                            {{ $plant->name }} @if($plant->variety) - {{ $plant->variety }} @endif
                            @if($plant->type) ({{ $plant->type->name }}) @endif
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Pilih komoditas/tanaman dari data "Tanaman Saya"</small>
                @error('plant_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="responsible_person_id" class="form-label">Penanggung Jawab</label>
                <select class="form-select @error('responsible_person_id') is-invalid @enderror" 
                        id="responsible_person_id" name="responsible_person_id">
                    <option value="">-- Pilih Penanggung Jawab --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->user_id }}" {{ old('responsible_person_id', auth()->id()) == $user->user_id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Pilih user sebagai penanggung jawab</small>
                @error('responsible_person_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="sku" class="form-label">ID Internal / SKU (Stock Keeping Unit) <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('sku') is-invalid @enderror" 
                       id="sku" name="sku" value="{{ old('sku') }}" 
                       placeholder="Contoh: PDI-INP-BP-001" required>
                @error('sku')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="electronic_id" class="form-label">ID Elektronik (Opsional)</label>
                <input type="text" class="form-control @error('electronic_id') is-invalid @enderror" 
                       id="electronic_id" name="electronic_id" value="{{ old('electronic_id') }}" 
                       placeholder="(Bisa untuk barcode/RFID)">
                @error('electronic_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="unit" class="form-label">Unit Inventaris <span class="text-danger">*</span></label>
                <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit" required>
                    <option value="">Pilih Unit</option>
                    <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>kg</option>
                    <option value="ton" {{ old('unit') == 'ton' ? 'selected' : '' }}>ton</option>
                    <option value="kantong" {{ old('unit') == 'kantong' ? 'selected' : '' }}>kantong</option>
                    <option value="unit" {{ old('unit') == 'unit' ? 'selected' : '' }}>unit</option>
                    <option value="polybag" {{ old('unit') == 'polybag' ? 'selected' : '' }}>polybag</option>
                    <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>pcs</option>
                </select>
                @error('unit')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="estimated_value_per_unit" class="form-label">Estimasi Nilai per Unit (Rp)</label>
                <input type="number" step="0.01" class="form-control @error('estimated_value_per_unit') is-invalid @enderror" 
                       id="estimated_value_per_unit" name="estimated_value_per_unit" value="{{ old('estimated_value_per_unit') }}" 
                       placeholder="15000">
                @error('estimated_value_per_unit')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="track_individual_lots" 
                           name="track_individual_lots" value="1" {{ old('track_individual_lots') ? 'checked' : '' }}>
                    <label class="form-check-label" for="track_individual_lots">
                        Lacak Lot Individual
                    </label>
                </div>
                <div class="form-text">Sangat penting untuk benih agar bisa melacak masa edar/kadaluarsa per batch produksi</div>
            </div>

            <div class="mb-3">
                <label for="low_stock_threshold" class="form-label">Peringatan Stok Rendah (di bawah...)</label>
                <div class="input-group">
                    <input type="number" step="0.01" class="form-control @error('low_stock_threshold') is-invalid @enderror" 
                           id="low_stock_threshold" name="low_stock_threshold" value="{{ old('low_stock_threshold') }}" 
                           placeholder="50" min="0">
                    <span class="input-group-text" id="low_stock_unit_label">kg</span>
                </div>
                <input type="hidden" id="low_stock_unit" name="low_stock_unit" value="{{ old('low_stock_unit', 'kg') }}">
                @error('low_stock_threshold')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @error('low_stock_unit')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="low_stock_email" class="form-label">Kirim Peringatan ke Email</label>
                <input type="email" class="form-control @error('low_stock_email') is-invalid @enderror" 
                       id="low_stock_email" name="low_stock_email" value="{{ old('low_stock_email') }}" 
                       placeholder="Email user terkait">
                @error('low_stock_email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                          id="description" name="description" rows="3" 
                          placeholder="Benih Pokok (BP) varietas Inpari Gemah...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('seed-stock.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-success">
                    Lanjut (Next) <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const unitSelect = document.getElementById('unit');
    const lowStockUnitLabel = document.getElementById('low_stock_unit_label');
    const lowStockUnitInput = document.getElementById('low_stock_unit');
    
    if (!unitSelect || !lowStockUnitLabel || !lowStockUnitInput) {
        return;
    }
    
    function syncLowStockUnit() {
        const selectedUnit = unitSelect.value || 'kg';
        
        // Format label berdasarkan satuan yang dipilih
        const unitLabels = {
            'kg': 'kg',
            'ton': 'ton',
            'gram': 'gram',
            'butir': 'butir/biji',
            'pcs': 'pcs',
            'batang': 'batang',
            'kantong': 'kantong',
            'unit': 'unit',
            'polybag': 'polybag'
        };
        
        const labelText = unitLabels[selectedUnit] || selectedUnit;
        lowStockUnitLabel.textContent = labelText;
        lowStockUnitInput.value = selectedUnit;
    }
    
    // Sync on change
    unitSelect.addEventListener('change', syncLowStockUnit);
    
    // Sync on page load
    syncLowStockUnit();
});
</script>
@endpush

@endsection

