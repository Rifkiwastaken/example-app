@extends('layouts.app')

@section('title', 'Laporan Rekapitulasi Penjualan - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Laporan Rekapitulasi Penjualan</h4>
        <small class="text-muted">Laporan keuangan sederhana untuk pendapatan (PAD)</small>
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
        <form method="GET" action="{{ route('reports.sales') }}" id="filterForm">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-select">
                        <option value="">Semua Tahun</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Komoditas</label>
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
                    <label class="form-label">Lokasi Lahan</label>
                    <select name="planting_location_id" class="form-select">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->planting_location_id }}" {{ request('planting_location_id') == $loc->planting_location_id ? 'selected' : '' }}>
                                {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('reports.sales') }}" class="btn btn-secondary">
                        <i class="fas fa-redo me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
@if($sales->count() > 0)
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title">Total Penjualan</h6>
                <h3 class="mb-0">Rp {{ number_format($sales->sum('total_amount'), 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="card-title">Jumlah Transaksi</h6>
                <h3 class="mb-0">{{ $sales->total() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="card-title">Rata-rata per Transaksi</h6>
                <h3 class="mb-0">Rp {{ $sales->count() > 0 ? number_format($sales->avg('total_amount'), 0, ',', '.') : '0' }}</h3>
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
                        <th>No. Struk</th>
                        <th>Tanggal Penjualan</th>
                        <th>Pembeli</th>
                        <th>Komoditas</th>
                        <th>Jumlah Item</th>
                        <th>Total Penjualan</th>
                        <th>Metode Pembayaran</th>
                        <th>Status Pembayaran</th>
                        <th>Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $index => $sale)
                        <tr>
                            <td>{{ $sales->firstItem() + $index }}</td>
                            <td>
                                <code>{{ $sale->receipt_number ?? '-' }}</code>
                            </td>
                            <td>{{ $sale->sale_date ? $sale->sale_date->format('d M Y') : '-' }}</td>
                            <td>
                                <strong>{{ $sale->buyer_name ?? '-' }}</strong>
                                @if($sale->buyer_contact)
                                    <br><small class="text-muted">{{ $sale->buyer_contact }}</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $uniquePlants = $sale->items->map(function($item) {
                                        return $item->inventoryType->plant->name ?? ($item->inventoryType->name ?? 'N/A');
                                    })->unique()->values();
                                @endphp
                                @foreach($uniquePlants as $plantName)
                                    <span class="badge bg-info">{{ $plantName }}</span>
                                @endforeach
                            </td>
                            <td class="text-end">
                                <strong>{{ number_format($sale->total_items, 2) }}</strong>
                            </td>
                            <td class="text-end">
                                <strong>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</strong>
                            </td>
                            <td>{{ $sale->payment_method_label ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $sale->payment_status_color ?? 'secondary' }}">
                                    {{ $sale->payment_status_label ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $sale->user->name ?? '-' }}</td>
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
                @if($sales->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <th colspan="6" class="text-end">Total:</th>
                        <th class="text-end">Rp {{ number_format($sales->sum('total_amount'), 0, ',', '.') }}</th>
                        <th colspan="3"></th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if($sales->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $sales->links() }}
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
    window.location.href = '{{ route("reports.sales") }}?export=pdf&' + params.toString();
}

function exportExcel() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    window.location.href = '{{ route("reports.sales") }}?export=excel&' + params.toString();
}
</script>
@endpush
@endsection

