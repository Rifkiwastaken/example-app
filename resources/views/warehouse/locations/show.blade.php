@extends('layouts.app')

@section('title', 'Detail Gudang: ' . $warehouse->name . ' - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Detail Gudang: {{ $warehouse->name }}</h4>
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

<!-- Bagian 1: Informasi Gudang -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Informasi Gudang</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Nama:</strong> {{ $warehouse->name }}</p>
                <p><strong>ID Internal:</strong> <code>{{ $warehouse->internal_id }}</code></p>
                <p><strong>Tipe Pelacakan:</strong> 
                    <span class="badge bg-info">{{ $warehouse->tracking_type_label }}</span>
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

<!-- Bagian 2: Manajemen Bin (hanya muncul jika tracking_type = bin_separated) -->
@if($warehouse->tracking_type === 'bin_separated')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Manajemen Bin / Tempat Penyimpanan</h5>
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addBinModal">
            <i class="fas fa-plus me-2"></i>Tambahkan Tempat Penyimpanan
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nama Bin</th>
                        <th>ID Internal</th>
                        <th>Kapasitas Maks.</th>
                        <th>Stok Saat Ini</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warehouse->bins as $bin)
                    <tr>
                        <td><strong>{{ $bin->name }}</strong></td>
                        <td><code>{{ $bin->internal_id }}</code></td>
                        <td>{{ number_format($bin->max_capacity, 0) }} {{ $bin->capacity_unit }}</td>
                        <td>
                            @php
                                $totalStock = $bin->inventoryLots->sum('current_stock');
                                $unit = $bin->inventoryLots->first()?->stock_unit ?? 'kg';
                            @endphp
                            {{ number_format($totalStock, 2) }} {{ $unit }}
                            @if($bin->inventoryLots->count() > 0)
                                <br><small class="text-muted">{{ $bin->inventoryLots->count() }} lot</small>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-success" 
                                        onclick="openAddBibitModal('{{ $bin->bin_id }}', '{{ addslashes($bin->name) }}')" 
                                        title="Tambah Benih">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info" 
                                        onclick="viewBinStocks('{{ $bin->bin_id }}')" 
                                        title="Lihat Stok">
                                    <i class="fas fa-boxes"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                        onclick="editBin('{{ $bin->bin_id }}', '{{ addslashes($bin->name) }}', '{{ addslashes($bin->internal_id) }}', {{ $bin->max_capacity }}, '{{ $bin->capacity_unit }}', '{{ addslashes($bin->description ?? '') }}')" 
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        title="Hapus"
                                        onclick="confirmDelete('{{ route('warehouse-locations.bins.destroy', ['warehouse' => $warehouse->warehouse_id, 'bin' => $bin->bin_id]) }}', '{{ addslashes($bin->name) }}', 'bin')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-box fa-3x mb-3"></i>
                                <p>Belum ada tempat penyimpanan yang ditambahkan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Modal: Tambah Bin -->
<div class="modal fade" id="addBinModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('warehouse-locations.bins.store', $warehouse) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Bin Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bin_name" class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="bin_name" name="name" 
                               placeholder="Contoh: Rak Padi A-03" required>
                    </div>
                    <div class="mb-3">
                        <label for="bin_internal_id" class="form-label">ID Internal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="bin_internal_id" name="internal_id" 
                               placeholder="Contoh: RAK-A03" required>
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
                                    <option value="kg" selected>Kilogram (kg)</option>
                                    <option value="ton">Ton</option>
                                    <option value="kuintal">Kuintal</option>
                                    <option value="karung">Karung</option>
                                    <option value="sak">Sak</option>
                                    <option value="liter">Liter</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="bin_description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="bin_description" name="description" rows="2" 
                                  placeholder="Contoh: Rak khusus benih BP Varietas Inpari"></textarea>
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

<!-- Modal: Edit Bin -->
<div class="modal fade" id="editBinModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editBinForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Bin</h5>
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
                                    <option value="kg">Kilogram (kg)</option>
                                    <option value="ton">Ton</option>
                                    <option value="kuintal">Kuintal</option>
                                    <option value="karung">Karung</option>
                                    <option value="sak">Sak</option>
                                    <option value="liter">Liter</option>
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

