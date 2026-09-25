<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus semua data pada tabel: stok benih, riwayat sertifikasi, dan data panen.
     * Urutan penghapusan mengikuti ketergantungan foreign key (anak dulu, lalu induk).
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        }

        $tables = [
            'stock_histories',
            'inventory_type_seeds',
            'warehouse_type',
            'inventory_transactions',
            'inventory_notes',
            'inventory_photos',
            'warehouse_lots',
            'certification_reports',
            'certifications',
            'inventory_types',
            'harvests',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }

    /**
     * Reverse tidak mengembalikan data; migration ini hanya untuk pembersihan.
     */
    public function down(): void
    {
        // Tidak ada rollback – data yang dihapus tidak bisa dikembalikan
    }
};
