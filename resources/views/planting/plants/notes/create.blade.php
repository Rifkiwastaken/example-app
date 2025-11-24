@extends('layouts.app')

@section('title', 'Catatan Baru - SIBIT')

@section('content')
<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('plants.index') }}">My Crops</a></li>
        <li class="breadcrumb-item"><a href="{{ route('plants.show', $plant) }}">{{ $plant->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('plants.notes.index', $plant) }}">Catatan</a></li>
        <li class="breadcrumb-item active">Catatan Baru</li>
    </ol>
</nav>

<!-- Plant Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 18px; font-weight: bold;">
            {{ substr($plant->name, 0, 2) }}
        </div>
        <div>
            <h4 class="mb-0">Catatan Baru</h4>
            <small class="text-muted">{{ $plant->name }}</small>
        </div>
    </div>
    <a href="{{ route('plants.notes.index', $plant) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<!-- Create Note Form -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Tambah Catatan</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('plants.notes.store', $plant) }}" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Catatan</label>
                        <input type="date" name="note_date" class="form-control @error('note_date') is-invalid @enderror" 
                               value="{{ old('note_date', date('Y-m-d')) }}" required>
                        @error('note_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Kata Kunci</label>
                        <input type="text" name="keywords" class="form-control @error('keywords') is-invalid @enderror" 
                               value="{{ old('keywords') }}" placeholder="Pisahkan dengan koma">
                        <small class="form-text text-muted">Contoh: pertumbuhan, penyakit, perawatan</small>
                        @error('keywords')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi Catatan</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                          rows="5" placeholder="Masukkan deskripsi catatan" required>{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Attachment</label>
                <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror" 
                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif">
                <small class="form-text text-muted">Format yang didukung: PDF, DOC, DOCX, JPG, PNG, GIF (Max: 10MB)</small>
                @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a class="btn btn-secondary" href="{{ route('plants.notes.index', $plant) }}">Batal</a>
                <button class="btn btn-success" type="submit">
                    <i class="fas fa-save me-2"></i>Simpan Catatan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection













