@php
    $field = $field ?? null;
    $gpsValue = old('koordinat_gps', $field->koordinat_gps ?? '');
    $oldPolygon = old('peta_lahan', $field->peta_lahan ?? null);
    $polygonValue = is_array($oldPolygon) ? json_encode($oldPolygon) : ($oldPolygon ?: '');
    $defaultLat = -0.947083;
    $defaultLng = 100.416644;
    if ($plantingLocation->google_maps_link && preg_match('/(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)/', $plantingLocation->google_maps_link, $mapsMatch)) {
        $defaultLat = (float) $mapsMatch[1];
        $defaultLng = (float) $mapsMatch[2];
    }
@endphp

<div class="mb-3">
    <label class="form-label">Kode Lahan <span class="text-danger">*</span></label>
    <input type="text" name="kode_lahan" maxlength="20"
           class="form-control @error('kode_lahan') is-invalid @enderror"
           value="{{ old('kode_lahan', $field->kode_lahan ?? '') }}"
           placeholder="Contoh: BLOK-A1, BLOK-B2" required>
    @error('kode_lahan')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <div class="form-text">Kode unik petak lahan di lokasi ini.</div>
</div>

<div class="mb-3">
    <label class="form-label">Luas Lahan (Ha) <span class="text-danger">*</span></label>
    <div class="input-group">
        <input type="number" name="luas_ha" step="0.01" min="0.01" max="999.99"
               class="form-control @error('luas_ha') is-invalid @enderror"
               value="{{ old('luas_ha', $field->luas_ha ?? '') }}" placeholder="0.00" required>
        <span class="input-group-text">Ha</span>
        @error('luas_ha')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Panjang (m)</label>
            <div class="input-group">
                <input type="number" name="panjang_m" step="0.01" min="0"
                       class="form-control @error('panjang_m') is-invalid @enderror"
                       value="{{ old('panjang_m', $field->panjang_m ?? '') }}" placeholder="0.00">
                <span class="input-group-text">m</span>
                @error('panjang_m')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Lebar (m)</label>
            <div class="input-group">
                <input type="number" name="lebar_m" step="0.01" min="0"
                       class="form-control @error('lebar_m') is-invalid @enderror"
                       value="{{ old('lebar_m', $field->lebar_m ?? '') }}" placeholder="0.00">
                <span class="input-group-text">m</span>
                @error('lebar_m')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Status Lahan <span class="text-danger">*</span></label>
    <select name="status_lahan" class="form-select @error('status_lahan') is-invalid @enderror" required>
        <option value="">Pilih status lahan</option>
        @foreach(\App\Models\PlantingField::statusOptions() as $value => $label)
            <option value="{{ $value }}" {{ old('status_lahan', $field->status_lahan ?? '') === $value ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>
    @error('status_lahan')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Deskripsi</label>
    <textarea name="description" rows="3" class="form-control">{{ old('description', $field->description ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">Lampiran</label>
    <input type="file" name="file" class="form-control">
    @if(!empty($field?->file_path))
        <small class="text-muted d-block mt-1">File tersimpan: {{ $field->file_name ?: basename($field->file_path) }}</small>
    @endif
</div>

<div class="mb-4">
    <label class="form-label">Koordinat GPS</label>
    <div class="input-group mb-2">
        <input type="text" name="koordinat_gps" id="koordinat_gps" maxlength="100"
               class="form-control @error('koordinat_gps') is-invalid @enderror"
               value="{{ $gpsValue }}"
               placeholder="Contoh: -0.9471,100.4172">
        <button type="button" class="btn btn-outline-primary" id="btnUseDeviceGps">
            <i class="fas fa-location-arrow me-1"></i>Lokasi perangkat
        </button>
        <button type="button" class="btn btn-outline-secondary" id="btnClearGps">
            <i class="fas fa-times"></i>
        </button>
        @error('koordinat_gps')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-text mb-2">Klik peta untuk menandai titik tengah petak lahan, atau gunakan lokasi perangkat.</div>
    <div id="field-map" style="height: 360px; width: 100%; border-radius: 8px; z-index: 1;"></div>
    <div id="gps-status" class="small text-muted mt-2"></div>
</div>

<div class="mb-4">
    <label class="form-label">Peta Lahan</label>
    <input type="hidden" name="peta_lahan" id="peta_lahan" value="{{ $polygonValue }}">
    <div class="d-flex flex-wrap gap-2 mb-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnUndoPolygon">
            <i class="fas fa-undo me-1"></i>Hapus titik terakhir
        </button>
        <button type="button" class="btn btn-outline-danger btn-sm" id="btnClearPolygon">
            <i class="fas fa-times me-1"></i>Reset peta
        </button>
    </div>
    @error('peta_lahan')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
    <div class="form-text mb-2">Klik 4 titik di peta untuk membatasi petak lahan. Titik dapat digeser setelah ditandai.</div>
    <div id="field-polygon-map" style="height: 380px; width: 100%; border-radius: 8px; z-index: 1;"></div>
    <div id="polygon-status" class="small text-muted mt-2">Belum ada titik. Tandai 4 sudut lahan.</div>
</div>

<style>
    .field-vertex-icon {
        background: transparent;
        border: 0;
    }
    .field-vertex-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #0d6efd;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        box-shadow: 0 0 0 2px #fff;
    }
</style>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(function () {
    const input = document.getElementById('koordinat_gps');
    const statusEl = document.getElementById('gps-status');
    const polygonInput = document.getElementById('peta_lahan');
    const polygonStatus = document.getElementById('polygon-status');
    const defaultLat = {{ $defaultLat }};
    const defaultLng = {{ $defaultLng }};
    let marker = null;
    let vertices = [];
    let polygonLayer = null;

    function parseGps(value) {
        if (!value) return null;
        const parts = value.split(',');
        if (parts.length < 2) return null;
        const lat = parseFloat(parts[0].trim());
        const lng = parseFloat(parts[1].trim());
        if (Number.isNaN(lat) || Number.isNaN(lng)) return null;
        if (lat < -90 || lat > 90 || lng < -180 || lng > 180) return null;
        return { lat, lng };
    }

    function formatGps(lat, lng) {
        return lat.toFixed(6) + ',' + lng.toFixed(6);
    }

    function osmLayer() {
        return L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        });
    }

    function setMarker(lat, lng, pan) {
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            marker.on('dragend', function () {
                const pos = marker.getLatLng();
                input.value = formatGps(pos.lat, pos.lng);
                statusEl.textContent = 'Titik lahan: ' + input.value;
            });
        }
        if (pan) {
            map.setView([lat, lng], Math.max(map.getZoom(), 16));
        }
        input.value = formatGps(lat, lng);
        statusEl.textContent = 'Titik lahan: ' + input.value;
        if (vertices.length === 0 && polygonMap) {
            polygonMap.setView([lat, lng], Math.max(polygonMap.getZoom(), 16));
        }
    }

    const initial = parseGps(input.value);
    const map = L.map('field-map').setView(
        [initial ? initial.lat : defaultLat, initial ? initial.lng : defaultLng],
        initial ? 16 : 13
    );
    osmLayer().addTo(map);

    const polygonMap = L.map('field-polygon-map').setView(
        [initial ? initial.lat : defaultLat, initial ? initial.lng : defaultLng],
        initial ? 16 : 13
    );
    osmLayer().addTo(polygonMap);

    if (initial) {
        setMarker(initial.lat, initial.lng, false);
    }

    map.on('click', function (e) {
        setMarker(e.latlng.lat, e.latlng.lng, false);
    });

    input.addEventListener('change', function () {
        const parsed = parseGps(input.value);
        if (parsed) {
            setMarker(parsed.lat, parsed.lng, true);
        }
    });

    document.getElementById('btnUseDeviceGps').addEventListener('click', function () {
        if (!navigator.geolocation) {
            statusEl.textContent = 'Peramban tidak mendukung GPS.';
            return;
        }
        statusEl.textContent = 'Mengambil lokasi perangkat...';
        navigator.geolocation.getCurrentPosition(function (pos) {
            setMarker(pos.coords.latitude, pos.coords.longitude, true);
        }, function () {
            statusEl.textContent = 'Tidak dapat mengambil lokasi perangkat. Izinkan akses lokasi, lalu coba lagi.';
        }, { enableHighAccuracy: true, timeout: 10000 });
    });

    document.getElementById('btnClearGps').addEventListener('click', function () {
        input.value = '';
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }
        statusEl.textContent = 'Koordinat GPS dikosongkan.';
    });

    function numberedIcon(n) {
        return L.divIcon({
            className: 'field-vertex-icon',
            html: '<span class="field-vertex-badge">' + n + '</span>',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });
    }

    function syncPolygonInput() {
        const data = vertices.map(function (vertex) {
            return {
                lat: Number(vertex.marker.getLatLng().lat.toFixed(6)),
                lng: Number(vertex.marker.getLatLng().lng.toFixed(6))
            };
        });
        polygonInput.value = data.length ? JSON.stringify(data) : '';
        if (data.length === 0) {
            polygonStatus.textContent = 'Belum ada titik. Tandai 4 sudut lahan.';
        } else if (data.length < 4) {
            polygonStatus.textContent = 'Titik ' + data.length + ' dari 4. Klik peta untuk menambah sudut.';
        } else {
            polygonStatus.textContent = 'Batas lahan lengkap (4 titik). Geser titik untuk menyesuaikan.';
        }
    }

    function redrawPolygon() {
        if (polygonLayer) {
            polygonMap.removeLayer(polygonLayer);
            polygonLayer = null;
        }
        if (vertices.length < 3) {
            return;
        }
        polygonLayer = L.polygon(vertices.map(function (vertex) {
            return vertex.marker.getLatLng();
        }), {
            color: '#198754',
            weight: 2,
            fillColor: '#198754',
            fillOpacity: 0.25
        }).addTo(polygonMap);
    }

    function addVertex(lat, lng) {
        if (vertices.length >= 4) {
            return;
        }
        const markerVertex = L.marker([lat, lng], {
            draggable: true,
            icon: numberedIcon(vertices.length + 1)
        }).addTo(polygonMap);

        markerVertex.on('drag', redrawPolygon);
        markerVertex.on('dragend', syncPolygonInput);

        vertices.push({ marker: markerVertex });
        redrawPolygon();
        syncPolygonInput();
    }

    function removeLastVertex() {
        const last = vertices.pop();
        if (!last) {
            return;
        }
        polygonMap.removeLayer(last.marker);
        redrawPolygon();
        syncPolygonInput();
    }

    function clearPolygon() {
        vertices.forEach(function (vertex) {
            polygonMap.removeLayer(vertex.marker);
        });
        vertices = [];
        redrawPolygon();
        syncPolygonInput();
    }

    polygonMap.on('click', function (e) {
        addVertex(e.latlng.lat, e.latlng.lng);
    });

    document.getElementById('btnUndoPolygon').addEventListener('click', removeLastVertex);
    document.getElementById('btnClearPolygon').addEventListener('click', clearPolygon);

    try {
        const saved = JSON.parse(polygonInput.value || '[]');
        if (Array.isArray(saved)) {
            saved.forEach(function (point) {
                const lat = parseFloat(point.lat ?? point[0]);
                const lng = parseFloat(point.lng ?? point[1]);
                if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
                    addVertex(lat, lng);
                }
            });
            if (vertices.length) {
                const bounds = L.latLngBounds(vertices.map(function (vertex) {
                    return vertex.marker.getLatLng();
                }));
                polygonMap.fitBounds(bounds.pad(0.2));
            }
        }
    } catch (err) {}

    const form = polygonInput.closest('form');
    if (form) {
        form.addEventListener('submit', function (event) {
            if (vertices.length > 0 && vertices.length < 4) {
                event.preventDefault();
                polygonStatus.textContent = 'Peta lahan harus terdiri dari tepat 4 titik, atau dikosongkan.';
                polygonStatus.classList.add('text-danger');
            }
        });
    }

    setTimeout(function () {
        map.invalidateSize();
        polygonMap.invalidateSize();
    }, 200);
})();
</script>
@endpush
