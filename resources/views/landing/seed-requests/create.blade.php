@extends('layouts.public')
@section('title', 'Form Permintaan Benih - SIBESTI')
@section('content')
@include('seed-requests._buyer', [
    'heading' => 'Informasi Pembeli',
    'backUrl' => $backUrl ?? route('public.seed-requests.index'),
    'buyerUrl' => $buyerUrl ?? route('public.seed-requests.buyer'),
    'buyer' => $buyer ?? [],
])
@endsection
