@extends('layouts.app')

@section('title', 'Detail Tanaman - SIBESTI')

@section('content')
<!-- Plant Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="{{ route('plants.index') }}" class="btn btn-secondary me-3">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 18px; font-weight: bold;">
            {{ substr($plant->name, 0, 2) }}
        </div>
        <div>
            <h4 class="mb-0">{{ $plant->name }}</h4>
            <small class="text-muted">{{ $plant->type?->name ?: 'Tidak ada tipe' }}</small>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('plants.show', $plant) }}">
            <i class="fas fa-info-circle me-1"></i>Detail
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('plants.current-plantings', $plant) }}">
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
        <!-- Plant Details Section -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Data Tanaman</h5>
                <a href="{{ route('plants.edit', $plant) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit me-1"></i>Edit
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Tipe</label>
                            <input type="text" class="form-control" value="{{ $plant->type ? ($plant->type->category ? $plant->type->category.' - ' : '').$plant->type->name : '-' }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Varietas</label>
                            <input type="text" class="form-control" value="{{ $plant->variety ?: '-' }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Lokasi Penanaman</label>
                            <div>
                                @if($plant->plantings->count() > 0)
                                    @foreach($plant->plantings->pluck('location.name')->unique() as $locationName)
                                        <span class="badge bg-primary me-1">{{ $locationName }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Tanaman Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Detail Tanaman</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Hari Sampai Muncul</label>
                            <div class="input-group">
                                <input type="number" class="form-control" value="{{ $plant->plantings->first()?->days_to_emerge ?: 0 }}" readonly>
                                <span class="input-group-text">hari</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jarak Tanaman</label>
                            <div class="input-group">
                                <input type="number" class="form-control" value="{{ $plant->plantings->first()?->spacing_between_plants ?: 0 }}" readonly>
                                <span class="input-group-text">cm</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jarak Baris</label>
                            <div class="input-group">
                                <input type="number" class="form-control" value="{{ $plant->plantings->first()?->spacing_between_rows ?: 0 }}" readonly>
                                <span class="input-group-text">cm</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kedalaman Tanam</label>
                            <div class="input-group">
                                <input type="number" class="form-control" value="{{ $plant->plantings->first()?->sowing_depth ?: 0 }}" readonly>
                                <span class="input-group-text">cm</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tinggi Rata-rata</label>
                            <div class="input-group">
                                <input type="number" class="form-control" value="{{ $plant->plantings->first()?->avg_height ?: 0 }}" readonly>
                                <span class="input-group-text">cm</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Metode Mulai</label>
                            <input type="text" class="form-control" value="{{ $plant->plantings->first()?->start_method ?: '-' }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Perkiraan Tingkat Perkecambahan</label>
                            <input type="text" class="form-control" value="{{ $plant->plantings->first()?->germination_stage ?: '-' }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Benih per Lubang/Sel</label>
                            <input type="number" class="form-control" value="{{ $plant->plantings->first()?->seeds_per_hole ?: 1 }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Profil Cahaya</label>
                            <input type="text" class="form-control" value="{{ $plant->plantings->first()?->light_profile ?: '-' }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kondisi Tanah</label>
                            <input type="text" class="form-control" value="{{ $plant->plantings->first()?->soil_condition ?: '-' }}" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label">Detail Tanaman</label>
                            <textarea class="form-control" rows="3" readonly>{{ $plant->plantings->first()?->planting_detail ?: '' }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Detail Pemangkasan</label>
                            <textarea class="form-control" rows="3" readonly>{{ $plant->plantings->first()?->pruning_detail ?: '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Harvest Details Section -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Detail Panen</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Hari Sampai Berbunga</label>
                            <div class="input-group">
                                <input type="number" class="form-control" value="{{ $plant->plantings->first()?->days_to_flower ?: 0 }}" readonly>
                                <span class="input-group-text">hari</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hari Sampai Panen</label>
                            <div class="input-group">
                                <input type="number" class="form-control" value="{{ $plant->plantings->first()?->days_to_harvest ?: 0 }}" readonly>
                                <span class="input-group-text">hari</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jendela Panen</label>
                            <div class="input-group">
                                <input type="number" class="form-control" value="{{ $plant->plantings->first()?->harvest_window_days ?: 0 }}" readonly>
                                <span class="input-group-text">hari</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Perkiraan Tingkat Kehilangan</label>
                            <div class="input-group">
                                <input type="number" class="form-control" value="{{ $plant->plantings->first()?->expected_loss_rate ?: 0 }}" readonly>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Satuan Panen</label>
                            <input type="text" class="form-control" value="{{ $plant->plantings->first()?->harvest_unit ?: '-' }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hasil yang Diharapkan per Periode Penanaman</label>
                            <input type="text" class="form-control" value="{{ $plant->plantings->first()?->expected_yield_per_hectare ?: '' }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah yang Ditanam</label>
                            <input type="text" class="form-control" value="{{ $plant->plantings->first()?->quantity_planted ?: '' }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</div>

@push('scripts')
<script>
function deletePlant() {
    if (confirm('Apakah Anda yakin ingin menghapus tanaman ini?')) {
        // Implement delete functionality
        console.log('Delete plant');
    }
}
</script>
@endpush
@endsection



