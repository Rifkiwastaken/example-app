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
            $table->id();
            $table->foreignId('planting_location_id')->constrained()->onDelete('cascade');
            $table->string('expense_name'); // Nama pengeluaran
            $table->decimal('amount', 10, 2); // Jumlah pengeluaran
            $table->enum('expense_type', ['perawatan', 'nutrisi']); // Tipe pengeluaran
            $table->date('expense_date'); // Tanggal pengeluaran
            $table->foreignId('treatment_id')->nullable()->constrained()->onDelete('cascade'); // FK ke treatments jika tipe perawatan
            $table->foreignId('nutrient_id')->nullable()->constrained()->onDelete('cascade'); // FK ke nutrients jika tipe nutrisi
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



