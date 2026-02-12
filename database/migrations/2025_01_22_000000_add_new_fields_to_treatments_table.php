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
            if (!Schema::hasColumn('treatments', 'treatment_name')) {
                $table->string('treatment_name')->nullable()->after('treatment_id');
            }
            if (!Schema::hasColumn('treatments', 'responsible_person_id')) {
                $table->foreignId('responsible_person_id')->nullable()->after('planting_location_id')->onDelete('set null');
            }
            if (!Schema::hasColumn('treatments', 'institution_source')) {
                $table->string('institution_source')->nullable()->after('technician');
            }
            if (!Schema::hasColumn('treatments', 'attachment')) {
                $table->string('attachment')->nullable()->after('institution_source');
            }
        });
        
        // Drop foreign key first if it exists, then drop columns
        if (Schema::hasColumn('treatments', 'subtract_from_inventory')) {
            // Try to drop foreign key - Laravel will handle if it doesn't exist
            try {
                Schema::table('treatments', function (Blueprint $table) {
                    $table->dropForeign(['subtract_from_inventory']);
                });
            } catch (\Exception $e) {
                // Try with standard naming convention
                try {
                    Schema::table('treatments', function (Blueprint $table) {
                        $table->dropForeign('treatments_subtract_from_inventory_foreign');
                    });
                } catch (\Exception $e2) {
                    // Foreign key might not exist, continue
                }
            }
        }
        
        // Drop columns
        Schema::table('treatments', function (Blueprint $table) {
            // Remove fields that are no longer needed
            if (Schema::hasColumn('treatments', 'inventory_amount_used')) {
                $table->dropColumn('inventory_amount_used');
            }
            if (Schema::hasColumn('treatments', 'inventory_unit')) {
                $table->dropColumn('inventory_unit');
            }
            if (Schema::hasColumn('treatments', 'subtract_from_inventory')) {
                $table->dropColumn('subtract_from_inventory');
            }
            if (Schema::hasColumn('treatments', 'record_expense')) {
                $table->dropColumn('record_expense');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->dropColumn(['treatment_name', 'responsible_person_id', 'institution_source', 'attachment']);
            $table->decimal('inventory_amount_used', 10, 2)->nullable();
            $table->string('inventory_unit')->nullable();
            $table->foreignId('subtract_from_inventory')->nullable()->onDelete('set null');
            $table->boolean('record_expense')->default(false);
        });
    }
};

