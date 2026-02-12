@extends('layouts.app')

@section('title', 'Penanaman - ' . $plantingLocation->name . ' - SIBESTI')

@push('styles')
<style>
    .nav-pills .nav-link {
        transition: all 0.3s ease;
    }
    .nav-pills .nav-link:not(.active) {
        background-color:rgb(114, 132, 153) !important;
        color: #495057 !important;
        opacity: 1 !important;
    }
    .nav-pills .nav-link.active {
        background-color:rgb(0, 83, 207) !important;
        color:rgb(141, 137, 137) !important;
        opacity: 1 !important;
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
        <a class="nav-link active" href="{{ route('planting-locations.plantings.index', $plantingLocation) }}">
            <i class="fas fa-seedling me-1"></i>Penanaman
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('planting-locations.planting-history', $plantingLocation) }}">
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
            <h6 class="mb-0">Penanaman - {{ $plantingLocation->name }}</h6>
        </div>
        @if(auth()->user()->isAdmin() || auth()->user()->canManagePlantingLocation($plantingLocation))
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTanamBaru">
                <i class="fas fa-plus me-2"></i>Tanam Baru
            </button>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Lokasi Tanam</th>
                    <th>Tanaman</th>
                    <th>Nomor Batch Tanam</th>
                    <th>Jumlah Tanam</th>
                    <th>Tanggal Tanam</th>
                    <th>Est. Panen</th>
                    <th>Progres</th>
                    <th width="150">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activePlantings as $planting)
                    @php
                        $daysSince = $planting->planted_at ? $planting->planted_at->diffInDays(now()) : 0;
                        // Use estimated_harvest_date if available, otherwise calculate from days_to_harvest
                        $estHarvest = null;
                        if ($planting->estimated_harvest_date) {
                            $estHarvest = $planting->estimated_harvest_date;
                        } else {
                            $daysToHarvest = $planting->days_to_harvest ?? ($planting->plant->type->days_to_harvest ?? 0);
                            if ($planting->planted_at && $daysToHarvest > 0) {
                                $estHarvest = $planting->planted_at->copy()->addDays($daysToHarvest);
                            }
                        }
                    @endphp
                    <tr style="cursor: pointer;" onclick="window.location.href='{{ route('planting-locations.plantings.reports', [$plantingLocation, $planting]) }}'">
                        <td>{{ $planting->bed_label ?: '-' }}</td>
                        <td><strong>{{ $planting->plant->name }}</strong><br><small class="text-muted">{{ $planting->plant->variety ?: 'Tidak ada varietas' }}</small></td>
                        <td><span class="badge bg-primary">{{ $planting->planting_batch_number ?? '-' }}</span></td>
                        <td>{{ number_format($planting->quantity_planted ?? 0, 0) }}</td>
                        <td>{{ $planting->planted_at ? $planting->planted_at->format('d M Y') : '-' }}</td>
                        <td>
                            @if($estHarvest)
                                <span class="text-info">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    {{ $estHarvest->format('d M Y') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $daysSince }} hari sejak tanam</small>
                        </td>
                        <td onclick="event.stopPropagation();">
                            <div class="btn-group btn-group-sm">
                                @if(auth()->user()->isAdmin() || auth()->user()->canManagePlantingLocation($plantingLocation))
                                    <button type="button" class="btn btn-outline-success" 
                                            onclick="openHarvestModal('{{ $planting->planting_id }}', '{{ addslashes($planting->plant->name) }}', '{{ addslashes($planting->bed_label ?? '') }}')"
                                            title="Catat Panen">
                                        <i class="fas fa-cut"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-warning" 
                                            onclick="openLossModal('{{ $planting->planting_id }}', '{{ addslashes($planting->plant->name) }}', '{{ addslashes($planting->bed_label ?? '') }}', {{ $planting->quantity_planted - $planting->losses->sum('loss_amount') }})"
                                            title="Catat Kehilangan">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="markFailed('{{ $planting->planting_id }}')"
                                            title="Gagal Panen">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                                <a href="{{ route('planting-locations.plantings.reports', [$plantingLocation, $planting]) }}" class="btn btn-outline-primary" title="Lihat Pelaporan">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">Belum ada penanaman aktif.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Form Tanam Baru -->
