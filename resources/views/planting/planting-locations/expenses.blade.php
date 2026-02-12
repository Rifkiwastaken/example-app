@extends('layouts.app')

@section('title', 'Pengeluaran - ' . $plantingLocation->name . ' - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $plantingLocation->name }}</h4>
    </div>
    <a href="{{ route('planting-locations.show', $plantingLocation) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Navigation Tabs -->
<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('planting-locations.show', $plantingLocation) }}">
            <i class="fas fa-info-circle me-1"></i>Detail & Lokasi Penanaman
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('planting-locations.plantings.index', $plantingLocation) }}">
            <i class="fas fa-seedling me-1"></i>Penanaman
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('planting-locations.planting-history', $plantingLocation) }}">
            <i class="fas fa-history me-1"></i>Riwayat Penanaman
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('planting-locations.expenses.index', $plantingLocation) }}">
            <i class="fas fa-money-bill-wave me-1"></i>Pengeluaran
        </a>
    </li>
</ul>

<div class="tab-content p-3 bg-white border border-top-0 rounded-bottom">
    <!-- Filter Card -->
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filter</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('planting-locations.expenses.index', $plantingLocation) }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Tahun</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipe Pengeluaran</label>
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ request('type') == 'all' || !request('type') ? 'selected' : '' }}>Semua Tipe</option>
                        <option value="perawatan" {{ request('type') == 'perawatan' ? 'selected' : '' }}>Perawatan</option>
                        <option value="nutrisi" {{ request('type') == 'nutrisi' ? 'selected' : '' }}>Nutrisi</option>
                        <option value="upah_pekerja" {{ request('type') == 'upah_pekerja' ? 'selected' : '' }}>Upah Pekerja</option>
                        <option value="lainnya" {{ request('type') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Penanaman</label>
                    <select name="planting_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Penanaman</option>
                        @foreach($allPlantings as $planting)
                            <option value="{{ $planting->id }}" {{ request('planting_id') == $planting->id ? 'selected' : '' }}>
                                {{ $planting->plant->name ?? 'Tanaman' }} 
                                @if($planting->bed_label) - {{ $planting->bed_label }} @endif
                                @if($planting->planted_at) ({{ $planting->planted_at->format('d M Y') }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <a href="{{ route('planting-locations.expenses.index', $plantingLocation) }}" class="btn btn-secondary btn-sm w-100">
                            <i class="fas fa-redo me-1"></i>Reset
                        </a>
                    </div>
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
                    @if(request('year') || (request('type') && request('type') != 'all') || request('planting_id'))
                        <small class="text-muted">
                            @if(request('year'))
                                Tahun: {{ request('year') }}
                            @endif
                            @if(request('type') && request('type') != 'all')
                                | Tipe: {{ ucfirst(str_replace('_', ' ', request('type'))) }}
                            @endif
                            @if(request('planting_id'))
                                @php
                                    $selectedPlanting = $allPlantings->firstWhere('id', request('planting_id'));
                                @endphp
                                @if($selectedPlanting)
                                    | Penanaman: {{ $selectedPlanting->plant->name ?? 'Tanaman' }}
                                    @if($selectedPlanting->bed_label) - {{ $selectedPlanting->bed_label }} @endif
                                @endif
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
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Pengeluaran</h6>
            @if(auth()->user()->isAdmin() || auth()->user()->canAddDataInPelaporan($plantingLocation))
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPengeluaran">
                    <i class="fas fa-plus me-1"></i>Tambah Pengeluaran Baru
                </button>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama Pengeluaran</th>
                            <th>Jumlah Pengeluaran</th>
                            <th>Tipe</th>
                            <th>Keterangan</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td>{{ $expense->expense_date ? $expense->expense_date->format('d M Y') : '-' }}</td>
                                <td><strong>{{ $expense->expense_name ?? '-' }}</strong></td>
                                <td><strong class="text-danger">Rp {{ number_format($expense->amount ?? 0, 0, ',', '.') }}</strong></td>
                                <td>
                                    <span class="badge bg-{{ $expense->expense_type === 'perawatan' ? 'info' : ($expense->expense_type === 'nutrisi' ? 'success' : ($expense->expense_type === 'upah_pekerja' ? 'dark' : 'secondary')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $expense->expense_type ?? '-')) }}
                                    </span>
                                </td>
                                <td>
                                    @if($expense->planting)
                                        <div class="mb-1">
                                            <span class="badge bg-info">
                                                <i class="fas fa-seedling me-1"></i>
                                                {{ $expense->planting->plant->name ?? 'Tanaman' }}
                                                @if($expense->planting->bed_label)
                                                    - {{ $expense->planting->bed_label }}
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                    @if($expense->treatment)
                                        <div class="small">Perawatan: {{ $expense->treatment->treatment_name ?? '-' }}</div>
                                    @elseif($expense->nutrient)
                                        <div class="small">Nutrisi: {{ $expense->nutrient->product_applied ?? '-' }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-info" onclick="viewExpense('{{ $expense->expense_id }}', '{{ $plantingLocation->planting_location_id }}')" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if(auth()->user()->isAdmin() || (auth()->user()->isAssignedToPlantingLocation($plantingLocation) && auth()->user()->canAddDataInPelaporan($plantingLocation)))
                                            @if(in_array($expense->expense_type, ['upah_pekerja', 'lainnya']))
                                                <button type="button" class="btn btn-outline-warning" onclick="editExpense('{{ $expense->expense_id }}', '{{ $plantingLocation->planting_location_id }}')" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('planting-locations.expenses.destroy', [$plantingLocation, $expense]) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengeluaran ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
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
        </div>
    </div>
</div>

<!-- Modal: Tambah Pengeluaran Baru -->
<div class="modal fade" id="modalTambahPengeluaran" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('planting-locations.expenses.store', $plantingLocation) }}" method="POST" id="formTambahPengeluaran">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengeluaran Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tipe Pengeluaran <span class="text-danger">*</span></label>
                        <select name="expense_type" id="expense_type" class="form-select" required onchange="toggleExpenseTypeFields()">
                            <option value="">-- Pilih Tipe Pengeluaran --</option>
                            <option value="upah_pekerja">Upah Pekerja</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                        <small class="text-muted">Pilih tipe pengeluaran. Untuk Perawatan dan Nutrisi, silakan tambahkan melalui menu Perawatan dan Nutrisi.</small>
                    </div>
                    
                    <div id="expenseFormFields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Nama Pengeluaran <span class="text-danger">*</span></label>
                            <input type="text" name="expense_name" id="expense_name" class="form-control" required>
                            <input type="hidden" name="work_name" id="work_name">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Pengeluaran</label>
                                <input type="date" name="work_date" id="work_date" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jumlah Pengeluaran <span class="text-danger">*</span></label>
                                <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Asosiasi Penanaman</label>
                            <select name="planting_id" id="planting_id" class="form-select">
                                <option value="">Tidak ada asosiasi</option>
                                @foreach($allPlantings as $planting)
                                    <option value="{{ $planting->id }}">
                                        {{ $planting->plant->name ?? 'Tanaman' }} 
                                        @if($planting->bed_label) - {{ $planting->bed_label }} @endif
                                        @if($planting->planted_at) ({{ $planting->planted_at->format('d M Y') }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih penanaman jika pengeluaran ini terkait dengan penanaman tertentu</small>
                        </div>
                        <div class="mb-3" id="workerFields" style="display: none;">
                            <label class="form-label">Nama Pekerja</label>
                            <input type="text" name="worker_name" id="worker_name" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi Pekerjaan</label>
                            <textarea name="work_description" id="work_description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="btnSubmitPengeluaran" disabled>
                        <i class="fas fa-save me-1"></i>Simpan Pengeluaran
                    </button>
                </div>
            </form>
        </div>
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
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
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
        } else {
            throw new Error('Invalid response format');
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

function toggleExpenseTypeFields() {
    const expenseType = document.getElementById('expense_type').value;
    const formFields = document.getElementById('expenseFormFields');
    const btnSubmit = document.getElementById('btnSubmitPengeluaran');
    const workerFields = document.getElementById('workerFields');
    const expenseName = document.getElementById('expense_name');
    const workName = document.getElementById('work_name');
    
    if (expenseType) {
        formFields.style.display = 'block';
        btnSubmit.disabled = false;
        
        // Show worker fields for upah_pekerja
        if (expenseType === 'upah_pekerja') {
            workerFields.style.display = 'block';
            if (expenseName) {
                expenseName.placeholder = 'Contoh: Pembersihan lahan, Penanaman, dll';
            }
        } else {
            workerFields.style.display = 'none';
            if (expenseName) {
                expenseName.placeholder = 'Masukkan nama pengeluaran';
            }
        }
    } else {
        formFields.style.display = 'none';
        btnSubmit.disabled = true;
    }
}

// Sync expense_name to work_name
document.addEventListener('DOMContentLoaded', function() {
    const expenseNameInput = document.getElementById('expense_name');
    const workNameInput = document.getElementById('work_name');
    
    if (expenseNameInput && workNameInput) {
        expenseNameInput.addEventListener('input', function() {
            workNameInput.value = expenseNameInput.value;
        });
    }
    
    // Reset form when modal is closed
    const modal = document.getElementById('modalTambahPengeluaran');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            const form = document.getElementById('formTambahPengeluaran');
            if (form) {
                form.reset();
                document.getElementById('expenseFormFields').style.display = 'none';
                document.getElementById('btnSubmitPengeluaran').disabled = true;
                document.getElementById('work_date').value = '{{ date('Y-m-d') }}';
            }
        });
    }
});
</script>
@endpush
@endsection

