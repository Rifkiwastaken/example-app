@extends('layouts.app')

@section('title', 'Kontak - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Daftar Kontak</h4>
        <small class="text-muted">Kelola kontak penting untuk operasional penanaman, sertifikasi, gudang, dan penyuluhan.</small>
    </div>
    @if(auth()->user()->isAdmin())
        <a href="{{ route('contacts.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>Tambah Kontak Baru
        </a>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('contacts.index') }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="search" class="form-label">Pencarian</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="search" name="search" placeholder="Cari nama, instansi, atau nomor telepon..." value="{{ $filters['search'] }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ $filters['status'] === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="contact_type" class="form-label">Tipe Kontak</label>
                    <select name="contact_type" id="contact_type" class="form-select">
                        <option value="">Semua Tipe</option>
                        @foreach($contactTypes as $key => $label)
                            <option value="{{ $key }}" {{ $filters['contact_type'] === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nama Lengkap</th>
                        <th>Tipe Kontak</th>
                        <th>Instansi / Organisasi</th>
                        <th>Nomor Telepon</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contacts as $contact)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        @if($contact->photo_url)
                                            <img src="{{ $contact->photo_url }}" alt="{{ $contact->full_name }}" class="rounded-circle" style="width: 48px; height: 48px; object-fit: cover;">
                                        @else
                                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <strong>{{ $contact->full_name }}</strong>
                                        @if($contact->position)
                                            <br><small class="text-muted">{{ $contact->position }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $contact->contact_type_label }}</span>
                            </td>
                            <td>
                                @if($contact->organization)
                                    <span class="badge bg-success">{{ $contact->organization }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span><i class="fas fa-phone me-2 text-muted"></i>{{ $contact->primary_phone }}</span>
                                    @if($contact->primary_phone_is_whatsapp)
                                        <small class="text-success"><i class="fab fa-whatsapp me-1"></i>Tersedia WhatsApp</small>
                                    @endif
                                    @if($contact->secondary_phone)
                                        <small class="text-muted">Cadangan: {{ $contact->secondary_phone }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($contact->status === \App\Models\Contact::STATUS_ACTIVE)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Non-Aktif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="{{ route('contacts.show', $contact) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye me-1"></i>Detail
                                    </a>
                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-sm btn-outline-warning">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                        <form action="{{ route('contacts.destroy', $contact) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kontak ini?')" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash me-1"></i>Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-address-book fa-3x mb-3"></i>
                                    <p class="mb-0">Belum ada kontak yang terdaftar.</p>
                                    @if(auth()->user()->isAdmin())
                                        <div class="mt-3">
                                            <a href="{{ route('contacts.create') }}" class="btn btn-success">
                                                <i class="fas fa-plus me-2"></i>Tambah Kontak Pertama
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contacts->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $contacts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection


