@extends('layouts.app')

@section('title', 'Detail Lahan ' . $field->kode_lahan . ' - ' . $plantingLocation->name . ' - SIBESTI')

@php
    $polygonPoints = $field->polygonPoints();
    $gpsParts = $field->koordinat_gps ? array_map('trim', explode(',', $field->koordinat_gps)) : [];
    $gpsLat = isset($gpsParts[0]) && is_numeric($gpsParts[0]) ? (float) $gpsParts[0] : null;
    $gpsLng = isset($gpsParts[1]) && is_numeric($gpsParts[1]) ? (float) $gpsParts[1] : null;
    $defaultLat = $gpsLat ?? ($polygonPoints[0]['lat'] ?? -0.947083);
    $defaultLng = $gpsLng ?? ($polygonPoints[0]['lng'] ?? 100.416644);
    $badge = match ($field->status_lahan) {
        'Digunakan' => 'bg-success',
        'Bera' => 'bg-warning text-dark',
        'Persiapan' => 'bg-info',
        default => 'bg-secondary',
    };
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $plantingLocation->name }}</h4>
        <small class="text-muted">Detail lahan {{ $field->kode_lahan }}</small>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->isAdmin() || auth()->user()->canManagePlantingLocation($plantingLocation))
            <a href="{{ route('planting-locations.fields.edit', [$plantingLocation, $field]) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
        @endif
        <a href="{{ route('planting-locations.fields.index', $plantingLocation) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<ul class="nav nav-tabs" role="tablist">
    @include('planting.planting-locations._tabs', ['plantingLocation' => $plantingLocation, 'activeTab' => 'fields'])
</ul>

<div class="tab-content p-3 bg-white border border-top-0 rounded-bottom">
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Lahan</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>Kode Lahan:</strong><br>
                        <span>{{ $field->kode_lahan }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Luas Lahan:</strong><br>
                        <span>{{ number_format((float) $field->luas_ha, 2, ',', '.') }} Ha</span>
                    </div>
                    <div class="mb-3">
                        <strong>Dimensi Lahan:</strong><br>
                        @if($field->panjang_m || $field->lebar_m)
                            <span>
                                {{ $field->panjang_m ? number_format((float) $field->panjang_m, 2, ',', '.').' m' : '-' }}
                                &times;
                                {{ $field->lebar_m ? number_format((float) $field->lebar_m, 2, ',', '.').' m' : '-' }}
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                    <div class="mb-0">
                        <strong>Status Lahan:</strong><br>
                        <span class="badge {{ $badge }}">{{ $field->statusLabel() }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>Koordinat GPS (titik tengah):</strong><br>
                        @if($field->koordinat_gps)
                            <a href="https://www.google.com/maps?q={{ urlencode($field->koordinat_gps) }}" target="_blank" rel="noopener">
                                {{ $field->koordinat_gps }}
                                <i class="fas fa-external-link-alt ms-1 small"></i>
                            </a>
                        @else
                            <span class="text-muted">Belum diisi</span>
                        @endif
                    </div>
                    <div class="mb-0">
                        <strong>Lokasi Penanaman:</strong><br>
                        <span>{{ $plantingLocation->name }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-draw-polygon me-2"></i>Peta Lahan</h6>
        </div>
        <div class="card-body">
            @if($field->hasPolygon())
                <div class="row">
                    <div class="col-md-4">
                        <p class="text-muted mb-2">Empat titik batas petak:</p>
                        <ol class="mb-3 ps-3">
                            @foreach($polygonPoints as $index => $point)
                                <li class="mb-1">
                                    Titik {{ $index + 1 }}:
                                    <code>{{ number_format($point['lat'], 6, '.', '') }},{{ number_format($point['lng'], 6, '.', '') }}</code>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                    <div class="col-md-8">
                        <div id="field-detail-map" style="height: 420px; width: 100%; border-radius: 8px; z-index: 1;"></div>
                    </div>
                </div>
            @else
                <p class="text-muted mb-0">Peta lahan belum dipetakan. Edit lahan ini untuk menandai 4 titik batas petak.</p>
                @if($field->koordinat_gps)
                    <div id="field-detail-map" class="mt-3" style="height: 360px; width: 100%; border-radius: 8px; z-index: 1;"></div>
                @endif
            @endif
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(function () {
    const mapEl = document.getElementById('field-detail-map');
    if (!mapEl) {
        return;
    }

    const defaultLat = {{ $defaultLat }};
    const defaultLng = {{ $defaultLng }};
    const gpsLat = @json($gpsLat);
    const gpsLng = @json($gpsLng);
    const polygonPoints = @json($polygonPoints);

    const map = L.map('field-detail-map').setView([defaultLat, defaultLng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const bounds = [];

    if (gpsLat !== null && gpsLng !== null) {
        const center = L.marker([gpsLat, gpsLng]).addTo(map);
        center.bindPopup('Titik tengah: ' + gpsLat + ',' + gpsLng);
        bounds.push([gpsLat, gpsLng]);
    }

    if (polygonPoints.length === 4) {
        const latLngs = polygonPoints.map(function (point) {
            return [point.lat, point.lng];
        });
        L.polygon(latLngs, {
            color: '#198754',
            weight: 2,
            fillColor: '#198754',
            fillOpacity: 0.25
        }).addTo(map);

        polygonPoints.forEach(function (point, index) {
            L.marker([point.lat, point.lng], {
                icon: L.divIcon({
                    className: 'field-vertex-icon',
                    html: '<span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:#0d6efd;color:#fff;font-size:12px;font-weight:700;box-shadow:0 0 0 2px #fff;">' + (index + 1) + '</span>',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                })
            }).addTo(map).bindPopup('Titik ' + (index + 1));
            bounds.push([point.lat, point.lng]);
        });
    }

    if (bounds.length > 1) {
        map.fitBounds(bounds, { padding: [24, 24] });
    }

    setTimeout(function () {
        map.invalidateSize();
    }, 200);
})();
</script>
@endpush
@endsection
