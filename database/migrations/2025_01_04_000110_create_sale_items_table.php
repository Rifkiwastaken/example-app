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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->string('sale_item_id', 36)->primary();
            $table->string('sale_id', 36)->onDelete('cascade');
            $table->string('inventory_type_id', 36)->onDelete('cascade'); // Pilih Benih
            $table->string('inventory_lot_id', 36)->nullable()->onDelete('set null'); // Pilih Lot/Batch
            $table->decimal('quantity', 15, 2); // Jumlah Jual
            $table->string('unit'); // Satuan (kg, ton, dll)
            $table->decimal('unit_price', 15, 2); // Harga Satuan (Rp)
            $table->decimal('subtotal', 15, 2); // Subtotal (Rp)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};

