<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropForeignsReferencing('plantings');
        $this->dropForeignsOnColumn('plantings', 'plant_id');

        if (Schema::hasTable('plantings') && Schema::hasColumn('plantings', 'plant_id')) {
            Schema::table('plantings', function (Blueprint $table) {
                $table->dropColumn('plant_id');
            });
        }

        if (Schema::hasTable('plantings') && ! Schema::hasTable('planting_production')) {
            Schema::rename('plantings', 'planting_production');
        }

        if (Schema::hasTable('planting_production')) {
            if (Schema::hasColumn('planting_production', 'plant_id')) {
                $this->dropForeignsOnColumn('planting_production', 'plant_id');
                Schema::table('planting_production', function (Blueprint $table) {
                    $table->dropColumn('plant_id');
                });
            }

            if (Schema::hasColumn('planting_production', 'planting_id') && ! Schema::hasColumn('planting_production', 'planting_production_id')) {
                Schema::table('planting_production', function (Blueprint $table) {
                    $table->renameColumn('planting_id', 'planting_production_id');
                });
            }

            Schema::table('planting_production', function (Blueprint $table) {
                $after = Schema::hasColumn('planting_production', 'planting_production_id')
                    ? 'planting_production_id'
                    : (Schema::hasColumn('planting_production', 'planting_id') ? 'planting_id' : null);
                if (! Schema::hasColumn('planting_production', 'seed_source_id')) {
                    $col = $table->string('seed_source_id', 36)->nullable();
                    if ($after) {
                        $col->after($after);
                    }
                }
                if (! Schema::hasColumn('planting_production', 'planting_field_id')) {
                    $table->unsignedBigInteger('planting_field_id')->nullable()->after('seed_source_id');
                }
            });

            $this->tryForeign('planting_production', 'seed_source_id', 'seed_sources', 'seed_source_id');
            $this->tryForeign('planting_production', 'planting_field_id', 'planting_fields', 'id');
        }

        $childTables = [
            'harvests',
            'expenses',
            'planting_losses',
            'planting_tasks',
            'planting_treatments',
            'planting_nutrients',
            'planting_notes',
        ];

        foreach ($childTables as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'planting_id')) {
                continue;
            }
            $this->tryForeign($tableName, 'planting_id', 'planting_production', 'planting_production_id');
        }

        if (Schema::hasTable('planting_attachments')) {
            Schema::table('planting_attachments', function (Blueprint $table) {
                if (! Schema::hasColumn('planting_attachments', 'seed_varieties_id')) {
                    $table->string('seed_varieties_id', 36)->nullable()->after('planting_location_id');
                }
                if (! Schema::hasColumn('planting_attachments', 'intended_for')) {
                    $table->string('intended_for', 36)->nullable()->after('created_by');
                }
            });

            try {
                DB::statement('ALTER TABLE planting_attachments MODIFY planting_location_id VARCHAR(36) NULL');
            } catch (\Throwable $e) {
                // already nullable
            }
        }

        if (! Schema::hasTable('planting_attachment_files')) {
            Schema::create('planting_attachment_files', function (Blueprint $table) {
                $table->id();
                $table->string('planting_attachment_id', 36);
                $table->string('file_path', 500);
                $table->string('file_name', 255)->nullable();
                $table->unsignedInteger('file_size')->nullable();
                $table->string('mime_type', 150)->nullable();
                $table->timestamps();

                $table->foreign('planting_attachment_id')
                    ->references('planting_attachment_id')
                    ->on('planting_attachments')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('planting_attachments') && Schema::hasColumn('planting_attachments', 'file_path')) {
            $rows = DB::table('planting_attachments')->whereNotNull('file_path')->where('file_path', '!=', '')->get();
            foreach ($rows as $row) {
                $exists = DB::table('planting_attachment_files')
                    ->where('planting_attachment_id', $row->planting_attachment_id)
                    ->exists();
                if ($exists) {
                    continue;
                }
                DB::table('planting_attachment_files')->insert([
                    'planting_attachment_id' => $row->planting_attachment_id,
                    'file_path' => $row->file_path,
                    'file_name' => $row->file_name ?? null,
                    'file_size' => $row->file_size ?? null,
                    'mime_type' => $row->mime_type ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (Schema::hasTable('seed_sources') && ! Schema::hasColumn('seed_sources', 'description')) {
            Schema::table('seed_sources', function (Blueprint $table) {
                $table->text('description')->nullable()->after('quantity_kg');
            });
        }

        if (! Schema::hasTable('seed_source_files')) {
            Schema::create('seed_source_files', function (Blueprint $table) {
                $table->id();
                $table->string('seed_source_id', 36);
                $table->string('file_path', 500);
                $table->string('file_name', 255)->nullable();
                $table->unsignedInteger('file_size')->nullable();
                $table->string('mime_type', 150)->nullable();
                $table->timestamps();

                $table->foreign('seed_source_id')
                    ->references('seed_source_id')
                    ->on('seed_sources')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seed_source_files');
        Schema::dropIfExists('planting_attachment_files');

        if (Schema::hasTable('seed_sources') && Schema::hasColumn('seed_sources', 'description')) {
            Schema::table('seed_sources', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }

    protected function dropForeignsOnColumn(string $tableName, string $column): void
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, $column)) {
            return;
        }

        $rows = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$tableName, $column]);

        foreach ($rows as $row) {
            try {
                Schema::table($tableName, function (Blueprint $table) use ($row) {
                    $table->dropForeign($row->CONSTRAINT_NAME);
                });
            } catch (\Throwable $e) {
                // already dropped
            }
        }
    }

    protected function dropForeignsReferencing(string $referencedTable): void
    {
        $rows = DB::select("
            SELECT TABLE_NAME, CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND REFERENCED_TABLE_NAME = ?
        ", [$referencedTable]);

        foreach ($rows as $row) {
            try {
                Schema::table($row->TABLE_NAME, function (Blueprint $table) use ($row) {
                    $table->dropForeign($row->CONSTRAINT_NAME);
                });
            } catch (\Throwable $e) {
                // already dropped
            }
        }
    }

    protected function tryForeign(string $table, string $column, string $refTable, string $refColumn): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($column, $refTable, $refColumn) {
                $blueprint->foreign($column)->references($refColumn)->on($refTable)->nullOnDelete();
            });
        } catch (\Throwable $e) {
            // duplicate or missing referenced rows
        }
    }
};
