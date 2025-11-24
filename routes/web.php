<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskTemplateController;
use App\Http\Controllers\TaskSeriesController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\PlantTypeController;
use App\Http\Controllers\PlantingLocationController;
use App\Http\Controllers\PlantingController;
use App\Http\Controllers\HarvestController;
use App\Http\Controllers\PlantNoteController;
use App\Http\Controllers\PlantPhotoController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\NutrientController;
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\InventoryTypeController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PlanningController;

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

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Task Management Routes
    Route::resource('tasks', TaskController::class);
    Route::get('tasks/create-from-template', [TaskController::class, 'createFromTemplate'])->name('tasks.create-from-template');
    Route::post('tasks/create-from-template', [TaskController::class, 'storeFromTemplate'])->name('tasks.store-from-template');
    
    // Task Template Management Routes
    Route::resource('task-templates', TaskTemplateController::class);
    Route::get('task-templates/{taskTemplate}/create-series', [TaskTemplateController::class, 'createSeries'])->name('task-templates.create-series');
    Route::post('task-templates/{taskTemplate}/create-series', [TaskTemplateController::class, 'storeSeries'])->name('task-templates.store-series');
    Route::get('api/task-templates/{taskTemplate}', [TaskTemplateController::class, 'apiShow'])->name('api.task-templates.show');
    
    // Task Series Management Routes
    Route::resource('task-series', TaskSeriesController::class);
    
    // Location Management Routes
    Route::resource('locations', LocationController::class);
    
    // Warehouse Routes (Gudang) - Lokasi Gudang
    Route::resource('warehouse-locations', WarehouseController::class);
    Route::post('warehouse-locations/{warehouse}/bins', [WarehouseController::class, 'storeBin'])->name('warehouse-locations.bins.store');
    Route::put('warehouse-locations/{warehouse}/bins/{bin}', [WarehouseController::class, 'updateBin'])->name('warehouse-locations.bins.update');
    Route::delete('warehouse-locations/{warehouse}/bins/{bin}', [WarehouseController::class, 'destroyBin'])->name('warehouse-locations.bins.destroy');
    Route::post('warehouse-locations/{warehouse}/bins/{bin}/inventory-lots', [WarehouseController::class, 'storeInventoryLot'])->name('warehouse-locations.bins.inventory-lots.store');
    Route::get('warehouse-locations/{warehouse}/bins/{bin}/stocks', [WarehouseController::class, 'getBinStocks'])->name('warehouse-locations.bins.stocks');
    
    // Warehouse Routes (Gudang) - Stok Bibit
    Route::get('seed-stock', [InventoryTypeController::class, 'index'])->name('seed-stock.index');
    Route::get('seed-stock/create', [InventoryTypeController::class, 'create'])->name('seed-stock.create');
    Route::post('seed-stock/create-step1', [InventoryTypeController::class, 'storeStep1'])->name('seed-stock.store-step1');
    Route::get('seed-stock/create-step2', [InventoryTypeController::class, 'createStep2'])->name('seed-stock.create-step2');
    Route::post('seed-stock/create-step2', [InventoryTypeController::class, 'storeStep2'])->name('seed-stock.store-step2');
    Route::get('seed-stock/create-step3', [InventoryTypeController::class, 'createStep3'])->name('seed-stock.create-step3');
    Route::post('seed-stock', [InventoryTypeController::class, 'store'])->name('seed-stock.store');
    Route::get('seed-stock/{inventoryType}', [InventoryTypeController::class, 'show'])->name('seed-stock.show');
    Route::get('seed-stock/{inventoryType}/edit', [InventoryTypeController::class, 'edit'])->name('seed-stock.edit');
    Route::put('seed-stock/{inventoryType}', [InventoryTypeController::class, 'update'])->name('seed-stock.update');
    Route::get('seed-stock/{inventoryType}/stock-adjustment/{action}', [InventoryTypeController::class, 'showStockAdjustment'])->name('seed-stock.show-stock-adjustment');
    Route::post('seed-stock/{inventoryType}/stock-adjustment', [InventoryTypeController::class, 'storeStockAdjustment'])->name('seed-stock.store-stock-adjustment');
    Route::post('seed-stock/{inventoryType}/notes', [InventoryTypeController::class, 'storeNote'])->name('seed-stock.store-note');
    Route::post('seed-stock/{inventoryType}/photos', [InventoryTypeController::class, 'storePhoto'])->name('seed-stock.store-photo');
    
    // Sales Routes (Penjualan)
    Route::get('sales/get-lots', [SaleController::class, 'getInventoryLots'])->name('sales.get-lots');
    Route::resource('sales', SaleController::class);

    // Contacts Routes
    Route::resource('contacts', ContactController::class);
    
    // Reports Routes (Laporan) - Admin & Pimpinan only
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        
        // A. Laporan Produksi & Pertanian
        Route::get('/planting-harvest', [ReportController::class, 'plantingHarvest'])->name('planting-harvest');
        Route::get('/production-supplies', [ReportController::class, 'productionSupplies'])->name('production-supplies');
        
        // B. Laporan Stok & Gudang
        Route::get('/stock-position', [ReportController::class, 'stockPosition'])->name('stock-position');
        Route::get('/stock-mutation', [ReportController::class, 'stockMutation'])->name('stock-mutation');
        Route::get('/expiry-monitoring', [ReportController::class, 'expiryMonitoring'])->name('expiry-monitoring');
        
        // C. Laporan Penjualan & Distribusi
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/distribution', [ReportController::class, 'distribution'])->name('distribution');
        
        // D. Laporan Sertifikasi
        Route::get('/certification', [ReportController::class, 'certification'])->name('certification');
    });

    // Planning Routes (Perencanaan) - Admin & Pimpinan only
    Route::prefix('planning')->name('planning.')->group(function () {
        Route::get('/', [PlanningController::class, 'index'])->name('index');
        
        // A. Rencana Anggaran (DPA)
        Route::get('/budget', [PlanningController::class, 'budgetIndex'])->name('budget.index');
        Route::get('/budget/item/create', [PlanningController::class, 'budgetItemCreate'])->name('budget.item.create');
        Route::post('/budget/item', [PlanningController::class, 'budgetItemStore'])->name('budget.item.store');
        Route::get('/budget/item/{budgetItem}/edit', [PlanningController::class, 'budgetItemEdit'])->name('budget.item.edit');
        Route::put('/budget/item/{budgetItem}', [PlanningController::class, 'budgetItemUpdate'])->name('budget.item.update');
        Route::delete('/budget/item/{budgetItem}', [PlanningController::class, 'budgetItemDestroy'])->name('budget.item.destroy');
        
        // B. Target Produksi
        Route::get('/production-target', [PlanningController::class, 'productionTargetIndex'])->name('production-target.index');
        Route::get('/production-target/create', [PlanningController::class, 'productionTargetCreate'])->name('production-target.create');
        Route::post('/production-target', [PlanningController::class, 'productionTargetStore'])->name('production-target.store');
        Route::get('/production-target/{productionTarget}/edit', [PlanningController::class, 'productionTargetEdit'])->name('production-target.edit');
        Route::put('/production-target/{productionTarget}', [PlanningController::class, 'productionTargetUpdate'])->name('production-target.update');
        Route::delete('/production-target/{productionTarget}', [PlanningController::class, 'productionTargetDestroy'])->name('production-target.destroy');
    });
    
    // User Management Routes (Admin only)
    Route::middleware(['auth'])->group(function () {
        Route::resource('users', UserController::class);
    });

    // Planting Module Routes
    Route::resource('plant-types', PlantTypeController::class)->except(['show']);
    Route::resource('plants', PlantController::class);
    Route::get('plants/{plant}/current-plantings', [PlantController::class, 'currentPlantings'])->name('plants.current-plantings');
    Route::get('plants/{plant}/harvests', [PlantController::class, 'harvestsIndex'])->name('plants.harvests.index');
    Route::resource('planting-locations', PlantingLocationController::class);
    Route::post('planting-locations/{plantingLocation}/plantings', [PlantingLocationController::class, 'storePlanting'])->name('planting-locations.plantings.store');
    Route::post('planting-locations/{plantingLocation}/tasks', [PlantingLocationController::class, 'storeTask'])->name('planting-locations.tasks.store');
    Route::post('planting-locations/{plantingLocation}/notes', [PlantingLocationController::class, 'storeNote'])->name('planting-locations.notes.store');
    Route::post('planting-locations/{plantingLocation}/photos', [PlantingLocationController::class, 'storePhoto'])->name('planting-locations.photos.store');
    Route::post('planting-locations/{plantingLocation}/plantings/{planting}/mark-failed', [PlantingLocationController::class, 'markPlantingFailed'])->name('planting-locations.plantings.mark-failed');
    Route::post('planting-locations/{plantingLocation}/losses', [PlantingLocationController::class, 'storeLoss'])->name('planting-locations.losses.store');
    Route::post('planting-locations/{plantingLocation}/treatments', [PlantingLocationController::class, 'storeTreatment'])->name('planting-locations.treatments.store');
    Route::post('planting-locations/{plantingLocation}/nutrients', [PlantingLocationController::class, 'storeNutrient'])->name('planting-locations.nutrients.store');
    
    // Planting Management Routes
    Route::resource('plantings', PlantingController::class);
    Route::resource('harvests', HarvestController::class);
    
    // Plant Notes Routes
Route::prefix('plants/{plant}')->name('plants.')->group(function () {
    Route::resource('notes', PlantNoteController::class);
    Route::resource('photos', PlantPhotoController::class);
});

// Planting Location Routes
Route::resource('planting-locations', PlantingLocationController::class);

// Treatment and Nutrient Routes for Planting Locations
Route::prefix('planting-locations/{plantingLocation}')->name('planting-locations.')->group(function () {
    Route::resource('treatments', TreatmentController::class);
    Route::resource('nutrients', NutrientController::class);
});

// Certification Routes
Route::prefix('certifications')->name('certifications.')->group(function () {
    Route::get('/', [App\Http\Controllers\CertificationController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\CertificationController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\CertificationController::class, 'store'])->name('store');
    Route::get('/harvests/{harvest}', [App\Http\Controllers\CertificationController::class, 'show'])->name('show');
    Route::get('/{certification}/reports/create', [App\Http\Controllers\CertificationController::class, 'createReport'])->name('reports.create');
    Route::post('/{certification}/reports', [App\Http\Controllers\CertificationController::class, 'storeReport'])->name('reports.store');
    Route::get('/reports/{report}', [App\Http\Controllers\CertificationController::class, 'showReport'])->name('reports.show');
});
});
