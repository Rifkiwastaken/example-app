@extends('layouts.app')

@section('title', 'Tanaman - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="mb-0">Daftar Tanaman</h4>
    <div class="d-none d-md-flex gap-2">
        <a href="{{ route('plants.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>Tambahkan Varietas Tanaman
        </a>
        <a href="{{ route('plant-types.index') }}" class="btn btn-primary">
            <i class="fas fa-tags me-2"></i>Kategori Tanaman
        </a>
        <a href="{{ route('seed-units.index') }}" class="btn btn-outline-success">
            <i class="fas fa-balance-scale me-2"></i>Tambah Satuan Benih
        </a>
    </div>
    <div class="d-flex d-md-none w-100 gap-2 flex-wrap">
        <a href="{{ route('plants.create') }}" class="btn btn-success btn-sm flex-fill">
            <i class="fas fa-plus me-1"></i>Tambahkan Varietas Tanaman
        </a>
        <a href="{{ route('plant-types.index') }}" class="btn btn-primary btn-sm flex-fill">
            <i class="fas fa-tags me-1"></i>Kategori Tanaman
        </a>
        <a href="{{ route('seed-units.index') }}" class="btn btn-outline-success btn-sm flex-fill">
            <i class="fas fa-balance-scale me-1"></i>Tambah Satuan Benih
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form class="row g-3" method="GET" action="{{ route('plants.index') }}" id="filterForm">
            <div class="col-md-2">
                <label class="form-label">Kategori</label>
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua kategori</option>
                    @foreach($categories ?? [] as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Nama tanaman</label>
                <select name="plant_name" class="form-select" {{ request('category') ? '' : 'disabled' }} onchange="this.form.submit()">
                    <option value="">Semua nama</option>
                    @foreach($plantNameOptions ?? [] as $option)
                        <option value="{{ $option->name }}" {{ request('plant_name') == $option->name ? 'selected' : '' }}>{{ $option->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Varietas</label>
                <select name="variety_id" class="form-select" {{ request('plant_name') ? '' : 'disabled' }}>
                    <option value="">Semua varietas</option>
                    @foreach($varietyOptions ?? [] as $variety)
                        <option value="{{ $variety->getKey() }}" {{ request('variety_id') == $variety->getKey() ? 'selected' : '' }}>{{ $variety->variety ?: $variety->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Lokasi Penanaman</label>
                <select name="planting_location_id" class="form-select">
                    <option value="">Semua Lokasi</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->planting_location_id }}" {{ request('planting_location_id') == $loc->planting_location_id ? 'selected' : '' }}>
                            {{ $loc->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Cari Nama Tanaman</label>
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama tanaman..." value="{{ request('search') }}" id="searchInput">
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
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
                    <th>Kategori</th>
                    <th>Nama Tanaman</th>
                    <th>Varietas</th>
                    <th>Lokasi</th>
                    <th width="140">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @php $lastCategory = null; @endphp
                @forelse($plants as $plant)
                    @php $category = $plant->type?->category ?: 'Lainnya'; @endphp
                    @if($lastCategory !== $category)
                        <tr class="table-light"><td colspan="5" class="fw-bold">{{ $category }}</td></tr>
                        @php $lastCategory = $category; @endphp
                    @endif
                    <tr>
                        <td>{{ $category }}</td>
                        <td><a href="{{ route('plants.show', $plant) }}" class="text-decoration-none">{{ $plant->type?->name ?: $plant->name }}</a></td>
                        <td>{{ $plant->variety ?: '-' }}</td>
                        <td>
                            @foreach($plant->plantings->map(fn($p) => $p->location?->name)->filter()->unique() as $locName)
                                <span class="data-pill data-pill-muted">{{ $locName }}</span>
                            @endforeach
                            @if($plant->plantings->map(fn($p) => $p->location?->name)->filter()->unique()->isEmpty())-@endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('plants.show', $plant) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail"><i class="fas fa-eye"></i></a>
                                @if(auth()->user()->role !== 'penangkar')
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            title="Hapus"
                                            onclick="confirmDelete('{{ route('plants.destroy', $plant) }}', '{{ addslashes($plant->name) }}', 'tanaman')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data tanaman.</td></tr>
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
                                        {{ $plant->type?->name ?: $plant->name }}
                                    </a>
                                </h6>
                                @if($plant->variety)
                                    <small class="text-muted">
                                        <i class="fas fa-seedling me-1"></i>Varietas: {{ $plant->variety }}
                                    </small>
                                @endif
                            </div>
                        </div>

                        <!-- Plant Details -->
                        <div class="mb-3">
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted d-block mb-1">
                                        <i class="fas fa-tag me-1"></i>Kategori
                                    </small>
                                    <div class="fw-medium">{{ $plant->type?->category ?: '-' }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block mb-1">
                                        <i class="fas fa-map-marker-alt me-1"></i>Lokasi
                                    </small>
                                    <div class="fw-medium">{{ $plant->plantings->map(fn($p) => $p->location?->name)->filter()->unique()->join(', ') ?: '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 d-flex justify-content-between mt-3">
                            <a href="{{ route('plants.show', $plant) }}" 
                               class="btn btn-info btn-sm flex-fill">
                                <i class="fas fa-eye me-1"></i>Detail
                            </a>
                            @if(auth()->user()->role !== 'penangkar')
                                <button type="button" 
                                        class="btn btn-danger btn-sm w-100 flex-fill"
                                        onclick="confirmDelete('{{ route('plants.destroy', $plant) }}', '{{ addslashes($plant->name) }}', 'tanaman')">
                                    <i class="fas fa-trash me-1"></i>Hapus
                                </button>
                            @endif
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

@push('scripts')
<script>
// Search functionality - submit on Enter key
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('filterForm').submit();
    }
});
</script>
@endpush
@endsection


