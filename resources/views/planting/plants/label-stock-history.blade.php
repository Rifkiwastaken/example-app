@extends('layouts.app')
@section('title', 'Riwayat Label & Stok - SIBESTI')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="{{ route('plants.index') }}" class="btn btn-secondary me-3">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
        <div>
            <h4 class="mb-0">{{ $plant->name }}</h4>
            <small class="text-muted">{{ $plant->type?->name ?: '-' }}</small>
        </div>
    </div>
</div>

<ul class="nav nav-tabs" role="tablist">
    @include('planting.plants._tabs', ['plant' => $plant, 'activeTab' => 'label-stock'])
</ul>

<div class="tab-content p-3 bg-white border border-top-0 rounded-bottom">
    <h5 class="mb-3">Stok benih aktif saat ini</h5>
    @php $unit = $plant->satuanStok?->code ?: ''; @endphp
    <div class="table-responsive mb-4">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>Nomor induk</th>
                    <th>Total stok aktif (satuan/produk)</th>
                    <th>Lokasi penyimpanan</th>
                    <th>Tempat penyimpanan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeStocks ?? [] as $active)
                    @php $produk = $active->packagings->where('status_kemasan', \App\Models\StockPackaging::STATUS_TERSEDIA)->count(); @endphp
                    <tr>
                        <td>{{ $active->nomor_induk ?: '-' }}</td>
                        <td>{{ number_format((float) $active->stok_saat_ini, 2) }} {{ $unit }} / {{ $produk }} produk</td>
                        <td>{{ $active->storageLocationNames() }}</td>
                        <td>{{ $active->storagePlaceNames() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted text-center">Tidak ada stok benih aktif.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <h5 class="mb-3">Riwayat Label & Stok</h5>
    <div class="table-responsive mb-4">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>No batch tanam</th>
                    <th>No induk</th>
                    <th>Stok saat ini</th>
                    <th>Status</th>
                    <th>Kedaluwarsa</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stocks as $stock)
                    <tr>
                        <td>{{ $stock->no_label_resmi ?: '-' }}</td>
                        <td>{{ $stock->nomor_induk ?: '-' }}</td>
                        <td>{{ number_format((float) $stock->stok_saat_ini, 2) }}</td>
                        <td>{{ $stock->displayStatusLabel() }}</td>
                        <td>{{ $stock->tgl_kedaluwarsa?->format('d/m/Y') ?: '-' }}</td>
                        <td>
                            <a href="{{ route('seed-stock.lots.show', [$plant, $stock]) }}" class="btn btn-sm btn-outline-info">Lihat detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center">Belum ada data stok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $stocks->links() }}
    <h6 class="mt-4">Mutasi stok</h6>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead><tr><th>Tanggal</th><th>Tipe</th><th>Kuantitas</th><th>Keterangan</th></tr></thead>
            <tbody>
                @forelse($histories as $history)
                    <tr>
                        <td>{{ $history->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $history->transaction_type }}</td>
                        <td>{{ number_format((float) $history->quantity, 2) }} {{ $history->unit }}</td>
                        <td>{{ $history->reason ?: $history->notes }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted text-center">Belum ada riwayat mutasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $histories->links() }}
</div>
@endsection
