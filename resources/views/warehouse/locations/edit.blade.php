@extends('layouts.app')

@section('title', 'Edit Lokasi Penyimpanan - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Lokasi Penyimpanan</h4>
    <a href="{{ route('warehouse-locations.show', $warehouse) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('warehouse-locations.update', $warehouse) }}" method="POST">
            @csrf
            @method('PUT')
            @include('warehouse.locations._form-fields', ['warehouse' => $warehouse])
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('warehouse-locations.show', $warehouse) }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
