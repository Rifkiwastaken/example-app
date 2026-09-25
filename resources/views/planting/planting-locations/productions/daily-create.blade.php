@extends('layouts.app')

@section('title', 'Tambahkan Laporan Harian - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Tambahkan Laporan Harian</h4>
    <a href="{{ route('planting-locations.plantings.reports', [$plantingLocation, $planting, 'tab' => 'daily']) }}" class="btn btn-secondary">Kembali</a>
</div>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('planting-locations.plantings.daily.store', [$plantingLocation, $planting]) }}" enctype="multipart/form-data" id="daily-form">
            @csrf
            <div class="mb-3">
                <label class="form-label">Judul laporan <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Asosiasi produksi</label>
                    <input type="text" class="form-control" value="{{ $planting->planting_batch_number }}" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Lahan</label>
                    <input type="text" class="form-control" value="{{ $planting->field?->kode_lahan }}" readonly>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fase tumbuh <span class="text-danger">*</span></label>
                    <select name="growth_phase" class="form-select" required>
                        <option value="">Pilih fase</option>
                        @foreach(\App\Models\PlantingReport::PHASES as $value => $label)
                            <option value="{{ $value }}" {{ old('growth_phase') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis kegiatan <span class="text-danger">*</span></label>
                    <select name="activity_type" id="activity-type" class="form-select" required>
                        <option value="">Pilih kegiatan</option>
                        @foreach(\App\Models\PlantingReport::ACTIVITIES as $value => $label)
                            <option value="{{ $value }}" {{ old('activity_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3 d-none" data-activity-group="custom">
                <label class="form-label">Sebutkan jenis kegiatan <span class="text-danger">*</span></label>
                <input type="text" name="activity_type_custom" class="form-control" value="{{ old('activity_type_custom') }}">
            </div>

            <div class="row d-none" data-activity-group="product">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Detail produk yang digunakan <span class="text-danger">*</span></label>
                    <input type="text" name="product_detail" class="form-control" value="{{ old('product_detail') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jumlah yang diterapkan <span class="text-danger">*</span></label>
                    <input type="text" name="applied_amount" class="form-control" value="{{ old('applied_amount') }}" placeholder="Misal: 25 kg">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Metode menerapkan produk <span class="text-danger">*</span></label>
                    <input type="text" name="application_method" class="form-control" value="{{ old('application_method') }}" placeholder="Misal: Tabur, semprot">
                </div>
            </div>

            <div class="row d-none" data-activity-group="rouging">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jumlah tanaman yang dicabut <span class="text-danger">*</span></label>
                    <input type="number" step="1" min="0" name="plants_removed" class="form-control" value="{{ old('plants_removed') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Karakteristik <span class="text-danger">*</span></label>
                    <input type="text" name="characteristic" class="form-control" value="{{ old('characteristic') }}" placeholder="Misal: Tipe simpang, tinggi berbeda">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Isi laporan harian <span class="text-danger">*</span></label>
                <textarea name="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Teknisi dari luar</label>
                <input type="text" name="external_technician" class="form-control" value="{{ old('external_technician') }}">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Petugas</label>
                    <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal aktivitas <span class="text-danger">*</span></label>
                    <input type="date" name="activity_date" class="form-control" value="{{ old('activity_date', date('Y-m-d')) }}" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Lampiran</label>
                <input type="file" name="file" class="form-control">
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('planting-locations.plantings.reports', [$plantingLocation, $planting, 'tab' => 'daily']) }}" class="btn btn-secondary">Batal</a>
                <button class="btn btn-success" type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const select = document.getElementById('activity-type');
    if (!select) return;

    const groups = {
        product: document.querySelector('[data-activity-group="product"]'),
        rouging: document.querySelector('[data-activity-group="rouging"]'),
        custom: document.querySelector('[data-activity-group="custom"]'),
    };

    // Jenis kegiatan menentukan blok field tambahan yang wajib diisi.
    const groupByActivity = {
        pemupukan: ['product'],
        opt: ['product'],
        rouging: ['rouging'],
        lainnya: ['custom'],
    };

    function apply(reset) {
        const active = groupByActivity[select.value] || [];
        Object.entries(groups).forEach(([name, el]) => {
            if (!el) return;
            const show = active.includes(name);
            el.classList.toggle('d-none', !show);
            el.querySelectorAll('input, textarea, select').forEach((input) => {
                input.disabled = !show;
                if (!show && reset) input.value = '';
            });
        });
    }

    select.addEventListener('change', () => apply(true));
    apply(false);
})();
</script>
@endpush
