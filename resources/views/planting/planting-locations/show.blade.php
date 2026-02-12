@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Detail Lokasi Penanaman - ' . $plantingLocation->name . ' - SIBESTI')

@push('styles')
<style>
    .nav-pills .nav-link {
        transition: all 0.3s ease;
    }
    .nav-pills .nav-link:not(.active) {
        background-color: #e9ecef !important;
        color: #495057 !important;
        opacity: 1 !important;
    }
    .nav-pills .nav-link.active {
        background-color: #0d6efd !important;
        color: #ffffff !important;
        opacity: 1 !important;
    }
    .tab-content {
        opacity: 1 !important;
    }
    .tab-pane {
        opacity: 1 !important;
    }
    .tab-pane.fade:not(.show) {
        display: none !important;
    }
    .tab-pane.fade.show {
        display: block !important;
        opacity: 1 !important;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $plantingLocation->name }}</h4>
    </div>
    <a href="{{ route('planting-locations.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('planting-locations.show', $plantingLocation) }}">
            <i class="fas fa-info-circle me-1"></i>Detail & Lokasi Penanaman
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('planting-locations.plantings.index', $plantingLocation) }}">
            <i class="fas fa-seedling me-1"></i>Penanaman
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('planting-locations.planting-history', $plantingLocation) }}">
            <i class="fas fa-history me-1"></i>Riwayat Penanaman
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('planting-locations.expenses.index', $plantingLocation) }}">
            <i class="fas fa-money-bill-wave me-1"></i>Pengeluaran
        </a>
    </li>
</ul>

