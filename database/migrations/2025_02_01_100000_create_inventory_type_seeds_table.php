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
        if (!Schema::hasTable('inventory_type_seeds')) {
            Schema::create('inventory_type_seeds', function (Blueprint $table) {
                $table->string('inventory_type_seed_id', 36)->primary();
                $table->string('inventory_type_id', 36);
                $table->string('plant_id', 36);
                $table->string('planting_location_id', 36);
                $table->decimal('quantity', 12, 2)->comment('Jumlah benih yang ditambahkan');
                $table->decimal('estimated_sale_price_per_kg', 12, 2)->nullable()->comment('Estimasi penjualan per kg');
                $table->date('expiry_date')->nullable()->comment('Tanggal kadaluarsa');
                $table->string('filled_by_user_id', 36)->nullable()->comment('User yang mengisi data');
                $table->timestamps();

                $table->foreign('inventory_type_id', 'inv_type_seeds_inv_type_fk')
                    ->references('inventory_type_id')
                    ->on('inventory_types')
                    ->onDelete('cascade');
                
                $table->foreign('plant_id', 'inv_type_seeds_plant_fk')
                    ->references('plant_id')
                    ->on('plants')
                    ->onDelete('cascade');
                
                $table->foreign('planting_location_id', 'inv_type_seeds_location_fk')
                    ->references('planting_location_id')
                    ->on('planting_locations')
                    ->onDelete('cascade');
                
                $table->foreign('filled_by_user_id', 'inv_type_seeds_user_fk')
                    ->references('user_id')
                    ->on('users')
                    ->onDelete('set null');

                $table->index(['inventory_type_id', 'plant_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_type_seeds');
    }
};

