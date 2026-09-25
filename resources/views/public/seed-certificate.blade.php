@extends(auth()->check() ? 'layouts.app' : 'layouts.public')
@section('title', 'Informasi Sertifikat Benih - SIBESTI')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 d-print-none">
    <h4 class="mb-0">Informasi Sertifikat Benih</h4>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-success" onclick="window.print()">Cetak / simpan</button>
        <a href="javascript:history.back()" class="btn btn-secondary">Kembali</a>
    </div>
</div>
@include('partials.seed-certificate', ['cert' => $cert, 'print' => true])
@endsection
