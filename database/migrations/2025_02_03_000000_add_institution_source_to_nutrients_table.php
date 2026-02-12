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
            if (!Schema::hasColumn('nutrients', 'institution_source')) {
                $table->string('institution_source')->nullable()->after('technician');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nutrients', function (Blueprint $table) {
            if (Schema::hasColumn('nutrients', 'institution_source')) {
                $table->dropColumn('institution_source');
            }
        });
    }
};

