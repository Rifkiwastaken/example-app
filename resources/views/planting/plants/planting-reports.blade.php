@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Pelaporan - ' . $planting->plant->name . ' - SIBESTI')

@push('styles')
<style>
    /* Styling untuk tab utama pelaporan - dengan warna berbeda untuk setiap tab */
    #pelaporan-pills .nav-link {
        background-color: #e9ecef !important;
        color: #495057 !important;
        border: 1px solid #dee2e6 !important;
        margin-right: 5px;
        opacity: 1 !important;
        font-weight: 500;
        border-radius: 0.375rem;
        padding: 0.5rem 1rem;
        display: inline-block;
        min-width: 120px;
        text-align: center;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        transition: none;
    }
    #pelaporan-pills .nav-link:not(.active) {
        background-color: #e9ecef !important;
        color: #495057 !important;
        opacity: 1 !important;
        border: 1px solid #dee2e6 !important;
    }
    #pelaporan-pills .nav-link:not(.active):hover {
        background-color: #e9ecef !important;
        border-color: #dee2e6 !important;
        color: #495057 !important;
    }
    #pelaporan-pills .nav-link:not(.active) i,
    #pelaporan-pills .nav-link:not(.active) i.fas,
    #pelaporan-pills .nav-link:not(.active) i.fa-clipboard-list,
    #pelaporan-pills .nav-link:not(.active) i.fa-first-aid,
    #pelaporan-pills .nav-link:not(.active) i.fa-flask,
    #pelaporan-pills .nav-link:not(.active) i.fa-sticky-note,
    #pelaporan-pills .nav-link:not(.active) i.fa-paperclip {
        color: #495057 !important;
        opacity: 1 !important;
        display: inline-block !important;
        visibility: visible !important;
    }
    /* Warna aktif untuk tab Laporan - Biru */
    #pelaporan-pills .nav-link.active#tab-laporan,
    #pelaporan-pills .nav-link.active[href="#laporan-subtab"] {
        background-color: #0d6efd !important;
        color: #ffffff !important;
        border-color: #0d6efd !important;
        opacity: 1 !important;
        font-weight: 600;
    }
    /* Warna aktif untuk tab Perawatan - Hijau */
    #pelaporan-pills .nav-link.active#tab-perawatan,
    #pelaporan-pills .nav-link.active[href="#perawatan-subtab"] {
        background-color: #198754 !important;
        color: #ffffff !important;
        border-color: #198754 !important;
        opacity: 1 !important;
        font-weight: 600;
    }
    /* Warna aktif untuk tab Nutrisi - Orange/Kuning */
    #pelaporan-pills .nav-link.active#tab-nutrisi,
    #pelaporan-pills .nav-link.active[href="#nutrisi-subtab"] {
        background-color: #ffc107 !important;
        color: #000000 !important;
        border-color: #ffc107 !important;
        opacity: 1 !important;
        font-weight: 600;
    }
    /* Warna aktif untuk tab Catatan - Biru Info */
    #pelaporan-pills .nav-link.active#tab-catatan,
    #pelaporan-pills .nav-link.active[href="#catatan-subtab"] {
        background-color: #17a2b8 !important;
        color: #ffffff !important;
        border-color: #17a2b8 !important;
        opacity: 1 !important;
        font-weight: 600;
    }
    /* Warna aktif untuk tab Lampiran - Ungu */
    #pelaporan-pills .nav-link.active#tab-lampiran,
    #pelaporan-pills .nav-link.active[href="#lampiran-subtab"] {
        background-color: #6f42c1 !important;
        color: #ffffff !important;
        border-color: #6f42c1 !important;
        opacity: 1 !important;
        font-weight: 600;
    }
    /* Ikon untuk tab aktif */
    #pelaporan-pills .nav-link.active i,
    #pelaporan-pills .nav-link.active i.fas,
    #pelaporan-pills .nav-link.active i.fa-clipboard-list,
    #pelaporan-pills .nav-link.active i.fa-first-aid,
    #pelaporan-pills .nav-link.active i.fa-flask,
    #pelaporan-pills .nav-link.active i.fa-sticky-note,
    #pelaporan-pills .nav-link.active i.fa-paperclip {
        opacity: 1 !important;
        display: inline-block !important;
        visibility: visible !important;
    }
    /* Warna ikon untuk tab aktif Laporan - Putih */
    #pelaporan-pills .nav-link.active#tab-laporan i,
    #pelaporan-pills .nav-link.active[href="#laporan-subtab"] i {
        color: #ffffff !important;
    }
    /* Warna ikon untuk tab aktif Perawatan - Putih */
    #pelaporan-pills .nav-link.active#tab-perawatan i,
    #pelaporan-pills .nav-link.active[href="#perawatan-subtab"] i {
        color: #ffffff !important;
    }
    /* Warna ikon untuk tab aktif Nutrisi - Hitam */
    #pelaporan-pills .nav-link.active#tab-nutrisi i,
    #pelaporan-pills .nav-link.active[href="#nutrisi-subtab"] i {
        color: #000000 !important;
    }
    /* Warna ikon untuk tab aktif Catatan - Putih */
    #pelaporan-pills .nav-link.active#tab-catatan i,
    #pelaporan-pills .nav-link.active[href="#catatan-subtab"] i {
        color: #ffffff !important;
    }
    /* Warna ikon untuk tab aktif Lampiran - Putih */
    #pelaporan-pills .nav-link.active#tab-lampiran i,
    #pelaporan-pills .nav-link.active[href="#lampiran-subtab"] i {
        color: #ffffff !important;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $planting->plant->name }}</h4>
        <small class="text-muted">Lokasi Penanaman: {{ $plantingLocation->name }}</small>
        @if($planting->bed_label)
            <br><small class="text-muted">Lokasi Tanam: {{ $planting->bed_label }}</small>
        @endif
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

<!-- Sub-tabs untuk Pelaporan -->
<ul class="nav nav-pills mb-3" role="tablist" id="pelaporan-pills">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#laporan-subtab" id="tab-laporan">
            <i class="fas fa-clipboard-list me-1"></i>Laporan
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#perawatan-subtab" id="tab-perawatan">
            <i class="fas fa-first-aid me-1"></i>Perawatan
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#nutrisi-subtab" id="tab-nutrisi">
            <i class="fas fa-flask me-1"></i>Nutrisi
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#catatan-subtab" id="tab-catatan">
            <i class="fas fa-sticky-note me-1"></i>Catatan
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#lampiran-subtab" id="tab-lampiran">
            <i class="fas fa-paperclip me-1"></i>Lampiran
        </a>
    </li>
</ul>

