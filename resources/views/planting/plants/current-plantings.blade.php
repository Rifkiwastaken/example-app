@extends('layouts.app')

@section('title', 'Produksi Penanaman - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="{{ route('plants.index') }}" class="btn btn-secondary me-3">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
        <div>
            <h4 class="mb-0">{{ $plant->name }}</h4>
            <small class="text-muted">Produksi penanaman berdasarkan varietas ini</small>
        </div>
    </div>
</div>

<ul class="nav nav-tabs" role="tablist">
    @include('planting.plants._tabs', ['plant' => $plant, 'activeTab' => 'current-plantings'])
</ul>

<div class="tab-content p-3 bg-white border border-top-0 rounded-bottom">
    <h6 class="mb-3">Produksi yang sedang berjalan</h6>
    @include('planting.plants._planting-table', [
        'plantings' => $currentPlantings,
        'emptyText' => 'Belum ada produksi yang sedang berjalan.',
        'historyMode' => false,
    ])

    <h6 class="mt-4 mb-3">Riwayat penanaman</h6>
    @include('planting.plants._planting-table', [
        'plantings' => $historyPlantings ?? collect(),
        'emptyText' => 'Belum ada riwayat penanaman.',
        'historyMode' => true,
    ])
</div>
@endsection
