@extends('layouts.public')
@section('title', 'Rincian Item Permintaan Benih - SIBESTI')
@section('content')
@include('seed-requests._items', [
    'heading' => 'Rincian Item dan Pembayaran',
    'backUrl' => $backUrl ?? route('public.seed-requests.create'),
    'storeUrl' => $storeUrl ?? route('public.seed-requests.store'),
    'lotsUrl' => $lotsUrl ?? url('/permintaan/plants'),
    'submitLabel' => 'Ajukan permintaan',
])
@endsection
