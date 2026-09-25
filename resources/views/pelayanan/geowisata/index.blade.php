@extends('layouts.app')
@section('title', 'Kunjungan Geowisata - SIBESTI')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Kunjungan Geowisata</h4>
        <small class="text-muted">Review pengajuan kunjungan agro-edukasi</small>
    </div>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama lembaga</th>
                    <th>Tanggal</th>
                    <th>Peserta</th>
                    <th>PJ</th>
                    <th>Kedatangan</th>
                    <th>Pengajuan</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuan as $item)
                    <tr>
                        <td>{{ $item->kode_booking }}</td>
                        <td>{{ $item->nama_lembaga }}</td>
                        <td>{{ $item->tgl_kunjungan?->format('d M Y') }}</td>
                        <td>{{ $item->jumlah_peserta }}</td>
                        <td>{{ $item->nama_pj }}</td>
                        <td>{{ $item->status_kedatangan }}</td>
                        <td><span class="badge bg-{{ $item->pengajuanColor() }}">{{ $item->pengajuanLabel() }}</span></td>
                        <td><a href="{{ route('geowisata.show', $item) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada pengajuan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body">{{ $pengajuan->links() }}</div>
</div>
@endsection
