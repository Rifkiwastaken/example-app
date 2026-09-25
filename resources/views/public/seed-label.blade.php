@extends(auth()->check() ? 'layouts.app' : 'layouts.public')
@section('title', 'Label Benih Unggul Bersertifikat - SIBESTI')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 d-print-none">
    <h4 class="mb-0">Label Benih Unggul Bersertifikat</h4>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-success" onclick="window.print()">Cetak / simpan</button>
        <a href="javascript:history.back()" class="btn btn-secondary">Kembali</a>
    </div>
</div>
@include('partials.seed-label-unggul', ['label' => $label])
@endsection
