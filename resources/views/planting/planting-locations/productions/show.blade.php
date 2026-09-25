@extends('layouts.app')

@php
    $plantName = $planting->plant?->name ?: $planting->seedSource?->variety?->name ?: 'Tanaman';
    $fieldCode = $planting->field?->kode_lahan ?: 'Lahan';
@endphp

@section('title', 'Laporan Produksi Penanaman - '.$plantName.' - '.$fieldCode.' - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Laporan Produksi Penanaman - {{ $plantName }} - {{ $fieldCode }}</h4>
        <small class="text-muted">{{ $planting->planting_batch_number }} · {{ $plantingLocation->name }}</small>
    </div>
    <a href="{{ !empty($readonly) ? route('planting-locations.planting-history', $plantingLocation) : route('planting-locations.plantings.index', $plantingLocation) }}" class="btn btn-secondary">Kembali</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
@if(!empty($readonly))
    <div class="alert alert-info">Mode lihat saja. Data laporan tidak dapat ditambahkan dari riwayat ini.</div>
@endif

@include('planting.planting-locations.productions._tabs')

<div class="bg-white border rounded p-3">
    @if($tab === 'progress')
        @include('planting.planting-locations.productions.partials.progress')
    @elseif($tab === 'daily')
        @include('planting.planting-locations.productions.partials.daily')
    @elseif($tab === 'certification')
        @include('planting.planting-locations.productions.partials.certification')
    @else
        @include('planting.planting-locations.productions.partials.notes')
    @endif
</div>
@endsection
