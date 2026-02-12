@extends('layouts.app')

@section('title', 'Dashboard - SIBESTI')

@section('content')
<div class="container-fluid">
    <!-- Alert: Sertifikasi yang Melewati Masa Edar (hanya untuk admin) -->
    @if(auth()->user()->isAdmin() && $expiredCertifications && $expiredCertifications->count() > 0)
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Peringatan: Sertifikasi Melewati Masa Edar</h5>
        <p class="mb-2">Terdapat <strong>{{ $expiredCertifications->count() }}</strong> sertifikasi yang telah melewati masa edar dan perlu dilakukan sertifikasi ulang:</p>
        <ul class="mb-0">
            @foreach($expiredCertifications->take(5) as $report)
            <li>
                <strong>{{ $report->certification->plant->name ?? ($report->certification->harvest->plant->name ?? 'N/A') }}</strong>
                @if($report->certification->plant->variety)
                    - {{ $report->certification->plant->variety }}
                @endif
                - No. Laporan: {{ $report->report_number_bpsb ?? '-' }}
                - Masa Edar: {{ $report->expiry_date->format('d M Y') }}
                @if($report->certification->harvest->location)
                    - Lokasi: {{ $report->certification->harvest->location->name }}
                @endif
                <a href="{{ route('certifications.show', $report->certification->harvest) }}" class="btn btn-sm btn-primary ms-2">
                    <i class="fas fa-redo me-1"></i>Lakukan Sertifikasi Ulang
                </a>
            </li>
            @endforeach
            @if($expiredCertifications->count() > 5)
            <li><em>...dan {{ $expiredCertifications->count() - 5 }} sertifikasi lainnya</em></li>
            @endif
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Alert: Stok Benih Rendah (hanya untuk admin dan petugas gudang) -->
    @if((auth()->user()->isAdmin() || auth()->user()->role === 'petugas_gudang') && isset($lowStockNotifications) && $lowStockNotifications->count() > 0)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Peringatan: Stok Benih Rendah</h5>
        <p class="mb-2">Terdapat <strong>{{ $lowStockNotifications->count() }}</strong> tipe benih yang stoknya sudah lebih rendah dari peringatan stok rendah:</p>
        <ul class="mb-0">
            @foreach($lowStockNotifications->take(5) as $item)
            <li class="mb-2">
                <strong>{{ $item['inventory_type_name'] }}</strong>
                @if($item['variety'])
                    - {{ $item['variety'] }}
                @endif
                <br>
                <small class="text-muted">
                    Stok saat ini: <strong>{{ number_format($item['current_stock'], 2) }} {{ $item['stock_unit'] }}</strong> | 
                    Peringatan stok rendah: <strong>{{ number_format($item['threshold'], 2) }} {{ $item['threshold_unit'] }}</strong> | 
                    Kekurangan: <strong>{{ number_format($item['difference'], 2) }} {{ $item['stock_unit'] }}</strong>
                </small>
                @if(!empty($item['inventory_type_id']))
                <a href="{{ route('seed-stock.show', $item['inventory_type_id']) }}" class="btn btn-sm btn-primary ms-2">
                    <i class="fas fa-boxes me-1"></i>Lihat Stok Benih
                </a>
                @endif
            </li>
            @endforeach
            @if($lowStockNotifications->count() > 5)
            <li><em>...dan {{ $lowStockNotifications->count() - 5 }} tipe benih lainnya</em></li>
            @endif
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Alert: Benih di Bin yang Melewati Masa Kadaluarsa (hanya untuk admin dan petugas gudang) -->
    @if((auth()->user()->isAdmin() || auth()->user()->role === 'petugas_gudang') && isset($expiredBinStocks) && $expiredBinStocks->count() > 0)
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Peringatan: Benih di Bin Melewati Masa Kadaluarsa</h5>
        <p class="mb-2">Terdapat benih di bin yang sudah melewati masa kadaluarsa dan perlu dilakukan pengurangan stok:</p>
        <ul class="mb-0">
            @foreach($expiredBinStocks->take(5) as $binStock)
            <li class="mb-2">
                <strong>{{ $binStock['warehouse_name'] }}</strong> - 
                <strong>{{ $binStock['bin_name'] }}</strong> ({{ $binStock['bin_internal_id'] }})
                <br>
                <small class="text-muted">
                    Terdapat <strong>{{ $binStock['expired_count'] }}</strong> lot yang kadaluarsa dengan total stok 
                    <strong>{{ number_format($binStock['total_expired_stock'], 2) }} {{ $binStock['lots']->first()['stock_unit'] ?? 'kg' }}</strong>
                </small>
                @if($binStock['warehouse_id'])
                    <a href="{{ route('warehouse-locations.show', $binStock['warehouse_id']) }}?bin_id={{ $binStock['bin_id'] }}" class="btn btn-sm btn-primary ms-2">
                        <i class="fas fa-boxes me-1"></i>Lihat Daftar Stok
                    </a>
                @endif
            </li>
            @endforeach
            @if($expiredBinStocks->count() > 5)
            <li><em>...dan {{ $expiredBinStocks->count() - 5 }} bin lainnya</em></li>
            @endif
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Alert: Benih Mendekati/Melewati Masa Kadaluarsa (sesuai penanggung jawab) -->
    @if($expiringSeeds && $expiringSeeds->count() > 0)
    <div class="alert alert-{{ $expiringSeeds->where('is_expired', true)->count() > 0 ? 'danger' : 'warning' }} alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Peringatan: Benih Mendekati/Melewati Masa Kadaluarsa</h5>
        <p class="mb-2">Terdapat <strong>{{ $expiringSeeds->count() }}</strong> benih yang mendekati atau sudah melewati masa kadaluarsa dan perlu dilakukan sertifikasi ulang:</p>
        <ul class="mb-0">
            @foreach($expiringSeeds->take(5) as $seed)
            <li>
                <strong>
                    @if($seed->notification_type === 'certified_seed')
                        {{ $seed->certification->plant->name ?? ($seed->certification->harvest->plant->name ?? 'N/A') }}
                    @else
                        {{ $seed->plant->name ?? 'N/A' }}
                    @endif
                </strong>
                @if($seed->notification_type === 'certified_seed')
                    @if($seed->certification->plant->variety ?? $seed->certification->harvest->plant->variety)
                        - {{ $seed->certification->plant->variety ?? $seed->certification->harvest->plant->variety }}
                    @endif
                @else
                    @if($seed->plant->variety)
                        - {{ $seed->plant->variety }}
                    @endif
                @endif
                - Masa Edar: {{ $seed->expiry_date->format('d M Y') }}
                @if($seed->is_expired)
                    <span class="badge bg-danger ms-2">Sudah Melewati</span>
                @else
                    <span class="badge bg-warning ms-2">Mendekati ({{ $seed->expiry_date->diffInMonths(now()) }} bulan)</span>
                @endif
                @php
                    $certification = null;
                    if ($seed->notification_type === 'certified_seed') {
                        $certification = $seed->certification;
                    } else {
                        $certification = \App\Models\Certification::where('plant_id', $seed->plant_id)->first();
                    }
                @endphp
                @if($certification)
                    <a href="{{ route('certifications.show', $certification->harvest) }}" class="btn btn-sm btn-primary ms-2">
                        <i class="fas fa-redo me-1"></i>Lakukan Sertifikasi Ulang
                    </a>
                @endif
            </li>
            @endforeach
            @if($expiringSeeds->count() > 5)
            <li><em>...dan {{ $expiringSeeds->count() - 5 }} benih lainnya</em></li>
            @endif
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(auth()->user()->isAdmin() || in_array(auth()->user()->role, ['kepala_satuan_tugas', 'penangkar']))
    <!-- Alert: Tugas Mendekati Deadline -->
    @if($taskNotifications && $taskNotifications->count() > 0)
    <div class="alert alert-{{ $taskNotifications->where('is_urgent', true)->count() > 0 ? 'danger' : 'warning' }} alert-dismissible fade show" role="alert">
        <h5 class="alert-heading">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Peringatan: Tugas Mendekati Deadline
        </h5>
        <p class="mb-2">Terdapat <strong>{{ $taskNotifications->count() }}</strong> tugas yang mendekati deadline (3 hari ke depan):</p>
        <ul class="mb-0">
            @foreach($taskNotifications->take(5) as $task)
            <li>
                <strong>{{ Str::limit($task->title, 50) }}</strong>
                - Lokasi: {{ $task->plantingLocation->name ?? 'Umum' }}
                - Deadline: {{ $task->due_date->format('d M Y') }}
                @if($task->days_until_deadline === 0)
                    (<strong class="text-danger">Hari ini!</strong>)
                @elseif($task->days_until_deadline === 1)
                    (<strong class="text-warning">Besok!</strong>)
                @else
                    (<strong>{{ $task->days_until_deadline }} hari lagi</strong>)
                @endif
            </li>
            @endforeach
            @if($taskNotifications->count() > 5)
            <li><em>...dan {{ $taskNotifications->count() - 5 }} tugas lainnya</em></li>
            @endif
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- List Notifikasi -->
    <div class="row mb-4">
        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-bell me-2"></i>List Notifikasi</h5>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    @php
                        $allNotifications = collect();
                        if ($upcomingTasks) {
                            $allNotifications = $allNotifications->merge($upcomingTasks->map(function($task) {
                                $task->notification_type = 'task';
                                return $task;
                            }));
                        }
                        if ($noteNotifications) {
                            $allNotifications = $allNotifications->merge($noteNotifications->map(function($note) {
                                $note->notification_type = 'note';
                                return $note;
                            }));
                        }
                        if (auth()->user()->isAdmin() && isset($expiredCertifications) && $expiredCertifications) {
                            $allNotifications = $allNotifications->merge($expiredCertifications->map(function($report) {
                                $report->notification_type = 'certification';
                                return $report;
                            }));
                        }
                        if (isset($expiringSeeds) && $expiringSeeds) {
                            $allNotifications = $allNotifications->merge($expiringSeeds->map(function($seed) {
                                if (!isset($seed->notification_type)) {
                                    $seed->notification_type = 'seed';
                                }
                                return $seed;
                            }));
                        }
                        $allNotifications = $allNotifications->sortByDesc(function($item) {
                            if ($item->notification_type === 'task') {
                                return $item->due_date ? $item->due_date->timestamp : 0;
                            } elseif ($item->notification_type === 'note') {
                                return $item->note_date ? $item->note_date->timestamp : $item->created_at->timestamp;
                            } elseif ($item->notification_type === 'certification') {
                                return $item->expiry_date ? $item->expiry_date->timestamp : 0;
                            } elseif ($item->notification_type === 'seed' || $item->notification_type === 'certified_seed') {
                                return $item->expiry_date ? $item->expiry_date->timestamp : 0;
                            }
                            return 0;
                        })->take(10);
                    @endphp
                    @if($allNotifications->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($allNotifications as $item)
                                @if($item->notification_type === 'task')
                                    <div class="list-group-item px-0 py-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <i class="fas fa-tasks me-1 text-info"></i>
                                                    {{ Str::limit($item->title, 40) }}
                                                </h6>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    {{ $item->plantingLocation->name ?? 'Umum' }}
                                                </small>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-user me-1"></i>
                                                    {{ $item->assignedUser->name ?? 'Tidak ditugaskan' }}
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                @if($item->days_until_deadline === 0)
                                                    <span class="badge bg-danger">Hari ini</span>
                                                @elseif($item->days_until_deadline === 1)
                                                    <span class="badge bg-warning">Besok</span>
                                                @else
                                                    <span class="badge bg-info">{{ $item->days_until_deadline }} hari</span>
                                                @endif
                                                <br>
                                                <small class="text-muted">
                                                    {{ $item->due_date->format('d M Y') }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($item->notification_type === 'certification')
                                    <div class="list-group-item px-0 py-2 border-danger">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <i class="fas fa-certificate me-1 text-danger"></i>
                                                    Sertifikasi Melewati Masa Edar
                                                </h6>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-seedling me-1"></i>
                                                    {{ $item->certification->plant->name ?? ($item->certification->harvest->plant->name ?? 'N/A') }}
                                                    @if($item->certification->plant->variety ?? $item->certification->harvest->plant->variety)
                                                        - {{ $item->certification->plant->variety ?? $item->certification->harvest->plant->variety }}
                                                    @endif
                                                </small>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-calendar-times me-1"></i>
                                                    Masa Edar: {{ $item->expiry_date->format('d M Y') }}
                                                </small>
                                                <small class="text-danger d-block">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Perlu melakukan sertifikasi ulang
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <a href="{{ route('certifications.show', $item->certification->harvest) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-redo me-1"></i>Sertifikasi Ulang
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($item->notification_type === 'seed' || $item->notification_type === 'certified_seed')
                                    <div class="list-group-item px-0 py-2 border-{{ $item->is_expired ? 'danger' : 'warning' }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <i class="fas fa-seedling me-1 text-{{ $item->is_expired ? 'danger' : 'warning' }}"></i>
                                                    Benih {{ $item->is_expired ? 'Melewati' : 'Mendekati' }} Masa Edar
                                                </h6>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-seedling me-1"></i>
                                                    @if($item->notification_type === 'certified_seed')
                                                        {{ $item->certification->plant->name ?? ($item->certification->harvest->plant->name ?? 'N/A') }}
                                                        @if($item->certification->plant->variety ?? $item->certification->harvest->plant->variety)
                                                            - {{ $item->certification->plant->variety ?? $item->certification->harvest->plant->variety }}
                                                        @endif
                                                    @else
                                                        {{ $item->plant->name ?? 'N/A' }}
                                                        @if($item->plant->variety)
                                                            - {{ $item->plant->variety }}
                                                        @endif
                                                    @endif
                                                </small>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-calendar-times me-1"></i>
                                                    Masa Edar: {{ $item->expiry_date->format('d M Y') }}
                                                </small>
                                                <small class="text-{{ $item->is_expired ? 'danger' : 'warning' }} d-block">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    {{ $item->is_expired ? 'Sudah melewati masa edar' : 'Mendekati masa edar' }} - Perlu melakukan sertifikasi ulang
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                @php
                                                    $certification = null;
                                                    if ($item->notification_type === 'certified_seed') {
                                                        $certification = $item->certification;
                                                    } else {
                                                        $certification = \App\Models\Certification::where('plant_id', $item->plant_id)->first();
                                                    }
                                                @endphp
                                                @if($certification)
                                                    <a href="{{ route('certifications.show', $certification->harvest) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-redo me-1"></i>Sertifikasi Ulang
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="list-group-item px-0 py-2 border-warning">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <i class="fas fa-sticky-note me-1 text-warning"></i>
                                                    {{ Str::limit($item->title ?: 'Catatan', 40) }}
                                                </h6>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    {{ $item->plantingLocation->name ?? 'Umum' }}
                                                </small>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-user me-1"></i>
                                                    {{ $item->user->name ?? 'Tidak diketahui' }}
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-warning">Catatan Baru</span>
                                                <br>
                                                <small class="text-muted">
                                                    {{ $item->note_date->format('d M Y') }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">Tidak ada notifikasi</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($isAdmin)
    <!-- Dashboard Eksekutif (Hanya untuk Admin) -->
    <div class="row mb-4">
        <!-- Grafik Tren Produksi -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Grafik Tren Produksi</h5>
                    <form method="GET" action="{{ route('dashboard') }}" class="d-inline">
                        <input type="hidden" name="inventory_type_filter" value="{{ $inventoryTypeFilter }}">
                        <select name="plant_filter" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                            <option value="all" {{ $plantFilter == 'all' ? 'selected' : '' }}>Semua Tanaman</option>
                            @foreach($plants as $plant)
                                <option value="{{ $plant->id }}" {{ $plantFilter == $plant->id ? 'selected' : '' }}>
                                    {{ $plant->name }} @if($plant->variety) - {{ $plant->variety }} @endif
                                </option>
                            @endforeach
                        </select>
                    </form>
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
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Komposisi Stok Benih</h5>
                    <form method="GET" action="{{ route('dashboard') }}" class="d-inline">
                        <input type="hidden" name="plant_filter" value="{{ $plantFilter }}">
                        <select name="inventory_type_filter" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                            <option value="all" {{ $inventoryTypeFilter == 'all' ? 'selected' : '' }}>Semua Stok Benih</option>
                            @foreach($inventoryTypes as $invType)
                                <option value="{{ $invType->inventory_type_id }}" {{ $inventoryTypeFilter == $invType->inventory_type_id ? 'selected' : '' }}>
                                    {{ $invType->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="card-body">
                    <canvas id="stockCompositionChart" height="200"></canvas>
                    <p class="text-muted mt-2 small">Distribusi stok terjual berdasarkan tipe benih (dalam Kg)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Pendapatan -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Grafik Pendapatan</h5>
                    <form method="GET" action="{{ route('dashboard') }}" class="d-inline">
                        <input type="hidden" name="plant_filter" value="{{ $plantFilter }}">
                        <select name="inventory_type_filter" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                            <option value="all" {{ $inventoryTypeFilter == 'all' ? 'selected' : '' }}>Semua Stok Benih</option>
                            @foreach($inventoryTypes as $invType)
                                <option value="{{ $invType->inventory_type_id }}" {{ $inventoryTypeFilter == $invType->inventory_type_id ? 'selected' : '' }}>
                                    {{ $invType->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="card-body">
                    <canvas id="revenueTrendChart" height="80"></canvas>
                    <p class="text-muted mt-2 small">Total penjualan bulan berjalan berdasarkan stok benih (dalam Rupiah)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Data Dashboard -->
    <div class="row mb-4">
        <!-- Tabel Produksi -->
        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-table me-2"></i>Tabel Data Produksi (Hasil Panen)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanaman</th>
                                    <th>Varietas</th>
                                    <th>Tipe Tanaman</th>
                                    <th>Total Jumlah Panen</th>
                                    <th>Total (Ton)</th>
                                    <th>Jumlah Panen</th>
                                    <th>Tanggal Panen Terakhir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($productionTableData as $data)
                                <tr>
                                    <td><strong>{{ $data['plant_name'] }}</strong></td>
                                    <td>{{ $data['variety'] ?: '-' }}</td>
                                    <td>{{ $data['plant_type'] ?: '-' }}</td>
                                    <td>{{ number_format($data['total_quantity'], 2) }} {{ $data['unit'] }}</td>
                                    <td><strong>{{ number_format($data['total_ton'], 2) }} Ton</strong></td>
                                    <td><span class="badge bg-info">{{ $data['harvest_count'] }} kali</span></td>
                                    <td>{{ $data['latest_harvest_date'] ? \Carbon\Carbon::parse($data['latest_harvest_date'])->format('d M Y') : '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada data produksi</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Tabel Stok Benih -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-table me-2"></i>Tabel Stok Benih Terjual</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Tipe Benih</th>
                                    <th>Varietas</th>
                                    <th>Stok Saat Ini</th>
                                    <th>Terjual</th>
                                    <th>Jumlah Penjualan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stockTableData as $data)
                                <tr>
                                    <td><strong>{{ $data['inventory_type_name'] }}</strong></td>
                                    <td>{{ $data['variety'] ?: '-' }}</td>
                                    <td>{{ number_format($data['current_stock'], 2) }} {{ $data['unit'] }}</td>
                                    <td><span class="badge bg-warning">{{ number_format($data['sold_quantity'], 2) }} {{ $data['unit'] }}</span></td>
                                    <td><span class="badge bg-info">{{ $data['sale_count'] }} transaksi</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada stok benih yang terjual</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Pendapatan -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-table me-2"></i>Tabel Pendapatan per Stok Benih</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Tipe Benih</th>
                                    <th>Varietas</th>
                                    <th>Total Pendapatan</th>
                                    <th>Jumlah Terjual</th>
                                    <th>Harga Rata-rata</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($revenueTableData as $data)
                                <tr>
                                    <td><strong>{{ $data['inventory_type_name'] }}</strong></td>
                                    <td>{{ $data['variety'] ?: '-' }}</td>
                                    <td><strong class="text-success">Rp {{ number_format($data['total_revenue'], 0, ',', '.') }}</strong></td>
                                    <td>{{ number_format($data['total_quantity'], 2) }} {{ $data['unit'] }}</td>
                                    <td>Rp {{ number_format($data['average_price'], 0, ',', '.') }}/{{ $data['unit'] }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada data pendapatan</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

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
    @if($isAdmin)
    // 1. Grafik Tren Produksi (Line Chart)
    const productionCtx = document.getElementById('productionTrendChart');
    if (productionCtx) {
        new Chart(productionCtx.getContext('2d'), {
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
    }

    // 2. Pie Chart Stok (Pie Chart)
    const stockCtx = document.getElementById('stockCompositionChart');
    if (stockCtx) {
        new Chart(stockCtx.getContext('2d'), {
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
    }

    // 3. Grafik Pendapatan (Bar Chart)
    const revenueCtx = document.getElementById('revenueTrendChart');
    if (revenueCtx) {
        new Chart(revenueCtx.getContext('2d'), {
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
    }
    @endif
});
</script>
@endsection
