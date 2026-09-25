@extends('layouts.app')

@section('title', 'Edit Varietas Tanaman - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Varietas Tanaman</h4>
    <a href="{{ route('plants.show', $plant) }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
</div>

<div class="card">
    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>Terjadi kesalahan:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('plants.update', $plant) }}" id="plantForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Nama Tanaman <span class="text-danger">*</span></label>
                <div class="d-flex gap-2">
                    <select name="plant_type_id" id="plantTypeSelect" class="form-select @error('plant_type_id') is-invalid @enderror" required>
                        <option value="">Pilih tanaman</option>
                        @foreach($types as $type)
                            <option value="{{ $type->plant_type_id }}" {{ old('plant_type_id', $plant->plant_type_id) == $type->plant_type_id ? 'selected' : '' }}>
                                {{ $type->name }}{{ $type->category ? ' - ' . $type->category : '' }}
                            </option>
                        @endforeach
                    </select>
                    @if(auth()->user()->role !== 'penangkar')
                        <button type="button" class="btn btn-success text-nowrap" data-bs-toggle="modal" data-bs-target="#addPlantTypeModal">
                            Kategori tanaman
                        </button>
                    @endif
                </div>
                @error('plant_type_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Varietas <span class="text-danger">*</span></label>
                <input type="text" name="variety" id="varietyInput" class="form-control @error('variety') is-invalid @enderror"
                       value="{{ old('variety', $plant->variety) }}" placeholder="Contoh: Bujang Merantau, Inpari 32" required>
                @error('variety')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror"
                          placeholder="Masukkan deskripsi varietas">{{ old('description', $plant->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @include('planting.plants._agronomy-fields', ['plant' => $plant, 'harvestUnitRequired' => false])

            <div class="d-flex justify-content-end gap-2">
                <a class="btn btn-secondary" href="{{ route('plants.show', $plant) }}">Batal</a>
                <button class="btn btn-success" type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="addPlantTypeModal" tabindex="-1" aria-labelledby="addPlantTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addPlantTypeForm">
                @csrf
            @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="addPlantTypeModalLabel">Tambahkan Kategori Tanaman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Tanaman <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="plantTypeName" class="form-control" required>
                        <div class="invalid-feedback" id="nameError"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori (opsional)</label>
                        <select name="category" id="plantTypeCategory" class="form-select" onchange="toggleCategoryCustom()">
                            <option value="">-- Pilih Kategori --</option>
                            <option value="pangan">Pangan</option>
                            <option value="hortikultura">Hortikultura</option>
                            <option value="sayur">Sayur</option>
                            <option value="buah">Buah</option>
                            <option value="hias">Hias</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                        <div class="invalid-feedback" id="categoryError"></div>
                        <div id="category_custom_container" class="mt-2" style="display: none;">
                            <input type="text" name="category_custom" id="plantTypeCategoryCustom"
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
document.addEventListener('DOMContentLoaded', function() {
    const selectElement = document.getElementById('plantTypeSelect');
    const form = document.getElementById('plantForm');

    form.addEventListener('submit', function(e) {
        if (!selectElement.value) {
            e.preventDefault();
            alert('Harap pilih nama tanaman terlebih dahulu.');
            selectElement.focus();
            return false;
        }
        const varietyInput = document.getElementById('varietyInput');
        if (!varietyInput || !varietyInput.value.trim()) {
            e.preventDefault();
            alert('Varietas wajib diisi.');
            if (varietyInput) varietyInput.focus();
            return false;
        }
    });

    const addPlantTypeModal = document.getElementById('addPlantTypeModal');
    const addPlantTypeForm = document.getElementById('addPlantTypeForm');

    window.toggleCategoryCustom = function() {
        const category = document.getElementById('plantTypeCategory');
        const customContainer = document.getElementById('category_custom_container');
        const customInput = document.getElementById('plantTypeCategoryCustom');

        if (category && category.value === 'lainnya') {
            if (customContainer) customContainer.style.display = 'block';
        } else {
            if (customContainer) customContainer.style.display = 'none';
            if (customInput) customInput.value = '';
        }
    };

    if (addPlantTypeModal) {
        addPlantTypeModal.addEventListener('show.bs.modal', function() {
            addPlantTypeForm.reset();
            document.getElementById('nameError').textContent = '';
            document.getElementById('categoryError').textContent = '';
            document.getElementById('plantTypeName').classList.remove('is-invalid');
            document.getElementById('plantTypeCategory').classList.remove('is-invalid');
            if (document.getElementById('category_custom_container')) {
                document.getElementById('category_custom_container').style.display = 'none';
            }
        });
    }

    if (addPlantTypeForm) {
        addPlantTypeForm.addEventListener('submit', function(e) {
            e.preventDefault();

            document.getElementById('nameError').textContent = '';
            document.getElementById('categoryError').textContent = '';
            document.getElementById('plantTypeName').classList.remove('is-invalid');
            document.getElementById('plantTypeCategory').classList.remove('is-invalid');

            const formData = new FormData(this);
            const category = document.getElementById('plantTypeCategory').value;
            if (category === 'lainnya') {
                const categoryCustom = document.getElementById('plantTypeCategoryCustom').value;
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
                    const select = document.getElementById('plantTypeSelect');
                    const option = document.createElement('option');
                    option.value = data.plant_type.plant_type_id;
                    option.textContent = data.plant_type.name + (data.plant_type.category ? ' - ' + data.plant_type.category : '');
                    option.selected = true;
                    select.appendChild(option);

                    const modal = bootstrap.Modal.getInstance(document.getElementById('addPlantTypeModal'));
                    modal.hide();
                    addPlantTypeForm.reset();
                    alert('Kategori tanaman berhasil ditambahkan!');
                }
            })
            .catch(error => {
                if (error.errors) {
                    if (error.errors.name) {
                        document.getElementById('plantTypeName').classList.add('is-invalid');
                        document.getElementById('nameError').textContent = error.errors.name[0];
                    }
                    if (error.errors.category) {
                        document.getElementById('plantTypeCategory').classList.add('is-invalid');
                        document.getElementById('categoryError').textContent = error.errors.category[0];
                    }
                } else {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menambahkan kategori tanaman.');
                }
            });
        });
    }
});
</script>
@endpush
@endsection
