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
        Schema::create('inventory_types', function (Blueprint $table) {
            $table->string('inventory_type_id', 36)->primary();
            $table->string('category');
            $table->string('name'); // Nama Varietas/Komoditas
            $table->string('sku')->unique(); // ID Internal / SKU
            $table->string('electronic_id')->nullable(); // Barcode/RFID
            $table->string('unit'); // kg, ton, kantong, unit, polybag, pcs
            $table->decimal('estimated_value_per_unit', 15, 2)->nullable(); // Estimasi nilai per unit (Rp)
            $table->decimal('estimated_kg_per_unit', 10, 2)->nullable(); // Estimasi kg per unit (jika unit adalah kantong)
            $table->boolean('track_individual_lots')->default(false); // Lacak lot individual
            $table->decimal('low_stock_threshold', 10, 2)->nullable(); // Peringatan stok rendah
            $table->string('low_stock_unit')->default('kg'); // Unit untuk peringatan stok
            $table->string('low_stock_email')->nullable(); // Email untuk peringatan
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_types');
    }
};

