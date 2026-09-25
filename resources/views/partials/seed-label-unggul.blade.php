@php
    $label = $label ?? [];
@endphp
<div class="seed-label-unggul mx-auto border border-dark bg-white" style="max-width:420px;">
    <div class="px-3 py-2 border-bottom border-dark text-center" style="background:#14532d;color:#fff;">
        <div class="small text-uppercase fw-semibold">UPTD BBI TPH Sumatera Barat</div>
        <h5 class="mb-0">Label Benih Unggul Bersertifikat</h5>
    </div>
    <div class="p-3">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="small">
                <div><strong>PRODUSEN</strong><br>{{ $label['produsen'] ?? '-' }}</div>
                <div class="mt-2"><strong>ALAMAT</strong><br>{{ $label['alamat'] ?? '-' }}</div>
            </div>
            <div class="text-center">
                @if(!empty($label['qr_url']))
                    <img src="{{ $label['qr_url'] }}" alt="QR label" style="width:88px;height:88px;object-fit:contain">
                @else
                    <div id="{{ $qrTarget ?? 'label-qr' }}"></div>
                @endif
                <div class="small mt-1">QR LABEL</div>
            </div>
        </div>
        <div class="row g-2 small">
            @foreach([
                'NO INDUK' => $label['no_induk'] ?? '-',
                'NOMOR LOT' => $label['nomor_lot'] ?? '-',
                'NO SERI' => $label['no_seri'] ?? '-',
                'VARIETAS' => $label['varietas'] ?? '-',
                'KELAS BENIH' => $label['kelas_benih'] ?? '-',
                'ISI KEMASAN' => $label['isi_kemasan'] ?? '-',
                'JENIS TANAMAN' => $label['jenis_tanaman'] ?? '-',
                'TGL PANEN' => $label['tgl_panen'] ?? '-',
                'TGL SELESAI' => $label['tgl_selesai'] ?? '-',
                'TGL BERAKHIR' => $label['tgl_berakhir'] ?? '-',
                'DAYA BERKECAMBAH' => $label['daya_berkecambah'] ?? '-',
                'KADAR AIR' => $label['kadar_air'] ?? '-',
                'KEMURNIAN BENIH' => $label['kemurnian_benih'] ?? '-',
                'CVL' => $label['cvl'] ?? '-',
                'BIJI GULMA / BIJI TANAMAN LAIN' => $label['biji_gulma'] ?? '-',
                'KOTORAN' => $label['kotoran'] ?? '-',
            ] as $name => $value)
                <div class="col-6">
                    <div class="text-muted">{{ $name }}</div>
                    <div class="fw-semibold">{{ $value }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@if(empty($label['qr_url']) && !empty($label['qr_token']))
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>new QRCode(document.getElementById(@json($qrTarget ?? 'label-qr')), {text:@json($label['qr_token']), width:88, height:88});</script>
@endpush
@endif
