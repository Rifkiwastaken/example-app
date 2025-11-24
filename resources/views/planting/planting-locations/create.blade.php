@extends('layouts.app')

@section('title', 'Tambah Lokasi Penanaman - SIBIT')

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
        <form method="POST" action="{{ route('planting-locations.store') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nama Lahan</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" placeholder="Example: Northwest Field" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Internal Id</label>
                        <div class="input-group">
                            <input type="text" name="internal_id" class="form-control @error('internal_id') is-invalid @enderror" 
                                   value="{{ old('internal_id') }}" placeholder="Example: F001">
                            <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                        </div>
                        @error('internal_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Electronic Id</label>
                        <input type="text" name="electronic_id" class="form-control @error('electronic_id') is-invalid @enderror" 
                               value="{{ old('electronic_id') }}">
                        @error('electronic_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tipe Lahan</label>
                        <select name="location_type" class="form-select @error('location_type') is-invalid @enderror" required>
                            <option value="">Pilih tipe lahan</option>
                            <option value="lapangan" {{ old('location_type') == 'lapangan' ? 'selected' : '' }}>Lapangan</option>
                            <option value="greenhouse" {{ old('location_type') == 'greenhouse' ? 'selected' : '' }}>Greenhouse</option>
                            <option value="grow_room" {{ old('location_type') == 'grow_room' ? 'selected' : '' }}>Grow Room</option>
                            <option value="padang_rumput" {{ old('location_type') == 'padang_rumput' ? 'selected' : '' }}>Padang Rumput</option>
                            <option value="petak_ternak" {{ old('location_type') == 'petak_ternak' ? 'selected' : '' }}>Petak Ternak</option>
                            <option value="lainnya" {{ old('location_type') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('location_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Lokasi Penanaman (Master)</label>
                        <select name="location_id" class="form-select @error('location_id') is-invalid @enderror" required>
                            <option value="">Pilih lokasi lahan</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Luas Lahan (Ha)</label>
                        <div class="input-group">
                            <input type="number" name="map_size" class="form-control @error('map_size') is-invalid @enderror" 
                                   value="{{ old('map_size') }}" step="0.01" min="0">
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
                               value="{{ old('location_summary') }}" placeholder="Contoh: Blok A, Sektor Timur">
                        @error('location_summary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Link Google Maps</label>
                        <input type="url" name="google_maps_link" class="form-control @error('google_maps_link') is-invalid @enderror"
                               value="{{ old('google_maps_link') }}" placeholder="https://maps.google.com/...">
                        @error('google_maps_link')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Alamat Administratif (Desa, Kecamatan, ...)</label>
                <textarea name="administrative_address" class="form-control @error('administrative_address') is-invalid @enderror"
                          rows="2" placeholder="Contoh: Desa Sukamaju, Kec. Seluma, Kab. Seluma, Prov. Bengkulu">{{ old('administrative_address') }}</textarea>
                @error('administrative_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label">Foto Lahan</label>
                <input type="file" name="primary_photo" class="form-control @error('primary_photo') is-invalid @enderror" accept="image/*">
                @error('primary_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">Format JPG/PNG, ukuran maksimal 5 MB.</small>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Penanggung Jawab</label>
                        <div class="d-flex gap-2 mb-2">
                            <select id="contactSelect" class="form-select @error('responsible_contact_ids') is-invalid @enderror">
                                <option value="">Pilih kontak...</option>
                                @foreach($contacts as $contact)
                                    <option value="{{ $contact->id }}" data-name="{{ $contact->full_name }}" data-org="{{ $contact->organization ?? '' }}">
                                        {{ $contact->full_name }}@if($contact->organization) - {{ $contact->organization }}@endif
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-primary" id="addContactBtn">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                        </div>
                        <div id="selectedContacts" class="mb-2">
                            @php
                                $oldContactIds = collect(old('responsible_contact_ids', []))->filter();
                            @endphp
                            @foreach($oldContactIds as $contactId)
                                @php
                                    $contact = $contacts->firstWhere('id', $contactId);
                                @endphp
                                @if($contact)
                                    <div class="selected-contact-item mb-2 p-2 border rounded d-flex justify-content-between align-items-center" data-contact-id="{{ $contact->id }}">
                                        <span>
                                            <strong>{{ $contact->full_name }}</strong>
                                            @if($contact->organization)
                                                <small class="text-muted"> - {{ $contact->organization }}</small>
                                            @endif
                                        </span>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-contact" title="Hapus">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <input type="hidden" name="responsible_contact_ids[]" value="{{ $contact->id }}">
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        @error('responsible_contact_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        @error('responsible_contact_ids.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <small class="text-muted">Pilih kontak dari dropdown dan klik "Tambah" untuk menambahkan penanggung jawab.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Status Lahan</label>
                        <select name="land_status" class="form-select @error('land_status') is-invalid @enderror" data-custom-target="#landStatusCustom">
                            <option value="">Pilih status</option>
                            <option value="Tersedia" {{ old('land_status') == 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                            <option value="Ditanami" {{ old('land_status') == 'Ditanami' ? 'selected' : '' }}>Ditanami</option>
                            <option value="_custom" {{ old('land_status') && !in_array(old('land_status'), ['Tersedia','Ditanami']) ? 'selected' : '' }}>Lainnya (isi manual)</option>
                        </select>
                        @error('land_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <input type="text" id="landStatusCustom" name="land_status_custom"
                               class="form-control mt-2 {{ old('land_status') && !in_array(old('land_status'), ['Tersedia','Ditanami']) ? '' : 'd-none' }}"
                               value="{{ old('land_status') && !in_array(old('land_status'), ['Tersedia','Ditanami']) ? old('land_status') : old('land_status_custom') }}"
                               placeholder="Tuliskan status lahan">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Penanggung Jawab Lahan</label>
                        <div class="d-flex gap-2 mb-2">
                            <select id="landManagerContactSelect" class="form-select @error('land_manager_contact_ids') is-invalid @enderror">
                                <option value="">Pilih kontak...</option>
                                @foreach($contacts as $contact)
                                    <option value="{{ $contact->id }}" data-name="{{ $contact->full_name }}" data-org="{{ $contact->organization ?? '' }}">
                                        {{ $contact->full_name }}@if($contact->organization) - {{ $contact->organization }}@endif
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-primary" id="addLandManagerContactBtn">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                        </div>
                        <div id="selectedLandManagerContacts" class="mb-2">
                            @php
                                $oldLandManagerContactIds = collect(old('land_manager_contact_ids', []))->filter();
                            @endphp
                            @foreach($oldLandManagerContactIds as $contactId)
                                @php
                                    $contact = $contacts->firstWhere('id', $contactId);
                                @endphp
                                @if($contact)
                                    <div class="selected-contact-item mb-2 p-2 border rounded d-flex justify-content-between align-items-center" data-contact-id="{{ $contact->id }}">
                                        <span>
                                            <strong>{{ $contact->full_name }}</strong>
                                            @if($contact->organization)
                                                <small class="text-muted"> - {{ $contact->organization }}</small>
                                            @endif
                                        </span>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-contact" title="Hapus">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <input type="hidden" name="land_manager_contact_ids[]" value="{{ $contact->id }}">
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        @error('land_manager_contact_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        @error('land_manager_contact_ids.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <small class="text-muted">Pilih kontak dari dropdown dan klik "Tambah" untuk menambahkan penanggung jawab lahan.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Pekerja Lahan</label>
                        <div class="d-flex gap-2 mb-2">
                            <select id="landWorkerContactSelect" class="form-select @error('land_worker_contact_ids') is-invalid @enderror">
                                <option value="">Pilih kontak...</option>
                                @foreach($contacts as $contact)
                                    <option value="{{ $contact->id }}" data-name="{{ $contact->full_name }}" data-org="{{ $contact->organization ?? '' }}">
                                        {{ $contact->full_name }}@if($contact->organization) - {{ $contact->organization }}@endif
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-primary" id="addLandWorkerContactBtn">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                        </div>
                        <div id="selectedLandWorkerContacts" class="mb-2">
                            @php
                                $oldLandWorkerContactIds = collect(old('land_worker_contact_ids', []))->filter();
                            @endphp
                            @foreach($oldLandWorkerContactIds as $contactId)
                                @php
                                    $contact = $contacts->firstWhere('id', $contactId);
                                @endphp
                                @if($contact)
                                    <div class="selected-contact-item mb-2 p-2 border rounded d-flex justify-content-between align-items-center" data-contact-id="{{ $contact->id }}">
                                        <span>
                                            <strong>{{ $contact->full_name }}</strong>
                                            @if($contact->organization)
                                                <small class="text-muted"> - {{ $contact->organization }}</small>
                                            @endif
                                        </span>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-contact" title="Hapus">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <input type="hidden" name="land_worker_contact_ids[]" value="{{ $contact->id }}">
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        @error('land_worker_contact_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        @error('land_worker_contact_ids.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <small class="text-muted">Pilih kontak dari dropdown dan klik "Tambah" untuk menambahkan pekerja lahan.</small>
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
            <div class="mb-4">
                <label class="form-label">Format Penanaman</label>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3 planting-format-card" data-format="ditanam_dalam_petak">
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="planting_format" value="ditanam_dalam_petak" 
                                           id="format_beds" {{ old('planting_format') == 'ditanam_dalam_petak' ? 'checked' : '' }}>
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
                                           id="format_cover" {{ old('planting_format') == 'cover_crop' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="format_cover">
                                        Tanaman Penutup/Cover Crop
                                    </label>
                                </div>
                                <p class="text-muted small mt-2">
                                    Penanaman dengan tanaman penutup atau cover crop
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
                                           id="format_row" {{ old('planting_format') == 'row_crop' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="format_row">
                                        Tanaman Baris/ Row Crop
                                    </label>
                                </div>
                                <p class="text-muted small mt-2">
                                    Tanaman yang di tanam dalam baris
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3 planting-format-card" data-format="lainnya">
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="planting_format" value="lainnya" 
                                           id="format_other" {{ old('planting_format') == 'lainnya' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="format_other">
                                        Lainnya
                                    </label>
                                </div>
                                <p class="text-muted small mt-2">
                                    Penanaman dengan metode lainya seperti dalam rak, aquaponik, tray, dll.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                @error('planting_format')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <div id="plantingFormatCustomWrapper" class="mt-3 {{ old('planting_format') === 'lainnya' ? '' : 'd-none' }}">
                    <label class="form-label">Format Penanaman (Lainnya)</label>
                    <input type="text" name="planting_format_custom" class="form-control @error('planting_format_custom') is-invalid @enderror"
                           value="{{ old('planting_format') === 'lainnya' ? old('planting_format_custom') : '' }}" placeholder="Tuliskan format penanaman">
                    @error('planting_format_custom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <!-- Bed Details (only for "ditanam_dalam_petak") -->
            <div id="bedDetails" class="mb-4" style="display: none;">
                <h6>Detail Petak</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Jumlah Petak</label>
                            <input type="number" name="num_beds" class="form-control @error('num_beds') is-invalid @enderror" 
                                   value="{{ old('num_beds', 5) }}" min="1">
                            @error('num_beds')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Panjang Petak</label>
                            <div class="input-group">
                                <input type="number" name="bed_length_m" class="form-control @error('bed_length_m') is-invalid @enderror" 
                                       value="{{ old('bed_length_m', 100) }}" step="0.1" min="0">
                                <span class="input-group-text">Meters</span>
                            </div>
                            @error('bed_length_m')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Lebar Petak</label>
                            <div class="input-group">
                                <input type="number" name="bed_width_m" class="form-control @error('bed_width_m') is-invalid @enderror" 
                                       value="{{ old('bed_width_m', 3) }}" step="0.1" min="0">
                                <span class="input-group-text">Meters</span>
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

            <div class="d-flex justify-content-end gap-2">
                <a class="btn btn-secondary" href="{{ route('planting-locations.index') }}">Cancel</a>
                <button class="btn btn-success" type="submit">Save</button>
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
            
            // Update card styling
            formatCards.forEach(c => c.classList.remove('border-primary'));
            this.classList.add('border-primary');
            
            // Show/hide bed details
            if (radio.value === 'ditanam_dalam_petak') {
                bedDetails.style.display = 'block';
            } else {
                bedDetails.style.display = 'none';
            }

            if (plantingFormatCustomWrapper) {
                plantingFormatCustomWrapper.classList.toggle('d-none', radio.value !== 'lainnya');
            }
        });
    });
    
    // Initialize on page load
    const checkedFormat = document.querySelector('input[name="planting_format"]:checked');
    if (checkedFormat) {
        checkedFormat.closest('.planting-format-card').classList.add('border-primary');
        if (checkedFormat.value === 'ditanam_dalam_petak') {
            bedDetails.style.display = 'block';
        }
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

    // Handle contact selection
    const contactSelect = document.getElementById('contactSelect');
    const addContactBtn = document.getElementById('addContactBtn');
    const selectedContacts = document.getElementById('selectedContacts');

    function getSelectedContactIds() {
        return Array.from(selectedContacts.querySelectorAll('input[type="hidden"]'))
            .map(input => input.value);
    }

    function updateContactDropdown() {
        const selectedIds = getSelectedContactIds();
        Array.from(contactSelect.options).forEach(option => {
            if (option.value && selectedIds.includes(option.value)) {
                option.style.display = 'none';
            } else {
                option.style.display = '';
            }
        });
    }

    function addContact() {
        const selectedOption = contactSelect.options[contactSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            alert('Silakan pilih kontak terlebih dahulu.');
            return;
        }

        const contactId = selectedOption.value;
        const contactName = selectedOption.dataset.name;
        const contactOrg = selectedOption.dataset.org || '';

        // Check if already added
        if (getSelectedContactIds().includes(contactId)) {
            alert('Kontak ini sudah ditambahkan.');
            return;
        }

        // Create contact item
        const contactItem = document.createElement('div');
        contactItem.className = 'selected-contact-item mb-2 p-2 border rounded d-flex justify-content-between align-items-center';
        contactItem.setAttribute('data-contact-id', contactId);
        contactItem.innerHTML = `
            <span>
                <strong>${contactName}</strong>
                ${contactOrg ? `<small class="text-muted"> - ${contactOrg}</small>` : ''}
            </span>
            <button type="button" class="btn btn-sm btn-outline-danger remove-contact" title="Hapus">
                <i class="fas fa-times"></i>
            </button>
            <input type="hidden" name="responsible_contact_ids[]" value="${contactId}">
        `;

        // Add remove event listener
        contactItem.querySelector('.remove-contact').addEventListener('click', function() {
            contactItem.remove();
            updateContactDropdown();
        });

        selectedContacts.appendChild(contactItem);
        contactSelect.value = '';
        updateContactDropdown();
    }

    // Add contact button click
    if (addContactBtn) {
        addContactBtn.addEventListener('click', addContact);
    }

    // Add contact on Enter key in select
    if (contactSelect) {
        contactSelect.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addContact();
            }
        });
    }

    // Remove contact buttons
    selectedContacts.querySelectorAll('.remove-contact').forEach(btn => {
        btn.addEventListener('click', function() {
            const contactItem = this.closest('.selected-contact-item');
            if (contactItem) {
                contactItem.remove();
                updateContactDropdown();
            }
        });
    });

    // Initialize dropdown state
    updateContactDropdown();

    // Handle land manager contact selection
    const landManagerContactSelect = document.getElementById('landManagerContactSelect');
    const addLandManagerContactBtn = document.getElementById('addLandManagerContactBtn');
    const selectedLandManagerContacts = document.getElementById('selectedLandManagerContacts');

    function getSelectedLandManagerContactIds() {
        return Array.from(selectedLandManagerContacts.querySelectorAll('input[type="hidden"]'))
            .map(input => input.value);
    }

    function updateLandManagerContactDropdown() {
        const selectedIds = getSelectedLandManagerContactIds();
        Array.from(landManagerContactSelect.options).forEach(option => {
            if (option.value && selectedIds.includes(option.value)) {
                option.style.display = 'none';
            } else {
                option.style.display = '';
            }
        });
    }

    function addLandManagerContact() {
        const selectedOption = landManagerContactSelect.options[landManagerContactSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            alert('Silakan pilih kontak terlebih dahulu.');
            return;
        }

        const contactId = selectedOption.value;
        const contactName = selectedOption.dataset.name;
        const contactOrg = selectedOption.dataset.org || '';

        if (getSelectedLandManagerContactIds().includes(contactId)) {
            alert('Kontak ini sudah ditambahkan.');
            return;
        }

        const contactItem = document.createElement('div');
        contactItem.className = 'selected-contact-item mb-2 p-2 border rounded d-flex justify-content-between align-items-center';
        contactItem.setAttribute('data-contact-id', contactId);
        contactItem.innerHTML = `
            <span>
                <strong>${contactName}</strong>
                ${contactOrg ? `<small class="text-muted"> - ${contactOrg}</small>` : ''}
            </span>
            <button type="button" class="btn btn-sm btn-outline-danger remove-contact" title="Hapus">
                <i class="fas fa-times"></i>
            </button>
            <input type="hidden" name="land_manager_contact_ids[]" value="${contactId}">
        `;

        contactItem.querySelector('.remove-contact').addEventListener('click', function() {
            contactItem.remove();
            updateLandManagerContactDropdown();
        });

        selectedLandManagerContacts.appendChild(contactItem);
        landManagerContactSelect.value = '';
        updateLandManagerContactDropdown();
    }

    if (addLandManagerContactBtn) {
        addLandManagerContactBtn.addEventListener('click', addLandManagerContact);
    }

    if (landManagerContactSelect) {
        landManagerContactSelect.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addLandManagerContact();
            }
        });
    }

    selectedLandManagerContacts.querySelectorAll('.remove-contact').forEach(btn => {
        btn.addEventListener('click', function() {
            const contactItem = this.closest('.selected-contact-item');
            if (contactItem) {
                contactItem.remove();
                updateLandManagerContactDropdown();
            }
        });
    });

    updateLandManagerContactDropdown();

    // Handle land worker contact selection
    const landWorkerContactSelect = document.getElementById('landWorkerContactSelect');
    const addLandWorkerContactBtn = document.getElementById('addLandWorkerContactBtn');
    const selectedLandWorkerContacts = document.getElementById('selectedLandWorkerContacts');

    function getSelectedLandWorkerContactIds() {
        return Array.from(selectedLandWorkerContacts.querySelectorAll('input[type="hidden"]'))
            .map(input => input.value);
    }

    function updateLandWorkerContactDropdown() {
        const selectedIds = getSelectedLandWorkerContactIds();
        Array.from(landWorkerContactSelect.options).forEach(option => {
            if (option.value && selectedIds.includes(option.value)) {
                option.style.display = 'none';
            } else {
                option.style.display = '';
            }
        });
    }

    function addLandWorkerContact() {
        const selectedOption = landWorkerContactSelect.options[landWorkerContactSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            alert('Silakan pilih kontak terlebih dahulu.');
            return;
        }

        const contactId = selectedOption.value;
        const contactName = selectedOption.dataset.name;
        const contactOrg = selectedOption.dataset.org || '';

        if (getSelectedLandWorkerContactIds().includes(contactId)) {
            alert('Kontak ini sudah ditambahkan.');
            return;
        }

        const contactItem = document.createElement('div');
        contactItem.className = 'selected-contact-item mb-2 p-2 border rounded d-flex justify-content-between align-items-center';
        contactItem.setAttribute('data-contact-id', contactId);
        contactItem.innerHTML = `
            <span>
                <strong>${contactName}</strong>
                ${contactOrg ? `<small class="text-muted"> - ${contactOrg}</small>` : ''}
            </span>
            <button type="button" class="btn btn-sm btn-outline-danger remove-contact" title="Hapus">
                <i class="fas fa-times"></i>
            </button>
            <input type="hidden" name="land_worker_contact_ids[]" value="${contactId}">
        `;

        contactItem.querySelector('.remove-contact').addEventListener('click', function() {
            contactItem.remove();
            updateLandWorkerContactDropdown();
        });

        selectedLandWorkerContacts.appendChild(contactItem);
        landWorkerContactSelect.value = '';
        updateLandWorkerContactDropdown();
    }

    if (addLandWorkerContactBtn) {
        addLandWorkerContactBtn.addEventListener('click', addLandWorkerContact);
    }

    if (landWorkerContactSelect) {
        landWorkerContactSelect.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addLandWorkerContact();
            }
        });
    }

    selectedLandWorkerContacts.querySelectorAll('.remove-contact').forEach(btn => {
        btn.addEventListener('click', function() {
            const contactItem = this.closest('.selected-contact-item');
            if (contactItem) {
                contactItem.remove();
                updateLandWorkerContactDropdown();
            }
        });
    });

    updateLandWorkerContactDropdown();
});
</script>
@endpush
@endsection








