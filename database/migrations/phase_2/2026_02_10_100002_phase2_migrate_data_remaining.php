<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FASE 2: Migrasi data untuk tabel Certification, Inventory, Sales, dan Support.
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

        // ========== CERTIFICATION TABLES ==========
        
        // 1. CERTIFICATIONS TABLE
        echo "Migrating certifications table...\n";
        if (Schema::hasTable('certifications')) {
            $certifications = DB::table('certifications')->whereNull('certification_id')->get();
            foreach ($certifications as $cert) {
                do {
                    $customId = $generateCustomId('CRT');
                    $exists = DB::table('certifications')->where('certification_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['certification_id' => $customId];
                
                if ($cert->plant_id) {
                    $plant = DB::table('plants')->where('id', $cert->plant_id)->first();
                    if ($plant && $plant->plant_id) {
                        $updateData['new_plant_id'] = $plant->plant_id;
                    }
                }
                
                if ($cert->user_id) {
                    $user = DB::table('users')->where('id', $cert->user_id)->first();
                    if ($user && $user->user_id) {
                        $updateData['new_user_id'] = $user->user_id;
                    }
                }
                
                DB::table('certifications')->where('id', $cert->id)->update($updateData);
            }
        }

        // 2. CERTIFICATION_REPORTS TABLE
        echo "Migrating certification_reports table...\n";
        if (Schema::hasTable('certification_reports')) {
            $reports = DB::table('certification_reports')->whereNull('certification_report_id')->get();
            foreach ($reports as $report) {
                do {
                    $customId = $generateCustomId('CRP');
                    $exists = DB::table('certification_reports')->where('certification_report_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['certification_report_id' => $customId];
                
                if ($report->certification_id) {
                    $cert = DB::table('certifications')->where('id', $report->certification_id)->first();
                    if ($cert && $cert->certification_id) {
                        $updateData['new_certification_id'] = $cert->certification_id;
                    }
                }
                
                if ($report->user_id) {
                    $user = DB::table('users')->where('id', $report->user_id)->first();
                    if ($user && $user->user_id) {
                        $updateData['new_user_id'] = $user->user_id;
                    }
                }
                
                DB::table('certification_reports')->where('id', $report->id)->update($updateData);
            }
        }

        // ========== INVENTORY & WAREHOUSE TABLES ==========
        
        // 3. WAREHOUSES TABLE
        echo "Migrating warehouses table...\n";
        if (Schema::hasTable('warehouses')) {
            $warehouses = DB::table('warehouses')->whereNull('warehouse_id')->get();
            foreach ($warehouses as $warehouse) {
                do {
                    $customId = $generateCustomId('WHS');
                    $exists = DB::table('warehouses')->where('warehouse_id', $customId)->exists();
                } while ($exists);
                
                DB::table('warehouses')->where('id', $warehouse->id)->update(['warehouse_id' => $customId]);
            }
        }

        // 4. BINS TABLE
        echo "Migrating bins table...\n";
        if (Schema::hasTable('bins')) {
            $bins = DB::table('bins')->whereNull('bin_id')->get();
            foreach ($bins as $bin) {
                do {
                    $customId = $generateCustomId('BIN');
                    $exists = DB::table('bins')->where('bin_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['bin_id' => $customId];
                
                if ($bin->warehouse_id) {
                    $warehouse = DB::table('warehouses')->where('id', $bin->warehouse_id)->first();
                    if ($warehouse && $warehouse->warehouse_id) {
                        $updateData['new_warehouse_id'] = $warehouse->warehouse_id;
                    }
                }
                
                DB::table('bins')->where('id', $bin->id)->update($updateData);
            }
        }

        // 5. INVENTORY_TYPES TABLE
        echo "Migrating inventory_types table...\n";
        if (Schema::hasTable('inventory_types')) {
            $inventoryTypes = DB::table('inventory_types')->whereNull('inventory_type_id')->get();
            foreach ($inventoryTypes as $type) {
                do {
                    $customId = $generateCustomId('INV');
                    $exists = DB::table('inventory_types')->where('inventory_type_id', $customId)->exists();
                } while ($exists);
                
                DB::table('inventory_types')->where('id', $type->id)->update(['inventory_type_id' => $customId]);
            }
        }

        // 6. INVENTORY_LOTS TABLE
        echo "Migrating inventory_lots table...\n";
        if (Schema::hasTable('inventory_lots')) {
            $lots = DB::table('inventory_lots')->whereNull('inventory_lot_id')->get();
            foreach ($lots as $lot) {
                do {
                    $customId = $generateCustomId('LOT');
                    $exists = DB::table('inventory_lots')->where('inventory_lot_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['inventory_lot_id' => $customId];
                
                if ($lot->inventory_type_id) {
                    $type = DB::table('inventory_types')->where('id', $lot->inventory_type_id)->first();
                    if ($type && $type->inventory_type_id) {
                        $updateData['new_inventory_type_id'] = $type->inventory_type_id;
                    }
                }
                
                if ($lot->bin_id) {
                    $bin = DB::table('bins')->where('id', $lot->bin_id)->first();
                    if ($bin && $bin->bin_id) {
                        $updateData['new_bin_id'] = $bin->bin_id;
                    }
                }
                
                DB::table('inventory_lots')->where('id', $lot->id)->update($updateData);
            }
        }

        // 7. INVENTORY_TRANSACTIONS TABLE
        echo "Migrating inventory_transactions table...\n";
        if (Schema::hasTable('inventory_transactions')) {
            $transactions = DB::table('inventory_transactions')->whereNull('inventory_transaction_id')->get();
            foreach ($transactions as $transaction) {
                do {
                    $customId = $generateCustomId('TRX');
                    $exists = DB::table('inventory_transactions')->where('inventory_transaction_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['inventory_transaction_id' => $customId];
                
                if ($transaction->inventory_lot_id) {
                    $lot = DB::table('inventory_lots')->where('id', $transaction->inventory_lot_id)->first();
                    if ($lot && $lot->inventory_lot_id) {
                        $updateData['new_inventory_lot_id'] = $lot->inventory_lot_id;
                    }
                }
                
                if ($transaction->user_id) {
                    $user = DB::table('users')->where('id', $transaction->user_id)->first();
                    if ($user && $user->user_id) {
                        $updateData['new_user_id'] = $user->user_id;
                    }
                }
                
                DB::table('inventory_transactions')->where('id', $transaction->id)->update($updateData);
            }
        }

        // 8. INVENTORY_TYPE_WAREHOUSES TABLE
        echo "Migrating inventory_type_warehouses table...\n";
        if (Schema::hasTable('inventory_type_warehouses')) {
            $typeWarehouses = DB::table('inventory_type_warehouses')->whereNull('inventory_type_warehouse_id')->get();
            foreach ($typeWarehouses as $tw) {
                do {
                    $customId = $generateCustomId('ITW');
                    $exists = DB::table('inventory_type_warehouses')->where('inventory_type_warehouse_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['inventory_type_warehouse_id' => $customId];
                
                if ($tw->inventory_type_id) {
                    $type = DB::table('inventory_types')->where('id', $tw->inventory_type_id)->first();
                    if ($type && $type->inventory_type_id) {
                        $updateData['new_inventory_type_id'] = $type->inventory_type_id;
                    }
                }
                
                if ($tw->warehouse_id) {
                    $warehouse = DB::table('warehouses')->where('id', $tw->warehouse_id)->first();
                    if ($warehouse && $warehouse->warehouse_id) {
                        $updateData['new_warehouse_id'] = $warehouse->warehouse_id;
                    }
                }
                
                DB::table('inventory_type_warehouses')->where('id', $tw->id)->update($updateData);
            }
        }

        // 9. INVENTORY_TYPE_SEEDS TABLE
        echo "Migrating inventory_type_seeds table...\n";
        if (Schema::hasTable('inventory_type_seeds')) {
            $seeds = DB::table('inventory_type_seeds')->whereNull('inventory_type_seed_id')->get();
            foreach ($seeds as $seed) {
                do {
                    $customId = $generateCustomId('ITS');
                    $exists = DB::table('inventory_type_seeds')->where('inventory_type_seed_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['inventory_type_seed_id' => $customId];
                
                if ($seed->inventory_type_id) {
                    $type = DB::table('inventory_types')->where('id', $seed->inventory_type_id)->first();
                    if ($type && $type->inventory_type_id) {
                        $updateData['new_inventory_type_id'] = $type->inventory_type_id;
                    }
                }
                
                if ($seed->plant_type_id) {
                    $plantType = DB::table('plant_types')->where('id', $seed->plant_type_id)->first();
                    if ($plantType && $plantType->plant_type_id) {
                        $updateData['new_plant_type_id'] = $plantType->plant_type_id;
                    }
                }
                
                DB::table('inventory_type_seeds')->where('id', $seed->id)->update($updateData);
            }
        }

        // 10. INVENTORY_TYPE_CERTIFICATION_REPORTS TABLE
        echo "Migrating inventory_type_certification_reports table...\n";
        if (Schema::hasTable('inventory_type_certification_reports')) {
            $icrs = DB::table('inventory_type_certification_reports')->whereNull('inventory_type_certification_report_id')->get();
            foreach ($icrs as $icr) {
                do {
                    $customId = $generateCustomId('ICR');
                    $exists = DB::table('inventory_type_certification_reports')->where('inventory_type_certification_report_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['inventory_type_certification_report_id' => $customId];
                
                if ($icr->inventory_type_id) {
                    $type = DB::table('inventory_types')->where('id', $icr->inventory_type_id)->first();
                    if ($type && $type->inventory_type_id) {
                        $updateData['new_inventory_type_id'] = $type->inventory_type_id;
                    }
                }
                
                if ($icr->certification_report_id) {
                    $report = DB::table('certification_reports')->where('id', $icr->certification_report_id)->first();
                    if ($report && $report->certification_report_id) {
                        $updateData['new_certification_report_id'] = $report->certification_report_id;
                    }
                }
                
                DB::table('inventory_type_certification_reports')->where('id', $icr->id)->update($updateData);
            }
        }

        // 11. INVENTORY_NOTES TABLE
        echo "Migrating inventory_notes table...\n";
        if (Schema::hasTable('inventory_notes')) {
            $notes = DB::table('inventory_notes')->whereNull('inventory_note_id')->get();
            foreach ($notes as $note) {
                do {
                    $customId = $generateCustomId('INN');
                    $exists = DB::table('inventory_notes')->where('inventory_note_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['inventory_note_id' => $customId];
                
                if ($note->inventory_lot_id) {
                    $lot = DB::table('inventory_lots')->where('id', $note->inventory_lot_id)->first();
                    if ($lot && $lot->inventory_lot_id) {
                        $updateData['new_inventory_lot_id'] = $lot->inventory_lot_id;
                    }
                }
                
                if ($note->user_id) {
                    $user = DB::table('users')->where('id', $note->user_id)->first();
                    if ($user && $user->user_id) {
                        $updateData['new_user_id'] = $user->user_id;
                    }
                }
                
                DB::table('inventory_notes')->where('id', $note->id)->update($updateData);
            }
        }

        // 12. INVENTORY_PHOTOS TABLE
        echo "Migrating inventory_photos table...\n";
        if (Schema::hasTable('inventory_photos')) {
            $photos = DB::table('inventory_photos')->whereNull('inventory_photo_id')->get();
            foreach ($photos as $photo) {
                do {
                    $customId = $generateCustomId('INP');
                    $exists = DB::table('inventory_photos')->where('inventory_photo_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['inventory_photo_id' => $customId];
                
                if ($photo->inventory_lot_id) {
                    $lot = DB::table('inventory_lots')->where('id', $photo->inventory_lot_id)->first();
                    if ($lot && $lot->inventory_lot_id) {
                        $updateData['new_inventory_lot_id'] = $lot->inventory_lot_id;
                    }
                }
                
                DB::table('inventory_photos')->where('id', $photo->id)->update($updateData);
            }
        }

        // 13. SEED_HISTORIES TABLE
        echo "Migrating seed_histories table...\n";
        if (Schema::hasTable('seed_histories')) {
            $histories = DB::table('seed_histories')->whereNull('seed_history_id')->get();
            foreach ($histories as $history) {
                do {
                    $customId = $generateCustomId('SDH');
                    $exists = DB::table('seed_histories')->where('seed_history_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['seed_history_id' => $customId];
                
                if ($history->inventory_type_seed_id) {
                    $seed = DB::table('inventory_type_seeds')->where('id', $history->inventory_type_seed_id)->first();
                    if ($seed && $seed->inventory_type_seed_id) {
                        $updateData['new_inventory_type_seed_id'] = $seed->inventory_type_seed_id;
                    }
                }
                
                DB::table('seed_histories')->where('id', $history->id)->update($updateData);
            }
        }

        // ========== SALES TABLES ==========
        
        // 14. SALES TABLE
        echo "Migrating sales table...\n";
        if (Schema::hasTable('sales')) {
            $sales = DB::table('sales')->whereNull('sale_id')->get();
            foreach ($sales as $sale) {
                do {
                    $customId = $generateCustomId('SAL');
                    $exists = DB::table('sales')->where('sale_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['sale_id' => $customId];
                
                if ($sale->user_id) {
                    $user = DB::table('users')->where('id', $sale->user_id)->first();
                    if ($user && $user->user_id) {
                        $updateData['new_user_id'] = $user->user_id;
                    }
                }
                
                DB::table('sales')->where('id', $sale->id)->update($updateData);
            }
        }

        // 15. SALE_ITEMS TABLE
        echo "Migrating sale_items table...\n";
        if (Schema::hasTable('sale_items')) {
            $saleItems = DB::table('sale_items')->whereNull('sale_item_id')->get();
            foreach ($saleItems as $item) {
                do {
                    $customId = $generateCustomId('SIT');
                    $exists = DB::table('sale_items')->where('sale_item_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['sale_item_id' => $customId];
                
                if ($item->sale_id) {
                    $sale = DB::table('sales')->where('id', $item->sale_id)->first();
                    if ($sale && $sale->sale_id) {
                        $updateData['new_sale_id'] = $sale->sale_id;
                    }
                }
                
                if ($item->inventory_lot_id) {
                    $lot = DB::table('inventory_lots')->where('id', $item->inventory_lot_id)->first();
                    if ($lot && $lot->inventory_lot_id) {
                        $updateData['new_inventory_lot_id'] = $lot->inventory_lot_id;
                    }
                }
                
                DB::table('sale_items')->where('id', $item->id)->update($updateData);
            }
        }

        // ========== SUPPORT TABLES ==========
        
        // 16. TASK_SERIES TABLE
        echo "Migrating task_series table...\n";
        if (Schema::hasTable('task_series')) {
            $taskSeries = DB::table('task_series')->whereNull('task_series_id')->get();
            foreach ($taskSeries as $series) {
                do {
                    $customId = $generateCustomId('TSR');
                    $exists = DB::table('task_series')->where('task_series_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['task_series_id' => $customId];
                
                if ($series->user_id) {
                    $user = DB::table('users')->where('id', $series->user_id)->first();
                    if ($user && $user->user_id) {
                        $updateData['new_user_id'] = $user->user_id;
                    }
                }
                
                DB::table('task_series')->where('id', $series->id)->update($updateData);
            }
        }

        // 17. TASK_TEMPLATES TABLE
        echo "Migrating task_templates table...\n";
        if (Schema::hasTable('task_templates')) {
            $templates = DB::table('task_templates')->whereNull('task_template_id')->get();
            foreach ($templates as $template) {
                do {
                    $customId = $generateCustomId('TTP');
                    $exists = DB::table('task_templates')->where('task_template_id', $customId)->exists();
                } while ($exists);
                
                DB::table('task_templates')->where('id', $template->id)->update(['task_template_id' => $customId]);
            }
        }

        // 18. TASKS TABLE
        echo "Migrating tasks table...\n";
        if (Schema::hasTable('tasks')) {
            $tasks = DB::table('tasks')->whereNull('task_id')->get();
            foreach ($tasks as $task) {
                do {
                    $customId = $generateCustomId('TSK');
                    $exists = DB::table('tasks')->where('task_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['task_id' => $customId];
                
                if ($task->user_id) {
                    $user = DB::table('users')->where('id', $task->user_id)->first();
                    if ($user && $user->user_id) {
                        $updateData['new_user_id'] = $user->user_id;
                    }
                }
                
                if ($task->assigned_to) {
                    $assignedUser = DB::table('users')->where('id', $task->assigned_to)->first();
                    if ($assignedUser && $assignedUser->user_id) {
                        $updateData['new_assigned_to'] = $assignedUser->user_id;
                    }
                }
                
                if ($task->task_series_id) {
                    $series = DB::table('task_series')->where('id', $task->task_series_id)->first();
                    if ($series && $series->task_series_id) {
                        $updateData['new_task_series_id'] = $series->task_series_id;
                    }
                }
                
                DB::table('tasks')->where('id', $task->id)->update($updateData);
            }
        }

        // 19. EXPENSES TABLE
        echo "Migrating expenses table...\n";
        if (Schema::hasTable('expenses')) {
            $expenses = DB::table('expenses')->whereNull('expense_id')->get();
            foreach ($expenses as $expense) {
                do {
                    $customId = $generateCustomId('EXP');
                    $exists = DB::table('expenses')->where('expense_id', $customId)->exists();
                } while ($exists);
                
                $updateData = ['expense_id' => $customId];
                
                if ($expense->user_id) {
                    $user = DB::table('users')->where('id', $expense->user_id)->first();
                    if ($user && $user->user_id) {
                        $updateData['new_user_id'] = $user->user_id;
                    }
                }
                
                if ($expense->planting_id) {
                    $planting = DB::table('plantings')->where('id', $expense->planting_id)->first();
                    if ($planting && $planting->planting_id) {
                        $updateData['new_planting_id'] = $planting->planting_id;
                    }
                }
                
                DB::table('expenses')->where('id', $expense->id)->update($updateData);
            }
        }

        // 20. ATTACHMENTS TABLE
        echo "Migrating attachments table...\n";
        if (Schema::hasTable('attachments')) {
            $attachments = DB::table('attachments')->whereNull('attachment_id')->get();
            foreach ($attachments as $attachment) {
                do {
                    $customId = $generateCustomId('ATT');
                    $exists = DB::table('attachments')->where('attachment_id', $customId)->exists();
                } while ($exists);
                
                DB::table('attachments')->where('id', $attachment->id)->update(['attachment_id' => $customId]);
            }
        }

        echo "Phase 2 Remaining tables migration completed!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset all custom IDs to null
        $tables = [
            'certifications' => ['certification_id', 'new_plant_id', 'new_user_id'],
            'certification_reports' => ['certification_report_id', 'new_certification_id', 'new_user_id'],
            'warehouses' => ['warehouse_id'],
            'bins' => ['bin_id', 'new_warehouse_id'],
            'inventory_types' => ['inventory_type_id'],
            'inventory_lots' => ['inventory_lot_id', 'new_inventory_type_id', 'new_bin_id'],
            'inventory_transactions' => ['inventory_transaction_id', 'new_inventory_lot_id', 'new_user_id'],
            'inventory_type_warehouses' => ['inventory_type_warehouse_id', 'new_inventory_type_id', 'new_warehouse_id'],
            'inventory_type_seeds' => ['inventory_type_seed_id', 'new_inventory_type_id', 'new_plant_type_id'],
            'inventory_type_certification_reports' => ['inventory_type_certification_report_id', 'new_inventory_type_id', 'new_certification_report_id'],
            'inventory_notes' => ['inventory_note_id', 'new_inventory_lot_id', 'new_user_id'],
            'inventory_photos' => ['inventory_photo_id', 'new_inventory_lot_id'],
            'seed_histories' => ['seed_history_id', 'new_inventory_type_seed_id'],
            'sales' => ['sale_id', 'new_user_id'],
            'sale_items' => ['sale_item_id', 'new_sale_id', 'new_inventory_lot_id'],
            'task_series' => ['task_series_id', 'new_user_id'],
            'task_templates' => ['task_template_id'],
            'tasks' => ['task_id', 'new_user_id', 'new_assigned_to', 'new_task_series_id'],
            'expenses' => ['expense_id', 'new_user_id', 'new_planting_id'],
            'attachments' => ['attachment_id'],
        ];

        foreach ($tables as $table => $columns) {
            if (Schema::hasTable($table)) {
                $updateData = [];
                foreach ($columns as $column) {
                    $updateData[$column] = null;
                }
                DB::table($table)->update($updateData);
            }
        }
    }
};
