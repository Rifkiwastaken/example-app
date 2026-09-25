@php
    $buyer = $buyer ?? [];
    $categories = $categories ?? collect();
    $plants = $plants ?? collect();
@endphp
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $heading ?? 'Rincian Item dan Pembayaran' }}</h4>
        <small class="text-muted">Langkah 2 dari 2 · Pilih produk benih, lalu pilih metode pembayaran.</small>
    </div>
    <a href="{{ $backUrl }}" class="btn btn-secondary">Kembali ke data pembeli</a>
</div>
@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<div class="card mb-3">
    <div class="card-body">
        <strong>{{ $buyer['buyer_name'] ?? '-' }}</strong> · {{ $buyer['organization'] ?? '-' }}
        <div class="small text-muted">{{ collect([$buyer['destination_village'] ?? null, $buyer['destination_district'] ?? null, $buyer['destination_city'] ?? null, $buyer['destination_province'] ?? null])->filter()->implode(', ') }}</div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $storeUrl }}" id="requestForm" enctype="multipart/form-data">
            @csrf
            <h5 class="mb-3">Rincian Item</h5>
            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Kategori tanaman</label>
                    <select id="filter_category" class="form-select">
                        <option value="">Semua kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nama tanaman</label>
                    <select id="filter_plant_name" class="form-select" disabled>
                        <option value="">Semua nama tanaman</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Varietas</label>
                    <select id="filter_variety" class="form-select" disabled>
                        <option value="">Semua varietas</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive mb-3">
                <table class="table table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Nama tanaman</th>
                            <th>Varietas</th>
                            <th>Stok</th>
                            <th>Harga per satuan</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="plant-rows">
                        @php
                            $readyPlants = $plants->filter(function ($plant) {
                                return (float) $plant->stocks->where('status_stok', \App\Models\Stock::STATUS_SIAP)->sum('stok_saat_ini') > 0;
                            })->groupBy(fn ($plant) => $plant->type?->category ?: 'Lainnya');
                        @endphp
                        @foreach($readyPlants as $category => $group)
                            <tr class="table-secondary plant-category-row" data-category="{{ $category }}">
                                <th colspan="5">{{ $category }}</th>
                            </tr>
                            @foreach($group as $plant)
                                @php
                                    $ready = (float) $plant->stocks->where('status_stok', \App\Models\Stock::STATUS_SIAP)->sum('stok_saat_ini');
                                @endphp
                                <tr class="plant-row"
                                    data-category="{{ $plant->type?->category ?: 'Lainnya' }}"
                                    data-name="{{ $plant->type?->name }}"
                                    data-variety="{{ $plant->variety }}"
                                    data-id="{{ $plant->getKey() }}">
                                    <td>{{ $plant->type?->name ?: $plant->name }}</td>
                                    <td>{{ $plant->variety ?: $plant->name }}</td>
                                    <td>{{ number_format($ready, 2) }} {{ $plant->satuanStok?->code }}</td>
                                    <td>Rp {{ number_format((float) $plant->harga_jual, 0, ',', '.') }} / {{ $plant->satuanStok?->code }}</td>
                                    <td><button type="button" class="btn btn-sm btn-outline-success btn-choose-plant" data-id="{{ $plant->getKey() }}" data-name="{{ $plant->displayName() }}">Pilih produk</button></td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div id="lot-box" class="d-none mb-3">
                <h6 id="lot-title" class="mb-2">Stok berdasarkan nomor induk</h6>
                <div id="lot-table"></div>
            </div>
            <div id="cart-hidden"></div>
            <div class="card border-success mb-3">
                <div class="card-body">
                    <h6>Produk yang dipilih</h6>
                    <div id="cart-list" class="small">Belum ada produk ditambahkan.</div>
                    <div class="mt-2">
                        <strong>Total produk:</strong> <span id="total-items">0</span>
                        · <strong>Total kuantitas:</strong> <span id="total-qty">0</span>
                        · <strong>Total biaya:</strong> Rp <span id="total-cost">0</span>
                    </div>
                </div>
            </div>

            <h5 class="mb-3">Pembayaran</h5>
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
                        <option value="cash">Cash</option>
                        <option value="transfer_bank">Non cash / transfer</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3" id="proof-wrap" style="display:none">
                    <label class="form-label">Lampiran bukti pembayaran <span class="text-danger">*</span></label>
                    <input type="file" name="payment_proof" id="payment_proof" class="form-control" accept="image/*,application/pdf">
                </div>
            </div>
            <div id="pay-cash" class="alert alert-info d-none">Silakan lakukan pembayaran di kantor UPTD BBI TPHP SUMBAR.</div>
            <div id="pay-transfer" class="alert alert-info d-none">Silakan lakukan pembayaran melalui Bank ABC dengan nomor rekening 1234567 dan kirimkan bukti pembayaran di bawah ini.</div>
            <div class="text-end">
                <button class="btn btn-success" type="submit">{{ $submitLabel ?? 'Kirim permintaan' }}</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="qtyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Tambahkan produk</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <p class="mb-2" id="qty-induk"></p>
                <label class="form-label">Jumlah produk (kemasan)</label>
                <div class="input-group">
                    <button type="button" class="btn btn-outline-secondary" id="qty-minus">-</button>
                    <input type="number" id="qty-input" class="form-control text-center" min="1" value="1">
                    <button type="button" class="btn btn-outline-secondary" id="qty-plus">+</button>
                </div>
                <div class="small text-muted mt-2">
                    Total produk: <span id="qty-produk">1</span> ·
                    Total kuantitas: <span id="qty-kuantitas">0</span> ·
                    Total biaya: Rp <span id="qty-biaya">0</span>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-success" id="qty-add">Tambahkan</button></div>
        </div>
    </div>
