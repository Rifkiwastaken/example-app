@extends('layouts.app')

@section('title', 'Laporan Realisasi Tanam & Panen - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Laporan Realisasi Tanam & Panen</h4>
        <small class="text-muted">Membandingkan rencana (target) dengan realisasi lapangan</small>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">
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
        <form method="GET" action="{{ route('reports.planting-harvest') }}" id="filterForm">
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
                            <option value="{{ $plant->id }}" {{ request('plant_id') == $plant->id ? 'selected' : '' }}>
                                {{ $plant->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Lokasi Lahan</label>
                    <select name="planting_location_id" class="form-select">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->planting_location_id }}" {{ request('planting_location_id') == $loc->planting_location_id ? 'selected' : '' }}>
                                {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('reports.planting-harvest') }}" class="btn btn-secondary">
                        <i class="fas fa-redo me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
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

<!-- Report Preview -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-table me-2"></i>Preview Data
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered" id="plantingHarvestTable">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2">No</th>
                        <th rowspan="2">KOMODITI</th>
                        <th rowspan="2">KELAS BENIH</th>
                        <th rowspan="2">VARIETAS</th>
                        <th rowspan="2">LUAS (ha)</th>
                        <th rowspan="2">LOKASI KEGIATAN</th>
                        <th colspan="2">WAKTU</th>
                        <th colspan="2">PRODUKSI (kg)</th>
                    </tr>
                    <tr>
                        <th>TANAM</th>
                        <th>PANEN</th>
                        <th>CALON BENIH (kg)</th>
                        <th>BENIH BERSERTIFIKAT</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $currentCommodity = null;
                        $currentSeedClass = null;
                        $rowNumber = 1;
                        $commodityTotals = [];
                        $groupedData = [];
                    @endphp
                    
                    @forelse($plantings as $index => $planting)
                        @php
                            $commodity = $planting->plant->type->name ?? 'Lainnya';
                            $seedClass = $planting->seed_class ?? '-';
                            
                            // Initialize totals for commodity if not exists
                            if (!isset($commodityTotals[$commodity])) {
                                $commodityTotals[$commodity] = [
                                    'area' => 0,
                                    'candidate_seed' => 0,
                                    'certified_seed' => 0
                                ];
                            }
                            
                            // Add to totals
                            $commodityTotals[$commodity]['area'] += $planting->area_ha ?? 0;
                            $commodityTotals[$commodity]['candidate_seed'] += $planting->candidate_seed_kg ?? 0;
                            $commodityTotals[$commodity]['certified_seed'] += $planting->certified_seed_kg ?? 0;
                            
                            // Check if we need to show commodity header
                            $showCommodityHeader = $currentCommodity !== $commodity;
                            $showSeedClassHeader = $currentSeedClass !== $seedClass || $showCommodityHeader;
                            
                            // Show total row before new commodity
                            if ($showCommodityHeader && $currentCommodity !== null && isset($commodityTotals[$currentCommodity])) {
                                $groupedData[] = [
                                    'type' => 'total',
                                    'commodity' => $currentCommodity,
                                    'totals' => $commodityTotals[$currentCommodity]
                                ];
                            }
                            
                            $currentCommodity = $commodity;
                            $currentSeedClass = $seedClass;
                            
                            $groupedData[] = [
                                'type' => 'data',
                                'planting' => $planting,
                                'commodity' => $commodity,
                                'seedClass' => $seedClass,
                                'showCommodityHeader' => $showCommodityHeader,
                                'showSeedClassHeader' => $showSeedClassHeader,
                                'rowNumber' => $rowNumber++
                            ];
                        @endphp
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                Tidak ada data ditemukan
                            </td>
                        </tr>
                    @endforelse
                    
                    @if($plantings->count() > 0 && $currentCommodity !== null && isset($commodityTotals[$currentCommodity]))
                        @php
                            $groupedData[] = [
                                'type' => 'total',
                                'commodity' => $currentCommodity,
                                'totals' => $commodityTotals[$currentCommodity]
                            ];
                        @endphp
                    @endif
                    
                    @foreach($groupedData as $item)
                        @if($item['type'] === 'total')
                            <tr class="table-info">
                                <td colspan="4"><strong>Total {{ $item['commodity'] }}</strong></td>
                                <td class="text-end"><strong>{{ number_format($item['totals']['area'], 2) }}</strong></td>
                                <td colspan="2"></td>
                                <td class="text-end"><strong>{{ number_format($item['totals']['candidate_seed'], 0, ',', '.') }}</strong></td>
                                <td class="text-end"><strong>{{ number_format($item['totals']['certified_seed'], 0, ',', '.') }}</strong></td>
                            </tr>
                        @else
                            <tr>
                                <td>{{ $item['rowNumber'] }}</td>
                                <td>
                                    @if($item['showCommodityHeader'])
                                        <strong>{{ $item['commodity'] }}</strong>
                                    @endif
                                </td>
                                <td>
                                    @if($item['showSeedClassHeader'])
                                        {{ $item['seedClass'] }}
                                    @endif
                                </td>
                                <td>{{ $item['planting']->plant->name ?? '-' }}</td>
                                <td class="text-end">{{ $item['planting']->area_ha > 0 ? number_format($item['planting']->area_ha, 2) : '-' }}</td>
                                <td>{{ $item['planting']->location->name ?? '-' }}</td>
                                <td>{{ $item['planting']->planted_at ? $item['planting']->planted_at->format('d-m-Y') : '-' }}</td>
                                <td>{{ $item['planting']->harvest && $item['planting']->harvest->harvested_at ? $item['planting']->harvest->harvested_at->format('d-m-Y') : '-' }}</td>
                                <td class="text-end">{{ $item['planting']->candidate_seed_kg > 0 ? number_format($item['planting']->candidate_seed_kg, 0, ',', '.') : '-' }}</td>
                                <td class="text-end">{{ $item['planting']->certified_seed_kg > 0 ? number_format($item['planting']->certified_seed_kg, 0, ',', '.') : '-' }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($plantings->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $plantings->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function exportPDF() {
    // Get current filter parameters
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    // Redirect to PDF export route
    window.location.href = '{{ route("reports.planting-harvest") }}?export=pdf&' + params.toString();
}

function exportExcel() {
    // Get current filter parameters
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    // Redirect to Excel export route
    window.location.href = '{{ route("reports.planting-harvest") }}?export=excel&' + params.toString();
}
</script>
@endpush
@endsection

