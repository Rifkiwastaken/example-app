@extends('layouts.app')

@section('title', 'Detail Kontak - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Detail Kontak</h4>
        <small class="text-muted">Informasi lengkap kontak untuk koordinasi kegiatan lapangan.</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <form action="{{ route('contacts.destroy', $contact) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kontak ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash me-2"></i>Hapus
                </button>
            </form>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center g-4">
            <div class="col-md-3 text-center">
                @if($contact->photo_url)
                    <img src="{{ $contact->photo_url }}" alt="{{ $contact->full_name }}" class="rounded-circle mb-3" style="width: 140px; height: 140px; object-fit: cover;">
                @else
                    <div class="bg-light border rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 140px; height: 140px;">
                        <i class="fas fa-user fa-4x text-muted"></i>
                    </div>
                @endif
                <div>
                    @if($contact->status === \App\Models\Contact::STATUS_ACTIVE)
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-secondary">Non-Aktif</span>
                    @endif
                    <span class="badge bg-light text-dark border ms-1">{{ $contact->contact_type_label }}</span>
                </div>
            </div>
            <div class="col-md-9">
                <h3 class="mb-1">{{ $contact->full_name }}</h3>
                @if($contact->position)
                    <p class="text-muted mb-2">{{ $contact->position }}</p>
                @endif
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-building text-muted me-3"></i>
                            <div>
                                <strong>Instansi / Organisasi</strong>
                                <div>{{ $contact->organization ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-id-card text-muted me-3"></i>
                            <div>
                                <strong>NIP</strong>
                                <div>{{ $contact->nip ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="detail-tab" data-bs-toggle="tab" data-bs-target="#detail-pane" type="button" role="tab" aria-controls="detail-pane" aria-selected="true">
            <i class="fas fa-info-circle me-1"></i>Detail Informasi
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity-pane" type="button" role="tab" aria-controls="activity-pane" aria-selected="false">
            <i class="fas fa-leaf me-1"></i>Aktivitas Terkait
        </button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="detail-pane" role="tabpanel" aria-labelledby="detail-tab">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Informasi Kontak</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Telepon Utama</dt>
                            <dd class="col-sm-8">
                                {{ $contact->primary_phone }}
                                @if($contact->primary_phone_is_whatsapp)
                                    <br><span class="badge bg-success mt-1"><i class="fab fa-whatsapp me-1"></i>WhatsApp</span>
                                @endif
                            </dd>

                            <dt class="col-sm-4">Telepon Lain</dt>
                            <dd class="col-sm-8">{{ $contact->secondary_phone ?? '-' }}</dd>

                            <dt class="col-sm-4">Email</dt>
                            <dd class="col-sm-8">
                                @if($contact->email)
                                    <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
                                @else
                                    -
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Alamat Domisili</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">{{ $contact->address }}</p>
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Provinsi</dt>
                            <dd class="col-sm-8">{{ $contact->province ?? '-' }}</dd>

                            <dt class="col-sm-4">Kab/Kota</dt>
                            <dd class="col-sm-8">{{ $contact->city ?? '-' }}</dd>

                            <dt class="col-sm-4">Kecamatan</dt>
                            <dd class="col-sm-8">{{ $contact->district ?? '-' }}</dd>

                            <dt class="col-sm-4">Desa/Kel.</dt>
                            <dd class="col-sm-8">{{ $contact->village ?? '-' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Catatan</h5>
                    </div>
                    <div class="card-body">
                        @if($contact->notes)
                            <p class="mb-0">{{ $contact->notes }}</p>
                        @else
                            <span class="text-muted">Belum ada catatan tambahan.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="activity-pane" role="tabpanel" aria-labelledby="activity-tab">
        <div class="card">
            <div class="card-body">
                @if($relatedPlantingLocations->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Lokasi Penanaman</th>
                                    <th>Jenis</th>
                                    <th>Penanggung Jawab</th>
                                    <th>Deskripsi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($relatedPlantingLocations as $location)
                                    <tr>
                                        <td>{{ $location->name }}</td>
                                        <td>{{ $location->location_type }}</td>
                                        <td>{{ $contact->full_name }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($location->description, 80) }}</td>
                                        <td>
                                            <a href="{{ route('planting-locations.show', $location) }}" class="btn btn-sm btn-outline-primary">Lihat Lokasi</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-info-circle fa-2x text-muted mb-3"></i>
                        <p class="mb-2">Belum ada aktivitas atau lahan yang terhubung dengan kontak ini.</p>
                        <small class="text-muted">Hubungkan kontak sebagai penanggung jawab lahan pada modul lokasi penanaman untuk menampilkan data di sini.</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


