@extends('layouts.app')

@section('title', 'Form Pencatatan Penjualan Baru - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Form Pencatatan Penjualan Baru</h4>
    <a href="{{ route('sales.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('sales.store') }}" method="POST" id="saleForm" enctype="multipart/form-data">
            @csrf

            <!-- Bagian A: Informasi Transaksi -->
            <h5 class="mb-3">Bagian A: Informasi Transaksi</h5>
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="receipt_number" class="form-label">Nomor Struk/Referensi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('receipt_number') is-invalid @enderror" 
                               id="receipt_number" name="receipt_number" value="{{ old('receipt_number', $receiptNumber) }}" required>
                        @error('receipt_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="sale_date" class="form-label">Tanggal Penjualan <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('sale_date') is-invalid @enderror" 
                               id="sale_date" name="sale_date" value="{{ old('sale_date', date('Y-m-d')) }}" required>
                        @error('sale_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="buyer_name" class="form-label">Nama Pembeli <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('buyer_name') is-invalid @enderror" 
                               id="buyer_name" name="buyer_name" value="{{ old('buyer_name') }}" 
                               placeholder="Contoh: Bapak Heru" required>
                        @error('buyer_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="buyer_contact" class="form-label">Kontak Pembeli (Opsional)</label>
                        <input type="text" class="form-control @error('buyer_contact') is-invalid @enderror" 
                               id="buyer_contact" name="buyer_contact" value="{{ old('buyer_contact') }}" 
                               placeholder="Contoh: 08123456789">
                        @error('buyer_contact')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="buyer_nik" class="form-label">NIK Pembeli</label>
                        <input type="text" class="form-control @error('buyer_nik') is-invalid @enderror" 
                               id="buyer_nik" name="buyer_nik" value="{{ old('buyer_nik') }}" 
                               placeholder="Contoh: 1234567890123456">
                        @error('buyer_nik')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Dicatat Oleh</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->name }}" readonly>
                    </div>
                </div>
            </div>

            <!-- Bagian B: Data Lokasi Sebaran -->
            <h5 class="mb-3 mt-4">Bagian B: Data Lokasi Sebaran</h5>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="destination_province" class="form-label">Provinsi</label>
                        <input type="text" class="form-control @error('destination_province') is-invalid @enderror" 
                               id="destination_province" name="destination_province" value="{{ old('destination_province') }}" 
                               placeholder="Contoh: Sumatera Barat">
                        @error('destination_province')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="destination_city" class="form-label">Kabupaten/Kota</label>
                        <input type="text" class="form-control @error('destination_city') is-invalid @enderror" 
                               id="destination_city" name="destination_city" value="{{ old('destination_city') }}" 
                               placeholder="Contoh: Padang">
                        @error('destination_city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="destination_district" class="form-label">Kecamatan</label>
                        <input type="text" class="form-control @error('destination_district') is-invalid @enderror" 
                               id="destination_district" name="destination_district" value="{{ old('destination_district') }}" 
                               placeholder="Contoh: Koto Tangah">
                        @error('destination_district')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="destination_village" class="form-label">Desa/Kelurahan</label>
                        <input type="text" class="form-control @error('destination_village') is-invalid @enderror" 
                               id="destination_village" name="destination_village" value="{{ old('destination_village') }}" 
                               placeholder="Contoh: Lubuk Buaya">
                        @error('destination_village')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="planned_location_name" class="form-label">Rencana Lokasi Lahan</label>
                        <input type="text" class="form-control @error('planned_location_name') is-invalid @enderror" 
                               id="planned_location_name" name="planned_location_name" value="{{ old('planned_location_name') }}" 
                               placeholder="Contoh: Blok A, Kampung Sawah">
                        <small class="text-muted">Nama blok atau kampung spesifik jika tersedia</small>
                        @error('planned_location_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="estimated_planting_area" class="form-label">Estimasi Luas Tanam (Hektar)</label>
                        <input type="number" step="0.01" class="form-control @error('estimated_planting_area') is-invalid @enderror" 
                               id="estimated_planting_area" name="estimated_planting_area" value="{{ old('estimated_planting_area') }}" 
                               placeholder="Contoh: 2.5">
                        <small class="text-muted">Luas lahan yang direncanakan untuk ditanami</small>
                        @error('estimated_planting_area')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Bagian C: Rincian Item -->
            <h5 class="mb-3 mt-4">Bagian C: Rincian Item (Benih yang Dibeli)</h5>
            <div id="items-container">
                <!-- Items will be added here dynamically -->
            </div>
            <button type="button" class="btn btn-success btn-sm mb-4" onclick="addItemRow()">
                <i class="fas fa-plus me-2"></i>Tambah Item Benih
            </button>

            @error('items')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror

            <!-- Bagian C: Pembayaran -->
            <h5 class="mb-3 mt-4">Bagian C: Pembayaran</h5>
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Total Belanja (Rp)</label>
                        <input type="text" class="form-control" id="total_amount_display" value="Rp 0" readonly>
                        <input type="hidden" name="total_amount" id="total_amount" value="0">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select class="form-select @error('payment_method') is-invalid @enderror" 
                                id="payment_method" name="payment_method" required>
                            <option value="">Pilih Metode</option>
                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="transfer_bank" {{ old('payment_method') == 'transfer_bank' ? 'selected' : '' }}>Transfer Bank</option>
                        </select>
                        @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="payment_status" class="form-label">Status Pembayaran <span class="text-danger">*</span></label>
                        <select class="form-select @error('payment_status') is-invalid @enderror" 
                                id="payment_status" name="payment_status" required>
                            <option value="lunas" {{ old('payment_status', 'lunas') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            <option value="belum_lunas" {{ old('payment_status') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                        </select>
                        @error('payment_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Keterangan (Opsional)</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="2" 
                                  placeholder="Contoh: Transfer via Bank Nagari, an. Bapak Heru.">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="payment_proof" class="form-label">Lampiran Bukti Pembayaran (Opsional)</label>
                        <input type="file" class="form-control @error('payment_proof') is-invalid @enderror" 
                               id="payment_proof" name="payment_proof" accept="image/*,application/pdf">
                        <small class="text-muted">Format: JPG, PNG, PDF (Max: 2MB)</small>
                        @error('payment_proof')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Simpan Transaksi
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
@php
$inventoryTypesArray = $inventoryTypes->map(function($type) {
    return [
        'id' => $type->inventory_type_id,
        'name' => $type->name,
        'sku' => $type->sku,
        'unit' => $type->unit,
        'estimated_value_per_unit' => $type->estimated_value_per_unit !== null ? (float) $type->estimated_value_per_unit : null
    ];
})->values()->toArray();
@endphp
const inventoryTypes = @json($inventoryTypesArray);

let itemRowCount = 0;

function addItemRow() {
    itemRowCount++;
    const container = document.getElementById('items-container');
    const row = document.createElement('div');
    row.className = 'item-row mb-3 p-3 border rounded';
    row.id = `item-row-${itemRowCount}`;
    
    row.innerHTML = `
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Pilih Benih (Tipe Inventaris) <span class="text-danger">*</span></label>
                <select class="form-select inventory-type-select" name="items[${itemRowCount}][inventory_type_id]" 
                        id="inventory-type-select-${itemRowCount}"
                        onchange="loadWarehousesAndLots(${itemRowCount}, this.value)" required>
                    <option value="">Pilih Benih</option>
                    ${inventoryTypes.map(type => 
                        `<option value="${type.id}">${type.name}</option>`
                    ).join('')}
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Pilih Lokasi Gudang <span class="text-danger">*</span></label>
                <select class="form-select warehouse-select" name="items[${itemRowCount}][warehouse_id]" 
                        id="warehouse-select-${itemRowCount}"
                        onchange="loadBins(${itemRowCount}, this.value)" required disabled>
                    <option value="">Pilih tipe benih terlebih dahulu</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Pilih Bin <span class="text-danger">*</span></label>
                <select class="form-select bin-select" name="items[${itemRowCount}][bin_id]" 
                        id="bin-select-${itemRowCount}" 
                        onchange="loadBinLots(${itemRowCount}, this.value)" required disabled>
                    <option value="">Pilih gudang terlebih dahulu</option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Satuan Jual (Opsional)</label>
                <select class="form-select" 
                        name="items[${itemRowCount}][package_unit_type]" 
                        id="package-unit-type-${itemRowCount}"
                        onchange="toggleCustomPackageUnit(${itemRowCount})">
                    <option value="">Pilih Satuan</option>
                    <option value="satuan">Satuan (per kg/pcs)</option>
                    <option value="kantong">Kantong</option>
                    <option value="ikat">Ikat</option>
                    <option value="gentong">Gentong</option>
                    <option value="custom">Isi Sendiri</option>
                </select>
                <small class="text-muted">Pilih satuan kemasan</small>
            </div>
            <div class="col-md-4" id="custom-unit-container-${itemRowCount}" style="display: none;">
                <label class="form-label">Nama Satuan Custom</label>
                <input type="text" class="form-control" 
                       name="items[${itemRowCount}][package_unit_custom]" 
                       id="package-unit-custom-${itemRowCount}"
                       placeholder="Contoh: karung, dus, dll"
                       onchange="togglePackageFields(${itemRowCount})">
                <small class="text-muted">Ketik nama satuan</small>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nilai Satuan Jual (Opsional)</label>
                <input type="number" step="0.01" class="form-control" 
                       name="items[${itemRowCount}][package_value]" 
                       id="package-value-${itemRowCount}" 
                       placeholder="Contoh: 25"
                       onchange="calculateQuantityFromPackage(${itemRowCount})"
                       disabled>
                <small class="text-muted">Nilai per satuan dalam kg</small>
            </div>
            <div class="col-md-4">
                <label class="form-label">Jumlah Satuan Jual (Opsional)</label>
                <input type="number" step="0.01" class="form-control" 
                       name="items[${itemRowCount}][package_quantity]" 
                       id="package-quantity-${itemRowCount}" 
                       placeholder="Contoh: 5"
                       onchange="calculateQuantityFromPackage(${itemRowCount})"
                       disabled>
                <small class="text-muted">Jumlah kemasan yang dibeli</small>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Total Jual <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" step="0.01" class="form-control quantity-input" 
                           name="items[${itemRowCount}][quantity]" 
                           id="quantity-${itemRowCount}" 
                           readonly required>
                    <span class="input-group-text unit-display" id="unit-${itemRowCount}">-</span>
                </div>
                <small class="text-muted stock-info" id="stock-info-${itemRowCount}"></small>
            </div>
            <div class="col-md-3">
                <label class="form-label">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control unit-price-input" 
                       name="items[${itemRowCount}][unit_price]" 
                       id="unit-price-${itemRowCount}" 
                       readonly required>
                <small class="text-muted">Otomatis dari data stok</small>
            </div>
            <div class="col-md-3">
                <label class="form-label">Subtotal (Rp)</label>
                <input type="text" class="form-control subtotal-display" 
                       id="subtotal-${itemRowCount}" value="0" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-danger w-100" onclick="removeItemRow(${itemRowCount})">
                    <i class="fas fa-trash me-2"></i>Hapus Item
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(row);
}

function removeItemRow(rowId) {
    const row = document.getElementById(`item-row-${rowId}`);
    if (row) {
        row.remove();
        calculateTotal();
    }
}

function loadWarehousesAndLots(rowId, inventoryTypeId) {
    const warehouseSelect = document.getElementById(`warehouse-select-${rowId}`);
    const binSelect = document.getElementById(`bin-select-${rowId}`);
    const unitDisplay = document.getElementById(`unit-${rowId}`);
    
    // Update unit display
    const inventoryType = inventoryTypes.find(t => t.id == inventoryTypeId);
    if (inventoryType) {
        unitDisplay.textContent = inventoryType.unit;
    }
    
    // Reset selects
    warehouseSelect.innerHTML = '<option value="">Memuat...</option>';
    warehouseSelect.disabled = true;
    binSelect.innerHTML = '<option value="">Pilih gudang terlebih dahulu</option>';
    binSelect.disabled = true;
    
    if (!inventoryTypeId) {
        warehouseSelect.innerHTML = '<option value="">Pilih tipe benih terlebih dahulu</option>';
        return;
    }
    
    // Load warehouses and lots
    fetch(`/api/inventory-types/${encodeURIComponent(inventoryTypeId)}/details`)
        .then(response => {
            if (!response.ok) return response.json().then(data => Promise.reject(new Error(data.message || 'Gagal memuat data')));
            return response.json();
        })
        .then(data => {
            if (data.success && data.first_lot) {
                // Populate warehouse dropdown (all warehouses or just first)
                const warehouses = data.warehouses && data.warehouses.length > 0
                    ? data.warehouses
                    : [{ warehouse_id: data.first_lot.warehouse_id, warehouse_name: data.first_lot.warehouse_name || 'Gudang' }];
                
                warehouseSelect.innerHTML = '<option value="">-- Pilih Gudang --</option>' + 
                    warehouses.map(w => 
                        `<option value="${w.warehouse_id || ''}">${(w.warehouse_name || 'Gudang').trim() || 'Gudang'}</option>`
                    ).join('');
                warehouseSelect.disabled = false;

                // Harga Satuan = nilai per unit dari data stok benih (tipe inventaris)
                const unitPriceInput = document.getElementById(`unit-price-${rowId}`);
                const val = data.inventory_type && data.inventory_type.estimated_value_per_unit != null
                    ? parseFloat(data.inventory_type.estimated_value_per_unit)
                    : null;
                if (unitPriceInput && !isNaN(val)) {
                    unitPriceInput.value = val;
                }
                if (typeof calculateSubtotal === 'function') {
                    calculateSubtotal(rowId);
                } else if (typeof calculateTotal === 'function') {
                    calculateTotal();
                }

                // Auto-select first warehouse and load its bins
                const firstWarehouseId = data.first_lot.warehouse_id;
                if (firstWarehouseId) {
                    warehouseSelect.value = firstWarehouseId;
                    loadBins(rowId, firstWarehouseId, data.first_lot.bin_id);
                }
            } else {
                warehouseSelect.innerHTML = '<option value="">Tidak ada stok tersedia</option>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            warehouseSelect.innerHTML = '<option value="">Error memuat data</option>';
        });
}

function loadBins(rowId, warehouseId, preselectedBinId = null) {
    const binSelect = document.getElementById(`bin-select-${rowId}`);
    
    binSelect.innerHTML = '<option value="">Memuat...</option>';
    binSelect.disabled = true;
    
    if (!warehouseId) {
        binSelect.innerHTML = '<option value="">Pilih gudang terlebih dahulu</option>';
        return;
    }
    
    fetch(`{{ route('sales.get-bins') }}?warehouse_id=${warehouseId}`)
        .then(response => response.json())
        .then(bins => {
            binSelect.innerHTML = '<option value="">Pilih Bin</option>';
            bins.forEach(bin => {
                const option = document.createElement('option');
                option.value = bin.id;
                option.textContent = `${bin.name}${bin.internal_id ? ' (' + bin.internal_id + ')' : ''}`;
                if (preselectedBinId && bin.id == preselectedBinId) {
                    option.selected = true;
                }
                binSelect.appendChild(option);
            });
            binSelect.disabled = false;
            
            if (preselectedBinId) {
                loadBinLots(rowId, preselectedBinId);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            binSelect.innerHTML = '<option value="">Error memuat data</option>';
        });
}

function loadBinLots(rowId, binId) {
    const stockInfo = document.getElementById(`stock-info-${rowId}`);
    const unitPriceInput = document.getElementById(`unit-price-${rowId}`);
    const unitDisplay = document.getElementById(`unit-${rowId}`);
    
    if (!binId) {
        stockInfo.textContent = '';
        return;
    }
    
    stockInfo.textContent = 'Memuat informasi stok...';
    stockInfo.className = 'text-muted small';
    
    // Fetch lots in this bin for FIFO calculation
    fetch(`{{ route('sales.get-bin-lots') }}?bin_id=${binId}`)
        .then(response => response.json())
        .then(lots => {
            if (lots.length > 0) {
                // Filter out expired lots
                const validLots = lots.filter(lot => lot.status !== 'kadaluarsa');
                
                if (validLots.length === 0) {
                    stockInfo.textContent = 'Bin ini hanya memiliki stok benih yang sudah kadaluarsa.';
                    stockInfo.className = 'text-danger small';
                    unitDisplay.textContent = '-';
                    return;
                }
                
                // Calculate total available stock from valid lots (FIFO)
                const totalStock = validLots.reduce((sum, lot) => sum + parseFloat(lot.current_stock), 0);
                const unit = validLots[0].stock_unit || 'kg';
                
                stockInfo.textContent = `Stok tersedia: ${totalStock.toFixed(2)} ${unit} (FIFO: ${validLots.length} lot)`;
                stockInfo.className = 'text-muted small';
                unitDisplay.textContent = unit;

                // Harga satuan sudah diisi dari data stok benih saat pilih tipe inventaris; jika belum terisi, pakai nilai per unit dari data tipe
                const inventoryTypeSelect = document.getElementById(`inventory-type-select-${rowId}`);
                const inventoryTypeId = inventoryTypeSelect ? inventoryTypeSelect.value : null;
                const inventoryType = inventoryTypeId ? inventoryTypes.find(t => t.id == inventoryTypeId) : null;
                if (unitPriceInput && inventoryType && inventoryType.estimated_value_per_unit != null && (unitPriceInput.value === '' || unitPriceInput.value == null)) {
                    unitPriceInput.value = parseFloat(inventoryType.estimated_value_per_unit);
                }
                if (typeof calculateSubtotal === 'function') {
                    calculateSubtotal(rowId);
                } else if (typeof calculateTotal === 'function') {
                    calculateTotal();
                }

                // Store lots data for FIFO processing during submission
                document.getElementById(`bin-select-${rowId}`).dataset.lots = JSON.stringify(validLots);
            } else {
                stockInfo.textContent = 'Tidak ada stok tersedia di bin ini.';
                stockInfo.className = 'text-danger small';
                unitDisplay.textContent = '-';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            stockInfo.textContent = 'Error memuat informasi stok';
            stockInfo.className = 'text-danger small';
        });
}

function toggleCustomPackageUnit(rowId) {
    const packageUnitTypeSelect = document.getElementById(`package-unit-type-${rowId}`);
    const customUnitContainer = document.getElementById(`custom-unit-container-${rowId}`);
    const packageValueInput = document.getElementById(`package-value-${rowId}`);
    const packageQuantityInput = document.getElementById(`package-quantity-${rowId}`);
    
    const selectedType = packageUnitTypeSelect.value;
    
    if (selectedType === 'custom') {
        // Show custom input field
        customUnitContainer.style.display = 'block';
        packageValueInput.disabled = false;
        packageQuantityInput.disabled = false;
    } else if (selectedType) {
        // Hide custom input field
        customUnitContainer.style.display = 'none';
        packageValueInput.disabled = false;
        packageQuantityInput.disabled = false;
        
        // If "satuan" is selected, set default values
        if (selectedType === 'satuan') {
            packageValueInput.value = 1;
            packageValueInput.disabled = true;
        }
        
        togglePackageFields(rowId);
    } else {
        // No selection
        customUnitContainer.style.display = 'none';
        packageValueInput.disabled = true;
        packageQuantityInput.disabled = true;
        packageValueInput.value = '';
        packageQuantityInput.value = '';
    }
}

function togglePackageFields(rowId) {
    const packageUnitTypeSelect = document.getElementById(`package-unit-type-${rowId}`);
    const packageValueInput = document.getElementById(`package-value-${rowId}`);
    const packageQuantityInput = document.getElementById(`package-quantity-${rowId}`);
    const quantityInput = document.getElementById(`quantity-${rowId}`);
    
    const selectedType = packageUnitTypeSelect.value;
    
    if (selectedType) {
        // Enable fields if satuan jual is selected
        packageValueInput.disabled = false;
        packageQuantityInput.disabled = false;
        
        // If "satuan" is selected, set default values
        if (selectedType === 'satuan') {
            packageValueInput.value = 1;
            packageValueInput.disabled = true;
        }
    } else {
        // Disable and clear fields if satuan jual is empty
        packageValueInput.disabled = true;
        packageQuantityInput.disabled = true;
        packageValueInput.value = '';
        packageQuantityInput.value = '';
        quantityInput.value = '';
    }
}

function calculateQuantityFromPackage(rowId) {
    const packageQuantityInput = document.getElementById(`package-quantity-${rowId}`);
    const packageValueInput = document.getElementById(`package-value-${rowId}`);
    const packageUnitTypeSelect = document.getElementById(`package-unit-type-${rowId}`);
    const quantityInput = document.getElementById(`quantity-${rowId}`);
    
    const packageQuantity = parseFloat(packageQuantityInput.value) || 0;
    const packageValue = parseFloat(packageValueInput.value) || 0;
    const packageUnitType = packageUnitTypeSelect.value;
    
    // Only calculate if all required fields are provided
    if (packageQuantity > 0 && packageValue > 0 && packageUnitType) {
        // Calculate total quantity: package_quantity × package_value
        const totalQuantity = packageQuantity * packageValue;
        quantityInput.value = totalQuantity.toFixed(2);
        
        // Trigger subtotal calculation
        calculateSubtotal(rowId);
    } else if (packageUnitType && (packageQuantity === 0 || packageValue === 0)) {
        // Clear total if any field is empty
        quantityInput.value = '';
    }
}

function calculateSubtotal(rowId) {
    const quantity = parseFloat(document.getElementById(`quantity-${rowId}`).value) || 0;
    const unitPrice = parseFloat(document.getElementById(`unit-price-${rowId}`).value) || 0;
    const subtotal = quantity * unitPrice;
    
    document.getElementById(`subtotal-${rowId}`).value = subtotal.toLocaleString('id-ID');
    calculateTotal();
}

function calculateTotal() {
    let total = 0;
    document.querySelectorAll('.subtotal-display').forEach(subtotal => {
        const value = parseFloat(subtotal.value.replace(/[^\d]/g, '')) || 0;
        total += value;
    });
    
    document.getElementById('total_amount_display').value = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('total_amount').value = total;
}

// Add first row on page load
document.addEventListener('DOMContentLoaded', function() {
    addItemRow();
});
</script>
@endpush
@endsection

