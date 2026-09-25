@extends('layouts.public')
@section('title', 'Laporan & Dokumen - '.$situs->office_name)

@section('content')
<p class="page-kicker mb-1">Akuntabilitas</p>
<h1 class="h3 mb-4">Laporan & Dokumen</h1>
<div class="table-responsive bg-white rounded shadow-sm">
    <table class="table align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th width="70">Jenis</th>
                <th>Judul Dokumen</th>
                <th width="120">Ukuran File</th>
                <th width="260">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $doc)
                <tr>
                    <td class="text-center text-danger fs-4"><i class="fas fa-file-pdf"></i></td>
                    <td>
                        <div class="fw-semibold">{{ $doc->judul }}</div>
                        <span class="badge bg-success-subtle text-success">{{ $doc->kip_label ?: $doc->jenisLabel() }}</span>
                    </td>
                    <td>{{ $doc->fileSizeLabel() }}</td>
                    <td class="d-flex flex-wrap gap-2">
                        <a class="btn btn-sm btn-outline-success" href="{{ route('site.documents.show', $doc) }}">Lihat detail laporan</a>
                        <a class="btn btn-sm btn-emerald" href="{{ route('site.download', $doc) }}">📥 Unduh PDF</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada dokumen yang dipublikasikan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $documents->links() }}</div>
@endsection
