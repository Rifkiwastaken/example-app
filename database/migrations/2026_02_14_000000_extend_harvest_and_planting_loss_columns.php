<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Kolom di harvests dan planting_losses dibatasi 50 karakter sehingga
     * data panen dan kehilangan gagal disimpan saat input lebih panjang.
     */
    public function up(): void
    {
        if (Schema::hasTable('harvests')) {
            DB::statement('ALTER TABLE `harvests` MODIFY COLUMN `batch_no` VARCHAR(255) NULL');
            DB::statement('ALTER TABLE `harvests` MODIFY COLUMN `source` VARCHAR(255) NULL');
            DB::statement('ALTER TABLE `harvests` MODIFY COLUMN `quality` VARCHAR(255) NULL');
            DB::statement('ALTER TABLE `harvests` MODIFY COLUMN `unit` VARCHAR(255) NULL');
            DB::statement('ALTER TABLE `harvests` MODIFY COLUMN `harvest_unit` VARCHAR(255) NULL');
        }

        if (Schema::hasTable('planting_losses')) {
            DB::statement('ALTER TABLE `planting_losses` MODIFY COLUMN `loss_reason` VARCHAR(255) NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('harvests')) {
            DB::statement('ALTER TABLE `harvests` MODIFY COLUMN `batch_no` VARCHAR(50) NULL');
            DB::statement('ALTER TABLE `harvests` MODIFY COLUMN `source` VARCHAR(50) NULL');
            DB::statement('ALTER TABLE `harvests` MODIFY COLUMN `quality` VARCHAR(50) NULL');
            DB::statement('ALTER TABLE `harvests` MODIFY COLUMN `unit` VARCHAR(50) NULL');
            DB::statement('ALTER TABLE `harvests` MODIFY COLUMN `harvest_unit` VARCHAR(50) NULL');
        }

        if (Schema::hasTable('planting_losses')) {
            DB::statement('ALTER TABLE `planting_losses` MODIFY COLUMN `loss_reason` VARCHAR(50) NULL');
        }
    }
};
