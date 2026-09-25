<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\PlantTypeController;
use App\Http\Controllers\PlantingLocationController;
use App\Http\Controllers\ProductionActivityController;
use App\Http\Controllers\PlantingController;
use App\Http\Controllers\PlantNoteController;
use App\Http\Controllers\PlantPhotoController;
use App\Http\Controllers\SeedSourceController;
use App\Http\Controllers\SeedUnitController;
use App\Http\Controllers\PlantingFieldController;
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SeedRequestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\BookingGeowisataController;
use App\Http\Controllers\PendaftaranMagangController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\ContentController;

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
Route::get('/permintaan', [SeedRequestController::class, 'publicIndex'])->name('public.seed-requests.index');
Route::get('/permintaan/buat', [SeedRequestController::class, 'publicCreate'])->name('public.seed-requests.create');
Route::get('/permintaan/plants/{plant}/packagings', [SeedRequestController::class, 'availablePackagings'])->name('public.seed-requests.packagings');
Route::post('/permintaan', [SeedRequestController::class, 'publicStore'])->name('public.seed-requests.store');
Route::get('/permintaan/{seedRequest}', [SeedRequestController::class, 'publicShow'])->name('public.seed-requests.show');
Route::get('/kunjungan-geowisata', [BookingGeowisataController::class, 'publicIndex'])->name('public.geowisata.index');
Route::get('/kunjungan-geowisata/kalender', [BookingGeowisataController::class, 'calendarJson'])->name('public.geowisata.calendar');
Route::get('/kunjungan-geowisata/buat', [BookingGeowisataController::class, 'publicCreate'])->name('public.geowisata.create');
Route::post('/kunjungan-geowisata', [BookingGeowisataController::class, 'publicStore'])->name('public.geowisata.store');
Route::get('/kunjungan-geowisata/{booking}', [BookingGeowisataController::class, 'publicShow'])->name('public.geowisata.show');
Route::get('/pendaftaran-magang', [PendaftaranMagangController::class, 'publicIndex'])->name('public.magang.index');
Route::get('/pendaftaran-magang/buat', [PendaftaranMagangController::class, 'publicCreate'])->name('public.magang.create');
Route::post('/pendaftaran-magang', [PendaftaranMagangController::class, 'publicStore'])->name('public.magang.store');
Route::get('/pendaftaran-magang/{pendaftaran}', [PendaftaranMagangController::class, 'publicShow'])->name('public.magang.show');
Route::get('/api/landing/varieties', [LandingPageController::class, 'varietiesJson'])->name('landing.varieties');
Route::get('/api/landing/plants/{plant}/lots', [SiteController::class, 'lotsJson'])->name('landing.lots');
Route::get('/sertifikat-benih/{stock}', [SiteController::class, 'seedCertificate'])->name('public.seed-certificate');
Route::get('/label-benih/{packaging}', [SiteController::class, 'seedLabel'])->name('public.seed-label');
Route::get('/permintaan/buat/rincian', [SeedRequestController::class, 'publicCreateItems'])->name('public.seed-requests.items');
Route::post('/permintaan/buat/pembeli', [SeedRequestController::class, 'publicSaveBuyer'])->name('public.seed-requests.buyer');
Route::get('/permintaan/plants/{plant}/lots', [SeedRequestController::class, 'availableLots'])->name('public.seed-requests.lots');
Route::get('/permintaan/{seedRequest}/struk', [SeedRequestController::class, 'publicReceipt'])->name('public.seed-requests.receipt');
Route::get('/profil', [SiteController::class, 'profil'])->name('site.profil');
Route::get('/berita', [SiteController::class, 'posts'])->name('site.posts');
Route::get('/berita/{slug}', [SiteController::class, 'showPost'])->name('site.show');
Route::get('/dokumen', [SiteController::class, 'documents'])->name('site.documents');
Route::get('/dokumen/{content}/unduh', [SiteController::class, 'download'])->name('site.download');
Route::get('/dokumen/{content}/lihat', [SiteController::class, 'viewFile'])->name('site.documents.file');
Route::get('/dokumen/{content}', [SiteController::class, 'showDocument'])->name('site.documents.show');
Route::get('/informasi-publik', [SiteController::class, 'prices'])->name('site.prices');
Route::get('/informasi-publik/{plant}', [SiteController::class, 'priceShow'])->name('site.prices.show');
Route::get('/kontak', [SiteController::class, 'contact'])->name('site.contact');
Route::get('/konten/{jenis}', [SiteController::class, 'contentsByJenis'])->name('site.jenis');
Route::get('/api/landing/plants/{plant}/labels', [SiteController::class, 'labelsJson'])->name('landing.labels');

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


    // Warehouse Routes (Gudang) - Stok Bibit
    Route::get('seed-stock', [StockController::class, 'index'])->name('seed-stock.index');
    Route::get('seed-stock/{plant}/lots/{stock}', [StockController::class, 'showLot'])->name('seed-stock.lots.show');
    Route::get('seed-stock/{plant}/lots/{stock}/lab-result', [StockController::class, 'labResult'])->name('seed-stock.lab-result');
    Route::get('seed-stock/{plant}/lots/{stock}/certificate', [StockController::class, 'certificate'])->name('seed-stock.certificate');
    Route::get('seed-stock/{plant}/lots/{stock}/lab-tests', [StockController::class, 'labResult'])->name('seed-stock.lab-tests');
    Route::get('seed-stock/packaging/{packaging}', [StockController::class, 'showPackaging'])->name('seed-stock.packaging.show');
    Route::get('seed-stock/{plant}/lots/{stock}/label/create', [StockController::class, 'createLabel'])->name('seed-stock.label.create');
    Route::post('seed-stock/{plant}/lots/{stock}/label', [StockController::class, 'storeLabel'])->name('seed-stock.label.store');
    Route::get('seed-stock/{plant}/lots/{stock}/labels/{report}/storage', [StockController::class, 'createStorage'])->name('seed-stock.label.storage');
    Route::post('seed-stock/{plant}/lots/{stock}/labels/{report}/storage', [StockController::class, 'storeStorage'])->name('seed-stock.label.storage.store');
    Route::get('seed-stock/{plant}/lots/{stock}/packaging/{packaging}/sticker', [StockController::class, 'sticker'])->name('seed-stock.packaging.sticker');
    Route::post('seed-stock/{plant}/lots/{stock}/print-labels', [StockController::class, 'printLabels'])->name('seed-stock.packaging.print');
    Route::post('seed-stock/{plant}/lots/{stock}/adjust', [StockController::class, 'adjustPackagings'])->name('seed-stock.packaging.adjust');
    Route::post('seed-stock/{plant}/lots/{stock}/recertify', [StockController::class, 'recertify'])->name('seed-stock.recertify');
    Route::post('seed-stock/{plant}/notes', [StockController::class, 'storeNote'])->name('seed-stock.plant-notes.store');
    Route::post('seed-stock/{plant}/photos', [StockController::class, 'storePhoto'])->name('seed-stock.plant-photos.store');
    Route::get('warehouse-locations/{warehouse}/racks/{bin}', [StockController::class, 'rackPackagings'])->name('warehouse-locations.racks.packagings');
    Route::post('warehouse-locations/{warehouse}/racks/{bin}/adjust', [StockController::class, 'adjustRackPackagings'])->name('warehouse-locations.racks.adjust');
    Route::post('warehouse-locations/{warehouse}/racks/{bin}/recertify', [StockController::class, 'recertifyRackPackagings'])->name('warehouse-locations.racks.recertify');
    Route::get('seed-stock/{plant}', [StockController::class, 'show'])->name('seed-stock.show');
    
    // Sales Routes (Penjualan)
    Route::get('sales/plants/{plant}/packagings', [SaleController::class, 'availablePackagings'])->name('sales.plant-packagings');
    Route::get('sales/plants/{plant}/lots', [SaleController::class, 'availableLots'])->name('sales.plant-lots');
    Route::get('sales/plants/{plant}', [SaleController::class, 'showByPlant'])->name('sales.by-plant');
    Route::post('sales/buat/pembeli', [SaleController::class, 'saveBuyer'])->name('sales.buyer');
    Route::get('sales/buat/rincian', [SaleController::class, 'createItems'])->name('sales.items');
    Route::resource('sales', SaleController::class);
    Route::get('seed-requests', [SeedRequestController::class, 'index'])->name('seed-requests.index');
    Route::get('seed-requests/create', [SeedRequestController::class, 'create'])->name('seed-requests.create');
    Route::post('seed-requests/buyer', [SeedRequestController::class, 'saveBuyer'])->name('seed-requests.buyer');
    Route::get('seed-requests/create/items', [SeedRequestController::class, 'createItems'])->name('seed-requests.items');
    Route::post('seed-requests', [SeedRequestController::class, 'store'])->name('seed-requests.store');
    Route::get('seed-requests/{seedRequest}', [SeedRequestController::class, 'show'])->name('seed-requests.show');
    Route::get('seed-requests/{seedRequest}/approve', [SeedRequestController::class, 'approveForm'])->name('seed-requests.approve-form');
    Route::get('seed-requests/{seedRequest}/pickup', [SeedRequestController::class, 'pickup'])->name('seed-requests.pickup');
    Route::get('seed-requests/{seedRequest}/sell', [SeedRequestController::class, 'sell'])->name('seed-requests.sell');
    Route::post('seed-requests/{seedRequest}/approve', [SeedRequestController::class, 'approve'])->name('seed-requests.approve');
    Route::post('seed-requests/{seedRequest}/reject', [SeedRequestController::class, 'reject'])->name('seed-requests.reject');
    Route::post('seed-requests/{seedRequest}/ready', [SeedRequestController::class, 'markReady'])->name('seed-requests.ready');
    Route::post('seed-requests/{seedRequest}/complete', [SeedRequestController::class, 'complete'])->name('seed-requests.complete');
    Route::get('plants/{plant}/lots', [SeedRequestController::class, 'availableLots'])->name('seed-requests.lots');

    Route::get('pelayanan-publik/kunjungan-geowisata', [BookingGeowisataController::class, 'index'])->name('geowisata.index');
    Route::get('pelayanan-publik/kunjungan-geowisata/{booking}', [BookingGeowisataController::class, 'show'])->name('geowisata.show');
    Route::post('pelayanan-publik/kunjungan-geowisata/{booking}/approve', [BookingGeowisataController::class, 'approve'])->name('geowisata.approve');
    Route::post('pelayanan-publik/kunjungan-geowisata/{booking}/reject', [BookingGeowisataController::class, 'reject'])->name('geowisata.reject');
    Route::post('pelayanan-publik/kunjungan-geowisata/{booking}/hadir', [BookingGeowisataController::class, 'markHadir'])->name('geowisata.hadir');
    Route::get('pelayanan-publik/pendaftaran-magang', [PendaftaranMagangController::class, 'index'])->name('magang.index');
    Route::get('pelayanan-publik/pendaftaran-magang/{pendaftaran}', [PendaftaranMagangController::class, 'show'])->name('magang.show');
    Route::post('pelayanan-publik/pendaftaran-magang/{pendaftaran}/approve', [PendaftaranMagangController::class, 'approve'])->name('magang.approve');
    Route::post('pelayanan-publik/pendaftaran-magang/{pendaftaran}/reject', [PendaftaranMagangController::class, 'reject'])->name('magang.reject');

    
    // Reports Routes (Laporan) - Admin & Pimpinan only
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        
        // A. Laporan Produksi & Pertanian
        Route::get('/planting-harvest', [ReportController::class, 'plantingHarvest'])->name('planting-harvest');
        Route::get('/by-location', [ReportController::class, 'byLocation'])->name('by-location');
        Route::get('/production', [ReportController::class, 'byLocation'])->name('production');
        Route::get('/api/varieties', [ReportController::class, 'varietiesJson'])->name('varieties');
        Route::get('/api/fields', [ReportController::class, 'fieldsJson'])->name('fields');
        Route::get('/api/plantings', [ReportController::class, 'plantingsJson'])->name('plantings');
        Route::get('/api/catalog', [ReportController::class, 'catalogJson'])->name('catalog');
        
        // B. Laporan Stok & Gudang
        Route::get('/stock-position', [ReportController::class, 'stockPosition'])->name('stock-position');
        Route::get('/stock-mutation', [ReportController::class, 'stockMutation'])->name('stock-mutation');
        
        // C. Laporan Penjualan & Distribusi
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/distribution', [ReportController::class, 'distribution'])->name('distribution');
        
        // D. Laporan Sertifikasi
        Route::get('/certification', [ReportController::class, 'certification'])->name('certification');
    });

    Route::prefix('contents')->name('contents.')->group(function () {
        Route::get('/', [ContentController::class, 'index'])->name('index');
        Route::get('/create', [ContentController::class, 'create'])->name('create');
        Route::post('/', [ContentController::class, 'store'])->name('store');
        Route::get('/settings', [ContentController::class, 'settings'])->name('settings');
        Route::post('/settings', [ContentController::class, 'updateSettings'])->name('settings.update');
        Route::post('/profil-kategori', [ContentController::class, 'storeProfilKategori'])->name('profil-kategori.store');
        Route::get('/{content}/edit', [ContentController::class, 'edit'])->name('edit');
        Route::put('/{content}', [ContentController::class, 'update'])->name('update');
        Route::delete('/{content}', [ContentController::class, 'destroy'])->name('destroy');
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
    Route::get('seed-units', [SeedUnitController::class, 'index'])->name('seed-units.index');
    Route::post('seed-units', [SeedUnitController::class, 'store'])->name('seed-units.store');
    Route::get('plants/{plant}/label-certificates', [PlantController::class, 'labelCertificates'])->name('plants.label-certificates');
    Route::get('plants/{plant}/label-certificates/{stock}', [PlantController::class, 'labelCertificateStock'])->name('plants.label-certificates.stock');
    Route::get('plants/{plant}/label-stock-history', [PlantController::class, 'labelStockHistory'])->name('plants.label-stock-history');
    Route::get('plants/{plant}/sales-history', [PlantController::class, 'salesHistory'])->name('plants.sales-history');
    Route::get('plants/{plant}/current-plantings', [PlantController::class, 'currentPlantings'])->name('plants.current-plantings');
    Route::post('plants/{plant}/current-plantings', [PlantController::class, 'storeProduction'])->name('plants.productions.store');
    Route::get('plants/{plant}/current-plantings/{planting}/reports', [PlantController::class, 'showPlantingReports'])->name('plants.current-plantings.reports');
    Route::resource('planting-locations', PlantingLocationController::class);
    Route::get('planting-locations/{plantingLocation}/plantings', [PlantingLocationController::class, 'currentPlantings'])->name('planting-locations.plantings.index');
    Route::get('planting-locations/{plantingLocation}/planting-history', [PlantingLocationController::class, 'plantingHistory'])->name('planting-locations.planting-history');
    Route::get('planting-locations/{plantingLocation}/label-certificates', [PlantingLocationController::class, 'labelCertificates'])->name('planting-locations.label-certificates');
    Route::get('planting-locations/{plantingLocation}/attachments', [PlantingLocationController::class, 'attachments'])->name('planting-locations.attachments.index');
    Route::resource('planting-locations.fields', PlantingFieldController::class)
        ->parameters(['planting-locations' => 'plantingLocation', 'fields' => 'field']);
    Route::post('planting-locations/{plantingLocation}/plantings', [PlantingLocationController::class, 'storePlanting'])->name('planting-locations.plantings.store');
    Route::get('planting-locations/{plantingLocation}/plantings/{planting}/reports', [ProductionActivityController::class, 'show'])->name('planting-locations.plantings.reports');
    Route::post('planting-locations/{plantingLocation}/plantings/{planting}/complete', [ProductionActivityController::class, 'complete'])->name('planting-locations.plantings.complete');
    Route::get('planting-locations/{plantingLocation}/plantings/{planting}/daily-logs/create', [ProductionActivityController::class, 'createDailyLog'])->name('planting-locations.plantings.daily.create');
    Route::post('planting-locations/{plantingLocation}/plantings/{planting}/daily-logs', [ProductionActivityController::class, 'storeDailyLog'])->name('planting-locations.plantings.daily.store');
    Route::get('planting-locations/{plantingLocation}/plantings/{planting}/certifications/create', [ProductionActivityController::class, 'createCertification'])->name('planting-locations.plantings.certifications.create');
    Route::post('planting-locations/{plantingLocation}/plantings/{planting}/certifications', [ProductionActivityController::class, 'storeCertification'])->name('planting-locations.plantings.certifications.store');
    Route::get('planting-locations/{plantingLocation}/plantings/{planting}/certifications/{stage}/{record}', [ProductionActivityController::class, 'showCertification'])->name('planting-locations.plantings.certifications.show');
    Route::get('planting-locations/{plantingLocation}/plantings/{planting}/lab-results/create', [ProductionActivityController::class, 'createLabResult'])->name('planting-locations.plantings.lab-result.create');
    Route::post('planting-locations/{plantingLocation}/plantings/{planting}/lab-results', [ProductionActivityController::class, 'storeLabResult'])->name('planting-locations.plantings.lab-result.store');
    Route::get('planting-locations/{plantingLocation}/plantings/{planting}/lab-results/{postHarvest}', [ProductionActivityController::class, 'showLabResult'])->name('planting-locations.plantings.lab-result.show');
    Route::get('planting-locations/{plantingLocation}/plantings/{planting}/labels/{report}', [ProductionActivityController::class, 'showLabel'])->name('planting-locations.plantings.labels.show');
    Route::post('planting-locations/{plantingLocation}/plantings/{planting}/notes', [ProductionActivityController::class, 'storeNote'])->name('planting-locations.plantings.notes.store');
    Route::post('planting-locations/{plantingLocation}/plantings/{planting}/assignments/{assignment}/complete', [ProductionActivityController::class, 'completeAssignment'])->name('planting-locations.plantings.assignments.complete');
    Route::get('planting-locations/{plantingLocation}/plantings/{planting}/history-reports', [ProductionActivityController::class, 'historyReports'])->name('planting-locations.plantings.history-reports');
    Route::get('api/plants/{plant}/seed-sources', [SeedSourceController::class, 'indexJson'])->name('api.plants.seed-sources');
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
    
    // Planting Management Routes
    Route::resource('plantings', PlantingController::class);
    
    // Plant Notes Routes
Route::prefix('plants/{plant}')->name('plants.')->group(function () {
    Route::resource('notes', PlantNoteController::class);
    Route::resource('photos', PlantPhotoController::class);
    Route::resource('seed-sources', SeedSourceController::class);
});

// Note: Planting Location Routes are already defined above in the main routes section

// Certification Routes
Route::prefix('certifications')->name('certifications.')->group(function () {
    Route::get('/', [App\Http\Controllers\CertificationController::class, 'index'])->name('index');
    Route::get('/by-plant/{plant}', [App\Http\Controllers\CertificationController::class, 'showByPlant'])->name('by-plant');
    Route::get('/labels/{report}', [App\Http\Controllers\CertificationController::class, 'showLabel'])->name('labels.show');
});
});
