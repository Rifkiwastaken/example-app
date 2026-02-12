<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FASE 1: Menambahkan kolom custom ID baru tanpa menghapus kolom lama.
     * Tabel Core: users, plant_types, plants, planting_locations, plantings, harvests
     */
    public function up(): void
    {
        // 1. USERS TABLE
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'user_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('user_id', 36)->nullable()->unique()->after('id');
            });
        }

        // 2. PLANT_TYPES TABLE
        if (Schema::hasTable('plant_types') && !Schema::hasColumn('plant_types', 'plant_type_id')) {
            Schema::table('plant_types', function (Blueprint $table) {
                $table->string('plant_type_id', 36)->nullable()->unique()->after('id');
            });
        }

        // 3. PLANTS TABLE
        if (Schema::hasTable('plants') && !Schema::hasColumn('plants', 'plant_id')) {
            Schema::table('plants', function (Blueprint $table) {
                $table->string('plant_id', 36)->nullable()->unique()->after('id');
                // Tambahkan kolom FK baru (temporary)
                $table->string('new_plant_type_id', 36)->nullable()->after('plant_type_id');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
            });
        }

        // 4. PLANTING_LOCATIONS TABLE
        if (Schema::hasTable('planting_locations') && !Schema::hasColumn('planting_locations', 'planting_location_id')) {
            Schema::table('planting_locations', function (Blueprint $table) {
                $table->string('planting_location_id', 36)->nullable()->unique()->after('id');
                // Note: planting_locations tidak memiliki location_id di database aktual
            });
        }

        // 5. PLANTINGS TABLE
        if (Schema::hasTable('plantings') && !Schema::hasColumn('plantings', 'planting_id')) {
            Schema::table('plantings', function (Blueprint $table) {
                $table->string('planting_id', 36)->nullable()->unique()->after('id');
                // Tambahkan kolom FK baru (temporary)
                $table->string('new_plant_id', 36)->nullable()->after('plant_id');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
            });
        }

        // 6. HARVESTS TABLE
        if (Schema::hasTable('harvests') && !Schema::hasColumn('harvests', 'harvest_id')) {
            Schema::table('harvests', function (Blueprint $table) {
                $table->string('harvest_id', 36)->nullable()->unique()->after('id');
                // Tambahkan kolom FK baru (temporary)
                $table->string('new_plant_id', 36)->nullable()->after('plant_id');
                $table->string('new_planting_id', 36)->nullable()->after('planting_id');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
                // harvests menggunakan recorded_by dan edited_by, bukan user_id
                $table->string('new_recorded_by', 36)->nullable()->after('recorded_by');
                $table->string('new_edited_by', 36)->nullable()->after('edited_by');
            });
        }

        // 7. PLANT_NOTES TABLE
        if (Schema::hasTable('plant_notes') && !Schema::hasColumn('plant_notes', 'plant_note_id')) {
            Schema::table('plant_notes', function (Blueprint $table) {
                $table->string('plant_note_id', 36)->nullable()->unique()->after('id');
                $table->string('new_plant_id', 36)->nullable()->after('plant_id');
                $table->string('new_user_id', 36)->nullable()->after('user_id');
            });
        }

        // 8. PLANT_PHOTOS TABLE
        if (Schema::hasTable('plant_photos') && !Schema::hasColumn('plant_photos', 'plant_photo_id')) {
            Schema::table('plant_photos', function (Blueprint $table) {
                $table->string('plant_photo_id', 36)->nullable()->unique()->after('id');
                $table->string('new_plant_id', 36)->nullable()->after('plant_id');
            });
        }

        // 9. PLANTING_LOCATION_NOTES TABLE
        if (Schema::hasTable('planting_location_notes') && !Schema::hasColumn('planting_location_notes', 'planting_location_note_id')) {
            Schema::table('planting_location_notes', function (Blueprint $table) {
                $table->string('planting_location_note_id', 36)->nullable()->unique()->after('id');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
                $table->string('new_user_id', 36)->nullable()->after('user_id');
            });
        }

        // 10. PLANTING_LOCATION_PHOTOS TABLE
        if (Schema::hasTable('planting_location_photos') && !Schema::hasColumn('planting_location_photos', 'planting_location_photo_id')) {
            Schema::table('planting_location_photos', function (Blueprint $table) {
                $table->string('planting_location_photo_id', 36)->nullable()->unique()->after('id');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
            });
        }

        // 11. PLANTING_LOSSES TABLE
        if (Schema::hasTable('planting_losses') && !Schema::hasColumn('planting_losses', 'planting_loss_id')) {
            Schema::table('planting_losses', function (Blueprint $table) {
                $table->string('planting_loss_id', 36)->nullable()->unique()->after('id');
                $table->string('new_planting_id', 36)->nullable()->after('planting_id');
            });
        }

        // 12. LOCATIONS TABLE - SKIP (tidak digunakan di database aktual)
        // if (Schema::hasTable('locations') && !Schema::hasColumn('locations', 'location_id')) {
        //     Schema::table('locations', function (Blueprint $table) {
        //         $table->string('location_id', 36)->nullable()->unique()->after('id');
        //     });
        // }

        // 13. NUTRIENTS TABLE
        if (Schema::hasTable('nutrients') && !Schema::hasColumn('nutrients', 'nutrient_id')) {
            Schema::table('nutrients', function (Blueprint $table) {
                $table->string('nutrient_id', 36)->nullable()->unique()->after('id');
                $table->string('new_planting_id', 36)->nullable()->after('planting_id');
            });
        }

        // 14. TREATMENTS TABLE
        if (Schema::hasTable('treatments') && !Schema::hasColumn('treatments', 'treatment_id')) {
            Schema::table('treatments', function (Blueprint $table) {
                $table->string('treatment_id', 36)->nullable()->unique()->after('id');
                $table->string('new_planting_id', 36)->nullable()->after('planting_id');
            });
        }

        // 15. USER PIVOT TABLES
        if (Schema::hasTable('user_planting_location_land_manager') && !Schema::hasColumn('user_planting_location_land_manager', 'user_planting_location_land_manager_id')) {
            Schema::table('user_planting_location_land_manager', function (Blueprint $table) {
                $table->string('user_planting_location_land_manager_id', 36)->nullable()->unique()->after('id');
                $table->string('new_user_id', 36)->nullable()->after('user_id');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
            });
        }

        if (Schema::hasTable('user_planting_location_land_worker') && !Schema::hasColumn('user_planting_location_land_worker', 'user_planting_location_land_worker_id')) {
            Schema::table('user_planting_location_land_worker', function (Blueprint $table) {
                $table->string('user_planting_location_land_worker_id', 36)->nullable()->unique()->after('id');
                $table->string('new_user_id', 36)->nullable()->after('user_id');
                $table->string('new_planting_location_id', 36)->nullable()->after('planting_location_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop kolom yang ditambahkan (reverse order)
        if (Schema::hasTable('user_planting_location_land_worker')) {
            Schema::table('user_planting_location_land_worker', function (Blueprint $table) {
                $table->dropColumn(['user_planting_location_land_worker_id', 'new_user_id', 'new_planting_location_id']);
            });
        }

        if (Schema::hasTable('user_planting_location_land_manager')) {
            Schema::table('user_planting_location_land_manager', function (Blueprint $table) {
                $table->dropColumn(['user_planting_location_land_manager_id', 'new_user_id', 'new_planting_location_id']);
            });
        }

        if (Schema::hasTable('treatments')) {
            Schema::table('treatments', function (Blueprint $table) {
                $table->dropColumn(['treatment_id', 'new_planting_id']);
            });
        }

        if (Schema::hasTable('nutrients')) {
            Schema::table('nutrients', function (Blueprint $table) {
                $table->dropColumn(['nutrient_id', 'new_planting_id']);
            });
        }

        if (Schema::hasTable('locations')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->dropColumn('location_id');
            });
        }

        if (Schema::hasTable('planting_losses')) {
            Schema::table('planting_losses', function (Blueprint $table) {
                $table->dropColumn(['planting_loss_id', 'new_planting_id']);
            });
        }

        if (Schema::hasTable('planting_location_photos')) {
            Schema::table('planting_location_photos', function (Blueprint $table) {
                $table->dropColumn(['planting_location_photo_id', 'new_planting_location_id']);
            });
        }

        if (Schema::hasTable('planting_location_notes')) {
            Schema::table('planting_location_notes', function (Blueprint $table) {
                $table->dropColumn(['planting_location_note_id', 'new_planting_location_id', 'new_user_id']);
            });
        }

        if (Schema::hasTable('plant_photos')) {
            Schema::table('plant_photos', function (Blueprint $table) {
                $table->dropColumn(['plant_photo_id', 'new_plant_id']);
            });
        }

        if (Schema::hasTable('plant_notes')) {
            Schema::table('plant_notes', function (Blueprint $table) {
                $table->dropColumn(['plant_note_id', 'new_plant_id', 'new_user_id']);
            });
        }

        if (Schema::hasTable('harvests')) {
            Schema::table('harvests', function (Blueprint $table) {
                $table->dropColumn(['harvest_id', 'new_plant_id', 'new_planting_id', 'new_planting_location_id', 'new_recorded_by', 'new_edited_by']);
            });
        }

        if (Schema::hasTable('plantings')) {
            Schema::table('plantings', function (Blueprint $table) {
                $table->dropColumn(['planting_id', 'new_plant_id', 'new_planting_location_id']);
            });
        }

        if (Schema::hasTable('planting_locations')) {
            Schema::table('planting_locations', function (Blueprint $table) {
                $table->dropColumn('planting_location_id');
            });
        }

        if (Schema::hasTable('plants')) {
            Schema::table('plants', function (Blueprint $table) {
                $table->dropColumn(['plant_id', 'new_plant_type_id', 'new_planting_location_id']);
            });
        }

        if (Schema::hasTable('plant_types')) {
            Schema::table('plant_types', function (Blueprint $table) {
                $table->dropColumn('plant_type_id');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }
};
