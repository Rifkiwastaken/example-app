@extends('layouts.app')

@section('title', 'Detail Benih Sumber - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Detail Benih Sumber</h4>
    <a href="{{ route('plants.seed-sources.index', $plant) }}" class="btn btn-secondary">Kembali</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-2"><strong>Kelas:</strong> {{ $seed_source->seedClassLabel() }}</div>
            <div class="col-md-4 mb-2"><strong>Nomor lot:</strong> {{ $seed_source->origin_lot_number }}</div>
            <div class="col-md-4 mb-2"><strong>Produsen:</strong> {{ $seed_source->origin_producer }}</div>
            <div class="col-md-4 mb-2"><strong>Sisa jumlah:</strong> {{ number_format((float) $seed_source->quantity_kg, 2) }} {{ $plant->satuanStok?->code }}</div>
            <div class="col-md-4 mb-2"><strong>Status:</strong> {{ $seed_source->stockStatusLabel() }}</div>
            <div class="col-md-4 mb-2"><strong>Deskripsi:</strong> {{ $seed_source->description ?: '-' }}</div>
            @if($seed_source->file_path)
                <div class="col-12 mb-2"><strong>Lampiran:</strong> <a href="{{ asset('storage/'.$seed_source->file_path) }}" target="_blank">{{ $seed_source->file_name ?: 'Unduh' }}</a></div>
            @endif
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h6 class="mb-0">Riwayat Lokasi dan Lahan Penanaman</h6></div>
    <div class="card-body table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>Lokasi Penanaman</th>
                    <th>Lahan</th>
                    <th>Nomor Batch</th>
                    <th>Jumlah Ditanam</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($seed_source->plantings as $planting)
                    <tr>
                        <td>{{ $planting->field?->plantingLocation?->name ?: '-' }}</td>
                        <td>{{ $planting->field?->kode_lahan ?: '-' }}</td>
                        <td>{{ $planting->planting_batch_number }}</td>
                        <td>{{ $planting->planting_amount }} {{ $plant->satuanTanam?->code }}</td>
                        <td>{{ $planting->statusLabel() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">Benih ini belum ditanam.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
