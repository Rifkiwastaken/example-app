@extends('layouts.app')

@section('title', 'Riwayat Data Benih - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Riwayat Data Benih</h4>
        <small class="text-muted">{{ $seed->plant->name }} - {{ $inventoryType->name }}</small>
    </div>
    <div>
        <a href="{{ route('seed-stock.show-seed-detail', ['inventoryType' => $inventoryType, 'seed' => $seed]) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Detail
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Informasi Benih</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Nama Benih:</strong> {{ $seed->plant->name }}</p>
                <p><strong>Varietas:</strong> {{ $seed->plant->variety ?: '-' }}</p>
                <p><strong>Lokasi Penanaman:</strong> {{ $seed->plantingLocation->name }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Jumlah Benih Total:</strong> {{ number_format($seed->total_seed_quantity ?? $seed->quantity, 2) }} {{ $seed->total_seed_unit ?? 'kg' }}</p>
                <p><strong>Tanggal Kadaluarsa:</strong> {{ $seed->expiry_date ? $seed->expiry_date->format('d M Y') : '-' }}</p>
                <p><strong>Dibuat Oleh:</strong> {{ $seed->filledByUser->name ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Riwayat Aksi</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal & Waktu</th>
                        <th>Aksi</th>
                        <th>Deskripsi</th>
                        <th>User</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($histories as $history)
                    <tr>
                        <td>{{ $history->created_at->format('d M Y H:i:s') }}</td>
                        <td>
                            @if($history->action == 'create')
                                <span class="badge bg-success">Tambah</span>
                            @elseif($history->action == 'update')
                                <span class="badge bg-warning">Edit</span>
                            @elseif($history->action == 'delete')
                                <span class="badge bg-danger">Hapus</span>
                            @elseif($history->action == 'reduce_stock')
                                <span class="badge bg-info">Kurangi Stok</span>
                            @else
                                <span class="badge bg-secondary">{{ $history->action }}</span>
                            @endif
                        </td>
                        <td>{{ $history->description }}</td>
                        <td>{{ $history->user->name ?? '-' }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#historyDetailModal{{ $history->id }}">
                                <i class="fas fa-eye"></i> Detail
                            </button>
                        </td>
                    </tr>

                    <!-- Modal: Detail History -->
                    <div class="modal fade" id="historyDetailModal{{ $history->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Detail Riwayat</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <strong>Tanggal & Waktu:</strong> {{ $history->created_at->format('d M Y H:i:s') }}
                                    </div>
                                    <div class="mb-3">
                                        <strong>Aksi:</strong> 
                                        @if($history->action == 'create')
                                            <span class="badge bg-success">Tambah</span>
                                        @elseif($history->action == 'update')
                                            <span class="badge bg-warning">Edit</span>
                                        @elseif($history->action == 'delete')
                                            <span class="badge bg-danger">Hapus</span>
                                        @elseif($history->action == 'reduce_stock')
                                            <span class="badge bg-info">Kurangi Stok</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $history->action }}</span>
                                        @endif
                                    </div>
                                    <div class="mb-3">
                                        <strong>Deskripsi:</strong> {{ $history->description }}
                                    </div>
                                    <div class="mb-3">
                                        <strong>User:</strong> {{ $history->user->name ?? '-' }}
                                    </div>
                                    @if($history->old_data)
                                    <div class="mb-3">
                                        <strong>Data Sebelumnya:</strong>
                                        <pre class="bg-light p-3 rounded">{{ json_encode($history->old_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                    @endif
                                    @if($history->new_data)
                                    <div class="mb-3">
                                        <strong>Data Setelahnya:</strong>
                                        <pre class="bg-light p-3 rounded">{{ json_encode($history->new_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-history fa-3x mb-3"></i>
                                <p>Belum ada riwayat untuk benih ini.</p>
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