</div>
@php
    $plantFilterData = $plants->map(fn ($p) => [
        'id' => $p->getKey(),
        'category' => $p->type?->category,
        'name' => $p->type?->name,
        'variety' => $p->variety,
    ])->values();
@endphp
@push('scripts')
<script>
const plants = @json($plantFilterData);
const lotsUrl = @json($lotsUrl ?? url('/permintaan/plants'));
let cart = [];
let pendingLot = null;
let currentPlantName = '';

function unique(list) { return [...new Set(list.filter(Boolean))]; }
function fillSelect(el, values, placeholder) {
    el.innerHTML = '<option value="">' + placeholder + '</option>' + values.map(v => '<option value="'+v+'">'+v+'</option>').join('');
}
function applyPlantFilter() {
    const cat = document.getElementById('filter_category').value;
    const name = document.getElementById('filter_plant_name').value;
    const variety = document.getElementById('filter_variety').value;
    document.querySelectorAll('.plant-row').forEach(function (row) {
        const ok = (!cat || row.dataset.category === cat)
            && (!name || row.dataset.name === name)
            && (!variety || row.dataset.variety === variety);
        row.style.display = ok ? '' : 'none';
    });
    document.querySelectorAll('.plant-category-row').forEach(function (header) {
        const visible = [...document.querySelectorAll('.plant-row')].some(function (row) {
            return row.dataset.category === header.dataset.category && row.style.display !== 'none';
        });
        header.style.display = visible ? '' : 'none';
    });
}
document.getElementById('filter_category').addEventListener('change', function () {
    const names = unique(plants.filter(p => !this.value || p.category === this.value).map(p => p.name));
    const nameSel = document.getElementById('filter_plant_name');
    fillSelect(nameSel, names, 'Semua nama tanaman');
    nameSel.disabled = !this.value;
    document.getElementById('filter_variety').disabled = true;
    fillSelect(document.getElementById('filter_variety'), [], 'Semua varietas');
    applyPlantFilter();
});
document.getElementById('filter_plant_name').addEventListener('change', function () {
    const cat = document.getElementById('filter_category').value;
    const varieties = unique(plants.filter(p => (!cat || p.category === cat) && (!this.value || p.name === this.value)).map(p => p.variety));
    const vSel = document.getElementById('filter_variety');
    fillSelect(vSel, varieties, 'Semua varietas');
    vSel.disabled = !this.value;
    applyPlantFilter();
});
document.getElementById('filter_variety').addEventListener('change', applyPlantFilter);