<!-- Modal: Tambah Benih ke Bin -->
<div class="modal fade" id="addBibitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addBibitForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Benih ke Bin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Bin:</strong> <span id="binNameDisplay"></span>
                    </div>
                    <div class="mb-3">
                        <label for="inventory_type_id" class="form-label">Pilih Benih (dari Master Stok Benih) <span class="text-danger">*</span></label>
                        <select class="form-select" id="inventory_type_id" name="inventory_type_id" required>
                            <option value="">-- Pilih Benih --</option>
                            @foreach($inventoryTypes as $invType)
                                <option value="{{ $invType->inventory_type_id }}" data-unit="{{ $invType->unit }}">
                                    {{ $invType->name }} @if($invType->sku) ({{ $invType->sku }}) @endif - {{ $invType->category }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Data benih diambil dari Master Stok Benih</small>
                    </div>
                    <div class="mb-3">
                        <label for="seed_id" class="form-label">Tambahkan Stok <span class="text-danger">*</span></label>
                        <select class="form-select" id="seed_id" name="seed_id" required>
                            <option value="">-- Pilih Data Benih --</option>
                        </select>
                        <small class="text-muted">Pilih data benih dari stok benih yang telah dipilih</small>
                    </div>
                    <div class="mb-3" id="seed_info_container" style="display: none;">
                        <div class="alert alert-info mb-0">
                            <strong>Informasi Benih:</strong>
                            <div id="seed_info_display"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="expiry_date" class="form-label">Tanggal Kadaluarsa <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="expiry_date" name="expiry_date" required>
                        <small class="text-muted">Isi tanggal kadaluarsa benih</small>
                    </div>
                    <div class="mb-3">
                        <label for="production_id" class="form-label">Nomor Penyimpanan</label>
                        <input type="text" class="form-control" id="production_id" name="production_id" placeholder="Nomor penyimpanan dari benih yang dipilih" readonly>
                        <small class="text-muted">Nomor penyimpanan diambil dari data benih yang dipilih</small>
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

<!-- Modal: Lihat Stok di Bin -->
<div class="modal fade" id="viewBinStocksModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Daftar Stok di Bin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="binStocksContent">
                    <p class="text-muted">Memuat data...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Update Data Benih -->
<div class="modal fade" id="updateStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="updateStockForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Update Data Benih di Bin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Bin:</strong> <span id="update_bin_name_display"></span><br>
                        <strong>Data Benih Saat Ini:</strong> <span id="update_current_seed_info"></span>
                    </div>
                    <div class="mb-3">
                        <label for="update_reason" class="form-label">Alasan Update Data Stok Benih <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="update_reason" name="update_reason" placeholder="melewati masa edar" required>
                        <small class="text-muted">Masukkan alasan mengapa data benih perlu diupdate</small>
                    </div>
                    <div class="mb-3">
                        <label for="update_inventory_type_id" class="form-label">Pilih Benih (dari Master Stok Benih) <span class="text-danger">*</span></label>
                        <select class="form-select" id="update_inventory_type_id" name="inventory_type_id" required>
                            <option value="">-- Pilih Bibit --</option>
                            @foreach($inventoryTypes as $invType)
                                <option value="{{ $invType->inventory_type_id }}" data-unit="{{ $invType->unit }}">
                                    {{ $invType->name }} @if($invType->sku) ({{ $invType->sku }}) @endif - {{ $invType->category }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Data benih diambil dari Master Stok Benih</small>
                    </div>
                    <div class="mb-3">
                        <label for="update_seed_id" class="form-label">Tambahkan Stok <span class="text-danger">*</span></label>
                        <select class="form-select" id="update_seed_id" name="seed_id" required>
                            <option value="">-- Pilih Data Benih --</option>
                        </select>
                        <small class="text-muted">Pilih data benih dari stok benih yang telah dipilih</small>
                    </div>
                    <div class="mb-3" id="update_seed_info_container" style="display: none;">
                        <div class="alert alert-info mb-0">
                            <strong>Informasi Benih:</strong>
                            <div id="update_seed_info_display"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="update_expiry_date" class="form-label">Tanggal Kadaluarsa <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="update_expiry_date" name="expiry_date" required>
                        <small class="text-muted">Isi tanggal kadaluarsa benih</small>
                    </div>
                    <div class="mb-3">
                        <label for="update_production_id" class="form-label">Nomor Batch/Produksi (Opsional)</label>
                        <input type="text" class="form-control" id="update_production_id" name="production_id" placeholder="Akan di-generate otomatis jika dikosongkan">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Kurangi Stok -->
<div class="modal fade" id="reduceStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="reduceStockForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Kurangi Stok Benih</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Bibit:</strong> <span id="reduce_stock_inventory_name"></span><br>
                        <strong>Batch/Produksi:</strong> <span id="reduce_stock_production_id"></span><br>
                        <strong>Stok Tersedia:</strong> <span id="reduce_stock_available"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah yang Dikurangi <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="reduce_quantity" id="reduce_quantity" step="0.01" min="0.01" required>
                            <span class="input-group-text" id="reduce_stock_unit">kg</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Dikurangi <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reduce_reason" id="reduce_reason" rows="3" placeholder="Masukkan alasan pengurangan stok" required></textarea>
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

<!-- Modal: Riwayat Mutasi Stok Lot -->
<div class="modal fade" id="lotHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Riwayat Mutasi Stok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="lotHistoryContent">
                    <p class="text-muted">Memuat riwayat...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Hapus Stok -->
<div class="modal fade" id="deleteLotModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deleteLotForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Stok Benih</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Peringatan:</strong> Tindakan ini akan menghapus stok benih secara permanen. Data yang dihapus akan tersimpan di riwayat.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Batch/Produksi</label>
                        <input type="text" class="form-control" id="delete_lot_production_id" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Benih Dihapus <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="delete_reason" id="delete_reason" rows="3" placeholder="Masukkan alasan benih dihapus" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus Stok</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto-open bin stocks modal if view_bin parameter is present in URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const viewBinId = urlParams.get('view_bin');
    
    if (viewBinId) {
        // Remove the parameter from URL
        const newUrl = window.location.pathname + window.location.search.replace(/[?&]view_bin=\d+/, '');
        window.history.replaceState({}, '', newUrl);
        
        // Open the bin stocks modal
        viewBinStocks(viewBinId);
    }
});

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

