@extends('layouts.app')

@section('title', 'Template Tugas - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Buat Tugas dari Template</h4>
    <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('tasks.store-from-template') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="template_id" class="form-label">Pilih Template Tugas <span class="text-danger">*</span></label>
                        <select class="form-select @error('template_id') is-invalid @enderror" id="template_id" name="template_id" required>
                            <option value="">Pilih Template</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                    {{ $template->name }} ({{ $template->association_label }})
                                </option>
                            @endforeach
                        </select>
                        @error('template_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="series_id" class="form-label">Pilih Series Tugas (Opsional)</label>
                        <select class="form-select @error('series_id') is-invalid @enderror" id="series_id" name="series_id">
                            <option value="">Pilih Series</option>
                            @foreach($series as $serie)
                                <option value="{{ $serie->id }}" {{ old('series_id') == $serie->id ? 'selected' : '' }}>
                                    {{ $serie->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('series_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Jika memilih series, semua tugas dalam series akan dibuat dengan tenggat waktu yang disesuaikan
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Tanggal Mulai Tugas <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                               id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required>
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Tanggal ini akan digunakan untuk menghitung tenggat waktu tugas dalam series
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">Ditugaskan Untuk</label>
                        <select class="form-select @error('assigned_to') is-invalid @enderror" id="assigned_to" name="assigned_to">
                            <option value="">Pilih User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->role_label }})
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle me-2"></i>Informasi Template Tugas</h6>
                <ul class="mb-0">
                    <li>Template tugas akan membuat tugas berdasarkan konfigurasi yang telah ditentukan sebelumnya</li>
                    <li>Jika memilih series, semua tugas dalam series akan dibuat dengan tenggat waktu yang disesuaikan berdasarkan tanggal mulai</li>
                    <li>Tanggal tenggat akan dihitung otomatis berdasarkan batas hari yang telah ditetapkan di template series</li>
                    <li>Semua tugas yang dibuat akan memiliki asosiasi yang sama dengan template yang dipilih</li>
                </ul>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-tasks me-2"></i>Tugaskan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Template Management Section -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Manajemen Template</h5>
                <a href="{{ route('task-templates.index') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-cog me-1"></i>Kelola
                </a>
            </div>
            <div class="card-body">
                <p class="text-muted">Kelola template tugas untuk membuat tugas yang konsisten dan efisien.</p>
                <div class="d-flex gap-2">
                    <a href="{{ route('task-templates.create') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus me-1"></i>Tambah Template
                    </a>
                    <a href="{{ route('task-templates.index') }}" class="btn btn-info btn-sm">
                        <i class="fas fa-list me-1"></i>Lihat Semua
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Manajemen Series</h5>
                <a href="{{ route('task-series.index') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-cog me-1"></i>Kelola
                </a>
            </div>
            <div class="card-body">
                <p class="text-muted">Kelola series tugas untuk membuat rangkaian tugas yang terorganisir.</p>
                <div class="d-flex gap-2">
                    <a href="{{ route('task-series.create') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus me-1"></i>Tambah Series
                    </a>
                    <a href="{{ route('task-series.index') }}" class="btn btn-info btn-sm">
                        <i class="fas fa-list me-1"></i>Lihat Semua
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection















