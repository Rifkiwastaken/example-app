@extends('layouts.app')

@section('title', 'Detail Penjualan: ' . $sale->receipt_number . ' - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Detail Penjualan (No. Struk: {{ $sale->receipt_number }})</h4>
        <span class="badge bg-{{ $sale->payment_status_color }} fs-6">{{ $sale->payment_status_label }}</span>
    </div>
    <div>
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print me-2"></i>Cetak Struk/Nota
        </button>
        <a href="{{ route('sales.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Riwayat
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <p><strong>Tanggal:</strong> {{ $sale->sale_date->format('d F Y') }}</p>
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
                <p><strong>Nama:</strong> {{ $sale->buyer_name }}</p>
            </div>
            <div class="col-md-6">
                @if($sale->buyer_contact)
                    <p><strong>Kontak:</strong> {{ $sale->buyer_contact }}</p>
                @endif
            </div>
        </div>

        <hr>

        <h5 class="mb-3">Rincian Item yang Dibeli:</h5>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Varietas</th>
                        <th>ID Lot</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $item)
                    <tr>
                        <td>{{ $item->inventoryType->name }}</td>
                        <td>
                            @if($item->inventoryLot)
                                <code>{{ $item->inventoryLot->production_id ?? 'Lot #' . $item->inventoryLot->id }}</code>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ number_format($item->quantity, 2) }} {{ $item->unit }}</td>
                        <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td><strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
                    </tr>
                    @endforeach
                    <tr class="table-info">
                        <td colspan="4" class="text-end"><strong>Total</strong></td>
                        <td><strong>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <hr>

        <h5 class="mb-3">Informasi Pembayaran:</h5>
        <div class="row">
            <div class="col-md-6">
                <p><strong>Metode Bayar:</strong> {{ $sale->payment_method_label }}</p>
                <p><strong>Dicatat Oleh:</strong> {{ $sale->user->name }}</p>
            </div>
            <div class="col-md-6">
                @if($sale->notes)
                    <p><strong>Keterangan:</strong> {{ $sale->notes }}</p>
                @else
                    <p><strong>Keterangan:</strong> -</p>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    .btn, nav, .sidebar {
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

