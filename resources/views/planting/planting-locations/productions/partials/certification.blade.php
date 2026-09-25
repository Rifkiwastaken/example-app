<div class="d-flex justify-content-between mb-3">
    <h5 class="mb-0">Laporan Sertifikasi</h5>
    @if(empty($readonly) && $planting->canAddReports())
        <a href="{{ route('planting-locations.plantings.certifications.create', [$plantingLocation, $planting]) }}" class="btn btn-success btn-sm">Tambahkan laporan</a>
    @endif
</div>

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Judul Laporan</th>
                <th>Tahapan Sertifikasi</th>
                <th>Tanggal</th>
                <th>Pengawas</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($certifications as $row)
                <tr>
                    <td>{{ $row['title'] }}</td>
                    <td>{{ $row['stage_label'] }}</td>
                    <td>{{ optional($row['date'])->format('d M Y') ?: '-' }}</td>
                    <td>{{ $row['supervisor'] ?: '-' }}</td>
                    <td><span class="badge bg-secondary">{{ $row['status'] }}</span></td>
                    <td>
                        <a href="{{ route('planting-locations.plantings.certifications.show', [$plantingLocation, $planting, $row['stage'], $row['record']->getKey()]) }}" class="btn btn-sm btn-outline-info">Lihat isi form</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">Belum ada laporan sertifikasi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<h6 class="mt-4 mb-3">Hasil Uji Lab Benih</h6>
<div class="table-responsive">
    <table class="table table-sm table-hover">
        <thead>
            <tr>
                <th>No Sertifikat Lab BPSB</th>
                <th>Uji Ke</th>
                <th>No Lot</th>
                <th>Tanggal Selesai Uji</th>
                <th>Total Hasil Uji</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($postHarvests as $item)
                <tr>
                    <td>{{ $item->no_sertifikat_lab_bpsb }}</td>
                    <td>{{ $item->uji_ke }}</td>
                    <td>{{ $item->nomor_lot }}</td>
                    <td>{{ $item->tgl_selesai_uji?->format('d M Y') ?: '-' }}</td>
                    <td>{{ number_format((float) $item->total_hasil_uji, 2) }}</td>
                    <td><span class="badge bg-{{ $item->isLulus() ? 'success' : 'danger' }}">{{ $item->status_kelulusan_lab }}</span></td>
                    <td><a href="{{ route('planting-locations.plantings.lab-result.show', [$plantingLocation, $planting, $item]) }}" class="btn btn-sm btn-outline-info">Lihat isi form</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">Belum ada hasil uji lab.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<h6 class="mt-4 mb-3">Pelabelan Sertifikat Benih</h6>
<div class="table-responsive">
    <table class="table table-sm table-hover">
        <thead>
            <tr>
                <th>Nomor lot</th>
                <th>No seri</th>
                <th>Jumlah label</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($labels ?? [] as $label)
                <tr>
                    <td>{{ $label->nomor_lot }}</td>
                    <td>{{ trim(($label->no_seri_label_awal ?: '').' – '.($label->no_seri_label_akhir ?: ''), ' –') }}</td>
                    <td>{{ $label->jumlah_lembar_label_dicetak }}</td>
                    <td>{{ optional($label->tgl_pemasangan_label ?? $label->created_at)->format('d M Y') }}</td>
                    <td><a href="{{ route('planting-locations.plantings.labels.show', [$plantingLocation, $planting, $label]) }}" class="btn btn-sm btn-outline-info">Lihat isi form</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">Belum ada pelabelan sertifikat benih.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
