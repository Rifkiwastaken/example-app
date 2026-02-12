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
        Schema::table('warehouses', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouses', 'responsible_person_id')) {
                $table->foreignId('responsible_person_id')->nullable()->after('description')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            if (Schema::hasColumn('warehouses', 'responsible_person_id')) {
                $table->dropForeign(['responsible_person_id']);
                $table->dropColumn('responsible_person_id');
            }
        });
    }
};

