@extends('layouts.app')

@section('title', 'Tambah Kontak - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Tambah Kontak Baru</h4>
        <small class="text-muted">Isi formulir berikut untuk menambahkan kontak baru pada sistem.</small>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Periksa kembali isian Anda.</strong> Beberapa field belum valid.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ route('contacts.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('contacts._form', [
        'statuses' => $statuses,
        'contactTypes' => $contactTypes,
    ])
</form>
@endsection







