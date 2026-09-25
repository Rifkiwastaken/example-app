@extends(!empty($publicLayout) ? 'layouts.public' : 'layouts.app')

@section('title', 'Detail Penjualan: ' . $sale->receipt_number . ' - SIBESTI')

@section('content')
@php
    $backUrl = $backUrl ?? (auth()->check() ? route('sales.index') : route('public.seed-requests.index'));
    $backLabel = $backLabel ?? (auth()->check() ? 'Kembali ke Riwayat' : 'Kembali');
@endphp
<div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
    <div>
        <h4 class="mb-0">Detail Penjualan (No. Struk: {{ $sale->receipt_number }})</h4>
        <span class="badge bg-{{ $sale->payment_status_color }} fs-6">{{ $sale->payment_status_label }}</span>
    </div>
    <div>
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print me-2"></i>Cetak Struk/Nota
        </button>
        <a href="{{ $backUrl }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>{{ $backLabel }}
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <p><strong>Tanggal:</strong> {{ $sale->sale_date?->format('d F Y') ?: '-' }}</p>
                <p><strong>No. Struk:</strong> <code>{{ $sale->receipt_number }}</code></p>
            </div>
            <div class="col-md-6">
                <p><strong>Status Pembayaran:</strong>
                    <span class="badge bg-{{ $sale->payment_status_color }}">{{ $sale->payment_status_label }}</span>
                </p>
                <p><strong>Metode Bayar:</strong> {{ $sale->payment_method_label }}</p>
            </div>
        </div>

        <hr>

        <h5 class="mb-3">Informasi Pembeli:</h5>
        <div class="row mb-4">
            <div class="col-md-6">
                <p><strong>Nama:</strong> {{ $sale->buyer_name ?: '-' }}</p>
                <p><strong>Instansi:</strong> {{ $sale->organization ?: $sale->items->first()?->displayOrganization() ?: '-' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Kontak:</strong> {{ $sale->buyer_contact ?: '-' }}</p>
            </div>
        </div>

        <hr>

        <h5 class="mb-3" id="detail-benih">Daftar record benih yang terjual</h5>
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
                    @foreach($sale->items as $item)
                    @php
                        $pkg = $item->resolvedPackaging();
                        $stock = $pkg?->stock;
                        $plant = $item->resolvedPlant();
                        $rack = $pkg?->rack;
                        $warehouse = $rack?->warehouse;
                    @endphp
                    <tr>
                        <td>{{ $item->displayPlantName() }}</td>
                        <td>{{ $item->displayNomorInduk() }}</td>
                        <td>{{ $item->displayLabelNumber() }}</td>
                        <td>{{ number_format($item->quantity, 2) }} {{ $item->unit }}</td>
                        <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td><strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
                        <td class="d-print-none">
                            @if($pkg)
                                <a href="{{ route('public.seed-label', $pkg) }}" class="btn btn-sm btn-outline-success mb-1" target="_blank">Lihat label benih</a>
                            @endif
                            @if(auth()->check() && $warehouse && $rack)
                                <a href="{{ route('warehouse-locations.racks.packagings', [$warehouse, $rack]) }}" class="btn btn-sm btn-outline-info mb-1">Lihat stok</a>
                            @elseif(auth()->check() && $plant && $stock)
                                <a href="{{ route('seed-stock.lots.show', [$plant, $stock]) }}" class="btn btn-sm btn-outline-info mb-1">Lihat stok</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    <tr class="table-info">
                        <td colspan="5" class="text-end"><strong>Total</strong></td>
                        <td colspan="2"><strong>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <hr>

        <h5 class="mb-3">Lokasi rencana tanam:</h5>
        <div class="row mb-4">
            <div class="col-md-6">
                <p><strong>Nama lahan:</strong> {{ $sale->planned_location_name ?: '-' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>GPS:</strong> {{ $sale->planned_gps ?: '-' }}</p>
            </div>
        </div>

        <hr>

        <h5 class="mb-3">Informasi Pembayaran:</h5>
        <div class="row">
            <div class="col-md-6">
                <p><strong>Metode Bayar:</strong> {{ $sale->payment_method_label }}</p>
                <p><strong>Dicatat Oleh:</strong> {{ $sale->user?->name ?: '-' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Keterangan:</strong> {{ $sale->notes ?: '-' }}</p>
            </div>
        </div>
        @if($sale->payment_method === 'transfer_bank' && $sale->payment_proof)
            <div class="mt-3">
                <p><strong>Bukti transfer:</strong></p>
                @php $proofUrl = asset('storage/'.$sale->payment_proof); $ext = strtolower(pathinfo($sale->payment_proof, PATHINFO_EXTENSION)); @endphp
                @if(in_array($ext, ['jpg','jpeg','png','gif','webp'], true))
                    <img src="{{ $proofUrl }}" alt="Bukti transfer" class="img-fluid border rounded" style="max-height: 360px;">
                @else
                    <a href="{{ $proofUrl }}" target="_blank" class="btn btn-outline-primary btn-sm">Lihat berkas bukti transfer</a>
                @endif
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
@media print {
    .btn, nav, .sidebar, .d-print-none {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
@endpush
@endsection
