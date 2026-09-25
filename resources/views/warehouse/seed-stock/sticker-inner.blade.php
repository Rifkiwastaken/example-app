@php
    $unit = $plant->satuanStok?->code ?: '';
    $label = $packaging->label ?: $stock->certificationReport;
    $qrUrl = $label?->qrLabelUrl();
@endphp
<div class="col-md-4 mb-3">
    <div class="border border-dark p-2 h-100">
        <strong>UPTD BBI TPPH</strong>
        <div class="small">{{ $packaging->no_label_seri }}</div>
        <div class="small">BPSB: {{ $label?->no_sertifikat_bpsb_final ?: $stock->postHarvest?->no_sertifikat_lab_bpsb ?: '-' }}</div>
        <div class="small">{{ $plant->type?->name ?: $plant->name }} / {{ $plant->variety }}</div>
        <div class="small">Label {{ $label?->warna_label ?: '-' }} · {{ number_format((float)$packaging->kapasitas_per_kemasan,2) }} {{ $unit }}</div>
        <div class="small">Exp {{ $stock->tgl_kedaluwarsa?->format('d M Y') }} · {{ $packaging->rack?->name ?: '-' }}</div>
        @if($qrUrl)
            <img src="{{ $qrUrl }}" alt="QR label" style="width:64px;height:64px;object-fit:contain">
        @else
            <div class="qr-{{ $packaging->id }}"></div>
        @endif
    </div>
</div>
@if(! $qrUrl)
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>new QRCode(document.querySelector('.qr-{{ $packaging->id }}'), {text:'{{ $packaging->qr_code_token }}', width:64, height:64});</script>
@endpush
@endif
