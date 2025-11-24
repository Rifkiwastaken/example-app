@extends('layouts.app')

@section('title', 'Tipe Tanaman - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Tipe Tanaman</h4>
    <a href="{{ route('plants.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
</div>
    <a href="{{ route('plant-types.create') }}" class="btn btn-success"><i class="fas fa-plus me-2"></i>Tambah Tipe</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Nama</th><th>Kategori</th><th width="120">Aksi</th></tr></thead>
                <tbody>
                @forelse($types as $type)
                    <tr>
                        <td>{{ $type->name }}</td>
                        <td>{{ $type->category ?: '-' }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('plant-types.edit', $type) }}" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                                <form class="d-inline" method="POST" action="{{ route('plant-types.destroy', $type) }}" onsubmit="return confirm('Hapus tipe?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">Belum ada tipe tanaman.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($types->hasPages())
            <div class="d-flex justify-content-center">{{ $types->links() }}</div>
        @endif
    </div>
</div>
@endsection















