@extends('layouts.app')

@section('title', 'Tambahkan Label Sertifikat - SIBESTI')

@php
    $unit = $plant->satuanStok?->code ?: 'kg';
    $totalVolume = (float) ($prefill['total_volume'] ?? $stock->stok_saat_ini);
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Tambahkan Label Sertifikat Benih</h4>
        <small class="text-muted">{{ $plant->name }}{{ $plant->variety ? ' - '.$plant->variety : '' }} · {{ $stock->nomor_induk ?: $stock->no_label_resmi }}</small>
    </div>
    <a href="{{ route('seed-stock.show', [$plant, 'tab' => 'lots']) }}" class="btn btn-secondary">Kembali</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('seed-stock.label.store', [$plant, $stock]) }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nomor induk <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" value="{{ $stock->postHarvest?->nomor_induk ?: $stock->nomor_induk }}" readonly>
                    <input type="hidden" name="id_label_rilis" value="{{ old('id_label_rilis', $prefill['id_label_rilis']) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nomor lot <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_lot" class="form-control" value="{{ old('nomor_lot', $prefill['nomor_lot']) }}" required readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">No sertifikat BPSB final <span class="text-danger">*</span></label>
                    <input type="text" name="no_sertifikat_bpsb_final" class="form-control" value="{{ old('no_sertifikat_bpsb_final', $prefill['no_sertifikat_bpsb_final']) }}" required readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Warna label <span class="text-danger">*</span></label>
                    <select name="warna_label" class="form-select" required>
                        <option value="">Pilih warna</option>
                        @foreach(\App\Models\CertificationReport::WARNA_LABEL as $value => $label)
                            <option value="{{ $value }}" {{ old('warna_label', $prefill['warna_label'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tipe kemasan <span class="text-danger">*</span></label>
                    <select name="tipe_kemasan" class="form-select" required>
                        <option value="">Pilih tipe</option>
                        @foreach(\App\Models\CertificationReport::TIPE_KEMASAN as $value => $label)
                            <option value="{{ $value }}" {{ old('tipe_kemasan', $prefill['tipe_kemasan'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Ukuran kemasan ({{ $unit }}) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" max="{{ $totalVolume }}" name="ukuran_kemasan_retail_kg" id="ukuran" class="form-control" value="{{ old('ukuran_kemasan_retail_kg', $prefill['ukuran_kemasan_retail_kg'] ?? '') }}" placeholder="Tidak melebihi {{ number_format($totalVolume, 2) }} {{ $unit }}" required>
                    <small class="text-muted">Total hasil uji: {{ number_format($totalVolume, 2) }} {{ $unit }}</small>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jumlah lembar label dicetak <span class="text-danger">*</span></label>
                    <input type="number" min="1" max="5000" name="jumlah_lembar_label_dicetak" id="jumlah-lembar" class="form-control" value="{{ old('jumlah_lembar_label_dicetak', $prefill['jumlah_lembar_label_dicetak'] ?? '') }}" required>
                    <small class="text-muted">Otomatis dari total volume dibagi ukuran kemasan.</small>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">No seri label <span class="text-danger">*</span></label>
                    <input type="text" name="no_seri_label_awal" id="seri-awal" class="form-control" value="{{ old('no_seri_label_awal', $prefill['no_seri_label_awal'] ?? '') }}" placeholder="Contoh: 500" required>
                    <small class="text-muted">Nomor urut otomatis menempel di akhir no seri label.</small>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Preview nomor label awal</label>
                    <input type="text" id="preview-awal" class="form-control bg-light" value="" readonly tabindex="-1">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Preview nomor label akhir</label>
                    <input type="text" id="preview-akhir" class="form-control bg-light" value="" readonly tabindex="-1">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal pemasangan label</label>
                    <input type="date" name="tgl_pemasangan_label" class="form-control" value="{{ old('tgl_pemasangan_label', $prefill['tgl_pemasangan_label'] ?? date('Y-m-d')) }}">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Lampiran QR label <span class="text-danger">*</span></label>
                    <input type="file" name="lampiran_qr_label" class="form-control" accept="image/*,.pdf" required>
                    <small class="text-muted">Berkas QR ini yang ditampilkan pada stiker kemasan.</small>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('seed-stock.show', [$plant, 'tab' => 'lots']) }}" class="btn btn-secondary">Batal</a>
                <button class="btn btn-success" type="submit">Simpan label benih</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const total = {{ json_encode($totalVolume) }};
    const ukuran = document.getElementById('ukuran');
    const jumlah = document.getElementById('jumlah-lembar');
    const awal = document.getElementById('seri-awal');
    const previewAwal = document.getElementById('preview-awal');
    const previewAkhir = document.getElementById('preview-akhir');

    function calcSheets() {
        const size = parseFloat(ukuran.value);
        if (!size || size <= 0) return;
        if (size > total) {
            ukuran.value = total;
        }
        jumlah.value = Math.max(1, Math.floor(total / parseFloat(ukuran.value)));
        calcEnd();
    }

    function calcEnd() {
        const start = (awal.value || '').trim();
        const sheets = parseInt(jumlah.value, 10) || 0;
        if (!start || sheets < 1) {
            previewAwal.value = '';
            previewAkhir.value = '';
            return;
        }
        previewAwal.value = start + '1';
        previewAkhir.value = start + String(sheets);
    }

    ukuran.addEventListener('input', calcSheets);
    jumlah.addEventListener('input', calcEnd);
    awal.addEventListener('input', calcEnd);
    calcEnd();
})();
</script>
@endpush
