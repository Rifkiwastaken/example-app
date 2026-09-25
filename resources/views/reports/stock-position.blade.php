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
                            <option value="{{ $warehouse->warehouse_id }}" {{ request('warehouse_id') == $warehouse->warehouse_id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @include('reports.partials._variety-scope-filter')
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
                <h3 class="mb-0">{{ number_format($lots->sum('stok_saat_ini'), 2) }}</h3>
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
                        <th>Nomor Lot Induk</th>
                        <th>Gudang</th>
                        <th>Rak Gudang</th>
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
                                <strong>{{ $lot->plant->name ?? 'N/A' }}</strong>
                                @if($lot->plant?->variety)
                                    <br><small class="text-muted">{{ $lot->plant->variety }}</small>
                                @endif
                            </td>
                            <td>
                                <code>{{ $lot->no_label_resmi }}</code>
                            </td>
                            <td>{{ $lot->rack?->warehouse?->name ?? '-' }}</td>
                            <td>{{ $lot->rack->name ?? '-' }}</td>
                            <td class="text-end">
                                <strong>{{ number_format($lot->stok_saat_ini, 2) }}</strong>
                            </td>
                            <td>{{ $lot->plant?->satuanStok?->code ?? '-' }}</td>
                            <td>
                                @if($lot->tgl_kedaluwarsa)
                                    {{ $lot->tgl_kedaluwarsa->format('d M Y') }}
                                    @php
                                        $daysRemaining = now()->diffInDays($lot->tgl_kedaluwarsa, false);
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
                                @php $displayStatus = $lot->displayStatus(); @endphp
                                @if($displayStatus === 'aktif')
                                    <span class="badge bg-success">{{ $lot->displayStatusLabel() }}</span>
                                @elseif(in_array($displayStatus, ['mendekati_masa_edar', 'siap_stok', 'pelabelan'], true))
                                    <span class="badge bg-warning">{{ $lot->displayStatusLabel() }}</span>
                                @elseif($displayStatus === 'butuh_uji_ulang')
                                    <span class="badge bg-info">{{ $lot->displayStatusLabel() }}</span>
                                @else
                                    <span class="badge bg-danger">{{ $lot->displayStatusLabel() }}</span>
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
                        <th class="text-end">{{ number_format($lots->sum('stok_saat_ini'), 2) }}</th>
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
@include('reports.partials._variety-scope-scripts')
@endsection

