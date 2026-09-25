@extends('layouts.app')

@section('title', 'Tambahkan Lahan - ' . $plantingLocation->name . ' - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Tambahkan Lahan</h4>
    <a href="{{ route('planting-locations.fields.index', $plantingLocation) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <p class="text-muted mb-4">
            Lokasi penanaman: <strong>{{ $plantingLocation->name }}</strong>
        </p>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('planting-locations.fields.store', $plantingLocation) }}">
            @csrf
            @include('planting.planting-locations.fields._form', ['plantingLocation' => $plantingLocation])
            <div class="d-flex justify-content-end gap-2">
                <a class="btn btn-secondary" href="{{ route('planting-locations.fields.index', $plantingLocation) }}">Batal</a>
                <button class="btn btn-success" type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
