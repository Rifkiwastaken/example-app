# Walkthrough Mendalam Method Backend (Bagian 4)

Melanjutkan **`DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART3_WALKTHROUGH_METHOD.md`**.

Berisi penjelasan langkah demi langkah untuk:

- **`InventoryTypeController::addSeedToWarehouse`**
- **`WarehouseController::storeInventoryLot`**, **`reduceStock`**, **`updateStock`**
- **`PlantingLocationController::updateTask`**
- **`ReportController::byLocation`** + **`exportByLocation`** / **`exportByLocationExcel`**

---

## 1. `InventoryTypeController::addSeedToWarehouse`

**Route:** `POST seed-stock/{inventoryType}/add-seed-to-warehouse` → `seed-stock.add-seed-to-warehouse`

### 1.1 Validasi request

Field wajib / diperiksa:

- **`seed_type`**: `certified` | `seed`
- **`warehouse_id`**, **`bin_id`** (exists di tabelnya)
- **`production_id`** opsional
- **`certification_report_id`** atau **`seed_id`** (yang relevan untuk tipe seed)

### 1.2 Konsistensi gudang–bin

- **`Warehouse::findOrFail`**, **`Bin::findOrFail`**
- Jika **`bin.warehouse_id`** ≠ **`warehouse.warehouse_id`** → error validasi bin.

### 1.3 Transaksi `DB::beginTransaction`

#### Cabang **`seed_type === 'certified'`**

1. **`CertificationReport::findOrFail(certification_report_id)`**
2. Pastikan laporan ada di koleksi **`$inventoryType->certificationReports`** (laporan tertaut ke tipe ini).
3. **`$initialStock`** = `quantity_added_to_stock` atau fallback `certified_seed_quantity`.
4. **`$stockUnit`** dari kolom laporan atau fallback unit tipe.
5. **`$expiryDate`** dari laporan.
6. **`productionFromCertification`** = **`$certificationReport->production_id`** (accessor/kolom); harus ada — jika kosong → error “Production ID … wajib diisi”.

#### Cabang **`seed` (data stok benih)**

1. **`CertificationReport::findOrFail(seed_id)`**
2. Pastikan **`inventory_type_id`** laporan = tipe route.
3. Stok awal & satuan sama seperti certified (dari kolom laporan).

### 1.4 Aturan “satu kali masuk gudang” per nomor penyimpanan

Jika ada **`storage_number`** pada **`$sourceReport`**:

- Cek **`InventoryLot`** dengan **`inventory_type_id`** sama dan **`production_id`** = `TRIM(storage_number)` (atau raw TRIM).
- Jika **`exists`** → tolak (sudah ada di gudang).

### 1.5 Penentuan **`production_id`** untuk lot baru

Urutan pengisian (singkat):

1. Dari request `production_id` jika tidak kosong.
2. Else dari **`sourceReport->storage_number`**.
3. Untuk certified: bisa dipaksa dari **`productionFromCertification`**.
4. Jika masih kosong → generate **`LOT-{tahun}-{nomor urut tahun}`**.

Lalu cek lagi **tidak boleh bentrok** dengan lot lain (`alreadyUsedLot` pada `production_id` yang sama).

### 1.6 Persistensi

1. **`InventoryLot::create`** dengan `warehouse_bin_id`, stok awal/saat ini, unit, kedaluwarsa.
2. **`$lot->updateStatus()`**.
3. **`StockHistory`** dengan **`transaction_type`** **`stok_masuk`**, qty positif.
4. Sinkron **`planting_batch_number`** pada **`CertificationReport`** jika beda dengan **`productionId`** (agar nomor penyimpanan konsisten).
5. **`commit`** → redirect **`seed-stock.show`**.

---

## 2. `WarehouseController::storeInventoryLot`

**Route:** `POST warehouse-locations/{warehouse}/bins/{bin}/inventory-lots` → `warehouse-locations.bins.inventory-lots.store`

Versi ini dipakai dari UI **halaman lokasi gudang** (bukan hanya wizard seed-stock).

### 2.1 Validasi

- **`inventory_type_id`**, **`seed_id`** (= `certification_reports.certification_report_id`),
- **`expiry_date`** wajib,
- **`production_id`** opsional.

### 2.2 Integritas data

1. **`InventoryType`**, **`CertificationReport`** harus cocok (**`report.inventory_type_id`**).
2. Duplikasi cek sama seperti **`addSeedToWarehouse`** berdasarkan **`storage_number`** ↔ **`production_id`** lot.

### 2.3 Stok & **`production_id`**

- **`initialStock`** dari `quantity_added_to_stock` / `certified_seed_quantity`.
- **`productionId`**: prioritaskan **`production_id`** dari sertifikasi; else **`storage_number`**; else generate **`LOT-...`**.
- **`expiry_date`** pakai dari **request** (user wajib isi).

### 2.4 Tulis jejak

1. **`InventoryLot::create`**, **`updateStatus()`**.
2. **`StockHistory`** **`stok_masuk`** dengan **`old_data`/`new_data`** stok (0 → awal).
3. Sinkron **`planting_batch_number`** pada laporan jika perlu.

### 2.5 Output

Redirect **`warehouse-locations.show`** dengan flash sukses.

---

## 3. `WarehouseController::reduceStock`

**Route:** `POST .../inventory-lots/{lot}/reduce-stock` → JSON (AJAX).

### 3.1 Verifikasi konteks

- Lot harus di **bin** yang sesuai route (`warehouse_bin_id`, `warehouse_id`).

### 3.2 Validasi body

- **`reduce_quantity`**: min 0.01, **max** = **`current_stock`** lot (rule dinamis).
- **`reduce_reason`** wajib.

### 3.3 Transaksi

1. Kurangi **`lot.current_stock`**, **`updateStatus()`**, **`save()`**.
2. **Sinkron ke `CertificationReport`** jika **`lot.production_id`** cocok dengan **`planting_batch_number` / `harvest_batch_number`** (TRIM):
   - Kurangi **`quantity_added_to_stock`** / **`seed_unit_quantity`** proporsional.
   - **`StockHistory`** dengan **`action`** **`reduce_stock`**, **`old_data`/`new_data`** snapshot laporan (audit).
3. **`StockHistory`** utama untuk lot: **`transaction_type`** **`pengurangan`**, qty **negatif**, **`old_data`/`new_data`** stok lot.

### 3.4 Response JSON

`success`, pesan, **`remaining_stock`**.

---

## 4. `WarehouseController::updateStock`

**Route:** `PUT .../inventory-lots/{lot}/update-stock` → JSON.

### 4.1 Validasi & laporan

Sama pola verifikasi bin + validasi **`update_reason`**, **`inventory_type_id`**, **`seed_id`**, **`expiry_date`**, **`production_id`**.

### 4.2 Logika utama

1. **`CertificationReport`** harus cocok **`inventory_type_id`**.
2. Snapshot **`oldData`** dari lot (audit).
3. **`newStock`** dari **`quantity_added_to_stock` / certified qty** laporan (bukan sembarang input user untuk qty — mengikuti sumber benih).
4. **`lot->update`** termasuk **`production_id`**, **`expiry_date`**, **`current_stock`**, **`initial_stock`**, dll.
5. **`updateStatus()`**.
6. **`StockHistory`** **`penyesuaian_tambah`** dengan selisih qty vs stok lama (field **`notes`** berisi JSON ringkas old/new).

### 4.3 Response

JSON sukses + payload ringkas **`lot`**.

---

## 5. `PlantingLocationController::updateTask`

**Route:** `PUT planting-locations/{plantingLocation}/tasks/{task}` → `planting-locations.tasks.update`

### 5.1 Guard (beda dari `storeTask`)

- **`isAssignedToPlantingLocation`**
- Hanya **admin** atau **`canManageDataInPelaporan`** (bukan sekadar `canAddDataInPelaporan`) — mengedit laporan yang sudah ada.

### 5.2 Validasi

Mirip **`storeTask`**: judul, status, prioritas, tanggal, checklist, lampiran, **`planting_id`** (dengan opsi umum).

### 5.3 Normalisasi

- Checklist dari JSON string.
- **`planting_id`**: cari di **`$plantingLocation->plantings()`** atau **`null`** untuk “umum”.
- Lampiran baru: upload ke **`task-attachments`** — **mengganti** array lampiran di payload update (perilaku di blok `if ($request->hasFile('attachments'))`; bandingkan dengan **`fillTaskReport`** yang merge).

### 5.4 Metadata edit

- **`last_edited_at`**, **`last_edited_by`**.

### 5.5 Redirect

Sama pola **`from_planting_reports`** → tab laporan dengan **`#laporan-subtab`**, atau **`planting-locations.show`**.

---

## 6. `ReportController::byLocation`

**Route:** `GET reports/by-location` → `reports.by-location`

### 6.1 Tanpa lokasi terpilih

