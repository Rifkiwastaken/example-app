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
        Schema::create('inventory_lots', function (Blueprint $table) {
            $table->string('inventory_lot_id', 36)->primary();
            $table->string('inventory_type_id', 36)->onDelete('cascade');
            $table->string('production_id')->nullable(); // ID Lot dari Produksi (PROD-2024-001)
            $table->date('expiry_date')->nullable(); // Masa edar / kadaluarsa
            $table->enum('status', ['tersedia', 'segera_kadaluarsa', 'kadaluarsa', 'habis'])->default('tersedia');
            $table->decimal('initial_stock', 15, 2)->default(0); // Stok awal
            $table->decimal('current_stock', 15, 2)->default(0); // Stok tersisa
            $table->string('stock_unit')->default('kg');
            $table->string('warehouse_id', 36)->nullable()->onDelete('set null');
            $table->string('bin_id', 36)->nullable()->onDelete('set null');
            $table->string('certification_id', 36)->nullable()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_lots');
    }
};

