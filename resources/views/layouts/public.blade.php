<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', ($situs->office_name ?? 'SIBESTI'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('styles')
    <style>
        :root {
            --instansi: #065f46;
            --instansi-dark: #064e3b;
            --latar: #f8fafc;
            --aksen: #f59e0b;
            --aksen-dark: #d97706;
        }
        html, body { min-height: 100%; }
        body { background: var(--latar); color: #0f172a; font-family: "Segoe UI", system-ui, sans-serif; }
        .btn-login-petugas { background: var(--aksen); border: 1px solid var(--aksen); color: #111827; font-weight: 700; white-space: nowrap; }
        .btn-login-petugas:hover { background: var(--aksen-dark); border-color: var(--aksen-dark); color: #fff; }
        .site-nav { background: var(--instansi); box-shadow: 0 2px 12px rgba(6,95,70,.25); }
        .site-nav .navbar-brand { font-weight: 700; letter-spacing: .02em; }
        .site-nav .nav-link { color: rgba(255,255,255,.88) !important; font-weight: 600; padding: .7rem .9rem !important; border-bottom: 3px solid transparent; }
        .site-nav .nav-link:hover, .site-nav .nav-link.active {
            color: #fff !important; border-bottom-color: var(--aksen);
        }
        .site-nav .dropdown-menu { border: 0; box-shadow: 0 12px 30px rgba(15,23,42,.12); }
        .brand-sub { font-size: .72rem; font-weight: 500; opacity: .85; line-height: 1.2; max-width: 280px; }
        .page-kicker { font-size: .78rem; letter-spacing: .12em; text-transform: uppercase; color: var(--aksen-dark); font-weight: 700; }
        .btn-emerald { background: var(--instansi); border-color: var(--instansi); color: #fff; }
        .btn-emerald:hover { background: var(--instansi-dark); color: #fff; }
        .btn-amber { background: var(--aksen); border-color: var(--aksen); color: #111827; font-weight: 700; }
        .shortcut-card { background: #fff; border: 0; border-radius: 16px; box-shadow: 0 8px 24px rgba(15,23,42,.06); transition: transform .2s, box-shadow .2s; height: 100%; }
        .shortcut-card:hover { transform: translateY(-4px); box-shadow: 0 16px 32px rgba(6,95,70,.14); }
        .news-card img { aspect-ratio: 16/9; object-fit: cover; transition: transform .6s ease; }
        .news-card:hover img { transform: scale(1.06); }
        .news-excerpt { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        .site-footer { background: #022c22; color: #d1fae5; }
        .site-footer a { color: #fcd34d; text-decoration: none; }
        .pill { display: inline-flex; align-items: center; border-radius: 999px; padding: .2rem .7rem; font-size: .78rem; margin: .1rem; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        input::placeholder, textarea::placeholder {
            color: #94a3b8 !important;
            opacity: 1;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
@php
    $situs = $situs ?? \App\Models\WebsiteSetting::current();
    $navJenis = $navJenis ?? \App\Models\WebsiteContent::navJenis();
@endphp
<nav class="navbar navbar-expand-lg navbar-dark site-nav sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('landing') }}">
            <i class="fas fa-seedling text-warning"></i>
            <span>
                <span class="d-block">SIBESTI</span>
                <span class="brand-sub d-none d-md-block">UPTD BBI TPH Sumatera Barat</span>
            </span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#siteNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="siteNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('landing') ? 'active' : '' }}" href="{{ route('landing') }}">Beranda</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('site.profil') ? 'active' : '' }}" href="{{ route('site.profil') }}">Profil</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('site.posts*') || request()->routeIs('site.show') ? 'active' : '' }}" href="{{ route('site.posts') }}">Berita & Artikel</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('site.documents') ? 'active' : '' }}" href="{{ route('site.documents') }}">Laporan & Dokumen</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('site.prices') ? 'active' : '' }}" href="{{ route('site.prices') }}">Informasi Publik</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('public.*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">Layanan</a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('site.prices') }}">E-Katalog & Stok</a></li>
                        <li><a class="dropdown-item {{ request()->routeIs('public.seed-requests.*') ? 'active' : '' }}" href="{{ route('public.seed-requests.index') }}">Permintaan Benih</a></li>
                        <li><a class="dropdown-item {{ request()->routeIs('public.geowisata.*') ? 'active' : '' }}" href="{{ route('public.geowisata.index') }}">Booking Geowisata</a></li>
                        <li><a class="dropdown-item {{ request()->routeIs('public.magang.*') ? 'active' : '' }}" href="{{ route('public.magang.index') }}">Pendaftaran Magang</a></li>
                    </ul>
                </li>
                @foreach($navJenis as $jenis => $label)
                    @continue(in_array($jenis, ['profil','berita','artikel','laporan','dokumen'], true))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('site.jenis') && request()->route('jenis') === $jenis ? 'active' : '' }}" href="{{ route('site.jenis', $jenis) }}">{{ $label }}</a>
                    </li>
                @endforeach
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('site.contact') ? 'active' : '' }}" href="{{ route('site.contact') }}">Kontak</a></li>
            </ul>
            <div class="ms-lg-3 mt-3 mt-lg-0">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-login-petugas btn-sm px-3 py-2">
                        <i class="fas fa-gauge me-1"></i>Dasbor Petugas
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-login-petugas btn-sm px-3 py-2">
                        <i class="fas fa-sign-in-alt me-1"></i>Login Petugas
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<main class="flex-grow-1 d-flex flex-column">
    @hasSection('hero')
        @yield('hero')
    @endif

    <div class="{{ $fullWidth ?? false ? '' : 'container py-4' }}">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @yield('content')
    </div>
</main>

<footer class="site-footer mt-auto pt-5 pb-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-5">
                <h6 class="text-warning text-uppercase mb-3">{{ $situs->office_name }}</h6>
                <p class="mb-1"><i class="fas fa-map-marker-alt me-2"></i>{{ $situs->address }}</p>
                @if($situs->phone)<p class="mb-1"><i class="fas fa-phone me-2"></i>{{ $situs->phone }}</p>@endif
                @if($situs->whatsapp)<p class="mb-1"><i class="fab fa-whatsapp me-2"></i>{{ $situs->whatsapp }}</p>@endif
                @if($situs->email)<p class="mb-0"><i class="fas fa-envelope me-2"></i>{{ $situs->email }}</p>@endif
            </div>
            <div class="col-md-4">
                <h6 class="text-warning text-uppercase mb-3">Layanan Publik</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route('public.seed-requests.index') }}">Permintaan Benih</a></li>
                    <li class="mb-2"><a href="{{ route('public.geowisata.index') }}">Booking Geowisata</a></li>
                    <li class="mb-2"><a href="{{ route('public.magang.index') }}">Pendaftaran Magang</a></li>
                    <li class="mb-2"><a href="{{ route('site.documents') }}">Laporan & Dokumen</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h6 class="text-warning text-uppercase mb-3">Media</h6>
                <div class="d-flex gap-3 fs-4">
                    @if($situs->facebook_url)<a href="{{ $situs->facebook_url }}" target="_blank" rel="noopener"><i class="fab fa-facebook"></i></a>@endif
                    @if($situs->instagram_url)<a href="{{ $situs->instagram_url }}" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>@endif
                    @if($situs->youtube_url)<a href="{{ $situs->youtube_url }}" target="_blank" rel="noopener"><i class="fab fa-youtube"></i></a>@endif
                </div>
            </div>
        </div>
        <hr class="border-success my-4">
        <div class="small text-center">© {{ date('Y') }} UPTD BBI TPH Provinsi Sumatera Barat · SIBESTI</div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
