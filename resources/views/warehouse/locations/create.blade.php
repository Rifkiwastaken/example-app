@extends('layouts.app')

@section('title', 'Tambah Lokasi Gudang - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Formulir: Tambah Lokasi Gudang Baru</h4>
    <a href="{{ route('warehouse-locations.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('warehouse-locations.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                       id="name" name="name" value="{{ old('name') }}" 
                       placeholder="Contoh: Gudang Utama Sukarami" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="internal_id" class="form-label">ID Internal <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('internal_id') is-invalid @enderror" 
                       id="internal_id" name="internal_id" value="{{ old('internal_id') }}" 
                       placeholder="Contoh: GUD-SKR" required>
                @error('internal_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Lacak Kapasitas <span class="text-danger">*</span></label>
                <div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tracking_type" 
                               id="tracking_bin_separated" value="bin_separated" 
                               {{ old('tracking_type', 'bin_separated') == 'bin_separated' ? 'checked' : '' }} required>
                        <label class="form-check-label" for="tracking_bin_separated">
                            • Di dalam bin terpisah (Artinya gudang ini akan memiliki sub-lokasi/bin)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tracking_type" 
                               id="tracking_warehouse_only" value="warehouse_only" 
                               {{ old('tracking_type') == 'warehouse_only' ? 'checked' : '' }}>
                        <label class="form-check-label" for="tracking_warehouse_only">
                            ( ) Hanya di lokasi ini (Artinya stok hanya dilacak di level gudang, tanpa bin)
                        </label>
                    </div>
                </div>
                @error('tracking_type')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                          id="description" name="description" rows="3" 
                          placeholder="Contoh: Gudang utama untuk penyimpanan benih padi dan palawija">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('warehouse-locations.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

