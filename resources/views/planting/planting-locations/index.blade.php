@extends('layouts.app')

@section('title', 'Lokasi Penanaman - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Lokasi Penanaman</h4>
    <div class="btn-group">
        <a href="{{ route('planting-locations.create') }}" class="btn btn-success">Lokasi Tanam</a>
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">Ekspor Data</a></li>
                <li><a class="dropdown-item" href="#">Cetak Laporan</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label class="form-label">Lokasi</label>
                <select class="form-select" id="locationFilter">
                    <option value="">Semua Lokasi</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tipe Lokasi</label>
                <select class="form-select" id="typeFilter">
                    <option value="">Semua Tipe</option>
                    <option value="lapangan">Lapangan</option>
                    <option value="greenhouse">Greenhouse</option>
                    <option value="grow_room">Grow Room</option>
                    <option value="padang_rumput">Padang Rumput</option>
                    <option value="petak_ternak">Petak Ternak</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Format Penanaman</label>
                <select class="form-select" id="formatFilter">
                    <option value="">Semua Format</option>
                    <option value="ditanam_dalam_petak">Ditanam dalam Petak</option>
                    <option value="cover_crop">Tanaman Penutup</option>
                    <option value="row_crop">Tanaman Baris</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Planting Locations List -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Lokasi Penanaman</h5>
            <div class="d-flex align-items-center gap-3">
                <div class="input-group" style="width: 300px;">
                    <input type="text" class="form-control" placeholder="Cari" id="searchInput">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if($plantingLocations->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Tipe</th>
                            <th>Format Penanaman</th>
                            <th>Lokasi</th>
                            <th>Petak</th>
                            <th>Luas</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plantingLocations as $location)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 12px; font-weight: bold;">
                                            {{ substr($location->name, 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $location->name }}</div>
                                            <small class="text-muted">{{ $location->id }}-{{ date('Y') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $location->location_type)) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $location->planting_format)) }}</span>
                                </td>
                                <td>{{ $location->baseLocation?->name ?: 'Tidak ada lokasi' }}</td>
                                <td>
                                    @if($location->planting_format === 'ditanam_dalam_petak')
                                        {{ $location->num_beds ?: 0 }} petak
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($location->map_size)
                                        {{ number_format($location->map_size, 2) }} Ha
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="{{ route('planting-locations.show', $location) }}">
                                                <i class="fas fa-eye me-2"></i>Lihat Detail
                                            </a></li>
                                            <li><a class="dropdown-item" href="{{ route('planting-locations.edit', $location) }}">
                                                <i class="fas fa-edit me-2"></i>Edit
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('planting-locations.destroy', $location) }}" method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi penanaman ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger border-0 bg-transparent w-100 text-start" style="cursor: pointer;">
                                                        <i class="fas fa-trash me-2"></i>Hapus
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($plantingLocations->hasPages())
                <div class="d-flex justify-content-center">{{ $plantingLocations->links() }}</div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Belum ada lokasi penanaman</h5>
                <p class="text-muted">Mulai dengan menambahkan lokasi penanaman baru.</p>
                <a href="{{ route('planting-locations.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Tambah Lokasi Penanaman
                </a>
            </div>
        @endif
    </div>
    <div class="card-footer">
        <small class="text-muted">Menampilkan semua {{ $plantingLocations->count() }} record</small>
    </div>
</div>

@push('scripts')
<script>
// Filter functionality
document.getElementById('locationFilter').addEventListener('change', function() {
    // Implement location filter
    console.log('Filter by location:', this.value);
});

document.getElementById('typeFilter').addEventListener('change', function() {
    // Implement type filter
    console.log('Filter by type:', this.value);
});

document.getElementById('formatFilter').addEventListener('change', function() {
    // Implement format filter
    console.log('Filter by format:', this.value);
});

document.getElementById('searchInput').addEventListener('input', function() {
    // Implement search functionality
    console.log('Search:', this.value);
});
</script>
@endpush
@endsection












