<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus tabel planting_location_photos.
     * Ubah nama tabel seed_histories menjadi stock_histories dan kolom PK seed_history_id menjadi stock_history_id.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // 1. Drop table planting_location_photos
        Schema::dropIfExists('planting_location_photos');

        // 2. Rename seed_histories -> stock_histories and PK column
        if (Schema::hasTable('seed_histories')) {
            DB::statement('RENAME TABLE `seed_histories` TO `stock_histories`');
            if (Schema::hasColumn('stock_histories', 'seed_history_id')) {
                DB::statement('ALTER TABLE `stock_histories` CHANGE `seed_history_id` `stock_history_id` VARCHAR(36) NOT NULL');
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        if (Schema::hasTable('stock_histories')) {
            if (Schema::hasColumn('stock_histories', 'stock_history_id')) {
                DB::statement('ALTER TABLE `stock_histories` CHANGE `stock_history_id` `seed_history_id` VARCHAR(36) NOT NULL');
            }
            DB::statement('RENAME TABLE `stock_histories` TO `seed_histories`');
        }
        // planting_location_photos tidak di-create kembali di down (recreate would need full schema)
    }
};
