@extends('layouts.app')
@section('title', 'Sertifikat Label - '.$plant->displayName())
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">{{ $plant->displayName() }}</h4>
        <small class="text-muted">Sertifikat label berdasarkan nomor induk</small>
    </div>
</div>
<ul class="nav nav-tabs" role="tablist">
    @include('planting.plants._tabs', ['activeTab' => 'label-certificates'])
</ul>
<div class="tab-content bg-white border border-top-0 p-3">
    @php $unit = $plant->satuanStok?->code ?: ''; @endphp
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nomor induk</th>
                    <th>No batch tanam</th>
                    <th>Kelas benih</th>
                    <th>Total produk yang tersedia</th>
                    <th>Isi kemasan</th>
                    <th>Total produk</th>
                    <th>Tanggal selesai uji</th>
                    <th>Tanggal masa edar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stocks as $row)
                    <tr>
                        <td>{{ $row->nomor_induk ?: '-' }}</td>
                        <td>{{ $row->nomor_batch ?: '-' }}</td>
                        <td>{{ $row->kelas_benih }}</td>
                        <td>{{ $row->total_tersedia }}</td>
                        <td>{{ $row->isi_kemasan !== null ? number_format((float) $row->isi_kemasan, 2).' '.$unit : '-' }}</td>
                        <td>{{ $row->total_produk }}</td>
                        <td>{{ $row->tgl_selesai?->format('d M Y') ?: '-' }}</td>
                        <td>{{ $row->tgl_masa_edar?->format('d M Y') ?: '-' }}</td>
                        <td>
                            <a href="{{ route('public.seed-certificate', $row->stock) }}" class="btn btn-sm btn-outline-success mb-1">Lihat sertifikat benih</a>
                            <a href="{{ route('plants.label-certificates.stock', [$plant, $row->stock]) }}" class="btn btn-sm btn-outline-primary mb-1">Lihat stok</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted">Belum ada benih berlabel untuk varietas ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
