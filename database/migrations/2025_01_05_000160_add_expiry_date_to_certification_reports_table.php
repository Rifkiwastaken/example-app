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
            if (!Schema::hasColumn('certification_reports', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('estimated_yield');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certification_reports', function (Blueprint $table) {
            if (Schema::hasColumn('certification_reports', 'expiry_date')) {
                $table->dropColumn('expiry_date');
            }
        });
    }
};












