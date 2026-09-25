@extends('layouts.app')

@section('title', 'Tambahkan Laporan Sertifikasi - SIBESTI')

@php
    $stages = \App\Http\Controllers\ProductionActivityController::STAGES;
    $selected = $stage ?: old('stage');
    $action = route('planting-locations.plantings.certifications.store', [$plantingLocation, $planting]);
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Tambahkan Laporan Sertifikasi</h4>
        <small class="text-muted">{{ $planting->planting_batch_number }} · {{ $planting->field?->kode_lahan }}</small>
    </div>
    <a href="{{ route('planting-locations.plantings.reports', [$plantingLocation, $planting, 'tab' => 'certification']) }}" class="btn btn-secondary">Kembali</a>
</div>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <label class="form-label">Tahapan sertifikasi <span class="text-danger">*</span></label>
        <select id="stage-select" class="form-select">
            <option value="">Pilih tahapan sertifikasi</option>
            @foreach($stages as $key => $meta)
                <option value="{{ $key }}" {{ $selected === $key ? 'selected' : '' }}>{{ $meta['label'] }}</option>
            @endforeach
        </select>
    </div>
</div>

<div id="stage-empty" class="alert alert-info">Pilih tahapan sertifikasi di atas untuk menampilkan formulirnya.</div>

