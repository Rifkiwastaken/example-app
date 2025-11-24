@extends('layouts.app')

@section('title', 'Target Produksi - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Target Produksi</h4>
        <small class="text-muted">Monitoring Target vs Realisasi Fisik</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('planning.production-target.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>Buat Target Produksi
        </a>
        <a href="{{ route('planning.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('planning.production-target.index') }}" class="row align-items-end">
            <div class="col-md-3 mb-3">
                <label class="form-label">Tahun</label>
                <select name="year" class="form-select" onchange="this.form.submit()">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Komoditas</label>
                <select name="commodity" class="form-select">
                    <option value="">Semua Komoditas</option>
                    @foreach($commodities as $comm)
                        <option value="{{ $comm }}" {{ request('commodity') == $comm ? 'selected' : '' }}>{{ $comm }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Lokasi</label>
                <select name="location_id" class="form-select">
                    <option value="">Semua Lokasi</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
                <a href="{{ route('planning.production-target.index', ['year' => request('year', date('Y'))]) }}" class="btn btn-secondary">
                    <i class="fas fa-redo me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Production Targets Table -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-table me-2"></i>Tabel Target Produksi
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Uraian / Varietas</th>
                        <th>Kelas Benih</th>
                        <th>Lokasi</th>
                        <th class="text-end">Target Luas (Ha)</th>
                        <th class="text-end">Realisasi Tanam (Ha)</th>
                        <th class="text-end">Target Produksi (Ton)</th>
                        <th class="text-end">Realisasi Produksi (Ton)</th>
                        <th class="text-center">Capaian (%)</th>
                        <th width="120" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($targets as $target)
                        <tr>
                            <td>
                                <strong>{{ $target->variety_name }}</strong><br>
                                <small class="text-muted">{{ $target->commodity }}</small>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $target->seed_class }}</span>
                            </td>
                            <td>{{ $target->plantingLocation->name ?? '-' }}</td>
                            <td class="text-end">{{ number_format($target->target_planting_area, 2) }}</td>
                            <td class="text-end">{{ number_format($target->realized_planting_area, 2) }}</td>
                            <td class="text-end">{{ number_format($target->target_production_volume, 2) }}</td>
                            <td class="text-end">{{ number_format($target->realized_production_volume, 2) }}</td>
                            <td class="text-center">
                                @php
                                    $achievement = $target->achievement_percentage;
                                    $color = $achievement >= 100 ? 'success' : ($achievement >= 80 ? 'warning' : ($achievement >= 50 ? 'info' : 'danger'));
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ number_format($achievement, 1) }}%</span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('planning.production-target.edit', $target) }}" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('planning.production-target.destroy', $target) }}" method="POST" class="d-inline" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus target produksi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                Belum ada target produksi. <a href="{{ route('planning.production-target.create') }}">Buat target pertama</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($targets->count() > 0)
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="3" class="text-end">TOTAL:</td>
                        <td class="text-end">{{ number_format($targets->sum('target_planting_area'), 2) }}</td>
                        <td class="text-end">{{ number_format($targets->sum('realized_planting_area'), 2) }}</td>
                        <td class="text-end">{{ number_format($targets->sum('target_production_volume'), 2) }}</td>
                        <td class="text-end">{{ number_format($targets->sum('realized_production_volume'), 2) }}</td>
                        <td class="text-center">
                            @php
                                $totalTarget = $targets->sum('target_production_volume');
                                $totalRealized = $targets->sum('realized_production_volume');
                                $totalAchievement = $totalTarget > 0 ? round(($totalRealized / $totalTarget) * 100, 1) : 0;
                                $totalColor = $totalAchievement >= 100 ? 'success' : ($totalAchievement >= 80 ? 'warning' : ($totalAchievement >= 50 ? 'info' : 'danger'));
                            @endphp
                            <span class="badge bg-{{ $totalColor }}">{{ number_format($totalAchievement, 1) }}%</span>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection


