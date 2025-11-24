@extends('layouts.app')

@section('title', 'Riwayat Panen - ' . $plant->name . ' - SIBIT')

@section('content')
<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('plants.index') }}">Tanaman Saya</a></li>
        <li class="breadcrumb-item"><a href="{{ route('plants.show', $plant) }}">{{ $plant->name }}</a></li>
        <li class="breadcrumb-item active">Riwayat Panen</li>
    </ol>
</nav>

<!-- Plant Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 18px; font-weight: bold;">
            {{ substr($plant->name, 0, 2) }}
        </div>
        <div>
            <h4 class="mb-0">{{ $plant->name }}</h4>
            <small class="text-muted">{{ $plant->variety ?: 'Tidak ada varietas' }}</small>
        </div>
    </div>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#manualHarvestModal">
        <i class="fas fa-plus me-2"></i>Catat Panen Manual
    </button>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('plants.show', $plant) }}">
            <i class="fas fa-info-circle me-1"></i>Detail
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('plants.current-plantings', $plant) }}">
            <i class="fas fa-seedling me-1"></i>Penanaman saat ini
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('plants.harvests.index', $plant) }}">
            <i class="fas fa-cut me-1"></i>Panen
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('plants.notes.index', $plant) }}">
            <i class="fas fa-sticky-note me-1"></i>Catatan
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('plants.photos.index', $plant) }}">
            <i class="fas fa-camera me-1"></i>Foto
        </a>
    </li>
</ul>

<div class="tab-content p-3 bg-white border border-top-0 rounded-bottom">
    <div class="tab-pane fade show active">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Tabel Riwayat Panen (Varietas: {{ $plant->name }})</h5>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <form method="GET" action="{{ route('plants.harvests.index', $plant) }}" class="d-flex gap-2">
                            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Semua Tahun</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="planting_location_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Semua Lokasi</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ request('planting_location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal Panen</th>
                                <th>Jumlah Panen</th>
                                <th>Dipanen Dari (Lahan)</th>
                                <th>Lokasi Tanam (Bed/Baris)</th>
                                <th>Batch</th>
                                <th>Kehilangan (Est.)</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($harvests as $harvest)
                            <tr>
                                <td>{{ $harvest->harvested_at->format('d M Y') }}</td>
                                <td><strong>{{ number_format($harvest->quantity, 2) }} {{ $harvest->unit }}</strong></td>
                                <td>{{ $harvest->location->name ?? '-' }}</td>
                                <td>{{ $harvest->planting->bed_label ?? '-' }}</td>
                                <td><code>{{ $harvest->batch_no }}</code></td>
                                <td>{{ $harvest->loss_quantity ? number_format($harvest->loss_quantity, 2) . ' ' . $harvest->unit : '-' }}</td>
                                <td>
                                    <a href="{{ route('harvests.show', $harvest) }}" class="btn btn-sm btn-outline-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-cut fa-3x mb-3"></i>
                                        <p>Belum ada riwayat panen.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($harvests->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $harvests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>

<!-- Modal: Catat Panen Manual -->
<div class="modal fade" id="manualHarvestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('harvests.store') }}" method="POST">
                @csrf
                <input type="hidden" name="plant_id" value="{{ $plant->id }}">
                
                <div class="modal-header">
                    <h5 class="modal-title">Formulir: Catat Panen Baru (Manual)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="manual_harvested_at" class="form-label">Tanggal Panen <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="manual_harvested_at" name="harvested_at" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="manual_batch_no" class="form-label">Nomor Batch (Panen) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="manual_batch_no" name="batch_no" 
                               value="{{ 'PAN-MANUAL-' . date('Y') . '-' . str_pad(\App\Models\Harvest::whereYear('harvested_at', date('Y'))->count() + 1, 3, '0', STR_PAD_LEFT) }}" required>
                        <small class="text-muted">Dibuat otomatis</small>
                    </div>

                    <div class="mb-3">
                        <label for="manual_planting_location_id" class="form-label">Pilih Lahan <span class="text-danger">*</span></label>
                        <select class="form-select" id="manual_planting_location_id" name="planting_location_id" 
                                onchange="loadBedsForLocation(this.value)" required>
                            <option value="">Pilih Lahan</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="manual_planting_id" class="form-label">Pilih Lokasi Tanam</label>
                        <select class="form-select" id="manual_planting_id" name="planting_id">
                            <option value="">Pilih Lokasi Tanam (Opsional)</option>
                        </select>
                        <small class="text-muted">Dropdown ini akan ter-filter berdasarkan Lahan yang dipilih</small>
                    </div>

                    <div class="mb-3">
                        <label for="manual_source" class="form-label">Sumber Panen (Manual Input) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="manual_source" name="source" 
                               placeholder="Contoh: Sawah Blok A1 - Baris 1-5" required>
                    </div>

                    <div class="mb-3">
                        <label for="manual_quality" class="form-label">Kualitas / Ukuran (Opsional)</label>
                        <input type="text" class="form-control" id="manual_quality" name="quality">
                    </div>

                    <div class="mb-3">
                        <label for="manual_quantity" class="form-label">Jumlah Panen <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" id="manual_quantity" name="quantity" required>
                            <span class="input-group-text">kg</span>
                        </div>
                        <input type="hidden" name="unit" value="kg">
                        <small class="text-muted">Satuan kg otomatis</small>
                    </div>

                    <div class="mb-3">
                        <label for="manual_note" class="form-label">Catatan</label>
                        <textarea class="form-control" id="manual_note" name="note" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Panen</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
@php
$plantingsByLocationArray = $plant->plantings->groupBy('planting_location_id')->map(function($plantings) {
    return $plantings->map(function($p) {
        return ['id' => $p->id, 'bed_label' => $p->bed_label ?? '-', 'planted_at' => $p->planted_at ? $p->planted_at->format('d M Y') : '-'];
    })->values();
})->toArray();
@endphp
const plantingsByLocation = @json($plantingsByLocationArray);

function loadBedsForLocation(locationId) {
    const plantingSelect = document.getElementById('manual_planting_id');
    plantingSelect.innerHTML = '<option value="">Pilih Lokasi Tanam (Opsional)</option>';
    
    if (locationId && plantingsByLocation[locationId]) {
        plantingsByLocation[locationId].forEach(planting => {
            const option = document.createElement('option');
            option.value = planting.id;
            option.textContent = planting.bed_label || 'Tanpa Bed Label';
            plantingSelect.appendChild(option);
        });
    }
}
</script>
@endpush
@endsection
