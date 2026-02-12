@extends('layouts.app')

@section('title', 'Detail Panen - ' . $planting->plant->name . ' - SIBESTI')

@push('styles')
<style>
    .section-card {
        margin-bottom: 1.5rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .section-header {
        background-color: #f8f9fa;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #dee2e6;
        font-weight: 600;
        font-size: 1.1rem;
    }
    .section-body {
        padding: 1.25rem;
    }
    .info-row {
        display: flex;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        font-weight: 600;
        width: 200px;
        flex-shrink: 0;
        color: #495057;
    }
    .info-value {
        flex: 1;
        color: #212529;
    }
    .table-responsive {
        margin-top: 1rem;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Detail Panen - {{ $planting->plant->name }}</h4>
        <small class="text-muted">Lokasi: {{ $plantingLocation->name }}</small>
        @if($planting->bed_label)
            <br><small class="text-muted">Lokasi Tanam: {{ $planting->bed_label }}</small>
        @endif
    </div>
    <a href="{{ route('planting-locations.planting-history', $plantingLocation) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<!-- Section 1: Informasi Penanaman -->
<div class="section-card">
    <div class="section-header">
        <i class="fas fa-seedling me-2"></i>Informasi Penanaman
    </div>
    <div class="section-body">
        <div class="info-row">
            <div class="info-label">Nama Tanaman:</div>
            <div class="info-value">{{ $planting->plant->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Varietas:</div>
            <div class="info-value">{{ $planting->plant->variety ?: '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Nomor Batch Tanam:</div>
            <div class="info-value">{{ $planting->planting_batch_number ?: '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Nomor Batch Panen:</div>
            <div class="info-value">
                <span class="badge bg-info">{{ $harvest->batch_no ?? '-' }}</span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Lokasi Tanam:</div>
            <div class="info-value">{{ $planting->bed_label ?: '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Jumlah Tanam:</div>
            <div class="info-value">{{ number_format($planting->quantity_planted ?? 0, 0) }} tanaman</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Tanam:</div>
            <div class="info-value">{{ $planting->planted_at ? $planting->planted_at->format('d F Y') : '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Panen:</div>
            <div class="info-value">{{ $harvest && $harvest->harvested_at ? $harvest->harvested_at->format('d F Y') : '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Jumlah Panen:</div>
            <div class="info-value">
                <strong>{{ $harvest ? number_format($harvest->quantity ?? 0, 2) . ' ' . ($harvest->unit ?? 'kg') : '-' }}</strong>
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Kualitas Panen:</div>
            <div class="info-value">{{ $harvest->quality ?? '-' }}</div>
        </div>
        @if($harvest && $harvest->note)
        <div class="info-row">
            <div class="info-label">Catatan Panen:</div>
            <div class="info-value">{{ $harvest->note }}</div>
        </div>
        @endif
        @if($planting->notes)
        <div class="info-row">
            <div class="info-label">Catatan Penanaman:</div>
            <div class="info-value">{{ $planting->notes }}</div>
        </div>
        @endif
    </div>
</div>

<!-- Section 2: Riwayat Laporan -->
<div class="section-card">
    <div class="section-header">
        <i class="fas fa-clipboard-list me-2"></i>Riwayat Laporan
    </div>
    <div class="section-body">
        @if($tasks->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
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
                        @foreach($tasks as $task)
                        <tr>
                            <td>{{ $task->due_date ? $task->due_date->format('d M Y') : '-' }}</td>
                            <td>
                                <strong>{{ $task->title }}</strong>
                                @if($task->description)
                                    <br><small class="text-muted">{{ Str::limit($task->description, 80) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $task->new_status === 'selesai' ? 'success' : ($task->new_status === 'dalam_progress' ? 'info' : 'danger') }}">
                                    {{ $task->new_status === 'selesai' ? 'Selesai' : ($task->new_status === 'dalam_progress' ? 'Dalam Progress' : 'Tidak Selesai') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $task->new_priority === 'tertinggi' || $task->new_priority === 'tinggi' ? 'danger' : ($task->new_priority === 'medium' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($task->new_priority ?? 'medium') }}
                                </span>
                            </td>
                            <td>{{ $task->assignedUser ? $task->assignedUser->name : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0">Belum ada riwayat laporan untuk penanaman ini.</p>
        @endif
    </div>
</div>

<!-- Section 3: Riwayat Perawatan -->
<div class="section-card">
    <div class="section-header">
        <i class="fas fa-first-aid me-2"></i>Riwayat Perawatan
    </div>
    <div class="section-body">
        @if($treatments->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
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
                        @foreach($treatments as $treatment)
                        <tr>
                            <td>{{ $treatment->treatment_date ? $treatment->treatment_date->format('d M Y') : '-' }}</td>
                            <td><strong>{{ $treatment->treatment_name }}</strong></td>
                            <td>{{ $treatment->treatment_type }}</td>
                            <td>{{ $treatment->product_detail ?: '-' }}</td>
                            <td>{{ $treatment->application_method ?: '-' }}</td>
                            <td>
                                @if($treatment->total_cost)
                                    Rp {{ number_format($treatment->total_cost, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0">Belum ada riwayat perawatan untuk penanaman ini.</p>
        @endif
    </div>
</div>

<!-- Section 4: Riwayat Nutrisi -->
<div class="section-card">
    <div class="section-header">
        <i class="fas fa-flask me-2"></i>Riwayat Nutrisi
    </div>
    <div class="section-body">
        @if($nutrients->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
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
                        @foreach($nutrients as $nutrient)
                        <tr>
                            <td>{{ $nutrient->application_date ? $nutrient->application_date->format('d M Y') : '-' }}</td>
                            <td><strong>{{ $nutrient->product_applied }}</strong></td>
                            <td>{{ number_format($nutrient->amount_applied, 2) }} {{ $nutrient->unit }}</td>
                            <td>{{ $nutrient->application_method ?: '-' }}</td>
                            <td>
                                @if($nutrient->total_cost)
                                    Rp {{ number_format($nutrient->total_cost, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0">Belum ada riwayat nutrisi untuk penanaman ini.</p>
        @endif
    </div>
</div>

<!-- Section 5: Catatan -->
<div class="section-card">
    <div class="section-header">
        <i class="fas fa-sticky-note me-2"></i>Catatan
    </div>
    <div class="section-body">
        @if($notes->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Judul</th>
                            <th>Deskripsi</th>
                            <th>Pembuat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notes as $note)
                        <tr>
                            <td>{{ $note->note_date ? $note->note_date->format('d M Y') : '-' }}</td>
                            <td><strong>{{ $note->title ?: 'Catatan' }}</strong></td>
                            <td>{{ Str::limit($note->description, 100) }}</td>
                            <td>{{ $note->user ? $note->user->name : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0">Belum ada catatan untuk lokasi penanaman ini.</p>
        @endif
    </div>
</div>

<!-- Section 6: Total Pengeluaran -->
<div class="section-card">
    <div class="section-header">
        <i class="fas fa-money-bill-wave me-2"></i>Total Pengeluaran
    </div>
    <div class="section-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Perawatan</h6>
                        <h4 class="mb-0">Rp {{ number_format($totalTreatmentCost, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Nutrisi</h6>
                        <h4 class="mb-0">Rp {{ number_format($totalNutrientCost, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Lainnya</h6>
                        <h4 class="mb-0">Rp {{ number_format($totalOtherExpenses, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h6 class="mb-2">Total Keseluruhan</h6>
                        <h4 class="mb-0">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>

        @if($expenses->count() > 0)
            <h6 class="mt-4 mb-3">Rincian Pengeluaran</h6>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama Pengeluaran</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date ? $expense->expense_date->format('d M Y') : '-' }}</td>
                            <td><strong>{{ $expense->expense_name }}</strong></td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ ucfirst(str_replace('_', ' ', $expense->expense_type)) }}
                                </span>
                            </td>
                            <td>Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0 mt-3">Belum ada rincian pengeluaran.</p>
        @endif
    </div>
</div>

@endsection
