@extends('layouts.app')

@section('title', 'Laporan Produksi - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Laporan Produksi</h4>
        <small class="text-muted">Pilih filter lokasi lahan atau filter varietas</small>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">Kembali</a>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Data</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reports.by-location') }}" id="filterForm">
            <input type="hidden" name="submitted" value="1">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jenis filter</label>
                    <select name="filter_mode" id="filterMode" class="form-select">
                        <option value="location" {{ request('filter_mode', 'location') === 'location' ? 'selected' : '' }}>Berdasarkan lokasi lahan</option>
                        <option value="variety" {{ request('filter_mode') === 'variety' ? 'selected' : '' }}>Berdasarkan varietas</option>
                    </select>
                </div>
            </div>

            <div id="locationScope" class="{{ request('filter_mode', 'location') === 'location' ? '' : 'd-none' }}">
                <div id="locationRows">
                    <div class="border rounded p-3 mb-3 js-location-row">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Lokasi penanaman</label>
                                <select name="locations[0][planting_location_id]" class="form-select js-location-id">
                                    <option value="">Pilih lokasi</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->planting_location_id }}">{{ $loc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Lokasi lahan</label>
                                <select name="locations[0][field_id]" class="form-select js-field-id">
                                    <option value="">Semua lahan</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Batch tanam</label>
                                <select name="locations[0][planting_ids][]" class="form-select js-planting-ids" multiple>
                                </select>
                                <small class="text-muted">Format: batch tanam-varietas. Boleh pilih lebih dari satu.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="addLocation">Tambahkan data lokasi lahan</button>
            </div>

            <div id="varietyScope" class="{{ request('filter_mode') === 'variety' ? '' : 'd-none' }}">
                <input type="hidden" name="view_mode" id="productionViewMode" value="{{ request('filter_mode') === 'variety' ? 'specific' : 'all' }}">
                <div class="row">
                    @include('reports.partials._variety-scope-filter')
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Lihat Laporan</button>
        </form>
    </div>
</div>
@endsection

@include('reports.partials._variety-scope-scripts')
@push('scripts')
<script>
(function () {
    const mode = document.getElementById('filterMode');
    const locationScope = document.getElementById('locationScope');
    const varietyScope = document.getElementById('varietyScope');
    const viewMode = document.getElementById('productionViewMode');
    const wrap = document.getElementById('locationRows');
    function toggle() {
        const isLocation = mode.value === 'location';
        locationScope.classList.toggle('d-none', !isLocation);
        varietyScope.classList.toggle('d-none', isLocation);
        if (viewMode) viewMode.value = isLocation ? 'all' : 'specific';
        const varietySelect = varietyScope.querySelector('.js-view-mode');
        if (varietySelect) {
            varietySelect.value = 'specific';
            varietySelect.closest('.col-md-4')?.classList.add('d-none');
        }
        varietyScope.querySelector('.js-variety-scope')?.classList.remove('d-none');
    }
    mode.addEventListener('change', toggle);
    toggle();

    function loadFields(row) {
        const locationId = row.querySelector('.js-location-id').value;
        const field = row.querySelector('.js-field-id');
        const plantings = row.querySelector('.js-planting-ids');
        field.innerHTML = '<option value="">Semua lahan</option>';
        plantings.innerHTML = '';
        if (!locationId) return;
        fetch('{{ route('reports.fields') }}?planting_location_id=' + encodeURIComponent(locationId))
            .then(r => r.json())
            .then(rows => rows.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.kode_lahan;
                field.appendChild(opt);
            }));
        loadPlantings(row);
    }
    function loadPlantings(row) {
        const locationId = row.querySelector('.js-location-id').value;
        const fieldId = row.querySelector('.js-field-id').value;
        const plantings = row.querySelector('.js-planting-ids');
        plantings.innerHTML = '';
        const params = new URLSearchParams();
        if (locationId) params.set('planting_location_id', locationId);
        if (fieldId) params.set('field_id', fieldId);
        fetch('{{ route('reports.plantings') }}?' + params.toString())
            .then(r => r.json())
            .then(rows => rows.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.label;
                plantings.appendChild(opt);
            }));
    }
    function bindRow(row) {
        row.querySelector('.js-location-id')?.addEventListener('change', () => loadFields(row));
        row.querySelector('.js-field-id')?.addEventListener('change', () => loadPlantings(row));
        row.querySelector('.js-remove-location')?.addEventListener('click', () => row.remove());
    }
    wrap.querySelectorAll('.js-location-row').forEach(bindRow);
    document.getElementById('addLocation')?.addEventListener('click', function () {
        const first = wrap.querySelector('.js-location-row');
        const clone = first.cloneNode(true);
        const index = wrap.querySelectorAll('.js-location-row').length;
        clone.querySelectorAll('select').forEach((el) => {
            el.name = el.name.replace(/locations\[\d+]/, 'locations[' + index + ']');
            if (!el.multiple) el.value = '';
            else el.innerHTML = '';
        });
        if (!clone.querySelector('.js-remove-location')) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-sm btn-outline-danger js-remove-location';
            btn.textContent = 'Hapus';
            clone.appendChild(btn);
        }
        wrap.appendChild(clone);
        bindRow(clone);
    });
})();
</script>
@endpush
