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
        Schema::table('certification_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('certification_reports', 'certified_seed_quantity')) {
                $table->decimal('certified_seed_quantity', 12, 2)->nullable()->after('expiry_date')->comment('Jumlah benih yang lulus sertifikasi dalam kg');
            }
            if (!Schema::hasColumn('certification_reports', 'estimated_sale_price_per_kg')) {
                $table->decimal('estimated_sale_price_per_kg', 12, 2)->nullable()->after('certified_seed_quantity')->comment('Estimasi penjualan per kg');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certification_reports', function (Blueprint $table) {
            if (Schema::hasColumn('certification_reports', 'estimated_sale_price_per_kg')) {
                $table->dropColumn('estimated_sale_price_per_kg');
            }
            if (Schema::hasColumn('certification_reports', 'certified_seed_quantity')) {
                $table->dropColumn('certified_seed_quantity');
            }
        });
    }
};



