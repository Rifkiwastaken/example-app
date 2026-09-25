@extends('layouts.app')
@section('title', 'Label Benih Unggul Bersertifikat - SIBESTI')
@section('content')
<div class="mb-3 d-print-none">
    <a href="javascript:history.back()" class="btn btn-secondary">Kembali</a>
    <button class="btn btn-primary" onclick="window.print()">Print label</button>
</div>
@include('partials.seed-label-unggul', ['label' => \App\Support\SeedCertificate::labelPayload($packaging)])
@endsection
