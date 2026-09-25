<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['planting_nutrients', 'planting_treatments', 'planting_expenses'] as $tableName) {
            if (Schema::hasTable($tableName)) {
                $this->dropForeignsReferencing($tableName);
                Schema::drop($tableName);
            }
        }

        if (Schema::hasTable('planting_production')) {
            $drop = array_values(array_filter([
                Schema::hasColumn('planting_production', 'planting_location_id') ? 'planting_location_id' : null,
                Schema::hasColumn('planting_production', 'bed_label') ? 'bed_label' : null,
                Schema::hasColumn('planting_production', 'planting_format') ? 'planting_format' : null,
                Schema::hasColumn('planting_production', 'planting_format_custom') ? 'planting_format_custom' : null,
                Schema::hasColumn('planting_production', 'area_ha') ? 'area_ha' : null,
            ]));

            if ($drop !== []) {
                $this->dropForeignsOnColumns('planting_production', $drop);
                Schema::table('planting_production', function (Blueprint $table) use ($drop) {
                    $table->dropColumn($drop);
                });
            }

            Schema::table('planting_production', function (Blueprint $table) {
                if (! Schema::hasColumn('planting_production', 'planting_amount_seeds')) {
                    $table->unsignedInteger('planting_amount_seeds')->nullable();
                }
                if (! Schema::hasColumn('planting_production', 'target_kelas')) {
                    $table->string('target_kelas', 20)->nullable();
                }
                if (! Schema::hasColumn('planting_production', 'no_form_bpsb')) {
                    $table->string('no_form_bpsb', 100)->nullable();
                }
                if (! Schema::hasColumn('planting_production', 'tanggal_daftar_bpsb')) {
                    $table->date('tanggal_daftar_bpsb')->nullable();
                }
                if (! Schema::hasColumn('planting_production', 'status_sertifikasi')) {
                    $table->string('status_sertifikasi', 50)->nullable();
                }
                if (! Schema::hasColumn('planting_production', 'description')) {
                    $table->text('description')->nullable();
                }
                if (! Schema::hasColumn('planting_production', 'file_path')) {
                    $table->string('file_path', 500)->nullable();
                }
                if (! Schema::hasColumn('planting_production', 'file_name')) {
                    $table->string('file_name', 255)->nullable();
                }
                if (! Schema::hasColumn('planting_production', 'completed_at')) {
                    $table->date('completed_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('planting_fields')) {
            Schema::table('planting_fields', function (Blueprint $table) {
                if (! Schema::hasColumn('planting_fields', 'description')) {
                    $table->text('description')->nullable();
                }
                if (! Schema::hasColumn('planting_fields', 'file_path')) {
                    $table->string('file_path', 500)->nullable();
                }
                if (! Schema::hasColumn('planting_fields', 'file_name')) {
                    $table->string('file_name', 255)->nullable();
                }
            });
        }

        if (Schema::hasTable('planting_tasks') && ! Schema::hasTable('planting_report')) {
            Schema::rename('planting_tasks', 'planting_report');
        }
        if (Schema::hasTable('planting_report')) {
            Schema::table('planting_report', function (Blueprint $table) {
                if (! Schema::hasColumn('planting_report', 'growth_phase')) {
                    $table->string('growth_phase', 30)->nullable();
                }
                if (! Schema::hasColumn('planting_report', 'activity_type')) {
                    $table->string('activity_type', 30)->nullable();
                }
                if (! Schema::hasColumn('planting_report', 'product_detail')) {
                    $table->string('product_detail', 255)->nullable();
                }
                if (! Schema::hasColumn('planting_report', 'applied_amount')) {
                    $table->string('applied_amount', 100)->nullable();
                }
                if (! Schema::hasColumn('planting_report', 'external_technician')) {
                    $table->string('external_technician', 150)->nullable();
                }
                if (! Schema::hasColumn('planting_report', 'officer_id')) {
                    $table->string('officer_id', 36)->nullable();
                }
                if (! Schema::hasColumn('planting_report', 'activity_date')) {
                    $table->date('activity_date')->nullable();
                }
                if (! Schema::hasColumn('planting_report', 'file_path')) {
                    $table->string('file_path', 500)->nullable();
                }
                if (! Schema::hasColumn('planting_report', 'file_name')) {
                    $table->string('file_name', 255)->nullable();
                }
            });

            if (Schema::hasColumn('planting_report', 'due_date')) {
                try {
                    DB::statement('ALTER TABLE `planting_report` MODIFY `due_date` DATE NULL');
                } catch (\Throwable $e) {
                }
            }
        }

        if (Schema::hasTable('planting_losses') && ! Schema::hasTable('planting_rouging')) {
            Schema::rename('planting_losses', 'planting_rouging');
        }
        if (Schema::hasTable('planting_rouging')) {
            if (Schema::hasColumn('planting_rouging', 'loss_date') && ! Schema::hasColumn('planting_rouging', 'rouging_date')) {
                DB::statement('ALTER TABLE `planting_rouging` CHANGE `loss_date` `rouging_date` DATE NULL');
            }
            if (Schema::hasColumn('planting_rouging', 'loss_amount') && ! Schema::hasColumn('planting_rouging', 'plants_removed')) {
                DB::statement('ALTER TABLE `planting_rouging` CHANGE `loss_amount` `plants_removed` DECIMAL(12,2) NULL');
            }

            Schema::table('planting_rouging', function (Blueprint $table) {
                if (! Schema::hasColumn('planting_rouging', 'title')) {
                    $table->string('title', 255)->nullable();
                }
                if (! Schema::hasColumn('planting_rouging', 'characteristic')) {
                    $table->string('characteristic', 255)->nullable();
                }
                if (! Schema::hasColumn('planting_rouging', 'officer_id')) {
                    $table->string('officer_id', 36)->nullable();
                }
                if (! Schema::hasColumn('planting_rouging', 'file_path')) {
                    $table->string('file_path', 500)->nullable();
                }
                if (! Schema::hasColumn('planting_rouging', 'file_name')) {
                    $table->string('file_name', 255)->nullable();
                }
            });
        }

        if (Schema::hasTable('harvests') && ! Schema::hasTable('planting_harvest')) {
            $this->dropForeignsReferencing('harvests');
            Schema::rename('harvests', 'planting_harvest');
        }
        if (Schema::hasTable('planting_harvest')) {
            Schema::table('planting_harvest', function (Blueprint $table) {
                if (! Schema::hasColumn('planting_harvest', 'gross_weight_kg')) {
                    $table->decimal('gross_weight_kg', 12, 2)->nullable();
                }
                if (! Schema::hasColumn('planting_harvest', 'harvest_moisture')) {
                    $table->decimal('harvest_moisture', 8, 2)->nullable();
                }
                if (! Schema::hasColumn('planting_harvest', 'candidate_lot_no')) {
                    $table->string('candidate_lot_no', 100)->nullable();
                }
                if (! Schema::hasColumn('planting_harvest', 'extraction_method')) {
                    $table->string('extraction_method', 50)->nullable();
                }
                if (! Schema::hasColumn('planting_harvest', 'method')) {
                    $table->string('method', 150)->nullable();
                }
                if (! Schema::hasColumn('planting_harvest', 'process_finished_at')) {
                    $table->date('process_finished_at')->nullable();
                }
                if (! Schema::hasColumn('planting_harvest', 'final_moisture')) {
                    $table->decimal('final_moisture', 8, 2)->nullable();
                }
                if (! Schema::hasColumn('planting_harvest', 'net_weight_kg')) {
                    $table->decimal('net_weight_kg', 12, 2)->nullable();
                }
                if (! Schema::hasColumn('planting_harvest', 'shrinkage_kg')) {
                    $table->decimal('shrinkage_kg', 12, 2)->nullable();
                }
                if (! Schema::hasColumn('planting_harvest', 'file_path')) {
                    $table->string('file_path', 500)->nullable();
                }
                if (! Schema::hasColumn('planting_harvest', 'file_name')) {
                    $table->string('file_name', 255)->nullable();
                }
                if (! Schema::hasColumn('planting_harvest', 'officer_id')) {
                    $table->string('officer_id', 36)->nullable();
                }
                if (! Schema::hasColumn('planting_harvest', 'description')) {
                    $table->text('description')->nullable();
                }
            });

            if (Schema::hasTable('certification_reports') && Schema::hasColumn('certification_reports', 'harvest_id')) {
                try {
                    Schema::table('certification_reports', function (Blueprint $table) {
                        $table->foreign('harvest_id')
                            ->references('harvest_id')
                            ->on('planting_harvest')
                            ->nullOnDelete();
                    });
                } catch (\Throwable $e) {
                    // already exists or incompatible leftover data
                }
            }
        }

        if (! Schema::hasTable('planting_inspection')) {
            Schema::create('planting_inspection', function (Blueprint $table) {
                $table->string('planting_inspection_id', 36)->primary();
                $table->string('planting_id', 36);
                $table->string('title', 255)->nullable();
                $table->string('inspection_phase', 30)->nullable();
                $table->date('inspected_at')->nullable();
                $table->string('supervisor_name', 150)->nullable();
                $table->string('conclusion', 50)->nullable();
                $table->string('minutes_file_path', 500)->nullable();
                $table->string('minutes_file_name', 255)->nullable();
                $table->string('officer_id', 36)->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('planting_location_attachment')) {
            Schema::create('planting_location_attachment', function (Blueprint $table) {
                $table->string('planting_location_attachment_id', 36)->primary();
                $table->string('planting_location_id', 36);
                $table->string('title', 255);
                $table->string('attachment_type', 255)->nullable();
                $table->string('plant_type', 255)->nullable();
                $table->text('description')->nullable();
                $table->date('attachment_date')->nullable();
                $table->string('file_path', 500)->nullable();
                $table->string('file_name', 255)->nullable();
                $table->unsignedInteger('file_size')->nullable();
                $table->string('mime_type', 150)->nullable();
                $table->string('created_by', 36)->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('planting_attachments')) {
            Schema::table('planting_attachments', function (Blueprint $table) {
                if (! Schema::hasColumn('planting_attachments', 'planting_id')) {
                    $table->string('planting_id', 36)->nullable()->after('planting_location_id');
                }
                if (! Schema::hasColumn('planting_attachments', 'keywords')) {
                    $table->string('keywords', 255)->nullable();
                }
            });
        }
    }

    protected function dropForeignsOnColumns(string $table, array $columns): void
    {
        try {
            $rows = DB::select('SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME IN ('.implode(',', array_fill(0, count($columns), '?')).') AND REFERENCED_TABLE_NAME IS NOT NULL', array_merge([$table], $columns));
            foreach ($rows as $row) {
                try {
                    DB::statement('ALTER TABLE `'.$table.'` DROP FOREIGN KEY `'.$row->CONSTRAINT_NAME.'`');
                } catch (\Throwable $e) {
                }
            }
        } catch (\Throwable $e) {
        }
    }

    protected function dropForeignsReferencing(string $referencedTable): void
    {
        try {
            $rows = DB::select('SELECT TABLE_NAME, CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME = ?', [$referencedTable]);
            foreach ($rows as $row) {
                try {
                    DB::statement('ALTER TABLE `'.$row->TABLE_NAME.'` DROP FOREIGN KEY `'.$row->CONSTRAINT_NAME.'`');
                } catch (\Throwable $e) {
                }
            }
        } catch (\Throwable $e) {
        }
    }
};
