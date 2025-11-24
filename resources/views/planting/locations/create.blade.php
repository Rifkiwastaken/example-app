@extends('layouts.app')

@section('title', 'Tambah Lokasi Penanaman - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Tambah Lokasi Penanaman</h4>
    <a href="{{ route('planting-locations.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('planting-locations.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nama Lahan</label>
                        <input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tipe Lokasi</label>
                        <select name="location_type" class="form-select @error('location_type') is-invalid @enderror">
                            @foreach(['lapangan','greenhouse','grow_room','padang_rumput','petak_ternak','lainnya'] as $opt)
                                <option value="{{ $opt }}" {{ old('location_type')==$opt?'selected':'' }}>{{ str_replace('_',' ',$opt) }}</option>
                            @endforeach
                        </select>
                        @error('location_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Format Penanaman</label>
                        <select name="planting_format" class="form-select @error('planting_format') is-invalid @enderror">
                            @foreach(['petak','cover_crop','row','lainnya'] as $opt)
                                <option value="{{ $opt }}" {{ old('planting_format')==$opt?'selected':'' }}>{{ str_replace('_',' ',$opt) }}</option>
                            @endforeach
                        </select>
                        @error('planting_format')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Jumlah Petak</label>
                            <input type="number" min="0" name="num_beds" class="form-control" value="{{ old('num_beds') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Panjang Petak (m)</label>
                            <input type="number" step="0.01" min="0" name="bed_length_m" class="form-control" value="{{ old('bed_length_m') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lebar Petak (m)</label>
                            <input type="number" step="0.01" min="0" name="bed_width_m" class="form-control" value="{{ old('bed_width_m') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Ukuran Peta</label>
                        <input name="map_size" class="form-control" value="{{ old('map_size') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Kondisi Cahaya</label>
                        <input name="light_condition" class="form-control" value="{{ old('light_condition') }}">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a class="btn btn-secondary" href="{{ route('planting-locations.index') }}">Batal</a>
                <button class="btn btn-success" type="submit"><i class="fas fa-save me-2"></i>Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection















