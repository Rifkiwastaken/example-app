<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plants', function (Blueprint $table) {
            if (!app('db')->getSchemaBuilder()->hasColumn('plants', 'planting_location_id')) {
                $table->foreignId('planting_location_id')->nullable();
            }
            $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('plants', function (Blueprint $table) {
            $table->dropForeign(['planting_location_id']);
        });
    }
};


















