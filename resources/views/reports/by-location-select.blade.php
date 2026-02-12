@extends('layouts.app')

@section('title', 'Laporan Per Lokasi Lahan - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Laporan Per Lokasi Lahan</h4>
        <small class="text-muted">Pilih lokasi lahan untuk melihat laporan lengkap</small>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i class="fas fa-filter me-2"></i>Pilih Lokasi Lahan
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reports.by-location') }}" id="filterForm">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Lokasi Lahan <span class="text-danger">*</span></label>
                    <select name="planting_location_id" class="form-select" required>
                        <option value="">-- Pilih Lokasi Lahan --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->planting_location_id }}" {{ request('planting_location_id') == $loc->planting_location_id ? 'selected' : '' }}>
                                {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-select">
                        <option value="">Semua Tahun</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Data Penanaman</label>
                    <select name="planting_id" id="planting_id" class="form-select" disabled>
                        <option value="">Semua Penanaman</option>
                    </select>
                    <small class="text-muted">Pilih lokasi lahan terlebih dahulu</small>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Lihat Laporan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const locationSelect = document.querySelector('select[name="planting_location_id"]');
    const plantingSelect = document.getElementById('planting_id');
    
    locationSelect.addEventListener('change', function() {
        const locationId = this.value;
        
        if (locationId) {
            // Enable planting select
            plantingSelect.disabled = false;
            plantingSelect.innerHTML = '<option value="">Memuat...</option>';
            
            // Fetch plantings for this location
            fetch(`/api/planting-locations/${locationId}/plantings`)
                .then(response => response.json())
                .then(data => {
                    plantingSelect.innerHTML = '<option value="">Semua Penanaman</option>';
                    data.forEach(planting => {
                        const option = document.createElement('option');
                        option.value = planting.id;
                        option.textContent = planting.plant_name + 
                            (planting.variety ? ' - ' + planting.variety : '') +
                            (planting.planted_at ? ' (' + planting.planted_at + ')' : '');
                        plantingSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading plantings:', error);
                    plantingSelect.innerHTML = '<option value="">Semua Penanaman</option>';
                });
        } else {
            // Disable planting select
            plantingSelect.disabled = true;
            plantingSelect.innerHTML = '<option value="">Semua Penanaman</option>';
        }
    });
    
    // Load plantings if location is already selected
    if (locationSelect.value) {
        locationSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection

