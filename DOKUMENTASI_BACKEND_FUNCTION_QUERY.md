# Dokumentasi Fungsi Backend & Query SIBESTI

Dokumen ini merangkum:
- fungsi backend yang dipakai di sistem,
- pola query database yang dipakai,
- contoh penggunaan fungsi/query langsung dari alur sistem.

Tujuan dokumen: jadi peta belajar cepat untuk memahami backend SIBESTI (Laravel + Eloquent).

**Lanjutan (detail per method, 4 controller inti):** lihat `DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART2_DETAIL_CONTROLLER.md`.

**Walkthrough alur besar (step-by-step):** lihat `DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART3_WALKTHROUGH_METHOD.md`.

**Walkthrough lanjutan (gudang, edit tugas, laporan per lokasi + export):** lihat `DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART4_WALKTHROUGH_METHOD.md`.

**Walkthrough export laporan (tanam–panen, sarana produksi, penjualan, sertifikasi) & tambah stok dari sertifikasi:** lihat `DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART5_WALKTHROUGH_METHOD.md`.

---

## 1) Gambaran arsitektur backend

Alur umum request:
1. Route di `routes/web.php` mengarah ke method controller.
2. Controller melakukan:
   - validasi request (`$request->validate()`),
   - query data via Eloquent/Query Builder,
   - business logic (hitung stok, filter akses, FIFO, dll),
   - persist data (`create/update/delete`) sering dalam transaksi DB.
3. Data dikirim ke view (`return view(...)`) atau JSON (`return response()->json(...)`).

Teknologi query utama:
- Eloquent Relationship (`with`, `whereHas`, relasi `belongsTo`, `hasMany`, `hasManyThrough`, dll).
- Query Builder (`join`, `leftJoin`, `selectRaw`, `groupBy`, agregasi).
- Collection transform (`map`, `filter`, `groupBy`, `sum`) setelah query.

---

## 2) Fungsi backend per modul (Controller)

Berikut fungsi-fungsi utama yang dipakai di sistem.

### A. Auth & Dashboard

**`AuthController`**
- `showLogin()`
- `login(Request $request)`
- `logout(Request $request)`

**`DashboardController`**
- `index(Request $request)`
- (private helper internal untuk grafik/ringkasan seperti komposisi stok & tren pendapatan)

Contoh query yang dipakai:
- `InventoryType::with('plant.type')->get()`
- `SaleItem::join('warehouse_lots as wl', ...)->select(...)->groupBy(...)->get()`
- `Task::whereHas('planting', ...)->count()`

---

### B. Penanaman & Lokasi

**`PlantTypeController`**
- `index`, `create`, `store`, `edit`, `update`, `destroy`, `getVariety`

**`PlantController`**
- `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`
- `currentPlantings`, `showPlantingReports`, `harvestsIndex`

