@extends('layouts.app')

@section('title', 'Detail Catatan - SIBESTI')

@section('content')
<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('plants.index') }}">My Crops</a></li>
        <li class="breadcrumb-item"><a href="{{ route('plants.show', $plant) }}">{{ $plant->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('plants.notes.index', $plant) }}">Catatan</a></li>
        <li class="breadcrumb-item active">Detail Catatan</li>
    </ol>
</nav>

<!-- Plant Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 18px; font-weight: bold;">
            {{ substr($plant->name, 0, 2) }}
        </div>
        <div>
            <h4 class="mb-0">Detail Catatan</h4>
            <small class="text-muted">{{ $plant->name }} - {{ $note->note_date->format('d M Y') }}</small>
        </div>
    </div>
    <div class="btn-group">
        <a href="{{ route('plants.notes.edit', [$plant, $note]) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
        <a href="{{ route('plants.notes.index', $plant) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<!-- Note Details -->
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Deskripsi Catatan</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Tanggal:</strong> {{ $note->note_date->format('d M Y') }}
                </div>
                @if($note->keywords)
                    <div class="mb-3">
                        <strong>Kata Kunci:</strong>
                        <div class="mt-1">
                            @foreach(explode(',', $note->keywords) as $keyword)
                                <span class="badge bg-light text-dark me-1">{{ trim($keyword) }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="mb-3">
                    <strong>Deskripsi:</strong>
                    <div class="mt-2">
                        {!! nl2br(e($note->description)) !!}
                    </div>
                </div>
                @if($note->attachment_path)
                    <div class="mb-3">
                        <strong>Attachment:</strong>
                        <div class="mt-2">
                            <a href="{{ Storage::url($note->attachment_path) }}" class="btn btn-outline-primary" target="_blank">
                                <i class="fas fa-paperclip me-2"></i>Download Attachment
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informasi</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Dibuat:</strong><br>
                    <small class="text-muted">{{ $note->created_at->format('d M Y H:i') }}</small>
                </div>
                <div class="mb-3">
                    <strong>Diperbarui:</strong><br>
                    <small class="text-muted">{{ $note->updated_at->format('d M Y H:i') }}</small>
                </div>
                @if($note->attachment_path)
                    <div class="mb-3">
                        <strong>Ukuran File:</strong><br>
                        <small class="text-muted">{{ number_format(Storage::size($note->attachment_path) / 1024, 1) }} KB</small>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Aksi</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('plants.notes.edit', [$plant, $note]) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Edit Catatan
                    </a>
                    <button class="btn btn-danger" onclick="deleteNote({{ $note->id }})">
                        <i class="fas fa-trash me-2"></i>Hapus Catatan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function deleteNote(noteId) {
    if (confirm('Apakah Anda yakin ingin menghapus catatan ini?')) {
        // Create a form to submit DELETE request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("plants.notes.destroy", [$plant, $note]) }}';
        
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
















