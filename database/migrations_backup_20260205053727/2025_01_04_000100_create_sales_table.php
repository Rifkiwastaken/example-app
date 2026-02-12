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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique(); // Nomor Struk/Referensi (PJ-2025-004)
            $table->date('sale_date');
            $table->string('buyer_name'); // Nama Pembeli
            $table->string('buyer_contact')->nullable(); // Kontak Pembeli
            $table->decimal('total_amount', 15, 2); // Total Pembayaran
            $table->enum('payment_method', ['cash', 'transfer_bank'])->default('cash');
            $table->enum('payment_status', ['lunas', 'belum_lunas'])->default('lunas');
            $table->text('notes')->nullable(); // Keterangan
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Dicatat Oleh
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};

