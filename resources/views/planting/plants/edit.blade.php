@extends('layouts.app')

@section('title', 'Edit Tanaman - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Tanaman: {{ $plant->name }}</h4>
    <a href="{{ route('plants.show', $plant) }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('plants.update', $plant) }}" id="plantForm">
            @csrf @method('PUT')
            
            <!-- Bagian 1: Data Tanaman -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Data Tanaman</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Tipe <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2">
                                    <select name="plant_type_id" id="plantTypeSelect" class="form-select @error('plant_type_id') is-invalid @enderror" required>
                                        <option value="">Pilih tipe</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type->plant_type_id }}" data-name="{{ $type->name }}" data-category="{{ $type->category }}" 
                                                    {{ old('plant_type_id', $plant->plant_type_id) == $type->plant_type_id ? 'selected' : '' }}>
                                                {{ $type->category ? $type->category.' - ' : '' }}{{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if(auth()->user()->role !== 'penangkar')
                                        <a href="{{ route('plant-types.create') }}" class="btn btn-success" target="_blank">Tambahkan tipe tanaman</a>
                                    @endif
                                </div>
                                @error('plant_type_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Varietas</label>
                                <input name="variety" class="form-control @error('variety') is-invalid @enderror" 
                                       value="{{ old('variety', $plant->variety) }}" placeholder="Masukkan varietas">
                                @error('variety')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Lokasi Penanaman</label>
                                <div id="plantingLocationsContainer">
                                    @php
                                        $existingLocationIds = $plant->plantings->pluck('planting_location_id')->unique()->toArray();
                                    @endphp
                                    @foreach($existingLocationIds as $index => $locationId)
                                        <div class="planting-location-item mb-2">
                                            <div class="d-flex gap-2">
                                                <select name="planting_location_ids[]" class="form-select planting-location-select">
                                                    <option value="">Pilih lokasi</option>
                                                    @foreach($locations as $loc)
                                                        <option value="{{ $loc->planting_location_id }}" {{ $locationId == $loc->planting_location_id ? 'selected' : '' }}>
                                                            {{ $loc->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-danger remove-location-btn" style="{{ count($existingLocationIds) > 1 ? '' : 'display: none;' }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if(empty($existingLocationIds))
                                        <div class="planting-location-item mb-2">
                                            <div class="d-flex gap-2">
                                                <select name="planting_location_ids[]" class="form-select planting-location-select">
                                                    <option value="">Pilih lokasi</option>
                                                    @foreach($locations as $loc)
                                                        <option value="{{ $loc->planting_location_id }}">
                                                            {{ $loc->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-danger remove-location-btn" style="display: none;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-success btn-sm mt-2" id="addLocationBtn">
                                    <i class="fas fa-plus"></i> Tambah Lokasi
                                </button>
                                <a href="{{ route('planting-locations.create') }}" class="btn btn-outline-success btn-sm mt-2" target="_blank">
                                    <i class="fas fa-plus"></i> Buat Lokasi Baru
                                </a>
                                @error('planting_location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian 2: Detail Penanaman -->
            @php
                $firstPlanting = $plant->plantings->first();
            @endphp
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
                                           value="{{ old('days_to_emerge', $firstPlanting?->days_to_emerge) }}" min="0">
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
                                           value="{{ old('spacing_between_plants', $firstPlanting?->spacing_between_plants) }}" step="0.1" min="0">
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
                                           value="{{ old('spacing_between_rows', $firstPlanting?->spacing_between_rows) }}" step="0.1" min="0">
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
                                           value="{{ old('sowing_depth', $firstPlanting?->sowing_depth) }}" step="0.1" min="0">
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
                                           value="{{ old('avg_height', $firstPlanting?->avg_height) }}" step="0.1" min="0">
                                    <select class="form-select" style="max-width: 100px;">
                                        <option value="cm">cm</option>
                                    </select>
                                </div>
                                @error('avg_height')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Metode Mulai</label>
                                <select name="start_method" class="form-select @error('start_method') is-invalid @enderror">
                                    <option value="">Pilih metode</option>
                                    <option value="Tanam Langsung" {{ old('start_method', $firstPlanting?->start_method) == 'Tanam Langsung' ? 'selected' : '' }}>Tanam Langsung</option>
                                    <option value="Mulai di baki semai" {{ old('start_method', $firstPlanting?->start_method) == 'Mulai di baki semai' ? 'selected' : '' }}>Mulai di baki semai</option>
                                    <option value="Pindahkan ke tanah" {{ old('start_method', $firstPlanting?->start_method) == 'Pindahkan ke tanah' ? 'selected' : '' }}>Pindahkan ke tanah</option>
                                    <option value="Pindah tanaman (transplant)" {{ old('start_method', $firstPlanting?->start_method) == 'Pindah tanaman (transplant)' ? 'selected' : '' }}>Pindah tanaman (transplant)</option>
                                    <option value="Dalam pot (container)" {{ old('start_method', $firstPlanting?->start_method) == 'Dalam pot (container)' ? 'selected' : '' }}>Dalam pot (container)</option>
                                    <option value="Ditanam di baki semai" {{ old('start_method', $firstPlanting?->start_method) == 'Ditanam di baki semai' ? 'selected' : '' }}>Ditanam di baki semai</option>
                                    <option value="Batang bawah/ tanaman induk" {{ old('start_method', $firstPlanting?->start_method) == 'Batang bawah/ tanaman induk' ? 'selected' : '' }}>Batang bawah/ tanaman induk</option>
                                    <option value="Umbi" {{ old('start_method', $firstPlanting?->start_method) == 'Umbi' ? 'selected' : '' }}>Umbi</option>
                                    <option value="Sambung/okulasi" {{ old('start_method', $firstPlanting?->start_method) == 'Sambung/okulasi' ? 'selected' : '' }}>Sambung/okulasi</option>
                                    <option value="Lainnya" {{ old('start_method', $firstPlanting?->start_method) == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
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
                                    <option value="benih ditanam" {{ old('germination_stage', $firstPlanting?->germination_stage) == 'benih ditanam' ? 'selected' : '' }}>Benih ditanam</option>
                                    <option value="perkecambahan" {{ old('germination_stage', $firstPlanting?->germination_stage) == 'perkecambahan' ? 'selected' : '' }}>Perkecambahan</option>
                                    <option value="bibit/ tunas muda" {{ old('germination_stage', $firstPlanting?->germination_stage) == 'bibit/ tunas muda' ? 'selected' : '' }}>Bibit/ tunas muda</option>
                                    <option value="sudah ditanam" {{ old('germination_stage', $firstPlanting?->germination_stage) == 'sudah ditanam' ? 'selected' : '' }}>Sudah ditanam</option>
                                    <option value="fase vegetatif" {{ old('germination_stage', $firstPlanting?->germination_stage) == 'fase vegetatif' ? 'selected' : '' }}>Fase vegetatif</option>
                                    <option value="berbunga" {{ old('germination_stage', $firstPlanting?->germination_stage) == 'berbunga' ? 'selected' : '' }}>Berbunga</option>
                                    <option value="pematangan buah" {{ old('germination_stage', $firstPlanting?->germination_stage) == 'pematangan buah' ? 'selected' : '' }}>Pematangan buah</option>
                                    <option value="selesai" {{ old('germination_stage', $firstPlanting?->germination_stage) == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                </select>
                                @error('germination_stage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Benih per Lubang</label>
                                <input type="number" name="seeds_per_hole" class="form-control @error('seeds_per_hole') is-invalid @enderror" 
                                       value="{{ old('seeds_per_hole', $firstPlanting?->seeds_per_hole ?? 1) }}" min="1">
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
                                    <option value="sinar matahari penuh" {{ old('light_profile', $firstPlanting?->light_profile) == 'sinar matahari penuh' ? 'selected' : '' }}>Sinar matahari penuh</option>
                                    <option value="sinar matahari penuh sebagian" {{ old('light_profile', $firstPlanting?->light_profile) == 'sinar matahari penuh sebagian' ? 'selected' : '' }}>Sinar matahari penuh sebagian</option>
                                    <option value="sinar matahari sebagian" {{ old('light_profile', $firstPlanting?->light_profile) == 'sinar matahari sebagian' ? 'selected' : '' }}>Sinar matahari sebagian</option>
                                    <option value="matahari hingga setengah teduh" {{ old('light_profile', $firstPlanting?->light_profile) == 'matahari hingga setengah teduh' ? 'selected' : '' }}>Matahari hingga setengah teduh</option>
                                    <option value="setengah teduh" {{ old('light_profile', $firstPlanting?->light_profile) == 'setengah teduh' ? 'selected' : '' }}>Setengah teduh</option>
                                    <option value="teduh sepenuhnya" {{ old('light_profile', $firstPlanting?->light_profile) == 'teduh sepenuhnya' ? 'selected' : '' }}>Teduh sepenuhnya</option>
                                </select>
                                @error('light_profile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Kondisi Tanah</label>
                                <select name="soil_condition" class="form-select @error('soil_condition') is-invalid @enderror">
                                    <option value="">Pilih kondisi tanah</option>
                                    <option value="tanah berkapur" {{ old('soil_condition', $firstPlanting?->soil_condition) == 'tanah berkapur' ? 'selected' : '' }}>Tanah berkapur</option>
                                    <option value="tanah liat" {{ old('soil_condition', $firstPlanting?->soil_condition) == 'tanah liat' ? 'selected' : '' }}>Tanah liat</option>
                                    <option value="tanah lempung" {{ old('soil_condition', $firstPlanting?->soil_condition) == 'tanah lempung' ? 'selected' : '' }}>Tanah lempung</option>
                                    <option value="tanah gambut" {{ old('soil_condition', $firstPlanting?->soil_condition) == 'tanah gambut' ? 'selected' : '' }}>Tanah gambut</option>
                                    <option value="tanah berpasir" {{ old('soil_condition', $firstPlanting?->soil_condition) == 'tanah berpasir' ? 'selected' : '' }}>Tanah berpasir</option>
                                    <option value="tanah lanau" {{ old('soil_condition', $firstPlanting?->soil_condition) == 'tanah lanau' ? 'selected' : '' }}>Tanah lanau</option>
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
                                          rows="3" placeholder="Masukkan detail tanaman">{{ old('planting_detail', $firstPlanting?->planting_detail) }}</textarea>
                                @error('planting_detail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Detail Pemangkasan</label>
                                <textarea name="pruning_detail" class="form-control @error('pruning_detail') is-invalid @enderror" 
                                          rows="3" placeholder="Masukkan detail pemangkasan">{{ old('pruning_detail', $firstPlanting?->pruning_detail) }}</textarea>
                                @error('pruning_detail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Panen Section -->
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
                                           value="{{ old('days_to_flower', $firstPlanting?->days_to_flower) }}" min="0">
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
                                           value="{{ old('days_to_harvest', $firstPlanting?->days_to_harvest) }}" min="0">
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
                                           value="{{ old('harvest_window_days', $firstPlanting?->harvest_window_days) }}" min="0">
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
                                           value="{{ old('expected_loss_rate', $firstPlanting?->expected_loss_rate) }}" step="0.1" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                                @error('expected_loss_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Satuan Panen</label>
                                <select name="harvest_unit" class="form-select @error('harvest_unit') is-invalid @enderror">
                                    <option value="">Pilih satuan</option>
                                    <option value="ikat" {{ old('harvest_unit', $firstPlanting?->harvest_unit) == 'ikat' ? 'selected' : '' }}>Ikat / Gulungan</option>
                                    <option value="barel" {{ old('harvest_unit', $firstPlanting?->harvest_unit) == 'barel' ? 'selected' : '' }}>Barel / Tong</option>
                                    <option value="tandan" {{ old('harvest_unit', $firstPlanting?->harvest_unit) == 'tandan' ? 'selected' : '' }}>Tandan / Ikat</option>
                                    <option value="gantang" {{ old('harvest_unit', $firstPlanting?->harvest_unit) == 'gantang' ? 'selected' : '' }}>Gantang</option>
                                    <option value="lusin" {{ old('harvest_unit', $firstPlanting?->harvest_unit) == 'lusin' ? 'selected' : '' }}>Lusin</option>
                                    <option value="gram" {{ old('harvest_unit', $firstPlanting?->harvest_unit) == 'gram' ? 'selected' : '' }}>Gram</option>
                                    <option value="batang" {{ old('harvest_unit', $firstPlanting?->harvest_unit) == 'batang' ? 'selected' : '' }}>Batang / Kepala</option>
                                    <option value="kilogram" {{ old('harvest_unit', $firstPlanting?->harvest_unit) == 'kilogram' ? 'selected' : '' }}>Kilogram</option>
                                    <option value="kiloliter" {{ old('harvest_unit', $firstPlanting?->harvest_unit) == 'kiloliter' ? 'selected' : '' }}>Kiloliter (1.000 liter)</option>
                                    <option value="liter" {{ old('harvest_unit', $firstPlanting?->harvest_unit) == 'liter' ? 'selected' : '' }}>Liter</option>
                                    <option value="mililiter" {{ old('harvest_unit', $firstPlanting?->harvest_unit) == 'mililiter' ? 'selected' : '' }}>Mililiter</option>
                                    <option value="jumlah" {{ old('harvest_unit', $firstPlanting?->harvest_unit) == 'jumlah' ? 'selected' : '' }}>Jumlah / Satuan</option>
                                    <option value="ton" {{ old('harvest_unit', $firstPlanting?->harvest_unit) == 'ton' ? 'selected' : '' }}>Ton</option>
                                </select>
                                @error('harvest_unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Hasil yang Diharapkan per Periode Penanaman</label>
                                <input type="number" name="expected_yield_per_hectare" class="form-control @error('expected_yield_per_hectare') is-invalid @enderror" 
                                       value="{{ old('expected_yield_per_hectare', $firstPlanting?->expected_yield_per_hectare) }}" step="0.1" min="0">
                                @error('expected_yield_per_hectare')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jumlah yang Ditanam</label>
                                <input type="number" name="quantity_planted" class="form-control @error('quantity_planted') is-invalid @enderror" 
                                       value="{{ old('quantity_planted', $firstPlanting?->quantity_planted) }}" step="0.1" min="0">
                                @error('quantity_planted')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a class="btn btn-secondary" href="{{ route('plants.show', $plant) }}">Batal</a>
                <button class="btn btn-success" type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectElement = document.getElementById('plantTypeSelect');
    
    // Form validation before submit
    const form = document.getElementById('plantForm');
    form.addEventListener('submit', function(e) {
        // Ensure plant_type_id is set
        if (!selectElement.value) {
            e.preventDefault();
            alert('Harap pilih tipe tanaman terlebih dahulu.');
            selectElement.focus();
            return false;
        }
    });

    // Multiple planting locations functionality
    const container = document.getElementById('plantingLocationsContainer');
    const addLocationBtn = document.getElementById('addLocationBtn');
    const locations = @json($locations);

    function updateRemoveButtons() {
        const items = container.querySelectorAll('.planting-location-item');
        items.forEach((item, index) => {
            const removeBtn = item.querySelector('.remove-location-btn');
            if (items.length > 1) {
                removeBtn.style.display = 'block';
            } else {
                removeBtn.style.display = 'none';
            }
        });
    }

    addLocationBtn.addEventListener('click', function() {
        const newItem = document.createElement('div');
        newItem.className = 'planting-location-item mb-2';
        newItem.innerHTML = `
            <div class="d-flex gap-2">
                <select name="planting_location_ids[]" class="form-select planting-location-select">
                    <option value="">Pilih lokasi</option>
                    ${locations.map(loc => `<option value="${loc.id}">${loc.name}</option>`).join('')}
                </select>
                <button type="button" class="btn btn-danger remove-location-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        container.appendChild(newItem);
        updateRemoveButtons();
    });

    container.addEventListener('click', function(e) {
        if (e.target.closest('.remove-location-btn')) {
            e.target.closest('.planting-location-item').remove();
            updateRemoveButtons();
        }
    });
});
</script>
@endpush
@endsection
