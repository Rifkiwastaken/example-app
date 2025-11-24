@extends('layouts.app')

@section('title', 'Edit Tanaman - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Tanaman: {{ $plant->name }}</h4>
    <a href="{{ route('plants.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('plants.update', $plant) }}">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nama Tanaman</label>
                        <input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $plant->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Varietas</label>
                        <input name="variety" class="form-control @error('variety') is-invalid @enderror" value="{{ old('variety', $plant->variety) }}">
                        @error('variety')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Tipe Tanaman</label>
                        <select name="plant_type_id" class="form-select @error('plant_type_id') is-invalid @enderror">
                            <option value="">Pilih tipe</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}" {{ old('plant_type_id', $plant->plant_type_id) == $type->id ? 'selected' : '' }}>
                                    {{ $type->category ? $type->category.' - ' : '' }}{{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('plant_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Lokasi Penanaman</label>
                        <select name="planting_location_id" class="form-select @error('planting_location_id') is-invalid @enderror">
                            <option value="">Pilih lokasi</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ old('planting_location_id', $plant->planting_location_id) == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('planting_location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            @foreach(['perencanaan'=>'Perencanaan','ditanam'=>'Ditanam','dipanen'=>'Dipanen','selesai'=>'Selesai'] as $k=>$v)
                                <option value="{{ $k }}" {{ old('status', $plant->status) == $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Progress (%)</label>
                        <input type="number" min="0" max="100" name="progress" class="form-control @error('progress') is-invalid @enderror" value="{{ old('progress', $plant->progress) }}">
                        @error('progress')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a class="btn btn-secondary" href="{{ route('plants.index') }}">Batal</a>
                <button class="btn btn-success" type="submit"><i class="fas fa-save me-2"></i>Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection















