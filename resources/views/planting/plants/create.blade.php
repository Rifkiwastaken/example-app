@extends('layouts.app')

@section('title', 'Tambah Tanaman - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Tanaman Baru</h4>
    <a href="{{ route('plants.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
</div>

<!-- Progress Indicator -->
<div class="mb-4">
    <div class="d-flex align-items-center">
        <div class="d-flex align-items-center">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 14px; font-weight: bold;">1</div>
            <span class="ms-2 fw-bold text-primary">Tipe Tanaman</span>
        </div>
        <div class="mx-3" style="width: 30px; height: 2px; background-color: #e9ecef;"></div>
        <div class="d-flex align-items-center">
            <div class="border border-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 14px; font-weight: bold; color: #6c757d;">2</div>
            <span class="ms-2 text-muted">Detail Penanaman</span>
        </div>
        <div class="mx-3" style="width: 30px; height: 2px; background-color: #e9ecef;"></div>
        <div class="d-flex align-items-center">
            <div class="border border-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 14px; font-weight: bold; color: #6c757d;">✓</div>
            <span class="ms-2 text-muted">Selesai</span>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('plants.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Tipe <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2">
                            <div class="flex-grow-1 position-relative">
                                <input type="text" class="form-control @error('plant_type_id') is-invalid @enderror" 
                                       id="plantTypeSearch" placeholder="Q cari Tipe" 
                                       value="{{ old('plant_type_name') }}" autocomplete="off" readonly>
                                <i class="fas fa-search position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); color: #6c757d; pointer-events: none;"></i>
                                <select name="plant_type_id" id="plantTypeSelect" class="form-select @error('plant_type_id') is-invalid @enderror" required>
                                    <option value="">Pilih tipe</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" data-name="{{ $type->name }}" data-category="{{ $type->category }}" 
                                                {{ old('plant_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->category ? $type->category.' - ' : '' }}{{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <a href="{{ route('plant-types.create') }}" class="btn btn-success">Tambahkan tipe tanaman</a>
                        </div>
                        @error('plant_type_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Varietas</label>
                        <input name="variety" class="form-control @error('variety') is-invalid @enderror" 
                               value="{{ old('variety') }}" placeholder="Masukkan varietas">
                        @error('variety')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Lokasi Penanaman</label>
                        <select name="planting_location_id" class="form-select @error('planting_location_id') is-invalid @enderror">
                            <option value="">Pilih lokasi</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ old('planting_location_id') == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('planting_location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a class="btn btn-secondary" href="{{ route('plants.index') }}">Cancel</a>
                <button class="btn btn-outline-secondary" type="button">Simpan dan Tambahkan</button>
                <button class="btn btn-success" type="submit">Next</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('plantTypeSearch');
    const selectElement = document.getElementById('plantTypeSelect');
    const options = selectElement.querySelectorAll('option');
    
    // Initially hide select visually but keep it in DOM for form submission
    selectElement.style.position = 'absolute';
    selectElement.style.top = '0';
    selectElement.style.left = '0';
    selectElement.style.width = '100%';
    selectElement.style.zIndex = '10';
    selectElement.style.opacity = '0';
    selectElement.style.pointerEvents = 'none';
    
    // Show/hide search input and select
    searchInput.addEventListener('click', function() {
        selectElement.style.opacity = '1';
        selectElement.style.pointerEvents = 'auto';
        searchInput.style.opacity = '0';
        searchInput.style.pointerEvents = 'none';
        selectElement.focus();
    });
    
    selectElement.addEventListener('blur', function() {
        setTimeout(() => {
            selectElement.style.opacity = '0';
            selectElement.style.pointerEvents = 'none';
            searchInput.style.opacity = '1';
            searchInput.style.pointerEvents = 'auto';
        }, 200);
    });
    
    selectElement.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            searchInput.value = selectedOption.textContent;
            selectElement.style.opacity = '0';
            selectElement.style.pointerEvents = 'none';
            searchInput.style.opacity = '1';
            searchInput.style.pointerEvents = 'auto';
        }
    });
    
    // Initialize search input value
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    if (selectedOption.value) {
        searchInput.value = selectedOption.textContent;
    } else {
        searchInput.value = '';
    }
    
    // Form validation before submit
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        // Ensure plant_type_id is set
        if (!selectElement.value) {
            e.preventDefault();
            alert('Harap pilih tipe tanaman terlebih dahulu.');
            selectElement.style.opacity = '1';
            selectElement.style.pointerEvents = 'auto';
            selectElement.focus();
            return false;
        }
    });
});
</script>
@endpush
@endsection


