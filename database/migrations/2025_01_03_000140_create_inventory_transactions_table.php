<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->string('inventory_transaction_id', 36)->primary();
            $table->string('inventory_type_id', 36)->onDelete('cascade');
            $table->string('inventory_lot_id', 36)->nullable()->onDelete('set null');
            $table->enum('transaction_type', ['stok_masuk', 'stok_keluar', 'penyesuaian_tambah', 'penyesuaian_kurang', 'distribusi', 'pindah_lokasi']);
            $table->decimal('quantity', 15, 2);
            $table->string('unit');
            $table->string('warehouse_id', 36)->nullable()->onDelete('set null');
            $table->string('bin_id', 36)->nullable()->onDelete('set null');
            $table->string('reason')->nullable(); // Alasan (Rusak/Spoilage, Hilang, dll)
            $table->text('notes')->nullable();
            $table->string('user_id', 36)->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};

