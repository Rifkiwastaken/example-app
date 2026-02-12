@extends('layouts.app')

@section('title', 'Record Harvest - SIBESTI')

@section('content')
<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('plants.index') }}">My Crops</a></li>
        <li class="breadcrumb-item"><a href="{{ route('plants.show', $selectedPlant) }}">{{ $selectedPlant->name }}</a></li>
        <li class="breadcrumb-item active">Record Harvest</li>
    </ol>
</nav>

<!-- Plant Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 18px; font-weight: bold;">
            {{ substr($selectedPlant->name, 0, 2) }}
        </div>
        <div>
            <h4 class="mb-0">{{ $selectedPlant->name }}</h4>
            <small class="text-muted">{{ $selectedPlant->type?->name ?: 'Tidak ada tipe' }}</small>
        </div>
    </div>
    <div class="btn-group">
        <a href="{{ route('plantings.create', ['plant_id' => $selectedPlant->id]) }}" class="btn btn-success">Add Planting</a>
        <a href="{{ route('harvests.create', ['plant_id' => $selectedPlant->id]) }}" class="btn btn-primary active">Harvest</a>
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('plants.edit', $selectedPlant) }}">Edit Plant</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Record Harvest Form -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Record Harvest</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('harvests.store') }}">
            @csrf
            <input type="hidden" name="plant_id" value="{{ $selectedPlant->id }}">
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Panen</label>
                        <div class="input-group">
                            <input type="date" name="harvested_at" class="form-control @error('harvested_at') is-invalid @enderror" 
                                   value="{{ old('harvested_at', date('Y-m-d')) }}" required>
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        </div>
                        @error('harvested_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Batch Number</label>
                        <input type="text" name="batch_no" class="form-control @error('batch_no') is-invalid @enderror" 
                               value="{{ old('batch_no', '1001') }}" required>
                        @error('batch_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea name="note" class="form-control @error('note') is-invalid @enderror" rows="3" 
                                  placeholder="Masukkan catatan panen">{{ old('note') }}</textarea>
                        @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Harvest Details Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Harvest Details</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Di Panen Dari</th>
                                    <th>Bed</th>
                                    <th>Grade/Size (Optional)</th>
                                    <th>Amount Harvested</th>
                                </tr>
                            </thead>
                            <tbody id="harvestDetailsTable">
                                <tr>
                                    <td>
                                        <select name="planting_location_id" class="form-select @error('planting_location_id') is-invalid @enderror" required>
                                            <option value="">Pilih lokasi</option>
                                            @foreach($locations as $location)
                                                <option value="{{ $location->id }}" {{ old('planting_location_id') == $location->id ? 'selected' : '' }}>
                                                    {{ $location->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('planting_location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </td>
                                    <td>
                                        <input type="text" name="source" class="form-control @error('source') is-invalid @enderror" 
                                               value="{{ old('source') }}" placeholder="-" required>
                                        @error('source')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </td>
                                    <td>
                                        <input type="text" name="quality" class="form-control @error('quality') is-invalid @enderror" 
                                               value="{{ old('quality') }}" placeholder="Kualitas/ukuran">
                                        @error('quality')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror" 
                                                   value="{{ old('quantity', 0) }}" step="0.01" min="0" required>
                                            <select name="unit" class="form-select @error('unit') is-invalid @enderror" required>
                                                <option value="">Unit</option>
                                                <option value="ikat" {{ old('unit') == 'ikat' ? 'selected' : '' }}>Ikat / Gulungan</option>
                                                <option value="barel" {{ old('unit') == 'barel' ? 'selected' : '' }}>Barel / Tong</option>
                                                <option value="tandan" {{ old('unit') == 'tandan' ? 'selected' : '' }}>Tandan / Ikat</option>
                                                <option value="gantang" {{ old('unit') == 'gantang' ? 'selected' : '' }}>Gantang</option>
                                                <option value="lusin" {{ old('unit') == 'lusin' ? 'selected' : '' }}>Lusin</option>
                                                <option value="gram" {{ old('unit') == 'gram' ? 'selected' : '' }}>Gram</option>
                                                <option value="batang" {{ old('unit') == 'batang' ? 'selected' : '' }}>Batang / Kepala</option>
                                                <option value="kilogram" {{ old('unit') == 'kilogram' ? 'selected' : '' }}>Kilogram</option>
                                                <option value="kiloliter" {{ old('unit') == 'kiloliter' ? 'selected' : '' }}>Kiloliter (1.000 liter)</option>
                                                <option value="liter" {{ old('unit') == 'liter' ? 'selected' : '' }}>Liter</option>
                                                <option value="mililiter" {{ old('unit') == 'mililiter' ? 'selected' : '' }}>Mililiter</option>
                                                <option value="jumlah" {{ old('unit') == 'jumlah' ? 'selected' : '' }}>Jumlah / Satuan</option>
                                                <option value="ton" {{ old('unit') == 'ton' ? 'selected' : '' }}>Ton</option>
                                            </select>
                                        </div>
                                        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-dark" onclick="addHarvestLocation()">
                            <i class="fas fa-plus me-2"></i>Add Location
                        </button>
                    </div>
                    <div class="text-end mt-3">
                        <strong>Harvest Total: <span id="harvestTotal">0.00</span> <span id="harvestUnit">Units</span></strong>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a class="btn btn-secondary" href="{{ route('plants.show', $selectedPlant) }}">Cancel</a>
                <button class="btn btn-success" type="submit">Save</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let harvestRowCount = 1;

function addHarvestLocation() {
    const table = document.getElementById('harvestDetailsTable');
    const newRow = table.insertRow();
    
    newRow.innerHTML = `
        <td>
            <select name="planting_location_id_${harvestRowCount}" class="form-select">
                <option value="">Pilih lokasi</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" name="source_${harvestRowCount}" class="form-control" placeholder="-">
        </td>
        <td>
            <input type="text" name="quality_${harvestRowCount}" class="form-control" placeholder="Kualitas/ukuran">
        </td>
        <td>
            <div class="input-group">
                <input type="number" name="quantity_${harvestRowCount}" class="form-control harvest-quantity" step="0.01" min="0" onchange="updateHarvestTotal()">
                <select name="unit_${harvestRowCount}" class="form-select harvest-unit" onchange="updateHarvestTotal()">
                    <option value="">Unit</option>
                    <option value="ikat">Ikat / Gulungan</option>
                    <option value="barel">Barel / Tong</option>
                    <option value="tandan">Tandan / Ikat</option>
                    <option value="gantang">Gantang</option>
                    <option value="lusin">Lusin</option>
                    <option value="gram">Gram</option>
                    <option value="batang">Batang / Kepala</option>
                    <option value="kilogram">Kilogram</option>
                    <option value="kiloliter">Kiloliter (1.000 liter)</option>
                    <option value="liter">Liter</option>
                    <option value="mililiter">Mililiter</option>
                    <option value="jumlah">Jumlah / Satuan</option>
                    <option value="ton">Ton</option>
                </select>
            </div>
        </td>
    `;
    
    harvestRowCount++;
}

function updateHarvestTotal() {
    const quantities = document.querySelectorAll('.harvest-quantity');
    const units = document.querySelectorAll('.harvest-unit');
    
    let total = 0;
    let unit = 'Units';
    
    quantities.forEach((qty, index) => {
        if (qty.value && !isNaN(parseFloat(qty.value))) {
            total += parseFloat(qty.value);
        }
        if (units[index] && units[index].value) {
            unit = units[index].value;
        }
    });
    
    document.getElementById('harvestTotal').textContent = total.toFixed(2);
    document.getElementById('harvestUnit').textContent = unit;
}

// Initialize harvest total
document.addEventListener('DOMContentLoaded', function() {
    updateHarvestTotal();
});
</script>
@endpush
@endsection
















