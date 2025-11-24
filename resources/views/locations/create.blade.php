@extends('layouts.app')

@section('title', 'Tambah Lokasi - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Tambah Lokasi Baru</h4>
    <a href="{{ route('locations.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('locations.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="type" class="form-label">Jenis/Asosiasi Lokasi <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="">Pilih Jenis Lokasi</option>
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="city" class="form-label">Kota/Kabupaten <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('city') is-invalid @enderror" 
                               id="city" name="city" value="{{ old('city') }}" required>
                        @error('city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="district" class="form-label">Kecamatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('district') is-invalid @enderror" 
                               id="district" name="district" value="{{ old('district') }}" required>
                        @error('district')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi Lokasi</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                          id="description" name="description" rows="3" 
                          placeholder="Masukkan deskripsi lokasi...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="google_maps_link" class="form-label">Link Google Maps</label>
                <input type="url" class="form-control @error('google_maps_link') is-invalid @enderror" 
                       id="google_maps_link" name="google_maps_link" 
                       value="{{ old('google_maps_link') }}" 
                       placeholder="https://maps.google.com/...">
                @error('google_maps_link')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">
                    Masukkan link Google Maps untuk menampilkan lokasi pada peta
                </div>
            </div>
            
            <div class="mb-4">
                <label for="photo" class="form-label">Foto Lokasi</label>
                <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                       id="photo" name="photo" accept="image/*">
                @error('photo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">
                    Format yang didukung: JPEG, PNG, JPG, GIF. Maksimal 2MB
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('locations.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Simpan Lokasi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection















