@php
    $harvestUnitRequired = $harvestUnitRequired ?? false;
    $harvestUnit = old('harvest_unit', data_get($plant ?? null, 'harvest_unit'));
    if ($harvestUnit === 'kilogram') {
        $harvestUnit = 'kg';
    }
@endphp

<fieldset @if(!empty($readonly)) disabled @endif>
@php $seedUnits = $seedUnits ?? \App\Models\SeedUnit::orderBy('name')->get(); @endphp
<div class="card mb-4 border-success">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Satuan & Harga (Wajib)</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Satuan Tanam <span class="text-danger">*</span></label>
                <select name="satuan_tanam_id" class="form-select" @if(empty($readonly)) required @endif>
                    <option value="">Pilih satuan tanam</option>
                    @foreach($seedUnits as $unit)
                        <option value="{{ $unit->getKey() }}" {{ old('satuan_tanam_id', data_get($plant ?? null, 'satuan_tanam_id')) == $unit->getKey() ? 'selected' : '' }}>{{ $unit->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Satuan Panen <span class="text-danger">*</span></label>
                <select name="satuan_panen_id" class="form-select" @if(empty($readonly)) required @endif>
                    <option value="">Pilih satuan panen</option>
                    @foreach($seedUnits as $unit)
                        <option value="{{ $unit->getKey() }}" {{ old('satuan_panen_id', data_get($plant ?? null, 'satuan_panen_id')) == $unit->getKey() ? 'selected' : '' }}>{{ $unit->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Satuan Stok <span class="text-danger">*</span></label>
                <select name="satuan_stok_id" id="satuanStokSelect" class="form-select" @if(empty($readonly)) required @endif>
                    <option value="">Pilih satuan stok</option>
                    @foreach($seedUnits as $unit)
                        <option value="{{ $unit->getKey() }}" data-label="{{ $unit->label() }}" {{ old('satuan_stok_id', data_get($plant ?? null, 'satuan_stok_id')) == $unit->getKey() ? 'selected' : '' }}>{{ $unit->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" id="hargaJualLabel">Harga Satuan <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" name="harga_jual" class="form-control @error('harga_jual') is-invalid @enderror"
                           value="{{ old('harga_jual', data_get($plant ?? null, 'harga_jual')) }}" step="0.01" min="0" @if(empty($readonly)) required @endif>
                </div>
                @error('harga_jual')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" id="minimalStokLabel">Minimal Stok <span class="text-danger">*</span></label>
                <input type="number" name="minimal_stok" class="form-control @error('minimal_stok') is-invalid @enderror"
                       value="{{ old('minimal_stok', data_get($plant ?? null, 'minimal_stok')) }}" step="0.01" min="0" @if(empty($readonly)) required @endif>
                @error('minimal_stok')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Detail Tanaman</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Hari Sampai Muncul</label>
                    <div class="input-group">
                        <input type="number" name="days_to_emerge" class="form-control @error('days_to_emerge') is-invalid @enderror"
                               value="{{ old('days_to_emerge', data_get($plant ?? null, 'days_to_emerge')) }}" min="0">
                        <select class="form-select" style="max-width: 100px;">
                            <option value="days">Hari</option>
                        </select>
                    </div>
                    @error('days_to_emerge')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Jarak Tanaman</label>
                    <div class="input-group">
                        <input type="number" name="spacing_between_plants" class="form-control @error('spacing_between_plants') is-invalid @enderror"
                               value="{{ old('spacing_between_plants', data_get($plant ?? null, 'spacing_between_plants')) }}" step="0.1" min="0">
                        <select class="form-select" style="max-width: 100px;">
                            <option value="cm">cm</option>
                        </select>
                    </div>
                    @error('spacing_between_plants')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Jarak Baris</label>
                    <div class="input-group">
                        <input type="number" name="spacing_between_rows" class="form-control @error('spacing_between_rows') is-invalid @enderror"
                               value="{{ old('spacing_between_rows', data_get($plant ?? null, 'spacing_between_rows')) }}" step="0.1" min="0">
                        <select class="form-select" style="max-width: 100px;">
                            <option value="cm">cm</option>
                        </select>
                    </div>
                    @error('spacing_between_rows')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Kedalaman Tanam</label>
                    <div class="input-group">
                        <input type="number" name="sowing_depth" class="form-control @error('sowing_depth') is-invalid @enderror"
                               value="{{ old('sowing_depth', data_get($plant ?? null, 'sowing_depth')) }}" step="0.1" min="0">
                        <select class="form-select" style="max-width: 100px;">
                            <option value="cm">cm</option>
                        </select>
                    </div>
                    @error('sowing_depth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Tinggi Rata-rata</label>
                    <div class="input-group">
                        <input type="number" name="avg_height" class="form-control @error('avg_height') is-invalid @enderror"
                               value="{{ old('avg_height', data_get($plant ?? null, 'avg_height')) }}" step="0.1" min="0">
                        <select class="form-select" style="max-width: 100px;">
                            <option value="cm">cm</option>
                        </select>
                    </div>
                    @error('avg_height')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Metode Mulai</label>
                    <select name="start_method" class="form-select @error('start_method') is-invalid @enderror">
                        <option value="">Pilih metode</option>
                        <option value="tanam_langsung" {{ old('start_method', data_get($plant ?? null, 'start_method')) == 'tanam_langsung' ? 'selected' : '' }}>Tanam Langsung</option>
                        <option value="baki_semai" {{ old('start_method', data_get($plant ?? null, 'start_method')) == 'baki_semai' ? 'selected' : '' }}>Mulai di baki semai</option>
                        <option value="pindahkan_ke_tanah" {{ old('start_method', data_get($plant ?? null, 'start_method')) == 'pindahkan_ke_tanah' ? 'selected' : '' }}>Pindahkan ke tanah</option>
                        <option value="transplant" {{ old('start_method', data_get($plant ?? null, 'start_method')) == 'transplant' ? 'selected' : '' }}>Pindah tanaman (transplant)</option>
                        <option value="container" {{ old('start_method', data_get($plant ?? null, 'start_method')) == 'container' ? 'selected' : '' }}>Dalam pot (container)</option>
                        <option value="ditanam_di_baki_semai" {{ old('start_method', data_get($plant ?? null, 'start_method')) == 'ditanam_di_baki_semai' ? 'selected' : '' }}>Ditanam di baki semai</option>
                        <option value="batang_bawah" {{ old('start_method', data_get($plant ?? null, 'start_method')) == 'batang_bawah' ? 'selected' : '' }}>Batang bawah/ tanaman induk</option>
                        <option value="umbi" {{ old('start_method', data_get($plant ?? null, 'start_method')) == 'umbi' ? 'selected' : '' }}>Umbi</option>
                        <option value="sambung_okulasi" {{ old('start_method', data_get($plant ?? null, 'start_method')) == 'sambung_okulasi' ? 'selected' : '' }}>Sambung/okulasi</option>
                        <option value="lainnya" {{ old('start_method', data_get($plant ?? null, 'start_method')) == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('start_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Perkiraan Tingkat Perkecambahan</label>
                    <select name="germination_stage" class="form-select @error('germination_stage') is-invalid @enderror">
                        <option value="">Pilih tingkat</option>
                        <option value="benih_ditanam" {{ old('germination_stage', data_get($plant ?? null, 'germination_stage')) == 'benih_ditanam' ? 'selected' : '' }}>Benih ditanam</option>
                        <option value="perkecambahan" {{ old('germination_stage', data_get($plant ?? null, 'germination_stage')) == 'perkecambahan' ? 'selected' : '' }}>Perkecambahan</option>
                        <option value="bibit" {{ old('germination_stage', data_get($plant ?? null, 'germination_stage')) == 'bibit' ? 'selected' : '' }}>Bibit/ tunas muda</option>
                        <option value="sudah_ditanam" {{ old('germination_stage', data_get($plant ?? null, 'germination_stage')) == 'sudah_ditanam' ? 'selected' : '' }}>Sudah ditanam</option>
                        <option value="vegetatif" {{ old('germination_stage', data_get($plant ?? null, 'germination_stage')) == 'vegetatif' ? 'selected' : '' }}>Fase vegetatif</option>
                        <option value="berbunga" {{ old('germination_stage', data_get($plant ?? null, 'germination_stage')) == 'berbunga' ? 'selected' : '' }}>Berbunga</option>
                        <option value="pematangan_buah" {{ old('germination_stage', data_get($plant ?? null, 'germination_stage')) == 'pematangan_buah' ? 'selected' : '' }}>Pematangan buah</option>
                        <option value="selesai" {{ old('germination_stage', data_get($plant ?? null, 'germination_stage')) == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                    @error('germination_stage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Benih per Lubang</label>
                    <input type="number" name="seeds_per_hole" class="form-control @error('seeds_per_hole') is-invalid @enderror"
                           value="{{ old('seeds_per_hole', data_get($plant ?? null, 'seeds_per_hole')) }}" min="1">
                    @error('seeds_per_hole')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Profil Cahaya</label>
                    <select name="light_profile" class="form-select @error('light_profile') is-invalid @enderror">
                        <option value="">Pilih profil cahaya</option>
                        <option value="matahari_penuh" {{ old('light_profile', data_get($plant ?? null, 'light_profile')) == 'matahari_penuh' ? 'selected' : '' }}>Sinar matahari penuh</option>
                        <option value="matahari_penuh_sebagian" {{ old('light_profile', data_get($plant ?? null, 'light_profile')) == 'matahari_penuh_sebagian' ? 'selected' : '' }}>Sinar matahari penuh sebagian</option>
                        <option value="matahari_sebagian" {{ old('light_profile', data_get($plant ?? null, 'light_profile')) == 'matahari_sebagian' ? 'selected' : '' }}>Sinar matahari sebagian</option>
                        <option value="matahari_setengah_teduh" {{ old('light_profile', data_get($plant ?? null, 'light_profile')) == 'matahari_setengah_teduh' ? 'selected' : '' }}>Matahari hingga setengah teduh</option>
                        <option value="setengah_teduh" {{ old('light_profile', data_get($plant ?? null, 'light_profile')) == 'setengah_teduh' ? 'selected' : '' }}>Setengah teduh</option>
                        <option value="teduh_sepenuhnya" {{ old('light_profile', data_get($plant ?? null, 'light_profile')) == 'teduh_sepenuhnya' ? 'selected' : '' }}>Teduh sepenuhnya</option>
                    </select>
                    @error('light_profile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Kondisi Tanah</label>
                    <select name="soil_condition" class="form-select @error('soil_condition') is-invalid @enderror">
                        <option value="">Pilih kondisi tanah</option>
                        <option value="berkapur" {{ old('soil_condition', data_get($plant ?? null, 'soil_condition')) == 'berkapur' ? 'selected' : '' }}>Tanah berkapur</option>
                        <option value="liat" {{ old('soil_condition', data_get($plant ?? null, 'soil_condition')) == 'liat' ? 'selected' : '' }}>Tanah liat</option>
                        <option value="lempung" {{ old('soil_condition', data_get($plant ?? null, 'soil_condition')) == 'lempung' ? 'selected' : '' }}>Tanah lempung</option>
                        <option value="gambut" {{ old('soil_condition', data_get($plant ?? null, 'soil_condition')) == 'gambut' ? 'selected' : '' }}>Tanah gambut</option>
                        <option value="berpasir" {{ old('soil_condition', data_get($plant ?? null, 'soil_condition')) == 'berpasir' ? 'selected' : '' }}>Tanah berpasir</option>
                        <option value="lanau" {{ old('soil_condition', data_get($plant ?? null, 'soil_condition')) == 'lanau' ? 'selected' : '' }}>Tanah lanau</option>
                    </select>
                    @error('soil_condition')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="mb-3">
                    <label class="form-label">Detail Tanaman</label>
                    <textarea name="planting_detail" class="form-control @error('planting_detail') is-invalid @enderror"
                              rows="3" placeholder="Masukkan detail tanaman">{{ old('planting_detail', data_get($plant ?? null, 'planting_detail')) }}</textarea>
                    @error('planting_detail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="mb-3">
                    <label class="form-label">Detail Pemangkasan</label>
                    <textarea name="pruning_detail" class="form-control @error('pruning_detail') is-invalid @enderror"
                              rows="3" placeholder="Masukkan detail pemangkasan">{{ old('pruning_detail', data_get($plant ?? null, 'pruning_detail')) }}</textarea>
                    @error('pruning_detail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Detail Panen</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Hari Sampai Berbunga</label>
                    <div class="input-group">
                        <input type="number" name="days_to_flower" class="form-control @error('days_to_flower') is-invalid @enderror"
                               value="{{ old('days_to_flower', data_get($plant ?? null, 'days_to_flower')) }}" min="0">
                        <span class="input-group-text">hari</span>
                    </div>
                    @error('days_to_flower')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Hari Sampai Panen</label>
                    <div class="input-group">
                        <input type="number" name="days_to_harvest" class="form-control @error('days_to_harvest') is-invalid @enderror"
                               value="{{ old('days_to_harvest', data_get($plant ?? null, 'days_to_harvest')) }}" min="0">
                        <span class="input-group-text">hari</span>
                    </div>
                    @error('days_to_harvest')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Jendela Panen</label>
                    <div class="input-group">
                        <input type="number" name="harvest_window_days" class="form-control @error('harvest_window_days') is-invalid @enderror"
                               value="{{ old('harvest_window_days', data_get($plant ?? null, 'harvest_window_days')) }}" min="0">
                        <span class="input-group-text">hari</span>
                    </div>
                    @error('harvest_window_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Perkiraan Tingkat Kehilangan</label>
                    <div class="input-group">
                        <input type="number" name="expected_loss_rate" class="form-control @error('expected_loss_rate') is-invalid @enderror"
                               value="{{ old('expected_loss_rate', data_get($plant ?? null, 'expected_loss_rate')) }}" step="0.1" min="0" max="100">
                        <span class="input-group-text">%</span>
                    </div>
                    @error('expected_loss_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

    </div>
</div>
</fieldset>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Informasi Publik</h5>
    </div>
    <div class="card-body">
        <p class="text-muted small">Data ini ditampilkan di landing page untuk informasi varietas kepada publik.</p>
        <div class="mb-3">
            <label class="form-label">Foto Tanaman</label>
            @if(!empty($readonly))
                @if(data_get($plant ?? null, 'public_photo_path'))
                    <div><img src="{{ asset('storage/'.data_get($plant, 'public_photo_path')) }}" alt="Foto varietas" class="img-fluid rounded" style="max-height: 220px;"></div>
                @else
                    <div class="text-muted">Belum ada foto publik.</div>
                @endif
            @else
                @if(data_get($plant ?? null, 'public_photo_path'))
                    <div class="mb-2"><img src="{{ asset('storage/'.data_get($plant, 'public_photo_path')) }}" alt="Foto varietas" class="img-fluid rounded" style="max-height: 160px;"></div>
                @endif
                <input type="file" name="public_photo" class="form-control @error('public_photo') is-invalid @enderror" accept="image/*">
                @error('public_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @endif
        </div>
        <div class="mb-0">
            <label class="form-label">Deskripsi Varietas</label>
            @if(!empty($readonly))
                <textarea class="form-control" rows="4" readonly>{{ data_get($plant ?? null, 'public_description') ?: '-' }}</textarea>
            @else
                <textarea name="public_description" rows="4" class="form-control @error('public_description') is-invalid @enderror"
                          placeholder="Deskripsi singkat varietas untuk publik">{{ old('public_description', data_get($plant ?? null, 'public_description')) }}</textarea>
                @error('public_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @endif
        </div>
    </div>
</div>

@if(empty($readonly))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('satuanStokSelect');
    const hargaLabel = document.getElementById('hargaJualLabel');
    const hargaHint = document.getElementById('hargaJualHint');
    const minLabel = document.getElementById('minimalStokLabel');
    const minHint = document.getElementById('minimalStokHint');
    if (!select) return;
    function syncUnitLabels() {
        const opt = select.options[select.selectedIndex];
        const unit = opt && opt.dataset.label ? opt.dataset.label : 'satuan stok';
        if (hargaLabel) hargaLabel.textContent = 'Harga Jual (per ' + unit + ')';
        if (hargaHint) hargaHint.textContent = 'Harga jual per ' + unit + ' benih.';
        if (minLabel) minLabel.textContent = 'Minimal Stok (' + unit + ')';
        if (minHint) minHint.textContent = 'Jumlah minimum stok yang harus tersedia, misalnya 100 ' + unit + '. Jika stok gudang di bawah angka ini, muncul notifikasi stok rendah.';
    }
    select.addEventListener('change', syncUnitLabels);
    syncUnitLabels();
});
</script>
@endif

