<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        $this->dropLegacyProductionTables();
        $this->createCertificationStageTables();
        $this->rebuildPostHarvestAsLabResult();
        $this->rebuildCertificationReportsAsLabel();
        $this->adjustPlantingProduction();
        $this->adjustPlantingReport();
        $this->adjustStockTables();
        $this->adjustWarehouses();

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('certification_pcb');
        Schema::dropIfExists('certification_harvest');
        Schema::dropIfExists('certification_field_sample');
        Schema::dropIfExists('certification_inspection_field');
        Schema::dropIfExists('certification_application');
    }

    protected function dropLegacyProductionTables(): void
    {
        foreach (['planting_rouging', 'planting_inspection', 'planting_harvest'] as $table) {
            Schema::dropIfExists($table);
        }
    }

    protected function createCertificationStageTables(): void
    {
        if (! Schema::hasTable('certification_application')) {
            Schema::create('certification_application', function (Blueprint $table) {
                $table->id();
                $table->string('planting_id', 36)->index();
                $table->enum('komoditas', ['Pangan', 'Hortikultura Sayur', 'Hortikultura Hias', 'Perkebunan']);
                $table->string('nama_varietas', 100);
                $table->string('kelas_benih_tujuan', 50);
                $table->decimal('luas_lahan_ha', 7, 2);
                $table->string('koordinat_gps_lahan', 100)->nullable();
                $table->string('file_peta_sketsa', 255)->nullable();
                $table->string('no_label_benih_sumber', 100);
                $table->string('kelas_benih_sumber', 20);
                $table->string('produsen_asal_benih_sumber', 150);
                $table->decimal('jumlah_benih_sumber_kg', 10, 2);
                $table->date('rencana_tgl_sebar');
                $table->date('rencana_tgl_tanam');
                $table->text('sejarah_lahan_sebelumnya')->nullable();
                $table->enum('status_pengajuan', ['Draft', 'Ditinjau', 'Disetujui', 'Ditolak'])->default('Ditinjau');
                $table->string('lampiran_formulir', 255)->nullable();
                $table->string('created_by', 36)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('certification_inspection_field')) {
            Schema::create('certification_inspection_field', function (Blueprint $table) {
                $table->id();
                $table->string('planting_id', 36)->index();
                $table->enum('fase_inspeksi', ['Pendahuluan', 'Vegetatif', 'Berbunga', 'Masak']);
                $table->date('tgl_inspeksi');
                $table->integer('umur_tanaman_hst');
                $table->string('nama_pbt_pemeriksa', 100);
                $table->integer('jarak_isolasi_meter')->nullable();
                $table->boolean('status_isolasi_waktu')->nullable();
                $table->boolean('kesesuaian_dokumen_sumber')->nullable();
                $table->integer('total_tanaman_sampel')->default(0);
                $table->integer('total_cvl_ditemukan')->default(0);
                $table->decimal('persentase_cvl_akhir', 5, 2)->default(0);
                $table->string('jenis_opt_dominan', 100)->nullable();
                $table->enum('tingkat_serangan_opt', ['Aman', 'Ringan', 'Sedang', 'Berat'])->default('Aman');
                $table->enum('status_roguing', ['Belum', 'Sudah Bersih', 'Perlu Diulang'])->default('Belum');
                $table->enum('status_kelulusan_fase', ['LULUS', 'TIDAK LULUS', 'REINSPEKSI'])->default('REINSPEKSI');
                $table->string('lampiran_formulir', 255)->nullable();
                $table->string('created_by', 36)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('certification_field_sample')) {
            Schema::create('certification_field_sample', function (Blueprint $table) {
                $table->id();
                $table->string('planting_id', 36)->index();
                $table->integer('nomor_titik_sampel');
                $table->integer('jumlah_tanaman_diperiksa')->default(100);
                $table->integer('jumlah_cvl_ditemukan')->default(0);
                $table->string('lampiran_formulir', 255)->nullable();
                $table->string('created_by', 36)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('certification_harvest')) {
            Schema::create('certification_harvest', function (Blueprint $table) {
                $table->id();
                $table->string('planting_id', 36)->index();
                $table->date('tgl_panen');
                $table->decimal('volume_kotor_panen_kg', 12, 2);
                $table->boolean('status_kebersihan_alat_panen')->default(true);
                $table->boolean('status_kebersihan_wadah')->default(true);
                $table->string('no_segel_sementara', 100)->nullable();
                $table->string('nama_pengawas_pbt', 100)->nullable();
                $table->string('lampiran_formulir', 255)->nullable();
                $table->string('created_by', 36)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('certification_pcb')) {
            Schema::create('certification_pcb', function (Blueprint $table) {
                $table->id();
                $table->string('planting_id', 36)->index();
                $table->string('no_berita_acara_pcb', 100)->nullable();
                $table->string('no_segel_sampel_lab', 100)->nullable();
                $table->integer('berat_sampel_kirim_gram')->nullable();
                $table->enum('status_posisi_lot', ['Diolah', 'Sampel Di Lab', 'Lulus Lab', 'Gagal'])->default('Diolah');
                $table->string('lampiran_formulir', 255)->nullable();
                $table->string('created_by', 36)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    protected function rebuildPostHarvestAsLabResult(): void
    {
        Schema::dropIfExists('planting_post_harvest');

        Schema::create('planting_post_harvest', function (Blueprint $table) {
            $table->string('planting_post_harvest_id', 36)->primary();
            $table->string('planting_id', 36)->index();
            $table->string('no_sertifikat_lab_bpsb', 100)->unique();
            $table->unsignedInteger('uji_ke')->default(1);
            $table->date('tgl_panen');
            $table->date('tgl_aju');
            $table->date('tgl_uji');
            $table->date('tgl_selesai_uji');
            $table->decimal('realisasi_produksi', 12, 2);
            $table->string('nomor_induk', 100);
            $table->string('nomor_lot', 100);
            $table->decimal('kadar_air_persen', 4, 2);
            $table->decimal('benih_murni_persen', 5, 2);
            $table->decimal('kotoran_benih_persen', 4, 2);
            $table->decimal('benih_tanaman_lain_persen', 4, 2);
            $table->unsignedInteger('daya_berkecambah_persen');
            $table->text('catatan_kesehatan_penyakit')->nullable();
            $table->date('tgl_kadaluarsa_mutu');
            $table->decimal('total_hasil_uji', 12, 2);
            $table->enum('status_kelulusan_lab', ['LULUS', 'TIDAK LULUS']);
            $table->string('lampiran_hasil_lab', 255)->nullable();
            $table->text('alasan_uji_ulang')->nullable();
            $table->string('created_by', 36)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    protected function rebuildCertificationReportsAsLabel(): void
    {
        Schema::dropIfExists('certification_reports');

        Schema::create('certification_reports', function (Blueprint $table) {
            $table->string('certification_report_id', 36)->primary();
            $table->string('id_label_rilis', 50)->unique();
            $table->string('planting_post_harvest_id', 36)->index();
            $table->string('nomor_lot', 50);
            $table->string('no_sertifikat_bpsb_final', 100);
            $table->enum('warna_label', ['Kuning', 'Putih', 'Ungu', 'Biru']);
            $table->enum('tipe_kemasan', ['karung_bulk', 'plastik_ritel', 'pot_satuan']);
            $table->decimal('ukuran_kemasan_retail_kg', 5, 2);
            $table->unsignedInteger('jumlah_lembar_label_dicetak');
            $table->string('no_seri_label_awal', 50);
            $table->string('no_seri_label_akhir', 50)->nullable();
            $table->date('tgl_pemasangan_label')->nullable();
            $table->string('lampiran_qr_label', 255)->nullable();
            $table->string('created_by', 36)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    protected function adjustPlantingProduction(): void
    {
        Schema::table('planting_production', function (Blueprint $table) {
            if (Schema::hasColumn('planting_production', 'status_sertifikasi')) {
                $table->dropColumn('status_sertifikasi');
            }
        });

        DB::statement("ALTER TABLE `planting_production` MODIFY COLUMN `status` VARCHAR(40) NOT NULL DEFAULT 'perencanaan_persiapan'");
        DB::table('planting_production')
            ->whereNotIn('status', ['perencanaan_persiapan', 'penanaman_pemeliharaan', 'panen', 'pasca_panen', 'stok'])
            ->update(['status' => 'perencanaan_persiapan']);
    }

    protected function adjustPlantingReport(): void
    {
        Schema::table('planting_report', function (Blueprint $table) {
            if (! Schema::hasColumn('planting_report', 'activity_type_custom')) {
                $table->string('activity_type_custom', 150)->nullable()->after('activity_type');
            }
            if (! Schema::hasColumn('planting_report', 'application_method')) {
                $table->string('application_method', 150)->nullable()->after('applied_amount');
            }
            if (! Schema::hasColumn('planting_report', 'plants_removed')) {
                $table->decimal('plants_removed', 10, 2)->nullable()->after('application_method');
            }
            if (! Schema::hasColumn('planting_report', 'characteristic')) {
                $table->string('characteristic', 255)->nullable()->after('plants_removed');
            }
        });

        DB::statement("ALTER TABLE `planting_report` MODIFY COLUMN `activity_type` VARCHAR(50) NULL");
        DB::table('planting_report')->where('activity_type', 'semprot_opt')->update(['activity_type' => 'opt']);
    }

    protected function adjustStockTables(): void
    {
        DB::statement("ALTER TABLE `stock` MODIFY COLUMN `status_stok` VARCHAR(30) NOT NULL DEFAULT 'pelabelan'");

        Schema::table('stock', function (Blueprint $table) {
            if (! Schema::hasColumn('stock', 'planting_post_harvest_id')) {
                $table->string('planting_post_harvest_id', 36)->nullable()->index()->after('certification_report_id');
            }
            if (! Schema::hasColumn('stock', 'nomor_induk')) {
                $table->string('nomor_induk', 100)->nullable()->index()->after('no_label_resmi');
            }
            if (! Schema::hasColumn('stock', 'uji_ke')) {
                $table->unsignedInteger('uji_ke')->default(1)->after('nomor_induk');
            }
        });

        DB::statement("ALTER TABLE `stock_packaging` MODIFY COLUMN `status_kemasan` VARCHAR(30) NOT NULL DEFAULT 'tersedia'");

        Schema::table('stock_packaging', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_packaging', 'certification_report_id')) {
                $table->string('certification_report_id', 36)->nullable()->index()->after('stok_benih_id');
            }
            if (! Schema::hasColumn('stock_packaging', 'alasan_penyesuaian')) {
                $table->string('alasan_penyesuaian', 255)->nullable();
            }
            if (! Schema::hasColumn('stock_packaging', 'tgl_penyesuaian')) {
                $table->timestamp('tgl_penyesuaian')->nullable();
            }
        });

        // Data lama tidak lagi kompatibel dengan alur label baru.
        foreach (['sale_items', 'seed_request_items', 'stock_histories'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'stock_packaging_id')) {
                DB::table($table)->update(['stock_packaging_id' => null]);
            }
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'stock_id')) {
                DB::table($table)->update(['stock_id' => null]);
            }
        }

        DB::table('stock_packaging')->delete();
        DB::table('stock')->delete();
    }

    protected function adjustWarehouses(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            if (! Schema::hasColumn('warehouses', 'tipe_lokasi')) {
                $table->string('tipe_lokasi', 20)->default('gudang')->after('internal_id');
            }
        });

        Schema::table('warehouses', function (Blueprint $table) {
            if (Schema::hasColumn('warehouses', 'tracking_type')) {
                $table->dropColumn('tracking_type');
            }
        });
    }
};
