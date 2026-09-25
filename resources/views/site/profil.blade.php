@extends('layouts.public')
@section('title', 'Profil Kelembagaan - '.$situs->office_name)

@section('content')
<p class="page-kicker mb-1">Profil Kelembagaan</p>
<h1 class="h3 mb-4">{{ $situs->office_name }}</h1>
<div class="row g-4">
    <aside class="col-lg-3">
        <div class="card border-0 shadow-sm sticky-top" style="top: 88px;">
            <div class="list-group list-group-flush">
                @foreach($categories as $key => $label)
                    <a class="list-group-item list-group-item-action" href="#{{ $key }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
    </aside>
    <div class="col-lg-9">
        @foreach($categories as $key => $label)
            <section id="{{ $key }}" class="mb-5">
                <h2 class="h4 mb-3">{{ $label }}</h2>
                @forelse($contents->get($key) ?? [] as $item)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <h3 class="h5">{{ $item->judul }}</h3>
                            @if($item->coverUrl())<img src="{{ $item->coverUrl() }}" class="img-fluid rounded mb-3" alt="{{ $item->judul }}">@endif
                            <div>{!! nl2br(e($item->body)) !!}</div>
                        </div>
                    </div>
                @empty
                    <div class="card border-0 shadow-sm"><div class="card-body text-muted">Konten {{ strtolower($label) }} belum ditambahkan.</div></div>
                @endforelse
            </section>
        @endforeach
    </div>
</div>
@endsection
