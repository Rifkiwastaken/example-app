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
        if (!Schema::hasTable('budget_items')) {
            Schema::create('budget_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('budget_id')->constrained('budgets')->cascadeOnDelete();
                $table->string('account_code'); // Kode Rekening Sub (01.01.0012, dll)
                $table->string('description'); // Uraian Kegiatan (Belanja Bahan Pupuk)
                $table->decimal('budgeted_amount', 15, 2); // Pagu Anggaran (Rp)
                $table->decimal('realized_amount', 15, 2)->default(0); // Realisasi (Rp) - diisi dari input pengeluaran
                $table->string('fund_source')->nullable(); // Sumber Dana (APBD, dll)
                $table->foreignId('parent_id')->nullable()->constrained('budget_items')->nullOnDelete(); // Untuk hierarki
                $table->integer('level')->default(0); // Level dalam hierarki (0=parent, 1=child, dll)
                $table->integer('order')->default(0); // Urutan tampil
                $table->timestamps();
                
                $table->index(['budget_id', 'parent_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_items');
    }
};
