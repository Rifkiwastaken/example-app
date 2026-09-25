@extends('layouts.app')

@section('title', $stageLabel.' - SIBESTI')

@php
    $bool = fn ($value, $yes, $no) => $value === null ? '-' : ($value ? $yes : $no);
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">{{ $item->reportTitle() }}</h4>
        <small class="text-muted">{{ $stageLabel }} · {{ $planting->planting_batch_number }}</small>
    </div>
    <a href="{{ route('planting-locations.plantings.reports', [$plantingLocation, $planting, 'tab' => 'certification']) }}" class="btn btn-secondary">Kembali</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            @if($stage === 'application')
                <div class="col-md-4 mb-3"><strong>Komoditas</strong><div>{{ $item->komoditas }}</div></div>
                <div class="col-md-4 mb-3"><strong>Nama varietas</strong><div>{{ $item->nama_varietas }}</div></div>
                <div class="col-md-4 mb-3"><strong>Kelas benih tujuan</strong><div>{{ $item->kelas_benih_tujuan }}</div></div>
                <div class="col-md-4 mb-3"><strong>Luas lahan (ha)</strong><div>{{ $item->luas_lahan_ha }}</div></div>
                <div class="col-md-4 mb-3"><strong>Koordinat GPS lahan</strong><div>{{ $item->koordinat_gps_lahan ?: '-' }}</div></div>
                <div class="col-md-4 mb-3"><strong>File peta / sketsa</strong><div>
                    @if($item->file_peta_sketsa)<a href="{{ asset('storage/'.$item->file_peta_sketsa) }}" target="_blank">Lihat berkas</a>@else - @endif
                </div></div>
                <div class="col-md-4 mb-3"><strong>No label benih sumber</strong><div>{{ $item->no_label_benih_sumber }}</div></div>
                <div class="col-md-4 mb-3"><strong>Kelas benih sumber</strong><div>{{ $item->kelas_benih_sumber }}</div></div>
                <div class="col-md-4 mb-3"><strong>Produsen asal benih sumber</strong><div>{{ $item->produsen_asal_benih_sumber }}</div></div>
                <div class="col-md-4 mb-3"><strong>Jumlah benih sumber (kg)</strong><div>{{ $item->jumlah_benih_sumber_kg }}</div></div>
                <div class="col-md-4 mb-3"><strong>Rencana tanggal sebar</strong><div>{{ $item->rencana_tgl_sebar?->format('d M Y') ?: '-' }}</div></div>
                <div class="col-md-4 mb-3"><strong>Rencana tanggal tanam</strong><div>{{ $item->rencana_tgl_tanam?->format('d M Y') ?: '-' }}</div></div>
                <div class="col-md-4 mb-3"><strong>Status pengajuan</strong><div>{{ $item->status_pengajuan }}</div></div>
                <div class="col-md-12 mb-3"><strong>Sejarah lahan sebelumnya</strong><div>{{ $item->sejarah_lahan_sebelumnya ?: '-' }}</div></div>
            @elseif($stage === 'inspection')
                <div class="col-md-3 mb-3"><strong>Fase inspeksi</strong><div>{{ $item->fase_inspeksi }}</div></div>
                <div class="col-md-3 mb-3"><strong>Tanggal inspeksi</strong><div>{{ $item->tgl_inspeksi?->format('d M Y') ?: '-' }}</div></div>
                <div class="col-md-3 mb-3"><strong>Umur tanaman (HST)</strong><div>{{ $item->umur_tanaman_hst }}</div></div>
                <div class="col-md-3 mb-3"><strong>Nama PBT pemeriksa</strong><div>{{ $item->nama_pbt_pemeriksa }}</div></div>
                <div class="col-md-3 mb-3"><strong>Jarak isolasi (meter)</strong><div>{{ $item->jarak_isolasi_meter ?? '-' }}</div></div>
                <div class="col-md-3 mb-3"><strong>Status isolasi waktu</strong><div>{{ $bool($item->status_isolasi_waktu, 'Memenuhi', 'Tidak memenuhi') }}</div></div>
                <div class="col-md-3 mb-3"><strong>Kesesuaian dokumen sumber</strong><div>{{ $bool($item->kesesuaian_dokumen_sumber, 'Sesuai', 'Tidak sesuai') }}</div></div>
                <div class="col-md-3 mb-3"><strong>Total tanaman sampel</strong><div>{{ $item->total_tanaman_sampel }}</div></div>
                <div class="col-md-3 mb-3"><strong>Total CVL ditemukan</strong><div>{{ $item->total_cvl_ditemukan }}</div></div>
                <div class="col-md-3 mb-3"><strong>Persentase CVL akhir</strong><div>{{ $item->persentase_cvl_akhir }}%</div></div>
                <div class="col-md-3 mb-3"><strong>Jenis OPT dominan</strong><div>{{ $item->jenis_opt_dominan ?: '-' }}</div></div>
                <div class="col-md-3 mb-3"><strong>Tingkat serangan OPT</strong><div>{{ $item->tingkat_serangan_opt }}</div></div>
                <div class="col-md-3 mb-3"><strong>Status roguing</strong><div>{{ $item->status_roguing }}</div></div>
                <div class="col-md-3 mb-3"><strong>Status kelulusan fase</strong><div>{{ $item->status_kelulusan_fase }}</div></div>
            @elseif($stage === 'field_sample')
                <div class="col-md-3 mb-3"><strong>Nomor titik sampel</strong><div>{{ $item->nomor_titik_sampel }}</div></div>
                <div class="col-md-3 mb-3"><strong>Jumlah tanaman diperiksa</strong><div>{{ $item->jumlah_tanaman_diperiksa }}</div></div>
                <div class="col-md-3 mb-3"><strong>Jumlah CVL ditemukan</strong><div>{{ $item->jumlah_cvl_ditemukan }}</div></div>
                <div class="col-md-3 mb-3"><strong>Persentase CVL</strong><div>{{ number_format($item->persentaseCvl(), 2) }}%</div></div>
            @elseif($stage === 'harvest')
                <div class="col-md-4 mb-3"><strong>Tanggal panen</strong><div>{{ $item->tgl_panen?->format('d M Y') ?: '-' }}</div></div>
                <div class="col-md-4 mb-3"><strong>Volume kotor panen (kg)</strong><div>{{ $item->volume_kotor_panen_kg }}</div></div>
                <div class="col-md-4 mb-3"><strong>No segel sementara</strong><div>{{ $item->no_segel_sementara ?: '-' }}</div></div>
                <div class="col-md-4 mb-3"><strong>Status kebersihan alat panen</strong><div>{{ $bool($item->status_kebersihan_alat_panen, 'Bersih', 'Tidak bersih') }}</div></div>
                <div class="col-md-4 mb-3"><strong>Status kebersihan wadah</strong><div>{{ $bool($item->status_kebersihan_wadah, 'Bersih', 'Tidak bersih') }}</div></div>
                <div class="col-md-4 mb-3"><strong>Nama pengawas PBT</strong><div>{{ $item->nama_pengawas_pbt ?: '-' }}</div></div>
            @elseif($stage === 'pcb')
                <div class="col-md-3 mb-3"><strong>No berita acara PCB</strong><div>{{ $item->no_berita_acara_pcb ?: '-' }}</div></div>
                <div class="col-md-3 mb-3"><strong>No segel sampel lab</strong><div>{{ $item->no_segel_sampel_lab ?: '-' }}</div></div>
                <div class="col-md-3 mb-3"><strong>Berat sampel kirim (gram)</strong><div>{{ $item->berat_sampel_kirim_gram ?? '-' }}</div></div>
                <div class="col-md-3 mb-3"><strong>Status posisi lot</strong><div>{{ $item->status_posisi_lot }}</div></div>
            @endif

            <div class="col-md-4 mb-3"><strong>Lampiran formulir</strong><div>
                @if($item->lampiran_formulir)<a href="{{ asset('storage/'.$item->lampiran_formulir) }}" target="_blank">Lihat berkas</a>@else - @endif
            </div></div>
            <div class="col-md-4 mb-3"><strong>Dibuat oleh</strong><div>{{ $item->creator?->name ?: '-' }}</div></div>
            <div class="col-md-4 mb-3"><strong>Dibuat pada</strong><div>{{ $item->created_at?->format('d M Y H:i') ?: '-' }}</div></div>
        </div>
    </div>
</div>
@endsection
