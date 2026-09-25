@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Lampiran Lokasi Penanaman - ' . $plantingLocation->name . ' - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $plantingLocation->name }}</h4>
    </div>
    <a href="{{ route('planting-locations.index') }}" class="btn btn-secondary">
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
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Data tidak dapat disimpan:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<ul class="nav nav-tabs" role="tablist">
    @include('planting.planting-locations._tabs', ['plantingLocation' => $plantingLocation, 'activeTab' => 'attachments'])
</ul>

<div class="tab-content p-3 bg-white border border-top-0 rounded-bottom">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">Lampiran untuk Lahan: {{ $plantingLocation->name }}</h6>
        @if(auth()->user()->isAdmin() || auth()->user()->canAddDataInPelaporan($plantingLocation))
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahLampiran">
                <i class="fas fa-plus me-1"></i>Tambah Lampiran
            </button>
        @endif
    </div>

    <div class="row">
        @forelse($attachments as $attachment)
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title mb-1">{{ $attachment->title }}</h6>
                        <p class="card-text mb-1">
                            <small class="text-muted">{{ $attachment->attachment_date ? $attachment->attachment_date->format('d M Y') : '-' }}</small>
                        </p>
                        <p class="card-text mb-1">
                            <small class="text-muted">Oleh: {{ $attachment->creator->name ?? '-' }}</small>
                        </p>
                        <p class="card-text mb-3">
                            <small class="text-muted">{{ $attachment->description ?: 'Tanpa deskripsi' }}</small>
                        </p>
                        <div class="mt-auto btn-group btn-group-sm w-100">
                            <button
                                type="button"
                                class="btn btn-info"
                                onclick="loadAttachmentDetail('{{ $attachment->getKey() }}')"
                                data-bs-toggle="modal"
                                data-bs-target="#modalDetailLampiran"
                                title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="btn btn-primary" title="Download">
                                <i class="fas fa-file-download"></i>
                            </a>
                            @if(auth()->user()->isAdmin() || auth()->user()->canAddDataInPelaporan($plantingLocation))
                                <button
                                    type="button"
                                    class="btn btn-warning"
                                    onclick="loadAttachmentEdit('{{ $attachment->getKey() }}')"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditLampiran"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('planting-locations.attachments.destroy', [$plantingLocation, $attachment]) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lampiran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-center text-muted mb-0">Belum ada lampiran.</p>
            </div>
        @endforelse
    </div>
</div>

@if(auth()->user()->isAdmin() || auth()->user()->canAddDataInPelaporan($plantingLocation))
<div class="modal fade" id="modalTambahLampiran" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('planting-locations.attachments.store', $plantingLocation) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Lampiran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('attachments._form-fields')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<div class="modal fade" id="modalDetailLampiran" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Lampiran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailLampiranContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->isAdmin() || auth()->user()->canAddDataInPelaporan($plantingLocation))
<div class="modal fade" id="modalEditLampiran" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEditLampiran" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Lampiran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul Lampiran <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Lampiran</label>
                        <input type="text" name="attachment_type" id="edit_attachment_type" class="form-control" placeholder="pengendalian hama">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Tanaman</label>
                        <input type="text" name="plant_type" id="edit_plant_type" class="form-control" placeholder="Contoh: padi">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi Lampiran</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Lampiran Dibuat <span class="text-danger">*</span></label>
                        <input type="date" name="attachment_date" id="edit_attachment_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih File/Foto (Kosongkan jika tidak ingin mengubah)</label>
                        <input type="file" name="file" class="form-control" accept="image/*,.pdf,.doc,.docx,.txt">
                        <small class="text-muted">Maksimal 10MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
function escapeHtml(value) {
    if (value === null || value === undefined) {
        return '-';
    }

    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function loadAttachmentDetail(attachmentId) {
    const content = document.getElementById('detailLampiranContent');
    content.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    fetch(`{{ route('planting-locations.attachments.show', [$plantingLocation, ':id']) }}`.replace(':id', attachmentId))
        .then(response => response.json())
        .then(data => {
            let html = '';

            if (data.edited_at) {
                html += `<div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Lampiran terakhir di edit pada ${new Date(data.edited_at).toLocaleDateString('id-ID', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    })} oleh ${escapeHtml(data.editor ? data.editor.name : '-')}
                </div>`;
            }

            html += `
                <div class="mb-3">
                    <strong>Judul Lampiran:</strong><br>
                    ${escapeHtml(data.title)}
                </div>
                <div class="mb-3">
                    <strong>Jenis Lampiran:</strong><br>
                    ${escapeHtml(data.attachment_type)}
                </div>
                <div class="mb-3">
                    <strong>Jenis Tanaman:</strong><br>
                    ${escapeHtml(data.plant_type)}
                </div>
                <div class="mb-3">
                    <strong>Deskripsi Lampiran:</strong><br>
                    ${escapeHtml(data.description)}
                </div>
                <div class="mb-3">
                    <strong>Tanggal Lampiran Dibuat:</strong><br>
                    ${new Date(data.attachment_date + 'T00:00:00').toLocaleDateString('id-ID', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    })}
                </div>
                <div class="mb-3">
                    <strong>Pembuat:</strong><br>
                    ${escapeHtml(data.creator ? data.creator.name : '-')}
                </div>
                <div class="mb-3">
                    <strong>File:</strong><br>
                    <a href="/storage/${escapeHtml(data.file_path)}" target="_blank" class="btn btn-sm btn-primary">
                        <i class="fas fa-download me-1"></i>Download File
                    </a>
                </div>
            `;

            content.innerHTML = html;
        })
        .catch(() => {
            content.innerHTML = '<div class="alert alert-danger">Gagal memuat data lampiran.</div>';
        });
}

function loadAttachmentEdit(attachmentId) {
    fetch(`{{ route('planting-locations.attachments.show', [$plantingLocation, ':id']) }}`.replace(':id', attachmentId))
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_title').value = data.title;
            document.getElementById('edit_attachment_type').value = data.attachment_type || '';
            document.getElementById('edit_plant_type').value = data.plant_type || '';
            document.getElementById('edit_description').value = data.description || '';
            document.getElementById('edit_attachment_date').value = data.attachment_date;
            document.getElementById('formEditLampiran').action = `{{ route('planting-locations.attachments.update', [$plantingLocation, ':id']) }}`.replace(':id', attachmentId);
        })
        .catch(() => {
            alert('Gagal memuat data lampiran untuk diedit.');
        });
}
</script>
@endpush
@endsection
