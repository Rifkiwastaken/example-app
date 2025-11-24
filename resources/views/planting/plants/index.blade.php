@extends('layouts.app')

@section('title', 'Tanaman - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="mb-0">Daftar Tanaman</h4>
    <div class="btn-group flex-wrap d-none d-md-flex">
        <a href="{{ route('plants.create') }}" class="btn btn-success"><i class="fas fa-plus me-2"></i>Tanaman Baru</a>
        <a href="{{ route('plantings.create') }}" class="btn btn-primary"><i class="fas fa-seedling me-2"></i>Tambahkan Penanaman</a>
        <a href="{{ route('plant-types.index') }}" class="btn btn-outline-primary"><i class="fas fa-list me-2"></i>Kelola Tipe</a>
    </div>
    <!-- Mobile buttons -->
    <div class="d-flex d-md-none w-100 gap-2">
        <a href="{{ route('plants.create') }}" class="btn btn-success btn-sm flex-fill">
            <i class="fas fa-plus me-1"></i>Tanaman Baru
        </a>
        <div class="dropdown">
            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('plantings.create') }}">
                    <i class="fas fa-seedling me-2"></i>Tambahkan Penanaman
                </a></li>
                <li><a class="dropdown-item" href="{{ route('plant-types.index') }}">
                    <i class="fas fa-list me-2"></i>Kelola Tipe
                </a></li>
            </ul>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form class="row g-3" method="GET" action="{{ route('plants.index') }}">
            <div class="col-md-4">
                <label class="form-label">Tipe Tanaman</label>
                <select name="plant_type_id" class="form-select">
                    <option value="">Semua Tipe</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" {{ request('plant_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->category ? $type->category.' - ' : '' }}{{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Lokasi Penanaman</label>
                <select name="planting_location_id" class="form-select">
                    <option value="">Semua Lokasi</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('planting_location_id') == $loc->id ? 'selected' : '' }}>
                            {{ $loc->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button class="btn btn-primary flex-fill" type="submit"><i class="fas fa-filter me-1"></i>Filter</button>
                <a class="btn btn-secondary flex-fill" href="{{ route('plants.index') }}"><i class="fas fa-times me-1"></i>Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <!-- Desktop Table View -->
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Nama Tanaman</th>
                    <th>Tipe</th>
                    <th>Lokasi</th>
                    <th width="140">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($plants as $plant)
                    <tr>
                        <td><a href="{{ route('plants.show', $plant) }}" class="text-decoration-none">{{ $plant->name }}</a><br><small class="text-muted">Varietas: {{ $plant->variety ?: '-' }}</small></td>
                        <td>{{ $plant->type?->name ?: '-' }}</td>
                        <td>{{ $plant->plantingLocation?->name ?: '-' }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('plants.show', $plant) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('plants.edit', $plant) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('plants.destroy', $plant) }}" method="POST" class="d-inline" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus tanaman ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada data tanaman.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="d-block d-md-none">
            @forelse($plants as $plant)
                <div class="card mb-3 shadow-sm border-start border-primary border-3">
                    <div class="card-body">
                        <!-- Header with Title -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="flex-grow-1">
                                <h6 class="card-title mb-1">
                                    <a href="{{ route('plants.show', $plant) }}" class="text-decoration-none text-primary fw-bold">
                                        {{ $plant->name }}
                                    </a>
                                </h6>
                                @if($plant->variety)
                                    <small class="text-muted">
                                        <i class="fas fa-seed me-1"></i>Varietas: {{ $plant->variety }}
                                    </small>
                                @endif
                            </div>
                        </div>

                        <!-- Plant Details -->
                        <div class="mb-3">
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted d-block mb-1">
                                        <i class="fas fa-tag me-1"></i>Tipe
                                    </small>
                                    <div class="fw-medium">{{ $plant->type?->name ?: '-' }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block mb-1">
                                        <i class="fas fa-map-marker-alt me-1"></i>Lokasi
                                    </small>
                                    <div class="fw-medium">{{ $plant->plantingLocation?->name ?: '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 d-flex justify-content-between mt-3">
                            <a href="{{ route('plants.show', $plant) }}" 
                               class="btn btn-info btn-sm flex-fill">
                                <i class="fas fa-eye me-1"></i>Detail
                            </a>
                            <a href="{{ route('plants.edit', $plant) }}" 
                               class="btn btn-warning btn-sm flex-fill">
                                <i class="fas fa-edit me-1"></i>Edit
                            </a>
                            <form action="{{ route('plants.destroy', $plant) }}" 
                                  method="POST" 
                                  class="flex-fill"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus tanaman ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm w-100">
                                    <i class="fas fa-trash me-1"></i>Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-seedling fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">Belum ada data tanaman.</p>
                </div>
            @endforelse
        </div>

        @if($plants->hasPages())
            <div class="d-flex justify-content-center mt-3">{{ $plants->links() }}</div>
        @endif
    </div>
</div>

<style>
/* Mobile-specific styles */
@media (max-width: 767.98px) {
    /* Card animations */
    .card.shadow-sm {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .card.shadow-sm:active {
        transform: scale(0.98);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15) !important;
    }
    
    .border-primary {
        border-width: 4px !important;
    }
    
    /* Improved button sizing for mobile */
    .btn-sm {
        padding: 0.625rem 0.875rem;
        font-size: 0.875rem;
        border-radius: 8px;
    }
    
    /* Improve tap targets on mobile (minimum 44x44px) */
    .btn, a.btn {
        min-height: 44px;
        min-width: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        touch-action: manipulation;
        -webkit-tap-highlight-color: rgba(0, 0, 0, 0.1);
    }
    
    /* Better spacing for mobile cards */
    .card-body {
        padding: 1.25rem;
    }
    
    /* Card title link improvements */
    .card-title a {
        font-size: 1.1rem;
        line-height: 1.4;
    }
    
    /* Better spacing between cards */
    .mb-3 {
        margin-bottom: 1rem !important;
    }
    
    /* Form improvements for mobile */
    .form-select, .form-control {
        font-size: 16px; /* Prevents zoom on iOS */
        padding: 0.75rem;
        border-radius: 8px;
    }
    
    /* Filter button improvements */
    .gap-2 > * {
        flex: 1;
    }
    
    /* Header improvements */
    h4 {
        font-size: 1.5rem;
    }
    
    /* Empty state improvements */
    .fa-seedling {
        opacity: 0.3;
    }
    
    /* Action button container */
    .d-grid.gap-2 {
        gap: 0.75rem !important;
    }
    
    /* Badge improvements */
    .badge {
        font-size: 0.75rem;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
    }
    
    /* Icon improvements */
    .fas, .far {
        font-size: 0.875rem;
    }
}
</style>
@endsection


