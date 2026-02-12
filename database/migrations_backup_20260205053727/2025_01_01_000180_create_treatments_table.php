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
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planting_location_id')->constrained()->onDelete('cascade');
            $table->string('treatment_type'); // Blight, Pupuk, Jamur, etc.
            $table->string('product_detail')->nullable();
            $table->string('opt_institution')->nullable();
            $table->string('application_method'); // Granul, Semprot, Lainnya
            $table->integer('withholding_period_days')->nullable();
            $table->string('technician')->nullable();
            $table->text('description')->nullable();
            $table->date('treatment_date');
            $table->string('treatment_location')->nullable(); // batang, daun, pohon
            $table->decimal('amount_applied', 10, 2)->nullable();
            $table->string('unit_measurement')->nullable(); // Bale, Gram, Kilogram, etc.
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->boolean('record_expense')->default(false);
            $table->string('keywords')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
















