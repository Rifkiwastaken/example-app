@extends('layouts.app')

@section('title', 'Laporan - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Modul Laporan</h4>
        <small class="text-muted">Laporan lengkap untuk evaluasi dan audit</small>
    </div>
</div>

<div class="row">
    <!-- A. Laporan Produksi & Pertanian -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-seedling me-2"></i>
                    Laporan Produksi & Pertanian
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Menjawab: "Seberapa produktif lahan kita dan apakah target tercapai?"</p>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="{{ route('reports.planting-harvest') }}" class="text-decoration-none">
                            <i class="fas fa-chart-bar me-2 text-primary"></i>
                            Laporan Realisasi Tanam & Panen
                        </a>
                        <br>
                        <small class="text-muted ms-4">Membandingkan rencana (target) dengan realisasi lapangan</small>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('reports.production-supplies') }}" class="text-decoration-none">
                            <i class="fas fa-flask me-2 text-primary"></i>
                            Laporan Penggunaan Sarana Produksi
                        </a>
                        <br>
                        <small class="text-muted ms-4">Rekap penggunaan pupuk dan pestisida untuk audit biaya</small>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('reports.by-location') }}" class="text-decoration-none">
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                            Laporan Per Lokasi Lahan
                        </a>
                        <br>
                        <small class="text-muted ms-4">Laporan lengkap semua data pelaporan per lokasi lahan</small>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- B. Laporan Stok & Gudang -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-warehouse me-2"></i>
                    Laporan Stok & Gudang
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Menjawab: "Apa aset yang kita miliki sekarang dan kondisinya?"</p>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="{{ route('reports.stock-position') }}" class="text-decoration-none">
                            <i class="fas fa-boxes me-2 text-success"></i>
                            Laporan Posisi Stok Akhir (Stock Opname)
                        </a>
                        <br>
                        <small class="text-muted ms-4">Jumlah stok real-time di semua gudang</small>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('reports.stock-mutation') }}" class="text-decoration-none">
                            <i class="fas fa-exchange-alt me-2 text-success"></i>
                            Laporan Mutasi Stok (Kartu Stok)
                        </a>
                        <br>
                        <small class="text-muted ms-4">Melacak pergerakan masuk dan keluar benih (Audit trail)</small>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- C. Laporan Penjualan & Distribusi -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Laporan Penjualan & Distribusi
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Menjawab: "Berapa pendapatan kita dan kemana benih menyebar?"</p>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="{{ route('reports.sales') }}" class="text-decoration-none">
                            <i class="fas fa-file-invoice-dollar me-2 text-info"></i>
                            Laporan Rekapitulasi Penjualan
                        </a>
                        <br>
                        <small class="text-muted ms-4">Laporan keuangan sederhana untuk pendapatan (PAD)</small>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- D. Laporan Sertifikasi -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-certificate me-2"></i>
                    Laporan Sertifikasi
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Menjawab: "Bagaimana kualitas benih kita?"</p>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="{{ route('reports.certification') }}" class="text-decoration-none">
                            <i class="fas fa-clipboard-check me-2 text-warning"></i>
                            Rekap Status Sertifikasi
                        </a>
                        <br>
                        <small class="text-muted ms-4">Melihat performa kelulusan uji benih</small>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info mt-4">
    <h6 class="alert-heading">
        <i class="fas fa-info-circle me-2"></i>
        Fitur Laporan
    </h6>
    <ul class="mb-0">
        <li><strong>Filter Data:</strong> Setiap laporan memiliki panel filter untuk menyempitkan data sesuai kebutuhan</li>
        <li><strong>Export PDF:</strong> Menghasilkan file PDF dengan kop surat resmi UPTD BBI TPPH (siap cetak)</li>
        <li><strong>Export Excel:</strong> Menghasilkan file .xlsx untuk pengolahan data lebih lanjut</li>
        <li><strong>Preview:</strong> Tampilkan tabel data langsung di layar sebelum di-download</li>
    </ul>
</div>
@endsection





