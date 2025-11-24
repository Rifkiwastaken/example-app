@extends('layouts.app')

@section('title', 'Tambah Nutrisi - SIBIT')

@section('content')
<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('planting-locations.index') }}">Grow Locations</a></li>
        <li class="breadcrumb-item"><a href="{{ route('planting-locations.show', $plantingLocation) }}">{{ $plantingLocation->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('planting-locations.nutrients.index', $plantingLocation) }}">Nutrisi</a></li>
        <li class="breadcrumb-item active">Tambah Nutrisi</li>
    </ol>
</nav>

<!-- Modal Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">New Nutrients for {{ $plantingLocation->name }}</h4>
    </div>
    <button type="button" class="btn-close" onclick="history.back()"></button>
</div>

<!-- Nutrient Form -->
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('planting-locations.nutrients.store', $plantingLocation) }}">
            @csrf
            
            <!-- General Application Details -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Product Applied</label>
                        <input type="text" name="product_applied" class="form-control @error('product_applied') is-invalid @enderror" 
                               value="{{ old('product_applied') }}" placeholder="Masukkan nama produk" required>
                        @error('product_applied')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Amount Applied</label>
                        <input type="number" name="amount_applied" class="form-control @error('amount_applied') is-invalid @enderror" 
                               value="{{ old('amount_applied') }}" step="0.01" min="0" placeholder="Masukkan jumlah" required>
                        @error('amount_applied')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Application Method</label>
                        <select name="application_method" class="form-select @error('application_method') is-invalid @enderror" required>
                            <option value="">Pilih metode aplikasi</option>
                            <option value="Penyebaran (dengan tangan atau alat)" {{ old('application_method') == 'Penyebaran (dengan tangan atau alat)' ? 'selected' : '' }}>Penyebaran (dengan tangan atau alat)</option>
                            <option value="Kompos - Padatan" {{ old('application_method') == 'Kompos - Padatan' ? 'selected' : '' }}>Kompos - Padatan</option>
                            <option value="Kompos - Teh" {{ old('application_method') == 'Kompos - Teh' ? 'selected' : '' }}>Kompos - Teh</option>
                            <option value="Granul" {{ old('application_method') == 'Granul' ? 'selected' : '' }}>Granul</option>
                            <option value="Cairan" {{ old('application_method') == 'Cairan' ? 'selected' : '' }}>Cairan</option>
                            <option value="Pupuk Kotoran Hewan" {{ old('application_method') == 'Pupuk Kotoran Hewan' ? 'selected' : '' }}>Pupuk Kotoran Hewan</option>
                            <option value="Pelet" {{ old('application_method') == 'Pelet' ? 'selected' : '' }}>Pelet</option>
                            <option value="Semprot" {{ old('application_method') == 'Semprot' ? 'selected' : '' }}>Semprot</option>
                            <option value="Lainnya" {{ old('application_method') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('application_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Date Applied</label>
                        <div class="input-group">
                            <input type="date" name="application_date" class="form-control @error('application_date') is-invalid @enderror" 
                                   value="{{ old('application_date', date('Y-m-d')) }}" required>
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        </div>
                        @error('application_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            
            <!-- Nutrients Applied Section -->
            <div class="mb-4">
                <h5 class="mb-3">Nutrients Applied</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Nitrogen (N)</label>
                            <input type="number" name="nitrogen_n" class="form-control @error('nitrogen_n') is-invalid @enderror" 
                                   value="{{ old('nitrogen_n') }}" step="0.01" min="0" placeholder="0.00">
                            @error('nitrogen_n')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Magnesium (Mg)</label>
                            <input type="number" name="magnesium_mg" class="form-control @error('magnesium_mg') is-invalid @enderror" 
                                   value="{{ old('magnesium_mg') }}" step="0.01" min="0" placeholder="0.00">
                            @error('magnesium_mg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Boron (B)</label>
                            <input type="number" name="boron_b" class="form-control @error('boron_b') is-invalid @enderror" 
                                   value="{{ old('boron_b') }}" step="0.01" min="0" placeholder="0.00">
                            @error('boron_b')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Zinc (Zn)</label>
                            <input type="number" name="zinc_zn" class="form-control @error('zinc_zn') is-invalid @enderror" 
                                   value="{{ old('zinc_zn') }}" step="0.01" min="0" placeholder="0.00">
                            @error('zinc_zn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Phosphorus (P)</label>
                            <input type="number" name="phosphorus_p" class="form-control @error('phosphorus_p') is-invalid @enderror" 
                                   value="{{ old('phosphorus_p') }}" step="0.01" min="0" placeholder="0.00">
                            @error('phosphorus_p')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sulfur (S)</label>
                            <input type="number" name="sulfur_s" class="form-control @error('sulfur_s') is-invalid @enderror" 
                                   value="{{ old('sulfur_s') }}" step="0.01" min="0" placeholder="0.00">
                            @error('sulfur_s')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Copper (Cu)</label>
                            <input type="number" name="copper_cu" class="form-control @error('copper_cu') is-invalid @enderror" 
                                   value="{{ old('copper_cu') }}" step="0.01" min="0" placeholder="0.00">
                            @error('copper_cu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Manganese (Mn)</label>
                            <input type="number" name="manganese_mn" class="form-control @error('manganese_mn') is-invalid @enderror" 
                                   value="{{ old('manganese_mn') }}" step="0.01" min="0" placeholder="0.00">
                            @error('manganese_mn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Potassium (K)</label>
                            <input type="number" name="potassium_k" class="form-control @error('potassium_k') is-invalid @enderror" 
                                   value="{{ old('potassium_k') }}" step="0.01" min="0" placeholder="0.00">
                            @error('potassium_k')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Calcium (Ca)</label>
                            <input type="number" name="calcium_ca" class="form-control @error('calcium_ca') is-invalid @enderror" 
                                   value="{{ old('calcium_ca') }}" step="0.01" min="0" placeholder="0.00">
                            @error('calcium_ca')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Iron (Fe)</label>
                            <input type="number" name="iron_fe" class="form-control @error('iron_fe') is-invalid @enderror" 
                                   value="{{ old('iron_fe') }}" step="0.01" min="0" placeholder="0.00">
                            @error('iron_fe')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Description Section -->
            <div class="mb-4">
                <label class="form-label">Description / Note</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                          rows="3" placeholder="Masukkan deskripsi atau catatan">{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            
            <!-- Action Buttons -->
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" onclick="history.back()">Cancel</button>
                <button type="submit" class="btn btn-success">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection













