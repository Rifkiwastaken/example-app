<?php

namespace App\Support;

use App\Models\CertificationReport;
use App\Models\Plant;
use App\Models\PlantingPostHarvest;
use App\Models\Stock;
use App\Models\StockPackaging;
use App\Models\WebsiteSetting;

class SeedCertificate
{
    public static function stockRelations(): array
    {
        return [
            'plant.type',
            'plant.satuanStok',
            'postHarvest.planting.field.plantingLocation',
            'postHarvest.planting.seedSource.variety.type',
            'certificationReport',
            'packagings',
        ];
    }

    public static function packagingRelations(): array
    {
        return [
            'stock.plant.type',
            'stock.plant.satuanStok',
            'stock.postHarvest.planting.field.plantingLocation',
            'stock.certificationReport',
            'label',
        ];
    }

    public static function activeLotsForPlant(Plant $plant): \Illuminate\Support\Collection
    {
        $today = now()->toDateString();

        return Stock::query()
            ->where('seed_varieties_id', $plant->getKey())
            ->where('status_stok', Stock::STATUS_SIAP)
            ->where('stok_saat_ini', '>', 0)
            ->whereDate('tgl_kedaluwarsa', '>=', $today)
            ->with(self::stockRelations())
            ->orderBy('tgl_kedaluwarsa')
            ->get()
            ->filter(fn (Stock $stock) => $stock->hasLabel())
            ->groupBy(fn (Stock $stock) => $stock->nomor_induk ?: 'id-'.$stock->id)
            ->map(function ($group) {
                $primary = $group->sortBy(fn (Stock $stock) => optional($stock->tgl_kedaluwarsa)->timestamp ?? PHP_INT_MAX)->first();
                $primary->stok_saat_ini = $group->sum(fn (Stock $stock) => (float) $stock->stok_saat_ini);
                $primary->setRelation('packagings', $group->pluck('packagings')->flatten(1)->values());

                return $primary;
            })
            ->values();
    }

    public static function lotRow(Stock $stock): array
    {
        $unit = $stock->plant?->satuanStok?->code ?: '';
        $label = $stock->certificationReport;
        $lab = $stock->postHarvest;
        $activePacks = $stock->packagings
            ->where('status_kemasan', StockPackaging::STATUS_TERSEDIA)
            ->filter(fn (StockPackaging $pkg) => $pkg->isSellable())
            ->count();
        $size = (float) ($label?->ukuran_kemasan_retail_kg ?? $stock->packagings->first()?->kapasitas_per_kemasan ?? 0);

        return [
            'stock_id' => $stock->id,
            'plant_id' => $stock->seed_varieties_id,
            'nomor_induk' => $stock->nomor_induk ?: '-',
            'tanggal_lulus' => optional($lab?->tgl_selesai_uji)->translatedFormat('d F Y') ?: '-',
            'tanggal_lulus_raw' => optional($lab?->tgl_selesai_uji)->format('Y-m-d'),
            'total_stok' => (float) $stock->stok_saat_ini,
            'isi_kemasan' => $size,
            'total_produk' => $activePacks,
            'tanggal_kadaluarsa' => optional($stock->tgl_kedaluwarsa)->translatedFormat('d F Y') ?: '-',
            'tanggal_kadaluarsa_raw' => optional($stock->tgl_kedaluwarsa)->format('Y-m-d'),
            'unit' => $unit,
            'unit_price' => (float) ($stock->plant?->harga_jual ?? 0),
            'kelas_benih' => $label?->warnaLabel() ?: ($stock->postHarvest?->planting?->target_kelas ?: '-'),
        ];
    }

