<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock_packaging') && ! Schema::hasColumn('stock_packaging', 'hold_for_recert')) {
            Schema::table('stock_packaging', function (Blueprint $table) {
                $table->boolean('hold_for_recert')->default(false)->after('status_kemasan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('stock_packaging') && Schema::hasColumn('stock_packaging', 'hold_for_recert')) {
            Schema::table('stock_packaging', function (Blueprint $table) {
                $table->dropColumn('hold_for_recert');
            });
        }
    }
};
