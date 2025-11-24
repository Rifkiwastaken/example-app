@extends('layouts.app')

@section('title', 'Detail Gudang: ' . $warehouse->name . ' - SIBIT')

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
                    <a href="{{ route('warehouse-locations.edit', ['warehouse_location' => $warehouse->id]) }}" class="btn btn-sm btn-warning">
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
                                        onclick="openAddBibitModal({{ $bin->id }}, '{{ addslashes($bin->name) }}')" 
                                        title="Tambah Bibit">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info" 
                                        onclick="viewBinStocks({{ $bin->id }})" 
                                        title="Lihat Stok">
                                    <i class="fas fa-boxes"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                        onclick="editBin({{ $bin->id }}, '{{ $bin->name }}', '{{ $bin->internal_id }}', {{ $bin->max_capacity }}, '{{ $bin->capacity_unit }}', '{{ addslashes($bin->description ?? '') }}')" 
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('warehouse-locations.bins.destroy', ['warehouse' => $warehouse->id, 'bin' => $bin->id]) }}" 
                                      method="POST" class="d-inline" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus bin ini?')">
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
            <form action="{{ route('warehouse-locations.bins.store', ['warehouse' => $warehouse->id]) }}" method="POST">
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
                                    <option value="quantity">quantity</option>
                                    <option value="kg" selected>kg</option>
                                    <option value="ton">ton</option>
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
                                    <option value="quantity">quantity</option>
                                    <option value="kg">kg</option>
                                    <option value="ton">ton</option>
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

<!-- Modal: Tambah Bibit ke Bin -->
<div class="modal fade" id="addBibitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addBibitForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Bibit ke Bin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Bin:</strong> <span id="binNameDisplay"></span>
                    </div>
                    <div class="mb-3">
                        <label for="inventory_type_id" class="form-label">Pilih Bibit (dari Master Stok Bibit) <span class="text-danger">*</span></label>
                        <select class="form-select" id="inventory_type_id" name="inventory_type_id" required>
                            <option value="">-- Pilih Bibit --</option>
                            @foreach($inventoryTypes as $invType)
                                <option value="{{ $invType->id }}" data-unit="{{ $invType->unit }}">
                                    {{ $invType->name }} @if($invType->sku) ({{ $invType->sku }}) @endif - {{ $invType->category }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Data bibit diambil dari Master Stok Bibit</small>
                    </div>
                    <div class="mb-3">
                        <label for="initial_stock" class="form-label">Jumlah Stok <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" class="form-control" id="initial_stock" name="initial_stock" required>
                            <span class="input-group-text" id="stock_unit_display">-</span>
                        </div>
                        <small class="text-muted">Unit akan otomatis sesuai dengan unit bibit yang dipilih</small>
                    </div>
                    <div class="mb-3">
                        <label for="expiry_date" class="form-label">Tanggal Kadaluarsa</label>
                        <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                        <small class="text-muted">Opsional: Isi tanggal kadaluarsa bibit</small>
                    </div>
                    <div class="mb-3">
                        <label for="production_id" class="form-label">Nomor Batch/Produksi (Opsional)</label>
                        <input type="text" class="form-control" id="production_id" name="production_id" placeholder="Akan di-generate otomatis jika dikosongkan">
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

@push('scripts')
<script>
function editBin(id, name, internalId, maxCapacity, capacityUnit, description) {
    const form = document.getElementById('editBinForm');
    form.action = '{{ route("warehouse-locations.bins.update", ["warehouse" => $warehouse->id, "bin" => ":bin"]) }}'.replace(':bin', id);
    
    document.getElementById('edit_bin_name').value = name;
    document.getElementById('edit_bin_internal_id').value = internalId;
    document.getElementById('edit_bin_max_capacity').value = maxCapacity;
    document.getElementById('edit_bin_capacity_unit').value = capacityUnit;
    document.getElementById('edit_bin_description').value = description || '';
    
    new bootstrap.Modal(document.getElementById('editBinModal')).show();
}

function openAddBibitModal(binId, binName) {
    const form = document.getElementById('addBibitForm');
    form.action = '{{ route("warehouse-locations.bins.inventory-lots.store", ["warehouse" => $warehouse->id, "bin" => ":bin"]) }}'.replace(':bin', binId);
    
    document.getElementById('binNameDisplay').textContent = binName;
    document.getElementById('initial_stock').value = '';
    document.getElementById('expiry_date').value = '';
    document.getElementById('production_id').value = '';
    document.getElementById('inventory_type_id').value = '';
    document.getElementById('stock_unit_display').textContent = '-';
    
    new bootstrap.Modal(document.getElementById('addBibitModal')).show();
}

// Update unit display when inventory type changes
document.getElementById('inventory_type_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const unit = selectedOption.dataset.unit || '-';
    document.getElementById('stock_unit_display').textContent = unit;
});

function viewBinStocks(binId) {
    const contentDiv = document.getElementById('binStocksContent');
    contentDiv.innerHTML = '<p class="text-muted">Memuat data...</p>';
    
    const url = '{{ route("warehouse-locations.bins.stocks", ["warehouse" => $warehouse->id, "bin" => ":bin"]) }}'.replace(':bin', binId);
    
    fetch(url)
        .then(response => response.json())
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
                                    <th>Bibit</th>
                                    <th>Batch/Produksi</th>
                                    <th>Stok</th>
                                    <th>Kadaluarsa</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                data.lots.forEach(lot => {
                    html += `
                        <tr>
                            <td><strong>${lot.inventory_type_name}</strong></td>
                            <td><code>${lot.production_id}</code></td>
                            <td>${parseFloat(lot.current_stock).toLocaleString('id-ID')} ${lot.stock_unit}</td>
                            <td>${lot.expiry_date}</td>
                            <td><span class="badge bg-${lot.status_color}">${lot.status_label}</span></td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                html += '<p class="text-muted text-center py-3">Belum ada stok bibit di bin ini.</p>';
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
</script>
@endpush
@endsection

