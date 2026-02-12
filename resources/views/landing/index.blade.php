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
                        <a class="nav-link" href="#alur">Alur Pembelian</a>
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

    <!-- Statistics Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4 col-sm-6">
                    <div class="stat-card">
                        <i class="fas fa-seedling"></i>
                        <h3>{{ number_format($totalVarieties) }}</h3>
                        <p>Total Varietas</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="stat-card">
                        <i class="fas fa-boxes"></i>
                        <h3>{{ number_format($totalStock / 1000, 1) }}</h3>
                        <p>Total Stok Tersedia (Ton)</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="stat-card">
                        <i class="fas fa-warehouse"></i>
                        <h3>{{ $totalWarehouses }}</h3>
                        <p>Jumlah Unit Gudang</p>
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
                    <form method="GET" action="{{ route('landing') }}" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Lokasi Gudang</label>
                            <select name="warehouse" class="form-select" onchange="this.form.submit()">
                                <option value="all" {{ $warehouseFilter == 'all' ? 'selected' : '' }}>Semua Lokasi</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ $warehouseFilter == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Kelas Benih</label>
                            <select name="seed_class" class="form-select" onchange="this.form.submit()">
                                <option value="all" {{ $seedClassFilter == 'all' ? 'selected' : '' }}>Semua Kelas</option>
                                <option value="BD" {{ $seedClassFilter == 'BD' ? 'selected' : '' }}>BD (Benih Dasar)</option>
                                <option value="BP" {{ $seedClassFilter == 'BP' ? 'selected' : '' }}>BP (Benih Pokok)</option>
                                <option value="BR" {{ $seedClassFilter == 'BR' ? 'selected' : '' }}>BR (Benih Sebar)</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <a href="{{ route('landing') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-redo me-2"></i>Reset Filter
                            </a>
                        </div>
                    </form>
                </div>
                
                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama Varietas</th>
                                <th>Kelas Benih</th>
                                <th>Lokasi Gudang</th>
                                <th>Stok Tersedia</th>
                                <th>Harga per Kg</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stockData as $stock)
                            <tr>
                                <td>
                                    <strong>{{ $stock['variety_name'] }}</strong>
                                    @if($stock['variety_detail'])
                                        <br><small class="text-muted">{{ $stock['variety_detail'] }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($stock['seed_class'])
                                        <span class="badge badge-seed-class badge-{{ $stock['seed_class'] }}">
                                            {{ $stock['seed_class'] }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if(count($stock['warehouse_names']) > 0)
                                        {{ implode(', ', $stock['warehouse_names']) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ number_format($stock['stock_available'], 2) }} {{ $stock['stock_unit'] }}</strong>
                                </td>
                                <td>
                                    @if($stock['price_per_kg'] > 0)
                                        <strong class="text-success">Rp {{ number_format($stock['price_per_kg'], 0, ',', '.') }}</strong>
                                    @else
                                        <span class="text-muted">Hubungi Petugas</span>
                                    @endif
                                </td>
                                <td>
                                    @if($stock['status'] == 'Tersedia')
                                        <span class="status-available">
                                            <i class="fas fa-check-circle me-1"></i>Tersedia
                                        </span>
                                    @else
                                        <span class="status-habis">
                                            <i class="fas fa-times-circle me-1"></i>Habis
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Tidak ada data stok benih yang tersedia</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Varieties Section -->
    <section id="varietas" class="py-5 bg-light">
        <div class="container">
            <div class="section-title">
                <h2>Sekilas Info Varietas</h2>
                <p>Varietas unggulan yang tersedia di UPTD BBI TPPH</p>
            </div>
            
            <div class="row g-4">
                @forelse($featuredVarieties as $variety)
                <div class="col-md-3 col-sm-6">
                    <div class="variety-card">
                        <img src="https://images.unsplash.com/photo-1593113598332-cd288d649433?w=400" alt="{{ $variety['name'] }}">
                        <div class="variety-card-body">
                            <h5>{{ $variety['name'] }}</h5>
                            @if($variety['variety'])
                                <p class="text-muted small mb-2">{{ $variety['variety'] }}</p>
                            @endif
                            <div class="d-flex justify-content-between mb-2">
                                <small><i class="fas fa-calendar-alt text-primary me-1"></i>Umur:</small>
                                <small class="fw-bold">{{ $variety['days_to_harvest'] ? $variety['days_to_harvest'] . ' hari' : '-' }}</small>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small><i class="fas fa-chart-line text-success me-1"></i>Potensi Hasil:</small>
                                <small class="fw-bold">{{ $variety['expected_yield'] ? number_format($variety['expected_yield'], 2) . ' ton/ha' : '-' }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">Belum ada varietas yang tersedia</p>
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
                <div class="col-md-3 col-sm-6">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <i class="fas fa-search"></i>
                        <h5>Cek Ketersediaan</h5>
                        <p class="text-muted">Cari varietas benih yang diinginkan dan cek stok tersedia</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <i class="fas fa-phone"></i>
                        <h5>Hubungi Petugas</h5>
                        <p class="text-muted">Hubungi petugas UPTD BBI TPPH melalui WhatsApp</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <i class="fas fa-money-bill-wave"></i>
                        <h5>Pembayaran</h5>
                        <p class="text-muted">Datangi UPTD BBI TPPH Padang pada alamat yang tersedia</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <i class="fas fa-truck"></i>
                        <h5>Ambil Benih</h5>
                        <p class="text-muted">Ambil benih di UPTD BBI TPPH sesuai dengan alamat yang tersedia</p>
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
                        {!! $landingSettings['office_address'] ?? 'UPTD Balai Benih Induk Tanaman Pangan dan Hortikultura<br>Jl. Raya Padang - Bukittinggi KM 15<br>Lubuk Minturun, Padang, Sumatera Barat<br>Kode Pos: 25163' !!}
                    </p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5><i class="fas fa-link me-2"></i>Tautan Cepat</h5>
                    <ul class="list-unstyled">
                        <li><a href="#beranda"><i class="fas fa-chevron-right me-2"></i>Beranda</a></li>
                        <li><a href="#stok"><i class="fas fa-chevron-right me-2"></i>Cek Stok Benih</a></li>
                        <li><a href="#varietas"><i class="fas fa-chevron-right me-2"></i>Info Varietas</a></li>
                        <li><a href="#alur"><i class="fas fa-chevron-right me-2"></i>Alur Pembelian</a></li>
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
        // Smooth scroll for anchor links
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
    </script>
</body>
</html>

