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
        Schema::table('inventory_types', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_types', 'plant_id')) {
                $table->string('plant_id', 36)->nullable()->after('inventory_type_id')->foreign('plant_id')->references('plant_id')->on('plants')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_types', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_types', 'plant_id')) {
                $table->dropForeign(['plant_id']);
                $table->dropColumn('plant_id');
            }
        });
    }
};




















