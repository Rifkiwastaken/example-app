@extends('layouts.app')

@section('title', 'Manajemen Lokasi Gudang - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Manajemen Lokasi Gudang</h4>
    <a href="{{ route('warehouse-locations.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-2"></i>Tambahkan Lokasi
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
                        <th>Nama Gudang</th>
                        <th>ID Internal</th>
                        <th>Tipe Pelacakan</th>
                        <th>Jumlah Bin</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warehouses as $warehouse)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                     style="width: 40px; height: 40px;">
                                    <i class="fas fa-warehouse text-white"></i>
                                </div>
                                <div>
                                    <strong>{{ $warehouse->name }}</strong>
                                    @if($warehouse->description)
                                        <br><small class="text-muted">{{ Str::limit($warehouse->description, 50) }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td><code>{{ $warehouse->internal_id }}</code></td>
                        <td>
                            <span class="badge bg-info">{{ $warehouse->tracking_type_label }}</span>
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $warehouse->bins_count }} Bin</span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('warehouse-locations.show', $warehouse) }}" class="btn btn-sm btn-outline-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('warehouse-locations.edit', $warehouse) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        title="Hapus"
                                        onclick="confirmDelete('{{ route('warehouse-locations.destroy', $warehouse) }}', '{{ addslashes($warehouse->name) }}', 'gudang')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-warehouse fa-3x mb-3"></i>
                                <p>Belum ada lokasi gudang yang ditambahkan.</p>
                                <a href="{{ route('warehouse-locations.create') }}" class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i>Tambah Lokasi Gudang Pertama
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($warehouses->hasPages())
            <div class="d-flex justify-content-center">
                {{ $warehouses->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

