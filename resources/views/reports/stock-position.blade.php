@extends('layouts.app')

@section('title', 'Laporan Posisi Stok Akhir - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Laporan Posisi Stok Akhir (Stock Opname)</h4>
        <small class="text-muted">Jumlah stok real-time di semua gudang</small>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i class="fas fa-filter me-2"></i>Filter Data
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reports.stock-position') }}" id="filterForm">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Gudang</label>
                    <select name="warehouse_id" class="form-select">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Komoditas/Tanaman</label>
                    <select name="plant_id" class="form-select">
                        <option value="">Semua Komoditas</option>
                        @foreach($plants as $plant)
                            <option value="{{ $plant->id }}" {{ request('plant_id') == $plant->id ? 'selected' : '' }}>
                                {{ $plant->name }} @if($plant->variety) - {{ $plant->variety }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tipe Inventaris</label>
                    <select name="inventory_type_id" class="form-select">
                        <option value="">Semua Tipe</option>
                        @foreach($inventoryTypes as $type)
                            <option value="{{ $type->id }}" {{ request('inventory_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->plant->name ?? ($type->name ?? 'N/A') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('reports.stock-position') }}" class="btn btn-secondary">
                        <i class="fas fa-redo me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
@if($lots->count() > 0)
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title">Total Lot</h6>
                <h3 class="mb-0">{{ $lots->total() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="card-title">Total Stok</h6>
                <h3 class="mb-0">{{ number_format($lots->sum('current_stock'), 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="card-title">Total Nilai Aset</h6>
                <h3 class="mb-0">Rp {{ number_format($lots->sum('asset_value'), 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Export Buttons -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" onclick="exportPDF()">
                <i class="fas fa-file-pdf me-2"></i>Download PDF
            </button>
            <button type="button" class="btn btn-success" onclick="exportExcel()">
                <i class="fas fa-file-excel me-2"></i>Export Excel
            </button>
        </div>
    </div>
</div>

<!-- Report Preview -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-table me-2"></i>Preview Data
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Komoditas/Tanaman</th>
                        <th>Batch/Produksi</th>
                        <th>Gudang</th>
                        <th>Bin</th>
                        <th>Stok Tersedia</th>
                        <th>Unit</th>
                        <th>Tanggal Kadaluarsa</th>
                        <th>Status</th>
                        <th>Nilai Aset</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lots as $index => $lot)
                        <tr>
                            <td>{{ $lots->firstItem() + $index }}</td>
                            <td>
                                <strong>{{ $lot->inventoryType->plant->name ?? ($lot->inventoryType->name ?? 'N/A') }}</strong>
                                @if($lot->inventoryType->plant && $lot->inventoryType->plant->variety)
                                    <br><small class="text-muted">{{ $lot->inventoryType->plant->variety }}</small>
                                @endif
                            </td>
                            <td>
                                <code>{{ $lot->production_id ?? '-' }}</code>
                            </td>
                            <td>{{ $lot->warehouse->name ?? '-' }}</td>
                            <td>{{ $lot->bin->name ?? '-' }}</td>
                            <td class="text-end">
                                <strong>{{ number_format($lot->current_stock, 2) }}</strong>
                            </td>
                            <td>{{ $lot->stock_unit ?? '-' }}</td>
                            <td>
                                @if($lot->expiry_date)
                                    {{ $lot->expiry_date->format('d M Y') }}
                                    @php
                                        $daysRemaining = now()->diffInDays($lot->expiry_date, false);
                                    @endphp
                                    @if($daysRemaining < 0)
                                        <br><small class="text-danger">(Kadaluarsa)</small>
                                    @elseif($daysRemaining <= 30)
                                        <br><small class="text-warning">({{ $daysRemaining }} hari lagi)</small>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($lot->status === 'tersedia')
                                    <span class="badge bg-success">Tersedia</span>
                                @elseif($lot->status === 'segera_kadaluarsa')
                                    <span class="badge bg-warning">Segera Kadaluarsa</span>
                                @elseif($lot->status === 'kadaluarsa')
                                    <span class="badge bg-danger">Kadaluarsa</span>
                                @elseif($lot->status === 'habis')
                                    <span class="badge bg-secondary">Habis</span>
                                @else
                                    <span class="badge bg-secondary">{{ $lot->status }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <strong>Rp {{ number_format($lot->asset_value, 0, ',', '.') }}</strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                Tidak ada data ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($lots->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <th colspan="5" class="text-end">Total:</th>
                        <th class="text-end">{{ number_format($lots->sum('current_stock'), 2) }}</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th class="text-end">Rp {{ number_format($lots->sum('asset_value'), 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if($lots->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $lots->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function exportPDF() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    window.location.href = '{{ route("reports.stock-position") }}?export=pdf&' + params.toString();
}

function exportExcel() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    window.location.href = '{{ route("reports.stock-position") }}?export=excel&' + params.toString();
}
</script>
@endpush
@endsection

