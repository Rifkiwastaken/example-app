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
        Schema::table('nutrients', function (Blueprint $table) {
            if (!Schema::hasColumn('nutrients', 'unit')) {
                $table->string('unit')->nullable()->after('amount_applied');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nutrients', function (Blueprint $table) {
            if (Schema::hasColumn('nutrients', 'unit')) {
                $table->dropColumn('unit');
            }
        });
    }
};












