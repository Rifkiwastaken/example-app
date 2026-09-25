<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel lampiran gabungan untuk modul tanaman, lokasi penanaman, dan stok.
        if (! Schema::hasTable('attachments')) {
            Schema::create('attachments', function (Blueprint $table) {
                $table->string('attachment_id', 36)->primary();
                $table->string('seed_varieties_id', 36)->nullable();
                $table->string('planting_location_id', 36)->nullable();
                $table->unsignedBigInteger('stock_id')->nullable();
                $table->string('title');
                $table->date('attachment_date');
                $table->text('description')->nullable();
                $table->string('file_path', 500)->nullable();
                $table->string('file_name', 255)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('mime_type', 150)->nullable();
                $table->string('created_by', 36)->nullable();
                $table->timestamps();

                $table->index('seed_varieties_id');
                $table->index('planting_location_id');
                $table->index('stock_id');
            });

            Schema::table('attachments', function (Blueprint $table) {
                $table->foreign('seed_varieties_id')->references('seed_varieties_id')->on('plant_varieties')->nullOnDelete();
                $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->nullOnDelete();
                $table->foreign('stock_id')->references('id')->on('stock')->nullOnDelete();
                $table->foreign('created_by')->references('user_id')->on('users')->nullOnDelete();
            });
        }

        // 2. Pindahkan lampiran lama (jika ada) ke tabel gabungan.
        if (Schema::hasTable('plant_attachments')) {
            foreach (DB::table('plant_attachments')->get() as $row) {
                DB::table('attachments')->insert([
                    'attachment_id' => 'ATC-'.strtoupper(substr(md5($row->plant_attachment_id), 0, 8)),
                    'seed_varieties_id' => $row->seed_varieties_id,
                    'title' => $row->keywords ?: 'Lampiran tanaman',
                    'attachment_date' => $row->note_date ?: ($row->created_at ?: now()),
                    'description' => $row->description,
                    'file_path' => $row->file_path,
                    'file_name' => $row->file_name,
                    'file_size' => $row->file_size,
                    'mime_type' => $row->mime_type,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }

        if (Schema::hasTable('planting_location_attachment')) {
            foreach (DB::table('planting_location_attachment')->get() as $row) {
                DB::table('attachments')->insert([
                    'attachment_id' => 'ATC-'.strtoupper(substr(md5($row->planting_location_attachment_id), 0, 8)),
                    'planting_location_id' => $row->planting_location_id,
                    'title' => $row->title ?: 'Lampiran lokasi',
                    'attachment_date' => $row->attachment_date ?: ($row->created_at ?: now()),
                    'description' => $row->description,
                    'file_path' => $row->file_path,
                    'file_name' => $row->file_name,
                    'file_size' => $row->file_size,
                    'mime_type' => $row->mime_type,
                    'created_by' => $row->created_by,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }

        Schema::dropIfExists('plant_attachments');
        Schema::dropIfExists('planting_location_attachment');
        Schema::dropIfExists('inventory_attachments');
        Schema::dropIfExists('planting_attachment_files');

        // 3. Lampiran produksi penanaman berubah menjadi penugasan.
        if (Schema::hasTable('planting_attachments') && ! Schema::hasTable('planting_assignments')) {
            Schema::rename('planting_attachments', 'planting_assignments');

            Schema::table('planting_assignments', function (Blueprint $table) {
                $table->renameColumn('planting_attachment_id', 'planting_assignment_id');
                $table->renameColumn('title', 'task_title');
            });

            foreach ([
                'planting_attachments_planting_location_id_foreign',
                'planting_attachments_seed_varieties_id_foreign',
                'planting_attachments_planting_id_foreign',
            ] as $constraint) {
                try {
                    DB::statement('ALTER TABLE `planting_assignments` DROP FOREIGN KEY `'.$constraint.'`');
                } catch (\Throwable $e) {
                    // FK tidak ada, lanjut
                }
            }

            Schema::table('planting_assignments', function (Blueprint $table) {
                foreach (['attachment_type', 'plant_type', 'keywords', 'seed_varieties_id', 'planting_location_id'] as $column) {
                    if (Schema::hasColumn('planting_assignments', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });

            Schema::table('planting_assignments', function (Blueprint $table) {
                $table->foreign('planting_id')->references('planting_production_id')->on('planting_production')->cascadeOnDelete();
            });

            Schema::table('planting_assignments', function (Blueprint $table) {
                if (! Schema::hasColumn('planting_assignments', 'status')) {
                    $table->string('status', 20)->default('ditugaskan');
                }
                if (! Schema::hasColumn('planting_assignments', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable();
                }
                if (! Schema::hasColumn('planting_assignments', 'completed_by')) {
                    $table->string('completed_by', 36)->nullable();
                }
                if (! Schema::hasColumn('planting_assignments', 'completion_note')) {
                    $table->text('completion_note')->nullable();
                }
            });
        }

        // 4. Penempatan lokasi penanaman disimpan langsung di akun user.
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'placement_location_id')) {
                $table->string('placement_location_id', 36)->nullable()->after('location_placement');
                $table->index('placement_location_id');
            }
        });

        Schema::dropIfExists('user_planting_location_land_worker');

        // 5. Informasi publik varietas untuk landing page.
        Schema::table('plant_varieties', function (Blueprint $table) {
            if (! Schema::hasColumn('plant_varieties', 'public_photo_path')) {
                $table->string('public_photo_path', 500)->nullable();
            }
            if (! Schema::hasColumn('plant_varieties', 'public_description')) {
                $table->text('public_description')->nullable();
            }
        });

        // 6. Hapus modul pengeluaran dan tipe inventaris.
        if (Schema::hasTable('stock_histories') && Schema::hasColumn('stock_histories', 'inventory_type_id')) {
            Schema::table('stock_histories', function (Blueprint $table) {
                try {
                    $table->dropForeign('stock_histories_inventory_type_id_foreign');
                } catch (\Throwable $e) {
                    // FK sudah tidak ada
                }
                $table->dropColumn('inventory_type_id');
            });
        }

        if (Schema::hasTable('stock_histories') && Schema::hasColumn('stock_histories', 'warehouse_lot_id')) {
            Schema::table('stock_histories', function (Blueprint $table) {
                $table->dropColumn('warehouse_lot_id');
            });
        }

        Schema::dropIfExists('expenses');
        Schema::dropIfExists('inventory_types');
    }

    public function down(): void
    {
        // Perubahan ini menghapus modul lama secara permanen; rollback tidak didukung.
    }
};
