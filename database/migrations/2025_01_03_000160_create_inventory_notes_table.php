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
        Schema::create('inventory_notes', function (Blueprint $table) {
            $table->string('inventory_note_id', 36)->primary();
            $table->string('inventory_type_id', 36)->onDelete('cascade');
            $table->text('content');
            $table->string('user_id', 36)->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_notes');
    }
};

