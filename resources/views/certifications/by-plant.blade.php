@extends('layouts.app')

@section('title', 'Kelola Sertifikasi - '.$plant->name.' - SIBESTI')

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('certifications.index') }}">Manajemen Sertifikasi</a></li>
        <li class="breadcrumb-item active">{{ $plant->name }}</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Sertifikasi: {{ $plant->name }}</h4>
        <small class="text-muted">{{ $plant->type?->name ?: '-' }}{{ $plant->variety ? ' · '.$plant->variety : '' }}</small>
    </div>
    <a href="{{ route('certifications.index') }}" class="btn btn-secondary">Kembali</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Lokasi penanaman</label>
                <select name="location_filter" class="form-select">
                    <option value="">Semua lokasi</option>
                    @foreach($allPlantingLocations as $location)
                        <option value="{{ $location->getKey() }}" {{ ($locationFilter ?? '') == $location->getKey() ? 'selected' : '' }}>{{ $location->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status sertifikasi</label>
                <select name="status_filter" class="form-select">
                    <option value="">Semua status</option>
                    <option value="proses_lab" {{ ($statusFilter ?? '') == 'proses_lab' ? 'selected' : '' }}>Proses lab</option>
                    <option value="dalam_proses" {{ ($statusFilter ?? '') == 'dalam_proses' ? 'selected' : '' }}>Proses lab</option>
                    <option value="lulus" {{ ($statusFilter ?? '') == 'lulus' ? 'selected' : '' }}>Lulus</option>
                    <option value="tidak_lulus" {{ ($statusFilter ?? '') == 'tidak_lulus' ? 'selected' : '' }}>Gagal</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button class="btn btn-primary">Filter</button>
                <a href="{{ route('certifications.by-plant', $plant) }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

@php
    $statusBadge = function ($status) {
        return match ($status) {
            'lulus' => 'bg-success',
            'tidak_lulus', 'gagal' => 'bg-danger',
            'proses_lab', 'dalam_proses' => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    };
@endphp

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Benih siap disertifikasi ({{ $uncertifiedPostHarvests->count() + $pendingReports->count() }})</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal sertifikasi</th>
                        <th>No sertifikat</th>
                        <th>No lot penyimpanan</th>
                        <th>Asal lokasi penanaman</th>
                        <th>Uji ke</th>
                        <th>Status sertifikasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($uncertifiedPostHarvests as $item)
                        <tr>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>{{ $item->planting?->field?->plantingLocation?->name ?: '-' }}</td>
                            <td>1</td>
                            <td><span class="badge bg-secondary">Belum diuji</span></td>
                            <td>
                                <a href="{{ route('certifications.create', ['post_harvest_id' => $item->getKey(), 'plant_id' => $plant->getKey()]) }}" class="btn btn-sm btn-success">Input hasil uji lab sertifikasi</a>
                            </td>
                        </tr>
                    @endforeach
                    @foreach($pendingReports as $report)
                        <tr>
                            <td>{{ $report->report_date?->format('d M Y') ?: '-' }}</td>
                            <td>{{ $report->report_number_bpsb ?: '-' }}</td>
                            <td>{{ $report->stock?->no_label_resmi ?: '-' }}</td>
                            <td>{{ $report->planting_location?->name ?: '-' }}</td>
                            <td>{{ $report->uji_ke ?: 1 }}</td>
                            <td><span class="badge {{ $statusBadge($report->certification_status) }}">{{ $report->status_label }}</span></td>
                            <td>
                                <a href="{{ route('certifications.create', ['report_id' => $report->getKey(), 'post_harvest_id' => $report->planting_post_harvest_id, 'plant_id' => $plant->getKey()]) }}" class="btn btn-sm btn-success">Input hasil uji lab sertifikasi</a>
                            </td>
                        </tr>
                    @endforeach
                    @if($uncertifiedPostHarvests->isEmpty() && $pendingReports->isEmpty())
                        <tr><td colspan="7" class="text-center text-muted">Tidak ada benih yang menunggu uji lab.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Riwayat sertifikasi ({{ $allReports->count() }})</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal sertifikasi</th>
                        <th>No sertifikat</th>
                        <th>No lot penyimpanan</th>
                        <th>Asal lokasi penanaman</th>
                        <th>Uji ke</th>
                        <th>Status sertifikasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allReports as $report)
                        <tr>
                            <td>{{ $report->report_date?->format('d M Y') ?: '-' }}</td>
                            <td>{{ $report->report_number_bpsb ?: '-' }}</td>
                            <td>{{ $report->stock?->no_label_resmi ?: '-' }}</td>
                            <td>{{ $report->planting_location?->name ?: '-' }}</td>
                            <td>{{ $report->uji_ke ?: 1 }}</td>
                            <td><span class="badge {{ $statusBadge($report->certification_status) }}">{{ $report->status_label }}</span></td>
                            <td>
                                @if($report->scan_file_path)
                                    <a href="{{ asset('storage/'.$report->scan_file_path) }}" target="_blank" class="btn btn-sm btn-outline-info">Lihat dokumen BPSB</a>
                                @endif
                                <a href="{{ route('certifications.reports.show', $report) }}" class="btn btn-sm btn-primary">Lihat detail sertifikasi</a>
                                <a href="{{ route('certifications.create', ['report_id' => $report->getKey(), 'post_harvest_id' => $report->planting_post_harvest_id, 'plant_id' => $plant->getKey()]) }}" class="btn btn-sm btn-warning">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Belum ada riwayat sertifikasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
