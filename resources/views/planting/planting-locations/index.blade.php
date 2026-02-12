@extends('layouts.app')

@section('title', 'Lokasi Penanaman - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Lokasi Penanaman</h4>
    @if(auth()->user()->isAdmin() || auth()->user()->role === 'kepala_satuan_tugas')
        <a href="{{ route('planting-locations.create') }}" class="btn btn-success">Lokasi Tanam</a>
    @endif
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('planting-locations.index') }}" id="filterForm">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Penugasan</label>
                    <select class="form-select" name="assignment" id="assignmentFilter">
                        <option value="">Semua Penugasan</option>
                        @foreach($assignedUsers as $user)
                            <option value="{{ $user->user_id }}" {{ request('assignment') == $user->user_id ? 'selected' : '' }}>
                                {{ $user->name }}@if($user->role) - {{ $user->role_label }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Planting Locations List -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Lokasi Penanaman</h5>
            <div class="d-flex align-items-center gap-3">
                <form method="GET" action="{{ route('planting-locations.index') }}" class="d-flex">
                    <input type="hidden" name="assignment" value="{{ request('assignment') }}">
                    <div class="input-group" style="width: 300px;">
                        <input type="text" class="form-control" name="search" placeholder="Cari nama lokasi..." value="{{ request('search') }}" id="searchInput">
                        <button type="submit" class="input-group-text btn btn-outline-secondary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if($plantingLocations->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Tipe</th>
                            <th>Lokasi</th>
                            <th>Penanggung Jawab Lahan</th>
                            <th>Pekerja Lahan</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plantingLocations as $location)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 12px; font-weight: bold;">
                                            {{ strtoupper(substr($location->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('planting-locations.show', $location) }}" class="text-decoration-none fw-bold">{{ $location->name }}</a>
                                            @if($location->internal_id)
                                                <br><small class="text-muted">{{ $location->internal_id }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $location->location_type)) }}</span>
                                </td>
                                <td>
                                    @if($location->location_summary)
                                        {{ $location->location_summary }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($location->landManagerUsers->count() > 0)
                                        <div>
                                            @foreach($location->landManagerUsers as $user)
                                                <div class="mb-1">
                                                    <small>
                                                        <i class="fas fa-user me-1"></i>
                                                        <strong>{{ $user->name }}</strong>
                                                        @if($user->role)
                                                            <span class="text-muted"> - {{ $user->role_label }}</span>
                                                        @endif
                                                    </small>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($location->landWorkerUsers->count() > 0)
                                        <div>
                                            @foreach($location->landWorkerUsers as $user)
                                                <div class="mb-1">
                                                    <small>
                                                        <i class="fas fa-user me-1"></i>
                                                        <strong>{{ $user->name }}</strong>
                                                        @if($user->role)
                                                            <span class="text-muted"> - {{ $user->role_label }}</span>
                                                        @endif
                                                    </small>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('planting-locations.show', $location) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(auth()->user()->isAdmin() || auth()->user()->canManagePlantingLocation($location))
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    title="Hapus"
                                                    onclick="confirmDelete('{{ route('planting-locations.destroy', $location) }}', '{{ addslashes($location->name) }}', 'lokasi penanaman')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($plantingLocations->hasPages())
                <div class="d-flex justify-content-center">{{ $plantingLocations->links() }}</div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Belum ada lokasi penanaman</h5>
                <p class="text-muted">Mulai dengan menambahkan lokasi penanaman baru.</p>
                <a href="{{ route('planting-locations.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Tambah Lokasi Penanaman
                </a>
            </div>
        @endif
    </div>
    <div class="card-footer">
        <small class="text-muted">Menampilkan semua {{ $plantingLocations->count() }} record</small>
    </div>
</div>

@push('scripts')
<script>
// Filter functionality - auto submit on change
document.getElementById('assignmentFilter').addEventListener('change', function() {
    document.getElementById('filterForm').submit();
});

// Search functionality - submit on Enter key
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        this.closest('form').submit();
    }
});
</script>
@endpush
@endsection












