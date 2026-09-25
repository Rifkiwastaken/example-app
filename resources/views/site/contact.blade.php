@extends('layouts.public')
@section('title', 'Kontak - '.$situs->office_name)

@section('content')
<p class="page-kicker mb-1">Hubungi Kami</p>
<h1 class="h3 mb-4">Kontak UPTD BBI TPH</h1>
<div class="row g-4">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h5">{{ $situs->office_name }}</h2>
                <p>{{ $situs->address }}</p>
                <p class="mb-1"><strong>Telepon:</strong> {{ $situs->phone ?: '-' }}</p>
                <p class="mb-1"><strong>WhatsApp:</strong> {{ $situs->whatsapp ?: '-' }}</p>
                <p class="mb-0"><strong>Email:</strong> {{ $situs->email ?: '-' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