document.querySelectorAll('.btn-choose-plant').forEach(function (btn) {
    btn.addEventListener('click', function () {
        fetch(lotsUrl + '/' + btn.dataset.id + '/lots')
            .then(r => r.json())
            .then(function (data) {
                currentPlantName = data.plant || btn.dataset.name || '';
                document.getElementById('lot-title').textContent = 'Stok · ' + currentPlantName;
                const lots = data.lots || [];
                document.getElementById('lot-box').classList.remove('d-none');
                document.getElementById('lot-table').innerHTML = lots.length ? '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>No induk</th><th>Tanggal lulus</th><th>Total stok saat ini</th><th>Isi kemasan</th><th>Total produk</th><th>Harga per satuan</th><th>Tanggal masa kadaluarsa</th><th></th><th></th></tr></thead><tbody>' +
                    lots.map(function (l) {
                        const price = Number(l.unit_price || 0);
                        return '<tr><td>'+l.nomor_induk+'</td><td>'+l.tanggal_lulus+'</td><td>'+Number(l.total_stok).toFixed(2)+' '+(l.unit||'')+'</td><td>'+Number(l.isi_kemasan).toFixed(2)+' '+(l.unit||'')+'</td><td>'+l.total_produk+' kemasan</td><td>Rp '+price.toLocaleString('id-ID')+(l.unit ? ' / '+l.unit : '')+'</td><td>'+l.tanggal_kadaluarsa+'</td><td><a class="btn btn-sm btn-outline-success" target="_blank" href="/sertifikat-benih/'+l.stock_id+'">Lihat sertifikat</a></td><td><button type="button" class="btn btn-sm btn-success btn-add-lot" data-stock="'+l.stock_id+'" data-plant="'+currentPlantName.replace(/"/g, '&quot;')+'" data-induk="'+l.nomor_induk+'" data-size="'+l.isi_kemasan+'" data-unit="'+(l.unit||'')+'" data-price="'+l.unit_price+'" data-max="'+l.total_produk+'">Tambahkan produk</button></td></tr>';
                    }).join('') + '</tbody></table></div>' : '<p class="text-muted">Belum ada stok aktif.</p>';
                document.querySelectorAll('.btn-add-lot').forEach(bindAddLot);
            });
    });
});

function bindAddLot(btn) {
    btn.addEventListener('click', function () {
        pendingLot = {
            stock_id: btn.dataset.stock,
            plant: currentPlantName || btn.dataset.plant || '',
            induk: btn.dataset.induk,
            size: parseFloat(btn.dataset.size || 0),
            unit: btn.dataset.unit,
            price: parseFloat(btn.dataset.price || 0),
            max: parseInt(btn.dataset.max || 0, 10)
        };
        document.getElementById('qty-induk').textContent = (pendingLot.plant ? pendingLot.plant + ' · ' : '') + 'No induk ' + pendingLot.induk;
        document.getElementById('qty-input').value = 1;
        document.getElementById('qty-input').max = pendingLot.max;
        updateQtyPreview();
        new bootstrap.Modal(document.getElementById('qtyModal')).show();
    });
}
function updateQtyPreview() {
    const qty = Math.max(1, parseInt(document.getElementById('qty-input').value || '1', 10));
    document.getElementById('qty-produk').textContent = qty;
    document.getElementById('qty-kuantitas').textContent = (qty * (pendingLot?.size || 0)).toFixed(2) + ' ' + (pendingLot?.unit || '');
    document.getElementById('qty-biaya').textContent = (qty * (pendingLot?.size || 0) * (pendingLot?.price || 0)).toLocaleString('id-ID');
}
document.getElementById('qty-minus').addEventListener('click', function () {
    const el = document.getElementById('qty-input');
    el.value = Math.max(1, parseInt(el.value || '1', 10) - 1);
    updateQtyPreview();
});
document.getElementById('qty-plus').addEventListener('click', function () {
    const el = document.getElementById('qty-input');
    el.value = Math.min(pendingLot?.max || 1, parseInt(el.value || '1', 10) + 1);
    updateQtyPreview();
});
document.getElementById('qty-input').addEventListener('input', updateQtyPreview);
document.getElementById('qty-add').addEventListener('click', function () {
    if (!pendingLot) return;
    const qty = Math.max(1, parseInt(document.getElementById('qty-input').value || '1', 10));
    cart.push({...pendingLot, qty});
    renderCart();
    bootstrap.Modal.getInstance(document.getElementById('qtyModal'))?.hide();
});
function renderCart() {
    const wrap = document.getElementById('cart-hidden');
    wrap.innerHTML = '';
    let items = 0, qty = 0, cost = 0;
    const lines = cart.map(function (row, i) {
        items += row.qty;
        qty += row.qty * row.size;
        cost += row.qty * row.size * row.price;
        wrap.insertAdjacentHTML('beforeend', '<input type="hidden" name="lots['+i+'][stock_id]" value="'+row.stock_id+'"><input type="hidden" name="lots['+i+'][quantity]" value="'+row.qty+'">');
        return '<tr><td>'+(row.plant || '-')+'</td><td>'+row.induk+'</td><td>'+row.qty+' kemasan</td><td>'+(row.qty * row.size).toFixed(2)+' '+row.unit+'</td></tr>';
    });
    document.getElementById('cart-list').innerHTML = lines.length
        ? '<div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Nama tanaman-varietas</th><th>No induk</th><th>Jumlah</th><th>Kuantitas</th></tr></thead><tbody>'+lines.join('')+'</tbody></table></div>'
        : 'Belum ada produk ditambahkan.';
    document.getElementById('total-items').textContent = items;
    document.getElementById('total-qty').textContent = qty.toFixed(2);
    document.getElementById('total-cost').textContent = cost.toLocaleString('id-ID');
    document.getElementById('total_amount').value = cost.toFixed(2);
    document.getElementById('total_amount_display').value = 'Rp ' + cost.toLocaleString('id-ID');
}
document.getElementById('payment_method').addEventListener('change', function () {
    document.getElementById('pay-cash').classList.toggle('d-none', this.value !== 'cash');
    document.getElementById('pay-transfer').classList.toggle('d-none', this.value !== 'transfer_bank');
    document.getElementById('proof-wrap').style.display = this.value === 'transfer_bank' ? '' : 'none';
    document.getElementById('payment_proof').required = this.value === 'transfer_bank';
});
document.getElementById('requestForm').addEventListener('submit', function (e) {
    if (!cart.length) {
        e.preventDefault();
        alert('Tambahkan minimal satu produk.');
    }
});
</script>
@endpush