<div class="modal fade" id="modalTanamBaru" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('planting-locations.plantings.store', $plantingLocation) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tanam Baru (di {{ $plantingLocation->name }})</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Tanaman (dari Katalog) <span class="text-danger">*</span></label>
                        <select name="plant_id" class="form-select" required>
                            <option value="">-- Pilih Tanaman --</option>
                            @foreach($allPlants as $plant)
                                <option value="{{ $plant->plant_id }}">{{ $plant->name }} @if($plant->variety) - {{ $plant->variety }} @endif</option>
                            @endforeach
                        </select>
                    </div>
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
                            <label class="form-label">Jumlah Tanam <span class="text-danger">*</span></label>
                            <input type="number" name="quantity_planted" class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estimasi Tanggal Panen</label>
                            <input type="date" name="estimated_harvest_date" class="form-control">
                            <small class="text-muted">Estimasi kapan tanaman ini akan siap dipanen</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Luas Lahan (ha)</label>
                            <input type="number" name="area_ha" class="form-control" step="0.01" min="0" placeholder="0.00">
                            <small class="text-muted">Luas lahan dalam hektar</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estimasi Hasil Panen</label>
                            <input type="number" name="expected_yield_per_hectare" class="form-control" step="0.01" min="0" placeholder="0.00">
                            <small class="text-muted">Estimasi hasil panen per hektar</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Satuan Panen</label>
                            <select name="harvest_unit" class="form-select">
                                <option value="">-- Pilih Satuan --</option>
                                <option value="kilogram">Kilogram (kg)</option>
                                <option value="ton">Ton</option>
                                <option value="gram">Gram</option>
                                <option value="ikat">Ikat / Gulungan</option>
                                <option value="tandan">Tandan / Ikat</option>
                                <option value="batang">Batang / Kepala</option>
                                <option value="satuan">Satuan / Jumlah</option>
                            </select>
                            <small class="text-muted">Satuan hasil panen</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi Tanam</label>
                        @if($plantingLocation->planting_format === 'ditanam_dalam_petak')
                            <input type="text" name="bed_label" class="form-control" placeholder="Contoh: Bed 1, Bed 2, atau Petak 1-5">
                        @elseif($plantingLocation->planting_format === 'row_crop')
                            <input type="text" name="bed_label" class="form-control" placeholder="Contoh: Baris 1, Baris 2, atau Baris 1-5">
                        @else
                            <input type="text" name="bed_label" class="form-control" placeholder="Masukkan lokasi tanam">
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle me-1"></i>Detail lain seperti jarak tanam, hari panen, dll. akan otomatis diambil dari Katalog.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Penanaman</button>
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
                <input type="hidden" name="plant_id" id="harvest_plant_id">
                <input type="hidden" name="planting_id" id="harvest_planting_id">
                <input type="hidden" name="planting_location_id" value="{{ $plantingLocation->planting_location_id }}">
                <input type="hidden" name="from_planting_location" value="1">
                <input type="hidden" name="unit" id="unit" value="kg">
                <div class="modal-header">
                    <h5 class="modal-title">Formulir: Catat Panen (Otomatis)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Sumber Panen (Otomatis Terisi):</strong><br>
                        Lahan: <span id="harvest_location_name">{{ $plantingLocation->name }}</span><br>
                        Lokasi Tanam: <span id="harvest_bed_label">-</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Panen <span class="text-danger">*</span></label>
                        <input type="date" name="harvested_at" class="form-control" id="harvested_at" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor Batch (Panen) <span class="text-danger">*</span></label>
                        <input type="text" name="batch_no" class="form-control" id="harvest_batch_no" 
                               value="{{ 'PAN-' . date('Y') . '-' . str_pad(\App\Models\Harvest::whereYear('harvested_at', date('Y'))->count() + 1, 3, '0', STR_PAD_LEFT) }}" required>
                        <small class="text-muted">Nomor batch akan otomatis terisi, namun dapat diubah jika diperlukan</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kualitas / Ukuran (Opsional)</label>
                        <input type="text" name="quality" class="form-control" id="harvest_quality">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Satuan Panen</label>
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
                    <div class="mb-3">
                        <label class="form-label">Jumlah Panen <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" id="quantity" name="quantity" required>
                            <span class="input-group-text" id="quantity_unit_label">kg</span>
                        </div>
                        <small class="text-muted">Masukkan jumlah panen</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catat Oleh</label>
                        <select class="form-select" id="recorded_by" name="recorded_by">
                            <option value="">Pilih user</option>
                            <option value="{{ auth()->user()->user_id }}" selected>{{ auth()->user()->name }} (Anda)</option>
                            @php
                                $locationUsers = $plantingLocation->landManagerUsers->merge($plantingLocation->landWorkerUsers)->unique('user_id')->sortBy('name');
                            @endphp
                            @foreach($locationUsers as $user)
                                @if($user->user_id != auth()->user()->user_id)
                                    <option value="{{ $user->user_id }}">{{ $user->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <small class="text-muted">User yang mencatat panen ini</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="note" class="form-control" id="harvest_note" rows="3"></textarea>
                    </div>
                    <input type="hidden" name="source" id="harvest_source_hidden">
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

<!-- Modal: Catat Kehilangan -->
<div class="modal fade" id="lossModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('planting-locations.losses.store', $plantingLocation) }}" method="POST">
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

@push('scripts')
<script>
@php
// Prepare plantings data with plant_id for JavaScript
$plantingsDataForHarvest = $activePlantings->mapWithKeys(function($p) {
    return [$p->planting_id => [
        'plant_id' => $p->plant_id,
        'plant_name' => $p->plant->name,
        'bed_label' => $p->bed_label ?? ''
    ]];
})->toArray();
@endphp
const plantingsDataForHarvest = @json($plantingsDataForHarvest);

function openHarvestModal(plantingId, plantName, bedLabel) {
    // Set planting_id
    document.getElementById('harvest_planting_id').value = plantingId;
    
    // Get plant_id from plantings data
    const plantingData = plantingsDataForHarvest[plantingId];
    if (plantingData) {
        document.getElementById('harvest_plant_id').value = plantingData.plant_id;
    }
    
    // Set bed label in alert box
    const bedLabelDisplay = bedLabel || '-';
    document.getElementById('harvest_bed_label').textContent = bedLabelDisplay;
    
    // Set source for hidden field
    const source = plantName + (bedLabel ? ' - ' + bedLabel : '');
    document.getElementById('harvest_source_hidden').value = source;
    
    // Generate batch number
    const today = new Date();
    const year = today.getFullYear();
    @php
        $harvestCount = \App\Models\Harvest::whereYear('harvested_at', date('Y'))->count() + 1;
    @endphp
    const harvestCount = {{ $harvestCount }};
    const batchNo = 'PAN-' + year + '-' + String(harvestCount).padStart(3, '0');
    document.getElementById('harvest_batch_no').value = batchNo;
    
    // Reset form fields
    document.getElementById('harvested_at').value = '{{ date('Y-m-d') }}';
    document.getElementById('quantity').value = '';
    document.getElementById('harvest_quality').value = '';
    document.getElementById('harvest_note').value = '';
    document.getElementById('harvest_unit').value = '';
    document.getElementById('quantity_unit_label').textContent = 'kg';
    document.getElementById('unit').value = 'kg';
    
    // Setup unit label update when harvest_unit changes
    const harvestUnitSelect = document.getElementById('harvest_unit');
    const quantityUnitLabel = document.getElementById('quantity_unit_label');
    const unitHidden = document.getElementById('unit');
    
    // Remove existing event listeners by cloning the element
    const newHarvestUnitSelect = harvestUnitSelect.cloneNode(true);
    harvestUnitSelect.parentNode.replaceChild(newHarvestUnitSelect, harvestUnitSelect);
    
    // Add event listener for unit change
    document.getElementById('harvest_unit').addEventListener('change', function() {
        const selectedUnit = this.value;
        const unitLabels = {
            'kg': 'kg',
            'ton': 'ton',
            'gram': 'gram',
            'butir': 'butir',
            'pcs': 'pcs',
            'batang': 'batang'
        };
        const unitLabel = unitLabels[selectedUnit] || 'kg';
        quantityUnitLabel.textContent = unitLabel;
        unitHidden.value = selectedUnit || 'kg';
    });
    
    new bootstrap.Modal(document.getElementById('harvestModal')).show();
}

function openLossModal(plantingId, plantName, bedLabel, remaining) {
    document.getElementById('loss_planting_id').value = plantingId;
    document.getElementById('lossModalTitle').textContent = 'Catat Kehilangan - ' + plantName + (bedLabel ? ' (' + bedLabel + ')' : '');
    document.getElementById('loss_current_plants').textContent = remaining + ' Tanaman Saat Ini';
    document.getElementById('loss_amount').max = remaining;
    document.getElementById('loss_amount').value = '';
    document.getElementById('loss_date').value = '{{ date('Y-m-d') }}';
    document.getElementById('loss_reason').value = '';
    document.getElementById('loss_description').value = '';
    
    new bootstrap.Modal(document.getElementById('lossModal')).show();
}

function markFailed(plantingId) {
    if (confirm('Apakah Anda yakin ingin menandai penanaman ini sebagai gagal panen?')) {
        fetch(`/planting-locations/{{ $plantingLocation->planting_location_id }}/plantings/${plantingId}/mark-failed`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok || response.redirected) {
                window.location.reload();
            } else {
                alert('Gagal menandai penanaman sebagai gagal panen.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal menandai penanaman sebagai gagal panen.');
        });
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
    
    // Sync unit from harvest_unit to hidden unit field
    const harvestUnitSelect = document.getElementById('harvest_unit');
    const unitInput = document.getElementById('unit');
    
    if (!harvestUnitSelect || !unitInput) {
        return;
    }
    
    function syncUnit() {
        const selectedUnit = harvestUnitSelect.value || 'kg';
        if (unitInput) {
            unitInput.value = selectedUnit;
        }
        const quantityUnitLabel = document.getElementById('quantity_unit_label');
        if (quantityUnitLabel) {
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
        @if(session('prefill_plant_id'))
            const plantSelect = document.querySelector('#modalTanamBaru select[name="plant_id"]');
            if (plantSelect) {
                plantSelect.value = '{{ session('prefill_plant_id') }}';
            }
        @endif
        
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

