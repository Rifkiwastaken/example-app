<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Pastikan task_templates mendukung "Simpan sebagai Template":
     * - association boleh string (bukan hanya enum) dan punya default
     * - name cukup panjang; kolom JSON ada dan nullable
     */
    public function up(): void
    {
        if (!Schema::hasTable('task_templates')) {
            Schema::create('task_templates', function (Blueprint $table) {
                $table->string('task_template_id', 36)->primary();
                $table->string('name', 255);
                $table->string('title', 255)->nullable();
                $table->text('description')->nullable();
                $table->json('checklist')->nullable();
                $table->json('attachments')->nullable();
                $table->string('association', 50)->default('penanaman');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
            return;
        }

        $driver = DB::getDriverName();

        Schema::table('task_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('task_templates', 'name')) {
                $table->string('name', 255)->after('task_template_id');
            }
            if (!Schema::hasColumn('task_templates', 'title')) {
                $table->string('title', 255)->nullable()->after('name');
            }
            if (!Schema::hasColumn('task_templates', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('task_templates', 'checklist')) {
                $table->json('checklist')->nullable()->after('description');
            }
            if (!Schema::hasColumn('task_templates', 'attachments')) {
                $table->json('attachments')->nullable()->after('checklist');
            }
            if (!Schema::hasColumn('task_templates', 'association')) {
                $table->string('association', 50)->default('penanaman')->after('attachments');
            }
            if (!Schema::hasColumn('task_templates', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('association');
            }
            if (!Schema::hasColumn('task_templates', 'created_at')) {
                $table->timestamps();
            }
        });

        // Ubah kolom association dari enum ke string jika perlu (MySQL)
        if ($driver === 'mysql') {
            $cols = DB::select("SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'task_templates' AND COLUMN_NAME = 'association'");
            if (!empty($cols) && ($cols[0]->DATA_TYPE ?? '') === 'enum') {
                DB::statement("ALTER TABLE task_templates MODIFY association VARCHAR(50) NOT NULL DEFAULT 'penanaman'");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak mengembalikan enum; struktur tetap aman
    }
};
