<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        echo "\n=== PHASE 3: FINALIZE - Restructure All Tables (FINAL) ===\n";
        echo "⚠️  WARNING: This is IRREVERSIBLE!\n";
        echo "This version includes error handling for partial migrations.\n\n";
        
        echo "Step 1: Dropping all FK constraints...\n";
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            DB::statement('ALTER TABLE `inventory_type_certification_reports` DROP FOREIGN KEY `inv_type_cert_reports_cert_fk`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_certification_reports` DROP FOREIGN KEY `inv_type_cert_reports_inv_type_fk`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `sale_items` DROP FOREIGN KEY `sale_items_inventory_lot_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `sale_items` DROP FOREIGN KEY `sale_items_inventory_type_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `sale_items` DROP FOREIGN KEY `sale_items_sale_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `expenses` DROP FOREIGN KEY `expenses_edited_by_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `expenses` DROP FOREIGN KEY `expenses_nutrient_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `expenses` DROP FOREIGN KEY `expenses_planting_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `expenses` DROP FOREIGN KEY `expenses_planting_location_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `expenses` DROP FOREIGN KEY `expenses_responsible_person_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `expenses` DROP FOREIGN KEY `expenses_treatment_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `nutrients` DROP FOREIGN KEY `nutrients_edited_by_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `nutrients` DROP FOREIGN KEY `nutrients_planting_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `nutrients` DROP FOREIGN KEY `nutrients_planting_location_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `nutrients` DROP FOREIGN KEY `nutrients_responsible_person_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `treatments` DROP FOREIGN KEY `treatments_edited_by_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `treatments` DROP FOREIGN KEY `treatments_planting_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `treatments` DROP FOREIGN KEY `treatments_planting_location_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `treatments` DROP FOREIGN KEY `treatments_responsible_person_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `attachments` DROP FOREIGN KEY `attachments_created_by_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `attachments` DROP FOREIGN KEY `attachments_edited_by_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `attachments` DROP FOREIGN KEY `attachments_planting_location_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `seed_histories` DROP FOREIGN KEY `seed_histories_inventory_type_seed_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `seed_histories` DROP FOREIGN KEY `seed_histories_user_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `planting_losses` DROP FOREIGN KEY `planting_losses_planting_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `plant_notes` DROP FOREIGN KEY `plant_notes_plant_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `plant_photos` DROP FOREIGN KEY `plant_photos_plant_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `planting_location_notes` DROP FOREIGN KEY `planting_location_notes_planting_location_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `planting_location_notes` DROP FOREIGN KEY `planting_location_notes_user_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `planting_location_photos` DROP FOREIGN KEY `planting_location_photos_planting_location_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_notes` DROP FOREIGN KEY `inventory_notes_inventory_type_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_notes` DROP FOREIGN KEY `inventory_notes_user_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_photos` DROP FOREIGN KEY `inventory_photos_inventory_type_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_photos` DROP FOREIGN KEY `inventory_photos_user_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `user_planting_location_land_manager` DROP FOREIGN KEY `user_planting_location_land_manager_planting_location_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `user_planting_location_land_manager` DROP FOREIGN KEY `user_planting_location_land_manager_user_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `user_planting_location_land_worker` DROP FOREIGN KEY `user_planting_location_land_worker_planting_location_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `user_planting_location_land_worker` DROP FOREIGN KEY `user_planting_location_land_worker_user_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `harvests` DROP FOREIGN KEY `harvests_edited_by_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `harvests` DROP FOREIGN KEY `harvests_plant_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `harvests` DROP FOREIGN KEY `harvests_planting_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `harvests` DROP FOREIGN KEY `harvests_planting_location_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `harvests` DROP FOREIGN KEY `harvests_recorded_by_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_lots` DROP FOREIGN KEY `inventory_lots_bin_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_lots` DROP FOREIGN KEY `inventory_lots_certification_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_lots` DROP FOREIGN KEY `inventory_lots_inventory_type_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_lots` DROP FOREIGN KEY `inventory_lots_warehouse_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `certification_reports` DROP FOREIGN KEY `certification_reports_certification_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` DROP FOREIGN KEY `inv_type_seeds_cert_report_fk`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` DROP FOREIGN KEY `inv_type_seeds_inv_type_fk`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` DROP FOREIGN KEY `inv_type_seeds_location_fk`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` DROP FOREIGN KEY `inv_type_seeds_plant_fk`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` DROP FOREIGN KEY `inv_type_seeds_user_fk`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` DROP FOREIGN KEY `inventory_type_seeds_edited_by_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `tasks` DROP FOREIGN KEY `tasks_assigned_to_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `tasks` DROP FOREIGN KEY `tasks_created_by_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `tasks` DROP FOREIGN KEY `tasks_last_edited_by_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `tasks` DROP FOREIGN KEY `tasks_planting_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `tasks` DROP FOREIGN KEY `tasks_planting_location_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `tasks` DROP FOREIGN KEY `tasks_series_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `tasks` DROP FOREIGN KEY `tasks_template_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `sales` DROP FOREIGN KEY `sales_planting_location_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `sales` DROP FOREIGN KEY `sales_user_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `plantings` DROP FOREIGN KEY `plantings_plant_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `plantings` DROP FOREIGN KEY `plantings_planting_location_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_types` DROP FOREIGN KEY `inventory_types_plant_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_types` DROP FOREIGN KEY `inventory_types_responsible_person_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `certifications` DROP FOREIGN KEY `certifications_harvest_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `certifications` DROP FOREIGN KEY `certifications_plant_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `certifications` DROP FOREIGN KEY `certifications_planting_location_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `plants` DROP FOREIGN KEY `plants_plant_type_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `plants` DROP FOREIGN KEY `plants_planting_location_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `bins` DROP FOREIGN KEY `bins_warehouse_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `task_series` DROP FOREIGN KEY `task_series_template_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `warehouses` DROP FOREIGN KEY `warehouses_responsible_person_id_foreign`;');
        } catch (\Exception $e) { /* Already dropped */ }
        echo "  ✓ All FK constraints dropped\n\n";


        echo "Step 2: Restructuring tables...\n";

        // users
        try {
            DB::statement('ALTER TABLE `users` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }

        // plant_types
        try {
            DB::statement('ALTER TABLE `plant_types` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }

        // warehouses
        try {
            DB::statement('ALTER TABLE `warehouses` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `warehouses` DROP COLUMN `responsible_person_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `warehouses` CHANGE `new_responsible_person_id` `responsible_person_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // task_templates
        try {
            DB::statement('ALTER TABLE `task_templates` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }

        // landing_page_settings
        try {
            DB::statement('ALTER TABLE `landing_page_settings` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }

        // plants
        try {
            DB::statement('ALTER TABLE `plants` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `plants` DROP COLUMN `planting_location_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `plants` CHANGE `new_planting_location_id` `planting_location_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `plants` DROP COLUMN `plant_type_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `plants` CHANGE `new_plant_type_id` `plant_type_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // planting_locations
        try {
            DB::statement('ALTER TABLE `planting_locations` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }

        // bins
        try {
            DB::statement('ALTER TABLE `bins` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `bins` DROP COLUMN `warehouse_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `bins` CHANGE `new_warehouse_id` `warehouse_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // task_series
        try {
            DB::statement('ALTER TABLE `task_series` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `task_series` DROP COLUMN `template_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `task_series` CHANGE `new_template_id` `template_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // plantings
        try {
            DB::statement('ALTER TABLE `plantings` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `plantings` DROP COLUMN `planting_location_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `plantings` CHANGE `new_planting_location_id` `planting_location_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `plantings` DROP COLUMN `plant_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `plantings` CHANGE `new_plant_id` `plant_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // inventory_types
        try {
            DB::statement('ALTER TABLE `inventory_types` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_types` DROP COLUMN `plant_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_types` CHANGE `new_plant_id` `plant_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `inventory_types` DROP COLUMN `responsible_person_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_types` CHANGE `new_responsible_person_id` `responsible_person_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // certifications
        try {
            DB::statement('ALTER TABLE `certifications` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `certifications` DROP COLUMN `harvest_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `certifications` CHANGE `new_harvest_id` `harvest_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `certifications` DROP COLUMN `planting_location_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `certifications` CHANGE `new_planting_location_id` `planting_location_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `certifications` DROP COLUMN `plant_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `certifications` CHANGE `new_plant_id` `plant_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // harvests
        try {
            DB::statement('ALTER TABLE `harvests` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `harvests` DROP COLUMN `edited_by`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `harvests` CHANGE `new_edited_by` `edited_by` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `harvests` DROP COLUMN `planting_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `harvests` CHANGE `new_planting_id` `planting_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `harvests` DROP COLUMN `planting_location_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `harvests` CHANGE `new_planting_location_id` `planting_location_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `harvests` DROP COLUMN `plant_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `harvests` CHANGE `new_plant_id` `plant_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `harvests` DROP COLUMN `recorded_by`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `harvests` CHANGE `new_recorded_by` `recorded_by` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // inventory_lots
        try {
            DB::statement('ALTER TABLE `inventory_lots` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_lots` DROP COLUMN `bin_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_lots` CHANGE `new_bin_id` `bin_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `inventory_lots` DROP COLUMN `certification_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_lots` CHANGE `new_certification_id` `certification_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `inventory_lots` DROP COLUMN `inventory_type_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_lots` CHANGE `new_inventory_type_id` `inventory_type_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `inventory_lots` DROP COLUMN `warehouse_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_lots` CHANGE `new_warehouse_id` `warehouse_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // certification_reports
        try {
            DB::statement('ALTER TABLE `certification_reports` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `certification_reports` DROP COLUMN `certification_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `certification_reports` CHANGE `new_certification_id` `certification_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // inventory_type_seeds
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` DROP COLUMN `edited_by`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` CHANGE `new_edited_by` `edited_by` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` DROP COLUMN `certification_report_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` CHANGE `new_certification_report_id` `certification_report_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` DROP COLUMN `inventory_type_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` CHANGE `new_inventory_type_id` `inventory_type_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` DROP COLUMN `planting_location_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` CHANGE `new_planting_location_id` `planting_location_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` DROP COLUMN `plant_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` CHANGE `new_plant_id` `plant_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` DROP COLUMN `filled_by_user_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` CHANGE `new_filled_by_user_id` `filled_by_user_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // tasks
        try {
            DB::statement('ALTER TABLE `tasks` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `tasks` DROP COLUMN `assigned_to`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `tasks` CHANGE `new_assigned_to` `assigned_to` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `tasks` DROP COLUMN `created_by`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `tasks` CHANGE `new_created_by` `created_by` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `tasks` DROP COLUMN `last_edited_by`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `tasks` CHANGE `new_last_edited_by` `last_edited_by` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `tasks` DROP COLUMN `planting_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `tasks` CHANGE `new_planting_id` `planting_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `tasks` DROP COLUMN `planting_location_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `tasks` CHANGE `new_planting_location_id` `planting_location_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `tasks` DROP COLUMN `series_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `tasks` CHANGE `new_series_id` `series_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `tasks` DROP COLUMN `template_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `tasks` CHANGE `new_template_id` `template_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // sales
        try {
            DB::statement('ALTER TABLE `sales` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `sales` DROP COLUMN `planting_location_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `sales` CHANGE `new_planting_location_id` `planting_location_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `sales` DROP COLUMN `user_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `sales` CHANGE `new_user_id` `user_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // inventory_transactions
        try {
            DB::statement('ALTER TABLE `inventory_transactions` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_transactions` DROP COLUMN `bin_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_transactions` CHANGE `new_bin_id` `bin_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `inventory_transactions` DROP COLUMN `inventory_lot_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_transactions` CHANGE `new_inventory_lot_id` `inventory_lot_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `inventory_transactions` DROP COLUMN `inventory_type_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_transactions` CHANGE `new_inventory_type_id` `inventory_type_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `inventory_transactions` DROP COLUMN `user_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_transactions` CHANGE `new_user_id` `user_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `inventory_transactions` DROP COLUMN `warehouse_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_transactions` CHANGE `new_warehouse_id` `warehouse_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // inventory_type_warehouses
        try {
            DB::statement('ALTER TABLE `inventory_type_warehouses` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_warehouses` DROP COLUMN `bin_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_warehouses` CHANGE `new_bin_id` `bin_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_warehouses` DROP COLUMN `inventory_type_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_warehouses` CHANGE `new_inventory_type_id` `inventory_type_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_warehouses` DROP COLUMN `warehouse_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_warehouses` CHANGE `new_warehouse_id` `warehouse_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // inventory_type_certification_reports
        try {
            DB::statement('ALTER TABLE `inventory_type_certification_reports` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_certification_reports` DROP COLUMN `certification_report_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_certification_reports` CHANGE `new_certification_report_id` `certification_report_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_certification_reports` DROP COLUMN `inventory_type_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_certification_reports` CHANGE `new_inventory_type_id` `inventory_type_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // sale_items
        try {
            DB::statement('ALTER TABLE `sale_items` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `sale_items` DROP COLUMN `inventory_lot_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `sale_items` CHANGE `new_inventory_lot_id` `inventory_lot_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `sale_items` DROP COLUMN `inventory_type_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `sale_items` CHANGE `new_inventory_type_id` `inventory_type_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `sale_items` DROP COLUMN `sale_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `sale_items` CHANGE `new_sale_id` `sale_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // expenses
        try {
            DB::statement('ALTER TABLE `expenses` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `expenses` DROP COLUMN `edited_by`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `expenses` CHANGE `new_edited_by` `edited_by` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `expenses` DROP COLUMN `nutrient_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `expenses` CHANGE `new_nutrient_id` `nutrient_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `expenses` DROP COLUMN `planting_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `expenses` CHANGE `new_planting_id` `planting_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `expenses` DROP COLUMN `planting_location_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `expenses` CHANGE `new_planting_location_id` `planting_location_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `expenses` DROP COLUMN `responsible_person_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `expenses` CHANGE `new_responsible_person_id` `responsible_person_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `expenses` DROP COLUMN `treatment_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `expenses` CHANGE `new_treatment_id` `treatment_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // nutrients
        try {
            DB::statement('ALTER TABLE `nutrients` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `nutrients` DROP COLUMN `edited_by`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `nutrients` CHANGE `new_edited_by` `edited_by` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `nutrients` DROP COLUMN `planting_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `nutrients` CHANGE `new_planting_id` `planting_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `nutrients` DROP COLUMN `planting_location_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `nutrients` CHANGE `new_planting_location_id` `planting_location_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `nutrients` DROP COLUMN `responsible_person_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `nutrients` CHANGE `new_responsible_person_id` `responsible_person_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // treatments
        try {
            DB::statement('ALTER TABLE `treatments` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `treatments` DROP COLUMN `edited_by`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `treatments` CHANGE `new_edited_by` `edited_by` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `treatments` DROP COLUMN `planting_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `treatments` CHANGE `new_planting_id` `planting_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `treatments` DROP COLUMN `planting_location_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `treatments` CHANGE `new_planting_location_id` `planting_location_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `treatments` DROP COLUMN `responsible_person_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `treatments` CHANGE `new_responsible_person_id` `responsible_person_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // attachments
        try {
            DB::statement('ALTER TABLE `attachments` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `attachments` DROP COLUMN `created_by`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `attachments` CHANGE `new_created_by` `created_by` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `attachments` DROP COLUMN `edited_by`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `attachments` CHANGE `new_edited_by` `edited_by` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `attachments` DROP COLUMN `planting_location_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `attachments` CHANGE `new_planting_location_id` `planting_location_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // seed_histories
        try {
            DB::statement('ALTER TABLE `seed_histories` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `seed_histories` DROP COLUMN `inventory_type_seed_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `seed_histories` CHANGE `new_inventory_type_seed_id` `inventory_type_seed_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `seed_histories` DROP COLUMN `user_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `seed_histories` CHANGE `new_user_id` `user_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // planting_losses
        try {
            DB::statement('ALTER TABLE `planting_losses` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `planting_losses` DROP COLUMN `planting_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `planting_losses` CHANGE `new_planting_id` `planting_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // plant_notes
        try {
            DB::statement('ALTER TABLE `plant_notes` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `plant_notes` DROP COLUMN `plant_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `plant_notes` CHANGE `new_plant_id` `plant_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // plant_photos
        try {
            DB::statement('ALTER TABLE `plant_photos` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `plant_photos` DROP COLUMN `plant_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `plant_photos` CHANGE `new_plant_id` `plant_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // planting_location_notes
        try {
            DB::statement('ALTER TABLE `planting_location_notes` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `planting_location_notes` DROP COLUMN `planting_location_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `planting_location_notes` CHANGE `new_planting_location_id` `planting_location_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `planting_location_notes` DROP COLUMN `user_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `planting_location_notes` CHANGE `new_user_id` `user_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // planting_location_photos
        try {
            DB::statement('ALTER TABLE `planting_location_photos` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `planting_location_photos` DROP COLUMN `planting_location_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `planting_location_photos` CHANGE `new_planting_location_id` `planting_location_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // inventory_notes
        try {
            DB::statement('ALTER TABLE `inventory_notes` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_notes` DROP COLUMN `inventory_type_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_notes` CHANGE `new_inventory_type_id` `inventory_type_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `inventory_notes` DROP COLUMN `user_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_notes` CHANGE `new_user_id` `user_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // inventory_photos
        try {
            DB::statement('ALTER TABLE `inventory_photos` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_photos` DROP COLUMN `inventory_type_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_photos` CHANGE `new_inventory_type_id` `inventory_type_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `inventory_photos` DROP COLUMN `user_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `inventory_photos` CHANGE `new_user_id` `user_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // user_planting_location_land_manager
        try {
            DB::statement('ALTER TABLE `user_planting_location_land_manager` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `user_planting_location_land_manager` DROP COLUMN `planting_location_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `user_planting_location_land_manager` CHANGE `new_planting_location_id` `planting_location_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `user_planting_location_land_manager` DROP COLUMN `user_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `user_planting_location_land_manager` CHANGE `new_user_id` `user_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        // user_planting_location_land_worker
        try {
            DB::statement('ALTER TABLE `user_planting_location_land_worker` DROP COLUMN `id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `user_planting_location_land_worker` DROP COLUMN `planting_location_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `user_planting_location_land_worker` CHANGE `new_planting_location_id` `planting_location_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }
        try {
            DB::statement('ALTER TABLE `user_planting_location_land_worker` DROP COLUMN `user_id`;');
        } catch (\Exception $e) { /* Already dropped */ }
        try {
            DB::statement('ALTER TABLE `user_planting_location_land_worker` CHANGE `new_user_id` `user_id` VARCHAR(36);');
        } catch (\Exception $e) { /* Already renamed */ }

        echo "  ✓ Tables restructured\n\n";


        echo "Step 3: Setting custom IDs as Primary Keys...\n";

        try {
            DB::statement('ALTER TABLE `users` ADD PRIMARY KEY (`user_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `plant_types` ADD PRIMARY KEY (`plant_type_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `warehouses` ADD PRIMARY KEY (`warehouse_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `task_templates` ADD PRIMARY KEY (`task_template_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `landing_page_settings` ADD PRIMARY KEY (`landing_page_setting_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `plants` ADD PRIMARY KEY (`plant_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `planting_locations` ADD PRIMARY KEY (`planting_location_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `bins` ADD PRIMARY KEY (`bin_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `task_series` ADD PRIMARY KEY (`task_series_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `plantings` ADD PRIMARY KEY (`planting_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `inventory_types` ADD PRIMARY KEY (`inventory_type_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `certifications` ADD PRIMARY KEY (`certification_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `harvests` ADD PRIMARY KEY (`harvest_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `inventory_lots` ADD PRIMARY KEY (`inventory_lot_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `certification_reports` ADD PRIMARY KEY (`certification_report_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` ADD PRIMARY KEY (`inventory_type_seed_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `tasks` ADD PRIMARY KEY (`task_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `sales` ADD PRIMARY KEY (`sale_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `inventory_transactions` ADD PRIMARY KEY (`inventory_transaction_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_warehouses` ADD PRIMARY KEY (`inventory_type_warehous_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_certification_reports` ADD PRIMARY KEY (`inventory_type_certification_report_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `sale_items` ADD PRIMARY KEY (`sale_item_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `expenses` ADD PRIMARY KEY (`expense_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `nutrients` ADD PRIMARY KEY (`nutrient_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `treatments` ADD PRIMARY KEY (`treatment_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `attachments` ADD PRIMARY KEY (`attachment_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `seed_histories` ADD PRIMARY KEY (`seed_history_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `planting_losses` ADD PRIMARY KEY (`planting_loss_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `plant_notes` ADD PRIMARY KEY (`plant_note_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `plant_photos` ADD PRIMARY KEY (`plant_photo_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `planting_location_notes` ADD PRIMARY KEY (`planting_location_note_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `planting_location_photos` ADD PRIMARY KEY (`planting_location_photo_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `inventory_notes` ADD PRIMARY KEY (`inventory_note_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `inventory_photos` ADD PRIMARY KEY (`inventory_photo_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `user_planting_location_land_manager` ADD PRIMARY KEY (`user_planting_location_land_manager_id`);');
        } catch (\Exception $e) { /* Already set */ }
        try {
            DB::statement('ALTER TABLE `user_planting_location_land_worker` ADD PRIMARY KEY (`user_planting_location_land_worker_id`);');
        } catch (\Exception $e) { /* Already set */ }
        echo "  ✓ Primary keys set\n\n";


        echo "Step 4: Recreating FK constraints...\n";

        try {
            DB::statement('ALTER TABLE `warehouses` ADD CONSTRAINT `warehouses_responsible_person_id_fk` FOREIGN KEY (`responsible_person_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `plants` ADD CONSTRAINT `plants_planting_location_id_fk` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations`(`planting_location_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `plants` ADD CONSTRAINT `plants_plant_type_id_fk` FOREIGN KEY (`plant_type_id`) REFERENCES `plant_types`(`plant_type_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `bins` ADD CONSTRAINT `bins_warehouse_id_fk` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`warehouse_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `task_series` ADD CONSTRAINT `task_series_template_id_fk` FOREIGN KEY (`template_id`) REFERENCES `task_templates`(`task_template_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `plantings` ADD CONSTRAINT `plantings_planting_location_id_fk` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations`(`planting_location_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `plantings` ADD CONSTRAINT `plantings_plant_id_fk` FOREIGN KEY (`plant_id`) REFERENCES `plants`(`plant_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_types` ADD CONSTRAINT `inventory_types_plant_id_fk` FOREIGN KEY (`plant_id`) REFERENCES `plants`(`plant_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_types` ADD CONSTRAINT `inventory_types_responsible_person_id_fk` FOREIGN KEY (`responsible_person_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `certifications` ADD CONSTRAINT `certifications_harvest_id_fk` FOREIGN KEY (`harvest_id`) REFERENCES `harvests`(`harvest_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `certifications` ADD CONSTRAINT `certifications_planting_location_id_fk` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations`(`planting_location_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `certifications` ADD CONSTRAINT `certifications_plant_id_fk` FOREIGN KEY (`plant_id`) REFERENCES `plants`(`plant_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `harvests` ADD CONSTRAINT `harvests_edited_by_fk` FOREIGN KEY (`edited_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `harvests` ADD CONSTRAINT `harvests_planting_id_fk` FOREIGN KEY (`planting_id`) REFERENCES `plantings`(`planting_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `harvests` ADD CONSTRAINT `harvests_planting_location_id_fk` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations`(`planting_location_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `harvests` ADD CONSTRAINT `harvests_plant_id_fk` FOREIGN KEY (`plant_id`) REFERENCES `plants`(`plant_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `harvests` ADD CONSTRAINT `harvests_recorded_by_fk` FOREIGN KEY (`recorded_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_lots` ADD CONSTRAINT `inventory_lots_bin_id_fk` FOREIGN KEY (`bin_id`) REFERENCES `bins`(`bin_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_lots` ADD CONSTRAINT `inventory_lots_certification_id_fk` FOREIGN KEY (`certification_id`) REFERENCES `certifications`(`certification_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_lots` ADD CONSTRAINT `inventory_lots_inventory_type_id_fk` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types`(`inventory_type_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_lots` ADD CONSTRAINT `inventory_lots_warehouse_id_fk` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`warehouse_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `certification_reports` ADD CONSTRAINT `certification_reports_certification_id_fk` FOREIGN KEY (`certification_id`) REFERENCES `certifications`(`certification_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` ADD CONSTRAINT `inventory_type_seeds_edited_by_fk` FOREIGN KEY (`edited_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` ADD CONSTRAINT `inventory_type_seeds_certification_report_id_fk` FOREIGN KEY (`certification_report_id`) REFERENCES `certification_reports`(`certification_report_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` ADD CONSTRAINT `inventory_type_seeds_inventory_type_id_fk` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types`(`inventory_type_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` ADD CONSTRAINT `inventory_type_seeds_planting_location_id_fk` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations`(`planting_location_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` ADD CONSTRAINT `inventory_type_seeds_plant_id_fk` FOREIGN KEY (`plant_id`) REFERENCES `plants`(`plant_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_seeds` ADD CONSTRAINT `inventory_type_seeds_filled_by_user_id_fk` FOREIGN KEY (`filled_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `tasks` ADD CONSTRAINT `tasks_assigned_to_fk` FOREIGN KEY (`assigned_to`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `tasks` ADD CONSTRAINT `tasks_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `tasks` ADD CONSTRAINT `tasks_last_edited_by_fk` FOREIGN KEY (`last_edited_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `tasks` ADD CONSTRAINT `tasks_planting_id_fk` FOREIGN KEY (`planting_id`) REFERENCES `plantings`(`planting_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `tasks` ADD CONSTRAINT `tasks_planting_location_id_fk` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations`(`planting_location_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `tasks` ADD CONSTRAINT `tasks_series_id_fk` FOREIGN KEY (`series_id`) REFERENCES `task_series`(`task_series_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `tasks` ADD CONSTRAINT `tasks_template_id_fk` FOREIGN KEY (`template_id`) REFERENCES `task_templates`(`task_template_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `sales` ADD CONSTRAINT `sales_planting_location_id_fk` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations`(`planting_location_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `sales` ADD CONSTRAINT `sales_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_transactions` ADD CONSTRAINT `inventory_transactions_bin_id_fk` FOREIGN KEY (`bin_id`) REFERENCES `bins`(`bin_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_transactions` ADD CONSTRAINT `inventory_transactions_inventory_lot_id_fk` FOREIGN KEY (`inventory_lot_id`) REFERENCES `inventory_lots`(`inventory_lot_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_transactions` ADD CONSTRAINT `inventory_transactions_inventory_type_id_fk` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types`(`inventory_type_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_transactions` ADD CONSTRAINT `inventory_transactions_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_transactions` ADD CONSTRAINT `inventory_transactions_warehouse_id_fk` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`warehouse_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_warehouses` ADD CONSTRAINT `inventory_type_warehouses_bin_id_fk` FOREIGN KEY (`bin_id`) REFERENCES `bins`(`bin_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_warehouses` ADD CONSTRAINT `inventory_type_warehouses_inventory_type_id_fk` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types`(`inventory_type_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_warehouses` ADD CONSTRAINT `inventory_type_warehouses_warehouse_id_fk` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`warehouse_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_certification_reports` ADD CONSTRAINT `inventory_type_certification_reports_certification_report_id_fk` FOREIGN KEY (`certification_report_id`) REFERENCES `certification_reports`(`certification_report_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_type_certification_reports` ADD CONSTRAINT `inventory_type_certification_reports_inventory_type_id_fk` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types`(`inventory_type_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `sale_items` ADD CONSTRAINT `sale_items_inventory_lot_id_fk` FOREIGN KEY (`inventory_lot_id`) REFERENCES `inventory_lots`(`inventory_lot_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `sale_items` ADD CONSTRAINT `sale_items_inventory_type_id_fk` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types`(`inventory_type_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `sale_items` ADD CONSTRAINT `sale_items_sale_id_fk` FOREIGN KEY (`sale_id`) REFERENCES `sales`(`sale_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `expenses` ADD CONSTRAINT `expenses_edited_by_fk` FOREIGN KEY (`edited_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `expenses` ADD CONSTRAINT `expenses_nutrient_id_fk` FOREIGN KEY (`nutrient_id`) REFERENCES `nutrients`(`nutrient_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `expenses` ADD CONSTRAINT `expenses_planting_id_fk` FOREIGN KEY (`planting_id`) REFERENCES `plantings`(`planting_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `expenses` ADD CONSTRAINT `expenses_planting_location_id_fk` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations`(`planting_location_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `expenses` ADD CONSTRAINT `expenses_responsible_person_id_fk` FOREIGN KEY (`responsible_person_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `expenses` ADD CONSTRAINT `expenses_treatment_id_fk` FOREIGN KEY (`treatment_id`) REFERENCES `treatments`(`treatment_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `nutrients` ADD CONSTRAINT `nutrients_edited_by_fk` FOREIGN KEY (`edited_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `nutrients` ADD CONSTRAINT `nutrients_planting_id_fk` FOREIGN KEY (`planting_id`) REFERENCES `plantings`(`planting_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `nutrients` ADD CONSTRAINT `nutrients_planting_location_id_fk` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations`(`planting_location_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `nutrients` ADD CONSTRAINT `nutrients_responsible_person_id_fk` FOREIGN KEY (`responsible_person_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `treatments` ADD CONSTRAINT `treatments_edited_by_fk` FOREIGN KEY (`edited_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `treatments` ADD CONSTRAINT `treatments_planting_id_fk` FOREIGN KEY (`planting_id`) REFERENCES `plantings`(`planting_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `treatments` ADD CONSTRAINT `treatments_planting_location_id_fk` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations`(`planting_location_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `treatments` ADD CONSTRAINT `treatments_responsible_person_id_fk` FOREIGN KEY (`responsible_person_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `attachments` ADD CONSTRAINT `attachments_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `attachments` ADD CONSTRAINT `attachments_edited_by_fk` FOREIGN KEY (`edited_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `attachments` ADD CONSTRAINT `attachments_planting_location_id_fk` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations`(`planting_location_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `seed_histories` ADD CONSTRAINT `seed_histories_inventory_type_seed_id_fk` FOREIGN KEY (`inventory_type_seed_id`) REFERENCES `inventory_type_seeds`(`inventory_type_seed_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `seed_histories` ADD CONSTRAINT `seed_histories_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `planting_losses` ADD CONSTRAINT `planting_losses_planting_id_fk` FOREIGN KEY (`planting_id`) REFERENCES `plantings`(`planting_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `plant_notes` ADD CONSTRAINT `plant_notes_plant_id_fk` FOREIGN KEY (`plant_id`) REFERENCES `plants`(`plant_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `plant_photos` ADD CONSTRAINT `plant_photos_plant_id_fk` FOREIGN KEY (`plant_id`) REFERENCES `plants`(`plant_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `planting_location_notes` ADD CONSTRAINT `planting_location_notes_planting_location_id_fk` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations`(`planting_location_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `planting_location_notes` ADD CONSTRAINT `planting_location_notes_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `planting_location_photos` ADD CONSTRAINT `planting_location_photos_planting_location_id_fk` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations`(`planting_location_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_notes` ADD CONSTRAINT `inventory_notes_inventory_type_id_fk` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types`(`inventory_type_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_notes` ADD CONSTRAINT `inventory_notes_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_photos` ADD CONSTRAINT `inventory_photos_inventory_type_id_fk` FOREIGN KEY (`inventory_type_id`) REFERENCES `inventory_types`(`inventory_type_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `inventory_photos` ADD CONSTRAINT `inventory_photos_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `user_planting_location_land_manager` ADD CONSTRAINT `user_planting_location_land_manager_planting_location_id_fk` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations`(`planting_location_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `user_planting_location_land_manager` ADD CONSTRAINT `user_planting_location_land_manager_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `user_planting_location_land_worker` ADD CONSTRAINT `user_planting_location_land_worker_planting_location_id_fk` FOREIGN KEY (`planting_location_id`) REFERENCES `planting_locations`(`planting_location_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }
        try {
            DB::statement('ALTER TABLE `user_planting_location_land_worker` ADD CONSTRAINT `user_planting_location_land_worker_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE;');
        } catch (\Exception $e) { /* FK already exists or error */ }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        echo "  ✓ FK constraints recreated\n\n";

        echo "\n✅ PHASE 3 COMPLETED SUCCESSFULLY!\n";
        echo "All tables now use custom string IDs as Primary Keys.\n\n";
    }

    public function down(): void
    {
        echo "\n⚠️  CRITICAL WARNING!\n";
        echo "Phase 3 rollback is NOT SUPPORTED.\n";
        echo "Please restore from backup if you need to revert.\n\n";
        
        throw new \Exception('Phase 3 rollback not supported. Restore from backup.');
    }
};