@php
    $oldFields = old('fields', []);
    $existingFields = isset($plantingLocation) ? $plantingLocation->fields : collect();
@endphp

<div class="card mb-4 border-success">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Tambah Lahan</h5>
        <button type="button" class="btn btn-light btn-sm" id="btnAddLahan">
            <i class="fas fa-plus me-1"></i>Tambahkan Lahan
        </button>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">Satu lokasi penanaman dapat memiliki beberapa lahan. Isi form lahan di bawah, lalu klik <strong>Tambahkan Lahan</strong> untuk menambah petak berikutnya.</p>

        @if(isset($plantingLocation) && $existingFields->count())
            <div class="alert alert-light border mb-3">
                <strong>Lahan yang sudah tersimpan:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($existingFields as $savedField)
                        <li>
                            {{ $savedField->kode_lahan }}
                            ({{ number_format((float) $savedField->luas_ha, 2) }} Ha)
                            — {{ $savedField->statusLabel() }}
                        </li>
                    @endforeach
                </ul>
                <div class="mt-2">
                    <a href="{{ route('planting-locations.fields.index', $plantingLocation) }}" class="small">Kelola lahan yang sudah ada</a>
                </div>
            </div>
        @endif

        <div id="lahanRepeaterList">
            @foreach($oldFields as $index => $row)
                @include('planting.planting-locations._lahan-repeater-card', ['index' => $index, 'row' => $row])
            @endforeach
        </div>
        <div id="lahanRepeaterEmpty" class="text-muted {{ count($oldFields) ? 'd-none' : '' }}">
            Belum ada lahan ditambahkan pada form ini.
        </div>
    </div>
</div>

<template id="lahanRepeaterTemplate">
    @include('planting.planting-locations._lahan-repeater-card', ['index' => '__INDEX__', 'row' => []])
</template>
