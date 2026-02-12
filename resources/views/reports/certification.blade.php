@extends('layouts.app')

@section('title', 'Rekap Status Sertifikasi - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Rekap Status Sertifikasi</h4>
        <small class="text-muted">Melihat performa kelulusan uji benih</small>
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
        <form method="GET" action="{{ route('reports.certification') }}" id="filterForm">
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
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('reports.certification') }}" class="btn btn-secondary">
                        <i class="fas fa-redo me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
@if($certifications->count() > 0)
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title">Total Sertifikasi</h6>
                <h3 class="mb-0">{{ $certifications->total() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="card-title">Lulus</h6>
                <h3 class="mb-0">{{ $certifications->where('certification_status', 'lulus')->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6 class="card-title">Tidak Lulus</h6>
                <h3 class="mb-0">{{ $certifications->where('certification_status', 'tidak_lulus')->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="card-title">Dalam Proses</h6>
                <h3 class="mb-0">{{ $certifications->where('certification_status', 'dalam_proses')->count() }}</h3>
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
                        <th>Lokasi Lahan</th>
                        <th>Kelas Benih Diminta</th>
                        <th>Status Sertifikasi</th>
                        <th>Tanggal Laporan Terakhir</th>
                        <th>Kesimpulan Terakhir</th>
                        <th>Jumlah Laporan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($certifications as $index => $certification)
                        @php
                            $latestReport = $certification->reports->first();
                            $plant = $certification->plant ?? ($certification->harvest->plant ?? null);
                        @endphp
                        <tr>
                            <td>{{ $certifications->firstItem() + $index }}</td>
                            <td>
                                @if($plant)
                                    <strong>{{ $plant->name }}</strong>
                                    @if($plant->variety)
                                        <br><small class="text-muted">{{ $plant->variety }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($certification->plantingLocation)
                                    {{ $certification->plantingLocation->name }}
                                @elseif($certification->harvest && $certification->harvest->location)
                                    {{ $certification->harvest->location->name }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($certification->seed_class_requested)
                                    <span class="badge bg-info">{{ $certification->seed_class_requested }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($certification->certification_status === 'lulus')
                                    <span class="badge bg-success">{{ $certification->status_label }}</span>
                                @elseif($certification->certification_status === 'tidak_lulus')
                                    <span class="badge bg-danger">{{ $certification->status_label }}</span>
                                @elseif($certification->certification_status === 'dalam_proses')
                                    <span class="badge bg-warning">{{ $certification->status_label }}</span>
                                @elseif($certification->certification_status === 'selesai')
                                    <span class="badge bg-secondary">{{ $certification->status_label }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ $certification->certification_status }}</span>
                                @endif
                            </td>
                            <td>
                                @if($latestReport && $latestReport->report_date)
                                    {{ $latestReport->report_date->format('d M Y') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($latestReport && $latestReport->conclusion)
                                    @if($latestReport->conclusion === 'LULUS')
                                        <span class="badge bg-success">{{ $latestReport->conclusion }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ $latestReport->conclusion }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $certification->reports->count() }}</span>
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
            </table>
        </div>

        @if($certifications->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $certifications->links() }}
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
    window.location.href = '{{ route("reports.certification") }}?export=pdf&' + params.toString();
}

function exportExcel() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    window.location.href = '{{ route("reports.certification") }}?export=excel&' + params.toString();
}
</script>
@endpush
@endsection

