@extends('layouts.app')

@section('title', 'Buat Template Tugas - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Buat Template Tugas Baru</h4>
    <a href="{{ route('task-templates.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('task-templates.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Template <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="association" class="form-label">Asosiasi <span class="text-danger">*</span></label>
                        <select class="form-select @error('association') is-invalid @enderror" id="association" name="association" required>
                            <option value="">Pilih Asosiasi</option>
                            @foreach($associations as $key => $label)
                                <option value="{{ $key }}" {{ old('association') == $key ? 'selected' : '' }}>
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
                <label for="description" class="form-label">Deskripsi Template</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                          id="description" name="description" rows="3" 
                          placeholder="Masukkan deskripsi template...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-4">
                <label class="form-label">Daftar Tugas dalam Template</label>
                <div id="tasks-container">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Judul Tugas</label>
                                    <input type="text" class="form-control" name="tasks_list[0][title]" placeholder="Masukkan judul tugas">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Deskripsi Tugas</label>
                                    <input type="text" class="form-control" name="tasks_list[0][description]" placeholder="Masukkan deskripsi tugas">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-success" onclick="addTaskItem()">
                    <i class="fas fa-plus me-2"></i>Tambah Daftar Tugas
                </button>
                <div class="form-text">
                    <i class="fas fa-info-circle me-1"></i>
                    Klik tombol "Tambah Daftar Tugas" untuk menambahkan tugas ke template
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('task-templates.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Simpan Template
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let taskIndex = 1;

function addTaskItem() {
    const container = document.getElementById('tasks-container');
    const taskCard = document.createElement('div');
    taskCard.className = 'card mb-3';
    taskCard.innerHTML = `
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Tugas ${taskIndex + 1}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTaskItem(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Judul Tugas</label>
                    <input type="text" class="form-control" name="tasks_list[${taskIndex}][title]" placeholder="Masukkan judul tugas">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Deskripsi Tugas</label>
                    <input type="text" class="form-control" name="tasks_list[${taskIndex}][description]" placeholder="Masukkan deskripsi tugas">
                </div>
            </div>
        </div>
    `;
    container.appendChild(taskCard);
    taskIndex++;
}

function removeTaskItem(button) {
    button.closest('.card').remove();
}
</script>
@endsection















