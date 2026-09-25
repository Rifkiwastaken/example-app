@extends('layouts.app')
@section('title', 'Riwayat Penjualan - SIBESTI')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="{{ route('plants.index') }}" class="btn btn-secondary me-3">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
        <div>
            <h4 class="mb-0">{{ $plant->name }}</h4>
            <small class="text-muted">{{ $plant->type?->name ?: '-' }}</small>
        </div>
    </div>
</div>

<ul class="nav nav-tabs" role="tablist">
    @include('planting.plants._tabs', ['plant' => $plant, 'activeTab' => 'sales-history'])
</ul>

<div class="tab-content p-3 bg-white border border-top-0 rounded-bottom">
    <h5 class="mb-3">Riwayat Penjualan</h5>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>No kwitansi</th>
                    <th>Pembeli</th>
                    <th>Jumlah stok produk yang terjual</th>
                    <th>Kuantitas</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    <tr>
                        <td>{{ \Illuminate\Support\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</td>
                        <td>{{ $sale->receipt_number }}</td>
                        <td>{{ $sale->buyer_name }}</td>
                        <td>{{ number_format((int) $sale->product_count, 0) }} produk</td>
                        <td>{{ number_format((float) $sale->total_quantity, 2) }} {{ $sale->unit }}</td>
                        <td>Rp {{ number_format((float) $sale->total_amount, 0, ',', '.') }}</td>
                        <td>
                            <a href="{{ route('sales.show', $sale->first_id) }}" class="btn btn-sm btn-outline-info">Detail penjualan</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted text-center">Belum ada penjualan untuk varietas ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $sales->links() }}
</div>
@endsection
