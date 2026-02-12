@extends('layouts.app')

@section('title', 'Manajemen Akun - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Manajemen Akun</h4>
    @if(auth()->user()->isAdmin())
    <a href="{{ route('users.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-2"></i>Akun Baru
    </a>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nama Akun</th>
                        <th>Email</th>
                        <th>Role/Peran</th>
                        <th>Lokasi Penempatan</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($user->photo_path)
                                    <img src="{{ Storage::url($user->photo_path) }}" class="rounded-circle me-3" 
                                         style="width: 40px; height: 40px; object-fit: cover;" alt="{{ $user->name }}">
                                @else
                                    <div class="bg-primary rounded-circle me-3 d-flex align-items-center justify-content-center text-white" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                                <div>
                                    <strong>{{ $user->name }}</strong>
                                    @if($user->id === auth()->id())
                                        <br><small class="text-success">(Anda)</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->role === 'admin')
                                <span class="badge bg-danger">{{ $user->role_label }}</span>
                            @elseif($user->role === 'kepala_satuan_tugas')
                                <span class="badge bg-warning">{{ $user->role_label }}</span>
                            @else
                                <span class="badge bg-info">{{ $user->role_label }}</span>
                            @endif
                        </td>
                        <td>
                            @if($user->location_placement)
                                <span class="badge bg-success">{{ $user->location_placement }}</span>
                            @else
                                <span class="text-muted">Belum ditugaskan</span>
                            @endif
                        </td>
                        <td>
                            @if(auth()->user()->isAdmin())
                            <div class="btn-group" role="group">
                                <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-info" title="Lihat">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        title="Hapus"
                                        onclick="confirmDelete('{{ route('users.destroy', $user) }}', '{{ addslashes($user->name) }}', 'akun')">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                            @else
                            <div class="btn-group" role="group">
                                <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-info" title="Lihat">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <p>Belum ada akun yang terdaftar.</p>
                                <a href="{{ route('users.create') }}" class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i>Tambah Akun Pertama
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages())
            <div class="d-flex justify-content-center">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Statistik Akun</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-3">
                        <div class="border-end">
                            <h4 class="text-primary">{{ $users->where('role', 'admin')->count() }}</h4>
                            <small class="text-muted">Admin</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="border-end">
                            <h4 class="text-warning">{{ $users->where('role', 'kepala_satuan_tugas')->count() }}</h4>
                            <small class="text-muted">Kepala Satuan</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="border-end">
                            <h4 class="text-info">{{ $users->whereIn('role', ['petugas_sertifikasi', 'petugas_gudang', 'petugas_bbi'])->count() }}</h4>
                            <small class="text-muted">Petugas</small>
                        </div>
                    </div>
                    <div class="col-3">
                        <h4 class="text-success">{{ $users->count() }}</h4>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Akses Modul</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted">Penanaman:</small><br>
                        <span class="badge bg-warning">{{ $users->where('role', 'kepala_satuan_tugas')->count() }} User</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Sertifikasi:</small><br>
                        <span class="badge bg-info">{{ $users->where('role', 'petugas_sertifikasi')->count() }} User</span>
                    </div>
                    <div class="col-6 mt-2">
                        <small class="text-muted">Gudang:</small><br>
                        <span class="badge bg-info">{{ $users->where('role', 'petugas_gudang')->count() }} User</span>
                    </div>
                    <div class="col-6 mt-2">
                        <small class="text-muted">Penjualan:</small><br>
                        <span class="badge bg-info">{{ $users->where('role', 'petugas_bbi')->count() }} User</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


















