<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plant_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable(); // pangan, hortikultura-sayur/buah/hias, dll
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plant_types');
    }
};


















