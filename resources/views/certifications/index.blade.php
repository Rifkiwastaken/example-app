@extends('layouts.app')

@section('title', 'Manajemen Sertifikasi Benih - SIBESTI')

@section('content')
<div class="mb-4">
    <h4 class="mb-0">Manajemen Sertifikasi Benih</h4>
    <small class="text-muted">Modul Manajemen Proses Sertifikasi - Lacak setiap tahapan sertifikasi benih</small>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <form class="row g-3" method="GET" action="{{ route('certifications.index') }}">
            <div class="col-md-4">
                <label class="form-label">Komoditas/Tanaman</label>
                <select name="plant_id" class="form-select">
                    <option value="">Semua Komoditas</option>
                    @foreach($allPlants as $plant)
                        <option value="{{ $plant->plant_id }}" {{ request('plant_id') == $plant->plant_id ? 'selected' : '' }}>
                            {{ $plant->name }} - {{ $plant->variety ?: 'Tanpa Varietas' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button class="btn btn-primary" type="submit"><i class="fas fa-filter me-1"></i>Filter</button>
                <a class="btn btn-secondary" href="{{ route('certifications.index') }}"><i class="fas fa-times me-1"></i>Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nama Tanaman</th>
                        <th>Kategori</th>
                        <th>Varietas</th>
                        <th>Data panen yang siap disertifikasi</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plants as $plant)
                        <tr>
                            <td>
                                <strong>{{ $plant->name }}</strong>
                            </td>
                            <td>
                                {{ $plant->type?->name ?: '-' }}
                            </td>
                            <td>
                                {{ $plant->variety ?: '-' }}
                            </td>
                            <td>
                                <span class="badge bg-{{ ($plant->harvests_ready_for_cert_count ?? 0) > 0 ? 'info' : 'secondary' }}">
                                    {{ $plant->harvests_ready_for_cert_count ?? 0 }} data
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('certifications.by-plant', $plant) }}" class="btn btn-sm btn-primary" title="Kelola">
                                    <i class="fas fa-cog me-1"></i>Kelola
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-certificate fa-3x mb-3"></i>
                                    <p>Belum ada komoditas/tanaman dengan sertifikasi.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