    public static function certificatePayload(Stock $stock, ?CertificationReport $label = null): array
    {
        $stock->loadMissing(self::stockRelations());
        $lab = $stock->postHarvest;
        $planting = $lab?->planting;
        $field = $planting?->field;
        $location = $field?->plantingLocation;
        $plant = $stock->plant ?: $planting?->seedSource?->variety;
        $label = $label ?: $stock->certificationReport;
        $unit = $plant?->satuanStok?->code ?: '';

        return [
            'provinsi' => $location?->province
                ?: collect(preg_split('/,/', (string) ($location?->administrative_address ?: $location?->location_summary)))
                    ->map(fn ($part) => trim((string) $part))
                    ->filter()
                    ->last() ?: '-',
            'jenis_benih' => $plant?->type?->name ?: ($plant?->name ?: '-'),
            'nama' => $location?->name ?: '-',
            'alamat' => $location?->location_summary ?: ($location?->administrative_address ?: '-'),
            'realisasi_luas' => $field?->luas_ha !== null ? number_format((float) $field->luas_ha, 2).' ha' : '-',
            'realisasi_produksi' => $lab?->realisasi_produksi !== null
                ? number_format((float) $lab->realisasi_produksi, 2).($unit ? ' '.$unit : '')
                : '-',
            'nomor_lot' => $label?->nomor_lot ?: ($lab?->nomor_lot ?: '-'),
            'kelas_benih' => $label?->warnaLabel() ?: ($planting?->target_kelas ?: '-'),
            'varietas' => $plant?->variety ?: ($plant?->name ?: '-'),
            'volume' => $lab?->total_hasil_uji !== null
                ? number_format((float) $lab->total_hasil_uji, 2).($unit ? ' '.$unit : '')
                : '-',
            'isi_kemasan' => $label?->ukuran_kemasan_retail_kg !== null
                ? number_format((float) $label->ukuran_kemasan_retail_kg, 2).($unit ? ' '.$unit : '')
                : '-',
            'jumlah' => $label?->jumlah_lembar_label_dicetak !== null
                ? (string) $label->jumlah_lembar_label_dicetak
                : (string) $stock->packagings->count(),
            'no_induk' => $stock->nomor_induk ?: ($lab?->nomor_induk ?: '-'),
            'tgl_panen' => optional($lab?->tgl_panen)->translatedFormat('d F Y') ?: '-',
            'tgl_aju' => optional($lab?->tgl_aju)->translatedFormat('d F Y') ?: '-',
            'tgl_uji' => optional($lab?->tgl_uji)->translatedFormat('d F Y') ?: '-',
            'tgl_selesai' => optional($lab?->tgl_selesai_uji)->translatedFormat('d F Y') ?: '-',
            'hasil_uji' => $lab?->total_hasil_uji !== null
                ? number_format((float) $lab->total_hasil_uji, 2).($unit ? ' '.$unit : '')
                : '-',
            'tgl_berakhir' => optional($lab?->tgl_kadaluarsa_mutu ?: $stock->tgl_kedaluwarsa)->translatedFormat('d F Y') ?: '-',
            'no_seri' => $label
                ? trim(($label->no_seri_label_awal ?: '').' – '.($label->no_seri_label_akhir ?: ''), ' –')
                : '-',
            'kadar_air' => self::percent($lab?->kadar_air_persen),
            'kemurnian_benih' => self::percent($lab?->benih_murni_persen),
            'cvl' => $lab?->catatan_kesehatan_penyakit ?: '-',
            'biji_gulma' => self::percent($lab?->benih_tanaman_lain_persen),
            'kotoran' => self::percent($lab?->kotoran_benih_persen),
            'daya_berkecambah' => $lab?->daya_berkecambah_persen !== null
                ? rtrim(rtrim(number_format((float) $lab->daya_berkecambah_persen, 2, ',', '.'), '0'), ',').'%'
                : '-',
            'qr_url' => $label?->qrLabelUrl(),
        ];
    }

    public static function labelPayload(StockPackaging $packaging): array
    {
        $packaging->loadMissing([
            'stock.plant.type',
            'stock.postHarvest.planting.field.plantingLocation',
            'stock.certificationReport',
            'label',
        ]);
        $stock = $packaging->stock;
        $lab = $stock?->postHarvest;
        $location = $lab?->planting?->field?->plantingLocation;
        $plant = $stock?->plant;
        $label = $packaging->label ?: $stock?->certificationReport;
        $situs = WebsiteSetting::current();
        $unit = $plant?->satuanStok?->code ?: '';

        return [
            'produsen' => $location?->name ?: ($situs->office_name ?: 'UPTD BBI TPH Sumatera Barat'),
            'alamat' => $location?->location_summary ?: ($situs->address ?: '-'),
            'no_induk' => $stock?->nomor_induk ?: ($lab?->nomor_induk ?: '-'),
            'nomor_lot' => $label?->nomor_lot ?: ($lab?->nomor_lot ?: '-'),
            'no_seri' => $packaging->no_label_seri ?: '-',
            'varietas' => $plant?->variety ?: ($plant?->name ?: '-'),
            'kelas_benih' => $label?->warnaLabel() ?: ($lab?->planting?->target_kelas ?: '-'),
            'isi_kemasan' => number_format((float) $packaging->kapasitas_per_kemasan, 2).($unit ? ' '.$unit : ''),
            'jenis_tanaman' => $plant?->type?->name ?: ($plant?->name ?: '-'),
            'tgl_panen' => optional($lab?->tgl_panen)->translatedFormat('d F Y') ?: '-',
            'tgl_selesai' => optional($lab?->tgl_selesai_uji)->translatedFormat('d F Y') ?: '-',
            'tgl_berakhir' => optional($lab?->tgl_kadaluarsa_mutu ?: $stock?->tgl_kedaluwarsa)->translatedFormat('d F Y') ?: '-',
            'daya_berkecambah' => $lab?->daya_berkecambah_persen !== null
                ? rtrim(rtrim(number_format((float) $lab->daya_berkecambah_persen, 2, ',', '.'), '0'), ',').'%'
                : '-',
            'kadar_air' => self::percent($lab?->kadar_air_persen),
            'kemurnian_benih' => self::percent($lab?->benih_murni_persen),
            'cvl' => $lab?->catatan_kesehatan_penyakit ?: '-',
            'biji_gulma' => self::percent($lab?->benih_tanaman_lain_persen),
            'kotoran' => self::percent($lab?->kotoran_benih_persen),
            'qr_url' => $label?->qrLabelUrl(),
            'qr_token' => $packaging->qr_code_token,
        ];
    }

    public static function labTestsForInduk(?string $nomorInduk)
    {
        if (! $nomorInduk) {
            return collect();
        }

        return PlantingPostHarvest::where('nomor_induk', $nomorInduk)
            ->with('creator')
            ->orderBy('uji_ke')
            ->get();
    }

    protected static function percent($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',').'%';
    }
}
