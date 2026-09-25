@extends('layouts.app')

@section('title', 'Lampiran Tanaman - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="{{ route('plants.index') }}" class="btn btn-secondary me-3">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
        <div>
            <h4 class="mb-0">{{ $plant->name }}</h4>
            <small class="text-muted">{{ $plant->type?->name ?: 'Tidak ada tipe' }}</small>
        </div>
    </div>
</div>

<ul class="nav nav-tabs" role="tablist">
    @include('planting.plants._tabs', ['plant' => $plant, 'activeTab' => 'notes'])
</ul>

<div class="tab-content p-3 bg-white border border-top-0 rounded-bottom">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Lampiran</h5>
            <a href="{{ route('plants.notes.create', $plant) }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus me-1"></i>Tambah Lampiran
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Tanggal</th>
                            <th>Dibuat oleh</th>
                            <th>File</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notes as $note)
                            <tr>
                                <td>{{ $note->title ?: '-' }}</td>
                                <td>{{ optional($note->attachment_date)->format('d M Y') ?: '-' }}</td>
                                <td>{{ $note->creator?->name ?: '-' }}</td>
                                <td>
                                    @if($note->file_path)
                                        <a href="{{ asset('storage/'.$note->file_path) }}" target="_blank">{{ $note->file_name ?: 'Unduh' }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('plants.notes.show', [$plant, $note]) }}" class="btn btn-sm btn-outline-info">Lihat</a>
                                    @if(auth()->user()->role !== 'penangkar')
                                        <a href="{{ route('plants.notes.edit', [$plant, $note]) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Belum ada lampiran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $notes->links() }}
        </div>
    </div>
</div>
@endsection