function openAddBibitModal(binId, binName) {
    const form = document.getElementById('addBibitForm');
    form.action = '{{ route("warehouse-locations.bins.inventory-lots.store", ["warehouse" => $warehouse->warehouse_id, "bin" => ":bin"]) }}'.replace(':bin', binId);
    
    document.getElementById('binNameDisplay').textContent = binName;
    document.getElementById('seed_id').value = '';
    document.getElementById('expiry_date').value = '';
    document.getElementById('production_id').value = '';
    document.getElementById('inventory_type_id').value = '';
    document.getElementById('seed_info_container').style.display = 'none';
    document.getElementById('seed_id').innerHTML = '<option value="">-- Pilih Data Benih --</option>';
    
    new bootstrap.Modal(document.getElementById('addBibitModal')).show();
}

// Load seeds when inventory type changes
document.getElementById('inventory_type_id').addEventListener('change', function() {
    const inventoryTypeId = this.value;
    const seedSelect = document.getElementById('seed_id');
    const seedInfoContainer = document.getElementById('seed_info_container');
    const seedInfoDisplay = document.getElementById('seed_info_display');
    
    // Reset
    seedSelect.innerHTML = '<option value="">-- Pilih Data Benih --</option>';
    seedInfoContainer.style.display = 'none';
    
    if (!inventoryTypeId) {
        return;
    }
    
    // Show loading
    seedSelect.innerHTML = '<option value="">Memuat data benih...</option>';
    seedSelect.disabled = true;
    
    // Fetch seeds
    fetch(`/warehouse-locations/inventory-types/${inventoryTypeId}/seeds`)
        .then(response => response.json())
        .then(data => {
            seedSelect.innerHTML = '<option value="">-- Pilih Data Benih --</option>';
            
            if (data.seeds && data.seeds.length > 0) {
                data.seeds.forEach(seed => {
                    const option = document.createElement('option');
                    option.value = seed.id;
                    // Add storage number to display text if available
                    let displayText = seed.display_text;
                    if (seed.storage_number && seed.storage_number !== '-') {
                        displayText += ' [Nomor Penyimpanan: ' + seed.storage_number + ']';
                    }
                    option.textContent = displayText;
                    option.dataset.quantity = seed.total_seed_quantity;
                    option.dataset.unit = seed.total_seed_unit;
                    option.dataset.expiryDate = seed.expiry_date || '';
                    option.dataset.bpsbNumber = seed.bpsb_number || '';
                    option.dataset.storageNumber = seed.storage_number || '';
                    seedSelect.appendChild(option);
                });
            } else {
                seedSelect.innerHTML = '<option value="">Tidak ada data benih tersedia</option>';
            }
            
            seedSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            seedSelect.innerHTML = '<option value="">Error memuat data benih</option>';
            seedSelect.disabled = false;
        });
});

