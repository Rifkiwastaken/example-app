<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Gabungkan inventory_transactions dan seed_history ke stock_histories.
     * Logika pengurangan dan distribusi stok (stok benih ditambahkan ke bin,
     * stok dihapus, stok dijual) tercatat di stock_histories. Data stok benih
     * di-update di bin dan di data stok benih.
     * - Pindahkan semua kolom dari inventory_transactions ke stock_histories
     * - Salin data dari inventory_transactions ke stock_histories
     * - Hapus tabel inventory_transactions
     */
    public function up(): void
    {
        $historyTable = Schema::hasTable('stock_histories') ? 'stock_histories' : 'seed_histories';
        if (!Schema::hasTable('inventory_transactions')) {
            return;
        }

        Schema::table($historyTable, function (Blueprint $table) use ($historyTable) {
            if (!Schema::hasColumn($historyTable, 'warehouse_lot_id')) {
                $table->string('warehouse_lot_id', 36)->nullable()->after('inventory_type_id');
            }
            if (!Schema::hasColumn($historyTable, 'transaction_type')) {
                $table->string('transaction_type', 50)->nullable()->after('warehouse_lot_id');
            }
            if (!Schema::hasColumn($historyTable, 'quantity')) {
                $table->decimal('quantity', 15, 2)->nullable()->after('transaction_type');
            }
            if (!Schema::hasColumn($historyTable, 'unit')) {
                $table->string('unit', 50)->nullable()->after('quantity');
            }
            if (!Schema::hasColumn($historyTable, 'warehouse_id')) {
                $table->string('warehouse_id', 36)->nullable()->after('unit');
            }
            if (!Schema::hasColumn($historyTable, 'warehouse_bin_id')) {
                $table->string('warehouse_bin_id', 36)->nullable()->after('warehouse_id');
            }
            if (!Schema::hasColumn($historyTable, 'reason')) {
                $table->string('reason', 255)->nullable()->after('warehouse_bin_id');
            }
            if (!Schema::hasColumn($historyTable, 'notes')) {
                $table->text('notes')->nullable()->after('reason');
            }
        });

        $pkCol = Schema::hasColumn($historyTable, 'stock_history_id') ? 'stock_history_id' : 'seed_history_id';

        // Migrate data: insert each inventory_transaction as a row in stock_histories
        $transactions = DB::table('inventory_transactions')->get();
        $prefix = $historyTable === 'stock_histories' ? 'STH' : 'SHD';
        foreach ($transactions as $tx) {
            $newId = $prefix . '-' . strtoupper(Str::random(8));
            $exists = DB::table($historyTable)->where($pkCol, $newId)->exists();
            while ($exists) {
                $newId = $prefix . '-' . strtoupper(Str::random(8));
                $exists = DB::table($historyTable)->where($pkCol, $newId)->exists();
            }

            DB::table($historyTable)->insert([
                $pkCol => $newId,
                'inventory_type_seed_id' => null,
                'inventory_type_id' => $tx->inventory_type_id,
                'warehouse_lot_id' => $tx->warehouse_lot_id ?? $tx->inventory_lot_id ?? null,
                'transaction_type' => $tx->transaction_type,
                'quantity' => $tx->quantity,
                'unit' => $tx->unit ?? 'kg',
                'warehouse_id' => $tx->warehouse_id,
                'warehouse_bin_id' => $tx->warehouse_bin_id ?? $tx->bin_id ?? null,
                'reason' => $tx->reason,
                'notes' => $tx->notes,
                'action' => $tx->transaction_type,
                'description' => $tx->notes,
                'user_id' => $tx->user_id,
                'created_at' => $tx->created_at ?? now(),
                'updated_at' => $tx->updated_at ?? now(),
            ]);
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }
        Schema::dropIfExists('inventory_transactions');
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
        // Recreate inventory_transactions with minimal structure; data not restored
        if (!Schema::hasTable('inventory_transactions')) {
            Schema::create('inventory_transactions', function (Blueprint $table) {
                $table->string('inventory_transaction_id', 36)->primary();
                $table->string('inventory_type_id', 36);
                $table->string('warehouse_lot_id', 36)->nullable();
                $table->string('transaction_type', 50);
                $table->decimal('quantity', 15, 2);
                $table->string('unit', 50)->nullable();
                $table->string('warehouse_id', 36)->nullable();
                $table->string('warehouse_bin_id', 36)->nullable();
                $table->string('reason', 255)->nullable();
                $table->text('notes')->nullable();
                $table->string('user_id', 36);
                $table->timestamps();
            });
        }

        $historyTable = Schema::hasTable('stock_histories') ? 'stock_histories' : 'seed_histories';
        if (Schema::hasTable($historyTable)) {
            Schema::table($historyTable, function (Blueprint $table) use ($historyTable) {
                $cols = ['warehouse_lot_id', 'transaction_type', 'quantity', 'unit', 'warehouse_id', 'warehouse_bin_id', 'reason', 'notes'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn($historyTable, $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
