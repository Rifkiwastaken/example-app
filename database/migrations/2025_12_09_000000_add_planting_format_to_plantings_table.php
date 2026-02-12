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
        Schema::table('plantings', function (Blueprint $table) {
            if (!Schema::hasColumn('plantings', 'planting_format')) {
                $table->string('planting_format')->nullable()->after('estimated_harvest_date');
            }
            if (!Schema::hasColumn('plantings', 'planting_format_custom')) {
                $table->string('planting_format_custom')->nullable()->after('planting_format');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plantings', function (Blueprint $table) {
            if (Schema::hasColumn('plantings', 'planting_format_custom')) {
                $table->dropColumn('planting_format_custom');
            }
            if (Schema::hasColumn('plantings', 'planting_format')) {
                $table->dropColumn('planting_format');
            }
        });
    }
};

