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
    <a href="{{ route('planting-locations.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
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
        <div class="row">
            <div class="col-md-6">
                <div class="mb-2"><strong>Tipe Lokasi:</strong> {{ str_replace('_',' ', $plantingLocation->location_type) }}</div>
                <div class="mb-2"><strong>Format Penanaman:</strong> {{ str_replace('_',' ', $plantingLocation->planting_format) }}</div>
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
            <div class="col-md-6">
                <div class="mb-2"><strong>Ukuran Peta:</strong> {{ $plantingLocation->map_size ?: '-' }}</div>
                <div class="mb-2"><strong>Kondisi Cahaya:</strong> {{ $plantingLocation->light_condition ?: 'Tidak ada data' }}</div>
                @if($plantingLocation->baseLocation)
                    <div class="mb-2"><strong>Lokasi Lahan:</strong> {{ $plantingLocation->baseLocation->name }}</div>
                @endif
            </div>
        </div>
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
        <p class="text-muted">Perawatan (akan ditambahkan).</p>
    </div>

    <!-- Tab: Nutrisi -->
    <div class="tab-pane fade" id="nutrisi">
        <p class="text-muted">Nutrisi (akan ditambahkan).</p>
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
                                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-primary" title="Edit">
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

<!-- Modal: Tugas Baru -->
<div class="modal fade" id="modalTugasBaru" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('planting-locations.tasks.store', $plantingLocation) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tugas Baru untuk Lahan: {{ $plantingLocation->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Judul <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="5"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Asosiasi Ke</label>
                                <select name="association_type" class="form-select" onchange="updateAssociation(this.value)">
                                    <option value="location">Umum (Lahan Ini)</option>
                                    <option value="planting">Penanaman Spesifik</option>
                                </select>
                                <select name="planting_id" id="task_planting_id" class="form-select mt-2" style="display: none;">
                                    <option value="">-- Pilih Penanaman --</option>
                                    @foreach($activePlantings as $planting)
                                        <option value="{{ $planting->id }}">{{ $planting->plant->name }} ({{ $planting->bed_label ?? '-' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Warna Tugas</label>
                                <input type="color" name="task_color" class="form-control form-control-color" value="#28a745">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="new_status" class="form-select" required>
                                    <option value="dilakukan">To Do</option>
                                    <option value="dalam_progress">In Progress</option>
                                    <option value="selesai">Selesai</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ditugaskan Kepada</label>
                                <select name="assigned_to" class="form-select">
                                    <option value="">-- Pilih Pengguna --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Prioritas <span class="text-danger">*</span></label>
                                <select name="new_priority" class="form-select" required>
                                    <option value="rendah">Rendah</option>
                                    <option value="medium" selected>Sedang</option>
                                    <option value="tinggi">Tinggi</option>
                                    <option value="tertinggi">Tertinggi</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jatuh Tempo <span class="text-danger">*</span></label>
                                <input type="date" name="due_date" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ulangi</label>
                                <select name="repeats" class="form-select">
                                    <option value="">Tidak berulang</option>
                                    <option value="daily">Harian</option>
                                    <option value="weekly">Mingguan</option>
                                    <option value="monthly">Bulanan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jam Dikerjakan (Opsional)</label>
                                <input type="number" name="hours_spent" class="form-control" step="0.5" min="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-success">Buat</button>
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

<!-- Modal: Gunakan Template -->
<div class="modal fade" id="modalGunakanTemplate" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gunakan Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Fitur template akan segera ditambahkan.</p>
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

function filterTasks() {
    const status = document.getElementById('filterStatus').value;
    const assignee = document.getElementById('filterAssignee').value;
    window.location.href = '?status=' + status + '&assignee=' + assignee;
}

function viewNote(noteId) {
    // Implement view note modal or redirect
    alert('Fitur melihat detail catatan akan segera ditambahkan.');
}
</script>
@endsection
