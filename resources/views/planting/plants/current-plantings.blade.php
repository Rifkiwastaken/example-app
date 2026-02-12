@extends('layouts.app')

@section('title', 'Penanaman Saat Ini - ' . $plant->name . ' - SIBESTI')

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
        <a class="nav-link active" href="{{ route('plants.current-plantings', $plant) }}">
            <i class="fas fa-seedling me-1"></i>Penanaman saat ini
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('plants.harvests.index', $plant) }}">
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
    <!-- Sub-tabs for planting status -->
    <ul class="nav nav-pills mb-3" role="tablist" id="plantingStatusTabs">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="pill" href="#ditanam-saat-ini" id="tab-ditanam-saat-ini">
                <i class="fas fa-seedling me-1"></i>Ditanam Saat Ini ({{ $currentPlantings->count() }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="pill" href="#telah-dipanen" id="tab-telah-dipanen">
                <i class="fas fa-check-circle me-1"></i>Telah Dipanen ({{ $harvestedPlantings->count() }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="pill" href="#kehilangan" id="tab-kehilangan">
                <i class="fas fa-exclamation-triangle me-1"></i>Kehilangan ({{ $lostPlantings->count() }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="pill" href="#gagal-panen" id="tab-gagal-panen">
                <i class="fas fa-times-circle me-1"></i>Gagal Panen ({{ $failedPlantings->count() }})
            </a>
        </li>
    </ul>
    
    <style>
        #plantingStatusTabs .nav-link {
            background-color: #6c757d !important;
            color: #ffffff !important;
            border: 1px solid #6c757d !important;
            margin-right: 5px;
            border-radius: 0.375rem;
            padding: 0.5rem 1rem;
            font-weight: 500;
            opacity: 1 !important;
            transition: all 0.3s ease;
        }
        #plantingStatusTabs .nav-link:not(.active) {
            background-color: #6c757d !important;
            color: #ffffff !important;
            opacity: 1 !important;
        }
        #plantingStatusTabs .nav-link:hover:not(.active) {
            background-color: #5a6268 !important;
            color: #ffffff !important;
            opacity: 1 !important;
            border-color: #5a6268 !important;
        }
        #plantingStatusTabs .nav-link.active {
            background-color: #0d6efd !important;
            color: #ffffff !important;
            border-color: #0d6efd !important;
            font-weight: 600;
            opacity: 1 !important;
        }
        #plantingStatusTabs .nav-link.active#tab-ditanam-saat-ini {
            background-color: #0d6efd !important;
            color: #ffffff !important;
        }
        #plantingStatusTabs .nav-link.active#tab-telah-dipanen {
            background-color: #0d6efd !important;
            color: #ffffff !important;
        }
        #plantingStatusTabs .nav-link.active#tab-kehilangan {
            background-color: #0d6efd !important;
            color: #ffffff !important;
        }
        #plantingStatusTabs .nav-link.active#tab-gagal-panen {
            background-color: #0d6efd !important;
            color: #ffffff !important;
        }
    </style>

    <div class="tab-content">
        <!-- Tab: Ditanam Saat Ini -->
        <div class="tab-pane fade show active" id="ditanam-saat-ini">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Tabel Penanaman Saat Ini (Varietas: {{ $plant->name }})</h5>
                </div>
                <div class="card-body">
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
                                    <th>Lokasi Lahan</th>
                                    <th>Lokasi Tanam (Bed/Baris)</th>
                                    <th>Nomor Batch Tanam</th>
                                    <th>Jumlah Ditanam</th>
                                    <th>Tanggal Tanam</th>
                                    <th>Estimasi Panen</th>
                                    <th width="200">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($currentPlantings as $planting)
                                <tr>
                                    <td>
                                        <strong>{{ $planting->location?->name ?? '-' }}</strong>
                                    </td>
                                    <td>{{ $planting->bed_label ?: '-' }}</td>
                                    <td><span class="badge bg-primary">{{ $planting->planting_batch_number ?? '-' }}</span></td>
                                    <td>{{ number_format($planting->quantity_planted ?? 0, 0) }} {{ $plant->type->name ?? 'unit' }}</td>
                                    <td>{{ $planting->planted_at ? $planting->planted_at->format('d M Y') : '-' }}</td>
                                    <td>
                                        @php
                                            $totalLosses = $planting->losses->sum('loss_amount');
                                            $currentPlants = ($planting->quantity_planted ?? 0) - $totalLosses;
                                        @endphp
                                        @if($planting->estimated_harvest_date)
                                            <span class="text-info">
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                {{ $planting->estimated_harvest_date->format('d M Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    onclick="openHarvestModal({{ $planting->planting_id }}, '{{ addslashes($planting->location?->name ?? '') }}', '{{ addslashes($planting->bed_label ?? '') }}')"
                                                    title="Catat Panen">
                                                <i class="fas fa-cut"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                    onclick="openLossModal({{ $planting->planting_id }}, '{{ addslashes($plant->name) }}', '{{ addslashes($planting->bed_label ?? '') }}', {{ $currentPlants }}, '{{ $planting->location?->planting_location_id ?? '' }}')"
                                                    title="Catat Kehilangan">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="markFailed({{ $planting->planting_id }})"
                                                    title="Catat Gagal Panen">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <a href="{{ $planting->location ? route('planting-locations.show', $planting->location) . '?tab=pelaporan&planting_id=' . $planting->planting_id : '#' }}" 
                                               class="btn btn-sm btn-outline-info" 
                                               title="Lihat Pelaporan">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-seedling fa-3x mb-3"></i>
                                            <p>Tidak ada penanaman aktif saat ini.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Telah Dipanen -->
        <div class="tab-pane fade" id="telah-dipanen">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Tabel Penanaman yang Telah Dipanen</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Lokasi Lahan</th>
                                    <th>Lokasi Tanam (Bed/Baris)</th>
                                    <th>Jumlah Ditanam</th>
                                    <th>Tanggal Tanam</th>
                                    <th>Tanggal Panen</th>
                                    <th>Jumlah Panen</th>
                                    <th>Nomor Batch Tanam</th>
                                    <th>Nomor Batch Panen</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($harvestedPlantings as $planting)
                                    @php
                                        $harvests = $planting->harvests->where('quantity', '>', 0)->sortByDesc('harvested_at');
                                        $totalHarvest = $harvests->sum('quantity');
                                        $latestHarvest = $harvests->first();
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $planting->location?->name ?? '-' }}</strong>
                                        </td>
                                        <td>{{ $planting->bed_label ?: '-' }}</td>
                                        <td>{{ number_format($planting->quantity_planted ?? 0, 0) }} {{ $plant->type->name ?? 'unit' }}</td>
                                        <td>{{ $planting->planted_at ? $planting->planted_at->format('d M Y') : '-' }}</td>
                                        <td>
                                            @if($latestHarvest && $latestHarvest->harvested_at)
                                                {{ $latestHarvest->harvested_at->format('d M Y') }}
                                                @if($harvests->count() > 1)
                                                    <br><small class="text-muted">({{ $harvests->count() }}x panen)</small>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($harvests->count() > 0)
                                                <strong>{{ number_format($totalHarvest, 2) }} {{ $latestHarvest->unit ?? 'kg' }}</strong>
                                                @if($harvests->count() > 1)
                                                    <br><small class="text-muted">Total dari {{ $harvests->count() }} panen</small>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td><span class="badge bg-primary">{{ $planting->planting_batch_number ?? '-' }}</span></td>
                                        <td>
                                            @if($latestHarvest)
                                                <span class="badge bg-info">{{ $latestHarvest->batch_no ?? '-' }}</span>
                                                @if($harvests->count() > 1)
                                                    <br><small class="text-muted">+{{ $harvests->count() - 1 }} batch lain</small>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ $planting->location ? route('planting-locations.show', $planting->location) : '#' }}" 
                                               class="btn btn-sm btn-outline-info" 
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        Belum ada penanaman yang telah dipanen.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Kehilangan -->
        <div class="tab-pane fade" id="kehilangan">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Tabel Penanaman dengan Kehilangan</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Lokasi Lahan</th>
                                    <th>Lokasi Tanam (Bed/Baris)</th>
                                    <th>Jumlah Ditanam</th>
                                    <th>Tanggal Tanam</th>
                                    <th>Total Kehilangan</th>
                                    <th>Jumlah Sisa</th>
                                    <th>Nomor Batch Tanam</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lostPlantings as $planting)
                                    @php
                                        $totalLosses = $planting->losses->sum('loss_amount');
                                        $remaining = ($planting->quantity_planted ?? 0) - $totalLosses;
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $planting->location?->name ?? '-' }}</strong>
                                        </td>
                                        <td>{{ $planting->bed_label ?: '-' }}</td>
                                        <td>{{ number_format($planting->quantity_planted ?? 0, 0) }} {{ $plant->type->name ?? 'unit' }}</td>
                                        <td>{{ $planting->planted_at ? $planting->planted_at->format('d M Y') : '-' }}</td>
                                        <td>
                                            <span class="text-warning">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                {{ number_format($totalLosses, 0) }} {{ $plant->type->name ?? 'unit' }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($remaining, 0) }} {{ $plant->type->name ?? 'unit' }}</td>
                                        <td><span class="badge bg-primary">{{ $planting->planting_batch_number ?? '-' }}</span></td>
                                        <td>
                                            <a href="{{ $planting->location ? route('planting-locations.show', $planting->location) : '#' }}" 
                                               class="btn btn-sm btn-outline-info" 
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        Belum ada penanaman dengan kehilangan.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Gagal Panen -->
        <div class="tab-pane fade" id="gagal-panen">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Tabel Penanaman yang Gagal Panen</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Lokasi Lahan</th>
                                    <th>Lokasi Tanam (Bed/Baris)</th>
                                    <th>Jumlah Ditanam</th>
                                    <th>Tanggal Tanam</th>
                                    <th>Tanggal Gagal</th>
                                    <th>Nomor Batch Tanam</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($failedPlantings as $planting)
                                    @php
                                        $failedHarvest = $planting->harvests->where('quantity', '<=', 0)->first();
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $planting->location?->name ?? '-' }}</strong>
                                        </td>
                                        <td>{{ $planting->bed_label ?: '-' }}</td>
                                        <td>{{ number_format($planting->quantity_planted ?? 0, 0) }} {{ $plant->type->name ?? 'unit' }}</td>
                                        <td>{{ $planting->planted_at ? $planting->planted_at->format('d M Y') : '-' }}</td>
                                        <td>
                                            @if($failedHarvest && $failedHarvest->harvested_at)
                                                {{ $failedHarvest->harvested_at->format('d M Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td><span class="badge bg-primary">{{ $planting->planting_batch_number ?? '-' }}</span></td>
                                        <td>
                                            <a href="{{ $planting->location ? route('planting-locations.show', $planting->location) : '#' }}" 
                                               class="btn btn-sm btn-outline-info" 
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Belum ada penanaman yang gagal panen.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Form Tanam Baru -->
<div class="modal fade" id="modalTanamBaru" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('planting-locations.plantings.store', ':locationId') }}" method="POST" id="formTanamBaru">
                @csrf
                <input type="hidden" name="plant_id" value="{{ $plant->plant_id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Tanam Baru - {{ $plant->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Lokasi Penanaman <span class="text-danger">*</span></label>
                        <select name="planting_location_id" id="select_planting_location" class="form-select" required onchange="updatePlantingLocationForm()">
                            <option value="">-- Pilih Lokasi Penanaman --</option>
                            @foreach($allPlantingLocations as $location)
                                <option value="{{ $location->id }}" data-format="{{ $location->planting_format }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="plantingFormFields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Nomor Batch Tanam <span class="text-danger">*</span></label>
                            <input type="text" name="planting_batch_number" id="planting_batch_number" class="form-control @error('planting_batch_number') is-invalid @enderror" 
                                   value="{{ old('planting_batch_number', 'TANAM-' . date('Y') . '-' . str_pad(\App\Models\Planting::whereYear('planted_at', date('Y'))->count() + 1, 3, '0', STR_PAD_LEFT)) }}" required>
                            <small class="text-muted">Nomor batch akan otomatis terisi, namun dapat diubah jika diperlukan</small>
                            @error('planting_batch_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Tanam <span class="text-danger">*</span></label>
                                <input type="date" name="planted_at" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Format Tanam</label>
                                <select name="planting_format" id="planting_format" class="form-select" onchange="togglePlantingFormatCustom()">
                                    <option value="">-- Pilih Format Tanam --</option>
                                    <option value="rumpun">Rumpun</option>
                                    <option value="batang">Batang</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3" id="planting_format_custom_container" style="display: none;">
                            <label class="form-label">Format Tanam (Lainnya)</label>
                            <input type="text" name="planting_format_custom" id="planting_format_custom" class="form-control" placeholder="Masukkan format tanam lainnya">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jumlah Tanam <span class="text-danger">*</span></label>
                                <input type="number" name="quantity_planted" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estimasi Tanggal Panen</label>
                                <input type="date" name="estimated_harvest_date" class="form-control">
                                <small class="text-muted">Estimasi kapan tanaman ini akan siap dipanen</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lokasi Tanam</label>
                            <input type="text" name="bed_label" id="bed_label" class="form-control" placeholder="Masukkan lokasi tanam">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="alert alert-info">
                            <small><i class="fas fa-info-circle me-1"></i>Detail lain seperti jarak tanam, hari panen, dll. akan otomatis diambil dari Katalog.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="btnSubmitTanam" disabled>Simpan Penanaman</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Catat Kehilangan -->
<div class="modal fade" id="modalCatatKehilangan" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST" id="formCatatKehilangan">
                @csrf
                <input type="hidden" name="planting_id" id="loss_planting_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="lossModalTitle">Catat Kehilangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="loss_date" id="loss_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Kehilangan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="loss_amount" id="loss_amount" class="form-control" step="0.01" min="0.01" required>
                            <span class="badge bg-secondary align-self-center ms-2" id="loss_current_plants">0 Tanaman Saat Ini</span>
                        </div>
                        <small class="text-muted">Masukkan jumlah tanaman yang hilang</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Kehilangan</label>
                        <select name="loss_reason" id="loss_reason" class="form-select">
                            <option value="">-- Pilih Alasan --</option>
                            <option value="penyakit">Penyakit</option>
                            <option value="hama">Hama</option>
                            <option value="cuaca">Cuaca Ekstrem</option>
                            <option value="kekeringan">Kekeringan</option>
                            <option value="banjir">Banjir</option>
                            <option value="hewan">Serangan Hewan</option>
                            <option value="human_error">Kesalahan Manusia</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="loss_description" class="form-control" rows="3" placeholder="Tambahkan catatan atau komentar..."></textarea>
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

<!-- Modal: Catat Panen -->
<div class="modal fade" id="harvestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('harvests.store') }}" method="POST">
                @csrf
                <input type="hidden" name="plant_id" value="{{ $plant->plant_id }}">
                <input type="hidden" name="planting_id" id="harvest_planting_id">
                <input type="hidden" name="planting_location_id" id="harvest_planting_location_id">
                
                <div class="modal-header">
                    <h5 class="modal-title">Formulir: Catat Panen (Otomatis)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Sumber Panen (Otomatis Terisi):</strong><br>
                        Lahan: <span id="harvest_location_name"></span><br>
                        Lokasi Tanam: <span id="harvest_bed_label"></span>
                    </div>

                    <div class="mb-3">
                        <label for="harvested_at" class="form-label">Tanggal Panen <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="harvested_at" name="harvested_at" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="batch_no" class="form-label">Nomor Batch (Panen) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="batch_no" name="batch_no" 
                               value="{{ 'PAN-' . date('Y') . '-' . str_pad(\App\Models\Harvest::whereYear('harvested_at', date('Y'))->count() + 1, 3, '0', STR_PAD_LEFT) }}" required>
                        <small class="text-muted">Nomor batch akan otomatis terisi, namun dapat diubah jika diperlukan</small>
                    </div>

                    <div class="mb-3">
                        <label for="quality" class="form-label">Kualitas / Ukuran (Opsional)</label>
                        <input type="text" class="form-control" id="quality" name="quality">
                    </div>

                    <div class="mb-3">
                        <label for="harvest_unit" class="form-label">Satuan Panen</label>
                        <select class="form-select" id="harvest_unit" name="harvest_unit">
                            <option value="">Pilih satuan</option>
                            <option value="kg">Kilogram (kg)</option>
                            <option value="ton">Ton</option>
                            <option value="gram">Gram</option>
                            <option value="butir">Butir/Biji</option>
                            <option value="pcs">Pcs</option>
                            <option value="batang">Batang</option>
                        </select>
                    </div>

                    <input type="hidden" name="source" id="source" value="">

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Jumlah Panen <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" id="quantity" name="quantity" required>
                            <span class="input-group-text" id="quantity_unit_label">kg</span>
                        </div>
                        <input type="hidden" id="unit" name="unit" value="kg">
                        <small class="text-muted">Masukkan jumlah panen</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catat Oleh</label>
                        <select class="form-select" id="recorded_by" name="recorded_by">
                            <option value="">Pilih user</option>
                            <option value="{{ auth()->user()->user_id }}" selected>{{ auth()->user()->name }} (Anda)</option>
                        </select>
                        <small class="text-muted">User yang mencatat panen ini</small>
                    </div>

                    <div class="mb-3">
                        <label for="note" class="form-label">Catatan</label>
                        <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="action" value="save_and_complete" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>Simpan dan Selesaikan Panen
                    </button>
                    <button type="submit" name="action" value="save_and_continue" class="btn btn-primary">
                        <i class="fas fa-arrow-right me-2"></i>Simpan dan Lanjutkan Penanaman
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
@php
$plantingsDataArray = $currentPlantings->mapWithKeys(function($p) {
    $locationUsers = collect();
    if ($p->location) {
        $locationUsers = $p->location->landManagerUsers->merge($p->location->landWorkerUsers)->unique('id');
    }
    return [$p->id => [
        'planting_location_id' => $p->planting_location_id, 
        'location_name' => $p->location->name ?? '', 
        'bed_label' => $p->bed_label ?? '',
        'users' => $locationUsers->map(function($u) {
            return ['id' => $u->id, 'name' => $u->name];
        })->values()->toArray()
    ]];
})->toArray();
@endphp
const plantingsData = @json($plantingsDataArray);

function updatePlantingLocationForm() {
    const select = document.getElementById('select_planting_location');
    const formFields = document.getElementById('plantingFormFields');
    const form = document.getElementById('formTanamBaru');
    const btnSubmit = document.getElementById('btnSubmitTanam');
    const bedLabel = document.getElementById('bed_label');
    const plantingBatchNumberInput = document.getElementById('planting_batch_number');
    
    if (select.value) {
        formFields.style.display = 'block';
        btnSubmit.disabled = false;
        
        // Update form action
        const locationId = select.value;
        form.action = form.action.replace(':locationId', locationId);
        
        // Generate batch number if field is empty
        if (plantingBatchNumberInput && (!plantingBatchNumberInput.value || plantingBatchNumberInput.value.trim() === '')) {
            const today = new Date();
            const year = today.getFullYear();
            @php
                $plantingCount = \App\Models\Planting::whereYear('planted_at', date('Y'))->count() + 1;
            @endphp
            const plantingCount = {{ $plantingCount }};
            const batchNo = 'TANAM-' + year + '-' + String(plantingCount).padStart(3, '0');
            plantingBatchNumberInput.value = batchNo;
        }
        
        // Update bed label placeholder based on planting format
        const selectedOption = select.options[select.selectedIndex];
        const plantingFormat = selectedOption.getAttribute('data-format');
        
        if (plantingFormat === 'ditanam_dalam_petak') {
            bedLabel.placeholder = 'Contoh: Bed 1, Bed 2, atau Petak 1-5';
        } else if (plantingFormat === 'row_crop') {
            bedLabel.placeholder = 'Contoh: Baris 1, Baris 2, atau Baris 1-5';
        } else {
            bedLabel.placeholder = 'Masukkan lokasi tanam';
        }
    } else {
        formFields.style.display = 'none';
        btnSubmit.disabled = true;
    }
}

// Auto-generate planting batch number when modal opens
document.addEventListener('DOMContentLoaded', function() {
    const modalTanamBaru = document.getElementById('modalTanamBaru');
    const plantingBatchNumberInput = document.getElementById('planting_batch_number');
    
    if (modalTanamBaru && plantingBatchNumberInput) {
        modalTanamBaru.addEventListener('show.bs.modal', function() {
            // Generate batch number if field is empty or has default value
            if (!plantingBatchNumberInput.value || plantingBatchNumberInput.value.trim() === '') {
                const today = new Date();
                const year = today.getFullYear();
                @php
                    $plantingCount = \App\Models\Planting::whereYear('planted_at', date('Y'))->count() + 1;
                @endphp
                const plantingCount = {{ $plantingCount }};
                const batchNo = 'TANAM-' + year + '-' + String(plantingCount).padStart(3, '0');
                plantingBatchNumberInput.value = batchNo;
            }
        });
    }
});

function togglePlantingFormatCustom() {
    const plantingFormat = document.getElementById('planting_format');
    const customContainer = document.getElementById('planting_format_custom_container');
    const customInput = document.getElementById('planting_format_custom');
    
    if (plantingFormat && plantingFormat.value === 'lainnya') {
        if (customContainer) customContainer.style.display = 'block';
        if (customInput) customInput.required = true;
    } else {
        if (customContainer) customContainer.style.display = 'none';
        if (customInput) {
            customInput.required = false;
            customInput.value = '';
        }
    }
}

function openHarvestModal(plantingId, locationName, bedLabel) {
    const plantingIdInput = document.getElementById('harvest_planting_id');
    const locationNameSpan = document.getElementById('harvest_location_name');
    const bedLabelSpan = document.getElementById('harvest_bed_label');
    const sourceInput = document.getElementById('source');
    const harvestModal = document.getElementById('harvestModal');
    
    if (!plantingIdInput || !locationNameSpan || !bedLabelSpan || !sourceInput || !harvestModal) {
        console.error('Modal elements not found');
        return;
    }
    
    plantingIdInput.value = plantingId;
    locationNameSpan.textContent = locationName;
    bedLabelSpan.textContent = bedLabel || '-';
    sourceInput.value = locationName + (bedLabel ? ' - ' + bedLabel : '');
    
    // Set planting_location_id from the planting data
    if (plantingsData && plantingsData[plantingId]) {
        const plantingLocationIdInput = document.getElementById('harvest_planting_location_id');
        if (plantingLocationIdInput) {
            plantingLocationIdInput.value = plantingsData[plantingId].planting_location_id;
        }
        
        // Update user dropdown
        const recordedBySelect = document.getElementById('recorded_by');
        if (recordedBySelect) {
            recordedBySelect.innerHTML = '<option value="">Pilih user</option>';
            
            const users = plantingsData[plantingId].users || [];
            users.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = user.name;
                if (user.id == '{{ auth()->user()->user_id }}') {
                    option.selected = true;
                }
                recordedBySelect.appendChild(option);
            });
            
            // Add current user if not in list
            const currentUserId = '{{ auth()->user()->user_id }}';
            if (!users.find(u => u.id == currentUserId)) {
                const option = document.createElement('option');
                option.value = currentUserId;
                option.textContent = '{{ auth()->user()->name }} (Anda)';
                option.selected = true;
                recordedBySelect.appendChild(option);
            }
        }
    }
    
    // Reset form fields
    const quantityInput = document.getElementById('quantity');
    const harvestUnitSelect = document.getElementById('harvest_unit');
    const unitInput = document.getElementById('unit');
    const quantityUnitLabel = document.getElementById('quantity_unit_label');
    
    if (quantityInput) quantityInput.value = '';
    if (harvestUnitSelect) harvestUnitSelect.value = '';
    if (unitInput) unitInput.value = 'kg';
    if (quantityUnitLabel) quantityUnitLabel.textContent = 'kg';
    
    new bootstrap.Modal(harvestModal).show();
}

