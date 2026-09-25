@extends('layouts.app')
@section('title', 'Penjemputan Benih - SIBESTI')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4 class="mb-0">Penjemputan {{ $seedRequest->request_number }}</h4>
    <a href="{{ route('seed-requests.show', $seedRequest) }}" class="btn btn-secondary">Kembali</a>
</div>
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
<div class="card mb-3">
    <div class="card-header">Informasi pembeli</div>
    <div class="card-body">
        <p>{{ $seedRequest->buyer_name }} · {{ $seedRequest->organization }}</p>
        <p class="mb-0">{{ collect([$seedRequest->destination_village,$seedRequest->destination_district,$seedRequest->destination_city,$seedRequest->destination_province])->filter()->implode(', ') }}</p>
    </div>
</div>
<div class="card mb-3">
    <div class="card-header">Rincian item</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Varietas</th><th>No induk</th><th>No label</th></tr></thead>
            <tbody>
                @foreach($seedRequest->packagings as $pkg)
                    <tr>
                        <td>{{ $pkg->stock?->plant?->displayName() }}</td>
                        <td>{{ $pkg->stock?->nomor_induk }}</td>
                        <td>{{ $pkg->no_label_seri }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="card mb-3">
    <div class="card-header">Pembayaran</div>
    <div class="card-body">
        <p>Metode: {{ $seedRequest->payment_method === 'transfer_bank' ? 'Non cash' : 'Cash' }}</p>
        <p>Total: Rp {{ number_format((float) $seedRequest->total_amount, 0, ',', '.') }}</p>
        @if($seedRequest->payment_proof)
            <p><a href="{{ asset('storage/'.$seedRequest->payment_proof) }}" target="_blank">Lihat bukti pembayaran</a></p>
        @endif
    </div>
</div>
<form method="POST" action="{{ route('seed-requests.complete', $seedRequest) }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="receipt_number" value="{{ \App\Models\SaleItem::generateReceiptNumber() }}">
    <input type="hidden" name="sale_date" value="{{ date('Y-m-d') }}">
    <input type="hidden" name="payment_method" value="{{ $seedRequest->payment_method ?: 'cash' }}">
    @foreach($seedRequest->packagings as $pkg)
        <input type="hidden" name="packaging_ids[]" value="{{ $pkg->id }}">
    @endforeach
    <div class="mb-3">
        <label class="form-label">Status pembayaran <span class="text-danger">*</span></label>
        <select name="payment_status" class="form-select" required>
            <option value="lunas">Lunas</option>
            <option value="batal">Tidak lunas / batal</option>
        </select>
    </div>
    <button class="btn btn-success">Simpan penjemputan</button>
</form>
@endsection
