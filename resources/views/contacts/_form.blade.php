@php
    $contact = $contact ?? null;
@endphp

<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Informasi Dasar</h5>
        <small class="text-muted">Lengkapi data utama dan status kontak.</small>
    </div>
    <div class="card-body">
        <div class="row g-4 align-items-center">
            <div class="col-md-3 text-center">
                <div class="mb-3">
                    @if(($contact?->photo_url))
                        <img src="{{ $contact->photo_url }}" alt="{{ $contact->full_name }}" class="rounded-circle mb-2" style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                        <div class="bg-light border rounded-circle d-flex align-items-center justify-content-center mb-2" style="width: 120px; height: 120px;">
                            <i class="fas fa-user fa-3x text-muted"></i>
                        </div>
                    @endif
                </div>
                <label for="photo" class="form-label">Foto Profil</label>
                <input type="file" name="photo" id="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                @error('photo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <div class="form-text">Format JPG, PNG, maksimal 2 MB.</div>
                @enderror
            </div>
            <div class="col-md-9">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="full_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" id="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name', $contact->full_name ?? '') }}" required>
                        @error('full_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status Kontak <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" {{ old('status', $contact->status ?? \App\Models\Contact::STATUS_ACTIVE) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="contact_type" class="form-label">Tipe Kontak <span class="text-danger">*</span></label>
                        <select name="contact_type" id="contact_type" class="form-select @error('contact_type') is-invalid @enderror" required>
                            <option value="">Pilih Tipe Kontak</option>
                            @foreach($contactTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('contact_type', $contact->contact_type ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('contact_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="organization" class="form-label">Instansi / Organisasi</label>
                        <input type="text" name="organization" id="organization" class="form-control @error('organization') is-invalid @enderror" value="{{ old('organization', $contact->organization ?? '') }}">
                        @error('organization')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="position" class="form-label">Jabatan / Posisi</label>
                        <input type="text" name="position" id="position" class="form-control @error('position') is-invalid @enderror" value="{{ old('position', $contact->position ?? '') }}">
                        @error('position')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label for="nip" class="form-label">NIP</label>
                        <input type="text" name="nip" id="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip', $contact->nip ?? '') }}">
                        @error('nip')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Opsional, untuk pegawai UPTD.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Informasi Kontak &amp; Alamat</h5>
        <small class="text-muted">Pastikan data kontak terbaru agar mudah dihubungi.</small>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="primary_phone" class="form-label">Nomor Telepon (Utama) <span class="text-danger">*</span></label>
                <input type="text" name="primary_phone" id="primary_phone" class="form-control @error('primary_phone') is-invalid @enderror" value="{{ old('primary_phone', $contact->primary_phone ?? '') }}" required>
                @error('primary_phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-check mt-2">
                    <input type="checkbox" class="form-check-input" id="primary_phone_is_whatsapp" name="primary_phone_is_whatsapp" value="1" {{ old('primary_phone_is_whatsapp', $contact->primary_phone_is_whatsapp ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="primary_phone_is_whatsapp">Tersambung ke WhatsApp</label>
                </div>
            </div>
            <div class="col-md-4">
                <label for="secondary_phone" class="form-label">Nomor Telepon (Lainnya)</label>
                <input type="text" name="secondary_phone" id="secondary_phone" class="form-control @error('secondary_phone') is-invalid @enderror" value="{{ old('secondary_phone', $contact->secondary_phone ?? '') }}">
                @error('secondary_phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $contact->email ?? '') }}">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12">
                <label for="address" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                <textarea name="address" id="address" rows="3" class="form-control @error('address') is-invalid @enderror" required>{{ old('address', $contact->address ?? '') }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Tuliskan alamat domisili lengkap (Jalan, RT/RW, Nomor Rumah).</div>
            </div>
        </div>

        <hr class="my-4">

        <div class="row g-3" data-region-selector
             data-selected-province="{{ old('province', $contact->province ?? '') }}"
             data-selected-city="{{ old('city', $contact->city ?? '') }}"
             data-selected-district="{{ old('district', $contact->district ?? '') }}"
             data-selected-village="{{ old('village', $contact->village ?? '') }}">
            <div class="col-md-3">
                <label for="province" class="form-label">Provinsi</label>
                <select name="province" id="province" class="form-select @error('province') is-invalid @enderror" data-region-select="province" data-placeholder="Pilih Provinsi">
                    <option value="">Pilih Provinsi</option>
                    @if(old('province', $contact->province ?? false))
                        <option value="{{ old('province', $contact->province ?? '') }}" selected>{{ old('province', $contact->province ?? '') }}</option>
                    @endif
                    <option value="_manual">Ketik Manual</option>
                </select>
                <input type="text" class="form-control mt-2 d-none" placeholder="Tuliskan provinsi" data-region-manual="province" value="{{ old('province', $contact->province ?? '') }}">
                @error('province')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-3">
                <label for="city" class="form-label">Kabupaten / Kota</label>
                <select name="city" id="city" class="form-select @error('city') is-invalid @enderror" data-region-select="city" data-placeholder="Pilih Kabupaten / Kota">
                    <option value="">Pilih Kabupaten / Kota</option>
                    @if(old('city', $contact->city ?? false))
                        <option value="{{ old('city', $contact->city ?? '') }}" selected>{{ old('city', $contact->city ?? '') }}</option>
                    @endif
                    <option value="_manual">Ketik Manual</option>
                </select>
                <input type="text" class="form-control mt-2 d-none" placeholder="Tuliskan kabupaten/kota" data-region-manual="city" value="{{ old('city', $contact->city ?? '') }}">
                @error('city')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-3">ia
                <label for="district" class="form-label">Kecamatan</label>
                <select name="district" id="district" class="form-select @error('district') is-invalid @enderror" data-region-select="district" data-placeholder="Pilih Kecamatan">
                    <option value="">Pilih Kecamatan</option>
                    @if(old('district', $contact->district ?? false))
                        <option value="{{ old('district', $contact->district ?? '') }}" selected>{{ old('district', $contact->district ?? '') }}</option>
                    @endif
                    <option value="_manual">Ketik Manual</option>
                </select>
                <input type="text" class="form-control mt-2 d-none" placeholder="Tuliskan kecamatan" data-region-manual="district" value="{{ old('district', $contact->district ?? '') }}">
                @error('district')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-3">
                <label for="village" class="form-label">Desa / Kelurahan</label>
                <select name="village" id="village" class="form-select @error('village') is-invalid @enderror" data-region-select="village" data-placeholder="Pilih Desa / Kelurahan">
                    <option value="">Pilih Desa / Kelurahan</option>
                    @if(old('village', $contact->village ?? false))
                        <option value="{{ old('village', $contact->village ?? '') }}" selected>{{ old('village', $contact->village ?? '') }}</option>
                    @endif
                    <option value="_manual">Ketik Manual</option>
                </select>
                <input type="text" class="form-control mt-2 d-none" placeholder="Tuliskan desa/kelurahan" data-region-manual="village" value="{{ old('village', $contact->village ?? '') }}">
                @error('village')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="form-text mt-2">Dropdown terhubung otomatis. Pilih opsi "Ketik Manual" bila data belum tersedia.</div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Informasi Pekerjaan &amp; Peran</h5>
        <small class="text-muted">Gunakan bagian ini untuk mengelompokkan kontak berdasarkan tanggung jawabnya.</small>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12">
                <div class="alert alert-info mb-0">
                    <i class="fas fa-lightbulb me-2"></i>
                    Pastikan tipe kontak sesuai agar mudah difilter pada daftar dan analitik modul lainnya.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Catatan Tambahan</h5>
    </div>
    <div class="card-body">
        <label for="notes" class="form-label">Catatan</label>
        <textarea name="notes" id="notes" rows="4" class="form-control @error('notes') is-invalid @enderror" placeholder="Contoh: Ahli dalam pengendalian hama, dapat dihubungi di luar jam kerja, dsb.">{{ old('notes', $contact->notes ?? '') }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="d-flex justify-content-between">
    <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
    </a>
    <button type="submit" class="btn btn-success">
        <i class="fas fa-save me-2"></i>Simpan Kontak
    </button>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const regionContainer = document.querySelector('[data-region-selector]');

            if (!regionContainer) {
                return;
            }

            const regionEndpoints = {
                provinces: 'https://emsifa.github.io/api-wilayah-indonesia/api/provinces.json',
                regencies: (provinceId) => `https://emsifa.github.io/api-wilayah-indonesia/api/regencies/${provinceId}.json`,
                districts: (regencyId) => `https://emsifa.github.io/api-wilayah-indonesia/api/districts/${regencyId}.json`,
                villages: (districtId) => `https://emsifa.github.io/api-wilayah-indonesia/api/villages/${districtId}.json`,
            };

            const selectors = {
                province: regionContainer.querySelector('[data-region-select="province"]'),
                city: regionContainer.querySelector('[data-region-select="city"]'),
                district: regionContainer.querySelector('[data-region-select="district"]'),
                village: regionContainer.querySelector('[data-region-select="village"]'),
            };

            const manualInputs = {
                province: regionContainer.querySelector('[data-region-manual="province"]'),
                city: regionContainer.querySelector('[data-region-manual="city"]'),
                district: regionContainer.querySelector('[data-region-manual="district"]'),
                village: regionContainer.querySelector('[data-region-manual="village"]'),
            };

            const selectedValues = {
                province: regionContainer.dataset.selectedProvince ?? '',
                city: regionContainer.dataset.selectedCity ?? '',
                district: regionContainer.dataset.selectedDistrict ?? '',
                village: regionContainer.dataset.selectedVillage ?? '',
            };

            Object.keys(selectors).forEach((key) => {
                const select = selectors[key];
                if (!select) return;

                select.dataset.originalName = select.name;

                select.addEventListener('change', () => {
                    handleManualToggle(key);
                    if (key === 'province') {
                        loadRegencies();
                    } else if (key === 'city') {
                        loadDistricts();
                    } else if (key === 'district') {
                        loadVillages();
                    }
                });
            });

            Object.keys(manualInputs).forEach((key) => {
                const input = manualInputs[key];
                if (!input) return;

                input.addEventListener('input', () => {
                    input.dataset.manualValue = input.value;
                });
            });

            function handleManualToggle(level) {
                const select = selectors[level];
                const manualInput = manualInputs[level];

                if (!select || !manualInput) return;

                if (select.value === '_manual') {
                    manualInput.classList.remove('d-none');
                    manualInput.name = select.dataset.originalName;
                    manualInput.required = select.required;
                    select.name = '';
                    if (manualInput.dataset.manualValue) {
                        manualInput.value = manualInput.dataset.manualValue;
                    }
                    manualInput.focus();
                } else {
                    if (manualInput.dataset.manualValue === undefined || manualInput.dataset.manualValue === '') {
                        manualInput.dataset.manualValue = manualInput.value;
                    }
                    manualInput.classList.add('d-none');
                    manualInput.required = false;
                    manualInput.name = '';
                    select.name = select.dataset.originalName;
                }
            }

            async function fetchRegions(url) {
                try {
                    const response = await fetch(url);
                    if (!response.ok) {
                        throw new Error('Gagal memuat data wilayah');
                    }
                    return await response.json();
                } catch (error) {
                    console.warn('[Kontak] Tidak dapat memuat data wilayah otomatis.', error);
                    return null;
                }
            }

            function populateSelect(select, data, nameKey = 'name') {
                if (!select) return;

                const currentValue = select.value && select.value !== '_manual' ? select.value : '';
                const manualOption = select.querySelector('option[value="_manual"]')?.cloneNode(true);
                const placeholder = select.dataset.placeholder || 'Pilih';

                select.innerHTML = `<option value="">${placeholder}</option>`;

                data?.forEach((item) => {
                    const option = document.createElement('option');
                    option.value = item[nameKey];
                    option.textContent = item[nameKey];
                    option.dataset.regionId = item.id;
                    select.appendChild(option);
                });

                if (manualOption) {
                    select.appendChild(manualOption);
                } else {
                    const manualFallback = document.createElement('option');
                    manualFallback.value = '_manual';
                    manualFallback.textContent = 'Ketik Manual';
                    select.appendChild(manualFallback);
                }

                if (currentValue) {
                    const optionToSelect = Array.from(select.options).find(option => option.value === currentValue);
                    if (optionToSelect) {
                        optionToSelect.selected = true;
                    } else {
                        select.value = '_manual';
                        const manualInput = manualInputs[select.dataset.regionSelect || ''];
                        if (manualInput) {
                            manualInput.dataset.manualValue = currentValue;
                            manualInput.value = currentValue;
                            handleManualToggle(select.dataset.regionSelect || '');
                        }
                    }
                } else {
                    select.value = '';
                }
            }

            async function loadProvinces() {
                const data = await fetchRegions(regionEndpoints.provinces);
                if (!data) {
                    enableManualFallback('province');
                    enableManualFallback('city');
                    enableManualFallback('district');
                    enableManualFallback('village');
                    return;
                }

                populateSelect(selectors.province, data);

                if (selectedValues.province) {
                    const option = Array.from(selectors.province.options).find(option => option.value === selectedValues.province);
                    if (option) {
                        option.selected = true;
                        await loadRegencies(option.dataset.regionId);
                    } else {
                        selectors.province.value = '_manual';
                        const manual = manualInputs.province;
                        if (manual) {
                            manual.dataset.manualValue = selectedValues.province;
                            manual.value = selectedValues.province;
                            handleManualToggle('province');
                        }
                    }
                }
            }

            async function loadRegencies(forcedProvinceId = null) {
                const provinceSelect = selectors.province;
                if (!provinceSelect || !selectors.city) return;

                const selectedOption = forcedProvinceId
                    ? Array.from(provinceSelect.options).find(option => option.dataset.regionId === forcedProvinceId)
                    : provinceSelect.selectedOptions[0];

                if (!selectedOption || !selectedOption.dataset.regionId) {
                    selectors.city.innerHTML = '<option value="">Pilih Kabupaten / Kota</option><option value="_manual">Ketik Manual</option>';
                    selectors.district.innerHTML = '<option value="">Pilih Kecamatan</option><option value="_manual">Ketik Manual</option>';
                    selectors.village.innerHTML = '<option value="">Pilih Desa / Kelurahan</option><option value="_manual">Ketik Manual</option>';
                    return;
                }

                const data = await fetchRegions(regionEndpoints.regencies(selectedOption.dataset.regionId));
                if (!data) {
                    enableManualFallback('city');
                    enableManualFallback('district');
                    enableManualFallback('village');
                    return;
                }

                populateSelect(selectors.city, data);

                if (selectedValues.city) {
                    const option = Array.from(selectors.city.options).find(option => option.value === selectedValues.city);
                    if (option) {
                        option.selected = true;
                        await loadDistricts(option.dataset.regionId);
                    } else {
                        selectors.city.value = '_manual';
                        const manual = manualInputs.city;
                        if (manual) {
                            manual.dataset.manualValue = selectedValues.city;
                            manual.value = selectedValues.city;
                            handleManualToggle('city');
                        }
                    }
                }
            }

            async function loadDistricts(forcedCityId = null) {
                const citySelect = selectors.city;
                if (!citySelect || !selectors.district) return;

                const selectedOption = forcedCityId
                    ? Array.from(citySelect.options).find(option => option.dataset.regionId === forcedCityId)
                    : citySelect.selectedOptions[0];

                if (!selectedOption || !selectedOption.dataset.regionId) {
                    selectors.district.innerHTML = '<option value="">Pilih Kecamatan</option><option value="_manual">Ketik Manual</option>';
                    selectors.village.innerHTML = '<option value="">Pilih Desa / Kelurahan</option><option value="_manual">Ketik Manual</option>';
                    return;
                }

                const data = await fetchRegions(regionEndpoints.districts(selectedOption.dataset.regionId));
                if (!data) {
                    enableManualFallback('district');
                    enableManualFallback('village');
                    return;
                }

                populateSelect(selectors.district, data);

                if (selectedValues.district) {
                    const option = Array.from(selectors.district.options).find(option => option.value === selectedValues.district);
                    if (option) {
                        option.selected = true;
                        await loadVillages(option.dataset.regionId);
                    } else {
                        selectors.district.value = '_manual';
                        const manual = manualInputs.district;
                        if (manual) {
                            manual.dataset.manualValue = selectedValues.district;
                            manual.value = selectedValues.district;
                            handleManualToggle('district');
                        }
                    }
                }
            }

            async function loadVillages(forcedDistrictId = null) {
                const districtSelect = selectors.district;
                if (!districtSelect || !selectors.village) return;

                const selectedOption = forcedDistrictId
                    ? Array.from(districtSelect.options).find(option => option.dataset.regionId === forcedDistrictId)
                    : districtSelect.selectedOptions[0];

                if (!selectedOption || !selectedOption.dataset.regionId) {
                    selectors.village.innerHTML = '<option value="">Pilih Desa / Kelurahan</option><option value="_manual">Ketik Manual</option>';
                    return;
                }

                const data = await fetchRegions(regionEndpoints.villages(selectedOption.dataset.regionId));
                if (!data) {
                    enableManualFallback('village');
                    return;
                }

                populateSelect(selectors.village, data);

                if (selectedValues.village) {
                    const option = Array.from(selectors.village.options).find(option => option.value === selectedValues.village);
                    if (option) {
                        option.selected = true;
                    } else {
                        selectors.village.value = '_manual';
                        const manual = manualInputs.village;
                        if (manual) {
                            manual.dataset.manualValue = selectedValues.village;
                            manual.value = selectedValues.village;
                            handleManualToggle('village');
                        }
                    }
                }
            }

            function enableManualFallback(level) {
                const select = selectors[level];
                const manualInput = manualInputs[level];

                if (!select || !manualInput) return;

                select.value = '_manual';
                manualInput.dataset.manualValue = manualInput.value || selectedValues[level] || '';
                manualInput.value = manualInput.dataset.manualValue;
                handleManualToggle(level);
            }

            // Initialize manual toggle for pre-filled values
            Object.keys(selectors).forEach((level) => {
                const select = selectors[level];
                if (!select) return;
                if (select.value === '_manual') {
                    handleManualToggle(level);
                }
            });

            loadProvinces();
        });
    </script>
@endpush

