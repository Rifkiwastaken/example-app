@extends('layouts.app')
@section('title', 'Stok Label '.$stock->nomor_induk.' - SIBESTI')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Stok produk · {{ $stock->nomor_induk ?: '-' }}</h4>
        <small class="text-muted">{{ $plant->displayName() }}</small>
    </div>
    <a href="{{ route('plants.label-certificates', $plant) }}" class="btn btn-secondary">Kembali</a>
</div>
<div class="table-responsive bg-white p-3 border rounded">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>No label</th>
                <th>Isi kemasan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($packagings as $pkg)
                <tr>
                    <td>{{ $pkg->no_label_seri }}</td>
                    <td>{{ number_format((float) $pkg->kapasitas_per_kemasan, 2) }} {{ $plant->satuanStok?->code }}</td>
                    <td><span class="badge bg-{{ $pkg->statusBadge() }}">{{ $pkg->statusLabel() }}</span></td>
                    <td>
                        <a href="{{ route('public.seed-label', $pkg) }}" class="btn btn-sm btn-outline-success" target="_blank">Lihat label</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">Belum ada produk berlabel.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
