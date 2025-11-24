@extends('layouts.app')

@section('title', 'Detail Foto - SIBIT')

@section('content')
<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('plants.index') }}">My Crops</a></li>
        <li class="breadcrumb-item"><a href="{{ route('plants.show', $plant) }}">{{ $plant->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('plants.photos.index', $plant) }}">Foto</a></li>
        <li class="breadcrumb-item active">Detail Foto</li>
    </ol>
</nav>

<!-- Plant Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 18px; font-weight: bold;">
            {{ substr($plant->name, 0, 2) }}
        </div>
        <div>
            <h4 class="mb-0">Detail Foto</h4>
            <small class="text-muted">{{ $plant->name }} - {{ $photo->file_name }}</small>
        </div>
    </div>
    <div class="btn-group">
        <a href="{{ route('plants.photos.edit', [$plant, $photo]) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
        <a href="{{ route('plants.photos.index', $plant) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<!-- Photo Details -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body text-center">
                <img src="{{ Storage::url($photo->file_path) }}" class="img-fluid rounded" alt="{{ $photo->file_name }}" style="max-height: 500px;">
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informasi Foto</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Nama File:</strong><br>
                    <small class="text-muted">{{ $photo->file_name }}</small>
                </div>
                <div class="mb-3">
                    <strong>Ukuran File:</strong><br>
                    <small class="text-muted">{{ number_format($photo->file_size / 1024, 1) }} KB</small>
                </div>
                <div class="mb-3">
                    <strong>Tipe File:</strong><br>
                    <small class="text-muted">{{ $photo->mime_type }}</small>
                </div>
                @if($photo->taken_at)
                    <div class="mb-3">
                        <strong>Tanggal Diambil:</strong><br>
                        <small class="text-muted">{{ $photo->taken_at->format('d M Y H:i') }}</small>
                    </div>
                @endif
                <div class="mb-3">
                    <strong>Dibuat:</strong><br>
                    <small class="text-muted">{{ $photo->created_at->format('d M Y H:i') }}</small>
                </div>
                <div class="mb-3">
                    <strong>Diperbarui:</strong><br>
                    <small class="text-muted">{{ $photo->updated_at->format('d M Y H:i') }}</small>
                </div>
            </div>
        </div>
        
        @if($photo->description)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Deskripsi</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $photo->description }}</p>
                </div>
            </div>
        @endif
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Aksi</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ Storage::url($photo->file_path) }}" class="btn btn-primary" target="_blank">
                        <i class="fas fa-external-link-alt me-2"></i>Lihat Full Size
                    </a>
                    <a href="{{ Storage::url($photo->file_path) }}" class="btn btn-outline-primary" download>
                        <i class="fas fa-download me-2"></i>Download
                    </a>
                    <a href="{{ route('plants.photos.edit', [$plant, $photo]) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Edit Foto
                    </a>
                    <button class="btn btn-danger" onclick="deletePhoto({{ $photo->id }})">
                        <i class="fas fa-trash me-2"></i>Hapus Foto
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function deletePhoto(photoId) {
    if (confirm('Apakah Anda yakin ingin menghapus foto ini?')) {
        // Create a form to submit DELETE request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("plants.photos.destroy", [$plant, $photo]) }}';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add method override
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection













