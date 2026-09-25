@extends('layouts.app')
@section('title', 'Detail Permintaan - SIBESTI')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $seedRequest->request_number }}</h4>
        <span class="badge bg-{{ $seedRequest->statusColor() }}">{{ $seedRequest->statusLabel() }}</span>
    </div>
    <a href="{{ route('seed-requests.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">Informasi pembeli</div>
            <div class="card-body">
                <p><strong>Nama:</strong> {{ $seedRequest->buyer_name }}</p>
                <p><strong>Instansi:</strong> {{ $seedRequest->organization ?: '-' }}</p>
                <p><strong>Kontak:</strong> {{ $seedRequest->buyer_contact ?: '-' }}</p>
                <p><strong>Tujuan:</strong> {{ collect([$seedRequest->destination_village,$seedRequest->destination_district,$seedRequest->destination_city,$seedRequest->destination_province])->filter()->implode(', ') ?: '-' }}</p>
                <p><strong>Pembayaran:</strong> {{ $seedRequest->payment_method === 'transfer_bank' ? 'Non cash' : 'Cash' }}
                    @if($seedRequest->total_amount) — Rp {{ number_format($seedRequest->total_amount, 0, ',', '.') }} @endif
                </p>
                @if($seedRequest->payment_proof)
                    <p><a href="{{ asset('storage/'.$seedRequest->payment_proof) }}" target="_blank">Lihat bukti pembayaran</a></p>
                @endif
                @if($seedRequest->rejection_reason)
                    <div class="alert alert-danger">Alasan ditolak: {{ $seedRequest->rejection_reason }}</div>
                @endif
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">Rincian benih</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Nama tanaman-varietas</th>
                            <th>Nomor induk</th>
                            <th>No label</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($seedRequest->packagings as $pkg)
                            @php
                                $stock = $pkg->stock;
                                $plant = $stock?->plant;
                                $rack = $pkg->rack;
                                $warehouse = $rack?->warehouse;
                            @endphp
                            <tr>
                                <td>{{ $plant?->displayName() ?: '-' }}</td>
                                <td>{{ $stock?->nomor_induk ?: '-' }}</td>
                                <td>{{ $pkg->no_label_seri ?: '-' }}</td>
                                <td>{{ number_format((float) ($pkg->kapasitas_per_kemasan ?: 0), 2) }}</td>
                                <td>{{ $plant?->satuanStok?->code ?: ($seedRequest->items->firstWhere('seed_varieties_id', $stock?->seed_varieties_id)?->unit ?: '-') }}</td>
                                <td>
                                    @if($warehouse && $rack)
                                        <a href="{{ route('warehouse-locations.racks.packagings', [$warehouse, $rack]) }}" class="btn btn-sm btn-outline-info">Lihat stok</a>
                                    @elseif($plant && $stock)
                                        <a href="{{ route('seed-stock.lots.show', [$plant, $stock]) }}" class="btn btn-sm btn-outline-info">Lihat stok</a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            @foreach($seedRequest->items as $item)
                                <tr>
                                    <td>{{ $item->plant?->displayName() ?: '-' }}</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>{{ number_format((float) $item->quantity, 2) }}</td>
                                    <td>{{ $item->unit ?: '-' }}</td>
                                    <td><span class="text-muted">-</span></td>
                                </tr>
                            @endforeach
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Aksi</div>
            <div class="card-body d-grid gap-2">
                @if($seedRequest->status === \App\Models\SeedRequest::STATUS_PENDING)
                    <a href="{{ route('seed-requests.approve-form', $seedRequest) }}" class="btn btn-success">Setujui permintaan</a>
                    <form method="POST" action="{{ route('seed-requests.reject', $seedRequest) }}">
                        @csrf
                        <textarea name="rejection_reason" class="form-control mb-2" placeholder="Alasan ditolak" required></textarea>
                        <button class="btn btn-danger w-100">Tolak permintaan</button>
                    </form>
                @endif
                @if(in_array($seedRequest->status, [\App\Models\SeedRequest::STATUS_APPROVED, \App\Models\SeedRequest::STATUS_READY], true))
                    <a href="{{ route('seed-requests.pickup', $seedRequest) }}" class="btn btn-primary">Lanjutkan ke penjemputan benih</a>
                @endif
                @if($seedRequest->status === \App\Models\SeedRequest::STATUS_TAKEN)
                    <a href="{{ route('public.seed-requests.receipt', $seedRequest) }}" class="btn btn-outline-success">Lihat struk</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
