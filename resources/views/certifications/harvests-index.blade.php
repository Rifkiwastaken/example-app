@extends('layouts.app')

@section('title', 'Data Panen Benih - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Data Panen Benih</h4>
        <small class="text-muted">Daftar semua panen benih yang sudah dan belum disertifikasi</small>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form class="row g-3" method="GET" action="{{ route('certifications.harvests.index') }}">
            <div class="col-md-2">
                <label class="form-label">Komoditas/Tanaman</label>
                <select name="plant_id" class="form-select">
                    <option value="">Semua Komoditas</option>
                    @foreach($allPlants as $plant)
                        <option value="{{ $plant->plant_id }}" {{ request('plant_id') == $plant->plant_id ? 'selected' : '' }}>
                            {{ $plant->name }} @if($plant->variety) - {{ $plant->variety }} @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Lokasi Penanaman</label>
                <select name="planting_location_id" class="form-select">
                    <option value="">Semua Lokasi</option>
                    @foreach($allPlantingLocations as $location)
                        <option value="{{ $location->planting_location_id }}" {{ request('planting_location_id') == $location->planting_location_id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status Sertifikasi</label>
                <select name="certification_status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="belum_disertifikasi" {{ request('certification_status') == 'belum_disertifikasi' ? 'selected' : '' }}>Belum Disertifikasi</option>
                    <option value="dalam_proses" {{ request('certification_status') == 'dalam_proses' ? 'selected' : '' }}>Dalam Proses</option>
                    <option value="lulus" {{ request('certification_status') == 'lulus' ? 'selected' : '' }}>Lulus</option>
                    <option value="sudah_melewati_masa_edar" {{ request('certification_status') == 'sudah_melewati_masa_edar' ? 'selected' : '' }}>Sudah Melewati Masa Edar</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status Stok</label>
                <select name="stock_status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="telah_ditambahkan_ke_stok" {{ request('stock_status') == 'telah_ditambahkan_ke_stok' ? 'selected' : '' }}>Telah Ditambahkan ke Stok</option>
                    <option value="telah_dihapus" {{ request('stock_status') == 'telah_dihapus' ? 'selected' : '' }}>Data Stok Telah Dihapus</option>
                    <option value="belum_ditambahkan_ke_stok" {{ request('stock_status') == 'belum_ditambahkan_ke_stok' ? 'selected' : '' }}>Belum Ditambahkan ke Stok</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button class="btn btn-primary" type="submit"><i class="fas fa-filter me-1"></i>Filter</button>
                <a class="btn btn-secondary" href="{{ route('certifications.harvests.index') }}"><i class="fas fa-times me-1"></i>Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Table Section -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>
                            <a href="{{ route('certifications.harvests.index', array_merge(request()->all(), ['sort_by' => 'plant_name', 'sort_order' => request('sort_by') == 'plant_name' && request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Komoditas Benih
                                @if(request('sort_by') == 'plant_name')
                                    <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort text-muted"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('certifications.harvests.index', array_merge(request()->all(), ['sort_by' => 'location_name', 'sort_order' => request('sort_by') == 'location_name' && request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                Lokasi Penanaman
                                @if(request('sort_by') == 'location_name')
                                    <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort text-muted"></i>
                                @endif
                            </a>
                        </th>
                        <th>Varietas</th>
                        <th>Tanggal Panen</th>
                        <th>Jumlah Panen</th>
                        <th>Status Sertifikasi</th>
                        <th>Tambahkan ke Stok</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($harvestsPaginated as $harvest)
                        <tr>
                            <td>
                                <strong>{{ $harvest->plant->name }}</strong>
                                @if($harvest->plant->type)
                                    <br><small class="text-muted">{{ $harvest->plant->type->name }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $harvest->location->name ?? '-' }}
                            </td>
                            <td>{{ $harvest->plant->variety ?: '-' }}</td>
                            <td>
                                @if($harvest->harvested_at)
                                    {{ $harvest->harvested_at->format('d M Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($harvest->quantity)
                                    {{ number_format($harvest->quantity, 2) }} {{ $harvest->unit ?? 'kg' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($harvest->certification)
                                    @php
                                        // Check if any report has expired
                                        $hasExpired = $harvest->certification->reports->contains(function($report) {
                                            return $report->expiry_date && $report->expiry_date->isPast();
                                        });
                                    @endphp
                                    @if($hasExpired)
                                        <span class="badge bg-danger">Sudah Melewati Masa Edar</span>
                                    @else
                                        <span class="badge {{ match($harvest->certification->certification_status) {
                                            'dalam_proses' => 'bg-warning',
                                            'lulus' => 'bg-success',
                                            'tidak_lulus' => 'bg-danger',
                                            'selesai' => 'bg-info',
                                            default => 'bg-secondary',
                                        } }}">
                                            {{ $harvest->certification->status_label }}
                                        </span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Belum Disertifikasi</span>
                                @endif
                            </td>
                            <td>
                                @if($harvest->certification)
                                    @php
                                        // Pernah ditambahkan = report punya pivot ke inventory type
                                        $hasBeenAddedToStock = $harvest->certification->reports->contains(function($report) {
                                            return $report->inventoryTypes->count() > 0;
                                        });
                                        // Data stok telah dihapus = ada report yang punya pivot tapi seed-nya sudah tidak ada
                                        $stockWasDeleted = false;
                                        if ($hasBeenAddedToStock) {
                                            $stockWasDeleted = $harvest->certification->reports->contains(function($report) {
                                                if ($report->inventoryTypes->count() === 0) return false;
                                                $linkedTypeIds = $report->inventoryTypes->pluck('inventory_type_id')->toArray();
                                                $seedStillExists = \App\Models\InventoryTypeSeed::where('certification_report_id', $report->certification_report_id)
                                                    ->whereIn('inventory_type_id', $linkedTypeIds)
                                                    ->exists();
                                                return !$seedStillExists;
                                            });
                                        }
                                        // Yang benar-benar "telah ditambahkan" = punya pivot dan seed masih ada
                                        if ($stockWasDeleted) {
                                            $hasBeenAddedToStock = false;
                                        } else if ($hasBeenAddedToStock) {
                                            $hasBeenAddedToStock = $harvest->certification->reports->contains(function($report) {
                                                if ($report->inventoryTypes->count() === 0) return false;
                                                $linkedTypeIds = $report->inventoryTypes->pluck('inventory_type_id')->toArray();
                                                return \App\Models\InventoryTypeSeed::where('certification_report_id', $report->certification_report_id)
                                                    ->whereIn('inventory_type_id', $linkedTypeIds)
                                                    ->exists();
                                            });
                                        }
                                        $linkedInventoryType = null;
                                        if ($hasBeenAddedToStock) {
                                            foreach ($harvest->certification->reports as $report) {
                                                if ($report->inventoryTypes->count() > 0) {
                                                    $linkedTypeIds = $report->inventoryTypes->pluck('inventory_type_id')->toArray();
                                                    if (\App\Models\InventoryTypeSeed::where('certification_report_id', $report->certification_report_id)->whereIn('inventory_type_id', $linkedTypeIds)->exists()) {
                                                        $linkedInventoryType = $report->inventoryTypes->first();
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                        $isEligibleForStock = !$stockWasDeleted && $harvest->certification->reports->contains(function($report) {
                                            return $report->conclusion === 'LULUS' 
                                                && $report->certified_seed_quantity 
                                                && $report->certified_seed_quantity > 0
                                                && $report->inventoryTypes->count() == 0;
                                        });
                                    @endphp
                                    @if($stockWasDeleted)
                                        <span class="badge bg-danger">
                                            <i class="fas fa-trash me-1"></i>Data Stok Telah Dihapus
                                        </span>
                                    @elseif($hasBeenAddedToStock)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Telah Ditambahkan ke Stok
                                        </span>
                                    @elseif($isEligibleForStock)
                                        <a href="{{ route('certifications.show', $harvest) }}" class="btn btn-sm btn-success" title="Tambahkan ke Stok">
                                            <i class="fas fa-plus me-1"></i>Tambahkan ke Stok
                                        </a>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-clock me-1"></i>Belum Ditambahkan ke Stok
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                        @if($harvest->certification)
                                        <a href="{{ route('certifications.show', $harvest) }}" class="btn btn-primary" title="Kelola Sertifikasi">
                                            <i class="fas fa-cog me-1"></i>Kelola
                                        </a>
                                        @if(isset($hasBeenAddedToStock) && $hasBeenAddedToStock && $linkedInventoryType)
                                            <a href="{{ route('seed-stock.show', $linkedInventoryType) }}?tab=certified-seeds" class="btn btn-info" title="Lihat Data Stok">
                                                <i class="fas fa-eye me-1"></i>Lihat Data Stok
                                            </a>
                                        @endif
                                    @else
                                        <a href="{{ route('certifications.create', ['harvest_id' => $harvest->harvest_id]) }}" class="btn btn-success" title="Mulai Sertifikasi">
                                            <i class="fas fa-plus me-1"></i>Mulai Sertifikasi
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-seedling fa-3x mb-3"></i>
                                    <p>Belum ada data panen benih.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($harvestsPaginated->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
                <div class="text-muted">
                    Menampilkan {{ $harvestsPaginated->firstItem() ?? 0 }} sampai {{ $harvestsPaginated->lastItem() ?? 0 }} dari {{ $harvestsPaginated->total() }} hasil
                </div>
                <nav aria-label="Page navigation">
                    {{ $harvestsPaginated->appends(request()->query())->links('pagination::bootstrap-5') }}
                </nav>
            </div>
        @endif
    </div>
</div>
@endsection


