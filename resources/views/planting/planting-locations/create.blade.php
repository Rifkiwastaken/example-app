@extends('layouts.app')

@section('title', 'Tambah Lokasi Penanaman - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Lokasi Penanaman</h4>
    <div class="d-flex align-items-center">
        <span class="badge bg-primary me-2">1 Details</span>
        <a href="{{ route('planting-locations.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Tambah Lokasi Penanaman</h5>
    </div>
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
        
        <form method="POST" action="{{ route('planting-locations.store') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nama Lokasi Penanaman <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tipe Lokasi Penanaman</label>
                        <select name="location_type" id="location_type" class="form-select @error('location_type') is-invalid @enderror" required onchange="toggleLocationTypeCustom()">
                            <option value="">Pilih tipe lokasi penanaman</option>
                            <option value="lapangan" {{ old('location_type') == 'lapangan' ? 'selected' : '' }}>Lapangan</option>
                            <option value="sawah" {{ old('location_type') == 'sawah' ? 'selected' : '' }}>Sawah</option>
                            <option value="greenhouse" {{ old('location_type') == 'greenhouse' ? 'selected' : '' }}>Greenhouse</option>
                            <option value="grow_room" {{ old('location_type') == 'grow_room' ? 'selected' : '' }}>Grow Room</option>
                            <option value="padang_rumput" {{ old('location_type') == 'padang_rumput' ? 'selected' : '' }}>Padang Rumput</option>
                            <option value="petak_ternak" {{ old('location_type') == 'petak_ternak' ? 'selected' : '' }}>Petak Ternak</option>
                            <option value="lainnya" {{ old('location_type') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('location_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div id="location_type_custom_container" class="mt-2" style="display: none;">
                            <input type="text" name="location_type_custom" id="location_type_custom" 
                                   class="form-control @error('location_type_custom') is-invalid @enderror" 
                                   value="{{ old('location_type_custom') }}" 
                                   placeholder="Masukkan tipe lokasi penanaman lainnya">
                            @error('location_type_custom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Luas Lokasi Penanaman (Ha) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="map_size" class="form-control @error('map_size') is-invalid @enderror" 
                                   value="{{ old('map_size') }}" step="0.01" min="0" required>
                            <span class="input-group-text">Ha</span>
                            <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                        </div>
                        @error('map_size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Alamat <span class="text-danger">*</span></label>
                        <input type="text" name="location_summary" class="form-control @error('location_summary') is-invalid @enderror"
                               value="{{ old('location_summary') }}" placeholder="kota/ kabupaten" required>
                        @error('location_summary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            @include('planting.planting-locations._location-gps-field')

            <div class="mb-3">
                <label class="form-label">Alamat Administratif</label>
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="province" maxlength="100" class="form-control @error('province') is-invalid @enderror"
                               value="{{ old('province') }}" placeholder="Provinsi" required>
                        <small class="text-muted">Provinsi <span class="text-danger">*</span></small>
                        @error('province')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="district" maxlength="100" class="form-control @error('district') is-invalid @enderror"
                               value="{{ old('district') }}" placeholder="Kecamatan" required>
                        <small class="text-muted">Kecamatan <span class="text-danger">*</span></small>
                        @error('district')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="village" maxlength="100" class="form-control @error('village') is-invalid @enderror"
                               value="{{ old('village') }}" placeholder="Desa/Kelurahan" required>
                        <small class="text-muted">Desa/Kelurahan <span class="text-danger">*</span></small>
                        @error('village')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Foto Lokasi Penanaman</label>
                <input type="file" name="primary_photo" class="form-control @error('primary_photo') is-invalid @enderror" accept="image/*">
                @error('primary_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">Format JPG/PNG, ukuran maksimal 5 MB.</small>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="mb-3">
                        <label class="form-label">Penempatan pekerja</label>
                        <p class="text-muted small mb-0">Penugasan lokasi penanaman untuk penangkar dan kepala satuan tugas diatur saat admin menambahkan atau mengedit akun user pada field lokasi penempatan.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Status Kepemilikan</label>
                        <select name="ownership_status" class="form-select @error('ownership_status') is-invalid @enderror" data-custom-target="#ownershipStatusCustom">
                            <option value="">Pilih status kepemilikan</option>
                            <option value="Milik Sendiri" {{ old('ownership_status') == 'Milik Sendiri' ? 'selected' : '' }}>Milik Sendiri</option>
                            <option value="Sewa" {{ old('ownership_status') == 'Sewa' ? 'selected' : '' }}>Sewa</option>
                            <option value="Milik Pemerintah" {{ old('ownership_status') == 'Milik Pemerintah' ? 'selected' : '' }}>Milik Pemerintah</option>
                            <option value="_custom" {{ old('ownership_status') && !in_array(old('ownership_status'), ['Milik Sendiri','Sewa','Milik Pemerintah']) ? 'selected' : '' }}>Lainnya (isi manual)</option>
                        </select>
                        @error('ownership_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <input type="text" id="ownershipStatusCustom" name="ownership_status_custom"
                               class="form-control mt-2 {{ old('ownership_status') && !in_array(old('ownership_status'), ['Milik Sendiri','Sewa','Milik Pemerintah']) ? '' : 'd-none' }}"
                               value="{{ old('ownership_status') && !in_array(old('ownership_status'), ['Milik Sendiri','Sewa','Milik Pemerintah']) ? old('ownership_status') : old('ownership_status_custom') }}"
                               placeholder="Tuliskan status kepemilikan">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Sumber Air</label>
                        <select name="water_source" class="form-select @error('water_source') is-invalid @enderror" data-custom-target="#waterSourceCustom">
                            <option value="">Pilih sumber air</option>
                            <option value="Irigasi" {{ old('water_source') == 'Irigasi' ? 'selected' : '' }}>Irigasi</option>
                            <option value="Tadah Hujan" {{ old('water_source') == 'Tadah Hujan' ? 'selected' : '' }}>Tadah Hujan</option>
                            <option value="_custom" {{ old('water_source') && !in_array(old('water_source'), ['Irigasi','Tadah Hujan']) ? 'selected' : '' }}>Lainnya (isi manual)</option>
                        </select>
                        @error('water_source')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <input type="text" id="waterSourceCustom" name="water_source_custom"
                               class="form-control mt-2 {{ old('water_source') && !in_array(old('water_source'), ['Irigasi','Tadah Hujan']) ? '' : 'd-none' }}"
                               value="{{ old('water_source') && !in_array(old('water_source'), ['Irigasi','Tadah Hujan']) ? old('water_source') : old('water_source_custom') }}"
                               placeholder="Tuliskan sumber air">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tipe Tanah</label>
                        <select name="soil_type" class="form-select @error('soil_type') is-invalid @enderror" data-custom-target="#soilTypeCustom">
                            <option value="">Pilih tipe tanah</option>
                            <option value="Aluvial" {{ old('soil_type') == 'Aluvial' ? 'selected' : '' }}>Aluvial</option>
                            <option value="Latosol" {{ old('soil_type') == 'Latosol' ? 'selected' : '' }}>Latosol</option>
                            <option value="Litosol" {{ old('soil_type') == 'Litosol' ? 'selected' : '' }}>Litosol</option>
                            <option value="_custom" {{ old('soil_type') && !in_array(old('soil_type'), ['Aluvial','Latosol','Litosol']) ? 'selected' : '' }}>Lainnya (isi manual)</option>
                        </select>
                        @error('soil_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <input type="text" id="soilTypeCustom" name="soil_type_custom"
                               class="form-control mt-2 {{ old('soil_type') && !in_array(old('soil_type'), ['Aluvial','Latosol','Litosol']) ? '' : 'd-none' }}"
                               value="{{ old('soil_type') && !in_array(old('soil_type'), ['Aluvial','Latosol','Litosol']) ? old('soil_type') : old('soil_type_custom') }}"
                               placeholder="Tuliskan tipe tanah">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Ketinggian (MDPL)</label>
                        <div class="input-group">
                            <input type="number" name="elevation_masl" class="form-control @error('elevation_masl') is-invalid @enderror"
                                   value="{{ old('elevation_masl') }}" step="1">
                            <span class="input-group-text">mdpl</span>
                        </div>
                        @error('elevation_masl')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Planting Format Section -->
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <label class="form-label">Format Penanaman</label>
                        <select name="planting_format" id="plantingFormatSelect" class="form-select @error('planting_format') is-invalid @enderror">
                            <option value="">Pilih format penanaman</option>
                            @foreach(\App\Models\PlantingLocation::FORMAT_PENANAMAN as $value => $label)
                                <option value="{{ $value }}" {{ old('planting_format') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('planting_format')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div id="plantingFormatCustomWrapper" class="mt-2 {{ old('planting_format') === 'lainnya' ? '' : 'd-none' }}">
                            <input type="text" name="planting_format_custom" class="form-control @error('planting_format_custom') is-invalid @enderror"
                                   value="{{ old('planting_format') === 'lainnya' ? old('planting_format_custom') : '' }}" placeholder="Tuliskan format penanaman lainnya">
                            @error('planting_format_custom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Kondisi Cahaya</label>
                        <select name="light_condition" class="form-select @error('light_condition') is-invalid @enderror" data-custom-target="#lightConditionCustom">
                            <option value="">Pilih kondisi cahaya</option>
                            <option value="sinar_matahari_penuh" {{ old('light_condition') == 'sinar_matahari_penuh' ? 'selected' : '' }}>Sinar Matahari Penuh</option>
                            <option value="sinar_matahari_penuh_hingga_sebagian" {{ old('light_condition') == 'sinar_matahari_penuh_hingga_sebagian' ? 'selected' : '' }}>Sinar Matahari Penuh hingga Sebagian</option>
                            <option value="sinar_matahari_sebagian" {{ old('light_condition') == 'sinar_matahari_sebagian' ? 'selected' : '' }}>Sinar Matahari Sebagian</option>
                            <option value="matahari_hingga_setengah_teduh" {{ old('light_condition') == 'matahari_hingga_setengah_teduh' ? 'selected' : '' }}>Matahari hingga Setengah Teduh</option>
                            <option value="setengah_teduh" {{ old('light_condition') == 'setengah_teduh' ? 'selected' : '' }}>Setengah Teduh</option>
                            <option value="teduh_sepenuhnya" {{ old('light_condition') == 'teduh_sepenuhnya' ? 'selected' : '' }}>Teduh Sepenuhnya</option>
                            <option value="_custom" {{ old('light_condition') && !in_array(old('light_condition'), ['sinar_matahari_penuh','sinar_matahari_penuh_hingga_sebagian','sinar_matahari_sebagian','matahari_hingga_setengah_teduh','setengah_teduh','teduh_sepenuhnya']) ? 'selected' : '' }}>Lainnya (isi manual)</option>
                        </select>
                        @error('light_condition')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <input type="text" id="lightConditionCustom" name="light_condition_custom"
                               class="form-control mt-2 {{ old('light_condition') && !in_array(old('light_condition'), ['sinar_matahari_penuh','sinar_matahari_penuh_hingga_sebagian','sinar_matahari_sebagian','matahari_hingga_setengah_teduh','setengah_teduh','teduh_sepenuhnya']) ? '' : 'd-none' }}"
                               value="{{ old('light_condition') && !in_array(old('light_condition'), ['sinar_matahari_penuh','sinar_matahari_penuh_hingga_sebagian','sinar_matahari_sebagian','matahari_hingga_setengah_teduh','setengah_teduh','teduh_sepenuhnya']) ? old('light_condition') : old('light_condition_custom') }}"
                               placeholder="Tuliskan kondisi cahaya">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                          rows="3" placeholder="Masukkan deskripsi lokasi penanaman">{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @include('planting.planting-locations._lahan-repeater')

            <div class="d-flex justify-content-end gap-2">
                <a class="btn btn-secondary" href="{{ route('planting-locations.index') }}">Cancel</a>
                <button class="btn btn-success" type="submit">Save</button>
            </div>
        </form>
    </div>
</div>

@include('planting.planting-locations._location-maps-scripts')

@push('scripts')
<script>
function toggleLocationTypeCustom() {
    const locationType = document.getElementById('location_type');
    const customContainer = document.getElementById('location_type_custom_container');
    const customInput = document.getElementById('location_type_custom');
    
    if (locationType.value === 'lainnya') {
        customContainer.style.display = 'block';
        customInput.required = true;
    } else {
        customContainer.style.display = 'none';
        customInput.required = false;
        customInput.value = '';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleLocationTypeCustom();
    const plantingFormatSelect = document.getElementById('plantingFormatSelect');
    const plantingFormatCustomWrapper = document.getElementById('plantingFormatCustomWrapper');

    if (plantingFormatSelect && plantingFormatCustomWrapper) {
        const toggleFormatCustom = () => {
            plantingFormatCustomWrapper.classList.toggle('d-none', plantingFormatSelect.value !== 'lainnya');
        };
        plantingFormatSelect.addEventListener('change', toggleFormatCustom);
        toggleFormatCustom();
    }

    document.querySelectorAll('select[data-custom-target]').forEach(select => {
        const targetSelector = select.getAttribute('data-custom-target');
        const targetInput = document.querySelector(targetSelector);

        const toggleCustom = () => {
            if (!targetInput) {
                return;
            }
            if (select.value === '_custom') {
                targetInput.classList.remove('d-none');
                targetInput.focus();
            } else {
                targetInput.value = '';
                targetInput.classList.add('d-none');
            }
        };

        select.addEventListener('change', toggleCustom);
        toggleCustom();
    });

    // Handle land worker user selection
    const landWorkerUserSelect = document.getElementById('landWorkerUserSelect');
    const addLandWorkerUserBtn = document.getElementById('addLandWorkerUserBtn');
    const selectedLandWorkerUsers = document.getElementById('selectedLandWorkerUsers');

    if (landWorkerUserSelect && selectedLandWorkerUsers) {

    function getSelectedLandWorkerUserIds() {
        return Array.from(selectedLandWorkerUsers.querySelectorAll('input[name="land_worker_user_ids[]"]'))
            .map(input => input.value);
    }

    function updateLandWorkerUserDropdown() {
        const selectedIds = getSelectedLandWorkerUserIds();
        Array.from(landWorkerUserSelect.options).forEach(option => {
            if (option.value && selectedIds.includes(option.value)) {
                option.style.display = 'none';
            } else {
                option.style.display = '';
            }
        });
    }

    const landWorkerRoleSelect = document.getElementById('landWorkerRoleSelect');
    function addLandWorkerUser() {
        const selectedOption = landWorkerUserSelect.options[landWorkerUserSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            alert('Silakan pilih user terlebih dahulu.');
            return;
        }
        const roleSelect = landWorkerRoleSelect ? landWorkerRoleSelect : document.querySelector('#landWorkerRoleSelect');
        const role = roleSelect ? roleSelect.value : 'petugas_lapangan';
        const roleLabel = role === 'penangkar' ? 'Penangkar' : 'Petugas Lapangan';

        const userId = selectedOption.value;
        const userName = selectedOption.dataset.name || selectedOption.text;

        if (getSelectedLandWorkerUserIds().includes(userId)) {
            alert('User ini sudah ditambahkan.');
            return;
        }

        const userItem = document.createElement('div');
        userItem.className = 'selected-user-item mb-2 p-2 border rounded d-flex justify-content-between align-items-center';
        userItem.setAttribute('data-user-id', userId);
        userItem.setAttribute('data-role', role);
        userItem.innerHTML = `
            <span>
                <strong>${userName}</strong>
                <small class="text-muted"> – ${roleLabel}</small>
            </span>
            <button type="button" class="btn btn-sm btn-outline-danger remove-user" title="Hapus">
                <i class="fas fa-times"></i>
            </button>
            <input type="hidden" name="land_worker_user_ids[]" value="${userId}">
            <input type="hidden" name="land_worker_roles[]" value="${role}">
        `;

        userItem.querySelector('.remove-user').addEventListener('click', function() {
            userItem.remove();
            updateLandWorkerUserDropdown();
        });

        selectedLandWorkerUsers.appendChild(userItem);
        landWorkerUserSelect.value = '';
        updateLandWorkerUserDropdown();
    }

    if (addLandWorkerUserBtn) {
        addLandWorkerUserBtn.addEventListener('click', addLandWorkerUser);
    }

    if (landWorkerUserSelect) {
        landWorkerUserSelect.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addLandWorkerUser();
            }
        });
    }

    selectedLandWorkerUsers.querySelectorAll('.remove-user').forEach(btn => {
        btn.addEventListener('click', function() {
            const userItem = this.closest('.selected-user-item');
            if (userItem) {
                userItem.remove();
                updateLandWorkerUserDropdown();
            }
        });
    });

    updateLandWorkerUserDropdown();
    }
});
</script>
@endpush
@endsection








