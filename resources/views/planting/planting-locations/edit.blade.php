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
    $selectedLandManagers = collect(old('land_manager_user_ids', $plantingLocation->landManagerUsers ? $plantingLocation->landManagerUsers->pluck('user_id')->all() : []));
    $selectedLandWorkers = collect(old('land_worker_user_ids', $plantingLocation->landWorkerUsers ? $plantingLocation->landWorkerUsers->pluck('user_id')->all() : []));
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
                        <label class="form-label">Nama Lahan</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $plantingLocation->name) }}" placeholder="Contoh: Lahan Produksi Utama" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tipe Lahan</label>
                        <select name="location_type" id="location_type" class="form-select @error('location_type') is-invalid @enderror" required onchange="toggleLocationTypeCustom()">
                            <option value="">Pilih tipe lahan</option>
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
                                   placeholder="Masukkan tipe lahan lainnya">
                            @error('location_type_custom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Luas Lahan (Ha)</label>
                        <div class="input-group">
                            <input type="number" name="map_size" class="form-control @error('map_size') is-invalid @enderror" 
                                   value="{{ old('map_size', $plantingLocation->map_size) }}" step="0.01" min="0">
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
                        <label class="form-label">Lokasi</label>
                        <input type="text" name="location_summary" class="form-control @error('location_summary') is-invalid @enderror"
                               value="{{ old('location_summary', $plantingLocation->location_summary) }}" placeholder="Contoh: Blok A, Sektor Timur">
                        @error('location_summary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Alamat Administratif (Desa, Kecamatan, ...)</label>
                <textarea name="administrative_address" class="form-control @error('administrative_address') is-invalid @enderror"
                          rows="2" placeholder="Contoh: Desa Sukamaju, Kec. Seluma, Kab. Seluma, Prov. Bengkulu">{{ old('administrative_address', $plantingLocation->administrative_address) }}</textarea>
                @error('administrative_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label">Foto Lahan</label>
                <input type="file" name="primary_photo" class="form-control @error('primary_photo') is-invalid @enderror" accept="image/*">
                @error('primary_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted d-block mb-2">Format JPG/PNG, ukuran maksimal 5 MB.</small>
                @if($plantingLocation->primary_photo_path)
                    <div class="d-inline-flex align-items-center gap-3">
                        <img src="{{ Storage::disk('public')->url($plantingLocation->primary_photo_path) }}" alt="Foto Lahan" class="rounded" style="height: 80px; object-fit: cover;">
                        <span class="text-muted">Foto saat ini</span>
                    </div>
                @endif
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Status Lahan</label>
                        <select name="land_status" class="form-select @error('land_status') is-invalid @enderror" data-custom-target="#landStatusCustom">
                            <option value="">Pilih status</option>
                            @foreach($landStatusPreset as $status)
                                <option value="{{ $status }}" {{ $landStatusValue === $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                            <option value="_custom" {{ $landStatusValue && !in_array($landStatusValue, $landStatusPreset) ? 'selected' : '' }}>Lainnya (isi manual)</option>
                        </select>
                        @error('land_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <input type="text" id="landStatusCustom" name="land_status_custom"
                               class="form-control mt-2 {{ $landStatusValue && !in_array($landStatusValue, $landStatusPreset) ? '' : 'd-none' }}"
                               value="{{ $landStatusValue && !in_array($landStatusValue, $landStatusPreset) ? $landStatusValue : old('land_status_custom') }}"
                               placeholder="Tuliskan status lahan">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Penanggung Jawab Lahan</label>
                        <div class="d-flex gap-2 mb-2">
                            <select id="landManagerUserSelect" class="form-select @error('land_manager_user_ids') is-invalid @enderror">
                                <option value="">Pilih user...</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->user_id }}" data-name="{{ $user->name }}" data-email="{{ $user->email ?? '' }}" data-role="{{ $user->role_label ?? '' }}">
                                        {{ $user->name }}@if($user->role) - {{ $user->role_label }}@endif
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-primary" id="addLandManagerUserBtn">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                        </div>
                        <div id="selectedLandManagerUsers" class="mb-2">
                            @php
                                $oldLandManagerUserIds = collect(old('land_manager_user_ids', $plantingLocation->landManagerUsers ? $plantingLocation->landManagerUsers->pluck('user_id')->all() : []))->filter();
                            @endphp
                            @foreach($oldLandManagerUserIds as $userId)
                                @php
                                    $user = $users->firstWhere('user_id', $userId);
                                @endphp
                                @if($user)
                                    <div class="selected-user-item mb-2 p-2 border rounded d-flex justify-content-between align-items-center" data-user-id="{{ $user->user_id }}">
                                        <span>
                                            <strong>{{ $user->name }}</strong>
                                            @if($user->role)
                                                <small class="text-muted"> - {{ $user->role_label }}</small>
                                            @endif
                                        </span>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-user" title="Hapus">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <input type="hidden" name="land_manager_user_ids[]" value="{{ $user->user_id }}">
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        @error('land_manager_user_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        @error('land_manager_user_ids.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <small class="text-muted">Pilih user dari dropdown dan klik "Tambah" untuk menambahkan penanggung jawab lahan.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Pekerja Lahan</label>
                        <div class="d-flex gap-2 mb-2">
                            <select id="landWorkerUserSelect" class="form-select @error('land_worker_user_ids') is-invalid @enderror">
                                <option value="">Pilih user...</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->user_id }}" data-name="{{ $user->name }}" data-email="{{ $user->email ?? '' }}" data-role="{{ $user->role_label ?? '' }}">
                                        {{ $user->name }}@if($user->role) - {{ $user->role_label }}@endif
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-primary" id="addLandWorkerUserBtn">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                        </div>
                        <div id="selectedLandWorkerUsers" class="mb-2">
                            @php
                                $oldLandWorkerUserIds = collect(old('land_worker_user_ids', $plantingLocation->landWorkerUsers ? $plantingLocation->landWorkerUsers->pluck('user_id')->all() : []))->filter();
                            @endphp
                            @foreach($oldLandWorkerUserIds as $userId)
                                @php
                                    $user = $users->firstWhere('user_id', $userId);
                                @endphp
                                @if($user)
                                    <div class="selected-user-item mb-2 p-2 border rounded d-flex justify-content-between align-items-center" data-user-id="{{ $user->user_id }}">
                                        <span>
                                            <strong>{{ $user->name }}</strong>
                                            @if($user->role)
                                                <small class="text-muted"> - {{ $user->role_label }}</small>
                                            @endif
                                        </span>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-user" title="Hapus">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <input type="hidden" name="land_worker_user_ids[]" value="{{ $user->user_id }}">
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        @error('land_worker_user_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        @error('land_worker_user_ids.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <small class="text-muted">Pilih user dari dropdown dan klik "Tambah" untuk menambahkan pekerja lahan.</small>
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
            <div class="mb-4">
                <label class="form-label">Format Penanaman</label>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3 planting-format-card" data-format="ditanam_dalam_petak">
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="planting_format" value="ditanam_dalam_petak" 
                                           id="format_beds" {{ old('planting_format', $plantingLocation->planting_format) == 'ditanam_dalam_petak' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="format_beds">
                                        Ditanam dalam Petak/ Beds
                                    </label>
                                </div>
                                <p class="text-muted small mt-2">
                                    Penanaman dengan petak atau beds yang berbeda untuk tanaman yang berbeda.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3 planting-format-card" data-format="cover_crop">
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="planting_format" value="cover_crop" 
                                           id="format_cover" {{ old('planting_format', $plantingLocation->planting_format) == 'cover_crop' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="format_cover">
                                        Tanaman Penutup / Cover Crop
                                    </label>
                                </div>
                                <p class="text-muted small mt-2">
                                    Penanaman dengan tanaman penutup atau cover crop.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3 planting-format-card" data-format="row_crop">
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="planting_format" value="row_crop" 
                                           id="format_row" {{ old('planting_format', $plantingLocation->planting_format) == 'row_crop' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="format_row">
                                        Tanaman Baris / Row Crop
                                    </label>
                                </div>
                                <p class="text-muted small mt-2">
                                    Tanaman yang ditanam berbaris.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3 planting-format-card" data-format="lainnya">
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="planting_format" value="lainnya" 
                                           id="format_other" {{ old('planting_format', $plantingLocation->planting_format) == 'lainnya' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="format_other">
                                        Lainnya
                                    </label>
                                </div>
                                <p class="text-muted small mt-2">
                                    Penanaman dengan metode lain seperti rak, aquaponik, tray, dll.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                @error('planting_format')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <div id="plantingFormatCustomWrapper" class="mt-3 {{ old('planting_format', $plantingLocation->planting_format) === 'lainnya' ? '' : 'd-none' }}">
                    <label class="form-label">Format Penanaman (Lainnya)</label>
                    <input type="text" name="planting_format_custom" class="form-control @error('planting_format_custom') is-invalid @enderror"
                           value="{{ old('planting_format', $plantingLocation->planting_format) === 'lainnya' ? old('planting_format_custom', $plantingLocation->planting_format_custom) : '' }}" placeholder="Tuliskan format penanaman">
                    @error('planting_format_custom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <!-- Bed Details -->
            <div id="bedDetails" class="mb-4" style="display: none;">
                <h6>Detail Petak</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Jumlah Petak</label>
                            <input type="number" name="num_beds" class="form-control @error('num_beds') is-invalid @enderror" 
                                   value="{{ old('num_beds', $plantingLocation->num_beds) }}" min="1">
                            @error('num_beds')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Panjang Petak</label>
                            <div class="input-group">
                                <input type="number" name="bed_length_m" class="form-control @error('bed_length_m') is-invalid @enderror" 
                                       value="{{ old('bed_length_m', $plantingLocation->bed_length_m) }}" step="0.1" min="0">
                                <span class="input-group-text">m</span>
                            </div>
                            @error('bed_length_m')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Lebar Petak</label>
                            <div class="input-group">
                                <input type="number" name="bed_width_m" class="form-control @error('bed_width_m') is-invalid @enderror" 
                                       value="{{ old('bed_width_m', $plantingLocation->bed_width_m) }}" step="0.1" min="0">
                                <span class="input-group-text">m</span>
                            </div>
                            @error('bed_width_m')<div class="invalid-feedback">{{ $message }}</div>@enderror
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

            <div class="d-flex justify-content-end gap-2">
                <a class="btn btn-secondary" href="{{ route('planting-locations.show', $plantingLocation) }}">Batal</a>
                <button class="btn btn-success" type="submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const formatCards = document.querySelectorAll('.planting-format-card');
    const bedDetails = document.getElementById('bedDetails');
    const plantingFormatCustomWrapper = document.getElementById('plantingFormatCustomWrapper');
    
    formatCards.forEach(card => {
        card.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            
            formatCards.forEach(c => c.classList.remove('border-primary'));
            this.classList.add('border-primary');
            
            bedDetails.style.display = radio.value === 'ditanam_dalam_petak' ? 'block' : 'none';
            if (plantingFormatCustomWrapper) {
                plantingFormatCustomWrapper.classList.toggle('d-none', radio.value !== 'lainnya');
            }
        });
    });
    
    const checkedFormat = document.querySelector('input[name="planting_format"]:checked');
    if (checkedFormat) {
        checkedFormat.closest('.planting-format-card').classList.add('border-primary');
        bedDetails.style.display = checkedFormat.value === 'ditanam_dalam_petak' ? 'block' : 'none';
        if (plantingFormatCustomWrapper) {
            plantingFormatCustomWrapper.classList.toggle('d-none', checkedFormat.value !== 'lainnya');
        }
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

    // Handle land manager user selection
    const landManagerUserSelect = document.getElementById('landManagerUserSelect');
    const addLandManagerUserBtn = document.getElementById('addLandManagerUserBtn');
    const selectedLandManagerUsers = document.getElementById('selectedLandManagerUsers');

    function getSelectedLandManagerUserIds() {
        return Array.from(selectedLandManagerUsers.querySelectorAll('input[type="hidden"]'))
            .map(input => input.value);
    }

    function updateLandManagerUserDropdown() {
        const selectedIds = getSelectedLandManagerUserIds();
        Array.from(landManagerUserSelect.options).forEach(option => {
            if (option.value && selectedIds.includes(option.value)) {
                option.style.display = 'none';
            } else {
                option.style.display = '';
            }
        });
    }

    function addLandManagerUser() {
        const selectedOption = landManagerUserSelect.options[landManagerUserSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            alert('Silakan pilih user terlebih dahulu.');
            return;
        }

        const userId = selectedOption.value;
        const userName = selectedOption.dataset.name;
        const userRole = selectedOption.dataset.role || '';

        if (getSelectedLandManagerUserIds().includes(userId)) {
            alert('User ini sudah ditambahkan.');
            return;
        }

        const userItem = document.createElement('div');
        userItem.className = 'selected-user-item mb-2 p-2 border rounded d-flex justify-content-between align-items-center';
        userItem.setAttribute('data-user-id', userId);
        userItem.innerHTML = `
            <span>
                <strong>${userName}</strong>
                ${userRole ? `<small class="text-muted"> - ${userRole}</small>` : ''}
            </span>
            <button type="button" class="btn btn-sm btn-outline-danger remove-user" title="Hapus">
                <i class="fas fa-times"></i>
            </button>
            <input type="hidden" name="land_manager_user_ids[]" value="${userId}">
        `;

        userItem.querySelector('.remove-user').addEventListener('click', function() {
            userItem.remove();
            updateLandManagerUserDropdown();
        });

        selectedLandManagerUsers.appendChild(userItem);
        landManagerUserSelect.value = '';
        updateLandManagerUserDropdown();
    }

    if (addLandManagerUserBtn) {
        addLandManagerUserBtn.addEventListener('click', addLandManagerUser);
    }

    if (landManagerUserSelect) {
        landManagerUserSelect.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addLandManagerUser();
            }
        });
    }

    selectedLandManagerUsers.querySelectorAll('.remove-user').forEach(btn => {
        btn.addEventListener('click', function() {
            const userItem = this.closest('.selected-user-item');
            if (userItem) {
                userItem.remove();
                updateLandManagerUserDropdown();
            }
        });
    });

    updateLandManagerUserDropdown();

    // Handle land worker user selection
    const landWorkerUserSelect = document.getElementById('landWorkerUserSelect');
    const addLandWorkerUserBtn = document.getElementById('addLandWorkerUserBtn');
    const selectedLandWorkerUsers = document.getElementById('selectedLandWorkerUsers');

    function getSelectedLandWorkerUserIds() {
        return Array.from(selectedLandWorkerUsers.querySelectorAll('input[type="hidden"]'))
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

    function addLandWorkerUser() {
        const selectedOption = landWorkerUserSelect.options[landWorkerUserSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            alert('Silakan pilih user terlebih dahulu.');
            return;
        }

        const userId = selectedOption.value;
        const userName = selectedOption.dataset.name;
        const userRole = selectedOption.dataset.role || '';

        if (getSelectedLandWorkerUserIds().includes(userId)) {
            alert('User ini sudah ditambahkan.');
            return;
        }

        const userItem = document.createElement('div');
        userItem.className = 'selected-user-item mb-2 p-2 border rounded d-flex justify-content-between align-items-center';
        userItem.setAttribute('data-user-id', userId);
        userItem.innerHTML = `
            <span>
                <strong>${userName}</strong>
                ${userRole ? `<small class="text-muted"> - ${userRole}</small>` : ''}
            </span>
            <button type="button" class="btn btn-sm btn-outline-danger remove-user" title="Hapus">
                <i class="fas fa-times"></i>
            </button>
            <input type="hidden" name="land_worker_user_ids[]" value="${userId}">
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








