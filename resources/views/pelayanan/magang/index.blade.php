@extends('layouts.app')
@section('title', 'Pendaftaran Magang - SIBESTI')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Pendaftaran Magang / PKL</h4>
        <small class="text-muted">Review pengajuan magang dan PKL</small>
    </div>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Nomor</th>
                    <th>Institusi</th>
                    <th>Nama lengkap</th>
                    <th>Jabatan</th>
                    <th>Periode</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuan as $item)
                    <tr>
                        <td>{{ $item->nomor_registrasi }}</td>
                        <td>{{ $item->institusi_asal }}</td>
                        <td>{{ $item->nama_lengkap }}</td>
                        <td>{{ $item->jabatan ?: '-' }}</td>
                        <td>{{ $item->tgl_mulai?->format('d M Y') }} — {{ $item->tgl_selesai?->format('d M Y') }}</td>
                        <td><span class="badge bg-{{ $item->statusColor() }}">{{ $item->status_magang }}</span></td>
                        <td><a href="{{ route('magang.show', $item) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pengajuan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body">{{ $pengajuan->links() }}</div>
</div>
@endsection
