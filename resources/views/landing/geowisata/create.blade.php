@extends('layouts.public')
@section('title', 'Form Booking Geowisata - SIBESTI')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0">Form Booking Kunjungan Geowisata</h3>
        <small class="text-muted">Pilih jadwal pada kalender pintar, lalu lengkapi data rombongan.</small>
    </div>
    <a href="{{ route('public.geowisata.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header bg-white"><strong>Smart Calendar</strong></div>
            <div class="card-body">
                @include('landing.geowisata._calendar', ['calendar' => $calendar, 'selectable' => true, 'inputId' => 'tgl_kunjungan'])
                <p class="small text-muted mt-2 mb-0">Hari merah tidak dapat dipilih: sudah dibooking, weekend, atau libur nasional.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('public.geowisata.store') }}" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama lembaga <span class="text-danger">*</span></label>
                        <input name="nama_lembaga" class="form-control" value="{{ old('nama_lembaga') }}" required placeholder="Nama sekolah / instansi rombongan">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah peserta <span class="text-danger">*</span></label>
                        <input type="number" min="1" name="jumlah_peserta" class="form-control" value="{{ old('jumlah_peserta') }}" required>
                        <small class="text-muted">Daya tampung kebun induk maksimal {{ \App\Models\BookingGeowisata::MAX_PESERTA_PER_HARI }} orang per hari.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal kunjungan <span class="text-danger">*</span></label>
                        <input type="date" name="tgl_kunjungan" id="tgl_kunjungan" class="form-control" value="{{ old('tgl_kunjungan') }}" required readonly>
                        <small class="text-muted">Pilih dari kalender di kiri.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama penanggung jawab <span class="text-danger">*</span></label>
                        <input name="nama_pj" class="form-control" value="{{ old('nama_pj') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. WhatsApp PJ <span class="text-danger">*</span></label>
                        <input name="no_whatsapp_pj" class="form-control" value="{{ old('no_whatsapp_pj') }}" required placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="text-end">
                        <button class="btn btn-emerald" type="submit">Kirim Pengajuan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
