@extends('layouts.app')

@section('title', 'Lahan - ' . $plantingLocation->name . ' - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $plantingLocation->name }}</h4>
    </div>
    <a href="{{ route('planting-locations.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<ul class="nav nav-tabs" role="tablist">
    @include('planting.planting-locations._tabs', ['plantingLocation' => $plantingLocation, 'activeTab' => 'fields'])
</ul>

<div class="tab-content p-3 bg-white border border-top-0 rounded-bottom">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">Daftar Lahan - {{ $plantingLocation->name }}</h6>
        @if(auth()->user()->isAdmin() || auth()->user()->canManagePlantingLocation($plantingLocation))
            <a href="{{ route('planting-locations.fields.create', $plantingLocation) }}" class="btn btn-success">
                <i class="fas fa-plus me-2"></i>Tambahkan Lahan
            </a>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Kode Lahan</th>
                    <th>Luas (Ha)</th>
                    <th>Panjang &times; Lebar (m)</th>
                    <th>Koordinat GPS</th>
                    <th>Status Lahan</th>
                    <th width="160">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fields as $field)
                    <tr>
                        <td><strong>{{ $field->kode_lahan }}</strong></td>
                        <td>{{ number_format((float) $field->luas_ha, 2, ',', '.') }}</td>
                        <td>
                            @if($field->panjang_m || $field->lebar_m)
                                {{ $field->panjang_m ? number_format((float) $field->panjang_m, 2, ',', '.') : '-' }}
                                &times;
                                {{ $field->lebar_m ? number_format((float) $field->lebar_m, 2, ',', '.') : '-' }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($field->koordinat_gps)
                                <a href="https://www.google.com/maps?q={{ urlencode($field->koordinat_gps) }}" target="_blank" rel="noopener">
                                    {{ $field->koordinat_gps }}
                                    <i class="fas fa-external-link-alt ms-1 small"></i>
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $badge = match ($field->status_lahan) {
                                    'Digunakan' => 'bg-success',
                                    'Bera' => 'bg-warning text-dark',
                                    'Persiapan' => 'bg-info',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badge }}">{{ $field->statusLabel() }}</span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('planting-locations.fields.show', [$plantingLocation, $field]) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(auth()->user()->isAdmin() || auth()->user()->canManagePlantingLocation($plantingLocation))
                                    <a href="{{ route('planting-locations.fields.edit', [$plantingLocation, $field]) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus"
                                            onclick="confirmDelete('{{ route('planting-locations.fields.destroy', [$plantingLocation, $field]) }}', '{{ addslashes($field->kode_lahan) }}', 'lahan')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            Belum ada data lahan. Tambahkan petak lahan yang akan dipakai saat penanaman.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($fields->hasPages())
        <div class="d-flex justify-content-center mt-3">{{ $fields->links() }}</div>
    @endif
</div>
@endsection
