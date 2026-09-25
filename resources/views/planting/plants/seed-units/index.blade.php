@extends('layouts.app')

@section('title', 'Satuan Benih - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Satuan Benih</h4>
    <a href="{{ route('plants.index') }}" class="btn btn-secondary">Kembali</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="card mb-4">
    <div class="card-header"><h6 class="mb-0">Tambah Satuan Benih</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('seed-units.store') }}" class="row g-3">
            @csrf
            <div class="col-md-5">
                <label class="form-label">Nama satuan <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Kode satuan <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control" value="{{ old('code') }}" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-success" type="submit">Simpan Satuan</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nama Satuan</th>
                    <th>Kode Satuan</th>
                    <th>Jenis</th>
                </tr>
            </thead>
            <tbody>
                @foreach($units as $unit)
                    <tr>
                        <td>{{ $unit->name }}</td>
                        <td><code>{{ $unit->code }}</code></td>
                        <td>
                            @if($unit->is_fixed)
                                <span class="badge bg-secondary">Data tetap</span>
                            @else
                                <span class="badge bg-success">Tambahan</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
