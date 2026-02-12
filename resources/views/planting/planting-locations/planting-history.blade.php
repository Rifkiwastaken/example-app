@extends('layouts.app')

@section('title', 'Riwayat Penanaman - ' . $plantingLocation->name . ' - SIBESTI')

@push('styles')
<style>
    .nav-pills .nav-link {
        transition: all 0.3s ease;
    }
    .nav-pills .nav-link:not(.active) {
        background-color: #e9ecef !important;
        color: #495057 !important;
        opacity: 1 !important;
    }
    .nav-pills .nav-link.active {
        opacity: 1 !important;
    }
    #plantingCategoryTabs .nav-link[href="#telah-dipanen"].active {
        background-color: #198754 !important;
        color: white !important;
    }
    #plantingCategoryTabs .nav-link[href="#kehilangan"].active {
        background-color: #ffc107 !important;
        color: #000 !important;
    }
    #plantingCategoryTabs .nav-link[href="#gagal-panen"].active {
        background-color: #dc3545 !important;
        color: white !important;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $plantingLocation->name }}</h4>
    </div>
    <a href="{{ route('planting-locations.show', $plantingLocation) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Navigation Tabs -->
<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('planting-locations.show', $plantingLocation) }}">
            <i class="fas fa-info-circle me-1"></i>Detail & Lokasi Penanaman
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('planting-locations.plantings.index', $plantingLocation) }}">
            <i class="fas fa-seedling me-1"></i>Penanaman
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('planting-locations.planting-history', $plantingLocation) }}">
            <i class="fas fa-history me-1"></i>Riwayat Penanaman
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('planting-locations.expenses.index', $plantingLocation) }}">
            <i class="fas fa-money-bill-wave me-1"></i>Pengeluaran
        </a>
    </li>
</ul>

