@extends('layouts.app')

@section('title', 'Data Pengeluaran - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Data Pengeluaran</h4>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Filter Card -->
<div class="card mb-3">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filter</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('expenses.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tahun</label>
                <select name="year" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Tahun</option>
                    @foreach($availableYears as $availableYear)
                        <option value="{{ $availableYear }}" {{ $year == $availableYear ? 'selected' : '' }}>
                            {{ $availableYear }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Bulan</label>
                <select name="month" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Bulan</option>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ $month == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $i)->locale('id')->monthName }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanaman</label>
                <select name="plant_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Tanaman</option>
                    @foreach($plants as $plant)
                        <option value="{{ $plant->id }}" {{ $plantId == $plant->id ? 'selected' : '' }}>
                            {{ $plant->name }}@if($plant->variety) - {{ $plant->variety }}@endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Lokasi Penanaman</label>
                <select name="planting_location_id" id="planting_location_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Lokasi</option>
                    @foreach($plantingLocations as $location)
                        <option value="{{ $location->planting_location_id }}" {{ $plantingLocationId == $location->planting_location_id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Data Penanaman</label>
                <select name="planting_id" id="planting_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Penanaman</option>
                    @foreach($allPlantings as $planting)
                        <option value="{{ $planting->planting_id }}" {{ $plantingId == $planting->planting_id ? 'selected' : '' }}>
                            {{ $planting->plant?->name ?? 'Tanaman' }} 
                            @if($planting->plant?->variety) - {{ $planting->plant->variety }} @endif
                            @if($planting->bed_label) ({{ $planting->bed_label }}) @endif
                            @if($planting->planted_at) - {{ $planting->planted_at->format('d M Y') }} @endif
                            @if($planting->location) - {{ $planting->location?->name }} @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12">
                <a href="{{ route('expenses.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-redo me-1"></i>Reset Filter
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Total Pengeluaran Card -->
<div class="card mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h6 class="mb-2">Total Pengeluaran</h6>
                <h3 class="text-primary mb-0">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</h3>
                @if($year || $month || $plantId || $plantingLocationId || $plantingId)
                    <small class="text-muted">
                        @if($year && $month)
                            Periode: {{ \Carbon\Carbon::create($year, $month)->locale('id')->monthName }} {{ $year }}
                        @elseif($year)
                            Tahun: {{ $year }}
                        @else
                            Semua Periode
                        @endif
                        @if($plantId || $plantingLocationId || $plantingId)
                            | Filter aktif
                        @endif
                    </small>
                @else
                    <small class="text-muted">Semua Periode</small>
                @endif
            </div>
            <div class="col-md-4 text-end">
                <i class="fas fa-money-bill-wave fa-3x text-primary opacity-25"></i>
            </div>
        </div>
    </div>
</div>

<!-- Expenses Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Pengeluaran</th>
                        <th>Jumlah Pengeluaran</th>
                        <th>Tipe</th>
                        <th>Tanaman</th>
                        <th>Lokasi Penanaman</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('d M Y') }}</td>
                            <td><strong>{{ $expense->expense_name }}</strong></td>
                            <td><strong class="text-danger">Rp {{ number_format($expense->amount, 0, ',', '.') }}</strong></td>
                            <td>
                                <span class="badge bg-{{ $expense->expense_type === 'perawatan' ? 'info' : ($expense->expense_type === 'nutrisi' ? 'success' : ($expense->expense_type === 'upah_pekerja' ? 'dark' : 'secondary')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $expense->expense_type)) }}
                                </span>
                            </td>
                            <td>
                                @if($expense->plant)
                                    {{ $expense->plant->name }}@if($expense->plant->variety) - {{ $expense->plant->variety }}@endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                {{ $expense->plantingLocation?->name ?? '-' }}
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @if($expense->plantingLocation)
                                    <button type="button" class="btn btn-outline-info" onclick="viewExpense('{{ $expense->expense_id }}', '{{ $expense->plantingLocation->planting_location_id }}')" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if(auth()->user()->isAdmin() || (auth()->user()->isAssignedToPlantingLocation($expense->plantingLocation) && auth()->user()->canAddDataInPelaporan($expense->plantingLocation)))
                                        @if(in_array($expense->expense_type, ['upah_pekerja', 'lainnya']))
                                            <button type="button" class="btn btn-outline-warning" onclick="editExpense('{{ $expense->expense_id }}', '{{ $expense->plantingLocation->planting_location_id }}')" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('planting-locations.expenses.destroy', [$expense->plantingLocation, $expense]) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengeluaran ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Belum ada data pengeluaran.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($expenses->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: View Expense -->
<div class="modal fade" id="viewExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pengeluaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewExpenseBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Edit Expense -->
<div class="modal fade" id="editExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editExpenseForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pengeluaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="editExpenseBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const plantingLocationSelect = document.getElementById('planting_location_id');
    const plantingSelect = document.getElementById('planting_id');
    
    if (plantingLocationSelect && plantingSelect) {
        plantingLocationSelect.addEventListener('change', function() {
            // Reset planting filter when location changes
            if (plantingSelect) {
                plantingSelect.value = '';
            }
        });
    }
});