Jika **`planting_location_id`** kosong → view pemilihan **`reports.by-location-select`** dengan daftar lokasi, tanaman, tahun.

### 6.2 Export

Jika ada **`export`** di query → **`exportByLocation($request, $selectedLocationId)`** (lihat bagian 7).

### 6.3 Halaman utama laporan lokasi

1. **`PlantingLocation::findOrFail`**
2. Tujuh query terpisah dari relasi lokasi (semua via **HasMany / HasManyThrough** model):
   - **`plantings`** (+ plant, harvest, losses),
   - **`treatments`**, **`nutrients`**, **`expenses`**, **`tasks`**, **`notes`**, **`attachments`**

### 6.4 Filter sinkron

Filter yang sama diterapkan ke **semua** query (tahun, rentang tanggal, **`plant_id`**):

- Planting: filter langsung **`plant_id`** pada query penanaman.
- Treatment/nutrient/expense: **`whereHas('planting', ... plant_id)`**.

### 6.5 Filter **`planting_id`** spesifik

Filter ini memakai **`$plantingQuery->where('planting_id', $plantingId)`** (primary key penanaman di model ini **`planting_id`**). Query lain memakai nama tabel eksplisit untuk FK planting (`planting_treatments.planting_id`, dll.).

Query lain memakai nama tabel eksplisit untuk FK planting (`planting_treatments.planting_id`, dll.).

### 6.6 Statistik agregat

Hitung total penanaman, panen, biaya (`sum amount`), jumlah perlakuan/pupuk/tugas/catatan/lampiran, tugas selesai.

### 6.7 Output

View **`reports.by-location`** dengan banyak variabel compact untuk tab/ringkasan.

---

## 7. Export laporan per lokasi

### 7.1 **`exportByLocation(Request, $selectedLocationId)`**

1. **`PlantingLocation::findOrFail`**
2. Bangun query **selaras** dengan **`byLocation`**: tahun, tanggal, **`plant_id`**, **`planting_id`**, serta filter paralel untuk treatment/nutrient/expense/task/note (tanpa **`losses`** pada eager load plantings — bisa disamakan jika diperlukan konsistensi tampilan).
3. **`export`**:
   - **`pdf`** → render view **`reports.exports.by-location-pdf`** (HTML untuk cetak/PDF).
   - **`excel`** → **`exportByLocationExcel(...)`**.
   - Selain itu → **`redirect()->back()`**.

### 7.2 **`exportByLocationExcel(...)`**

- Nama file CSV: **`Laporan_Per_Lokasi_Lahan_{nama}_{tanggal}.csv`**
- **`response()->stream`** dengan **`fopen('php://output')`**
- BOM UTF-8 (`EF BB BF`) agar Excel membaca UTF-8.
- Header kolom CSV tetap; baris berikutnya mengisi data per jenis (penanaman, perlakuan, pupuk, biaya, tugas, catatan, lampiran) — lihat loop di method untuk urutan pastinya.

---

## 8. Ringkasan pola yang muncul lagi

| Pola | Di mana |
|------|---------|
| **`production_id` ↔ `storage_number` / batch laporan** | `addSeedToWarehouse`, `storeInventoryLot`, `reduceStock`, matching di modul gudang |
| **`StockHistory` ganda** (lot + opsional laporan) | `reduceStock` |
| **Filter laporan paralel** | `byLocation`, `exportByLocation` |
| **Guard pelaporan** | `updateTask` memakai **`canManageDataInPelaporan`** |

---

## 9. Pranala dokumen

- Ringkas: **`DOKUMENTASI_BACKEND_FUNCTION_QUERY.md`**
- Per controller: **`DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART2_DETAIL_CONTROLLER.md`**
- Walkthrough batch 1: **`DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART3_WALKTHROUGH_METHOD.md`**
- Walkthrough batch 2 (file ini): **`DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART4_WALKTHROUGH_METHOD.md`**
- Walkthrough batch 3 (export laporan & tambah stok dari sertifikasi): **`DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART5_WALKTHROUGH_METHOD.md`**

---

## 10. Bagian 5 (walkthrough lengkap)

Sudah ada **`DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART5_WALKTHROUGH_METHOD.md`** untuk **`exportPlantingHarvest`**, **`exportProductionSupplies`**, **`exportSales`**, **`exportCertification`**, **`exportByLocation`** / **`exportByLocationExcel`**, serta **`CertificationController::addToStock`** dan **`addToStockDirect`**.
