@php
    $row = $row ?? [];
    $index = $index ?? '__INDEX__';
@endphp
<div class="card mb-3 nested-lahan-card" data-index="{{ $index }}">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <strong>Lahan</strong>
        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-lahan">Hapus</button>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Kode Lahan <span class="text-danger">*</span></label>
                    <input type="text" name="fields[{{ $index }}][kode_lahan]" maxlength="20"
                           class="form-control @error('fields.'.$index.'.kode_lahan') is-invalid @enderror"
                           value="{{ $row['kode_lahan'] ?? '' }}"
                           placeholder="Contoh: BLOK-A1, BLOK-B2">
                    @error('fields.'.$index.'.kode_lahan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Luas Lahan (Ha) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" name="fields[{{ $index }}][luas_ha]" step="0.01" min="0.01" max="999.99"
                               class="form-control @error('fields.'.$index.'.luas_ha') is-invalid @enderror"
                               value="{{ $row['luas_ha'] ?? '' }}" placeholder="0.00">
                        <span class="input-group-text">Ha</span>
                    </div>
                    @error('fields.'.$index.'.luas_ha')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label class="form-label">Panjang (m)</label>
                    <input type="number" name="fields[{{ $index }}][panjang_m]" step="0.01" min="0"
                           class="form-control @error('fields.'.$index.'.panjang_m') is-invalid @enderror"
                           value="{{ $row['panjang_m'] ?? '' }}" placeholder="0.00">
                    @error('fields.'.$index.'.panjang_m')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label class="form-label">Lebar (m)</label>
                    <input type="number" name="fields[{{ $index }}][lebar_m]" step="0.01" min="0"
                           class="form-control @error('fields.'.$index.'.lebar_m') is-invalid @enderror"
                           value="{{ $row['lebar_m'] ?? '' }}" placeholder="0.00">
                    @error('fields.'.$index.'.lebar_m')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Status Lahan <span class="text-danger">*</span></label>
                    <select name="fields[{{ $index }}][status_lahan]" class="form-select @error('fields.'.$index.'.status_lahan') is-invalid @enderror">
                        <option value="">Pilih status lahan</option>
                        @foreach(\App\Models\PlantingField::statusOptions() as $value => $label)
                            <option value="{{ $value }}" {{ ($row['status_lahan'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('fields.'.$index.'.status_lahan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Koordinat GPS</label>
            <div class="input-group mb-2">
                <input type="text" name="fields[{{ $index }}][koordinat_gps]" maxlength="100"
                       class="form-control nested-gps-input @error('fields.'.$index.'.koordinat_gps') is-invalid @enderror"
                       value="{{ $row['koordinat_gps'] ?? '' }}"
                       placeholder="Contoh: -0.9471,100.4172">
                <button type="button" class="btn btn-outline-primary btn-nested-device-gps">
                    <i class="fas fa-location-arrow me-1"></i>Lokasi perangkat
                </button>
                <button type="button" class="btn btn-outline-secondary btn-nested-clear-gps">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @error('fields.'.$index.'.koordinat_gps')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            <div class="form-text mb-2">Klik peta untuk menandai titik tengah petak lahan, atau gunakan lokasi perangkat.</div>
            <div class="nested-gps-map" style="height: 280px; width: 100%; border-radius: 8px; z-index: 1;"></div>
            <div class="nested-gps-status small text-muted mt-2"></div>
        </div>

        <div class="mb-0">
            <label class="form-label">Peta Lahan</label>
            <input type="hidden" name="fields[{{ $index }}][peta_lahan]" class="nested-peta-input" value="{{ is_array($row['peta_lahan'] ?? null) ? json_encode($row['peta_lahan']) : ($row['peta_lahan'] ?? '') }}">
            <div class="d-flex flex-wrap gap-2 mb-2">
                <button type="button" class="btn btn-outline-secondary btn-sm btn-nested-undo-polygon">
                    <i class="fas fa-undo me-1"></i>Hapus titik terakhir
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm btn-nested-clear-polygon">
                    <i class="fas fa-times me-1"></i>Reset peta
                </button>
            </div>
            @error('fields.'.$index.'.peta_lahan')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
            <div class="form-text mb-2">Klik 4 titik di peta untuk membatasi petak lahan. Titik dapat digeser setelah ditandai.</div>
            <div class="nested-polygon-map" style="height: 280px; width: 100%; border-radius: 8px; z-index: 1;"></div>
            <div class="nested-polygon-status small text-muted mt-2">Belum ada titik. Tandai 4 sudut lahan.</div>
        </div>
    </div>
</div>
