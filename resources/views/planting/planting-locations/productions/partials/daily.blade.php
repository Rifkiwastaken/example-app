<div class="d-flex justify-content-between mb-3">
    <h5 class="mb-0">Log Harian</h5>
    @if(empty($readonly) && $planting->canAddReports())
        <a href="{{ route('planting-locations.plantings.daily.create', [$plantingLocation, $planting]) }}" class="btn btn-success btn-sm">Tambahkan laporan harian</a>
    @endif
</div>
<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Laporan</th>
                <th>Asosiasi Produksi</th>
                <th>Fase Tumbuh</th>
                <th>Jenis Kegiatan</th>
                <th>Petugas</th>
                <th>Tanggal Aktivitas</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $item)
                <tr>
                    <td>{{ $item->title }}</td>
                    <td>{{ $planting->planting_batch_number }}</td>
                    <td>{{ $item->phaseLabel() }}</td>
                    <td>{{ $item->activityLabel() }}</td>
                    <td>{{ $item->officer?->name ?: '-' }}</td>
                    <td>{{ $item->activity_date?->format('d M Y') }}</td>
                    <td><button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#detail-daily-{{ $item->getKey() }}">Detail</button></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">Belum ada log harian.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@include('planting.planting-locations.productions.partials._daily-modals')
