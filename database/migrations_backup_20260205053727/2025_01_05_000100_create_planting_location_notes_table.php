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
        if (!Schema::hasTable('planting_location_notes')) {
            Schema::create('planting_location_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('planting_location_id')->constrained('planting_locations')->onDelete('cascade');
                $table->string('title')->nullable();
                $table->text('description');
                $table->date('note_date');
                $table->string('keywords')->nullable();
                $table->string('attachment_path')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planting_location_notes');
    }
};

