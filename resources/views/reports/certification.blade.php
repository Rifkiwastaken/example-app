@extends('layouts.app')

@section('title', 'Rekap Status Sertifikasi - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Rekap Status Sertifikasi</h4>
        <small class="text-muted">Performa kelulusan uji benih BPSB</small>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">Kembali</a>
</div>
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3" id="filterForm">
            <div class="col-md-2">
                <label class="form-label">Tahun</label>
                <select name="year" class="form-select">
                    <option value="">Semua</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            @include('reports.partials._variety-scope-filter')
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary">Filter</button></div>
        </form>
    </div>
</div>
<div class="table-responsive card">
    <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th>No. BPSB</th>
                <th>Uji ke</th>
                <th>Varietas</th>
                <th>Status</th>
                <th>Kelas benih</th>
                <th>Kesimpulan</th>
                <th>Qty tersertifikasi</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
                @php
                    $plant = $report->stock?->plant ?: $report->postHarvest?->planting?->seedSource?->variety;
                @endphp
                <tr>
                    <td>{{ $report->report_number_bpsb ?: '-' }}</td>
                    <td>{{ $report->uji_ke ?? '-' }}</td>
                    <td>{{ $plant?->variety ?: $plant?->name ?: '-' }}</td>
                    <td>{{ $report->certification_status ?: '-' }}</td>
                    <td>{{ $report->seed_class_requested ?: '-' }}</td>
                    <td>{{ $report->conclusion ?: '-' }}</td>
                    <td>{{ number_format((float) ($report->certified_seed_quantity ?? 0), 2) }}</td>
                    <td>{{ optional($report->report_date)->format('d M Y') ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">Belum ada laporan sertifikasi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $reports->links() }}</div>
@include('reports.partials._variety-scope-scripts')
@endsection
