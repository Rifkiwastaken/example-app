@extends('layouts.app')

@section('title', 'Nutrisi - SIBIT')

@section('content')
<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('planting-locations.index') }}">Grow Locations</a></li>
        <li class="breadcrumb-item"><a href="{{ route('planting-locations.show', $plantingLocation) }}">{{ $plantingLocation->name }}</a></li>
        <li class="breadcrumb-item active">Nutrisi</li>
    </ol>
</nav>

<!-- Location Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 18px; font-weight: bold;">
            {{ substr($plantingLocation->name, 0, 2) }}
        </div>
        <div>
            <h4 class="mb-0">Nutrisi</h4>
            <small class="text-muted">{{ $plantingLocation->name }}</small>
        </div>
    </div>
    <div class="btn-group">
        <a href="{{ route('planting-locations.nutrients.create', $plantingLocation) }}" class="btn btn-success">Tambah Nutrisi</a>
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('planting-locations.edit', $plantingLocation) }}">Edit Location</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Sidebar Navigation -->
<div class="row">
    <div class="col-md-3">
        <div class="list-group">
            <a href="{{ route('planting-locations.show', $plantingLocation) }}" class="list-group-item list-group-item-action">
                <i class="fas fa-info-circle me-2"></i>Detail
            </a>
            <a href="{{ route('planting-locations.show', $plantingLocation) }}#plantings" class="list-group-item list-group-item-action">
                <i class="fas fa-seedling me-2"></i>Penanaman
            </a>
            <a href="{{ route('planting-locations.show', $plantingLocation) }}#history" class="list-group-item list-group-item-action">
                <i class="fas fa-history me-2"></i>Riwayat Penanaman
            </a>
            <a href="{{ route('planting-locations.treatments.index', $plantingLocation) }}" class="list-group-item list-group-item-action">
                <i class="fas fa-medkit me-2"></i>Perawatan
            </a>
            <a href="{{ route('planting-locations.nutrients.index', $plantingLocation) }}" class="list-group-item list-group-item-action active">
                <i class="fas fa-flask me-2"></i>Nutrisi
            </a>
            <a href="{{ route('planting-locations.show', $plantingLocation) }}#harvests" class="list-group-item list-group-item-action">
                <i class="fas fa-cut me-2"></i>Panen
            </a>
            <a href="{{ route('planting-locations.show', $plantingLocation) }}#tasks" class="list-group-item list-group-item-action">
                <i class="fas fa-tasks me-2"></i>Tugas
            </a>
            <a href="{{ route('planting-locations.show', $plantingLocation) }}#notes" class="list-group-item list-group-item-action">
                <i class="fas fa-sticky-note me-2"></i>Catatan
            </a>
            <a href="{{ route('planting-locations.show', $plantingLocation) }}#photos" class="list-group-item list-group-item-action">
                <i class="fas fa-camera me-2"></i>Foto
            </a>
            <a href="{{ route('planting-locations.show', $plantingLocation) }}#files" class="list-group-item list-group-item-action">
                <i class="fas fa-file me-2"></i>File
            </a>
        </div>
    </div>
    
    <div class="col-md-9">
        <!-- Nutrients List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Riwayat Nutrisi</h5>
            </div>
            <div class="card-body">
                @if($nutrients->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Produk</th>
                                    <th>Jumlah</th>
                                    <th>Metode Aplikasi</th>
                                    <th>N-P-K</th>
                                    <th>Mikro</th>
                                    <th width="100">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($nutrients as $nutrient)
                                    <tr>
                                        <td>{{ $nutrient->application_date->format('d M Y') }}</td>
                                        <td>{{ $nutrient->product_applied }}</td>
                                        <td>{{ number_format($nutrient->amount_applied, 2) }} unit</td>
                                        <td>{{ $nutrient->application_method }}</td>
                                        <td>
                                            @if($nutrient->nitrogen_n || $nutrient->phosphorus_p || $nutrient->potassium_k)
                                                <small>
                                                    N: {{ $nutrient->nitrogen_n ?: '0' }}<br>
                                                    P: {{ $nutrient->phosphorus_p ?: '0' }}<br>
                                                    K: {{ $nutrient->potassium_k ?: '0' }}
                                                </small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($nutrient->magnesium_mg || $nutrient->calcium_ca || $nutrient->sulfur_s)
                                                <small>
                                                    Mg: {{ $nutrient->magnesium_mg ?: '0' }}<br>
                                                    Ca: {{ $nutrient->calcium_ca ?: '0' }}<br>
                                                    S: {{ $nutrient->sulfur_s ?: '0' }}
                                                </small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="{{ route('planting-locations.nutrients.show', [$plantingLocation, $nutrient]) }}">
                                                        <i class="fas fa-eye me-2"></i>View
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="{{ route('planting-locations.nutrients.edit', [$plantingLocation, $nutrient]) }}">
                                                        <i class="fas fa-edit me-2"></i>Edit
                                                    </a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteNutrient({{ $nutrient->id }})">
                                                        <i class="fas fa-trash me-2"></i>Delete
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($nutrients->hasPages())
                        <div class="d-flex justify-content-center">{{ $nutrients->links() }}</div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-flask fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Belum ada data nutrisi</h5>
                        <p class="text-muted">Mulai dengan menambahkan record nutrisi untuk lokasi ini.</p>
                        <a href="{{ route('planting-locations.nutrients.create', $plantingLocation) }}" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Tambah Nutrisi
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function deleteNutrient(nutrientId) {
    if (confirm('Apakah Anda yakin ingin menghapus data nutrisi ini?')) {
        // Implement delete functionality
        console.log('Delete nutrient:', nutrientId);
    }
}
</script>
@endpush
@endsection













