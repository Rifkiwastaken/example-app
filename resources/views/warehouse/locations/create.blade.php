@extends('layouts.app')

@section('title', 'Tambah Lokasi Penyimpanan - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Formulir: Tambah Lokasi Penyimpanan Baru</h4>
    <a href="{{ route('warehouse-locations.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('warehouse-locations.store') }}" method="POST">
            @csrf
            @include('warehouse.locations._form-fields', ['warehouse' => null])
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('warehouse-locations.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
