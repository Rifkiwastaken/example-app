@extends('layouts.app')

@section('title', 'Template Tugas - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Template Tugas</h4>
    <a href="{{ route('task-templates.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-2"></i>Template Baru
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nama Template</th>
                        <th>Deskripsi</th>
                        <th>Asosiasi</th>
                        <th>Jumlah Tugas</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                    <tr>
                        <td>
                            <strong>{{ $template->name }}</strong>
                            @if($template->tasks_count > 0)
                                <br><small class="text-muted">{{ $template->tasks_count }} tugas dibuat</small>
                            @endif
                        </td>
                        <td>
                            @if($template->description)
                                {{ Str::limit($template->description, 100) }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $template->association_label }}</span>
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $template->tasks_count }} Tugas</span>
                        </td>
                        <td>
                            @if($template->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('task-templates.show', $template) }}" class="btn btn-sm btn-outline-info" title="Lihat">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('task-templates.edit', $template) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('task-templates.create-series', $template) }}" class="btn btn-sm btn-outline-primary" title="Buat Series">
                                    <i class="fas fa-list"></i>
                                </a>
                                <form action="{{ route('task-templates.destroy', $template) }}" method="POST" class="d-inline" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus template ini?')">
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
                        <td colspan="6" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-copy fa-3x mb-3"></i>
                                <p>Belum ada template tugas yang dibuat.</p>
                                <a href="{{ route('task-templates.create') }}" class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i>Buat Template Pertama
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($templates->hasPages())
            <div class="d-flex justify-content-center">
                {{ $templates->links() }}
            </div>
        @endif
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Statistik Template</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <h4 class="text-success">{{ $templates->where('is_active', true)->count() }}</h4>
                        <small class="text-muted">Aktif</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-secondary">{{ $templates->where('is_active', false)->count() }}</h4>
                        <small class="text-muted">Nonaktif</small>
                    </div>
                    <div class="col-4">
                        <h4 class="text-info">{{ $templates->count() }}</h4>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Template Berdasarkan Asosiasi</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted">Penanaman:</small><br>
                        <span class="badge bg-warning">{{ $templates->where('association', 'penanaman')->count() }} Template</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Sertifikasi:</small><br>
                        <span class="badge bg-info">{{ $templates->where('association', 'sertifikasi')->count() }} Template</span>
                    </div>
                    <div class="col-6 mt-2">
                        <small class="text-muted">Gudang:</small><br>
                        <span class="badge bg-info">{{ $templates->where('association', 'gudang')->count() }} Template</span>
                    </div>
                    <div class="col-6 mt-2">
                        <small class="text-muted">Penjualan:</small><br>
                        <span class="badge bg-info">{{ $templates->where('association', 'penjualan')->count() }} Template</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection















