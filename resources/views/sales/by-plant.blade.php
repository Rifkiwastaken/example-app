@extends('layouts.app')
@section('title', 'Detail Benih Terjual - '.$plant->name.' - SIBESTI')
@section('content')
<div class="d-flex justify-content-between mb-4">
    <div>
        <h4 class="mb-0">Daftar benih terjual · {{ $plant->displayName() }}</h4>
        <small class="text-muted">{{ $plant->variety ?: '-' }}</small>
    </div>
    <a href="{{ route('sales.index') }}" class="btn btn-secondary">Kembali</a>
</div>
<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>No struk</th>
                <th>Tanggal</th>
                <th>Pembeli</th>
                <th>No seri label</th>
                <th>Kuantitas</th>
                <th width="280">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($soldItems as $item)
                @php
                    $stock = $item->packaging?->stock;
                    $planting = $stock?->postHarvest?->planting;
                    $location = $planting?->field?->plantingLocation;
                @endphp
                <tr>
                    <td>{{ $item->receipt_number }}</td>
                    <td>{{ $item->sale_date?->format('d M Y') }}</td>
                    <td>{{ $item->buyer_name }}</td>
                    <td>{{ $item->packaging?->no_label_seri ?: '-' }}</td>
                    <td>{{ number_format((float) $item->quantity, 2) }} {{ $item->unit }}</td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            @if($stock)
                                <a href="{{ route('seed-stock.certificate', [$plant, $stock]) }}" class="btn btn-sm btn-outline-secondary">Sertifikat label</a>
                            @endif
                            @if($planting && $location)
                                <a href="{{ route('planting-locations.plantings.reports', [$location, $planting]) }}" class="btn btn-sm btn-outline-info">Laporan produksi</a>
                            @endif
                            <a href="{{ route('sales.show', $item) }}" class="btn btn-sm btn-outline-primary">Detail penjualan</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">Belum ada penjualan untuk benih ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
