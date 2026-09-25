@extends('layouts.app')
@section('title', ($content->exists ? 'Edit' : 'Tambah').' Konten - SIBESTI')
@section('content')
<h4 class="mb-4">{{ $content->exists ? 'Edit' : 'Tambah' }} Konten Website</h4>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $content->exists ? route('contents.update', $content) : route('contents.store') }}" enctype="multipart/form-data">
            @csrf
            @if($content->exists) @method('PUT') @endif
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jenis konten</label>
                    <select name="jenis" id="jenis" class="form-select" required>
                        @foreach($jenisList as $key => $label)
                            <option value="{{ $key }}" {{ old('jenis', $content->jenis)===$key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                        <option value="lainnya" {{ old('jenis')==='lainnya' ? 'selected' : '' }}>Lainnya (jenis baru)</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3" id="jenisCustomWrap" style="display:none;">
                    <label class="form-label">Nama jenis baru</label>
                    <input name="jenis_custom" class="form-control" value="{{ old('jenis_custom') }}">
                </div>
                <div class="col-md-4 mb-3" id="kategoriWrap">
                    <label class="form-label">Kategori profil</label>
                    <select name="kategori" id="kategori" class="form-select">
                        <option value="">-</option>
                        @foreach(\App\Models\WebsiteContent::profilKategori() as $key => $label)
                            <option value="{{ $key }}" {{ old('kategori', $content->kategori)===$key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                        <option value="lainnya" {{ old('kategori')==='lainnya' ? 'selected' : '' }}>Lainnya (kategori baru)</option>
                    </select>
                    <input name="kategori_custom" id="kategori_custom" class="form-control mt-2" placeholder="Nama kategori baru" value="{{ old('kategori_custom') }}" style="display:none">
                </div>
            </div>
            <div class="mb-3"><label class="form-label">Judul</label><input name="judul" class="form-control" value="{{ old('judul', $content->judul) }}" required></div>
            <div class="mb-3"><label class="form-label">Cuplikan</label><textarea name="excerpt" class="form-control" rows="2">{{ old('excerpt', $content->excerpt) }}</textarea></div>
            <div class="mb-3"><label class="form-label">Isi konten</label><textarea name="body" class="form-control" rows="8">{{ old('body', $content->body) }}</textarea></div>
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label">Penulis</label><input name="author" class="form-control" value="{{ old('author', $content->author) }}"></div>
                <div class="col-md-4 mb-3"><label class="form-label">Label KIP/PPID</label>
                    <select name="kip_label" class="form-select">
                        <option value="">-</option>
                        @foreach(\App\Models\WebsiteContent::KIP as $key => $label)
                            <option value="{{ $key }}" {{ old('kip_label', $content->kip_label)===$key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3"><label class="form-label">Tanggal publikasi</label><input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', optional($content->published_at)->format('Y-m-d\TH:i')) }}"></div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label">Cover / gambar</label><input type="file" name="cover" class="form-control" accept="image/*"></div>
                <div class="col-md-6 mb-3"><label class="form-label">Berkas (PDF/dokumen)</label><input type="file" name="file" class="form-control"></div>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_published" value="1" id="is_published" {{ old('is_published', $content->is_published) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_published">Publikasikan di website</label>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('contents.index') }}" class="btn btn-secondary">Batal</a>
                <button class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
function syncJenis() {
    const jenis = document.getElementById('jenis').value;
    document.getElementById('jenisCustomWrap').style.display = jenis === 'lainnya' ? '' : 'none';
    document.getElementById('kategoriWrap').style.display = jenis === 'profil' ? '' : 'none';
}
document.getElementById('jenis').addEventListener('change', syncJenis);
syncJenis();
function syncKategori() {
    const el = document.getElementById('kategori_custom');
    if (el) el.style.display = document.getElementById('kategori').value === 'lainnya' ? '' : 'none';
}
document.getElementById('kategori')?.addEventListener('change', syncKategori);
syncKategori();
</script>
@endpush
