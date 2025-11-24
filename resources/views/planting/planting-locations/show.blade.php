@extends('layouts.app')

@section('title', 'Detail Lokasi Penanaman - ' . $plantingLocation->name . ' - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $plantingLocation->name }}</h4>
        @if($plantingLocation->baseLocation)
            <small class="text-muted">{{ $plantingLocation->baseLocation->name }}</small>
        @endif
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('planting-locations.edit', $plantingLocation) }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Edit Detail
        </a>
        <a href="{{ route('planting-locations.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#detail"><i class="fas fa-info-circle me-1"></i>Detail</a></li>
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#penanaman"><i class="fas fa-seedling me-1"></i>Penanaman</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#riwayat"><i class="fas fa-history me-1"></i>Riwayat Penanaman</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#perawatan"><i class="fas fa-first-aid me-1"></i>Perawatan</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#nutrisi"><i class="fas fa-flask me-1"></i>Nutrisi</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tugas"><i class="fas fa-tasks me-1"></i>Tugas</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#catatan"><i class="fas fa-sticky-note me-1"></i>Catatan</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#foto"><i class="fas fa-camera me-1"></i>Foto</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#file"><i class="fas fa-file me-1"></i>File</a></li>
</ul>

<div class="tab-content p-3 bg-white border border-top-0 rounded-bottom">
    <!-- Tab: Detail -->
    <div class="tab-pane fade" id="detail">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('planting-locations.edit', $plantingLocation) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit me-2"></i>Edit Detail Lokasi
            </a>
        </div>

        @php
            $lightLabels = [
                'sinar_matahari_penuh' => 'Sinar Matahari Penuh',
                'sinar_matahari_penuh_hingga_sebagian' => 'Sinar Matahari Penuh hingga Sebagian',
                'sinar_matahari_sebagian' => 'Sinar Matahari Sebagian',
                'matahari_hingga_setengah_teduh' => 'Matahari hingga Setengah Teduh',
                'setengah_teduh' => 'Setengah Teduh',
                'teduh_sepenuhnya' => 'Teduh Sepenuhnya',
            ];
            $lightLabel = $lightLabels[$plantingLocation->light_condition] ?? $plantingLocation->light_condition;
            $formatLabel = $plantingLocation->planting_format === 'lainnya' && $plantingLocation->planting_format_custom
                ? $plantingLocation->planting_format_custom
                : str_replace('_', ' ', $plantingLocation->planting_format);
        @endphp

        <div class="row">
            <div class="col-md-4">
                @if($plantingLocation->primary_photo_path)
                    <img src="{{ Storage::disk('public')->url($plantingLocation->primary_photo_path) }}" alt="Foto Lahan" class="img-fluid rounded mb-3" style="object-fit: cover; width: 100%; max-height: 220px;">
                @endif

                <div class="mb-2"><strong>Tipe Lokasi:</strong> {{ str_replace('_',' ', $plantingLocation->location_type) }}</div>
                <div class="mb-2"><strong>Format Penanaman:</strong> {{ $formatLabel ?: '-' }}</div>
                <div class="mb-2"><strong>Lokasi (Ringkas):</strong> {{ $plantingLocation->location_summary ?: '-' }}</div>
                @if($plantingLocation->google_maps_link)
                    <div class="mb-2">
                        <a href="{{ $plantingLocation->google_maps_link }}" target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-map-marker-alt me-1"></i> Buka Google Maps
                        </a>
                    </div>
                @endif
            </div>
            <div class="col-md-4">
                @if($plantingLocation->baseLocation)
                    <div class="mb-2"><strong>Lokasi Master:</strong> {{ $plantingLocation->baseLocation->name }}</div>
                @endif
                <div class="mb-2"><strong>Ukuran Lahan:</strong> {{ $plantingLocation->map_size ? $plantingLocation->map_size . ' Ha' : '-' }}</div>
                <div class="mb-2"><strong>Ketinggian:</strong> {{ $plantingLocation->elevation_masl ? $plantingLocation->elevation_masl . ' mdpl' : '-' }}</div>
                <div class="mb-2"><strong>Kondisi Cahaya:</strong> {{ $lightLabel ?: '-' }}</div>
                <div class="mb-2"><strong>Sumber Air:</strong> {{ $plantingLocation->water_source ?: '-' }}</div>
                <div class="mb-2"><strong>Tipe Tanah:</strong> {{ $plantingLocation->soil_type ?: '-' }}</div>
            </div>
            <div class="col-md-4">
                <div class="mb-2"><strong>Status Lahan:</strong> {{ $plantingLocation->land_status ?: '-' }}</div>
                <div class="mb-2"><strong>Status Kepemilikan:</strong> {{ $plantingLocation->ownership_status ?: '-' }}</div>
                <div class="mb-2"><strong>Penanggung Jawab:</strong>
                    @if($plantingLocation->responsibleContacts->isNotEmpty())
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            @foreach($plantingLocation->responsibleContacts as $contact)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    {{ $contact->full_name }}@if($contact->organization) <small class="text-muted"> ({{ $contact->organization }})</small>@endif
                                </span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted">Belum ditetapkan</span>
                    @endif
                </div>
                <div class="mb-2"><strong>Penanggung Jawab Lahan:</strong>
                    @if($plantingLocation->landManagerContacts->isNotEmpty())
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            @foreach($plantingLocation->landManagerContacts as $contact)
                                <span class="badge bg-info-subtle text-info border border-info-subtle">
                                    {{ $contact->full_name }}@if($contact->organization) <small class="text-muted"> ({{ $contact->organization }})</small>@endif
                                </span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted">Belum ditetapkan</span>
                    @endif
                </div>
                <div class="mb-2"><strong>Pekerja Lahan:</strong>
                    @if($plantingLocation->landWorkerContacts->isNotEmpty())
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            @foreach($plantingLocation->landWorkerContacts as $contact)
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                    {{ $contact->full_name }}@if($contact->organization) <small class="text-muted"> ({{ $contact->organization }})</small>@endif
                                </span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted">Belum ditetapkan</span>
                    @endif
                </div>
                <div class="mb-2"><strong>Jumlah Petak:</strong> {{ $plantingLocation->num_beds ?: '-' }}</div>
                @if($plantingLocation->bed_length_m || $plantingLocation->bed_width_m)
                    <div class="mb-2"><strong>Ukuran Petak:</strong> 
                        @if($plantingLocation->bed_length_m && $plantingLocation->bed_width_m)
                            {{ $plantingLocation->bed_length_m }}m x {{ $plantingLocation->bed_width_m }}m
                        @else
                            {{ $plantingLocation->bed_length_m ?: $plantingLocation->bed_width_m }}m
                        @endif
                    </div>
                @endif
            </div>
        </div>
        @if($plantingLocation->administrative_address)
            <div class="mt-3"><strong>Alamat Administratif:</strong><br>{{ $plantingLocation->administrative_address }}</div>
        @endif
        @if($plantingLocation->description)
            <div class="mt-3"><strong>Deskripsi:</strong><br>{{ $plantingLocation->description }}</div>
        @endif
    </div>

    <!-- Tab: Penanaman (Default) -->
    <div class="tab-pane fade show active" id="penanaman">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-3">
                <h6 class="mb-0">Penanaman Saat Ini - {{ $plantingLocation->name }}</h6>
                <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='?year=' + this.value">
                    @foreach($plantingYears as $py)
                        <option value="{{ $py }}" {{ $py == $year ? 'selected' : '' }}>{{ $py }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTanamBaru">
                <i class="fas fa-plus me-2"></i>Tanam Baru
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Lokasi Tanam</th>
                        <th>Tanaman</th>
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
                            $daysToHarvest = $planting->days_to_harvest ?? ($planting->plant->type->days_to_harvest ?? 0);
                            $progress = $daysToHarvest > 0 ? min(100, ($daysSince / $daysToHarvest) * 100) : ($daysSince > 0 ? 50 : 0);
                            $estHarvest = $planting->planted_at && $daysToHarvest > 0 
                                ? $planting->planted_at->copy()->addDays($daysToHarvest) 
                                : null;
                            $statusColor = $progress >= 100 ? 'success' : ($progress >= 75 ? 'warning' : 'info');
                        @endphp
                        <tr>
                            <td>{{ $planting->bed_label ?: '-' }}</td>
                            <td><strong>{{ $planting->plant->name }}</strong><br><small class="text-muted">{{ $planting->plant->variety ?: 'Tidak ada varietas' }}</small></td>
                            <td>{{ number_format($planting->quantity_planted ?? 0, 0) }}</td>
                            <td>{{ $planting->planted_at ? $planting->planted_at->format('d M Y') : '-' }}</td>
                            <td>{{ $estHarvest ? $estHarvest->format('d M Y') : '-' }}</td>
                            <td>
                                <div class="progress mb-1" style="height: 20px;">
                                    <div class="progress-bar bg-{{ $statusColor }}" role="progressbar" style="width: {{ $progress }}%">
                                        {{ number_format($progress, 0) }}%
                                    </div>
                                </div>
                                <small class="text-muted">{{ $daysSince }} hari</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-success" 
                                            onclick="openHarvestModal({{ $planting->id }}, '{{ addslashes($planting->plant->name) }}', '{{ addslashes($planting->bed_label ?? '') }}')"
                                            title="Catat Panen">
                                        <i class="fas fa-cut"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-warning" 
                                            onclick="openLossModal({{ $planting->id }}, '{{ addslashes($planting->plant->name) }}', '{{ addslashes($planting->bed_label ?? '') }}', {{ $planting->quantity_planted - $planting->losses->sum('loss_amount') }})"
                                            title="Catat Kehilangan">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="markFailed({{ $planting->id }})"
                                            title="Gagal Panen">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <a href="{{ route('plantings.show', $planting) }}" class="btn btn-outline-primary" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Belum ada penanaman aktif.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab: Riwayat Penanaman -->
    <div class="tab-pane fade" id="riwayat">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-3">
                <h6 class="mb-0">Riwayat Penanaman</h6>
                <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='?year=' + this.value">
                    @foreach($plantingYears as $py)
                        <option value="{{ $py }}" {{ $py == $year ? 'selected' : '' }}>{{ $py }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Lokasi Tanam</th>
                        <th>Tanaman</th>
                        <th>Jumlah Tanam</th>
                        <th>Tanggal Tanam</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historicalPlantings as $planting)
                        <tr>
                            <td>{{ $planting->bed_label ?: '-' }}</td>
                            <td><strong>{{ $planting->plant->name }}</strong></td>
                            <td>{{ number_format($planting->quantity_planted ?? 0, 0) }}</td>
                            <td>{{ $planting->planted_at ? $planting->planted_at->format('d M Y') : '-' }}</td>
                            <td>
                                @if($planting->harvest)
                                    @if($planting->harvest->quantity > 0)
                                        <span class="badge bg-success">Panen</span>
                                    @else
                                        <span class="badge bg-danger">Gagal</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Selesai</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('plantings.show', $planting) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Belum ada riwayat penanaman.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab: Perawatan -->
    <div class="tab-pane fade" id="perawatan">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Riwayat Perawatan - {{ $plantingLocation->name }}</h6>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalPerawatanBaru">
                <i class="fas fa-plus me-1"></i>Tambahkan Perawatan
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal Perawatan</th>
                        <th>Tipe Perawatan</th>
                        <th>Produk/Detail</th>
                        <th>Petugas</th>
                        <th>Asosiasi</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($treatments as $treatment)
                        <tr>
                            <td>{{ $treatment->treatment_date ? $treatment->treatment_date->format('d M Y') : '-' }}</td>
                            <td><span class="badge bg-info">{{ $treatment->treatment_type }}</span></td>
                            <td>{{ $treatment->product_detail ?: '-' }}</td>
                            <td>{{ $treatment->technician ?: '(Unassigned)' }}</td>
                            <td>
                                @if($treatment->planting_id)
                                    @php
                                        $planting = \App\Models\Planting::find($treatment->planting_id);
                                    @endphp
                                    @if($planting && $planting->plant)
                                        {{ $planting->plant->name }} ({{ $planting->bed_label ?? '-' }})
                                    @else
                                        <span class="text-muted">Umum (Lahan Ini)</span>
                                    @endif
                                @else
                                    <span class="text-muted">Umum (Lahan Ini)</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('planting-locations.treatments.edit', [$plantingLocation, $treatment]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> Lihat/Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Belum ada data perawatan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab: Nutrisi -->
    <div class="tab-pane fade" id="nutrisi">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Riwayat Nutrisi - {{ $plantingLocation->name }}</h6>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalNutrisiBaru">
                <i class="fas fa-plus me-1"></i>Tambahkan Nutrisi
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal Penerapan</th>
                        <th>Produk yang Diterapkan</th>
                        <th>Metode Aplikasi</th>
                        <th>N-P-K</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nutrients as $nutrient)
                        <tr>
                            <td>{{ $nutrient->application_date ? $nutrient->application_date->format('d M Y') : '-' }}</td>
                            <td><strong>{{ $nutrient->product_applied }}</strong></td>
                            <td>{{ $nutrient->application_method }}</td>
                            <td>
                                @if($nutrient->nitrogen_n || $nutrient->phosphorus_p || $nutrient->potassium_k)
                                    {{ $nutrient->nitrogen_n ?: '-' }}-{{ $nutrient->phosphorus_p ?: '-' }}-{{ $nutrient->potassium_k ?: '-' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('planting-locations.nutrients.edit', [$plantingLocation, $nutrient]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> Lihat/Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Belum ada data nutrisi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab: Tugas -->
    <div class="tab-pane fade" id="tugas">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Tugas untuk Lahan: {{ $plantingLocation->name }}</h6>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalGunakanTemplate">
                    <i class="fas fa-file-alt me-1"></i>Gunakan Template
                </button>
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTugasBaru">
                    <i class="fas fa-plus me-1"></i>Tambah Tugas
                </button>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm" placeholder="Search Tasks..." id="searchTasks">
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-sm" id="filterStatus" onchange="filterTasks()">
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="dilakukan" {{ $statusFilter === 'dilakukan' ? 'selected' : '' }}>To Do</option>
                    <option value="dalam_progress" {{ $statusFilter === 'dalam_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="selesai" {{ $statusFilter === 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            <div class="col-md-5">
                <select class="form-select form-select-sm" id="filterAssignee" onchange="filterTasks()">
                    <option value="all" {{ $assigneeFilter === 'all' ? 'selected' : '' }}>Semua Pengguna</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $assigneeFilter == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" id="selectAll"></th>
                        <th>Tugas (Title)</th>
                        <th>Asosiasi ke Penanaman</th>
                        <th>Jatuh Tempo</th>
                        <th>Prioritas</th>
                        <th>Status</th>
                        <th>Ditugaskan</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr>
                            <td><input type="checkbox" class="task-checkbox" value="{{ $task->id }}"></td>
                            <td>
                                <strong>{{ $task->title }}</strong>
                                @if($task->description)
                                    <br><small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($task->planting_id && $task->plant)
                                    {{ $task->plant->name }} ({{ $task->plant->bed_label ?? '-' }})
                                @else
                                    <span class="text-muted">Umum (Lahan Ini)</span>
                                @endif
                            </td>
                            <td>
                                {{ $task->due_date ? $task->due_date->format('d M Y') : '-' }}
                                @if($task->isOverdue())
                                    <span class="badge bg-danger">Terlewat</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $task->new_priority === 'tertinggi' || $task->new_priority === 'tinggi' ? 'danger' : ($task->new_priority === 'medium' ? 'warning' : 'secondary') }}">
                                    {{ $task->priority_label }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $task->new_status === 'selesai' ? 'success' : ($task->new_status === 'dalam_progress' ? 'info' : 'secondary') }}">
                                    {{ $task->status_label }}
                                </span>
                            </td>
                            <td>{{ $task->assignedUser ? $task->assignedUser->name : '(Unassigned)' }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('tasks.edit', ['task' => $task, 'return_to_planting_location' => $plantingLocation->id]) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($task->new_status !== 'selesai')
                                        <form action="{{ route('tasks.update', $task) }}" method="POST" class="d-inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="new_status" value="selesai">
                                            <button type="submit" class="btn btn-outline-success" title="Tandai Selesai">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                            </div>
                        </td>
                    </tr>
                @empty
                        <tr><td colspan="8" class="text-center text-muted">Belum ada tugas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab: Catatan -->
    <div class="tab-pane fade" id="catatan">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Catatan untuk Lahan: {{ $plantingLocation->name }}</h6>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalCatatanBaru">
                <i class="fas fa-plus me-1"></i>Tambah Catatan Baru
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Judul/Deskripsi Singkat</th>
                        <th>Kata Kunci</th>
                        <th>Pembuat</th>
                        <th>Lampiran</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notes as $note)
                        <tr>
                            <td>{{ $note->note_date->format('d M Y') }}</td>
                            <td>
                                <strong>{{ $note->title ?: 'Catatan' }}</strong>
                                @if($note->description)
                                    <br><small class="text-muted">{{ Str::limit($note->description, 100) }}</small>
                                @endif
                            </td>
                            <td>{{ $note->keywords ?: '-' }}</td>
                            <td>{{ $note->user ? $note->user->name : '-' }}</td>
                            <td>
                                @if($note->attachment_path)
                                    <span class="badge bg-success">Ya</span>
                                @else
                                    <span class="text-muted">Tidak</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewNote({{ $note->id }})">
                                    <i class="fas fa-eye"></i> Lihat
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Belum ada catatan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab: Foto -->
    <div class="tab-pane fade" id="foto">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Galeri Foto - {{ $plantingLocation->name }}</h6>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalUnggahFoto">
                <i class="fas fa-plus me-1"></i>Unggah Foto
            </button>
        </div>

        <div class="row g-3">
            @forelse($photos as $photo)
                <div class="col-md-3">
                    <div class="card">
                        <img src="{{ Storage::url($photo->file_path) }}" class="card-img-top" alt="Foto" style="height: 200px; object-fit: cover;">
                        <div class="card-body p-2">
                            <small class="text-muted d-block">{{ $photo->taken_at ? $photo->taken_at->format('d M Y') : $photo->created_at->format('d M Y') }}</small>
                            @if($photo->description)
                                <small class="text-muted">{{ Str::limit($photo->description, 30) }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-muted text-center">Belum ada foto.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Tab: File -->
    <div class="tab-pane fade" id="file">
        <p class="text-muted">File (akan ditambahkan).</p>
    </div>
</div>

<!-- Modal: Tanam Baru -->
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
                                <option value="{{ $plant->id }}">{{ $plant->name }} @if($plant->variety) - {{ $plant->variety }} @endif</option>
                            @endforeach
                        </select>
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
<div class="modal fade" id="modalCatatPanen" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('harvests.store') }}" method="POST">
                @csrf
                <input type="hidden" name="planting_id" id="harvest_planting_id">
                <input type="hidden" name="planting_location_id" value="{{ $plantingLocation->id }}">
                <input type="hidden" name="from_planting_location" value="1">
                <input type="hidden" name="unit" value="kg">
                <div class="modal-header">
                    <h5 class="modal-title">Catat Panen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Panen</label>
                        <input type="date" name="harvested_at" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor Batch (Otomatis)</label>
                        <input type="text" class="form-control" id="harvest_batch_no" readonly>
                        <input type="hidden" name="batch_no" id="harvest_batch_no_hidden">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sumber Panen (Otomatis)</label>
                        <input type="text" class="form-control" id="harvest_source" readonly>
                        <input type="hidden" name="source" id="harvest_source_hidden">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kualitas/Ukuran (Opsional)</label>
                        <input type="text" name="quality" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Panen (kg)</label>
                        <input type="number" name="quantity" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="note" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Panen</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Catat Kehilangan -->
<div class="modal fade" id="modalCatatKehilangan" tabindex="-1">
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

<!-- Modal: Tugas Baru -->
<div class="modal fade" id="modalTugasBaru" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="{{ route('planting-locations.tasks.store', $plantingLocation) }}" method="POST" enctype="multipart/form-data" id="taskForm">
                @csrf
                <input type="hidden" name="action_type" id="actionType" value="create">
                <div class="modal-header">
                    <h5 class="modal-title">Tugas Baru untuk Lahan: {{ $plantingLocation->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Judul Tugas <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="association" class="form-label">Asosiasi Tugas <span class="text-danger">*</span></label>
                                <select class="form-select @error('association') is-invalid @enderror" id="association" name="association" required onchange="updateTaskAssociation(this)">
                                    <option value="penanaman" selected>Umum (Lahan Ini)</option>
                                    <option value="penanaman_specific">Penanaman Spesifik</option>
                                </select>
                                <select name="planting_id" id="task_planting_id" class="form-select mt-2" style="display: none;">
                                    <option value="">-- Pilih Penanaman --</option>
                                    @foreach($activePlantings as $planting)
                                        <option value="{{ $planting->id }}">{{ $planting->plant->name }} ({{ $planting->bed_label ?? '-' }})</option>
                                    @endforeach
                                </select>
                                @error('association')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                            <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi Tugas</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="Masukkan deskripsi tugas...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                            </div>
                    
                    <div class="mb-3">
                        <label for="task_report" class="form-label">Laporan Tugas</label>
                        <textarea class="form-control @error('task_report') is-invalid @enderror" 
                                  id="task_report" name="task_report" rows="3" 
                                  placeholder="Laporan tugas akan diisi oleh petugas yang ditugaskan...">{{ old('task_report') }}</textarea>
                        @error('task_report')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Field ini hanya dapat diisi oleh petugas yang ditugaskan
                        </div>
                    </div>
                    
                            <div class="mb-3">
                        <label for="checklist" class="form-label">Checklist</label>
                        <div id="checklist-container">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" name="checklist[]" placeholder="Masukkan item checklist">
                                <button type="button" class="btn btn-outline-success" onclick="addChecklistItem()">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Klik tombol + untuk menambahkan item checklist
                        </div>
                    </div>
                    
                            <div class="mb-3">
                        <label for="attachments" class="form-label">Lampiran (Foto dan Dokumen)</label>
                        <input type="file" class="form-control @error('attachments') is-invalid @enderror" 
                               id="attachments" name="attachments[]" multiple accept="image/*,.pdf,.doc,.docx">
                        @error('attachments')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Format yang didukung: JPEG, PNG, JPG, GIF, PDF, DOC, DOCX
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="assigned_to" class="form-label">Ditugaskan Untuk</label>
                                <select class="form-select @error('assigned_to') is-invalid @enderror" id="assigned_to" name="assigned_to">
                                    <option value="">Pilih User</option>
                                    @if($plantingLocation->responsibleContacts->isNotEmpty())
                                        <optgroup label="Penanggung Jawab">
                                            @foreach($plantingLocation->responsibleContacts as $contact)
                                                <option value="contact_{{ $contact->id }}" data-contact-email="{{ $contact->email }}">
                                                    {{ $contact->full_name }}@if($contact->organization) ({{ $contact->organization }})@endif - Contact
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                    @if($plantingLocation->landManagerContacts->isNotEmpty())
                                        <optgroup label="Penanggung Jawab Lahan">
                                            @foreach($plantingLocation->landManagerContacts as $contact)
                                                <option value="contact_{{ $contact->id }}" data-contact-email="{{ $contact->email }}">
                                                    {{ $contact->full_name }}@if($contact->organization) ({{ $contact->organization }})@endif - Contact
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                    @if($plantingLocation->landWorkerContacts->isNotEmpty())
                                        <optgroup label="Pekerja Lahan">
                                            @foreach($plantingLocation->landWorkerContacts as $contact)
                                                <option value="contact_{{ $contact->id }}" data-contact-email="{{ $contact->email }}">
                                                    {{ $contact->full_name }}@if($contact->organization) ({{ $contact->organization }})@endif - Contact
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                    @if($users->isNotEmpty())
                                        <optgroup label="Users">
                                    @foreach($users as $user)
                                                <option value="{{ $user->id }}">
                                                    {{ $user->name }} ({{ $user->role_label ?? 'User' }})
                                                </option>
                                    @endforeach
                                        </optgroup>
                                    @endif
                                </select>
                                <input type="hidden" name="assigned_contact_id" id="assigned_contact_id" value="">
                                @error('assigned_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('new_status') is-invalid @enderror" id="new_status" name="new_status" required>
                                    <option value="">Pilih Status</option>
                                    @foreach(\App\Models\Task::getStatuses() as $key => $label)
                                        <option value="{{ $key }}" {{ old('new_status', 'dilakukan') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('new_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="priority" class="form-label">Prioritas <span class="text-danger">*</span></label>
                                <select class="form-select @error('new_priority') is-invalid @enderror" id="new_priority" name="new_priority" required>
                                    <option value="">Pilih Prioritas</option>
                                    @foreach(\App\Models\Task::getPriorities() as $key => $label)
                                        <option value="{{ $key }}" {{ old('new_priority', 'medium') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('new_priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Tanggal Tenggat <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                                       id="due_date" name="due_date" value="{{ old('due_date') }}" required>
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                       id="start_date" name="start_date" value="{{ old('start_date') }}">
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Jam Mulai</label>
                                <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                       id="start_time" name="start_time" value="{{ old('start_time') }}">
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="due_time" class="form-label">Jam Tenggat</label>
                                <input type="time" class="form-control @error('due_time') is-invalid @enderror" 
                                       id="due_time" name="due_time" value="{{ old('due_time') }}">
                                @error('due_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="repeats" class="form-label">Ulangi</label>
                        <select name="repeats" id="repeats" class="form-select">
                                    <option value="">Tidak berulang</option>
                                    <option value="daily">Harian</option>
                                    <option value="weekly">Mingguan</option>
                                    <option value="monthly">Bulanan</option>
                                </select>
                            </div>

                            <div class="mb-3">
                        <label for="hours_spent" class="form-label">Jam Dikerjakan (Opsional)</label>
                        <input type="number" name="hours_spent" id="hours_spent" class="form-control" step="0.5" min="0" value="{{ old('hours_spent') }}">
                            </div>

                    <div class="mb-3">
                        <label for="task_color" class="form-label">Warna Tugas</label>
                        <input type="color" name="task_color" id="task_color" class="form-control form-control-color" value="{{ old('task_color', '#28a745') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-info" onclick="saveAsTemplate()">
                        <i class="fas fa-save me-2"></i>Simpan sebagai Template
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>Buat Tugas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Catatan Baru -->
<div class="modal fade" id="modalCatatanBaru" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('planting-locations.notes.store', $plantingLocation) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Catatan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul Catatan (Opsional)</label>
                        <input type="text" name="title" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Catatan</label>
                        <input type="date" name="note_date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kata Kunci</label>
                        <input type="text" name="keywords" class="form-control" placeholder="Dipisah koma, misal: Irigasi, Hama">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lampiran</label>
                        <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.txt">
                        <small class="text-muted">Maksimal 10MB</small>
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

<!-- Modal: Unggah Foto -->
<div class="modal fade" id="modalUnggahFoto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('planting-locations.photos.store', $plantingLocation) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Unggah Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Foto <span class="text-danger">*</span></label>
                        <input type="file" name="photos[]" class="form-control" multiple accept="image/*" required>
                        <small class="text-muted">Bisa memilih beberapa foto sekaligus. Maksimal 5MB per foto.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi Foto (Opsional)</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Pengambilan (Opsional)</label>
                        <input type="date" name="taken_at" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Unggah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Perawatan Baru -->
<div class="modal fade" id="modalPerawatanBaru" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('planting-locations.treatments.store', $plantingLocation) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Perawatan Baru untuk {{ $plantingLocation->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tipe Perawatan <span class="text-danger">*</span></label>
                                <select name="treatment_type" class="form-select" required>
                                    <option value="">-- Pilih Tipe --</option>
                                    <option value="Blight">Blight</option>
                                    <option value="Pupuk">Pupuk</option>
                                    <option value="Jamur">Jamur</option>
                                    <option value="Herbisida">Herbisida</option>
                                    <option value="Insektisida">Insektisida</option>
                                    <option value="Irigasi">Irigasi</option>
                                    <option value="Mildew">Mildew</option>
                                    <option value="Tungau">Tungau</option>
                                    <option value="Nutrisi">Nutrisi</option>
                                    <option value="Pestisida">Pestisida</option>
                                    <option value="Pengolahan Tanah">Pengolahan Tanah</option>
                                    <option value="Virus">Virus</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Detail/Produk</label>
                                <input type="text" name="product_detail" class="form-control" placeholder="Contoh: Pestisida Decis 25 EC">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Metode Aplikasi <span class="text-danger">*</span></label>
                                <select name="application_method" class="form-select" required>
                                    <option value="">-- Pilih Metode --</option>
                                    <option value="Granul">Granul</option>
                                    <option value="Semprot">Semprot</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Hari hingga Akhir Periode Penahanan</label>
                                <input type="number" name="withholding_period_days" class="form-control" min="0" placeholder="Contoh: 7">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Petugas</label>
                                <input type="text" name="technician" class="form-control" placeholder="Contoh: Ahmad Rifki">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Catatan detail mengenai perawatan..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tanggal Perawatan <span class="text-danger">*</span></label>
                                <input type="date" name="treatment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Batch # (Opsional)</label>
                                <input type="text" name="batch_number" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jumlah yang Diterapkan</label>
                                <input type="number" name="amount_applied" class="form-control" step="0.01" min="0" placeholder="Contoh: 200">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Lokasi Perawatan</label>
                                <input type="text" name="treatment_location" class="form-control" placeholder="Contoh: Daun, Batang, Tanah">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tanggal Perawatan Ulang (Opsional)</label>
                                <input type="date" name="retreat_date" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Total Biaya Perawatan (Opsional)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="total_cost" class="form-control" step="0.01" min="0" placeholder="150000">
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="record_expense" value="1" id="recordExpense">
                                    <label class="form-check-label" for="recordExpense">
                                        Catat Biaya (Record Expense)
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kata Kunci (Opsional)</label>
                                <input type="text" name="keywords" class="form-control" placeholder="Contoh: Hama wereng, aplikasi bulanan">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Asosiasi Ke</label>
                                <select name="planting_id" class="form-select">
                                    <option value="">Umum (Lahan Ini)</option>
                                    @foreach($activePlantings as $planting)
                                        <option value="{{ $planting->id }}">{{ $planting->plant->name }} ({{ $planting->bed_label ?? '-' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
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

<!-- Modal: Nutrisi Baru -->
<div class="modal fade" id="modalNutrisiBaru" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('planting-locations.nutrients.store', $plantingLocation) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nutrisi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 class="mb-3">Bagian 1: Detail Aplikasi</h6>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Produk yang Diterapkan <span class="text-danger">*</span></label>
                            <input type="text" name="product_applied" class="form-control" placeholder="Contoh: NPK Mutiara 16-16-16" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Penerapan <span class="text-danger">*</span></label>
                            <input type="date" name="application_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah yang Diterapkan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="amount_applied" class="form-control" step="0.01" min="0" placeholder="100" required>
                                <select name="unit" class="form-select" style="max-width: 150px;" required>
                                    <option value="kg">kg</option>
                                    <option value="gram">gram</option>
                                    <option value="ton">ton</option>
                                    <option value="liter">liter</option>
                                    <option value="mL">mL</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Metode Aplikasi <span class="text-danger">*</span></label>
                            <select name="application_method" class="form-select" required>
                                <option value="">-- Pilih Metode --</option>
                                <option value="Penyebaran (tangan/alat)">Penyebaran (tangan/alat)</option>
                                <option value="Kompos - Padatan">Kompos - Padatan</option>
                                <option value="Kompos - Teh">Kompos - Teh</option>
                                <option value="Granul">Granul</option>
                                <option value="Cairan">Cairan</option>
                                <option value="Pupuk Kotoran Hewan">Pupuk Kotoran Hewan</option>
                                <option value="Pelet">Pelet</option>
                                <option value="Semprot">Semprot</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Biaya Perawatan (Opsional)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="total_cost" class="form-control" step="0.01" min="0" placeholder="150000">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Petugas</label>
                            <input type="text" name="technician" class="form-control" placeholder="Contoh: Ahmad Rifki">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Asosiasi Ke</label>
                            <select name="planting_id" class="form-select">
                                <option value="">Umum (Lahan Ini)</option>
                                @foreach($activePlantings as $planting)
                                    <option value="{{ $planting->id }}">{{ $planting->plant->name }} ({{ $planting->bed_label ?? '-' }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <h6 class="mb-3">Bagian 2: Catatan</h6>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi / Catatan</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Contoh: Pemupukan kedua setelah 30 HST"></textarea>
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

<!-- Modal: Gunakan Template -->
<div class="modal fade" id="modalGunakanTemplate" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gunakan Template Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @php
                    $templates = \App\Models\TaskTemplate::where('association', 'penanaman')->active()->get();
                @endphp
                @if($templates->isEmpty())
                    <p class="text-muted text-center py-4">Belum ada template tugas tersedia.</p>
                @else
                    <div class="list-group">
                        @foreach($templates as $template)
                            <a href="#" class="list-group-item list-group-item-action" onclick="loadTemplate({{ $template->id }}, event)">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $template->name }}</h6>
                                    <small>{{ $template->association_label }}</small>
                                </div>
                                @if($template->description)
                                    <p class="mb-1 text-muted">{{ Str::limit($template->description, 100) }}</p>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function openHarvestModal(plantingId, plantName, bedLabel) {
    document.getElementById('harvest_planting_id').value = plantingId;
    const locationName = '{{ addslashes($plantingLocation->name) }}';
    const source = locationName + ', Lokasi ' + bedLabel;
    document.getElementById('harvest_source').value = source;
    document.getElementById('harvest_source_hidden').value = source;
    
    // Generate batch number
    @php
        $harvestCount = \App\Models\Harvest::whereYear('harvested_at', date('Y'))->count() + 1;
        $locationCode = strtoupper(substr($plantingLocation->name, 0, 2));
    @endphp
    const year = {{ date('Y') }};
    const locationCode = '{{ $locationCode }}';
    const harvestCount = {{ $harvestCount }};
    const batchNo = 'PAN-' + locationCode + '-' + year + '-' + String(harvestCount).padStart(3, '0');
    document.getElementById('harvest_batch_no').value = batchNo;
    document.getElementById('harvest_batch_no_hidden').value = batchNo;
    
    // Set plant_id from planting
    @php
        $plantingsData = $activePlantings->map(function($p) {
            return [
                'id' => $p->id,
                'plant_id' => $p->plant_id,
                'plant_name' => $p->plant->name,
                'bed_label' => $p->bed_label ?? ''
            ];
        })->keyBy('id');
    @endphp
    const plantingsData = @json($plantingsData);
    const planting = plantingsData[plantingId];
    if (planting) {
        // Remove existing plant_id input if any
        const existingInput = document.querySelector('#modalCatatPanen form input[name="plant_id"]');
        if (existingInput && existingInput.type === 'hidden') {
            existingInput.remove();
        }
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'plant_id';
        hiddenInput.value = planting.plant_id;
        document.querySelector('#modalCatatPanen form').appendChild(hiddenInput);
    }
    
    new bootstrap.Modal(document.getElementById('modalCatatPanen')).show();
}

function openLossModal(plantingId, plantName, bedLabel, currentPlants) {
    document.getElementById('loss_planting_id').value = plantingId;
    document.getElementById('lossModalTitle').textContent = 'Catat Kehilangan - ' + plantName + (bedLabel ? ' (' + bedLabel + ')' : '');
    document.getElementById('loss_current_plants').textContent = currentPlants + ' Tanaman Saat Ini';
    document.getElementById('loss_amount').max = currentPlants;
    document.getElementById('loss_amount').value = '';
    document.getElementById('loss_date').value = '{{ date('Y-m-d') }}';
    document.getElementById('loss_reason').value = '';
    document.getElementById('loss_description').value = '';
    
    new bootstrap.Modal(document.getElementById('modalCatatKehilangan')).show();
}

function markFailed(plantingId) {
    if (confirm('Apakah Anda yakin ingin menandai penanaman ini sebagai gagal panen?')) {
        fetch('{{ route('planting-locations.plantings.mark-failed', ['plantingLocation' => $plantingLocation->id, 'planting' => ':id']) }}'.replace(':id', plantingId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(() => location.reload());
    }
}

function updateAssociation(type) {
    const plantingSelect = document.getElementById('task_planting_id');
    if (type === 'planting') {
        plantingSelect.style.display = 'block';
        plantingSelect.required = true;
    } else {
        plantingSelect.style.display = 'none';
        plantingSelect.required = false;
        plantingSelect.value = '';
    }
}

function updateTaskAssociation(select) {
    const plantingSelect = document.getElementById('task_planting_id');
    const associationValue = select.value;
    
    // If association is "Penanaman Spesifik", show planting select and set association to "penanaman"
    if (associationValue === 'penanaman_specific') {
        plantingSelect.style.display = 'block';
        plantingSelect.required = true;
        // Set hidden association value to "penanaman" for backend
        select.name = 'association_temp';
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'association';
        hiddenInput.value = 'penanaman';
        hiddenInput.id = 'association_hidden';
        if (!document.getElementById('association_hidden')) {
            select.parentNode.appendChild(hiddenInput);
        }
    } else {
        plantingSelect.style.display = 'none';
        plantingSelect.required = false;
        plantingSelect.value = '';
        // Remove hidden input if exists
        const hiddenInput = document.getElementById('association_hidden');
        if (hiddenInput) {
            hiddenInput.remove();
        }
        // Restore original name
        if (select.name === 'association_temp') {
            select.name = 'association';
        }
    }
}

function addChecklistItem() {
    const container = document.getElementById('checklist-container');
    const inputGroup = document.createElement('div');
    inputGroup.className = 'input-group mb-2';
    inputGroup.innerHTML = `
        <input type="text" class="form-control" name="checklist[]" placeholder="Masukkan item checklist">
        <button type="button" class="btn btn-outline-danger" onclick="removeChecklistItem(this)">
            <i class="fas fa-minus"></i>
        </button>
    `;
    container.appendChild(inputGroup);
}

function removeChecklistItem(button) {
    button.closest('.input-group').remove();
}

function saveAsTemplate() {
    const form = document.getElementById('taskForm');
    const title = document.getElementById('title').value;
    
    if (!title) {
        alert('Judul tugas harus diisi untuk menyimpan sebagai template.');
        return;
    }
    
    if (confirm('Apakah Anda yakin ingin menyimpan tugas ini sebagai template?')) {
        document.getElementById('actionType').value = 'save_template';
        form.submit();
    }
}

function loadTemplate(templateId, event) {
    event.preventDefault();
    
    fetch(`{{ url('/api/task-templates') }}/${templateId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            // Load template data to form
            if (data.tasks_list && data.tasks_list.length > 0) {
                const task = data.tasks_list[0]; // Get first task from template
                
                document.getElementById('title').value = task.title || '';
                document.getElementById('description').value = task.description || '';
                document.getElementById('task_report').value = task.task_report || '';
                
                if (task.checklist && Array.isArray(task.checklist)) {
                    const container = document.getElementById('checklist-container');
                    container.innerHTML = '';
                    task.checklist.forEach((item, index) => {
                        const inputGroup = document.createElement('div');
                        inputGroup.className = 'input-group mb-2';
                        inputGroup.innerHTML = `
                            <input type="text" class="form-control" name="checklist[]" value="${item}" placeholder="Masukkan item checklist">
                            <button type="button" class="btn btn-outline-${index === 0 ? 'success' : 'danger'}" onclick="${index === 0 ? 'addChecklistItem()' : 'removeChecklistItem(this)'}">
                                <i class="fas fa-${index === 0 ? 'plus' : 'minus'}"></i>
                            </button>
                        `;
                        container.appendChild(inputGroup);
                    });
                }
                
                if (task.new_status) {
                    document.getElementById('new_status').value = task.new_status;
                }
                
                if (task.new_priority) {
                    document.getElementById('new_priority').value = task.new_priority;
                }
                
                if (task.repeats) {
                    document.getElementById('repeats').value = task.repeats;
                }
                
                if (task.hours_spent) {
                    document.getElementById('hours_spent').value = task.hours_spent;
                }
                
                if (task.task_color) {
                    document.getElementById('task_color').value = task.task_color;
                }
            }
            
            // Close template modal
            bootstrap.Modal.getInstance(document.getElementById('modalGunakanTemplate')).hide();
            
            // Open task modal
            new bootstrap.Modal(document.getElementById('modalTugasBaru')).show();
        })
        .catch(error => {
            console.error('Error loading template:', error);
            alert('Gagal memuat template. Silakan coba lagi.');
        });
}

// Handle assigned_to change to extract contact_id if it's a contact
document.addEventListener('DOMContentLoaded', function() {
    const assignedToSelect = document.getElementById('assigned_to');
    if (assignedToSelect) {
        assignedToSelect.addEventListener('change', function() {
            const value = this.value;
            const assignedContactId = document.getElementById('assigned_contact_id');
            
            if (value && value.startsWith('contact_')) {
                const contactId = value.replace('contact_', '');
                assignedContactId.value = contactId;
                // Clear assigned_to since it's a contact, not a user
                // We'll handle this in the backend
            } else {
                assignedContactId.value = '';
            }
        });
    }
});

function filterTasks() {
    const status = document.getElementById('filterStatus').value;
    const assignee = document.getElementById('filterAssignee') ? document.getElementById('filterAssignee').value : 'all';
    window.location.href = '?status=' + status + '&assignee=' + assignee;
}

function viewNote(noteId) {
    // Implement view note modal or redirect
    alert('Fitur melihat detail catatan akan segera ditambahkan.');
}
</script>
@endsection
