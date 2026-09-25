@extends('layouts.app')

@section('title', 'Laporan Persebaran - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Laporan Persebaran</h4>
        <small class="text-muted">Peta sebaran benih yang sudah dijual berdasarkan koordinat GPS rencana tanam</small>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i class="fas fa-filter me-2"></i>Filter Data
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reports.distribution') }}" id="filterForm">
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
                @include('reports.partials._variety-scope-filter')
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('reports.distribution') }}" class="btn btn-secondary">
                        <i class="fas fa-redo me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-title text-muted">Titik sebaran</h6>
                <h3 class="mb-0">{{ number_format(count($points)) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-title text-muted">Jumlah struk</h6>
                <h3 class="mb-0">{{ number_format($receiptCount) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-title text-muted">Volume terjual</h6>
                <h3 class="mb-0">{{ number_format($totalQty, 2, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h6 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i>Peta sebaran penjualan</h6>
    </div>
    <div class="card-body">
        <div id="distribution-map" style="height: 520px;" class="rounded border"></div>
        @if(count($points) === 0)
            <p class="text-muted small mt-3 mb-0">Belum ada penjualan dengan koordinat GPS yang dapat ditampilkan.</p>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-table me-2"></i>Rincian sebaran</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>No. Struk</th>
                        <th>Pembeli</th>
                        <th>Instansi</th>
                        <th>Tanaman-varietas</th>
                        <th>Nama lahan</th>
                        <th>GPS</th>
                        <th>Jumlah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->sale_date?->format('d M Y') ?: '-' }}</td>
                            <td><code>{{ $item->receipt_number }}</code></td>
                            <td>{{ $item->buyer_name ?: '-' }}</td>
                            <td>{{ $item->displayOrganization() ?: '-' }}</td>
                            <td>{{ $item->displayPlantName() }}</td>
                            <td>{{ $item->planned_location_name ?: '-' }}</td>
                            <td>{{ $item->planned_gps ?: '-' }}</td>
                            <td>{{ number_format((float) $item->quantity, 2) }} {{ $item->unit }}</td>
                            <td>
                                <a href="{{ route('sales.show', $item) }}" class="btn btn-sm btn-outline-info">Lihat struk</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                Tidak ada data sebaran ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const points = @json($points);
    const mapEl = document.getElementById('distribution-map');
    if (!mapEl || typeof L === 'undefined') return;

    const map = L.map(mapEl).setView([-0.95, 100.35], 8);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(map);

    const bounds = [];
    points.forEach(function (p) {
        const radius = Math.max(6, Math.min(25, (p.qty || 1) / 20));
        const marker = L.circleMarker([p.lat, p.lng], {
            radius: radius,
            color: '#0ea5e9',
            fillColor: '#0284c7',
            fillOpacity: 0.55
        }).addTo(map);

        const popup = [
            '<strong>' + (p.plant || 'Benih terjual') + '</strong>',
            (p.qty || 0) + ' ' + (p.unit || 'kg'),
            'Pembeli: ' + (p.buyer || '-'),
            'Instansi: ' + (p.organization || '-'),
            'Lahan: ' + (p.location || '-'),
            'Struk: <a href="' + p.url + '">' + (p.receipt || '-') + '</a>',
            p.date ? ('Tanggal: ' + p.date) : ''
        ].filter(Boolean).join('<br>');
        marker.bindPopup(popup);
        bounds.push([p.lat, p.lng]);
    });

    if (bounds.length === 1) {
        map.setView(bounds[0], 12);
    } else if (bounds.length > 1) {
        map.fitBounds(bounds, { padding: [28, 28] });
    }
});
</script>
@endpush
@include('reports.partials._variety-scope-scripts')
@endsection
