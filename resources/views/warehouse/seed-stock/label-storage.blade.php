@extends('layouts.app')

@section('title', 'Input Data Stok Benih - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Input Data Stok Benih</h4>
        <small class="text-muted">Pilih lokasi dan tempat penyimpanan · {{ $report->jumlah_lembar_label_dicetak }} lembar label</small>
    </div>
    <a href="{{ route('seed-stock.show', [$plant, 'tab' => 'lots']) }}" class="btn btn-secondary">Kembali</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('seed-stock.label.storage.store', [$plant, $stock, $report]) }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Lokasi penyimpanan <span class="text-danger">*</span></label>
                <select name="warehouse_id" id="warehouse-id" class="form-select" required>
                    <option value="">Pilih lokasi</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->warehouse_id }}" {{ old('warehouse_id') == $warehouse->warehouse_id ? 'selected' : '' }}>{{ $warehouse->name }} ({{ $warehouse->tipe_lokasi_label }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Tempat penyimpanan <span class="text-danger">*</span></label>
                <select name="rak_gudang_id" id="bin-id" class="form-select" required>
                    <option value="">Pilih lokasi terlebih dahulu</option>
                    @foreach($warehouses as $warehouse)
                        @foreach($warehouse->bins as $bin)
                            <option value="{{ $bin->warehouse_bin_id }}" data-warehouse="{{ $warehouse->warehouse_id }}" {{ old('rak_gudang_id') == $bin->warehouse_bin_id ? 'selected' : '' }} hidden>
                                {{ $bin->name }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('seed-stock.show', [$plant, 'tab' => 'lots']) }}" class="btn btn-secondary">Batal</a>
                <button class="btn btn-success" type="submit">Simpan ke lokasi penyimpanan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const warehouse = document.getElementById('warehouse-id');
    const bin = document.getElementById('bin-id');
    function filterBins() {
        const warehouseId = warehouse.value;
        Array.from(bin.options).forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }
            const match = option.getAttribute('data-warehouse') === warehouseId;
            option.hidden = !match;
            if (!match && option.selected) {
                option.selected = false;
            }
        });
        if (!bin.value) {
            bin.selectedIndex = 0;
        }
    }
    warehouse.addEventListener('change', filterBins);
    filterBins();
})();
</script>
@endpush