// Show seed info when seed is selected
document.getElementById('seed_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const seedInfoContainer = document.getElementById('seed_info_container');
    const seedInfoDisplay = document.getElementById('seed_info_display');
    const productionIdField = document.getElementById('production_id');
    
    if (this.value && selectedOption.dataset.quantity) {
        let info = `<div class="mb-1"><strong>Jumlah:</strong> ${parseFloat(selectedOption.dataset.quantity).toFixed(2)} ${selectedOption.dataset.unit || 'kg'}</div>`;
        
        // Add storage number if available
        if (selectedOption.dataset.storageNumber && selectedOption.dataset.storageNumber !== '-') {
            info += `<div class="mb-1"><strong>Nomor Penyimpanan:</strong> ${selectedOption.dataset.storageNumber}</div>`;
            // Auto-fill storage number
            if (productionIdField) {
                productionIdField.value = selectedOption.dataset.storageNumber;
            }
        } else {
            // Clear storage number if not available
            if (productionIdField) {
                productionIdField.value = '';
            }
        }
        
        // Add BPSB number if available
        if (selectedOption.dataset.bpsbNumber && selectedOption.dataset.bpsbNumber !== '-') {
            info += `<div class="mb-1"><strong>Nomor Laporan BPSB:</strong> ${selectedOption.dataset.bpsbNumber}</div>`;
        }
        
        if (selectedOption.dataset.expiryDate) {
            const expiryDate = new Date(selectedOption.dataset.expiryDate);
            info += `<div class="mb-1"><strong>Tanggal Kadaluarsa:</strong> ${expiryDate.toLocaleDateString('id-ID')}</div>`;
        }
        
        seedInfoDisplay.innerHTML = info;
        seedInfoContainer.style.display = 'block';
        
        // Auto-fill expiry date if available
        if (selectedOption.dataset.expiryDate) {
            document.getElementById('expiry_date').value = selectedOption.dataset.expiryDate;
        }
    } else {
        seedInfoContainer.style.display = 'none';
        // Clear storage number if no seed selected
        if (productionIdField) {
            productionIdField.value = '';
        }
    }
});

