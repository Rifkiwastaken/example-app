@extends('layouts.app')
@section('title', 'Print Label Kemasan - SIBESTI')
@section('content')
<div class="d-print-none mb-3">
    <a href="{{ route('seed-stock.lots.show', [$plant, $stock]) }}" class="btn btn-secondary">Kembali</a>
    <button class="btn btn-primary" onclick="window.print()">Print semua</button>
</div>
<div class="row">
    @forelse($packagings as $packaging)
        @include('warehouse.seed-stock.sticker-inner', ['packaging'=>$packaging,'plant'=>$plant,'stock'=>$stock])
    @empty
        <p class="text-muted">Tidak ada kemasan dipilih.</p>
    @endforelse
</div>
@endsection
