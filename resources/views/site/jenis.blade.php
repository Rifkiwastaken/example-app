@extends('layouts.public')
@php $navJenis = $navJenis ?? \App\Models\WebsiteContent::navJenis(); @endphp
@section('title', ($navJenis[$jenis] ?? ucfirst($jenis)).' - '.$situs->office_name)

@section('content')
<p class="page-kicker mb-1">Konten</p>
<h1 class="h3 mb-4">{{ $navJenis[$jenis] ?? ucfirst($jenis) }}</h1>
<div class="row g-4">
    @forelse($items as $item)
        <div class="col-md-4">
            <a href="{{ route('site.show', $item->slug) }}" class="text-decoration-none text-dark">
                <div class="card news-card border-0 shadow-sm h-100">
                    @if($item->coverUrl())<img src="{{ $item->coverUrl() }}" alt="{{ $item->judul }}">@endif
                    <div class="card-body">
                        <h2 class="h5">{{ $item->judul }}</h2>
                        <p class="news-excerpt text-muted">{{ $item->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($item->body), 140) }}</p>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-12"><div class="alert alert-light border">Belum ada konten pada jenis ini.</div></div>
    @endforelse
</div>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
