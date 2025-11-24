@extends('layouts.app')

@section('title', 'Dashboard - SIBIT')

@section('content')
<div class="container-fluid">
    <!-- Alert: Benih yang akan kadaluarsa -->
    @if($expiringSeeds->count() > 0)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Peringatan: Benih Akan Kadaluarsa</h5>
        <p class="mb-2">Terdapat <strong>{{ $expiringSeeds->count() }}</strong> lot benih yang akan kadaluarsa dalam 30 hari ke depan:</p>
        <ul class="mb-0">
            @foreach($expiringSeeds->take(5) as $lot)
            <li>
                <strong>{{ $lot->inventoryType->name ?? 'N/A' }}</strong> 
                - Stok: {{ number_format($lot->current_stock, 2) }} {{ $lot->stock_unit ?? '' }}
                - Kadaluarsa: {{ $lot->expiry_date->format('d M Y') }} 
                (<strong>{{ $lot->days_until_expiry }} hari lagi</strong>)
                @if($lot->warehouse)
                    - Gudang: {{ $lot->warehouse->name }}
                @endif
            </li>
            @endforeach
            @if($expiringSeeds->count() > 5)
            <li><em>...dan {{ $expiringSeeds->count() - 5 }} lot lainnya</em></li>
            @endif
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Dashboard Eksekutif -->
    <div class="row mb-4">
        <!-- Grafik Tren Produksi -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Grafik Tren Produksi</h5>
                </div>
                <div class="card-body">
                    <canvas id="productionTrendChart" height="100"></canvas>
                    <p class="text-muted mt-2 small">Menampilkan hasil panen per bulan (dalam Ton)</p>
                </div>
            </div>
        </div>

        <!-- Pie Chart Stok -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Komposisi Stok Benih</h5>
                </div>
                <div class="card-body">
                    <canvas id="stockCompositionChart" height="200"></canvas>
                    <p class="text-muted mt-2 small">Distribusi stok berdasarkan kategori (dalam Kg)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Pendapatan -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Grafik Pendapatan</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueTrendChart" height="80"></canvas>
                    <p class="text-muted mt-2 small">Total penjualan bulan berjalan (dalam Rupiah)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Weather Section (Optional) -->
    @if($weatherData)
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-cloud me-2"></i>CUACA KOTA PADANG</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <h1 class="display-4 text-primary me-3">{{ round($weatherData['main']['temp']) }}°C</h1>
                                <i class="fas fa-cloud text-muted fa-2x"></i>
                            </div>
                            <p class="text-muted mb-2">{{ $weatherData['weather'][0]['description'] }} - H {{ round($weatherData['main']['temp_max']) }}°C L {{ round($weatherData['main']['temp_min']) }}°C</p>
                            
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Sunset: 6:23PM</small><br>
                                    <small class="text-muted">Wind: {{ $weatherData['wind']['speed'] ?? 1 }} mps <i class="fas fa-arrow-up"></i></small><br>
                                    <small class="text-muted">Humidity: {{ $weatherData['main']['humidity'] }}%</small>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Feels like {{ round($weatherData['main']['feels_like']) }}°C</small><br>
                                    <small class="text-muted">Sky Cover: {{ $weatherData['clouds']['all'] ?? 25 }}%</small><br>
                                    <small class="text-muted">1-Hr Precip: 0mm</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Grafik Tren Produksi (Line Chart)
    const productionCtx = document.getElementById('productionTrendChart').getContext('2d');
    new Chart(productionCtx, {
        type: 'line',
        data: {
            labels: @json($productionTrend['labels']),
            datasets: [{
                label: 'Hasil Panen (Ton)',
                data: @json($productionTrend['data']),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Ton'
                    }
                }
            }
        }
    });

    // 2. Pie Chart Stok (Pie Chart)
    const stockCtx = document.getElementById('stockCompositionChart').getContext('2d');
    new Chart(stockCtx, {
        type: 'pie',
        data: {
            labels: @json($stockComposition['labels']),
            datasets: [{
                label: 'Stok (Kg)',
                data: @json($stockComposition['data']),
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += new Intl.NumberFormat('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }).format(context.parsed) + ' Kg';
                            return label;
                        }
                    }
                }
            }
        }
    });

    // 3. Grafik Pendapatan (Bar Chart)
    const revenueCtx = document.getElementById('revenueTrendChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: @json($revenueTrend['labels']),
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: @json($revenueTrend['data']),
                backgroundColor: 'rgba(23, 162, 184, 0.8)',
                borderColor: 'rgba(23, 162, 184, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0
                            }).format(context.parsed.y);
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0,
                                notation: 'compact'
                            }).format(value);
                        }
                    },
                    title: {
                        display: true,
                        text: 'Rupiah'
                    }
                }
            }
        }
    });
});
</script>
@endsection
