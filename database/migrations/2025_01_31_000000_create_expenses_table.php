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
        Schema::create('expenses', function (Blueprint $table) {
            $table->string('expense_id', 36)->primary();
            $table->string('planting_location_id', 36)->onDelete('cascade');
            $table->string('expense_name'); // Nama pengeluaran
            $table->decimal('amount', 10, 2); // Jumlah pengeluaran
            $table->enum('expense_type', ['perawatan', 'nutrisi']); // Tipe pengeluaran
            $table->date('expense_date'); // Tanggal pengeluaran
            $table->string('treatment_id', 36)->nullable()->onDelete('cascade'); // FK ke treatments jika tipe perawatan
            $table->string('nutrient_id', 36)->nullable()->onDelete('cascade'); // FK ke nutrients jika tipe nutrisi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};



