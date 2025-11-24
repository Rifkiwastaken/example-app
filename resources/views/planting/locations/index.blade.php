@extends('layouts.app')

@section('title', 'Lokasi Penanaman - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Lokasi Penanaman</h4>
    <a href="{{ route('planting-locations.create') }}" class="btn btn-success"><i class="fas fa-plus me-2"></i>Tambah Lokasi Tanam</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Nama</th><th>Tipe</th><th>Format</th><th>Jumlah Tanaman</th><th width="140">Aksi</th></tr></thead>
                <tbody>
                @forelse($locations as $loc)
                    <tr>
                        <td>{{ $loc->name }}</td>
                        <td><span class="badge bg-secondary">{{ str_replace('_',' ', $loc->location_type) }}</span></td>
                        <td>{{ str_replace('_',' ', $loc->planting_format) }}</td>
                        <td><span class="badge bg-primary">{{ $loc->plants_count }}</span></td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('planting-locations.show', $loc) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('planting-locations.edit', $loc) }}" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada lokasi penanaman.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($locations->hasPages())
            <div class="d-flex justify-content-center">{{ $locations->links() }}</div>
        @endif
    </div>
</div>
@endsection















