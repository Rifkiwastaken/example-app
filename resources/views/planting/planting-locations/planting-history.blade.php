@extends('layouts.app')

@section('title', 'Riwayat Produksi - ' . $plantingLocation->name . ' - SIBESTI')

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

<ul class="nav nav-tabs" role="tablist">
    @include('planting.planting-locations._tabs', ['plantingLocation' => $plantingLocation, 'activeTab' => 'history'])
</ul>

<div class="tab-content p-3 bg-white border border-top-0 rounded-bottom">
    <h6 class="mb-3">Riwayat Produksi Benih</h6>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Lahan</th>
                    <th>Nomor Batch</th>
                    <th>No Induk</th>
                    <th>No Lot</th>
                    <th>Tanggal Selesai Uji</th>
                    <th>Berat Bersih</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($postHarvests as $item)
                    @php
                        $planting = $item->planting;
                        $stock = $item->stock;
                        $label = $item->certificationReports->first();
                        $pendingStorage = $stock && $stock->isAwaitingStorage();
                        $stored = $stock && $stock->packagings->isNotEmpty();
                    @endphp
                    <tr>
                        <td>{{ $planting?->field?->kode_lahan ?: '-' }}</td>
                        <td>{{ $planting?->planting_batch_number ?: '-' }}</td>
                        <td>{{ $item->nomor_induk ?: '-' }}</td>
                        <td>{{ $item->nomor_lot ?: '-' }}</td>
                        <td>{{ $item->tgl_selesai_uji?->format('d M Y') ?: '-' }}</td>
                        <td>{{ number_format((float) $item->total_hasil_uji, 2) }} {{ $planting?->satuanPanen?->code }}</td>
                        <td>
                            @if(! $item->isLulus())
                                <span class="badge bg-danger">Tidak lulus uji lab</span>
                            @elseif($pendingStorage)
                                <span class="badge bg-warning text-dark">Input ke lokasi penyimpanan</span>
                            @elseif($stored)
                                <span class="badge bg-success">Sudah masuk stok</span>
                            @else
                                <span class="badge bg-secondary">Telah dilabel</span>
                            @endif
                        </td>
                        <td>
                            @if($pendingStorage && $label)
                                <a href="{{ route('seed-stock.label.storage', [$stock->seed_varieties_id, $stock, $label]) }}" class="btn btn-sm btn-success mb-1">Input data stok benih</a>
                            @elseif($stock && $item->isLulus() && ! $item->isCertified())
                                <a href="{{ route('seed-stock.label.create', [$stock->seed_varieties_id, $stock]) }}" class="btn btn-sm btn-outline-primary mb-1">Tambahkan Label Benih</a>
                            @elseif($stock && $stored)
                                <a href="{{ route('seed-stock.lots.show', [$stock->seed_varieties_id, $stock]) }}" class="btn btn-sm btn-outline-success mb-1">Lihat stok</a>
                            @endif
                            @if($planting && $label)
                                <a href="{{ route('planting-locations.plantings.labels.show', [$plantingLocation, $planting, $label]) }}" class="btn btn-sm btn-outline-secondary mb-1">Lihat label</a>
                            @endif
                            @if($planting)
                                <a href="{{ route('planting-locations.plantings.lab-result.show', [$plantingLocation, $planting, $item]) }}" class="btn btn-sm btn-outline-secondary mb-1">Hasil Uji Lab</a>
                                <a href="{{ route('planting-locations.plantings.history-reports', [$plantingLocation, $planting]) }}" class="btn btn-sm btn-outline-info mb-1">Detail</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">Belum ada riwayat produksi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
