@extends('layouts.app')

@section('title', 'Sertifikat Uji Lab Benih - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Sertifikat Uji Lab Benih</h4>
        <small class="text-muted">{{ $plant->name }} · No induk {{ $stock->nomor_induk ?: '-' }}</small>
    </div>
    <a href="{{ route('seed-stock.show', [$plant, 'tab' => 'lots']) }}" class="btn btn-secondary">Kembali</a>
</div>

@forelse($tests as $postHarvest)
    <h5 class="mb-3">Uji ke-{{ $postHarvest->uji_ke }}</h5>
    @include('planting.planting-locations.productions.partials._lab-result-detail', ['postHarvest' => $postHarvest])
@empty
    <div class="alert alert-info">Belum ada hasil uji lab pada nomor induk ini.</div>
@endforelse

@if($labels->isNotEmpty())
    <h5 class="mb-3">Label yang sudah dirilis</h5>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>ID Label</th>
                    <th>No Lot</th>
                    <th>No Sertifikat BPSB</th>
                    <th>Warna</th>
                    <th>Jumlah lembar</th>
                    <th>Seri</th>
                </tr>
            </thead>
            <tbody>
                @foreach($labels as $label)
                    <tr>
                        <td>{{ $label->id_label_rilis }}</td>
                        <td>{{ $label->nomor_lot }}</td>
                        <td>{{ $label->no_sertifikat_bpsb_final }}</td>
                        <td>{{ $label->warnaLabel() }}</td>
                        <td>{{ $label->jumlah_lembar_label_dicetak }}</td>
                        <td>{{ $label->no_seri_label_awal }} – {{ $label->no_seri_label_akhir }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
