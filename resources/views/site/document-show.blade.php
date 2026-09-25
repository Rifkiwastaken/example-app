@extends('layouts.public')
@section('title', $content->judul.' - '.$situs->office_name)

@section('content')
<p class="page-kicker mb-1">{{ $content->jenisLabel() }}</p>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <h1 class="h3 mb-0">{{ $content->judul }}</h1>
    <a href="{{ route('site.documents') }}" class="btn btn-outline-success"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
</div>
<div class="text-muted mb-3" style="font-size:12px;">
    {{ optional($content->published_at ?: $content->created_at)->translatedFormat('d F Y') }}
    @if($content->author)
        · {{ $content->author }}
    @endif
    @if($content->kip_label)
        · {{ $content->kip_label }}
    @endif
</div>

@if($content->excerpt)
    <div class="card border-0 shadow-sm mb-4" style="background:#ecfdf5;">
        <div class="card-body">
            <div class="fw-bold text-success mb-1">Cuplikan</div>
            <p class="mb-0">{{ $content->excerpt }}</p>
        </div>
    </div>
@endif

@if($content->coverUrl())
    <div class="mb-4">
        <div class="fw-semibold mb-2">Cover / gambar</div>
        <a href="{{ $content->coverUrl() }}" target="_blank" rel="noopener">
            <img src="{{ $content->coverUrl() }}" class="img-fluid rounded shadow-sm w-100" style="max-height:420px;object-fit:cover;" alt="{{ $content->judul }}">
        </a>
    </div>
@endif

<div class="bg-white p-4 rounded shadow-sm mb-4">
    <div class="fw-semibold mb-2">Isi konten</div>
    @if($content->body)
        <article>{!! nl2br(e($content->body)) !!}</article>
    @else
        <p class="text-muted mb-0">Belum ada isi konten.</p>
    @endif
</div>

@if($content->file_path)
    <div class="card border-0 shadow-sm">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="fw-semibold">Berkas</div>
                <a href="{{ route('site.documents.file', $content) }}" target="_blank" rel="noopener" class="text-decoration-none">
                    <i class="fas fa-file-pdf text-danger me-1"></i>{{ $content->file_name ?: basename($content->file_path) }}
                    <span class="text-muted">({{ $content->fileSizeLabel() }})</span>
                </a>
                <div class="small text-muted">Klik nama berkas untuk melihat isinya.</div>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-success" href="{{ route('site.documents.file', $content) }}" target="_blank" rel="noopener">Lihat berkas</a>
                <a class="btn btn-emerald" href="{{ route('site.download', $content) }}">Unduh</a>
            </div>
        </div>
    </div>
@endif
@endsection
