@extends('layouts.app')

@section('title', 'Edit Tipe Bibit - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Tipe Bibit: {{ $inventoryType->name }}</h4>
    <a href="{{ route('seed-stock.show', $inventoryType) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('seed-stock.update', $inventoryType) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label for="category" class="form-label">Kategori Bibit <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('category') is-invalid @enderror" 
                       id="category" name="category" value="{{ old('category', $inventoryType->category) }}" required>
                @error('category')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Nama Varietas/Komoditas <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                       id="name" name="name" value="{{ old('name', $inventoryType->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="sku" class="form-label">ID Internal / SKU <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('sku') is-invalid @enderror" 
                       id="sku" name="sku" value="{{ old('sku', $inventoryType->sku) }}" required>
                @error('sku')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="unit" class="form-label">Unit Inventaris <span class="text-danger">*</span></label>
                <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit" required>
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
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                          id="description" name="description" rows="3">{{ old('description', $inventoryType->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="d-flex justify-content-end gap-2">
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

