@php
    $unit = $unit ?? ($plant->satuanStok?->code ?: '');
    $historyMode = $historyMode ?? false;
    $labelQueue = $labelQueue ?? false;
    $showRecert = $showRecert ?? false;
    $showStorage = $showStorage ?? $showRecert;
@endphp
<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>No Sertifikasi BPSB</th>
                <th>No Induk</th>
                <th>Tanggal Lulus</th>
                <th>Total Hasil Uji</th>
                @unless($historyMode)
                    <th>Total Jumlah Stok ({{ $unit ?: 'satuan' }})</th>
                    <th>Stok Produk Saat Ini (kemasan)</th>
                @endunless
                <th>Tanggal Kadaluarsa</th>
                @if($showStorage)
                    <th>Lokasi penyimpanan</th>
                    <th>Tempat penyimpanan</th>
                @endif
                @unless($historyMode)
                    <th>Status</th>
                @endunless
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lots as $stock)
                @php
                    $lab = $stock->postHarvest;
                    $activePacks = $stock->packagings->where('status_kemasan', \App\Models\StockPackaging::STATUS_TERSEDIA)->count();
                    $hasLabel = $stock->labels->isNotEmpty() || $stock->certification_report_id;
                    $pendingLabel = $stock->pendingLabelReport();
                @endphp
                <tr>
                    <td>{{ $lab?->no_sertifikat_lab_bpsb ?: '-' }}</td>
                    <td>{{ $stock->nomor_induk ?: '-' }}</td>
                    <td>{{ $lab?->tgl_selesai_uji?->format('d M Y') ?: '-' }}</td>
                    <td>{{ number_format((float) ($lab?->total_hasil_uji ?? $stock->stok_awal), 2) }} {{ $unit }}</td>
                    @unless($historyMode)
                        <td>{{ number_format((float) $stock->stok_saat_ini, 2) }} {{ $unit }}</td>
                        <td>{{ $activePacks }}</td>
                    @endunless
                    <td>{{ $stock->tgl_kedaluwarsa?->format('d M Y') ?: '-' }}</td>
                    @if($showStorage)
                        <td>{{ $stock->storageLocationNames() }}</td>
                        <td>{{ $stock->storagePlaceNames() }}</td>
                    @endif
                    @unless($historyMode)
                        <td><span class="badge bg-{{ $stock->displayStatusBadge() }}">{{ $stock->displayStatusLabel() }}</span></td>
                    @endunless
                    <td>
                        @if($lab)
                            <a href="{{ route('seed-stock.lab-result', [$plant, $stock]) }}" class="btn btn-sm btn-outline-info mb-1">Lihat hasil uji lab</a>
                        @endif
                        @if($labelQueue || $pendingLabel)
                            <a href="{{ $pendingLabel ? route('seed-stock.label.storage', [$plant, $stock, $pendingLabel]) : route('seed-stock.label.create', [$plant, $stock]) }}" class="btn btn-sm btn-success mb-1">Input data stok benih</a>
                        @elseif($stock->isAwaitingLabel() && ! $hasLabel)
                            <a href="{{ route('seed-stock.label.create', [$plant, $stock]) }}" class="btn btn-sm btn-warning mb-1">Tambahkan Label Benih</a>
                        @elseif($hasLabel)
                            <a href="{{ route('seed-stock.lots.show', [$plant, $stock]) }}" class="btn btn-sm btn-outline-success mb-1">Lihat stok</a>
                            <a href="{{ route('public.seed-certificate', $stock) }}" class="btn btn-sm btn-outline-secondary mb-1">Lihat sertifikat</a>
                            @if($showRecert)
                                <a href="{{ route('seed-stock.lots.show', [$plant, $stock]) }}#recert" class="btn btn-sm btn-outline-warning mb-1">Sertifikasi ulang</a>
                            @endif
                            @php $reportRoute = $stock->productionReportRoute(); @endphp
                            @if($reportRoute)
                                <a href="{{ route('planting-locations.plantings.history-reports', $reportRoute) }}" class="btn btn-sm btn-outline-primary mb-1">Lihat laporan produksi</a>
                            @endif
                        @else
                            <a href="{{ route('seed-stock.label.create', [$plant, $stock]) }}" class="btn btn-sm btn-success mb-1">Tambahkan label</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ ($historyMode ? 6 : 9) + ($showStorage ? 2 : 0) }}" class="text-center text-muted">{{ $emptyText ?? 'Belum ada lot stok benih.' }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
