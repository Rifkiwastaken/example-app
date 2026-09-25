@php
    $plantings = $plantings ?? collect();
    $historyMode = $historyMode ?? false;
@endphp
<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Lokasi Penanaman</th>
                <th>Lahan</th>
                <th>Nomor Batch Tanam</th>
                <th>Benih Sumber</th>
                <th>Jumlah Tanam</th>
                <th>Tanggal Tanam</th>
                <th>Estimasi Panen</th>
                <th>Progres</th>
                <th>Tahapan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($plantings as $planting)
                @php
                    $daysSince = $planting->planted_at ? $planting->planted_at->diffInDays(now()) : 0;
                    $unit = $planting->plant?->satuanTanam?->code ?: $plant->satuanTanam?->code ?: '';
                    $location = $planting->field?->plantingLocation;
                    $reportUrl = $location
                        ? ($historyMode
                            ? route('planting-locations.plantings.history-reports', [$location, $planting])
                            : route('planting-locations.plantings.reports', [$location, $planting]))
                        : '#';
                @endphp
                <tr>
                    <td>{{ $location?->name ?: '-' }}</td>
                    <td>{{ $planting->field?->kode_lahan ?: '-' }}</td>
                    <td>{{ $planting->planting_batch_number ?: '-' }}</td>
                    <td>{{ $planting->seedSource?->plant?->variety ?: $planting->plant?->variety ?: '-' }}</td>
                    <td>{{ $planting->planting_amount !== null ? number_format((float) $planting->planting_amount, 2).' '.$unit : '-' }}</td>
                    <td>{{ $planting->planted_at?->format('d M Y') ?: '-' }}</td>
                    <td>{{ $planting->estimated_harvest_date?->format('d M Y') ?: '-' }}</td>
                    <td><small class="text-muted">{{ $daysSince }} hari sejak tanam</small></td>
                    <td><span class="badge bg-{{ $planting->statusBadge() }}">{{ $planting->statusLabel() }}</span></td>
                    <td>
                        <a href="{{ $reportUrl }}" class="btn btn-sm btn-outline-info">Detail</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center text-muted">{{ $emptyText ?? 'Belum ada data.' }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
