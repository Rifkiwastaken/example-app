@extends('layouts.app')

@section('title', 'Detail Tugas - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Detail Tugas: {{ $task->title }}</h4>
    <div>
        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
        <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informasi Tugas</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Judul Tugas:</strong></div>
                    <div class="col-sm-9">{{ $task->title }}</div>
                </div>
                
                @if($task->description)
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Deskripsi:</strong></div>
                    <div class="col-sm-9">{{ $task->description }}</div>
                </div>
                @endif
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Asosiasi:</strong></div>
                    <div class="col-sm-9">
                        <span class="badge bg-info">{{ $task->association_label }}</span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Ditugaskan untuk:</strong></div>
                    <div class="col-sm-9">
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
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Prioritas:</strong></div>
                    <div class="col-sm-9">
                        @if($task->priority === 'tertinggi')
                            <span class="badge bg-danger">{{ $task->priority_label }}</span>
                        @elseif($task->priority === 'tinggi')
                            <span class="badge bg-warning">{{ $task->priority_label }}</span>
                        @elseif($task->priority === 'medium')
                            <span class="badge bg-secondary">{{ $task->priority_label }}</span>
                        @else
                            <span class="badge bg-success">{{ $task->priority_label }}</span>
                        @endif
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Status:</strong></div>
                    <div class="col-sm-9">
                        @if($task->status === 'selesai')
                            <span class="badge bg-success">{{ $task->status_label }}</span>
                        @elseif($task->status === 'dalam_progress')
                            <span class="badge bg-warning">{{ $task->status_label }}</span>
                        @elseif($task->status === 'tidak_selesai')
                            <span class="badge bg-danger">{{ $task->status_label }}</span>
                        @elseif($task->status === 'terlewat')
                            <span class="badge bg-danger">{{ $task->status_label }}</span>
                        @elseif($task->status === 'ditinggalkan')
                            <span class="badge bg-dark">{{ $task->status_label }}</span>
                        @else
                            <span class="badge bg-secondary">{{ $task->status_label }}</span>
                        @endif
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Tanggal Mulai:</strong></div>
                    <div class="col-sm-9">
                        @if($task->start_date)
                            {{ $task->start_date->format('d M Y') }}
                            @if($task->start_time)
                                - {{ \Carbon\Carbon::parse($task->start_time)->format('H:i') }}
                            @endif
                        @else
                            <span class="text-muted">Belum ditentukan</span>
                        @endif
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Tanggal Tenggat:</strong></div>
                    <div class="col-sm-9">
                        {{ $task->due_date->format('d M Y') }}
                        @if($task->due_time)
                            - {{ \Carbon\Carbon::parse($task->due_time)->format('H:i') }}
                        @endif
                        @if($task->isOverdue())
                            <br><small class="text-danger">
                                <i class="fas fa-exclamation-triangle"></i> Terlambat
                            </small>
                        @endif
                    </div>
                </div>
                
                @if($task->template)
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Template:</strong></div>
                    <div class="col-sm-9">
                        <span class="badge bg-primary">{{ $task->template->name }}</span>
                    </div>
                </div>
                @endif
                
                @if($task->series)
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Series:</strong></div>
                    <div class="col-sm-9">
                        <span class="badge bg-info">{{ $task->series->name }}</span>
                    </div>
                </div>
                @endif
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Dibuat:</strong></div>
                    <div class="col-sm-9">{{ $task->created_at->format('d M Y H:i') }}</div>
                </div>
                
                <div class="row">
                    <div class="col-sm-3"><strong>Terakhir Diupdate:</strong></div>
                    <div class="col-sm-9">{{ $task->updated_at->format('d M Y H:i') }}</div>
                </div>
            </div>
        </div>
        
        @if($task->task_report)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Laporan Tugas</h5>
            </div>
            <div class="card-body">
                <p>{{ $task->task_report }}</p>
            </div>
        </div>
        @endif
        
        @if($task->checklist && count($task->checklist) > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Checklist</h5>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    @foreach($task->checklist as $item)
                    <li class="list-group-item">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="checklist_{{ $loop->index }}">
                            <label class="form-check-label" for="checklist_{{ $loop->index }}">
                                {{ $item }}
                            </label>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-md-4">
        @if($task->attachments && count($task->attachments) > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Lampiran</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($task->attachments as $attachment)
                    <div class="col-12 mb-3">
                        <div class="card">
                            <div class="card-body p-2 text-center">
                                @if(str_contains($attachment, '.jpg') || str_contains($attachment, '.jpeg') || str_contains($attachment, '.png') || str_contains($attachment, '.gif'))
                                    <img src="{{ asset('storage/' . $attachment) }}" class="img-fluid rounded" style="max-height: 150px;">
                                @else
                                    <i class="fas fa-file fa-3x text-muted mb-2"></i>
                                    <br>
                                @endif
                                <small class="text-muted d-block">{{ basename($attachment) }}</small>
                                <a href="{{ asset('storage/' . $attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                    <i class="fas fa-download me-1"></i>Download
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Edit Tugas
                    </a>
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" 
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash me-2"></i>Hapus Tugas
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection















