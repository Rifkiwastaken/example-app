@extends('layouts.app')

@section('title', 'Foto Tanaman - SIBIT')

@section('content')
<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('plants.index') }}">My Crops</a></li>
        <li class="breadcrumb-item"><a href="{{ route('plants.show', $plant) }}">{{ $plant->name }}</a></li>
        <li class="breadcrumb-item active">Foto</li>
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
        <a href="{{ route('plants.photos.create', $plant) }}" class="btn btn-success">Upload Foto</a>
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('plants.edit', $plant) }}">Edit Plant</a></li>
            </ul>
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
        <a class="nav-link active" href="{{ route('plants.photos.index', $plant) }}">
            <i class="fas fa-camera me-1"></i>Foto
        </a>
    </li>
</ul>

<div class="tab-content p-3 bg-white border border-top-0 rounded-bottom">
    <div class="tab-pane fade show active">
        <!-- Photos Grid -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Foto Tanaman</h5>
            </div>
            <div class="card-body">
                @if($photos->count() > 0)
                    <div class="row">
                        @foreach($photos as $photo)
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="position-relative">
                                        <img src="{{ Storage::url($photo->file_path) }}" class="card-img-top" 
                                             style="height: 200px; object-fit: cover;" alt="{{ $photo->file_name }}">
                                        <div class="position-absolute top-0 end-0 p-2">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="{{ Storage::url($photo->file_path) }}" target="_blank">
                                                        <i class="fas fa-eye me-2"></i>Lihat
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="{{ route('plants.photos.edit', [$plant, $photo]) }}">
                                                        <i class="fas fa-edit me-2"></i>Edit
                                                    </a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="deletePhoto({{ $photo->id }})">
                                                        <i class="fas fa-trash me-2"></i>Delete
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $photo->file_name }}</h6>
                                        <p class="card-text small text-muted">
                                            {{ $photo->taken_at ? $photo->taken_at->format('d M Y H:i') : 'Tidak ada tanggal' }}
                                        </p>
                                        @if($photo->description)
                                            <p class="card-text small">{{ Str::limit($photo->description, 100) }}</p>
                                        @endif
                                        <small class="text-muted">{{ number_format($photo->file_size / 1024, 1) }} KB</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($photos->hasPages())
                        <div class="d-flex justify-content-center">{{ $photos->links() }}</div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-camera fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Belum ada foto</h5>
                        <p class="text-muted">Mulai dengan mengupload foto untuk tanaman ini.</p>
                        <a href="{{ route('plants.photos.create', $plant) }}" class="btn btn-success">
                            <i class="fas fa-upload me-2"></i>Upload Foto
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>

@push('scripts')
<script>
function deletePhoto(photoId) {
    if (confirm('Apakah Anda yakin ingin menghapus foto ini?')) {
        // Implement delete functionality
        console.log('Delete photo:', photoId);
    }
}
</script>
@endpush
@endsection





