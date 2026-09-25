<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS inventory_type_seeds');

        DB::statement("
            CREATE VIEW inventory_type_seeds AS
            SELECT
                cr.certification_report_id AS inventory_type_seed_id,
                cr.inventory_type_id AS inventory_type_id,
                cr.certification_report_id AS certification_report_id,
                cr.certified_seed_quantity AS quantity,
                cr.seed_unit AS seed_unit,
                cr.seed_unit_quantity AS seed_unit_quantity,
                cr.package_content_per_pack AS seed_per_unit,
                cr.harvest_per_unit_unit AS seed_per_unit_unit,
                cr.quantity_added_to_stock AS total_seed_quantity,
                cr.seed_unit AS total_seed_unit,
                cr.estimated_sale_price_per_kg AS estimated_sale_price_per_kg,
                cr.expiry_date AS expiry_date,
                cr.reporter_name AS filled_by_user_id,
                cr.planting_batch_number AS storage_number,
                cr.report_type AS report_type,
                cr.updated_at AS edited_at,
                cr.reporter_name AS edited_by,
                cr.created_at AS created_at,
                cr.updated_at AS updated_at
            FROM certification_reports cr
            WHERE cr.inventory_type_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS inventory_type_seeds');
    }
};

