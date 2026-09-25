@extends('layouts.app')



@section('title', 'Benih Sumber - SIBESTI')



@push('styles')

<style>

.badge-kelas-bs { background:#facc15; color:#422006; }

.badge-kelas-fs { background:#fff; color:#111; border:1px solid #cbd5e1; }

.badge-kelas-ss { background:#c084fc; color:#3b0764; }

.badge-kelas-es { background:#60a5fa; color:#1e3a8a; }

</style>

@endpush



@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div class="d-flex align-items-center">

        <a href="{{ route('plants.index') }}" class="btn btn-secondary me-3">

            <i class="fas fa-arrow-left me-2"></i>Kembali

        </a>

        <div>

            <h4 class="mb-0">{{ $plant->name }}</h4>

            <small class="text-muted">{{ $plant->type?->name ?: 'Tidak ada tipe' }}{{ $plant->variety ? ' · ' . $plant->variety : '' }}</small>

        </div>

    </div>

</div>



<ul class="nav nav-tabs" role="tablist">

    @include('planting.plants._tabs', ['plant' => $plant, 'activeTab' => 'seed-sources'])

</ul>



<div class="tab-content p-3 bg-white border border-top-0 rounded-bottom">

    @if(session('success'))

        <div class="alert alert-success">{{ session('success') }}</div>

    @endif



    <div class="d-flex justify-content-between align-items-center mb-3">

        <h5 class="mb-0">Benih Sumber</h5>

        <a href="{{ route('plants.seed-sources.create', $plant) }}" class="btn btn-success btn-sm">

            <i class="fas fa-plus me-1"></i>Tambah Benih Sumber

        </a>

    </div>



    <div class="table-responsive">

        <table class="table table-hover">

            <thead>

                <tr>

                    <th>Tanggal Ditambahkan</th>

                    <th>Kelas Benih</th>

                    <th>Nomor Lot Asal Benih Sumber</th>

                    <th>Produsen Asal</th>

                    <th>Total Benih Sumber</th>

                    <th>Jumlah Benih Sumber yang Tersedia</th>

                    <th>Lahan Penanaman</th>

                    <th>Aksi</th>

                </tr>

            </thead>

            <tbody>

                @forelse($seedSources as $source)

                    <tr>

                        <td>{{ $source->created_at?->format('d/m/Y') }}</td>

                        <td><span class="badge {{ $source->seedClassBadgeClass() }}">{{ $source->seedClassLabel() }}</span></td>

                        <td>{{ $source->origin_lot_number }}</td>

                        <td>{{ $source->origin_producer }}</td>

                        <td>{{ number_format($source->totalQuantity(), 2, ',', '.') }} {{ $plant->satuanStok?->code ?: 'kg' }}</td>

                        <td>{{ number_format((float) $source->quantity_kg, 2, ',', '.') }} {{ $plant->satuanStok?->code ?: 'kg' }}</td>

                        <td>

                            @foreach($source->plantedFieldLabels() as $label)

                                <span class="data-pill data-pill-muted">{{ $label }}</span>

                            @endforeach

                            @if(empty($source->plantedFieldLabels()))-@endif

                        </td>

                        <td>

                            <a href="{{ route('plants.seed-sources.show', [$plant, $source]) }}" class="btn btn-sm btn-outline-info">Detail</a>

                            @if(auth()->user()->role !== 'penangkar')

                                <a href="{{ route('plants.seed-sources.edit', [$plant, $source]) }}" class="btn btn-sm btn-outline-warning">Edit</a>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr><td colspan="8" class="text-center text-muted">Belum ada data benih sumber.</td></tr>

                @endforelse

            </tbody>

        </table>

    </div>

    @if($seedSources->hasPages())

        <div class="d-flex justify-content-center mt-3">{{ $seedSources->links() }}</div>

    @endif

</div>

@endsection

