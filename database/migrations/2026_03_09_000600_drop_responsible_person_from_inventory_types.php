<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventory_types')) {
            return;
        }

        Schema::table('inventory_types', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_types', 'responsible_person_id')) {
                $table->dropColumn('responsible_person_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('inventory_types')) {
            return;
        }

        Schema::table('inventory_types', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_types', 'responsible_person_id')) {
                $table->string('responsible_person_id', 36)->nullable()->after('plant_id');
            }
        });
    }
};

