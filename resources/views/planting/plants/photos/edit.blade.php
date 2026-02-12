@extends('layouts.app')

@section('title', 'Edit Foto - SIBESTI')

@section('content')
<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('plants.index') }}">My Crops</a></li>
        <li class="breadcrumb-item"><a href="{{ route('plants.show', $plant) }}">{{ $plant->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('plants.photos.index', $plant) }}">Foto</a></li>
        <li class="breadcrumb-item active">Edit Foto</li>
    </ol>
</nav>

<!-- Plant Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 18px; font-weight: bold;">
            {{ substr($plant->name, 0, 2) }}
        </div>
        <div>
            <h4 class="mb-0">Edit Foto</h4>
            <small class="text-muted">{{ $plant->name }} - {{ $photo->file_name }}</small>
        </div>
    </div>
    <a href="{{ route('plants.photos.index', $plant) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<!-- Edit Photo Form -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Preview Foto</h5>
            </div>
            <div class="card-body text-center">
                <img src="{{ Storage::url($photo->file_path) }}" class="img-fluid rounded" alt="{{ $photo->file_name }}">
                <div class="mt-3">
                    <h6>{{ $photo->file_name }}</h6>
                    <small class="text-muted">{{ number_format($photo->file_size / 1024, 1) }} KB</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Informasi Foto</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('plants.photos.update', [$plant, $photo]) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                  rows="3" placeholder="Masukkan deskripsi foto">{{ old('description', $photo->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Foto Diambil</label>
                        <input type="datetime-local" name="taken_at" class="form-control @error('taken_at') is-invalid @enderror" 
                               value="{{ old('taken_at', $photo->taken_at ? $photo->taken_at->format('Y-m-d\TH:i') : '') }}">
                        <small class="form-text text-muted">Kosongkan jika tidak diketahui</small>
                        @error('taken_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a class="btn btn-secondary" href="{{ route('plants.photos.index', $plant) }}">Batal</a>
                        <button class="btn btn-success" type="submit">
                            <i class="fas fa-save me-2"></i>Update Foto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
















