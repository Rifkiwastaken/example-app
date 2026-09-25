<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        $this->wipeApplicationData();
        $this->moveAgronomyFromSeedClassToVarieties();
        $this->dropSeedClasses();
        $this->renameCommodityAndVarietyTables();
        $this->renameVarietyPrimaryKey();
        $this->renameSeedSourcesForeignKey();
        $this->mergeInventoryNotesAndPhotos();
        $this->mergePlantNotesAndPhotos();
    }

    public function down(): void
    {
        // Irreversible: data wipe + structural merge.
    }

    protected function wipeApplicationData(): void
    {
        $keep = [
            'migrations',
            'users',
            'password_reset_tokens',
            'password_resets',
            'sessions',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'failed_jobs',
            'personal_access_tokens',
        ];

        $database = DB::getDatabaseName();
        $tableKey = 'Tables_in_' . $database;
        $tables = DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $row) {
            $table = $row->{$tableKey};
            if (in_array($table, $keep, true)) {
                continue;
            }
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        foreach ([
            'plant-notes',
            'plant-photos',
            'inventory-photos',
            'inventory-notes',
            'planting-attachments',
            'attachments',
        ] as $directory) {
            if (Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->deleteDirectory($directory);
            }
        }
    }

    protected function moveAgronomyFromSeedClassToVarieties(): void
    {
        $varietyTable = Schema::hasTable('seed_varieties') ? 'seed_varieties' : (Schema::hasTable('plant_varieties') ? 'plant_varieties' : null);
        if (! $varietyTable) {
            return;
        }

        Schema::table($varietyTable, function (Blueprint $table) use ($varietyTable) {
            if (! Schema::hasColumn($varietyTable, 'days_to_emerge')) {
                $table->unsignedInteger('days_to_emerge')->nullable();
            }
            if (! Schema::hasColumn($varietyTable, 'spacing_between_plants')) {
                $table->string('spacing_between_plants')->nullable();
            }
            if (! Schema::hasColumn($varietyTable, 'spacing_between_rows')) {
                $table->string('spacing_between_rows')->nullable();
            }
            if (! Schema::hasColumn($varietyTable, 'sowing_depth')) {
                $table->string('sowing_depth')->nullable();
            }
            if (! Schema::hasColumn($varietyTable, 'avg_height')) {
                $table->string('avg_height')->nullable();
            }
            if (! Schema::hasColumn($varietyTable, 'start_method')) {
                $table->string('start_method')->nullable();
            }
            if (! Schema::hasColumn($varietyTable, 'germination_stage')) {
                $table->string('germination_stage')->nullable();
            }
            if (! Schema::hasColumn($varietyTable, 'seeds_per_hole')) {
                $table->unsignedInteger('seeds_per_hole')->nullable();
            }
            if (! Schema::hasColumn($varietyTable, 'light_profile')) {
                $table->string('light_profile')->nullable();
            }
            if (! Schema::hasColumn($varietyTable, 'soil_condition')) {
                $table->string('soil_condition')->nullable();
            }
            if (! Schema::hasColumn($varietyTable, 'planting_detail')) {
                $table->text('planting_detail')->nullable();
            }
            if (! Schema::hasColumn($varietyTable, 'pruning_detail')) {
                $table->text('pruning_detail')->nullable();
            }
            if (! Schema::hasColumn($varietyTable, 'perennial')) {
                $table->boolean('perennial')->default(false);
            }
            if (! Schema::hasColumn($varietyTable, 'days_to_flower')) {
                $table->unsignedInteger('days_to_flower')->nullable();
            }
            if (! Schema::hasColumn($varietyTable, 'days_to_harvest')) {
                $table->unsignedInteger('days_to_harvest')->nullable();
            }
            if (! Schema::hasColumn($varietyTable, 'harvest_window_days')) {
                $table->unsignedInteger('harvest_window_days')->nullable();
            }
            if (! Schema::hasColumn($varietyTable, 'expected_loss_rate')) {
                $table->string('expected_loss_rate')->nullable();
            }
            if (! Schema::hasColumn($varietyTable, 'harvest_unit')) {
                $table->string('harvest_unit')->nullable();
            }
        });
    }

    protected function dropSeedClasses(): void
    {
        Schema::dropIfExists('seed_classes');
    }

    protected function renameCommodityAndVarietyTables(): void
    {
        if (Schema::hasTable('seed_commodities') && ! Schema::hasTable('plant_commodities')) {
            Schema::rename('seed_commodities', 'plant_commodities');
        }
        if (Schema::hasTable('seed_varieties') && ! Schema::hasTable('plant_varieties')) {
            Schema::rename('seed_varieties', 'plant_varieties');
        }

        $this->renameIndexIfExists('plant_commodities', 'seed_commodities_name_unique', 'plant_commodities_name_unique');
        $this->renameIndexIfExists('plant_varieties', 'seed_varieties_variety_unique', 'plant_varieties_variety_unique');
    }

    protected function renameVarietyPrimaryKey(): void
    {
        if (! Schema::hasTable('plant_varieties') || ! Schema::hasColumn('plant_varieties', 'plant_id')) {
            return;
        }

        $this->dropForeignKeysReferencing('plant_varieties', 'plant_id');
        $this->dropForeignKeysOn('plant_varieties', 'plant_id');

        DB::statement('ALTER TABLE `plant_varieties` CHANGE `plant_id` `seed_varieties_id` VARCHAR(36) NOT NULL');
    }

    protected function renameSeedSourcesForeignKey(): void
    {
        if (! Schema::hasTable('seed_sources') || ! Schema::hasColumn('seed_sources', 'plant_id')) {
            return;
        }

        $this->dropForeignKeysOn('seed_sources', 'plant_id');
        DB::statement('ALTER TABLE `seed_sources` CHANGE `plant_id` `seed_varieties_id` VARCHAR(36) NULL');
    }

    protected function mergeInventoryNotesAndPhotos(): void
    {
        if (! Schema::hasTable('inventory_attachments')) {
            Schema::create('inventory_attachments', function (Blueprint $table) {
                $table->string('inventory_attachment_id', 36)->primary();
                $table->string('inventory_type_id', 36);
                $table->enum('type', ['note', 'photo']);
                $table->text('content')->nullable();
                $table->string('caption', 255)->nullable();
                $table->string('file_path', 255)->nullable();
                $table->string('user_id', 36)->nullable();
                $table->timestamps();
            });
        }

        Schema::dropIfExists('inventory_photos');
        Schema::dropIfExists('inventory_notes');
    }

    protected function mergePlantNotesAndPhotos(): void
    {
        if (! Schema::hasTable('plant_attachments')) {
            Schema::create('plant_attachments', function (Blueprint $table) {
                $table->string('plant_attachment_id', 36)->primary();
                $table->string('seed_varieties_id', 36);
                $table->enum('type', ['note', 'photo']);
                $table->text('description')->nullable();
                $table->date('note_date')->nullable();
                $table->string('keywords')->nullable();
                $table->string('file_path', 512)->nullable();
                $table->string('file_name', 255)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('mime_type', 100)->nullable();
                $table->dateTime('taken_at')->nullable();
                $table->timestamps();
            });
        }

        Schema::dropIfExists('plant_photos');
        Schema::dropIfExists('plant_notes');
    }

    protected function dropForeignKeysOn(string $table, string $column): void
    {
        $constraints = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$table, $column]);

        foreach ($constraints as $row) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$row->CONSTRAINT_NAME}`");
        }
    }

    protected function dropForeignKeysReferencing(string $referencedTable, string $referencedColumn): void
    {
        $constraints = DB::select("
            SELECT TABLE_NAME, CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND REFERENCED_TABLE_NAME = ?
              AND REFERENCED_COLUMN_NAME = ?
        ", [$referencedTable, $referencedColumn]);

        foreach ($constraints as $row) {
            DB::statement("ALTER TABLE `{$row->TABLE_NAME}` DROP FOREIGN KEY `{$row->CONSTRAINT_NAME}`");
        }
    }

    protected function renameIndexIfExists(string $table, string $from, string $to): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $indexes = collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->pluck('Key_name')
            ->unique()
            ->all();

        if (in_array($from, $indexes, true) && ! in_array($to, $indexes, true)) {
            DB::statement("ALTER TABLE `{$table}` RENAME INDEX `{$from}` TO `{$to}`");
        }
    }
};
