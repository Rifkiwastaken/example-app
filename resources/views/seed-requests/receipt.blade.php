@extends(auth()->check() ? 'layouts.app' : 'layouts.public')
@section('title', 'Struk '.$seedRequest->request_number)
@section('content')
@php
    $rows = $seedRequest->sales->isNotEmpty() ? $seedRequest->sales : $seedRequest->packagings;
@endphp
<div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
    <div>
        <h4 class="mb-0">Detail Penjualan (No. Struk: {{ $seedRequest->receipt_number ?: $seedRequest->request_number }})</h4>
        <span class="badge bg-success fs-6">LUNAS</span>
    </div>
    <div>
        <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-2"></i>Cetak Struk/Nota</button>
        <a href="javascript:history.back()" class="btn btn-secondary">Kembali</a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <p><strong>Tanggal:</strong> {{ optional($seedRequest->taken_at ?: $seedRequest->request_date)->format('d F Y') }}</p>
                <p><strong>No. Struk:</strong> <code>{{ $seedRequest->receipt_number ?: $seedRequest->request_number }}</code></p>
            </div>
            <div class="col-md-6">
                <p><strong>Status Pembayaran:</strong> <span class="badge bg-success">LUNAS</span></p>
                <p><strong>Metode Bayar:</strong> {{ $seedRequest->payment_method === 'transfer_bank' ? 'Transfer Bank' : 'Cash' }}</p>
            </div>
        </div>
        <hr>
        <h5 class="mb-3">Informasi Pembeli:</h5>
        <div class="row mb-4">
            <div class="col-md-6">
                <p><strong>Nama:</strong> {{ $seedRequest->buyer_name }}</p>
                <p><strong>Instansi:</strong> {{ $seedRequest->organization ?: '-' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Kontak:</strong> {{ $seedRequest->buyer_contact ?: '-' }}</p>
            </div>
        </div>
        <hr>
        <h5 class="mb-3">Daftar record benih yang terjual</h5>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nama tanaman-varietas</th>
                        <th>Nomor induk</th>
                        <th>No label</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                        <th class="d-print-none">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        @php
                            $pkg = $row->packaging ?? $row;
                            $plant = $pkg?->stock?->plant;
                            $qty = (float) ($row->quantity ?? $pkg?->kapasitas_per_kemasan ?? 0);
                            $unit = $row->unit ?? ($plant?->satuanStok?->code ?? '');
                            $price = (float) ($row->unit_price ?? $plant?->harga_jual ?? 0);
                            $subtotal = (float) ($row->subtotal ?? ($qty * $price));
                        @endphp
                        <tr>
                            <td>{{ $plant?->displayName() }}</td>
                            <td>{{ $pkg?->stock?->nomor_induk ?: '-' }}</td>
                            <td>{{ $pkg?->no_label_seri ?: '-' }}</td>
                            <td>{{ number_format($qty, 2) }} {{ $unit }}</td>
                            <td>Rp {{ number_format($price, 0, ',', '.') }}</td>
                            <td><strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong></td>
                            <td class="d-print-none">
                                @if($pkg)
                                    <a href="{{ route('public.seed-label', $pkg) }}" class="btn btn-sm btn-outline-success" target="_blank">Lihat label benih</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <tr class="table-info">
                        <td colspan="5" class="text-end"><strong>Total</strong></td>
                        <td colspan="2"><strong>Rp {{ number_format((float) $seedRequest->total_amount, 0, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <hr>
        <h5 class="mb-3">Informasi Pembayaran:</h5>
        <p><strong>Metode Bayar:</strong> {{ $seedRequest->payment_method === 'transfer_bank' ? 'Transfer Bank' : 'Cash' }}</p>
        @if($seedRequest->payment_method === 'transfer_bank' && $seedRequest->payment_proof)
            @php $proofUrl = asset('storage/'.$seedRequest->payment_proof); $ext = strtolower(pathinfo($seedRequest->payment_proof, PATHINFO_EXTENSION)); @endphp
            <p><strong>Bukti transfer:</strong></p>
            @if(in_array($ext, ['jpg','jpeg','png','gif','webp'], true))
                <img src="{{ $proofUrl }}" alt="Bukti transfer" class="img-fluid border rounded" style="max-height: 360px;">
            @else
                <a href="{{ $proofUrl }}" target="_blank" class="btn btn-outline-primary btn-sm">Lihat berkas bukti transfer</a>
            @endif
        @endif
    </div>
</div>
@endsection
