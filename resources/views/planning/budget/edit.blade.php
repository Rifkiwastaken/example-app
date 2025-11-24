@extends('layouts.app')

@section('title', 'Edit Mata Anggaran - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Edit Mata Anggaran</h4>
        <small class="text-muted">Tahun Anggaran: {{ $budget->fiscal_year }}</small>
    </div>
    <a href="{{ route('planning.budget.index', ['year' => $budget->fiscal_year]) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('planning.budget.item.update', $budgetItem) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tahun Anggaran</label>
                    <input type="text" class="form-control" value="{{ $budget->fiscal_year }}" disabled>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sumber Dana</label>
                    <select name="fund_source" class="form-select">
                        <option value="">-- Pilih Sumber Dana --</option>
                        @foreach($fundSources as $source)
                            <option value="{{ $source }}" {{ old('fund_source', $budgetItem->fund_source) == $source ? 'selected' : '' }}>
                                {{ $source }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kode Rekening (Sub) <span class="text-danger">*</span></label>
                    <input type="text" name="account_code" class="form-control @error('account_code') is-invalid @enderror" 
                           value="{{ old('account_code', $budgetItem->account_code) }}" required>
                    @error('account_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Uraian Kegiatan <span class="text-danger">*</span></label>
                <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" 
                       value="{{ old('description', $budgetItem->description) }}" required>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pagu Anggaran (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="budgeted_amount" class="form-control @error('budgeted_amount') is-invalid @enderror" 
                           value="{{ old('budgeted_amount', $budgetItem->budgeted_amount) }}" step="0.01" min="0" required>
                    @error('budgeted_amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Realisasi (Rp)</label>
                    <input type="number" name="realized_amount" class="form-control @error('realized_amount') is-invalid @enderror" 
                           value="{{ old('realized_amount', $budgetItem->realized_amount) }}" step="0.01" min="0">
                    @error('realized_amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Diisi dari input pengeluaran</small>
                </div>
            </div>

            <div class="alert alert-info">
                <strong>Info:</strong>
                <ul class="mb-0">
                    <li>Pagu: Rp {{ number_format($budgetItem->budgeted_amount, 0, ',', '.') }}</li>
                    <li>Realisasi: Rp {{ number_format($budgetItem->realized_amount, 0, ',', '.') }}</li>
                    <li>Sisa Pagu: Rp {{ number_format($budgetItem->sisa_pagu, 0, ',', '.') }}</li>
                    <li>Persentase: {{ number_format($budgetItem->percentage, 2) }}%</li>
                </ul>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('planning.budget.index', ['year' => $budget->fiscal_year]) }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


