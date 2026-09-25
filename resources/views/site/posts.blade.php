@extends('layouts.public')
@section('title', 'Berita & Artikel - '.$situs->office_name)

@section('content')
<p class="page-kicker mb-1">Publikasi</p>
<h1 class="h3 mb-4">Artikel & Berita Pertanian</h1>
<form class="row g-2 mb-3" method="GET">
    <div class="col-md-6">
        <input name="q" value="{{ $q }}" class="form-control" placeholder="Cari berita atau artikel...">
    </div>
    <div class="col-md-2"><button class="btn btn-emerald w-100">Cari</button></div>
</form>
<ul class="nav nav-pills mb-4">
    <li class="nav-item"><a class="nav-link {{ $tab==='semua' ? 'active' : '' }}" href="{{ route('site.posts', ['q'=>$q]) }}">Semua</a></li>
    <li class="nav-item"><a class="nav-link {{ $tab==='berita' ? 'active' : '' }}" href="{{ route('site.posts', ['jenis'=>'berita','q'=>$q]) }}">Berita Kegiatan</a></li>
    <li class="nav-item"><a class="nav-link {{ $tab==='artikel' ? 'active' : '' }}" href="{{ route('site.posts', ['jenis'=>'artikel','q'=>$q]) }}">Artikel Edukasi Tani</a></li>
</ul>
<div class="row g-4">
    @forelse($posts as $post)
        <div class="col-md-4">
            <a href="{{ route('site.show', $post->slug) }}" class="text-decoration-none text-dark">
                <div class="card news-card border-0 shadow-sm h-100 overflow-hidden">
                    <img src="{{ $post->coverUrl() ?: 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=800' }}" alt="{{ $post->judul }}">
                    <div class="card-body">
                        <div class="text-muted" style="font-size:12px;">{{ optional($post->published_at ?: $post->created_at)->translatedFormat('d F Y') }} · {{ $post->author ?: 'Humas UPTD BBI TPH' }}</div>
                        <h2 class="h5 mt-2">{{ $post->judul }}</h2>
                        <p class="news-excerpt text-muted mb-2">{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 140) }}</p>
                        <span class="text-success fw-semibold">Baca Selengkapnya →</span>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-12"><div class="alert alert-light border">Belum ada berita atau artikel yang dipublikasikan.</div></div>
    @endforelse
</div>
<div class="mt-4">{{ $posts->links() }}</div>
@endsection
