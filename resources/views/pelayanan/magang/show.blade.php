@extends('layouts.app')
@section('title', 'Detail Magang '.$pendaftaran->nomor_registrasi)
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>{{ $pendaftaran->nomor_registrasi }}</h4>
    <a href="{{ route('magang.index') }}" class="btn btn-secondary">Kembali</a>
</div>
<div class="card mb-3">
    <div class="card-body">
        <p><strong>Nama lengkap:</strong> {{ $pendaftaran->nama_lengkap }}</p>
        <p><strong>Institusi:</strong> {{ $pendaftaran->institusi_asal }}</p>
        <p><strong>Jabatan:</strong> {{ $pendaftaran->jabatan ?: '-' }}</p>
        <p><strong>WhatsApp:</strong> {{ $pendaftaran->no_whatsapp }}</p>
        <p><strong>Periode:</strong> {{ $pendaftaran->tgl_mulai?->format('d M Y') }} — {{ $pendaftaran->tgl_selesai?->format('d M Y') }}</p>
        <p><strong>Status magang:</strong> <span class="badge bg-{{ $pendaftaran->statusColor() }}">{{ $pendaftaran->status_magang }}</span></p>
        @if($pendaftaran->alasan_ditolak)
            <div class="alert alert-danger mb-0">Alasan penolakan: {{ $pendaftaran->alasan_ditolak }}</div>
        @endif
    </div>
</div>
<div class="card mb-3">
    <div class="card-header bg-white">Daftar peserta magang</div>
    <ul class="list-group list-group-flush">
        @forelse($pendaftaran->pesertaList() as $peserta)
            <li class="list-group-item">{{ $peserta['nama_lengkap'] ?? '-' }} @if(!empty($peserta['identitas'])) — {{ $peserta['identitas'] }} @endif @if(!empty($peserta['jabatan'])) ({{ $peserta['jabatan'] }}) @endif</li>
        @empty
            <li class="list-group-item text-muted">Tidak ada peserta.</li>
        @endforelse
    </ul>
</div>
@if($pendaftaran->status_magang === 'Menunggu Review')
<div class="d-flex gap-2 flex-wrap">
    <form method="POST" action="{{ route('magang.approve', $pendaftaran) }}">
        @csrf
        <button class="btn btn-success">Terima pendaftaran</button>
    </form>
    <form method="POST" action="{{ route('magang.reject', $pendaftaran) }}" class="d-flex gap-2">
        @csrf
        <input name="alasan_ditolak" class="form-control" placeholder="Alasan penolakan" required>
        <button class="btn btn-danger">Tolak</button>
    </form>
</div>
@endif
@endsection
