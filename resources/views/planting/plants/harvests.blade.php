@extends('layouts.app')

@section('title', 'Riwayat Panen - ' . $plant->name . ' - SIBESTI')

@section('content')
<!-- Plant Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="{{ route('plants.index') }}" class="btn btn-secondary me-3">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 18px; font-weight: bold;">
            {{ substr($plant->name, 0, 2) }}
        </div>
        <div>
            <h4 class="mb-0">{{ $plant->name }}</h4>
            <small class="text-muted">{{ $plant->variety ?: 'Tidak ada varietas' }}</small>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('plants.show', $plant) }}">
            <i class="fas fa-info-circle me-1"></i>Detail
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('plants.current-plantings', $plant) }}">
            <i class="fas fa-seedling me-1"></i>Penanaman saat ini
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('plants.harvests.index', $plant) }}">
            <i class="fas fa-cut me-1"></i>Panen
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('plants.notes.index', $plant) }}">
            <i class="fas fa-sticky-note me-1"></i>Catatan
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('plants.photos.index', $plant) }}">
            <i class="fas fa-camera me-1"></i>Foto
        </a>
    </li>
</ul>

<div class="tab-content p-3 bg-white border border-top-0 rounded-bottom">
    <div class="tab-pane fade show active">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Tabel Riwayat Panen (Varietas: {{ $plant->name }})</h5>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <form method="GET" action="{{ route('plants.harvests.index', $plant) }}" class="d-flex gap-2">
                            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Semua Tahun</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="planting_location_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Semua Lokasi</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ request('planting_location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal Panen</th>
                                <th>Jumlah Panen</th>
                                <th>Dipanen Dari (Lahan)</th>
                                <th>Lokasi Tanam (Bed/Baris)</th>
                                <th>Nomor Batch Tanam</th>
                                <th>Nomor Batch Panen</th>
                                <th>Kehilangan (Est.)</th>
                                <th>Sertifikasi</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($harvests as $harvest)
                            <tr>
                                <td>{{ $harvest->harvested_at->format('d M Y') }}</td>
                                <td><strong>{{ number_format($harvest->quantity, 2) }} {{ $harvest->unit }}</strong></td>
                                <td>{{ $harvest->location->name ?? '-' }}</td>
                                <td>{{ $harvest->planting->bed_label ?? '-' }}</td>
                                <td><span class="badge bg-primary">{{ $harvest->planting->planting_batch_number ?? '-' }}</span></td>
                                <td><span class="badge bg-info">{{ $harvest->batch_no }}</span></td>
                                <td>{{ $harvest->loss_quantity ? number_format($harvest->loss_quantity, 2) . ' ' . $harvest->unit : '-' }}</td>
                                <td>
                                    @if($harvest->certification)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Telah Disertifikasi
                                        </span>
                                    @else
                                        @if(auth()->user()->role !== 'penangkar')
                                            <a href="{{ route('certifications.create', ['harvest_id' => $harvest->harvest_id, 'plant_id' => $plant->plant_id]) }}" class="btn btn-sm btn-success" title="Lanjutkan ke Sertifikasi">
                                                <i class="fas fa-certificate me-1"></i>Lanjutkan ke Sertifikasi
                                            </a>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-lock me-1"></i>Belum Disertifikasi
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#harvestDetailModal"
                                                onclick="loadHarvestDetail('{{ $harvest->harvest_id }}')"
                                                title="Detail Panen">
                                        <i class="fas fa-eye me-1"></i>Detail Panen
                                        </button>
                                        @if(auth()->user()->role !== 'penangkar')
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteHarvest('{{ $harvest->harvest_id }}')"
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-cut fa-3x mb-3"></i>
                                        <p>Belum ada riwayat panen.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($harvests->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $harvests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>

<!-- Modal: Detail Panen -->
<div class="modal fade" id="harvestDetailModal" tabindex="-1" aria-labelledby="harvestDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="harvestDetailModalLabel">
                    <i class="fas fa-info-circle me-2"></i>Detail Panen
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="harvestDetailContent" style="max-height: 70vh; overflow-y: auto;">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
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
// Load harvest detail
function loadHarvestDetail(harvestId) {
    if (!harvestId || String(harvestId).trim() === '') {
        document.getElementById('harvestDetailContent').innerHTML = '<div class="alert alert-warning">Data panen tidak tersedia.</div>';
        return;
    }
    // Show loading
    document.getElementById('harvestDetailContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    fetch(`{{ url('harvests') }}/${encodeURIComponent(harvestId)}/detail`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
        .then(async response => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const msg = data.message || data.error || 'Network response was not ok';
                throw new Error(msg);
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                const harvest = data.harvest;
                const planting = data.planting;
                const tasks = data.tasks || [];
                const treatments = data.treatments || [];
                const nutrients = data.nutrients || [];
                const notes = data.notes || [];
                const expenses = data.expenses || [];
                
                let content = '';
                
                // Section 1: Informasi Penanaman (data dari Form Tanam Baru)
                content += `
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-seedling me-2"></i>Informasi Penanaman
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2"><strong>Nama Tanaman:</strong> ${planting.plant_name || '-'}</div>
                                <div class="mb-2"><strong>Varietas:</strong> ${planting.variety || '-'}</div>
                                <div class="mb-2"><strong>Nomor Batch Tanam:</strong> ${planting.planting_batch_number || '-'}</div>
                                <div class="mb-2"><strong>Lokasi Tanam:</strong> ${planting.bed_label || '-'}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2"><strong>Jumlah Tanam:</strong> ${planting.quantity_planted ? number_format(planting.quantity_planted, 0) + ' tanaman' : '-'}</div>
                                <div class="mb-2"><strong>Tanggal Tanam:</strong> ${planting.planted_at || '-'}</div>
                                <div class="mb-2"><strong>Estimasi Panen:</strong> ${planting.estimated_harvest_date || '-'}</div>
                            </div>
                        </div>
                        ${planting.notes ? `<div class="mt-2"><strong>Catatan Penanaman:</strong> ${planting.notes}</div>` : ''}
                    </div>
                `;

                // Section 2: Informasi Panen
                content += `
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-clipboard-check me-2"></i>Informasi Panen
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2"><strong>Nomor Batch Panen:</strong> <span class="badge bg-info">${harvest.batch_no || '-'}</span></div>
                                <div class="mb-2"><strong>Tanggal Panen:</strong> ${harvest.harvested_at_formatted || '-'}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2"><strong>Jumlah Panen:</strong> <strong>${harvest.quantity_formatted || '0.00'} ${harvest.unit || 'kg'}</strong></div>
                                <div class="mb-2"><strong>Kualitas Panen:</strong> ${harvest.quality || '-'}</div>
                            </div>
                        </div>
                        ${harvest.note ? `<div class="mt-2"><strong>Catatan Panen:</strong> ${harvest.note}</div>` : ''}
                    </div>
                `;
                
                // Section 3: Riwayat Laporan
                content += `
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-clipboard-list me-2"></i>Riwayat Laporan
                        </h6>
                `;
                if (tasks.length > 0) {
                    content += `
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Judul Laporan</th>
                                        <th>Status</th>
                                        <th>Prioritas</th>
                                        <th>Ditugaskan</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    tasks.forEach(task => {
                        const statusClass = task.new_status === 'selesai' ? 'success' : (task.new_status === 'dalam_progress' ? 'info' : 'danger');
                        const statusLabel = task.new_status === 'selesai' ? 'Selesai' : (task.new_status === 'dalam_progress' ? 'Dalam Progress' : 'Tidak Selesai');
                        const priorityClass = task.new_priority === 'tertinggi' || task.new_priority === 'tinggi' ? 'danger' : (task.new_priority === 'medium' ? 'warning' : 'secondary');
                        content += `
                            <tr>
                                <td>${task.due_date || '-'}</td>
                                <td><strong>${task.title}</strong></td>
                                <td><span class="badge bg-${statusClass}">${statusLabel}</span></td>
                                <td><span class="badge bg-${priorityClass}">${task.new_priority ? task.new_priority.charAt(0).toUpperCase() + task.new_priority.slice(1) : 'Medium'}</span></td>
                                <td>${task.assigned_user_name || '-'}</td>
                            </tr>
                        `;
                    });
                    content += `
                                </tbody>
                            </table>
                        </div>
                    `;
                } else {
                    content += '<p class="text-muted mb-0">Belum ada riwayat laporan untuk penanaman ini.</p>';
                }
                content += '</div>';
                
                // Section 3: Riwayat Perawatan
                content += `
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-first-aid me-2"></i>Riwayat Perawatan
                        </h6>
                `;
                if (treatments.length > 0) {
                    content += `
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Nama Perawatan</th>
                                        <th>Tipe</th>
                                        <th>Produk</th>
                                        <th>Metode</th>
                                        <th>Biaya</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    treatments.forEach(treatment => {
                        content += `
                            <tr>
                                <td>${treatment.treatment_date || '-'}</td>
                                <td><strong>${treatment.treatment_name}</strong></td>
                                <td>${treatment.treatment_type}</td>
                                <td>${treatment.product_detail || '-'}</td>
                                <td>${treatment.application_method || '-'}</td>
                                <td>${treatment.total_cost ? 'Rp ' + number_format(treatment.total_cost, 0) : '-'}</td>
                            </tr>
                        `;
                    });
                    content += `
                                </tbody>
                            </table>
                        </div>
                    `;
                } else {
                    content += '<p class="text-muted mb-0">Belum ada riwayat perawatan untuk penanaman ini.</p>';
                }
                content += '</div>';
                
                // Section 4: Riwayat Nutrisi
                content += `
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-flask me-2"></i>Riwayat Nutrisi
                        </h6>
                `;
                if (nutrients.length > 0) {
                    content += `
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Produk</th>
                                        <th>Jumlah</th>
                                        <th>Metode</th>
                                        <th>Biaya</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    nutrients.forEach(nutrient => {
                        content += `
                            <tr>
                                <td>${nutrient.application_date || '-'}</td>
                                <td><strong>${nutrient.product_applied}</strong></td>
                                <td>${number_format(nutrient.amount_applied, 2)} ${nutrient.unit}</td>
                                <td>${nutrient.application_method || '-'}</td>
                                <td>${nutrient.total_cost ? 'Rp ' + number_format(nutrient.total_cost, 0) : '-'}</td>
                            </tr>
                        `;
                    });
                    content += `
                                </tbody>
                            </table>
                        </div>
                    `;
                } else {
                    content += '<p class="text-muted mb-0">Belum ada riwayat nutrisi untuk penanaman ini.</p>';
                }
                content += '</div>';
                
                // Section 5: Catatan
                content += `
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-sticky-note me-2"></i>Catatan
                        </h6>
                `;
                if (notes.length > 0) {
                    content += `
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Judul</th>
                                        <th>Deskripsi</th>
                                        <th>Pembuat</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    notes.forEach(note => {
                        content += `
                            <tr>
                                <td>${note.note_date || '-'}</td>
                                <td><strong>${note.title || 'Catatan'}</strong></td>
                                <td>${note.description_short || '-'}</td>
                                <td>${note.user_name || '-'}</td>
                            </tr>
                        `;
                    });
                    content += `
                                </tbody>
                            </table>
                        </div>
                    `;
                } else {
                    content += '<p class="text-muted mb-0">Belum ada catatan untuk lokasi penanaman ini.</p>';
                }
                content += '</div>';
                
                // Section 6: Total Pengeluaran
                if (data.totalExpenses !== undefined) {
                    content += `
                        <div class="mb-3">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-money-bill-wave me-2"></i>Total Pengeluaran
                            </h6>
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center p-2">
                                            <small class="text-muted">Perawatan</small>
                                            <h6 class="mb-0">Rp ${number_format(data.totalTreatmentCost || 0, 0)}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center p-2">
                                            <small class="text-muted">Nutrisi</small>
                                            <h6 class="mb-0">Rp ${number_format(data.totalNutrientCost || 0, 0)}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center p-2">
                                            <small class="text-muted">Lainnya</small>
                                            <h6 class="mb-0">Rp ${number_format(data.totalOtherExpenses || 0, 0)}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center p-2">
                                            <small>Total Keseluruhan</small>
                                            <h6 class="mb-0">Rp ${number_format(data.totalExpenses || 0, 0)}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    `;
                    
                    if (expenses.length > 0) {
                        content += `
                            <h6 class="mt-3 mb-2">Rincian Pengeluaran</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Nama Pengeluaran</th>
                                            <th>Tipe</th>
                                            <th>Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        expenses.forEach(expense => {
                            content += `
                                <tr>
                                    <td>${expense.expense_date || '-'}</td>
                                    <td><strong>${expense.expense_name}</strong></td>
                                    <td><span class="badge bg-secondary">${expense.expense_type_label || '-'}</span></td>
                                    <td>Rp ${number_format(expense.amount, 0)}</td>
                                </tr>
                            `;
                        });
                        content += `
                                    </tbody>
                                </table>
                            </div>
                        `;
                    }
                    content += '</div>';
                }
                
                document.getElementById('harvestDetailContent').innerHTML = content;
            } else {
                throw new Error('Invalid response format');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            let errorMessage = 'Terjadi kesalahan saat memuat data. Silakan refresh halaman dan coba lagi.';
            if (error.message) {
                errorMessage += '<br><small class="text-muted">Detail: ' + error.message + '</small>';
            }
            document.getElementById('harvestDetailContent').innerHTML = 
                '<div class="alert alert-danger">' + errorMessage + '</div>';
        });
}

// Helper function for number formatting
function number_format(number, decimals) {
    if (number === null || number === undefined) return '0.00';
    return parseFloat(number).toFixed(decimals || 2);
}

// Load harvest edit form
function loadHarvestEdit(harvestId) {
    // Show loading
    document.getElementById('harvestEditContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    fetch(`{{ url('harvests') }}/${harvestId}/edit`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.harvest) {
                const harvest = data.harvest;
                const locations = data.locations || [];
                const plantings = data.plantings || [];
                
                const content = `
                    <input type="hidden" name="harvest_id" value="${harvest.id}">
                    <input type="hidden" name="plant_id" value="${harvest.plant_id || ''}">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Panen <span class="text-danger">*</span></label>
                        <input type="date" name="harvested_at" class="form-control" value="${harvest.harvested_at}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor Batch <span class="text-danger">*</span></label>
                        <input type="text" name="batch_no" class="form-control" value="${harvest.batch_no}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih Lahan <span class="text-danger">*</span></label>
                        <select name="planting_location_id" class="form-select" id="edit_planting_location_id" required onchange="updateEditPlantings(this.value, ${harvest.id})">
                            <option value="">Pilih Lahan</option>
                            ${locations.map(loc => `
                                <option value="${loc.id}" ${harvest.planting_location_id == loc.id ? 'selected' : ''}>${loc.name}</option>
                            `).join('')}
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih Lokasi Tanam</label>
                        <select name="planting_id" class="form-select" id="edit_planting_id">
                            <option value="">-</option>
                            ${plantings.map(p => `
                                <option value="${p.id}" ${harvest.planting_id == p.id ? 'selected' : ''}>${p.bed_label || 'Tanpa Bed Label'}</option>
                            `).join('')}
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sumber Panen <span class="text-danger">*</span></label>
                        <input type="text" name="source" class="form-control" value="${harvest.source || ''}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kualitas / Ukuran (Opsional)</label>
                        <input type="text" name="quality" class="form-control" value="${harvest.quality || ''}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Satuan Panen</label>
                        <select name="harvest_unit" class="form-select" id="edit_harvest_unit">
                            <option value="">Pilih satuan</option>
                            <option value="ikat" ${harvest.harvest_unit == 'ikat' ? 'selected' : ''}>Ikat / Gulungan</option>
                            <option value="barel" ${harvest.harvest_unit == 'barel' ? 'selected' : ''}>Barel / Tong</option>
                            <option value="tandan" ${harvest.harvest_unit == 'tandan' ? 'selected' : ''}>Tandan / Ikat</option>
                            <option value="gantang" ${harvest.harvest_unit == 'gantang' ? 'selected' : ''}>Gantang</option>
                            <option value="lusin" ${harvest.harvest_unit == 'lusin' ? 'selected' : ''}>Lusin</option>
                            <option value="gram" ${harvest.harvest_unit == 'gram' ? 'selected' : ''}>Gram</option>
                            <option value="batang" ${harvest.harvest_unit == 'batang' ? 'selected' : ''}>Batang / Kepala</option>
                            <option value="kilogram" ${harvest.harvest_unit == 'kilogram' ? 'selected' : ''}>Kilogram</option>
                            <option value="kiloliter" ${harvest.harvest_unit == 'kiloliter' ? 'selected' : ''}>Kiloliter (1.000 liter)</option>
                            <option value="liter" ${harvest.harvest_unit == 'liter' ? 'selected' : ''}>Liter</option>
                            <option value="mililiter" ${harvest.harvest_unit == 'mililiter' ? 'selected' : ''}>Mililiter</option>
                            <option value="jumlah" ${harvest.harvest_unit == 'jumlah' ? 'selected' : ''}>Jumlah / Satuan</option>
                            <option value="ton" ${harvest.harvest_unit == 'ton' ? 'selected' : ''}>Ton</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Satuan Panen</label>
                        <input type="number" step="0.01" name="unit_quantity" class="form-control" id="edit_unit_quantity" value="${harvest.unit_quantity || ''}" min="0" oninput="calculateEditTotal()">
                        <small class="text-muted">Jumlah satuan yang dipanen (opsional)</small>
                    </div>
                    <div class="mb-3" id="editQuantityPerUnitContainer" style="display: ${harvest.unit_quantity ? 'block' : 'none'};">
                        <label class="form-label">Jumlah Panen per Satuan</label>
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" id="edit_quantity_per_unit_kg" name="quantity_per_unit_kg" value="${harvest.quantity_per_unit_kg || harvest.quantity_per_unit || ''}" min="0" oninput="calculateEditTotal()">
                            <select class="form-select" id="edit_quantity_per_unit_unit" name="quantity_per_unit_unit" style="max-width: 150px;">
                                <option value="kg" ${(harvest.quantity_per_unit_unit || harvest.unit) == 'kg' ? 'selected' : ''}>kg</option>
                                <option value="ikat" ${(harvest.quantity_per_unit_unit || harvest.unit) == 'ikat' ? 'selected' : ''}>Ikat / Gulungan</option>
                                <option value="barel" ${(harvest.quantity_per_unit_unit || harvest.unit) == 'barel' ? 'selected' : ''}>Barel / Tong</option>
                                <option value="tandan" ${(harvest.quantity_per_unit_unit || harvest.unit) == 'tandan' ? 'selected' : ''}>Tandan / Ikat</option>
                                <option value="gantang" ${(harvest.quantity_per_unit_unit || harvest.unit) == 'gantang' ? 'selected' : ''}>Gantang</option>
                                <option value="lusin" ${(harvest.quantity_per_unit_unit || harvest.unit) == 'lusin' ? 'selected' : ''}>Lusin</option>
                                <option value="gram" ${(harvest.quantity_per_unit_unit || harvest.unit) == 'gram' ? 'selected' : ''}>Gram</option>
                                <option value="batang" ${(harvest.quantity_per_unit_unit || harvest.unit) == 'batang' ? 'selected' : ''}>Batang / Kepala</option>
                                <option value="kilogram" ${(harvest.quantity_per_unit_unit || harvest.unit) == 'kilogram' ? 'selected' : ''}>Kilogram</option>
                                <option value="kiloliter" ${(harvest.quantity_per_unit_unit || harvest.unit) == 'kiloliter' ? 'selected' : ''}>Kiloliter</option>
                                <option value="liter" ${(harvest.quantity_per_unit_unit || harvest.unit) == 'liter' ? 'selected' : ''}>Liter</option>
                                <option value="mililiter" ${(harvest.quantity_per_unit_unit || harvest.unit) == 'mililiter' ? 'selected' : ''}>Mililiter</option>
                                <option value="jumlah" ${(harvest.quantity_per_unit_unit || harvest.unit) == 'jumlah' ? 'selected' : ''}>Jumlah / Satuan</option>
                                <option value="ton" ${(harvest.quantity_per_unit_unit || harvest.unit) == 'ton' ? 'selected' : ''}>Ton</option>
                            </select>
                        </div>
                        <small class="text-muted">Jumlah panen per satuan</small>
                        <input type="hidden" name="quantity_per_unit" id="edit_quantity_per_unit" value="${harvest.quantity_per_unit || ''}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Panen <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="quantity" class="form-control" id="edit_quantity" value="${harvest.quantity}" required readonly>
                            <select class="form-select" id="editQuantityUnit" name="quantityUnit" style="max-width: 150px;" required>
                                <option value="kg" ${harvest.unit == 'kg' ? 'selected' : ''}>kg</option>
                                <option value="ikat" ${harvest.unit == 'ikat' ? 'selected' : ''}>Ikat / Gulungan</option>
                                <option value="barel" ${harvest.unit == 'barel' ? 'selected' : ''}>Barel / Tong</option>
                                <option value="tandan" ${harvest.unit == 'tandan' ? 'selected' : ''}>Tandan / Ikat</option>
                                <option value="gantang" ${harvest.unit == 'gantang' ? 'selected' : ''}>Gantang</option>
                                <option value="lusin" ${harvest.unit == 'lusin' ? 'selected' : ''}>Lusin</option>
                                <option value="gram" ${harvest.unit == 'gram' ? 'selected' : ''}>Gram</option>
                                <option value="batang" ${harvest.unit == 'batang' ? 'selected' : ''}>Batang / Kepala</option>
                                <option value="kilogram" ${harvest.unit == 'kilogram' ? 'selected' : ''}>Kilogram</option>
                                <option value="kiloliter" ${harvest.unit == 'kiloliter' ? 'selected' : ''}>Kiloliter</option>
                                <option value="liter" ${harvest.unit == 'liter' ? 'selected' : ''}>Liter</option>
                                <option value="mililiter" ${harvest.unit == 'mililiter' ? 'selected' : ''}>Mililiter</option>
                                <option value="jumlah" ${harvest.unit == 'jumlah' ? 'selected' : ''}>Jumlah / Satuan</option>
                                <option value="ton" ${harvest.unit == 'ton' ? 'selected' : ''}>Ton</option>
                            </select>
                        </div>
                        <input type="hidden" name="unit" id="editUnit" value="${harvest.unit || 'kg'}">
                        <small class="text-muted">Dihitung otomatis: Jumlah Satuan Panen × Jumlah Panen per Satuan</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="note" class="form-control" rows="3">${harvest.note || ''}</textarea>
                    </div>
                `;
                document.getElementById('harvestEditContent').innerHTML = content;
                
                // Setup calculation
                setupEditCalculation();
                
                // Update plantings when location changes
                window.updateEditPlantings = function(locationId, harvestId) {
                    const select = document.getElementById('edit_planting_id');
                    if (!locationId) {
                        select.innerHTML = '<option value="">-</option>';
                        return;
                    }
                    // Fetch plantings for the selected location
                    fetch(`{{ url('harvests') }}/${harvestId}/edit?planting_location_id=${locationId}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.plantings) {
                            select.innerHTML = '<option value="">-</option>';
                            data.plantings.forEach(planting => {
                                const option = document.createElement('option');
                                option.value = planting.id;
                                option.textContent = planting.bed_label || 'Tanpa Bed Label';
                                select.appendChild(option);
                            });
                        }
                    })
                    .catch(error => console.error('Error loading plantings:', error));
                };
            } else {
                throw new Error('Invalid response format');
            }
        })
        .catch(error => {
            console.error('Error loading harvest edit:', error);
            let errorMessage = 'Terjadi kesalahan saat memuat data.';
            if (error.message) {
                errorMessage += ' ' + error.message;
            } else if (error.errors) {
                errorMessage += ' ' + JSON.stringify(error.errors);
            }
            document.getElementById('harvestEditContent').innerHTML = 
                `<div class="alert alert-danger">${errorMessage}</div>`;
        });
}

