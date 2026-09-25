<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIBESTI - Sistem Informasi Benih Bersertifikat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-green: #10b981;
            --dark-green: #059669;
            --light-green: #d1fae5;
            --accent-yellow: #fbbf24;
            --dark-yellow: #f59e0b;
            --text-dark: #1f2937;
            --text-light: #6b7280;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
        }
        
        /* Navbar */
        .navbar {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white !important;
        }
        
        .navbar-brand i {
            color: var(--accent-yellow);
        }
        
        .navbar-nav .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: all 0.3s;
        }
        
        .navbar-nav .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
        }
        
        .btn-login {
            background: white;
            color: var(--primary-green);
            border: 2px solid white;
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background: transparent;
            color: white;
            border-color: white;
        }
        
        /* Hero Section */
        .hero-section {
            min-height: 600px;
            display: flex;
            align-items: center;
            color: white;
            padding: 4rem 0;
        }
        
        .hero-content h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .hero-content p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }
        
        .search-box {
            background: white;
            border-radius: 50px;
            padding: 1rem 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-top: 2rem;
        }
        
        .search-box input {
            border: none;
            outline: none;
            font-size: 1.1rem;
            width: 100%;
        }
        
        .search-box button {
            background: var(--primary-green);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .search-box button:hover {
            background: var(--dark-green);
            transform: translateY(-2px);
        }
        
        /* Statistics Cards */
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            border-top: 4px solid var(--primary-green);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .stat-card i {
            font-size: 3rem;
            color: var(--primary-green);
            margin-bottom: 1rem;
        }
        
        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-green);
            margin: 0.5rem 0;
        }
        
        .stat-card p {
            color: var(--text-light);
            margin: 0;
        }
        
        /* Section Titles */
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }
        
        .section-title p {
            color: var(--text-light);
            font-size: 1.1rem;
        }
        
        /* Stock Table */
        .stock-table-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .filter-section {
            background: var(--light-green);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead {
            background: var(--primary-green);
            color: white;
        }
        
        .table thead th {
            border: none;
            padding: 1rem;
            font-weight: 600;
        }
        
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
        }
        
        .badge-seed-class {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .badge-BD {
            background: #9333ea;
            color: white;
        }
        
        .badge-BP {
            background: #ffffff;
            color: var(--text-dark);
            border: 2px solid var(--text-dark);
        }
        
        .badge-BR {
            background: #3b82f6;
            color: white;
        }
        
        .status-available {
            color: var(--primary-green);
            font-weight: 600;
        }
        
        .status-habis {
            color: #ef4444;
            font-weight: 600;
        }
        
        /* Featured Varieties */
        .variety-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            height: 100%;
        }
        
        .variety-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .variety-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .variety-card-body {
            padding: 1.5rem;
        }
        
        .variety-card h5 {
            color: var(--primary-green);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        /* Purchase Steps */
        .step-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            height: 100%;
            position: relative;
        }
        
        .step-number {
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--accent-yellow);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .step-card i {
            font-size: 3rem;
            color: var(--primary-green);
            margin: 1rem 0;
        }
        
        .step-card h5 {
            color: var(--text-dark);
            font-weight: 700;
            margin: 1rem 0;
        }
        
        /* Footer */
        .footer {
            background: linear-gradient(135deg, var(--dark-green) 0%, #047857 100%);
            color: white;
            padding: 3rem 0 1rem;
        }
        
        .footer h5 {
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .footer a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .footer a:hover {
            color: white;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.2);
            margin-top: 2rem;
            padding-top: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('landing') }}">
                <i class="fas fa-seedling me-2"></i>SIBESTI
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#beranda">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#stok">Cek Stok Benih</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#varietas">Info Varietas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('public.seed-requests.index') }}">Permintaan Benih</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('public.geowisata.index') }}">Booking Geowisata</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('public.magang.index') }}">Pendaftaran Magang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#kontak">Kontak</a>
                    </li>
                </ul>
                <a href="{{ route('login') }}" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Login Petugas
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="beranda" class="hero-section" style="background: linear-gradient(rgba(16, 185, 129, 0.8), rgba(5, 150, 105, 0.8)), url('{{ $landingSettings['hero_image'] ?? 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=1920' }}') center/cover;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="hero-content">
                        <h1>{{ $landingSettings['hero_title'] ?? 'Penyedia Benih Sumber & Benih Sebar Berkualitas di Sumatera Barat' }}</h1>
                        <p>{{ $landingSettings['hero_subtitle'] ?? 'Pantau ketersediaan stok benih padi bersertifikat secara real-time dari seluruh unit UPTD BBI TPPH.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stock Availability Section -->
    <section id="stok" class="py-5">
        <div class="container">
            <div class="section-title">
                <h2>Informasi Ketersediaan Benih & Harga Retribusi</h2>
                <p>Lihat ketersediaan stok benih bersertifikat dan harga retribusi terbaru</p>
            </div>
            
            <div class="stock-table-container">
                <!-- Filters -->
                <div class="filter-section">
                    <form method="GET" action="{{ route('landing') }}#stok" class="row g-3 align-items-end">
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label fw-bold">Komoditas</label>
                            <select name="commodity_id" id="landingCommodity" class="form-select">
                                <option value="all">Semua komoditas</option>
                                @foreach($commodities ?? [] as $commodity)
                                    <option value="{{ $commodity->getKey() }}" {{ ($commodityFilter ?? 'all') == $commodity->getKey() ? 'selected' : '' }}>
                                        {{ $commodity->category ? $commodity->category.' - ' : '' }}{{ $commodity->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label fw-bold">Varietas</label>
                            <select name="variety_id" id="landingVariety" class="form-select">
                                <option value="all">Semua varietas</option>
                                @foreach($varietyRecords ?? [] as $opt)
                                    <option value="{{ $opt->getKey() }}" {{ ($varietyIdFilter ?? 'all') == $opt->getKey() ? 'selected' : '' }}>{{ $opt->variety ?: $opt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-bold">Cari</label>
                            <input type="text" name="search" class="form-control" value="{{ $searchQuery }}" placeholder="Cari varietas...">
                        </div>
                        <div class="col-lg-1 col-md-6">
                            <button class="btn btn-success w-100">Filter</button>
                        </div>
                    </form>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama Tanaman</th>
                                <th>Varietas</th>
                                <th>Jumlah stok tersedia</th>
                                <th>Satuan</th>
                                <th>Harga per satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(collect($stockData ?? [])->groupBy(fn ($row) => $row['category'] ?: 'Lainnya') as $category => $rows)
                            <tr class="table-light">
                                <th colspan="5">{{ $category }}</th>
                            </tr>
                            @foreach($rows as $stock)
                            <tr>
                                <td>{{ $stock['plant_name'] ?: $stock['variety_name'] }}</td>
                                <td>{{ $stock['variety_detail'] ?: $stock['variety_name'] }}</td>
                                <td><strong>{{ number_format($stock['stock_available'], 2) }}</strong></td>
                                <td>{{ $stock['stock_unit'] ?: '-' }}</td>
                                <td>{{ $stock['unit_price'] !== null ? 'Rp '.number_format($stock['unit_price'], 0, ',', '.') : '-' }}</td>
                            </tr>
                            @endforeach
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <p class="text-muted mb-0">Tidak ada data stok benih yang tersedia</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-4 mb-2">
                    <a href="{{ route('public.seed-requests.create') }}" class="btn btn-success btn-lg">
                        <i class="fas fa-file-signature me-2"></i>Ajukan Permintaan Benih
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Varieties Section -->
    <section id="varietas" class="py-5 bg-light">
        <div class="container">
            <div class="section-title">
                <h2>Informasi Varietas</h2>
                <p>Data publik yang diisi petugas untuk setiap varietas</p>
            </div>
            <form method="GET" action="{{ route('landing') }}#varietas" class="row g-3 mb-4">
                <div class="col-md-3">
                    <select name="info_commodity_id" id="infoCommodity" class="form-select">
                        <option value="all">Semua komoditas</option>
                        @foreach($commodities ?? [] as $commodity)
                            <option value="{{ $commodity->getKey() }}" {{ ($infoCommodityFilter ?? 'all') == $commodity->getKey() ? 'selected' : '' }}>{{ $commodity->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="info_variety_id" id="infoVariety" class="form-select">
                        <option value="all">Semua varietas</option>
                        @foreach($infoVarietyRecords ?? [] as $opt)
                            <option value="{{ $opt->getKey() }}" {{ ($infoVarietyFilter ?? 'all') == $opt->getKey() ? 'selected' : '' }}>{{ $opt->variety ?: $opt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="info_search" class="form-control" value="{{ $infoSearch ?? '' }}" placeholder="Cari varietas...">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success w-100">Cari</button>
                </div>
            </form>
            <div class="row g-4">
                @forelse($publicVarieties ?? [] as $variety)
                <div class="col-md-3 col-sm-6">
                    <div class="variety-card">
                        <img src="{{ $variety['photo'] }}" alt="{{ $variety['name'] }}">
                        <div class="variety-card-body">
                            <h5>{{ $variety['name'] }}</h5>
                            <p class="text-muted small mb-2">{{ $variety['category'] }}</p>
                            <p class="small">{{ $variety['description'] }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">Belum ada informasi publik varietas.</p>
                </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Purchase Flow Section -->
    <section id="alur" class="py-5">
        <div class="container">
            <div class="section-title">
                <h2>Alur Pembelian</h2>
                <p>Langkah-langkah mudah untuk mendapatkan benih bersertifikat</p>
            </div>
            <div class="row g-4">
                <div class="col-md col-sm-6">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <i class="fas fa-search"></i>
                        <h5>Cek ketersediaan benih</h5>
                        <p class="text-muted">Lihat stok varietas yang tersedia di atas</p>
                    </div>
                </div>
                <div class="col-md col-sm-6">
                    <a href="{{ route('public.seed-requests.create') }}" class="text-decoration-none text-dark">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <i class="fas fa-file-alt"></i>
                        <h5>Mengisi form permintaan</h5>
                        <p class="text-muted">Ajukan jumlah benih yang diinginkan</p>
                    </div>
                    </a>
                </div>
                <div class="col-md col-sm-6">
                    <a href="{{ route('public.seed-requests.index') }}" class="text-decoration-none text-dark">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <i class="fas fa-clipboard-check"></i>
                        <h5>Lihat status permintaan</h5>
                        <p class="text-muted">Pantau verifikasi hingga siap diambil</p>
                    </div>
                    </a>
                </div>
                <div class="col-md col-sm-6">
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <i class="fas fa-building"></i>
                        <h5>Datang ke UPTD BBI TPHP</h5>
                        <p class="text-muted">Jemput benih dan selesaikan transaksi</p>
                    </div>
                </div>
                <div class="col-md col-sm-6">
                    <div class="step-card">
                        <div class="step-number">5</div>
                        <i class="fas fa-check-circle"></i>
                        <h5>Selesai</h5>
                        <p class="text-muted">Benih telah diambil dan tercatat</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="kontak" class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><i class="fas fa-map-marker-alt me-2"></i>Alamat Kantor Pusat</h5>
                    <p class="mb-0">
                        {!! $landingSettings['office_address'] ?? 'UPTD Balai Benih Induk Tanaman Pangan dan Hortikultura<br>Jl. Pertanian, Lubuk Minturun, Kec. Koto Tangah, Kota Padang, Sumatera Barat 25586' !!}
                    </p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5><i class="fas fa-link me-2"></i>Tautan Cepat</h5>
                    <ul class="list-unstyled">
                        <li><a href="#beranda"><i class="fas fa-chevron-right me-2"></i>Beranda</a></li>
                        <li><a href="#stok"><i class="fas fa-chevron-right me-2"></i>Cek Stok Benih</a></li>
                        <li><a href="#varietas"><i class="fas fa-chevron-right me-2"></i>Info Varietas</a></li>
                        <li><a href="{{ route('public.geowisata.index') }}"><i class="fas fa-chevron-right me-2"></i>Booking Geowisata</a></li>
                        <li><a href="{{ route('public.magang.index') }}"><i class="fas fa-chevron-right me-2"></i>Pendaftaran Magang</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5><i class="fas fa-phone me-2"></i>Kontak</h5>
                    <p class="mb-2">
                        <i class="fas fa-phone-alt me-2"></i>Telp: {{ $landingSettings['office_phone'] ?? '(0751) 123456' }}<br>
                        <i class="fab fa-whatsapp me-2"></i>WhatsApp: {{ $landingSettings['office_whatsapp'] ?? '+62 812-3456-7890' }}<br>
                        <i class="fas fa-envelope me-2"></i>Email: {{ $landingSettings['office_email'] ?? 'info@bbitpph.sumbar.go.id' }}
                    </p>
                    <div class="mt-3">
                        <a href="{{ $landingSettings['facebook_url'] ?? '#' }}" class="btn btn-light btn-sm me-2" target="_blank"><i class="fab fa-facebook"></i></a>
                        <a href="{{ $landingSettings['instagram_url'] ?? '#' }}" class="btn btn-light btn-sm me-2" target="_blank"><i class="fab fa-instagram"></i></a>
                        <a href="{{ $landingSettings['youtube_url'] ?? '#' }}" class="btn btn-light btn-sm" target="_blank"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p class="mb-0">&copy; {{ date('Y') }} SIBESTI - Sistem Informasi Benih Bersertifikat. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        function bindVarietyFilter(commoditySelectId, varietySelectId, selectedVariety) {
            const commodity = document.getElementById(commoditySelectId);
            const variety = document.getElementById(varietySelectId);
            if (!commodity || !variety) return;
            const current = selectedVariety || variety.value || 'all';
            function loadVarieties() {
                const commodityId = commodity.value || 'all';
                fetch('{{ route('landing.varieties') }}?commodity_id=' + encodeURIComponent(commodityId))
                    .then(r => r.json())
                    .then(rows => {
                        const keep = variety.value;
                        variety.innerHTML = '<option value="all">Semua varietas</option>';
                        (rows || []).forEach(function (row) {
                            const opt = document.createElement('option');
                            opt.value = row.seed_varieties_id;
                            opt.textContent = row.variety || row.name;
                            variety.appendChild(opt);
                        });
                        const preferred = keep && keep !== 'all' ? keep : current;
                        if (preferred && [...variety.options].some(o => o.value == preferred)) {
                            variety.value = preferred;
                        }
                    })
                    .catch(function () {});
            }
            commodity.addEventListener('change', function () {
                variety.value = 'all';
                loadVarieties();
            });
        }
        bindVarietyFilter('landingCommodity', 'landingVariety', @json($varietyIdFilter ?? 'all'));
        bindVarietyFilter('infoCommodity', 'infoVariety', @json($infoVarietyFilter ?? 'all'));
    </script>
</body>
</html>