function viewExpense(expenseId, plantingLocationId) {
    fetch(`/planting-locations/${plantingLocationId}/expenses/${expenseId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.expense) {
            const expense = data.expense;
            const body = document.getElementById('viewExpenseBody');
            
            let html = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6>Tanggal Pengeluaran</h6>
                        <p>${expense.expense_date_formatted || '-'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Nama Pengeluaran</h6>
                        <p><strong>${expense.expense_name || '-'}</strong></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6>Jumlah Pengeluaran</h6>
                        <p><strong class="text-danger">Rp ${expense.amount_formatted || '0'}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Tipe</h6>
                        <p><span class="badge bg-${expense.expense_type === 'perawatan' ? 'info' : (expense.expense_type === 'nutrisi' ? 'success' : (expense.expense_type === 'upah_pekerja' ? 'dark' : 'secondary'))}">${expense.expense_type_label || '-'}</span></p>
                    </div>
                </div>
                ${expense.description ? `
                <div class="mb-3">
                    <h6>Keterangan</h6>
                    <p>${expense.description}</p>
                </div>
                ` : ''}
                ${expense.plant ? `
                <div class="mb-3">
                    <h6>Tanaman</h6>
                    <p>${expense.plant.name}${expense.plant.variety ? ' - ' + expense.plant.variety : ''}</p>
                </div>
                ` : ''}
                ${expense.planting_location ? `
                <div class="mb-3">
                    <h6>Lokasi Penanaman</h6>
                    <p>${expense.planting_location.name}</p>
                </div>
                ` : ''}
            `;
            
            body.innerHTML = html;
            new bootstrap.Modal(document.getElementById('viewExpenseModal')).show();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal memuat detail pengeluaran.');
    });
}

function editExpense(expenseId, plantingLocationId) {
    fetch(`/planting-locations/${plantingLocationId}/expenses/${expenseId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.expense) {
            const expense = data.expense;
            const body = document.getElementById('editExpenseBody');
            const form = document.getElementById('editExpenseForm');
            
            form.action = `/planting-locations/${plantingLocationId}/expenses/${expenseId}`;
            
            let html = `
                <input type="hidden" name="expense_id" value="${expense.id}">
                <div class="mb-3">
                    <label class="form-label">Nama Pengeluaran <span class="text-danger">*</span></label>
                    <input type="text" name="expense_name" class="form-control" value="${expense.expense_name || ''}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal Pengeluaran</label>
                    <input type="date" name="work_date" class="form-control" value="${expense.expense_date || ''}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Jumlah Pengeluaran <span class="text-danger">*</span></label>
                    <input type="number" name="amount" class="form-control" step="0.01" min="0" value="${expense.amount || 0}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="description" class="form-control" rows="3">${expense.description || ''}</textarea>
                </div>
            `;
            
            body.innerHTML = html;
            new bootstrap.Modal(document.getElementById('editExpenseModal')).show();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal memuat data pengeluaran untuk diedit.');
    });
}

// Handle edit form submit
document.addEventListener('submit', function(e) {
    if (e.target && e.target.id === 'editExpenseForm') {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        // Ensure _method is set for PUT request
        if (!formData.has('_method')) {
            formData.append('_method', 'PUT');
        }
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            if (data.success || data.message) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editExpenseModal'));
                if (modal) modal.hide();
                if (data.message) {
                    alert(data.message);
                }
                window.location.reload();
            } else {
                alert('Gagal menyimpan perubahan.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            let errorMessage = 'Gagal menyimpan perubahan.';
            if (error.message) {
                errorMessage = error.message;
            } else if (error.errors) {
                const errorList = Object.values(error.errors).flat().join(', ');
                errorMessage = errorList || errorMessage;
            }
            alert(errorMessage);
        });
    }
});
</script>
@endpush
@endsection

