@extends('layouts.app')

@section('title', 'Edit Tipe Benih - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Tipe Benih: {{ $inventoryType->name }}</h4>
    <a href="{{ route('seed-stock.show', $inventoryType) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Terjadi Kesalahan</h6>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('seed-stock.update', $inventoryType) }}" method="POST">
            @csrf
            @method('PUT')
            
            <!-- Informasi Dasar -->
            <h5 class="mb-3">Informasi Dasar</h5>
            
            <div class="mb-3">
                <label for="plant_id" class="form-label">Komoditas/Tanaman <span class="text-danger">*</span></label>
                <select class="form-select @error('plant_id') is-invalid @enderror" 
                        id="plant_id" name="plant_id" required>
                    <option value="">-- Pilih Komoditas/Tanaman --</option>
                    @foreach($plants as $plant)
                        <option value="{{ $plant->id }}" {{ old('plant_id', $inventoryType->plant_id) == $plant->id ? 'selected' : '' }}>
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
                        <option value="{{ $user->id }}" {{ old('responsible_person_id', $inventoryType->responsible_person_id) == $user->id ? 'selected' : '' }}>
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
                       id="sku" name="sku" value="{{ old('sku', $inventoryType->sku) }}" 
                       placeholder="Contoh: PDI-INP-BP-001" required>
                @error('sku')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="unit" class="form-label">Unit Inventaris <span class="text-danger">*</span></label>
                <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit" required>
                    <option value="">Pilih Unit</option>
                    <option value="kg" {{ old('unit', $inventoryType->unit) == 'kg' ? 'selected' : '' }}>kg</option>
                    <option value="ton" {{ old('unit', $inventoryType->unit) == 'ton' ? 'selected' : '' }}>ton</option>
                    <option value="kantong" {{ old('unit', $inventoryType->unit) == 'kantong' ? 'selected' : '' }}>kantong</option>
                    <option value="unit" {{ old('unit', $inventoryType->unit) == 'unit' ? 'selected' : '' }}>unit</option>
                    <option value="polybag" {{ old('unit', $inventoryType->unit) == 'polybag' ? 'selected' : '' }}>polybag</option>
                    <option value="pcs" {{ old('unit', $inventoryType->unit) == 'pcs' ? 'selected' : '' }}>pcs</option>
                </select>
                @error('unit')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="estimated_value_per_unit" class="form-label">Estimasi Nilai per Unit (Rp)</label>
                <input type="number" step="0.01" class="form-control @error('estimated_value_per_unit') is-invalid @enderror" 
                       id="estimated_value_per_unit" name="estimated_value_per_unit" value="{{ old('estimated_value_per_unit', $inventoryType->estimated_value_per_unit) }}" 
                       placeholder="15000">
                @error('estimated_value_per_unit')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="estimated_kg_per_unit" class="form-label">Estimasi kg per Unit (Opsional)</label>
                <input type="number" step="0.01" class="form-control @error('estimated_kg_per_unit') is-invalid @enderror" 
                       id="estimated_kg_per_unit" name="estimated_kg_per_unit" value="{{ old('estimated_kg_per_unit', $inventoryType->estimated_kg_per_unit) }}" 
                       placeholder="25">
                @error('estimated_kg_per_unit')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Diisi jika unit di atas adalah 'kantong'</div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="low_stock_threshold" class="form-label">Peringatan Stok Rendah (di bawah...) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('low_stock_threshold') is-invalid @enderror" 
                               id="low_stock_threshold" name="low_stock_threshold" value="{{ old('low_stock_threshold', $inventoryType->low_stock_threshold) }}" 
                               placeholder="50" required>
                        @error('low_stock_threshold')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="low_stock_unit" class="form-label">Unit <span class="text-danger">*</span></label>
                        <select class="form-select" id="low_stock_unit" name="low_stock_unit" required>
                            <option value="kg" {{ old('low_stock_unit', $inventoryType->low_stock_unit ?? 'kg') == 'kg' ? 'selected' : '' }}>kg</option>
                            <option value="ton" {{ old('low_stock_unit', $inventoryType->low_stock_unit) == 'ton' ? 'selected' : '' }}>ton</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                          id="description" name="description" rows="3" 
                          placeholder="Benih Pokok (BP) varietas Inpari Gemah...">{{ old('description', $inventoryType->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('seed-stock.show', $inventoryType) }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

