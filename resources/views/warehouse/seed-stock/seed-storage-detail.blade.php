@extends('layouts.app')

@section('title', 'Data Penyimpanan Stok Benih - SIBESTI')

@section('content')
@php
    $plantName = $seed->plant?->name ?? 'Benih';
    $plantVariety = $seed->plant?->variety ?? '-';
@endphp
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('seed-stock.index') }}">Stok Benih</a></li>
        <li class="breadcrumb-item"><a href="{{ route('seed-stock.show', $inventoryType) }}">{{ $inventoryType->name }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('seed-stock.show-seed-detail', ['inventoryType' => $inventoryType, 'seed' => $seed]) }}">Detail Benih</a></li>
        <li class="breadcrumb-item active">Data Penyimpanan Stok Benih</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Data Penyimpanan Stok Benih</h4>
        <small class="text-muted">{{ $plantName }} - {{ $plantVariety }} | Nomor Penyimpanan: {{ $seed->storage_number ?? '-' }}</small>
    </div>
    <a href="{{ route('seed-stock.show', $inventoryType) }}#certified-seeds" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali ke Detail Stok Benih
    </a>
</div>

@if($lots->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-warehouse fa-3x text-muted mb-3"></i>
            <p class="text-muted mb-0">Benih ini belum ditambahkan ke lokasi gudang.</p>
            <a href="{{ route('seed-stock.show', $inventoryType) }}#certified-seeds" class="btn btn-primary mt-3">Kembali</a>
        </div>
    </div>
@else
    @foreach($lots as $lot)
    <div class="card mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-map-marker-alt me-2"></i>{{ $lot->warehouse->name ?? 'Gudang' }} &rarr; {{ $lot->bin->name ?? 'Bin' }}
            </h5>
            <span class="badge bg-primary">{{ number_format($lot->current_stock, 2) }} {{ $lot->stock_unit ?? 'kg' }} stok saat ini</span>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted small">Lokasi Penyimpanan</label>
                    <p class="mb-0">{{ $lot->warehouse->name ?? '-' }} ({{ $lot->bin->name ?? '-' }})</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted small">Tanggal Ditambahkan ke Gudang</label>
                    <p class="mb-0">{{ $lot->created_at ? $lot->created_at->format('d M Y H:i') : '-' }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted small">Nomor Lot / Produksi</label>
                    <p class="mb-0">{{ $lot->production_id ?? '-' }}</p>
                </div>
                <div class="col-md-4 mt-2">
                    <label class="form-label fw-bold text-muted small">Stok Awal</label>
                    <p class="mb-0">{{ number_format($lot->initial_stock ?? 0, 2) }} {{ $lot->stock_unit ?? 'kg' }}</p>
                </div>
                <div class="col-md-4 mt-2">
                    <label class="form-label fw-bold text-muted small">Stok Saat Ini</label>
                    <p class="mb-0"><strong>{{ number_format($lot->current_stock ?? 0, 2) }} {{ $lot->stock_unit ?? 'kg' }}</strong></p>
                </div>
                @if($lot->expiry_date)
                <div class="col-md-4 mt-2">
                    <label class="form-label fw-bold text-muted small">Tanggal Kadaluarsa</label>
                    <p class="mb-0">{{ $lot->expiry_date->format('d M Y') }}</p>
                </div>
                @endif
            </div>

            <h6 class="mb-3"><i class="fas fa-exchange-alt me-2"></i>Riwayat Transaksi (Stok Masuk / Pengurangan)</h6>
            @php $transactions = $transactionsByLot[$lot->inventory_lot_id] ?? collect(); @endphp
            @if($transactions->isEmpty())
                <p class="text-muted small mb-0">Belum ada riwayat transaksi.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Jumlah</th>
                                <th>Alasan / Catatan</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $tx)
                            <tr>
                                <td>{{ $tx->created_at ? $tx->created_at->format('d M Y H:i') : '-' }}</td>
                                <td>
                                    @if($tx->transaction_type === 'stok_masuk')
                                        <span class="badge bg-success">Stok Masuk</span>
                                    @elseif($tx->transaction_type === 'pengurangan')
                                        <span class="badge bg-danger">Pengurangan</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $tx->transaction_type }}</span>
                                    @endif
                                </td>
                                <td>{{ $tx->quantity > 0 ? '+' : '' }}{{ number_format($tx->quantity, 2) }} {{ $tx->unit ?? 'kg' }}</td>
                                <td>{{ $tx->reason ?? '-' }} @if($tx->notes)<br><small class="text-muted">{{ \Illuminate\Support\Str::limit($tx->notes, 60) }}</small>@endif</td>
                                <td>{{ $tx->user->name ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    @endforeach
@endif
@endsection
