@extends('layouts.app')

@section('title', 'Riwayat Penjualan Benih - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Riwayat Penjualan Benih</h4>
    <a href="{{ route('sales.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-2"></i>Catat Penjualan Baru
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Dashboard ringkasan penjualan --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fas fa-receipt text-primary fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 small">Total Transaksi</p>
                        <h4 class="mb-0">{{ number_format($totalTransactions ?? 0, 0, ',', '.') }}</h4>
                        <small class="text-muted">transaksi</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fas fa-weight-hanging text-success fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 small">Total Kuantitas Terjual</p>
                        <h4 class="mb-0">{{ number_format($totalQuantitySold ?? 0, 2, ',', '.') }}</h4>
                        <small class="text-muted">kg / unit</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="fas fa-coins text-info fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 small">Total Pendapatan</p>
                        <h4 class="mb-0">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</h4>
                        <small class="text-muted">sesuai filter periode</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light py-3">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('sales.index') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label small">Tanggal Dari</label>
                <input type="date" class="form-control form-control-sm" name="date_from" value="{{ request('date_from', $dateFrom?->format('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Tanggal Sampai</label>
                <input type="date" class="form-control form-control-sm" name="date_to" value="{{ request('date_to', $dateTo?->format('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Kategori</label>
                <select class="form-select form-select-sm" name="category" id="filter_category">
                    <option value="">Semua Kategori</option>
                    @foreach($categories ?? [] as $cat)
                        <option value="{{ $cat }}" {{ ($category ?? '') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Nama tanaman</label>
                <select class="form-select form-select-sm" name="plant_name" id="filter_plant_name" {{ empty($category) ? 'disabled' : '' }}>
                    <option value="">Semua nama tanaman</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Varietas</label>
                <select class="form-select form-select-sm" name="variety" id="filter_variety" {{ empty($plantName) ? 'disabled' : '' }}>
                    <option value="">Semua varietas</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Cari struk / pembeli</label>
                <input type="text" class="form-control form-control-sm" name="search" value="{{ request('search', $search ?? '') }}" placeholder="No. struk atau pembeli">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search me-1"></i>Terapkan
                </button>
                <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
        @if(request()->hasAny(['date_from', 'date_to', 'category', 'plant_name', 'variety', 'search']))
            <p class="text-muted small mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>Menampilkan data sesuai filter periode dan kriteria. Dashboard di atas juga mengikuti filter.
            </p>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-light py-3">
        <h5 class="mb-0">Riwayat transaksi (struk terlama ke terbaru)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No. struk</th>
                        <th>Tanggal</th>
                        <th>Pembeli</th>
                        <th>Nama tanaman-varietas</th>
                        <th>Jumlah stok produk yang terjual</th>
                        <th>Kuantitas</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipts as $row)
                    <tr>
                        <td><code>{{ $row->receipt_number }}</code></td>
                        <td>{{ \Illuminate\Support\Carbon::parse($row->sale_date)->format('d M Y') }}</td>
                        <td>{{ $row->buyer_name }}</td>
                        <td>{{ $row->plant_names ?: '-' }}</td>
                        <td>{{ number_format((int) ($row->product_count ?? 0), 0) }} produk</td>
                        <td>{{ number_format((float) $row->total_quantity, 2) }}</td>
                        <td>Rp {{ number_format((float) $row->total_amount, 0, ',', '.') }}</td>
                        <td>
                            <a href="{{ route('sales.show', $row->first_id) }}" class="btn btn-sm btn-outline-success">Detail penjualan</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="text-muted">
                                <p>Belum ada transaksi penjualan.</p>
                                <a href="{{ route('sales.create') }}" class="btn btn-success">Catat Penjualan Pertama</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $receipts->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const plants = @json($plantFilterData);
    const selectedName = @json($plantName);
    const selectedVariety = @json($variety);
    const cat = document.getElementById('filter_category');
    const nameSel = document.getElementById('filter_plant_name');
    const varietySel = document.getElementById('filter_variety');
    function unique(list) { return [...new Set(list.filter(Boolean))]; }
    function fillSelect(el, values, placeholder, selected) {
        el.innerHTML = '<option value="">' + placeholder + '</option>' + values.map(function (value) {
            const isSelected = selected && String(value) === String(selected) ? ' selected' : '';
            return '<option value="'+value+'"'+isSelected+'>'+value+'</option>';
        }).join('');
    }
    function refreshNames(keepVariety) {
        const names = unique(plants.filter(function (item) {
            return !cat.value || item.category === cat.value;
        }).map(function (item) { return item.name; }));
        fillSelect(nameSel, names, 'Semua nama tanaman', selectedName);
        nameSel.disabled = !cat.value;
        if (!keepVariety) {
            fillSelect(varietySel, [], 'Semua varietas', '');
            varietySel.disabled = true;
        }
    }
    function refreshVarieties() {
        const varieties = unique(plants.filter(function (item) {
            return (!cat.value || item.category === cat.value)
                && (!nameSel.value || item.name === nameSel.value);
        }).map(function (item) { return item.variety; }));
        fillSelect(varietySel, varieties, 'Semua varietas', selectedVariety);
        varietySel.disabled = !nameSel.value;
    }
    cat.addEventListener('change', function () { refreshNames(false); });
    nameSel.addEventListener('change', refreshVarieties);
    if (cat.value) {
        refreshNames(true);
        if (nameSel.value) {
            refreshVarieties();
        }
    }
})();
</script>
@endpush

