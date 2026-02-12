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
        Schema::create('planting_losses', function (Blueprint $table) {
            $table->string('planting_loss_id', 36)->primary();
            $table->string('planting_id', 36)->cascadeOnDelete();
            $table->date('loss_date');
            $table->decimal('loss_amount', 12, 2);
            $table->string('loss_reason')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planting_losses');
    }
};





