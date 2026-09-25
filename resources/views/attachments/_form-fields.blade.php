<div class="mb-3">
    <label class="form-label">Judul <span class="text-danger">*</span></label>
    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
           value="{{ old('title', $attachment->title ?? '') }}" required>
    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
    <input type="date" name="attachment_date" class="form-control @error('attachment_date') is-invalid @enderror"
           value="{{ old('attachment_date', optional($attachment->attachment_date ?? null)->format('Y-m-d') ?: date('Y-m-d')) }}" required>
    @error('attachment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label">Pembuat lampiran</label>
    <input type="text" class="form-control" value="{{ $attachment->creator->name ?? auth()->user()->name }}" readonly>
</div>
<div class="mb-3">
    <label class="form-label">Deskripsi lampiran</label>
    <textarea name="description" class="form-control" rows="4">{{ old('description', $attachment->description ?? '') }}</textarea>
</div>
<div class="mb-0">
    <label class="form-label">File lampiran @if(empty($attachment))<span class="text-danger">*</span>@endif</label>
    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" @if(empty($attachment)) required @endif>
    @if(!empty($attachment?->file_path))
        <small class="text-muted d-block mt-1">File saat ini: <a href="{{ asset('storage/'.$attachment->file_path) }}" target="_blank">{{ $attachment->file_name ?: 'Unduh' }}</a></small>
    @endif
    @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
