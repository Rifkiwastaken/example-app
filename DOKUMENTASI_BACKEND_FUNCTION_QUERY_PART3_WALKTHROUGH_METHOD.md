# Walkthrough Mendalam Method Backend (Bagian 3)

Dokumen ini melanjutkan:

- **`DOKUMENTASI_BACKEND_FUNCTION_QUERY.md`** (gambaran umum)
- **`DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART2_DETAIL_CONTROLLER.md`** (ringkasan per controller)

Berisi **alur langkah demi langkah** untuk method-method yang panjang dan penting bagi pemahaman bisnis.

---

## 1. `PlantingLocationController::storeTask`

**File:** `app/Http/Controllers/PlantingLocationController.php`  
**Route (contoh):** `POST planting-locations/{plantingLocation}/tasks` → `planting-locations.tasks.store`

### 1.1 Guard & izin

1. Ambil `$user = auth()->user()`.
2. **`isAssignedToPlantingLocation($plantingLocation)`** → jika tidak, `abort(403)`.
3. **`canAddDataInPelaporan($plantingLocation)`** → jika tidak, `abort(403)` (kepala satuan tugas / penangkar dalam konteks pelaporan).

### 1.2 Validasi input

Field utama yang divalidasi (ringkas):

- `title`, `description`, `task_report`, `checklist` (string JSON dari form),
- `attachments[]` file,
- `planting_id` (opsional / `"umum"`),
- `new_status`, `new_priority`, tanggal mulai/jatuh tempo,
- `assigned_to`, `created_by`, dll.

### 1.3 Normalisasi data

| Langkah | Perilaku |
|---------|-----------|
| Checklist | `json_decode` string checklist → array; jika gagal → array kosong. |
| Planting | Jika `planting_id` terisi dan bukan `"umum"` → pastikan **`$plantingLocation->plantings()->find(...)`** ada; jika tidak → `planting_id = null` (laporan umum lokasi). |
| Asosiasi | `association = 'penanaman'`. |
| Penugasan | Kumpulkan **`landWorkerUsers`** lokasi + **admin**, **unique** `user_id`. Jika `assigned_to` satu user → set kolom itu; jika `"semua_user"` atau kosong → `assigned_to = null`, **`collaborators`** = array semua `user_id`. |
| Pembuat | Jika `created_by` kosong → user yang login. |
| Lampiran | Upload tiap file ke disk `public` path prefix **`task-attachments/`**, simpan array path di `$data['attachments']`. |

### 1.4 Cabang `action_type`

| `action_type` | Hasil |
|---------------|--------|
| **`save_template`** | Buat **`TaskTemplate`** dari judul/deskripsi/checklist/lampiran; redirect ke **`planting-locations.show`** dengan sukses atau error log. |
| **`save_and_fill_report`** | Setelah **`Task::create`**, redirect dengan **`fill_task_id`** ke halaman lokasi atau tab laporan planting (query `from_planting_reports`, `planting_id_for_redirect`). |
| *(default)* | **`Task::create($data)`** saja. |

### 1.5 Setelah task tercipta

1. Tentukan **`$userIds`**: satu user ditugaskan, atau seluruh `collaborators`.
2. Jika ada penerima: **`SendTaskNotificationJob::dispatch($task, $userIds)`** (email/job antrian).
3. Redirect:
   - dari halaman laporan planting → route **`planting-locations.plantings.reports`**,
   - atau **`planting-locations.show`**.

### 1.6 Model & tabel terlibat

- **`Task`** → tabel `planting_tasks`.
- **`TaskTemplate`** (opsional).
- **`SendTaskNotificationJob`** (queue).

---

## 2. `PlantingLocationController::fillTaskReport`

**Route (contoh):** `PUT planting-locations/{plantingLocation}/tasks/{task}/fill` → `planting-locations.tasks.fill`

### 2.1 Guard

1. User harus **assigned** ke lokasi (`isAssignedToPlantingLocation`).
2. **`canAddDataInPelaporan`** atau **`isAdmin`** (penangkar vs admin/KST — lihat kondisi exact di file).
3. Task harus punya **`planting_id`** yang mengarah ke **`Planting`** dengan **`planting_location_id`** sama dengan lokasi route (laporan terikat penanaman).
4. Jika task punya **`assigned_to`** dan user bukan assignee dan bukan admin → `403`.

### 2.2 Validasi isi laporan

- `task_report` (wajib),
- `new_status`,
- `checklist` (JSON opsional),
- `start_date`, `start_time` (wajib),
- lampiran file opsional.

### 2.3 Merge checklist & lampiran

- Checklist baru dari JSON, atau pertahankan **`$task->checklist`** jika kosong di request.
- Lampiran baru **digabung** dengan **`$task->attachments`** yang sudah ada (array path).

### 2.4 Update baris task

Array **`$updateData`** berisi minimal: `task_report`, `new_status`, `start_date`, `start_time`, plus checklist/lampiran jika ada.

- **`created_by`** di-set ke user yang mengisi (laporan dianggap dari pengisi).
- Role **`penangkar`**: hanya field laporan tersebut.
- **Admin / kepala_satuan_tugas**: tambah **`last_edited_at`**, **`last_edited_by`**.

### 2.5 Redirect

- Jika **`from_planting_reports`** + **`planting_id_for_redirect`** → kembali ke **`planting-locations.plantings.reports`** dengan anchor `#laporan-subtab`.
- Jika tidak → **`planting-locations.show`**.

---

## 3. `InventoryTypeController::show` + `autoRemoveExpiredSeeds`

**Route:** `GET seed-stock/{inventoryType}` → `seed-stock.show`

Method **`show`** sangat panjang; berikut urutan logika yang disarankan saat membaca kode.

### 3.1 Langkah awal: `autoRemoveExpiredSeeds($inventoryType)`

Dipanggil **sebelum** render view.

1. Cari **`CertificationReport`** dengan **`inventory_type_id`** = tipe ini, **`expiry_date`** sudah lewat hari ini.
2. Untuk tiap laporan kedaluwarsa:
   - Ambil **`storage_number`** (nomor penyimpanan).
   - Jika ada: cari **`InventoryLot`** dengan **`production_id`** cocok (termasuk `TRIM`), hapus stok lot (`StockHistory` penghapusan, `lot->delete()` jika perlu).
   - Tulis **`StockHistory`** untuk penghapusan data benih ( **`action` `delete`**, **`transaction_type` `penghapusan`**, **`old_data`** snapshot laporan).
   - Lepas ikatan dari stok: mis. **`inventory_type_id = null`**, **`quantity_added_to_stock = 0`** pada baris laporan (detail persis di method).
3. **`DB::commit`** atau rollback diam-diam jika error.

Intinya: **membuka halaman detail** dapat memicu pembersihan otomatis benih/lot yang sudah lewat masa edar.

### 3.2 Refresh & eager load

1. `$inventoryType->refresh()`.
2. **`load([...])`** untuk:
   - **`lots`** (+ warehouse, bin), urut expiry,
   - **`transactions`** (stok, limit 50),
   - **`notes`**, **`photos`**,
   - **`certificationReports`** & **`seeds`** (compat) dengan harvest/plant/location.

### 3.3 Status lot & ringkasan gudang

1. Loop tiap lot → **`$lot->updateStatus()`**.
2. Ambil semua lot tipe → **`groupBy`** kombinasi gudang+bin → jumlahkan stok **hanya lot aktif operasional** (`isActiveForOperationalStock()`).
3. Hitung berapa gudang unik yang masih punya stok operasional (`operationalWarehouseCount`).

### 3.4 Benih bersertifikat “tersedia ditambahkan”

Query **`CertificationReport`**:

- `conclusion = LULUS`, qty benih > 0,
- **`certification_report_id`** belum ada di **`$inventoryType->certificationReports`**.

### 3.5 Prefill form (query string)

Jika **`certification_report_id`** + **`prefill=true`**: load satu laporan, isi array **`prefillData`** untuk form tambah benih.

### 3.6 Seed ↔ lot (tampilan per baris benih)

Untuk tiap “seed” (baris **`certification_reports`** lewat relasi `seeds`):

- Cocokkan **`production_id`** pada **`InventoryLot`** dengan **`storage_number`** seed.
- **`seedDisplayStock`**: jika sudah ada di gudang → jumlahkan **`current_stock`** lot yang **aktif**; jika belum → pakai kuantitas dari data laporan.

### 3.7 Total tampilan & riwayat

- **`displayTotalQuantity`**: jumlahkan tampilan per seed (gudang vs data murni).
- **`allStockHistories`**: semua **`StockHistory`** untuk `inventory_type_id`, lalu **filter per seed** by lot IDs atau JSON **`certification_report_id`** di **`old_data`/`new_data`**.
- **`collectSeedHistoriesFromAll`**: query tambahan dengan **`JSON_EXTRACT`** MySQL untuk jejak per `certification_report_id`.
- **`archivedRemovedSeeds`**: filter histori untuk arsip benih yang dihapus otomatis (masa edar) atau manual.

### 3.8 Output

**`return view('warehouse.seed-stock.show', compact(...))`** dengan banyak variabel untuk tab ringkasan, gudang, riwayat, arsip.

---

## 4. `SaleController::store` (FIFO penjualan)

**Route:** `POST sales` → `sales.store`

