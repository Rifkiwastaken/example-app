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
        if (!Schema::hasTable('budgets')) {
            Schema::create('budgets', function (Blueprint $table) {
                $table->id();
                $table->year('fiscal_year'); // Tahun Anggaran
                $table->string('account_code')->nullable(); // Kode Rekening Induk (5.1.02, dll)
                $table->string('account_name')->nullable(); // Nama Rekening Induk
                $table->timestamps();
                
                $table->unique(['fiscal_year', 'account_code']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};

