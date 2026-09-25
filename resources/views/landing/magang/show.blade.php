@extends('layouts.public')
@section('title', $pendaftaran->nomor_registrasi.' - SIBESTI')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h3>{{ $pendaftaran->nomor_registrasi }}</h3>
    <a href="{{ route('public.magang.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@if($pendaftaran->status_magang === 'Diterima')
    <div class="alert alert-success">Pendaftaran magang Anda disetujui. Periode {{ $pendaftaran->tgl_mulai?->format('d M Y') }} s.d. {{ $pendaftaran->tgl_selesai?->format('d M Y') }}.</div>
@elseif($pendaftaran->status_magang === 'Ditolak')
    <div class="alert alert-danger">Pendaftaran tidak disetujui. {{ $pendaftaran->alasan_ditolak }}</div>
@else
    <div class="alert alert-warning">Pendaftaran masih menunggu review petugas.</div>
@endif
<div class="card mb-3">
    <div class="card-body">
        <p><strong>Nama lengkap:</strong> {{ $pendaftaran->nama_lengkap }}</p>
        <p><strong>Institusi:</strong> {{ $pendaftaran->institusi_asal }}</p>
        <p><strong>Jabatan:</strong> {{ $pendaftaran->jabatan ?: '-' }}</p>
        <p><strong>WhatsApp:</strong> {{ $pendaftaran->no_whatsapp }}</p>
        <p><strong>Periode:</strong> {{ $pendaftaran->tgl_mulai?->format('d M Y') }} — {{ $pendaftaran->tgl_selesai?->format('d M Y') }}</p>
        <p class="mb-0"><strong>Status magang:</strong> <span class="badge bg-{{ $pendaftaran->statusColor() }}">{{ $pendaftaran->status_magang }}</span></p>
    </div>
</div>
<div class="card">
    <div class="card-header bg-white"><strong>Daftar peserta magang</strong></div>
    <ul class="list-group list-group-flush">
        @forelse($pendaftaran->pesertaList() as $peserta)
            <li class="list-group-item">{{ $peserta['nama_lengkap'] ?? '-' }} @if(!empty($peserta['identitas'])) — {{ $peserta['identitas'] }} @endif @if(!empty($peserta['jabatan'])) ({{ $peserta['jabatan'] }}) @endif</li>
        @empty
            <li class="list-group-item text-muted">Tidak ada peserta.</li>
        @endforelse
    </ul>
</div>
@endsection
