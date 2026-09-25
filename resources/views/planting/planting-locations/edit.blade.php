@extends('layouts.app')

@section('title', 'Edit Lokasi Penanaman - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Lokasi Penanaman</h4>
    <div class="d-flex align-items-center">
        <a href="{{ route('planting-locations.show', $plantingLocation) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

@php
    $selectedLandWorkers = collect(old('land_worker_user_ids', $plantingLocation->landWorkerUsers ? $plantingLocation->landWorkerUsers->pluck('user_id')->all() : []));
    $selectedLandWorkerRoles = collect(old('land_worker_roles', $plantingLocation->landWorkerUsers ? $plantingLocation->landWorkerUsers->map(fn($u) => $u->role ?? 'petugas_lapangan')->all() : []));
    $landStatusValue = old('land_status', $plantingLocation->land_status);
    $ownershipStatusValue = old('ownership_status', $plantingLocation->ownership_status);
    $waterSourceValue = old('water_source', $plantingLocation->water_source);
    $soilTypeValue = old('soil_type', $plantingLocation->soil_type);
    $lightConditionValue = old('light_condition', $plantingLocation->light_condition);

    $landStatusPreset = ['Tersedia', 'Ditanami'];
    $ownershipPreset = ['Milik Sendiri', 'Sewa', 'Milik Pemerintah'];
    $waterPreset = ['Irigasi', 'Tadah Hujan'];
    $soilPreset = ['Aluvial', 'Latosol', 'Litosol'];
    $lightPreset = ['sinar_matahari_penuh','sinar_matahari_penuh_hingga_sebagian','sinar_matahari_sebagian','matahari_hingga_setengah_teduh','setengah_teduh','teduh_sepenuhnya'];
@endphp

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Edit Lokasi Penanaman</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('planting-locations.update', $plantingLocation) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nama Lokasi Penanaman <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $plantingLocation->name) }}" placeholder="Contoh: Lokasi Produksi Utama" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tipe Lokasi Penanaman</label>
                        <select name="location_type" id="location_type" class="form-select @error('location_type') is-invalid @enderror" required onchange="toggleLocationTypeCustom()">
                            <option value="">Pilih tipe lokasi penanaman</option>
                            <option value="lapangan" {{ old('location_type', $plantingLocation->location_type) == 'lapangan' ? 'selected' : '' }}>Lapangan</option>
                            <option value="sawah" {{ old('location_type', $plantingLocation->location_type) == 'sawah' ? 'selected' : '' }}>Sawah</option>
                            <option value="greenhouse" {{ old('location_type', $plantingLocation->location_type) == 'greenhouse' ? 'selected' : '' }}>Greenhouse</option>
                            <option value="grow_room" {{ old('location_type', $plantingLocation->location_type) == 'grow_room' ? 'selected' : '' }}>Grow Room</option>
                            <option value="padang_rumput" {{ old('location_type', $plantingLocation->location_type) == 'padang_rumput' ? 'selected' : '' }}>Padang Rumput</option>
                            <option value="petak_ternak" {{ old('location_type', $plantingLocation->location_type) == 'petak_ternak' ? 'selected' : '' }}>Petak Ternak</option>
                            <option value="lainnya" {{ old('location_type', $plantingLocation->location_type) == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('location_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div id="location_type_custom_container" class="mt-2" style="display: {{ old('location_type', $plantingLocation->location_type) == 'lainnya' ? 'block' : 'none' }};">
                            <input type="text" name="location_type_custom" id="location_type_custom" 
                                   class="form-control @error('location_type_custom') is-invalid @enderror" 
                                   value="{{ old('location_type_custom', $plantingLocation->location_type_custom) }}" 
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
                                   value="{{ old('map_size', $plantingLocation->map_size) }}" step="0.01" min="0" required>
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
                               value="{{ old('location_summary', $plantingLocation->location_summary) }}" placeholder="kota/ kabupaten" required>
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
                               value="{{ old('province', $plantingLocation->province) }}" placeholder="Provinsi" required>
                        <small class="text-muted">Provinsi <span class="text-danger">*</span></small>
                        @error('province')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="district" maxlength="100" class="form-control @error('district') is-invalid @enderror"
                               value="{{ old('district', $plantingLocation->district) }}" placeholder="Kecamatan" required>
                        <small class="text-muted">Kecamatan <span class="text-danger">*</span></small>
                        @error('district')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="village" maxlength="100" class="form-control @error('village') is-invalid @enderror"
                               value="{{ old('village', $plantingLocation->village) }}" placeholder="Desa/Kelurahan" required>
                        <small class="text-muted">Desa/Kelurahan <span class="text-danger">*</span></small>
                        @error('village')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Foto Lokasi Penanaman</label>
                <input type="file" name="primary_photo" class="form-control @error('primary_photo') is-invalid @enderror" accept="image/*">
                @error('primary_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted d-block mb-2">Format JPG/PNG, ukuran maksimal 5 MB.</small>
                @if($plantingLocation->primary_photo_path)
                    <div class="d-inline-flex align-items-center gap-3">
                        <img src="{{ Storage::disk('public')->url($plantingLocation->primary_photo_path) }}" alt="Foto Lokasi Penanaman" class="rounded" style="height: 80px; object-fit: cover;">
                        <span class="text-muted">Foto saat ini</span>
                    </div>
                @endif
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="mb-3">
                        <label class="form-label">Penempatan pekerja</label>
                        <p class="text-muted small mb-2">Penugasan lokasi diatur dari form akun user (lokasi penempatan).</p>
                        @forelse($plantingLocation->assignedUsers as $user)
                            <span class="badge bg-secondary me-1 mb-1">{{ $user->name }} ({{ $user->role_label ?? $user->role }})</span>
                        @empty
                            <span class="text-muted small">Belum ada user yang ditempatkan di lokasi ini.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Status Kepemilikan</label>
                        <select name="ownership_status" class="form-select @error('ownership_status') is-invalid @enderror" data-custom-target="#ownershipStatusCustom">
                            <option value="">Pilih status kepemilikan</option>
                            @foreach($ownershipPreset as $status)
                                <option value="{{ $status }}" {{ $ownershipStatusValue === $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                            <option value="_custom" {{ $ownershipStatusValue && !in_array($ownershipStatusValue, $ownershipPreset) ? 'selected' : '' }}>Lainnya (isi manual)</option>
                        </select>
                        @error('ownership_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <input type="text" id="ownershipStatusCustom" name="ownership_status_custom"
                               class="form-control mt-2 {{ $ownershipStatusValue && !in_array($ownershipStatusValue, $ownershipPreset) ? '' : 'd-none' }}"
                               value="{{ $ownershipStatusValue && !in_array($ownershipStatusValue, $ownershipPreset) ? $ownershipStatusValue : old('ownership_status_custom') }}"
                               placeholder="Tuliskan status kepemilikan">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Sumber Air</label>
                        <select name="water_source" class="form-select @error('water_source') is-invalid @enderror" data-custom-target="#waterSourceCustom">
                            <option value="">Pilih sumber air</option>
                            @foreach($waterPreset as $option)
                                <option value="{{ $option }}" {{ $waterSourceValue === $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                            <option value="_custom" {{ $waterSourceValue && !in_array($waterSourceValue, $waterPreset) ? 'selected' : '' }}>Lainnya (isi manual)</option>
                        </select>
                        @error('water_source')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <input type="text" id="waterSourceCustom" name="water_source_custom"
                               class="form-control mt-2 {{ $waterSourceValue && !in_array($waterSourceValue, $waterPreset) ? '' : 'd-none' }}"
                               value="{{ $waterSourceValue && !in_array($waterSourceValue, $waterPreset) ? $waterSourceValue : old('water_source_custom') }}"
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
                            @foreach($soilPreset as $option)
                                <option value="{{ $option }}" {{ $soilTypeValue === $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                            <option value="_custom" {{ $soilTypeValue && !in_array($soilTypeValue, $soilPreset) ? 'selected' : '' }}>Lainnya (isi manual)</option>
                        </select>
                        @error('soil_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <input type="text" id="soilTypeCustom" name="soil_type_custom"
                               class="form-control mt-2 {{ $soilTypeValue && !in_array($soilTypeValue, $soilPreset) ? '' : 'd-none' }}"
                               value="{{ $soilTypeValue && !in_array($soilTypeValue, $soilPreset) ? $soilTypeValue : old('soil_type_custom') }}"
                               placeholder="Tuliskan tipe tanah">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Ketinggian (MDPL)</label>
                        <div class="input-group">
                            <input type="number" name="elevation_masl" class="form-control @error('elevation_masl') is-invalid @enderror"
                                   value="{{ old('elevation_masl', $plantingLocation->elevation_masl) }}" step="1">
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
                        @php($formatValue = old('planting_format', $plantingLocation->planting_format))
                        <select name="planting_format" id="plantingFormatSelect" class="form-select @error('planting_format') is-invalid @enderror">
                            <option value="">Pilih format penanaman</option>
                            @foreach(\App\Models\PlantingLocation::FORMAT_PENANAMAN as $value => $label)
                                <option value="{{ $value }}" {{ $formatValue === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('planting_format')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div id="plantingFormatCustomWrapper" class="mt-2 {{ $formatValue === 'lainnya' ? '' : 'd-none' }}">
                            <input type="text" name="planting_format_custom" class="form-control @error('planting_format_custom') is-invalid @enderror"
                                   value="{{ $formatValue === 'lainnya' ? old('planting_format_custom', $plantingLocation->planting_format_custom) : '' }}" placeholder="Tuliskan format penanaman lainnya">
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
                            <option value="sinar_matahari_penuh" {{ $lightConditionValue == 'sinar_matahari_penuh' ? 'selected' : '' }}>Sinar Matahari Penuh</option>
                            <option value="sinar_matahari_penuh_hingga_sebagian" {{ $lightConditionValue == 'sinar_matahari_penuh_hingga_sebagian' ? 'selected' : '' }}>Sinar Matahari Penuh hingga Sebagian</option>
                            <option value="sinar_matahari_sebagian" {{ $lightConditionValue == 'sinar_matahari_sebagian' ? 'selected' : '' }}>Sinar Matahari Sebagian</option>
                            <option value="matahari_hingga_setengah_teduh" {{ $lightConditionValue == 'matahari_hingga_setengah_teduh' ? 'selected' : '' }}>Matahari hingga Setengah Teduh</option>
                            <option value="setengah_teduh" {{ $lightConditionValue == 'setengah_teduh' ? 'selected' : '' }}>Setengah Teduh</option>
                            <option value="teduh_sepenuhnya" {{ $lightConditionValue == 'teduh_sepenuhnya' ? 'selected' : '' }}>Teduh Sepenuhnya</option>
                            <option value="_custom" {{ $lightConditionValue && !in_array($lightConditionValue, $lightPreset) ? 'selected' : '' }}>Lainnya (isi manual)</option>
                        </select>
                        @error('light_condition')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <input type="text" id="lightConditionCustom" name="light_condition_custom"
                               class="form-control mt-2 {{ $lightConditionValue && !in_array($lightConditionValue, $lightPreset) ? '' : 'd-none' }}"
                               value="{{ $lightConditionValue && !in_array($lightConditionValue, $lightPreset) ? $lightConditionValue : old('light_condition_custom') }}"
                               placeholder="Tuliskan kondisi cahaya">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                          rows="3" placeholder="Masukkan deskripsi lokasi penanaman">{{ old('description', $plantingLocation->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @include('planting.planting-locations._lahan-repeater')

            <div class="d-flex justify-content-end gap-2">
                <a class="btn btn-secondary" href="{{ route('planting-locations.show', $plantingLocation) }}">Batal</a>
                <button class="btn btn-success" type="submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@include('planting.planting-locations._location-maps-scripts')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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
    const roleLabels = { petugas_lapangan: 'Petugas Lapangan', penangkar: 'Penangkar' };

    function addLandWorkerUser() {
        const selectedOption = landWorkerUserSelect.options[landWorkerUserSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            alert('Silakan pilih user terlebih dahulu.');
            return;
        }

        const userId = selectedOption.value;
        const userName = selectedOption.dataset.name;
        const role = landWorkerRoleSelect ? landWorkerRoleSelect.value : 'petugas_lapangan';
        const roleLabel = roleLabels[role] || roleLabels.petugas_lapangan;

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
toggleLocationTypeCustom();
</script>
@endpush
@endsection








