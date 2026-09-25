<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sesuaikan nama kolom PK (dan FK yang merujuk ke mereka) dengan nama tabel baru.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $this->dropFkConstraints();
        $this->renamePrimaryKeyColumns();
        $this->renameForeignKeyColumns();
        $this->addFkConstraints();
    }

    protected function dropFkConstraints(): void
    {
        $pairs = [
            ['expenses', 'nutrient_id'],
            ['expenses', 'treatment_id'],
            ['inventory_transactions', 'inventory_lot_id'],
            ['inventory_transactions', 'bin_id'],
            ['sale_items', 'inventory_lot_id'],
            ['warehouse_lots', 'bin_id'],
            ['warehouse_type', 'bin_id'],
        ];
        foreach ($pairs as [$table, $column]) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
                continue;
            }
            $rows = DB::select(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL",
                [$table, $column]
            );
            if (!empty($rows)) {
                DB::statement('ALTER TABLE `' . $table . '` DROP FOREIGN KEY `' . $rows[0]->CONSTRAINT_NAME . '`');
            }
        }
    }

    protected function renamePrimaryKeyColumns(): void
    {
        $renames = [
            'planting_notes' => ['planting_location_note_id', 'planting_note_id', 'VARCHAR(36)'],
            'planting_nutrients' => ['nutrient_id', 'planting_nutrient_id', 'VARCHAR(36)'],
            'planting_treatments' => ['treatment_id', 'planting_treatment_id', 'VARCHAR(36)'],
            'planting_tasks' => ['task_id', 'planting_task_id', 'VARCHAR(36)'],
            'planting_attachments' => ['attachment_id', 'planting_attachment_id', 'VARCHAR(36)'],
            'warehouse_lots' => ['inventory_lot_id', 'warehouse_lot_id', 'VARCHAR(36)'],
            'warehouse_bins' => ['bin_id', 'warehouse_bin_id', 'VARCHAR(36)'],
            'warehouse_type' => ['inventory_type_warehouse_id', 'warehouse_type_id', 'VARCHAR(36)'],
        ];
        foreach ($renames as $table => [$old, $new, $type]) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $old)) {
                continue;
            }
            DB::statement("ALTER TABLE `{$table}` CHANGE `{$old}` `{$new}` {$type} NOT NULL");
        }
    }

    protected function renameForeignKeyColumns(): void
    {
        $renames = [
            'expenses' => [['nutrient_id', 'planting_nutrient_id', 'VARCHAR(36)'], ['treatment_id', 'planting_treatment_id', 'VARCHAR(36)']],
            'inventory_transactions' => [['inventory_lot_id', 'warehouse_lot_id', 'VARCHAR(36)'], ['bin_id', 'warehouse_bin_id', 'VARCHAR(36)']],
            'sale_items' => [['inventory_lot_id', 'warehouse_lot_id', 'VARCHAR(36)']],
            'warehouse_lots' => [['bin_id', 'warehouse_bin_id', 'VARCHAR(36)']],
            'warehouse_type' => [['bin_id', 'warehouse_bin_id', 'VARCHAR(36)']],
        ];
        foreach ($renames as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            foreach ($columns as [$old, $new, $type]) {
                if (!Schema::hasColumn($table, $old)) {
                    continue;
                }
                DB::statement("ALTER TABLE `{$table}` CHANGE `{$old}` `{$new}` {$type} NULL");
            }
        }
    }

    protected function addFkConstraints(): void
    {
        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'planting_nutrient_id')) {
            DB::statement('ALTER TABLE `expenses` ADD CONSTRAINT `expenses_planting_nutrient_id_foreign` FOREIGN KEY (`planting_nutrient_id`) REFERENCES `planting_nutrients`(`planting_nutrient_id`) ON DELETE CASCADE');
        }
        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'planting_treatment_id')) {
            DB::statement('ALTER TABLE `expenses` ADD CONSTRAINT `expenses_planting_treatment_id_foreign` FOREIGN KEY (`planting_treatment_id`) REFERENCES `planting_treatments`(`planting_treatment_id`) ON DELETE CASCADE');
        }
        if (Schema::hasTable('inventory_transactions') && Schema::hasColumn('inventory_transactions', 'warehouse_lot_id')) {
            DB::statement('ALTER TABLE `inventory_transactions` ADD CONSTRAINT `inventory_transactions_warehouse_lot_id_foreign` FOREIGN KEY (`warehouse_lot_id`) REFERENCES `warehouse_lots`(`warehouse_lot_id`) ON DELETE SET NULL');
        }
        if (Schema::hasTable('inventory_transactions') && Schema::hasColumn('inventory_transactions', 'warehouse_bin_id')) {
            DB::statement('ALTER TABLE `inventory_transactions` ADD CONSTRAINT `inventory_transactions_warehouse_bin_id_foreign` FOREIGN KEY (`warehouse_bin_id`) REFERENCES `warehouse_bins`(`warehouse_bin_id`) ON DELETE SET NULL');
        }
        if (Schema::hasTable('sale_items') && Schema::hasColumn('sale_items', 'warehouse_lot_id')) {
            DB::statement('ALTER TABLE `sale_items` ADD CONSTRAINT `sale_items_warehouse_lot_id_foreign` FOREIGN KEY (`warehouse_lot_id`) REFERENCES `warehouse_lots`(`warehouse_lot_id`) ON DELETE SET NULL');
        }
        if (Schema::hasTable('warehouse_lots') && Schema::hasColumn('warehouse_lots', 'warehouse_bin_id')) {
            DB::statement('ALTER TABLE `warehouse_lots` ADD CONSTRAINT `warehouse_lots_warehouse_bin_id_foreign` FOREIGN KEY (`warehouse_bin_id`) REFERENCES `warehouse_bins`(`warehouse_bin_id`) ON DELETE SET NULL');
        }
        if (Schema::hasTable('warehouse_type') && Schema::hasColumn('warehouse_type', 'warehouse_bin_id')) {
            DB::statement('ALTER TABLE `warehouse_type` ADD CONSTRAINT `warehouse_type_warehouse_bin_id_foreign` FOREIGN KEY (`warehouse_bin_id`) REFERENCES `warehouse_bins`(`warehouse_bin_id`) ON DELETE CASCADE');
        }
    }

    public function down(): void
    {
        // Reverse: drop new FKs, rename columns back (not fully implemented for brevity; restore from backup if needed)
    }
};