function setupEditCalculation() {
    const quantityInput = document.querySelector('#harvestEditContent input[name="quantity"]');
    const unitQuantityInput = document.getElementById('edit_unit_quantity');
    const quantityPerUnitKgInput = document.getElementById('edit_quantity_per_unit_kg');
    const quantityPerUnitUnitSelect = document.getElementById('edit_quantity_per_unit_unit');
    const quantityPerUnitHidden = document.getElementById('edit_quantity_per_unit');
    const quantityPerUnitContainer = document.getElementById('editQuantityPerUnitContainer');
    const harvestUnitSelect = document.querySelector('#harvestEditContent select[name="harvest_unit"]');
    const unitInput = document.getElementById('editUnit');
    const quantityUnitSelect = document.getElementById('editQuantityUnit');
    
    function calculateTotalQuantity() {
        const unitQuantity = parseFloat(unitQuantityInput?.value) || 0;
        const quantityPerUnit = parseFloat(quantityPerUnitKgInput?.value) || 0;
        
        // Show/hide quantity per unit container based on unit_quantity
        if (quantityPerUnitContainer) {
            if (unitQuantity > 0) {
                quantityPerUnitContainer.style.display = 'block';
            } else {
                quantityPerUnitContainer.style.display = 'none';
            }
        }
        
        if (unitQuantity > 0 && quantityPerUnit > 0) {
            const totalQuantity = unitQuantity * quantityPerUnit;
            if (quantityInput) quantityInput.value = totalQuantity.toFixed(2);
            
            // Set quantity_per_unit for saving
            if (quantityPerUnitHidden) {
                quantityPerUnitHidden.value = quantityPerUnit.toFixed(2);
            }
        } else {
            if (quantityInput) quantityInput.value = '';
            if (quantityPerUnitHidden) {
                quantityPerUnitHidden.value = '';
            }
        }
    }
    
    // Override global calculateEditTotal if it exists
    window.calculateEditTotal = calculateTotalQuantity;
    
    function updateQuantityUnit() {
        const selectedUnit = harvestUnitSelect.value;
        if (selectedUnit) {
            // Map harvest unit to quantity unit
            const unitMap = {
                'ikat': 'kg',
                'barel': 'kg',
                'tandan': 'kg',
                'gantang': 'kg',
                'lusin': 'kg',
                'gram': 'gram',
                'batang': 'kg',
                'kilogram': 'kg',
                'kiloliter': 'liter',
                'liter': 'liter',
                'mililiter': 'ml',
                'jumlah': 'jumlah',
                'ton': 'kg'
            };
            
            const mappedUnit = unitMap[selectedUnit] || 'kg';
            unitInput.value = mappedUnit;
            quantityUnitSelect.value = mappedUnit;
            // Update quantity per unit unit to match
            if (quantityPerUnitUnitSelect) {
                quantityPerUnitUnitSelect.value = mappedUnit;
            }
        } else {
            unitInput.value = 'kg';
            quantityUnitSelect.value = 'kg';
            if (quantityPerUnitUnitSelect) {
                quantityPerUnitUnitSelect.value = 'kg';
            }
        }
    }
    
    // Attach event listeners
    if (unitQuantityInput) {
        unitQuantityInput.addEventListener('input', calculateTotalQuantity);
    }
    if (quantityPerUnitKgInput) {
        quantityPerUnitKgInput.addEventListener('input', calculateTotalQuantity);
    }
    
    if (harvestUnitSelect && quantityUnitSelect) {
        harvestUnitSelect.addEventListener('change', updateQuantityUnit);
        quantityUnitSelect.addEventListener('change', function() {
            unitInput.value = quantityUnitSelect.value;
            if (quantityPerUnitUnitSelect) {
                quantityPerUnitUnitSelect.value = quantityUnitSelect.value;
            }
        });
    }
    
    // Show container if unit_quantity is filled
    if (unitQuantityInput && parseFloat(unitQuantityInput.value) > 0) {
        quantityPerUnitContainer.style.display = 'block';
    }
}

