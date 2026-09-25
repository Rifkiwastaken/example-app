@extends('layouts.app')
@section('title', 'Stok Produk - SIBESTI')
@section('content')
@php
    $unit = $plant->satuanStok?->code ?: '';
    $groups = [
        'aktif' => $packagings->filter(fn ($p) => $p->status_kemasan === \App\Models\StockPackaging::STATUS_TERSEDIA && ! $p->hold_for_request && ! $p->hold_for_recert && ! $p->isExpiredHeld()),
        'ditahan' => $packagings->filter(fn ($p) => $p->status_kemasan !== \App\Models\StockPackaging::STATUS_DISALURKAN && ($p->hold_for_request || $p->hold_for_recert || $p->isExpiredHeld())),
        'terjual' => $packagings->where('status_kemasan', \App\Models\StockPackaging::STATUS_DISALURKAN),
        'tidak_aktif' => $packagings->filter(fn ($p) => in_array($p->status_kemasan, [\App\Models\StockPackaging::STATUS_TIDAK_AKTIF, \App\Models\StockPackaging::STATUS_AFKIR], true)),
    ];
@endphp
<div class="d-flex justify-content-between mb-4">
    <div>
        <h4 class="mb-0">Stok produk · {{ $stock->nomor_induk ?: $stock->no_label_resmi }}</h4>
        <small class="text-muted">{{ $plant->name }}{{ $plant->variety ? ' - '.$plant->variety : '' }}</small>
    </div>
    <a href="{{ route('seed-stock.show', [$plant, 'tab'=>'lots']) }}" class="btn btn-secondary">Kembali</a>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<form id="bulkForm" method="POST">
    @csrf
    <div class="mb-3 d-flex flex-wrap gap-2">
        <button formaction="{{ route('seed-stock.packaging.print', [$plant, $stock]) }}" class="btn btn-outline-primary btn-sm">Print label</button>
        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#adjustModal">Penyesuaian stok</button>
        <button type="button" class="btn btn-outline-warning btn-sm" id="recert" data-bs-toggle="modal" data-bs-target="#recertModal">Sertifikasi ulang</button>
    </div>
    <div class="mb-3 d-flex flex-wrap gap-2" id="status-toggle">
        <button type="button" class="btn btn-sm btn-dark status-btn" data-status="all">Semua</button>
        <button type="button" class="btn btn-sm btn-outline-success status-btn" data-status="aktif">Aktif ({{ $groups['aktif']->count() }})</button>
        <button type="button" class="btn btn-sm btn-outline-warning status-btn" data-status="ditahan">Di tahan ({{ $groups['ditahan']->count() }})</button>
        <button type="button" class="btn btn-sm btn-outline-primary status-btn" data-status="terjual">Terjual ({{ $groups['terjual']->count() }})</button>
        <button type="button" class="btn btn-sm btn-outline-secondary status-btn" data-status="tidak_aktif">Tidak aktif ({{ $groups['tidak_aktif']->count() }})</button>
    </div>

    @foreach(['aktif' => 'Aktif', 'ditahan' => 'Di tahan', 'terjual' => 'Terjual', 'tidak_aktif' => 'Tidak aktif'] as $key => $title)
        @php $items = $groups[$key]; @endphp
        <div class="status-group mb-4" data-group="{{ $key }}">
            <h6 class="text-muted">{{ $title }}</h6>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><input type="checkbox" onclick="document.querySelectorAll('.pkg-{{ $key }}').forEach(c=>c.checked=this.checked)"></th>
                            <th>No Induk</th>
                            <th>No Label Seri</th>
                            <th>Isi kemasan</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $pkg)
                            <tr>
                                <td><input class="pkg pkg-{{ $key }}" type="checkbox" name="packaging_ids[]" value="{{ $pkg->id }}"></td>
                                <td>{{ $stock->nomor_induk ?: $stock->no_label_resmi }}</td>
                                <td>{{ $pkg->no_label_seri }}</td>
                                <td>{{ number_format((float) $pkg->kapasitas_per_kemasan, 2) }} {{ $unit }}</td>
                                <td><span class="badge bg-{{ $pkg->statusBadge() }}">{{ $pkg->statusLabel() }}</span></td>
                                <td>
                                    @if($pkg->sales->isNotEmpty())
                                        Terjual
                                    @elseif($pkg->alasan_penyesuaian)
                                        {{ $pkg->alasanPenyesuaianLabel() }}
                                    @elseif($pkg->hold_for_recert)
                                        Sertifikasi ulang
                                    @elseif($pkg->isExpiredHeld())
                                        Melewati masa kadaluarsa
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('public.seed-label', $pkg) }}" class="btn btn-sm btn-outline-success" target="_blank">Detail label</a>
                                    <a href="{{ route('seed-stock.packaging.show', $pkg) }}" class="btn btn-sm btn-outline-info">Detail stok</a>
                                    @if($pkg->sales->isNotEmpty())
                                        <a href="{{ route('sales.show', $pkg->sales->first()->receipt_number) }}" class="btn btn-sm btn-outline-secondary">Detail penjualan</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">Tidak ada produk dengan status ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    <div class="modal fade" id="adjustModal" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Penyesuaian stok</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
            <div class="modal-body">
                <label class="form-label">Alasan <span class="text-danger">*</span></label>
                <select name="reason_code" id="reason-code" class="form-select" form="bulkForm">
                    <option value="">Pilih alasan</option>
                    @foreach(\App\Models\StockPackaging::ALASAN_PENYESUAIAN as $code => $label)
                        <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                </select>
                <div class="mt-3 d-none" id="reason-other-wrap">
                    <label class="form-label">Sebutkan alasan</label>
                    <input type="text" name="reason_other" class="form-control" form="bulkForm">
                </div>
            </div>
            <div class="modal-footer">
                <button form="bulkForm" formaction="{{ route('seed-stock.packaging.adjust', [$plant, $stock]) }}" class="btn btn-danger" onclick="if(!document.querySelectorAll('.pkg:checked').length){alert('Centang produk yang ingin disesuaikan.');return false;}">Simpan penyesuaian</button>
            </div>
        </div></div>
    </div>

    <div class="modal fade" id="recertModal" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Sertifikasi ulang</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
            <div class="modal-body">
                <p class="small text-muted">Centang stok produk yang ingin dirangkum menjadi satu hasil uji lab baru.</p>
                <label class="form-label">Alasan sertifikasi ulang <span class="text-danger">*</span></label>
                <textarea name="reason" id="recert-reason" class="form-control" rows="3" form="bulkForm" placeholder="Contoh: melewati masa edar, hasil uji ulang BPSB">{{ old('reason') }}</textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" id="recert-continue" data-url="{{ route('seed-stock.recertify', [$plant, $stock]) }}">Lanjutkan</button>
            </div>
        </div></div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.getElementById('reason-code')?.addEventListener('change', function () {
    document.getElementById('reason-other-wrap').classList.toggle('d-none', this.value !== 'lainnya');
});
document.querySelectorAll('.status-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
        const status = btn.dataset.status;
        document.querySelectorAll('.status-group').forEach((group) => {
            group.classList.toggle('d-none', status !== 'all' && group.dataset.group !== status);
        });
        document.querySelectorAll('.status-btn').forEach((other) => other.classList.remove('btn-dark'));
        btn.classList.add('btn-dark');
    });
});
document.getElementById('recert-continue')?.addEventListener('click', function () {
    if (!document.querySelectorAll('.pkg:checked').length) {
        alert('Centang nomor label produk yang ingin disertifikasi ulang.');
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
