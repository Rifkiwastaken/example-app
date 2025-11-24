@extends('layouts.app')

@section('title', 'Stok Bibit (Tipe Inventaris) - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Stok Bibit (Tipe Inventaris)</h4>
    <a href="{{ route('seed-stock.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-2"></i>Tambahkan Tipe Bibit Baru
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
                        <th>Nama Varietas/Komoditas</th>
                        <th>Kategori</th>
                        <th>ID Internal / SKU</th>
                        <th>Unit</th>
                        <th>Total Stok (Semua Gudang)</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventoryTypes as $type)
                    <tr>
                        <td><strong>{{ $type->name }}</strong></td>
                        <td><span class="badge bg-secondary">{{ $type->category }}</span></td>
                        <td><code>{{ $type->sku }}</code></td>
                        <td>{{ $type->unit }}</td>
                        <td>
                            <strong>{{ number_format($type->total_stock_calculated, 2) }} {{ $type->unit }}</strong>
                        </td>
                        <td>
                            <a href="{{ route('seed-stock.show', $type) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-seedling fa-3x mb-3"></i>
                                <p>Belum ada tipe bibit yang ditambahkan.</p>
                                <a href="{{ route('seed-stock.create') }}" class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i>Tambah Tipe Bibit Pertama
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($inventoryTypes->hasPages())
            <div class="d-flex justify-content-center">
                {{ $inventoryTypes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

