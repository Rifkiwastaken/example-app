@foreach($reports as $item)
<div class="modal fade" id="detail-daily-{{ $item->getKey() }}" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Detail Laporan Harian</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <p><strong>Judul:</strong> {{ $item->title }}</p>
            <p><strong>Asosiasi produksi:</strong> {{ $planting->planting_batch_number }}</p>
            <p><strong>Lahan:</strong> {{ $planting->field?->kode_lahan }}</p>
            <p><strong>Fase tumbuh:</strong> {{ $item->phaseLabel() }}</p>
            <p><strong>Jenis kegiatan:</strong> {{ $item->activityLabel() }}</p>
            @if($item->usesProductFields())
                <p><strong>Detail produk yang digunakan:</strong> {{ $item->product_detail ?: '-' }}</p>
                <p><strong>Jumlah yang diterapkan:</strong> {{ $item->applied_amount ?: '-' }}</p>
                <p><strong>Metode menerapkan produk:</strong> {{ $item->application_method ?: '-' }}</p>
            @elseif($item->usesRougingFields())
                <p><strong>Jumlah tanaman yang dicabut:</strong> {{ $item->plants_removed !== null ? number_format((float) $item->plants_removed, 0) : '-' }}</p>
                <p><strong>Karakteristik:</strong> {{ $item->characteristic ?: '-' }}</p>
            @endif
            <p><strong>Isi laporan harian:</strong> {{ $item->description ?: '-' }}</p>
            <p><strong>Teknisi dari luar:</strong> {{ $item->external_technician ?: '-' }}</p>
            <p><strong>Petugas:</strong> {{ $item->officer?->name ?: '-' }}</p>
            <p><strong>Tanggal aktivitas:</strong> {{ $item->activity_date?->format('d M Y') }}</p>
            @if($item->file_path)
                <p><strong>Lampiran:</strong> <a href="{{ asset('storage/'.$item->file_path) }}" target="_blank">{{ $item->file_name ?: 'Unduh' }}</a></p>
            @endif
        </div>
    </div></div>
</div>
@endforeach
