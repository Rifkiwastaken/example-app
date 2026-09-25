@extends('layouts.app')

@section('title', 'Detail Lokasi Penyimpanan: ' . $warehouse->name . ' - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Detail Lokasi Penyimpanan: {{ $warehouse->name }}</h4>
    <a href="{{ route('warehouse-locations.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Informasi Lokasi Penyimpanan</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Nama:</strong> {{ $warehouse->name }}</p>
                <p><strong>ID Internal:</strong> <code>{{ $warehouse->internal_id }}</code></p>
                <p><strong>Tipe lokasi:</strong>
                    <span class="badge bg-info">{{ $warehouse->tipe_lokasi_label }}</span>
                </p>
            </div>
            <div class="col-md-6">
                @if($warehouse->description)
                    <p><strong>Deskripsi:</strong> {{ $warehouse->description }}</p>
                @endif
                <div class="mt-3">
                    <a href="{{ route('warehouse-locations.edit', $warehouse) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit me-2"></i>Edit Informasi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Manajemen Tempat Penyimpanan</h5>
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addBinModal">
            <i class="fas fa-plus me-2"></i>Tambah Blok Penyimpanan
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nama Blok</th>
                        <th>ID Internal</th>
                        <th>Varietas</th>
                        <th>Kapasitas Maks.</th>
                        <th>Stok Produk Saat Ini</th>
                        <th width="230">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warehouse->bins as $bin)
                        @php
                            $activePacks = $bin->packagings->where('status_kemasan', \App\Models\StockPackaging::STATUS_TERSEDIA);
                            $totalStock = $activePacks->sum('kapasitas_per_kemasan');
                            $unit = $bin->capacity_unit ?: '';
                            $varieties = $activePacks
                                ->map(fn ($pkg) => $pkg->stock?->plant?->variety ?: $pkg->stock?->plant?->name)
                                ->filter()
                                ->unique()
                                ->values();
                        @endphp
                        <tr>
                            <td><strong>{{ $bin->name }}</strong></td>
                            <td><code>{{ $bin->internal_id }}</code></td>
                            <td>
                                @forelse($varieties as $variety)
                                    <span class="badge rounded-pill bg-light text-dark border me-1">{{ $variety }}</span>
                                @empty
                                    <span class="text-muted">-</span>
                                @endforelse
                            </td>
                            <td>{{ number_format((float) $bin->max_capacity, 2) }} {{ $unit }}</td>
                            <td>
                                {{ number_format((float) $totalStock, 2) }} {{ $unit }}
                                <br><small class="text-muted">{{ $activePacks->count() }} produk aktif
                                @if($bin->packagings->count() > $activePacks->count())
                                    · {{ $bin->packagings->count() - $activePacks->count() }} tidak aktif
                                @endif
                                </small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('warehouse-locations.racks.packagings', [$warehouse, $bin]) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-boxes me-1"></i>Stok produk
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-warning" title="Edit"
                                            onclick="editBin('{{ $bin->warehouse_bin_id }}', '{{ addslashes($bin->name) }}', '{{ addslashes($bin->internal_id) }}', {{ (float) $bin->max_capacity }}, '{{ $bin->capacity_unit }}', '{{ addslashes($bin->description ?? '') }}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus"
                                            onclick="confirmDelete('{{ route('warehouse-locations.bins.destroy', ['warehouse' => $warehouse->warehouse_id, 'bin' => $bin->warehouse_bin_id]) }}', '{{ addslashes($bin->name) }}', 'blok penyimpanan')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-box fa-3x mb-3"></i>
                                    <p>Belum ada blok penyimpanan yang ditambahkan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addBinModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('warehouse-locations.bins.store', $warehouse) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Blok Penyimpanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bin_name" class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="bin_name" name="name"
                               placeholder="Contoh: Blok Padi A-03" required>
                    </div>
                    <div class="mb-3">
                        <label for="bin_internal_id" class="form-label">ID Internal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="bin_internal_id" name="internal_id"
                               placeholder="Contoh: BLK-A03" required>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="bin_max_capacity" class="form-label">Kapasitas Maksimal <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="bin_max_capacity" name="max_capacity"
                                       placeholder="1000" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="bin_capacity_unit" class="form-label">Unit</label>
                                <select class="form-select" id="bin_capacity_unit" name="capacity_unit" required>
                                    <option value="">Pilih Satuan</option>
                                    @foreach(($seedUnits ?? \App\Models\SeedUnit::orderBy('name')->get()) as $seedUnit)
                                        <option value="{{ $seedUnit->code }}" {{ $seedUnit->code === 'kg' ? 'selected' : '' }}>{{ $seedUnit->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="bin_description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="bin_description" name="description" rows="2"
                                  placeholder="Contoh: Blok khusus benih BP Varietas Inpari"></textarea>
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

<div class="modal fade" id="editBinModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editBinForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Blok Penyimpanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_bin_name" class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_bin_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_bin_internal_id" class="form-label">ID Internal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_bin_internal_id" name="internal_id" required>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="edit_bin_max_capacity" class="form-label">Kapasitas Maksimal <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="edit_bin_max_capacity" name="max_capacity" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_bin_capacity_unit" class="form-label">Unit</label>
                                <select class="form-select" id="edit_bin_capacity_unit" name="capacity_unit" required>
                                    <option value="">Pilih Satuan</option>
                                    @foreach(($seedUnits ?? \App\Models\SeedUnit::orderBy('name')->get()) as $seedUnit)
                                        <option value="{{ $seedUnit->code }}">{{ $seedUnit->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_bin_description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="edit_bin_description" name="description" rows="2"></textarea>
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
@endsection

@push('scripts')
<script>
function editBin(id, name, internalId, maxCapacity, capacityUnit, description) {
    const form = document.getElementById('editBinForm');
    form.action = '{{ route("warehouse-locations.bins.update", ["warehouse" => $warehouse->warehouse_id, "bin" => ":bin"]) }}'.replace(':bin', id);

    document.getElementById('edit_bin_name').value = name;
    document.getElementById('edit_bin_internal_id').value = internalId;
    document.getElementById('edit_bin_max_capacity').value = maxCapacity;
    document.getElementById('edit_bin_capacity_unit').value = capacityUnit;
    document.getElementById('edit_bin_description').value = description || '';

    new bootstrap.Modal(document.getElementById('editBinModal')).show();
}
</script>
@endpush
