@extends('layouts.app')

@section('title', 'Edit Landing Page')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">
                <i class="fas fa-edit text-primary me-2"></i>Edit Informasi Landing Page
            </h2>
            <p class="text-muted">Kelola konten dan informasi yang ditampilkan di halaman landing page publik</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('landing.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Hero Section -->
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-image me-2"></i>Hero Section</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="hero_title" class="form-label fw-bold">Judul Utama (Headline)</label>
                            <input type="text" class="form-control @error('hero_title') is-invalid @enderror" 
                                   id="hero_title" name="hero_title" 
                                   value="{{ old('hero_title', $settings['hero_title'] ?? '') }}" required>
                            @error('hero_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Judul besar yang ditampilkan di bagian hero section</small>
                        </div>

                        <div class="mb-3">
                            <label for="hero_subtitle" class="form-label fw-bold">Sub-judul</label>
                            <textarea class="form-control @error('hero_subtitle') is-invalid @enderror" 
                                      id="hero_subtitle" name="hero_subtitle" rows="3" required>{{ old('hero_subtitle', $settings['hero_subtitle'] ?? '') }}</textarea>
                            @error('hero_subtitle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Deskripsi singkat yang muncul di bawah judul utama</small>
                        </div>

                        <div class="mb-3">
                            <label for="hero_image" class="form-label fw-bold">URL Gambar Background</label>
                            <input type="url" class="form-control @error('hero_image') is-invalid @enderror" 
                                   id="hero_image" name="hero_image" 
                                   value="{{ old('hero_image', $settings['hero_image'] ?? '') }}" required>
                            @error('hero_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">URL gambar untuk background hero section (contoh: dari Unsplash)</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-address-card me-2"></i>Informasi Kontak & Alamat</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="office_address" class="form-label fw-bold">Alamat Kantor</label>
                            <textarea class="form-control @error('office_address') is-invalid @enderror" 
                                      id="office_address" name="office_address" rows="4" required>{{ old('office_address', $settings['office_address'] ?? '') }}</textarea>
                            @error('office_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Alamat lengkap kantor pusat UPTD BBI TPPH (HTML diperbolehkan untuk line break)</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="office_phone" class="form-label fw-bold">Nomor Telepon</label>
                                <input type="text" class="form-control @error('office_phone') is-invalid @enderror" 
                                       id="office_phone" name="office_phone" 
                                       value="{{ old('office_phone', $settings['office_phone'] ?? '') }}" required>
                                @error('office_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="office_whatsapp" class="form-label fw-bold">Nomor WhatsApp</label>
                                <input type="text" class="form-control @error('office_whatsapp') is-invalid @enderror" 
                                       id="office_whatsapp" name="office_whatsapp" 
                                       value="{{ old('office_whatsapp', $settings['office_whatsapp'] ?? '') }}" required>
                                @error('office_whatsapp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="office_email" class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control @error('office_email') is-invalid @enderror" 
                                       id="office_email" name="office_email" 
                                       value="{{ old('office_email', $settings['office_email'] ?? '') }}" required>
                                @error('office_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Media -->
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-share-alt me-2"></i>Sosial Media</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="facebook_url" class="form-label fw-bold">
                                    <i class="fab fa-facebook text-primary me-2"></i>Facebook URL
                                </label>
                                <input type="url" class="form-control @error('facebook_url') is-invalid @enderror" 
                                       id="facebook_url" name="facebook_url" 
                                       value="{{ old('facebook_url', $settings['facebook_url'] ?? '#') }}">
                                @error('facebook_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="instagram_url" class="form-label fw-bold">
                                    <i class="fab fa-instagram text-danger me-2"></i>Instagram URL
                                </label>
                                <input type="url" class="form-control @error('instagram_url') is-invalid @enderror" 
                                       id="instagram_url" name="instagram_url" 
                                       value="{{ old('instagram_url', $settings['instagram_url'] ?? '#') }}">
                                @error('instagram_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="youtube_url" class="form-label fw-bold">
                                    <i class="fab fa-youtube text-danger me-2"></i>YouTube URL
                                </label>
                                <input type="url" class="form-control @error('youtube_url') is-invalid @enderror" 
                                       id="youtube_url" name="youtube_url" 
                                       value="{{ old('youtube_url', $settings['youtube_url'] ?? '#') }}">
                                @error('youtube_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('landing') }}" target="_blank" class="btn btn-outline-secondary">
                                <i class="fas fa-external-link-alt me-2"></i>Lihat Landing Page
                            </a>
                            <div>
                                <a href="{{ route('dashboard') }}" class="btn btn-secondary me-2">
                                    <i class="fas fa-times me-2"></i>Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection



