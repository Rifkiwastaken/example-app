@extends('layouts.app')
@section('title', 'Informasi Umum Situs - SIBESTI')
@section('content')
<h4 class="mb-4">Informasi Umum Website</h4>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('contents.settings.update') }}">
            @csrf
            <div class="mb-3"><label class="form-label">Nama instansi</label><input name="office_name" class="form-control" value="{{ old('office_name', $situs->office_name) }}" required></div>
            <div class="mb-3"><label class="form-label">Tagline</label><input name="tagline" class="form-control" value="{{ old('tagline', $situs->tagline) }}"></div>
            <div class="mb-3"><label class="form-label">Alamat</label><textarea name="address" class="form-control" rows="2" required>{{ old('address', $situs->address) }}</textarea></div>
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label">Telepon</label><input name="phone" class="form-control" value="{{ old('phone', $situs->phone) }}"></div>
                <div class="col-md-4 mb-3"><label class="form-label">WhatsApp</label><input name="whatsapp" class="form-control" value="{{ old('whatsapp', $situs->whatsapp) }}"></div>
                <div class="col-md-4 mb-3"><label class="form-label">Email</label><input name="email" class="form-control" value="{{ old('email', $situs->email) }}"></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label">Facebook</label><input name="facebook_url" class="form-control" value="{{ old('facebook_url', $situs->facebook_url) }}"></div>
                <div class="col-md-4 mb-3"><label class="form-label">Instagram</label><input name="instagram_url" class="form-control" value="{{ old('instagram_url', $situs->instagram_url) }}"></div>
                <div class="col-md-4 mb-3"><label class="form-label">YouTube</label><input name="youtube_url" class="form-control" value="{{ old('youtube_url', $situs->youtube_url) }}"></div>
            </div>
            <hr>
            <div class="mb-3"><label class="form-label">Hero title beranda</label><input name="hero_title" class="form-control" value="{{ old('hero_title', $situs->hero_title) }}" required></div>
            <div class="mb-3"><label class="form-label">Hero subtitle</label><textarea name="hero_subtitle" class="form-control" rows="2">{{ old('hero_subtitle', $situs->hero_subtitle) }}</textarea></div>
            <div class="mb-3"><label class="form-label">URL gambar hero</label><input name="hero_image" class="form-control" value="{{ old('hero_image', $situs->hero_image) }}"></div>
            <div class="mb-3"><label class="form-label">Catatan retribusi (informasi publik)</label><textarea name="retribusi_note" class="form-control" rows="3">{{ old('retribusi_note', $situs->retribusi_note) }}</textarea></div>
            <button class="btn btn-success">Simpan</button>
        </form>
    </div>
</div>
@endsection