function viewBinStocks(binId) {
    const contentDiv = document.getElementById('binStocksContent');
    contentDiv.innerHTML = '<p class="text-muted">Memuat data...</p>';
    
    const url = '{{ route("warehouse-locations.bins.stocks", ["warehouse" => $warehouse->warehouse_id, "bin" => ":bin"]) }}'.replace(':bin', binId);
    
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            const ct = response.headers.get('content-type');
            if (!ct || !ct.includes('application/json')) throw new Error('Respons bukan JSON');
            return response.json();
        })
        .then(data => {
            let html = `
                <div class="mb-3">
                    <h6>Bin: <strong>${data.bin.name}</strong> (${data.bin.internal_id})</h6>
                </div>
            `;
            
            if (data.lots.length > 0) {
                html += `
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Benih</th>
                                    <th>Nomor Penyimpanan</th>
                                    <th>Stok</th>
                                    <th>Kadaluarsa</th>
                                    <th>Status</th>
                                    <th width="200">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                data.lots.forEach(lot => {
                    // Build detail button URL
                    let detailButton = '';
                    if (lot.seed_type === 'certified' && lot.certification_report_id) {
                        detailButton = `<a href="/seed-stock/${lot.inventory_type_id}/certified-seeds/${lot.certification_report_id}" class="btn btn-sm btn-outline-info" title="Lihat Detail Benih">
                            <i class="fas fa-eye"></i>
                        </a>`;
                    } else if (lot.seed_type === 'seed' && lot.seed_id) {
                        detailButton = `<a href="/seed-stock/${lot.inventory_type_id}/seeds/${lot.seed_id}" class="btn btn-sm btn-outline-info" title="Lihat Detail Benih">
                            <i class="fas fa-eye"></i>
                        </a>`;
                    }
                    
                    html += `
                        <tr id="lot-row-${lot.id}">
                            <td><strong>${lot.inventory_type_name}</strong></td>
                            <td><code>${lot.production_id || '-'}</code></td>
                            <td>${parseFloat(lot.current_stock).toLocaleString('id-ID')} ${lot.stock_unit}</td>
                            <td>${lot.expiry_date}</td>
                            <td><span class="badge bg-${lot.status_color}">${lot.status_label}</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    ${detailButton}
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="updateStock('${binId}', '${lot.id}', '${lot.inventory_type_name.replace(/'/g, "\\'")}', '${(lot.production_id || '').replace(/'/g, "\\'")}', ${lot.current_stock}, '${lot.stock_unit}', '${lot.inventory_type_id}')" 
                                            title="Update Data Benih">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning" 
                                            onclick="reduceStock('${binId}', '${lot.id}', '${lot.inventory_type_name.replace(/'/g, "\\'")}', '${(lot.production_id || '').replace(/'/g, "\\'")}', ${lot.current_stock}, '${lot.stock_unit}')" 
                                            title="Kurangi Stok">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" 
                                            onclick="showLotHistory('{{ $warehouse->warehouse_id }}', '${binId}', '${lot.id}', '${(lot.production_id || '-').replace(/'/g, "\\'")}', '${(lot.inventory_type_name || '').replace(/'/g, "\\'")}')" 
                                            title="Riwayat Mutasi Stok">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteLot('${binId}', '${lot.id}', '${(lot.production_id || '').replace(/'/g, "\\'")}')" 
                                            title="Hapus Stok">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                html += '<p class="text-muted text-center py-3">Belum ada stok benih di bin ini.</p>';
            }
            
            contentDiv.innerHTML = html;
            new bootstrap.Modal(document.getElementById('viewBinStocksModal')).show();
        })
        .catch(error => {
            contentDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Error memuat data stok: ${error.message}
                </div>
            `;
            new bootstrap.Modal(document.getElementById('viewBinStocksModal')).show();
        });
}

function updateStock(binId, lotId, inventoryName, productionId, currentStock, stockUnit, inventoryTypeId) {
    // Set form action
    const form = document.getElementById('updateStockForm');
    form.action = '{{ route("warehouse-locations.bins.inventory-lots.update-stock", ["warehouse" => $warehouse->warehouse_id, "bin" => ":bin", "lot" => ":lot"]) }}'
        .replace(':bin', binId)
        .replace(':lot', lotId);
    
    // Get bin name from the table
    const binNameElement = document.querySelector(`button[onclick*="viewBinStocks('${binId.replace(/'/g, "\\'")}')"]`);
    let binName = 'Bin';
    if (binNameElement) {
        const row = binNameElement.closest('tr');
        if (row) {
            const binNameCell = row.querySelector('td:first-child');
            if (binNameCell) {
                binName = binNameCell.textContent.trim();
            }
        }
    }
    
    // Populate form fields
    document.getElementById('update_bin_name_display').textContent = binName;
    document.getElementById('update_current_seed_info').textContent = `${inventoryName} - Batch: ${productionId} - Stok: ${parseFloat(currentStock).toLocaleString('id-ID')} ${stockUnit}`;
    document.getElementById('update_reason').value = '';
    document.getElementById('update_inventory_type_id').value = inventoryTypeId || '';
    document.getElementById('update_seed_id').innerHTML = '<option value="">-- Pilih Data Benih --</option>';
    document.getElementById('update_seed_id').disabled = true;
    document.getElementById('update_seed_info_container').style.display = 'none';
    document.getElementById('update_expiry_date').value = '';
    document.getElementById('update_production_id').value = '';
    
    // Load seeds if inventory type is selected
    if (inventoryTypeId) {
        loadSeedsForUpdate(inventoryTypeId);
    }
    
    // Show modal
    new bootstrap.Modal(document.getElementById('updateStockModal')).show();
}

function loadSeedsForUpdate(inventoryTypeId) {
    const seedSelect = document.getElementById('update_seed_id');
    seedSelect.innerHTML = '<option value="">Memuat data benih...</option>';
    seedSelect.disabled = true;
    
    fetch(`/warehouse-locations/inventory-types/${inventoryTypeId}/seeds`)
        .then(response => response.json())
        .then(data => {
            seedSelect.innerHTML = '<option value="">-- Pilih Data Benih --</option>';
            
            if (data.seeds && data.seeds.length > 0) {
                data.seeds.forEach(seed => {
                    const option = document.createElement('option');
                    option.value = seed.id;
                    let displayText = seed.display_text;
                    if (seed.storage_number && seed.storage_number !== '-') {
                        displayText += ' [Nomor Penyimpanan: ' + seed.storage_number + ']';
                    }
                    if (seed.bpsb_number && seed.bpsb_number !== '-') {
                        displayText += ' [BPSB: ' + seed.bpsb_number + ']';
                    }
                    option.textContent = displayText;
                    option.dataset.quantity = seed.total_seed_quantity;
                    option.dataset.unit = seed.total_seed_unit;
                    option.dataset.expiryDate = seed.expiry_date || '';
                    option.dataset.bpsbNumber = seed.bpsb_number || '';
                    option.dataset.storageNumber = seed.storage_number || '';
                    seedSelect.appendChild(option);
                });
            } else {
                seedSelect.innerHTML = '<option value="">Tidak ada data benih tersedia</option>';
            }
            
            seedSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            seedSelect.innerHTML = '<option value="">Error memuat data benih</option>';
            seedSelect.disabled = false;
        });
}

function reduceStock(binId, lotId, inventoryName, productionId, currentStock, stockUnit) {
    // Set form action
    const form = document.getElementById('reduceStockForm');
    form.action = '{{ route("warehouse-locations.bins.inventory-lots.reduce-stock", ["warehouse" => $warehouse->warehouse_id, "bin" => ":bin", "lot" => ":lot"]) }}'
        .replace(':bin', binId)
        .replace(':lot', lotId);
    
    // Set form method
    form.method = 'POST';
    
    // Populate form fields
    document.getElementById('reduce_stock_inventory_name').textContent = inventoryName;
    document.getElementById('reduce_stock_production_id').textContent = productionId;
    document.getElementById('reduce_stock_available').textContent = parseFloat(currentStock).toLocaleString('id-ID') + ' ' + stockUnit;
    document.getElementById('reduce_stock_unit').textContent = stockUnit;
    document.getElementById('reduce_quantity').value = '';
    document.getElementById('reduce_quantity').max = currentStock;
    document.getElementById('reduce_reason').value = '';
    
    // Show modal
    new bootstrap.Modal(document.getElementById('reduceStockModal')).show();
}

// Load seeds when inventory type changes in update form
document.addEventListener('DOMContentLoaded', function() {
    const updateInventoryTypeSelect = document.getElementById('update_inventory_type_id');
    if (updateInventoryTypeSelect) {
        updateInventoryTypeSelect.addEventListener('change', function() {
            const inventoryTypeId = this.value;
            const seedSelect = document.getElementById('update_seed_id');
            const seedInfoContainer = document.getElementById('update_seed_info_container');
            
            seedSelect.innerHTML = '<option value="">-- Pilih Data Benih --</option>';
            seedInfoContainer.style.display = 'none';
            
            if (!inventoryTypeId) {
                seedSelect.disabled = true;
                return;
            }
            
            loadSeedsForUpdate(inventoryTypeId);
        });
    }
    
    // Show seed info when seed is selected in update form
    const updateSeedSelect = document.getElementById('update_seed_id');
    if (updateSeedSelect) {
        updateSeedSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const seedInfoContainer = document.getElementById('update_seed_info_container');
            const seedInfoDisplay = document.getElementById('update_seed_info_display');
            
            if (this.value && selectedOption.dataset.quantity) {
                let info = `<div class="mb-1"><strong>Jumlah:</strong> ${parseFloat(selectedOption.dataset.quantity).toFixed(2)} ${selectedOption.dataset.unit || 'kg'}</div>`;
                
                if (selectedOption.dataset.bpsbNumber && selectedOption.dataset.bpsbNumber !== '-') {
                    info += `<div class="mb-1"><strong>Nomor Laporan BPSB:</strong> ${selectedOption.dataset.bpsbNumber}</div>`;
                }
                
                if (selectedOption.dataset.expiryDate) {
                    const expiryDate = new Date(selectedOption.dataset.expiryDate);
                    info += `<div class="mb-1"><strong>Tanggal Kadaluarsa:</strong> ${expiryDate.toLocaleDateString('id-ID')}</div>`;
                }
                
                seedInfoDisplay.innerHTML = info;
                seedInfoContainer.style.display = 'block';
                
                if (selectedOption.dataset.expiryDate) {
                    document.getElementById('update_expiry_date').value = selectedOption.dataset.expiryDate;
                }
            } else {
                seedInfoContainer.style.display = 'none';
            }
        });
    }
});

// Auto-open bin stocks modal if bin_id parameter exists in URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const binId = urlParams.get('bin_id');
    
    if (binId) {
        // Wait a bit for page to fully load, then open the modal
        setTimeout(function() {
            viewBinStocks(binId);
        }, 500);
    }
});

// Handle update stock form submission
document.addEventListener('DOMContentLoaded', function() {
    const updateStockForm = document.getElementById('updateStockForm');
    if (updateStockForm) {
        updateStockForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            
            // Disable submit button
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
            
            // Add _method for PUT request
            formData.append('_method', 'PUT');
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('updateStockModal'));
                    if (modal) {
                        modal.hide();
                    }
                    
                    // Show success message
                    alert(data.message || 'Data benih berhasil diupdate.');
                    
                    // Reload bin stocks
                    const binIdMatch = form.action.match(/bins\/(\d+)\//);
                    if (binIdMatch) {
                        viewBinStocks(binIdMatch[1]);
                    }
                } else {
                    // Show error message
                    alert(data.message || 'Terjadi kesalahan saat mengupdate data benih.');
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengupdate data benih.');
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        });
    }
});

// Handle reduce stock form submission
document.addEventListener('DOMContentLoaded', function() {
    const reduceStockForm = document.getElementById('reduceStockForm');
    if (reduceStockForm) {
        reduceStockForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            
            // Disable submit button
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('reduceStockModal'));
                    if (modal) {
                        modal.hide();
                    }
                    
                    // Show success message
                    alert(data.message || 'Stok berhasil dikurangi.');
                    
                    // Reload bin stocks
                    const binIdMatch = form.action.match(/bins\/(\d+)\//);
                    if (binIdMatch) {
                        viewBinStocks(binIdMatch[1]);
                    }
                } else {
                    // Show error message
                    alert(data.message || 'Terjadi kesalahan saat mengurangi stok.');
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan jaringan atau server.');
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        });
    }
});

