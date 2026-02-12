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
        Schema::table('tasks', function (Blueprint $table) {
            $table->timestamp('last_edited_at')->nullable()->after('updated_at');
            $table->foreignId('last_edited_by')->nullable()->after('last_edited_at')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['last_edited_by']);
            $table->dropColumn(['last_edited_at', 'last_edited_by']);
        });
    }
};



