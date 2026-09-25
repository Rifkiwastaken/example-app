@extends('layouts.app')

@section('title', 'Pelabelan Sertifikat Benih - SIBESTI')

@php
    $unit = $planting->plant?->satuanStok?->code ?: $planting->satuanPanen?->code ?: '';
    $packagings = $report->packagings;
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Pelabelan Sertifikat Benih</h4>
        <small class="text-muted">{{ $planting->planting_batch_number }} · {{ $report->nomor_lot }}</small>
    </div>
    <a href="{{ route('planting-locations.plantings.reports', [$plantingLocation, $planting]) }}" class="btn btn-secondary">Kembali</a>
</div>

<div class="card mb-4">
    <div class="card-header">Isi form pelabelan</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-2"><strong>Nomor induk:</strong> {{ $report->postHarvest?->nomor_induk ?: '-' }}</div>
            <div class="col-md-4 mb-2"><strong>Nomor lot:</strong> {{ $report->nomor_lot ?: '-' }}</div>
            <div class="col-md-4 mb-2"><strong>No sertifikat BPSB final:</strong> {{ $report->no_sertifikat_bpsb_final ?: '-' }}</div>
            <div class="col-md-4 mb-2"><strong>Warna label:</strong> {{ $report->warna_label ?: '-' }}</div>
            <div class="col-md-4 mb-2"><strong>Tipe kemasan:</strong> {{ $report->tipe_kemasan ?: '-' }}</div>
            <div class="col-md-4 mb-2"><strong>Isi kemasan:</strong> {{ $report->ukuran_kemasan_retail_kg !== null ? number_format((float) $report->ukuran_kemasan_retail_kg, 2).' '.$unit : '-' }}</div>
            <div class="col-md-4 mb-2"><strong>Jumlah lembar label:</strong> {{ $report->jumlah_lembar_label_dicetak ?: '-' }}</div>
            <div class="col-md-4 mb-2"><strong>No seri:</strong> {{ trim(($report->no_seri_label_awal ?: '').' – '.($report->no_seri_label_akhir ?: ''), ' –') ?: '-' }}</div>
            <div class="col-md-4 mb-2"><strong>Tanggal pemasangan:</strong> {{ optional($report->tgl_pemasangan_label)->format('d M Y') ?: '-' }}</div>
            <div class="col-md-4 mb-2"><strong>Petugas:</strong> {{ $report->creator?->name ?: '-' }}</div>
            @if($report->lampiran_qr_label)
                <div class="col-md-12 mb-2"><strong>Lampiran QR:</strong> <a href="{{ asset('storage/'.$report->lampiran_qr_label) }}" target="_blank">Lihat berkas</a></div>
            @endif
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Stok produk benih berdasarkan nomor label</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>No label</th>
                    <th>Isi kemasan</th>
                    <th>Status</th>
                    <th>Lokasi penyimpanan</th>
                    <th>Tempat penyimpanan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($packagings as $pkg)
                    <tr>
                        <td>{{ $pkg->no_label_seri }}</td>
                        <td>{{ number_format((float) $pkg->kapasitas_per_kemasan, 2) }} {{ $unit }}</td>
                        <td><span class="badge bg-{{ $pkg->statusBadge() }}">{{ $pkg->statusLabel() }}</span></td>
                        <td>{{ $pkg->rack?->warehouse?->name ?: '-' }}</td>
                        <td>{{ $pkg->rack?->name ?: '-' }}</td>
                        <td>
                            <a href="{{ route('public.seed-label', $pkg) }}" class="btn btn-sm btn-outline-success" target="_blank">Lihat label</a>
                            <a href="{{ route('seed-stock.packaging.show', $pkg) }}" class="btn btn-sm btn-outline-info">Detail stok</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">Produk berlabel belum dimasukkan ke lokasi penyimpanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
