@extends('layouts.app')

@section('title', 'Laporan Penggunaan Sarana Produksi - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Laporan Penggunaan Sarana Produksi</h4>
        <small class="text-muted">Rekap penggunaan pupuk dan pestisida untuk audit biaya</small>
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
        <form method="GET" action="{{ route('reports.production-supplies') }}" id="filterForm">
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
                <div class="col-md-3 mb-3">
                    <label class="form-label">Jenis Pengeluaran</label>
                    <select name="expense_type" class="form-select">
                        <option value="">Semua Jenis</option>
                        @foreach($expenseTypes as $key => $label)
                            <option value="{{ $key }}" {{ request('expense_type') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('reports.production-supplies') }}" class="btn btn-secondary">
                        <i class="fas fa-redo me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
@if($expenses->count() > 0)
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title">Total Pengeluaran</h6>
                <h3 class="mb-0">Rp {{ number_format($expenses->sum('amount'), 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="card-title">Jumlah Transaksi</h6>
                <h3 class="mb-0">{{ $expenses->total() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="card-title">Rata-rata per Transaksi</h6>
                <h3 class="mb-0">Rp {{ $expenses->count() > 0 ? number_format($expenses->avg('amount'), 0, ',', '.') : '0' }}</h3>
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
                        <th>Tanggal Pengeluaran</th>
                        <th>Nama Pengeluaran</th>
                        <th>Jenis Pengeluaran</th>
                        <th>Komoditas</th>
                        <th>Lokasi Lahan</th>
                        <th>Penanggung Jawab</th>
                        <th>Total Biaya</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $index => $expense)
                        @php
                            // Get plant from expense
                            $plant = null;
                            if ($expense->planting && $expense->planting->plant) {
                                $plant = $expense->planting->plant;
                            } elseif ($expense->treatment && $expense->treatment->planting && $expense->treatment->planting->plant) {
                                $plant = $expense->treatment->planting->plant;
                            } elseif ($expense->nutrient && $expense->nutrient->planting && $expense->nutrient->planting->plant) {
                                $plant = $expense->nutrient->planting->plant;
                            }
                        @endphp
                        <tr>
                            <td>{{ $expenses->firstItem() + $index }}</td>
                            <td>{{ $expense->expense_date ? $expense->expense_date->format('d M Y') : '-' }}</td>
                            <td>
                                <strong>{{ $expense->expense_name ?? $expense->work_name ?? '-' }}</strong>
                                @if($expense->description)
                                    <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($expense->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($expense->expense_type === 'perawatan')
                                    <span class="badge bg-warning">{{ $expenseTypes['perawatan'] }}</span>
                                @elseif($expense->expense_type === 'nutrisi')
                                    <span class="badge bg-info">{{ $expenseTypes['nutrisi'] }}</span>
                                @elseif($expense->expense_type === 'upah_pekerja')
                                    <span class="badge bg-primary">{{ $expenseTypes['upah_pekerja'] }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ $expenseTypes['lainnya'] }}</span>
                                @endif
                            </td>
                            <td>
                                @if($plant)
                                    {{ $plant->name }}
                                    @if($plant->variety)
                                        <br><small class="text-muted">{{ $plant->variety }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $expense->plantingLocation->name ?? '-' }}</td>
                            <td>{{ $expense->responsiblePerson->name ?? '-' }}</td>
                            <td class="text-end">
                                <strong>Rp {{ number_format($expense->amount, 0, ',', '.') }}</strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                Tidak ada data ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($expenses->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <th colspan="7" class="text-end">Total:</th>
                        <th class="text-end">Rp {{ number_format($expenses->sum('amount'), 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function exportPDF() {
    // Get current filter parameters
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    // Redirect to PDF export route
    window.location.href = '{{ route("reports.production-supplies") }}?export=pdf&' + params.toString();
}

function exportExcel() {
    // Get current filter parameters
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    // Redirect to Excel export route
    window.location.href = '{{ route("reports.production-supplies") }}?export=excel&' + params.toString();
}
</script>
@endpush
@endsection

