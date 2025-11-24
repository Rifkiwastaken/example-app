@extends('layouts.app')

@section('title', 'Detail Tanaman - SIBIT')

@section('content')
<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('plants.index') }}">My Crops</a></li>
        <li class="breadcrumb-item active">{{ $plant->name }}</li>
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
            <small class="text-muted">{{ $plant->type?->name ?: 'Tidak ada tipe' }}</small>
        </div>
    </div>
    <div class="btn-group">
        <a href="{{ route('plantings.create', ['plant_id' => $plant->id]) }}" class="btn btn-success">Add Planting</a>
        <a href="{{ route('harvests.create', ['plant_id' => $plant->id]) }}" class="btn btn-primary">Harvest</a>
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('plants.edit', $plant) }}">Edit Plant</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#" onclick="deletePlant()">Delete Plant</a></li>
            </ul>
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
            <div class="card-header">
                <h5 class="mb-0">Type & Variety</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Crop Type</label>
                            <input type="text" class="form-control" value="{{ $plant->type?->name ?: '-' }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Variety / Strain</label>
                            <input type="text" class="form-control" value="{{ $plant->variety ?: '-' }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Botanical Name</label>
                            <input type="text" class="form-control" value="{{ $plant->type?->name ?: '-' }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" value="{{ $plant->plantingLocation?->name ?: '-' }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Icon</label>
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 18px; font-weight: bold;">
                                {{ substr($plant->name, 0, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Planting Details Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Planting Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Days To Emerge</label>
                            <div class="input-group">
                                <input type="number" class="form-control" value="{{ $plant->plantings->first()?->days_to_emerge ?: 0 }}" readonly>
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Plant Spacing</label>
                            <div class="input-group">
                                <input type="number" class="form-control" value="{{ $plant->plantings->first()?->spacing_between_plants ?: 0 }}" readonly>
                                <span class="input-group-text">cm</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Row Spacing</label>
                            <div class="input-group">
                                <input type="number" class="form-control" value="{{ $plant->plantings->first()?->spacing_between_rows ?: 0 }}" readonly>
                                <span class="input-group-text">cm</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Planting Depth</label>
                            <div class="input-group">
                                <input type="number" class="form-control" value="{{ $plant->plantings->first()?->sowing_depth ?: 0 }}" readonly>
                                <span class="input-group-text">cm</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Average Height</label>
                            <div class="input-group">
                                <input type="number" class="form-control" value="{{ $plant->plantings->first()?->avg_height ?: 0 }}" readonly>
                                <span class="input-group-text">cm</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Start Method</label>
                            <input type="text" class="form-control" value="{{ $plant->plantings->first()?->start_method ?: '-' }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estimated Germination Rate</label>
                            <div class="input-group">
                                <input type="number" class="form-control" value="{{ $plant->plantings->first()?->germination_stage ?: 0 }}" readonly>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Seeds Per Hole/Cell</label>
                            <input type="number" class="form-control" value="{{ $plant->plantings->first()?->seeds_per_hole ?: 1 }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Light Profile</label>
                            <input type="text" class="form-control" value="{{ $plant->plantings->first()?->light_profile ?: '-' }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Soil Conditions</label>
                            <input type="text" class="form-control" value="{{ $plant->plantings->first()?->soil_condition ?: '-' }}" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label">Planting Details</label>
                            <textarea class="form-control" rows="3" readonly>{{ $plant->plantings->first()?->planting_detail ?: '' }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pruning Details</label>
                            <textarea class="form-control" rows="3" readonly>{{ $plant->plantings->first()?->pruning_detail ?: '' }}</textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" {{ $plant->plantings->first()?->perennial ? 'checked' : '' }} disabled>
                            <label class="form-check-label">Plant is Perennial</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" disabled>
                            <label class="form-check-label">Automatically create tasks for new plantings</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Harvest Details Section -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Harvest Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Days To Flower</label>
                            <div class="input-group">
                                <input type="number" class="form-control" value="{{ $plant->plantings->first()?->days_to_flower ?: 0 }}" readonly>
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Days To Maturity</label>
                            <div class="input-group">
                                <input type="number" class="form-control" value="{{ $plant->plantings->first()?->days_to_harvest ?: 0 }}" readonly>
                                <span class="input-group-text">Days</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harvest Window</label>
                            <div class="input-group">
                                <input type="number" class="form-control" value="{{ $plant->plantings->first()?->harvest_window_days ?: 0 }}" readonly>
                                <span class="input-group-text">Days</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estimated Loss Rate</label>
                            <div class="input-group">
                                <input type="number" class="form-control" value="{{ $plant->plantings->first()?->expected_loss_rate ?: 0 }}" readonly>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Harvest Units</label>
                            <input type="text" class="form-control" value="{{ $plant->plantings->first()?->harvest_unit ?: '-' }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estimated Revenue</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control" value="0.00" readonly>
                                <span class="input-group-text">per harvest unit</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Expected Yield Per 50.40m</label>
                            <input type="text" class="form-control" value="" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Expected Yield Per Hectare</label>
                            <input type="text" class="form-control" value="{{ $plant->plantings->first()?->expected_yield_per_hectare ?: '' }}" readonly>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <a href="#" class="btn btn-link">Customize fields</a>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a class="btn btn-secondary" href="{{ route('plants.index') }}">Cancel</a>
            <a class="btn btn-success" href="{{ route('plants.edit', $plant) }}">Save</a>
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


