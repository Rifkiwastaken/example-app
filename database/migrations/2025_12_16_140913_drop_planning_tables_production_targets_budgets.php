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
        // Drop tables in correct order to handle foreign key constraints
        // Drop budget_items first (likely has FK to budgets)
        Schema::dropIfExists('budget_items');
        
        // Drop budgets table
        Schema::dropIfExists('budgets');
        
        // Drop production_targets table
        Schema::dropIfExists('production_targets');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: We cannot recreate these tables without knowing their original structure
        // If needed, restore from backup or recreate manually based on original migration files
    }
};
