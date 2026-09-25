@extends('layouts.public')
@section('title', $booking->kode_booking.' - SIBESTI')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h3>{{ $booking->kode_booking }}</h3>
    <a href="{{ route('public.geowisata.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@if($booking->status_pengajuan === 'disetujui')
    <div class="alert alert-success">Kunjungan Anda disetujui. Silakan hadir pada tanggal {{ $booking->tgl_kunjungan?->format('d M Y') }}.</div>
@elseif($booking->status_pengajuan === 'ditolak')
    <div class="alert alert-danger">Pengajuan tidak disetujui. {{ $booking->alasan_ditolak }}</div>
@else
    <div class="alert alert-warning">Pengajuan masih menunggu review petugas.</div>
@endif
<div class="card">
    <div class="card-body">
        <p><strong>Nama lembaga:</strong> {{ $booking->nama_lembaga }}</p>
        <p><strong>Jumlah peserta:</strong> {{ $booking->jumlah_peserta }}</p>
        <p><strong>Tanggal kunjungan:</strong> {{ $booking->tgl_kunjungan?->format('d M Y') }}</p>
        <p><strong>Penanggung jawab:</strong> {{ $booking->nama_pj }} ({{ $booking->no_whatsapp_pj }})</p>
        <p><strong>Status kedatangan:</strong> {{ $booking->status_kedatangan }}</p>
        <p class="mb-0"><strong>Status pengajuan:</strong> <span class="badge bg-{{ $booking->pengajuanColor() }}">{{ $booking->pengajuanLabel() }}</span></p>
    </div>
</div>
@endsection