**`PlantingLocationController`** (modul paling besar)
- master lokasi: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`
- planting/loss: `storePlanting`, `storeLoss`, `markPlantingFailed`, `currentPlantings`, `plantingHistory`
- task: `storeTask`, `updateTaskStatus`, `viewTask`, `editTask`, `updateTask`, `deleteTask`, `fillTaskReport`
- template task: `getTaskTemplate`, `updateTaskTemplate`, `deleteTaskTemplate`
- note: `storeNote`, `viewNote`, `markNoteAsRead`
- attachment: `storeAttachment`, `showAttachment`, `updateAttachment`, `destroyAttachment`, `attachments`
- treatment/nutrient/expense: `storeTreatment`, `showTreatment`, `updateTreatment`, `destroyTreatment`, `storeNutrient`, `showNutrient`, `updateNutrient`, `destroyNutrient`, `storeExpense`, `showExpense`, `updateExpense`, `destroyExpense`, `expenses`
- report by planting: `harvestDetail`, `showPlantingReports`

**`PlantingController`**
- `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`

**`HarvestController`**
- `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`, `showDetail`

Contoh query yang dipakai:
- filter akses lokasi non-admin:
  - `PlantingLocation::with('landWorkerUsers')->whereHas('landWorkerUsers', ...)`
- filter + pencarian:
  - `->where('name', 'like', "%$search%")->orderBy('name')->paginate(15)`
- pivot assignment worker:
  - `DB::table('user_planting_location_land_worker')->distinct()->pluck('user_id')`

---

### C. Sertifikasi

**`CertificationController`**
- `index`, `showByPlant`
- `show(Harvest $harvest)`, `createReport(Harvest $harvest)`, `storeReport(...)`
- `showReport`, `editReport`, `updateReport`, `destroyReport`
- `create`, `store`
- `addToStock`, `addToStockDirect`
- `harvestsIndex`

Contoh query:
- `Harvest::whereHas('planting', fn($q) => ...)->with(...)->orderBy(...)->get()`
- `CertificationReport::whereYear('report_date', $year)->count()`
- `InventoryType::whereHas('certificationReports.harvest.planting', ...)->first()`

---

### D. Gudang & Stok Benih

**`InventoryTypeController`**
- index + create multi-step:
  - `index`, `create`, `storeStep1`, `createStep2`, `storeStep2`, `createStep3`, `store`
- detail/manajemen:
  - `getInventoryTypeData`, `show`, `edit`, `update`, `destroy`, `destroyAll`
- stok:
  - `showStockAdjustment`, `storeStockAdjustment`, `reduceStock`, `addSeedToWarehouse`
- seed/certified seed:
  - `certifiedSeeds`, `showCertifiedSeedDetail`, `showCertifiedSeedDetailWithType`
  - `getCertificationsByPlantType`, `suggestStorageNumber`, `addCertifiedSeed`
  - `showSeedDetail`, `updateSeed`, `destroySeed`, `showSeedHistory`, `showSeedStorageDetail`
- notes/photos:
  - `storeNote`, `storePhoto`

**`WarehouseController`**
- warehouse CRUD: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`
- bin: `storeBin`, `updateBin`, `destroyBin`
- lot: `storeInventoryLot`, `destroyInventoryLot`, `reduceStock`, `updateStock`
- ajax/data: `getBinStocks`, `getLotTransactions`, `getInventoryTypeSeeds`

Contoh query:
- `InventoryLot::whereIn('inventory_type_id', ...)->whereNotNull('warehouse_bin_id')->get()->groupBy('inventory_type_id')`
- `StockHistory::where('warehouse_lot_id', ...)->whereNotNull('transaction_type')->with('user')->orderBy('created_at', 'desc')->get()`
- pencocokan lot ke laporan:
  - `CertificationReport::where('inventory_type_id', ...)->where(function($q){ ... orWhereRaw('TRIM(...) = ?', [$pid]) ... })->first()`

---

### E. Penjualan

**`SaleController`**
- `index`, `showByInventoryType`
- `create`, `store`, `show`, `destroy`
- API pendukung: `getBins`, `getInventoryTypeDetails`, `getBinInventoryLots`

Contoh query:
- dashboard transaksi:
  - `SaleItem::query()->where(...)->sum('quantity')`
- agregasi per tipe:
  - `SaleItem::leftJoin('warehouse_lots as wl', ...)->select(DB::raw(...))->groupBy('wl.inventory_type_id')`
- list histori per inventory type:
  - `SaleItem::whereHas('inventoryLot', ...)->with(['user','inventoryType','inventoryLot'])->orderBy(...)->get()->groupBy('receipt_number')`
- create form FIFO:
  - `InventoryType::whereHas('lots', fn($q) => $q->activeForOperationalStock())->with(['lots' => fn($q) => $q->activeForOperationalStock()->orderBy('created_at', 'asc')])->get()`

---

### F. Laporan

**`ReportController`**
- `index`
- `plantingHarvest`, `byLocation`, `productionSupplies`
- `stockPosition`, `stockMutation`
- `sales`, `certification`

