<?php

namespace App\Console\Commands;

use App\Models\InventoryType;
use App\Models\InventoryTypeSeed;
use App\Models\SeedHistory;
use App\Models\InventoryNote;
use App\Models\InventoryPhoto;
use App\Models\InventoryTransaction;
use App\Models\InventoryLot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteAllInventoryTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:delete-all {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all inventory types and their related data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Apakah Anda yakin ingin menghapus SEMUA data stok bibit? Tindakan ini tidak dapat dibatalkan!')) {
                $this->info('Operasi dibatalkan.');
                return 0;
            }
        }

        $this->info('Memulai penghapusan semua data stok bibit...');
        
        try {
            DB::beginTransaction();

            $inventoryTypes = InventoryType::all();
            $totalCount = $inventoryTypes->count();
            
            if ($totalCount === 0) {
                $this->info('Tidak ada data stok bibit yang perlu dihapus.');
                return 0;
            }

            $this->info("Menemukan {$totalCount} tipe bibit yang akan dihapus...");
            $bar = $this->output->createProgressBar($totalCount);
            $bar->start();

            foreach ($inventoryTypes as $inventoryType) {
                // Delete seeds and their histories
                $seeds = $inventoryType->seeds()->get();
                foreach ($seeds as $seed) {
                    $seed->histories()->delete();
                }
                $inventoryType->seeds()->delete();

                // Delete notes
                $inventoryType->notes()->delete();

                // Delete photos and their files
                $photos = $inventoryType->photos()->get();
                foreach ($photos as $photo) {
                    if ($photo->file_path && Storage::exists($photo->file_path)) {
                        Storage::delete($photo->file_path);
                    }
                }
                $inventoryType->photos()->delete();

                // Delete transactions
                $inventoryType->transactions()->delete();

                // Delete lots
                $inventoryType->lots()->delete();

                // Delete pivot table relationships
                $inventoryType->warehouses()->detach();
                $inventoryType->certificationReports()->detach();

                // Delete inventory type
                $inventoryType->delete();

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            DB::commit();

            $this->info("✓ Berhasil menghapus {$totalCount} tipe bibit beserta semua data terkait.");
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
}

