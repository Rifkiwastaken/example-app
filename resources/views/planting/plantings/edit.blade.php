@extends('layouts.app')

@section('title', 'Edit Penanaman - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Penanaman</h4>
    @if($planting->plant)
        <a href="{{ route('plants.show', $planting->plant) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Detail Tanaman
        </a>
    @else
        <a href="{{ route('plantings.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali</a>
    @endif
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('plantings.update', $planting) }}">
            @csrf
            @method('PUT')

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tanaman <span class="text-danger">*</span></label>
                        <select name="plant_id" class="form-select @error('plant_id') is-invalid @enderror" required>
                            @foreach($plants as $plant)
                                <option value="{{ $plant->plant_id }}" {{ old('plant_id', $planting->plant_id) == $plant->plant_id ? 'selected' : '' }}>
                                    {{ $plant->name }} @if($plant->variety) - {{ $plant->variety }} @endif
                                </option>
                            @endforeach
                        </select>
                        @error('plant_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Lokasi Penanaman <span class="text-danger">*</span></label>
                        <select name="planting_location_id" class="form-select @error('planting_location_id') is-invalid @enderror" required>
                            @foreach($locations as $location)
                                <option value="{{ $location->planting_location_id }}" {{ old('planting_location_id', $planting->planting_location_id) == $location->planting_location_id ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('planting_location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nomor Batch Tanam</label>
                        <input type="text" name="planting_batch_number" class="form-control @error('planting_batch_number') is-invalid @enderror"
                               value="{{ old('planting_batch_number', $planting->planting_batch_number) }}">
                        @error('planting_batch_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Lokasi Tanam (Bed / Petak)</label>
                        <input type="text" name="bed_label" class="form-control @error('bed_label') is-invalid @enderror"
                               value="{{ old('bed_label', $planting->bed_label) }}" placeholder="Contoh: Bed 1, Petak 4">
                        @error('bed_label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Tanam</label>
                        <input type="date" name="planted_at" class="form-control @error('planted_at') is-invalid @enderror"
                               value="{{ old('planted_at', optional($planting->planted_at)->format('Y-m-d')) }}">
                        @error('planted_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Estimasi Tanggal Panen</label>
                        <input type="date" name="estimated_harvest_date" class="form-control @error('estimated_harvest_date') is-invalid @enderror"
                               value="{{ old('estimated_harvest_date', optional($planting->estimated_harvest_date)->format('Y-m-d')) }}">
                        @error('estimated_harvest_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Jumlah Tanam</label>
                        <div class="input-group">
                            <input type="number" name="planting_amount" class="form-control @error('planting_amount') is-invalid @enderror"
                                   value="{{ old('planting_amount', $planting->planting_amount) }}" step="0.01" min="0">
                            <span class="input-group-text">tanaman</span>
                        </div>
                        @error('planting_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Luas Lahan (ha)</label>
                        <input type="number" name="area_ha" class="form-control @error('area_ha') is-invalid @enderror"
                               value="{{ old('area_ha', $planting->area_ha) }}" step="0.01" min="0">
                        @error('area_ha')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Format Penanaman</label>
                        <select name="planting_format" class="form-select @error('planting_format') is-invalid @enderror">
                            <option value="">Pilih format</option>
                            <option value="rumpun" {{ old('planting_format', $planting->planting_format) == 'rumpun' ? 'selected' : '' }}>Rumpun</option>
                            <option value="batang" {{ old('planting_format', $planting->planting_format) == 'batang' ? 'selected' : '' }}>Batang</option>
                            <option value="lainnya" {{ old('planting_format', $planting->planting_format) == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('planting_format')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Format Penanaman (Lainnya)</label>
                        <input type="text" name="planting_format_custom" class="form-control @error('planting_format_custom') is-invalid @enderror"
                               value="{{ old('planting_format_custom', $planting->planting_format_custom) }}" placeholder="Tuliskan format lain jika diperlukan">
                        @error('planting_format_custom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Progres (%)</label>
                        <input type="number" name="progress" class="form-control @error('progress') is-invalid @enderror"
                               value="{{ old('progress', $planting->progress) }}" min="0" max="100">
                        @error('progress')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                @if($planting->plant)
                    <a href="{{ route('plants.show', $planting->plant) }}" class="btn btn-secondary">Batal</a>
                @else
                    <a href="{{ route('plantings.index') }}" class="btn btn-secondary">Batal</a>
                @endif
                <button type="submit" class="btn btn-success">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@endsection

