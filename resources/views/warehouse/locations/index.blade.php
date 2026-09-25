@extends('layouts.app')

@section('title', 'Lokasi Penyimpanan - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Lokasi Penyimpanan</h4>
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
                        <th>Nama Lokasi</th>
                        <th>ID Internal</th>
                        <th>Tipe Lokasi</th>
                        <th>Jumlah Blok</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warehouses as $warehouse)
                    <tr>
                        <td>
                            <strong>{{ $warehouse->name }}</strong>
                            @if($warehouse->description)
                                <br><small class="text-muted">{{ Str::limit($warehouse->description, 50) }}</small>
                            @endif
                        </td>
                        <td><code>{{ $warehouse->internal_id }}</code></td>
                        <td>
                            <span class="badge bg-info">{{ $warehouse->tipe_lokasi_label }}</span>
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $warehouse->bins_count }} Blok</span>
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
                                        onclick="confirmDelete('{{ route('warehouse-locations.destroy', $warehouse) }}', '{{ addslashes($warehouse->name) }}', 'lokasi penyimpanan')">
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
                                <p>Belum ada lokasi penyimpanan yang ditambahkan.</p>
                                <a href="{{ route('warehouse-locations.create') }}" class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i>Tambah Lokasi Penyimpanan Pertama
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
