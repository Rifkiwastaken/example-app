@extends('layouts.app')

@section('title', 'Detail Bibit: ' . $inventoryType->name . ' - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Detail Bibit: {{ $inventoryType->name }}</h4>
        <small class="text-muted">SKU: {{ $inventoryType->sku }}</small>
    </div>
    <div>
        <a href="{{ route('seed-stock.show-stock-adjustment', [$inventoryType, 'add']) }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>Tambah Stok (Penyesuaian)
        </a>
        <a href="{{ route('seed-stock.show-stock-adjustment', [$inventoryType, 'subtract']) }}" class="btn btn-danger">
            <i class="fas fa-minus me-2"></i>Kurangi Stok (Penyesuaian)
        </a>
        <a href="{{ route('seed-stock.edit', $inventoryType) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>Edit Tipe Bibit Ini
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Ringkasan Stok -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-4">
                <h5 class="text-primary">Total Stok</h5>
                <h3 class="mb-0">{{ number_format($inventoryType->total_stock, 2) }} {{ $inventoryType->unit }}</h3>
            </div>
            <div class="col-md-4">
                <h5 class="text-info">Lokasi</h5>
                <h3 class="mb-0">{{ $inventoryType->lots->groupBy('warehouse_id')->count() }} Gudang</h3>
            </div>
            <div class="col-md-4">
                <h5 class="text-success">Nilai Total</h5>
                <h3 class="mb-0">Rp {{ number_format($inventoryType->total_value, 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="detail-tab" data-bs-toggle="tab" data-bs-target="#detail" type="button" role="tab">
            Detail Bibit
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button" role="tab">
            Catatan
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
            Riwayat
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="lots-tab" data-bs-toggle="tab" data-bs-target="#lots" type="button" role="tab">
            Lots
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="photos-tab" data-bs-toggle="tab" data-bs-target="#photos" type="button" role="tab">
            Foto
        </button>
    </li>
</ul>

<!-- Tabs Content -->
<div class="tab-content">
    <!-- Tab: Detail Bibit -->
    <div class="tab-pane fade show active" id="detail" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-4">Informasi Dasar</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>SKU:</strong> <code>{{ $inventoryType->sku }}</code></p>
                        <p><strong>Kategori:</strong> {{ $inventoryType->category }}</p>
                        <p><strong>Unit:</strong> {{ $inventoryType->unit }}</p>
                        <p><strong>Nilai per Unit:</strong> Rp {{ number_format($inventoryType->estimated_value_per_unit ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Peringatan Stok Rendah:</strong> {{ $inventoryType->low_stock_threshold ?? '-' }} {{ $inventoryType->low_stock_unit ?? 'kg' }}</p>
                        <p><strong>Lacak Lot Individual:</strong> {{ $inventoryType->track_individual_lots ? 'Ya' : 'Tidak' }}</p>
                        @if($inventoryType->description)
                            <p><strong>Deskripsi:</strong> {{ $inventoryType->description }}</p>
                        @endif
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="mb-3">Ringkasan Stok per Lokasi</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Lokasi Gudang</th>
                                <th>Lokasi Bin</th>
                                <th>Jumlah Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stockSummary as $summary)
                            <tr>
                                <td>{{ $summary->warehouse->name ?? '-' }}</td>
                                <td>{{ $summary->bin->name ?? '(Lokasi Utama)' }}</td>
                                <td><strong>{{ number_format($summary->total_stock, 2) }} {{ $inventoryType->unit }}</strong></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">Belum ada stok di lokasi manapun</td>
                            </tr>
                            @endforelse
                            @if($stockSummary->sum('total_stock') > 0)
                            <tr class="table-info">
                                <td><strong>Total</strong></td>
                                <td></td>
                                <td><strong>{{ number_format($stockSummary->sum('total_stock'), 2) }} {{ $inventoryType->unit }}</strong></td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Catatan -->
    <div class="tab-pane fade" id="notes" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Catatan</h5>
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                        <i class="fas fa-plus me-2"></i>Tambah Catatan
                    </button>
                </div>
                @forelse($inventoryType->notes as $note)
                <div class="card mb-3">
                    <div class="card-body">
                        <p class="mb-2">{{ $note->content }}</p>
                        <small class="text-muted">
                            Oleh: {{ $note->user->name }} - {{ $note->created_at->format('d M Y H:i') }}
                        </small>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-4">Belum ada catatan</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Tab: Riwayat -->
    <div class="tab-pane fade" id="history" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-4">Log Audit</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Tipe Aksi</th>
                                <th>Pengguna</th>
                                <th>Jumlah</th>
                                <th>Lokasi (Bin)</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventoryType->transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('d-M-Y') }}</td>
                                <td>{{ $transaction->transaction_type_label }}</td>
                                <td>{{ $transaction->user->name }}</td>
                                <td>{{ $transaction->quantity > 0 ? '+' : '' }}{{ number_format($transaction->quantity, 2) }} {{ $transaction->unit }}</td>
                                <td>
                                    {{ $transaction->bin->name ?? ($transaction->warehouse->name ?? '-') }}
                                </td>
                                <td>{{ $transaction->notes ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada riwayat transaksi</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Lots -->
    <div class="tab-pane fade" id="lots" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-4">Daftar Lots (Batch Produksi)</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID Lot (dari Produksi)</th>
                                <th>Status</th>
                                <th>Stok Tersisa</th>
                                <th>Masa Edar</th>
                                <th>Lokasi (Bin)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventoryType->lots as $lot)
                            <tr>
                                <td><code>{{ $lot->production_id ?? '-' }}</code></td>
                                <td>
                                    <span class="badge bg-{{ $lot->status_color }}">
                                        {{ $lot->status_label }}
                                    </span>
                                </td>
                                <td>{{ number_format($lot->current_stock, 2) }} {{ $lot->stock_unit }}</td>
                                <td>{{ $lot->expiry_date ? $lot->expiry_date->format('d-M-Y') : '-' }}</td>
                                <td>{{ $lot->bin->name ?? ($lot->warehouse->name ?? '-') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada lot yang dibuat</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Foto -->
    <div class="tab-pane fade" id="photos" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Galeri Foto</h5>
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addPhotoModal">
                        <i class="fas fa-plus me-2"></i>Unggah Foto
                    </button>
                </div>
                <div class="row">
                    @forelse($inventoryType->photos as $photo)
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <img src="{{ asset('storage/' . $photo->photo_path) }}" class="card-img-top" alt="{{ $photo->caption }}">
                            <div class="card-body">
                                <p class="card-text small">{{ $photo->caption ?? '-' }}</p>
                                <small class="text-muted">{{ $photo->created_at->format('d M Y') }}</small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-4">
                        <p class="text-muted">Belum ada foto</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Tambah Catatan -->
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('seed-stock.store-note', $inventoryType) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Catatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="note_content" class="form-label">Catatan</label>
                        <textarea class="form-control" id="note_content" name="content" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Tambah Foto -->
<div class="modal fade" id="addPhotoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('seed-stock.store-photo', $inventoryType) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Unggah Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="photo" class="form-label">Foto</label>
                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*" required>
                    </div>
                    <div class="mb-3">
                        <label for="caption" class="form-label">Keterangan</label>
                        <input type="text" class="form-control" id="caption" name="caption">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Unggah</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

