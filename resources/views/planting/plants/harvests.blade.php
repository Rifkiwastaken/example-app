@extends('layouts.app')

@section('title', 'Riwayat Panen - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="{{ route('plants.index') }}" class="btn btn-secondary me-3">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
        <div>
            <h4 class="mb-0">{{ $plant->name }}</h4>
            <small class="text-muted">Riwayat panen dari benih sumber tanaman ini</small>
        </div>
    </div>
</div>

<ul class="nav nav-tabs" role="tablist">
    @include('planting.plants._tabs', ['plant' => $plant, 'activeTab' => 'harvests'])
</ul>

<div class="tab-content p-3 bg-white border border-top-0 rounded-bottom">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Tanggal Panen</th>
                    <th>Benih Sumber</th>
                    <th>Lokasi / Lahan</th>
                    <th>No Lot Calon</th>
                    <th>Berat Kotor</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($harvests as $harvest)
                    @php
                        $location = $harvest->planting?->field?->plantingLocation;
                        $detailUrl = $location
                            ? route('planting-locations.plantings.history-reports', [$location, $harvest->planting])
                            : '#';
                    @endphp
                    <tr>
                        <td>{{ $harvest->harvested_at?->format('d M Y') ?: '-' }}</td>
                        <td>{{ $harvest->planting?->seedSource?->origin_lot_number ?: '-' }}</td>
                        <td>{{ $location?->name ?: '-' }} / {{ $harvest->planting?->field?->kode_lahan ?: '-' }}</td>
                        <td>{{ $harvest->candidate_lot_no ?: '-' }}</td>
                        <td>{{ $harvest->gross_weight_kg !== null ? number_format((float) $harvest->gross_weight_kg, 2) : '-' }}</td>
                        <td>
                            @if($location)
                                <a href="{{ $detailUrl }}" class="btn btn-sm btn-outline-info">Detail</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">Belum ada riwayat panen.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($harvests->hasPages())
        <div class="d-flex justify-content-center mt-3">{{ $harvests->links() }}</div>
    @endif
</div>
@endsection