function deleteLot(binId, lotId, productionId) {
    // Set form action
    const form = document.getElementById('deleteLotForm');
    form.action = '{{ route("warehouse-locations.bins.inventory-lots.destroy", ["warehouse" => $warehouse->warehouse_id, "bin" => ":bin", "lot" => ":lot"]) }}'
        .replace(':bin', binId)
        .replace(':lot', lotId);
    
    // Set production ID
    document.getElementById('delete_lot_production_id').value = productionId;
    document.getElementById('delete_reason').value = '';
    
    // Show modal
    new bootstrap.Modal(document.getElementById('deleteLotModal')).show();
}

function showLotHistory(warehouseId, binId, lotId, productionId, inventoryTypeName) {
    const contentDiv = document.getElementById('lotHistoryContent');
    contentDiv.innerHTML = '<p class="text-muted">Memuat riwayat...</p>';
    const modal = new bootstrap.Modal(document.getElementById('lotHistoryModal'));
    modal.show();

    const url = '{{ url("warehouse-locations") }}/' + warehouseId + '/bins/' + binId + '/inventory-lots/' + lotId + '/transactions';
    fetch(url, {
        method: 'GET',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            contentDiv.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Gagal memuat riwayat.') + '</div>';
            return;
        }
        let html = '<div class="mb-3"><strong>Lot:</strong> ' + (productionId || '-') + ' &nbsp;|&nbsp; <strong>Benih:</strong> ' + (data.lot.inventory_type_name || '-') + ' &nbsp;|&nbsp; <strong>Stok saat ini:</strong> ' + parseFloat(data.lot.current_stock || 0).toLocaleString('id-ID') + ' ' + (data.lot.stock_unit || 'kg') + '</div>';
        if (data.transactions && data.transactions.length > 0) {
            html += '<div class="table-responsive"><table class="table table-sm table-hover"><thead><tr><th>Tanggal</th><th>Jenis</th><th>Jumlah</th><th>Alasan / Catatan</th><th>User</th></tr></thead><tbody>';
            data.transactions.forEach(function(tx) {
                const qty = parseFloat(tx.quantity);
                const qtyStr = (qty >= 0 ? '+' : '') + qty.toLocaleString('id-ID') + ' ' + (tx.unit || 'kg');
                html += '<tr><td>' + (tx.date || '-') + '</td><td><span class="badge bg-secondary">' + (tx.type_label || tx.type) + '</span></td><td>' + qtyStr + '</td><td>' + (tx.reason || '-') + (tx.notes && tx.notes !== '-' ? '<br><small class="text-muted">' + tx.notes + '</small>' : '') + '</td><td>' + (tx.user || '-') + '</td></tr>';
            });
            html += '</tbody></table></div>';
        } else {
            html += '<p class="text-muted">Belum ada riwayat mutasi untuk lot ini.</p>';
        }
        contentDiv.innerHTML = html;
    })
    .catch(error => {
        contentDiv.innerHTML = '<div class="alert alert-danger">Error memuat riwayat: ' + error.message + '</div>';
    });
}

// Handle delete lot form submission
document.addEventListener('DOMContentLoaded', function() {
    const deleteLotForm = document.getElementById('deleteLotForm');
    if (deleteLotForm) {
        deleteLotForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            
            // Disable submit button
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteLotModal'));
                    if (modal) {
                        modal.hide();
                    }
                    
                    // Show success message
                    alert(data.message || 'Stok berhasil dihapus.');
                    
                    // Reload bin stocks (extract bin id from URL: .../bins/{id}/inventory-lots/...)
                    const binIdMatch = form.action.match(/\/bins\/([^/]+)\/inventory-lots\//);
                    if (binIdMatch) {
                        viewBinStocks(binIdMatch[1]);
                    }
                } else {
                    // Show error message
                    alert(data.message || 'Terjadi kesalahan saat menghapus stok.');
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus stok.');
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        });
    }
});
</script>
@endpush
@endsection

