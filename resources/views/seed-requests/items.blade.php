@extends('layouts.app')
@section('title', 'Rincian Item Permintaan Benih - SIBESTI')
@section('content')
@include('seed-requests._items', [
    'heading' => 'Rincian Item dan Pembayaran',
    'backUrl' => $backUrl ?? route('seed-requests.create'),
    'storeUrl' => $storeUrl ?? route('seed-requests.store'),
    'lotsUrl' => $lotsUrl ?? url('/permintaan/plants'),
    'submitLabel' => 'Kirim permintaan',
])
@endsection
