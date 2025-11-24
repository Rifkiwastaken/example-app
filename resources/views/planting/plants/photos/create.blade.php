@extends('layouts.app')

@section('title', 'Upload Foto - SIBIT')

@section('content')
<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('plants.index') }}">My Crops</a></li>
        <li class="breadcrumb-item"><a href="{{ route('plants.show', $plant) }}">{{ $plant->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('plants.photos.index', $plant) }}">Foto</a></li>
        <li class="breadcrumb-item active">Upload Foto</li>
    </ol>
</nav>

<!-- Plant Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 18px; font-weight: bold;">
            {{ substr($plant->name, 0, 2) }}
        </div>
        <div>
            <h4 class="mb-0">Upload Foto</h4>
            <small class="text-muted">{{ $plant->name }}</small>
        </div>
    </div>
    <a href="{{ route('plants.photos.index', $plant) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<!-- Upload Photos Form -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Upload Foto Tanaman</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('plants.photos.store', $plant) }}" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-3">
                <label class="form-label">Pilih Foto</label>
                <input type="file" name="photos[]" class="form-control @error('photos') is-invalid @enderror" 
                       multiple accept="image/*" required>
                <small class="form-text text-muted">Format yang didukung: JPG, JPEG, PNG, GIF (Max: 10MB per foto)</small>
                @error('photos')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                          rows="3" placeholder="Masukkan deskripsi foto (opsional)">{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Tanggal Foto Diambil</label>
                <input type="datetime-local" name="taken_at" class="form-control @error('taken_at') is-invalid @enderror" 
                       value="{{ old('taken_at', now()->format('Y-m-d\TH:i')) }}">
                <small class="form-text text-muted">Kosongkan jika tidak diketahui</small>
                @error('taken_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a class="btn btn-secondary" href="{{ route('plants.photos.index', $plant) }}">Batal</a>
                <button class="btn btn-success" type="submit">
                    <i class="fas fa-upload me-2"></i>Upload Foto
                </button>
            </div>
        </form>
    </div>
</div>
@endsection













