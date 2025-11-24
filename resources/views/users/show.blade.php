@extends('layouts.app')

@section('title', 'Detail Akun - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Detail Akun: {{ $user->name }}</h4>
    <div>
        <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informasi Akun</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Nama Akun:</strong></div>
                    <div class="col-sm-9">{{ $user->name }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Email:</strong></div>
                    <div class="col-sm-9">{{ $user->email }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Peran/Asosiasi:</strong></div>
                    <div class="col-sm-9">
                        @if($user->role === 'admin')
                            <span class="badge bg-danger">{{ $user->role_label }}</span>
                        @elseif($user->role === 'kepala_satuan_tugas')
                            <span class="badge bg-warning">{{ $user->role_label }}</span>
                        @else
                            <span class="badge bg-info">{{ $user->role_label }}</span>
                        @endif
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Lokasi Penempatan:</strong></div>
                    <div class="col-sm-9">
                        @if($user->location)
                            <span class="badge bg-success">{{ $user->location->name }}</span>
                            <br><small class="text-muted">{{ $user->location->city }}, {{ $user->location->district }}</small>
                        @else
                            <span class="text-muted">Belum ditugaskan</span>
                        @endif
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Status Akun:</strong></div>
                    <div class="col-sm-9">
                        @if($user->email_verified_at)
                            <span class="badge bg-success">Aktif</span>
                            <br><small class="text-muted">Terverifikasi pada {{ $user->email_verified_at->format('d M Y H:i') }}</small>
                        @else
                            <span class="badge bg-warning">Belum Verifikasi</span>
                        @endif
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Dibuat:</strong></div>
                    <div class="col-sm-9">{{ $user->created_at->format('d M Y H:i') }}</div>
                </div>
                
                <div class="row">
                    <div class="col-sm-3"><strong>Terakhir Diupdate:</strong></div>
                    <div class="col-sm-9">{{ $user->updated_at->format('d M Y H:i') }}</div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Hak Akses Modul</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Dashboard</span>
                            <span class="badge bg-success">✓ Akses Penuh</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Penanaman</span>
                            @if($user->hasAccessTo('penanaman'))
                                <span class="badge bg-success">✓ Akses Penuh</span>
                            @else
                                <span class="badge bg-secondary">✗ Tidak Ada Akses</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Sertifikasi</span>
                            @if($user->hasAccessTo('sertifikasi'))
                                <span class="badge bg-success">✓ Akses Penuh</span>
                            @else
                                <span class="badge bg-secondary">✗ Tidak Ada Akses</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Gudang/Stok</span>
                            @if($user->hasAccessTo('gudang'))
                                <span class="badge bg-success">✓ Akses Penuh</span>
                            @else
                                <span class="badge bg-secondary">✗ Tidak Ada Akses</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Penjualan</span>
                            @if($user->hasAccessTo('penjualan'))
                                <span class="badge bg-success">✓ Akses Penuh</span>
                            @else
                                <span class="badge bg-secondary">✗ Tidak Ada Akses</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Tugas</span>
                            <span class="badge bg-success">✓ Akses Penuh</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Profil User</h5>
            </div>
            <div class="card-body text-center">
                <div class="bg-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center text-white" 
                     style="width: 80px; height: 80px;">
                    <i class="fas fa-user fa-2x"></i>
                </div>
                <h5>{{ $user->name }}</h5>
                <p class="text-muted">{{ $user->email }}</p>
                @if($user->role === 'admin')
                    <span class="badge bg-danger mb-2">{{ $user->role_label }}</span>
                @elseif($user->role === 'kepala_satuan_tugas')
                    <span class="badge bg-warning mb-2">{{ $user->role_label }}</span>
                @else
                    <span class="badge bg-info mb-2">{{ $user->role_label }}</span>
                @endif
                
                @if($user->location)
                    <div class="mt-3">
                        <small class="text-muted">Ditugaskan di:</small><br>
                        <strong>{{ $user->location->name }}</strong><br>
                        <small class="text-muted">{{ $user->location->city }}, {{ $user->location->district }}</small>
                    </div>
                @endif
            </div>
        </div>
        
        @if($user->id === auth()->id())
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Akun Anda</h5>
            </div>
            <div class="card-body">
                <p class="text-success mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Ini adalah akun Anda sendiri. Anda dapat mengedit informasi akun melalui menu edit.
                </p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection















