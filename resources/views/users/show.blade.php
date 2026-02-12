@extends('layouts.app')

@section('title', 'Detail Akun - SIBESTI')

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
        <!-- Informasi Akun -->
        <div class="card mb-4">
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
                    <div class="col-sm-3"><strong>Password:</strong></div>
                    <div class="col-sm-9">
                        @if($user->password)
                            <span class="badge bg-success">Password Sudah Di-set</span>
                            <br><small class="text-muted">Password telah dikonfigurasi untuk akun ini</small>
                        @else
                            <span class="badge bg-warning">Password Belum Di-set</span>
                        @endif
                    </div>
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
                        @if($user->location_placement)
                            <span class="badge bg-success">{{ $user->location_placement }}</span>
                        @else
                            <span class="text-muted">Belum ditugaskan</span>
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
        
        <!-- Informasi Dasar -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informasi Dasar</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Nama Lengkap:</strong></div>
                    <div class="col-sm-9">{{ $user->full_name ?: '-' }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Tipe Kontak:</strong></div>
                    <div class="col-sm-9">
                        @if($user->contact_type)
                            @php
                                $contactTypeLabels = [
                                    'pegawai_uptd_bbi_tpph' => 'Pegawai UPTD BBI TPPH',
                                    'pegawai_gudang' => 'Pegawai Gudang',
                                    'petugas_sertifikasi' => 'Petugas Sertifikasi',
                                    'petani' => 'Petani',
                                    'penyuluh' => 'Penyuluh',
                                    'penangkar' => 'Penangkar',
                                    'lainnya' => 'Lainnya',
                                ];
                            @endphp
                            {{ $contactTypeLabels[$user->contact_type] ?? $user->contact_type }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Instansi / Organisasi:</strong></div>
                    <div class="col-sm-9">{{ $user->organization ?: '-' }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Jabatan / Posisi:</strong></div>
                    <div class="col-sm-9">{{ $user->position ?: '-' }}</div>
                </div>
                
                <div class="row">
                    <div class="col-sm-3"><strong>NIP:</strong></div>
                    <div class="col-sm-9">{{ $user->nip ?: '-' }}</div>
                </div>
            </div>
        </div>
        
        <!-- Informasi Kontak & Alamat -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informasi Kontak & Alamat</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Nomor Telepon (Utama):</strong></div>
                    <div class="col-sm-9">
                        {{ $user->primary_phone ?: '-' }}
                        @if($user->primary_phone_is_whatsapp)
                            <span class="badge bg-success ms-2">WhatsApp</span>
                        @endif
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Nomor Telepon (Lainnya):</strong></div>
                    <div class="col-sm-9">{{ $user->secondary_phone ?: '-' }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Alamat Lengkap:</strong></div>
                    <div class="col-sm-9">{{ $user->address ?: '-' }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Provinsi:</strong></div>
                    <div class="col-sm-9">{{ $user->province ?: '-' }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Kabupaten / Kota:</strong></div>
                    <div class="col-sm-9">{{ $user->city ?: '-' }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Kecamatan:</strong></div>
                    <div class="col-sm-9">{{ $user->district ?: '-' }}</div>
                </div>
                
                <div class="row">
                    <div class="col-sm-3"><strong>Desa / Kelurahan:</strong></div>
                    <div class="col-sm-9">{{ $user->village ?: '-' }}</div>
                </div>
            </div>
        </div>
        
        <!-- Catatan Tambahan -->
        @if($user->notes)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Catatan Tambahan</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $user->notes }}</p>
            </div>
        </div>
        @endif
        
        <!-- Hak Akses Modul -->
        <div class="card mb-4">
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
                @if($user->photo_path)
                    <img src="{{ Storage::url($user->photo_path) }}" class="rounded-circle mb-3" style="width: 80px; height: 80px; object-fit: cover;">
                @else
                    <div class="bg-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center text-white" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-user fa-2x"></i>
                    </div>
                @endif
                <h5>{{ $user->name }}</h5>
                <p class="text-muted">{{ $user->email }}</p>
                @if($user->role === 'admin')
                    <span class="badge bg-danger mb-2">{{ $user->role_label }}</span>
                @elseif($user->role === 'kepala_satuan_tugas')
                    <span class="badge bg-warning mb-2">{{ $user->role_label }}</span>
                @else
                    <span class="badge bg-info mb-2">{{ $user->role_label }}</span>
                @endif
                
                @if($user->location_placement)
                    <div class="mt-3">
                        <small class="text-muted">Ditugaskan di:</small><br>
                        <strong>{{ $user->location_placement }}</strong>
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




