@extends('layouts.app')
@section('title', 'Detail Kunjungan '.$booking->kode_booking)
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>{{ $booking->kode_booking }}</h4>
    <a href="{{ route('geowisata.index') }}" class="btn btn-secondary">Kembali</a>
</div>
<div class="card mb-3">
    <div class="card-body">
        <p><strong>Nama lembaga:</strong> {{ $booking->nama_lembaga }}</p>
        <p><strong>Jumlah peserta:</strong> {{ $booking->jumlah_peserta }}</p>
        <p><strong>Tanggal kunjungan:</strong> {{ $booking->tgl_kunjungan?->format('d M Y') }}</p>
        <p><strong>Penanggung jawab:</strong> {{ $booking->nama_pj }} — {{ $booking->no_whatsapp_pj }}</p>
        <p><strong>Status kedatangan:</strong> {{ $booking->status_kedatangan }}</p>
        <p><strong>Status pengajuan:</strong> <span class="badge bg-{{ $booking->pengajuanColor() }}">{{ $booking->pengajuanLabel() }}</span></p>
        @if($booking->alasan_ditolak)
            <div class="alert alert-danger mb-0">Alasan penolakan: {{ $booking->alasan_ditolak }}</div>
        @endif
    </div>
</div>
<div class="d-flex gap-2 flex-wrap">
    @if($booking->status_pengajuan === 'menunggu_review')
        <form method="POST" action="{{ route('geowisata.approve', $booking) }}">
            @csrf
            <button class="btn btn-success">Setujui kunjungan</button>
        </form>
        <form method="POST" action="{{ route('geowisata.reject', $booking) }}" class="d-flex gap-2">
            @csrf
            <input name="alasan_ditolak" class="form-control" placeholder="Alasan penolakan" required>
            <button class="btn btn-danger">Tolak</button>
        </form>
    @endif
    @if($booking->status_pengajuan === 'disetujui' && $booking->status_kedatangan !== 'Hadir')
        <form method="POST" action="{{ route('geowisata.hadir', $booking) }}">
            @csrf
            <button class="btn btn-primary">Tandai hadir</button>
        </form>
    @endif
</div>
@endsection
