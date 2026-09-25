@extends('layouts.app')
@section('title', 'Simpan ke Rak Gudang - SIBESTI')
@section('content')
<div class="d-flex justify-content-between mb-4">
    <h4 class="mb-0">Simpan ke Rak Gudang · {{ $stock->no_label_resmi }}</h4>
    <a href="{{ route('seed-stock.lots.show', [$plant, $stock]) }}" class="btn btn-secondary">Lewati</a>
</div>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('seed-stock.packaging.rack.store', [$plant, $stock]) }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Gudang penyimpanan</label>
            <select name="warehouse_id" id="warehouse_id" class="form-select" required>
                <option value="">Pilih gudang</option>
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->warehouse_id }}">{{ $wh->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Rak gudang</label>
            <select name="rak_gudang_id" id="rak_gudang_id" class="form-select" required>
                <option value="">Pilih gudang dulu</option>
            </select>
        </div>
        <button class="btn btn-success">Simpan ke rak</button>
    </form>
</div></div>
@endsection
@push('scripts')
<script>
const racks = @json($warehouses->mapWithKeys(fn($w) => [$w->warehouse_id => $w->bins->map(fn($b) => ['id'=>$b->warehouse_bin_id,'name'=>$b->name])]));
document.getElementById('warehouse_id').addEventListener('change', function(){
    const select=document.getElementById('rak_gudang_id');
    select.innerHTML='<option value="">Pilih rak</option>';
    (racks[this.value]||[]).forEach(function(r){
        const o=document.createElement('option'); o.value=r.id; o.textContent=r.name; select.appendChild(o);
    });
});
</script>
@endpush
