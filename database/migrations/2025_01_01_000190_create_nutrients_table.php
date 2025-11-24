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
        Schema::create('nutrients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planting_location_id')->constrained()->onDelete('cascade');
            $table->string('product_applied');
            $table->decimal('amount_applied', 10, 2);
            $table->string('application_method'); // Penyebaran, Kompos, Granul, etc.
            $table->date('application_date');
            
            // Nutrient values
            $table->decimal('nitrogen_n', 8, 2)->nullable();
            $table->decimal('phosphorus_p', 8, 2)->nullable();
            $table->decimal('potassium_k', 8, 2)->nullable();
            $table->decimal('magnesium_mg', 8, 2)->nullable();
            $table->decimal('sulfur_s', 8, 2)->nullable();
            $table->decimal('calcium_ca', 8, 2)->nullable();
            $table->decimal('boron_b', 8, 2)->nullable();
            $table->decimal('copper_cu', 8, 2)->nullable();
            $table->decimal('iron_fe', 8, 2)->nullable();
            $table->decimal('manganese_mn', 8, 2)->nullable();
            $table->decimal('zinc_zn', 8, 2)->nullable();
            
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nutrients');
    }
};