function openLossModal(plantingId, plantName, bedLabel, currentPlants, plantingLocationId) {
    document.getElementById('loss_planting_id').value = plantingId;
    document.getElementById('lossModalTitle').textContent = 'Catat Kehilangan - ' + plantName + (bedLabel ? ' (' + bedLabel + ')' : '');
    document.getElementById('loss_current_plants').textContent = currentPlants + ' Tanaman Saat Ini';
    document.getElementById('loss_amount').max = currentPlants;
    document.getElementById('loss_amount').value = '';
    document.getElementById('loss_date').value = '{{ date('Y-m-d') }}';
    document.getElementById('loss_reason').value = '';
    document.getElementById('loss_description').value = '';
    
    // Update form action
    const form = document.getElementById('formCatatKehilangan');
    form.action = '{{ route('planting-locations.losses.store', ':id') }}'.replace(':id', plantingLocationId);
    
    new bootstrap.Modal(document.getElementById('modalCatatKehilangan')).show();
}

function markFailed(plantingId) {
    if (confirm('Apakah Anda yakin ingin menandai penanaman ini sebagai gagal panen?')) {
        fetch(`/plantings/${plantingId}/mark-failed`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan saat menandai gagal panen.');
        });
    }
}

// Sync unit from harvest_unit to hidden unit field
document.addEventListener('DOMContentLoaded', function() {
    const harvestUnitSelect = document.getElementById('harvest_unit');
    const unitInput = document.getElementById('unit');
    
    if (!harvestUnitSelect || !unitInput) {
        return; // Elements not found, skip initialization
    }
    
    function syncUnit() {
        const selectedUnit = harvestUnitSelect.value || 'kg';
        if (unitInput) {
            unitInput.value = selectedUnit;
        }
        // Update label satuan di field jumlah panen
        const quantityUnitLabel = document.getElementById('quantity_unit_label');
        if (quantityUnitLabel) {
            // Format label berdasarkan satuan yang dipilih
            const unitLabels = {
                'kg': 'kg',
                'ton': 'ton',
                'gram': 'gram',
                'butir': 'butir/biji',
                'pcs': 'pcs',
                'batang': 'batang'
            };
            quantityUnitLabel.textContent = unitLabels[selectedUnit] || selectedUnit;
        }
    }
    
    if (harvestUnitSelect) {
        harvestUnitSelect.addEventListener('change', syncUnit);
        // Sync on page load
        syncUnit();
    }
});

// Handle opening new planting modal from session (after save_and_continue harvest)
document.addEventListener('DOMContentLoaded', function() {
    @if(session('open_new_planting_modal'))
        // Open the new planting modal
        const modal = new bootstrap.Modal(document.getElementById('modalTanamBaru'));
        modal.show();
        
        // Pre-fill form data from previous planting
        @if(session('prefill_bed_label'))
            const bedLabelInput = document.querySelector('#modalTanamBaru input[name="bed_label"]');
            if (bedLabelInput) {
                bedLabelInput.value = '{{ session('prefill_bed_label') }}';
            }
        @endif
        
        @if(session('prefill_notes'))
            const notesTextarea = document.querySelector('#modalTanamBaru textarea[name="notes"]');
            if (notesTextarea) {
                notesTextarea.value = '{{ session('prefill_notes') }}';
            }
        @endif
    @endif
});
</script>
@endpush
@endsection
