<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_histories', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_histories', 'stock_id')) {
                $table->unsignedBigInteger('stock_id')->nullable()->after('inventory_type_id');
            }
            if (! Schema::hasColumn('stock_histories', 'stock_packaging_id')) {
                $table->unsignedBigInteger('stock_packaging_id')->nullable()->after('stock_id');
            }
            if (! Schema::hasColumn('stock_histories', 'seed_varieties_id')) {
                $table->string('seed_varieties_id', 36)->nullable()->after('stock_packaging_id');
            }
        });

        Schema::table('stock_histories', function (Blueprint $table) {
            $table->index('stock_id');
            $table->index('stock_packaging_id');
            $table->index('seed_varieties_id');
        });
    }

    public function down(): void
    {
        Schema::table('stock_histories', function (Blueprint $table) {
            $table->dropIndex(['stock_id']);
            $table->dropIndex(['stock_packaging_id']);
            $table->dropIndex(['seed_varieties_id']);
            $table->dropColumn(['stock_id', 'stock_packaging_id', 'seed_varieties_id']);
        });
    }
};
