<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus tabel legacy jika masih ada
        Schema::dropIfExists('warehouse_types');
    }

    public function down(): void
    {
        // Tidak dikembalikan otomatis karena tabel ini sudah tidak dipakai.
    }
};

