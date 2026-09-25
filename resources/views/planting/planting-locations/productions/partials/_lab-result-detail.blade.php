<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>{{ $postHarvest->no_sertifikat_lab_bpsb }}</strong>
        <span class="badge bg-{{ $postHarvest->isLulus() ? 'success' : 'danger' }}">{{ $postHarvest->status_kelulusan_lab }}</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-3"><strong>Uji ke</strong><div>{{ $postHarvest->uji_ke }}</div></div>
            <div class="col-md-3 mb-3"><strong>Nomor induk</strong><div>{{ $postHarvest->nomor_induk }}</div></div>
            <div class="col-md-3 mb-3"><strong>Nomor lot</strong><div>{{ $postHarvest->nomor_lot }}</div></div>
            <div class="col-md-3 mb-3"><strong>Realisasi produksi</strong><div>{{ number_format((float) $postHarvest->realisasi_produksi, 2) }}</div></div>
            <div class="col-md-3 mb-3"><strong>Tanggal panen</strong><div>{{ $postHarvest->tgl_panen?->format('d M Y') ?: '-' }}</div></div>
            <div class="col-md-3 mb-3"><strong>Tanggal aju</strong><div>{{ $postHarvest->tgl_aju?->format('d M Y') ?: '-' }}</div></div>
            <div class="col-md-3 mb-3"><strong>Tanggal uji</strong><div>{{ $postHarvest->tgl_uji?->format('d M Y') ?: '-' }}</div></div>
            <div class="col-md-3 mb-3"><strong>Tanggal selesai uji</strong><div>{{ $postHarvest->tgl_selesai_uji?->format('d M Y') ?: '-' }}</div></div>
            <div class="col-md-3 mb-3"><strong>Kadar air</strong><div>{{ $postHarvest->kadar_air_persen }}%</div></div>
            <div class="col-md-3 mb-3"><strong>Benih murni</strong><div>{{ $postHarvest->benih_murni_persen }}%</div></div>
            <div class="col-md-3 mb-3"><strong>Kotoran benih</strong><div>{{ $postHarvest->kotoran_benih_persen }}%</div></div>
            <div class="col-md-3 mb-3"><strong>Benih tanaman lain</strong><div>{{ $postHarvest->benih_tanaman_lain_persen }}%</div></div>
            <div class="col-md-3 mb-3"><strong>Daya berkecambah</strong><div>{{ $postHarvest->daya_berkecambah_persen }}%</div></div>
            <div class="col-md-3 mb-3"><strong>Total hasil uji</strong><div>{{ number_format((float) $postHarvest->total_hasil_uji, 2) }}</div></div>
            <div class="col-md-3 mb-3"><strong>Tanggal kadaluarsa mutu</strong><div>{{ $postHarvest->tgl_kadaluarsa_mutu?->format('d M Y') ?: '-' }}</div></div>
            <div class="col-md-3 mb-3"><strong>Lampiran hasil lab</strong><div>
                @if($postHarvest->lampiran_hasil_lab)<a href="{{ asset('storage/'.$postHarvest->lampiran_hasil_lab) }}" target="_blank">Lihat berkas</a>@else - @endif
            </div></div>
            @if($postHarvest->alasan_uji_ulang)
                <div class="col-md-12 mb-3"><strong>Alasan uji ulang</strong><div>{{ $postHarvest->alasan_uji_ulang }}</div></div>
            @endif
            <div class="col-md-12 mb-3"><strong>Catatan kesehatan / penyakit</strong><div>{{ $postHarvest->catatan_kesehatan_penyakit ?: '-' }}</div></div>
            <div class="col-md-6"><strong>Dicatat oleh</strong><div>{{ $postHarvest->creator?->name ?: '-' }}</div></div>
            <div class="col-md-6"><strong>Dicatat pada</strong><div>{{ $postHarvest->created_at?->format('d M Y H:i') ?: '-' }}</div></div>
        </div>
    </div>
</div>
