@extends('layouts.app')

@section('title', 'Ubah Kontak - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Edit Kontak</h4>
        <small class="text-muted">Perbarui informasi kontak sesuai kebutuhan operasional.</small>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Periksa kembali isian Anda.</strong> Beberapa field belum valid.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ route('contacts.update', $contact) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @include('contacts._form', [
        'contact' => $contact,
        'statuses' => $statuses,
        'contactTypes' => $contactTypes,
    ])
</form>
@endsection







