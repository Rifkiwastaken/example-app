@extends('layouts.app')

@section('title', 'Tambah Perawatan - SIBIT')

@section('content')
<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('planting-locations.index') }}">Grow Locations</a></li>
        <li class="breadcrumb-item"><a href="{{ route('planting-locations.show', $plantingLocation) }}">{{ $plantingLocation->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('planting-locations.treatments.index', $plantingLocation) }}">Perawatan</a></li>
        <li class="breadcrumb-item active">Tambah Perawatan</li>
    </ol>
</nav>

<!-- Modal Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">New Treatment for {{ $plantingLocation->name }}</h4>
    </div>
    <button type="button" class="btn-close" onclick="history.back()"></button>
</div>

<!-- Treatment Form -->
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('planting-locations.treatments.store', $plantingLocation) }}">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Treatment Type</label>
                        <select name="treatment_type" class="form-select @error('treatment_type') is-invalid @enderror" required>
                            <option value="">Pilih tipe perlakuan</option>
                            <option value="Blight" {{ old('treatment_type') == 'Blight' ? 'selected' : '' }}>Blight (Penyakit)</option>
                            <option value="Pupuk" {{ old('treatment_type') == 'Pupuk' ? 'selected' : '' }}>Pupuk</option>
                            <option value="Jamur" {{ old('treatment_type') == 'Jamur' ? 'selected' : '' }}>Jamur</option>
                            <option value="Herbisida" {{ old('treatment_type') == 'Herbisida' ? 'selected' : '' }}>Herbisida</option>
                            <option value="Insektisida" {{ old('treatment_type') == 'Insektisida' ? 'selected' : '' }}>Insektisida</option>
                            <option value="Irigasi" {{ old('treatment_type') == 'Irigasi' ? 'selected' : '' }}>Irigasi</option>
                            <option value="Mildew" {{ old('treatment_type') == 'Mildew' ? 'selected' : '' }}>Mildew (Jamur Tepung)</option>
                            <option value="Tungau" {{ old('treatment_type') == 'Tungau' ? 'selected' : '' }}>Tungau</option>
                            <option value="Nutrisi" {{ old('treatment_type') == 'Nutrisi' ? 'selected' : '' }}>Nutrisi</option>
                            <option value="Pestisida" {{ old('treatment_type') == 'Pestisida' ? 'selected' : '' }}>Pestisida</option>
                            <option value="Pengolahan Tanah" {{ old('treatment_type') == 'Pengolahan Tanah' ? 'selected' : '' }}>Pengolahan Tanah</option>
                            <option value="Virus" {{ old('treatment_type') == 'Virus' ? 'selected' : '' }}>Virus</option>
                            <option value="Lainnya" {{ old('treatment_type') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('treatment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Details/Product</label>
                        <input type="text" name="product_detail" class="form-control @error('product_detail') is-invalid @enderror" 
                               value="{{ old('product_detail') }}" placeholder="Masukkan detail produk">
                        @error('product_detail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Lembaga OPT</label>
                        <input type="text" name="opt_institution" class="form-control @error('opt_institution') is-invalid @enderror" 
                               value="{{ old('opt_institution') }}" placeholder="Masukkan lembaga OPT">
                        @error('opt_institution')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Application Method</label>
                        <select name="application_method" class="form-select @error('application_method') is-invalid @enderror" required>
                            <option value="">Pilih metode aplikasi</option>
                            <option value="Granul" {{ old('application_method') == 'Granul' ? 'selected' : '' }}>Granul</option>
                            <option value="Semprot" {{ old('application_method') == 'Semprot' ? 'selected' : '' }}>Semprot</option>
                            <option value="Lainnya" {{ old('application_method') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('application_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Days until End Of Withholding Period</label>
                        <input type="number" name="withholding_period_days" class="form-control @error('withholding_period_days') is-invalid @enderror" 
                               value="{{ old('withholding_period_days') }}" min="0" placeholder="Masukkan hari">
                        @error('withholding_period_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Technician</label>
                        <input type="text" name="technician" class="form-control @error('technician') is-invalid @enderror" 
                               value="{{ old('technician') }}" placeholder="example: Alpine Vet, etc">
                        @error('technician')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                  rows="3" placeholder="Masukkan deskripsi">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Treatment Date</label>
                        <div class="input-group">
                            <input type="date" name="treatment_date" class="form-control @error('treatment_date') is-invalid @enderror" 
                                   value="{{ old('treatment_date', date('Y-m-d')) }}" required>
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        </div>
                        @error('treatment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Add Map Location</label>
                        <button type="button" class="btn btn-outline-primary">
                            <i class="fas fa-map-marker-alt me-2"></i>Add Map Location
                        </button>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Batch #</label>
                        <input type="text" name="batch_number" class="form-control @error('batch_number') is-invalid @enderror" 
                               value="{{ old('batch_number') }}" placeholder="Masukkan nomor batch">
                        @error('batch_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Amount Applied</label>
                        <input type="number" name="amount_applied" class="form-control @error('amount_applied') is-invalid @enderror" 
                               value="{{ old('amount_applied') }}" step="0.01" min="0" placeholder="Masukkan jumlah">
                        @error('amount_applied')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Inventory Amount Used</label>
                        <input type="number" name="inventory_amount_used" class="form-control @error('inventory_amount_used') is-invalid @enderror" 
                               value="{{ old('inventory_amount_used') }}" step="0.01" min="0" placeholder="Masukkan jumlah inventory">
                        @error('inventory_amount_used')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Treatment Location</label>
                        <input type="text" name="treatment_location" class="form-control @error('treatment_location') is-invalid @enderror" 
                               value="{{ old('treatment_location') }}" placeholder="Leaf, Seed, Soil">
                        @error('treatment_location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Retreat Date</label>
                        <div class="input-group">
                            <input type="date" name="retreat_date" class="form-control @error('retreat_date') is-invalid @enderror" 
                                   value="{{ old('retreat_date') }}" placeholder="hh/bb/tttt">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        </div>
                        @error('retreat_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Treatment Total Cost</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="total_cost" class="form-control @error('total_cost') is-invalid @enderror" 
                                   value="{{ old('total_cost') }}" step="0.01" min="0" placeholder="Masukkan total biaya">
                            <span class="input-group-text"><i class="fas fa-question-circle"></i></span>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="record_expense" value="1" 
                                   {{ old('record_expense') ? 'checked' : '' }}>
                            <label class="form-check-label">Record Expense</label>
                        </div>
                        @error('total_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Unit Measurement</label>
                        <select name="unit_measurement" class="form-select @error('unit_measurement') is-invalid @enderror">
                            <option value="">Pilih unit</option>
                            <option value="Bale" {{ old('unit_measurement') == 'Bale' ? 'selected' : '' }}>Bale</option>
                            <option value="Gram" {{ old('unit_measurement') == 'Gram' ? 'selected' : '' }}>Gram</option>
                            <option value="Kilogram" {{ old('unit_measurement') == 'Kilogram' ? 'selected' : '' }}>Kilogram</option>
                            <option value="Kiloliter" {{ old('unit_measurement') == 'Kiloliter' ? 'selected' : '' }}>Kiloliter</option>
                            <option value="Liter" {{ old('unit_measurement') == 'Liter' ? 'selected' : '' }}>Liter</option>
                            <option value="Mililiter" {{ old('unit_measurement') == 'Mililiter' ? 'selected' : '' }}>Mililiter</option>
                            <option value="Kuantitas" {{ old('unit_measurement') == 'Kuantitas' ? 'selected' : '' }}>Kuantitas</option>
                            <option value="Ton" {{ old('unit_measurement') == 'Ton' ? 'selected' : '' }}>Ton</option>
                        </select>
                        @error('unit_measurement')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Keywords</label>
                        <div class="input-group">
                            <input type="text" name="keywords" class="form-control @error('keywords') is-invalid @enderror" 
                                   value="{{ old('keywords') }}" placeholder="example: monthly application, etc">
                            <span class="input-group-text"><i class="fas fa-tag"></i></span>
                        </div>
                        @error('keywords')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between align-items-center">
                <a href="#" class="text-decoration-none">
                    <i class="fas fa-cog me-2"></i>Customize Fields
                </a>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary" onclick="history.back()">Cancel</button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection













