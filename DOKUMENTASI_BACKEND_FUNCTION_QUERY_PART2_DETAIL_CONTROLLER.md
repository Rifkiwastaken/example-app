# Dokumentasi Detail per Controller (Bagian 2)

Dokumen ini melanjutkan **`DOKUMENTASI_BACKEND_FUNCTION_QUERY.md`** dengan breakdown **input → validasi → query utama → output** untuk empat controller inti.

**Langkah berikutnya:** untuk alur panjang **langkah demi langkah** (mis. `storeTask`, `InventoryTypeController::show`, FIFO penjualan), buka **`DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART3_WALKTHROUGH_METHOD.md`**.

**Gudang, edit tugas, laporan lokasi + export:** **`DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART4_WALKTHROUGH_METHOD.md`**.

**Export laporan & `addToStock` / `addToStockDirect`:** **`DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART5_WALKTHROUGH_METHOD.md`**.

**Catatan pembaca:** route lengkap ada di `routes/web.php`. Nama route disebutkan jika membantu tracing.

---

## Ringkasan cepat

| Controller | Peran utama |
|------------|-------------|
| `PlantingLocationController` | CRUD lokasi penanaman; penanaman, tugas, catatan, lampiran; perlakuan, pupuk, biaya; riwayat & laporan terkait lokasi. |
| `InventoryTypeController` | Modul **stok benih** (`/seed-stock`): wizard buat tipe, detail stok, penyesuaian stok, benih bersertifikat, tambah benih ke gudang. |
| `SaleController` | Penjualan dari lot gudang (FIFO), dashboard penjualan per tipe, API JSON untuk form. |
| `ReportController` | Halaman laporan dan ekspor (tanam–panen, lokasi, stok, mutasi, penjualan, sertifikasi). |

---

## 1. `PlantingLocationController`

**Namespace:** `App\Http\Controllers\PlantingLocationController`  
**Umumnya:** semua method memanggil **`auth()->user()`** lalu **`isAssignedToPlantingLocation($plantingLocation)`**; gagal → `403`. Variasi izin memakai **`canManagePlantingLocation`**, **`canAddDataInPelaporan`**, dll.

### 1.1 Daftar method publik dan fungsi ringkas

| Method | Peran ringkas |
|--------|----------------|
| `index(Request)` | Daftar lokasi + filter penugasan/search; **non-admin** pakai `whereHas('landWorkerUsers', ...)`. Dropdown user dari pivot `user_planting_location_land_worker`. |
| `create()` | Form buat lokasi (admin / kepala_satuan_tugas). |
| `store(Request)` | Validasi field lokasi + upload foto + **`landWorkerUsers()->sync(...)`**. |
| `show` | Detail lokasi (load `landWorkerUsers`). |
| `edit` / `update` / `destroy` | Ubah/hapus lokasi sesuai aturan role. |
| `storePlanting` | Tambah **`Planting`** di lokasi ini; generate `planting_batch_number` otomatis jika kosong. |
| `storeLoss` | Tambah **`PlantingLoss`** untuk satu planting. |
| `storeTask` … `fillTaskReport` | CRUD / status / isi laporan **`Task`** (`planting_tasks`), terikat `planting_id` + template. |
| `getTaskTemplate` / `updateTaskTemplate` / `deleteTaskTemplate` | Kelola **`TaskTemplate`**. |
| `storeNote` / `viewNote` / `markNoteAsRead` | **`PlantingLocationNote`** (tabel `planting_notes`), job notifikasi opsional. |
| `storeAttachment` … `showAttachment` | **`Attachment`** (`planting_attachments`). |
| `markPlantingFailed` | Set penanaman gagal / selesai sesuai alur UI. |
| `storeTreatment` … `destroyTreatment` | **`Treatment`** per planting. |
| `storeNutrient` … `destroyNutrient` | **`Nutrient`** per planting. |
| `storeExpense` … `destroyExpense` | **`Expense`** per planting. |
| `currentPlantings` | Planting aktif (`is_completed = false`) vs semua; eager load `plant`, `harvests`, `losses`. |
| `plantingHistory` | Filter koleksi: sudah panen, ada rugi, gagal panen. |
| `attachments` | Daftar lampiran lokasi. |
| `harvestDetail` | Detail panen + tugas/perlakuan/pupuk/catatan terkait planting (query `where planting_id` atau `null` untuk umum lokasi). |
| `expenses` | Ringkasan/index pengeluaran lokasi. |
| `showPlantingReports` | Laporan per planting (tab pelaporan). |

### 1.2 Contoh mendalam — `index(Request $request)`

**Input (query):** `search`, `assignment` (filter user penugasan).  
**Query:**

- `PlantingLocation::with(['landWorkerUsers'])`
- Jika bukan admin: `whereHas('landWorkerUsers', fn ($q) => $q->where('users.user_id', $user->user_id))`
- Opsional: `where('name', 'like', "%{$search}%")`
- Opsional filter assignment: lagi-lagi `whereHas('landWorkerUsers', ...)`
- `orderBy('name')->paginate(15)->withQueryString()`
- Dropdown user: `DB::table('user_planting_location_land_worker')->distinct()->pluck('user_id')` lalu `User::whereIn(...)`

**Output:** view `planting/planting-locations/index`.

### 1.3 Contoh mendalam — `storePlanting(...)`

**Input (body):** `plant_id`, `planted_at`, `planting_amount`, `estimated_harvest_date`, `area_ha`, format tanam, dll.  
**Validasi utama:** `plant_id` exists `plants`; `planting_batch_number` unique `plantings`; angka non-negatif.  
**Query / logika:**

- Jika batch kosong: generate `TANAM-{tahun}-{nomor}` dengan loop `Planting::where(...)->exists()` sampai unik.
- `Planting::create([...])` dengan `planting_location_id` dari route.

**Output:** redirect ke `planting-locations.plantings.index` dengan flash success.

### 1.4 Contoh mendalam — `storeLoss(...)`

**Validasi:** `planting_id`, `loss_date`, `loss_amount`, dll.  
**Query:** `Planting::findOrFail` → `PlantingLoss::create($data)`.  
**Output:** redirect ke daftar penanaman lokasi.

### 1.5 Contoh mendalam — `currentPlantings(...)`

**Query:**

```php
$plantingLocation->plantings()
    ->with(['plant', 'plant.type', 'harvests', 'losses'])
    ->whereNotNull('planted_at')
    ->orderBy('planted_at', 'desc')
    ->get();
```

Lalu **filter koleksi** `$activePlantings = $allPlantings->filter(fn ($p) => !$p->is_completed)`.

**Output:** view `planting/planting-locations/current-plantings`.

### 1.6 Contoh mendalam — `plantingHistory(...)`

**Query:** sama load plantings + relasi seperti di atas; pisahkan dengan **`filter()`** untuk: ada panen qty > 0, ada losses, atau panen gagal (qty 0/null).

### 1.7 Contoh mendalam — `harvestDetail(...)`

**Keamanan:** cocokkan `planting_location_id` planting dan `planting_id` harvest dengan route.  
**Query:**

- `$plantingLocation->tasks()->where(fn ($q) => planting_id = X OR planting_id IS NULL)`
- Serupa untuk `treatments()`, `nutrients()`, `notes()` (filter `planting_id` pada tabel turunan).

**Output:** view detail panen + aktivitas terkait.

---

## 2. `InventoryTypeController`

**Namespace:** `App\Http\Controllers\InventoryTypeController`  
**Prefix URL umum:** `/seed-stock`, `/api/inventory-types/{id}`.

### 2.1 Method helper private (penting dipahami)

| Method | Fungsi |
|--------|--------|
| `findOrCreateInventoryTypeForCertification(CertificationReport $report, ...)` | Cari **`InventoryType`** yang sudah terkait plant lewat `whereHas('certificationReports.harvest', ...)` atau buat baru dengan SKU otomatis. |
| `autoRemoveExpiredSeeds` (dipanggil dari `show`) | Menyesuaikan data saat ada benih kedaluwarsa (detail implementasi di dalam controller). |

### 2.2 Daftar method publik

| Method | Peran ringkas |
|--------|----------------|
| `index()` | Halaman utama stok benih per tanaman; agregasi lot vs seed + **`display_total_stock`**. |
| `create()` | Wizard langkah 1 — pilih tanaman + metadata; load `Warehouse::with('bins')`. |
| `storeStep1` / `createStep2` / `storeStep2` / `createStep3` | Simpan session wizard lalu lanjut ke langkah berikutnya. |
| `store(Request)` | Transaksi: buat **`InventoryType`**, bisa sekaligus hubungkan gudang/bin (sesuai isi session). |
| `getInventoryTypeData($id)` | **JSON** untuk AJAX. |
| `show(Request, InventoryType)` | Halaman detail: load `lots`, `transactions`, `notes`, `photos`, `certificationReports`, `seeds`; hitung ringkasan stok per gudang/bin; benih lulus yang belum ditautkan; optional prefill dari query `certification_report_id` + `prefill=true`. |
| `edit` / `update` / `destroy` | Ubah/hapus tipe stok benih. |
| `destroyAll()` | Hapus massal (transaksi DB). |
| `showStockAdjustment` / `storeStockAdjustment` | Form & simpan penyesuaian stok (**tambah/kurang**) pada lot; bisa buat lot baru jika `action=add` tanpa `inventory_lot_id`. |
| `storeNote` / `storePhoto` | Catatan & foto di tipe inventaris. |
| `certifiedSeeds` / `showCertifiedSeedDetail` / `showCertifiedSeedDetailWithType` | Daftar & detail benih bersertifikat. |
| `getCertificationsByPlantType` | JSON list sertifikasi untuk form. |
| `suggestStorageNumber` | JSON nomor penyimpanan. |
| `addCertifiedSeed` | Menautkan laporan sertifikasi ke tipe stok / benih (alur form). |
| `showSeedDetail` / `updateSeed` / `destroySeed` | Kelola satu baris “seed” (**compat**: model sering memakai **`CertificationReport`** sebagai `$seed`). |
| `reduceStock` | Kurangi stok dari sisi data benih + **`StockHistory`**. |
| `showSeedHistory` / `showSeedStorageDetail` | Riwayat & detail penyimpanan per benih. |
| `addSeedToWarehouse` | Tambah **`InventoryLot`** di bin dari laporan sertifikasi / seed; cek duplikasi `production_id` ↔ `storage_number`; **`DB::transaction`**. |

### 2.3 Contoh mendalam — `show(Request, InventoryType $inventoryType)`

**Langkah utama:**

1. `autoRemoveExpiredSeeds($inventoryType)` lalu `refresh`.
2. `load([...])` untuk lots (dengan warehouse/bin), transaksi stok (limit 50), notes, photos, `certificationReports`, `seeds`.
3. Loop `$inventoryType->lots` → `$lot->updateStatus()`.
4. Agregasi **`stockSummary`** group by `warehouse_id|warehouse_bin_id`, jumlahkan hanya lot **`isActiveForOperationalStock()`**.
5. Query benih lulus yang belum masuk tipe ini:  
   `CertificationReport::...->where('conclusion','LULUS')->whereNotIn('certification_report_id', $addedCertReportIds)`.
6. Prefill form: jika `?certification_report_id=&prefill=true`, load satu `CertificationReport` dan isi array prefill.
7. Map lokasi lot per seed: cocokkan **`production_id`** lot dengan **`storage_number`** seed; hitung **`seedDisplayStock`** dari lot aktif.
8. `StockHistory::where('inventory_type_id', ...)` lalu filter per benih untuk tabel riwayat.

**Output:** view detail stok benih (biasanya `warehouse.seed-stock.show` atau setara).

### 2.4 Contoh mendalam — `storeStockAdjustment(Request, InventoryType)`

**Input:** `action` (`add`|`subtract`), `quantity`, `warehouse_id`, `bin_id`, optional `inventory_lot_id`, `reason`, `notes`.  
**Validasi:** lihat rules di controller.  
**Query / transaksi:**

- `DB::beginTransaction`
- Jika lot existing: `InventoryLot::findOrFail`; jika tambah tanpa lot → `InventoryLot::create([...])` di bin.
- Update `current_stock` / `initial_stock`, `$lot->updateStatus()`, `save()`.
- `StockHistory::create` dengan `transaction_type` **`penyesuaian_tambah`** atau **`penyesuaian_kurang`**.
- `commit` → redirect `seed-stock.show`.

### 2.5 Contoh mendalam — `addSeedToWarehouse(Request, InventoryType)`

**Input:** `seed_type` (`certified`|`seed`), `warehouse_id`, `bin_id`, `production_id`, `seed_id` / `certification_report_id`.  
**Validasi bisnis:**

- Bin milik gudang yang dipilih.
- Untuk certified: laporan harus terkait tipe ini dan punya **`production_id`** (dari accessor `production_id` laporan).
- Cegah duplikasi: `InventoryLot::where(... production_id / TRIM(production_id) ...) ->exists()`.
- Isi stok awal dari `quantity_added_to_stock` / `certified_seed_quantity` laporan.

**Output:** redirect kembali ke halaman stok dengan pesan sukses (setelah commit transaksi).

---

## 3. `SaleController`

**Peran:** semua penjualan tercatat di tabel **`sale_items`** (header pembeli + struk diduplikasi per baris).

### 3.1 Daftar method

| Method | Peran |
|--------|--------|
| `index(Request)` | Dashboard: filter tanggal/kategori/search; agregasi `SaleItem` + join `warehouse_lots`; total transaksi unik `receipt_number`. |
| `showByInventoryType(InventoryType)` | Riwayat penjualan per tipe; group by `receipt_number`. |
| `create()` | Form: load gudang + bin + **`InventoryType`** dengan lot **`activeForOperationalStock()`**, lot sort **`created_at asc`** (FIFO). |
| `store(Request)` | Validasi header + array `items`; untuk tiap item: FIFO kurangi **`InventoryLot`**, tulis **`StockHistory`** (`distribusi`, qty negatif), **`SaleItem::create`**. Update `total_amount` per `receipt_number`. |
| `show(SaleItem $sale)` | Binding route ke satu `SaleItem`; muat semua baris dengan `receipt_number` sama. |
| `getBins(Request)` | JSON daftar bin untuk `warehouse_id`. |
| `getInventoryTypeDetails($id)` | JSON tipe + lot FIFO untuk UI. |
| `getBinInventoryLots(Request)` | JSON lot aktif di bin (untuk form/AJAX). |
| `destroy(SaleItem $sale)` | Ambil semua baris dengan `receipt_number` sama; **kembalikan stok** ke tiap `InventoryLot`, tulis **`StockHistory`** (`transaction_type` = `stok_masuk`, alasan pembatalan), lalu hapus semua **`SaleItem`** tersebut dalam transaksi. |

### 3.2 Contoh mendalam — `store(Request)` (inti bisnis)

**Validasi:** `receipt_number` unique di `sale_items`; tiap item wajib `warehouse_id`, `bin_id`, `quantity`, `unit_price`, dll.  
**Query inti penjualan:**

```php
$lots = InventoryLot::where('warehouse_bin_id', $binId)
    ->activeForOperationalStock()
    ->orderBy('created_at', 'asc')
    ->get();
```

Loop FIFO: kurangi `current_stock` per lot, `updateStatus()`, lalu:

```php
StockHistory::create([
    'transaction_type' => 'distribusi',
    'quantity' => -$quantityToTake,
    'old_data' => ['current_stock' => $stockBefore],
    'new_data' => ['current_stock' => $stockAfter],
    ...
]);
```

Satu **`SaleItem`** per baris form (header sama); `warehouse_lot_id` diisi dari lot pertama yang dipakai (lihat implementasi untuk multi-lot dalam satu item).

**Output:** redirect `sales.show` dengan nomor struk.

---

## 4. `ReportController`

**Prefix:** `reports.*` di `routes/web.php`.

### 4.1 Method dan ringkasan

