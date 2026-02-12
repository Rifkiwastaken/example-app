@extends('layouts.app')

@section('title', 'Tipe Tanaman - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Tipe Tanaman</h4>
        <button type="button" class="btn btn-success mt-2" data-bs-toggle="modal" data-bs-target="#addPlantTypeModal">
            <i class="fas fa-plus me-2"></i>Tambah Tipe
        </button>
    </div>
    <a href="{{ route('plants.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Varietas</th>
                        <th>Kategori</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($types as $type)
                        <tr>
                            <td>{{ $type->name }}</td>
                            <td>
                                @if($type->variety)
                                    <small>{{ Str::limit($type->variety, 50) }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($type->category)
                                    <span class="badge bg-secondary">{{ $type->category }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-warning" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editPlantTypeModal"
                                            data-id="{{ $type->id }}"
                                            data-name="{{ $type->name }}"
                                            data-variety="{{ $type->variety }}"
                                            data-category="{{ $type->category }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="confirmDelete('{{ route('plant-types.destroy', $type) }}', '{{ addslashes($type->name) }}', 'tipe tanaman')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Belum ada tipe tanaman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($types->hasPages())
            <div class="d-flex justify-content-center mt-3">{{ $types->links() }}</div>
        @endif
    </div>
</div>

<!-- Modal: Tambah Tipe Tanaman -->
<div class="modal fade" id="addPlantTypeModal" tabindex="-1" aria-labelledby="addPlantTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addPlantTypeForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addPlantTypeModalLabel">
                        <i class="fas fa-plus me-2"></i>Tambah Tipe Tanaman
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Tanaman <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="addPlantTypeName" class="form-control" required>
                        <div class="invalid-feedback" id="addNameError"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Varietas <span class="text-danger">*</span></label>
                        <textarea name="variety" id="addPlantTypeVariety" class="form-control" rows="3" required placeholder="Masukkan varietas (pisahkan dengan enter untuk multiple varietas)"></textarea>
                        <small class="text-muted">Contoh: Varietas A, Varietas B, Varietas C atau pisahkan dengan enter</small>
                        <div class="invalid-feedback" id="addVarietyError"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori (opsional)</label>
                        <select name="category" id="addPlantTypeCategory" class="form-select" onchange="toggleCategoryCustom('add')">
                            <option value="">-- Pilih Kategori --</option>
                            <option value="pangan">Pangan</option>
                            <option value="hortikultura">Hortikultura</option>
                            <option value="sayur">Sayur</option>
                            <option value="buah">Buah</option>
                            <option value="hias">Hias</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                        <div class="invalid-feedback" id="addCategoryError"></div>
                        <div id="add_category_custom_container" class="mt-2" style="display: none;">
                            <input type="text" name="category_custom" id="addPlantTypeCategoryCustom" 
                                   class="form-control" 
                                   placeholder="Masukkan kategori lainnya">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Tipe Tanaman -->
<div class="modal fade" id="editPlantTypeModal" tabindex="-1" aria-labelledby="editPlantTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editPlantTypeForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editPlantTypeModalLabel">
                        <i class="fas fa-edit me-2"></i>Edit Tipe Tanaman
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editPlantTypeId">
                    <div class="mb-3">
                        <label class="form-label">Nama Tanaman <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editPlantTypeName" class="form-control" required>
                        <div class="invalid-feedback" id="editNameError"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Varietas <span class="text-danger">*</span></label>
                        <textarea name="variety" id="editPlantTypeVariety" class="form-control" rows="3" required placeholder="Masukkan varietas (pisahkan dengan enter untuk multiple varietas)"></textarea>
                        <small class="text-muted">Contoh: Varietas A, Varietas B, Varietas C atau pisahkan dengan enter</small>
                        <div class="invalid-feedback" id="editVarietyError"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori (opsional)</label>
                        <select name="category" id="editPlantTypeCategory" class="form-select" onchange="toggleCategoryCustom('edit')">
                            <option value="">-- Pilih Kategori --</option>
                            <option value="pangan">Pangan</option>
                            <option value="hortikultura">Hortikultura</option>
                            <option value="sayur">Sayur</option>
                            <option value="buah">Buah</option>
                            <option value="hias">Hias</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                        <div class="invalid-feedback" id="editCategoryError"></div>
                        <div id="edit_category_custom_container" class="mt-2" style="display: none;">
                            <input type="text" name="category_custom" id="editPlantTypeCategoryCustom" 
                                   class="form-control" 
                                   placeholder="Masukkan kategori lainnya">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleCategoryCustom(type) {
    const category = document.getElementById(type === 'add' ? 'addPlantTypeCategory' : 'editPlantTypeCategory');
    const customContainer = document.getElementById(type === 'add' ? 'add_category_custom_container' : 'edit_category_custom_container');
    const customInput = document.getElementById(type === 'add' ? 'addPlantTypeCategoryCustom' : 'editPlantTypeCategoryCustom');
    
    if (category && category.value === 'lainnya') {
        if (customContainer) customContainer.style.display = 'block';
    } else {
        if (customContainer) customContainer.style.display = 'none';
        if (customInput) customInput.value = '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Handle Add Plant Type Modal
    const addModal = document.getElementById('addPlantTypeModal');
    const addForm = document.getElementById('addPlantTypeForm');
    
    if (addModal) {
        addModal.addEventListener('show.bs.modal', function() {
            addForm.reset();
            clearErrors('add');
            // Reset category custom container
            if (document.getElementById('add_category_custom_container')) {
                document.getElementById('add_category_custom_container').style.display = 'none';
            }
            // Reset variety field
            if (document.getElementById('addPlantTypeVariety')) {
                document.getElementById('addPlantTypeVariety').value = '';
            }
        });
    }
    
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearErrors('add');
            
            const formData = new FormData(this);
            
            // Handle category: if "lainnya" is selected, use category_custom value
            const category = document.getElementById('addPlantTypeCategory').value;
            if (category === 'lainnya') {
                const categoryCustom = document.getElementById('addPlantTypeCategoryCustom').value;
                formData.set('category', categoryCustom || '');
            }
            
            fetch('{{ route("plant-types.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(addModal);
                    modal.hide();
                    
                    // Reload page to show new data
                    window.location.reload();
                }
            })
            .catch(error => {
                handleErrors(error, 'add');
            });
        });
    }
    
    // Handle Edit Plant Type Modal
    const editModal = document.getElementById('editPlantTypeModal');
    const editForm = document.getElementById('editPlantTypeForm');
    
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const variety = button.getAttribute('data-variety');
            const category = button.getAttribute('data-category');
            
            document.getElementById('editPlantTypeId').value = id;
            document.getElementById('editPlantTypeName').value = name || '';
            document.getElementById('editPlantTypeVariety').value = variety || '';
            
            // Handle category: if category is not in dropdown options, set to "lainnya" and show custom field
            const validCategories = ['pangan', 'hortikultura', 'sayur', 'buah', 'hias'];
            const categorySelect = document.getElementById('editPlantTypeCategory');
            const categoryCustomInput = document.getElementById('editPlantTypeCategoryCustom');
            const categoryCustomContainer = document.getElementById('edit_category_custom_container');
            
            if (category && !validCategories.includes(category)) {
                categorySelect.value = 'lainnya';
                if (categoryCustomInput) categoryCustomInput.value = category;
                if (categoryCustomContainer) categoryCustomContainer.style.display = 'block';
            } else {
                categorySelect.value = category || '';
                if (categoryCustomInput) categoryCustomInput.value = '';
                if (categoryCustomContainer) categoryCustomContainer.style.display = 'none';
            }
            
            clearErrors('edit');
        });
    }
    
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearErrors('edit');
            
            const id = document.getElementById('editPlantTypeId').value;
            const formData = new FormData(this);
            
            // Handle category: if "lainnya" is selected, use category_custom value
            const category = document.getElementById('editPlantTypeCategory').value;
            if (category === 'lainnya') {
                const categoryCustom = document.getElementById('editPlantTypeCategoryCustom').value;
                formData.set('category', categoryCustom || '');
            }
            
            // Ensure _method is set for PUT request
            if (!formData.has('_method')) {
                formData.append('_method', 'PUT');
            }
            
            fetch(`{{ url('plant-types') }}/${id}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(editModal);
                    modal.hide();
                    
                    // Reload page to show updated data
                    window.location.reload();
                }
            })
            .catch(error => {
                handleErrors(error, 'edit');
            });
        });
    }
    
    function clearErrors(type) {
        const prefix = type === 'add' ? 'add' : 'edit';
        document.getElementById(`${prefix}PlantTypeName`).classList.remove('is-invalid');
        document.getElementById(`${prefix}PlantTypeVariety`).classList.remove('is-invalid');
        document.getElementById(`${prefix}PlantTypeCategory`).classList.remove('is-invalid');
        document.getElementById(`${prefix}NameError`).textContent = '';
        document.getElementById(`${prefix}VarietyError`).textContent = '';
        document.getElementById(`${prefix}CategoryError`).textContent = '';
    }
    
    function handleErrors(error, type) {
        const prefix = type === 'add' ? 'add' : 'edit';
        if (error.errors) {
            if (error.errors.name) {
                document.getElementById(`${prefix}PlantTypeName`).classList.add('is-invalid');
                document.getElementById(`${prefix}NameError`).textContent = error.errors.name[0];
            }
            if (error.errors.variety) {
                document.getElementById(`${prefix}PlantTypeVariety`).classList.add('is-invalid');
                document.getElementById(`${prefix}VarietyError`).textContent = error.errors.variety[0];
            }
            if (error.errors.category) {
                document.getElementById(`${prefix}PlantTypeCategory`).classList.add('is-invalid');
                document.getElementById(`${prefix}CategoryError`).textContent = error.errors.category[0];
            }
        } else {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan data.');
        }
    }
});
</script>
@endpush
@endsection


















