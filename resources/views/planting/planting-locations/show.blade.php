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
    @include('planting.planting-locations._tabs', ['plantingLocation' => $plantingLocation, 'activeTab' => 'detail'])
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
                                    <strong>Nama Lokasi Penanaman:</strong><br>
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
                                    <strong>Tipe Lokasi Penanaman:</strong><br>
                                    <span>{{ ucfirst(str_replace('_', ' ', $plantingLocation->location_type ?? '-')) }}</span>
                                </div>
                                <div class="mb-3">
                                    <strong>Alamat:</strong><br>
                                    <span>{{ $plantingLocation->location_summary ?: '-' }}</span>
                                </div>
                                <div class="mb-3">
                                    <strong>Koordinat lokasi lahan:</strong><br>
                                    <span>{{ $plantingLocation->koordinat_gps ?: '-' }}</span>
                                    @if($plantingLocation->koordinat_gps)
                                    <a href="https://www.google.com/maps?q={{ urlencode($plantingLocation->koordinat_gps) }}" target="_blank" class="ms-2 small">
                                        Lihat peta <i class="fas fa-external-link-alt"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Luas Lokasi Penanaman:</strong><br>
                                    <span>{{ $plantingLocation->map_size ? $plantingLocation->map_size.' Ha' : '-' }}</span>
                                </div>
                                <div class="mb-3">
                                    <strong>Link Google Maps:</strong><br>
                                    @if($plantingLocation->google_maps_link)
                                    <a href="{{ $plantingLocation->google_maps_link }}" target="_blank" class="text-primary">
                                        {{ Str::limit($plantingLocation->google_maps_link, 50) }}
                                        <i class="fas fa-external-link-alt ms-1"></i>
                                    </a>
                                    @else
                                    <span>-</span>
                                    @endif
                                </div>
                                <div class="mb-3">
                                    <strong>Alamat Administratif:</strong><br>
                                    <span>{{ $plantingLocation->administrative_address ?: '-' }}</span>
                                </div>
                                <div class="mb-3">
                                    <strong>Foto Lokasi Penanaman:</strong><br>
                                    @if($plantingLocation->primary_photo_path)
                                    <img src="{{ Storage::url($plantingLocation->primary_photo_path) }}" 
                                         alt="Foto Lokasi Penanaman" 
                                         class="img-thumbnail mt-2" 
                                         style="max-width: 200px; max-height: 200px; cursor: pointer;"
                                         onclick="window.open('{{ Storage::url($plantingLocation->primary_photo_path) }}', '_blank')">
                                    @else
                                    <span>-</span>
                                    @endif
                                </div>
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
                                <div class="mb-3">
                                    <strong>Status Lokasi Penanaman:</strong><br>
                                    <span>{{ $plantingLocation->land_status ?: '-' }}</span>
                                </div>
                                <div class="mb-3">
                                    <strong>Status Kepemilikan:</strong><br>
                                    <span>{{ $plantingLocation->ownership_status ?: '-' }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>Sumber Air:</strong><br>
                                    <span>{{ $plantingLocation->water_source ?: '-' }}</span>
                                </div>
                                <div class="mb-3">
                                    <strong>Tipe Tanah:</strong><br>
                                    <span>{{ $plantingLocation->soil_type ?: '-' }}</span>
                                </div>
                                <div class="mb-3">
                                    <strong>Ketinggian (MDPL):</strong><br>
                                    <span>{{ $plantingLocation->elevation_masl !== null ? $plantingLocation->elevation_masl.' mdpl' : '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pekerja Lokasi Penanaman -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-users me-2"></i>Pekerja Lokasi Penanaman</h6>
                    </div>
                    <div class="card-body">
                        @if($plantingLocation->landWorkerUsers->count() > 0)
                            <ul class="list-unstyled mt-2 mb-0">
                                @foreach($plantingLocation->landWorkerUsers as $user)
                                    @php
                                        $jabatanLabel = $user->role_label ?? ($user->role === 'penangkar' ? 'Penangkar' : 'Petugas Lapangan');
                                    @endphp
                                    <li class="mb-2">
                                        <i class="fas fa-user me-2"></i>
                                        <strong>{{ $user->name }}</strong>
                                        <small class="text-muted"> – {{ $jabatanLabel }}</small>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-muted">-</span>
                        @endif
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
                                <div class="mb-3">
                                    <strong>Kondisi Cahaya:</strong><br>
                                    <span>{{ $plantingLocation->light_condition ? ucfirst(str_replace('_', ' ', $plantingLocation->light_condition)) : '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-align-left me-1"></i>Deskripsi</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $plantingLocation->description ?: '-' }}</p>
                    </div>
                </div>
            </div>
    </div>
</div>
@endsection
