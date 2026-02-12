@extends('layouts.app')

@section('title', 'Laporan Mutasi Stok - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Laporan Mutasi Stok (Kartu Stok)</h4>
        <small class="text-muted">Melacak pergerakan masuk dan keluar benih (Audit trail)</small>
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
        <form method="GET" action="{{ route('reports.stock-mutation') }}" id="filterForm">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
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
                <div class="col-md-3 mb-3">
                    <label class="form-label">Lot/Batch</label>
                    <select name="inventory_lot_id" class="form-select">
                        <option value="">Semua Lot</option>
                        @foreach($lots as $lot)
                            <option value="{{ $lot->id }}" {{ request('inventory_lot_id') == $lot->id ? 'selected' : '' }}>
                                {{ $lot->production_id ?? 'Lot #' . $lot->id }}
                            </option>
                        @endforeach
                    </select>
                </div>
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
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('reports.stock-mutation') }}" class="btn btn-secondary">
                        <i class="fas fa-redo me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

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
                        <th>Tanggal & Waktu</th>
                        <th>Komoditas/Tanaman</th>
                        <th>Lot/Batch</th>
                        <th>Jenis Transaksi</th>
                        <th>Masuk</th>
                        <th>Keluar</th>
                        <th>Saldo</th>
                        <th>Unit</th>
                        <th>Gudang</th>
                        <th>Bin</th>
                        <th>Keterangan</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $index => $transaction)
                        @php
                            $isAddition = in_array($transaction->transaction_type, ['stok_masuk', 'penyesuaian_tambah', 'pindah_lokasi']);
                        @endphp
                        <tr>
                            <td>{{ $transactions->firstItem() + $index }}</td>
                            <td>
                                {{ $transaction->created_at->format('d M Y') }}<br>
                                <small class="text-muted">{{ $transaction->created_at->format('H:i:s') }}</small>
                            </td>
                            <td>
                                <strong>{{ $transaction->inventoryType->plant->name ?? ($transaction->inventoryType->name ?? 'N/A') }}</strong>
                                @if($transaction->inventoryType->plant && $transaction->inventoryType->plant->variety)
                                    <br><small class="text-muted">{{ $transaction->inventoryType->plant->variety }}</small>
                                @endif
                            </td>
                            <td>
                                @if($transaction->inventoryLot)
                                    <code>{{ $transaction->inventoryLot->production_id ?? 'Lot #' . $transaction->inventoryLot->id }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $isAddition ? 'success' : 'danger' }}">
                                    {{ $transaction->transaction_type_label }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if($isAddition)
                                    <strong class="text-success">+{{ number_format(abs($transaction->quantity), 2) }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if(!$isAddition)
                                    <strong class="text-danger">-{{ number_format(abs($transaction->quantity), 2) }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <strong>{{ number_format($transaction->balance, 2) }}</strong>
                            </td>
                            <td>{{ $transaction->unit ?? '-' }}</td>
                            <td>{{ $transaction->warehouse->name ?? '-' }}</td>
                            <td>{{ $transaction->bin->name ?? '-' }}</td>
                            <td>
                                @if($transaction->reason)
                                    <small>{{ $transaction->reason }}</small>
                                @endif
                                @if($transaction->notes)
                                    <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($transaction->notes, 30) }}</small>
                                @endif
                            </td>
                            <td>
                                <small>{{ $transaction->user->name ?? '-' }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                Tidak ada data ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $transactions->links() }}
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
    window.location.href = '{{ route("reports.stock-mutation") }}?export=pdf&' + params.toString();
}

function exportExcel() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    window.location.href = '{{ route("reports.stock-mutation") }}?export=excel&' + params.toString();
}
</script>
@endpush
@endsection

