@extends('layouts.app')

@section('title', 'Penanaman Saat Ini - ' . $plant->name . ' - SIBIT')

@section('content')
<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('plants.index') }}">Tanaman Saya</a></li>
        <li class="breadcrumb-item"><a href="{{ route('plants.show', $plant) }}">{{ $plant->name }}</a></li>
        <li class="breadcrumb-item active">Penanaman saat ini</li>
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
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('plants.show', $plant) }}">
            <i class="fas fa-info-circle me-1"></i>Detail
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('plants.current-plantings', $plant) }}">
            <i class="fas fa-seedling me-1"></i>Penanaman saat ini
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('plants.harvests.index', $plant) }}">
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
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Tabel Penanaman Saat Ini (Varietas: {{ $plant->name }})</h5>
            </div>
            <div class="card-body">
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
                                <th>Lokasi Lahan</th>
                                <th>Lokasi Tanam (Bed/Baris)</th>
                                <th>Jumlah Ditanam</th>
                                <th>Tanggal Tanam</th>
                                <th>Status/Progres</th>
                                <th width="150">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($currentPlantings as $planting)
                            <tr>
                                <td>
                                    <strong>{{ $planting->location->name }}</strong>
                                    @if($planting->location->baseLocation)
                                        <br><small class="text-muted">{{ $planting->location->baseLocation->name }}</small>
                                    @endif
                                </td>
                                <td>{{ $planting->bed_label ?: '-' }}</td>
                                <td>{{ number_format($planting->quantity_planted ?? 0, 0) }} {{ $plant->type->name ?? 'unit' }}</td>
                                <td>{{ $planting->planted_at ? $planting->planted_at->format('d M Y') : '-' }}</td>
                                <td>
                                    @php
                                        $daysSince = $planting->planted_at ? $planting->planted_at->diffInDays(now()) : 0;
                                        $progress = ($planting->days_to_harvest ?? 0) > 0 
                                            ? min(100, ($daysSince / ($planting->days_to_harvest ?? 1)) * 100) 
                                            : ($daysSince > 0 ? 50 : 0);
                                        $statusColor = $progress >= 100 ? 'success' : ($progress >= 75 ? 'warning' : 'info');
                                    @endphp
                                    <div class="progress mb-1" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $statusColor }}" 
                                             role="progressbar" style="width: {{ $progress }}%">
                                            {{ number_format($progress, 0) }}%
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        @if($daysSince > 0)
                                            {{ $daysSince }} hari sejak tanam
                                        @else
                                            Belum ditanam
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="openHarvestModal({{ $planting->id }}, '{{ addslashes($planting->location->name ?? '') }}', '{{ addslashes($planting->bed_label ?? '') }}')"
                                                title="Catat Panen">
                                            <i class="fas fa-cut"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="markFailed({{ $planting->id }})"
                                                title="Catat Gagal Panen">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <a href="{{ route('planting-locations.show', $planting->location) }}" 
                                           class="btn btn-sm btn-outline-info" 
                                           title="Lihat Detail Tanam">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-seedling fa-3x mb-3"></i>
                                        <p>Tidak ada penanaman aktif saat ini.</p>
                                        <a href="{{ route('plantings.create', ['plant_id' => $plant->id]) }}" class="btn btn-success">
                                            <i class="fas fa-plus me-2"></i>Tambahkan Penanaman
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Modal: Catat Panen -->
<div class="modal fade" id="harvestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('harvests.store') }}" method="POST">
                @csrf
                <input type="hidden" name="plant_id" value="{{ $plant->id }}">
                <input type="hidden" name="planting_id" id="harvest_planting_id">
                <input type="hidden" name="planting_location_id" id="harvest_planting_location_id">
                
                <div class="modal-header">
                    <h5 class="modal-title">Formulir: Catat Panen (Otomatis)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Sumber Panen (Otomatis Terisi):</strong><br>
                        Lahan: <span id="harvest_location_name"></span><br>
                        Lokasi Tanam: <span id="harvest_bed_label"></span>
                    </div>

                    <div class="mb-3">
                        <label for="harvested_at" class="form-label">Tanggal Panen <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="harvested_at" name="harvested_at" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="batch_no" class="form-label">Nomor Batch (Panen) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="batch_no" name="batch_no" 
                               value="{{ 'PAN-' . date('Y') . '-' . str_pad(\App\Models\Harvest::whereYear('harvested_at', date('Y'))->count() + 1, 3, '0', STR_PAD_LEFT) }}" required>
                        <small class="text-muted">Dibuat otomatis</small>
                    </div>

                    <div class="mb-3">
                        <label for="quality" class="form-label">Kualitas / Ukuran (Opsional)</label>
                        <input type="text" class="form-control" id="quality" name="quality">
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Jumlah Panen <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" id="quantity" name="quantity" required>
                            <span class="input-group-text">kg</span>
                        </div>
                        <input type="hidden" name="unit" value="kg">
                        <small class="text-muted">Satuan kg otomatis</small>
                    </div>

                    <input type="hidden" name="source" id="source" value="">

                    <div class="mb-3">
                        <label for="note" class="form-label">Catatan</label>
                        <textarea class="form-control" id="note" name="note" rows="3"></textarea>
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
$plantingsDataArray = $currentPlantings->mapWithKeys(function($p) {
    return [$p->id => [
        'planting_location_id' => $p->planting_location_id, 
        'location_name' => $p->location->name ?? '', 
        'bed_label' => $p->bed_label ?? ''
    ]];
})->toArray();
@endphp
const plantingsData = @json($plantingsDataArray);

function openHarvestModal(plantingId, locationName, bedLabel) {
    document.getElementById('harvest_planting_id').value = plantingId;
    document.getElementById('harvest_location_name').textContent = locationName;
    document.getElementById('harvest_bed_label').textContent = bedLabel || '-';
    document.getElementById('source').value = locationName + (bedLabel ? ' - ' + bedLabel : '');
    
    // Set planting_location_id from the planting data
    if (plantingsData[plantingId]) {
        document.getElementById('harvest_planting_location_id').value = plantingsData[plantingId].planting_location_id;
    }
    
    new bootstrap.Modal(document.getElementById('harvestModal')).show();
}

function markFailed(plantingId) {
    if (confirm('Apakah Anda yakin ingin menandai penanaman ini sebagai gagal panen?')) {
        // Implement failed harvest functionality
        fetch(`/plantings/${plantingId}/mark-failed`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan saat menandai gagal panen.');
        });
    }
}
</script>
@endpush
@endsection
