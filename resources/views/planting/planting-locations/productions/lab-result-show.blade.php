@extends('layouts.app')

@section('title', 'Hasil Uji Lab - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Hasil Uji Lab Benih @if($postHarvest->uji_ke > 1)(Uji ke-{{ $postHarvest->uji_ke }})@endif</h4>
        <small class="text-muted">{{ $planting->planting_batch_number }} · {{ $postHarvest->nomor_lot }}</small>
    </div>
    <a href="{{ route('planting-locations.plantings.reports', [$plantingLocation, $planting, 'tab' => 'certification']) }}" class="btn btn-secondary">Kembali</a>
</div>

@include('planting.planting-locations.productions.partials._lab-result-detail', ['postHarvest' => $postHarvest])
@endsection
