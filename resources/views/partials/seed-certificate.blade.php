@php
    $cert = $cert ?? [];
    $print = $print ?? false;
@endphp
<div class="seed-certificate border rounded-3 bg-white p-4 {{ $print ? '' : 'shadow-sm' }}">
    <div class="text-center mb-4">
        <div class="text-uppercase small text-muted fw-semibold">UPTD BBI TPH Provinsi Sumatera Barat</div>
        <h4 class="mb-0">Informasi Sertifikat Benih</h4>
    </div>
    <div class="row g-2 small">
        @foreach([
            'PROVINSI' => $cert['provinsi'] ?? '-',
            'JENIS BENIH' => $cert['jenis_benih'] ?? '-',
            'NAMA' => $cert['nama'] ?? '-',
            'ALAMAT' => $cert['alamat'] ?? '-',
            'REALISASI LUAS' => $cert['realisasi_luas'] ?? '-',
            'REALISASI PRODUKSI' => $cert['realisasi_produksi'] ?? '-',
            'NOMOR LOT' => $cert['nomor_lot'] ?? '-',
            'KELAS BENIH' => $cert['kelas_benih'] ?? '-',
            'VARIETAS' => $cert['varietas'] ?? '-',
            'VOLUME' => $cert['volume'] ?? '-',
            'ISI KEMASAN' => $cert['isi_kemasan'] ?? '-',
            'JUMLAH' => $cert['jumlah'] ?? '-',
            'NO INDUK' => $cert['no_induk'] ?? '-',
            'TGL PANEN' => $cert['tgl_panen'] ?? '-',
            'TGL AJU' => $cert['tgl_aju'] ?? '-',
            'TGL UJI' => $cert['tgl_uji'] ?? '-',
            'TGL SELESAI' => $cert['tgl_selesai'] ?? '-',
            'HASIL UJI' => $cert['hasil_uji'] ?? '-',
            'TGL BERAKHIR' => $cert['tgl_berakhir'] ?? '-',
            'NO SERI' => $cert['no_seri'] ?? '-',
            'KADAR AIR' => $cert['kadar_air'] ?? '-',
            'KEMURNIAN BENIH' => $cert['kemurnian_benih'] ?? '-',
            'CVL' => $cert['cvl'] ?? '-',
            'BIJI GULMA / BIJI TANAMAN LAIN' => $cert['biji_gulma'] ?? '-',
            'KOTORAN' => $cert['kotoran'] ?? '-',
            'DAYA BERKECAMBAH' => $cert['daya_berkecambah'] ?? '-',
        ] as $label => $value)
            <div class="col-md-6">
                <div class="d-flex justify-content-between border-bottom py-1">
                    <span class="text-muted">{{ $label }}</span>
                    <strong class="text-end ms-3">{{ $value }}</strong>
                </div>
            </div>
        @endforeach
        <div class="col-12 mt-3">
            <div class="text-muted small mb-2">QR LABEL</div>
            @if(!empty($cert['qr_url']))
                <img src="{{ $cert['qr_url'] }}" alt="QR label" style="width:120px;height:120px;object-fit:contain">
            @else
                <span class="text-muted">-</span>
            @endif
        </div>
    </div>
</div>
