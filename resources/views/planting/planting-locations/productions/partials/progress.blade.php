<h5 class="mb-3">Detail Produksi Penanaman</h5>
<div class="row mb-4">
    <div class="col-md-4 mb-2"><strong>Tanaman:</strong> {{ $planting->plant?->name ?: '-' }}</div>
    <div class="col-md-4 mb-2"><strong>Benih sumber:</strong> {{ $planting->seedSource?->origin_lot_number ?: '-' }}</div>
    <div class="col-md-4 mb-2"><strong>Lahan:</strong> {{ $planting->field?->kode_lahan ?: '-' }}</div>
    <div class="col-md-4 mb-2"><strong>Nomor batch:</strong> {{ $planting->planting_batch_number ?: '-' }}</div>
    <div class="col-md-4 mb-2"><strong>Jumlah tanam:</strong> {{ $planting->planting_amount !== null ? number_format((float) $planting->planting_amount, 2).' '.($planting->plant?->satuanTanam?->code ?: '') : '-' }}</div>
    <div class="col-md-4 mb-2"><strong>Status:</strong> <span class="badge bg-{{ $planting->statusBadge() }}">{{ $planting->statusLabel() }}</span></div>
    <div class="col-md-4 mb-2"><strong>Tanggal tanam:</strong> {{ $planting->planted_at?->format('d M Y') ?: '-' }}</div>
    <div class="col-md-4 mb-2"><strong>Estimasi panen:</strong> {{ $planting->estimated_harvest_date?->format('d M Y') ?: '-' }}</div>
    <div class="col-md-4 mb-2"><strong>Target kelas:</strong> {{ $planting->target_kelas ?: '-' }}</div>
    <div class="col-md-4 mb-2"><strong>No form BPSB:</strong> {{ $planting->no_form_bpsb ?: '-' }}</div>
    <div class="col-md-4 mb-2"><strong>Tanggal daftar BPSB:</strong> {{ $planting->tanggal_daftar_bpsb?->format('d M Y') ?: '-' }}</div>
    <div class="col-md-12 mb-2"><strong>Deskripsi:</strong> {{ $historyDescription ?? ($planting->description ?: '-') }}</div>
</div>

<h5 class="mb-3">Riwayat Laporan</h5>
<div class="table-responsive">
    <table class="table table-sm table-hover">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Judul</th>
                <th>Petugas</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($timeline as $row)
                <tr>
                    <td>{{ optional($row['at'])->format('d M Y') ?: '-' }}</td>
                    <td>{{ $row['type'] }}</td>
                    <td>{{ $row['title'] }}</td>
                    <td>{{ $row['actor'] ?: '-' }}</td>
                    <td>
                        @php $linkPlanting = $row['report_planting'] ?? $planting; @endphp
                        @if($row['kind'] === 'certification')
                            <a href="{{ route('planting-locations.plantings.certifications.show', [$plantingLocation, $linkPlanting, $row['stage'], $row['item']->getKey()]) }}" class="btn btn-sm btn-outline-info">Lihat isi form</a>
                        @elseif($row['kind'] === 'post_harvest')
                            <a href="{{ route('planting-locations.plantings.lab-result.show', [$plantingLocation, $linkPlanting, $row['item']]) }}" class="btn btn-sm btn-outline-info">Lihat isi form</a>
                        @elseif($row['kind'] === 'label')
                            <a href="{{ route('planting-locations.plantings.labels.show', [$plantingLocation, $linkPlanting, $row['item']]) }}" class="btn btn-sm btn-outline-info">Lihat isi form</a>
                        @else
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#detail-daily-{{ $row['item']->getKey() }}">Detail</button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">Belum ada laporan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@include('planting.planting-locations.productions.partials._daily-modals')
