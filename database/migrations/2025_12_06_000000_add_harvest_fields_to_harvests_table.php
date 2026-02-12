<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            $table->string('harvest_unit')->nullable()->after('unit');
            $table->decimal('unit_quantity', 12, 2)->nullable()->after('harvest_unit');
            $table->decimal('quantity_per_unit', 12, 2)->nullable()->after('unit_quantity');
            $table->foreignId('recorded_by')->nullable()->after('quantity_per_unit')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            $table->dropForeign(['recorded_by']);
            $table->dropColumn(['harvest_unit', 'unit_quantity', 'quantity_per_unit', 'recorded_by']);
        });
    }
};


