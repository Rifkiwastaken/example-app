@extends('layouts.app')

@section('title', ($action === 'add' ? 'Tambah' : 'Kurangi') . ' Stok - ' . $inventoryType->name . ' - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Penyesuaian Stok ({{ $action === 'add' ? 'Tambah' : 'Kurangi' }} Stok)</h4>
    <a href="{{ route('seed-stock.show', $inventoryType) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="alert alert-info mb-4">
            <strong>Varietas:</strong> {{ $inventoryType->name }} ({{ $inventoryType->sku }})
        </div>

        <form action="{{ route('seed-stock.store-stock-adjustment', $inventoryType) }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="{{ $action }}">

            <div class="mb-3">
                <label for="quantity" class="form-label">
                    Jumlah yang {{ $action === 'add' ? 'Ditambahkan' : 'Dikurangi' }} 
                    <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="number" step="0.01" class="form-control @error('quantity') is-invalid @enderror" 
                           id="quantity" name="quantity" value="{{ old('quantity') }}" required>
                    <span class="input-group-text">{{ $inventoryType->unit }}</span>
                </div>
                @error('quantity')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="warehouse_id" class="form-label">Pilih Gudang <span class="text-danger">*</span></label>
                <select class="form-select @error('warehouse_id') is-invalid @enderror" 
                        id="warehouse_id" name="warehouse_id" required onchange="loadBins()">
                    <option value="">Pilih Gudang</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                            {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>
                @error('warehouse_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3" id="bin-container" style="display: none;">
                <label for="bin_id" class="form-label">Pilih Bin</label>
                <select class="form-select @error('bin_id') is-invalid @enderror" 
                        id="bin_id" name="bin_id" onchange="loadLots()">
                    <option value="">Pilih Bin (Opsional)</option>
                </select>
                @error('bin_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @if($action === 'subtract')
            <div class="mb-3" id="lot-container" style="display: none;">
                <label for="inventory_lot_id" class="form-label">Pilih Lot (Batch) <span class="text-danger">*</span></label>
                <select class="form-select @error('inventory_lot_id') is-invalid @enderror" 
                        id="inventory_lot_id" name="inventory_lot_id">
                    <option value="">Pilih Lot</option>
                </select>
                @error('inventory_lot_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            @endif

            <div class="mb-3">
                <label for="reason" class="form-label">Alasan {{ $action === 'subtract' ? 'Pengurangan' : '' }}</label>
                <select class="form-select @error('reason') is-invalid @enderror" id="reason" name="reason">
                    @if($action === 'subtract')
                        <option value="">Pilih Alasan</option>
                        <option value="Rusak/Spoilage">Rusak/Spoilage</option>
                        <option value="Hilang">Hilang</option>
                        <option value="Dipakai Sampel">Dipakai Sampel</option>
                        <option value="Koreksi Data">Koreksi Data</option>
                    @else
                        <option value="">Pilih Alasan</option>
                        <option value="Penerimaan Baru">Penerimaan Baru</option>
                        <option value="Koreksi Data">Koreksi Data</option>
                        <option value="Retur">Retur</option>
                    @endif
                </select>
                @error('reason')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">
                    Catatan 
                    @if($action === 'subtract')
                        <span class="text-danger">(Wajib jika alasan 'Rusak')</span>
                    @endif
                </label>
                <textarea class="form-control @error('notes') is-invalid @enderror" 
                          id="notes" name="notes" rows="3" 
                          placeholder="Contoh: Kemasan sobek terkena tikus">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('seed-stock.show', $inventoryType) }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-{{ $action === 'add' ? 'success' : 'danger' }}">
                    <i class="fas fa-{{ $action === 'add' ? 'save' : 'minus' }} me-2"></i>Simpan Penyesuaian
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const warehouses = @json($warehouses->mapWithKeys(function($w) {
    return [$w->id => $w->bins->map(function($b) { return ['id' => $b->id, 'name' => $b->name]; })];
}));

const lots = @json($inventoryType->lots->map(function($l) {
    return [
        'id' => $l->id,
        'warehouse_id' => $l->warehouse_id,
        'bin_id' => $l->bin_id,
        'production_id' => $l->production_id,
        'current_stock' => $l->current_stock
    ];
}));

function loadBins() {
    const warehouseId = document.getElementById('warehouse_id').value;
    const binSelect = document.getElementById('bin_id');
    const binContainer = document.getElementById('bin-container');
    
    binSelect.innerHTML = '<option value="">Pilih Bin (Opsional)</option>';
    
    if (warehouseId && warehouses[warehouseId]) {
        warehouses[warehouseId].forEach(bin => {
            binSelect.innerHTML += `<option value="${bin.id}">${bin.name}</option>`;
        });
        binContainer.style.display = 'block';
    } else {
        binContainer.style.display = 'none';
    }
    
    loadLots();
}

function loadLots() {
    const warehouseId = document.getElementById('warehouse_id').value;
    const binId = document.getElementById('bin_id').value;
    const lotSelect = document.getElementById('inventory_lot_id');
    const lotContainer = document.getElementById('lot-container');
    
    if (!lotSelect) return;
    
    lotSelect.innerHTML = '<option value="">Pilih Lot</option>';
    
    if (warehouseId) {
        const filteredLots = lots.filter(lot => 
            lot.warehouse_id == warehouseId && 
            (!binId || lot.bin_id == binId) &&
            lot.current_stock > 0
        );
        
        if (filteredLots.length > 0) {
            filteredLots.forEach(lot => {
                lotSelect.innerHTML += `<option value="${lot.id}">${lot.production_id || 'Lot #' + lot.id} (Sisa: ${lot.current_stock} {{ $inventoryType->unit }})</option>`;
            });
            lotContainer.style.display = 'block';
        } else {
            lotContainer.style.display = 'none';
        }
    } else {
        lotContainer.style.display = 'none';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('warehouse_id').value) {
        loadBins();
    }
});
</script>
@endpush
@endsection