// Handle edit form submit
document.addEventListener('DOMContentLoaded', function() {
    // Use event delegation for dynamically created form
    document.addEventListener('submit', function(e) {
        if (e.target && e.target.closest('#harvestEditModal')) {
            const form = e.target.closest('form');
            if (form && form.id === 'harvestEditForm') {
                e.preventDefault();
                const formData = new FormData(form);
                const harvestId = formData.get('harvest_id');
                
                if (!harvestId) {
                    alert('ID panen tidak ditemukan.');
                    return;
                }
                
                // Ensure _method is set for PUT request
                if (!formData.has('_method')) {
                    formData.append('_method', 'PUT');
                }
                
                // Ensure CSRF token is included
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (csrfToken) {
                    formData.append('_token', csrfToken);
                }
                
                // Get unit from quantityUnit select
                const quantityUnitSelect = form.querySelector('#editQuantityUnit');
                if (quantityUnitSelect) {
                    formData.set('unit', quantityUnitSelect.value);
                }
                
                // Show loading
                const submitButton = form.querySelector('button[type="submit"]');
                const originalButtonText = submitButton ? submitButton.innerHTML : '';
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
                }
                
                fetch(`{{ url('harvests') }}/${harvestId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => Promise.reject(err));
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('harvestEditModal'));
                        if (modal) {
                            modal.hide();
                        }
                        // Show success message
                        if (data.message) {
                            alert(data.message);
                        }
                        window.location.reload();
                    } else {
                        throw new Error(data.message || 'Terjadi kesalahan saat menyimpan data.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    let errorMessage = 'Terjadi kesalahan saat menyimpan data.';
                    if (error.message) {
                        errorMessage = error.message;
                    } else if (error.errors) {
                        const errorList = Object.values(error.errors).flat().join(', ');
                        errorMessage = errorList || errorMessage;
                    }
                    alert(errorMessage);
                })
                .finally(() => {
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonText;
                    }
                });
            }
        }
    });
});

// Delete harvest function
function deleteHarvest(harvestId) {
    if (confirm('Apakah Anda yakin ingin menghapus data panen ini? Tindakan ini tidak dapat dibatalkan.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('harvests') }}/${harvestId}`;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = csrfToken;
        form.appendChild(tokenInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection
