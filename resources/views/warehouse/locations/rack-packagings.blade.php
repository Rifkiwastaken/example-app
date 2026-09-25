@extends('layouts.app')
@section('title', 'Stok Produk - SIBESTI')
@section('content')
@php
    $statusCounts = $statusCounts ?? collect();
    $statusFilter = $statusFilter ?? 'all';
    $grouped = $packagings->groupBy(fn ($p) => $p->stock?->nomor_induk ?: ($p->stock?->id ?: 'tanpa-induk'));
@endphp
<div class="d-flex justify-content-between mb-4">
    <div>
        <h4 class="mb-0">Stok produk · {{ $warehouse->name }} · {{ $bin->name }}</h4>
        <small class="text-muted">Dikelompokkan berdasarkan nomor induk</small>
    </div>
    <a href="{{ route('warehouse-locations.show', $warehouse) }}" class="btn btn-secondary">Kembali</a>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<form id="bulkForm" method="POST">
    @csrf
    <div class="mb-3 d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#adjustModal">Penyesuaian stok</button>
        <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#recertModal">Sertifikasi ulang</button>
    </div>
    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="{{ route('warehouse-locations.racks.packagings', [$warehouse, $bin, 'status' => 'all']) }}" class="btn btn-sm {{ $statusFilter === 'all' ? 'btn-dark' : 'btn-outline-dark' }}">Semua</a>
        <a href="{{ route('warehouse-locations.racks.packagings', [$warehouse, $bin, 'status' => 'tersedia']) }}" class="btn btn-sm {{ $statusFilter === 'tersedia' ? 'btn-success' : 'btn-outline-success' }}">Aktif</a>
        <a href="{{ route('warehouse-locations.racks.packagings', [$warehouse, $bin, 'status' => 'sudah_disalurkan']) }}" class="btn btn-sm {{ $statusFilter === 'sudah_disalurkan' ? 'btn-primary' : 'btn-outline-primary' }}">Terjual</a>
        <a href="{{ route('warehouse-locations.racks.packagings', [$warehouse, $bin, 'status' => 'tidak_aktif']) }}" class="btn btn-sm {{ in_array($statusFilter, ['tidak_aktif','afkir_rusak']) ? 'btn-secondary' : 'btn-outline-secondary' }}">Tidak aktif</a>
    </div>

    @forelse($grouped as $induk => $items)
        @php
            $first = $items->first();
            $stock = $first->stock;
            $plantName = trim(($stock?->plant?->name ?: '').($stock?->plant?->variety ? ' - '.$stock->plant->variety : ''));
            $aktif = $items->where('status_kemasan', \App\Models\StockPackaging::STATUS_TERSEDIA)->count();
            $nonaktif = $items->whereIn('status_kemasan', [\App\Models\StockPackaging::STATUS_TIDAK_AKTIF, \App\Models\StockPackaging::STATUS_AFKIR])->count();
            $reportRoute = $stock?->productionReportRoute();
        @endphp
        <div class="card mb-4">
            <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <strong>{{ $plantName ?: '-' }}</strong>
                    · No induk {{ $induk }}
                    · Tanggal kadaluarsa {{ $stock?->tgl_kedaluwarsa?->format('d M Y') ?: '-' }}
                    · Aktif {{ $aktif }}
                    · Tidak aktif {{ $nonaktif }}
                </div>
                <div class="d-flex flex-wrap gap-1">
                    @if($stock)
                        <a href="{{ route('seed-stock.lab-result', [$stock->plant, $stock]) }}" class="btn btn-sm btn-outline-info">Lihat detail hasil uji lab</a>
                        <a href="{{ route('public.seed-certificate', $stock) }}" class="btn btn-sm btn-outline-secondary" target="_blank">Lihat sertifikat</a>
                    @endif
                    @if($reportRoute)
                        <a href="{{ route('planting-locations.plantings.history-reports', $reportRoute) }}" class="btn btn-sm btn-outline-primary">Lihat detail laporan produksi</a>
                    @endif
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th><input type="checkbox" onclick="document.querySelectorAll('.pkg-{{ md5((string) $induk) }}').forEach(c=>c.checked=this.checked)"></th>
                            <th>No label</th>
                            <th>Isi kemasan</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $pkg)
                            <tr>
                                <td><input class="pkg pkg-{{ md5((string) $induk) }}" type="checkbox" name="packaging_ids[]" value="{{ $pkg->id }}"></td>
                                <td>{{ $pkg->no_label_seri }}</td>
                                <td>{{ number_format((float) $pkg->kapasitas_per_kemasan, 2) }}</td>
                                <td><span class="badge bg-{{ $pkg->statusBadge() }}">{{ $pkg->statusLabel() }}</span></td>
                                <td>
                                    @if($pkg->sales->isNotEmpty())
                                        Terjual
                                    @elseif($pkg->alasan_penyesuaian)
                                        {{ $pkg->alasanPenyesuaianLabel() }}
                                    @elseif($pkg->hold_for_request)
                                        Ditahan permintaan
                                    @elseif($pkg->isExpiredHeld())
                                        Melewati masa kadaluarsa
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('public.seed-label', $pkg) }}" class="btn btn-sm btn-outline-success mb-1" target="_blank">Detail label</a>
                                    <a href="{{ route('seed-stock.packaging.show', $pkg) }}" class="btn btn-sm btn-outline-info mb-1">Detail stok</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="alert alert-info">Belum ada produk di blok ini.</div>
    @endforelse

    <div class="modal fade" id="adjustModal" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Penyesuaian stok</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <p class="small text-muted">Centang produk di tabel, lalu pilih alasan.</p>
                <select name="reason_code" class="form-select mb-2" form="bulkForm">
                    <option value="">Pilih alasan</option>
                    @foreach(\App\Models\StockPackaging::ALASAN_PENYESUAIAN as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <input name="reason_other" class="form-control" form="bulkForm" placeholder="Alasan lain">
            </div>
            <div class="modal-footer">
                <button form="bulkForm" formaction="{{ route('warehouse-locations.racks.adjust', [$warehouse, $bin]) }}" class="btn btn-danger" onclick="if(!document.querySelectorAll('.pkg:checked').length){alert('Centang produk yang ingin disesuaikan.');return false;}">Simpan penyesuaian</button>
            </div>
        </div></div>
    </div>

    <div class="modal fade" id="recertModal" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Sertifikasi ulang</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <p class="small text-muted">Pilih stok produk dari satu lot yang sama. Data terpilih dirangkum menjadi satu hasil uji lab baru.</p>
                <label class="form-label">Alasan sertifikasi ulang <span class="text-danger">*</span></label>
                <textarea name="reason" id="recert-reason" class="form-control" rows="3" form="bulkForm" placeholder="Contoh: melewati masa edar">{{ old('reason') }}</textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" id="recert-continue" data-url="{{ route('warehouse-locations.racks.recertify', [$warehouse, $bin]) }}">Lanjutkan</button>
            </div>
        </div></div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.getElementById('recert-continue')?.addEventListener('click', function () {
    if (!document.querySelectorAll('.pkg:checked').length) {
        alert('Centang produk yang ingin disertifikasi ulang.');
        return;
    }
    const reason = document.getElementById('recert-reason');
    if (!reason || !reason.value.trim()) {
        alert('Isi alasan sertifikasi ulang.');
        return;
    }
    if (!confirm('Yakin melakukan sertifikasi ulang? data yang dipilih statusnya akan diperbarui menjadi tidak aktif, pastikan agar data hasil uji sudah ada')) {
        return;
    }
    const form = document.getElementById('bulkForm');
    form.setAttribute('action', this.getAttribute('data-url'));
    form.submit();
});
</script>
@endpush
