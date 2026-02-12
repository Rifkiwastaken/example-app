<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FASE 3: Finalisasi - Drop kolom lama, jadikan kolom baru sebagai PK, update FK.
     * Tabel Core: users, plant_types, plants, planting_locations, plantings, harvests
     */
    public function up(): void
    {
        // IMPORTANT: Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // 1. USERS TABLE
            echo "Finalizing users table...\n";
            Schema::table('users', function (Blueprint $table) {
                $table->dropPrimary(['id']);
                $table->dropColumn('id');
            });
            
            Schema::table('users', function (Blueprint $table) {
                $table->string('user_id', 36)->primary()->change();
            });

            // 2. PLANT_TYPES TABLE
            echo "Finalizing plant_types table...\n";
            Schema::table('plant_types', function (Blueprint $table) {
                $table->dropPrimary(['id']);
                $table->dropColumn('id');
            });
            
            Schema::table('plant_types', function (Blueprint $table) {
                $table->string('plant_type_id', 36)->primary()->change();
            });

            // 3. LOCATIONS TABLE
            if (Schema::hasTable('locations')) {
                echo "Finalizing locations table...\n";
                Schema::table('locations', function (Blueprint $table) {
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('locations', function (Blueprint $table) {
                    $table->string('location_id', 36)->primary()->change();
                });
            }

            // 4. PLANTING_LOCATIONS TABLE
            echo "Finalizing planting_locations table...\n";
            // Drop old FK first
            Schema::table('planting_locations', function (Blueprint $table) {
                $table->dropForeign(['location_id']);
                $table->dropColumn('location_id');
                $table->dropPrimary(['id']);
                $table->dropColumn('id');
            });
            
            // Rename new columns and set PK
            Schema::table('planting_locations', function (Blueprint $table) {
                $table->string('planting_location_id', 36)->primary()->change();
                $table->renameColumn('new_location_id', 'location_id');
            });
            
            // Add FK constraint
            if (Schema::hasTable('locations')) {
                Schema::table('planting_locations', function (Blueprint $table) {
                    $table->foreign('location_id')->references('location_id')->on('locations')->nullOnDelete();
                });
            }

            // 5. PLANTS TABLE
            echo "Finalizing plants table...\n";
            // Drop old FKs
            Schema::table('plants', function (Blueprint $table) {
                $table->dropForeign(['plant_type_id']);
                $table->dropColumn('plant_type_id');
                $table->dropColumn('planting_location_id'); // No FK constraint on this
                $table->dropPrimary(['id']);
                $table->dropColumn('id');
            });
            
            // Rename new columns and set PK
            Schema::table('plants', function (Blueprint $table) {
                $table->string('plant_id', 36)->primary()->change();
                $table->renameColumn('new_plant_type_id', 'plant_type_id');
                $table->renameColumn('new_planting_location_id', 'planting_location_id');
            });
            
            // Add FK constraints
            Schema::table('plants', function (Blueprint $table) {
                $table->foreign('plant_type_id')->references('plant_type_id')->on('plant_types')->nullOnDelete();
                $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->nullOnDelete();
            });

            // 6. PLANTINGS TABLE
            echo "Finalizing plantings table...\n";
            // Drop old FKs
            Schema::table('plantings', function (Blueprint $table) {
                $table->dropForeign(['plant_id']);
                $table->dropForeign(['planting_location_id']);
                $table->dropColumn(['plant_id', 'planting_location_id']);
                $table->dropPrimary(['id']);
                $table->dropColumn('id');
            });
            
            // Rename new columns and set PK
            Schema::table('plantings', function (Blueprint $table) {
                $table->string('planting_id', 36)->primary()->change();
                $table->renameColumn('new_plant_id', 'plant_id');
                $table->renameColumn('new_planting_location_id', 'planting_location_id');
            });
            
            // Add FK constraints
            Schema::table('plantings', function (Blueprint $table) {
                $table->foreign('plant_id')->references('plant_id')->on('plants')->cascadeOnDelete();
                $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->nullOnDelete();
            });

            // 7. HARVESTS TABLE
            echo "Finalizing harvests table...\n";
            // Drop old FKs
            Schema::table('harvests', function (Blueprint $table) {
                $table->dropForeign(['plant_id']);
                $table->dropForeign(['planting_id']);
                $table->dropForeign(['planting_location_id']);
                $table->dropForeign(['user_id']);
                $table->dropColumn(['plant_id', 'planting_id', 'planting_location_id', 'user_id']);
                $table->dropPrimary(['id']);
                $table->dropColumn('id');
            });
            
            // Rename new columns and set PK
            Schema::table('harvests', function (Blueprint $table) {
                $table->string('harvest_id', 36)->primary()->change();
                $table->renameColumn('new_plant_id', 'plant_id');
                $table->renameColumn('new_planting_id', 'planting_id');
                $table->renameColumn('new_planting_location_id', 'planting_location_id');
                $table->renameColumn('new_user_id', 'user_id');
            });
            
            // Add FK constraints
            Schema::table('harvests', function (Blueprint $table) {
                $table->foreign('plant_id')->references('plant_id')->on('plants')->cascadeOnDelete();
                $table->foreign('planting_id')->references('planting_id')->on('plantings')->cascadeOnDelete();
                $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->nullOnDelete();
                $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            });

            // 8. PLANT_NOTES TABLE
            if (Schema::hasTable('plant_notes')) {
                echo "Finalizing plant_notes table...\n";
                Schema::table('plant_notes', function (Blueprint $table) {
                    $table->dropForeign(['plant_id']);
                    $table->dropForeign(['user_id']);
                    $table->dropColumn(['plant_id', 'user_id']);
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('plant_notes', function (Blueprint $table) {
                    $table->string('plant_note_id', 36)->primary()->change();
                    $table->renameColumn('new_plant_id', 'plant_id');
                    $table->renameColumn('new_user_id', 'user_id');
                });
                
                Schema::table('plant_notes', function (Blueprint $table) {
                    $table->foreign('plant_id')->references('plant_id')->on('plants')->cascadeOnDelete();
                    $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
                });
            }

            // 9. PLANT_PHOTOS TABLE
            if (Schema::hasTable('plant_photos')) {
                echo "Finalizing plant_photos table...\n";
                Schema::table('plant_photos', function (Blueprint $table) {
                    $table->dropForeign(['plant_id']);
                    $table->dropColumn('plant_id');
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('plant_photos', function (Blueprint $table) {
                    $table->string('plant_photo_id', 36)->primary()->change();
                    $table->renameColumn('new_plant_id', 'plant_id');
                });
                
                Schema::table('plant_photos', function (Blueprint $table) {
                    $table->foreign('plant_id')->references('plant_id')->on('plants')->cascadeOnDelete();
                });
            }

            // 10. PLANTING_LOCATION_NOTES TABLE
            if (Schema::hasTable('planting_location_notes')) {
                echo "Finalizing planting_location_notes table...\n";
                Schema::table('planting_location_notes', function (Blueprint $table) {
                    $table->dropForeign(['planting_location_id']);
                    $table->dropForeign(['user_id']);
                    $table->dropColumn(['planting_location_id', 'user_id']);
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('planting_location_notes', function (Blueprint $table) {
                    $table->string('planting_location_note_id', 36)->primary()->change();
                    $table->renameColumn('new_planting_location_id', 'planting_location_id');
                    $table->renameColumn('new_user_id', 'user_id');
                });
                
                Schema::table('planting_location_notes', function (Blueprint $table) {
                    $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->cascadeOnDelete();
                    $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
                });
            }

            // 11. PLANTING_LOCATION_PHOTOS TABLE
            if (Schema::hasTable('planting_location_photos')) {
                echo "Finalizing planting_location_photos table...\n";
                Schema::table('planting_location_photos', function (Blueprint $table) {
                    $table->dropForeign(['planting_location_id']);
                    $table->dropColumn('planting_location_id');
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('planting_location_photos', function (Blueprint $table) {
                    $table->string('planting_location_photo_id', 36)->primary()->change();
                    $table->renameColumn('new_planting_location_id', 'planting_location_id');
                });
                
                Schema::table('planting_location_photos', function (Blueprint $table) {
                    $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->cascadeOnDelete();
                });
            }

            // 12. PLANTING_LOSSES TABLE
            if (Schema::hasTable('planting_losses')) {
                echo "Finalizing planting_losses table...\n";
                Schema::table('planting_losses', function (Blueprint $table) {
                    $table->dropForeign(['planting_id']);
                    $table->dropColumn('planting_id');
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('planting_losses', function (Blueprint $table) {
                    $table->string('planting_loss_id', 36)->primary()->change();
                    $table->renameColumn('new_planting_id', 'planting_id');
                });
                
                Schema::table('planting_losses', function (Blueprint $table) {
                    $table->foreign('planting_id')->references('planting_id')->on('plantings')->cascadeOnDelete();
                });
            }

            // 13. NUTRIENTS TABLE
            if (Schema::hasTable('nutrients')) {
                echo "Finalizing nutrients table...\n";
                Schema::table('nutrients', function (Blueprint $table) {
                    $table->dropForeign(['planting_id']);
                    $table->dropColumn('planting_id');
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('nutrients', function (Blueprint $table) {
                    $table->string('nutrient_id', 36)->primary()->change();
                    $table->renameColumn('new_planting_id', 'planting_id');
                });
                
                Schema::table('nutrients', function (Blueprint $table) {
                    $table->foreign('planting_id')->references('planting_id')->on('plantings')->cascadeOnDelete();
                });
            }

            // 14. TREATMENTS TABLE
            if (Schema::hasTable('treatments')) {
                echo "Finalizing treatments table...\n";
                Schema::table('treatments', function (Blueprint $table) {
                    $table->dropForeign(['planting_id']);
                    $table->dropColumn('planting_id');
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('treatments', function (Blueprint $table) {
                    $table->string('treatment_id', 36)->primary()->change();
                    $table->renameColumn('new_planting_id', 'planting_id');
                });
                
                Schema::table('treatments', function (Blueprint $table) {
                    $table->foreign('planting_id')->references('planting_id')->on('plantings')->cascadeOnDelete();
                });
            }

            // 15. USER PIVOT TABLES
            if (Schema::hasTable('user_planting_location_land_manager')) {
                echo "Finalizing user_planting_location_land_manager table...\n";
                Schema::table('user_planting_location_land_manager', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                    $table->dropForeign(['planting_location_id']);
                    $table->dropColumn(['user_id', 'planting_location_id']);
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('user_planting_location_land_manager', function (Blueprint $table) {
                    $table->string('user_planting_location_land_manager_id', 36)->primary()->change();
                    $table->renameColumn('new_user_id', 'user_id');
                    $table->renameColumn('new_planting_location_id', 'planting_location_id');
                });
                
                Schema::table('user_planting_location_land_manager', function (Blueprint $table) {
                    $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
                    $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->cascadeOnDelete();
                });
            }

            if (Schema::hasTable('user_planting_location_land_worker')) {
                echo "Finalizing user_planting_location_land_worker table...\n";
                Schema::table('user_planting_location_land_worker', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                    $table->dropForeign(['planting_location_id']);
                    $table->dropColumn(['user_id', 'planting_location_id']);
                    $table->dropPrimary(['id']);
                    $table->dropColumn('id');
                });
                
                Schema::table('user_planting_location_land_worker', function (Blueprint $table) {
                    $table->string('user_planting_location_land_worker_id', 36)->primary()->change();
                    $table->renameColumn('new_user_id', 'user_id');
                    $table->renameColumn('new_planting_location_id', 'planting_location_id');
                });
                
                Schema::table('user_planting_location_land_worker', function (Blueprint $table) {
                    $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
                    $table->foreign('planting_location_id')->references('planting_location_id')->on('planting_locations')->cascadeOnDelete();
                });
            }

            echo "Phase 3 Core finalization completed!\n";

        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a destructive migration, rollback is not recommended
        // You should restore from backup instead
        throw new Exception('Phase 3 rollback is not supported. Please restore from backup.');
    }
};
