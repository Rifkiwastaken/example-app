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
        Schema::table('treatments', function (Blueprint $table) {
            if (!Schema::hasColumn('treatments', 'edited_at')) {
                $table->timestamp('edited_at')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('treatments', 'edited_by')) {
                $table->foreignId('edited_by')->nullable()->after('edited_at')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            if (Schema::hasColumn('treatments', 'edited_by')) {
                $table->dropForeign(['edited_by']);
                $table->dropColumn('edited_by');
            }
            if (Schema::hasColumn('treatments', 'edited_at')) {
                $table->dropColumn('edited_at');
            }
        });
    }
};

