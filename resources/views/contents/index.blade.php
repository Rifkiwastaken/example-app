@extends('layouts.app')
@section('title', 'Konten Situs - SIBESTI')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="mb-0">Konten Website</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('contents.settings') }}" class="btn btn-outline-secondary">Informasi Umum</a>
        <a href="{{ route('contents.create') }}" class="btn btn-success">Tambah Konten</a>
    </div>
</div>
<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2" method="GET">
            <div class="col-md-4">
                <select name="jenis" class="form-select">
                    <option value="">Semua jenis</option>
                    @foreach($jenisList as $key => $label)
                        <option value="{{ $key }}" {{ $jenis===$key ? 'selected' : '' }}>{{ $label }} ({{ $counts[$key] ?? 0 }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6"><input name="q" class="form-control" value="{{ request('q') }}" placeholder="Cari judul atau slug"></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Jenis</th><th>Judul</th><th>Kategori</th><th>Status</th><th>Tanggal</th><th></th></tr></thead>
            <tbody>
                @forelse($contents as $item)
                    <tr>
                        <td><span class="badge bg-success">{{ $item->jenisLabel() }}</span></td>
                        <td>{{ $item->judul }}</td>
                        <td>{{ $item->kategoriLabel() }}</td>
                        <td>{{ $item->is_published ? 'Publik' : 'Draft' }}</td>
                        <td>{{ optional($item->published_at ?: $item->created_at)->format('d/m/Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('contents.edit', $item) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                            <form action="{{ route('contents.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus konten ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada konten.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $contents->links() }}</div>
</div>
@endsection
