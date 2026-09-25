@extends('layouts.app')

@section('title', 'Tambah Lampiran - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Tambah Lampiran</h4>
    <a href="{{ route('plants.notes.index', $plant) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <p class="text-muted">{{ $plant->name }}</p>
        <form method="POST" action="{{ route('plants.notes.store', $plant) }}" enctype="multipart/form-data">
            @csrf
            @include('attachments._form-fields')
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a class="btn btn-secondary" href="{{ route('plants.notes.index', $plant) }}">Batal</a>
                <button class="btn btn-success" type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
