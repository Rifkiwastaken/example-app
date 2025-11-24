@extends('layouts.app')

@section('title', 'Rencana Anggaran (DPA) - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Rencana Anggaran (DPA)</h4>
        <small class="text-muted">Memonitor Pagu (Batas Atas) vs Realisasi penyerapan dana</small>
    </div>
    <a href="{{ route('planning.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<!-- Header Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('planning.budget.index') }}" class="row align-items-end">
            <div class="col-md-3 mb-3">
                <label class="form-label">Tahun Anggaran</label>
                <select name="year" class="form-select" onchange="this.form.submit()">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $budget->fiscal_year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-9 mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Total Pagu</label>
                        <div class="h5 mb-0 text-primary">
                            Rp {{ number_format($budget->total_pagu, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Realisasi</label>
                        <div class="h5 mb-0 text-info">
                            Rp {{ number_format($budget->total_realisasi, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sisa Pagu</label>
                        <div class="h5 mb-0 {{ $budget->sisa_pagu >= 0 ? 'text-success' : 'text-danger' }}">
                            Rp {{ number_format($budget->sisa_pagu, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Action Buttons -->
<div class="mb-3">
    <a href="{{ route('planning.budget.item.create', ['budget_id' => $budget->id]) }}" class="btn btn-success">
        <i class="fas fa-plus me-2"></i>Tambah Mata Anggaran Baru
    </a>
</div>

<!-- Budget Items Table -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-table me-2"></i>Tabel Rencana Anggaran
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-light">
                    <tr>
                        <th width="150">Kode Rekening</th>
                        <th>Uraian / Akun Belanja</th>
                        <th width="150" class="text-end">Pagu Anggaran (Rp)</th>
                        <th width="150" class="text-end">Realisasi (Rp)</th>
                        <th width="80" class="text-center">%</th>
                        <th width="150" class="text-end">Sisa Pagu (Rp)</th>
                        <th width="120" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($treeItems as $item)
                        @include('planning.budget.partials.tree-item', ['item' => $item, 'level' => 0])
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                Belum ada mata anggaran. <a href="{{ route('planning.budget.item.create', ['budget_id' => $budget->id]) }}">Tambah mata anggaran pertama</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($treeItems->count() > 0)
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="2" class="text-end">TOTAL:</td>
                        <td class="text-end">Rp {{ number_format($budget->total_pagu, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($budget->total_realisasi, 0, ',', '.') }}</td>
                        <td class="text-center">
                            @php
                                $totalPercentage = $budget->total_pagu > 0 
                                    ? round(($budget->total_realisasi / $budget->total_pagu) * 100, 1) 
                                    : 0;
                                $totalColor = $totalPercentage >= 100 ? 'danger' : ($totalPercentage >= 80 ? 'warning' : 'success');
                            @endphp
                            <span class="badge bg-{{ $totalColor }}">{{ number_format($totalPercentage, 1) }}%</span>
                        </td>
                        <td class="text-end {{ $budget->sisa_pagu < 0 ? 'text-danger' : '' }}">
                            Rp {{ number_format($budget->sisa_pagu, 0, ',', '.') }}
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

