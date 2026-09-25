@php
    $defaultLat = $defaultLat ?? -0.947083;
    $defaultLng = $defaultLng ?? 100.416644;
@endphp
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
    .field-vertex-icon { background: transparent; border: 0; }
    .field-vertex-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 24px; height: 24px; border-radius: 50%;
        background: #0d6efd; color: #fff; font-size: 12px; font-weight: 700;
        box-shadow: 0 0 0 2px #fff;
    }
</style>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(function () {
    const defaultLat = {{ $defaultLat ?? -0.947083 }};
    const defaultLng = {{ $defaultLng ?? 100.416644 }};

    function parseGps(value) {
        if (!value) return null;
        const parts = String(value).split(',');
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

    function numberedIcon(n) {
        return L.divIcon({
            className: 'field-vertex-icon',
            html: '<span class="field-vertex-badge">' + n + '</span>',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });
    }

    function initLocationGps() {
        const input = document.getElementById('location_koordinat_gps');
        const mapEl = document.getElementById('location-gps-map');
        const statusEl = document.getElementById('location-gps-status');
        if (!input || !mapEl || typeof L === 'undefined') return;

        const initial = parseGps(input.value);
        const map = L.map(mapEl).setView(
            [initial ? initial.lat : defaultLat, initial ? initial.lng : defaultLng],
            initial ? 16 : 13
        );
        osmLayer().addTo(map);
        let marker = null;

        function setMarker(lat, lng, pan) {
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                marker.on('dragend', function () {
                    const pos = marker.getLatLng();
                    input.value = formatGps(pos.lat, pos.lng);
                    statusEl.textContent = 'Koordinat: ' + input.value;
                });
            }
            if (pan) {
                map.setView([lat, lng], Math.max(map.getZoom(), 16));
            }
            input.value = formatGps(lat, lng);
            statusEl.textContent = 'Koordinat: ' + input.value;
        }

        if (initial) setMarker(initial.lat, initial.lng, false);
        map.on('click', function (e) { setMarker(e.latlng.lat, e.latlng.lng, false); });
        input.addEventListener('change', function () {
            const parsed = parseGps(input.value);
            if (parsed) setMarker(parsed.lat, parsed.lng, true);
        });
        document.getElementById('btnLocationDeviceGps')?.addEventListener('click', function () {
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
        document.getElementById('btnLocationClearGps')?.addEventListener('click', function () {
            input.value = '';
            if (marker) {
                map.removeLayer(marker);
                marker = null;
            }
            statusEl.textContent = 'Koordinat dikosongkan.';
        });
        setTimeout(function () { map.invalidateSize(); }, 250);
    }

    function initNestedLahanCard(card) {
        if (!card || card.dataset.mapsReady === '1' || typeof L === 'undefined') return;
        card.dataset.mapsReady = '1';

        const input = card.querySelector('.nested-gps-input');
        const mapEl = card.querySelector('.nested-gps-map');
        const statusEl = card.querySelector('.nested-gps-status');
        const polygonInput = card.querySelector('.nested-peta-input');
        const polygonMapEl = card.querySelector('.nested-polygon-map');
        const polygonStatus = card.querySelector('.nested-polygon-status');
        if (!input || !mapEl || !polygonInput || !polygonMapEl) return;

        const initial = parseGps(input.value);
        let marker = null;
        let vertices = [];
        let polygonLayer = null;

        const map = L.map(mapEl).setView(
            [initial ? initial.lat : defaultLat, initial ? initial.lng : defaultLng],
            initial ? 16 : 13
        );
        osmLayer().addTo(map);
        const polygonMap = L.map(polygonMapEl).setView(
            [initial ? initial.lat : defaultLat, initial ? initial.lng : defaultLng],
            initial ? 16 : 13
        );
        osmLayer().addTo(polygonMap);

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
            if (pan) map.setView([lat, lng], Math.max(map.getZoom(), 16));
            input.value = formatGps(lat, lng);
            statusEl.textContent = 'Titik lahan: ' + input.value;
            if (vertices.length === 0) {
                polygonMap.setView([lat, lng], Math.max(polygonMap.getZoom(), 16));
            }
        }

        if (initial) setMarker(initial.lat, initial.lng, false);
        map.on('click', function (e) { setMarker(e.latlng.lat, e.latlng.lng, false); });
        input.addEventListener('change', function () {
            const parsed = parseGps(input.value);
            if (parsed) setMarker(parsed.lat, parsed.lng, true);
        });
        card.querySelector('.btn-nested-device-gps')?.addEventListener('click', function () {
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
        card.querySelector('.btn-nested-clear-gps')?.addEventListener('click', function () {
            input.value = '';
            if (marker) {
                map.removeLayer(marker);
                marker = null;
            }
            statusEl.textContent = 'Koordinat GPS dikosongkan.';
        });

        function syncPolygonInput() {
            const data = vertices.map(function (vertex) {
                return {
                    lat: Number(vertex.marker.getLatLng().lat.toFixed(6)),
                    lng: Number(vertex.marker.getLatLng().lng.toFixed(6))
                };
            });
            polygonInput.value = data.length ? JSON.stringify(data) : '';
            if (data.length === 0) polygonStatus.textContent = 'Belum ada titik. Tandai 4 sudut lahan.';
            else if (data.length < 4) polygonStatus.textContent = 'Titik ' + data.length + ' dari 4. Klik peta untuk menambah sudut.';
            else polygonStatus.textContent = 'Batas lahan lengkap (4 titik). Geser titik untuk menyesuaikan.';
        }

        function redrawPolygon() {
            if (polygonLayer) {
                polygonMap.removeLayer(polygonLayer);
                polygonLayer = null;
            }
            if (vertices.length < 3) return;
            polygonLayer = L.polygon(vertices.map(function (vertex) {
                return vertex.marker.getLatLng();
            }), { color: '#198754', weight: 2, fillColor: '#198754', fillOpacity: 0.25 }).addTo(polygonMap);
        }

        function addVertex(lat, lng) {
            if (vertices.length >= 4) return;
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

        polygonMap.on('click', function (e) { addVertex(e.latlng.lat, e.latlng.lng); });
        card.querySelector('.btn-nested-undo-polygon')?.addEventListener('click', function () {
            const last = vertices.pop();
            if (!last) return;
            polygonMap.removeLayer(last.marker);
            redrawPolygon();
            syncPolygonInput();
        });
        card.querySelector('.btn-nested-clear-polygon')?.addEventListener('click', function () {
            vertices.forEach(function (vertex) { polygonMap.removeLayer(vertex.marker); });
            vertices = [];
            redrawPolygon();
            syncPolygonInput();
        });

        try {
            const saved = JSON.parse(polygonInput.value || '[]');
            if (Array.isArray(saved)) {
                saved.forEach(function (point) {
                    const lat = parseFloat(point.lat ?? point[0]);
                    const lng = parseFloat(point.lng ?? point[1]);
                    if (!Number.isNaN(lat) && !Number.isNaN(lng)) addVertex(lat, lng);
                });
            }
        } catch (err) {}

        const form = card.closest('form');
        if (form && !form.dataset.nestedPolygonGuard) {
            form.dataset.nestedPolygonGuard = '1';
            form.addEventListener('submit', function (event) {
                const incomplete = Array.from(form.querySelectorAll('.nested-lahan-card')).some(function (item) {
                    const val = item.querySelector('.nested-peta-input')?.value || '';
                    if (!val) return false;
                    try {
                        const parsed = JSON.parse(val);
                        return Array.isArray(parsed) && parsed.length > 0 && parsed.length !== 4;
                    } catch (e) {
                        return true;
                    }
                });
                if (incomplete) {
                    event.preventDefault();
                    alert('Peta lahan harus terdiri dari tepat 4 titik, atau dikosongkan.');
                }
            });
        }

        setTimeout(function () {
            map.invalidateSize();
            polygonMap.invalidateSize();
        }, 250);
    }

    function renumberLahanCards() {
        const empty = document.getElementById('lahanRepeaterEmpty');
        const cards = document.querySelectorAll('#lahanRepeaterList .nested-lahan-card');
        if (empty) empty.classList.toggle('d-none', cards.length > 0);
    }

    document.addEventListener('DOMContentLoaded', function () {
        initLocationGps();
        document.querySelectorAll('#lahanRepeaterList .nested-lahan-card').forEach(initNestedLahanCard);

        let nextIndex = document.querySelectorAll('#lahanRepeaterList .nested-lahan-card').length;
        document.getElementById('btnAddLahan')?.addEventListener('click', function () {
            const template = document.getElementById('lahanRepeaterTemplate');
            const list = document.getElementById('lahanRepeaterList');
            if (!template || !list) return;
            const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex));
            const wrap = document.createElement('div');
            wrap.innerHTML = html.trim();
            const card = wrap.firstElementChild;
            list.appendChild(card);
            nextIndex += 1;
            renumberLahanCards();
            initNestedLahanCard(card);
        });

        document.getElementById('lahanRepeaterList')?.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-remove-lahan');
            if (!btn) return;
            btn.closest('.nested-lahan-card')?.remove();
            renumberLahanCards();
        });
    });
})();
</script>
@endpush
