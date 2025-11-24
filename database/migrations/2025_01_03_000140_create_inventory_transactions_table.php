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
            $table->id();
            $table->foreignId('inventory_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('inventory_lot_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('transaction_type', ['stok_masuk', 'stok_keluar', 'penyesuaian_tambah', 'penyesuaian_kurang', 'distribusi', 'pindah_lokasi']);
            $table->decimal('quantity', 15, 2);
            $table->string('unit');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('bin_id')->nullable()->constrained()->onDelete('set null');
            $table->string('reason')->nullable(); // Alasan (Rusak/Spoilage, Hilang, dll)
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
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

