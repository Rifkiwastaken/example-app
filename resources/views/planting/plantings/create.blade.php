@extends('layouts.app')

@section('title', 'Tambahkan Penanaman - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Tambahkan Penanaman</h4>
    <a href="{{ route('plants.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('plantings.store') }}">
            @csrf
            
            <!-- Plant Selection -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Pilih Tanaman</label>
                        <select name="plant_id" class="form-select @error('plant_id') is-invalid @enderror" required>
                            <option value="">Pilih tanaman</option>
                            @foreach($plants as $plant)
                                <option value="{{ $plant->id }}" {{ (old('plant_id') == $plant->id || $selectedPlant?->id == $plant->id) ? 'selected' : '' }}>
                                    {{ $plant->name }} - {{ $plant->variety ?: 'Tidak ada varietas' }}
                                </option>
                            @endforeach
                        </select>
                        @error('plant_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Lokasi Penanaman</label>
                        <select name="planting_location_id" class="form-select @error('planting_location_id') is-invalid @enderror" required>
                            <option value="">Pilih lokasi</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" {{ old('planting_location_id') == $location->id ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('planting_location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Detail Penanaman Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Detail Penanaman</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Hari Sampai Muncul</label>
                                <div class="input-group">
                                    <input type="number" name="days_to_emerge" class="form-control @error('days_to_emerge') is-invalid @enderror" 
                                           value="{{ old('days_to_emerge') }}" min="0">
                                    <select class="form-select" style="max-width: 100px;">
                                        <option value="days">Hari</option>
                                        <option value="weeks">Minggu</option>
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
                                           value="{{ old('spacing_between_plants') }}" step="0.1" min="0">
                                    <select class="form-select" style="max-width: 100px;">
                                        <option value="cm">cm</option>
                                        <option value="m">m</option>
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
                                           value="{{ old('spacing_between_rows') }}" step="0.1" min="0">
                                    <select class="form-select" style="max-width: 100px;">
                                        <option value="cm">cm</option>
                                        <option value="m">m</option>
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
                                           value="{{ old('sowing_depth') }}" step="0.1" min="0">
                                    <select class="form-select" style="max-width: 100px;">
                                        <option value="cm">cm</option>
                                        <option value="m">m</option>
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
                                           value="{{ old('avg_height') }}" step="0.1" min="0">
                                    <select class="form-select" style="max-width: 100px;">
                                        <option value="cm">cm</option>
                                        <option value="m">m</option>
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
                                    <option value="Tanam Langsung" {{ old('start_method') == 'Tanam Langsung' ? 'selected' : '' }}>Tanam Langsung</option>
                                    <option value="Mulai di baki semai" {{ old('start_method') == 'Mulai di baki semai' ? 'selected' : '' }}>Mulai di baki semai</option>
                                    <option value="Pindahkan ke tanah" {{ old('start_method') == 'Pindahkan ke tanah' ? 'selected' : '' }}>Pindahkan ke tanah</option>
                                    <option value="Pindah tanaman (transplant)" {{ old('start_method') == 'Pindah tanaman (transplant)' ? 'selected' : '' }}>Pindah tanaman (transplant)</option>
                                    <option value="Dalam pot (container)" {{ old('start_method') == 'Dalam pot (container)' ? 'selected' : '' }}>Dalam pot (container)</option>
                                    <option value="Ditanam di baki semai" {{ old('start_method') == 'Ditanam di baki semai' ? 'selected' : '' }}>Ditanam di baki semai</option>
                                    <option value="Batang bawah/ tanaman induk" {{ old('start_method') == 'Batang bawah/ tanaman induk' ? 'selected' : '' }}>Batang bawah/ tanaman induk</option>
                                    <option value="Umbi" {{ old('start_method') == 'Umbi' ? 'selected' : '' }}>Umbi</option>
                                    <option value="Sambung/okulasi" {{ old('start_method') == 'Sambung/okulasi' ? 'selected' : '' }}>Sambung/okulasi</option>
                                    <option value="Lainnya" {{ old('start_method') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
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
                                    <option value="benih ditanam" {{ old('germination_stage') == 'benih ditanam' ? 'selected' : '' }}>Benih ditanam</option>
                                    <option value="perkecambahan" {{ old('germination_stage') == 'perkecambahan' ? 'selected' : '' }}>Perkecambahan</option>
                                    <option value="bibit/ tunas muda" {{ old('germination_stage') == 'bibit/ tunas muda' ? 'selected' : '' }}>Bibit/ tunas muda</option>
                                    <option value="sudah ditanam" {{ old('germination_stage') == 'sudah ditanam' ? 'selected' : '' }}>Sudah ditanam</option>
                                    <option value="fase vegetatif" {{ old('germination_stage') == 'fase vegetatif' ? 'selected' : '' }}>Fase vegetatif</option>
                                    <option value="berbunga" {{ old('germination_stage') == 'berbunga' ? 'selected' : '' }}>Berbunga</option>
                                    <option value="pematangan buah" {{ old('germination_stage') == 'pematangan buah' ? 'selected' : '' }}>Pematangan buah</option>
                                    <option value="selesai" {{ old('germination_stage') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                </select>
                                @error('germination_stage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Benih per Lubang</label>
                                <input type="number" name="seeds_per_hole" class="form-control @error('seeds_per_hole') is-invalid @enderror" 
                                       value="{{ old('seeds_per_hole', 1) }}" min="1">
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
                                    <option value="sinar matahari penuh" {{ old('light_profile') == 'sinar matahari penuh' ? 'selected' : '' }}>Sinar matahari penuh</option>
                                    <option value="sinar matahari penuh sebagian" {{ old('light_profile') == 'sinar matahari penuh sebagian' ? 'selected' : '' }}>Sinar matahari penuh sebagian</option>
                                    <option value="sinar matahari sebagian" {{ old('light_profile') == 'sinar matahari sebagian' ? 'selected' : '' }}>Sinar matahari sebagian</option>
                                    <option value="matahari hingga setengah teduh" {{ old('light_profile') == 'matahari hingga setengah teduh' ? 'selected' : '' }}>Matahari hingga setengah teduh</option>
                                    <option value="setengah teduh" {{ old('light_profile') == 'setengah teduh' ? 'selected' : '' }}>Setengah teduh</option>
                                    <option value="teduh sepenuhnya" {{ old('light_profile') == 'teduh sepenuhnya' ? 'selected' : '' }}>Teduh sepenuhnya</option>
                                </select>
                                @error('light_profile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Kondisi Tanah</label>
                                <select name="soil_condition" class="form-select @error('soil_condition') is-invalid @enderror">
                                    <option value="">Pilih kondisi tanah</option>
                                    <option value="tanah berkapur" {{ old('soil_condition') == 'tanah berkapur' ? 'selected' : '' }}>Tanah berkapur</option>
                                    <option value="tanah liat" {{ old('soil_condition') == 'tanah liat' ? 'selected' : '' }}>Tanah liat</option>
                                    <option value="tanah lempung" {{ old('soil_condition') == 'tanah lempung' ? 'selected' : '' }}>Tanah lempung</option>
                                    <option value="tanah gambut" {{ old('soil_condition') == 'tanah gambut' ? 'selected' : '' }}>Tanah gambut</option>
                                    <option value="tanah berpasir" {{ old('soil_condition') == 'tanah berpasir' ? 'selected' : '' }}>Tanah berpasir</option>
                                    <option value="tanah lanau" {{ old('soil_condition') == 'tanah lanau' ? 'selected' : '' }}>Tanah lanau</option>
                                </select>
                                @error('soil_condition')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Detail Penanaman</label>
                                <textarea name="planting_detail" class="form-control @error('planting_detail') is-invalid @enderror" 
                                          rows="3" placeholder="Masukkan detail penanaman">{{ old('planting_detail') }}</textarea>
                                @error('planting_detail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Detail Pemangkasan</label>
                                <textarea name="pruning_detail" class="form-control @error('pruning_detail') is-invalid @enderror" 
                                          rows="3" placeholder="Masukkan detail pemangkasan">{{ old('pruning_detail') }}</textarea>
                                @error('pruning_detail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="perennial" value="1" 
                                       {{ old('perennial') ? 'checked' : '' }}>
                                <label class="form-check-label">Tanaman Tahunan</label>
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
                                           value="{{ old('days_to_flower') }}" min="0">
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
                                           value="{{ old('days_to_harvest') }}" min="0">
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
                                           value="{{ old('harvest_window_days') }}" min="0">
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
                                           value="{{ old('expected_loss_rate') }}" step="0.1" min="0" max="100">
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
                                    <option value="ikat" {{ old('harvest_unit') == 'ikat' ? 'selected' : '' }}>Ikat / Gulungan</option>
                                    <option value="barel" {{ old('harvest_unit') == 'barel' ? 'selected' : '' }}>Barel / Tong</option>
                                    <option value="tandan" {{ old('harvest_unit') == 'tandan' ? 'selected' : '' }}>Tandan / Ikat</option>
                                    <option value="gantang" {{ old('harvest_unit') == 'gantang' ? 'selected' : '' }}>Gantang</option>
                                    <option value="lusin" {{ old('harvest_unit') == 'lusin' ? 'selected' : '' }}>Lusin</option>
                                    <option value="gram" {{ old('harvest_unit') == 'gram' ? 'selected' : '' }}>Gram</option>
                                    <option value="batang" {{ old('harvest_unit') == 'batang' ? 'selected' : '' }}>Batang / Kepala</option>
                                    <option value="kilogram" {{ old('harvest_unit') == 'kilogram' ? 'selected' : '' }}>Kilogram</option>
                                    <option value="kiloliter" {{ old('harvest_unit') == 'kiloliter' ? 'selected' : '' }}>Kiloliter (1.000 liter)</option>
                                    <option value="liter" {{ old('harvest_unit') == 'liter' ? 'selected' : '' }}>Liter</option>
                                    <option value="mililiter" {{ old('harvest_unit') == 'mililiter' ? 'selected' : '' }}>Mililiter</option>
                                    <option value="jumlah" {{ old('harvest_unit') == 'jumlah' ? 'selected' : '' }}>Jumlah / Satuan</option>
                                    <option value="ton" {{ old('harvest_unit') == 'ton' ? 'selected' : '' }}>Ton</option>
                                </select>
                                @error('harvest_unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Hasil yang Diharapkan per Hektar</label>
                                <input type="number" name="expected_yield_per_hectare" class="form-control @error('expected_yield_per_hectare') is-invalid @enderror" 
                                       value="{{ old('expected_yield_per_hectare') }}" step="0.1" min="0">
                                @error('expected_yield_per_hectare')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jumlah yang Ditanam</label>
                                <input type="number" name="quantity_planted" class="form-control @error('quantity_planted') is-invalid @enderror" 
                                       value="{{ old('quantity_planted') }}" step="0.1" min="0">
                                @error('quantity_planted')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Ditanam</label>
                                <input type="date" name="planted_at" class="form-control @error('planted_at') is-invalid @enderror" 
                                       value="{{ old('planted_at', date('Y-m-d')) }}">
                                @error('planted_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-end gap-2">
                <a class="btn btn-secondary" href="{{ route('plants.index') }}">Batal</a>
                <button class="btn btn-success" type="submit"><i class="fas fa-save me-2"></i>Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection













