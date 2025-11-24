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
        Schema::table('nutrients', function (Blueprint $table) {
            // Remove nutrient percentage fields
            $table->dropColumn([
                'nitrogen_n',
                'phosphorus_p',
                'potassium_k',
                'magnesium_mg',
                'sulfur_s',
                'calcium_ca',
                'boron_b',
                'copper_cu',
                'iron_fe',
                'manganese_mn',
                'zinc_zn',
            ]);
            
            // Add new fields
            $table->decimal('total_cost', 15, 2)->nullable()->after('application_method');
            $table->string('technician')->nullable()->after('total_cost');
            $table->foreignId('planting_id')->nullable()->constrained('plantings')->onDelete('set null')->after('technician');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nutrients', function (Blueprint $table) {
            // Remove new fields
            $table->dropForeign(['planting_id']);
            $table->dropColumn(['total_cost', 'technician', 'planting_id']);
            
            // Restore nutrient percentage fields
            $table->string('nitrogen_n')->nullable();
            $table->string('phosphorus_p')->nullable();
            $table->string('potassium_k')->nullable();
            $table->string('magnesium_mg')->nullable();
            $table->string('sulfur_s')->nullable();
            $table->string('calcium_ca')->nullable();
            $table->string('boron_b')->nullable();
            $table->string('copper_cu')->nullable();
            $table->string('iron_fe')->nullable();
            $table->string('manganese_mn')->nullable();
            $table->string('zinc_zn')->nullable();
        });
    }
};

