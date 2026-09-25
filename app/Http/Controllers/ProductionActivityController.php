<?php

namespace App\Http\Controllers;

use App\Models\CertificationApplication;
use App\Models\CertificationFieldSample;
use App\Models\CertificationHarvest;
use App\Models\CertificationInspectionField;
use App\Models\CertificationPcb;
use App\Models\Planting;
use App\Models\PlantingAssignment;
use App\Models\PlantingLocation;
use App\Models\CertificationReport;
use App\Models\PlantingPostHarvest;
use App\Models\PlantingReport;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionActivityController extends Controller
{
    /**
     * Tahapan sertifikasi yang bisa dicatat selama masa produksi.
     */
    public const STAGES = [
        'application' => [
            'label' => 'Pengajuan Permohonan Sertifikasi',
            'model' => CertificationApplication::class,
            'form' => 'application',
        ],
        'inspection' => [
            'label' => 'Pemeriksaan Lapangan (Inspeksi Pertanaman)',
            'model' => CertificationInspectionField::class,
            'form' => 'inspection',
        ],
        'field_sample' => [
            'label' => 'Detail Titik Sampel Inspeksi Lapangan',
            'model' => CertificationFieldSample::class,
            'form' => 'field-sample',
        ],
        'harvest' => [
            'label' => 'Pengawasan Panen dan Pemeriksaan Alat',
            'model' => CertificationHarvest::class,
            'form' => 'harvest',
        ],
        'pcb' => [
            'label' => 'Pengolahan Benih & Pengambilan Contoh Benih (PCB)',
            'model' => CertificationPcb::class,
            'form' => 'pcb',
        ],
    ];

    public function show(PlantingLocation $plantingLocation, Planting $planting, Request $request)
    {
        $this->authorizeProduction($plantingLocation, $planting);
        $planting->load(['seedSource.variety.type', 'field', 'plant']);
        $tab = $request->get('tab', 'progress');
        $readonly = $request->boolean('readonly');

        $source = $planting->recertSourceId() ? Planting::find($planting->recertSourceId()) : null;
        $historyPlanting = $source ?: $planting;
        $reports = $historyPlanting->reports()->with('officer')->orderBy('activity_date')->orderBy('created_at')->get();
        $notes = $historyPlanting->assignments()->with(['creator', 'assignee', 'completer'])->orderBy('attachment_date')->orderBy('created_at')->get();
        $postHarvests = $historyPlanting->postHarvests()->with('creator')->orderBy('uji_ke')->orderBy('created_at')->get();
        if ($source) {
            $postHarvests = $postHarvests->concat(
                $planting->postHarvests()->with('creator')->orderBy('uji_ke')->orderBy('created_at')->get()
            )->values();
        }
        $labels = \App\Models\CertificationReport::whereIn('planting_post_harvest_id', $postHarvests->pluck('planting_post_harvest_id'))
            ->with(['creator', 'packagings'])
            ->orderBy('created_at')
            ->get();
        $certifications = $this->certificationRecords($historyPlanting);

        $timeline = collect()
            ->concat($reports->map(fn ($item) => [
                'at' => $item->activity_date ?? $item->created_at,
                'type' => 'Log harian',
                'title' => $item->title ?: 'Laporan harian',
                'actor' => $item->officer?->name,
                'item' => $item,
                'kind' => 'daily',
                'report_planting' => $historyPlanting,
            ]))
            ->concat($certifications->map(fn (array $item) => [
                'at' => $item['date'],
                'type' => 'Sertifikasi',
                'title' => $item['title'],
                'actor' => $item['supervisor'],
                'item' => $item['record'],
                'kind' => 'certification',
                'stage' => $item['stage'],
                'report_planting' => $historyPlanting,
            ]))
            ->concat($postHarvests->map(fn ($item) => [
                'at' => $item->tgl_selesai_uji ?? $item->created_at,
                'type' => 'Pasca panen',
                'title' => 'Hasil uji lab'.($item->uji_ke > 1 ? ' (uji ke-'.$item->uji_ke.')' : ''),
                'actor' => $item->creator?->name,
                'item' => $item,
                'kind' => 'post_harvest',
                'report_planting' => $item->planting_id === $planting->getKey() ? $planting : $historyPlanting,
            ]))
            ->concat($labels->map(fn ($item) => [
                'at' => $item->tgl_pemasangan_label ?? $item->created_at,
                'type' => 'Pasca panen',
                'title' => 'Pelabelan Sertifikat Benih',
                'actor' => $item->creator?->name,
                'item' => $item,
                'kind' => 'label',
                'report_planting' => $item->postHarvest?->planting_id === $planting->getKey() ? $planting : $historyPlanting,
            ]))
            ->sortByDesc('at')
            ->values();

        $users = \App\Models\User::orderBy('name')->get();

        $historyDescription = $source
            ? ($source->description ?: '-')
            : ($planting->description ?: '-');

        return view('planting.planting-locations.productions.show', compact(
            'plantingLocation',
            'planting',
            'tab',
            'reports',
            'notes',
            'postHarvests',
            'labels',
            'certifications',
            'timeline',
            'users',
            'readonly',
            'historyPlanting',
            'historyDescription'
        ));
    }

    /**
     * Gabungan seluruh laporan sertifikasi milik satu produksi, terurut terbaru dulu.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    protected function certificationRecords(Planting $planting)
    {
        return collect(self::STAGES)
            ->flatMap(function (array $meta, string $stage) use ($planting) {
                /** @var class-string<Model> $class */
                $class = $meta['model'];

                return $class::where('planting_id', $planting->getKey())
                    ->with('creator')
                    ->get()
                    ->map(fn ($record) => [
                        'stage' => $stage,
                        'stage_label' => $meta['label'],
                        'title' => $record->reportTitle(),
                        'date' => $record->reportDate(),
                        'supervisor' => $record->supervisorName(),
                        'status' => $record->reportStatus(),
                        'record' => $record,
                    ]);
            })
            ->sortByDesc(fn (array $item) => $item['record']->created_at)
            ->values();
    }

    public function createDailyLog(PlantingLocation $plantingLocation, Planting $planting)
    {
        $this->authorizeProduction($plantingLocation, $planting);
        $planting->load(['field', 'seedSource.variety']);

        return view('planting.planting-locations.productions.daily-create', compact('plantingLocation', 'planting'));
    }

    public function storeDailyLog(Request $request, PlantingLocation $plantingLocation, Planting $planting)
    {
        $this->authorizeProduction($plantingLocation, $planting);
        $this->ensureCanAddReports($planting);

        $activities = implode(',', array_keys(PlantingReport::ACTIVITIES));
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'growth_phase' => 'required|in:persemaian,vegetatif,berbunga,masak',
            'activity_type' => 'required|in:'.$activities,
            'activity_type_custom' => 'required_if:activity_type,lainnya|nullable|string|max:150',
            'description' => 'required|string',
            'product_detail' => 'required_if:activity_type,pemupukan,opt|nullable|string|max:255',
            'applied_amount' => 'required_if:activity_type,pemupukan,opt|nullable|string|max:100',
            'application_method' => 'required_if:activity_type,pemupukan,opt|nullable|string|max:150',
            'plants_removed' => 'required_if:activity_type,rouging|nullable|numeric|min:0',
            'characteristic' => 'required_if:activity_type,rouging|nullable|string|max:255',
            'external_technician' => 'nullable|string|max:150',
            'activity_date' => 'required|date',
            'file' => 'nullable|file|max:10240',
        ], [
            'description.required' => 'Isi laporan harian wajib diisi.',
            'activity_type_custom.required_if' => 'Sebutkan jenis kegiatan lainnya.',
            'product_detail.required_if' => 'Detail produk yang digunakan wajib diisi.',
            'applied_amount.required_if' => 'Jumlah yang diterapkan wajib diisi.',
            'application_method.required_if' => 'Metode menerapkan produk wajib diisi.',
            'plants_removed.required_if' => 'Jumlah tanaman yang dicabut wajib diisi.',
            'characteristic.required_if' => 'Karakteristik wajib diisi.',
        ]);

        $withProduct = in_array($data['activity_type'], PlantingReport::ACTIVITIES_WITH_PRODUCT, true);
        $withRouging = in_array($data['activity_type'], PlantingReport::ACTIVITIES_WITH_ROUGING, true);

        $file = $request->file('file');
        PlantingReport::create([
            'title' => $data['title'],
            'planting_id' => $planting->getKey(),
            'growth_phase' => $data['growth_phase'],
            'activity_type' => $data['activity_type'],
            'activity_type_custom' => $data['activity_type'] === 'lainnya' ? $data['activity_type_custom'] : null,
            'product_detail' => $withProduct ? $data['product_detail'] : null,
            'applied_amount' => $withProduct ? $data['applied_amount'] : null,
            'application_method' => $withProduct ? $data['application_method'] : null,
            'plants_removed' => $withRouging ? $data['plants_removed'] : null,
            'characteristic' => $withRouging ? $data['characteristic'] : null,
            'external_technician' => $data['external_technician'] ?? null,
            'description' => $data['description'],
            'officer_id' => auth()->user()->user_id,
            'created_by' => auth()->user()->user_id,
            'activity_date' => $data['activity_date'],
            'due_date' => $data['activity_date'],
            'file_path' => $file ? $file->store('planting-reports', 'public') : null,
            'file_name' => $file?->getClientOriginalName(),
        ]);

        return redirect()->route('planting-locations.plantings.reports', [$plantingLocation, $planting, 'tab' => 'daily'])
            ->with('success', 'Laporan harian berhasil ditambahkan');
    }

    public function createCertification(Request $request, PlantingLocation $plantingLocation, Planting $planting)
    {
        $this->authorizeProduction($plantingLocation, $planting);
        $this->ensureCanAddReports($planting);
        $planting->load(['field.plantingLocation', 'seedSource.variety.type']);

        $stage = $request->get('stage');
        if ($stage && ! array_key_exists($stage, self::STAGES)) {
            $stage = null;
        }

        $prefill = $this->certificationPrefill($planting);
        $nextSampleNumber = (int) CertificationFieldSample::where('planting_id', $planting->getKey())->max('nomor_titik_sampel') + 1;

        return view('planting.planting-locations.productions.certification-create', compact(
            'plantingLocation',
            'planting',
            'stage',
            'prefill',
            'nextSampleNumber'
        ));
    }

    /**
     * Data produksi yang bisa mengisi form sertifikasi secara otomatis.
     *
     * @return array<string, mixed>
     */
    protected function certificationPrefill(Planting $planting): array
    {
        $seedSource = $planting->seedSource;
        $field = $planting->field;
        $variety = $seedSource?->variety;

        return [
            'komoditas' => $this->guessKomoditas($variety?->type?->name),
            'nama_varietas' => $variety?->variety ?: $variety?->name,
            'kelas_benih_tujuan' => $planting->target_kelas,
            'luas_lahan_ha' => $field?->luas_ha,
            'koordinat_gps_lahan' => $field?->koordinat_gps ?: $field?->plantingLocation?->koordinat_gps,
            'no_label_benih_sumber' => $seedSource?->origin_lot_number,
            'kelas_benih_sumber' => $seedSource?->seed_class,
            'produsen_asal_benih_sumber' => $seedSource?->origin_producer,
            'jumlah_benih_sumber_kg' => $seedSource?->quantity_kg ?: $planting->planting_amount,
            'rencana_tgl_sebar' => $planting->planted_at?->format('Y-m-d'),
            'rencana_tgl_tanam' => $planting->planted_at?->format('Y-m-d'),
        ];
    }

    protected function guessKomoditas(?string $typeName): string
    {
        $name = strtolower((string) $typeName);
        return match (true) {
            str_contains($name, 'sayur') => 'Hortikultura Sayur',
            str_contains($name, 'hias') => 'Hortikultura Hias',
            str_contains($name, 'kebun') => 'Perkebunan',
            default => 'Pangan',
        };
    }

    public function storeCertification(Request $request, PlantingLocation $plantingLocation, Planting $planting)
    {
        $this->authorizeProduction($plantingLocation, $planting);
        $this->ensureCanAddReports($planting);

        $stage = $request->input('stage');
        abort_unless(array_key_exists($stage, self::STAGES), 422, 'Tahapan sertifikasi tidak dikenal.');

        $record = match ($stage) {
            'application' => $this->storeApplication($request, $planting),
            'inspection' => $this->storeInspection($request, $planting),
            'field_sample' => $this->storeFieldSample($request, $planting),
            'harvest' => $this->storeCertificationHarvest($request, $planting),
            'pcb' => $this->storePcb($request, $planting),
        };

        $planting->refresh()->syncProductionStatus();

        return redirect()->route('planting-locations.plantings.reports', [$plantingLocation, $planting, 'tab' => 'certification'])
            ->with('success', 'Laporan '.self::STAGES[$stage]['label'].' berhasil disimpan.');
    }

    protected function storeApplication(Request $request, Planting $planting): CertificationApplication
    {
        $data = $request->validate([
            'komoditas' => 'required|in:'.implode(',', CertificationApplication::KOMODITAS),
            'nama_varietas' => 'required|string|max:100',
            'kelas_benih_tujuan' => 'required|string|max:50',
            'luas_lahan_ha' => 'required|numeric|min:0',
            'koordinat_gps_lahan' => 'nullable|string|max:100',
            'file_peta_sketsa' => 'nullable|file|max:10240',
            'no_label_benih_sumber' => 'required|string|max:100',
            'kelas_benih_sumber' => 'required|string|max:20',
            'produsen_asal_benih_sumber' => 'required|string|max:150',
            'jumlah_benih_sumber_kg' => 'required|numeric|min:0',
            'rencana_tgl_sebar' => 'required|date',
            'rencana_tgl_tanam' => 'required|date',
            'sejarah_lahan_sebelumnya' => 'nullable|string',
            'status_pengajuan' => 'required|in:'.implode(',', CertificationApplication::STATUS_PENGAJUAN),
            'lampiran_formulir' => 'nullable|file|max:10240',
        ]);

        return CertificationApplication::create(array_merge($data, [
            'planting_id' => $planting->getKey(),
            'file_peta_sketsa' => $this->storeUpload($request, 'file_peta_sketsa'),
            'lampiran_formulir' => $this->storeUpload($request, 'lampiran_formulir'),
            'created_by' => auth()->user()->user_id,
        ]));
    }

    protected function storeInspection(Request $request, Planting $planting): CertificationInspectionField
    {
        $data = $request->validate([
            'fase_inspeksi' => 'required|in:'.implode(',', CertificationInspectionField::FASE),
            'tgl_inspeksi' => 'required|date',
            'umur_tanaman_hst' => 'required|integer|min:0',
            'nama_pbt_pemeriksa' => 'required|string|max:100',
            'jarak_isolasi_meter' => 'nullable|integer|min:0',
            'status_isolasi_waktu' => 'nullable|boolean',
            'kesesuaian_dokumen_sumber' => 'nullable|boolean',
            'jenis_opt_dominan' => 'nullable|string|max:100',
            'tingkat_serangan_opt' => 'required|in:'.implode(',', CertificationInspectionField::TINGKAT_OPT),
            'status_roguing' => 'required|in:'.implode(',', CertificationInspectionField::STATUS_ROUGING),
            'status_kelulusan_fase' => 'required|in:'.implode(',', CertificationInspectionField::STATUS_KELULUSAN),
            'lampiran_formulir' => 'nullable|file|max:10240',
        ]);

        $isPendahuluan = $data['fase_inspeksi'] === 'Pendahuluan';
        $totals = $this->fieldSampleTotals($planting);

        return CertificationInspectionField::create(array_merge($data, [
            'planting_id' => $planting->getKey(),
            'jarak_isolasi_meter' => $isPendahuluan ? ($data['jarak_isolasi_meter'] ?? null) : null,
            'status_isolasi_waktu' => $isPendahuluan ? ($data['status_isolasi_waktu'] ?? null) : null,
            'kesesuaian_dokumen_sumber' => $isPendahuluan ? ($data['kesesuaian_dokumen_sumber'] ?? null) : null,
            'total_tanaman_sampel' => $isPendahuluan ? 0 : $totals['plants'],
            'total_cvl_ditemukan' => $isPendahuluan ? 0 : $totals['cvl'],
            'persentase_cvl_akhir' => $isPendahuluan ? 0 : $totals['percentage'],
            'lampiran_formulir' => $this->storeUpload($request, 'lampiran_formulir'),
            'created_by' => auth()->user()->user_id,
        ]));
    }

    protected function storeFieldSample(Request $request, Planting $planting): CertificationFieldSample
    {
        $data = $request->validate([
            'nomor_titik_sampel' => 'required|integer|min:1',
            'jumlah_tanaman_diperiksa' => 'required|integer|min:1',
            'jumlah_cvl_ditemukan' => 'required|integer|min:0',
            'lampiran_formulir' => 'nullable|file|max:10240',
        ]);

        $sample = CertificationFieldSample::create(array_merge($data, [
            'planting_id' => $planting->getKey(),
            'lampiran_formulir' => $this->storeUpload($request, 'lampiran_formulir'),
            'created_by' => auth()->user()->user_id,
        ]));

        $this->refreshInspectionTotals($planting);

        return $sample;
    }

    /**
     * Rekap kolektif titik sampel untuk mengisi hasil otomatis pada inspeksi.
     *
     * @return array{plants: int, cvl: int, percentage: float}
     */
    protected function fieldSampleTotals(Planting $planting): array
    {
        $plants = (int) CertificationFieldSample::where('planting_id', $planting->getKey())->sum('jumlah_tanaman_diperiksa');
        $cvl = (int) CertificationFieldSample::where('planting_id', $planting->getKey())->sum('jumlah_cvl_ditemukan');

        return [
            'plants' => $plants,
            'cvl' => $cvl,
            'percentage' => $plants > 0 ? round($cvl / $plants * 100, 2) : 0.0,
        ];
    }

    protected function refreshInspectionTotals(Planting $planting): void
    {
        $totals = $this->fieldSampleTotals($planting);
        CertificationInspectionField::where('planting_id', $planting->getKey())
            ->where('fase_inspeksi', '!=', 'Pendahuluan')
            ->update([
                'total_tanaman_sampel' => $totals['plants'],
                'total_cvl_ditemukan' => $totals['cvl'],
                'persentase_cvl_akhir' => $totals['percentage'],
            ]);
    }

    protected function storeCertificationHarvest(Request $request, Planting $planting): CertificationHarvest
    {
        $data = $request->validate([
            'tgl_panen' => 'required|date',
            'volume_kotor_panen_kg' => 'required|numeric|min:0',
            'status_kebersihan_alat_panen' => 'required|boolean',
            'status_kebersihan_wadah' => 'required|boolean',
            'no_segel_sementara' => 'nullable|string|max:100',
            'nama_pengawas_pbt' => 'nullable|string|max:100',
            'lampiran_formulir' => 'nullable|file|max:10240',
        ]);

        return CertificationHarvest::create(array_merge($data, [
            'planting_id' => $planting->getKey(),
            'lampiran_formulir' => $this->storeUpload($request, 'lampiran_formulir'),
            'created_by' => auth()->user()->user_id,
        ]));
    }

    protected function storePcb(Request $request, Planting $planting): CertificationPcb
    {
        $data = $request->validate([
            'no_berita_acara_pcb' => 'nullable|string|max:100',
            'no_segel_sampel_lab' => 'nullable|string|max:100',
            'berat_sampel_kirim_gram' => 'nullable|integer|min:0',
            'status_posisi_lot' => 'required|in:'.implode(',', CertificationPcb::STATUS_POSISI_LOT),
            'lampiran_formulir' => 'nullable|file|max:10240',
        ]);

        return CertificationPcb::create(array_merge($data, [
            'planting_id' => $planting->getKey(),
            'lampiran_formulir' => $this->storeUpload($request, 'lampiran_formulir'),
            'created_by' => auth()->user()->user_id,
        ]));
    }

    public function showCertification(PlantingLocation $plantingLocation, Planting $planting, string $stage, int $record)
    {
        $this->authorizeProduction($plantingLocation, $planting);
        abort_unless(array_key_exists($stage, self::STAGES), 404);

        /** @var class-string<Model> $class */
        $class = self::STAGES[$stage]['model'];
        $item = $class::with('creator')->where('planting_id', $planting->getKey())->findOrFail($record);
        $stageLabel = self::STAGES[$stage]['label'];

        return view('planting.planting-locations.productions.certification-show', compact(
            'plantingLocation',
            'planting',
            'stage',
            'stageLabel',
            'item'
        ));
    }

    public function createLabResult(PlantingLocation $plantingLocation, Planting $planting, Request $request)
    {
        $this->authorizeProduction($plantingLocation, $planting);
        $planting->load(['field', 'seedSource.variety']);

        $previous = $planting->latestPostHarvest();
        if (! $previous && $planting->recertSourceId()) {
            $previous = Planting::find($planting->recertSourceId())?->latestPostHarvest();
        }
        $ujiKe = $previous ? (int) $previous->uji_ke + 1 : 1;
        $nomorInduk = $previous?->nomor_induk ?: $this->buildNomorInduk($planting);
        $alasanUjiUlang = $request->get('alasan_uji_ulang');

        $lastHarvest = $planting->certificationHarvests()->orderByDesc('tgl_panen')->first();
        $prefill = [
            'uji_ke' => $ujiKe,
            'nomor_induk' => $nomorInduk,
            'nomor_lot' => PlantingPostHarvest::generateLotNumber($nomorInduk, $ujiKe),
            'tgl_panen' => $lastHarvest?->tgl_panen?->format('Y-m-d') ?: $previous?->tgl_panen?->format('Y-m-d'),
            'realisasi_produksi' => $lastHarvest?->volume_kotor_panen_kg ?? $previous?->realisasi_produksi,
            'previous_bpsb' => $previous?->no_sertifikat_lab_bpsb,
        ];
        if ($alasanUjiUlang && $previous) {
            $prefill['nomor_induk'] = $previous->nomor_induk;
            $prefill['nomor_lot'] = $previous->nomor_lot;
            $prefill['tgl_panen'] = $previous->tgl_panen?->format('Y-m-d');
            $prefill['realisasi_produksi'] = $previous->realisasi_produksi;
            if ($request->filled('volume_uji_ulang')) {
                $prefill['total_hasil_uji'] = $request->volume_uji_ulang;
            }
        }

        return view('planting.planting-locations.productions.lab-result-create', compact(
            'plantingLocation',
            'planting',
            'prefill',
            'previous',
            'alasanUjiUlang'
        ));
    }

    protected function buildNomorInduk(Planting $planting): string
    {
        $base = $planting->planting_batch_number ?: 'IND-'.date('Y');

        return strtoupper(preg_replace('/\s+/', '-', $base));
    }

    public function storeLabResult(Request $request, PlantingLocation $plantingLocation, Planting $planting)
    {
        $this->authorizeProduction($plantingLocation, $planting);

        $data = $request->validate([
            'no_sertifikat_lab_bpsb' => 'required|string|max:100|unique:planting_post_harvest,no_sertifikat_lab_bpsb',
            'uji_ke' => 'required|integer|min:1',
            'tgl_panen' => 'required|date',
            'tgl_aju' => 'required|date',
            'tgl_uji' => 'required|date',
            'tgl_selesai_uji' => 'required|date',
            'realisasi_produksi' => 'required|numeric|min:0',
            'nomor_induk' => 'required|string|max:100',
            'nomor_lot' => 'required|string|max:100',
            'kadar_air_persen' => 'required|numeric|min:0|max:99.99',
            'benih_murni_persen' => 'required|numeric|min:0|max:100',
            'kotoran_benih_persen' => 'required|numeric|min:0|max:99.99',
            'benih_tanaman_lain_persen' => 'required|numeric|min:0|max:99.99',
            'daya_berkecambah_persen' => 'required|integer|min:0|max:100',
            'catatan_kesehatan_penyakit' => 'nullable|string',
            'tgl_kadaluarsa_mutu' => 'required|date',
            'total_hasil_uji' => 'required|numeric|min:0',
            'status_kelulusan_lab' => 'required|in:LULUS,TIDAK LULUS',
            'lampiran_hasil_lab' => 'nullable|file|max:10240',
            'alasan_uji_ulang' => 'nullable|string',
        ]);

        $previous = $planting->latestPostHarvest();
        if (! $previous && $planting->recertSourceId()) {
            $previous = Planting::find($planting->recertSourceId())?->latestPostHarvest();
        }
        if ($request->filled('alasan_uji_ulang') && $previous) {
            $data['nomor_induk'] = $previous->nomor_induk;
            $data['nomor_lot'] = $previous->nomor_lot;
            $data['tgl_panen'] = optional($previous->tgl_panen)->format('Y-m-d');
            $data['realisasi_produksi'] = $previous->realisasi_produksi;
            if (strcasecmp(trim((string) $data['no_sertifikat_lab_bpsb']), trim((string) $previous->no_sertifikat_lab_bpsb)) === 0) {
                return back()->withErrors([
                    'no_sertifikat_lab_bpsb' => 'Nomor sertifikat BPSB sertifikasi ulang harus berbeda dari nomor sebelumnya ('.$previous->no_sertifikat_lab_bpsb.').',
                ])->withInput();
            }
        }

        DB::transaction(function () use ($data, $request, $planting) {
            $postHarvest = PlantingPostHarvest::create(array_merge($data, [
                'planting_id' => $planting->getKey(),
                'lampiran_hasil_lab' => $this->storeUpload($request, 'lampiran_hasil_lab'),
                'created_by' => auth()->user()->user_id,
            ]));

            if ($postHarvest->isLulus()) {
                $this->createStockLot($planting, $postHarvest);
            }
        });

        $planting->refresh();
        $latest = $planting->latestPostHarvest();
        if ($latest && ! $latest->isLulus()) {
            $planting->update([
                'is_completed' => true,
                'completed_at' => $planting->completed_at ?: ($data['tgl_selesai_uji'] ?? now()),
            ]);
            $planting->syncProductionStatus();

            return redirect()->route('planting-locations.planting-history', $plantingLocation)
                ->with('success', 'Hasil uji lab benih disimpan. Produksi tidak lulus dan dipindah ke riwayat.');
        }

        $planting->syncProductionStatus();

        return redirect()->route('planting-locations.plantings.index', $plantingLocation)
            ->with('success', 'Hasil uji lab benih berhasil disimpan. Lanjutkan dengan menambahkan label benih.');
    }

    /**
     * Setiap hasil uji lab yang lulus otomatis membuka satu lot stok benih baru
     * yang menunggu pelabelan.
     */
    protected function createStockLot(Planting $planting, PlantingPostHarvest $postHarvest): void
    {
        $label = $postHarvest->nomor_lot;
        $suffix = 1;
        while (Stock::where('no_label_resmi', $label)->exists()) {
            $label = $postHarvest->nomor_lot.'-'.(++$suffix);
        }

        Stock::create([
            'no_label_resmi' => $label,
            'nomor_induk' => $postHarvest->nomor_induk,
            'uji_ke' => $postHarvest->uji_ke,
            'qr_code_token' => Stock::generateToken(),
            'stok_awal' => $postHarvest->total_hasil_uji,
            'stok_saat_ini' => $postHarvest->total_hasil_uji,
            'tgl_kedaluwarsa' => $postHarvest->tgl_kadaluarsa_mutu,
            'status_stok' => Stock::STATUS_PELABELAN,
            'seed_varieties_id' => $planting->seedSource?->seed_varieties_id,
            'planting_post_harvest_id' => $postHarvest->getKey(),
            'updated_by' => auth()->user()->user_id,
        ]);
    }

    public function showLabResult(PlantingLocation $plantingLocation, Planting $planting, PlantingPostHarvest $postHarvest)
    {
        $this->authorizeProduction($plantingLocation, $planting);
        abort_unless($postHarvest->planting_id === $planting->getKey(), 404);
        $postHarvest->load('creator');

        return view('planting.planting-locations.productions.lab-result-show', compact(
            'plantingLocation',
            'planting',
            'postHarvest'
        ));
    }

    public function showLabel(PlantingLocation $plantingLocation, Planting $planting, CertificationReport $report)
    {
        $this->authorizeProduction($plantingLocation, $planting);
        $planting->load(['plant.satuanStok', 'satuanPanen']);
        $report->load(['postHarvest', 'packagings.rack.warehouse', 'packagings.stock', 'creator']);
        $report->setRelation('packagings', \App\Models\StockPackaging::sorted($report->packagings));
        abort_unless($report->postHarvest?->planting_id === $planting->getKey(), 404);

        return view('planting.planting-locations.productions.label-show', compact(
            'plantingLocation',
            'planting',
            'report'
        ));
    }

    public function storeNote(Request $request, PlantingLocation $plantingLocation, Planting $planting)
    {
        $this->authorizeProduction($plantingLocation, $planting);
        $this->ensureCanAddReports($planting);
        $data = $request->validate([
            'task_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'intended_for' => 'required|exists:users,user_id',
            'file' => 'nullable|file|max:10240',
        ]);
        $file = $request->file('file');
        PlantingAssignment::create([
            'planting_id' => $planting->getKey(),
            'planting_location_id' => $plantingLocation->planting_location_id,
            'task_title' => $data['task_title'],
            'description' => $data['description'] ?? null,
            'attachment_date' => now()->toDateString(),
            'created_by' => auth()->user()->user_id,
            'intended_for' => $data['intended_for'],
            'status' => PlantingAssignment::STATUS_ASSIGNED,
            'file_path' => $file ? $file->store('planting-assignments', 'public') : null,
            'file_name' => $file?->getClientOriginalName(),
            'file_size' => $file?->getSize(),
            'mime_type' => $file?->getMimeType(),
        ]);

        return back()->with('success', 'Penugasan berhasil ditambahkan');
    }

    public function completeAssignment(Request $request, PlantingLocation $plantingLocation, Planting $planting, PlantingAssignment $assignment)
    {
        $this->authorizeProduction($plantingLocation, $planting);
        if ($assignment->planting_id !== $planting->getKey()) {
            abort(404);
        }
        if (! $assignment->isAssignedTo(auth()->user()) && ! auth()->user()->isAdmin()) {
            abort(403, 'Hanya user yang ditugaskan yang dapat menyelesaikan tugas ini.');
        }
        $data = $request->validate([
            'completion_note' => 'nullable|string',
        ]);
        $assignment->update([
            'status' => PlantingAssignment::STATUS_DONE,
            'completed_at' => now(),
            'completed_by' => auth()->user()->user_id,
            'completion_note' => $data['completion_note'] ?? null,
        ]);

        return back()->with('success', 'Tugas ditandai selesai.');
    }

    public function complete(Request $request, PlantingLocation $plantingLocation, Planting $planting)
    {
        $this->authorizeProduction($plantingLocation, $planting);
        $data = $request->validate([
            'completed_at' => 'required|date|after_or_equal:'.$planting->planted_at?->format('Y-m-d'),
        ], [
            'completed_at.required' => 'Tanggal selesai produksi wajib diisi.',
        ]);

        $planting->update([
            'is_completed' => true,
            'completed_at' => $data['completed_at'],
        ]);

        return redirect()->route('planting-locations.plantings.index', $plantingLocation)
            ->with('success', 'Produksi penanaman ditandai selesai');
    }

    public function historyReports(PlantingLocation $plantingLocation, Planting $planting)
    {
        return redirect()->route('planting-locations.plantings.reports', [
            $plantingLocation,
            $planting,
            'tab' => 'progress',
            'readonly' => 1,
        ]);
    }

    protected function storeUpload(Request $request, string $field): ?string
    {
        return $request->hasFile($field)
            ? $request->file($field)->store('certifications', 'public')
            : null;
    }

    protected function ensureCanAddReports(Planting $planting): void
    {
        if (! $planting->canAddReports()) {
            abort(403, 'Produksi sudah ditandai selesai sehingga laporan tidak dapat ditambahkan.');
        }
    }

    protected function authorizeProduction(PlantingLocation $plantingLocation, Planting $planting): void
    {
        $planting->loadMissing('field');
        $user = auth()->user();
        if (! $user->isAssignedToPlantingLocation($plantingLocation)) {
            abort(403, 'Anda tidak memiliki akses ke lokasi penanaman ini.');
        }
        if ($planting->field?->planting_location_id !== $plantingLocation->planting_location_id) {
            abort(404, 'Produksi tidak ditemukan di lokasi ini.');
        }
    }
}
