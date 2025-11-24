@extends('layouts.app')

@section('title', 'Manajemen Sertifikasi Benih - SIBIT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Manajemen Sertifikasi Benih</h4>
        <small class="text-muted">Modul Manajemen Proses Sertifikasi - Lacak setiap tahapan sertifikasi benih</small>
    </div>
    <a href="{{ route('certifications.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-2"></i>Tambah Sertifikasi
    </a>
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
                    @foreach($plants as $plant)
                        <option value="{{ $plant->id }}" {{ request('plant_id') == $plant->id ? 'selected' : '' }}>
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
                        <th>Varietas</th>
                        <th>Blok Lahan</th>
                        <th>Komoditas</th>
                        <th>Status Sertifikasi</th>
                        <th>Tgl. Laporan Terakhir</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($harvests as $harvest)
                        @php
                            $certification = $harvest->certification;
                            $variety = $harvest->plant->variety ?: '-';
                            $blockName = $harvest->location?->name ?: '-';
                            $commodity = $harvest->plant->type?->name ?: $harvest->plant->name;
                            $status = $certification ? $certification->status_label : 'Belum Dikelola';
                            $statusClass = $certification ? match($certification->certification_status) {
                                'dalam_proses' => 'bg-warning',
                                'lulus' => 'bg-success',
                                'tidak_lulus' => 'bg-danger',
                                'selesai' => 'bg-info',
                                default => 'bg-secondary',
                            } : 'bg-secondary';
                            $lastReportDate = $certification ? $certification->latest_report_date : '-';
                        @endphp
                        <tr>
                            <td>
                                @if($variety != '-')
                                    <i class="fas fa-leaf text-success me-2"></i>{{ $variety }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($blockName != '-')
                                    {{ $blockName }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <i class="fas fa-seedling text-primary me-2"></i>{{ $commodity }}
                            </td>
                            <td>
                                <span class="badge {{ $statusClass }}">{{ $status }}</span>
                            </td>
                            <td>
                                @if($lastReportDate != '-')
                                    <i class="fas fa-calendar me-2"></i>{{ $lastReportDate }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('certifications.show', $harvest) }}" class="btn btn-sm btn-primary" title="Kelola">
                                        <i class="fas fa-cog me-1"></i>Kelola
                                    </a>
                                    <form action="{{ route('harvests.destroy', $harvest) }}" method="POST" class="d-inline" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus data sertifikasi ini? Data harvest yang terkait juga akan dihapus.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-certificate fa-3x mb-3"></i>
                                    <p>Belum ada lot produksi untuk sertifikasi.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($harvests->hasPages())
            <div class="d-flex justify-content-center">
                {{ $harvests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

