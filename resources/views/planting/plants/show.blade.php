@extends('layouts.app')

@section('title', 'Detail Tanaman - SIBESTI')

@section('content')
<!-- Plant Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="{{ route('plants.index') }}" class="btn btn-secondary me-3">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 18px; font-weight: bold;">
            {{ substr($plant->name, 0, 2) }}
        </div>
        <div>
            <h4 class="mb-0">{{ $plant->name }}</h4>
            <small class="text-muted">{{ $plant->type?->name ?: 'Tidak ada tipe' }}</small>
        </div>
    </div>
</div>

<ul class="nav nav-tabs" role="tablist">
    @include('planting.plants._tabs', ['plant' => $plant, 'activeTab' => 'detail'])
</ul>

<div class="tab-content p-3 bg-white border border-top-0 rounded-bottom">
    <div class="tab-pane fade show active">
        @php $unit = $plant->satuanStok?->code ?: ''; @endphp
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card border-success h-100">
                    <div class="card-body">
                        <small class="text-muted">Total stok saat ini</small>
                        <h4 class="mb-0">{{ number_format((float) ($currentStock ?? 0), 2) }} {{ $unit }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-info h-100">
                    <div class="card-body">
                        <small class="text-muted">Total benih terjual</small>
                        <h4 class="mb-0">{{ number_format((float) ($soldQty ?? 0), 2) }} {{ $unit }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-primary h-100">
                    <div class="card-body">
                        <small class="text-muted">Total pendapatan</small>
                        <h4 class="mb-0">Rp {{ number_format((float) ($revenue ?? 0), 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- Plant Details Section -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Data Tanaman</h5>
                <a href="{{ route('plants.edit', $plant) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit me-1"></i>Edit
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Tipe</label>
                            <input type="text" class="form-control" value="{{ $plant->type ? ($plant->type->category ? $plant->type->category.' - ' : '').$plant->type->name : '-' }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Varietas</label>
                            <input type="text" class="form-control" value="{{ $plant->variety ?: '-' }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" rows="2" readonly>{{ $plant->description ?: '-' }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Lokasi Penanaman</label>
                            <div>
                                @if($plant->plantings->count() > 0)
                                    @foreach($plant->plantings->map(fn($p) => $p->location?->name)->filter()->unique() as $locationName)
                                        <span class="badge bg-primary me-1">{{ $locationName }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('planting.plants._agronomy-fields', ['plant' => $plant, 'readonly' => true, 'harvestUnitRequired' => false])

    </div>
</div>
</div>

@push('scripts')
<script>
function deletePlant() {
    if (confirm('Apakah Anda yakin ingin menghapus tanaman ini?')) {
        // Implement delete functionality
        console.log('Delete plant');
    }
}
</script>
@endpush
@endsection



