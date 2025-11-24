@extends('layouts.app')

@section('title', 'Detail Lokasi - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Detail Lokasi: {{ $location->name }}</h4>
    <div>
        <a href="{{ route('locations.edit', $location) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
        <a href="{{ route('locations.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informasi Lokasi</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Nama Lokasi:</strong></div>
                    <div class="col-sm-9">{{ $location->name }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Kota/Kabupaten:</strong></div>
                    <div class="col-sm-9">{{ $location->city }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Kecamatan:</strong></div>
                    <div class="col-sm-9">{{ $location->district }}</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Jenis/Asosiasi:</strong></div>
                    <div class="col-sm-9">
                        <span class="badge bg-info">{{ $location->type_label }}</span>
                    </div>
                </div>
                
                @if($location->description)
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Deskripsi:</strong></div>
                    <div class="col-sm-9">{{ $location->description }}</div>
                </div>
                @endif
                
                @if($location->google_maps_link)
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Google Maps:</strong></div>
                    <div class="col-sm-9">
                        <a href="{{ $location->google_maps_link }}" target="_blank" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-map-marked-alt me-1"></i>Lihat di Google Maps
                        </a>
                    </div>
                </div>
                @endif
                
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Dibuat:</strong></div>
                    <div class="col-sm-9">{{ $location->created_at->format('d M Y H:i') }}</div>
                </div>
                
                <div class="row">
                    <div class="col-sm-3"><strong>Terakhir Diupdate:</strong></div>
                    <div class="col-sm-9">{{ $location->updated_at->format('d M Y H:i') }}</div>
                </div>
            </div>
        </div>
        
        @if($location->users->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">User yang Ditugaskan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($location->users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $user->role_label }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-md-4">
        @if($location->photo)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Foto Lokasi</h5>
            </div>
            <div class="card-body text-center">
                <img src="{{ asset('storage/' . $location->photo) }}" 
                     alt="{{ $location->name }}" 
                     class="img-fluid rounded">
            </div>
        </div>
        @endif
        
        @if($location->google_maps_link)
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Peta Lokasi</h5>
            </div>
            <div class="card-body">
                <div class="ratio ratio-16x9">
                    <iframe src="{{ str_replace('https://maps.google.com', 'https://maps.google.com/embed', $location->google_maps_link) }}" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection















