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
        Schema::table('treatments', function (Blueprint $table) {
            if (!Schema::hasColumn('treatments', 'subtract_from_inventory')) {
                $table->foreignId('subtract_from_inventory')->nullable()->after('product_detail')->constrained('inventory_types')->onDelete('set null');
            }
            if (!Schema::hasColumn('treatments', 'batch_number')) {
                $table->string('batch_number')->nullable()->after('technician');
            }
            if (!Schema::hasColumn('treatments', 'inventory_amount_used')) {
                $table->decimal('inventory_amount_used', 10, 2)->nullable()->after('amount_applied');
            }
            if (!Schema::hasColumn('treatments', 'inventory_unit')) {
                $table->string('inventory_unit')->nullable()->after('inventory_amount_used');
            }
            if (!Schema::hasColumn('treatments', 'retreat_date')) {
                $table->date('retreat_date')->nullable()->after('treatment_date');
            }
            if (!Schema::hasColumn('treatments', 'planting_id')) {
                $table->foreignId('planting_id')->nullable()->after('planting_location_id')->constrained('plantings')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            if (Schema::hasColumn('treatments', 'subtract_from_inventory')) {
                $table->dropForeign(['subtract_from_inventory']);
                $table->dropColumn('subtract_from_inventory');
            }
            if (Schema::hasColumn('treatments', 'batch_number')) {
                $table->dropColumn('batch_number');
            }
            if (Schema::hasColumn('treatments', 'inventory_amount_used')) {
                $table->dropColumn('inventory_amount_used');
            }
            if (Schema::hasColumn('treatments', 'inventory_unit')) {
                $table->dropColumn('inventory_unit');
            }
            if (Schema::hasColumn('treatments', 'retreat_date')) {
                $table->dropColumn('retreat_date');
            }
            if (Schema::hasColumn('treatments', 'planting_id')) {
                $table->dropForeign(['planting_id']);
                $table->dropColumn('planting_id');
            }
        });
    }
};









