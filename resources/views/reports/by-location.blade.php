@extends('layouts.app')

@section('title', 'Laporan Per Lokasi Lahan - ' . $plantingLocation->name . ' - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Laporan Per Lokasi Lahan</h4>
        <small class="text-muted">{{ $plantingLocation->name }}</small>
    </div>
    <a href="{{ route('reports.by-location') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i class="fas fa-filter me-2"></i>Filter Data
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reports.by-location') }}" id="filterForm">
            <input type="hidden" name="planting_location_id" value="{{ $plantingLocation->planting_location_id }}">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-select">
                        <option value="">Semua Tahun</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Komoditas</label>
                    <select name="plant_id" class="form-select">
                        <option value="">Semua Komoditas</option>
                        @foreach($plants as $plant)
                            <option value="{{ $plant->plant_id }}" {{ request('plant_id') == $plant->plant_id ? 'selected' : '' }}>
                                {{ $plant->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Data Penanaman</label>
                    <select name="planting_id" class="form-select">
                        <option value="">Semua Penanaman</option>
                        @foreach($allPlantingsForLocation as $planting)
                            <option value="{{ $planting->planting_id }}" {{ request('planting_id') == $planting->planting_id ? 'selected' : '' }}>
                                {{ $planting->plant->name ?? 'Tanaman' }} 
                                @if($planting->plant->variety) - {{ $planting->plant->variety }} @endif
                                @if($planting->planted_at) ({{ $planting->planted_at->format('d M Y') }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('reports.by-location', ['planting_location_id' => $plantingLocation->planting_location_id]) }}" class="btn btn-secondary">
                        <i class="fas fa-redo me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-primary">{{ $totalPlantings }}</h5>
                <p class="card-text text-muted mb-0">Total Penanaman</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-success">{{ $totalHarvests }}</h5>
                <p class="card-text text-muted mb-0">Total Panen</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-danger">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</h5>
                <p class="card-text text-muted mb-0">Total Pengeluaran</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-info">{{ $totalTasks }}</h5>
                <p class="card-text text-muted mb-0">Total Tugas ({{ $completedTasks }} selesai)</p>
            </div>
        </div>
    </div>
</div>

<!-- Export Buttons -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" onclick="exportPDF()">
                <i class="fas fa-file-pdf me-2"></i>Download PDF
            </button>
            <button type="button" class="btn btn-success" onclick="exportExcel()">
                <i class="fas fa-file-excel me-2"></i>Export Excel
            </button>
        </div>
    </div>
</div>

<!-- Combined Report Table -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-table me-2"></i>Laporan Gabungan - {{ $plantingLocation->name }}
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered" id="combinedReportTable">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Jenis Data</th>
                        <th>Tanggal</th>
                        <th>Judul/Nama</th>
                        <th>Deskripsi/Detail</th>
                        <th>Penanggung Jawab</th>
                        <th>Status</th>
                        <th>Biaya/Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $allData = collect();
                    @endphp
                    
                    @foreach($plantings as $planting)
                        @php
                            $harvest = $planting->harvest;
                            
                            // Panen (harvest dengan quantity > 0)
                            if ($harvest && $harvest->quantity > 0) {
                                $allData->push([
                                    'type' => 'Panen',
                                    'date' => $harvest->harvested_at ?? $planting->planted_at,
                                    'title' => $planting->plant->name ?? '-',
                                    'description' => 'Varietas: ' . ($planting->plant->variety ?? '-') . ($planting->bed_label ? ' | Bed: ' . $planting->bed_label : '') . ' | Jumlah: ' . number_format($harvest->quantity, 2) . ' ' . ($harvest->unit ?? 'kg'),
                                    'responsible' => '-',
                                    'status' => 'Berhasil',
                                    'amount' => null,
                                ]);
                            }
                            
                            // Gagal Panen (harvest dengan quantity = 0 atau null)
                            if ($harvest && ($harvest->quantity == 0 || $harvest->quantity === null)) {
                                $allData->push([
                                    'type' => 'Gagal Panen',
                                    'date' => $harvest->harvested_at ?? $planting->planted_at,
                                    'title' => $planting->plant->name ?? '-',
                                    'description' => 'Varietas: ' . ($planting->plant->variety ?? '-') . ($planting->bed_label ? ' | Bed: ' . $planting->bed_label : '') . ' | Alasan: Panen gagal',
                                    'responsible' => '-',
                                    'status' => 'Gagal',
                                    'amount' => null,
                                ]);
                            }
                            
                            // Kehilangan (ada losses)
                            foreach ($planting->losses as $loss) {
                                $allData->push([
                                    'type' => 'Kehilangan',
                                    'date' => $loss->loss_date ?? $planting->planted_at,
                                    'title' => $planting->plant->name ?? '-',
                                    'description' => 'Varietas: ' . ($planting->plant->variety ?? '-') . ($planting->bed_label ? ' | Bed: ' . $planting->bed_label : '') . ' | Jumlah: ' . number_format($loss->loss_amount, 2) . ' | Alasan: ' . ($loss->loss_reason ?? '-'),
                                    'responsible' => '-',
                                    'status' => 'Kehilangan',
                                    'amount' => null,
                                ]);
                            }
                        @endphp
                    @endforeach
                    
                    @foreach($treatments as $treatment)
                        @php
                            $allData->push([
                                'type' => 'Perawatan',
                                'date' => $treatment->treatment_date,
                                'title' => $treatment->treatment_name ?? '-',
                                'description' => 'Tipe: ' . ($treatment->treatment_type ?? '-') . ' | Metode: ' . ($treatment->application_method ?? '-'),
                                'responsible' => $treatment->responsiblePerson->name ?? '-',
                                'status' => '-',
                                'amount' => $treatment->total_cost ?? 0,
                            ]);
                        @endphp
                    @endforeach
                    
                    @foreach($nutrients as $nutrient)
                        @php
                            $allData->push([
                                'type' => 'Nutrisi',
                                'date' => $nutrient->application_date,
                                'title' => $nutrient->product_applied ?? '-',
                                'description' => 'Metode: ' . ($nutrient->application_method ?? '-') . ' | Jumlah: ' . ($nutrient->amount_applied ?? '-') . ' ' . ($nutrient->unit ?? ''),
                                'responsible' => $nutrient->responsiblePerson->name ?? '-',
                                'status' => '-',
                                'amount' => $nutrient->total_cost ?? 0,
                            ]);
                        @endphp
                    @endforeach
                    
                    @foreach($tasks as $task)
                        @php
                            $allData->push([
                                'type' => 'Tugas',
                                'date' => $task->due_date,
                                'title' => $task->title ?? '-',
                                'description' => Str::limit($task->description ?? '-', 100),
                                'responsible' => $task->assignedUser->name ?? '-',
                                'status' => $task->new_status === 'selesai' ? 'Selesai' : ($task->new_status === 'dalam_progress' ? 'Dalam Progress' : 'Belum Selesai'),
                                'amount' => null,
                            ]);
                        @endphp
                    @endforeach
                    
                    
                    @php
                        $allData = $allData->sortByDesc('date')->values();
                        $rowNumber = 1;
                    @endphp
                    
                    @forelse($allData as $item)
                        <tr>
                            <td>{{ $rowNumber++ }}</td>
                            <td>
                                @php
                                    $badgeClass = match($item['type']) {
                                        'Tugas' => 'primary',
                                        'Perawatan' => 'warning',
                                        'Nutrisi' => 'info',
                                        'Panen' => 'success',
                                        'Kehilangan' => 'danger',
                                        'Gagal Panen' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $badgeClass }}">
                                    {{ $item['type'] }}
                                </span>
                            </td>
                            <td>{{ $item['date'] ? \Carbon\Carbon::parse($item['date'])->format('d-m-Y') : '-' }}</td>
                            <td>{{ $item['title'] }}</td>
                            <td>{{ $item['description'] }}</td>
                            <td>{{ $item['responsible'] }}</td>
                            <td>
                                @if($item['status'] !== '-')
                                    @if($item['status'] === 'Berhasil' || $item['status'] === 'Selesai')
                                        <span class="badge bg-success">{{ $item['status'] }}</span>
                                    @elseif($item['status'] === 'Gagal' || $item['status'] === 'Belum Selesai')
                                        <span class="badge bg-danger">{{ $item['status'] }}</span>
                                    @else
                                        <span class="badge bg-warning">{{ $item['status'] }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($item['amount'] !== null && $item['amount'] > 0)
                                    <strong>Rp {{ number_format($item['amount'], 0, ',', '.') }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                Tidak ada data ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($allData->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <th colspan="7" class="text-end">Total Pengeluaran:</th>
                        <th class="text-end">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

<!-- Old Tabs (Hidden, kept for reference) -->
<div class="d-none">
    <!-- Penanaman Tab -->
    <div class="tab-pane fade show active" id="planting" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-seedling me-2"></i>Data Penanaman</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Varietas</th>
                                <th>Tanggal Tanam</th>
                                <th>Jumlah Tanam</th>
                                <th>Estimasi Panen</th>
                                <th>Tanggal Panen</th>
                                <th>Hasil Panen</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plantings as $index => $planting)
                                @php
                                    $harvest = $planting->harvest;
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $planting->plant->name }}</strong><br>
                                        <small class="text-muted">{{ $planting->plant->variety ?: '-' }}</small>
                                    </td>
                                    <td>{{ $planting->planted_at ? $planting->planted_at->format('d M Y') : '-' }}</td>
                                    <td>{{ $planting->quantity_planted ?? '-' }}</td>
                                    <td>{{ $planting->estimated_harvest_date ? $planting->estimated_harvest_date->format('d M Y') : '-' }}</td>
                                    <td>{{ $harvest && $harvest->harvested_at ? $harvest->harvested_at->format('d M Y') : '-' }}</td>
                                    <td>
                                        @if($harvest && $harvest->quantity > 0)
                                            {{ number_format($harvest->quantity, 2) }} {{ $harvest->unit ?? 'kg' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($harvest && $harvest->quantity > 0)
                                            <span class="badge bg-success">Berhasil</span>
                                        @elseif($harvest)
                                            <span class="badge bg-danger">Gagal</span>
                                        @else
                                            <span class="badge bg-secondary">Belum Panen</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                        Tidak ada data penanaman ditemukan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Perawatan Tab -->
    <div class="tab-pane fade" id="treatment" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-spray-can me-2"></i>Data Perawatan</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Perawatan</th>
                                <th>Tipe</th>
                                <th>Tanggal</th>
                                <th>Metode</th>
                                <th>Jumlah</th>
                                <th>Penanggung Jawab</th>
                                <th>Total Biaya</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($treatments as $index => $treatment)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $treatment->treatment_name ?? '-' }}</td>
                                    <td>{{ $treatment->treatment_type ?? '-' }}</td>
                                    <td>{{ $treatment->treatment_date ? $treatment->treatment_date->format('d M Y') : '-' }}</td>
                                    <td>{{ $treatment->application_method ?? '-' }}</td>
                                    <td>{{ $treatment->amount_applied ?? '-' }} {{ $treatment->unit_measurement ?? '' }}</td>
                                    <td>{{ $treatment->responsiblePerson->name ?? '-' }}</td>
                                    <td>Rp {{ number_format($treatment->total_cost ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                        Tidak ada data perawatan ditemukan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Nutrisi Tab -->
    <div class="tab-pane fade" id="nutrient" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-flask me-2"></i>Data Nutrisi</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Nutrisi</th>
                                <th>Tanggal</th>
                                <th>Metode</th>
                                <th>Jumlah</th>
                                <th>Unit</th>
                                <th>Total Biaya</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($nutrients as $index => $nutrient)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $nutrient->nutrient_name ?? '-' }}</td>
                                    <td>{{ $nutrient->application_date ? $nutrient->application_date->format('d M Y') : '-' }}</td>
                                    <td>{{ $nutrient->application_method ?? '-' }}</td>
                                    <td>{{ $nutrient->amount_applied ?? '-' }}</td>
                                    <td>{{ $nutrient->unit ?? '-' }}</td>
                                    <td>Rp {{ number_format($nutrient->total_cost ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                        Tidak ada data nutrisi ditemukan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Pengeluaran Tab -->
    <div class="tab-pane fade" id="expense" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Data Pengeluaran</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Pengeluaran</th>
                                <th>Jenis</th>
                                <th>Tanggal</th>
                                <th>Total Biaya</th>
                                <th>Penanggung Jawab</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $index => $expense)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $expense->expense_name ?? '-' }}</td>
                                    <td>
                                        @if($expense->expense_type === 'perawatan')
                                            <span class="badge bg-primary">Perawatan</span>
                                        @elseif($expense->expense_type === 'nutrisi')
                                            <span class="badge bg-info">Nutrisi</span>
                                        @elseif($expense->expense_type === 'upah_pekerja')
                                            <span class="badge bg-warning">Upah Pekerja</span>
                                        @else
                                            <span class="badge bg-secondary">Lainnya</span>
                                        @endif
                                    </td>
                                    <td>{{ $expense->expense_date ? $expense->expense_date->format('d M Y') : '-' }}</td>
                                    <td>Rp {{ number_format($expense->amount ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ $expense->responsiblePerson->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                        Tidak ada data pengeluaran ditemukan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Total Pengeluaran:</th>
                                <th colspan="2">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tugas Tab -->
    <div class="tab-pane fade" id="task" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-tasks me-2"></i>Data Tugas</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Tugas</th>
                                <th>Tanggal Jatuh Tempo</th>
                                <th>Ditugaskan ke</th>
                                <th>Status</th>
                                <th>Prioritas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tasks as $index => $task)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $task->title ?? '-' }}</td>
                                    <td>{{ $task->due_date ? $task->due_date->format('d M Y') : '-' }}</td>
                                    <td>{{ $task->assignedUser->name ?? '-' }}</td>
                                    <td>
                                        @if($task->new_status === 'selesai')
                                            <span class="badge bg-success">Selesai</span>
                                        @elseif($task->new_status === 'dalam_proses')
                                            <span class="badge bg-warning">Dalam Proses</span>
                                        @else
                                            <span class="badge bg-secondary">Belum Dimulai</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->new_priority === 'tinggi')
                                            <span class="badge bg-danger">Tinggi</span>
                                        @elseif($task->new_priority === 'sedang')
                                            <span class="badge bg-warning">Sedang</span>
                                        @else
                                            <span class="badge bg-info">Rendah</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                        Tidak ada data tugas ditemukan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Catatan Tab -->
    <div class="tab-pane fade" id="note" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Data Catatan</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Judul</th>
                                <th>Tanggal</th>
                                <th>Pembuat</th>
                                <th>Isi Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notes as $index => $note)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $note->title ?? '-' }}</td>
                                    <td>{{ $note->note_date ? $note->note_date->format('d M Y') : '-' }}</td>
                                    <td>{{ $note->user->name ?? '-' }}</td>
                                    <td>{{ Str::limit($note->description ?? '-', 100) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                        Tidak ada data catatan ditemukan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Lampiran Tab -->
    <div class="tab-pane fade" id="attachment" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-paperclip me-2"></i>Data Lampiran</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Judul Lampiran</th>
                                <th>Tanggal</th>
                                <th>Pembuat</th>
                                <th>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attachments as $index => $attachment)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $attachment->title ?? '-' }}</td>
                                    <td>{{ $attachment->attachment_date ? $attachment->attachment_date->format('d M Y') : '-' }}</td>
                                    <td>{{ $attachment->creator->name ?? '-' }}</td>
                                    <td>{{ Str::limit($attachment->description ?? '-', 100) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                        Tidak ada data lampiran ditemukan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End of Hidden Tabs -->

@push('scripts')
<script>
function exportPDF() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    window.location.href = '{{ route("reports.by-location") }}?export=pdf&' + params.toString();
}

function exportExcel() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    window.location.href = '{{ route("reports.by-location") }}?export=excel&' + params.toString();
}
</script>
@endpush
@endsection

