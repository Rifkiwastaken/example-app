<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - Tambah kolom planting_amount dan progress di plantings.
     * - Hapus kolom status dan progress dari plants.
     */
    public function up(): void
    {
        if (Schema::hasTable('plantings')) {
            Schema::table('plantings', function (Blueprint $table) {
                if (!Schema::hasColumn('plantings', 'planting_amount')) {
                    $table->decimal('planting_amount', 12, 2)->nullable()->after('bed_label');
                }
                if (!Schema::hasColumn('plantings', 'progress')) {
                    $table->unsignedTinyInteger('progress')->default(0)->after('planting_amount')->comment('0..100');
                }
            });
        }

        if (Schema::hasTable('plants')) {
            Schema::table('plants', function (Blueprint $table) {
                if (Schema::hasColumn('plants', 'status')) {
                    $table->dropColumn('status');
                }
                if (Schema::hasColumn('plants', 'progress')) {
                    $table->dropColumn('progress');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('plantings')) {
            Schema::table('plantings', function (Blueprint $table) {
                if (Schema::hasColumn('plantings', 'planting_amount')) {
                    $table->dropColumn('planting_amount');
                }
                if (Schema::hasColumn('plantings', 'progress')) {
                    $table->dropColumn('progress');
                }
            });
        }
        if (Schema::hasTable('plants')) {
            Schema::table('plants', function (Blueprint $table) {
                if (!Schema::hasColumn('plants', 'status')) {
                    $table->enum('status', ['perencanaan', 'ditanam', 'dipanen', 'selesai'])->default('perencanaan')->after('variety');
                }
                if (!Schema::hasColumn('plants', 'progress')) {
                    $table->unsignedTinyInteger('progress')->default(0)->after('status');
                }
            });
        }
    }
};
