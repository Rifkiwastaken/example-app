@extends('layouts.app')

@section('title', 'Tambah Benih Sumber - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Tambah Benih Sumber</h4>
    <a href="{{ route('plants.seed-sources.index', $plant) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <p class="text-muted mb-4">
            Varietas: <strong>{{ $plant->variety ?: $plant->name }}</strong>
            @if($plant->type)
                · {{ $plant->type->name }}{{ $plant->type->category ? ' (' . $plant->type->category . ')' : '' }}
            @endif
        </p>

            <form method="POST" action="{{ route('plants.seed-sources.store', $plant) }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label">Kelas Benih <span class="text-danger">*</span></label>
                <select name="seed_class" class="form-select @error('seed_class') is-invalid @enderror" required>
                    <option value="">Pilih kelas benih</option>
                    @foreach(\App\Models\SeedSource::KELAS_BENIH as $code => $label)
                        <option value="{{ $code }}" {{ old('seed_class') === $code ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('seed_class')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Nomor lot asal benih sumber <span class="text-danger">*</span></label>
                <input type="text" name="origin_lot_number" maxlength="50"
                       class="form-control @error('origin_lot_number') is-invalid @enderror"
                       value="{{ old('origin_lot_number') }}" placeholder="Contoh: BB-PADI-2026-001" required>
                @error('origin_lot_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Produsen Asal <span class="text-danger">*</span></label>
                <input type="text" name="origin_producer" maxlength="150"
                       class="form-control @error('origin_producer') is-invalid @enderror"
                       value="{{ old('origin_producer') }}" placeholder="Contoh: BB Padi Sukamandi" required>
                @error('origin_producer')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label">Jumlah benih sumber <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" name="quantity_kg" step="0.01" min="0.01"
                           class="form-control @error('quantity_kg') is-invalid @enderror"
                           value="{{ old('quantity_kg') }}" placeholder="0.00" required>
                    <span class="input-group-text">{{ $plant->satuanStok?->code ?: 'kg' }}</span>
                    @error('quantity_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                          placeholder="Masukkan deskripsi benih sumber">{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label">Lampiran</label>
                <input type="file" name="file" class="form-control">
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a class="btn btn-secondary" href="{{ route('plants.seed-sources.index', $plant) }}">Batal</a>
                <button class="btn btn-success" type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
