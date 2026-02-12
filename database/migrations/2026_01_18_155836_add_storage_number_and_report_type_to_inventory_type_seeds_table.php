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
        Schema::table('inventory_type_seeds', function (Blueprint $table) {
            $table->string('storage_number', 50)->nullable()->after('filled_by_user_id')->comment('Nomor penyimpanan (dapat diedit oleh user)');
            $table->string('report_type', 50)->nullable()->after('storage_number')->comment('Jenis laporan BPSB');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_type_seeds', function (Blueprint $table) {
            $table->dropColumn(['storage_number', 'report_type']);
        });
    }
};
