@extends('layouts.app')

@section('title', 'Edit Tugas - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Tugas: {{ $task->title }}</h4>
    <a href="{{ $returnToPlantingLocation ? route('planting-locations.show', $returnToPlantingLocation) : route('tasks.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('tasks.update', $task) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @if($returnToPlantingLocation)
                <input type="hidden" name="return_to_planting_location" value="{{ $returnToPlantingLocation }}">
            @endif
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="title" class="form-label">Judul Tugas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title', $task->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="association" class="form-label">Asosiasi Tugas <span class="text-danger">*</span></label>
                        <select class="form-select @error('association') is-invalid @enderror" id="association" name="association" required>
                            <option value="">Pilih Asosiasi</option>
                            @foreach($associations as $key => $label)
                                <option value="{{ $key }}" {{ old('association', $task->association) == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
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
                          placeholder="Masukkan deskripsi tugas...">{{ old('description', $task->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="task_report" class="form-label">Laporan Tugas</label>
                <textarea class="form-control @error('task_report') is-invalid @enderror" 
                          id="task_report" name="task_report" rows="3" 
                          placeholder="Laporan tugas akan diisi oleh petugas yang ditugaskan...">{{ old('task_report', $task->task_report) }}</textarea>
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
                    @if($task->checklist && is_array($task->checklist) && count($task->checklist) > 0)
                        @foreach($task->checklist as $index => $item)
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" name="checklist[]" value="{{ $item }}" placeholder="Masukkan item checklist">
                                <button type="button" class="btn btn-outline-danger" onclick="removeChecklistItem(this)">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        @endforeach
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" name="checklist[]" placeholder="Masukkan item checklist">
                            <button type="button" class="btn btn-outline-success" onclick="addChecklistItem()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    @else
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" name="checklist[]" placeholder="Masukkan item checklist">
                            <button type="button" class="btn btn-outline-success" onclick="addChecklistItem()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    @endif
                </div>
                <div class="form-text">
                    <i class="fas fa-info-circle me-1"></i>
                    Klik tombol + untuk menambahkan item checklist
                </div>
            </div>
            
            @if($task->attachments && count($task->attachments) > 0)
            <div class="mb-3">
                <label class="form-label">Lampiran Saat Ini</label>
                <div class="row">
                    @foreach($task->attachments as $attachment)
                    <div class="col-md-3 mb-2">
                        <div class="card">
                            <div class="card-body p-2 text-center">
                                @if(str_contains($attachment, '.jpg') || str_contains($attachment, '.jpeg') || str_contains($attachment, '.png') || str_contains($attachment, '.gif'))
                                    <img src="{{ asset('storage/' . $attachment) }}" class="img-thumbnail" style="max-height: 80px;">
                                @else
                                    <i class="fas fa-file fa-2x text-muted"></i>
                                @endif
                                <br>
                                <small class="text-muted">{{ basename($attachment) }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            <div class="mb-3">
                <label for="attachments" class="form-label">Tambah Lampiran Baru (Foto dan Dokumen)</label>
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
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to', $task->assigned_to) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->role_label }})
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="new_status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('new_status') is-invalid @enderror" id="new_status" name="new_status" required>
                            <option value="">Pilih Status</option>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" {{ old('new_status', $task->new_status ?? $task->status) == $key ? 'selected' : '' }}>
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
                        <label for="new_priority" class="form-label">Prioritas <span class="text-danger">*</span></label>
                        <select class="form-select @error('new_priority') is-invalid @enderror" id="new_priority" name="new_priority" required>
                            <option value="">Pilih Prioritas</option>
                            @foreach($priorities as $key => $label)
                                <option value="{{ $key }}" {{ old('new_priority', $task->new_priority ?? $task->priority) == $key ? 'selected' : '' }}>
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
                               id="due_date" name="due_date" value="{{ old('due_date', $task->due_date) }}" required>
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
                               id="start_date" name="start_date" value="{{ old('start_date', $task->start_date) }}">
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="start_time" class="form-label">Jam Mulai</label>
                        <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                               id="start_time" name="start_time" value="{{ old('start_time', $task->start_time ? \Carbon\Carbon::parse($task->start_time)->format('H:i') : '') }}">
                        @error('start_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="due_time" class="form-label">Jam Tenggat</label>
                        <input type="time" class="form-control @error('due_time') is-invalid @enderror" 
                               id="due_time" name="due_time" value="{{ old('due_time', $task->due_time ? \Carbon\Carbon::parse($task->due_time)->format('H:i') : '') }}">
                        @error('due_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ $returnToPlantingLocation ? route('planting-locations.show', $returnToPlantingLocation) : route('tasks.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Update Tugas
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
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
    const container = document.getElementById('checklist-container');
    const inputGroups = container.querySelectorAll('.input-group');
    // Don't remove if it's the last item
    if (inputGroups.length > 1) {
        button.closest('.input-group').remove();
    } else {
        // If it's the last item, just clear the input
        const input = button.closest('.input-group').querySelector('input');
        if (input) {
            input.value = '';
        }
    }
}
</script>
@endpush
@endsection