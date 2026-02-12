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
        Schema::table('inventory_type_seeds', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_type_seeds', 'seed_unit')) {
                $table->string('seed_unit')->nullable()->after('quantity')->comment('Satuan benih (kg, ton, kuintal, karung, sak, liter)');
            }
            if (!Schema::hasColumn('inventory_type_seeds', 'seed_unit_quantity')) {
                $table->decimal('seed_unit_quantity', 12, 2)->nullable()->after('seed_unit')->comment('Jumlah satuan benih');
            }
            if (!Schema::hasColumn('inventory_type_seeds', 'seed_per_unit')) {
                $table->decimal('seed_per_unit', 12, 2)->nullable()->after('seed_unit_quantity')->comment('Jumlah benih per satuan benih');
            }
            if (!Schema::hasColumn('inventory_type_seeds', 'seed_per_unit_unit')) {
                $table->string('seed_per_unit_unit')->nullable()->after('seed_per_unit')->comment('Satuan untuk jumlah benih per satuan benih');
            }
            if (!Schema::hasColumn('inventory_type_seeds', 'total_seed_quantity')) {
                $table->decimal('total_seed_quantity', 12, 2)->nullable()->after('seed_per_unit_unit')->comment('Jumlah benih total');
            }
            if (!Schema::hasColumn('inventory_type_seeds', 'total_seed_unit')) {
                $table->string('total_seed_unit')->nullable()->after('total_seed_quantity')->comment('Satuan untuk jumlah benih total');
            }
            if (!Schema::hasColumn('inventory_type_seeds', 'edited_at')) {
                $table->timestamp('edited_at')->nullable()->after('filled_by_user_id');
            }
            if (!Schema::hasColumn('inventory_type_seeds', 'edited_by')) {
                $table->foreignId('edited_by')->nullable()->after('edited_at')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_type_seeds', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_type_seeds', 'edited_by')) {
                $table->dropForeign(['edited_by']);
                $table->dropColumn('edited_by');
            }
            if (Schema::hasColumn('inventory_type_seeds', 'edited_at')) {
                $table->dropColumn('edited_at');
            }
            if (Schema::hasColumn('inventory_type_seeds', 'total_seed_unit')) {
                $table->dropColumn('total_seed_unit');
            }
            if (Schema::hasColumn('inventory_type_seeds', 'total_seed_quantity')) {
                $table->dropColumn('total_seed_quantity');
            }
            if (Schema::hasColumn('inventory_type_seeds', 'seed_per_unit_unit')) {
                $table->dropColumn('seed_per_unit_unit');
            }
            if (Schema::hasColumn('inventory_type_seeds', 'seed_per_unit')) {
                $table->dropColumn('seed_per_unit');
            }
            if (Schema::hasColumn('inventory_type_seeds', 'seed_unit_quantity')) {
                $table->dropColumn('seed_unit_quantity');
            }
            if (Schema::hasColumn('inventory_type_seeds', 'seed_unit')) {
                $table->dropColumn('seed_unit');
            }
        });
    }
};

