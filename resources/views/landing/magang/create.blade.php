@extends('layouts.public')
@section('title', 'Form Pendaftaran Magang - SIBESTI')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0">Form Pendaftaran Magang / PKL</h3>
        <small class="text-muted">Lengkapi data pemohon dan daftar peserta magang.</small>
    </div>
    <a href="{{ route('public.magang.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('public.magang.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama lengkap <span class="text-danger">*</span></label>
                    <input name="nama_lengkap" class="form-control" value="{{ old('nama_lengkap') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Institusi asal <span class="text-danger">*</span></label>
                    <input name="institusi_asal" class="form-control" value="{{ old('institusi_asal') }}" required placeholder="Nama SMK / Universitas">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                    <input name="jabatan" class="form-control" value="{{ old('jabatan') }}" required placeholder="Contoh: Mahasiswa / Penyuluh">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. WhatsApp <span class="text-danger">*</span></label>
                    <input name="no_whatsapp" class="form-control" value="{{ old('no_whatsapp') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal mulai <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_mulai" class="form-control" value="{{ old('tgl_mulai') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal selesai <span class="text-danger">*</span></label>
                    <input type="date" name="tgl_selesai" class="form-control" value="{{ old('tgl_selesai') }}" required>
                </div>
            </div>
            <h5 class="mt-2">Daftar peserta magang <span class="text-danger">*</span></h5>
            <div id="peserta-wrap">
                @php $oldPeserta = old('peserta', [['nama_lengkap' => '', 'identitas' => '', 'jabatan' => '']]); @endphp
                @foreach($oldPeserta as $i => $row)
                <div class="row peserta-row mb-2">
                    <div class="col-md-5"><input name="peserta[{{ $i }}][nama_lengkap]" class="form-control" placeholder="Nama peserta" value="{{ $row['nama_lengkap'] ?? '' }}" required></div>
                    <div class="col-md-3"><input name="peserta[{{ $i }}][identitas]" class="form-control" placeholder="NIM / NIS" value="{{ $row['identitas'] ?? '' }}"></div>
                    <div class="col-md-3"><input name="peserta[{{ $i }}][jabatan]" class="form-control" placeholder="Jabatan" value="{{ $row['jabatan'] ?? '' }}"></div>
                    <div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100" onclick="this.closest('.peserta-row').remove()">×</button></div>
                </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-outline-success btn-sm mb-3" onclick="addPeserta()">Tambah peserta</button>
            <div class="text-end">
                <button class="btn btn-emerald" type="submit">Kirim Pendaftaran</button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
let pesertaIdx = {{ count(old('peserta', [1])) }};
function addPeserta() {
    const wrap = document.getElementById('peserta-wrap');
    wrap.insertAdjacentHTML('beforeend', `<div class="row peserta-row mb-2">
        <div class="col-md-5"><input name="peserta[${pesertaIdx}][nama_lengkap]" class="form-control" placeholder="Nama peserta" required></div>
        <div class="col-md-3"><input name="peserta[${pesertaIdx}][identitas]" class="form-control" placeholder="NIM / NIS"></div>
        <div class="col-md-3"><input name="peserta[${pesertaIdx}][jabatan]" class="form-control" placeholder="Jabatan"></div>
        <div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100" onclick="this.closest('.peserta-row').remove()">×</button></div>
    </div>`);
    pesertaIdx++;
}
</script>
@endpush