Contoh query:
- `Planting::with(['plant.type','harvest.certification'])->whereNotNull('planted_at')`
- filter waktu:
  - `whereYear(...)`, `whereDate('>=')`, `whereDate('<=')`
- tahun dinamis:
  - `Planting::selectRaw('YEAR(planted_at) as year')->distinct()->orderBy('year','desc')->pluck('year')`

---

### G. User & Landing

**`UserController`**
- `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`

**`LandingPageController`**
- `index`, `edit`, `update`

**Controller lain**
- `ExpenseController`: `index`, `show`
- `TreatmentController`: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`
- `NutrientController`: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`
- `PlantNoteController`: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`
- `PlantPhotoController`: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`

---

## 3) Fungsi backend di level Model (yang sering dipakai)

### Model stok/gudang

**`InventoryLot`**
- `transactions()`
- `isActiveForOperationalStock()`  
- `scopeActiveForOperationalStock(...)`  
- `updateStatus()`

Pemakaian:
- dipakai oleh `SaleController` untuk filter FIFO hanya lot aktif.
- dipakai `WarehouseController` saat split aktif/nonaktif.

**`StockHistory`**
- `signedQuantityForLotLedger()`
- `lotStockBefore()`
- `lotStockAfter()`
- `getTransactionTypeLabelAttribute()`

Pemakaian:
- dipakai modal riwayat mutasi stok (stok sebelum/sesudah).

### Model inventory

**`InventoryType`**
- relasi: `lots`, `transactions`, `certificationReports`, `latestCertificationReport`, `saleItems`
- kalkulasi: `getTotalStockAttribute`, `getCurrentStockFromLotsAttribute`, dst.

Pemakaian:
- halaman daftar stok, ringkasan dashboard, laporan stok.

### Model penanaman/tugas

**`PlantingLocation`**
- `plantings`, `tasks`, `notes`, `attachments`, `landWorkerUsers`, `expenses`, `treatments`, `nutrients`

**`Task`**
- scope: `scopeAssignedTo`, `scopeByAssociation`, `scopeByStatus`
- helper: `isOverdue()`

**`User`**
- akses: `isAdmin`, `hasAccessTo`, `isAssignedToPlantingLocation`, `canAddDataInPelaporan`

---

## 4) Query yang dipakai di sistem (cheat sheet)

Berikut daftar pola query yang paling sering dipakai:

- **Eager loading relasi**
  - `with([...])`
- **Filter berdasar relasi**
  - `whereHas('relasi', fn($q) => ...)`
- **Filter kolom biasa**
  - `where`, `whereIn`, `whereNotNull`, `whereNull`
- **Filter waktu**
  - `whereDate`, `whereYear`, `whereMonth`
- **Pencarian**
  - `where('kolom', 'like', "%keyword%")`
- **Sorting & paging**
  - `orderBy(...)->paginate(...)`
- **Agregasi**
  - `count`, `sum`, `distinct`, `groupBy`, `selectRaw`, `DB::raw`
- **Join tabel**
  - `join`, `leftJoin`
- **Mutasi data**
  - `create`, `update`, `delete`
- **Transaksi**
  - `DB::beginTransaction()`, `DB::commit()`, `DB::rollBack()`

---

## 5) Contoh fungsi + contoh penggunaan di sistem

### Contoh 1 — Filter akses lokasi berdasarkan assignment user

Fungsi:
- `PlantingLocationController::index()`

Contoh:
```php
$query = PlantingLocation::with(['landWorkerUsers']);
if (!$user->isAdmin()) {
    $query->whereHas('landWorkerUsers', function($q) use ($user) {
        $q->where('users.user_id', $user->user_id);
    });
}
$plantingLocations = $query->orderBy('name')->paginate(15);
```

Kegunaan:
- user non-admin hanya melihat lokasi yang ditugaskan.

---

### Contoh 2 — Agregasi penjualan per tipe stok

Fungsi:
- `SaleController::index()`

Contoh:
```php
$itemsQuery = SaleItem::query()
    ->leftJoin('warehouse_lots as wl', 'wl.warehouse_lot_id', '=', 'sale_items.warehouse_lot_id')
    ->select(
        'wl.inventory_type_id as inventory_type_id',
        DB::raw('COUNT(DISTINCT sale_items.receipt_number) as total_sales'),
        DB::raw('COALESCE(SUM(sale_items.quantity), 0) as total_quantity_sold'),
        DB::raw('COALESCE(SUM(sale_items.subtotal), 0) as total_revenue')
    )
    ->whereNotNull('wl.inventory_type_id')
    ->groupBy('wl.inventory_type_id');
```

Kegunaan:
- menghitung dashboard penjualan berdasarkan tipe inventory.

---

### Contoh 3 — FIFO lot aktif untuk penjualan

Fungsi:
- `InventoryLot::scopeActiveForOperationalStock()`
- dipakai di `SaleController::create()`

Contoh:
```php
$inventoryTypes = InventoryType::whereHas('lots', function ($query) {
        $query->activeForOperationalStock();
    })
    ->with(['lots' => function ($query) {
        $query->activeForOperationalStock()
              ->orderBy('created_at', 'asc'); // FIFO
    }])
    ->get();
```

Kegunaan:
- hanya lot yang stok > 0 dan belum expired yang boleh dijual.

---

### Contoh 4 — Riwayat mutasi lot + stok sebelum/sesudah

Fungsi:
- `WarehouseController::getLotTransactions()`
- helper model `StockHistory::lotStockBefore/After()`

Contoh:
```php
$transactions = StockHistory::where('warehouse_lot_id', $lot->warehouse_lot_id)
    ->whereNotNull('transaction_type')
    ->with('user')
    ->orderBy('created_at', 'desc')
    ->get();
```

Kegunaan:
- menampilkan ledger stok di modal riwayat.

---

### Contoh 5 — Laporan per lokasi dengan multi-query relasi

Fungsi:
- `ReportController::byLocation()`

Contoh:
```php
$plantingQuery = $plantingLocation->plantings()->with(['plant.type', 'harvest', 'losses']);
$treatmentQuery = $plantingLocation->treatments()->with(['planting.plant', 'responsiblePerson', 'editor']);
$nutrientQuery = $plantingLocation->nutrients()->with(['planting.plant', 'editor', 'responsiblePerson']);
$expenseQuery = $plantingLocation->expenses()->with(['planting.plant', 'responsiblePerson', 'editor']);
```

Kegunaan:
- menghasilkan laporan komprehensif per lokasi lahan.

---

## 6) Rekomendasi cara belajar backend SIBESTI

Urutan belajar paling efektif:
1. `routes/web.php` (lihat endpoint dan modul).
2. `PlantingLocationController`, `InventoryTypeController`, `SaleController`, `ReportController`.
3. Model inti: `PlantingLocation`, `Planting`, `Harvest`, `InventoryType`, `InventoryLot`, `StockHistory`, `SaleItem`.
4. Ikuti satu alur end-to-end:
   - tambah lokasi -> tambah planting -> panen -> sertifikasi -> masuk gudang -> penjualan -> laporan.

Checklist memahami sistem:
- paham perbedaan data master vs transaksi,
- paham relasi `whereHas` dan eager loading `with`,
- paham alur stok aktif/nonaktif + FIFO,
- paham bagaimana `StockHistory` menyatukan mutasi stok.

---

## 7) Catatan

- Dokumen ini fokus ke fungsi backend dan query yang aktif dipakai pada kode saat ini.
- Jika Anda ingin, saya bisa lanjutkan versi **lebih detail per fungsi** (satu per satu method controller lengkap dengan input, output, validasi, dan query SQL ekuivalen).

