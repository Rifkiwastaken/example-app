@php
    $gpsValue = old('koordinat_gps', isset($plantingLocation) ? $plantingLocation->koordinat_gps : '');
    $defaultLat = -0.947083;
    $defaultLng = 100.416644;
    $sourceLink = old('google_maps_link', isset($plantingLocation) ? $plantingLocation->google_maps_link : '');
    if ($sourceLink && preg_match('/(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)/', $sourceLink, $mapsMatch)) {
        $defaultLat = (float) $mapsMatch[1];
        $defaultLng = (float) $mapsMatch[2];
    }
    if ($gpsValue && preg_match('/(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)/', $gpsValue, $gpsMatch)) {
        $defaultLat = (float) $gpsMatch[1];
        $defaultLng = (float) $gpsMatch[2];
    }
@endphp

<div class="mb-4">
    <label class="form-label">Koordinat lokasi lahan</label>
    <div class="input-group mb-2">
        <input type="text" name="koordinat_gps" id="location_koordinat_gps" maxlength="100"
               class="form-control @error('koordinat_gps') is-invalid @enderror"
               value="{{ $gpsValue }}"
               placeholder="Contoh: -0.9471,100.4172">
        <button type="button" class="btn btn-outline-primary" id="btnLocationDeviceGps">
            <i class="fas fa-location-arrow me-1"></i>Lokasi perangkat
        </button>
        <button type="button" class="btn btn-outline-secondary" id="btnLocationClearGps">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @error('koordinat_gps')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    <div class="form-text mb-2">Tandai koordinat lokasi penanaman melalui GPS perangkat atau klik peta.</div>
    <div id="location-gps-map" style="height: 320px; width: 100%; border-radius: 8px; z-index: 1;"></div>
    <div id="location-gps-status" class="small text-muted mt-2"></div>
</div>
