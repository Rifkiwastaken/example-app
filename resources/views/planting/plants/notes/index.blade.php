@extends('layouts.app')

@section('title', 'Catatan Tanaman - SIBESTI')

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
        <a class="nav-link active" href="{{ route('plants.notes.index', $plant) }}">
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
        <!-- Notes List -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Catatan Tanaman</h5>
                <a href="{{ route('plants.notes.create', $plant) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i>Catatan Baru
                </a>
            </div>
            <div class="card-body">
                @if($notes->count() > 0)
                    <div class="row">
                        @foreach($notes as $note)
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title">{{ $note->note_date->format('d M Y') }}</h6>
                                            @if(auth()->user()->role !== 'penangkar')
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="{{ route('plants.notes.edit', [$plant, $note]) }}">
                                                            <i class="fas fa-edit me-2"></i>Edit
                                                        </a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteNote({{ $note->id }})">
                                                            <i class="fas fa-trash me-2"></i>Delete
                                                        </a></li>
                                                    </ul>
                                                </div>
                                            @endif
                                        </div>
                                        <p class="card-text">{{ Str::limit($note->description, 150) }}</p>
                                        @if($note->keywords)
                                            <div class="mb-2">
                                                @foreach(explode(',', $note->keywords) as $keyword)
                                                    <span class="badge bg-light text-dark me-1">{{ trim($keyword) }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if($note->attachment_path)
                                            <div class="mt-2">
                                                <a href="{{ Storage::url($note->attachment_path) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="fas fa-paperclip me-1"></i>Attachment
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($notes->hasPages())
                        <div class="d-flex justify-content-center">{{ $notes->links() }}</div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-sticky-note fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Belum ada catatan</h5>
                        <p class="text-muted">Mulai dengan menambahkan catatan untuk tanaman ini.</p>
                        <a href="{{ route('plants.notes.create', $plant) }}" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Tambahkan Catatan
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
function deleteNote(noteId) {
    if (confirm('Apakah Anda yakin ingin menghapus catatan ini?')) {
        // Implement delete functionality
        console.log('Delete note:', noteId);
    }
}
</script>
@endpush
@endsection





