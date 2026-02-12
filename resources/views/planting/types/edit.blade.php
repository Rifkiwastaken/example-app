@extends('layouts.app')

@section('title', 'Edit Tipe Tanaman - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Tipe Tanaman</h4>
    <a href="{{ route('plant-types.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('plant-types.update', $type) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Nama Tipe</label>
                <input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $type->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Kategori (opsional)</label>
                <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" onchange="toggleCategoryCustom()">
                    <option value="">-- Pilih Kategori --</option>
                    <option value="pangan" {{ old('category', $type->category) == 'pangan' ? 'selected' : '' }}>Pangan</option>
                    <option value="hortikultura" {{ old('category', $type->category) == 'hortikultura' ? 'selected' : '' }}>Hortikultura</option>
                    <option value="sayur" {{ old('category', $type->category) == 'sayur' ? 'selected' : '' }}>Sayur</option>
                    <option value="buah" {{ old('category', $type->category) == 'buah' ? 'selected' : '' }}>Buah</option>
                    <option value="hias" {{ old('category', $type->category) == 'hias' ? 'selected' : '' }}>Hias</option>
                    <option value="lainnya" {{ old('category', $type->category) && !in_array(old('category', $type->category), ['pangan', 'hortikultura', 'sayur', 'buah', 'hias']) ? 'selected' : '' }}>Lainnya</option>
                </select>
                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div id="category_custom_container" class="mt-2" style="display: {{ old('category', $type->category) && !in_array(old('category', $type->category), ['pangan', 'hortikultura', 'sayur', 'buah', 'hias']) ? 'block' : 'none' }};">
                    <input type="text" name="category_custom" id="category_custom" 
                           class="form-control @error('category_custom') is-invalid @enderror" 
                           value="{{ old('category', $type->category) && !in_array(old('category', $type->category), ['pangan', 'hortikultura', 'sayur', 'buah', 'hias']) ? old('category', $type->category) : old('category_custom') }}" 
                           placeholder="Masukkan kategori lainnya">
                    @error('category_custom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a class="btn btn-secondary" href="{{ route('plant-types.index') }}">Batal</a>
                <button class="btn btn-success" type="submit"><i class="fas fa-save me-2"></i>Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleCategoryCustom() {
    const category = document.getElementById('category');
    const customContainer = document.getElementById('category_custom_container');
    const customInput = document.getElementById('category_custom');
    
    if (category.value === 'lainnya') {
        customContainer.style.display = 'block';
        customInput.required = false; // Optional field
    } else {
        customContainer.style.display = 'none';
        customInput.required = false;
        customInput.value = '';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleCategoryCustom();
});
</script>
@endpush
@endsection


















