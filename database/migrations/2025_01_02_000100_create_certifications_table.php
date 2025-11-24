<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('harvest_id')->constrained('harvests')->cascadeOnDelete();
            $table->string('certification_status')->default('dalam_proses'); // dalam_proses, lulus, tidak_lulus, selesai
            $table->string('seed_class_requested')->nullable(); // BS, BP, BR
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};











