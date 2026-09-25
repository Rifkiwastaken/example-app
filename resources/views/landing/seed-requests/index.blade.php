@extends('layouts.public')
@section('title', 'Status Permintaan Benih - SIBESTI')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <p class="page-kicker mb-1">Layanan Publik</p>
        <h1 class="h3 mb-0">Status Permintaan Benih</h1>
    </div>
    <a href="{{ route('public.seed-requests.create') }}" class="btn btn-emerald">Ajukan Permintaan</a>
</div>
<form class="row g-2 mb-3" method="GET">
    <div class="col-md-6"><input name="q" class="form-control" placeholder="Cari nomor permintaan / nama / instansi" value="{{ request('q') }}"></div>
    <div class="col-md-2"><button class="btn btn-emerald w-100">Cari</button></div>
</form>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-light"><tr><th>Nama pembeli</th><th>Instansi</th><th>Tanggal</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($requests as $item)
                    <tr>
                        <td>{{ $item->buyer_name }}</td>
                        <td>{{ $item->organization ?: '-' }}</td>
                        <td>{{ $item->request_date?->format('d M Y') }}</td>
                        <td><span class="badge bg-{{ $item->statusColor() }}">{{ $item->statusLabel() }}</span></td>
                        <td><a href="{{ route('public.seed-requests.show', $item) }}" class="btn btn-sm btn-outline-success">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada permintaan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $requests->links() }}</div>
@endsection
