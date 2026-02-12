@extends('layouts.app')

@section('title', 'Tambah Tipe Benih Baru - Langkah 2 - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Tentukan Lokasi Penyimpanan (Langkah 2 dari 3)</h4>
    <a href="{{ route('seed-stock.create') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="alert alert-info mb-4">
            <strong>Pilih Gudang dan Bin yang Diizinkan untuk:</strong> {{ $step1Data['name'] ?? 'Tipe Benih Baru' }}
        </div>

        <form action="{{ route('seed-stock.store-step2') }}" method="POST">
            @csrf
            
            <div id="warehouses-container">
                @foreach($warehouses as $warehouse)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="form-check mb-2">
                            <input class="form-check-input warehouse-checkbox" 
                                   type="checkbox" 
                                   name="warehouses[{{ $warehouse->id }}][warehouse_id]" 
                                   value="{{ $warehouse->id }}" 
                                   id="warehouse_{{ $warehouse->id }}"
                                   onchange="toggleWarehouseBins({{ $warehouse->id }})">
                            <label class="form-check-label fw-bold" for="warehouse_{{ $warehouse->id }}">
                                (✓) {{ $warehouse->name }}
                            </label>
                        </div>

                        <div class="ms-4" id="bins_{{ $warehouse->id }}" style="display: none;">
                            @if($warehouse->tracking_type === 'warehouse_only')
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" 
                                           name="warehouses[{{ $warehouse->id }}][warehouse_only]" 
                                           value="1" 
                                           id="warehouse_only_{{ $warehouse->id }}"
                                           checked>
                                    <label class="form-check-label" for="warehouse_only_{{ $warehouse->id }}">
                                        (Hanya di lokasi ini)
                                    </label>
                                </div>
                            @else
                                @if($warehouse->bins->count() > 0)
                                    @foreach($warehouse->bins as $bin)
                                    <div class="form-check">
                                        <input class="form-check-input bin-checkbox" 
                                               type="checkbox" 
                                               name="warehouses[{{ $warehouse->id }}][bin_ids][]" 
                                               value="{{ $bin->id }}" 
                                               id="bin_{{ $bin->id }}">
                                        <label class="form-check-label" for="bin_{{ $bin->id }}">
                                            {{ $bin->name }}
                                        </label>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="warehouses[{{ $warehouse->id }}][warehouse_only]" 
                                               value="1" 
                                               id="warehouse_only_{{ $warehouse->id }}"
                                               checked>
                                        <label class="form-check-label" for="warehouse_only_{{ $warehouse->id }}">
                                            (Hanya di lokasi ini)
                                        </label>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @error('warehouses')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('seed-stock.create') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
                <button type="submit" class="btn btn-success">
                    Lanjut (Next) <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleWarehouseBins(warehouseId) {
    const checkbox = document.getElementById('warehouse_' + warehouseId);
    const binsDiv = document.getElementById('bins_' + warehouseId);
    
    if (checkbox.checked) {
        binsDiv.style.display = 'block';
    } else {
        binsDiv.style.display = 'none';
        // Uncheck all bins in this warehouse
        binsDiv.querySelectorAll('.bin-checkbox').forEach(bin => bin.checked = false);
    }
}
</script>
@endpush
@endsection

