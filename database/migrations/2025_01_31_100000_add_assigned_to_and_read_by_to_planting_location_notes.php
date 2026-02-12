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
        Schema::table('planting_location_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('planting_location_notes', 'assigned_to')) {
                $table->json('assigned_to')->nullable()->after('user_id'); // Array of user IDs
            }
            if (!Schema::hasColumn('planting_location_notes', 'read_by')) {
                $table->json('read_by')->nullable()->after('assigned_to'); // Array of user IDs who have read the note
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planting_location_notes', function (Blueprint $table) {
            if (Schema::hasColumn('planting_location_notes', 'read_by')) {
                $table->dropColumn('read_by');
            }
            if (Schema::hasColumn('planting_location_notes', 'assigned_to')) {
                $table->dropColumn('assigned_to');
            }
        });
    }
};

