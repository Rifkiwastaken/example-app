<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropForeignsReferencing([
            'planting_attachment_files',
            'seed_source_files',
            'task_templates',
            'warehouse_lots',
            'warehouse_bins',
            'warehouses',
        ]);

        foreach ([
            'planting_attachment_files',
            'seed_source_files',
            'task_templates',
            'warehouse_lots',
            'warehouse_bins',
            'warehouses',
        ] as $tableName) {
            Schema::dropIfExists($tableName);
        }

        if (Schema::hasTable('seed_sources') && ! Schema::hasTable('plant_seed_source')) {
            $this->dropForeignsOnColumns('seed_sources', ['seed_varieties_id']);
            $this->dropForeignsReferencing(['seed_sources']);
            Schema::rename('seed_sources', 'plant_seed_source');
        }

        if (Schema::hasTable('plant_seed_source')) {
            Schema::table('plant_seed_source', function (Blueprint $table) {
                if (! Schema::hasColumn('plant_seed_source', 'file_path')) {
                    $table->string('file_path', 500)->nullable();
                }
                if (! Schema::hasColumn('plant_seed_source', 'file_name')) {
                    $table->string('file_name', 255)->nullable();
                }
            });
        }

        if (! Schema::hasTable('seed_units')) {
            Schema::create('seed_units', function (Blueprint $table) {
                $table->string('seed_unit_id', 36)->primary();
                $table->string('name', 100);
                $table->string('code', 20);
                $table->boolean('is_fixed')->default(false);
                $table->timestamps();
                $table->unique('code');
            });
        }

        $this->ensureFixedSeedUnits();

        if (Schema::hasTable('plant_varieties')) {
            Schema::table('plant_varieties', function (Blueprint $table) {
                if (! Schema::hasColumn('plant_varieties', 'satuan_stok_id')) {
                    $table->string('satuan_stok_id', 36)->nullable();
                }
                if (! Schema::hasColumn('plant_varieties', 'satuan_tanam_id')) {
                    $table->string('satuan_tanam_id', 36)->nullable();
                }
                if (! Schema::hasColumn('plant_varieties', 'satuan_panen_id')) {
                    $table->string('satuan_panen_id', 36)->nullable();
                }
            });
        }

        if (Schema::hasTable('planting_production')) {
            Schema::table('planting_production', function (Blueprint $table) {
                if (! Schema::hasColumn('planting_production', 'status')) {
                    $table->string('status', 30)->default('berjalan');
                }
                if (! Schema::hasColumn('planting_production', 'satuan_panen_id')) {
                    $table->string('satuan_panen_id', 36)->nullable();
                }
            });

            DB::table('planting_production')->whereNull('status')->orWhere('status', '')->update(['status' => 'berjalan']);
            DB::table('planting_production')->where('is_completed', 1)->update(['status' => 'selesai']);

            $indexes = collect(DB::select('SHOW INDEX FROM planting_production'))->pluck('Key_name');
            $hasDuplicateBatch = DB::table('planting_production')
                ->select('planting_batch_number')
                ->whereNotNull('planting_batch_number')
                ->groupBy('planting_batch_number')
                ->havingRaw('COUNT(*) > 1')
                ->exists();
            if (! $indexes->contains('planting_production_batch_unique') && ! $hasDuplicateBatch) {
                Schema::table('planting_production', function (Blueprint $table) {
                    $table->unique('planting_batch_number', 'planting_production_batch_unique');
                });
            }
        }

        if (Schema::hasTable('planting_harvest')) {
            $drop = array_values(array_filter([
                Schema::hasColumn('planting_harvest', 'final_moisture') ? 'final_moisture' : null,
                Schema::hasColumn('planting_harvest', 'net_weight_kg') ? 'net_weight_kg' : null,
                Schema::hasColumn('planting_harvest', 'extraction_method') ? 'extraction_method' : null,
                Schema::hasColumn('planting_harvest', 'process_finished_at') ? 'process_finished_at' : null,
                Schema::hasColumn('planting_harvest', 'shrinkage_kg') ? 'shrinkage_kg' : null,
            ]));
            if ($drop !== []) {
                Schema::table('planting_harvest', function (Blueprint $table) use ($drop) {
                    $table->dropColumn($drop);
                });
            }
        }

        if (! Schema::hasTable('planting_post_harvest')) {
            Schema::create('planting_post_harvest', function (Blueprint $table) {
                $table->string('planting_post_harvest_id', 36)->primary();
                $table->string('harvest_id', 36);
                $table->string('planting_id', 36);
                $table->string('lot_number', 100)->nullable();
                $table->date('finished_at');
                $table->string('extraction_method', 50);
                $table->string('drying_method', 50)->nullable();
                $table->decimal('drying_temp_celsius', 4, 2)->nullable();
                $table->decimal('net_weight', 8, 2);
                $table->decimal('process_shrinkage', 8, 2);
                $table->boolean('continue_planting')->default(false);
                $table->string('created_by', 36)->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('certification_reports') && ! Schema::hasColumn('certification_reports', 'planting_post_harvest_id')) {
            Schema::table('certification_reports', function (Blueprint $table) {
                $table->string('planting_post_harvest_id', 36)->nullable();
            });
        }
    }

    public function ensureFixedSeedUnits(): void
    {
        $fixed = [
            ['name' => 'Kilogram', 'code' => 'kg'],
            ['name' => 'Gram', 'code' => 'g'],
            ['name' => 'Butir Biji', 'code' => 'btr'],
            ['name' => 'Kecambah', 'code' => 'kcb'],
            ['name' => 'Batang / Pohon', 'code' => 'btg'],
            ['name' => 'Stek', 'code' => 'stk'],
            ['name' => 'Ton', 'code' => 'ton'],
        ];

        foreach ($fixed as $unit) {
            $existing = DB::table('seed_units')->where('code', $unit['code'])->first();
            if ($existing) {
                DB::table('seed_units')->where('seed_unit_id', $existing->seed_unit_id)->update([
                    'name' => $unit['name'],
                    'is_fixed' => 1,
                    'updated_at' => now(),
                ]);
                continue;
            }

            DB::table('seed_units')->insert([
                'seed_unit_id' => 'SUN-'.strtoupper(substr(md5($unit['code'].microtime()), 0, 8)),
                'name' => $unit['name'],
                'code' => $unit['code'],
                'is_fixed' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
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