<div class="tab-content p-3 bg-white border border-top-0 rounded-bottom">
    <!-- Tab: Detail Lokasi dan Penanaman -->
    <div class="tab-pane fade show active" id="detail-dan-penanaman">
        <!-- Detail Lokasi Penanaman -->
        <div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Detail Lokasi Penanaman</h6>
                    @if(auth()->user()->isAdmin() || auth()->user()->canManagePlantingLocation($plantingLocation))
                        <a href="{{ route('planting-locations.edit', $plantingLocation) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                    @endif
                </div>

                <!-- Informasi Umum -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Umum</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Nama Lahan:</strong><br>
                                    <span>{{ $plantingLocation->name }}</span>
                                </div>
                                @if($plantingLocation->internal_id)
                                <div class="mb-3">
                                    <strong>Internal Id:</strong><br>
                                    <span>{{ $plantingLocation->internal_id }}</span>
                                </div>
                                @endif
                                @if($plantingLocation->electronic_id)
                                <div class="mb-3">
                                    <strong>Electronic Id:</strong><br>
                                    <span>{{ $plantingLocation->electronic_id }}</span>
                                </div>
                                @endif
                                <div class="mb-3">
                                    <strong>Tipe Lahan:</strong><br>
                                    <span>{{ ucfirst(str_replace('_', ' ', $plantingLocation->location_type ?? '-')) }}</span>
                                </div>
                                @if($plantingLocation->location_summary)
                                <div class="mb-3">
                                    <strong>Lokasi:</strong><br>
                                    <span>{{ $plantingLocation->location_summary }}</span>
                                </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if($plantingLocation->map_size)
                                <div class="mb-3">
                                    <strong>Luas Lahan:</strong><br>
                                    <span>{{ $plantingLocation->map_size }} Ha</span>
                                </div>
                                @endif
                                @if($plantingLocation->google_maps_link)
                                <div class="mb-3">
                                    <strong>Link Google Maps:</strong><br>
                                    <a href="{{ $plantingLocation->google_maps_link }}" target="_blank" class="text-primary">
                                        {{ Str::limit($plantingLocation->google_maps_link, 50) }}
                                        <i class="fas fa-external-link-alt ms-1"></i>
                                    </a>
                                </div>
                                @endif
                                @if($plantingLocation->administrative_address)
                                <div class="mb-3">
                                    <strong>Alamat Administratif:</strong><br>
                                    <span>{{ $plantingLocation->administrative_address }}</span>
                                </div>
                                @endif
                                @if($plantingLocation->primary_photo_path)
                                <div class="mb-3">
                                    <strong>Foto Lahan:</strong><br>
                                    <img src="{{ Storage::url($plantingLocation->primary_photo_path) }}" 
                                         alt="Foto Lahan" 
                                         class="img-thumbnail mt-2" 
                                         style="max-width: 200px; max-height: 200px; cursor: pointer;"
                                         onclick="window.open('{{ Storage::url($plantingLocation->primary_photo_path) }}', '_blank')">
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status dan Kepemilikan -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Status dan Kepemilikan</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                @if($plantingLocation->land_status)
                                <div class="mb-3">
                                    <strong>Status Lahan:</strong><br>
                                    <span>{{ $plantingLocation->land_status }}</span>
                                </div>
                                @endif
                                @if($plantingLocation->ownership_status)
                                <div class="mb-3">
                                    <strong>Status Kepemilikan:</strong><br>
                                    <span>{{ $plantingLocation->ownership_status }}</span>
                                </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if($plantingLocation->water_source)
                                <div class="mb-3">
                                    <strong>Sumber Air:</strong><br>
                                    <span>{{ $plantingLocation->water_source }}</span>
                                </div>
                                @endif
                                @if($plantingLocation->soil_type)
                                <div class="mb-3">
                                    <strong>Tipe Tanah:</strong><br>
                                    <span>{{ $plantingLocation->soil_type }}</span>
                                </div>
                                @endif
                                @if($plantingLocation->elevation_masl)
                                <div class="mb-3">
                                    <strong>Ketinggian (MDPL):</strong><br>
                                    <span>{{ $plantingLocation->elevation_masl }} mdpl</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Penanggung Jawab dan Pekerja -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-users me-2"></i>Penanggung Jawab dan Pekerja</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Penanggung Jawab Lahan:</strong><br>
                                    @if($plantingLocation->landManagerUsers->count() > 0)
                                        <ul class="list-unstyled mt-2 mb-0">
                                            @foreach($plantingLocation->landManagerUsers as $user)
                                                <li class="mb-2">
                                                    <i class="fas fa-user me-2"></i>
                                                    <strong>{{ $user->name }}</strong>
                                                    @if($user->role)
                                                        <small class="text-muted"> - {{ $user->role_label }}</small>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Pekerja Lahan:</strong><br>
                                    @if($plantingLocation->landWorkerUsers->count() > 0)
                                        <ul class="list-unstyled mt-2 mb-0">
                                            @foreach($plantingLocation->landWorkerUsers as $user)
                                                <li class="mb-2">
                                                    <i class="fas fa-user me-2"></i>
                                                    <strong>{{ $user->name }}</strong>
                                                    @if($user->role)
                                                        <small class="text-muted"> - {{ $user->role_label }}</small>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Format Penanaman -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-seedling me-2"></i>Format Penanaman</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Format Penanaman:</strong><br>
                                    @if($plantingLocation->planting_format === 'lainnya' && $plantingLocation->planting_format_custom)
                                        <span>{{ $plantingLocation->planting_format_custom }}</span>
                                    @else
                                        <span>{{ $plantingLocation->planting_format ? ucfirst(str_replace('_', ' ', $plantingLocation->planting_format)) : '-' }}</span>
                                    @endif
                                </div>
                                @if($plantingLocation->planting_format === 'ditanam_dalam_petak')
                                    <div class="mb-3">
                                        <strong>Jumlah Petak:</strong><br>
                                        <span>{{ $plantingLocation->num_beds ?? '-' }}</span>
                                    </div>
                                    @if($plantingLocation->bed_length_m || $plantingLocation->bed_width_m)
                                        <div class="mb-3">
                                            <strong>Ukuran Petak:</strong><br>
                                            @if($plantingLocation->bed_length_m && $plantingLocation->bed_width_m)
                                                <span>{{ number_format($plantingLocation->bed_length_m, 2) }}m x {{ number_format($plantingLocation->bed_width_m, 2) }}m</span>
                                            @else
                                                <span>{{ $plantingLocation->bed_length_m ? number_format($plantingLocation->bed_length_m, 2) . 'm' : '' }}{{ $plantingLocation->bed_width_m ? number_format($plantingLocation->bed_width_m, 2) . 'm' : '' }}</span>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if($plantingLocation->light_condition)
                                <div class="mb-3">
                                    <strong>Kondisi Cahaya:</strong><br>
                                    <span>{{ ucfirst(str_replace('_', ' ', $plantingLocation->light_condition)) }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deskripsi -->
                @if($plantingLocation->description)
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-align-left me-2"></i>Deskripsi</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $plantingLocation->description }}</p>
                    </div>
                </div>
                @endif
            </div>
                    </div>
                </div>

@push('scripts')
<script>
    function updateFilters() {
        const year = document.getElementById('yearFilter').value;
        const monthSelect = document.getElementById('monthFilter');
        const month = monthSelect ? monthSelect.value : '';
        const params = new URLSearchParams(window.location.search);
        params.set('year', year);
        if (month) {
            params.set('month', month);
        } else {
            params.delete('month');
        }
        window.location.href = window.location.pathname + '?' + params.toString();
    }
</script>
@endpush
@endsection
