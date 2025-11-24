@extends('layouts.app')

@section('title', 'Tambah Mata Anggaran - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Tambah Mata Anggaran Baru</h4>
        <small class="text-muted">Tahun Anggaran: {{ $budget->fiscal_year }}</small>
    </div>
    <a href="{{ route('planning.budget.index', ['year' => $budget->fiscal_year]) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('planning.budget.item.store') }}" method="POST">
            @csrf
            <input type="hidden" name="budget_id" value="{{ $budget->id }}">
            @if($parentItem)
                <input type="hidden" name="parent_id" value="{{ $parentItem->id }}">
                <div class="alert alert-info mb-4">
                    <strong>Induk:</strong> {{ $parentItem->account_code }} - {{ $parentItem->description }}
                </div>
            @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tahun Anggaran <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" value="{{ $budget->fiscal_year }}" disabled>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sumber Dana</label>
                    <select name="fund_source" class="form-select">
                        <option value="">-- Pilih Sumber Dana --</option>
                        @foreach($fundSources as $source)
                            <option value="{{ $source }}" {{ old('fund_source') == $source ? 'selected' : '' }}>
                                {{ $source }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if(!$parentItem)
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Induk Rekening</label>
                    <select name="parent_account_code" id="parent_account_code" class="form-select">
                        <option value="">-- Pilih Induk Rekening --</option>
                        @foreach($parentAccounts as $code => $name)
                            <option value="{{ $code }}" {{ old('parent_account_code') == $code || $budget->account_code == $code ? 'selected' : '' }}>
                                {{ $code }} - {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="parent_account_name" id="parent_account_name">
                </div>
            </div>
            @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kode Rekening (Sub) <span class="text-danger">*</span></label>
                    <input type="text" name="account_code" class="form-control @error('account_code') is-invalid @enderror" 
                           value="{{ old('account_code') }}" placeholder="Contoh: 01.01.0012" required>
                    @error('account_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Urutan</label>
                    <input type="number" name="order" class="form-control" 
                           value="{{ old('order', $maxOrder + 1) }}" min="1">
                    <small class="text-muted">Urutan tampil dalam tabel</small>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Uraian Kegiatan <span class="text-danger">*</span></label>
                <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" 
                       value="{{ old('description') }}" placeholder="Contoh: Belanja Bahan Pupuk" required>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Pagu Anggaran (Rp) <span class="text-danger">*</span></label>
                <input type="number" name="budgeted_amount" class="form-control @error('budgeted_amount') is-invalid @enderror" 
                       value="{{ old('budgeted_amount') }}" step="0.01" min="0" required>
                @error('budgeted_amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Masukkan angka tanpa titik atau koma</small>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('planning.budget.index', ['year' => $budget->fiscal_year]) }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const parentAccountSelect = document.getElementById('parent_account_code');
    const parentAccountName = document.getElementById('parent_account_name');
    
    if (parentAccountSelect && parentAccountName) {
        parentAccountSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const text = selectedOption.textContent;
                const name = text.split(' - ')[1] || '';
                parentAccountName.value = name;
            } else {
                parentAccountName.value = '';
            }
        });
        
        // Initialize on load
        if (parentAccountSelect.value) {
            parentAccountSelect.dispatchEvent(new Event('change'));
        }
    }
});
</script>
@endpush
@endsection


