@extends('layouts.app')

@section('title', 'Manajemen Tugas - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Manajemen Tugas</h4>
    <div class="btn-group">
        <a href="{{ route('tasks.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>Tambah Tugas
        </a>
        <a href="{{ route('tasks.create-from-template') }}" class="btn btn-primary">
            <i class="fas fa-copy me-2"></i>Buat dari Template
        </a>
        <a href="{{ route('task-templates.index') }}" class="btn btn-info">
            <i class="fas fa-cog me-2"></i>Kelola Template
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('tasks.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="association" class="form-label">Asosiasi Tugas</label>
                <select class="form-select" id="association" name="association">
                    <option value="">Semua Asosiasi</option>
                    @foreach($associations as $key => $label)
                        <option value="{{ $key }}" {{ request('association') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="assigned_to" class="form-label">Ditugaskan Untuk</label>
                <select class="form-select" id="assigned_to" name="assigned_to">
                    <option value="">Semua User</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Judul Tugas</th>
                        <th>Ditugaskan Untuk</th>
                        <th>Asosiasi</th>
                        <th>Prioritas</th>
                        <th>Tenggat</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                    <tr class="{{ $task->isOverdue() ? 'table-warning' : '' }}">
                        <td>
                            <div>
                                <strong>{{ $task->title }}</strong>
                                @if($task->template)
                                    <br><small class="text-muted">Template: {{ $task->template->name }}</small>
                                @endif
                                @if($task->series)
                                    <br><small class="text-muted">Series: {{ $task->series->name }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($task->assignedUser)
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center text-white" 
                                         style="width: 30px; height: 30px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        {{ $task->assignedUser->name }}
                                        <br><small class="text-muted">{{ $task->assignedUser->role_label }}</small>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">Belum ditugaskan</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $task->association_label }}</span>
                        </td>
                        <td>
                            @if($task->new_priority === 'tertinggi')
                                <span class="badge bg-danger">{{ $task->priority_label }}</span>
                            @elseif($task->new_priority === 'tinggi')
                                <span class="badge bg-warning">{{ $task->priority_label }}</span>
                            @elseif($task->new_priority === 'medium')
                                <span class="badge bg-secondary">{{ $task->priority_label }}</span>
                            @else
                                <span class="badge bg-success">{{ $task->priority_label }}</span>
                            @endif
                        </td>
                        <td>
                            <div>
                                {{ $task->due_date->format('d M Y') }}
                                @if($task->due_time)
                                    <br><small class="text-muted">{{ \Carbon\Carbon::parse($task->due_time)->format('H:i') }}</small>
                                @endif
                            </div>
                            @if($task->isOverdue())
                                <small class="text-danger">
                                    <i class="fas fa-exclamation-triangle"></i> Terlambat
                                </small>
                            @endif
                        </td>
                        <td>
                            @if($task->new_status === 'selesai')
                                <span class="badge bg-success">{{ $task->status_label }}</span>
                            @elseif($task->new_status === 'dalam_progress')
                                <span class="badge bg-warning">{{ $task->status_label }}</span>
                            @elseif($task->new_status === 'tidak_selesai')
                                <span class="badge bg-danger">{{ $task->status_label }}</span>
                            @elseif($task->new_status === 'terlewat')
                                <span class="badge bg-danger">{{ $task->status_label }}</span>
                            @elseif($task->new_status === 'ditinggalkan')
                                <span class="badge bg-dark">{{ $task->status_label }}</span>
                            @else
                                <span class="badge bg-secondary">{{ $task->status_label }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-info" title="Lihat">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-tasks fa-3x mb-3"></i>
                                <p>Belum ada tugas yang dibuat.</p>
                                <a href="{{ route('tasks.create') }}" class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i>Tambah Tugas Pertama
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($tasks->hasPages())
            <div class="d-flex justify-content-center">
                {{ $tasks->links() }}
            </div>
        @endif
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Statistik Tugas</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-3">
                        <h4 class="text-success">{{ $tasks->where('new_status', 'selesai')->count() }}</h4>
                        <small class="text-muted">Selesai</small>
                    </div>
                    <div class="col-3">
                        <h4 class="text-warning">{{ $tasks->where('new_status', 'dalam_progress')->count() }}</h4>
                        <small class="text-muted">Progress</small>
                    </div>
                    <div class="col-3">
                        <h4 class="text-info">{{ $tasks->where('new_status', 'dilakukan')->count() }}</h4>
                        <small class="text-muted">Dilakukan</small>
                    </div>
                    <div class="col-3">
                        <h4 class="text-danger">{{ $tasks->where('new_status', 'tidak_selesai')->count() }}</h4>
                        <small class="text-muted">Tidak Selesai</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Tugas Berdasarkan Asosiasi</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted">Penanaman:</small><br>
                        <span class="badge bg-warning">{{ $tasks->where('association', 'penanaman')->count() }} Tugas</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Sertifikasi:</small><br>
                        <span class="badge bg-info">{{ $tasks->where('association', 'sertifikasi')->count() }} Tugas</span>
                    </div>
                    <div class="col-6 mt-2">
                        <small class="text-muted">Gudang:</small><br>
                        <span class="badge bg-info">{{ $tasks->where('association', 'gudang')->count() }} Tugas</span>
                    </div>
                    <div class="col-6 mt-2">
                        <small class="text-muted">Penjualan:</small><br>
                        <span class="badge bg-info">{{ $tasks->where('association', 'penjualan')->count() }} Tugas</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection