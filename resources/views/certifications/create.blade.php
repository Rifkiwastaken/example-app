@extends('layouts.app')

@section('title', 'Form Input Laporan Sertifikasi Benih - SIBESTI')

@section('content')
<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('certifications.index') }}">Manajemen Sertifikasi</a></li>
        <li class="breadcrumb-item active">Tambah Sertifikasi</li>
    </ol>
</nav>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Terjadi kesalahan!</strong>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Form Input Laporan Sertifikasi Benih</h4>
        <small class="text-muted">Input data sertifikasi dan laporan pemeriksaan untuk lot produksi</small>
    </div>
    <a href="{{ route('certifications.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

<form action="{{ route('certifications.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    @if(isset($selectedHarvestId) && $selectedHarvestId)
        <input type="hidden" name="harvest_id" value="{{ $selectedHarvestId }}">
    @endif
    
    @if(isset($redirectPlantId) && $redirectPlantId)
        <input type="hidden" name="plant_id" value="{{ $redirectPlantId }}">
    @endif
    
    <!-- Bagian A: Informasi Dasar Laporan -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Bagian A: Informasi Dasar Laporan</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor Laporan BPSB <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('report_number_bpsb') is-invalid @enderror" 
                           name="report_number_bpsb" id="report_number_bpsb" 
                           value="{{ old('report_number_bpsb', 'BPSB-' . date('Y') . '-' . str_pad(\App\Models\CertificationReport::whereYear('report_date', date('Y'))->count() + 1, 6, '0', STR_PAD_LEFT)) }}" 
                           placeholder="Contoh: Pdg 01.P/L3-21-40/..." required>
                    <small class="text-muted">Nomor batch akan otomatis terisi, namun dapat diubah jika diperlukan</small>
                    @error('report_number_bpsb')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Laporan <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('report_date') is-invalid @enderror" 
                           name="report_date" value="{{ old('report_date', date('Y-m-d')) }}" required>
                    <small class="text-muted">Contoh: 25 Desember 2024</small>
                    @error('report_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Musim Tanam</label>
                    <input type="text" class="form-control @error('growing_season') is-invalid @enderror" 
                           name="growing_season" value="{{ old('growing_season') }}" 
                           placeholder="Contoh: 2024">
                    <small class="text-muted">Contoh: 2024</small>
                    @error('growing_season')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fase Pemeriksaan <span class="text-danger">*</span></label>
                    <select class="form-select @error('inspection_phase') is-invalid @enderror" name="inspection_phase" required>
                        <option value="">Pilih Fase</option>
                        <option value="Vegetatif" {{ old('inspection_phase') == 'Vegetatif' ? 'selected' : '' }}>Vegetatif</option>
                        <option value="Generatif" {{ old('inspection_phase') == 'Generatif' ? 'selected' : '' }}>Generatif</option>
                        <option value="Menjelang Panen" {{ old('inspection_phase') == 'Menjelang Panen' ? 'selected' : '' }}>Menjelang Panen</option>
                        <option value="Lainnya" {{ old('inspection_phase') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    <small class="text-muted">Contoh: Vegetatif</small>
                    @error('inspection_phase')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Petugas Pengawas Mutu (BPSB)</label>
                    <input type="text" class="form-control @error('inspector_name') is-invalid @enderror" 
                           name="inspector_name" value="{{ old('inspector_name') }}" 
                           placeholder="Contoh: Toharwanto">
                    <small class="text-muted">Contoh: Toharwanto</small>
                    @error('inspector_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pengisi Laporan Sertifikasi</label>
                    <input type="text" class="form-control" 
                           value="{{ auth()->user()->name }}" 
                           readonly>
                    <small class="text-muted">User admin yang mengisi laporan</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian B: Tautkan ke Lokasi Produksi -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-link me-2"></i>Bagian B: Tautkan ke Lokasi Produksi</h5>
        </div>
        <div class="card-body">
            @if(isset($harvest) && $harvest)
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Data dari Panen:</strong> Lokasi dan tanaman sudah terisi otomatis dari data panen.
                </div>
            @endif
            
            <div class="mb-3">
                <label class="form-label">Lokasi Produksi <span class="text-danger">*</span></label>
                <select class="form-select @error('planting_location_id') is-invalid @enderror" name="planting_location_id" id="planting_location_id" required {{ isset($harvest) && $harvest ? 'disabled' : '' }}>
                    <option value="">-- Pilih Lokasi Penanaman --</option>
                    @foreach($plantingLocations as $location)
                        <option value="{{ $location->planting_location_id }}" 
                                {{ old('planting_location_id', $selectedPlantingLocationId) == $location->planting_location_id ? 'selected' : '' }}
                                data-location-name="{{ $location->name }}">
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
                @if(isset($harvest) && $harvest)
                    <input type="hidden" name="planting_location_id" value="{{ $selectedPlantingLocationId }}">
                @endif
                <small class="text-muted">Pilih lokasi penanaman yang akan diperiksa. Data diambil dari Lokasi Penanaman.</small>
                @error('planting_location_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Pilih Benih untuk Disertifikasi <span class="text-danger">*</span></label>
                <select class="form-select @error('plant_id') is-invalid @enderror" name="plant_id" id="plant_id" required {{ isset($harvest) && $harvest ? 'disabled' : '' }}>
                    <option value="">-- Pilih Benih --</option>
                    @foreach($plants as $plant)
                        @php
                            $variety = $plant->variety ?: 'Tanpa Varietas';
                            $commodity = $plant->type?->name ?: $plant->name;
                        @endphp
                        <option value="{{ $plant->plant_id }}" 
                                {{ old('plant_id', $selectedPlantId) == $plant->plant_id ? 'selected' : '' }}
                                data-commodity="{{ $commodity }}"
                                data-variety="{{ $variety }}">
                            {{ $commodity }} - {{ $variety }}
                        </option>
                    @endforeach
                </select>
                @if(isset($harvest) && $harvest)
                    <input type="hidden" name="plant_id" value="{{ $selectedPlantId }}">
                @endif
                <small class="text-muted">Pilih benih yang akan disertifikasi. Data diambil dari Tanaman Saya.</small>
                @error('plant_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Kelas Benih yang Diajukan -->
            <div class="mb-3">
                <label class="form-label">Kelas Benih yang Diajukan</label>
                <select class="form-select @error('seed_class_requested') is-invalid @enderror" name="seed_class_requested">
                    <option value="BP" {{ old('seed_class_requested', 'BP') == 'BP' ? 'selected' : '' }}>BP (Benih Pokok)</option>
                    <option value="BS" {{ old('seed_class_requested') == 'BS' ? 'selected' : '' }}>BS (Benih Dasar)</option>
                    <option value="BR" {{ old('seed_class_requested') == 'BR' ? 'selected' : '' }}>BR (Benih Sebar)</option>
                </select>
                <small class="text-muted">Default: BP (Benih Pokok)</small>
                @error('seed_class_requested')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Nomor Batch Tanam -->
            <div class="mb-3">
                <label class="form-label">Nomor Batch Tanam</label>
                <input type="text" class="form-control @error('planting_batch_number') is-invalid @enderror" 
                       name="planting_batch_number" id="planting_batch_number"
                       value="{{ old('planting_batch_number', isset($harvest) && $harvest && $harvest->planting ? $harvest->planting->planting_batch_number : '') }}" 
                       readonly>
                <small class="text-muted">Otomatis terisi dari data panen yang dipilih</small>
                @error('planting_batch_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Nomor Batch Panen -->
            <div class="mb-3">
                <label class="form-label">Nomor Batch Panen</label>
                <input type="text" class="form-control @error('harvest_batch_number') is-invalid @enderror" 
                       name="harvest_batch_number" id="harvest_batch_number"
                       value="{{ old('harvest_batch_number', isset($harvest) && $harvest ? $harvest->batch_no : '') }}" 
                       readonly>
                <small class="text-muted">Otomatis terisi dari data panen yang dipilih</small>
                @error('harvest_batch_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Auto-fill Info Display -->
            <div id="selectionInfo" class="card bg-light" style="display: none;">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Informasi yang Dipilih</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">Lokasi Produksi:</small>
                            <div id="infoLocation" class="fw-bold">-</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Komoditas:</small>
                            <div id="infoCommodity" class="fw-bold">-</div>
                        </div>
                        <div class="col-md-6 mt-2">
                            <small class="text-muted">Varietas:</small>
                            <div id="infoVariety" class="fw-bold">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian C: Hasil Pemeriksaan -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Bagian C: Hasil Pemeriksaan</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kelas Benih yang Dihasilkan</label>
                    <select class="form-select @error('seed_class_result') is-invalid @enderror" name="seed_class_result">
                        <option value="">Pilih Kelas</option>
                        <option value="BS" {{ old('seed_class_result') == 'BS' ? 'selected' : '' }}>BS (Benih Dasar)</option>
                        <option value="BP" {{ old('seed_class_result') == 'BP' ? 'selected' : '' }}>BP (Benih Pokok)</option>
                        <option value="BR" {{ old('seed_class_result') == 'BR' ? 'selected' : '' }}>BR (Benih Sebar)</option>
                    </select>
                    <small class="text-muted">Contoh: BP</small>
                    @error('seed_class_result')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sifat Tanaman Sesuai Varietas</label>
                    <div class="mt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="plant_characteristics_match" id="match_yes" value="1" {{ old('plant_characteristics_match') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="match_yes">Ya</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="plant_characteristics_match" id="match_no" value="0" {{ old('plant_characteristics_match') == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="match_no">Tidak</label>
                        </div>
                    </div>
                    <small class="text-muted">Contoh: Ya</small>
                    @error('plant_characteristics_match')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Isolasi - Utara</label>
                    <input type="text" class="form-control @error('isolation_north') is-invalid @enderror" 
                           name="isolation_north" value="{{ old('isolation_north') }}" 
                           placeholder="Contoh: Sawah">
                    <small class="text-muted">Contoh: Sawah</small>
                    @error('isolation_north')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Isolasi - Timur</label>
                    <input type="text" class="form-control @error('isolation_east') is-invalid @enderror" 
                           name="isolation_east" value="{{ old('isolation_east') }}" 
                           placeholder="Contoh: Sawah">
                    <small class="text-muted">Contoh: Sawah</small>
                    @error('isolation_east')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Isolasi - Selatan</label>
                    <input type="text" class="form-control @error('isolation_south') is-invalid @enderror" 
                           name="isolation_south" value="{{ old('isolation_south') }}" 
                           placeholder="Contoh: Sawah">
                    <small class="text-muted">Contoh: Sawah</small>
                    @error('isolation_south')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Isolasi - Barat</label>
                    <input type="text" class="form-control @error('isolation_west') is-invalid @enderror" 
                           name="isolation_west" value="{{ old('isolation_west') }}" 
                           placeholder="Contoh: Sawah">
                    <small class="text-muted">Contoh: Sawah</small>
                    @error('isolation_west')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-12 mb-3">
                    <label class="form-label">Keadaan Hama dan Penyakit</label>
                    <textarea class="form-control @error('pest_disease_condition') is-invalid @enderror" 
                              name="pest_disease_condition" rows="3" 
                              placeholder="Deskripsi keadaan hama dan penyakit">{{ old('pest_disease_condition') }}</textarea>
                    <small class="text-muted">Contoh: Aman dari gejala hama penyakit</small>
                    @error('pest_disease_condition')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Keadaan Rerumputan</label>
                    <select class="form-select @error('weed_condition') is-invalid @enderror" name="weed_condition">
                        <option value="">Pilih Kondisi</option>
                        <option value="Bersih" {{ old('weed_condition') == 'Bersih' ? 'selected' : '' }}>Bersih</option>
                        <option value="Cukup Bersih" {{ old('weed_condition') == 'Cukup Bersih' ? 'selected' : '' }}>Cukup Bersih</option>
                        <option value="Kotor" {{ old('weed_condition') == 'Kotor' ? 'selected' : '' }}>Kotor</option>
                    </select>
                    <small class="text-muted">Contoh: Bersih</small>
                    @error('weed_condition')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Populasi per Contoh Pemeriksaan</label>
                    <input type="number" class="form-control @error('population_per_sample') is-invalid @enderror" 
                           name="population_per_sample" value="{{ old('population_per_sample') }}" 
                           min="0" placeholder="Jumlah">
                    <small class="text-muted">Contoh: 200 (batang/rumpun)</small>
                    @error('population_per_sample')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jumlah Temuan Campuran Varietas Lain</label>
                    <input type="number" class="form-control @error('other_variety_mix_count') is-invalid @enderror" 
                           name="other_variety_mix_count" value="{{ old('other_variety_mix_count') }}" 
                           min="0" placeholder="Jumlah">
                    <small class="text-muted">Contoh: 2 (dari total 16 kotak)</small>
                    @error('other_variety_mix_count')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Rata-rata Campuran Varietas Lain (%)</label>
                    <input type="number" class="form-control @error('other_variety_mix_percentage') is-invalid @enderror" 
                           name="other_variety_mix_percentage" value="{{ old('other_variety_mix_percentage') }}" 
                           step="0.01" min="0" max="100" placeholder="0.00">
                    <small class="text-muted">Contoh: 0.3 %</small>
                    @error('other_variety_mix_percentage')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Taksiran Hasil</label>
                    <input type="number" class="form-control @error('estimated_yield') is-invalid @enderror" 
                           name="estimated_yield" value="{{ old('estimated_yield') }}" 
                           step="0.01" min="0" placeholder="0.00">
                    <small class="text-muted">Input sebagai Ton/ha</small>
                    @error('estimated_yield')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Masa Edar / Kadaluarsa <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                           name="expiry_date" value="{{ old('expiry_date') }}" required>
                    <small class="text-muted">Diisi berdasarkan sertifikat yang dikeluarkan oleh BPSB</small>
                    @error('expiry_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian D: Jumlah Benih yang Lulus Sertifikasi -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-seedling me-2"></i>Bagian D: Jumlah Benih yang Lulus Sertifikasi</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Satuan Inventaris</label>
                    <select class="form-select @error('seed_unit') is-invalid @enderror" name="seed_unit" id="seed_unit">
                        <option value="">Pilih Satuan</option>
                        <option value="kg" {{ old('seed_unit') == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                        <option value="ton" {{ old('seed_unit') == 'ton' ? 'selected' : '' }}>Ton</option>
                        <option value="gram" {{ old('seed_unit') == 'gram' ? 'selected' : '' }}>Gram</option>
                        <option value="butir" {{ old('seed_unit') == 'butir' ? 'selected' : '' }}>Butir/Biji</option>
                        <option value="pcs" {{ old('seed_unit') == 'pcs' ? 'selected' : '' }}>Pcs</option>
                        <option value="batang" {{ old('seed_unit') == 'batang' ? 'selected' : '' }}>Batang</option>
                    </select>
                    <small class="text-muted">Pilih satuan inventaris</small>
                    @error('seed_unit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Total Inventaris</label>
                    <input type="number" class="form-control @error('certified_seed_quantity') is-invalid @enderror" 
                           name="certified_seed_quantity" id="certified_seed_quantity" value="{{ old('certified_seed_quantity') }}" 
                           step="0.01" min="0" placeholder="0.00">
                    <input type="hidden" name="certified_seed_unit" id="certified_seed_unit" value="{{ old('certified_seed_unit', 'kg') }}">
                    <small class="text-muted">Masukkan total inventaris</small>
                    @error('certified_seed_quantity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @error('certified_seed_unit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Estimasi Penjualan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control @error('estimated_sale_price_per_kg') is-invalid @enderror" 
                               name="estimated_sale_price_per_kg" value="{{ old('estimated_sale_price_per_kg') }}" 
                               step="0.01" min="0" placeholder="0.00">
                    </div>
                    <small class="text-muted">Masukkan estimasi harga penjualan</small>
                    @error('estimated_sale_price_per_kg')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian E: Kesimpulan & Lampiran -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Bagian E: Kesimpulan & Lampiran</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kesimpulan / Rekomendasi <span class="text-danger">*</span></label>
                    <div class="mt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="conclusion" id="conclusion_lulus" value="LULUS" {{ old('conclusion') == 'LULUS' ? 'checked' : '' }} required>
                            <label class="form-check-label text-success fw-bold" for="conclusion_lulus">LULUS</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="conclusion" id="conclusion_tidak_lulus" value="TIDAK LULUS" {{ old('conclusion') == 'TIDAK LULUS' ? 'checked' : '' }} required>
                            <label class="form-check-label text-danger fw-bold" for="conclusion_tidak_lulus">TIDAK LULUS</label>
                        </div>
                    </div>
                    <small class="text-muted">Contoh: LULUS</small>
                    @error('conclusion')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Unggah Pindaian Laporan (Scan)</label>
                    <input type="file" class="form-control @error('scan_file') is-invalid @enderror" 
                           name="scan_file" accept=".pdf,.jpg,.jpeg,.png">
                    <small class="text-muted">Format: PDF, JPG, PNG (Maks: 10MB)</small>
                    @error('scan_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex justify-content-between mb-4">
        <a href="{{ route('certifications.index') }}" class="btn btn-secondary">
            <i class="fas fa-times me-2"></i>Batal
        </a>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save me-2"></i>Simpan Sertifikasi & Laporan
        </button>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const locationSelect = document.getElementById('planting_location_id');
    const plantSelect = document.getElementById('plant_id');
    const selectionInfo = document.getElementById('selectionInfo');
    const reportNumberBpsbInput = document.getElementById('report_number_bpsb');
    
    // Auto-generate report number BPSB if field is empty
    if (reportNumberBpsbInput && (!reportNumberBpsbInput.value || reportNumberBpsbInput.value.trim() === '')) {
        const today = new Date();
        const year = today.getFullYear();
        @php
            $reportCount = \App\Models\CertificationReport::whereYear('report_date', date('Y'))->count() + 1;
        @endphp
        const reportCount = {{ $reportCount }};
        const reportNo = 'BPSB-' + year + '-' + String(reportCount).padStart(6, '0');
        reportNumberBpsbInput.value = reportNo;
    }
    
    function updateSelectionInfo() {
        const locationOption = locationSelect.options[locationSelect.selectedIndex];
        const plantOption = plantSelect.options[plantSelect.selectedIndex];
        
        if (locationOption && locationOption.value && plantOption && plantOption.value) {
            document.getElementById('infoLocation').textContent = locationOption.dataset.locationName || locationOption.textContent;
            document.getElementById('infoCommodity').textContent = plantOption.dataset.commodity || '-';
            document.getElementById('infoVariety').textContent = plantOption.dataset.variety || '-';
            selectionInfo.style.display = 'block';
        } else {
            selectionInfo.style.display = 'none';
        }
    }
    
    if (locationSelect && plantSelect && selectionInfo) {
        // Show info if already selected
        if (locationSelect.value && plantSelect.value) {
            updateSelectionInfo();
        }
        
        locationSelect.addEventListener('change', updateSelectionInfo);
        plantSelect.addEventListener('change', updateSelectionInfo);
    }

    // Sync unit from seed_unit to certified_seed_unit (hidden field)
    const seedUnit = document.getElementById('seed_unit');
    const certifiedSeedUnit = document.getElementById('certified_seed_unit');

    function syncUnit() {
        if (seedUnit && certifiedSeedUnit) {
            const selectedUnit = seedUnit.value || 'kg';
            certifiedSeedUnit.value = selectedUnit;
        }
    }

    if (seedUnit) {
        seedUnit.addEventListener('change', syncUnit);
        // Sync on page load
        syncUnit();
    }
});
</script>
@endpush
@endsection

