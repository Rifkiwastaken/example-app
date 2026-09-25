<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - plant_types → seed_commodities (PK plant_type_id → seed_commodity_id)
     * - plants → seed_varieties (FK plant_type_id → seed_commodity_id, variety → description)
     * - Pindahkan kolom agronomi plants ke seed_classes
     * - Tambah seed_sources (benih sumber)
     */
    public function up(): void
    {
        $this->renamePlantTypesToSeedCommodities();
        $this->renamePlantsToSeedVarieties();
        $this->createSeedClassesAndMoveColumns();
        $this->createSeedSources();
    }

    protected function renamePlantTypesToSeedCommodities(): void
    {
        if (Schema::hasTable('plant_types') && !Schema::hasTable('seed_commodities')) {
            Schema::rename('plant_types', 'seed_commodities');
        }

        if (!Schema::hasTable('seed_commodities') || !Schema::hasColumn('seed_commodities', 'plant_type_id')) {
            return;
        }

        DB::statement('ALTER TABLE `seed_commodities` CHANGE `plant_type_id` `seed_commodity_id` VARCHAR(36) NOT NULL');

        $this->renameIndexIfExists('seed_commodities', 'plant_types_name_unique', 'seed_commodities_name_unique');
    }

    protected function renamePlantsToSeedVarieties(): void
    {
        if (Schema::hasTable('plants') && !Schema::hasTable('seed_varieties')) {
            Schema::rename('plants', 'seed_varieties');
        }

        if (!Schema::hasTable('seed_varieties')) {
            return;
        }

        if (Schema::hasColumn('seed_varieties', 'plant_type_id')) {
            DB::statement('ALTER TABLE `seed_varieties` CHANGE `plant_type_id` `seed_commodity_id` VARCHAR(36) NULL');
        }

        if (Schema::hasColumn('seed_varieties', 'variety') && !Schema::hasColumn('seed_varieties', 'description')) {
            DB::statement('ALTER TABLE `seed_varieties` CHANGE `variety` `description` VARCHAR(255) NOT NULL');
        }

        $this->renameIndexIfExists('seed_varieties', 'plants_variety_unique', 'seed_varieties_description_unique');
    }

    protected function createSeedClassesAndMoveColumns(): void
    {
        if (!Schema::hasTable('seed_varieties')) {
            return;
        }

        if (!Schema::hasTable('seed_classes')) {
            Schema::create('seed_classes', function (Blueprint $table) {
                $table->string('seed_class_id', 36)->primary();
                $table->string('plant_id', 36);
                $table->unsignedInteger('days_to_emerge')->nullable();
                $table->string('spacing_between_plants')->nullable();
                $table->string('spacing_between_rows')->nullable();
                $table->string('sowing_depth')->nullable();
                $table->string('avg_height')->nullable();
                $table->string('start_method')->nullable();
                $table->string('germination_stage')->nullable();
                $table->unsignedInteger('seeds_per_hole')->nullable();
                $table->string('light_profile')->nullable();
                $table->string('soil_condition')->nullable();
                $table->text('planting_detail')->nullable();
                $table->text('pruning_detail')->nullable();
                $table->boolean('perennial')->default(false);
                $table->unsignedInteger('days_to_flower')->nullable();
                $table->unsignedInteger('days_to_harvest')->nullable();
                $table->unsignedInteger('harvest_window_days')->nullable();
                $table->string('expected_loss_rate')->nullable();
                $table->string('harvest_unit')->nullable();
                $table->timestamps();

                $table->unique('plant_id', 'seed_classes_plant_id_unique');
            });
        }

        $agronomyColumns = [
            'days_to_emerge',
            'spacing_between_plants',
            'spacing_between_rows',
            'sowing_depth',
            'avg_height',
            'start_method',
            'germination_stage',
            'seeds_per_hole',
            'light_profile',
            'soil_condition',
            'planting_detail',
            'pruning_detail',
            'perennial',
            'days_to_flower',
            'days_to_harvest',
            'harvest_window_days',
            'expected_loss_rate',
            'harvest_unit',
        ];

        $existing = array_values(array_filter(
            $agronomyColumns,
            fn (string $column) => Schema::hasColumn('seed_varieties', $column)
        ));

        if ($existing !== []) {
            $selectList = implode(', ', array_map(
                fn (string $column) => '`' . $column . '`',
                $existing
            ));

            $insertList = '`seed_class_id`, `plant_id`, ' . $selectList . ', `created_at`, `updated_at`';
            $fromList = "CONCAT('SCL-', UPPER(SUBSTRING(MD5(`plant_id`), 1, 8))), `plant_id`, {$selectList}, `created_at`, `updated_at`";

            DB::statement("
                INSERT INTO `seed_classes` ({$insertList})
                SELECT {$fromList}
                FROM `seed_varieties`
                WHERE `plant_id` NOT IN (SELECT `plant_id` FROM `seed_classes`)
            ");

            Schema::table('seed_varieties', function (Blueprint $table) use ($existing) {
                $table->dropColumn($existing);
            });
        }
    }

    protected function createSeedSources(): void
    {
        if (Schema::hasTable('seed_sources')) {
            return;
        }

        Schema::create('seed_sources', function (Blueprint $table) {
            $table->string('seed_source_id', 36)->primary();
            $table->string('plant_id', 36)->nullable();
            $table->enum('seed_class', ['BS', 'FS']);
            $table->string('origin_lot_number', 50);
            $table->string('origin_producer', 150);
            $table->decimal('quantity_kg', 8, 2);
            $table->timestamps();
        });
    }

    protected function renameIndexIfExists(string $table, string $from, string $to): void
    {
        $indexes = collect(DB::select('SHOW INDEX FROM `' . $table . '`'))
            ->pluck('Key_name')
            ->unique()
            ->all();

        if (in_array($from, $indexes, true) && !in_array($to, $indexes, true)) {
            DB::statement("ALTER TABLE `{$table}` RENAME INDEX `{$from}` TO `{$to}`");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('seed_sources')) {
            Schema::drop('seed_sources');
        }

        if (Schema::hasTable('seed_classes') && Schema::hasTable('seed_varieties')) {
            $agronomyColumns = [
                'days_to_emerge' => "INT UNSIGNED NULL",
                'spacing_between_plants' => "VARCHAR(255) NULL",
                'spacing_between_rows' => "VARCHAR(255) NULL",
                'sowing_depth' => "VARCHAR(255) NULL",
                'avg_height' => "VARCHAR(255) NULL",
                'start_method' => "VARCHAR(255) NULL",
                'germination_stage' => "VARCHAR(255) NULL",
                'seeds_per_hole' => "INT UNSIGNED NULL",
                'light_profile' => "VARCHAR(255) NULL",
                'soil_condition' => "VARCHAR(255) NULL",
                'planting_detail' => "TEXT NULL",
                'pruning_detail' => "TEXT NULL",
                'perennial' => "TINYINT(1) NOT NULL DEFAULT 0",
                'days_to_flower' => "INT UNSIGNED NULL",
                'days_to_harvest' => "INT UNSIGNED NULL",
                'harvest_window_days' => "INT UNSIGNED NULL",
                'expected_loss_rate' => "VARCHAR(255) NULL",
                'harvest_unit' => "VARCHAR(255) NULL",
            ];

            foreach ($agronomyColumns as $column => $definition) {
                if (!Schema::hasColumn('seed_varieties', $column)) {
                    DB::statement("ALTER TABLE `seed_varieties` ADD `{$column}` {$definition}");
                }
            }

            DB::statement("
                UPDATE `seed_varieties` sv
                JOIN `seed_classes` sc ON sc.plant_id = sv.plant_id
                SET
                    sv.days_to_emerge = sc.days_to_emerge,
                    sv.spacing_between_plants = sc.spacing_between_plants,
                    sv.spacing_between_rows = sc.spacing_between_rows,
                    sv.sowing_depth = sc.sowing_depth,
                    sv.avg_height = sc.avg_height,
                    sv.start_method = sc.start_method,
                    sv.germination_stage = sc.germination_stage,
                    sv.seeds_per_hole = sc.seeds_per_hole,
                    sv.light_profile = sc.light_profile,
                    sv.soil_condition = sc.soil_condition,
                    sv.planting_detail = sc.planting_detail,
                    sv.pruning_detail = sc.pruning_detail,
                    sv.perennial = sc.perennial,
                    sv.days_to_flower = sc.days_to_flower,
                    sv.days_to_harvest = sc.days_to_harvest,
                    sv.harvest_window_days = sc.harvest_window_days,
                    sv.expected_loss_rate = sc.expected_loss_rate,
                    sv.harvest_unit = sc.harvest_unit
            ");

            Schema::drop('seed_classes');
        }

        if (Schema::hasTable('seed_varieties') && !Schema::hasTable('plants')) {
            if (Schema::hasColumn('seed_varieties', 'description') && !Schema::hasColumn('seed_varieties', 'variety')) {
                DB::statement('ALTER TABLE `seed_varieties` CHANGE `description` `variety` VARCHAR(255) NOT NULL');
            }
            if (Schema::hasColumn('seed_varieties', 'seed_commodity_id') && !Schema::hasColumn('seed_varieties', 'plant_type_id')) {
                DB::statement('ALTER TABLE `seed_varieties` CHANGE `seed_commodity_id` `plant_type_id` VARCHAR(36) NULL');
            }
            $this->renameIndexIfExists('seed_varieties', 'seed_varieties_description_unique', 'plants_variety_unique');
            Schema::rename('seed_varieties', 'plants');
        }

        if (Schema::hasTable('seed_commodities') && !Schema::hasTable('plant_types')) {
            if (Schema::hasColumn('seed_commodities', 'seed_commodity_id') && !Schema::hasColumn('seed_commodities', 'plant_type_id')) {
                DB::statement('ALTER TABLE `seed_commodities` CHANGE `seed_commodity_id` `plant_type_id` VARCHAR(36) NOT NULL');
            }
            $this->renameIndexIfExists('seed_commodities', 'seed_commodities_name_unique', 'plant_types_name_unique');
            Schema::rename('seed_commodities', 'plant_types');
        }
    }
};
