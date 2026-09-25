@extends('layouts.public')
@section('title', $post->judul.' - '.$situs->office_name)

@section('content')
<p class="page-kicker mb-1">{{ $post->jenisLabel() }}</p>
<h1 class="h3 mb-2">{{ $post->judul }}</h1>
<div class="text-muted mb-4" style="font-size:12px;">{{ optional($post->published_at ?: $post->created_at)->translatedFormat('d F Y') }} · {{ $post->author ?: 'Humas UPTD BBI TPH' }}</div>
@if($post->coverUrl())
    <img src="{{ $post->coverUrl() }}" class="img-fluid rounded mb-4 w-100" style="max-height:420px;object-fit:cover;" alt="{{ $post->judul }}">
@endif
<article class="bg-white p-4 rounded shadow-sm">{!! nl2br(e($post->body)) !!}</article>
@if($post->file_path)
    <a class="btn btn-emerald mt-3" href="{{ route('site.download', $post) }}"><i class="fas fa-download me-1"></i>Unduh lampiran</a>
@endif
@endsection
