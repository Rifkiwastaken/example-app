@extends('layouts.app')
@section('title', 'Permintaan Benih - SIBESTI')
@section('content')
@include('seed-requests._buyer', [
    'heading' => 'Informasi Pembeli',
    'backUrl' => $backUrl ?? route('seed-requests.index'),
    'buyerUrl' => $buyerUrl ?? route('seed-requests.buyer'),
    'buyer' => $buyer ?? [],
])
@endsection
