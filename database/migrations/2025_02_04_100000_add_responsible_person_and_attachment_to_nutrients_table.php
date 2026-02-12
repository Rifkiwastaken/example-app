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
            if (!Schema::hasColumn('nutrients', 'responsible_person_id')) {
                $table->foreignId('responsible_person_id')->nullable()->after('institution_source')->onDelete('set null');
            }
            if (!Schema::hasColumn('nutrients', 'attachment')) {
                $table->string('attachment')->nullable()->after('responsible_person_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nutrients', function (Blueprint $table) {
            if (Schema::hasColumn('nutrients', 'attachment')) {
                $table->dropColumn('attachment');
            }
            if (Schema::hasColumn('nutrients', 'responsible_person_id')) {
                $table->dropForeign(['responsible_person_id']);
                $table->dropColumn('responsible_person_id');
            }
        });
    }
};

