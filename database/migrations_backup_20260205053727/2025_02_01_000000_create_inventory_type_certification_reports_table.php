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
        if (!Schema::hasTable('inventory_type_certification_reports')) {
            Schema::create('inventory_type_certification_reports', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('inventory_type_id');
                $table->unsignedBigInteger('certification_report_id');
                $table->decimal('quantity', 12, 2)->comment('Jumlah benih yang ditambahkan ke stok bibit');
                $table->timestamps();

                $table->foreign('inventory_type_id', 'inv_type_cert_reports_inv_type_fk')
                    ->references('id')
                    ->on('inventory_types')
                    ->onDelete('cascade');
                
                $table->foreign('certification_report_id', 'inv_type_cert_reports_cert_fk')
                    ->references('id')
                    ->on('certification_reports')
                    ->onDelete('cascade');

                $table->unique(['inventory_type_id', 'certification_report_id'], 'inv_type_cert_report_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_type_certification_reports');
    }
};

