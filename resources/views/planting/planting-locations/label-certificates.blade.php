@extends('layouts.app')

@section('title', 'Riwayat Sertifikat Label - ' . $plantingLocation->name . ' - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">{{ $plantingLocation->name }}</h4>
    <a href="{{ route('planting-locations.show', $plantingLocation) }}" class="btn btn-secondary">Kembali</a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<ul class="nav nav-tabs" role="tablist">
    @include('planting.planting-locations._tabs', ['plantingLocation' => $plantingLocation, 'activeTab' => 'label-certificates'])
</ul>

<div class="tab-content p-3 bg-white border border-top-0 rounded-bottom">
    <h6 class="mb-3">Riwayat Sertifikat Label</h6>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Tanaman</th>
                    <th>No induk</th>
                    <th>No batch tanam</th>
                    <th>ID label rilis</th>
                    <th>No seri</th>
                    <th>Warna label</th>
                    <th>Stok saat ini</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stocks as $stock)
                    @php
                        $label = $stock->certificationReport ?: $stock->labels->first();
                        $plant = $stock->plant;
                        $unit = $plant?->satuanStok?->code ?: '';
                    @endphp
                    <tr>
                        <td>{{ $plant?->displayName() ?: '-' }}</td>
                        <td>{{ $stock->nomor_induk ?: '-' }}</td>
                        <td>{{ $stock->postHarvest?->planting?->planting_batch_number ?: '-' }}</td>
                        <td>{{ $label?->id_label_rilis ?: '-' }}</td>
                        <td>
                            @if($label)
                                {{ $label->no_seri_label_awal }} – {{ $label->no_seri_label_akhir }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $label?->warna_label ?: '-' }}</td>
                        <td>{{ number_format((float) $stock->stok_saat_ini, 2) }} {{ $unit }}</td>
                        <td><span class="badge bg-{{ $stock->displayStatusBadge() }}">{{ $stock->displayStatusLabel() }}</span></td>
                        <td>
                            @if($plant)
                                <a href="{{ route('seed-stock.certificate', [$plant, $stock]) }}" class="btn btn-sm btn-outline-secondary mb-1">Lihat sertifikat</a>
                                <a href="{{ route('seed-stock.lots.show', [$plant, $stock]) }}" class="btn btn-sm btn-outline-info mb-1">Lihat stok</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted">Belum ada sertifikat label pada lokasi ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
