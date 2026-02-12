@extends('layouts.app')

@section('title', 'Detail Stok Benih: ' . $inventoryType->name . ' - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Detail Stok Benih: {{ $inventoryType->name }}</h4>
        <small class="text-muted">SKU: {{ $inventoryType->sku }}</small>
    </div>
    <div>
        <a href="{{ route('seed-stock.index') }}" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
        <a href="{{ route('seed-stock.edit', $inventoryType) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>Edit Tipe Benih Ini
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
            <div class="col-md-2">
                <h5 class="text-primary">Stok Saat Ini</h5>
                <h3 class="mb-0">{{ number_format($displayTotalQuantity ?? $inventoryType->total_stock_from_seeds, 2) }} {{ $inventoryType->unit }}</h3>
                <small class="text-muted">Total dari data stok benih (mengikuti gudang jika sudah ditambahkan)</small>
            </div>
            <div class="col-md-2">
                <h5 class="text-info">Lokasi</h5>
                <h3 class="mb-0">{{ $inventoryType->lots->groupBy('warehouse_id')->count() }} Gudang</h3>
            </div>
            <div class="col-md-2">
                <h5 class="text-secondary">Nilai per Unit</h5>
                <h3 class="mb-0">Rp {{ number_format($inventoryType->estimated_value_per_unit ?? 0, 0, ',', '.') }}</h3>
            </div>
            <div class="col-md-3">
                <h5 class="text-success">Nilai Total Saat Ini</h5>
                @php
                    $displayTotalValue = isset($displayTotalQuantity) ? ($displayTotalQuantity * ($inventoryType->estimated_value_per_unit ?? 0)) : $inventoryType->total_value_from_seeds;
                @endphp
                <h3 class="mb-0">Rp {{ number_format($displayTotalValue, 0, ',', '.') }}</h3>
                <small class="text-muted">Nilai dari data stok benih (mengikuti gudang jika sudah ditambahkan)</small>
            </div>
            <div class="col-md-3">
                <h5 class="text-warning">Total Data Benih</h5>
                <h3 class="mb-0">{{ $inventoryType->seeds()->count() }} Record</h3>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="detail-tab" data-bs-toggle="tab" data-bs-target="#detail" type="button" role="tab">
            Detail Stok Benih
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="certified-seeds-tab" data-bs-toggle="tab" data-bs-target="#certified-seeds" type="button" role="tab">
            Detail Stok Benih
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
    <!-- Tab: Detail Stok Benih -->
    <div class="tab-pane show active" id="detail" role="tabpanel">
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
                                <td>{{ $summary->warehouse->name ?? $summary->bin?->warehouse?->name ?? '-' }}</td>
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
    <div class="tab-pane" id="notes" role="tabpanel">
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
                            Oleh: {{ $note->user?->name ?? '-' }} - {{ $note->created_at->format('d M Y H:i') }}
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
    <div class="tab-pane" id="history" role="tabpanel">
        <!-- Sub-menu Navigation -->
        <ul class="nav nav-pills mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="log-audit-tab" data-bs-toggle="tab" data-bs-target="#log-audit" type="button" role="tab">
                    Log Audit
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="seed-history-tab" data-bs-toggle="tab" data-bs-target="#seed-history" type="button" role="tab">
                    Riwayat Stok Benih
                </button>
            </li>
        </ul>

        <!-- Sub-menu Content -->
        <div class="tab-content">
            <!-- Sub-tab: Log Audit -->
            <div class="tab-pane fade show active" id="log-audit" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-4">Log Audit</h5>
                
                <!-- Transactions History -->
                <h6 class="mb-3">Riwayat Transaksi Stok</h6>
                        <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Tipe Aksi</th>
                                <th>Pengguna</th>
                                <th>Jumlah</th>
                                        <th>Lokasi Gudang</th>
                                <th>Lokasi (Bin)</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventoryType->transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('d-M-Y H:i') }}</td>
                                        <td>
                                            @if($transaction->reason === 'Update Stok Benih')
                                                update stok benih
                                            @else
                                                {{ $transaction->transaction_type_label }}
                                            @endif
                                        </td>
                                <td>{{ $transaction->user?->name ?? '-' }}</td>
                                <td>{{ $transaction->quantity > 0 ? '+' : '' }}{{ number_format($transaction->quantity, 2) }} {{ $transaction->unit }}</td>
                                <td>
                                            {{ $transaction->warehouse->name ?? '-' }}
                                </td>
                                        <td>
                                            {{ $transaction->bin->name ?? '-' }}
                                        </td>
                                        <td>
                                            @if($transaction->reason === 'Update Stok Benih')
                                                update stok benih
                                            @else
                                                {{ $transaction->notes ?? '-' }}
                                            @endif
                                        </td>
                            </tr>
                            @empty
                            <tr>
                                        <td colspan="7" class="text-center">Belum ada riwayat transaksi</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                        </div>
                    </div>
                </div>
                </div>
                
            <!-- Sub-tab: Riwayat Stok Benih -->
            <div class="tab-pane fade" id="seed-history" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-4">Riwayat Stok Benih</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                        <th>Tanggal & Waktu</th>
                                <th>Aksi</th>
                                <th>Nama Benih</th>
                                        <th>Deskripsi</th>
                                        <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                        // Load histories from SeedHistory (termasuk yang seed-nya sudah dihapus)
                                        $allHistories = \App\Models\SeedHistory::where('inventory_type_id', $inventoryType->inventory_type_id)
                                            ->with(['user', 'seed.plant'])
                                            ->orderBy('created_at', 'desc')
                                            ->get()
                                            ->map(function($history) {
                                                $plantName = '-';
                                                if ($history->seed && $history->seed->plant) {
                                                    $plantName = $history->seed->plant->name;
                                                } elseif ($history->old_data && !empty($history->old_data['plant_id'])) {
                                                    $plant = \App\Models\Plant::find($history->old_data['plant_id']);
                                                    $plantName = $plant ? $plant->name : '-';
                                                }
                                                return [
                                                    'history' => $history,
                                                    'plant_name' => $plantName,
                                                ];
                                            });
                            @endphp
                                    @forelse($allHistories as $item)
                                    <tr>
                                        <td>{{ $item['history']->created_at->format('d M Y H:i:s') }}</td>
                                        <td>
                                            @if($item['history']->action == 'create')
                                                <span class="badge bg-success">Tambah</span>
                                            @elseif($item['history']->action == 'update')
                                                <span class="badge bg-warning">Edit</span>
                                            @elseif($item['history']->action == 'delete')
                                                <span class="badge bg-danger">Hapus</span>
                                            @elseif($item['history']->action == 'reduce_stock')
                                                <span class="badge bg-info">Kurangi Stok</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $item['history']->action }}</span>
                                            @endif
                                </td>
                                        <td>{{ $item['plant_name'] }}</td>
                                        <td>{{ $item['history']->description }}</td>
                                        <td>{{ $item['history']->user?->name ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                        <td colspan="5" class="text-center">Belum ada riwayat stok benih</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Lots -->
    <div class="tab-pane" id="lots" role="tabpanel">
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

    <!-- Tab: Data Benih -->
    <div class="tab-pane" id="certified-seeds" role="tabpanel">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Data Stok Benih</h5>
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addStockFromCertificationModal">
                    <i class="fas fa-plus me-2"></i>Tambahkan Data Stok
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama Benih</th>
                                <th>Varietas</th>
                                <th>Asal Lokasi Penanaman/Produksi</th>
                                <th>Jenis Laporan BPSB</th>
                                <th>Nomor Penyimpanan</th>
                                <th>Jumlah Inventaris</th>
                                <th>Tanggal Kadaluarsa</th>
                                <th>Tanggal Ditambahkan</th>
                                <th>Lokasi Penyimpanan</th>
                                <th>Bin Penyimpanan</th>
                                <th>Status</th>
                                <th width="280">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Only show seeds from inventory_type_seeds table to avoid duplication
                                $allSeeds = collect();
                                
                                foreach ($inventoryType->seeds as $seed) {
                                    $isExpired = $seed->expiry_date && $seed->expiry_date->isPast();
                                    $isNearExpiry = $seed->expiry_date && $seed->expiry_date->isFuture() && $seed->expiry_date->diffInMonths(now()) <= 6;
                                    
                                    // Nama benih, varietas, lokasi: dari plant/plantingLocation atau fallback dari sertifikasi
                                    $plantName = $seed->plant?->name ?? $seed->certificationReport?->certification?->plant?->name ?? '-';
                                    $variety = $seed->plant?->variety ?: $seed->certificationReport?->certification?->plant?->variety ?: '-';
                                    $location = $seed->plantingLocation?->name ?? $seed->certificationReport?->certification?->plantingLocation?->name ?? $seed->certificationReport?->certification?->harvest?->location?->name ?? '-';
                                    
                                    $allSeeds->push([
                                        'type' => 'seed',
                                        'id' => $seed->inventory_type_seed_id ?? $seed->id ?? null,
                                        'plant_name' => $plantName,
                                        'variety' => $variety,
                                        'location' => $location,
                                        'quantity' => $seed->quantity,
                                        'expiry_date' => $seed->expiry_date,
                                        'seed' => $seed,
                                        'is_expired' => $isExpired,
                                        'is_near_expiry' => $isNearExpiry,
                                        'plant_id' => $seed->plant_id,
                                        'harvest_id' => null,
                                    ]);
                                }
                                
                                // Sort by created date (newest first)
                                $allSeeds = $allSeeds->sortByDesc(function($item) {
                                    return $item['seed']->created_at;
                                });
                            @endphp
                            
                            @forelse($allSeeds as $seedData)
                            <tr>
                                <td>
                                    <strong>{{ $seedData['plant_name'] }}</strong>
                                </td>
                                <td>
                                    {{ $seedData['variety'] }}
                                </td>
                                <td>
                                    {{ $seedData['location'] }}
                                </td>
                                <td>
                                    {{ $seedData['seed']->report_type ?? '-' }}
                                </td>
                                <td>
                                    {{ $seedData['seed']->storage_number ?? '-' }}
                                </td>
                                <td>
                                    @php
                                        $seedId = (string)($seedData['seed']->inventory_type_seed_id);
                                        $displayStock = $seedDisplayStock[$seedId] ?? null;
                                    @endphp
                                    @if($displayStock !== null)
                                        <strong>{{ number_format($displayStock['quantity'], 2) }} {{ $displayStock['unit'] ?? 'kg' }}</strong>
                                        <small class="text-muted d-block">Mengikuti stok di gudang</small>
                                    @elseif(isset($seedData['seed']->total_seed_quantity))
                                        <strong>{{ number_format($seedData['seed']->total_seed_quantity, 2) }} {{ $seedData['seed']->total_seed_unit ?? 'kg' }}</strong>
                                    @else
                                        <strong>{{ number_format($seedData['quantity'], 2) }} kg</strong>
                                    @endif
                                </td>
                                <td>
                                    @if($seedData['expiry_date'])
                                        {{ $seedData['expiry_date']->format('d M Y') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($seedData['seed']->created_at)
                                        {{ $seedData['seed']->created_at->format('d M Y H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                @php
                                    $locations = $seedLocations[(string)($seedData['seed']->inventory_type_seed_id)] ?? collect();
                                @endphp
                                <td>
                                    @if($locations->isNotEmpty())
                                        @foreach($locations as $loc)
                                            <div class="mb-1"><span class="badge bg-info">{{ $loc['warehouse'] }}</span></div>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($locations->isNotEmpty())
                                        @foreach($locations as $loc)
                                            <div class="mb-1"><span class="badge bg-secondary">{{ $loc['bin'] }}</span></div>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($seedData['is_expired'])
                                        <span class="badge bg-danger">Sudah Melewati Masa Edar</span>
                                    @elseif($seedData['is_near_expiry'])
                                        <span class="badge bg-warning">Mendekati Masa Edar</span>
                                    @else
                                        <span class="badge bg-success">Aktif</span>
                                    @endif
                                </td>
                                <td>
                                        <div class="btn-group" role="group">
                                        <a href="{{ route('seed-stock.show-seed-detail', ['inventoryType' => $inventoryType, 'seed' => $seedData['seed']]) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($locations->isNotEmpty())
                                            <a href="{{ route('seed-stock.seed-storage-detail', ['inventoryType' => $inventoryType, 'seed' => $seedData['seed']]) }}" class="btn btn-sm btn-outline-success" title="Lihat data stok benih di gudang">
                                                <i class="fas fa-warehouse"></i> Lihat data stok benih di gudang
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-primary" title="Tambahkan ke Gudang" onclick="openAddToWarehouseModal('seed', '{{ $seedData['seed']->inventory_type_seed_id }}', '{{ $seedData['seed']->storage_number ?? '' }}')">
                                                <i class="fas fa-warehouse"></i> Tambahkan ke Gudang
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Kurangi Stok" onclick="loadReduceStock('{{ $seedData['seed']->inventory_type_seed_id }}')">
                                            <i class="fas fa-minus me-1"></i>Kurangi Stok
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus Stok" onclick="openDeleteStockModal('{{ $seedData['seed']->inventory_type_seed_id }}', '{{ addslashes($seedData['plant_name']) }} - {{ addslashes($seedData['variety']) }}')">
                                            <i class="fas fa-trash me-1"></i>Hapus Stok
                                        </button>
                                        </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-certificate fa-3x mb-3"></i>
                                        <p>Belum ada benih yang ditambahkan ke stok benih ini.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Foto -->
    <div class="tab-pane" id="photos" role="tabpanel">
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

<!-- Modal: Tambah Benih -->
<div class="modal fade" id="addCertifiedSeedModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Benih dari Sertifikasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            @if($prefillData && $certificationReport)
            <div class="alert alert-info m-3 mb-0">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Data dari Sertifikasi:</strong> Form ini telah diisi otomatis dengan data dari laporan sertifikasi. Silakan periksa dan sesuaikan jika diperlukan.
            </div>
            @endif
            <form action="{{ route('seed-stock.add-certified-seed', $inventoryType) }}" method="POST">
                @csrf
                @if($prefillData && $certificationReport)
                    <input type="hidden" name="certification_report_id" value="{{ $certificationReport->id }}">
                @endif
                <div class="modal-body">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <!-- 1. Pilih Benih -->
                            <div class="mb-3">
                            <label class="form-label">Pilih Benih <span class="text-danger">*</span></label>
                            <select class="form-select @error('plant_id') is-invalid @enderror" name="plant_id" id="plant_id" required {{ $prefillData ? 'disabled' : '' }}>
                                <option value="">-- Pilih Benih --</option>
                                @foreach($plants as $plant)
                                    <option value="{{ $plant->id }}" 
                                            data-variety="{{ $plant->variety ?: '-' }}"
                                            {{ ($prefillData && $prefillData['plant_id'] == $plant->id) || old('plant_id') == $plant->id ? 'selected' : '' }}>
                                        {{ $plant->name }} @if($plant->variety) - {{ $plant->variety }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @if($prefillData)
                                <input type="hidden" name="plant_id" value="{{ $prefillData['plant_id'] }}">
                            @endif
                            @error('plant_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Pilih benih dari data tanaman saya</small>
                        </div>

                            <!-- 2. Satuan Inventaris -->
                            <div class="mb-3">
                            <label class="form-label">Satuan Inventaris <span class="text-danger">*</span></label>
                            <select class="form-select @error('seed_unit') is-invalid @enderror" name="seed_unit" id="seed_unit" required>
                                <option value="">-- Pilih Satuan --</option>
                                @foreach(['kg'=>'Kilogram (kg)','ton'=>'Ton','gram'=>'Gram','butir'=>'Butir/Biji','pcs'=>'Pcs','batang'=>'Batang'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('seed_unit', $prefillData['seed_unit'] ?? '') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('seed_unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Pilih satuan inventaris</small>
                        </div>

                            <!-- 3. Estimasi Penjualan per Unit -->
                            <div class="mb-3">
                            <label class="form-label">Estimasi Penjualan per Unit</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control @error('estimated_sale_price_per_kg') is-invalid @enderror" 
                                       name="estimated_sale_price_per_kg" id="estimated_sale_price_per_kg" 
                                       step="0.01" min="0" placeholder="0.00" 
                                       value="{{ old('estimated_sale_price_per_kg', $prefillData['estimated_sale_price_per_kg'] ?? (isset($certificationReport) && $certificationReport ? ($certificationReport->estimated_sale_price_per_kg ?? $inventoryType->estimated_value_per_unit ?? '') : ($inventoryType->estimated_value_per_unit ?? ''))) }}">
                            </div>
                            @error('estimated_sale_price_per_kg')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Data otomatis diambil dari sertifikasi, dapat diedit</small>
                        </div>

                            <!-- 5. Pengisi Data -->
                            <div class="mb-3">
                            <label class="form-label">Pengisi Data <span class="text-danger">*</span></label>
                            <select class="form-select @error('filled_by_user_id') is-invalid @enderror" name="filled_by_user_id" id="filled_by_user_id" required>
                                <option value="">-- Pilih User --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('filled_by_user_id', auth()->id()) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('filled_by_user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Pilih user yang mengisi data</small>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <!-- 1. Lokasi Penanaman -->
                            <div class="mb-3">
                                <label class="form-label">Lokasi Penanaman <span class="text-danger">*</span></label>
                                <select class="form-select @error('planting_location_id') is-invalid @enderror" name="planting_location_id" id="planting_location_id" required {{ $prefillData ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Lokasi Penanaman --</option>
                                    @foreach($plantingLocations as $location)
                                        <option value="{{ $location->id }}" {{ ($prefillData && $prefillData['planting_location_id'] == $location->id) || old('planting_location_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($prefillData)
                                    <input type="hidden" name="planting_location_id" value="{{ $prefillData['planting_location_id'] }}">
                                @endif
                                @error('planting_location_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Pilih lokasi penanaman dari data lokasi penanaman</small>
                            </div>

                            <!-- 2. Jumlah Inventaris -->
                            <div class="mb-3">
                                <label class="form-label">Jumlah Inventaris <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('seed_quantity') is-invalid @enderror" 
                                       name="seed_quantity" id="seed_quantity" 
                                       step="0.01" min="0.01" required placeholder="0.00" 
                                       value="{{ old('seed_quantity', $prefillData['certified_seed_quantity'] ?? '') }}">
                                @error('seed_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Masukkan jumlah inventaris</small>
                            </div>

                            <!-- 3. Tanggal Kadaluarsa -->
                            <div class="mb-3">
                                <label class="form-label">Tanggal Kadaluarsa</label>
                                <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                       name="expiry_date" id="expiry_date" 
                                       value="{{ old('expiry_date', $prefillData['expiry_date'] ?? ($certificationReport && $certificationReport->expiry_date ? $certificationReport->expiry_date->format('Y-m-d') : '')) }}">
                                @error('expiry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Data otomatis diambil dari sertifikasi benih</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Tambah Benih</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Styling untuk semua tab utama agar tidak aktif berwarna abu */
    .nav-tabs .nav-link:not(.active) {
        background-color: #6c757d !important;
        color: #ffffff !important;
        opacity: 1 !important;
        border-color: #6c757d !important;
        font-weight: 500;
    }
    
    .nav-tabs .nav-link.active {
        background-color: #6c757d !important;
        color: #ffffff !important;
        opacity: 1 !important;
        border-color: #6c757d !important;
        font-weight: 600;
    }
    
</style>
@endpush

@push('scripts')
<script>
    // Auto-open tab and modal if prefill data exists - Execute first
    @if($prefillData && $certificationReport)
    (function() {
        let executed = false;
        
        function execute(attempt = 0) {
            if (executed || attempt > 30) return;
            
            const tabButton = document.querySelector('#certified-seeds-tab');
            const tabPane = document.querySelector('#certified-seeds');
            const modal = document.querySelector('#addCertifiedSeedModal');
            
            if (!tabButton || !tabPane || !modal) {
                // Elements not found yet, retry
                setTimeout(function() {
                    execute(attempt + 1);
                }, 100);
                return;
            }
            
            // Check if tab is already active
            const isTabActive = tabPane.classList.contains('active') && tabPane.classList.contains('show');
            
            if (isTabActive) {
                // Tab is active, open modal directly
                    executed = true;
                    setTimeout(function() {
                    const modalInstance = new bootstrap.Modal(modal);
                    modalInstance.show();
                }, 200);
            } else {
                // Activate tab first
                const tab = new bootstrap.Tab(tabButton);
                tab.show();
                
                // Listen for tab shown event
                const handleShown = function() {
                    setTimeout(function() {
                        if (!executed) {
                            executed = true;
                            const modalInstance = new bootstrap.Modal(modal);
                            modalInstance.show();
            }
                    tabButton.removeEventListener('shown.bs.tab', handleShown);
                    }, 200);
                };
                tabButton.addEventListener('shown.bs.tab', handleShown);
                
                // Fallback: open modal after delay even if event doesn't fire
                setTimeout(function() {
                    if (!executed) {
                            executed = true;
                        const modalInstance = new bootstrap.Modal(modal);
                        modalInstance.show();
                }
                }, 800);
            }
        }
        
        // Start when page is ready
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(function() {
                execute();
            }, 300);
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    execute();
                }, 300);
            });
        }
        
        // Also try on DOMContentLoaded as backup
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    execute();
                }, 300);
            });
        } else {
            setTimeout(function() {
                execute();
            }, 300);
        }
    })();
    @endif
    
    // Sinkronkan unit total dengan pilihan seed_unit
    document.addEventListener('DOMContentLoaded', function() {
        const seedUnit = document.getElementById('seed_unit');
        const totalSeedUnit = document.getElementById('total_seed_unit');
        const totalSeedUnitLabel = document.getElementById('total_seed_unit_label');

        function syncUnit() {
            const val = seedUnit?.value || 'kg';
            if (totalSeedUnit) totalSeedUnit.value = val;
            if (totalSeedUnitLabel) totalSeedUnitLabel.textContent = val;
        }

        if (seedUnit) {
            seedUnit.addEventListener('change', syncUnit);
            syncUnit();
        }
    });

    // Define edit seed elements and calculate function
    const editSeedUnitQuantity = document.getElementById('edit_seed_unit_quantity');
    const editSeedPerUnit = document.getElementById('edit_seed_per_unit');
    const editTotalSeedQuantity = document.getElementById('edit_total_seed_quantity');
    
    function calculateEditTotal() {
        if (editSeedUnitQuantity && editSeedPerUnit && editTotalSeedQuantity) {
            const unitQty = parseFloat(editSeedUnitQuantity.value) || 0;
            const perUnit = parseFloat(editSeedPerUnit.value) || 0;
            const total = unitQty * perUnit;
            editTotalSeedQuantity.value = total.toFixed(2);
        }
    }

    if (editSeedUnitQuantity) {
        editSeedUnitQuantity.addEventListener('input', calculateEditTotal);
    }
    if (editSeedPerUnit) {
        editSeedPerUnit.addEventListener('input', calculateEditTotal);
    }

document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill estimated sale price from inventory type
    const estimatedPriceInput = document.getElementById('estimated_sale_price_per_kg');
    if (estimatedPriceInput) {
        const estimatedValue = {{ $inventoryType->estimated_value_per_unit ?? 0 }};
        if (estimatedValue > 0 && !estimatedPriceInput.value) {
            estimatedPriceInput.value = estimatedValue.toFixed(2);
        }
    }
    
    
    // Auto-open tab if specified in URL (only if not already handled by prefill)
    @if(!$prefillData)
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    if (tabParam) {
        const tabElement = document.querySelector(`button[data-bs-target="#${tabParam}"]`);
        if (tabElement) {
            const tab = new bootstrap.Tab(tabElement);
            tab.show();
        }
    }
    @endif
});

// Load seed edit data
function loadSeedEdit(seedId) {
    fetch(`/seed-stock/{{ $inventoryType->inventory_type_id }}/seeds/${seedId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_seed_unit').value = data.seed_unit || '';
            document.getElementById('edit_seed_unit_quantity').value = data.seed_unit_quantity || '';
            document.getElementById('edit_seed_per_unit').value = data.seed_per_unit || '';
            document.getElementById('edit_seed_per_unit_unit').value = data.seed_per_unit_unit || '';
            document.getElementById('edit_total_seed_quantity').value = data.total_seed_quantity || '';
            document.getElementById('edit_total_seed_unit').value = data.total_seed_unit || '';
            document.getElementById('edit_estimated_sale_price_per_kg').value = data.estimated_sale_price_per_kg || '';
            document.getElementById('edit_expiry_date').value = data.expiry_date || '';
            document.getElementById('edit_seed_form').action = `/seed-stock/{{ $inventoryType->inventory_type_id }}/seeds/${seedId}`;
            new bootstrap.Modal(document.getElementById('editSeedModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat data benih');
        });
}

// Load reduce stock data
function loadReduceStock(seedId) {
    fetch(`/seed-stock/{{ $inventoryType->inventory_type_id }}/seeds/${seedId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP error! status: ${response.status}, body: ${text.substring(0, 100)}`);
                });
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Response is not JSON');
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('reduce_stock_max').textContent = data.total_seed_quantity || 0;
            document.getElementById('reduce_stock_unit').textContent = data.total_seed_unit || 'kg';
            document.getElementById('reduce_stock_form').action = `/seed-stock/{{ $inventoryType->inventory_type_id }}/seeds/${seedId}/reduce-stock`;
            document.getElementById('reduce_quantity').max = data.total_seed_quantity || 0;
            document.getElementById('reduce_quantity').value = '';
            document.getElementById('reduce_reason').value = '';
            new bootstrap.Modal(document.getElementById('reduceStockModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat data benih. Silakan refresh halaman dan coba lagi.');
        });
}

// Open add to warehouse modal (storageNumber = nomor penyimpanan dari data stok benih yang dipilih)
function openAddToWarehouseModal(seedType, seedId, storageNumber) {
    document.getElementById('add_warehouse_seed_type').value = seedType;
    
    if (seedType === 'certified') {
        document.getElementById('add_warehouse_certification_report_id').value = seedId;
        document.getElementById('add_warehouse_seed_id').value = '';
    } else {
        document.getElementById('add_warehouse_seed_id').value = seedId;
        document.getElementById('add_warehouse_certification_report_id').value = '';
    }
    
    // Pre-fill Nomor Penyimpanan from selected seed stock data
    document.getElementById('add_warehouse_production_id').value = storageNumber || '';
    
    // Reset warehouse/bin
    document.getElementById('add_warehouse_warehouse_id').value = '';
    document.getElementById('add_warehouse_bin_id').innerHTML = '<option value="">-- Pilih Gudang Terlebih Dahulu --</option>';
    document.getElementById('add_warehouse_bin_id').disabled = true;
    
    new bootstrap.Modal(document.getElementById('addToWarehouseModal')).show();
}

function openDeleteStockModal(seedId, seedLabel) {
    document.getElementById('delete_stock_seed_id').value = seedId;
    document.getElementById('delete_stock_label').textContent = seedLabel || 'Data stok benih ini';
    document.getElementById('delete_stock_form').action = `/seed-stock/{{ $inventoryType->inventory_type_id }}/seeds/${seedId}`;
    document.getElementById('delete_reason').value = '';
    new bootstrap.Modal(document.getElementById('deleteStockModal')).show();
}

// Handle warehouse selection change
document.addEventListener('DOMContentLoaded', function() {
    const warehouseSelect = document.getElementById('add_warehouse_warehouse_id');
    const binSelect = document.getElementById('add_warehouse_bin_id');
    
    if (warehouseSelect && binSelect) {
        warehouseSelect.addEventListener('change', function() {
            const warehouseId = this.value;
            binSelect.innerHTML = '<option value="">-- Memuat bin... --</option>';
            binSelect.disabled = true;
            
            if (warehouseId) {
                // Get bins for selected warehouse
                const warehouse = @json($warehouses);
                const selectedWarehouse = warehouse.find(w => (w.warehouse_id || w.id) == warehouseId);
                
                if (selectedWarehouse && selectedWarehouse.bins) {
                    binSelect.innerHTML = '<option value="">-- Pilih Bin/Lot --</option>';
                    selectedWarehouse.bins.forEach(bin => {
                        const option = document.createElement('option');
                        option.value = bin.bin_id || bin.id;
                        option.textContent = bin.name;
                        binSelect.appendChild(option);
                    });
                    binSelect.disabled = false;
                } else {
                    binSelect.innerHTML = '<option value="">-- Tidak ada bin tersedia --</option>';
                }
            } else {
                binSelect.innerHTML = '<option value="">-- Pilih Gudang Terlebih Dahulu --</option>';
            }
        });
    }
});

// Handle modal multi-step untuk tambah stok dari sertifikasi
let currentStep = 1;
let selectedCertificationReport = null;
let certificationsData = {}; // Store certifications data by ID

function loadCertifications() {
    const plantTypeId = document.getElementById('plant_type_id').value;
    if (!plantTypeId) {
        alert('Silakan pilih tipe tanaman terlebih dahulu');
        return;
    }

    // Show loading
    document.getElementById('certifications_list').innerHTML = '<p class="text-muted">Memuat sertifikasi...</p>';
    
    // Fetch certifications
    fetch(`/seed-stock/{{ $inventoryType->inventory_type_id }}/certifications-by-plant-type?plant_type_id=${plantTypeId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                document.getElementById('certifications_list').innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                return;
            }

            if (data.certifications.length === 0) {
                document.getElementById('certifications_list').innerHTML = '<div class="alert alert-warning">Tidak ada sertifikasi yang lulus untuk tipe tanaman ini.</div>';
                return;
            }

            // Store certifications data
            certificationsData = {};
            data.certifications.forEach(cert => {
                certificationsData[cert.id] = cert;
            });

            // Build certifications dropdown for step 3
            const certificationSelect = document.getElementById('form_certification_report_id');
            if (certificationSelect) {
                certificationSelect.innerHTML = '<option value="">-- Pilih Sertifikasi Benih --</option>';
                data.certifications.forEach(cert => {
                    const option = document.createElement('option');
                    option.value = cert.id;
                    option.textContent = `${cert.report_number_bpsb || '-'} - ${cert.plant_name || '-'} (${cert.location_name || '-'})`;
                    option.setAttribute('data-plant-id', cert.plant_id);
                    option.setAttribute('data-plant-name', cert.plant_name);
                    option.setAttribute('data-planting-location-id', cert.planting_location_id);
                    option.setAttribute('data-location-name', cert.location_name);
                    option.setAttribute('data-seed-unit', cert.seed_unit || 'kg');
                    option.setAttribute('data-certified-seed-quantity', cert.certified_seed_quantity || '');
                    option.setAttribute('data-estimated-price', cert.estimated_sale_price_per_kg || '');
                    option.setAttribute('data-expiry-date', cert.expiry_date || '');
                    option.setAttribute('data-report-number', cert.report_number_bpsb || '');
                    certificationSelect.appendChild(option);
                });
            }
            
            // Build certifications list for step 2
            let html = '<div class="table-responsive"><table class="table table-hover">';
            html += '<thead><tr><th>Pilih</th><th>Nomor Laporan BPSB</th><th>Nama Tanaman</th><th>Lokasi Penanaman</th><th>Tanggal Laporan</th><th>Jumlah Benih</th></tr></thead>';
            html += '<tbody>';
            
            data.certifications.forEach(cert => {
                html += `<tr>
                    <td>
                        <input type="radio" name="selected_certification" value="${cert.id}" onchange="selectCertification(${cert.id})">
                    </td>
                    <td><strong>${cert.report_number_bpsb || '-'}</strong></td>
                    <td>${cert.plant_name || '-'}</td>
                    <td>${cert.location_name || '-'}</td>
                    <td>${cert.report_date || '-'}</td>
                    <td>${cert.certified_seed_quantity || 0} ${cert.certified_seed_unit || 'kg'}</td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
            document.getElementById('certifications_list').innerHTML = html;
            
            // Show step 2
            goToStep2();
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('certifications_list').innerHTML = '<div class="alert alert-danger">Terjadi kesalahan saat memuat sertifikasi.</div>';
        });
}

function selectCertification(certId) {
    if (certificationsData[certId]) {
        selectedCertificationReport = certificationsData[certId];
        document.getElementById('selected_certification_report_id').value = certId;
        document.getElementById('btn_continue_to_form').disabled = false;
        console.log('Certification selected:', selectedCertificationReport);
    } else {
        console.error('Certification data not found for ID:', certId);
        alert('Terjadi kesalahan saat memilih sertifikasi');
    }
}

function goToStep1() {
    document.getElementById('step1').style.display = 'block';
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step3').style.display = 'none';
    currentStep = 1;
}

function goToStep2() {
    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'block';
    document.getElementById('step3').style.display = 'none';
    currentStep = 2;
}

function goToStep3() {
    if (!selectedCertificationReport) {
        alert('Silakan pilih sertifikasi terlebih dahulu');
        return;
    }

    console.log('Going to step 3 with certification:', selectedCertificationReport);

    // Set certification report ID in dropdown
    const certificationSelect = document.getElementById('form_certification_report_id');
    if (certificationSelect) {
        certificationSelect.value = selectedCertificationReport.id;
        // Trigger change event to fill other fields
        certificationSelect.dispatchEvent(new Event('change'));
    }

    // Show step 3
    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step3').style.display = 'block';
    currentStep = 3;
}

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Reset modal when closed
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('addStockFromCertificationModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            // Reset form
            currentStep = 1;
            selectedCertificationReport = null;
            certificationsData = {};
            document.getElementById('plant_type_id').value = '';
            document.getElementById('selected_certification_report_id').value = '';
            document.getElementById('certifications_list').innerHTML = '<p class="text-muted">Pilih tipe tanaman terlebih dahulu</p>';
            document.getElementById('btn_continue_to_form').disabled = true;
            
            // Reset form fields
            const formCertificationReportId = document.getElementById('form_certification_report_id');
            const formPlantIdHidden = document.getElementById('form_plant_id_hidden');
            const formPlantingLocationIdHidden = document.getElementById('form_planting_location_id_hidden');
            const formPlantingLocationName = document.getElementById('form_planting_location_name');
            
            if (formCertificationReportId) {
                formCertificationReportId.value = '';
            }
            if (formPlantIdHidden) {
                formPlantIdHidden.value = '';
            }
            if (formPlantingLocationIdHidden) {
                formPlantingLocationIdHidden.value = '';
            }
            if (formPlantingLocationName) {
                formPlantingLocationName.value = '';
            }
            
            const formSeedUnit = document.getElementById('form_seed_unit');
            const formSeedQuantity = document.getElementById('form_seed_quantity');
            const formEstimatedPrice = document.getElementById('form_estimated_sale_price_per_kg');
            const formExpiryDate = document.getElementById('form_expiry_date');
            const formStorageNumber = document.getElementById('form_storage_number');
            
            if (formSeedUnit) formSeedUnit.value = '';
            if (formSeedQuantity) formSeedQuantity.value = '';
            if (formEstimatedPrice) formEstimatedPrice.value = '';
            if (formExpiryDate) formExpiryDate.value = '';
            if (formStorageNumber) formStorageNumber.value = '';
            
            goToStep1();
        });
    }
});

</script>
@endpush

<!-- Modal: Edit Benih -->
<div class="modal fade" id="editSeedModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="edit_seed_form" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Benih</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Satuan Benih <span class="text-danger">*</span></label>
                            <select class="form-select" name="seed_unit" id="edit_seed_unit" required>
                                <option value="">-- Pilih Satuan --</option>
                                <option value="kg">Kilogram (kg)</option>
                                <option value="ton">Ton</option>
                                <option value="kuintal">Kuintal</option>
                                <option value="karung">Karung</option>
                                <option value="sak">Sak</option>
                                <option value="liter">Liter</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah Satuan Benih <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="seed_unit_quantity" id="edit_seed_unit_quantity" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah Benih per Satuan Benih <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="seed_per_unit" id="edit_seed_per_unit" step="0.01" min="0.01" required>
                                <select class="form-select" name="seed_per_unit_unit" id="edit_seed_per_unit_unit" style="max-width: 120px;" required>
                                    <option value="">Pilih</option>
                                    <option value="kg">kg</option>
                                    <option value="ton">ton</option>
                                    <option value="kuintal">kuintal</option>
                                    <option value="karung">karung</option>
                                    <option value="sak">sak</option>
                                    <option value="liter">liter</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah Benih <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="total_seed_quantity" id="edit_total_seed_quantity" step="0.01" min="0.01" required readonly>
                                <select class="form-select" name="total_seed_unit" id="edit_total_seed_unit" style="max-width: 120px;" required>
                                    <option value="">Pilih</option>
                                    <option value="kg">kg</option>
                                    <option value="ton">ton</option>
                                    <option value="kuintal">kuintal</option>
                                    <option value="karung">karung</option>
                                    <option value="sak">sak</option>
                                    <option value="liter">liter</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estimasi Penjualan per Kg</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" name="estimated_sale_price_per_kg" id="edit_estimated_sale_price_per_kg" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Kadaluarsa</label>
                            <input type="date" class="form-control" name="expiry_date" id="edit_expiry_date">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Kurangi Stok -->
<div class="modal fade" id="reduceStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="reduce_stock_form" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Kurangi Stok Benih</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Stok Tersedia:</strong> <span id="reduce_stock_max">0</span> <span id="reduce_stock_unit">kg</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah yang Dikurangi <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="reduce_quantity" id="reduce_quantity" step="0.01" min="0.01" required>
                            <select class="form-select" name="reduce_unit" id="reduce_unit" style="max-width: 120px;" required>
                                <option value="kg">kg</option>
                                <option value="ton">ton</option>
                                <option value="gram">gram</option>
                                <option value="butir">butir/biji</option>
                                <option value="pcs">pcs</option>
                                <option value="batang">batang</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Dikurangi <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reason" id="reduce_reason" rows="3" placeholder="Masukkan alasan pengurangan stok" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Kurangi Stok</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Hapus Stok -->
<div class="modal fade" id="deleteStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="delete_stock_form" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" name="seed_id" id="delete_stock_seed_id">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Data Stok Benih</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Perhatian:</strong> Tindakan ini akan menghapus data stok benih <strong id="delete_stock_label"></strong> dan stok di gudang yang terkait akan ikut terhapus/dikurangi. Data yang dihapus akan tercatat di Riwayat Stok Benih.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan penghapusan (opsional)</label>
                        <textarea class="form-control" name="delete_reason" id="delete_reason" rows="2" placeholder="Contoh: Data duplikat, kesalahan input"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i>Hapus Stok</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Tambahkan ke Gudang -->
<div class="modal fade" id="addToWarehouseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="add_to_warehouse_form" method="POST" action="{{ route('seed-stock.add-seed-to-warehouse', $inventoryType) }}">
                @csrf
                <input type="hidden" name="seed_type" id="add_warehouse_seed_type">
                <input type="hidden" name="seed_id" id="add_warehouse_seed_id">
                <input type="hidden" name="certification_report_id" id="add_warehouse_certification_report_id">
                <div class="modal-header">
                    <h5 class="modal-title">Tambahkan Benih ke Gudang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Gudang <span class="text-danger">*</span></label>
                        <select class="form-select" name="warehouse_id" id="add_warehouse_warehouse_id" required>
                            <option value="">-- Pilih Gudang --</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->warehouse_id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih Bin/Lot <span class="text-danger">*</span></label>
                        <select class="form-select" name="bin_id" id="add_warehouse_bin_id" required>
                            <option value="">-- Pilih Gudang Terlebih Dahulu --</option>
                        </select>
                        <small class="text-muted">Pilih bin atau lot tempat penyimpanan</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor Penyimpanan</label>
                        <input type="text" class="form-control" name="production_id" id="add_warehouse_production_id" placeholder="Mengikuti data stok benih yang dipilih">
                        <small class="text-muted">Diisi dari data stok benih yang dipilih; jika kosong akan di-generate otomatis</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Tambahkan ke Gudang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Tambahkan Data Stok dari Sertifikasi -->
<div class="modal fade" id="addStockFromCertificationModal" tabindex="-1" aria-labelledby="addStockFromCertificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStockFromCertificationModalLabel">Tambahkan Data Stok dari Sertifikasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addStockFromCertificationForm" method="POST" action="{{ route('seed-stock.add-certified-seed', $inventoryType) }}">
                @csrf
                <input type="hidden" name="certification_report_id" id="selected_certification_report_id">
                <div class="modal-body">
                    <!-- Step 1: Pilih Tipe Tanaman -->
                    <div id="step1" class="step-content">
                        <h6 class="mb-3">Langkah 1: Pilih Tipe Tanaman</h6>
                        <div class="mb-3">
                            <label class="form-label">Tipe Tanaman <span class="text-danger">*</span></label>
                            <select class="form-select @error('plant_type_id') is-invalid @enderror" name="plant_type_id" id="plant_type_id" required>
                                <option value="">-- Pilih Tipe Tanaman --</option>
                                @foreach($plantTypes as $plantType)
                                    <option value="{{ $plantType->id }}">{{ $plantType->name }}</option>
                                @endforeach
                            </select>
                            @error('plant_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Pilih tipe tanaman untuk melihat sertifikasi yang tersedia</small>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-primary" onclick="loadCertifications()">Lanjutkan</button>
                        </div>
                    </div>

                    <!-- Step 2: Pilih Sertifikasi -->
                    <div id="step2" class="step-content" style="display: none;">
                        <h6 class="mb-3">Langkah 2: Pilih Sertifikasi yang Lulus</h6>
                        <div id="certifications_list" class="mb-3">
                            <p class="text-muted">Pilih tipe tanaman terlebih dahulu</p>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="goToStep1()">Kembali</button>
                            <button type="button" class="btn btn-primary" onclick="goToStep3()" id="btn_continue_to_form" disabled>Lanjutkan ke Form</button>
                        </div>
                    </div>

                    <!-- Step 3: Form Tambah Stok -->
                    <div id="step3" class="step-content" style="display: none;">
                        <h6 class="mb-3">Langkah 3: Form Tambah Stok</h6>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Data dari Sertifikasi:</strong> Form ini akan diisi otomatis dengan data dari sertifikasi yang dipilih.
                        </div>
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <!-- 1. Pilih Sertifikasi Benih -->
                                <div class="mb-3">
                                    <label class="form-label">Pilih Sertifikasi Benih <span class="text-danger">*</span></label>
                                    <select class="form-select @error('certification_report_id') is-invalid @enderror" name="certification_report_id" id="form_certification_report_id" required>
                                        <option value="">-- Pilih Sertifikasi Benih --</option>
                                    </select>
                                    <input type="hidden" name="plant_id" id="form_plant_id_hidden">
                                    <input type="hidden" name="planting_location_id" id="form_planting_location_id_hidden">
                                    @error('certification_report_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Pilih sertifikasi benih yang akan ditambahkan ke stok</small>
                                </div>

                                <!-- 2. Satuan Inventaris -->
                                <div class="mb-3">
                                    <label class="form-label">Satuan Inventaris <span class="text-danger">*</span></label>
                                    <select class="form-select @error('seed_unit') is-invalid @enderror" name="seed_unit" id="form_seed_unit" required>
                                        <option value="">-- Pilih Satuan --</option>
                                        @foreach(['kg'=>'Kilogram (kg)','ton'=>'Ton','gram'=>'Gram','butir'=>'Butir/Biji','pcs'=>'Pcs','batang'=>'Batang'] as $val => $label)
                                            <option value="{{ $val }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('seed_unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- 3. Estimasi Penjualan per Unit -->
                                <div class="mb-3">
                                    <label class="form-label">Estimasi Penjualan per Unit</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control @error('estimated_sale_price_per_kg') is-invalid @enderror" 
                                               name="estimated_sale_price_per_kg" id="form_estimated_sale_price_per_kg" 
                                               step="0.01" min="0" placeholder="0.00">
                                    </div>
                                    @error('estimated_sale_price_per_kg')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- 4. Pengisi Data -->
                                <div class="mb-3">
                                    <label class="form-label">Pengisi Data <span class="text-danger">*</span></label>
                                    <select class="form-select @error('filled_by_user_id') is-invalid @enderror" name="filled_by_user_id" id="form_filled_by_user_id" required>
                                        <option value="">-- Pilih User --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ auth()->id() == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('filled_by_user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <!-- 1. Lokasi Penanaman -->
                                <div class="mb-3">
                                    <label class="form-label">Lokasi Penanaman</label>
                                    <input type="text" class="form-control" id="form_planting_location_name" readonly>
                                    <small class="text-muted">Lokasi penanaman mengikuti data sertifikasi benih yang dipilih</small>
                                </div>

                                <!-- 2. Jumlah Inventaris -->
                                <div class="mb-3">
                                    <label class="form-label">Jumlah Inventaris <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('seed_quantity') is-invalid @enderror" 
                                           name="seed_quantity" id="form_seed_quantity" 
                                           step="0.01" min="0.01" required placeholder="0.00">
                                    @error('seed_quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- 3. Tanggal Kadaluarsa -->
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Kadaluarsa</label>
                                    <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                           name="expiry_date" id="form_expiry_date">
                                    @error('expiry_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- 4. Nomor Penyimpanan -->
                                <div class="mb-3">
                                    <label class="form-label">Nomor Penyimpanan</label>
                                    <input type="text" class="form-control @error('storage_number') is-invalid @enderror" 
                                           name="storage_number" id="form_storage_number" 
                                           placeholder="Nomor penyimpanan" maxlength="50">
                                    @error('storage_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Nomor penyimpanan (dapat diedit)</small>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <button type="button" class="btn btn-secondary" onclick="goToStep2()">Kembali</button>
                            <button type="submit" class="btn btn-success">Simpan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

