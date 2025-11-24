@extends('layouts.app')

@section('title', 'Laporan Realisasi Tanam & Panen - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Laporan Realisasi Tanam & Panen</h4>
        <small class="text-muted">Membandingkan rencana (target) dengan realisasi lapangan</small>
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
        <form method="GET" action="{{ route('reports.planting-harvest') }}" id="filterForm">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tahun Anggaran</label>
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
                                {{ $plant->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Lokasi Lahan</label>
                    <select name="planting_location_id" class="form-select">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('planting_location_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('reports.planting-harvest') }}" class="btn btn-secondary">
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
                        <th>Varietas</th>
                        <th>Lokasi Tanam</th>
                        <th>Tanggal Tanam</th>
                        <th>Luas Tanam (Ha)</th>
                        <th>Tanggal Panen</th>
                        <th>Hasil Panen (Ton)</th>
                        <th>Produktivitas (Ton/Ha)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plantings as $index => $planting)
                        @php
                            $harvest = $planting->harvest;
                            $area = $planting->location->map_size ?? 0;
                        @endphp
                        <tr>
                            <td>{{ $plantings->firstItem() + $index }}</td>
                            <td>
                                <strong>{{ $planting->plant->name }}</strong><br>
                                <small class="text-muted">{{ $planting->plant->variety ?: '-' }}</small>
                            </td>
                            <td>{{ $planting->location->name ?? '-' }}</td>
                            <td>{{ $planting->planted_at ? $planting->planted_at->format('d M Y') : '-' }}</td>
                            <td>{{ $area > 0 ? number_format($area, 2) : '-' }}</td>
                            <td>{{ $harvest && $harvest->harvested_at ? $harvest->harvested_at->format('d M Y') : '-' }}</td>
                            <td>
                                @if($harvest && $harvest->quantity > 0)
                                    @php
                                        $unit = strtolower($harvest->unit ?? 'kg');
                                        $factors = ['kg' => 0.001, 'kilogram' => 0.001, 'gram' => 0.000001, 'ton' => 1, 'kuintal' => 0.1];
                                        $harvestInTon = $harvest->quantity * ($factors[$unit] ?? 1);
                                    @endphp
                                    {{ number_format($harvestInTon, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($planting->productivity > 0)
                                    {{ number_format($planting->productivity, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($planting->status === 'Berhasil')
                                    <span class="badge bg-success">Berhasil</span>
                                @elseif($planting->status === 'Gagal')
                                    <span class="badge bg-danger">Gagal</span>
                                @else
                                    <span class="badge bg-secondary">{{ $planting->status }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                Tidak ada data ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($plantings->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $plantings->links() }}
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
    window.location.href = '{{ route("reports.planting-harvest") }}?export=pdf&' + params.toString();
}

function exportExcel() {
    // Get current filter parameters
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    // Redirect to Excel export route
    window.location.href = '{{ route("reports.planting-harvest") }}?export=excel&' + params.toString();
}
</script>
@endpush
@endsection

