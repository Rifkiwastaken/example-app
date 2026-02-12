@extends('layouts.app')

@section('title', 'Riwayat Penjualan: ' . $inventoryType->name . ' - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Riwayat Penjualan Benih</h4>
        <small class="text-muted">{{ $inventoryType->name }} - {{ $inventoryType->category ?? '' }}</small>
    </div>
    <div>
        <a href="{{ route('sales.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Stok Benih
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Summary Card -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Total Transaksi</h6>
                <h3 class="mb-0">{{ $totalSales }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Total Kuantitas Terjual</h6>
                <h3 class="mb-0">{{ number_format($totalQuantity, 2) }} {{ $inventoryType->unit ?? '' }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Total Pendapatan</h6>
                <h3 class="mb-0 text-success">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Daftar Penjualan</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No. Struk/Ref.</th>
                        <th>Tanggal</th>
                        <th>Nama Pembeli</th>
                        <th>Kuantitas</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                        <th>Metode Bayar</th>
                        <th>Dicatat Oleh</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    @foreach($sale->items as $item)
                    <tr>
                        <td><code>{{ $sale->receipt_number }}</code></td>
                        <td>{{ $sale->sale_date->format('d M Y') }}</td>
                        <td><strong>{{ $sale->buyer_name }}</strong></td>
                        <td>{{ number_format($item->quantity, 2) }} {{ $item->unit }}</td>
                        <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td><strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
                        <td>
                            <span class="badge bg-info">{{ $sale->payment_method_label }}</span>
                        </td>
                        <td>{{ $sale->user->name }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                <p>Belum ada penjualan untuk stok benih ini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($sales->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

