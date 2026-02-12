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
        if (Schema::hasTable('expenses')) {
            Schema::table('expenses', function (Blueprint $table) {
                if (!Schema::hasColumn('expenses', 'responsible_person_id')) {
                    $table->foreignId('responsible_person_id')->nullable()->after('expense_date')->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('expenses')) {
            Schema::table('expenses', function (Blueprint $table) {
                if (Schema::hasColumn('expenses', 'responsible_person_id')) {
                    $table->dropForeign(['responsible_person_id']);
                    $table->dropColumn('responsible_person_id');
                }
            });
        }
    }
};


