@extends('layouts.app')
@section('title', 'Kemasan Stok - SIBESTI')
@section('content')
@php $unit = $plant->satuanStok?->code ?: ''; @endphp
<div class="d-flex justify-content-between mb-4">
    <h4 class="mb-0">Isi Kemasan Stok · {{ $stock->no_label_resmi }}</h4>
    <a href="{{ route('seed-stock.show', [$plant, 'tab'=>'lots']) }}" class="btn btn-secondary">Kembali</a>
</div>
<div class="card"><div class="card-body">
    <p>Sisa belum dikemas: <strong>{{ number_format($stock->unpackagedQuantity(), 2) }} {{ $unit }}</strong></p>
    <form method="POST" action="{{ route('seed-stock.packaging.store', [$plant, $stock]) }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Tipe kemasan</label>
            <select name="jenis_kemasan" class="form-select" required>
                @foreach(\App\Models\StockPackaging::JENIS as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Kapasitas per kemasan ({{ $unit }})</label>
            <input type="number" step="0.01" name="kapasitas_per_kemasan" id="kapasitas" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Jumlah kemasan</label>
            <input type="number" name="jumlah_kemasan" id="jumlah" class="form-control" min="1" required>
        </div>
        <p class="text-muted" id="totalHint">Total akan dihitung otomatis.</p>
        <div class="mb-3">
            <label class="form-label">Nomor label seri (awalan, xxx diganti otomatis)</label>
            <input name="label_prefix" class="form-control" value="{{ $defaultPrefix }}" required>
        </div>
        <button class="btn btn-success">Simpan kemasan</button>
    </form>
</div></div>
@endsection
@push('scripts')
<script>
function hint(){
    const k=parseFloat(document.getElementById('kapasitas').value||0);
    const j=parseInt(document.getElementById('jumlah').value||0,10);
    const jenis=document.querySelector('[name=jenis_kemasan] option:checked')?.textContent||'kemasan';
    document.getElementById('totalHint').textContent = j&&k ? `(${j} ${jenis} x ${k} = ${(j*k).toFixed(2)} akan dialokasikan)` : 'Total akan dihitung otomatis.';
}
document.getElementById('kapasitas').addEventListener('input', hint);
document.getElementById('jumlah').addEventListener('input', hint);
document.querySelector('[name=jenis_kemasan]').addEventListener('change', hint);
</script>
@endpush
