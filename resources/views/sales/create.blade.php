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
        <form action="{{ route('sales.store') }}" method="POST" id="saleForm">
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
                <div class="col-md-6">
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
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Dicatat Oleh</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->name }}" readonly>
                    </div>
                </div>
            </div>

            <!-- Bagian B: Rincian Item -->
            <h5 class="mb-3 mt-4">Bagian B: Rincian Item (Benih yang Dibeli)</h5>
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

            <div class="mb-4">
                <label for="notes" class="form-label">Keterangan (Opsional)</label>
                <textarea class="form-control @error('notes') is-invalid @enderror" 
                          id="notes" name="notes" rows="2" 
                          placeholder="Contoh: Transfer via Bank Nagari, an. Bapak Heru.">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
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
        'id' => $type->id,
        'name' => $type->name,
        'sku' => $type->sku,
        'unit' => $type->unit
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
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Pilih Benih (Tipe Inventaris) <span class="text-danger">*</span></label>
                <select class="form-select inventory-type-select" name="items[${itemRowCount}][inventory_type_id]" 
                        onchange="loadLots(${itemRowCount}, this.value)" required>
                    <option value="">Pilih Benih</option>
                    ${inventoryTypes.map(type => 
                        `<option value="${type.id}">${type.name}</option>`
                    ).join('')}
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Pilih Lot/Batch</label>
                <select class="form-select lot-select" name="items[${itemRowCount}][inventory_lot_id]" 
                        id="lot-select-${itemRowCount}" onchange="updateLotInfo(${itemRowCount})">
                    <option value="">Pilih Lot</option>
                </select>
                <small class="text-muted lot-info" id="lot-info-${itemRowCount}"></small>
            </div>
            <div class="col-md-2">
                <label class="form-label">Jumlah Jual <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" step="0.01" class="form-control quantity-input" 
                           name="items[${itemRowCount}][quantity]" 
                           id="quantity-${itemRowCount}" 
                           onchange="calculateSubtotal(${itemRowCount})" required>
                    <span class="input-group-text unit-display" id="unit-${itemRowCount}">-</span>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control unit-price-input" 
                       name="items[${itemRowCount}][unit_price]" 
                       id="unit-price-${itemRowCount}" 
                       onchange="calculateSubtotal(${itemRowCount})" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Subtotal (Rp)</label>
                <input type="text" class="form-control subtotal-display" 
                       id="subtotal-${itemRowCount}" value="0" readonly>
                <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeItemRow(${itemRowCount})">
                    <i class="fas fa-trash"></i> Hapus
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

function loadLots(rowId, inventoryTypeId) {
    const lotSelect = document.getElementById(`lot-select-${rowId}`);
    const unitDisplay = document.getElementById(`unit-${rowId}`);
    
    // Update unit display
    const inventoryType = inventoryTypes.find(t => t.id == inventoryTypeId);
    if (inventoryType) {
        unitDisplay.textContent = inventoryType.unit;
    }
    
    // Load lots
    if (!inventoryTypeId) {
        lotSelect.innerHTML = '<option value="">Pilih Lot</option>';
        return;
    }
    
    fetch(`{{ route('sales.get-lots') }}?inventory_type_id=${inventoryTypeId}`)
        .then(response => response.json())
        .then(lots => {
            lotSelect.innerHTML = '<option value="">Pilih Lot</option>';
            lots.forEach(lot => {
                const option = document.createElement('option');
                option.value = lot.id;
                option.textContent = `${lot.production_id} (Sisa: ${lot.current_stock} ${lot.stock_unit}, Edar: ${lot.expiry_date})`;
                option.dataset.currentStock = lot.current_stock;
                option.dataset.expiryDate = lot.expiry_date;
                option.dataset.unit = lot.stock_unit;
                lotSelect.appendChild(option);
            });
        });
}

function updateLotInfo(rowId) {
    const lotSelect = document.getElementById(`lot-select-${rowId}`);
    const lotInfo = document.getElementById(`lot-info-${rowId}`);
    const selectedOption = lotSelect.options[lotSelect.selectedIndex];
    
    if (selectedOption.value && selectedOption.dataset.currentStock) {
        lotInfo.textContent = `Stok tersedia: ${selectedOption.dataset.currentStock} ${selectedOption.dataset.unit || 'kg'}`;
        lotInfo.className = 'text-muted small';
    } else {
        lotInfo.textContent = '';
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

