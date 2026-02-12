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
            if (!Schema::hasColumn('plantings', 'estimated_harvest_date')) {
                $table->date('estimated_harvest_date')->nullable()->after('planted_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plantings', function (Blueprint $table) {
            if (Schema::hasColumn('plantings', 'estimated_harvest_date')) {
                $table->dropColumn('estimated_harvest_date');
            }
        });
    }
};

