@extends('layouts.app')

@section('title', 'Data Benih - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Data Benih</h4>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link" onclick="window.location.href='{{ route('seed-stock.index') }}'">
            <i class="fas fa-boxes me-1"></i>Stok Benih
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link active">
            <i class="fas fa-certificate me-1"></i>Data Benih
        </button>
    </li>
</ul>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nama Benih</th>
                        <th>Varietas</th>
                        <th>Asal Lokasi Penanaman/Produksi</th>
                        <th>No Laporan BPSB</th>
                        <th>Jumlah Benih</th>
                        <th>Tanggal Kadaluarsa</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($certifiedSeeds as $seed)
                    <tr>
                        <td>
                            <strong>{{ $seed->certification->plant->name }}</strong>
                        </td>
                        <td>
                            {{ $seed->certification->plant->variety ?: '-' }}
                        </td>
                        <td>
                            {{ $seed->certification->plantingLocation->name }}
                        </td>
                        <td>
                            {{ $seed->report_number_bpsb ?: '-' }}
                        </td>
                        <td>
                            <strong>{{ number_format($seed->certified_seed_quantity, 2) }} kg</strong>
                        </td>
                        <td>
                            @if($seed->expiry_date)
                                {{ $seed->expiry_date->format('d M Y') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('certifications.reports.show', $seed) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail Sertifikasi">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-certificate fa-3x mb-3"></i>
                                <p>Belum ada benih yang lulus sertifikasi.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($certifiedSeeds->hasPages())
            <div class="d-flex justify-content-center">
                {{ $certifiedSeeds->links() }}
            </div>
        @endif
    </div>
</div>
@endsection



