@extends('layouts.app')

@section('title', 'Catat Hasil Lab Benih - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Catat Hasil Lab Benih</h4>
        <small class="text-muted">{{ $planting->planting_batch_number }} · {{ $planting->field?->kode_lahan }}</small>
    </div>
    <a href="{{ route('planting-locations.planting-history', $plantingLocation) }}" class="btn btn-secondary">Kembali</a>
</div>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
@if($alasanUjiUlang)
    <div class="alert alert-warning">Sertifikasi ulang · alasan uji ulang: {{ $alasanUjiUlang }}</div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('planting-locations.plantings.lab-result.store', [$plantingLocation, $planting]) }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="alasan_uji_ulang" value="{{ $alasanUjiUlang }}">

            <h6 class="mb-3">Identitas Pengujian</h6>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">No sertifikat lab BPSB <span class="text-danger">*</span></label>
                    <input type="text" name="no_sertifikat_lab_bpsb" class="form-control" value="{{ old('no_sertifikat_lab_bpsb') }}" required>
                    @if($alasanUjiUlang && !empty($prefill['previous_bpsb']))
                        <small class="text-muted">Harus berbeda dari nomor sebelumnya: {{ $prefill['previous_bpsb'] }}</small>
                    @endif
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Uji ke <span class="text-danger">*</span></label>
                    <input type="number" min="1" name="uji_ke" class="form-control" value="{{ old('uji_ke', $prefill['uji_ke']) }}" required {{ $alasanUjiUlang || ($prefill['uji_ke'] ?? 1) > 1 ? 'readonly' : '' }}>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Nomor induk <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_induk" class="form-control" value="{{ old('nomor_induk', $alasanUjiUlang ? ($prefill['nomor_induk'] ?? '') : '') }}" placeholder="{{ $prefill['nomor_induk'] ?? 'Nomor induk benih' }}" required {{ $alasanUjiUlang ? 'readonly' : '' }}>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Nomor lot <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_lot" class="form-control" value="{{ old('nomor_lot', $alasanUjiUlang ? ($prefill['nomor_lot'] ?? '') : '') }}" placeholder="{{ $prefill['nomor_lot'] ?? 'Nomor lot uji' }}" required {{ $alasanUjiUlang ? 'readonly' : '' }}>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal panen <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_panen" class="form-control" value="{{ old('tgl_panen', $prefill['tgl_panen']) }}" required {{ $alasanUjiUlang ? 'readonly' : '' }}>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal aju <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_aju" class="form-control" value="{{ old('tgl_aju', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal uji <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_uji" class="form-control" value="{{ old('tgl_uji', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal selesai uji <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_selesai_uji" class="form-control" value="{{ old('tgl_selesai_uji', date('Y-m-d')) }}" required>
                </div>
            </div>

            <h6 class="mt-2 mb-3">Komponen Fisik &amp; Biologis</h6>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Kadar air (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" max="99.99" name="kadar_air_persen" class="form-control" value="{{ old('kadar_air_persen') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Benih murni (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" max="100" name="benih_murni_persen" class="form-control" value="{{ old('benih_murni_persen') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Kotoran benih (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" max="99.99" name="kotoran_benih_persen" class="form-control" value="{{ old('kotoran_benih_persen') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Benih tanaman lain (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" max="99.99" name="benih_tanaman_lain_persen" class="form-control" value="{{ old('benih_tanaman_lain_persen') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Daya berkecambah (%) <span class="text-danger">*</span></label>
                    <input type="number" min="0" max="100" name="daya_berkecambah_persen" class="form-control" value="{{ old('daya_berkecambah_persen') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Realisasi produksi <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="realisasi_produksi" class="form-control" value="{{ old('realisasi_produksi', $prefill['realisasi_produksi']) }}" required {{ $alasanUjiUlang ? 'readonly' : '' }}>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Total hasil uji <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="total_hasil_uji" class="form-control" value="{{ old('total_hasil_uji', $prefill['total_hasil_uji'] ?? '') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal kadaluarsa mutu <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_kadaluarsa_mutu" class="form-control" value="{{ old('tgl_kadaluarsa_mutu') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status kelulusan lab <span class="text-danger">*</span></label>
                    <select name="status_kelulusan_lab" class="form-select" required>
                        <option value="LULUS" {{ old('status_kelulusan_lab', 'LULUS') === 'LULUS' ? 'selected' : '' }}>LULUS</option>
                        <option value="TIDAK LULUS" {{ old('status_kelulusan_lab') === 'TIDAK LULUS' ? 'selected' : '' }}>TIDAK LULUS</option>
                    </select>
                </div>
                <div class="col-md-9 mb-3">
                    <label class="form-label">Lampiran hasil lab</label>
                    <input type="file" name="lampiran_hasil_lab" class="form-control">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Catatan kesehatan / penyakit</label>
                    <textarea name="catatan_kesehatan_penyakit" class="form-control" rows="3">{{ old('catatan_kesehatan_penyakit') }}</textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('planting-locations.planting-history', $plantingLocation) }}" class="btn btn-secondary">Batal</a>
                <button class="btn btn-success" type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
