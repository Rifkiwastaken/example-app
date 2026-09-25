@extends('layouts.app')

@section('title', 'Hasil Uji Lab Benih - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Hasil Uji Lab Benih</h4>
        <small class="text-muted">{{ $plant->displayName() }} · No induk {{ $stock->nomor_induk ?: '-' }}</small>
    </div>
    <a href="{{ route('seed-stock.show', [$plant, 'tab' => 'lots']) }}" class="btn btn-secondary">Kembali</a>
</div>

@forelse($tests as $item)
    <h5 class="mb-3">Uji ke-{{ $item->uji_ke }}</h5>
    @include('planting.planting-locations.productions.partials._lab-result-detail', ['postHarvest' => $item])
@empty
    <div class="alert alert-info">Belum ada hasil uji lab pada nomor induk ini.</div>
@endforelse
@endsection
