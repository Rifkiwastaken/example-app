<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FASE 2: Migrasi data - Generate custom ID dan update semua FK.
     * Tabel Core: users, plant_types, plants, planting_locations, plantings, harvests
     */
    public function up(): void
    {
        // Helper function untuk generate custom ID
        $generateCustomId = function ($prefix, $length = 8) {
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[random_int(0, strlen($characters) - 1)];
            }
            return "{$prefix}-{$randomString}";
        };

        // 1. USERS TABLE
        echo "Migrating users table...\n";
        $users = DB::table('users')->whereNull('user_id')->get();
        foreach ($users as $user) {
            do {
                $customId = $generateCustomId('USR');
                $exists = DB::table('users')->where('user_id', $customId)->exists();
            } while ($exists);
            
            DB::table('users')->where('id', $user->id)->update(['user_id' => $customId]);
        }

        // 2. PLANT_TYPES TABLE
        echo "Migrating plant_types table...\n";
        $plantTypes = DB::table('plant_types')->whereNull('plant_type_id')->get();
        foreach ($plantTypes as $plantType) {
            do {
                $customId = $generateCustomId('PTY');
                $exists = DB::table('plant_types')->where('plant_type_id', $customId)->exists();
            } while ($exists);
            
            DB::table('plant_types')->where('id', $plantType->id)->update(['plant_type_id' => $customId]);
        }

        // 3. LOCATIONS TABLE
        echo "Migrating locations table...\n";
        if (Schema::hasTable('locations')) {
            $locations = DB::table('locations')->whereNull('location_id')->get();
            foreach ($locations as $location) {
                do {
                    $customId = $generateCustomId('LCT');
                    $exists = DB::table('locations')->where('location_id', $customId)->exists();
                } while ($exists);
                
                DB::table('locations')->where('id', $location->id)->update(['location_id' => $customId]);
            }
        }

        // 4. PLANTING_LOCATIONS TABLE
        echo "Migrating planting_locations table...\n";
        $plantingLocations = DB::table('planting_locations')->whereNull('planting_location_id')->get();
        foreach ($plantingLocations as $location) {
            do {
                $customId = $generateCustomId('LOC');
                $exists = DB::table('planting_locations')->where('planting_location_id', $customId)->exists();
            } while ($exists);
            
            $updateData = ['planting_location_id' => $customId];
            
            // Update FK: location_id
            if ($location->location_id) {
                $parentLocation = DB::table('locations')->where('id', $location->location_id)->first();
                if ($parentLocation && $parentLocation->location_id) {
                    $updateData['new_location_id'] = $parentLocation->location_id;
                }
            }
            
            DB::table('planting_locations')->where('id', $location->id)->update($updateData);
        }

        // 5. PLANTS TABLE
        echo "Migrating plants table...\n";
        $plants = DB::table('plants')->whereNull('plant_id')->get();
        foreach ($plants as $plant) {
            do {
                $customId = $generateCustomId('PLT');
                $exists = DB::table('plants')->where('plant_id', $customId)->exists();
            } while ($exists);
            
            $updateData = ['plant_id' => $customId];
            
            // Update FK: plant_type_id
            if ($plant->plant_type_id) {
                $plantType = DB::table('plant_types')->where('id', $plant->plant_type_id)->first();
                if ($plantType && $plantType->plant_type_id) {
                    $updateData['new_plant_type_id'] = $plantType->plant_type_id;
                }
            }
            
            // Update FK: planting_location_id
            if ($plant->planting_location_id) {
                $plantingLocation = DB::table('planting_locations')->where('id', $plant->planting_location_id)->first();
                if ($plantingLocation && $plantingLocation->planting_location_id) {
                    $updateData['new_planting_location_id'] = $plantingLocation->planting_location_id;
                }
            }
            
            DB::table('plants')->where('id', $plant->id)->update($updateData);
        }

        // 6. PLANTINGS TABLE
        echo "Migrating plantings table...\n";
        $plantings = DB::table('plantings')->whereNull('planting_id')->get();
        foreach ($plantings as $planting) {
            do {
                $customId = $generateCustomId('PLN');
                $exists = DB::table('plantings')->where('planting_id', $customId)->exists();
            } while ($exists);
            
            $updateData = ['planting_id' => $customId];
            
            // Update FK: plant_id
            if ($planting->plant_id) {
                $plant = DB::table('plants')->where('id', $planting->plant_id)->first();
                if ($plant && $plant->plant_id) {
                    $updateData['new_plant_id'] = $plant->plant_id;
                }
            }
            
            // Update FK: planting_location_id
            if ($planting->planting_location_id) {
                $plantingLocation = DB::table('planting_locations')->where('id', $planting->planting_location_id)->first();
                if ($plantingLocation && $plantingLocation->planting_location_id) {
                    $updateData['new_planting_location_id'] = $plantingLocation->planting_location_id;
                }
            }
            
            DB::table('plantings')->where('id', $planting->id)->update($updateData);
        }

        // 7. HARVESTS TABLE
        echo "Migrating harvests table...\n";
        $harvests = DB::table('harvests')->whereNull('harvest_id')->get();
        foreach ($harvests as $harvest) {
            do {
                $customId = $generateCustomId('HRV');
                $exists = DB::table('harvests')->where('harvest_id', $customId)->exists();
            } while ($exists);
            
            $updateData = ['harvest_id' => $customId];
            
            // Update FK: plant_id
            if ($harvest->plant_id) {
                $plant = DB::table('plants')->where('id', $harvest->plant_id)->first();
                if ($plant && $plant->plant_id) {
                    $updateData['new_plant_id'] = $plant->plant_id;
                }
            }
            
            // Update FK: planting_id
            if ($harvest->planting_id) {
                $planting = DB::table('plantings')->where('id', $harvest->planting_id)->first();
                if ($planting && $planting->planting_id) {
                    $updateData['new_planting_id'] = $planting->planting_id;
                }
            }
            
            // Update FK: planting_location_id
            if ($harvest->planting_location_id) {
                $plantingLocation = DB::table('planting_locations')->where('id', $harvest->planting_location_id)->first();
                if ($plantingLocation && $plantingLocation->planting_location_id) {
                    $updateData['new_planting_location_id'] = $plantingLocation->planting_location_id;
                }
            }
            
            // Update FK: user_id
            if ($harvest->user_id) {
                $user = DB::table('users')->where('id', $harvest->user_id)->first();
                if ($user && $user->user_id) {
                    $updateData['new_user_id'] = $user->user_id;
                }
            }
            
            DB::table('harvests')->where('id', $harvest->id)->update($updateData);
        }

        // 8. PLANT_NOTES TABLE
        echo "Migrating plant_notes table...\n";
        if (Schema::hasTable('plant_notes')) {
            $plantNotes = DB::table('plant_notes')->whereNull('plant_note_id')->get();
            foreach ($plantNotes as $note) {
                do {
                    $customId = $generateCustomId('PLN');
                    $exists = DB::table('plant_notes')->where('plant_note_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['plant_note_id' => $customId];
                
                if ($note->plant_id) {
                    $plant = DB::table('plants')->where('id', $note->plant_id)->first();
                    if ($plant && $plant->plant_id) {
                        $updateData['new_plant_id'] = $plant->plant_id;
                    }
                }
                
                if ($note->user_id) {
                    $user = DB::table('users')->where('id', $note->user_id)->first();
                    if ($user && $user->user_id) {
                        $updateData['new_user_id'] = $user->user_id;
                    }
                }
                
                DB::table('plant_notes')->where('id', $note->id)->update($updateData);
            }
        }

        // 9. PLANT_PHOTOS TABLE
        echo "Migrating plant_photos table...\n";
        if (Schema::hasTable('plant_photos')) {
            $plantPhotos = DB::table('plant_photos')->whereNull('plant_photo_id')->get();
            foreach ($plantPhotos as $photo) {
                do {
                    $customId = $generateCustomId('PHP');
                    $exists = DB::table('plant_photos')->where('plant_photo_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['plant_photo_id' => $customId];
                
                if ($photo->plant_id) {
                    $plant = DB::table('plants')->where('id', $photo->plant_id)->first();
                    if ($plant && $plant->plant_id) {
                        $updateData['new_plant_id'] = $plant->plant_id;
                    }
                }
                
                DB::table('plant_photos')->where('id', $photo->id)->update($updateData);
            }
        }

        // 10. PLANTING_LOCATION_NOTES TABLE
        echo "Migrating planting_location_notes table...\n";
        if (Schema::hasTable('planting_location_notes')) {
            $notes = DB::table('planting_location_notes')->whereNull('planting_location_note_id')->get();
            foreach ($notes as $note) {
                do {
                    $customId = $generateCustomId('LCN');
                    $exists = DB::table('planting_location_notes')->where('planting_location_note_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['planting_location_note_id' => $customId];
                
                if ($note->planting_location_id) {
                    $location = DB::table('planting_locations')->where('id', $note->planting_location_id)->first();
                    if ($location && $location->planting_location_id) {
                        $updateData['new_planting_location_id'] = $location->planting_location_id;
                    }
                }
                
                if ($note->user_id) {
                    $user = DB::table('users')->where('id', $note->user_id)->first();
                    if ($user && $user->user_id) {
                        $updateData['new_user_id'] = $user->user_id;
                    }
                }
                
                DB::table('planting_location_notes')->where('id', $note->id)->update($updateData);
            }
        }

        // 11. PLANTING_LOCATION_PHOTOS TABLE
        echo "Migrating planting_location_photos table...\n";
        if (Schema::hasTable('planting_location_photos')) {
            $photos = DB::table('planting_location_photos')->whereNull('planting_location_photo_id')->get();
            foreach ($photos as $photo) {
                do {
                    $customId = $generateCustomId('LCP');
                    $exists = DB::table('planting_location_photos')->where('planting_location_photo_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['planting_location_photo_id' => $customId];
                
                if ($photo->planting_location_id) {
                    $location = DB::table('planting_locations')->where('id', $photo->planting_location_id)->first();
                    if ($location && $location->planting_location_id) {
                        $updateData['new_planting_location_id'] = $location->planting_location_id;
                    }
                }
                
                DB::table('planting_location_photos')->where('id', $photo->id)->update($updateData);
            }
        }

        // 12. PLANTING_LOSSES TABLE
        echo "Migrating planting_losses table...\n";
        if (Schema::hasTable('planting_losses')) {
            $losses = DB::table('planting_losses')->whereNull('planting_loss_id')->get();
            foreach ($losses as $loss) {
                do {
                    $customId = $generateCustomId('PLS');
                    $exists = DB::table('planting_losses')->where('planting_loss_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['planting_loss_id' => $customId];
                
                if ($loss->planting_id) {
                    $planting = DB::table('plantings')->where('id', $loss->planting_id)->first();
                    if ($planting && $planting->planting_id) {
                        $updateData['new_planting_id'] = $planting->planting_id;
                    }
                }
                
                DB::table('planting_losses')->where('id', $loss->id)->update($updateData);
            }
        }

        // 13. NUTRIENTS TABLE
        echo "Migrating nutrients table...\n";
        if (Schema::hasTable('nutrients')) {
            $nutrients = DB::table('nutrients')->whereNull('nutrient_id')->get();
            foreach ($nutrients as $nutrient) {
                do {
                    $customId = $generateCustomId('NTR');
                    $exists = DB::table('nutrients')->where('nutrient_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['nutrient_id' => $customId];
                
                if ($nutrient->planting_id) {
                    $planting = DB::table('plantings')->where('id', $nutrient->planting_id)->first();
                    if ($planting && $planting->planting_id) {
                        $updateData['new_planting_id'] = $planting->planting_id;
                    }
                }
                
                DB::table('nutrients')->where('id', $nutrient->id)->update($updateData);
            }
        }

        // 14. TREATMENTS TABLE
        echo "Migrating treatments table...\n";
        if (Schema::hasTable('treatments')) {
            $treatments = DB::table('treatments')->whereNull('treatment_id')->get();
            foreach ($treatments as $treatment) {
                do {
                    $customId = $generateCustomId('TRT');
                    $exists = DB::table('treatments')->where('treatment_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['treatment_id' => $customId];
                
                if ($treatment->planting_id) {
                    $planting = DB::table('plantings')->where('id', $treatment->planting_id)->first();
                    if ($planting && $planting->planting_id) {
                        $updateData['new_planting_id'] = $planting->planting_id;
                    }
                }
                
                DB::table('treatments')->where('id', $treatment->id)->update($updateData);
            }
        }

        // 15. USER PIVOT TABLES
        echo "Migrating user_planting_location_land_manager table...\n";
        if (Schema::hasTable('user_planting_location_land_manager')) {
            $managers = DB::table('user_planting_location_land_manager')->whereNull('user_planting_location_land_manager_id')->get();
            foreach ($managers as $manager) {
                do {
                    $customId = $generateCustomId('ULM');
                    $exists = DB::table('user_planting_location_land_manager')->where('user_planting_location_land_manager_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['user_planting_location_land_manager_id' => $customId];
                
                if ($manager->user_id) {
                    $user = DB::table('users')->where('id', $manager->user_id)->first();
                    if ($user && $user->user_id) {
                        $updateData['new_user_id'] = $user->user_id;
                    }
                }
                
                if ($manager->planting_location_id) {
                    $location = DB::table('planting_locations')->where('id', $manager->planting_location_id)->first();
                    if ($location && $location->planting_location_id) {
                        $updateData['new_planting_location_id'] = $location->planting_location_id;
                    }
                }
                
                DB::table('user_planting_location_land_manager')->where('id', $manager->id)->update($updateData);
            }
        }

        echo "Migrating user_planting_location_land_worker table...\n";
        if (Schema::hasTable('user_planting_location_land_worker')) {
            $workers = DB::table('user_planting_location_land_worker')->whereNull('user_planting_location_land_worker_id')->get();
            foreach ($workers as $worker) {
                do {
                    $customId = $generateCustomId('ULW');
                    $exists = DB::table('user_planting_location_land_worker')->where('user_planting_location_land_worker_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['user_planting_location_land_worker_id' => $customId];
                
                if ($worker->user_id) {
                    $user = DB::table('users')->where('id', $worker->user_id)->first();
                    if ($user && $user->user_id) {
                        $updateData['new_user_id'] = $user->user_id;
                    }
                }
                
                if ($worker->planting_location_id) {
                    $location = DB::table('planting_locations')->where('id', $worker->planting_location_id)->first();
                    if ($location && $location->planting_location_id) {
                        $updateData['new_planting_location_id'] = $location->planting_location_id;
                    }
                }
                
                DB::table('user_planting_location_land_worker')->where('id', $worker->id)->update($updateData);
            }
        }

        echo "Phase 2 Core migration completed!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset semua custom ID dan FK baru ke NULL
        DB::table('users')->update(['user_id' => null]);
        DB::table('plant_types')->update(['plant_type_id' => null]);
        
        if (Schema::hasTable('locations')) {
            DB::table('locations')->update(['location_id' => null]);
        }
        
        DB::table('planting_locations')->update([
            'planting_location_id' => null,
            'new_location_id' => null
        ]);
        
        DB::table('plants')->update([
            'plant_id' => null,
            'new_plant_type_id' => null,
            'new_planting_location_id' => null
        ]);
        
        DB::table('plantings')->update([
            'planting_id' => null,
            'new_plant_id' => null,
            'new_planting_location_id' => null
        ]);
        
        DB::table('harvests')->update([
            'harvest_id' => null,
            'new_plant_id' => null,
            'new_planting_id' => null,
            'new_planting_location_id' => null,
            'new_user_id' => null
        ]);
        
        // Reset child tables
        if (Schema::hasTable('plant_notes')) {
            DB::table('plant_notes')->update(['plant_note_id' => null, 'new_plant_id' => null, 'new_user_id' => null]);
        }
        
        if (Schema::hasTable('plant_photos')) {
            DB::table('plant_photos')->update(['plant_photo_id' => null, 'new_plant_id' => null]);
        }
        
        if (Schema::hasTable('planting_location_notes')) {
            DB::table('planting_location_notes')->update(['planting_location_note_id' => null, 'new_planting_location_id' => null, 'new_user_id' => null]);
        }
        
        if (Schema::hasTable('planting_location_photos')) {
            DB::table('planting_location_photos')->update(['planting_location_photo_id' => null, 'new_planting_location_id' => null]);
        }
        
        if (Schema::hasTable('planting_losses')) {
            DB::table('planting_losses')->update(['planting_loss_id' => null, 'new_planting_id' => null]);
        }
        
        if (Schema::hasTable('nutrients')) {
            DB::table('nutrients')->update(['nutrient_id' => null, 'new_planting_id' => null]);
        }
        
        if (Schema::hasTable('treatments')) {
            DB::table('treatments')->update(['treatment_id' => null, 'new_planting_id' => null]);
        }
        
        if (Schema::hasTable('user_planting_location_land_manager')) {
            DB::table('user_planting_location_land_manager')->update(['user_planting_location_land_manager_id' => null, 'new_user_id' => null, 'new_planting_location_id' => null]);
        }
        
        if (Schema::hasTable('user_planting_location_land_worker')) {
            DB::table('user_planting_location_land_worker')->update(['user_planting_location_land_worker_id' => null, 'new_user_id' => null, 'new_planting_location_id' => null]);
        }
    }
};
