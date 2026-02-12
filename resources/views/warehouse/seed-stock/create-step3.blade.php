@extends('layouts.app')

@section('title', 'Tambah Tipe Benih Baru - Langkah 3 - SIBESTI')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Proses Selesai</h4>
</div>

<div class="card">
    <div class="card-body text-center py-5">
        <div class="mb-4">
            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
            <h4 class="text-success">Berhasil!</h4>
        </div>
        
        <p class="mb-4">
            Tipe inventaris <strong>"{{ $step1Data['name'] }}"</strong> telah berhasil dibuat dan ditautkan ke 
            <strong>{{ count($step2Data['warehouses']) }} Gudang</strong>.
        </p>

        <div class="alert alert-info text-start mb-4">
            <strong>Catatan:</strong> Anda belum menambahkan jumlah stok. Stok akan bertambah secara otomatis saat Anda menerima benih dari Modul Produksi/Sertifikasi ke dalam Lot, atau secara manual melalui halaman detail benih.
        </div>

        <form action="{{ route('seed-stock.store') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fas fa-check me-2"></i>Selesai (Kembali ke Daftar)
            </button>
        </form>
    </div>
</div>
@endsection

