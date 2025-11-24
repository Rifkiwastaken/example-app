@extends('layouts.app')

@section('title', 'Lokasi - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Daftar Lokasi</h4>
    <a href="{{ route('locations.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-2"></i>Lokasi Baru
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
                        <th>Nama Lokasi</th>
                        <th>Kota/Kabupaten</th>
                        <th>Kecamatan</th>
                        <th>Jenis/Asosiasi Lokasi</th>
                        <th>Jumlah User</th>
                        <th>Google Maps</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($locations as $location)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($location->photo)
                                    <img src="{{ asset('storage/' . $location->photo) }}" 
                                         alt="{{ $location->name }}" 
                                         class="rounded-circle me-3" 
                                         width="40" height="40" 
                                         style="object-fit: cover;">
                                @else
                                    <div class="bg-secondary rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-map-marker-alt text-white"></i>
                                    </div>
                                @endif
                                <div>
                                    <strong>{{ $location->name }}</strong>
                                    @if($location->description)
                                        <br><small class="text-muted">{{ Str::limit($location->description, 50) }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $location->city }}</td>
                        <td>{{ $location->district }}</td>
                        <td>
                            <span class="badge bg-info">{{ $location->type_label }}</span>
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $location->users_count }} User</span>
                        </td>
                        <td>
                            @if($location->google_maps_link)
                                <a href="{{ $location->google_maps_link }}" target="_blank" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-map-marked-alt"></i> Lihat
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('locations.show', $location) }}" class="btn btn-sm btn-outline-info" title="Lihat">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('locations.edit', $location) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('locations.destroy', $location) }}" method="POST" class="d-inline" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi ini?')">
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
                                <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
                                <p>Belum ada lokasi yang ditambahkan.</p>
                                <a href="{{ route('locations.create') }}" class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i>Tambah Lokasi Pertama
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($locations->hasPages())
            <div class="d-flex justify-content-center">
                {{ $locations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection















