@extends('layouts.app')

@section('title', 'Produksi - ' . $plantingLocation->name . ' - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">{{ $plantingLocation->name }}</h4>
    <a href="{{ route('planting-locations.show', $plantingLocation) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Kembali
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<ul class="nav nav-tabs" role="tablist">
    @include('planting.planting-locations._tabs', ['plantingLocation' => $plantingLocation, 'activeTab' => 'plantings'])
</ul>

<div class="tab-content p-3 bg-white border border-top-0 rounded-bottom">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">Produksi - {{ $plantingLocation->name }}</h6>
        @if(auth()->user()->isAdmin() || auth()->user()->canManagePlantingLocation($plantingLocation))
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTanamBaru">
                <i class="fas fa-plus me-2"></i>Tambah produksi benih
            </button>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Lahan</th>
                    <th>Nomor Batch Tanam</th>
                    <th>Benih Sumber</th>
                    <th>Jumlah Tanam</th>
                    <th>Tanggal Tanam</th>
                    <th>Estimasi Panen</th>
                    <th>Progres</th>
                    <th>Tahapan</th>
                    <th width="260">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activePlantings as $planting)
                    @php
                        $daysSince = $planting->planted_at ? $planting->planted_at->diffInDays(now()) : 0;
                        $tanamUnit = $planting->plant?->satuanTanam?->code ?: '';
                    @endphp
                    <tr style="cursor:pointer" onclick="window.location='{{ route('planting-locations.plantings.reports', [$plantingLocation, $planting]) }}'">
                        <td>{{ $planting->field?->kode_lahan ?: '-' }}</td>
                        <td>{{ $planting->planting_batch_number ?: '-' }}</td>
                        <td>{{ $planting->seedSource?->plant?->variety ?: $planting->seedSource?->plant?->name ?: '-' }}</td>
                        <td>{{ $planting->planting_amount !== null ? number_format((float) $planting->planting_amount, 2).' '.$tanamUnit : '-' }}</td>
                        <td>{{ $planting->planted_at?->format('d M Y') ?: '-' }}</td>
                        <td>{{ $planting->estimated_harvest_date?->format('d M Y') ?: '-' }}</td>
                        <td><small class="text-muted">{{ $daysSince }} hari sejak tanam</small></td>
                        <td><span class="badge bg-{{ $planting->statusBadge() }}">{{ $planting->statusLabel() }}</span></td>
                        <td onclick="event.stopPropagation()">
                            @php
                                $lab = $planting->latestPostHarvest();
                                $labStock = $lab?->stock;
                            @endphp
                            <a href="{{ route('planting-locations.plantings.reports', [$plantingLocation, $planting]) }}" class="btn btn-sm btn-outline-info">Detail</a>
                            @if($lab && $lab->isLulus() && $labStock && ! $lab->isCertified())
                                <a href="{{ route('seed-stock.label.create', [$labStock->seed_varieties_id, $labStock]) }}" class="btn btn-sm btn-success">Tambahkan Label Benih</a>
                            @elseif(! $lab || ! $lab->isLulus())
                                <a href="{{ route('planting-locations.plantings.lab-result.create', [$plantingLocation, $planting]) }}" class="btn btn-sm btn-outline-success">Catat Hasil Lab Benih</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted">Belum ada produksi aktif.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalTanamBaru" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('planting-locations.plantings.store', $plantingLocation) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah produksi benih</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Lahan <span class="text-danger">*</span></label>
                        <select name="planting_field_id" class="form-select" required>
                            <option value="">-- Pilih lahan --</option>
                            @foreach($plantingLocation->fields as $field)
                                <option value="{{ $field->id }}">{{ $field->kode_lahan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanaman <span class="text-danger">*</span></label>
                        <select name="plant_id" id="plant_id" class="form-select" required>
                            <option value="">-- Pilih tanaman --</option>
                            @foreach($allPlants as $plant)
                                <option value="{{ $plant->getKey() }}"
                                    data-satuan-tanam="{{ $plant->satuanTanam?->code ?: $plant->satuanTanam?->label() ?: '' }}"
                                    data-satuan-panen="{{ $plant->satuanPanen?->label() ?: $plant->satuanPanen?->code ?: '' }}"
                                    data-satuan-panen-id="{{ $plant->satuan_panen_id }}">
                                    {{ $plant->displayName() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Benih Sumber <span class="text-danger">*</span></label>
                        <select name="seed_source_id" id="seed_source_id" class="form-select" required>
                            <option value="">-- Pilih tanaman terlebih dahulu --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor Batch Tanam <span class="text-danger">*</span></label>
                        <input type="text" name="planting_batch_number" class="form-control" value="{{ old('planting_batch_number') }}" placeholder="Contoh: TANAM-2026-014" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Tanam <span class="text-danger">*</span></label>
                            <input type="date" name="planted_at" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estimasi Tanggal Panen <span class="text-danger">*</span></label>
                            <input type="date" name="estimated_harvest_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah benih yang ditanam <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="planting_amount" class="form-control" step="0.01" min="0.01" required>
                            <span class="input-group-text" id="satuanTanamLabel">satuan</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Satuan panen</label>
                        <input type="text" id="satuanPanenLabel" class="form-control bg-light" value="" readonly tabindex="-1" placeholder="Mengikuti satuan panen tanaman">
                        <input type="hidden" name="satuan_panen_id" id="satuan_panen_id">
                        <div class="form-text">Tidak dapat diubah. Nilai ini mengikuti satuan panen pada data varietas tanaman.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Target kelas benih</label>
                        <select name="target_kelas" class="form-select">
                            <option value="">Pilih kelas</option>
                            @foreach(\App\Models\SeedSource::KELAS_BENIH as $code => $label)
                                <option value="{{ $code }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lampiran</label>
                        <input type="file" name="file" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Produksi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSelesai" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="formSelesai">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Selesaikan Produksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Tanggal selesai produksi <span class="text-danger">*</span></label>
                    <input type="date" name="completed_at" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('plant_id').addEventListener('change', async function () {
    const selected = this.options[this.selectedIndex];
    document.getElementById('satuanTanamLabel').textContent = selected.getAttribute('data-satuan-tanam') || 'satuan';
    document.getElementById('satuanPanenLabel').value = selected.getAttribute('data-satuan-panen') || '-';
    document.getElementById('satuan_panen_id').value = selected.getAttribute('data-satuan-panen-id') || '';

    const select = document.getElementById('seed_source_id');
    select.innerHTML = '<option value="">Memuat...</option>';
    if (!this.value) {
        select.innerHTML = '<option value="">-- Pilih tanaman terlebih dahulu --</option>';
        return;
    }
    const response = await fetch('{{ url('api/plants') }}/' + this.value + '/seed-sources');
    const items = await response.json();
    select.innerHTML = '<option value="">-- Pilih benih sumber --</option>';
    items.forEach(function (item) {
        const option = document.createElement('option');
        option.value = item.seed_source_id;
        option.textContent = item.label;
        select.appendChild(option);
    });
    if (!items.length) {
        select.innerHTML = '<option value="">Tidak ada benih sumber yang tersedia untuk tanaman ini</option>';
    }
});

document.getElementById('modalSelesai').addEventListener('show.bs.modal', function (event) {
    const id = event.relatedTarget.getAttribute('data-id');
    document.getElementById('formSelesai').action = '{{ url('planting-locations/'.$plantingLocation->planting_location_id.'/plantings') }}/' + id + '/complete';
});
</script>
@endpush
