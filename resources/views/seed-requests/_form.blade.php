<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $heading ?? 'Permintaan Benih' }}</h4>
        <small class="text-muted">Lengkapi data transaksi, lokasi, rincian benih, dan pembayaran. Koordinat GPS dan keterangan tidak wajib diisi.</small>
    </div>
    <a href="{{ $backUrl }}" class="btn btn-secondary">Kembali</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Lengkapi form terlebih dahulu.</strong>
        <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $storeUrl }}" id="requestForm" enctype="multipart/form-data">
            @csrf
            <h5 class="mb-3">Informasi Transaksi</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Permintaan <span class="text-danger">*</span></label>
                    <input type="date" name="request_date" class="form-control" value="{{ old('request_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nama Pembeli <span class="text-danger">*</span></label>
                    <input type="text" name="buyer_name" class="form-control" value="{{ old('buyer_name') }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Instansi / Organisasi <span class="text-danger">*</span></label>
                    <input type="text" name="organization" class="form-control" value="{{ old('organization') }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kontak <span class="text-danger">*</span></label>
                    <input type="text" name="buyer_contact" class="form-control" value="{{ old('buyer_contact') }}" placeholder="Contoh: 08123456789" required>
                    <small class="text-muted">Pastikan nomor dapat dihubungi melalui WhatsApp.</small>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">NIK <span class="text-danger">*</span></label>
                    <input type="text" name="buyer_nik" class="form-control" value="{{ old('buyer_nik') }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kategori pembeli <span class="text-danger">*</span></label>
                    <select name="buyer_category" id="buyer_category" class="form-select" required>
                        <option value="">Pilih</option>
                        @foreach(['petani_perorangan'=>'Petani perorangan','kelompok_tani'=>'Kelompok tani','instansi_pemerintah'=>'Instansi pemerintah','swasta'=>'Swasta','lainnya'=>'Lainnya'] as $val=>$label)
                            <option value="{{ $val }}" {{ old('buyer_category')==$val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3" id="buyer_category_custom_wrap" style="{{ old('buyer_category')==='lainnya' ? '' : 'display:none' }}">
                    <label class="form-label">Kategori lainnya <span class="text-danger">*</span></label>
                    <input type="text" name="buyer_category_custom" class="form-control" value="{{ old('buyer_category_custom') }}">
                </div>
            </div>

            <h5 class="mb-3 mt-3">Data Lokasi Sebaran</h5>
            <div class="row">
                <div class="col-md-3 mb-3"><label class="form-label">Provinsi <span class="text-danger">*</span></label><input name="destination_province" class="form-control" value="{{ old('destination_province') }}" required></div>
                <div class="col-md-3 mb-3"><label class="form-label">Kabupaten/Kota <span class="text-danger">*</span></label><input name="destination_city" class="form-control" value="{{ old('destination_city') }}" required></div>
                <div class="col-md-3 mb-3"><label class="form-label">Kecamatan <span class="text-danger">*</span></label><input name="destination_district" class="form-control" value="{{ old('destination_district') }}" required></div>
                <div class="col-md-3 mb-3"><label class="form-label">Desa/Kelurahan <span class="text-danger">*</span></label><input name="destination_village" class="form-control" value="{{ old('destination_village') }}" required></div>
                <div class="col-md-6 mb-3"><label class="form-label">Rencana lokasi lahan <span class="text-danger">*</span></label><input name="planned_location_name" class="form-control" value="{{ old('planned_location_name') }}" required></div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Koordinat GPS</label>
                    <div class="input-group">
                        <input type="text" name="planned_gps" id="planned_gps" class="form-control" value="{{ old('planned_gps') }}" placeholder="-0.947083, 100.417206">
                        <button type="button" class="btn btn-outline-secondary" id="btnAmbilGps">Ambil GPS</button>
                    </div>
                    <div id="gps-map" class="mt-2 rounded border" style="height: 240px; min-height: 240px;"></div>
                    <small class="text-muted d-block">Tidak wajib diisi. Klik peta, ketik lintang,bujur, atau gunakan tombol Ambil GPS. Contoh: -0.947083, 100.417206</small>
                    <small id="gps-status" class="text-muted d-block"></small>
                </div>
                <div class="col-md-6 mb-3"><label class="form-label">Estimasi luas tanam (ha) <span class="text-danger">*</span></label><input type="number" step="0.01" min="0.01" name="estimated_planting_area" class="form-control" value="{{ old('estimated_planting_area') }}" required></div>
            </div>

            <h5 class="mb-3 mt-3">Rincian Item (Benih yang Dibeli)</h5>
            <div class="mb-3">
                <label class="form-label">Pilih benih <span class="text-danger">*</span></label>
                <select id="plant_id" name="plant_id" class="form-select" required @if(!empty($lockedPlant)) disabled @endif>
                    <option value="">Pilih tanaman</option>
                    @foreach($plants as $plant)
                        <option value="{{ $plant->getKey() }}" data-price="{{ (float) ($plant->harga_jual ?? 0) }}" data-unit="{{ $plant->satuanStok?->code }}"
                            {{ old('plant_id', $lockedPlant?->getKey()) == $plant->getKey() ? 'selected' : '' }}>
                            {{ $plant->type?->name }} - {{ $plant->variety ?: $plant->name }}
                        </option>
                    @endforeach
                </select>
                @if(!empty($lockedPlant))
                    <input type="hidden" name="plant_id" value="{{ $lockedPlant->getKey() }}">
                    <small class="text-muted d-block mt-1">Varietas terkunci dari halaman informasi publik.</small>
                @endif
            </div>
            <div id="stock-summary" class="card border-success mb-3 d-none">
                <div class="card-body py-3">
                    <div class="row g-2 small">
                        <div class="col-md-3"><strong>Nama benih</strong><div id="sum-name">-</div></div>
                        <div class="col-md-3"><strong>Total kuantitas tersedia</strong><div id="sum-qty">-</div></div>
                        <div class="col-md-3"><strong>Harga satuan</strong><div id="sum-price">-</div></div>
                        <div class="col-md-3"><strong>Total produk tersedia</strong><div id="sum-products">-</div></div>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Jumlah produk (kemasan) yang ingin dibeli <span class="text-danger">*</span></label>
                <input type="number" id="purchase_count" class="form-control" min="1" step="1" placeholder="Masukkan jumlah kemasan">
                <small class="text-muted">Sistem otomatis memilih kemasan FEFO sesuai jumlah yang diminta.</small>
            </div>
            <div id="packaging-hidden"></div>
            <div class="mb-4 p-3 bg-light rounded">
                <strong>Total produk:</strong> <span id="total-items">0</span> kemasan
                &nbsp;·&nbsp;
                <strong>Total kuantitas:</strong> <span id="total-qty">0</span>
                &nbsp;·&nbsp;
                <strong>Total biaya:</strong> Rp <span id="total-cost">0</span>
            </div>

            <h5 class="mb-3 mt-3">Pembayaran</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Total belanja</label>
                    <input type="text" class="form-control" id="total_amount_display" value="Rp 0" readonly>
                    <input type="hidden" name="total_amount" id="total_amount" value="0">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Metode pembayaran <span class="text-danger">*</span></label>
                    <select name="payment_method" id="payment_method" class="form-select" required>
                        <option value="">Pilih metode</option>
                        <option value="cash" {{ old('payment_method')=='cash' ? 'selected' : '' }}>Cash</option>
                        <option value="transfer_bank" {{ old('payment_method')=='transfer_bank' ? 'selected' : '' }}>Transfer Bank</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Lampiran bukti pembayaran <span class="text-danger">*</span></label>
                    <input type="file" name="payment_proof" id="payment_proof" class="form-control" accept="image/*,application/pdf">
                    <small class="text-muted">Wajib untuk transfer bank. Format JPG, PNG, PDF (maks. 2MB).</small>
                </div>
            </div>
            <div id="bank-info" class="alert alert-info" style="{{ old('payment_method')==='transfer_bank' ? '' : 'display:none' }}">
                <strong>Informasi transfer bank UPTD BBI TPHP</strong>
                <div>Nama bank: Bank Nagari</div>
                <div>Atas nama: UPTD BBI TPHP Sumatera Barat</div>
                <div>Nomor rekening: 1402-01-000123-30</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Opsional">{{ old('notes') }}</textarea>
            </div>
            <div class="text-end">
                <button class="btn btn-success" type="submit">Kirim Permintaan</button>
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
const packagingUrl = @json(url('/permintaan/plants'));
let fefoPackagings = [];

function onReady(fn) {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
}

onReady(function () {
    const plantSelect = document.getElementById('plant_id');
    if (plantSelect) {
        plantSelect.addEventListener('change', loadFefoPackagings);
        if (plantSelect.value) loadFefoPackagings();
    }
    const method = document.getElementById('payment_method');
    if (method) {
        method.addEventListener('change', toggleBankInfo);
        toggleBankInfo();
    }
    const cat = document.getElementById('buyer_category');
    if (cat) cat.addEventListener('change', function () {
        document.getElementById('buyer_category_custom_wrap').style.display = cat.value === 'lainnya' ? '' : 'none';
    });
    initGpsMap();
    const gpsInput = document.getElementById('planned_gps');
    if (gpsInput) {
        gpsInput.addEventListener('change', syncGpsFromInput);
        gpsInput.addEventListener('blur', syncGpsFromInput);
    }
    const gpsBtn = document.getElementById('btnAmbilGps');
    if (gpsBtn) gpsBtn.addEventListener('click', captureGps);
    const purchaseCount = document.getElementById('purchase_count');
    if (purchaseCount) purchaseCount.addEventListener('input', applyPurchaseCount);
    document.getElementById('requestForm')?.addEventListener('submit', function (e) {
        const hidden = document.querySelectorAll('#packaging-hidden input[name="packaging_ids[]"]').length;
        if (!hidden) {
            e.preventDefault();
            alert('Lengkapi form: isi jumlah produk yang ingin dibeli.');
        }
        const gps = (document.getElementById('planned_gps')?.value || '').trim();
        if (gps && !/^-?\d{1,3}(\.\d+)?\s*,\s*-?\d{1,3}(\.\d+)?$/.test(gps)) {
            e.preventDefault();
            alert('Koordinat GPS harus berformat lintang, bujur. Contoh: -0.947083, 100.417206');
        }
        const pay = document.getElementById('payment_method')?.value;
        const proof = document.getElementById('payment_proof');
        if (pay === 'transfer_bank' && proof && !proof.files.length) {
            e.preventDefault();
            alert('Lengkapi form: lampiran bukti pembayaran wajib untuk transfer bank.');
        }
    });
});

function toggleBankInfo() {
    const show = document.getElementById('payment_method')?.value === 'transfer_bank';
    const box = document.getElementById('bank-info');
    const proof = document.getElementById('payment_proof');
    if (box) box.style.display = show ? '' : 'none';
    if (proof) proof.required = !!show;
}

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
    if (existing) gpsMarker = L.marker(start).addTo(gpsMap);
    gpsMap.on('click', function (e) { setGps(e.latlng.lat, e.latlng.lng, true); });
    setTimeout(function () { gpsMap.invalidateSize(); }, 250);
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
    if (btn) { btn.disabled = true; btn.textContent = 'Mengambil...'; }
    navigator.geolocation.getCurrentPosition(function (pos) {
        setGps(pos.coords.latitude, pos.coords.longitude, true);
        if (btn) { btn.disabled = false; btn.textContent = 'Ambil GPS'; }
    }, function () {
        if (btn) { btn.disabled = false; btn.textContent = 'Ambil GPS'; }
        setGpsStatus('Lokasi perangkat tidak tersedia. Klik peta atau ketik koordinat, misalnya -0.947083, 100.417206.', true);
    }, { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 });
}

function loadFefoPackagings() {
    const plantSelect = document.getElementById('plant_id');
    const plantId = plantSelect?.value || document.querySelector('input[name="plant_id"]')?.value;
    const summary = document.getElementById('stock-summary');
    fefoPackagings = [];
    if (!plantId) {
        summary?.classList.add('d-none');
        updateSaleTotals();
        return;
    }
    fetch(packagingUrl + '/' + plantId + '/packagings')
        .then(r => r.json())
        .then(data => {
            fefoPackagings = data.packagings || [];
            if (summary) {
                summary.classList.toggle('d-none', fefoPackagings.length === 0);
                document.getElementById('sum-name').textContent = data.plant_name || '-';
                document.getElementById('sum-qty').textContent = Number(data.total_quantity || 0).toLocaleString('id-ID') + ' ' + (data.unit || '');
                document.getElementById('sum-price').textContent = 'Rp ' + Number(data.unit_price || 0).toLocaleString('id-ID');
                document.getElementById('sum-products').textContent = (data.available_count || 0) + ' kemasan';
            }
            const purchase = document.getElementById('purchase_count');
            if (purchase && purchase.value) applyPurchaseCount();
            else updateSaleTotals();
        })
        .catch(function () {
            alert('Gagal memuat stok kemasan benih.');
        });
}

function applyPurchaseCount() {
    const count = parseInt(document.getElementById('purchase_count')?.value || '0', 10);
    const hidden = document.getElementById('packaging-hidden');
    hidden.innerHTML = '';
    if (!count || count < 1 || !fefoPackagings.length) {
        updateSaleTotals();
        return;
    }
    if (count > fefoPackagings.length) {
        alert('Jumlah produk melebihi stok kemasan tersedia (' + fefoPackagings.length + ').');
        document.getElementById('purchase_count').value = fefoPackagings.length;
        return applyPurchaseCount();
    }
    const selected = fefoPackagings.slice(0, count);
    selected.forEach(function (pkg) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'packaging_ids[]';
        input.value = pkg.id;
        input.dataset.qty = pkg.quantity;
        input.dataset.price = pkg.unit_price;
        hidden.appendChild(input);
    });
    updateSaleTotalsFromPackagings(selected);
}

function updateSaleTotalsFromPackagings(packages) {
    let qty = 0, cost = 0, items = 0;
    (packages || []).forEach(function (pkg) {
        const q = parseFloat(pkg.quantity || 0);
        const p = parseFloat(pkg.unit_price || 0);
        qty += q;
        cost += q * p;
        items += 1;
    });
    renderTotals(items, qty, cost);
}

function updateSaleTotals() {
    let qty = 0, cost = 0, items = 0;
    document.querySelectorAll('#packaging-hidden input[name="packaging_ids[]"]').forEach(function (el) {
        const q = parseFloat(el.dataset.qty || 0);
        const p = parseFloat(el.dataset.price || 0);
        qty += q;
        cost += q * p;
        items += 1;
    });
    renderTotals(items, qty, cost);
}

function renderTotals(items, qty, cost) {
    document.getElementById('total-items').textContent = items;
    document.getElementById('total-qty').textContent = qty.toLocaleString('id-ID', {minimumFractionDigits: 2});
    document.getElementById('total-cost').textContent = cost.toLocaleString('id-ID');
    document.getElementById('total_amount').value = cost.toFixed(2);
    document.getElementById('total_amount_display').value = 'Rp ' + cost.toLocaleString('id-ID');
}
</script>
@endpush
