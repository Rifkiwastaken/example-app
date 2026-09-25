<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('warehouses')) {
            Schema::create('warehouses', function (Blueprint $table) {
                $table->string('warehouse_id', 36)->primary();
                $table->string('name');
                $table->string('internal_id')->unique();
                $table->enum('tracking_type', ['bin_separated', 'warehouse_only'])->default('warehouse_only');
                $table->text('description')->nullable();
                $table->string('responsible_person_id', 36)->nullable();
                $table->timestamps();

                $table->foreign('responsible_person_id', 'warehouses_responsible_person_fk')
                    ->references('user_id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasTable('warehouse_bins')) {
            Schema::create('warehouse_bins', function (Blueprint $table) {
                $table->string('warehouse_bin_id', 36)->primary();
                $table->string('warehouse_id', 36);
                $table->string('name');
                $table->string('internal_id');
                $table->decimal('max_capacity', 15, 2);
                $table->string('capacity_unit')->default('kg');
                $table->text('description')->nullable();
                $table->timestamps();

                $table->unique(['warehouse_id', 'internal_id']);
                $table->foreign('warehouse_id')
                    ->references('warehouse_id')
                    ->on('warehouses')
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('warehouse_lots')) {
            Schema::create('warehouse_lots', function (Blueprint $table) {
                $table->string('warehouse_lot_id', 36)->primary();
                $table->string('inventory_type_id', 36);
                $table->string('production_id')->nullable();
                $table->date('expiry_date')->nullable();
                $table->enum('status', ['tersedia', 'segera_kadaluarsa', 'kadaluarsa', 'habis'])->default('tersedia');
                $table->decimal('initial_stock', 15, 2)->default(0);
                $table->decimal('current_stock', 15, 2)->default(0);
                $table->string('stock_unit')->default('kg');
                $table->string('warehouse_bin_id', 36)->nullable();
                $table->timestamps();

                $table->unique(['inventory_type_id', 'production_id'], 'warehouse_lots_inv_type_prod_unique');
                $table->foreign('warehouse_bin_id')
                    ->references('warehouse_bin_id')
                    ->on('warehouse_bins')
                    ->nullOnDelete();
            });

            if (Schema::hasTable('inventory_types')) {
                Schema::table('warehouse_lots', function (Blueprint $table) {
                    $table->foreign('inventory_type_id')
                        ->references('inventory_type_id')
                        ->on('inventory_types')
                        ->cascadeOnDelete();
                });
            }
        }

        $this->restoreForeign('sale_items', 'warehouse_lot_id', 'sale_items_warehouse_lot_id_foreign');
        $this->restoreForeign('stock_histories', 'warehouse_lot_id', 'stock_histories_warehouse_lot_id_foreign');
    }

    public function down(): void
    {
        $this->dropForeignIfExists('sale_items', 'sale_items_warehouse_lot_id_foreign');
        $this->dropForeignIfExists('stock_histories', 'stock_histories_warehouse_lot_id_foreign');

        Schema::dropIfExists('warehouse_lots');
        Schema::dropIfExists('warehouse_bins');
        Schema::dropIfExists('warehouses');
    }

    protected function restoreForeign(string $table, string $column, string $constraint): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column) || ! Schema::hasTable('warehouse_lots')) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($column, $constraint) {
                $blueprint->foreign($column, $constraint)
                    ->references('warehouse_lot_id')
                    ->on('warehouse_lots')
                    ->nullOnDelete();
            });
        } catch (\Throwable $e) {
        }
    }

    protected function dropForeignIfExists(string $table, string $constraint): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($constraint) {
                $blueprint->dropForeign($constraint);
            });
        } catch (\Throwable $e) {
        }
    }
};
