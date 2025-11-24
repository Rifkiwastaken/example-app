@extends('layouts.app')

@section('title', 'Riwayat Penjualan Benih - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Riwayat Penjualan Benih</h4>
    <a href="{{ route('sales.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-2"></i>Catat Penjualan Baru
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No. Struk/Ref.</th>
                        <th>Tanggal</th>
                        <th>Nama Pembeli</th>
                        <th>Total Pembayaran</th>
                        <th>Metode Bayar</th>
                        <th>Dicatat Oleh</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td><code>{{ $sale->receipt_number }}</code></td>
                        <td>{{ $sale->sale_date->format('d M Y') }}</td>
                        <td><strong>{{ $sale->buyer_name }}</strong></td>
                        <td><strong>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</strong></td>
                        <td>
                            <span class="badge bg-info">{{ $sale->payment_method_label }}</span>
                        </td>
                        <td>{{ $sale->user->name }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                <form action="{{ route('sales.destroy', $sale) }}" method="POST" class="d-inline" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus penjualan ini? Stok akan dikembalikan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                <p>Belum ada penjualan yang dicatat.</p>
                                <a href="{{ route('sales.create') }}" class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i>Catat Penjualan Pertama
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($sales->hasPages())
            <div class="d-flex justify-content-center">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

