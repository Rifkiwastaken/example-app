@extends('layouts.public')
@section('title', 'Booking Geowisata - SIBESTI')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0">Pengajuan Kunjungan Geowisata</h3>
        <small class="text-muted">Antrean agro-edukasi kebun induk UPTD BBI TPHP</small>
    </div>
    <a href="{{ route('public.geowisata.create') }}" class="btn btn-emerald"><i class="fas fa-plus me-1"></i>Tambahkan Pengajuan</a>
</div>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-white"><strong>Daftar kunjungan bulan ini</strong></div>
            <ul class="list-group list-group-flush" id="geo-month-list"></ul>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white"><strong>Kalender kunjungan</strong></div>
            <div class="card-body">
                @include('landing.geowisata._calendar', ['calendar' => $calendar, 'selectable' => false])
            </div>
        </div>
    </div>
</div>
<div class="card mt-4">
    <div class="card-header bg-white"><strong>Daftar pengajuan kunjungan geowisata</strong></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Nama lembaga</th>
                    <th>Kode booking</th>
                    <th>Jumlah peserta</th>
                    <th>Tanggal kunjungan</th>
                    <th>Nama penanggung jawab</th>
                    <th>Status kedatangan</th>
                    <th>Status pengajuan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuan as $item)
                    <tr>
                        <td><a href="{{ route('public.geowisata.show', $item) }}">{{ $item->nama_lembaga }}</a></td>
                        <td>{{ $item->kode_booking }}</td>
                        <td>{{ $item->jumlah_peserta }}</td>
                        <td>{{ $item->tgl_kunjungan?->format('d M Y') }}</td>
                        <td>{{ $item->nama_pj }}</td>
                        <td>{{ $item->status_kedatangan }}</td>
                        <td><span class="badge bg-{{ $item->pengajuanColor() }}">{{ $item->pengajuanLabel() }}</span></td>
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