<div class="tab-content p-3 bg-white border border-top-0 rounded-bottom">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="mb-0">Riwayat Penanaman - {{ $plantingLocation->name }}</h6>
        </div>
    </div>

    <!-- Sub-tabs untuk kategori penanaman -->
    <ul class="nav nav-pills mb-3" role="tablist" id="plantingCategoryTabs">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#telah-dipanen" 
               style="background-color: #198754; color: white; font-weight: 600;">
                <i class="fas fa-check-circle me-1"></i>Telah Dipanen
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#kehilangan" 
               style="background-color: #e9ecef; color: #495057;">
                <i class="fas fa-exclamation-triangle me-1"></i>Kehilangan
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#gagal-panen" 
               style="background-color: #e9ecef; color: #495057;">
                <i class="fas fa-times-circle me-1"></i>Gagal Panen
            </a>
        </li>
    </ul>
    
    <div class="tab-content">
        <!-- Sub-tab: Telah Dipanen -->
        <div class="tab-pane fade show active" id="telah-dipanen">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Lokasi Tanam</th>
                            <th>Tanaman</th>
                            <th>Jumlah Tanam</th>
                            <th>Tanggal Tanam</th>
                            <th>Tanggal Panen</th>
                            <th>Jumlah Panen</th>
                            <th>Nomor Batch Tanam</th>
                            <th>Nomor Batch Panen</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($harvestedPlantings as $planting)
                            @php
                                // Get all harvests with quantity > 0, ordered by date
                                $harvests = $planting->harvests->where('quantity', '>', 0)->sortByDesc('harvested_at');
                            @endphp
                            @if($harvests->count() > 0)
                                @foreach($harvests as $harvest)
                                    <tr>
                                        <td>{{ $planting->bed_label ?: '-' }}</td>
                                        <td><strong>{{ $planting->plant->name }}</strong><br><small class="text-muted">{{ $planting->plant->variety ?: 'Tidak ada varietas' }}</small></td>
                                        <td>{{ number_format($planting->quantity_planted ?? 0, 0) }}</td>
                                        <td>{{ $planting->planted_at ? $planting->planted_at->format('d M Y') : '-' }}</td>
                                        <td>{{ $harvest && $harvest->harvested_at ? $harvest->harvested_at->format('d M Y') : '-' }}</td>
                                        <td>{{ $harvest ? number_format($harvest->quantity ?? 0, 2) . ' ' . ($harvest->unit ?? 'kg') : '-' }}</td>
                                        <td><span class="badge bg-primary">{{ $planting->planting_batch_number ?? '-' }}</span></td>
                                        <td><span class="badge bg-info">{{ $harvest->batch_no ?? '-' }}</span></td>
                                        <td>
                                            <button type="button" 
                                                    class="btn btn-sm btn-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#harvestDetailModal"
                                                    onclick="loadHarvestDetailFromLocation('{{ $harvest->harvest_id }}')"
                                                    title="Lihat Detail Panen">
                                                <i class="fas fa-eye me-1"></i>Detail Panen
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td>{{ $planting->bed_label ?: '-' }}</td>
                                    <td><strong>{{ $planting->plant->name }}</strong><br><small class="text-muted">{{ $planting->plant->variety ?: 'Tidak ada varietas' }}</small></td>
                                    <td>{{ number_format($planting->quantity_planted ?? 0, 0) }}</td>
                                    <td>{{ $planting->planted_at ? $planting->planted_at->format('d M Y') : '-' }}</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td><span class="badge bg-primary">{{ $planting->planting_batch_number ?? '-' }}</span></td>
                                    <td>-</td>
                                    <td>-</td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="9" class="text-center text-muted">Belum ada penanaman yang telah dipanen.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sub-tab: Kehilangan -->
        <div class="tab-pane fade" id="kehilangan" data-tab-type="loss">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Lokasi Tanam</th>
                            <th>Tanaman</th>
                            <th>Jumlah Tanam</th>
                            <th>Total Kehilangan</th>
                            <th>Sisa Tanaman</th>
                            <th>Nomor Batch Tanam</th>
                            <th>Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lossPlantings as $planting)
                            @php
                                $totalLoss = $planting->losses->sum('loss_amount');
                                $remaining = $planting->quantity_planted - $totalLoss;
                            @endphp
                            <tr>
                                <td>{{ $planting->bed_label ?: '-' }}</td>
                                <td><strong>{{ $planting->plant->name }}</strong><br><small class="text-muted">{{ $planting->plant->variety ?: 'Tidak ada varietas' }}</small></td>
                                <td>{{ number_format($planting->quantity_planted ?? 0, 0) }}</td>
                                <td><span class="badge bg-warning">{{ number_format($totalLoss, 0) }}</span></td>
                                <td>{{ number_format($remaining, 0) }}</td>
                                <td><span class="badge bg-primary">{{ $planting->planting_batch_number ?? '-' }}</span></td>
                                <td>
                                    @foreach($planting->losses->take(2) as $loss)
                                        <small class="d-block">{{ $loss->loss_reason ?: 'Tidak disebutkan' }}</small>
                                    @endforeach
                                    @if($planting->losses->count() > 2)
                                        <small class="text-muted">+{{ $planting->losses->count() - 2 }} lainnya</small>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">Belum ada data kehilangan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sub-tab: Gagal Panen -->
        <div class="tab-pane fade" id="gagal-panen" data-tab-type="failed">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Lokasi Tanam</th>
                            <th>Tanaman</th>
                            <th>Jumlah Tanam</th>
                            <th>Tanggal Tanam</th>
                            <th>Tanggal Gagal</th>
                            <th>Nomor Batch Tanam</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($failedPlantings as $planting)
                            @php
                                // Get harvest (support both hasOne and hasMany)
                                $harvest = $planting->harvest;
                                if (!$harvest && $planting->harvests && $planting->harvests->count() > 0) {
                                    $harvest = $planting->harvests->first();
                                }
                            @endphp
                            <tr>
                                <td>{{ $planting->bed_label ?: '-' }}</td>
                                <td><strong>{{ $planting->plant->name }}</strong><br><small class="text-muted">{{ $planting->plant->variety ?: 'Tidak ada varietas' }}</small></td>
                                <td>{{ number_format($planting->quantity_planted ?? 0, 0) }}</td>
                                <td>{{ $planting->planted_at ? $planting->planted_at->format('d M Y') : '-' }}</td>
                                <td>{{ $harvest && $harvest->harvested_at ? $harvest->harvested_at->format('d M Y') : '-' }}</td>
                                <td><span class="badge bg-primary">{{ $planting->planting_batch_number ?? '-' }}</span></td>
                                <td>
                                    @if($harvest && $harvest->note)
                                        {{ Str::limit($harvest->note, 50) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">Belum ada penanaman yang gagal panen.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>

// Handle sub-tab styling
document.addEventListener('DOMContentLoaded', function() {
    const pills = document.querySelectorAll('#plantingCategoryTabs .nav-link');
    pills.forEach(pill => {
        pill.addEventListener('shown.bs.tab', function(e) {
            pills.forEach(p => {
                p.classList.remove('bg-primary', 'text-white', 'active');
                if (p.getAttribute('href') === '#telah-dipanen') {
                    p.style.backgroundColor = '#e9ecef';
                    p.style.color = '#495057';
                } else if (p.getAttribute('href') === '#kehilangan') {
                    p.style.backgroundColor = '#e9ecef';
                    p.style.color = '#495057';
                } else if (p.getAttribute('href') === '#gagal-panen') {
                    p.style.backgroundColor = '#e9ecef';
                    p.style.color = '#495057';
                }
            });
            
            const href = e.target.getAttribute('href');
            if (href === '#telah-dipanen') {
                e.target.style.backgroundColor = '#198754';
                e.target.style.color = 'white';
            } else if (href === '#kehilangan') {
                e.target.style.backgroundColor = '#ffc107';
                e.target.style.color = '#000';
            } else if (href === '#gagal-panen') {
                e.target.style.backgroundColor = '#dc3545';
                e.target.style.color = 'white';
            }
        });
    });
});

// Load harvest detail for modal
function loadHarvestDetailFromLocation(harvestId) {
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
    
    fetch(`/harvests/${encodeURIComponent(harvestId)}/detail`, {
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
                
                // Section 4: Riwayat Perawatan
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
    return parseFloat(number).toFixed(decimals || 2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
</script>
@endpush

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

@endsection







