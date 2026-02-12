<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\PlantTypeController;
use App\Http\Controllers\PlantingLocationController;
use App\Http\Controllers\PlantingController;
use App\Http\Controllers\HarvestController;
use App\Http\Controllers\PlantNoteController;
use App\Http\Controllers\PlantPhotoController;
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\InventoryTypeController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LandingPageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public Landing Page
Route::get('/', [LandingPageController::class, 'index'])->name('landing');

// Redirect /home to login for authenticated users
Route::get('/home', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    
    // Warehouse Routes (Gudang) - Lokasi Gudang
    Route::resource('warehouse-locations', WarehouseController::class);
    Route::post('warehouse-locations/{warehouse}/bins', [WarehouseController::class, 'storeBin'])->name('warehouse-locations.bins.store');
    Route::put('warehouse-locations/{warehouse}/bins/{bin}', [WarehouseController::class, 'updateBin'])->name('warehouse-locations.bins.update');
    Route::delete('warehouse-locations/{warehouse}/bins/{bin}', [WarehouseController::class, 'destroyBin'])->name('warehouse-locations.bins.destroy');
    Route::post('warehouse-locations/{warehouse}/bins/{bin}/inventory-lots', [WarehouseController::class, 'storeInventoryLot'])->name('warehouse-locations.bins.inventory-lots.store');
    Route::delete('warehouse-locations/{warehouse}/bins/{bin}/inventory-lots/{lot}', [WarehouseController::class, 'destroyInventoryLot'])->name('warehouse-locations.bins.inventory-lots.destroy');
    Route::post('warehouse-locations/{warehouse}/bins/{bin}/inventory-lots/{lot}/reduce-stock', [WarehouseController::class, 'reduceStock'])->name('warehouse-locations.bins.inventory-lots.reduce-stock');
    Route::put('warehouse-locations/{warehouse}/bins/{bin}/inventory-lots/{lot}/update-stock', [WarehouseController::class, 'updateStock'])->name('warehouse-locations.bins.inventory-lots.update-stock');
    Route::get('warehouse-locations/{warehouse}/bins/{bin}/stocks', [WarehouseController::class, 'getBinStocks'])->name('warehouse-locations.bins.stocks');
    Route::get('warehouse-locations/{warehouse}/bins/{bin}/inventory-lots/{lot}/transactions', [WarehouseController::class, 'getLotTransactions'])->name('warehouse-locations.bins.inventory-lots.transactions');
    Route::get('warehouse-locations/inventory-types/{inventoryType}/seeds', [WarehouseController::class, 'getInventoryTypeSeeds'])->name('warehouse-locations.get-inventory-type-seeds');
    
    // API Routes for Inventory Types
    Route::get('api/inventory-types/{id}', [InventoryTypeController::class, 'getInventoryTypeData'])->name('api.inventory-types.show');

    // Warehouse Routes (Gudang) - Stok Bibit
    Route::get('seed-stock', [InventoryTypeController::class, 'index'])->name('seed-stock.index');
    Route::get('seed-stock/create', [InventoryTypeController::class, 'create'])->name('seed-stock.create');
    Route::post('seed-stock/create-step1', [InventoryTypeController::class, 'storeStep1'])->name('seed-stock.store-step1');
    Route::get('seed-stock/create-step2', [InventoryTypeController::class, 'createStep2'])->name('seed-stock.create-step2');
    Route::post('seed-stock/create-step2', [InventoryTypeController::class, 'storeStep2'])->name('seed-stock.store-step2');
    Route::get('seed-stock/create-step3', [InventoryTypeController::class, 'createStep3'])->name('seed-stock.create-step3');
    Route::post('seed-stock', [InventoryTypeController::class, 'store'])->name('seed-stock.store');
    Route::delete('seed-stock', [InventoryTypeController::class, 'destroyAll'])->name('seed-stock.destroy-all');
    
    // Certified Seeds Routes (Data Benih) - Must be before {inventoryType} routes
    Route::get('seed-stock/certified-seeds', [InventoryTypeController::class, 'certifiedSeeds'])->name('seed-stock.certified-seeds');
    Route::get('seed-stock/certified-seeds/{certificationReport}', [InventoryTypeController::class, 'showCertifiedSeedDetail'])->name('seed-stock.certified-seed-detail');
    
    // Suggested storage number for add-certified-seed form (must be before {inventoryType} routes)
    Route::get('seed-stock/suggest-storage-number', [InventoryTypeController::class, 'suggestStorageNumber'])->name('seed-stock.suggest-storage-number');

    // Add certified seed to inventory type
    Route::get('seed-stock/{inventoryType}/certifications-by-plant-type', [InventoryTypeController::class, 'getCertificationsByPlantType'])->name('seed-stock.certifications-by-plant-type');
    Route::post('seed-stock/{inventoryType}/add-certified-seed', [InventoryTypeController::class, 'addCertifiedSeed'])->name('seed-stock.add-certified-seed');
    Route::post('seed-stock/{inventoryType}/add-seed-to-warehouse', [InventoryTypeController::class, 'addSeedToWarehouse'])->name('seed-stock.add-seed-to-warehouse');
    Route::get('seed-stock/{inventoryType}/certified-seeds/{certificationReport}', [InventoryTypeController::class, 'showCertifiedSeedDetail'])->name('seed-stock.show-certified-seed-detail');
    Route::get('seed-stock/{inventoryType}/seeds/{seed}', [InventoryTypeController::class, 'showSeedDetail'])->name('seed-stock.show-seed-detail');
    Route::put('seed-stock/{inventoryType}/seeds/{seed}', [InventoryTypeController::class, 'updateSeed'])->name('seed-stock.update-seed');
    Route::delete('seed-stock/{inventoryType}/seeds/{seed}', [InventoryTypeController::class, 'destroySeed'])->name('seed-stock.destroy-seed');
    Route::post('seed-stock/{inventoryType}/seeds/{seed}/reduce-stock', [InventoryTypeController::class, 'reduceStock'])->name('seed-stock.reduce-stock');
    Route::get('seed-stock/{inventoryType}/seeds/{seed}/history', [InventoryTypeController::class, 'showSeedHistory'])->name('seed-stock.seed-history');
    Route::get('seed-stock/{inventoryType}/seeds/{seed}/storage-detail', [InventoryTypeController::class, 'showSeedStorageDetail'])->name('seed-stock.seed-storage-detail');
    
    Route::get('seed-stock/{inventoryType}', [InventoryTypeController::class, 'show'])->name('seed-stock.show');
    Route::get('seed-stock/{inventoryType}/edit', [InventoryTypeController::class, 'edit'])->name('seed-stock.edit');
    Route::put('seed-stock/{inventoryType}', [InventoryTypeController::class, 'update'])->name('seed-stock.update');
    Route::delete('seed-stock/{inventoryType}', [InventoryTypeController::class, 'destroy'])->name('seed-stock.destroy');
    Route::get('seed-stock/{inventoryType}/stock-adjustment/{action}', [InventoryTypeController::class, 'showStockAdjustment'])->name('seed-stock.show-stock-adjustment');
    Route::post('seed-stock/{inventoryType}/stock-adjustment', [InventoryTypeController::class, 'storeStockAdjustment'])->name('seed-stock.store-stock-adjustment');
    Route::post('seed-stock/{inventoryType}/notes', [InventoryTypeController::class, 'storeNote'])->name('seed-stock.store-note');
    Route::post('seed-stock/{inventoryType}/photos', [InventoryTypeController::class, 'storePhoto'])->name('seed-stock.store-photo');
    
    // Sales Routes (Penjualan)
    Route::get('sales/get-bins', [SaleController::class, 'getBins'])->name('sales.get-bins');
    Route::get('sales/get-bin-lots', [SaleController::class, 'getBinInventoryLots'])->name('sales.get-bin-lots');
    Route::get('sales/inventory-types/{inventoryType}', [SaleController::class, 'showByInventoryType'])->name('sales.by-inventory-type');
    Route::get('api/inventory-types/{id}/details', [SaleController::class, 'getInventoryTypeDetails'])->name('api.inventory-types.details');
    Route::resource('sales', SaleController::class);

    
    // Reports Routes (Laporan) - Admin & Pimpinan only
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        
        // A. Laporan Produksi & Pertanian
        Route::get('/planting-harvest', [ReportController::class, 'plantingHarvest'])->name('planting-harvest');
        Route::get('/production-supplies', [ReportController::class, 'productionSupplies'])->name('production-supplies');
        Route::get('/by-location', [ReportController::class, 'byLocation'])->name('by-location');
        
        // B. Laporan Stok & Gudang
        Route::get('/stock-position', [ReportController::class, 'stockPosition'])->name('stock-position');
        Route::get('/stock-mutation', [ReportController::class, 'stockMutation'])->name('stock-mutation');
        
        // C. Laporan Penjualan & Distribusi
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        
        // D. Laporan Sertifikasi
        Route::get('/certification', [ReportController::class, 'certification'])->name('certification');
    });

    // User Management Routes (Admin only)
    Route::middleware(['auth'])->group(function () {
        Route::resource('users', UserController::class);
    });

    // Planting Module Routes
    Route::resource('plant-types', PlantTypeController::class)->except(['show']);
    // API endpoint to get variety by plant type ID
    Route::get('api/plant-types/{id}/variety', [PlantTypeController::class, 'getVariety'])->name('api.plant-types.variety');
    Route::resource('plants', PlantController::class);
    Route::get('plants/{plant}/current-plantings', [PlantController::class, 'currentPlantings'])->name('plants.current-plantings');
    Route::get('plants/{plant}/current-plantings/{planting}/reports', [PlantController::class, 'showPlantingReports'])->name('plants.current-plantings.reports');
    Route::get('plants/{plant}/harvests', [PlantController::class, 'harvestsIndex'])->name('plants.harvests.index');
    Route::resource('planting-locations', PlantingLocationController::class);
    Route::get('planting-locations/{plantingLocation}/plantings', [PlantingLocationController::class, 'currentPlantings'])->name('planting-locations.plantings.index');
    Route::get('planting-locations/{plantingLocation}/planting-history', [PlantingLocationController::class, 'plantingHistory'])->name('planting-locations.planting-history');
    Route::get('planting-locations/{plantingLocation}/harvest-detail/{planting}/{harvest}', [PlantingLocationController::class, 'harvestDetail'])->name('planting-locations.harvest-detail');
    Route::get('planting-locations/{plantingLocation}/expenses', [PlantingLocationController::class, 'expenses'])->name('planting-locations.expenses.index');
    Route::post('planting-locations/{plantingLocation}/plantings', [PlantingLocationController::class, 'storePlanting'])->name('planting-locations.plantings.store');
    Route::get('planting-locations/{plantingLocation}/plantings/{planting}/reports', [PlantingLocationController::class, 'showPlantingReports'])->name('planting-locations.plantings.reports');
    Route::post('planting-locations/{plantingLocation}/tasks', [PlantingLocationController::class, 'storeTask'])->name('planting-locations.tasks.store');
    Route::put('planting-locations/{plantingLocation}/tasks/{task}/status', [PlantingLocationController::class, 'updateTaskStatus'])->name('planting-locations.tasks.update-status');
    Route::get('planting-locations/{plantingLocation}/tasks/{task}/view', [PlantingLocationController::class, 'viewTask'])->name('planting-locations.tasks.view');
    Route::get('planting-locations/{plantingLocation}/tasks/{task}/edit', [PlantingLocationController::class, 'editTask'])->name('planting-locations.tasks.edit');
    Route::put('planting-locations/{plantingLocation}/tasks/{task}', [PlantingLocationController::class, 'updateTask'])->name('planting-locations.tasks.update');
    Route::delete('planting-locations/{plantingLocation}/tasks/{task}', [PlantingLocationController::class, 'deleteTask'])->name('planting-locations.tasks.destroy');
    Route::put('planting-locations/{plantingLocation}/tasks/{task}/fill', [PlantingLocationController::class, 'fillTaskReport'])->name('planting-locations.tasks.fill');
    Route::get('api/task-templates/{template}', [PlantingLocationController::class, 'getTaskTemplate'])->name('api.task-templates.show');
    Route::put('task-templates/{template}', [PlantingLocationController::class, 'updateTaskTemplate'])->name('task-templates.update');
    Route::delete('task-templates/{template}', [PlantingLocationController::class, 'deleteTaskTemplate'])->name('task-templates.destroy');
    Route::post('planting-locations/{plantingLocation}/notes', [PlantingLocationController::class, 'storeNote'])->name('planting-locations.notes.store');
    Route::get('planting-locations/{plantingLocation}/notes/{note}/view', [PlantingLocationController::class, 'viewNote'])->name('planting-locations.notes.view');
    Route::post('planting-locations/{plantingLocation}/notes/{note}/mark-read', [PlantingLocationController::class, 'markNoteAsRead'])->name('planting-locations.notes.mark-read');
    Route::post('planting-locations/{plantingLocation}/photos', [PlantingLocationController::class, 'storePhoto'])->name('planting-locations.photos.store');
    Route::post('planting-locations/{plantingLocation}/attachments', [PlantingLocationController::class, 'storeAttachment'])->name('planting-locations.attachments.store');
    Route::get('planting-locations/{plantingLocation}/attachments/{attachment}', [PlantingLocationController::class, 'showAttachment'])->name('planting-locations.attachments.show');
    Route::put('planting-locations/{plantingLocation}/attachments/{attachment}', [PlantingLocationController::class, 'updateAttachment'])->name('planting-locations.attachments.update');
    Route::delete('planting-locations/{plantingLocation}/attachments/{attachment}', [PlantingLocationController::class, 'destroyAttachment'])->name('planting-locations.attachments.destroy');
    Route::post('planting-locations/{plantingLocation}/plantings/{planting}/mark-failed', [PlantingLocationController::class, 'markPlantingFailed'])->name('planting-locations.plantings.mark-failed');
    Route::post('planting-locations/{plantingLocation}/losses', [PlantingLocationController::class, 'storeLoss'])->name('planting-locations.losses.store');
    Route::post('planting-locations/{plantingLocation}/treatments', [PlantingLocationController::class, 'storeTreatment'])->name('planting-locations.treatments.store');
    Route::get('planting-locations/{plantingLocation}/treatments/{treatment}', [PlantingLocationController::class, 'showTreatment'])->name('planting-locations.treatments.show');
    Route::put('planting-locations/{plantingLocation}/treatments/{treatment}', [PlantingLocationController::class, 'updateTreatment'])->name('planting-locations.treatments.update');
    Route::delete('planting-locations/{plantingLocation}/treatments/{treatment}', [PlantingLocationController::class, 'destroyTreatment'])->name('planting-locations.treatments.destroy');
    Route::post('planting-locations/{plantingLocation}/nutrients', [PlantingLocationController::class, 'storeNutrient'])->name('planting-locations.nutrients.store');
    Route::get('planting-locations/{plantingLocation}/nutrients/{nutrient}', [PlantingLocationController::class, 'showNutrient'])->name('planting-locations.nutrients.show');
    Route::put('planting-locations/{plantingLocation}/nutrients/{nutrient}', [PlantingLocationController::class, 'updateNutrient'])->name('planting-locations.nutrients.update');
    Route::delete('planting-locations/{plantingLocation}/nutrients/{nutrient}', [PlantingLocationController::class, 'destroyNutrient'])->name('planting-locations.nutrients.destroy');
    Route::post('planting-locations/{plantingLocation}/expenses', [PlantingLocationController::class, 'storeExpense'])->name('planting-locations.expenses.store');
    Route::get('planting-locations/{plantingLocation}/expenses/{expense}', [PlantingLocationController::class, 'showExpense'])->name('planting-locations.expenses.show');
    Route::put('planting-locations/{plantingLocation}/expenses/{expense}', [PlantingLocationController::class, 'updateExpense'])->name('planting-locations.expenses.update');
    Route::delete('planting-locations/{plantingLocation}/expenses/{expense}', [PlantingLocationController::class, 'destroyExpense'])->name('planting-locations.expenses.destroy');
    
    // Planting Management Routes
    Route::resource('plantings', PlantingController::class);
    Route::get('harvests/{harvest}/detail', [HarvestController::class, 'showDetail'])->name('harvests.detail');
    Route::resource('harvests', HarvestController::class);
    
    // Plant Notes Routes
Route::prefix('plants/{plant}')->name('plants.')->group(function () {
    Route::resource('notes', PlantNoteController::class);
    Route::resource('photos', PlantPhotoController::class);
});

// Note: Planting Location Routes are already defined above in the main routes section

// Expenses Routes (Global)
Route::prefix('expenses')->name('expenses.')->group(function () {
    Route::get('/', [App\Http\Controllers\ExpenseController::class, 'index'])->name('index');
});

// Certification Routes
Route::prefix('certifications')->name('certifications.')->group(function () {
    Route::get('/', [App\Http\Controllers\CertificationController::class, 'index'])->name('index');
    Route::get('/by-plant/{plant}', [App\Http\Controllers\CertificationController::class, 'showByPlant'])->name('by-plant');
    Route::get('/create', [App\Http\Controllers\CertificationController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\CertificationController::class, 'store'])->name('store');
    Route::get('/harvests/{harvest}', [App\Http\Controllers\CertificationController::class, 'show'])->name('show');
    Route::get('/{certification}/reports/create', [App\Http\Controllers\CertificationController::class, 'createReport'])->name('reports.create');
    Route::post('/{certification}/reports', [App\Http\Controllers\CertificationController::class, 'storeReport'])->name('reports.store');
    Route::get('/reports/{report}', [App\Http\Controllers\CertificationController::class, 'showReport'])->name('reports.show');
    Route::get('/reports/{report}/edit', [App\Http\Controllers\CertificationController::class, 'editReport'])->name('reports.edit');
    Route::put('/reports/{report}', [App\Http\Controllers\CertificationController::class, 'updateReport'])->name('reports.update');
    Route::delete('/reports/{report}', [App\Http\Controllers\CertificationController::class, 'destroyReport'])->name('reports.destroy');
    Route::get('/reports/{report}/add-to-stock', [App\Http\Controllers\CertificationController::class, 'addToStock'])->name('add-to-stock');
    
    // Harvest routes for certification
    Route::prefix('harvests')->name('harvests.')->group(function () {
        Route::get('/', [App\Http\Controllers\CertificationController::class, 'harvestsIndex'])->name('index');
    });
});
});
