@extends('layouts.app')
@section('title', 'Hasil Uji Lab - SIBESTI')
@section('content')
<div class="d-flex justify-content-between mb-4">
    <h4 class="mb-0">Hasil uji lab sertifikasi</h4>
    <a href="{{ route('seed-stock.show', [$plant, 'tab'=>'lots']) }}" class="btn btn-secondary">Kembali</a>
</div>
<div class="card"><div class="card-body">
    <div class="row">
        <div class="col-md-4 mb-2"><strong>No sertifikat:</strong> {{ $report->report_number_bpsb }}</div>
        <div class="col-md-4 mb-2"><strong>Tanggal:</strong> {{ $report->report_date?->format('d M Y') }}</div>
        <div class="col-md-4 mb-2"><strong>Uji ke:</strong> {{ $report->uji_ke ?: 1 }}</div>
        <div class="col-md-4 mb-2"><strong>Status:</strong> {{ $report->status_label }}</div>
        <div class="col-md-4 mb-2"><strong>Kesimpulan:</strong> {{ $report->conclusion ?: '-' }}</div>
        <div class="col-md-4 mb-2"><strong>Lokasi:</strong> {{ $report->planting_location?->name ?: '-' }}</div>
        <div class="col-md-4 mb-2"><strong>Daya berkecambah:</strong> {{ $report->daya_berkecambah }}</div>
        <div class="col-md-4 mb-2"><strong>Kadar air:</strong> {{ $report->kadar_air }}</div>
        <div class="col-md-4 mb-2"><strong>Benih murni:</strong> {{ $report->benih_murni }}</div>
        <div class="col-12 mb-2"><strong>Dokumen BPSB:</strong>
            @if($report->scan_file_path)
                <a href="{{ asset('storage/'.$report->scan_file_path) }}" target="_blank">Lihat dokumen</a>
            @else
                -
            @endif
        </div>
    </div>
    <a href="{{ route('certifications.reports.edit', $report) }}" class="btn btn-warning">Edit</a>
</div></div>
@endsection
