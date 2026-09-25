@extends('layouts.app')

@section('title', 'Stok Benih - SIBESTI')

@section('content')
<div class="mb-4">
    <h4 class="mb-0">Stok Benih</h4>
</div>
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nama Tanaman</th>
                    <th>Varietas</th>
                    <th>Satuan Stok</th>
                    <th>Harga Jual</th>
                    <th>Minimal Stok</th>
                    <th>Total Stok Saat Ini</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @php $lastCategory = null; @endphp
                @forelse($plants as $plant)
                    @php
                        $category = $plant->type?->category ?: 'Lainnya';
                        $unit = $plant->satuanStok?->code ?: '';
                        $isLow = $plant->minimal_stok !== null && (float) $plant->minimal_stok > 0 && $plant->total_stock < (float) $plant->minimal_stok;
                    @endphp
                    @if($lastCategory !== $category)
                        <tr class="table-light"><td colspan="7" class="fw-bold">{{ $category }}</td></tr>
                        @php $lastCategory = $category; @endphp
                    @endif
                    <tr class="{{ $isLow ? 'table-warning' : '' }}" style="cursor:pointer" onclick="window.location='{{ route('seed-stock.show', $plant) }}'">
                        <td>
                            <a href="{{ route('seed-stock.show', $plant) }}" class="text-decoration-none" onclick="event.stopPropagation()">
                                {{ $plant->type?->name ?: $plant->name }}
                            </a>
                        </td>
                        <td>{{ $plant->variety ?: '-' }}</td>
                        <td>{{ $unit ?: '-' }}</td>
                        <td>Rp {{ number_format((float) $plant->harga_jual, 0, ',', '.') }}{{ $unit ? ' / '.$unit : '' }}</td>
                        <td>{{ $plant->minimal_stok !== null ? number_format((float) $plant->minimal_stok, 2).' '.$unit : '-' }}</td>
                        <td>
                            {{ number_format($plant->total_stock, 2) }} {{ $unit }}
                            @if($isLow)
                                <span class="badge bg-warning text-dark">Stok rendah</span>
                            @endif
                        </td>
                        <td onclick="event.stopPropagation()">
                            <a href="{{ route('seed-stock.show', $plant) }}" class="btn btn-sm btn-outline-info">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">Belum ada data tanaman.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