<div class="tab-content p-3 bg-white border rounded">
    <!-- Sub-tab: Laporan -->
    <div class="tab-pane show active" id="laporan-subtab">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Laporan untuk Penanaman: {{ $planting->plant->name }} @if($planting->bed_label) ({{ $planting->bed_label }}) @endif</h6>
                    <div class="d-flex gap-2">
                        @if(auth()->user()->isAdmin() || auth()->user()->canManageDataInPelaporan($plantingLocation))
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalGunakanTemplate">
                                <i class="fas fa-file-alt me-1"></i>Gunakan Template
                            </button>
                        @endif
                        @if(auth()->user()->isAdmin() || auth()->user()->canAddDataInPelaporan($plantingLocation))
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTugasBaru">
                                <i class="fas fa-plus me-1"></i>Tambah Tugas
                            </button>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm" placeholder="Cari Laporan..." id="searchAllTasks">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="filterAllStatus" onchange="filterAllTasks()">
                            <option value="all">Semua Status</option>
                            <option value="selesai">Telah dilakukan (Selesai)</option>
                            <option value="dalam_progress">Dalam progress/ akan dilakukan</option>
                            <option value="tidak_selesai">Tidak selesai</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="filterAllAssignee" onchange="filterAllTasks()">
                            <option value="all">Ditugaskan untuk</option>
                            @foreach($locationUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" id="filterAllYear" onchange="filterAllTasks()">
                            <option value="">Semua Tahun</option>
                            @foreach($taskYears ?? [] as $ty)
                                <option value="{{ $ty }}">{{ $ty }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Laporan (Judul)</th>
                                <th>Asosiasi ke Penanaman</th>
                                <th>Jatuh Tempo</th>
                                <th>Prioritas</th>
                                <th>Status</th>
                                <th>Ditugaskan</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="allTasksTableBody">
                            @forelse($allTasks ?? [] as $task)
                                <tr>
                                    <td>
                                        <strong>{{ $task->title }}</strong>
                                        @if($task->description)
                                            <br><small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->planting_id && $task->planting)
                                            {{ $task->planting->plant->name ?? 'Tanaman' }}
                                            @if($task->planting->bed_label) - {{ $task->planting->bed_label }} @endif
                                        @else
                                            <span class="text-muted">Umum (Lahan Ini)</span>
                                        @endif
                                    </td>
                                    <td>{{ $task->due_date ? $task->due_date->format('d M Y') : '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $task->new_priority === 'tertinggi' || $task->new_priority === 'tinggi' ? 'danger' : ($task->new_priority === 'medium' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($task->new_priority ?? 'medium') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $task->new_status === 'selesai' ? 'success' : ($task->new_status === 'dalam_progress' ? 'info' : 'danger') }}">
                                            {{ $task->status_label ?? 'Dalam progress' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($task->assignedUser)
                                            {{ $task->assignedUser->name }}
                                        @else
                                            <span class="text-muted">(Unassigned)</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-info" onclick="viewTask('{{ $task->task_id }}')" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if(auth()->user()->isAdmin() || auth()->user()->canAddDataInPelaporan($plantingLocation))
                                                <button type="button" class="btn btn-outline-primary" onclick="isiLaporan('{{ $task->task_id }}')" title="Isi Laporan">
                                                    <i class="fas fa-clipboard-list"></i>
                                                </button>
                                                <form action="{{ route('planting-locations.tasks.destroy', [$plantingLocation, $task]) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus laporan ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        Belum ada laporan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
    </div>

    <!-- Sub-tab: Perawatan -->
    <div class="tab-pane" id="perawatan-subtab">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Data Perawatan untuk Penanaman: {{ $planting->plant->name }} @if($planting->bed_label) ({{ $planting->bed_label }}) @endif</h6>
            @if(auth()->user()->isAdmin() || auth()->user()->canAddDataInPelaporan($plantingLocation))
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalPerawatanBaru">
                    <i class="fas fa-plus me-1"></i>Tambah Perawatan
                </button>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal Perawatan</th>
                        <th>Tipe Perawatan</th>
                        <th>Produk</th>
                        <th>Metode Aplikasi</th>
                        <th>Jumlah</th>
                        <th>Teknisi</th>
                        <th>Biaya</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($treatments as $treatment)
                        <tr>
                            <td>{{ $treatment->treatment_date ? $treatment->treatment_date->format('d M Y') : '-' }}</td>
                            <td><strong>{{ $treatment->treatment_type }}</strong></td>
                            <td>{{ $treatment->product_detail ?: '-' }}</td>
                            <td>{{ $treatment->application_method ?: '-' }}</td>
                            <td>
                                @if($treatment->amount_applied)
                                    {{ number_format($treatment->amount_applied, 2) }}
                                    @if($treatment->unit_measurement)
                                        {{ $treatment->unit_measurement }}
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $treatment->technician ?: '-' }}</td>
                            <td>
                                @if($treatment->total_cost)
                                    Rp {{ number_format($treatment->total_cost, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" onclick="loadTreatmentDetail({{ $treatment->id }})" data-bs-toggle="modal" data-bs-target="#modalDetailPerawatan" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if(auth()->user()->isAdmin() || auth()->user()->canAddDataInPelaporan($plantingLocation))
                                    <form action="{{ route('planting-locations.treatments.destroy', [$plantingLocation, $treatment]) }}" method="POST" class="d-inline" onsubmit="return confirmDelete(this, '{{ $treatment->treatment_name }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">Belum ada data perawatan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sub-tab: Nutrisi -->
    <div class="tab-pane" id="nutrisi-subtab">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Data Nutrisi untuk Penanaman: {{ $planting->plant->name }} @if($planting->bed_label) ({{ $planting->bed_label }}) @endif</h6>
            @if(auth()->user()->isAdmin() || auth()->user()->canAddDataInPelaporan($plantingLocation))
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalNutrisiBaru">
                    <i class="fas fa-plus me-1"></i>Tambah Nutrisi
                </button>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal Aplikasi</th>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Unit</th>
                        <th>Metode Aplikasi</th>
                        <th>Teknisi</th>
                        <th>Biaya</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nutrients as $nutrient)
                        <tr>
                            <td>{{ $nutrient->application_date ? $nutrient->application_date->format('d M Y') : '-' }}</td>
                            <td><strong>{{ $nutrient->product_applied }}</strong></td>
                            <td>{{ number_format($nutrient->amount_applied, 2) }}</td>
                            <td>{{ $nutrient->unit ?: '-' }}</td>
                            <td>{{ $nutrient->application_method ?: '-' }}</td>
                            <td>{{ $nutrient->technician ?: '-' }}</td>
                            <td>
                                @if($nutrient->total_cost)
                                    Rp {{ number_format($nutrient->total_cost, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" onclick="loadNutrientDetail({{ $nutrient->id }})" data-bs-toggle="modal" data-bs-target="#modalDetailNutrisi" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if(auth()->user()->isAdmin() || auth()->user()->canAddDataInPelaporan($plantingLocation))
                                    <form action="{{ route('planting-locations.nutrients.destroy', [$plantingLocation, $nutrient]) }}" method="POST" class="d-inline" onsubmit="return confirmDelete(this, '{{ $nutrient->product_applied }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">Belum ada data nutrisi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sub-tab: Catatan -->
    <div class="tab-pane" id="catatan-subtab">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Catatan untuk Lahan: {{ $plantingLocation->name }}</h6>
            @if(auth()->user()->isAdmin() || auth()->user()->canAddDataInPelaporan($plantingLocation))
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalCatatanBaru">
                    <i class="fas fa-plus me-1"></i>Tambah Catatan
                </button>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Judul/Deskripsi</th>
                        <th>Kata Kunci</th>
                        <th>Pembuat</th>
                        <th>Diperuntukan untuk</th>
                        <th>Status</th>
                        <th>Lampiran</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notes as $note)
                        <tr class="{{ $note->isAssignedTo(auth()->id()) && !$note->isReadBy(auth()->id()) ? 'table-warning' : '' }}">
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
                                @if($note->assigned_to && count($note->assigned_to) > 0)
                                    @foreach($note->assignedUsers() as $assignedUser)
                                        <span class="badge bg-info me-1">{{ $assignedUser->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($note->isAssignedTo(auth()->id()))
                                    @if($note->isReadBy(auth()->id()))
                                        <span class="badge bg-success"><i class="fas fa-check"></i> Sudah dibaca</span>
                                    @else
                                        <span class="badge bg-warning"><i class="fas fa-exclamation-circle"></i> Belum dibaca</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($note->attachment_path)
                                    <span class="badge bg-success">Ya</span>
                                @else
                                    <span class="text-muted">Tidak</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" onclick="viewNote({{ $note->id }})" title="Lihat">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($note->isAssignedTo(auth()->id()) && !$note->isReadBy(auth()->id()))
                                        <button type="button" class="btn btn-outline-success" onclick="markNoteAsRead({{ $note->id }})" title="Tandai sebagai sudah dibaca">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">Belum ada catatan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sub-tab: Lampiran -->
    <div class="tab-pane" id="lampiran-subtab">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Lampiran untuk Lahan: {{ $plantingLocation->name }}</h6>
            @if(auth()->user()->isAdmin() || auth()->user()->canAddDataInPelaporan($plantingLocation))
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahLampiran">
                    <i class="fas fa-plus me-1"></i>Tambah Lampiran
                </button>
            @endif
        </div>

        <div class="row">
            @forelse($attachments as $attachment)
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">{{ $attachment->title }}</h6>
                            <p class="card-text"><small class="text-muted">{{ $attachment->attachment_date ? $attachment->attachment_date->format('d M Y') : '-' }}</small></p>
                            <p class="card-text"><small class="text-muted">Oleh: {{ $attachment->creator->name ?? '-' }}</small></p>
                            <div class="btn-group btn-group-sm w-100">
                                <button type="button" class="btn btn-info" onclick="loadAttachmentDetail('{{ $attachment->attachment_id }}')" data-bs-toggle="modal" data-bs-target="#modalDetailLampiran" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="btn btn-primary" title="Download">
                                    <i class="fas fa-file-download"></i>
                                </a>
                                @if(auth()->user()->isAdmin() || auth()->user()->canAddDataInPelaporan($plantingLocation))
                                    <button type="button" class="btn btn-warning" onclick="loadAttachmentEdit('{{ $attachment->attachment_id }}')" data-bs-toggle="modal" data-bs-target="#modalEditLampiran" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('planting-locations.attachments.destroy', [$plantingLocation, $attachment]) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lampiran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-center text-muted">Belum ada lampiran.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modals -->
<!-- Modal: Laporan Baru -->
<div class="modal fade" id="modalTugasBaru" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('planting-locations.tasks.store', $plantingLocation) }}" method="POST" enctype="multipart/form-data" id="formLaporanBaru">
                @csrf
                <input type="hidden" name="action_type" id="action_type" value="create">
                <input type="hidden" name="from_planting_reports" value="1">
                <input type="hidden" name="planting_id_for_redirect" value="{{ $planting->planting_id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Tugas Baru untuk Penanaman: {{ $planting->plant->name }} @if($planting->bed_label) ({{ $planting->bed_label }}) @endif</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Judul Tugas <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Asosiasi Penanaman <span class="text-danger">*</span></label>
                                <select name="planting_id" id="association_planting_id" class="form-select" required>
                                    <option value="umum">Umum (di lokasi penanaman ini)</option>
                                    @foreach($allPlantingsForLocation as $p)
                                        <option value="{{ $p->planting_id }}" {{ $p->planting_id == $planting->planting_id ? 'selected' : '' }}>
                                            {{ $p->plant->name ?? 'Tanaman' }} 
                                            @if($p->bed_label) - {{ $p->bed_label }} @endif
                                            @if($p->planted_at) ({{ $p->planted_at->format('d M Y') }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Pilih "Umum" untuk menerapkan ke semua penanaman di lokasi ini</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Deskripsi Tugas</label>
                                <textarea name="description" class="form-control" rows="5" placeholder="Masukkan deskripsi tugas..."></textarea>
                            </div>
                        </div>
                    </div>
                    {{-- Field Laporan disembunyikan --}}
                    <div class="row" style="display: none;">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Laporan </label>
                                <textarea name="task_report" class="form-control" rows="5" placeholder="Laporan tugas akan diisi oleh petugas yang ditugaskan..."></textarea>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> Field ini hanya dapat diisi oleh petugas yang ditugaskan
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Checklist</label>
                                <div class="input-group">
                                    <input type="text" id="checklist_item" class="form-control" placeholder="Masukkan item checklist">
                                    <button type="button" class="btn btn-success" onclick="addChecklistItem()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> Klik tombol + untuk menambahkan item checklist
                                </small>
                                <div id="checklist_items" class="mt-2"></div>
                                <input type="hidden" name="checklist" id="checklist_hidden">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Lampiran Tugas (Foto dan Dokumen)</label>
                                <input type="file" name="attachments[]" class="form-control" multiple accept="image/*,.pdf,.doc,.docx">
                                <small class="text-muted">Format yang didukung: JPEG, PNG, JPG, GIF, PDF, DOC, DOCX</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ditugaskan Untuk</label>
                                <select name="assigned_to" class="form-select">
                                    <option value="">-- Pilih User --</option>
                                    <option value="semua_user">Semua User</option>
                                    @foreach($locationUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Pilih "Semua User" untuk menugaskan ke semua user yang terdaftar sebagai penanggung jawab di lokasi penanaman ini</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Prioritas <span class="text-danger">*</span></label>
                                <select name="new_priority" class="form-select" required>
                                    <option value="rendah">Rendah</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="tinggi">Tinggi</option>
                                    <option value="tertinggi">Tertinggi</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="new_status" class="form-select" required>
                                    <option value="">-- Pilih Status --</option>
                                    <option value="selesai">Telah dilakukan (Selesai)</option>
                                    <option value="dalam_progress" selected>Dalam progress/ akan dilakukan</option>
                                    <option value="tidak_selesai">Tidak selesai</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    {{-- Field Tanggal Mulai dan Jam Mulai disembunyikan --}}
                    <div class="row" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" name="start_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jam Mulai</label>
                                <input type="time" name="start_time" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Tenggat <span class="text-danger">*</span></label>
                                <input type="date" name="due_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jam Tenggat</label>
                                <input type="time" name="due_time" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-info" onclick="saveAsTemplate()">
                        <i class="fas fa-save me-1"></i>Simpan sebagai Template
                    </button>
                    <button type="submit" class="btn btn-success" onclick="setActionType('create')">
                        <i class="fas fa-save me-1"></i>Simpan Tugas
                    </button>
                    <button type="button" class="btn btn-primary" onclick="saveAndFillReport()">
                        <i class="fas fa-clipboard-check me-1"></i>Simpan Tugas sebagai Laporan
                    </button>
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
                <h5 class="modal-title">Gunakan Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($taskTemplates->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nama Template</th>
                                    <th>Deskripsi</th>
                                    <th width="200">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($taskTemplates as $template)
                                    <tr>
                                        <td><strong>{{ $template->name }}</strong></td>
                                        <td>{{ Str::limit($template->description, 50) }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-primary" onclick="useTemplate('{{ $template->task_template_id }}')" title="Gunakan">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-warning" onclick="editTemplate('{{ $template->task_template_id }}')" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger" onclick="deleteTemplate('{{ $template->task_template_id }}')" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center">Belum ada template yang tersedia.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit Template -->
<div class="modal fade" id="modalEditTemplate" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formEditTemplate" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="editTemplateBody">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Perawatan Baru -->
<div class="modal fade" id="modalPerawatanBaru" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('planting-locations.treatments.store', $plantingLocation) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="from_planting_reports" value="1">
                <input type="hidden" name="planting_id_for_redirect" value="{{ $planting->planting_id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Data Perawatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Perawatan/Gangguan <span class="text-danger">*</span></label>
                        <input type="text" name="treatment_name" class="form-control" placeholder="Contoh: Pengendalian Hama Ulat, Pemupukan, dll" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Perawatan <span class="text-danger">*</span></label>
                            <input type="date" name="treatment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipe Perawatan <span class="text-danger">*</span></label>
                            <select name="treatment_type" class="form-select" required>
                                <option value="">Pilih Tipe Perawatan</option>
                                <option value="Layu">Layu</option>
                                <option value="Pemupukan">Pemupukan</option>
                                <option value="Jamur">Jamur</option>
                                <option value="Herbisida">Herbisida</option>
                                <option value="Serangga">Serangga</option>
                                <option value="Irigasi">Irigasi</option>
                                <option value="Embun Tepung">Embun Tepung</option>
                                <option value="Tungau">Tungau</option>
                                <option value="Kapang">Kapang</option>
                                <option value="Nutrisi">Nutrisi</option>
                                <option value="Pestisida">Pestisida</option>
                                <option value="Pengolahan Tanah">Pengolahan Tanah</option>
                                <option value="Virus">Virus</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Penanggung Jawab <span class="text-danger">*</span></label>
                            <select name="responsible_person_id" class="form-select" required>
                                <option value="{{ auth()->user()->user_id }}" selected>{{ auth()->user()->name }}</option>
                                @foreach($locationUsers as $user)
                                    @if($user->user_id != auth()->user()->user_id)
                                        <option value="{{ $user->user_id }}">{{ $user->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Detail Produk</label>
                            <input type="text" name="product_detail" class="form-control" placeholder="Nama produk yang digunakan">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Metode Aplikasi <span class="text-danger">*</span></label>
                            <select name="application_method" class="form-select" required>
                                <option value="">Pilih Metode Aplikasi</option>
                                <option value="Semprot">Semprot</option>
                                <option value="Siram">Siram</option>
                                <option value="Tabur">Tabur</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Asosiasi Penanaman</label>
                            <select name="planting_id" id="treatment_planting_id" class="form-select">
                                <option value="umum">Umum (di lokasi penanaman ini)</option>
                                @foreach($allPlantingsForLocation as $p)
                                    <option value="{{ $p->planting_id }}" {{ $p->planting_id == $planting->planting_id ? 'selected' : '' }}>
                                        {{ $p->plant->name ?? 'Tanaman' }} 
                                        @if($p->bed_label) - {{ $p->bed_label }} @endif
                                        @if($p->planted_at) ({{ $p->planted_at->format('d M Y') }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih "Umum" untuk menerapkan ke semua penanaman di lokasi ini</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi Perawatan</label>
                        <input type="text" name="treatment_location" class="form-control" placeholder="Contoh: Bed 1-5, Seluruh lahan">
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jumlah yang Diterapkan</label>
                            <input type="number" name="amount_applied" class="form-control" step="0.01" min="0" placeholder="0.00">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Unit Pengukuran</label>
                            <input type="text" name="unit_measurement" class="form-control" placeholder="Contoh: liter, kg, ml">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nomor Batch</label>
                            <input type="text" name="batch_number" class="form-control" placeholder="Nomor batch produk">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teknisi</label>
                            <input type="text" name="technician" class="form-control" placeholder="Nama teknisi yang melakukan perawatan">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sumber Lembaga Perawatan</label>
                            <input type="text" name="institution_source" class="form-control" placeholder="Contoh: Dinas Pertanian, LSM, dll">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lampiran/Bukti Transaksi</label>
                        <input type="file" name="attachment" class="form-control" accept="image/*,.pdf,.doc,.docx">
                        <small class="text-muted">Format: JPG, PNG, PDF, DOC, DOCX</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Periode Penahanan (Hari)</label>
                            <input type="number" name="withholding_period_days" class="form-control" min="0" placeholder="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Biaya <span class="text-danger">*</span></label>
                        <input type="number" name="total_cost" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                        <small class="text-muted">Data pengeluaran akan otomatis tersimpan di halaman pengeluaran</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kata Kunci</label>
                        <input type="text" name="keywords" class="form-control" placeholder="Dipisah koma, misal: Pestisida, Hama, Semprot">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Tambahkan catatan atau komentar..."></textarea>
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
            <form action="{{ route('planting-locations.nutrients.store', $plantingLocation) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="from_planting_reports" value="1">
                <input type="hidden" name="planting_id_for_redirect" value="{{ $planting->planting_id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Data Nutrisi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Nutrisi</label>
                        <input type="text" name="nutrient_name" class="form-control" placeholder="Nama nutrisi">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Aplikasi <span class="text-danger">*</span></label>
                            <input type="date" name="application_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Produk yang Diterapkan <span class="text-danger">*</span></label>
                            <input type="text" name="product_applied" class="form-control" placeholder="Nama produk nutrisi" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jumlah yang Diterapkan <span class="text-danger">*</span></label>
                            <input type="number" name="amount_applied" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Unit <span class="text-danger">*</span></label>
                            <input type="text" name="unit" class="form-control" placeholder="Contoh: liter, kg, ml" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Metode Aplikasi <span class="text-danger">*</span></label>
                            <select name="application_method" class="form-select" required>
                                <option value="">Pilih Metode Aplikasi</option>
                                <option value="Siar">Siar</option>
                                <option value="Kompos - Padat">Kompos - Padat</option>
                                <option value="Kompos - Teh">Kompos - Teh</option>
                                <option value="Granul">Granul</option>
                                <option value="Cair">Cair</option>
                                <option value="Pupuk Kandang">Pupuk Kandang</option>
                                <option value="Pelet">Pelet</option>
                                <option value="Semprot">Semprot</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Asosiasi Penanaman</label>
                            <select name="planting_id" id="nutrient_planting_id" class="form-select">
                                <option value="umum">Umum (di lokasi penanaman ini)</option>
                                @foreach($allPlantingsForLocation as $p)
                                    <option value="{{ $p->planting_id }}" {{ $p->planting_id == $planting->planting_id ? 'selected' : '' }}>
                                        {{ $p->plant->name ?? 'Tanaman' }} 
                                        @if($p->bed_label) - {{ $p->bed_label }} @endif
                                        @if($p->planted_at) ({{ $p->planted_at->format('d M Y') }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih "Umum" untuk menerapkan ke semua penanaman di lokasi ini</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Penanggung Jawab</label>
                            <select name="responsible_person_id" class="form-select">
                                <option value="{{ auth()->user()->user_id }}" selected>{{ auth()->user()->name }}</option>
                                @foreach($locationUsers as $user)
                                    @if($user->user_id != auth()->user()->user_id)
                                        <option value="{{ $user->user_id }}">{{ $user->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teknisi</label>
                            <input type="text" name="technician" class="form-control" placeholder="Nama teknisi yang melakukan aplikasi">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sumber Lembaga Nutrisi</label>
                            <input type="text" name="institution_source" class="form-control" placeholder="Nama lembaga sumber nutrisi">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lampiran/Bukti Transaksi</label>
                        <input type="file" name="attachment" class="form-control" accept="image/*,.pdf,.doc,.docx">
                        <small class="text-muted">Format: JPG, PNG, PDF, DOC, DOCX. Maksimal 10MB</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Biaya <span class="text-danger">*</span></label>
                            <input type="number" name="total_cost" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Tambahkan catatan atau komentar..."></textarea>
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

<!-- Modal: Catatan Baru -->
<div class="modal fade" id="modalCatatanBaru" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('planting-locations.notes.store', $plantingLocation) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="from_planting_reports" value="1">
                <input type="hidden" name="planting_id_for_redirect" value="{{ $planting->planting_id }}">
                <input type="hidden" name="planting_id" value="{{ $planting->planting_id }}">
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
                        <label class="form-label">Di peruntukan untuk</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">-- Pilih User --</option>
                            <option value="all">Semua User</option>
                            @foreach($locationUsers as $user)
                                <option value="{{ $user->user_id }}">{{ $user->name }}@if($user->role) - {{ $user->role_label }}@endif</option>
                            @endforeach
                            @foreach($users->where('role', 'admin') as $admin)
                                @if(!$locationUsers->contains('user_id', $admin->user_id))
                                    <option value="{{ $admin->user_id }}">{{ $admin->name }} (Admin)</option>
                                @endif
                            @endforeach
                        </select>
                        <small class="text-muted">Pilih user yang harus melihat catatan ini</small>
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

<!-- Modal: Tambah Lampiran -->
<div class="modal fade" id="modalTambahLampiran" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('planting-locations.attachments.store', $plantingLocation) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="from_planting_reports" value="1">
                <input type="hidden" name="planting_id_for_redirect" value="{{ $planting->planting_id }}">
                <input type="hidden" name="planting_id" value="{{ $planting->planting_id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Lampiran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul Lampiran <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi Lampiran</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Lampiran Dibuat <span class="text-danger">*</span></label>
                        <input type="date" name="attachment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih File/Foto <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept="image/*,.pdf,.doc,.docx,.txt" required>
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

<!-- Modal: Lihat Laporan -->
<div class="modal fade" id="modalViewTask" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewTaskBody">
                <div class="text-center">
                    <div class="spinner-border" role="status">
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

<!-- Modal: Edit Laporan -->
<div class="modal fade" id="modalEditTask" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formEditTask" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="from_planting_reports" value="1">
                <input type="hidden" name="planting_id_for_redirect" value="{{ $planting->planting_id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="editTaskBody">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Isi Laporan -->
<div class="modal fade" id="modalFillTaskReport" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formFillTaskReport" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="from_planting_reports" value="1">
                <input type="hidden" name="planting_id_for_redirect" value="{{ $planting->planting_id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Isi Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="fillTaskReportBody">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Laporan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Detail Perawatan -->
<div class="modal fade" id="modalDetailPerawatan" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Data Perawatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailPerawatanContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
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


<!-- Modal: Detail Nutrisi -->
<div class="modal fade" id="modalDetailNutrisi" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Data Nutrisi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailNutrisiContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
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

<!-- Modal: Edit Nutrisi -->
<div class="modal fade" id="modalEditNutrisi" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formEditNutrisi" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="from_planting_reports" value="1">
                <input type="hidden" name="planting_id_for_redirect" value="{{ $planting->planting_id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Data Nutrisi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Nutrisi</label>
                        <input type="text" name="nutrient_name" id="edit_nutrient_name" class="form-control" placeholder="Nama nutrisi">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Aplikasi <span class="text-danger">*</span></label>
                            <input type="date" name="application_date" id="edit_application_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Produk yang Diterapkan <span class="text-danger">*</span></label>
                            <input type="text" name="product_applied" id="edit_product_applied" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jumlah yang Diterapkan <span class="text-danger">*</span></label>
                            <input type="number" name="amount_applied" id="edit_amount_applied" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Unit <span class="text-danger">*</span></label>
                            <input type="text" name="unit" id="edit_unit" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Metode Aplikasi <span class="text-danger">*</span></label>
                            <select name="application_method" id="edit_application_method" class="form-select" required>
                                <option value="">Pilih Metode Aplikasi</option>
                                <option value="Siar">Siar</option>
                                <option value="Kompos - Padat">Kompos - Padat</option>
                                <option value="Kompos - Teh">Kompos - Teh</option>
                                <option value="Granul">Granul</option>
                                <option value="Cair">Cair</option>
                                <option value="Pupuk Kandang">Pupuk Kandang</option>
                                <option value="Pelet">Pelet</option>
                                <option value="Semprot">Semprot</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Asosiasi Penanaman</label>
                            <select name="planting_id" id="edit_planting_id" class="form-select">
                                <option value="umum">Umum (di lokasi penanaman ini)</option>
                                @foreach($allPlantingsForLocation as $p)
                                    <option value="{{ $p->planting_id }}" {{ $p->planting_id == $planting->planting_id ? 'selected' : '' }}>
                                        {{ $p->plant->name ?? 'Tanaman' }} 
                                        @if($p->bed_label) - {{ $p->bed_label }} @endif
                                        @if($p->planted_at) ({{ $p->planted_at->format('d M Y') }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih "Umum" untuk menerapkan ke semua penanaman di lokasi ini</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Penanggung Jawab</label>
                            <select name="responsible_person_id" id="edit_responsible_person_id" class="form-select">
                                <option value="">Pilih Penanggung Jawab</option>
                                @foreach($locationUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teknisi</label>
                            <input type="text" name="technician" id="edit_technician" class="form-control" placeholder="Nama teknisi yang melakukan aplikasi">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sumber Lembaga Nutrisi</label>
                            <input type="text" name="institution_source" id="edit_institution_source" class="form-control" placeholder="Nama lembaga sumber nutrisi">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lampiran/Bukti Transaksi</label>
                        <input type="file" name="attachment" class="form-control" accept="image/*,.pdf,.doc,.docx">
                        <small class="text-muted">Format: JPG, PNG, PDF, DOC, DOCX. Maksimal 10MB. Kosongkan jika tidak ingin mengubah.</small>
                        <div id="edit_attachment_preview" class="mt-2"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Biaya</label>
                            <input type="number" name="total_cost" id="edit_total_cost" class="form-control" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3" placeholder="Tambahkan catatan atau komentar..."></textarea>
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
// Set planting_id automatically when modal opens
document.addEventListener('DOMContentLoaded', function() {
    const modalTugasBaru = document.getElementById('modalTugasBaru');
    if (modalTugasBaru) {
        modalTugasBaru.addEventListener('show.bs.modal', function() {
            const associationSelect = document.getElementById('association_planting_id');
            if (associationSelect) {
                associationSelect.value = '{{ $planting->planting_id }}';
            }
        });
    }
    
    // Auto-open modal isi laporan jika ada parameter fill_task_id dari redirect
    const urlParams = new URLSearchParams(window.location.search);
    const fillTaskId = urlParams.get('fill_task_id');
    
    // Also check for session flash message
    @if(session('fill_task_id'))
        const sessionFillTaskId = '{{ session("fill_task_id") }}';
    @else
        const sessionFillTaskId = null;
    @endif
    
    const taskIdToFill = fillTaskId || sessionFillTaskId;
    
    if (taskIdToFill) {
        // Wait a bit for page to fully load
        setTimeout(function() {
            isiLaporan(taskIdToFill);
            // Remove parameter from URL without reload
            const newUrl = window.location.pathname + window.location.hash;
            window.history.replaceState({}, document.title, newUrl);
        }, 500);
    }
    
    // Similar for treatment and nutrient modals
    const modalPerawatanBaru = document.getElementById('modalPerawatanBaru');
    if (modalPerawatanBaru) {
        modalPerawatanBaru.addEventListener('show.bs.modal', function() {
            const treatmentPlantingSelect = document.getElementById('treatment_planting_id');
            if (treatmentPlantingSelect) {
                treatmentPlantingSelect.value = '{{ $planting->planting_id }}';
            }
        });
    }
    
    const modalNutrisiBaru = document.getElementById('modalNutrisiBaru');
    if (modalNutrisiBaru) {
        modalNutrisiBaru.addEventListener('show.bs.modal', function() {
            const nutrientPlantingSelect = document.getElementById('nutrient_planting_id');
            if (nutrientPlantingSelect) {
                nutrientPlantingSelect.value = '{{ $planting->planting_id }}';
            }
        });
    }
    
    // Handle tab styling untuk pelaporan pills
    const pelaporanPills = document.querySelectorAll('#pelaporan-pills .nav-link');
    
    // Fungsi untuk mengatur styling tab dengan !important
    function setTabStyles() {
        pelaporanPills.forEach(pill => {
            const href = pill.getAttribute('href');
            const icon = pill.querySelector('i');
            const isActive = pill.classList.contains('active');
            
            if (isActive) {
                // Set warna untuk tab aktif berdasarkan href dengan !important
                if (href === '#laporan-subtab') {
                    pill.style.setProperty('background-color', '#0d6efd', 'important');
                    pill.style.setProperty('color', '#ffffff', 'important');
                    pill.style.setProperty('border-color', '#0d6efd', 'important');
                    if (icon) icon.style.setProperty('color', '#ffffff', 'important');
                } else if (href === '#perawatan-subtab') {
                    pill.style.setProperty('background-color', '#198754', 'important');
                    pill.style.setProperty('color', '#ffffff', 'important');
                    pill.style.setProperty('border-color', '#198754', 'important');
                    if (icon) icon.style.setProperty('color', '#ffffff', 'important');
                } else if (href === '#nutrisi-subtab') {
                    pill.style.setProperty('background-color', '#ffc107', 'important');
                    pill.style.setProperty('color', '#000000', 'important');
                    pill.style.setProperty('border-color', '#ffc107', 'important');
                    if (icon) icon.style.setProperty('color', '#000000', 'important');
                } else if (href === '#catatan-subtab') {
                    pill.style.setProperty('background-color', '#17a2b8', 'important');
                    pill.style.setProperty('color', '#ffffff', 'important');
                    pill.style.setProperty('border-color', '#17a2b8', 'important');
                    if (icon) icon.style.setProperty('color', '#ffffff', 'important');
                } else if (href === '#lampiran-subtab') {
                    pill.style.setProperty('background-color', '#6f42c1', 'important');
                    pill.style.setProperty('color', '#ffffff', 'important');
                    pill.style.setProperty('border-color', '#6f42c1', 'important');
                    if (icon) icon.style.setProperty('color', '#ffffff', 'important');
                }
            } else {
                // Set warna untuk tab tidak aktif dengan !important
                pill.style.setProperty('background-color', '#e9ecef', 'important');
                pill.style.setProperty('color', '#495057', 'important');
                pill.style.setProperty('border-color', '#dee2e6', 'important');
                if (icon) icon.style.setProperty('color', '#495057', 'important');
            }
        });
    }
    
    // Set styling awal saat halaman dimuat (dengan sedikit delay untuk memastikan DOM siap)
    setTimeout(function() {
        setTabStyles();
    }, 10);
    
    // Handle perubahan tab saat diklik
    pelaporanPills.forEach(pill => {
        pill.addEventListener('shown.bs.tab', function(e) {
            // Reset semua tab
            pelaporanPills.forEach(p => {
                p.classList.remove('active');
            });
            
            // Set tab yang diklik sebagai aktif
            e.target.classList.add('active');
            
            // Update semua styling tab
            setTabStyles();
        });
    });
});

// Filter functions (simplified - you may need to implement full filtering logic)
function filterTasksByStatus(status) {
    const url = new URL(window.location.href);
    url.searchParams.set('status', status);
    window.location.href = url.toString();
}

function filterTasks() {
    const status = document.getElementById('filterStatus').value;
    const assignee = document.getElementById('filterAssignee').value;
    const year = document.getElementById('filterYear').value;
    const month = document.getElementById('filterMonth').value;
    
    const url = new URL(window.location.href);
    if (status !== 'all') url.searchParams.set('status', status);
    else url.searchParams.delete('status');
    
    if (assignee !== 'all') url.searchParams.set('assignee', assignee);
    else url.searchParams.delete('assignee');
    
    if (year) url.searchParams.set('task_year', year);
    else url.searchParams.delete('task_year');
    
    if (month) url.searchParams.set('task_month', month);
    else url.searchParams.delete('task_month');
    
    window.location.href = url.toString();
}

// Checklist functions
let checklistItems = [];

function addChecklistItem() {
    const input = document.getElementById('checklist_item');
    const item = input.value.trim();
    if (item) {
        checklistItems.push(item);
        updateChecklistDisplay();
        input.value = '';
    }
}

function removeChecklistItem(index) {
    checklistItems.splice(index, 1);
    updateChecklistDisplay();
}

function updateChecklistDisplay() {
    const container = document.getElementById('checklist_items');
    const hidden = document.getElementById('checklist_hidden');
    if (!container || !hidden) return;
    
    container.innerHTML = '';
    
    checklistItems.forEach((item, index) => {
        const badge = document.createElement('span');
        badge.className = 'badge bg-primary me-2 mb-2';
        badge.innerHTML = item + ' <i class="fas fa-times ms-1" style="cursor: pointer;" onclick="removeChecklistItem(' + index + ')"></i>';
        container.appendChild(badge);
    });
    
    hidden.value = JSON.stringify(checklistItems);
}

function setActionType(type) {
    document.getElementById('action_type').value = type;
}

function saveAsTemplate() {
    document.getElementById('action_type').value = 'save_template';
    document.getElementById('formLaporanBaru').submit();
}

function saveAndFillReport() {
    document.getElementById('action_type').value = 'save_and_fill_report';
    document.getElementById('formLaporanBaru').submit();
}

function useTemplate(templateId) {
    fetch('/api/task-templates/' + templateId, {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.tasks_list && data.tasks_list.length > 0) {
            const task = data.tasks_list[0];
            const form = document.getElementById('formLaporanBaru');
            
            // Fill form with template data
            form.querySelector('input[name="title"]').value = task.title || '';
            form.querySelector('textarea[name="description"]').value = task.description || '';
            form.querySelector('textarea[name="task_report"]').value = task.task_report || '';
            form.querySelector('select[name="new_status"]').value = task.new_status || 'dalam_progress';
            form.querySelector('select[name="new_priority"]').value = task.new_priority || 'medium';
            
            // Set planting_id to current planting
            const associationSelect = form.querySelector('select[name="planting_id"]');
            if (associationSelect) {
                associationSelect.value = '{{ $planting->planting_id }}';
            }
            
            // Handle checklist
            if (task.checklist && Array.isArray(task.checklist)) {
                checklistItems = task.checklist;
                updateChecklistDisplay();
            }
            
            // Close template modal and open form modal
            bootstrap.Modal.getInstance(document.getElementById('modalGunakanTemplate')).hide();
            new bootstrap.Modal(document.getElementById('modalTugasBaru')).show();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal memuat template.');
    });
}

function editTemplate(templateId) {
    fetch('/api/task-templates/' + templateId, {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const form = document.getElementById('formEditTemplate');
        form.action = '{{ route("task-templates.update", ":id") }}'.replace(':id', templateId);
        
        const body = document.getElementById('editTemplateBody');
        const name = (data.name || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        const description = (data.description || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        
        body.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Nama Template <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="${name}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3">${description}</textarea>
            </div>
        `;
        
        new bootstrap.Modal(document.getElementById('modalEditTemplate')).show();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal memuat template.');
    });
}

function deleteTemplate(templateId) {
    if (confirm('Apakah Anda yakin ingin menghapus template ini?')) {
        fetch('{{ route("task-templates.destroy", ":id") }}'.replace(':id', templateId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (response.ok || response.redirected) {
                location.reload();
            } else {
                alert('Gagal menghapus template.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal menghapus template.');
        });
    }
}

// View Task function
function viewTask(taskId) {
    fetch('{{ route('planting-locations.tasks.view', [$plantingLocation, ':id']) }}'.replace(':id', taskId), {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const task = data.task;
        const body = document.getElementById('viewTaskBody');
        
        // Escape HTML function
        const escapeHtml = (text) => {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };
        
        // Get user names for display
        const usersData = @json($locationUsers->map(function($u) {
            return ['id' => $u->user_id, 'name' => $u->name];
        })->values());
        const adminUsers = @json($users->where('role', 'admin')->map(function($u) {
            return ['id' => $u->user_id, 'name' => $u->name];
        })->values());
        
        // Get assigned user name
        let assignedUserName = '-';
        if (task.assigned_to) {
            const assignedId = Array.isArray(task.assigned_to) ? task.assigned_to[0] : task.assigned_to;
            const allUsers = [...usersData, ...adminUsers];
            const assignedUser = allUsers.find(u => String(u.id) === String(assignedId));
            if (assignedUser) {
                assignedUserName = assignedUser.name + (assignedUser.id && adminUsers.find(a => a.id === assignedUser.id) ? ' (Admin)' : '');
            } else if (task.assigned_to === 'semua_user' || task.assigned_to === 'all') {
                assignedUserName = 'Semua User';
            } else if (task.assigned_user) {
                assignedUserName = task.assigned_user.name || '-';
            }
        }
        
        // Get created by user name
        let createdByName = '-';
        if (task.created_by) {
            const allUsers = [...usersData, ...adminUsers];
            const createdUser = allUsers.find(u => String(u.id) === String(task.created_by));
            if (createdUser) {
                createdByName = createdUser.name + (createdUser.id && adminUsers.find(a => a.id === createdUser.id) ? ' (Admin)' : '');
            } else if (task.created_by_user) {
                createdByName = task.created_by_user.name || '-';
            }
        }
        
        // Get priority label
        const priorityLabels = {
            'rendah': 'Rendah',
            'medium': 'Medium',
            'tinggi': 'Tinggi',
            'tertinggi': 'Tertinggi'
        };
        const priorityLabel = priorityLabels[task.new_priority] || task.priority_label || task.new_priority || '-';
        
        // Format date
        const formatDate = (dateStr) => {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        };
        
        // Format datetime
        const formatDateTime = (dateStr) => {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        };
        
        // Get checklist items
        let checklistHtml = '';
        if (task.checklist && Array.isArray(task.checklist) && task.checklist.length > 0) {
            task.checklist.forEach((item, index) => {
                checklistHtml += `
                    <div class="d-flex align-items-center mb-2">
                        <input type="text" class="form-control" value="${escapeHtml(item)}" readonly>
                    </div>
                `;
            });
        } else {
            checklistHtml = '<p class="text-muted mb-0">Tidak ada checklist</p>';
        }
        
        // Get status label
        const statusLabels = {
            'selesai': 'Telah dilakukan (Selesai)',
            'dalam_progress': 'Dalam progress/ akan dilakukan',
            'tidak_selesai': 'Tidak selesai'
        };
        const statusLabel = statusLabels[task.new_status] || task.status_label || task.new_status || '-';
        const statusBadgeClass = task.new_status === 'selesai' ? 'success' : (task.new_status === 'dalam_progress' ? 'info' : 'danger');
        
        // Get attachments
        let attachmentsHtml = '';
        if (task.attachments && Array.isArray(task.attachments) && task.attachments.length > 0) {
            task.attachments.forEach((attachment, index) => {
                const fileName = attachment.split('/').pop();
                const isImage = /\.(jpg|jpeg|png|gif)$/i.test(fileName);
                attachmentsHtml += `
                    <div class="mb-2">
                        <a href="/storage/${attachment}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-${isImage ? 'image' : 'file'} me-1"></i>${escapeHtml(fileName)}
                        </a>
                    </div>
                `;
            });
        } else {
            attachmentsHtml = '<p class="text-muted mb-0">Tidak ada lampiran</p>';
        }
        
        let html = `
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Judul Tugas</label>
                        <p class="mb-0">${escapeHtml(task.title || '-')}</p>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi Tugas</label>
                        <p class="mb-0">${escapeHtml(task.description || '-')}</p>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Ditugaskan Untuk</label>
                        <p class="mb-0">${assignedUserName}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pembuat Laporan</label>
                        <p class="mb-0">${createdByName}</p>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Prioritas</label>
                        <p class="mb-0"><span class="badge bg-${task.new_priority === 'tertinggi' || task.new_priority === 'tinggi' ? 'danger' : (task.new_priority === 'medium' ? 'warning' : 'secondary')}">${priorityLabel}</span></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal Tenggat</label>
                        <p class="mb-0">${formatDate(task.due_date)}</p>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Laporan</label>
                        ${task.task_report ? `
                        <div class="border p-3 rounded bg-light">
                            <p class="mb-0" style="white-space: pre-wrap;">${escapeHtml(task.task_report)}</p>
                        </div>
                        ` : '<div class="alert alert-warning mb-0"><small>Laporan belum diisi</small></div>'}
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Checklist</label>
                        <div>${checklistHtml}</div>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal Mulai</label>
                        <p class="mb-0">${formatDate(task.start_date)}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jam Mulai</label>
                        <p class="mb-0">${task.start_time ? (() => {
                            // Handle different time formats
                            if (typeof task.start_time === 'string') {
                                // If it's already in HH:mm format, return as is
                                if (/^\d{2}:\d{2}$/.test(task.start_time)) {
                                    return task.start_time;
                                }
                                // If it's a datetime string, extract time part
                                if (task.start_time.includes('T') || task.start_time.includes(' ')) {
                                    try {
                                        const date = new Date(task.start_time);
                                        return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
                                    } catch(e) {
                                        return task.start_time;
                                    }
                                }
                                return task.start_time;
                            }
                            return task.start_time || '-';
                        })() : '-'}</p>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status</label>
                        <p class="mb-0"><span class="badge bg-${statusBadgeClass}">${statusLabel}</span></p>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lampiran Laporan (Foto dan Dokumen)</label>
                        <div>${attachmentsHtml}</div>
                    </div>
                </div>
            </div>
            ${task.last_edited_at ? `
            <div class="alert alert-info">
                <small><i class="fas fa-info-circle me-1"></i>Laporan terakhir di edit pada ${formatDateTime(task.last_edited_at)}${task.last_edited_by_user ? ' oleh ' + escapeHtml(task.last_edited_by_user.name) : ''}</small>
            </div>
            ` : ''}
        `;
        
        body.innerHTML = html;
        new bootstrap.Modal(document.getElementById('modalViewTask')).show();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal memuat detail laporan.');
    });
}

// Edit Task function
function editTask(taskId) {
    fetch('{{ route('planting-locations.tasks.edit', [$plantingLocation, ':id']) }}'.replace(':id', taskId), {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error response:', text);
                let errorMsg = 'HTTP error! status: ' + response.status;
                try {
                    const errorJson = JSON.parse(text);
                    if (errorJson.error || errorJson.message) {
                        errorMsg = errorJson.error || errorJson.message;
                    }
                } catch (e) {
                    // Not JSON, use text as is
                }
                throw new Error(errorMsg);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        if (!data || !data.task) {
            console.error('Invalid data:', data);
            throw new Error('Invalid response data');
        }
        const task = data.task;
        const form = document.getElementById('formEditTask');
        if (!form) {
            throw new Error('Form not found');
        }
        form.action = '{{ route('planting-locations.tasks.update', [$plantingLocation, ':id']) }}'.replace(':id', taskId);
        
        const body = document.getElementById('editTaskBody');
        if (!body) {
            throw new Error('Modal body not found');
        }
        
        // Get all plantings and users data from server
        const plantingsData = @json($allPlantingsForLocation->map(function($p) {
            return ['id' => $p->planting_id, 'name' => $p->plant->name ?? '', 'bed_label' => $p->bed_label];
        })->values());
        const usersData = @json($locationUsers->map(function($u) {
            return ['id' => $u->user_id, 'name' => $u->name];
        })->values());
        const adminUsers = @json($users->where('role', 'admin')->map(function($u) {
            return ['id' => $u->user_id, 'name' => $u->name];
        })->values());
        
        // Build plantings options (all plantings with "Umum" option)
        let plantsOptions = '<option value="umum"' + (!task.planting_id ? ' selected' : '') + '>Umum (di lokasi penanaman ini)</option>';
        if (plantingsData && Array.isArray(plantingsData)) {
            plantingsData.forEach(planting => {
                const selected = task.planting_id && parseInt(task.planting_id) === planting.id ? ' selected' : '';
                const bedLabel = planting.bed_label ? ' (' + planting.bed_label + ')' : '';
                plantsOptions += '<option value="' + planting.id + '"' + selected + '>' + (planting.name || '') + bedLabel + '</option>';
            });
        }
        
        // Build users options (location users + admins not in location users)
        let usersOptions = '<option value="">-- Pilih User --</option>';
        // Add "Semua User" option
        const taskAssignedToAll = task.assigned_to === 'semua_user' || task.assigned_to === 'all' ? ' selected' : '';
        usersOptions += '<option value="semua_user"' + taskAssignedToAll + '>Semua User</option>';
        let usersOptionsCreatedBy = '<option value="">-- Pilih User --</option>';
        const locationUserIds = usersData.map(u => String(u.id));
        if (usersData && Array.isArray(usersData) && usersData.length > 0) {
            usersData.forEach(user => {
                const taskAssignedTo = task.assigned_to ? String(task.assigned_to) : '';
                const taskCreatedBy = task.created_by ? String(task.created_by) : '';
                const userId = user.id ? String(user.id) : '';
                const selectedAssigned = taskAssignedTo && userId && taskAssignedTo === userId ? ' selected' : '';
                const selectedCreated = taskCreatedBy && userId && taskCreatedBy === userId ? ' selected' : '';
                usersOptions += '<option value="' + (user.id || '') + '"' + selectedAssigned + '>' + (user.name || '') + '</option>';
                usersOptionsCreatedBy += '<option value="' + (user.id || '') + '"' + selectedCreated + '>' + (user.name || '') + '</option>';
            });
        }
        // Add admin users not in location users
        if (adminUsers && Array.isArray(adminUsers) && adminUsers.length > 0) {
            adminUsers.forEach(admin => {
                if (!locationUserIds.includes(String(admin.id))) {
                    const taskAssignedTo = task.assigned_to ? String(task.assigned_to) : '';
                    const taskCreatedBy = task.created_by ? String(task.created_by) : '';
                    const adminId = admin.id ? String(admin.id) : '';
                    const selectedAssigned = taskAssignedTo && adminId && taskAssignedTo === adminId ? ' selected' : '';
                    const selectedCreated = taskCreatedBy && adminId && taskCreatedBy === adminId ? ' selected' : '';
                    usersOptions += '<option value="' + (admin.id || '') + '"' + selectedAssigned + '>' + (admin.name || '') + ' (Admin)</option>';
                    usersOptionsCreatedBy += '<option value="' + (admin.id || '') + '"' + selectedCreated + '>' + (admin.name || '') + ' (Admin)</option>';
                }
            });
        }
        
        // Escape HTML untuk textarea
        const escapeHtml = (text) => {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };
        
        // Load form content
        body.innerHTML = `
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Judul Tugas <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="${escapeHtml(task.title || '')}" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Asosiasi Penanaman <span class="text-danger">*</span></label>
                        <select name="planting_id" id="edit_association_planting_id" class="form-select" required>
                            ${plantsOptions}
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Deskripsi Tugas</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Masukkan deskripsi tugas...">${escapeHtml(task.description || '')}</textarea>
                    </div>
                </div>
            </div>
            {{-- Field Laporan disembunyikan --}}
            <div class="row" style="display: none;">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Laporan </label>
                        <textarea name="task_report" class="form-control" rows="5" placeholder="Laporan tugas akan diisi oleh petugas yang ditugaskan...">${escapeHtml(task.task_report || '')}</textarea>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Field ini hanya dapat diisi oleh petugas yang ditugaskan
                        </small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Ditugaskan Untuk</label>
                        <select name="assigned_to" class="form-select">
                            ${usersOptions}
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Pembuat Laporan</label>
                        <select name="created_by" class="form-select">
                            ${usersOptionsCreatedBy}
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Prioritas <span class="text-danger">*</span></label>
                        <select name="new_priority" class="form-select" required>
                            <option value="rendah" ${task.new_priority === 'rendah' ? 'selected' : ''}>Rendah</option>
                            <option value="medium" ${task.new_priority === 'medium' ? 'selected' : ''}>Medium</option>
                            <option value="tinggi" ${task.new_priority === 'tinggi' ? 'selected' : ''}>Tinggi</option>
                            <option value="tertinggi" ${task.new_priority === 'tertinggi' ? 'selected' : ''}>Tertinggi</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="new_status" class="form-select" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="selesai" ${task.new_status === 'selesai' ? 'selected' : ''}>Telah dilakukan (Selesai)</option>
                            <option value="dalam_progress" ${task.new_status === 'dalam_progress' ? 'selected' : ''}>Dalam progress/ akan dilakukan</option>
                            <option value="tidak_selesai" ${task.new_status === 'tidak_selesai' ? 'selected' : ''}>Tidak selesai</option>
                        </select>
                    </div>
                </div>
            </div>
            {{-- Field Tanggal Mulai dan Jam Mulai disembunyikan --}}
            <div class="row" style="display: none;">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" value="${task.start_date || ''}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Jam Mulai</label>
                        <input type="time" name="start_time" class="form-control" value="${task.start_time || ''}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Tenggat <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" class="form-control" value="${task.due_date || ''}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Jam Tenggat</label>
                        <input type="time" name="due_time" class="form-control" value="${task.due_time || ''}">
                    </div>
                </div>
            </div>
        `;
        
        new bootstrap.Modal(document.getElementById('modalEditTask')).show();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal memuat data laporan untuk diedit: ' + error.message);
    });
}

// Isi Laporan function
function isiLaporan(taskId) {
    fetch('{{ route('planting-locations.tasks.view', [$plantingLocation, ':id']) }}'.replace(':id', taskId), {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error response:', text);
                let errorMsg = 'HTTP error! status: ' + response.status;
                try {
                    const errorJson = JSON.parse(text);
                    if (errorJson.error || errorJson.message) {
                        errorMsg = errorJson.error || errorJson.message;
                    }
                } catch (e) {
                    // Not JSON, use text as is
                    if (text) {
                        errorMsg = text;
                    }
                }
                throw new Error(errorMsg);
            });
        }
        return response.json();
    })
    .then(data => {
        if (!data || !data.task) {
            throw new Error('Data laporan tidak ditemukan');
        }
        const task = data.task;
        const form = document.getElementById('formFillTaskReport');
        form.action = '{{ route('planting-locations.tasks.fill', [$plantingLocation, ':id']) }}'.replace(':id', taskId);
        
        const body = document.getElementById('fillTaskReportBody');
        
        // Escape HTML function
        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
        
        // Get user names for display
        const usersData = @json($locationUsers->map(function($u) {
            return ['id' => $u->user_id, 'name' => $u->name];
        })->values());
        const adminUsers = @json($users->where('role', 'admin')->map(function($u) {
            return ['id' => $u->user_id, 'name' => $u->name];
        })->values());
        
        // Get assigned user name
        let assignedUserName = '-';
        if (task.assigned_to) {
            const assignedId = Array.isArray(task.assigned_to) ? task.assigned_to[0] : task.assigned_to;
            const allUsers = [...usersData, ...adminUsers];
            const assignedUser = allUsers.find(u => String(u.id) === String(assignedId));
            if (assignedUser) {
                assignedUserName = assignedUser.name + (assignedUser.id && adminUsers.find(a => a.id === assignedUser.id) ? ' (Admin)' : '');
            } else if (task.assigned_to === 'semua_user' || task.assigned_to === 'all') {
                assignedUserName = 'Semua User';
            }
        }
        
        // Pembuat Laporan = user yang saat ini mengisi form (otomatis, tidak dapat diubah)
        const currentUserNameForReport = @json(auth()->user()->name ?? '');
        
        // Get priority label
        const priorityLabels = {
            'rendah': 'Rendah',
            'medium': 'Medium',
            'tinggi': 'Tinggi',
            'tertinggi': 'Tertinggi'
        };
        const priorityLabel = priorityLabels[task.new_priority] || task.new_priority || '-';
        
        // Format date
        const formatDate = (dateStr) => {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
        };
        
        // Get checklist items (readonly)
        let checklistHtml = '';
        checklistItemsFill = []; // Reset checklist items
        if (task.checklist && Array.isArray(task.checklist) && task.checklist.length > 0) {
            task.checklist.forEach((item, index) => {
                const itemValue = escapeHtml(item);
                checklistItemsFill.push(item);
                checklistHtml += `
                    <div class="d-flex align-items-center mb-2">
                        <input type="text" class="form-control" value="${itemValue}" readonly>
                    </div>
                `;
            });
        } else {
            checklistHtml = '<p class="text-muted mb-0">Tidak ada checklist</p>';
        }
        
        const taskTitle = escapeHtml(task.title || '-');
        const taskDescription = escapeHtml(task.description || '-');
        const taskReport = task.task_report || '';
        const dueDate = task.due_date || '';
        // Format start_date for input type="date" (YYYY-MM-DD)
        const startDate = task.start_date ? (new Date(task.start_date).toISOString().split('T')[0]) : '';
        
        body.innerHTML = `
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Judul Tugas</label>
                        <input type="text" class="form-control" value="${taskTitle}" readonly>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Deskripsi Tugas</label>
                        <textarea class="form-control" rows="3" readonly>${taskDescription}</textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Ditugaskan Untuk</label>
                        <input type="text" class="form-control" value="${assignedUserName}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Pembuat Laporan</label>
                        <input type="text" class="form-control bg-light" value="${escapeHtml(currentUserNameForReport) || '-'}" readonly>
                        <small class="text-muted">User yang mengisi form ini (otomatis)</small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Prioritas</label>
                        <input type="text" class="form-control" value="${priorityLabel}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Tenggat</label>
                        <input type="text" class="form-control" value="${formatDate(dueDate)}" readonly>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Laporan <span class="text-danger">*</span></label>
                        <textarea name="task_report" class="form-control" rows="5" placeholder="Laporan tugas akan diisi oleh petugas yang ditugaskan..." required>${escapeHtml(taskReport)}</textarea>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Isi laporan kegiatan yang telah dilakukan
                        </small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Checklist</label>
                        <div id="checklist_items_container">
                            ${checklistHtml}
                        </div>
                        <input type="hidden" name="checklist" id="checklist_hidden_fill" value='${JSON.stringify(checklistItemsFill)}'>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="new_status" class="form-select" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="selesai" ${task.new_status === 'selesai' ? 'selected' : ''}>Telah dilakukan (Selesai)</option>
                            <option value="dalam_progress" ${task.new_status === 'dalam_progress' ? 'selected' : ''}>Dalam progress/ akan dilakukan</option>
                            <option value="tidak_selesai" ${task.new_status === 'tidak_selesai' ? 'selected' : ''}>Tidak selesai</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="${startDate}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" class="form-control" value="${task.start_time || ''}" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Lampiran Laporan (Foto dan Dokumen)</label>
                        <input type="file" name="attachments[]" class="form-control" multiple accept="image/*,.pdf,.doc,.docx">
                        <small class="text-muted">Format yang didukung: JPEG, PNG, JPG, GIF, PDF, DOC, DOCX</small>
                    </div>
                </div>
            </div>
        `;
        
        new bootstrap.Modal(document.getElementById('modalFillTaskReport')).show();
    })
    .catch(error => {
        console.error('Error:', error);
        let errorMessage = 'Gagal memuat data laporan.';
        if (error.message) {
            errorMessage += '\n' + error.message;
        }
        alert(errorMessage);
    });
}

// Helper functions for checklist in fill form (readonly, no longer needed but kept for compatibility)
let checklistItemsFill = [];

// Fill Task Report function (keep for backward compatibility)
function fillTaskReport(taskId) {
    isiLaporan(taskId);
}

// Load Treatment Detail
function loadTreatmentDetail(treatmentId) {
    const content = document.getElementById('detailPerawatanContent');
    if (!content) {
        console.error('detailPerawatanContent element not found');
        return;
    }
    content.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    const treatmentUrl = `/planting-locations/{{ $plantingLocation->planting_location_id }}/treatments/${treatmentId}`;
    fetch(treatmentUrl, {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error response:', text);
                throw new Error('HTTP error! status: ' + response.status);
            });
        }
        return response.json();
    })
    .then(data => {
        const escapeHtml = (text) => {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };
        
        let html = '';
        
        if (data.edited_at) {
            html += `<div class="alert alert-info mb-3">
                <i class="fas fa-info-circle me-2"></i>
                Laporan terakhir di edit pada ${new Date(data.edited_at).toLocaleDateString('id-ID', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })} oleh ${data.editor ? data.editor.name : '-'}
            </div>`;
        }
        
        html += `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <strong>Nama Perawatan/Gangguan:</strong>
                    <p>${escapeHtml(data.treatment_name || '-')}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Tanggal Perawatan:</strong>
                    <p>${data.treatment_date ? new Date(data.treatment_date).toLocaleDateString('id-ID') : '-'}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <strong>Tipe Perawatan:</strong>
                    <p>${escapeHtml(data.treatment_type || '-')}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Penanggung Jawab:</strong>
                    <p>${data.responsible_person ? escapeHtml(data.responsible_person.name) : '-'}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <strong>Detail Produk:</strong>
                    <p>${escapeHtml(data.product_detail || '-')}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Metode Aplikasi:</strong>
                    <p>${escapeHtml(data.application_method || '-')}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <strong>Asosiasi Penanaman:</strong>
                    <p>${data.planting ? escapeHtml(data.planting.name) : 'Umum (Lahan Ini)'}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Lokasi Perawatan:</strong>
                    <p>${escapeHtml(data.treatment_location || '-')}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <strong>Jumlah yang Diterapkan:</strong>
                    <p>${data.amount_applied ? data.amount_applied + ' ' + (data.unit_measurement || '') : '-'}</p>
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Unit Pengukuran:</strong>
                    <p>${escapeHtml(data.unit_measurement || '-')}</p>
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Nomor Batch:</strong>
                    <p>${escapeHtml(data.batch_number || '-')}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <strong>Teknisi:</strong>
                    <p>${escapeHtml(data.technician || '-')}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Sumber Lembaga Perawatan:</strong>
                    <p>${escapeHtml(data.institution_source || '-')}</p>
                </div>
            </div>
            ${data.attachment ? `
                <div class="mb-3">
                    <strong>Lampiran/Bukti Transaksi:</strong>
                    <p><a href="/storage/${data.attachment}" target="_blank" class="text-primary"><i class="fas fa-file me-1"></i>Lihat Lampiran</a></p>
                </div>
            ` : ''}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <strong>Periode Penahanan (Hari):</strong>
                    <p>${data.withholding_period_days || '-'}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Total Biaya:</strong>
                    <p>${data.total_cost ? 'Rp ' + new Intl.NumberFormat('id-ID').format(data.total_cost) : '-'}</p>
                </div>
            </div>
            <div class="mb-3">
                <strong>Kata Kunci:</strong>
                <p>${escapeHtml(data.keywords || '-')}</p>
            </div>
            <div class="mb-3">
                <strong>Deskripsi:</strong>
                <p>${escapeHtml(data.description || '-')}</p>
            </div>
        `;
        
        content.innerHTML = html;
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = '<div class="alert alert-danger">Gagal memuat data perawatan.</div>';
    });
}


// Load Nutrient Detail
function loadNutrientDetail(nutrientId) {
    const content = document.getElementById('detailNutrisiContent');
    if (!content) {
        console.error('detailNutrisiContent element not found');
        return;
    }
    content.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    const nutrientUrl = `/planting-locations/{{ $plantingLocation->planting_location_id }}/nutrients/${nutrientId}`;
    fetch(nutrientUrl, {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error response:', text);
                throw new Error('HTTP error! status: ' + response.status);
            });
        }
        return response.json();
    })
    .then(data => {
        let html = '';
        
        if (data.edited_at) {
            html += `<div class="alert alert-info mb-3">
                <i class="fas fa-info-circle me-2"></i>
                Laporan terakhir di edit pada ${new Date(data.edited_at).toLocaleDateString('id-ID', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })} oleh ${data.editor ? data.editor.name : '-'}
            </div>`;
        }
        
        html += `
            <div class="mb-3">
                <strong>Nama Nutrisi:</strong><br>
                ${data.nutrient_name || '-'}
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <strong>Tanggal Aplikasi:</strong><br>
                    ${new Date(data.application_date + 'T00:00:00').toLocaleDateString('id-ID', { 
                        weekday: 'long', 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    })}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Produk yang Diterapkan:</strong><br>
                    ${data.product_applied}
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <strong>Jumlah yang Diterapkan:</strong><br>
                    ${parseFloat(data.amount_applied).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})} ${data.unit}
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Metode Aplikasi:</strong><br>
                    ${data.application_method || '-'}
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Asosiasi Penanaman:</strong><br>
                    ${data.planting ? data.planting.name : '<span class="text-muted">Umum</span>'}
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <strong>Penanggung Jawab:</strong><br>
                    ${data.responsible_person ? data.responsible_person.name : '-'}
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <strong>Teknisi:</strong><br>
                    ${data.technician || '-'}
                </div>
                <div class="col-md-6 mb-3">
                    <strong>Sumber Lembaga Nutrisi:</strong><br>
                    ${data.institution_source || '-'}
                </div>
            </div>
            <div class="mb-3">
                <strong>Lampiran/Bukti Transaksi:</strong><br>
                ${data.attachment ? `<a href="/storage/${data.attachment}" target="_blank" class="btn btn-sm btn-primary"><i class="fas fa-file-download me-1"></i>Download Lampiran</a>` : '-'}
            </div>
            <div class="mb-3">
                <strong>Total Biaya:</strong><br>
                ${data.total_cost ? 'Rp ' + parseFloat(data.total_cost).toLocaleString('id-ID') : '-'}
            </div>
            <div class="mb-3">
                <strong>Deskripsi:</strong><br>
                ${data.description || '-'}
            </div>
        `;
        
        content.innerHTML = html;
    })
    .catch(error => {
        console.error('Error:', error);
        if (content) {
            content.innerHTML = '<div class="alert alert-danger">Gagal memuat data nutrisi.</div>';
        }
    });
}

// Load Nutrient Edit
function loadNutrientEdit(nutrientId) {
    const nutrientUrl = `/planting-locations/{{ $plantingLocation->planting_location_id }}/nutrients/${nutrientId}`;
    fetch(nutrientUrl, {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error response:', text);
                throw new Error('HTTP error! status: ' + response.status);
            });
        }
        return response.json();
    })
    .then(data => {
        document.getElementById('edit_nutrient_name').value = data.nutrient_name || '';
        document.getElementById('edit_application_date').value = data.application_date;
        document.getElementById('edit_product_applied').value = data.product_applied;
        document.getElementById('edit_amount_applied').value = data.amount_applied;
        document.getElementById('edit_unit').value = data.unit;
        document.getElementById('edit_application_method').value = data.application_method;
        const editPlantingSelect = document.getElementById('edit_planting_id');
        if (editPlantingSelect) {
            editPlantingSelect.value = data.planting_id || 'umum';
        }
        document.getElementById('edit_responsible_person_id').value = data.responsible_person_id || '';
        document.getElementById('edit_technician').value = data.technician || '';
        document.getElementById('edit_institution_source').value = data.institution_source || '';
        document.getElementById('edit_total_cost').value = data.total_cost || '';
        document.getElementById('edit_description').value = data.description || '';
        
        // Show attachment preview if exists
        const attachmentPreview = document.getElementById('edit_attachment_preview');
        if (data.attachment && attachmentPreview) {
            attachmentPreview.innerHTML = `
                <small class="text-muted">Lampiran saat ini: 
                    <a href="/storage/${data.attachment}" target="_blank" class="text-primary">
                        <i class="fas fa-file me-1"></i>Lihat Lampiran
                    </a>
                </small>
            `;
        } else if (attachmentPreview) {
            attachmentPreview.innerHTML = '';
        }
        
        const updateUrl = `/planting-locations/{{ $plantingLocation->planting_location_id }}/nutrients/${nutrientId}`;
        document.getElementById('formEditNutrisi').action = updateUrl;
        
        new bootstrap.Modal(document.getElementById('modalEditNutrisi')).show();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal memuat data nutrisi untuk diedit.');
    });
}

// View Note function
function viewNote(noteId) {
    fetch('{{ route('planting-locations.notes.view', [$plantingLocation, ':id']) }}'.replace(':id', noteId), {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.note) {
            const note = data.note;
            const modalBody = document.getElementById('viewNoteBody');
            
            // Build assigned users HTML
            let assignedUsersHtml = '';
            if (note.assigned_users && note.assigned_users.length > 0) {
                note.assigned_users.forEach(user => {
                    assignedUsersHtml += `<span class="badge bg-info me-1">${escapeHtml(user.name)}</span>`;
                });
            } else {
                assignedUsersHtml = '<span class="text-muted">-</span>';
            }
            
            // Build read status HTML
            let readStatusHtml = '';
            if (note.is_read) {
                readStatusHtml = '<span class="badge bg-success"><i class="fas fa-check"></i> Sudah dibaca</span>';
            } else {
                readStatusHtml = '<span class="badge bg-warning"><i class="fas fa-exclamation-circle"></i> Belum dibaca</span>';
            }
            
            // Build attachment HTML
            let attachmentHtml = '';
            if (note.attachment_path) {
                attachmentHtml = `<a href="/storage/${note.attachment_path}" target="_blank" class="btn btn-sm btn-primary">
                    <i class="fas fa-download"></i> Unduh Lampiran
                </a>`;
            } else {
                attachmentHtml = '<span class="text-muted">Tidak ada lampiran</span>';
            }
            
            modalBody.innerHTML = `
                <div class="mb-3">
                    <strong>Tanggal Catatan:</strong><br>
                    ${note.note_date || '-'}
                </div>
                ${note.title ? `
                <div class="mb-3">
                    <strong>Judul:</strong><br>
                    ${escapeHtml(note.title)}
                </div>
                ` : ''}
                <div class="mb-3">
                    <strong>Deskripsi:</strong><br>
                    <div class="border p-3 rounded bg-light">
                        ${escapeHtml(note.description || '-')}
                    </div>
                </div>
                ${note.keywords ? `
                <div class="mb-3">
                    <strong>Kata Kunci:</strong><br>
                    ${escapeHtml(note.keywords)}
                </div>
                ` : ''}
                <div class="mb-3">
                    <strong>Pembuat:</strong><br>
                    ${note.user ? escapeHtml(note.user.name) : '-'}
                </div>
                <div class="mb-3">
                    <strong>Diperuntukan untuk:</strong><br>
                    ${assignedUsersHtml}
                </div>
                <div class="mb-3">
                    <strong>Status:</strong><br>
                    ${readStatusHtml}
                </div>
                <div class="mb-3">
                    <strong>Lampiran:</strong><br>
                    ${attachmentHtml}
                </div>
            `;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('modalViewNote'));
            modal.show();
        } else {
            alert('Gagal memuat detail catatan.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memuat detail catatan.');
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function markNoteAsRead(noteId) {
    if (!confirm('Apakah Anda yakin ingin menandai catatan ini sebagai sudah dibaca?')) {
        return;
    }
    
    fetch('{{ route('planting-locations.notes.mark-read', [$plantingLocation, ':id']) }}'.replace(':id', noteId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Gagal menandai catatan sebagai sudah dibaca');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menandai catatan');
    });
}

// Load attachment detail
function loadAttachmentDetail(attachmentId) {
    const content = document.getElementById('detailLampiranContent');
    if (!content) {
        console.error('detailLampiranContent element not found');
        return;
    }
    content.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    fetch(`{{ route('planting-locations.attachments.show', [$plantingLocation, ':id']) }}`.replace(':id', attachmentId))
        .then(response => response.json())
        .then(data => {
            let html = '';
            
            if (data.edited_at) {
                html += `<div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Laporan terakhir di edit pada ${new Date(data.edited_at).toLocaleDateString('id-ID', { 
                        weekday: 'long', 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    })} oleh ${data.editor ? data.editor.name : '-'}
                </div>`;
            }
            
            html += `
                <div class="mb-3">
                    <strong>Judul Lampiran:</strong><br>
                    ${data.title}
                </div>
                <div class="mb-3">
                    <strong>Deskripsi Lampiran:</strong><br>
                    ${data.description || '-'}
                </div>
                <div class="mb-3">
                    <strong>Tanggal Lampiran Dibuat:</strong><br>
                    ${new Date(data.attachment_date + 'T00:00:00').toLocaleDateString('id-ID', { 
                        weekday: 'long', 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    })}
                </div>
                <div class="mb-3">
                    <strong>Pembuat:</strong><br>
                    ${data.creator ? data.creator.name : '-'}
                </div>
                <div class="mb-3">
                    <strong>File:</strong><br>
                    <a href="/storage/${data.file_path}" target="_blank" class="btn btn-sm btn-primary">
                        <i class="fas fa-download me-1"></i>Download File
                    </a>
                </div>
            `;
            
            content.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div class="alert alert-danger">Gagal memuat data lampiran.</div>';
        });
}

// Load attachment edit
function loadAttachmentEdit(attachmentId) {
    fetch(`{{ route('planting-locations.attachments.show', [$plantingLocation, ':id']) }}`.replace(':id', attachmentId))
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_title').value = data.title;
            document.getElementById('edit_description').value = data.description || '';
            document.getElementById('edit_attachment_date').value = data.attachment_date;
            document.getElementById('formEditLampiran').action = `{{ route('planting-locations.attachments.update', [$plantingLocation, ':id']) }}`.replace(':id', attachmentId);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat data lampiran untuk diedit.');
        });
}
</script>
@endpush

<!-- Modal: View Note -->
<div class="modal fade" id="modalViewNote" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Catatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewNoteBody">
                <div class="text-center">
                    <div class="spinner-border" role="status">
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

<!-- Modal: Detail Lampiran -->
<div class="modal fade" id="modalDetailLampiran" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Lampiran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailLampiranContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
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

<!-- Modal: Edit Lampiran -->
<div class="modal fade" id="modalEditLampiran" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEditLampiran" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="from_planting_reports" value="1">
                <input type="hidden" name="planting_id_for_redirect" value="{{ $planting->planting_id }}">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Lampiran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul Lampiran <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi Lampiran</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Lampiran Dibuat <span class="text-danger">*</span></label>
                        <input type="date" name="attachment_date" id="edit_attachment_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih File/Foto (Kosongkan jika tidak ingin mengubah)</label>
                        <input type="file" name="file" class="form-control" accept="image/*,.pdf,.doc,.docx,.txt">
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
@endsection

m