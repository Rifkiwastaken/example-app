@extends('layouts.app')

@section('title', 'Detail Laporan Pemeriksaan - SIBESTI')

@section('content')
<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('certifications.index') }}">Manajemen Sertifikasi</a></li>
        <li class="breadcrumb-item"><a href="{{ route('certifications.show', $report->certification->harvest) }}">Kelola Sertifikasi</a></li>
        <li class="breadcrumb-item active">Detail Laporan</li>
    </ol>
</nav>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Detail {{ $report->report_type ?? 'Laporan Pemeriksaan Pertanaman' }}</h4>
        <small class="text-muted">No. Laporan: {{ $report->report_number_bpsb ?: '-' }} | Tanggal: {{ $report->report_date->format('d M Y') }}</small>
    </div>
    <a href="{{ route('certifications.show', $report->certification->harvest) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

@php
    $harvest = $report->certification->harvest;
    $plant = $harvest->plant ?? null;
    $planting = $harvest->planting ?? null;
    $location = $harvest->location ?? null;
    $certification = $report->certification;
@endphp

<!-- Informasi Dasar -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Dasar Laporan</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nomor Laporan BPSB</label>
                    <p class="mb-0">{{ $report->report_number_bpsb ?: '-' }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Tanggal Laporan</label>
                    <p class="mb-0">{{ $report->report_date->format('d M Y') }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Musim Tanam</label>
                    <p class="mb-0">{{ $report->growing_season ?: '-' }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label fw-bold">Fase Pemeriksaan</label>
                    <p class="mb-0"><span class="badge bg-info">{{ $report->inspection_phase }}</span></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Petugas Pengawas Mutu (BPSB)</label>
                    <p class="mb-0">{{ $report->inspector_name ?: '-' }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Kesimpulan</label>
                    <p class="mb-0">
                        <span class="badge {{ $report->conclusion_badge_class }}">
                            {{ $report->conclusion ?: 'Belum Ditentukan' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bagian B: Lot Produksi yang Diperiksa -->
<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-seedling me-2"></i>Bagian B: Lot Produksi yang Diperiksa</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Produsen Benih</label>
                <p class="mb-0">{{ $plant ? ($plant->type?->name ?: $plant->name) : '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Varietas</label>
                <p class="mb-0">{{ $plant && $plant->variety ? $plant->variety : 'Tanpa Varietas' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Lokasi</label>
                <p class="mb-0">{{ $location ? $location->name : '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Status Sertifikasi</label>
                <p class="mb-0">
                    <span class="badge 
                        @if($certification->certification_status == 'lulus') bg-success
                        @elseif($certification->certification_status == 'tidak_lulus') bg-danger
                        @elseif($certification->certification_status == 'dalam_proses') bg-warning
                        @else bg-secondary
                        @endif">
                        {{ $certification->status_label }}
                    </span>
                </p>
            </div>
            @if($planting)
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Tanggal Tanam</label>
                <p class="mb-0">{{ $planting->planting_date ? \Carbon\Carbon::parse($planting->planting_date)->format('d M Y') : '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Luas Tanam</label>
                <p class="mb-0">
                    @if($planting->area && $planting->area_unit)
                        {{ number_format($planting->area, 2) }} {{ $planting->area_unit }}
                    @else
                        -
                    @endif
                </p>
            </div>
            @endif
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Musim Tanam</label>
                <p class="mb-0">{{ $report->growing_season ?: '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Kelas Benih (Diajukan)</label>
                <p class="mb-0">
                    @if($certification->seed_class_requested)
                        <span class="badge bg-info">{{ $certification->seed_class_requested }}</span>
                    @else
                        -
                    @endif
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Nomor Batch Tanam</label>
                <p class="mb-0">
                    @if($report->planting_batch_number)
                        <span class="badge bg-primary">{{ $report->planting_batch_number }}</span>
                    @else
                        -
                    @endif
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Nomor Batch Panen</label>
                <p class="mb-0">
                    @if($report->harvest_batch_number)
                        <span class="badge bg-success">{{ $report->harvest_batch_number }}</span>
                    @else
                        -
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Hasil Pemeriksaan -->
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Bagian C: Hasil Pemeriksaan</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Kelas Benih yang Dihasilkan</label>
                <p class="mb-0">
                    @if($report->seed_class_result)
                        <span class="badge bg-info">{{ $report->seed_class_result }}</span>
                    @else
                        -
                    @endif
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Sifat Tanaman Sesuai Varietas</label>
                <p class="mb-0">
                    @if($report->plant_characteristics_match !== null)
                        <span class="badge {{ $report->plant_characteristics_match ? 'bg-success' : 'bg-danger' }}">
                            {{ $report->plant_characteristics_match ? 'Ya' : 'Tidak' }}
                        </span>
                    @else
                        -
                    @endif
                </p>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">Isolasi - Utara</label>
                <p class="mb-0">{{ $report->isolation_north ?: '-' }}</p>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">Isolasi - Timur</label>
                <p class="mb-0">{{ $report->isolation_east ?: '-' }}</p>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">Isolasi - Selatan</label>
                <p class="mb-0">{{ $report->isolation_south ?: '-' }}</p>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label fw-bold">Isolasi - Barat</label>
                <p class="mb-0">{{ $report->isolation_west ?: '-' }}</p>
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label fw-bold">Keadaan Hama dan Penyakit</label>
                <p class="mb-0">{{ $report->pest_disease_condition ?: '-' }}</p>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Keadaan Rerumputan</label>
                <p class="mb-0">
                    @if($report->weed_condition)
                        <span class="badge bg-secondary">{{ $report->weed_condition }}</span>
                    @else
                        -
                    @endif
                </p>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Populasi per Contoh Pemeriksaan</label>
                <p class="mb-0">{{ $report->population_per_sample ?: '-' }}</p>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold">Jumlah Temuan Campuran Varietas Lain</label>
                <p class="mb-0">{{ $report->other_variety_mix_count ?: '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Rata-rata Campuran Varietas Lain (%)</label>
                <p class="mb-0">{{ $report->other_variety_mix_percentage ? number_format($report->other_variety_mix_percentage, 2) : '-' }}%</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Taksiran Hasil</label>
                <p class="mb-0">{{ $report->estimated_yield ? number_format($report->estimated_yield, 2) : '-' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Tanggal Masa Edar / Kadaluarsa</label>
                <p class="mb-0">
                    @if($report->expiry_date)
                        {{ $report->expiry_date->format('d M Y') }}
                        @if($report->expiry_date->isPast())
                            <span class="badge bg-danger ms-2">Melewati Masa Edar</span>
                        @elseif($report->expiry_date->diffInMonths(now()) <= 3)
                            <span class="badge bg-warning ms-2">Mendekati Masa Edar</span>
                        @endif
                    @else
                        -
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Bagian D: Jumlah Benih yang Lulus Sertifikasi -->
<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Bagian D: Jumlah Benih yang Lulus Sertifikasi</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Satuan Inventaris</label>
                <p class="mb-0">
                    @if($report->seed_unit)
                        <span class="badge bg-info">{{ strtoupper($report->seed_unit) }}</span>
                    @else
                        -
                    @endif
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Total Inventaris</label>
                <p class="mb-0">
                    @if($report->certified_seed_quantity)
                        {{ number_format($report->certified_seed_quantity, 2) }} {{ $report->seed_unit ?? '' }}
                    @else
                        -
                    @endif
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Estimasi Penjualan per Unit</label>
                <p class="mb-0">
                    @if($report->estimated_sale_price_per_kg)
                        Rp {{ number_format($report->estimated_sale_price_per_kg, 2, ',', '.') }}
                    @else
                        -
                    @endif
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Pengisi Data</label>
                <p class="mb-0">{{ $report->reporter_name ?: '-' }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Bagian E: Kesimpulan & Lampiran -->
<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Bagian E: Kesimpulan & Lampiran</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Kesimpulan / Rekomendasi</label>
                <p class="mb-0">
                    <span class="badge {{ $report->conclusion_badge_class }}">
                        {{ $report->conclusion ?: 'Belum Ditentukan' }}
                    </span>
                </p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Lampiran</label>
                <p class="mb-0">
                    @if($report->scan_file_path)
                        <a href="{{ asset('storage/' . $report->scan_file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-file-pdf me-2"></i>Lihat File
                        </a>
                    @else
                        <span class="text-muted">Tidak ada lampiran</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Tombol Lihat Data Panen Benih -->
<div class="d-flex justify-content-between mb-4">
    <a href="{{ route('certifications.show', $report->certification->harvest) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
    @if($harvest)
        <button type="button" class="btn btn-success" onclick="viewHarvestDetail({{ $harvest->id }})">
            <i class="fas fa-seedling me-2"></i>Lihat Data Panen Benih
        </button>
    @endif
</div>

<!-- Modal: Detail Panen Benih -->
<div class="modal fade" id="harvestDetailModal" tabindex="-1" aria-labelledby="harvestDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="harvestDetailModalLabel">
                    <i class="fas fa-info-circle me-2"></i>Detail Panen Benih
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
function viewHarvestDetail(harvestId) {
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('harvestDetailModal'));
    modal.show();
    
    // Show loading
    document.getElementById('harvestDetailContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    // Fetch harvest detail
    fetch(`/harvests/${harvestId}/detail`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
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
    return parseFloat(number).toFixed(decimals || 2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
</script>
@endpush

@endsection














