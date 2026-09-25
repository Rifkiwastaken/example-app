@extends('layouts.app')

@section('title', 'Stok Benih - '.$plant->name.' - SIBESTI')

@php $unit = $plant->satuanStok?->code ?: ''; @endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $plant->name }}{{ $plant->variety ? ' - '.$plant->variety : '' }}</h4>
        <small class="text-muted">Stok benih · satuan {{ $unit ?: '-' }}</small>
    </div>
    <a href="{{ route('seed-stock.index') }}" class="btn btn-secondary">Kembali</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
@if(!empty($isLowStock))
    <div class="alert alert-warning">
        <strong>Stok rendah.</strong> Stok siap salur saat ini {{ number_format($readyStock, 2) }} {{ $unit }}
        di bawah minimal stok {{ number_format((float) $plant->minimal_stok, 2) }} {{ $unit }}.
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md"><div class="card"><div class="card-body"><small class="text-muted">Siap ditambahkan ke stok</small><h5 class="mb-0">{{ number_format((float) $unpackaged, 0) }} lot</h5></div></div></div>
    <div class="col-md"><div class="card"><div class="card-body"><small class="text-muted">Total stok benih di penyimpanan</small><h5 class="mb-0">{{ number_format($inWarehouse, 2) }} {{ $unit }}</h5></div></div></div>
    <div class="col-md"><div class="card"><div class="card-body"><small class="text-muted">Total benih terjual</small><h5 class="mb-0">{{ number_format($soldQty, 2) }} {{ $unit }}</h5></div></div></div>
    <div class="col-md"><div class="card"><div class="card-body"><small class="text-muted">Total pendapatan</small><h5 class="mb-0">Rp {{ number_format($revenue, 0, ',', '.') }}</h5></div></div></div>
    <div class="col-md"><div class="card"><div class="card-body"><small class="text-muted">Nilai per unit</small><h5 class="mb-0">Rp {{ number_format((float) $plant->harga_jual, 0, ',', '.') }} / {{ $unit }}</h5></div></div></div>
</div>

<ul class="nav nav-tabs">
    <li class="nav-item"><a class="nav-link {{ $tab==='detail'?'active':'' }}" href="{{ route('seed-stock.show', [$plant, 'tab'=>'detail']) }}">Detail Benih</a></li>
    <li class="nav-item"><a class="nav-link {{ $tab==='lots'?'active':'' }}" href="{{ route('seed-stock.show', [$plant, 'tab'=>'lots']) }}">Lot Stok Benih</a></li>
    <li class="nav-item"><a class="nav-link {{ $tab==='gudang'?'active':'' }}" href="{{ route('seed-stock.show', [$plant, 'tab'=>'gudang']) }}">Riwayat Stok</a></li>
    <li class="nav-item"><a class="nav-link {{ $tab==='lampiran'?'active':'' }}" href="{{ route('seed-stock.show', [$plant, 'tab'=>'lampiran']) }}">Lampiran</a></li>
</ul>

<div class="bg-white border border-top-0 p-3">
    @if($tab==='detail')
        <div class="row">
            <div class="col-md-6 mb-2"><strong>Nama stok benih:</strong> {{ $plant->name }}{{ $plant->variety ? ' - '.$plant->variety : '' }}</div>
            <div class="col-md-6 mb-2"><strong>Total panen benih:</strong> {{ number_format($totalHarvest, 2) }} {{ $unit }}</div>
            <div class="col-md-6 mb-2"><strong>Total benih disertifikasi:</strong> {{ number_format($totalCertified, 2) }} {{ $unit }}</div>
            <div class="col-md-6 mb-2"><strong>Minimal stok benih:</strong> {{ $plant->minimal_stok !== null ? number_format((float)$plant->minimal_stok, 2).' '.$unit : '-' }}</div>
            <div class="col-md-6 mb-2"><strong>Harga jual:</strong> Rp {{ number_format((float) $plant->harga_jual, 0, ',', '.') }}{{ $unit ? ' / '.$unit : '' }}</div>
            <div class="col-md-6 mb-2"><strong>Stok siap salur:</strong> {{ number_format($readyStock ?? 0, 2) }} {{ $unit }}</div>
        </div>
    @elseif($tab==='lots')
        @if(($awaitingLabelLots ?? collect())->isNotEmpty())
            <div class="mb-4">
                <h6 class="mb-2">Menunggu pelabelan</h6>
                <p class="text-muted small mb-2">Benih yang sudah lulus uji lab, termasuk hasil sertifikasi ulang, dan menunggu label.</p>
                @include('warehouse.seed-stock._lots-table', ['lots' => $awaitingLabelLots, 'emptyText' => 'Tidak ada benih yang menunggu pelabelan.', 'historyMode' => false])
            </div>
        @endif
        <div class="mb-4">
            <h6 class="mb-2">Siap ditambahkan ke stok</h6>
            <p class="text-muted small mb-2">Benih yang sudah dilabel dan menunggu input lokasi serta tempat penyimpanan.</p>
            @include('warehouse.seed-stock._lots-table', ['lots' => $pendingLabelLots ?? collect(), 'emptyText' => 'Tidak ada benih yang menunggu input stok.', 'historyMode' => false, 'labelQueue' => true])
        </div>
        <h6 class="mb-2">Lot stok benih aktif</h6>
        @include('warehouse.seed-stock._lots-table', ['lots' => $activeLots, 'emptyText' => 'Belum ada lot stok benih aktif.', 'historyMode' => false, 'showRecert' => true])
    @elseif($tab==='gudang')
        <p class="text-muted small mb-3">Lot stok benih yang stok aktifnya sudah habis (0).</p>
        @include('warehouse.seed-stock._lots-table', ['lots' => $emptyLots, 'emptyText' => 'Belum ada riwayat lot dengan stok aktif 0.', 'historyMode' => true])
    @else
        <form method="POST" action="{{ route('seed-stock.plant-notes.store', $plant) }}" enctype="multipart/form-data" class="mb-4">
            @csrf
            @include('attachments._form-fields')
            <div class="mt-3"><button class="btn btn-success">Simpan lampiran</button></div>
        </form>
        <table class="table">
            <thead><tr><th>Judul</th><th>Tanggal</th><th>File</th></tr></thead>
            <tbody>
                @forelse($notes as $note)
                    <tr>
                        <td>{{ $note->title }}</td>
                        <td>{{ $note->attachment_date?->format('d M Y') }}</td>
                        <td>@if($note->file_path)<a href="{{ asset('storage/'.$note->file_path) }}" target="_blank">{{ $note->file_name }}</a>@else-@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-muted text-center">Belum ada lampiran.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif
</div>
@endsection