| Method | View / output | Query / catatan |
|--------|----------------|-------------------|
| `index()` | Menu laporan | — |
| `plantingHarvest(Request)` | `reports.planting-harvest` | `Planting::with(...)->whereNotNull('planted_at')` + filter tahun/tanggal/lokasi; transform koleksi untuk kolom laporan; pagination manual `LengthAwarePaginator`. |
| `byLocation(Request)` | Select lokasi atau detail multi-tab | Jika belum pilih lokasi: list lokasi. Jika sudah: query terpisah untuk plantings, treatments, nutrients, expenses, tasks, notes, attachments dengan filter tahun/tanggal sama. |
| `productionSupplies(Request)` | Laporan sarana produksi | (lihat file untuk join/agregasi spesifik). |
| `stockPosition(Request)` | `reports.stock-position` | `InventoryLot::with(...)->where('current_stock','>')` + filter gudang/plant/inventory_type; pagination 50; hitung nilai aset = `current_stock * estimated_value_per_unit`. |
| `stockMutation(Request)` | `reports.stock-mutation` | `StockHistory::with(...)->whereNotNull('transaction_type')->whereHas('inventoryType')` + filter tanggal/plant/gudang/bin; saldo berjalan dihitung di memory per `warehouse_lot_id`. |
| `sales(Request)` | `reports.sales` | `SaleItem` dengan filter; urut `receipt_number` by max `sale_date`; group per struk; export jika `?export`. |
| `certification(Request)` | Rekap sertifikasi | `Certification::with(...)` + filter tahun/tanggal; export jika `?export`. |

### 4.2 Contoh — `stockPosition`

**Filter request:** `warehouse_id`, `plant_id`, `inventory_type_id`.  
**Query:**

```php
InventoryLot::with(['inventoryType.plant', 'warehouse', 'bin'])
    ->where('current_stock', '>', 0)
    ->when(..., fn ($q) => $q->whereHas('bin', ...))
    ->when(..., fn ($q) => $q->whereHas('inventoryType', ...))
    ->orderBy(...)
    ->paginate(50);
```

### 4.3 Contoh — `stockMutation`

**Query dasar:**

```php
StockHistory::with([...])
    ->whereNotNull('transaction_type')
    ->whereHas('inventoryType')
    ->when($date_from, fn ($q) => $q->whereDate('created_at', '>=', ...))
    ...
    ->orderBy('created_at', 'desc')
    ->paginate(50);
```

**Saldo:** loop collection; jenis transaksi dianggap penambah jika termasuk `mendaftarkan_stok`, `stok_masuk`, `penyesuaian_tambah`, `pindah_lokasi`; selain itu pengurang.

### 4.4 Contoh — `sales`

- Clone query `SaleItem` untuk filter.
- `distinct receipt_number` → urutkan dengan subquery `MAX(sale_date)`.
- Ambil halaman → load items → `groupBy('receipt_number')` → bungkus dalam paginator custom.

---

## 5. Cara memakai dokumen ini saat baca kode

1. Buka **method** di tabel di atas → buka file controller di editor pada nomor baris (gunakan “Go to Symbol” / search nama method).
2. Ikuti **alur redirect** atau **nama view** (`return view('...')`) untuk melihat UI yang memicu method tersebut.
3. Untuk query yang sama muncul di banyak tempat (misalnya `activeForOperationalStock`), baca juga model **`InventoryLot`** dan **`StockHistory`**.

---

## 6. Walkthrough mendalam (Bagian 3 & 4)

- **Bagian 3 —** **`DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART3_WALKTHROUGH_METHOD.md`**: `storeTask`, `fillTaskReport`, `InventoryTypeController::show` (+ `autoRemoveExpiredSeeds`), FIFO `SaleController::store`, `CertificationController::storeReport`.
- **Bagian 4 —** **`DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART4_WALKTHROUGH_METHOD.md`**: `addSeedToWarehouse`, `WarehouseController::storeInventoryLot` / `reduceStock` / `updateStock`, `updateTask`, `ReportController::byLocation` + export.
- **Bagian 5 —** **`DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART5_WALKTHROUGH_METHOD.md`**: export `plantingHarvest`, `productionSupplies`, `sales`, `certification`, pelengkap `exportByLocation`, serta `CertificationController::addToStock` / `addToStockDirect`.

Untuk **Bagian 5** (export lain / `addToStock`): sebutkan prioritas method jika ingin ditambahkan.
