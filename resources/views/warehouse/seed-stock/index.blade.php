@extends('layouts.app')

@section('title', 'Stok Benih (Tipe Inventaris) - SIBESTI')

@section('content')
<div class="mb-4">
    <h4 class="mb-0">Stok Benih (Tipe Inventaris)</h4>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanaman (Tanaman Saya)</th>
                        <th>Kategori</th>
                        <th>ID Internal / SKU</th>
                        <th>Unit</th>
                        <th>Total Stok</th>
                        <th width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plants as $plant)
                    @php
                        $type = $inventoryTypesByPlant->get($plant->plant_id);
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $plant->name }} @if($plant->variety) - {{ $plant->variety }} @endif</div>
                            <small class="text-muted">ID Tanaman: {{ $plant->plant_id }}</small>
                        </td>
                        <td><span class="badge bg-secondary">{{ $plant->type->name ?? '-' }}</span></td>
                        <td><code>{{ $type->sku ?? '-' }}</code></td>
                        <td>{{ $type->unit ?? '-' }}</td>
                        <td>
                            @if($type)
                            <strong>{{ number_format($type->display_total_stock ?? $type->total_stock_from_seeds ?? 0, 2) }} {{ $type->unit ?? '' }}</strong>
                            @else
                                <span class="text-muted">Belum ada stok</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                @if($type)
                                <a href="{{ route('seed-stock.show', $type) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        title="Hapus"
                                            onclick="confirmDelete('{{ route('seed-stock.destroy', $type) }}', '{{ addslashes($type->name) }}', 'tipe benih')">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @else
                                    <a href="{{ route('seed-stock.create', ['plant_id' => $plant->plant_id]) }}" class="btn btn-sm btn-success" title="Buat Tipe Benih">
                                        <i class="fas fa-plus me-1"></i>Buat Tipe Benih
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-seedling fa-3x mb-3"></i>
                                <p>Belum ada data tanaman.</p>
                                <a href="{{ route('seed-stock.create') }}" class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i>Tambah Tipe Benih Pertama
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

