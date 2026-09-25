@extends('layouts.public')
@php($fullWidth = true)
@section('title', 'SIBESTI - '.$situs->office_name)

@section('hero')
<section class="text-white py-5" style="background: linear-gradient(rgba(6,78,59,.82), rgba(6,95,70,.88)), url('{{ $situs->hero_image }}') center/cover; min-height: 420px;">
    <div class="container py-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <p class="page-kicker text-warning mb-2">Website Resmi UPTD BBI TPH Sumatera Barat</p>
                <h1 class="display-5 fw-bold">{{ $situs->hero_title }}</h1>
                <p class="lead mb-4">{{ $situs->hero_subtitle ?: $situs->tagline }}</p>
                <a href="#stok" class="btn btn-amber me-2">Cek Stok Benih</a>
                <a href="{{ route('public.seed-requests.create') }}" class="btn btn-outline-light">Ajukan Permintaan</a>
            </div>
        </div>
    </div>
</section>
@endsection

@section('content')
<div class="container py-5" id="stok">
    <div class="text-center mb-4">
        <p class="page-kicker mb-1">Transparansi Stok</p>
        <h2 class="h3">Informasi Ketersediaan Benih</h2>
    </div>
    <form method="GET" class="row g-2 mb-3" id="stock-filter">
        <div class="col-md-3">
            <select name="category" class="form-select" onchange="document.getElementById('plant_name').value='all';document.getElementById('variety_id').value='all';this.form.submit()">
                <option value="all">Semua kategori</option>
                @foreach($categories ?? [] as $category)
                    <option value="{{ $category }}" {{ ($categoryFilter ?? 'all') == $category ? 'selected' : '' }}>{{ $category }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="plant_name" id="plant_name" class="form-select" {{ ($categoryFilter ?? 'all') === 'all' ? 'disabled' : '' }} onchange="document.getElementById('variety_id').value='all';this.form.submit()">
                <option value="all">Semua nama tanaman</option>
                @foreach($plantNameOptions ?? [] as $option)
                    <option value="{{ $option->name }}" {{ ($plantNameFilter ?? 'all') == $option->name ? 'selected' : '' }}>{{ $option->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="variety_id" id="variety_id" class="form-select" {{ ($plantNameFilter ?? 'all') === 'all' ? 'disabled' : '' }} onchange="this.form.submit()">
                <option value="all">Semua varietas</option>
                @foreach($varietyOptions ?? [] as $variety)
                    <option value="{{ $variety->getKey() }}" {{ ($varietyIdFilter ?? 'all') == $variety->getKey() ? 'selected' : '' }}>{{ $variety->variety ?: $variety->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><input name="search" class="form-control" value="{{ $searchQuery }}" placeholder="Cari varietas..."></div>
        <div class="col-md-1"><button class="btn btn-emerald w-100">Filter</button></div>
    </form>
    <div class="table-responsive bg-white rounded shadow-sm mb-3">
        <table class="table mb-0">
            <thead class="table-light"><tr><th>Nama Tanaman</th><th>Varietas</th><th>Stok</th><th>Harga per satuan</th><th>Label</th></tr></thead>
            <tbody>
                @forelse(collect($stockData ?? [])->groupBy(fn ($row) => $row['category'] ?: 'Lainnya') as $category => $rows)
                    <tr class="table-light">
                        <th colspan="5">{{ $category }}</th>
                    </tr>
                    @foreach($rows as $stock)
                    <tr>
                        <td>{{ $stock['plant_name'] ?: $stock['variety_name'] }}</td>
                        <td>{{ $stock['variety_detail'] ?: $stock['variety_name'] }}</td>
                        <td>{{ number_format($stock['stock_available'], 2) }} {{ $stock['stock_unit'] }}</td>
                        <td>{{ $stock['unit_price'] !== null ? 'Rp '.number_format($stock['unit_price'], 0, ',', '.').($stock['stock_unit'] ? ' / '.$stock['stock_unit'] : '') : '-' }}</td>
                        <td>@if(!empty($stock['plant_id']))<button type="button" class="btn btn-sm btn-outline-success btn-label-info" data-plant="{{ $stock['plant_id'] }}">Lihat label</button>@else-@endif</td>
                    </tr>
                    @endforeach
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada stok benih siap salur.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-body">
            <h3 class="h5 mb-3">Alur Pembelian Benih</h3>
            <div class="row text-center g-3">
                <div class="col-md-3"><span class="badge bg-success rounded-pill px-3 py-2">1. Cek stok & label</span></div>
                <div class="col-md-3"><span class="badge bg-success rounded-pill px-3 py-2">2. Ajukan permintaan</span></div>
                <div class="col-md-3"><span class="badge bg-warning text-dark rounded-pill px-3 py-2">3. Verifikasi petugas</span></div>
                <div class="col-md-3"><span class="badge bg-success rounded-pill px-3 py-2">4. Ambil benih / bayar</span></div>
            </div>
        </div>
    </div>

    <div class="mb-5">
        <p class="page-kicker mb-1">Layanan Utama</p>
        <h2 class="h3 mb-4">Pintasan Layanan Publik</h2>
        <div class="row g-4">
            @foreach([
                ['route'=>'site.prices','icon'=>'fa-boxes-stacked','title'=>'E-Katalog & Stok','desc'=>'Tarif retribusi dan ketersediaan gudang'],
                ['route'=>'public.seed-requests.create','icon'=>'fa-file-signature','title'=>'Permintaan Benih','desc'=>'Form permintaan benih online'],
                ['route'=>'public.geowisata.index','icon'=>'fa-map-marked-alt','title'=>'Booking Geowisata','desc'=>'Kunjungan edukasi kebun induk'],
                ['route'=>'public.magang.index','icon'=>'fa-user-graduate','title'=>'Pendaftaran Magang','desc'=>'Program magang UPTD BBI TPH'],
            ] as $card)
                <div class="col-md-6 col-lg-3">
                    <a href="{{ route($card['route']) }}" class="text-decoration-none text-dark">
                        <div class="shortcut-card p-4">
                            <i class="fas {{ $card['icon'] }} fa-2x text-success mb-3"></i>
                            <h3 class="h6">{{ $card['title'] }}</h3>
                            <p class="small text-muted mb-0">{{ $card['desc'] }}</p>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div><p class="page-kicker mb-1">Publikasi</p><h2 class="h3 mb-0">Berita & Artikel</h2></div>
            <a href="{{ route('site.posts') }}" class="btn btn-outline-success">Semua berita</a>
        </div>
        <div class="row g-4">
            @forelse($latestPosts as $post)
                <div class="col-md-4">
                    <a href="{{ route('site.show', $post->slug) }}" class="text-decoration-none text-dark">
                        <div class="card news-card border-0 shadow-sm h-100 overflow-hidden">
                            <img src="{{ $post->coverUrl() ?: 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=800' }}" alt="{{ $post->judul }}">
                            <div class="card-body">
                                <div class="text-muted" style="font-size:12px;">{{ optional($post->published_at)->translatedFormat('d F Y') }}</div>
                                <h3 class="h6 mt-2">{{ $post->judul }}</h3>
                                <p class="news-excerpt text-muted small">{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 120) }}</p>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-light border">Belum ada berita dipublikasikan.</div></div>
            @endforelse
        </div>
    </div>
</div>

<div class="modal fade" id="labelModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content">
    <div class="modal-header bg-success text-white"><h5 class="modal-title" id="labelModalTitle">Stok aktif berdasarkan nomor induk</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="labelModalBody"></div>
</div></div></div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-label-info').forEach(function (btn) {
    btn.addEventListener('click', function () {
        fetch(@json(url('/api/landing/plants')) + '/' + btn.dataset.plant + '/lots')
            .then(r => r.json())
            .then(function (data) {
                document.getElementById('labelModalTitle').textContent = 'Stok aktif · ' + data.plant;
                const lots = data.lots || [];
                if (!lots.length) {
                    document.getElementById('labelModalBody').innerHTML = '<p class="text-muted mb-0">Belum ada stok aktif berdasarkan nomor induk.</p>';
                } else {
                    document.getElementById('labelModalBody').innerHTML = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>No induk</th><th>Tanggal lulus</th><th>Total stok saat ini</th><th>Isi kemasan</th><th>Total produk</th><th>Tanggal masa kadaluarsa</th><th>Sertifikat</th></tr></thead><tbody>' +
                        lots.map(function (l) {
                            return '<tr><td>' + (l.nomor_induk || '-') + '</td><td>' + (l.tanggal_lulus || '-') + '</td><td>' + Number(l.total_stok || 0).toFixed(2) + ' ' + (l.unit || '') + '</td><td>' + Number(l.isi_kemasan || 0).toFixed(2) + ' ' + (l.unit || '') + '</td><td>' + (l.total_produk || 0) + '</td><td>' + (l.tanggal_kadaluarsa || '-') + '</td><td><a class="btn btn-sm btn-outline-success" target="_blank" href="/sertifikat-benih/' + l.stock_id + '">Lihat sertifikat</a></td></tr>';
                        }).join('') + '</tbody></table></div>';
                }
                new bootstrap.Modal(document.getElementById('labelModal')).show();
            });
    });
});
</script>
@endpush
