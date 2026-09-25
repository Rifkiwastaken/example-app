@extends('layouts.app')

@section('title', 'Pilih Kemasan Permintaan - SIBESTI')

@section('content')
<div class="d-flex justify-content-between mb-4">
    <h4 class="mb-0">Pilih kemasan untuk {{ $seedRequest->request_number }}</h4>
    <a href="{{ route('seed-requests.show', $seedRequest) }}" class="btn btn-secondary">Kembali</a>
</div>
<p>Pembeli: <strong>{{ $seedRequest->buyer_name }}</strong> · {{ $seedRequest->organization }}</p>
<form method="POST" action="{{ route('seed-requests.approve', $seedRequest) }}">
    @csrf
    @foreach($seedRequest->items as $item)
        <div class="card mb-3">
            <div class="card-header">{{ $item->plant?->name }} — diminta {{ $item->quantity }} {{ $item->unit }}</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm" data-plant="{{ $item->seed_varieties_id }}">
                        <thead><tr><th></th><th>Label</th><th>Qty</th><th>Rak</th></tr></thead>
                        <tbody class="fefo-body" id="fefo-{{ $item->seed_varieties_id }}">
                            <tr><td colspan="4" class="text-muted">Memuat stok FEFO...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
    @error('packaging_ids')<div class="alert alert-danger">{{ $message }}</div>@enderror
    <button class="btn btn-success">Simpan & setujui (tahan stok 30 hari)</button>
</form>
@endsection
@push('scripts')
<script>
const urls = @json($seedRequest->items->mapWithKeys(fn($i) => [$i->seed_varieties_id => route('sales.plant-packagings', $i->seed_varieties_id)]));
Object.keys(urls).forEach(async function (plantId) {
    const res = await fetch(urls[plantId]);
    const data = await res.json();
    const rows = data.packagings || data;
    const body = document.getElementById('fefo-' + plantId);
    if (!Array.isArray(rows) || !rows.length) {
        body.innerHTML = '<tr><td colspan="4" class="text-muted">Tidak ada kemasan siap jual.</td></tr>';
        return;
    }
    body.innerHTML = rows.map(r => `<tr>
        <td><input type="checkbox" name="packaging_ids[]" value="${r.id}"></td>
        <td>${r.no_label_seri || '-'}</td>
        <td>${r.quantity || r.kapasitas_per_kemasan || 0}</td>
        <td>${r.rack || r.rack_name || '-'}</td>
    </tr>`).join('');
});
</script>
@endpush
