<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plantings', function (Blueprint $table) {
            $table->boolean('is_completed')->default(false)->after('planted_at');
        });
    }

    public function down(): void
    {
        Schema::table('plantings', function (Blueprint $table) {
            $table->dropColumn('is_completed');
        });
    }
};