{{-- Pengajuan Permohonan Sertifikasi --}}
<div class="card mb-4 stage-form d-none" data-stage="application">
    <div class="card-header"><strong>{{ $stages['application']['label'] }}</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="stage" value="application">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Komoditas <span class="text-danger">*</span></label>
                    <select name="komoditas" class="form-select" required>
                        @foreach(\App\Models\CertificationApplication::KOMODITAS as $item)
                            <option value="{{ $item }}" {{ old('komoditas', $prefill['komoditas']) === $item ? 'selected' : '' }}>{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama varietas <span class="text-danger">*</span></label>
                    <input type="text" name="nama_varietas" class="form-control" value="{{ old('nama_varietas', $prefill['nama_varietas']) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kelas benih tujuan <span class="text-danger">*</span></label>
                    <input type="text" name="kelas_benih_tujuan" class="form-control" value="{{ old('kelas_benih_tujuan', $prefill['kelas_benih_tujuan']) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Luas lahan (ha) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="luas_lahan_ha" class="form-control" value="{{ old('luas_lahan_ha', $prefill['luas_lahan_ha']) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Koordinat GPS lahan</label>
                    <input type="text" name="koordinat_gps_lahan" class="form-control" value="{{ old('koordinat_gps_lahan', $prefill['koordinat_gps_lahan']) }}" placeholder="Lat, Long">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">File peta / sketsa</label>
                    <input type="file" name="file_peta_sketsa" class="form-control">
                </div>
            </div>

            <h6 class="mt-2 mb-3">Data Asal-Usul Benih Sumber</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">No label benih sumber <span class="text-danger">*</span></label>
                    <input type="text" name="no_label_benih_sumber" class="form-control" value="{{ old('no_label_benih_sumber', $prefill['no_label_benih_sumber']) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kelas benih sumber <span class="text-danger">*</span></label>
                    <input type="text" name="kelas_benih_sumber" class="form-control" value="{{ old('kelas_benih_sumber', $prefill['kelas_benih_sumber']) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Produsen asal benih sumber <span class="text-danger">*</span></label>
                    <input type="text" name="produsen_asal_benih_sumber" class="form-control" value="{{ old('produsen_asal_benih_sumber', $prefill['produsen_asal_benih_sumber']) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jumlah benih sumber (kg) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="jumlah_benih_sumber_kg" class="form-control" value="{{ old('jumlah_benih_sumber_kg', $prefill['jumlah_benih_sumber_kg']) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Rencana tanggal sebar <span class="text-danger">*</span></label>
                    <input type="date" name="rencana_tgl_sebar" class="form-control" value="{{ old('rencana_tgl_sebar', $prefill['rencana_tgl_sebar']) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Rencana tanggal tanam <span class="text-danger">*</span></label>
                    <input type="date" name="rencana_tgl_tanam" class="form-control" value="{{ old('rencana_tgl_tanam', $prefill['rencana_tgl_tanam']) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status pengajuan <span class="text-danger">*</span></label>
                    <select name="status_pengajuan" class="form-select" required>
                        @foreach(\App\Models\CertificationApplication::STATUS_PENGAJUAN as $item)
                            <option value="{{ $item }}" {{ old('status_pengajuan', 'Ditinjau') === $item ? 'selected' : '' }}>{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Sejarah lahan sebelumnya</label>
                    <textarea name="sejarah_lahan_sebelumnya" class="form-control" rows="3">{{ old('sejarah_lahan_sebelumnya') }}</textarea>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Lampiran formulir / hasil sertifikasi</label>
                    <input type="file" name="lampiran_formulir" class="form-control">
                </div>
            </div>
            <div class="d-flex justify-content-end"><button class="btn btn-success" type="submit">Simpan laporan</button></div>
        </form>
    </div>
</div>

{{-- Pemeriksaan Lapangan --}}
<div class="card mb-4 stage-form d-none" data-stage="inspection">
    <div class="card-header"><strong>{{ $stages['inspection']['label'] }}</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="stage" value="inspection">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Fase inspeksi <span class="text-danger">*</span></label>
                    <select name="fase_inspeksi" class="form-select" id="fase-inspeksi" required>
                        @foreach(\App\Models\CertificationInspectionField::FASE as $item)
                            <option value="{{ $item }}" {{ old('fase_inspeksi') === $item ? 'selected' : '' }}>{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal inspeksi <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_inspeksi" class="form-control" value="{{ old('tgl_inspeksi', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Umur tanaman (HST) <span class="text-danger">*</span></label>
                    <input type="number" min="0" name="umur_tanaman_hst" class="form-control" value="{{ old('umur_tanaman_hst') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Nama PBT pemeriksa <span class="text-danger">*</span></label>
                    <input type="text" name="nama_pbt_pemeriksa" class="form-control" value="{{ old('nama_pbt_pemeriksa') }}" required>
                </div>
            </div>

            <div class="row d-none" id="fase-pendahuluan">
                <div class="col-12"><h6 class="mb-3">Khusus Fase Pendahuluan</h6></div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jarak isolasi (meter)</label>
                    <input type="number" min="0" name="jarak_isolasi_meter" class="form-control" value="{{ old('jarak_isolasi_meter') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status isolasi waktu</label>
                    <select name="status_isolasi_waktu" class="form-select">
                        <option value="">-</option>
                        <option value="1" {{ old('status_isolasi_waktu') === '1' ? 'selected' : '' }}>Memenuhi</option>
                        <option value="0" {{ old('status_isolasi_waktu') === '0' ? 'selected' : '' }}>Tidak memenuhi</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kesesuaian dokumen sumber</label>
                    <select name="kesesuaian_dokumen_sumber" class="form-select">
                        <option value="">-</option>
                        <option value="1" {{ old('kesesuaian_dokumen_sumber') === '1' ? 'selected' : '' }}>Sesuai</option>
                        <option value="0" {{ old('kesesuaian_dokumen_sumber') === '0' ? 'selected' : '' }}>Tidak sesuai</option>
                    </select>
                </div>
            </div>

            <div class="alert alert-secondary py-2 small">
                Total tanaman sampel, total CVL, dan persentase CVL akhir dihitung otomatis dari Detail Titik Sampel Inspeksi Lapangan.
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Jenis OPT dominan</label>
                    <input type="text" name="jenis_opt_dominan" class="form-control" value="{{ old('jenis_opt_dominan') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tingkat serangan OPT <span class="text-danger">*</span></label>
                    <select name="tingkat_serangan_opt" class="form-select" required>
                        @foreach(\App\Models\CertificationInspectionField::TINGKAT_OPT as $item)
                            <option value="{{ $item }}" {{ old('tingkat_serangan_opt', 'Aman') === $item ? 'selected' : '' }}>{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status roguing <span class="text-danger">*</span></label>
                    <select name="status_roguing" class="form-select" required>
                        @foreach(\App\Models\CertificationInspectionField::STATUS_ROUGING as $item)
                            <option value="{{ $item }}" {{ old('status_roguing', 'Belum') === $item ? 'selected' : '' }}>{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status kelulusan fase <span class="text-danger">*</span></label>
                    <select name="status_kelulusan_fase" class="form-select" required>
                        @foreach(\App\Models\CertificationInspectionField::STATUS_KELULUSAN as $item)
                            <option value="{{ $item }}" {{ old('status_kelulusan_fase', 'REINSPEKSI') === $item ? 'selected' : '' }}>{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Lampiran formulir / hasil sertifikasi</label>
                    <input type="file" name="lampiran_formulir" class="form-control">
                </div>
            </div>
            <div class="d-flex justify-content-end"><button class="btn btn-success" type="submit">Simpan laporan</button></div>
        </form>
    </div>
</div>

{{-- Detail Titik Sampel --}}
<div class="card mb-4 stage-form d-none" data-stage="field_sample">
    <div class="card-header"><strong>{{ $stages['field_sample']['label'] }}</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="stage" value="field_sample">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nomor titik sampel <span class="text-danger">*</span></label>
                    <input type="number" min="1" name="nomor_titik_sampel" class="form-control" value="{{ old('nomor_titik_sampel', $nextSampleNumber) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jumlah tanaman diperiksa <span class="text-danger">*</span></label>
                    <input type="number" min="1" name="jumlah_tanaman_diperiksa" class="form-control" value="{{ old('jumlah_tanaman_diperiksa', 100) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jumlah CVL ditemukan <span class="text-danger">*</span></label>
                    <input type="number" min="0" name="jumlah_cvl_ditemukan" class="form-control" value="{{ old('jumlah_cvl_ditemukan', 0) }}" required>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Lampiran formulir / hasil sertifikasi</label>
                    <input type="file" name="lampiran_formulir" class="form-control">
                </div>
            </div>
            <div class="d-flex justify-content-end"><button class="btn btn-success" type="submit">Simpan laporan</button></div>
        </form>
    </div>
</div>

{{-- Pengawasan Panen --}}
<div class="card mb-4 stage-form d-none" data-stage="harvest">
    <div class="card-header"><strong>{{ $stages['harvest']['label'] }}</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="stage" value="harvest">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal panen <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_panen" class="form-control" value="{{ old('tgl_panen', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Volume kotor panen (kg) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="volume_kotor_panen_kg" class="form-control" value="{{ old('volume_kotor_panen_kg') }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">No segel sementara</label>
                    <input type="text" name="no_segel_sementara" class="form-control" value="{{ old('no_segel_sementara') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status kebersihan alat panen <span class="text-danger">*</span></label>
                    <select name="status_kebersihan_alat_panen" class="form-select" required>
                        <option value="1" {{ old('status_kebersihan_alat_panen', '1') === '1' ? 'selected' : '' }}>Bersih</option>
                        <option value="0" {{ old('status_kebersihan_alat_panen') === '0' ? 'selected' : '' }}>Tidak bersih</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status kebersihan wadah <span class="text-danger">*</span></label>
                    <select name="status_kebersihan_wadah" class="form-select" required>
                        <option value="1" {{ old('status_kebersihan_wadah', '1') === '1' ? 'selected' : '' }}>Bersih</option>
                        <option value="0" {{ old('status_kebersihan_wadah') === '0' ? 'selected' : '' }}>Tidak bersih</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nama pengawas PBT</label>
                    <input type="text" name="nama_pengawas_pbt" class="form-control" value="{{ old('nama_pengawas_pbt') }}">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Lampiran formulir / hasil sertifikasi</label>
                    <input type="file" name="lampiran_formulir" class="form-control">
                </div>
            </div>
            <div class="d-flex justify-content-end"><button class="btn btn-success" type="submit">Simpan laporan</button></div>
        </form>
    </div>
</div>

{{-- PCB --}}
<div class="card mb-4 stage-form d-none" data-stage="pcb">
    <div class="card-header"><strong>{{ $stages['pcb']['label'] }}</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="stage" value="pcb">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">No berita acara PCB</label>
                    <input type="text" name="no_berita_acara_pcb" class="form-control" value="{{ old('no_berita_acara_pcb') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">No segel sampel lab</label>
                    <input type="text" name="no_segel_sampel_lab" class="form-control" value="{{ old('no_segel_sampel_lab') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Berat sampel kirim (gram)</label>
                    <input type="number" min="0" name="berat_sampel_kirim_gram" class="form-control" value="{{ old('berat_sampel_kirim_gram') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status posisi lot <span class="text-danger">*</span></label>
                    <select name="status_posisi_lot" class="form-select" required>
                        @foreach(\App\Models\CertificationPcb::STATUS_POSISI_LOT as $item)
                            <option value="{{ $item }}" {{ old('status_posisi_lot', 'Diolah') === $item ? 'selected' : '' }}>{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Lampiran formulir / hasil sertifikasi</label>
                    <input type="file" name="lampiran_formulir" class="form-control">
                </div>
            </div>
            <div class="d-flex justify-content-end"><button class="btn btn-success" type="submit">Simpan laporan</button></div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const select = document.getElementById('stage-select');
    const empty = document.getElementById('stage-empty');
    const forms = document.querySelectorAll('.stage-form');

    function applyStage() {
        let found = false;
        forms.forEach((form) => {
            const show = form.dataset.stage === select.value;
            form.classList.toggle('d-none', !show);
            found = found || show;
        });
        empty.classList.toggle('d-none', found);
    }

    const fase = document.getElementById('fase-inspeksi');
    const pendahuluan = document.getElementById('fase-pendahuluan');
    function applyFase() {
        pendahuluan.classList.toggle('d-none', fase.value !== 'Pendahuluan');
    }

    select.addEventListener('change', applyStage);
    fase.addEventListener('change', applyFase);
    applyStage();
    applyFase();
})();
</script>
@endpush