### 4.1 Ringkasan transaksi

1. **`DB::beginTransaction()`**.
2. Validasi header pembeli + array **`items`** (minimal satu baris).
3. Untuk **tiap item** dalam loop:

### 4.2 Validasi bin & stok

1. **`Bin::findOrFail`** → cek **`warehouse_id`** konsisten dengan pilihan form.
2. Ambil lot:  
   **`InventoryLot::where('warehouse_bin_id', $binId)->activeForOperationalStock()->orderBy('created_at', 'asc')->get()`**  
   → FIFO, exclude habis/kadaluarsa operasional.
3. Jika tidak ada lot aktif tapi ada stok fisik non-aktif → pesan error bahwa stok tidak dijual lewat FIFO (habis/expired).
4. Pastikan **satu bin tidak mencampur banyak `inventory_type_id`** (di kode dicek `unique` pada lot).

### 4.3 Alokasi kuantitas FIFO

Untuk sisa **`$remainingQuantity`**:

- Ambil **`min(sisa, lot->current_stock)`** dari lot terurut.
- Kurangi **`current_stock`**, **`updateStatus()`**, **`save()`**.
- Catat **`StockHistory`**:
  - **`transaction_type`** = **`distribusi`**
  - **`quantity`** = **negatif** (pengeluaran)
  - **`old_data` / `new_data`** stok lot untuk audit.

### 4.4 Baris `sale_items`

- **`SaleItem::create`** menggabungkan **header** (struk, pembeli, pembayaran, user) dengan **`warehouse_lot_id`** (lot pertama yang dipakai dalam implementasi saat ini untuk baris tersebut), qty, harga, subtotal.

### 4.5 Penutup

- **`SaleItem::where('receipt_number', ...)->update(['total_amount' => $totalAmount])`** menyamakan total header.
- **`commit`** → redirect **`sales.show`** dengan **`receipt_number`**.

---

## 5. `CertificationController::storeReport`

**Route:** `POST certifications/harvests/{harvest}/reports` → `certifications.reports.store`

### 5.1 Nomor laporan BPSB

Jika **`report_number_bpsb`** kosong:

1. Hitung **`CertificationReport::whereYear('report_date', $year)->count() + 1`**.
2. Bentuk string **`BPSB-{tahun}-{nomor 6 digit}`**.
3. Loop **`while`** sampai **`unique`** di kolom **`report_number_bpsb`**.
4. **`$request->merge([...])`**.

### 5.2 Validasi form laporan

Rules panjang untuk lapangan, isolasi, mutu benih, **`planting_batch_number`** (wajib), **`expiry_date`**, **`certified_seed_quantity`**, **`conclusion`**, file scan, dll.

### 5.3 Harvest untuk laporan

- Default: **`$harvest`** dari route.
- Jika **`renew_from_report_id`** ada: load laporan lama → pakai **`harvest`** dari laporan lama; set **`report_type`** ke sertifikasi ulang.

### 5.4 Field sistem

- **`harvest_id`** = harvest yang dipakai.
- **`certification_status`** awal `dalam_proses`, lalu setelah create disesuaikan dari **`conclusion`** (lulus / tidak lulus).
- **`seed_class_requested`** dari hasil kelas benih form atau default.

### 5.5 File scan

Upload ke storage **`public`**, path disimpan **`scan_file_path`**.

### 5.6 Persist

1. **`DB::beginTransaction()`**.
2. **`CertificationReport::create($data)`**.
3. Update lagi **`certification_status`** berdasarkan **`conclusion`**.
4. **`commit`** atau rollback dengan pesan error.

### 5.7 Redirect sukses

Jika harvest punya **`plant_id`**: redirect **`certifications.by-plant`** ke plant terkait; jika tidak, **`back()`** dengan flash.

---

## 6. Cara belajar dari walkthrough ini

1. Buka file controller pada **nomor baris** method (search `function storeTask` dll).
2. Samakan **setiap blok** di atas dengan **blok kode** (guard → validate → normalize → DB → response).
3. Uji di browser dengan **Network tab** untuk melihat payload form yang mengisi field validasi.

---

## 7. Lanjutan (Bagian 4 & 5)

- **`DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART4_WALKTHROUGH_METHOD.md`**: **`addSeedToWarehouse`**, **`WarehouseController::storeInventoryLot`** / **`reduceStock`** / **`updateStock`**, **`updateTask`**, dan **`byLocation`** (+ export CSV/PDF).
- **`DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART5_WALKTHROUGH_METHOD.md`**: export **`plantingHarvest`**, **`productionSupplies`**, **`sales`**, **`certification`**, konsistensi **`exportByLocation`**, serta **`CertificationController::addToStock`** / **`addToStockDirect`**.
