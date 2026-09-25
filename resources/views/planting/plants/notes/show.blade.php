@extends('layouts.app')

@section('title', 'Detail Lampiran - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">{{ $note->title ?: 'Lampiran' }}</h4>
    <a href="{{ route('plants.notes.index', $plant) }}" class="btn btn-secondary">Kembali</a>
</div>
<div class="card">
    <div class="card-body">
        <p><strong>Dibuat oleh:</strong> {{ $note->creator?->name ?: '-' }}</p>
        <p><strong>Tanggal:</strong> {{ optional($note->attachment_date)->format('d M Y') ?: '-' }}</p>
        <p>{{ $note->description ?: '-' }}</p>
        <h6>File</h6>
        @if($note->file_path)
            <a href="{{ asset('storage/'.$note->file_path) }}" target="_blank">{{ $note->file_name ?: 'Unduh' }}</a>
        @else
            <p class="text-muted">Tidak ada file.</p>
        @endif
    </div>
</div>
@endsection
