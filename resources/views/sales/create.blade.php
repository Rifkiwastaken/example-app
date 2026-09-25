@extends('layouts.app')

@section('title', 'Form Pencatatan Penjualan Baru - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Form Pencatatan Penjualan Baru</h4>
    <a href="{{ route('sales.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('sales.store') }}" method="POST" id="saleForm" enctype="multipart/form-data">
            @csrf

            <!-- Bagian A: Informasi Transaksi -->
            <h5 class="mb-3">Bagian A: Informasi Transaksi</h5>
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="receipt_number" class="form-label">Nomor Struk/Referensi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('receipt_number') is-invalid @enderror" 
                               id="receipt_number" name="receipt_number" value="{{ old('receipt_number', $receiptNumber) }}" required>
                        @error('receipt_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="sale_date" class="form-label">Tanggal Penjualan <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('sale_date') is-invalid @enderror" 
                               id="sale_date" name="sale_date" value="{{ old('sale_date', date('Y-m-d')) }}" required>
                        @error('sale_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="buyer_name" class="form-label">Nama Pembeli <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('buyer_name') is-invalid @enderror" 
                               id="buyer_name" name="buyer_name" value="{{ old('buyer_name') }}" 
                               placeholder="Contoh: Bapak Heru" required>
                        @error('buyer_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="buyer_contact" class="form-label">Kontak Pembeli (Opsional)</label>
                        <input type="text" class="form-control @error('buyer_contact') is-invalid @enderror" 
                               id="buyer_contact" name="buyer_contact" value="{{ old('buyer_contact') }}" 
                               placeholder="Contoh: 08123456789">
                        <small class="text-muted">Pastikan nomor dapat dihubungi melalui WhatsApp.</small>
                        @error('buyer_contact')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="buyer_nik" class="form-label">NIK Pembeli</label>
                        <input type="text" class="form-control @error('buyer_nik') is-invalid @enderror" 
                               id="buyer_nik" name="buyer_nik" value="{{ old('buyer_nik') }}" 
                               placeholder="Contoh: 1234567890123456">
                        @error('buyer_nik')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Dicatat Oleh</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->name }}" readonly>
                    </div>
                </div>
            </div>

            <!-- Bagian B: Data Lokasi Sebaran -->
            <h5 class="mb-3 mt-4">Bagian B: Data Lokasi Sebaran</h5>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="destination_province" class="form-label">Provinsi</label>
                        <input type="text" class="form-control @error('destination_province') is-invalid @enderror" 
                               id="destination_province" name="destination_province" value="{{ old('destination_province') }}" 
                               placeholder="Contoh: Sumatera Barat">
                        @error('destination_province')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="destination_city" class="form-label">Kabupaten/Kota</label>
                        <input type="text" class="form-control @error('destination_city') is-invalid @enderror" 
                               id="destination_city" name="destination_city" value="{{ old('destination_city') }}" 
                               placeholder="Contoh: Padang">
                        @error('destination_city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="destination_district" class="form-label">Kecamatan</label>
                        <input type="text" class="form-control @error('destination_district') is-invalid @enderror" 
                               id="destination_district" name="destination_district" value="{{ old('destination_district') }}" 
                               placeholder="Contoh: Koto Tangah">
                        @error('destination_district')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="destination_village" class="form-label">Desa/Kelurahan</label>
                        <input type="text" class="form-control @error('destination_village') is-invalid @enderror" 
                               id="destination_village" name="destination_village" value="{{ old('destination_village') }}" 
                               placeholder="Contoh: Lubuk Buaya">
                        @error('destination_village')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="planned_location_name" class="form-label">Rencana Lokasi Lahan</label>
                        <input type="text" class="form-control @error('planned_location_name') is-invalid @enderror" 
                               id="planned_location_name" name="planned_location_name" value="{{ old('planned_location_name') }}" 
                               placeholder="Contoh: Blok A, Kampung Sawah">
                        <small class="text-muted">Nama blok atau kampung spesifik jika tersedia</small>
                        @error('planned_location_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="planned_gps" class="form-label">Lokasi GPS rencana tanam</label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('planned_gps') is-invalid @enderror"
                                   id="planned_gps" name="planned_gps" value="{{ old('planned_gps') }}"
                                   placeholder="Contoh: -0.947083, 100.417206">
                            <button type="button" class="btn btn-outline-secondary" id="btnAmbilGps">Ambil GPS</button>
                        </div>
                        <div id="gps-map" class="mt-2 rounded border" style="height: 240px; min-height: 240px;"></div>
                        <small class="text-muted d-block">Klik peta untuk menandai lokasi, atau ketik koordinat (lintang, bujur). Tombol Ambil GPS memakai lokasi perangkat jika diizinkan.</small>
                        <small id="gps-status" class="text-muted d-block"></small>
                        @error('planned_gps')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="estimated_planting_area" class="form-label">Estimasi Luas Tanam (Hektar)</label>
                        <input type="number" step="0.01" class="form-control @error('estimated_planting_area') is-invalid @enderror" 
                               id="estimated_planting_area" name="estimated_planting_area" value="{{ old('estimated_planting_area') }}" 
                               placeholder="Contoh: 2.5">
                        <small class="text-muted">Luas lahan yang direncanakan untuk ditanami</small>
                        @error('estimated_planting_area')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Bagian C: Rincian Item -->
            <h5 class="mb-3 mt-4">Bagian C: Rincian Item (Benih yang Dibeli)</h5>
            <div class="mb-3">
                <label class="form-label">Pilih tanaman</label>
                <select id="plant_id" name="plant_id" class="form-select" required>
                    <option value="">Pilih tanaman</option>
                    @foreach($plants ?? [] as $plant)
                        <option value="{{ $plant->getKey() }}" {{ old('plant_id') == $plant->getKey() ? 'selected' : '' }}>
                            {{ $plant->displayName() }}{{ $plant->type?->category ? ' ('.$plant->type->category.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="table-responsive mb-2">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th></th>
                            <th>No label seri</th>
                            <th>Kuantitas</th>
                            <th>Lokasi gudang</th>
                            <th>Rak penyimpanan</th>
                        </tr>
                    </thead>
                    <tbody id="fefo-body">
                        <tr><td colspan="5" class="text-muted text-center">Pilih benih untuk menampilkan stok kemasan (FEFO).</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="mb-4">
                <strong>Total jual:</strong> <span id="total-qty">0</span>
                &nbsp;·&nbsp;
                <strong>Total biaya:</strong> Rp <span id="total-cost">0</span>
            </div>
            @error('packaging_ids')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            @error('items')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror

            <!-- Bagian C: Pembayaran -->
            <h5 class="mb-3 mt-4">Bagian C: Pembayaran</h5>
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Total Belanja (Rp)</label>
                        <input type="text" class="form-control" id="total_amount_display" value="Rp 0" readonly>
                        <input type="hidden" name="total_amount" id="total_amount" value="0">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select class="form-select @error('payment_method') is-invalid @enderror" 
                                id="payment_method" name="payment_method" required>
                            <option value="">Pilih Metode</option>
                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="transfer_bank" {{ old('payment_method') == 'transfer_bank' ? 'selected' : '' }}>Transfer Bank</option>
                        </select>
                        @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="payment_status" class="form-label">Status Pembayaran <span class="text-danger">*</span></label>
                        <select class="form-select @error('payment_status') is-invalid @enderror" 
                                id="payment_status" name="payment_status" required>
                            <option value="lunas" {{ old('payment_status', 'lunas') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            <option value="belum_lunas" {{ old('payment_status') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                        </select>
                        @error('payment_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Keterangan (Opsional)</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="2" 
                                  placeholder="Contoh: Transfer via Bank Nagari, an. Bapak Heru.">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="payment_proof" class="form-label">Lampiran Bukti Pembayaran (Opsional)</label>
                        <input type="file" class="form-control @error('payment_proof') is-invalid @enderror" 
                               id="payment_proof" name="payment_proof" accept="image/*,application/pdf">
                        <small class="text-muted">Format: JPG, PNG, PDF (Max: 2MB)</small>
                        @error('payment_proof')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Simpan Transaksi
                </button>
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
let gpsMap, gpsMarker;

function onReady(fn) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fn);
    } else {
        fn();
    }
}

onReady(function() {
    const plantSelect = document.getElementById('plant_id');
    if (plantSelect) {
        plantSelect.addEventListener('change', loadFefoPackagings);
        if (plantSelect.value) {
            loadFefoPackagings();
        }
    }
    initGpsMap();
    const gpsInput = document.getElementById('planned_gps');
    if (gpsInput) {
        gpsInput.addEventListener('change', syncGpsFromInput);
        gpsInput.addEventListener('blur', syncGpsFromInput);
    }
    const gpsBtn = document.getElementById('btnAmbilGps');
    if (gpsBtn) gpsBtn.addEventListener('click', captureGps);
});

function parseGps(value) {
    if (!value) return null;
    const parts = String(value).split(/[,\s]+/).filter(Boolean);
    if (parts.length < 2) return null;
    const lat = parseFloat(parts[0]);
    const lng = parseFloat(parts[1]);
    if (Number.isNaN(lat) || Number.isNaN(lng)) return null;
    return { lat, lng };
}

function initGpsMap() {
    const el = document.getElementById('gps-map');
    if (!el || typeof L === 'undefined') return;
    const existing = parseGps(document.getElementById('planned_gps')?.value);
    const start = existing ? [existing.lat, existing.lng] : [-0.947083, 100.417206];
    gpsMap = L.map('gps-map').setView(start, existing ? 14 : 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(gpsMap);
    if (existing) {
        gpsMarker = L.marker(start).addTo(gpsMap);
    }
    gpsMap.on('click', function (e) {
        setGps(e.latlng.lat, e.latlng.lng, true);
    });
    setTimeout(function () { gpsMap.invalidateSize(); }, 250);
    if (existing) {
        setGpsStatus('Koordinat tersimpan: ' + existing.lat.toFixed(6) + ', ' + existing.lng.toFixed(6), false);
    }
}

function setGpsStatus(message, isError) {
    const el = document.getElementById('gps-status');
    if (!el) return;
    el.textContent = message || '';
    el.classList.toggle('text-danger', !!isError);
    el.classList.toggle('text-success', !isError && !!message);
}

function syncGpsFromInput() {
    const parsed = parseGps(document.getElementById('planned_gps')?.value);
    if (parsed) setGps(parsed.lat, parsed.lng, false);
}

function setGps(lat, lng, writeInput) {
    if (writeInput !== false) {
        document.getElementById('planned_gps').value = lat.toFixed(6) + ', ' + lng.toFixed(6);
    }
    if (!gpsMap) return;
    if (gpsMarker) gpsMarker.setLatLng([lat, lng]);
    else gpsMarker = L.marker([lat, lng]).addTo(gpsMap);
    gpsMap.setView([lat, lng], 14);
    setGpsStatus('Koordinat tersimpan: ' + lat.toFixed(6) + ', ' + lng.toFixed(6), false);
}

function captureGps() {
    const btn = document.getElementById('btnAmbilGps');
    if (!navigator.geolocation) {
        setGpsStatus('GPS perangkat tidak didukung. Klik peta atau isi koordinat secara manual.', true);
        return;
    }
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Mengambil...';
    }
    setGpsStatus('Meminta izin lokasi perangkat...', false);
    navigator.geolocation.getCurrentPosition(function (pos) {
        setGps(pos.coords.latitude, pos.coords.longitude, true);
        if (btn) { btn.disabled = false; btn.textContent = 'Ambil GPS'; }
    }, function () {
        if (btn) { btn.disabled = false; btn.textContent = 'Ambil GPS'; }
        setGpsStatus('Lokasi perangkat tidak tersedia (butuh HTTPS/izin). Klik peta atau ketik koordinat, misalnya -0.947083, 100.417206.', true);
    }, { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 });
}

function loadFefoPackagings() {
    const plantId = document.getElementById('plant_id').value;
    const body = document.getElementById('fefo-body');
    if (!plantId) {
        body.innerHTML = '<tr><td colspan="5" class="text-muted text-center">Pilih benih untuk menampilkan stok kemasan (FEFO).</td></tr>';
        updateSaleTotals();
        return;
    }
    body.innerHTML = '<tr><td colspan="5" class="text-muted text-center">Memuat stok kemasan...</td></tr>';
    fetch('{{ url("sales/plants") }}/' + plantId + '/packagings')
        .then(r => r.json())
        .then(data => {
            if (!data.packagings || data.packagings.length === 0) {
                body.innerHTML = '<tr><td colspan="5" class="text-muted text-center">Tidak ada kemasan siap salur untuk benih ini.</td></tr>';
                updateSaleTotals();
                return;
            }
            body.innerHTML = data.packagings.map(function (pkg) {
                return '<tr>' +
                    '<td><input type="checkbox" class="pkg-check" name="packaging_ids[]" value="' + pkg.id + '" data-qty="' + pkg.quantity + '" data-price="' + pkg.unit_price + '" onchange="updateSaleTotals()"></td>' +
                    '<td>' + pkg.no_label_seri + '</td>' +
                    '<td>' + pkg.quantity + ' ' + pkg.unit + '</td>' +
                    '<td>' + pkg.warehouse + '</td>' +
                    '<td>' + pkg.rack + '</td>' +
                    '</tr>';
            }).join('');
            updateSaleTotals();
        })
        .catch(function () {
            body.innerHTML = '<tr><td colspan="5" class="text-danger text-center">Gagal memuat stok kemasan.</td></tr>';
        });
}

function updateSaleTotals() {
    let qty = 0;
    let cost = 0;
    document.querySelectorAll('.pkg-check:checked').forEach(function (el) {
        const q = parseFloat(el.dataset.qty || 0);
        const p = parseFloat(el.dataset.price || 0);
        qty += q;
        cost += q * p;
    });
    document.getElementById('total-qty').textContent = qty.toLocaleString('id-ID', {minimumFractionDigits: 2});
    document.getElementById('total-cost').textContent = cost.toLocaleString('id-ID');
}
</script>
@endpush
@endsection

