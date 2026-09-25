@extends('layouts.public')
@section('title', 'Pendaftaran Magang/PKL - SIBESTI')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0">Pendaftaran Magang / PKL</h3>
        <small class="text-muted">Pengajuan magang agro-edukasi UPTD BBI TPHP</small>
    </div>
    <a href="{{ route('public.magang.create') }}" class="btn btn-emerald"><i class="fas fa-plus me-1"></i>Lakukan Pengajuan</a>
</div>
<div class="card mb-4">
    <div class="card-header bg-white"><strong>Daftar magang yang sedang dijalankan</strong></div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Institusi</th><th>Tanggal mulai</th><th>Tanggal selesai</th></tr></thead>
            <tbody>
                @forelse($berjalan as $item)
                    <tr>
                        <td>{{ $item->institusi_asal }}</td>
                        <td>{{ $item->tgl_mulai?->format('d M Y') }}</td>
                        <td>{{ $item->tgl_selesai?->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">Belum ada magang yang sedang berjalan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="card">
    <div class="card-header bg-white"><strong>Daftar pengajuan pendaftaran magang/PKL dan statusnya</strong></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Institusi</th>
                    <th>Nama lengkap</th>
                    <th>Nomor registrasi</th>
                    <th>Jabatan</th>
                    <th>Status magang</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuan as $item)
                    <tr>
                        <td>{{ $item->institusi_asal }}</td>
                        <td><a href="{{ route('public.magang.show', $item) }}">{{ $item->nama_lengkap }}</a></td>
                        <td>{{ $item->nomor_registrasi }}</td>
                        <td>{{ $item->jabatan ?: '-' }}</td>
                        <td><span class="badge bg-{{ $item->statusColor() }}">{{ $item->status_magang }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada pengajuan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body">{{ $pengajuan->links() }}</div>
</div>
@endsection
