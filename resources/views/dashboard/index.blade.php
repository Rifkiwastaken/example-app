@extends('layouts.app')

@section('title', 'Dashboard - SIBESTI')

@section('content')
<div class="container-fluid">
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-2">
            <label class="form-label">Tahun</label>
            <select name="year" class="form-select" onchange="this.form.submit()">
                @for($y = now()->year; $y >= now()->year - 5; $y--)
                    <option value="{{ $y }}" {{ (int)($year ?? now()->year) === $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Komoditas</label>
            <select name="commodity_id" class="form-select" onchange="this.form.submit()">
                <option value="">Semua komoditas</option>
                @foreach($commodities ?? [] as $commodity)
                    <option value="{{ $commodity->getKey() }}" {{ ($commodityId ?? '') == $commodity->getKey() ? 'selected' : '' }}>{{ $commodity->name }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100 border-0 text-white" style="background:linear-gradient(135deg,#059669,#10b981);">
                <div class="card-body">
                    <div class="small text-white-50">Total Produksi Benih Bersertifikat</div>
                    <div class="display-6 fw-bold">{{ number_format($certifiedThis ?? 0, 0, ',', '.') }} Kg</div>
                    <div class="small mt-1">{{ ($certTrend ?? 0) >= 0 ? '📈 +' : '📉 ' }}{{ $certTrend ?? 0 }}% vs musim lalu</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100 border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="small text-white-50">Kelulusan Sertifikasi BPSB</div>
                    <div class="display-6 fw-bold">{{ $passRate ?? 0 }}%</div>
                    <div class="small mt-1">Validitas mutu musim berjalan</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100 border-0 bg-dark text-white">
                <div class="card-body">
                    <div class="small text-white-50">Total Pendapatan</div>
                    <div class="display-6 fw-bold">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</div>
                    <div class="small mt-1">Penjualan tahun berjalan</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">Komposisi Stok per Kategori Tanaman</div>
                <div class="card-body"><canvas id="chartStockDonut" height="220"></canvas></div>
                <div class="card-footer small text-muted">Klik potongan donat untuk rincian tanaman/varietas.</div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">Komposisi Produksi per Kategori Tanaman</div>
                <div class="card-body"><canvas id="chartProdDonut" height="220"></canvas></div>
                <div class="card-footer small text-muted">Klik potongan donat untuk rincian tanaman/varietas.</div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">Komposisi Penjualan per Kategori Tanaman</div>
                <div class="card-body"><canvas id="chartSaleDonut" height="220"></canvas></div>
                <div class="card-footer small text-muted">Klik potongan donat untuk rincian tanaman/varietas.</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="card h-100 border-warning">
                <div class="card-header bg-warning">Pusat Peringatan Dini</div>
                <div class="card-body" style="max-height:320px;overflow:auto;">
                    @forelse($expiredLots ?? [] as $lot)
                        <div class="alert alert-danger py-2">
                            <strong>{{ $lot->no_label_resmi }}</strong> di rak {{ $lot->rack?->name ?: '-' }} kedaluwarsa
                            ({{ $lot->tgl_kedaluwarsa?->format('d M Y') }}). Stok {{ number_format($lot->stok_saat_ini, 0) }} Kg dibekukan.
                            <a class="btn btn-sm btn-light mt-1" href="{{ route('seed-stock.show', $lot->seed_varieties_id) }}">Ajukan uji ulang</a>
                        </div>
                    @empty
                        <p class="text-muted small">Tidak ada lot kedaluwarsa.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">Peta Sebaran Penyaluran</div>
                <div class="card-body"><div id="geo-map" style="height:320px;" class="rounded"></div></div>
            </div>
        </div>
    </div>

    <!-- Alert: Sertifikasi yang Melewati Masa Edar (hanya untuk admin) -->
    @if(auth()->user()->isAdmin() && $expiredCertifications && $expiredCertifications->count() > 0)
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Peringatan: Sertifikasi Melewati Masa Edar</h5>
        <p class="mb-2">Terdapat <strong>{{ $expiredCertifications->count() }}</strong> sertifikasi yang telah melewati masa edar dan perlu dilakukan sertifikasi ulang:</p>
        <ul class="mb-0">
            @foreach($expiredCertifications->take(5) as $report)
            <li>
                @php $variety = $report->planting?->seedSource?->variety ?? $report->stock?->plant; @endphp
                <strong>{{ $variety?->name ?? $report->report_number_bpsb ?? 'N/A' }}</strong>
                @if($variety?->variety)
                    - {{ $variety->variety }}
                @endif
                - No. Sertifikat Lab: {{ $report->report_number_bpsb ?? '-' }}
                - Masa Edar: {{ optional($report->tgl_kadaluarsa_mutu)->format('d M Y') }}
                <a href="{{ route('reports.certification') }}" class="btn btn-sm btn-primary ms-2">
                    <i class="fas fa-redo me-1"></i>Lihat Sertifikasi
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
                    <a href="{{ route('warehouse-locations.show', $binStock['warehouse_id']) }}?bin_id={{ $binStock['warehouse_bin_id'] }}" class="btn btn-sm btn-primary ms-2">
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
                <strong>{{ $seed->plant?->displayName() ?? $seed->plant?->name ?? 'N/A' }}</strong>
                - Masa Edar: {{ optional($seed->expiry_date)->format('d M Y') }}
                @if($seed->is_expired)
                    <span class="badge bg-danger ms-2">Sudah Melewati</span>
                @else
                    <span class="badge bg-warning ms-2">Mendekati ({{ \Carbon\Carbon::today()->diffInDays($seed->expiry_date) }} hari)</span>
                @endif
                @if($seed->plant)
                    <a href="{{ route('seed-stock.show', [$seed->plant, 'tab' => 'lots']) }}" class="btn btn-sm btn-primary ms-2">
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

    @if(auth()->user()->isAdmin() || in_array(auth()->user()->role, ['kepala_satuan_tugas', 'penangkar', 'petugas_gudang']))
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
                        if (isset($lowStockNotifications) && $lowStockNotifications) {
                            $allNotifications = $allNotifications->merge($lowStockNotifications->map(function ($item) {
                                $obj = is_array($item) ? (object) $item : $item;
                                $obj->notification_type = 'low_stock';
                                return $obj;
                            }));
                        }
                        $allNotifications = $allNotifications->sortByDesc(function($item) {
                            if ($item->notification_type === 'task') {
                                return $item->due_date ? $item->due_date->timestamp : 0;
                            } elseif ($item->notification_type === 'note') {
                                return $item->note_date ? $item->note_date->timestamp : $item->created_at->timestamp;
                            } elseif ($item->notification_type === 'certification') {
                                $expiry = $item->tgl_kadaluarsa_mutu ?? $item->expiry_date;
                                return $expiry ? $expiry->timestamp : 0;
                            } elseif ($item->notification_type === 'seed' || $item->notification_type === 'certified_seed') {
                                return $item->expiry_date ? $item->expiry_date->timestamp : 0;
                            } elseif ($item->notification_type === 'low_stock') {
                                return $item->difference ?? 0;
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
                                    @php $variety = $item->planting?->seedSource?->variety ?? $item->stock?->plant; @endphp
                                    <div class="list-group-item px-0 py-2 border-danger">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <i class="fas fa-certificate me-1 text-danger"></i>
                                                    Sertifikasi Melewati Masa Edar
                                                </h6>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-seedling me-1"></i>
                                                    {{ $variety?->displayName() ?? $variety?->name ?? ($item->report_number_bpsb ?? 'N/A') }}
                                                </small>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-calendar-times me-1"></i>
                                                    Masa Edar: {{ optional($item->tgl_kadaluarsa_mutu ?? $item->expiry_date)->format('d M Y') }}
                                                </small>
                                                <small class="text-danger d-block">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Perlu melakukan sertifikasi ulang
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                @if($variety)
                                                    <a href="{{ route('seed-stock.show', [$variety, 'tab' => 'lots']) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-redo me-1"></i>Sertifikasi Ulang
                                                    </a>
                                                @else
                                                    <a href="{{ route('reports.certification') }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-redo me-1"></i>Lihat Sertifikasi
                                                    </a>
                                                @endif
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
                                                    {{ $item->plant?->displayName() ?? $item->plant?->name ?? 'N/A' }}
                                                </small>
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-calendar-times me-1"></i>
                                                    Masa Edar: {{ optional($item->expiry_date)->format('d M Y') }}
                                                </small>
                                                <small class="text-{{ $item->is_expired ? 'danger' : 'warning' }} d-block">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    {{ $item->is_expired ? 'Sudah melewati masa edar' : 'Mendekati masa edar' }} - Perlu melakukan sertifikasi ulang
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                @if($item->plant)
                                                    <a href="{{ route('seed-stock.show', [$item->plant, 'tab' => 'lots']) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-redo me-1"></i>Sertifikasi Ulang
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @elseif($item->notification_type === 'low_stock')
                                    <div class="list-group-item px-0 py-2 border-warning">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <i class="fas fa-boxes me-1 text-warning"></i>
                                                    Stok Benih Rendah
                                                </h6>
                                                <small class="text-muted d-block">
                                                    <strong>{{ $item->inventory_type_name ?? $item->name }}</strong>
                                                    @if(!empty($item->variety))
                                                        - {{ $item->variety }}
                                                    @endif
                                                </small>
                                                <small class="text-muted d-block">
                                                    Stok saat ini: {{ number_format((float) ($item->current_stock ?? 0), 2) }} {{ $item->stock_unit ?? '' }}
                                                    | Minimum: {{ number_format((float) ($item->threshold ?? 0), 2) }} {{ $item->threshold_unit ?? $item->stock_unit ?? '' }}
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-warning text-dark">Stok rendah</span>
                                                <br>
                                                @if(!empty($item->inventory_type_id ?? $item->id ?? null))
                                                    <a href="{{ route('seed-stock.show', $item->inventory_type_id ?? $item->id) }}" class="btn btn-sm btn-outline-primary mt-1">Lihat stok</a>
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
    @php
        $productionGroups = collect($productionTableData ?? [])->groupBy(fn ($row) => $row['category'] ?? 'Lainnya');
        $stockGroups = collect($stockTableData ?? [])->groupBy(fn ($row) => $row['category'] ?? 'Lainnya');
        $revenueGroups = collect($revenueTableData ?? [])->groupBy(fn ($row) => $row['category'] ?? 'Lainnya');
        $fmtSatuanProduk = fn ($qty, $unit, $produk) => number_format((float) $qty, 2, ',', '.').' '.($unit ?: '').' / '.number_format((int) $produk, 0, ',', '.').' produk';
    @endphp
    <!-- Tabel Data Dashboard -->
    <div class="row mb-4">
        <!-- Tabel Produksi -->
        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-table me-2"></i>Tabel Data Produksi</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nama Tanaman</th>
                                    <th>Varietas</th>
                                    <th>Total Jumlah Panen</th>
                                    <th>Total (Ton)</th>
                                    <th>Jumlah Panen</th>
                                    <th>Tanggal Panen Terakhir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($productionGroups as $category => $rows)
                                    <tr class="table-secondary">
                                        <th colspan="6">{{ $category }}</th>
                                    </tr>
                                    @foreach($rows as $data)
                                    <tr>
                                        <td><strong>{{ $data['plant_name'] }}</strong></td>
                                        <td>{{ $data['variety'] ?: '-' }}</td>
                                        <td>{{ number_format($data['total_quantity'], 2) }} {{ $data['unit'] }}</td>
                                        <td><strong>{{ number_format($data['total_ton'], 2) }} Ton</strong></td>
                                        <td><span class="badge bg-info">{{ $data['harvest_count'] }} kali</span></td>
                                        <td>{{ $data['latest_harvest_date'] ? \Carbon\Carbon::parse($data['latest_harvest_date'])->format('d M Y') : '-' }}</td>
                                    </tr>
                                    @endforeach
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada data produksi</td>
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
        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-table me-2"></i>Tabel Data Stok Benih</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Nama Tanaman</th>
                                    <th>Varietas</th>
                                    <th>Stok Saat Ini</th>
                                    <th>Minimal Stok</th>
                                    <th>Harga Jual</th>
                                    <th>Total Stok Penyesuaian (satuan/produk)</th>
                                    <th>Total Stok Terjual (satuan/produk)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stockGroups as $category => $rows)
                                    <tr class="table-secondary">
                                        <th colspan="7">{{ $category }}</th>
                                    </tr>
                                    @foreach($rows as $data)
                                    <tr class="{{ !empty($data['is_low_stock']) ? 'table-warning' : '' }}">
                                        <td><strong>{{ $data['plant_name'] }}</strong></td>
                                        <td>{{ $data['variety'] ?: '-' }}</td>
                                        <td>
                                            {{ number_format($data['current_stock'], 2) }} {{ $data['unit'] }}
                                            @if(!empty($data['is_low_stock']))
                                                <span class="badge bg-warning text-dark">Stok rendah</span>
                                            @endif
                                        </td>
                                        <td>{{ $data['minimal_stok'] !== null ? number_format($data['minimal_stok'], 2).' '.$data['unit'] : '-' }}</td>
                                        <td>Rp {{ number_format($data['harga_jual'] ?? 0, 0, ',', '.') }} / {{ $data['unit'] }}</td>
                                        <td>{{ $fmtSatuanProduk($data['adjusted_quantity'] ?? 0, $data['unit'] ?? '', $data['adjusted_products'] ?? 0) }}</td>
                                        <td>{{ $fmtSatuanProduk($data['sold_quantity'] ?? 0, $data['unit'] ?? '', $data['sold_products'] ?? 0) }}</td>
                                    </tr>
                                    @endforeach
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada data stok benih</td>
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
        <!-- Tabel Pendapatan -->
        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-table me-2"></i>Tabel Pendapatan per Tanaman</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Nama Tanaman</th>
                                    <th>Varietas</th>
                                    <th>Total Pendapatan</th>
                                    <th>Total Terjual (satuan/produk)</th>
                                    <th>Harga Rata-rata</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($revenueGroups as $category => $rows)
                                    <tr class="table-secondary">
                                        <th colspan="5">{{ $category }}</th>
                                    </tr>
                                    @foreach($rows as $data)
                                    <tr>
                                        <td><strong>{{ $data['plant_name'] }}</strong></td>
                                        <td>{{ $data['variety'] ?: '-' }}</td>
                                        <td><strong class="text-success">Rp {{ number_format($data['total_revenue'], 0, ',', '.') }}</strong></td>
                                        <td>{{ $fmtSatuanProduk($data['total_quantity'] ?? 0, $data['unit'] ?? '', $data['sold_products'] ?? 0) }}</td>
                                        <td>Rp {{ number_format($data['average_price'], 0, ',', '.') }}/{{ $data['unit'] }}</td>
                                    </tr>
                                    @endforeach
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

    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Grafik Pendapatan</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueTrendChart" height="90"></canvas>
                    <p class="text-muted mt-2 small mb-0">Total penjualan 12 bulan terakhir (dalam Rupiah)</p>
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
                                <h1 class="display-4 text-primary me-3">{{ round($weatherData['main']['temp'] ?? 0) }}°C</h1>
                                <i class="fas fa-cloud text-muted fa-2x"></i>
                            </div>
                            <p class="text-muted mb-2">{{ $weatherData['weather'][0]['description'] ?? '-' }} - H {{ round($weatherData['main']['temp_max'] ?? $weatherData['main']['temp'] ?? 0) }}°C L {{ round($weatherData['main']['temp_min'] ?? $weatherData['main']['temp'] ?? 0) }}°C</p>
                            
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Sunset: 6:23PM</small><br>
                                    <small class="text-muted">Wind: {{ $weatherData['wind']['speed'] ?? 0 }} mps <i class="fas fa-arrow-up"></i></small><br>
                                    <small class="text-muted">Humidity: {{ $weatherData['main']['humidity'] ?? 0 }}%</small>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Feels like {{ round($weatherData['main']['feels_like'] ?? $weatherData['main']['temp'] ?? 0) }}°C</small><br>
                                    <small class="text-muted">Sky Cover: {{ $weatherData['clouds']['all'] ?? 0 }}%</small><br>
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
    const revenueCtx = document.getElementById('revenueTrendChart');
    if (revenueCtx && typeof Chart !== 'undefined') {
        new Chart(revenueCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($revenueTrend['labels'] ?? []),
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: @json($revenueTrend['data'] ?? []),
                    backgroundColor: 'rgba(23, 162, 184, 0.8)',
                    borderColor: 'rgba(23, 162, 184, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: true, position: 'top' },
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
                        title: { display: true, text: 'Rupiah' }
                    }
                }
            }
        });
    }
});
</script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const points = @json($geoPoints ?? []);
    const donutColors = ['#059669','#f59e0b','#3b82f6','#ef4444','#8b5cf6','#14b8a6','#f97316','#64748b'];

    function bindCategoryDonut(canvasId, categories, varieties) {
        const canvas = document.getElementById(canvasId);
        if (!canvas || typeof Chart === 'undefined') return;
        const chart = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: (categories || []).map(x => x.label),
                datasets: [{ data: (categories || []).map(x => x.value), backgroundColor: donutColors }]
            },
            options: {
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
        canvas.onclick = function (evt) {
            const els = chart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
            if (!els.length) return;
            const label = chart.data.labels[els[0].index];
            const rows = (varieties || []).filter(v => v.category === label);
            if (!rows.length) return;
            chart.data.labels = rows.map(r => r.label);
            chart.data.datasets[0].data = rows.map(r => r.value);
            chart.update();
        };
    }

    bindCategoryDonut('chartStockDonut', @json($stockByCategory ?? []), @json($stockByVariety ?? []));
    bindCategoryDonut('chartProdDonut', @json($prodByCategory ?? []), @json($prodByVariety ?? []));
    bindCategoryDonut('chartSaleDonut', @json($saleByCategory ?? []), @json($saleByVariety ?? []));
    if (document.getElementById('geo-map') && typeof L !== 'undefined') {
        const map = L.map('geo-map').setView([-0.95, 100.35], 8);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(map);
        points.forEach(p => {
            const r = Math.max(6, Math.min(25, (p.qty || 1) / 20));
            L.circleMarker([p.lat, p.lng], { radius: r, color: '#dc2626', fillOpacity: 0.45 })
                .bindPopup((p.label || 'Sebaran') + ' — ' + (p.qty || 0))
                .addTo(map);
        });
    }
});
</script>
@endsection
