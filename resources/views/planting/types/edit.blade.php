@extends('layouts.app')

@section('title', 'Edit Tipe Tanaman - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Tipe Tanaman</h4>
    <a href="{{ route('plant-types.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('plant-types.update', $type) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Nama Tipe</label>
                <input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $type->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Kategori (opsional)</label>
                <input name="category" class="form-control @error('category') is-invalid @enderror" value="{{ old('category', $type->category) }}">
                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a class="btn btn-secondary" href="{{ route('plant-types.index') }}">Batal</a>
                <button class="btn btn-success" type="submit"><i class="fas fa-save me-2"></i>Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection















