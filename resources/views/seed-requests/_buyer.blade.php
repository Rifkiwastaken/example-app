@php
    $buyer = $buyer ?? [];
@endphp
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $heading ?? 'Informasi Pembeli' }}</h4>
        <small class="text-muted">Langkah 1 dari 2 · Lengkapi data pembeli dan lokasi sebaran.</small>
    </div>
    <a href="{{ $backUrl }}" class="btn btn-secondary">Kembali</a>
</div>
@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $buyerUrl }}">
            @csrf
            <h5 class="mb-3">Informasi Transaksi</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Permintaan <span class="text-danger">*</span></label>
                    <input type="date" name="request_date" class="form-control" value="{{ old('request_date', $buyer['request_date'] ?? date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nama Pembeli <span class="text-danger">*</span></label>
                    <input type="text" name="buyer_name" class="form-control" value="{{ old('buyer_name', $buyer['buyer_name'] ?? '') }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Instansi / Organisasi <span class="text-danger">*</span></label>
                    <input type="text" name="organization" class="form-control" value="{{ old('organization', $buyer['organization'] ?? '') }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kontak <span class="text-danger">*</span></label>
                    <input type="text" name="buyer_contact" class="form-control" value="{{ old('buyer_contact', $buyer['buyer_contact'] ?? '') }}" placeholder="Contoh: 08123456789" required>
                    <small class="text-muted">Pastikan nomor dapat dihubungi melalui WhatsApp.</small>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">NIK <span class="text-danger">*</span></label>
                    <input type="text" name="buyer_nik" class="form-control" value="{{ old('buyer_nik', $buyer['buyer_nik'] ?? '') }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kategori pembeli <span class="text-danger">*</span></label>
                    <select name="buyer_category" id="buyer_category" class="form-select" required>
                        <option value="">Pilih</option>
                        @foreach(['petani_perorangan'=>'Petani perorangan','kelompok_tani'=>'Kelompok tani','instansi_pemerintah'=>'Instansi pemerintah','swasta'=>'Swasta','lainnya'=>'Lainnya'] as $val=>$label)
                            <option value="{{ $val }}" {{ old('buyer_category', $buyer['buyer_category'] ?? '')==$val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3" id="buyer_category_custom_wrap" style="{{ old('buyer_category', $buyer['buyer_category'] ?? '')==='lainnya' ? '' : 'display:none' }}">
                    <label class="form-label">Kategori lainnya <span class="text-danger">*</span></label>
                    <input type="text" name="buyer_category_custom" class="form-control" value="{{ old('buyer_category_custom', $buyer['buyer_category_custom'] ?? '') }}">
                </div>
            </div>
            <h5 class="mb-3 mt-3">Data Lokasi Sebaran</h5>
            <div class="row">
                <div class="col-md-3 mb-3"><label class="form-label">Provinsi <span class="text-danger">*</span></label><input name="destination_province" class="form-control" value="{{ old('destination_province', $buyer['destination_province'] ?? '') }}" required></div>
                <div class="col-md-3 mb-3"><label class="form-label">Kabupaten/Kota <span class="text-danger">*</span></label><input name="destination_city" class="form-control" value="{{ old('destination_city', $buyer['destination_city'] ?? '') }}" required></div>
                <div class="col-md-3 mb-3"><label class="form-label">Kecamatan <span class="text-danger">*</span></label><input name="destination_district" class="form-control" value="{{ old('destination_district', $buyer['destination_district'] ?? '') }}" required></div>
                <div class="col-md-3 mb-3"><label class="form-label">Desa/Kelurahan <span class="text-danger">*</span></label><input name="destination_village" class="form-control" value="{{ old('destination_village', $buyer['destination_village'] ?? '') }}" required></div>
                <div class="col-md-6 mb-3"><label class="form-label">Rencana lokasi lahan <span class="text-danger">*</span></label><input name="planned_location_name" class="form-control" value="{{ old('planned_location_name', $buyer['planned_location_name'] ?? '') }}" required></div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Koordinat GPS</label>
                    <div class="input-group">
                        <input type="text" name="planned_gps" id="planned_gps" class="form-control" value="{{ old('planned_gps', $buyer['planned_gps'] ?? '') }}" placeholder="-0.947083, 100.417206">
                        <button type="button" class="btn btn-outline-secondary" id="btnAmbilGps">Ambil GPS</button>
                    </div>
                    <div id="gps-map" class="mt-2 rounded border" style="height: 240px;"></div>
                    <small class="text-muted d-block">Tidak wajib diisi. Klik peta, ketik lintang,bujur, atau gunakan tombol Ambil GPS.</small>
                </div>
                <div class="col-md-6 mb-3"><label class="form-label">Estimasi luas tanam (ha) <span class="text-danger">*</span></label><input type="number" step="0.01" min="0.01" name="estimated_planting_area" class="form-control" value="{{ old('estimated_planting_area', $buyer['estimated_planting_area'] ?? '') }}" required></div>
                <div class="col-md-6 mb-3"><label class="form-label">Keterangan</label><textarea name="notes" class="form-control" rows="2" placeholder="Opsional">{{ old('notes', $buyer['notes'] ?? '') }}</textarea></div>
            </div>
            <div class="text-end">
                <button class="btn btn-success">Lanjutkan</button>
            </div>
        </form>
    </div>
</div>
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.getElementById('buyer_category')?.addEventListener('change', function () {
    document.getElementById('buyer_category_custom_wrap').style.display = this.value === 'lainnya' ? '' : 'none';
});
let gpsMap, gpsMarker;
(function () {
    const el = document.getElementById('gps-map');
    if (!el || typeof L === 'undefined') return;
    const raw = document.getElementById('planned_gps').value;
    const parts = raw.split(',');
    const start = parts.length === 2 ? [parseFloat(parts[0]), parseFloat(parts[1])] : [-0.947083, 100.417206];
    gpsMap = L.map('gps-map').setView(start, 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(gpsMap);
    if (parts.length === 2) gpsMarker = L.marker(start).addTo(gpsMap);
    gpsMap.on('click', function (e) {
        document.getElementById('planned_gps').value = e.latlng.lat.toFixed(6) + ', ' + e.latlng.lng.toFixed(6);
        if (gpsMarker) gpsMarker.setLatLng(e.latlng); else gpsMarker = L.marker(e.latlng).addTo(gpsMap);
    });
    document.getElementById('btnAmbilGps')?.addEventListener('click', function () {
        navigator.geolocation?.getCurrentPosition(function (pos) {
            const latlng = {lat: pos.coords.latitude, lng: pos.coords.longitude};
            document.getElementById('planned_gps').value = latlng.lat.toFixed(6) + ', ' + latlng.lng.toFixed(6);
            gpsMap.setView(latlng, 14);
            if (gpsMarker) gpsMarker.setLatLng(latlng); else gpsMarker = L.marker(latlng).addTo(gpsMap);
        });
    });
})();
</script>
@endpush
