@extends('layouts.app')

@section('title', 'Tambah Tanaman - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Tanaman Baru</h4>
    <a href="{{ route('plants.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
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
        
        <form method="POST" action="{{ route('plants.store') }}" id="plantForm">
            @csrf
            
            <!-- Bagian 1: Data Tanaman -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Data Tanaman</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Nama Tanaman <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2">
                                    <select name="plant_type_id" id="plantTypeSelect" class="form-select @error('plant_type_id') is-invalid @enderror" required>
                                        <option value="">Pilih tanaman</option>
                                        @foreach($types as $type)
                                            @php
                                                // Get first variety from the list
                                                $varieties = $type->variety ? preg_split('/[\n,]+/', $type->variety) : [];
                                                $firstVariety = count($varieties) > 0 ? trim($varieties[0]) : '';
                                            @endphp
                                            <option value="{{ $type->plant_type_id }}" 
                                                    data-name="{{ $type->name }}" 
                                                    data-category="{{ $type->category }}"
                                                    data-variety="{{ $type->variety }}"
                                                    {{ old('plant_type_id') == $type->plant_type_id ? 'selected' : '' }}>
                                                {{ $firstVariety ? $firstVariety.' - ' : '' }}{{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if(auth()->user()->role !== 'penangkar')
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPlantTypeModal">Tambahkan tipe tanaman</button>
                                    @endif
                                </div>
                                @error('plant_type_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Varietas</label>
                                <select name="variety" id="varietySelect" class="form-select @error('variety') is-invalid @enderror" disabled>
                                    <option value="">Pilih tanaman terlebih dahulu</option>
                                </select>
                                @error('variety')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted">Varietas akan muncul setelah memilih tanaman</small>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Lokasi Penanaman</label>
                                <div id="plantingLocationsContainer">
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

            <!-- Bagian 2: Detail Tanaman -->
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
                                           value="{{ old('days_to_emerge') }}" min="0">
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
                                           value="{{ old('spacing_between_plants') }}" step="0.1" min="0">
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
                                           value="{{ old('spacing_between_rows') }}" step="0.1" min="0">
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
                                           value="{{ old('sowing_depth') }}" step="0.1" min="0">
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
                                           value="{{ old('avg_height') }}" step="0.1" min="0">
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
                                <label class="form-label">Detail Tanaman</label>
                                <textarea name="planting_detail" class="form-control @error('planting_detail') is-invalid @enderror" 
                                          rows="3" placeholder="Masukkan detail tanaman">{{ old('planting_detail') }}</textarea>
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
                                <label class="form-label">Hasil yang Diharapkan per Periode Penanaman</label>
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
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a class="btn btn-secondary" href="{{ route('plants.index') }}">Batal</a>
                <button class="btn btn-success" type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Tambah Tipe Tanaman -->
<div class="modal fade" id="addPlantTypeModal" tabindex="-1" aria-labelledby="addPlantTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addPlantTypeForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addPlantTypeModalLabel">Tambahkan Tipe Tanaman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Tanaman <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="plantTypeName" class="form-control" required>
                        <div class="invalid-feedback" id="nameError"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Varietas <span class="text-danger">*</span></label>
                        <textarea name="variety" id="plantTypeVariety" class="form-control" rows="3" required placeholder="Masukkan varietas (pisahkan dengan enter untuk multiple varietas)"></textarea>
                        <small class="text-muted">Contoh: Varietas A, Varietas B, Varietas C atau pisahkan dengan enter</small>
                        <div class="invalid-feedback" id="varietyError"></div>
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
    const varietySelect = document.getElementById('varietySelect');
    
    // Auto-fill variety when plant type is selected
    if (selectElement && varietySelect) {
        selectElement.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const plantTypeId = this.value;
            
            // Reset variety select
            varietySelect.innerHTML = '<option value="">Memuat varietas...</option>';
            varietySelect.disabled = true;
            
            if (!plantTypeId) {
                varietySelect.innerHTML = '<option value="">Pilih tanaman terlebih dahulu</option>';
                return;
            }
            
            // Get variety from data attribute first (faster)
            const varietyData = selectedOption.getAttribute('data-variety');
            
            if (varietyData) {
                // Split variety by newline or comma
                const varieties = varietyData.split(/[\n,]+/).map(v => v.trim()).filter(v => v);
                
                // Clear and populate variety select
                varietySelect.innerHTML = '';
                
                if (varieties.length > 0) {
                    varieties.forEach((variety, index) => {
                        const option = document.createElement('option');
                        option.value = variety;
                        option.textContent = variety;
                        // Auto-select first variety
                        if (index === 0) {
                            option.selected = true;
                        }
                        varietySelect.appendChild(option);
                    });
                    varietySelect.disabled = false;
                } else {
                    varietySelect.innerHTML = '<option value="">Tidak ada varietas tersedia</option>';
                }
            } else {
                // Fallback to API fetch
                fetch(`/api/plant-types/${plantTypeId}/variety`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.variety) {
                        // Split variety by newline or comma
                        const varieties = data.variety.split(/[\n,]+/).map(v => v.trim()).filter(v => v);
                        
                        // Clear and populate variety select
                        varietySelect.innerHTML = '';
                        
                        if (varieties.length > 0) {
                            varieties.forEach((variety, index) => {
                                const option = document.createElement('option');
                                option.value = variety;
                                option.textContent = variety;
                                // Auto-select first variety
                                if (index === 0) {
                                    option.selected = true;
                                }
                                varietySelect.appendChild(option);
                            });
                            varietySelect.disabled = false;
                        } else {
                            varietySelect.innerHTML = '<option value="">Tidak ada varietas tersedia</option>';
                        }
                    } else {
                        varietySelect.innerHTML = '<option value="">Tidak ada varietas tersedia</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching variety:', error);
                    varietySelect.innerHTML = '<option value="">Error memuat varietas</option>';
                });
            }
        });
        
        // Trigger change if there's an old value selected
        if (selectElement.value) {
            selectElement.dispatchEvent(new Event('change'));
        }
    }
    
    // Form validation before submit
    const form = document.getElementById('plantForm');
    form.addEventListener('submit', function(e) {
        // Ensure plant_type_id is set
        if (!selectElement.value) {
            e.preventDefault();
            alert('Harap pilih tanaman terlebih dahulu.');
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
                    ${locations.map(loc => `<option value="${loc.planting_location_id}">${loc.name}</option>`).join('')}
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

    // Listen for new location added in popup window
    window.addEventListener('storage', function(e) {
        if (e.key === 'new_planting_location') {
            const newLocation = JSON.parse(e.newValue);
            // Reload page to get updated locations list
            window.location.reload();
        }
    });

    // Check if we're returning from creating a new location
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('location_created') === '1') {
        // Reload to get updated locations
        window.location.href = window.location.pathname;
    }

    // Handle Add Plant Type Modal Form
    const addPlantTypeModal = document.getElementById('addPlantTypeModal');
    const addPlantTypeForm = document.getElementById('addPlantTypeForm');
    
    // Function to toggle category custom field
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
    
    // Reset form and clear errors when modal is opened
    if (addPlantTypeModal) {
        addPlantTypeModal.addEventListener('show.bs.modal', function() {
            addPlantTypeForm.reset();
            document.getElementById('nameError').textContent = '';
            document.getElementById('varietyError').textContent = '';
            document.getElementById('categoryError').textContent = '';
            document.getElementById('plantTypeName').classList.remove('is-invalid');
            document.getElementById('plantTypeVariety').classList.remove('is-invalid');
            document.getElementById('plantTypeCategory').classList.remove('is-invalid');
            if (document.getElementById('category_custom_container')) {
                document.getElementById('category_custom_container').style.display = 'none';
            }
        });
    }
    
    if (addPlantTypeForm) {
        addPlantTypeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Clear previous errors
            document.getElementById('nameError').textContent = '';
            document.getElementById('varietyError').textContent = '';
            document.getElementById('categoryError').textContent = '';
            document.getElementById('plantTypeName').classList.remove('is-invalid');
            document.getElementById('plantTypeVariety').classList.remove('is-invalid');
            document.getElementById('plantTypeCategory').classList.remove('is-invalid');
            
            const formData = new FormData(this);
            
            // Handle category: if "lainnya" is selected, use category_custom value
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
                    // Add new option to select
                    const select = document.getElementById('plantTypeSelect');
                    const option = document.createElement('option');
                    option.value = data.plant_type.plant_type_id;
                    option.setAttribute('data-name', data.plant_type.name);
                    option.setAttribute('data-category', data.plant_type.category || '');
                    option.setAttribute('data-variety', data.plant_type.variety || '');
                    
                    // Get first variety for display
                    const varieties = data.plant_type.variety ? data.plant_type.variety.split(/[\n,]+/).map(v => v.trim()).filter(v => v) : [];
                    const firstVariety = varieties.length > 0 ? varieties[0] : '';
                    
                    option.textContent = (firstVariety ? firstVariety + ' - ' : '') + data.plant_type.name;
                    option.selected = true;
                    select.appendChild(option);
                    
                    // Trigger change event to load varieties
                    select.dispatchEvent(new Event('change'));
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addPlantTypeModal'));
                    modal.hide();
                    
                    // Reset form
                    addPlantTypeForm.reset();
                    
                    // Show success message
                    alert('Tipe tanaman berhasil ditambahkan!');
                }
            })
            .catch(error => {
                // Handle validation errors
                if (error.errors) {
                    if (error.errors.name) {
                        document.getElementById('plantTypeName').classList.add('is-invalid');
                        document.getElementById('nameError').textContent = error.errors.name[0];
                    }
                    if (error.errors.variety) {
                        document.getElementById('plantTypeVariety').classList.add('is-invalid');
                        document.getElementById('varietyError').textContent = error.errors.variety[0];
                    }
                    if (error.errors.category) {
                        document.getElementById('plantTypeCategory').classList.add('is-invalid');
                        document.getElementById('categoryError').textContent = error.errors.category[0];
                    }
                } else {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menambahkan tipe tanaman.');
                }
            });
        });
    }
});
</script>
@endpush
@endsection
