@extends('layouts.app')

@section('title', 'Riwayat Penjualan Benih - SIBESTI')

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

{{-- Dashboard ringkasan penjualan --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fas fa-receipt text-primary fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 small">Total Transaksi</p>
                        <h4 class="mb-0">{{ number_format($totalTransactions ?? 0, 0, ',', '.') }}</h4>
                        <small class="text-muted">transaksi</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fas fa-weight-hanging text-success fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 small">Total Kuantitas Terjual</p>
                        <h4 class="mb-0">{{ number_format($totalQuantitySold ?? 0, 2, ',', '.') }}</h4>
                        <small class="text-muted">kg / unit</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="fas fa-coins text-info fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 small">Total Pendapatan</p>
                        <h4 class="mb-0">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</h4>
                        <small class="text-muted">sesuai filter periode</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light py-3">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('sales.index') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label small">Tanggal Dari</label>
                <input type="date" class="form-control form-control-sm" name="date_from" value="{{ request('date_from', $dateFrom?->format('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Tanggal Sampai</label>
                <input type="date" class="form-control form-control-sm" name="date_to" value="{{ request('date_to', $dateTo?->format('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Kategori</label>
                <select class="form-select form-select-sm" name="category">
                    <option value="">Semua Kategori</option>
                    @foreach($categories ?? [] as $cat)
                        <option value="{{ $cat }}" {{ ($category ?? '') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Cari (Nama / SKU)</label>
                <input type="text" class="form-control form-control-sm" name="search" value="{{ request('search', $search ?? '') }}" placeholder="Nama stok atau SKU...">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search me-1"></i>Terapkan
                </button>
                <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
        @if(request()->hasAny(['date_from', 'date_to', 'category', 'search']))
            <p class="text-muted small mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>Menampilkan data sesuai filter periode dan kriteria. Dashboard di atas juga mengikuti filter.
            </p>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-light py-3">
        <h5 class="mb-0">Daftar Stok Benih</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nama Stok Benih</th>
                        <th>Kategori</th>
                        <th>SKU</th>
                        <th>Total Penjualan</th>
                        <th>Total Kuantitas Terjual</th>
                        <th>Total Pendapatan</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventoryTypes as $inventoryType)
                    <tr>
                        <td>
                            <strong>{{ $inventoryType->name }}</strong>
                        </td>
                        <td>{{ $inventoryType->category ?? '-' }}</td>
                        <td><code>{{ $inventoryType->sku ?? '-' }}</code></td>
                        <td>
                            <span class="badge bg-primary">{{ $inventoryType->total_sales ?? 0 }} transaksi</span>
                        </td>
                        <td>
                            <strong>{{ number_format($inventoryType->total_quantity_sold ?? 0, 2) }} {{ $inventoryType->unit ?? '' }}</strong>
                        </td>
                        <td>
                            <strong>Rp {{ number_format($inventoryType->total_revenue ?? 0, 0, ',', '.') }}</strong>
                        </td>
                        <td>
                            <a href="{{ route('sales.by-inventory-type', $inventoryType) }}" class="btn btn-sm btn-outline-info" title="Lihat Riwayat Penjualan">
                                <i class="fas fa-eye"></i> Lihat Riwayat
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-box fa-3x mb-3"></i>
                                <p>Belum ada stok benih yang memiliki riwayat penjualan.</p>
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
    </div>
</div>
@endsection

