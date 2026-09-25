<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropForeignsReferencing(['warehouse_lots']);

        if (Schema::hasTable('sale_items') && Schema::hasColumn('sale_items', 'warehouse_lot_id')) {
            Schema::table('sale_items', function (Blueprint $table) {
                if (! Schema::hasColumn('sale_items', 'stock_packaging_id')) {
                    $table->unsignedBigInteger('stock_packaging_id')->nullable()->after('sale_item_id');
                }
                if (! Schema::hasColumn('sale_items', 'planned_gps')) {
                    $table->string('planned_gps', 100)->nullable();
                }
            });
            Schema::table('sale_items', function (Blueprint $table) {
                $table->dropColumn('warehouse_lot_id');
            });
        }

        Schema::dropIfExists('warehouse_lots');

        if (Schema::hasTable('warehouse_bins') && ! Schema::hasTable('warehouse_racks')) {
            Schema::rename('warehouse_bins', 'warehouse_racks');
        }

        if (Schema::hasTable('plant_varieties')) {
            Schema::table('plant_varieties', function (Blueprint $table) {
                if (! Schema::hasColumn('plant_varieties', 'harga_jual')) {
                    $table->decimal('harga_jual', 15, 2)->nullable();
                }
                if (! Schema::hasColumn('plant_varieties', 'minimal_stok')) {
                    $table->decimal('minimal_stok', 8, 2)->nullable();
                }
            });
        }

        if (! Schema::hasTable('stock')) {
            Schema::create('stock', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('no_label_resmi', 100)->unique();
                $table->string('qr_code_token', 255)->unique();
                $table->decimal('stok_awal', 8, 2);
                $table->decimal('stok_saat_ini', 8, 2);
                $table->string('rak_gudang_id', 36)->nullable();
                $table->date('tgl_kedaluwarsa');
                $table->enum('status_stok', ['siap_salur', 'butuh_uji_ulang', 'afkir_total'])->default('siap_salur');
                $table->string('seed_varieties_id', 36)->nullable();
                $table->string('certification_report_id', 36)->nullable();
                $table->boolean('hold_for_recert')->default(false);
                $table->string('updated_by', 36)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('stock_packaging')) {
            Schema::create('stock_packaging', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('stok_benih_id');
                $table->string('rak_gudang_id', 36)->nullable();
                $table->string('no_label_seri')->unique();
                $table->enum('jenis_kemasan', ['karung_bulk', 'plastik_ritel', 'pot_satuan']);
                $table->decimal('kapasitas_per_kemasan', 8, 2);
                $table->enum('status_kemasan', ['tersedia', 'sudah_disalurkan', 'afkir_rusak', 'tidak_aktif'])->default('tersedia');
                $table->string('qr_code_token', 255)->unique();
                $table->timestamps();

                $table->foreign('stok_benih_id')->references('id')->on('stock')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('certification_reports')) {
            Schema::table('certification_reports', function (Blueprint $table) {
                if (! Schema::hasColumn('certification_reports', 'uji_ke')) {
                    $table->unsignedInteger('uji_ke')->default(1);
                }
                if (! Schema::hasColumn('certification_reports', 'stock_id')) {
                    $table->unsignedBigInteger('stock_id')->nullable();
                }
            });

            $this->dropForeignsOnColumns('certification_reports', ['harvest_id', 'inventory_type_id']);

            $drops = array_values(array_filter([
                Schema::hasColumn('certification_reports', 'harvest_id') ? 'harvest_id' : null,
                Schema::hasColumn('certification_reports', 'inventory_type_id') ? 'inventory_type_id' : null,
                Schema::hasColumn('certification_reports', 'report_type') ? 'report_type' : null,
            ]));
            if ($drops !== []) {
                Schema::table('certification_reports', function (Blueprint $table) use ($drops) {
                    $table->dropColumn($drops);
                });
            }
        }
    }

    protected function dropForeignsReferencing(array $tables): void
    {
        foreach ($tables as $referencedTable) {
            try {
                $rows = DB::select(
                    'SELECT TABLE_NAME, CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME = ?',
                    [$referencedTable]
                );
                foreach ($rows as $row) {
                    try {
                        DB::statement('ALTER TABLE `'.$row->TABLE_NAME.'` DROP FOREIGN KEY `'.$row->CONSTRAINT_NAME.'`');
                    } catch (\Throwable $e) {
                    }
                }
            } catch (\Throwable $e) {
            }
        }
    }

    protected function dropForeignsOnColumns(string $table, array $columns): void
    {
        try {
            $rows = DB::select(
                'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME IN ('.implode(',', array_fill(0, count($columns), '?')).') AND REFERENCED_TABLE_NAME IS NOT NULL',
                array_merge([$table], $columns)
            );
            foreach ($rows as $row) {
                try {
                    DB::statement('ALTER TABLE `'.$table.'` DROP FOREIGN KEY `'.$row->CONSTRAINT_NAME.'`');
                } catch (\Throwable $e) {
                }
            }
        } catch (\Throwable $e) {
        }
    }
};
