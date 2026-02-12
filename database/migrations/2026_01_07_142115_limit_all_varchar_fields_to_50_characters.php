<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengubah semua field VARCHAR menjadi maksimal 50 karakter
     */
    public function up(): void
    {
        // Daftar semua tabel dan field yang perlu diubah menjadi VARCHAR(50)
        $tables = [
            // Users table
            'users' => ['name', 'email', 'photo_path', 'full_name', 'organization', 'position', 'nip', 'primary_phone', 'secondary_phone', 'province', 'city', 'district', 'village', 'location_placement'],
            
            // Plant Types
            'plant_types' => ['name', 'category'],
            
            // Plants
            'plants' => ['name', 'variety'],
            
            // Planting Locations
            'planting_locations' => ['name', 'location_summary', 'administrative_address', 'google_maps_link', 'primary_photo_path', 'planting_format_custom', 'map_size', 'light_condition', 'land_status', 'ownership_status', 'water_source', 'soil_type', 'location_type_custom'],
            
            // Plantings
            'plantings' => ['bed_label', 'start_method', 'germination_stage', 'light_profile', 'soil_condition', 'planting_format', 'planting_format_custom', 'harvest_unit'],
            
            // Harvests
            'harvests' => ['batch_no', 'source', 'quality', 'unit', 'harvest_unit'],
            
            // Certifications
            'certifications' => ['certification_status', 'seed_class_requested'],
            
            // Certification Reports
            'certification_reports' => ['report_number_bpsb', 'growing_season', 'inspection_phase', 'inspector_name', 'reporter_name', 'seed_class_result', 'isolation_north', 'isolation_east', 'isolation_south', 'isolation_west', 'scan_file_path', 'report_type', 'seed_unit', 'certified_seed_unit', 'harvest_per_unit_unit'],
            
            // Warehouses
            'warehouses' => ['name', 'internal_id', 'tracking_type'],
            
            // Bins
            'bins' => ['name', 'internal_id', 'capacity_unit'],
            
            // Inventory Types
            'inventory_types' => ['category', 'name', 'sku', 'electronic_id', 'unit', 'low_stock_unit', 'low_stock_email'],
            
            // Inventory Lots
            'inventory_lots' => ['production_id', 'status', 'stock_unit'],
            
            // Inventory Transactions
            'inventory_transactions' => ['transaction_type', 'unit', 'reason'],
            
            // Sales
            'sales' => ['receipt_number', 'buyer_name', 'buyer_contact', 'buyer_nik', 'buyer_category_custom', 'destination_province', 'destination_city', 'destination_district', 'destination_village', 'planned_location_name', 'payment_method', 'payment_status'],
            
            // Sale Items
            'sale_items' => ['unit'],
            
            // Treatments
            'treatments' => ['treatment_name', 'treatment_type', 'product_detail', 'opt_institution', 'application_method', 'technician', 'institution_source', 'attachment', 'batch_number', 'treatment_location', 'unit_measurement', 'keywords'],
            
            // Nutrients
            'nutrients' => ['nutrient_name', 'product_applied', 'unit', 'application_method', 'technician', 'institution_source', 'attachment'],
            
            // Expenses
            'expenses' => ['expense_name', 'work_name', 'expense_type', 'worker_name'],
            
            // Attachments
            'attachments' => ['title', 'file_path', 'file_name', 'mime_type'],
            
            // Tasks
            'tasks' => ['title', 'association', 'new_status', 'new_priority', 'task_color'],
            
            // Task Templates
            'task_templates' => ['name', 'description'],
            
            // Task Series
            'task_series' => ['name', 'description'],
            
            // Plant Notes
            'plant_notes' => ['keywords', 'attachment_path'],
            
            // Plant Photos
            'plant_photos' => ['file_path', 'file_name', 'mime_type'],
            
            // Planting Location Notes
            'planting_location_notes' => ['title', 'keywords', 'attachment_path'],
            
            // Planting Location Photos
            'planting_location_photos' => ['file_path', 'file_name', 'mime_type'],
            
            // Planting Losses
            'planting_losses' => ['loss_reason'],
            
            // Inventory Notes
            'inventory_notes' => [],
            
            // Inventory Photos
            'inventory_photos' => ['photo_path', 'caption'],
            
            // Inventory Type Seeds
            'inventory_type_seeds' => ['seed_unit', 'seed_per_unit_unit', 'total_seed_unit'],
            
            // Seed Histories
            'seed_histories' => ['action'],
        ];

        foreach ($tables as $tableName => $fields) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            foreach ($fields as $field) {
                if (Schema::hasColumn($tableName, $field)) {
                    try {
                        // Gunakan DB::statement untuk mengubah ukuran kolom
                        // Periksa tipe database
                        $driver = DB::getDriverName();
                        
                        if ($driver === 'mysql') {
                            DB::statement("ALTER TABLE `{$tableName}` MODIFY COLUMN `{$field}` VARCHAR(50)");
                        } elseif ($driver === 'pgsql') {
                            DB::statement("ALTER TABLE \"{$tableName}\" ALTER COLUMN \"{$field}\" TYPE VARCHAR(50)");
                        } elseif ($driver === 'sqlite') {
                            // SQLite tidak mendukung ALTER COLUMN langsung
                            // Perlu membuat tabel baru dan copy data
                            // Skip untuk SQLite karena kompleks
                            continue;
                        }
                    } catch (\Exception $e) {
                        // Log error tapi lanjutkan ke field berikutnya
                        \Log::warning("Failed to modify column {$tableName}.{$field}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     * Mengembalikan ukuran VARCHAR menjadi 255 (default Laravel)
     */
    public function down(): void
    {
        // Daftar semua tabel dan field yang perlu dikembalikan
        $tables = [
            'users' => ['name', 'email', 'photo_path', 'full_name', 'organization', 'position', 'nip', 'primary_phone', 'secondary_phone', 'province', 'city', 'district', 'village', 'location_placement'],
            'plant_types' => ['name', 'category'],
            'plants' => ['name', 'variety'],
            'planting_locations' => ['name', 'location_summary', 'administrative_address', 'google_maps_link', 'primary_photo_path', 'planting_format_custom', 'map_size', 'light_condition', 'land_status', 'ownership_status', 'water_source', 'soil_type', 'location_type_custom'],
            'plantings' => ['bed_label', 'start_method', 'germination_stage', 'light_profile', 'soil_condition', 'planting_format', 'planting_format_custom', 'harvest_unit'],
            'harvests' => ['batch_no', 'source', 'quality', 'unit', 'harvest_unit'],
            'certifications' => ['certification_status', 'seed_class_requested'],
            'certification_reports' => ['report_number_bpsb', 'growing_season', 'inspection_phase', 'inspector_name', 'reporter_name', 'seed_class_result', 'isolation_north', 'isolation_east', 'isolation_south', 'isolation_west', 'scan_file_path', 'report_type', 'seed_unit', 'certified_seed_unit', 'harvest_per_unit_unit'],
            'warehouses' => ['name', 'internal_id', 'tracking_type'],
            'bins' => ['name', 'internal_id', 'capacity_unit'],
            'inventory_types' => ['category', 'name', 'sku', 'electronic_id', 'unit', 'low_stock_unit', 'low_stock_email'],
            'inventory_lots' => ['production_id', 'status', 'stock_unit'],
            'inventory_transactions' => ['transaction_type', 'unit', 'reason'],
            'sales' => ['receipt_number', 'buyer_name', 'buyer_contact', 'buyer_nik', 'buyer_category_custom', 'destination_province', 'destination_city', 'destination_district', 'destination_village', 'planned_location_name', 'payment_method', 'payment_status'],
            'sale_items' => ['unit'],
            'treatments' => ['treatment_name', 'treatment_type', 'product_detail', 'opt_institution', 'application_method', 'technician', 'institution_source', 'attachment', 'batch_number', 'treatment_location', 'unit_measurement', 'keywords'],
            'nutrients' => ['nutrient_name', 'product_applied', 'unit', 'application_method', 'technician', 'institution_source', 'attachment'],
            'expenses' => ['expense_name', 'work_name', 'expense_type', 'worker_name'],
            'attachments' => ['title', 'file_path', 'file_name', 'mime_type'],
            'tasks' => ['title', 'association', 'new_status', 'new_priority', 'task_color'],
            'task_templates' => ['name', 'description'],
            'task_series' => ['name', 'description'],
            'plant_notes' => ['keywords', 'attachment_path'],
            'plant_photos' => ['file_path', 'file_name', 'mime_type'],
            'planting_location_notes' => ['title', 'keywords', 'attachment_path'],
            'planting_location_photos' => ['file_path', 'file_name', 'mime_type'],
            'planting_losses' => ['loss_reason'],
            'inventory_photos' => ['photo_path', 'caption'],
            'inventory_type_seeds' => ['seed_unit', 'seed_per_unit_unit', 'total_seed_unit'],
            'seed_histories' => ['action'],
        ];

        foreach ($tables as $tableName => $fields) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            foreach ($fields as $field) {
                if (Schema::hasColumn($tableName, $field)) {
                    try {
                        $driver = DB::getDriverName();
                        
                        if ($driver === 'mysql') {
                            DB::statement("ALTER TABLE `{$tableName}` MODIFY COLUMN `{$field}` VARCHAR(255)");
                        } elseif ($driver === 'pgsql') {
                            DB::statement("ALTER TABLE \"{$tableName}\" ALTER COLUMN \"{$field}\" TYPE VARCHAR(255)");
                        }
                    } catch (\Exception $e) {
                        \Log::warning("Failed to revert column {$tableName}.{$field}: " . $e->getMessage());
                    }
                }
            }
        }
    }
};
