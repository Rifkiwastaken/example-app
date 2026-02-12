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
        Schema::create('bins', function (Blueprint $table) {
            $table->string('bin_id', 36)->primary();
            $table->string('warehouse_id', 36)->onDelete('cascade');
            $table->string('name');
            $table->string('internal_id');
            $table->decimal('max_capacity', 15, 2);
            $table->string('capacity_unit')->default('kg');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['warehouse_id', 'internal_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bins');
    }
};

