@extends('layouts.app')

@section('title', 'Permintaan Benih - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Permintaan Benih</h4>
        <small class="text-muted">Daftar permintaan publik dan internal</small>
    </div>
    <a href="{{ route('seed-requests.create') }}" class="btn btn-success"><i class="fas fa-plus me-1"></i>Tambah Permintaan</a>
</div>
<form class="row g-2 mb-3" method="GET">
    <div class="col-md-4"><input name="q" class="form-control" placeholder="Cari nama/nomor/instansi" value="{{ request('q') }}"></div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">Semua status</option>
            @foreach(['menunggu_verifikasi'=>'Menunggu verifikasi','disetujui'=>'Disetujui','ditolak'=>'Ditolak','siap_diambil'=>'Siap diambil','telah_diambil'=>'Telah diambil'] as $k=>$v)
                <option value="{{ $k }}" {{ request('status')===$k ? 'selected' : '' }}>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary">Filter</button></div>
</form>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Nomor</th>
                    <th>Nama pembeli</th>
                    <th>Instansi</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $item)
                    <tr>
                        <td>{{ $item->request_number }}</td>
                        <td>{{ $item->buyer_name }}</td>
                        <td>{{ $item->organization ?: '-' }}</td>
                        <td>{{ $item->request_date?->format('d M Y') }}</td>
                        <td><span class="badge bg-{{ $item->statusColor() }}">{{ $item->statusLabel() }}</span></td>
                        <td>
                            <a href="{{ route('seed-requests.show', $item) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                            @if($item->status === 'menunggu_verifikasi')
                                <a href="{{ route('seed-requests.approve-form', $item) }}" class="btn btn-sm btn-success">Setujui permintaan</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">Belum ada permintaan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $requests->links() }}
@endsection
