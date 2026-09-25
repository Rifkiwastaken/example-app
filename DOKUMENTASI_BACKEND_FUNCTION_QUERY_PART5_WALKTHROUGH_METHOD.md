# Walkthrough Mendalam Method Backend (Bagian 5)

Melanjutkan **`DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART4_WALKTHROUGH_METHOD.md`**.

Berisi penjelasan langkah demi langkah untuk:

- **`ReportController`**: export laporan (**tanam–panen**, **sarana produksi**, **penjualan**, **sertifikasi**) dan pelengkap **`exportByLocation`** (selaras dengan **`byLocation`**).
- **`CertificationController::addToStock`** dan **`addToStockDirect`**.

---

## 1. Realisasi tanam & panen — `plantingHarvest` → `exportPlantingHarvest`

**File:** `app/Http/Controllers/ReportController.php`  
**Route:** mengikuti definisi laporan tanam–panen di `routes/web.php` (mis. GET dengan query `export`).

### 1.1 Masuk export

Jika request punya parameter **`export`** → **`exportPlantingHarvest($request)`** (bukan halaman utama).

### 1.2 Query dasar

- **`Planting::with(...)`** dengan **`plant.type`** dan **`harvest.certificationReports`** (diurut **`report_date` DESC**) — satu baris laporan pemeriksaan terbaru dipakai sebagai sumber **`certification_status`** dan **`seed_class_requested`** (setelah refactor, data ini ada di **`certification_reports`**, bukan model **`Harvest::certification`** yang lama).
- Hanya penanaman dengan **`planted_at`** tidak null.

### 1.3 Filter (sama antara halaman & export)

| Parameter | Efek |
|-----------|------|
| **`year`** | **`whereYear('planted_at', …)`** |
| **`plant_id`** | **`where('plant_id', …)`** pada planting |
| **`planting_location_id`** | **`where('planting_location_id', …)`** |
| **`date_from`** / **`date_to`** | rentang **`planted_at`** |

### 1.4 Transform (inti bisnis)

Untuk tiap **`Planting`**:

1. **`$latestReport`** = laporan sertifikasi pertama dari relasi (**terbaru karena sudah di-order**).
2. **`$certification`** = objek sintetis dengan **`certification_status`** dan **`seed_class_requested`** dari laporan tersebut (atau null jika tidak ada laporan).
3. **Luas ha:** **`area_ha`** planting, fallback **`location->map_size`**.
4. **Calon benih (kg):** dari **`harvest.quantity`** dikonversi ke kg memakai faktor untuk **`kg`**, **`gram`**, **`ton`**, **`kuintal`**, dll.
5. **Benih bersertifikat (kg):** sama dengan calon benih **hanya jika** status laporan **`lulus`** atau **`selesai`**.
6. **Kelas benih tampilan:** dari **`seed_class_requested`** dengan pemetaan **`BS`→BS-BD**, **`BD`→BD-BP**, **`BP`→BP-BR**.

Halaman utama setelah itu mengurutkan berdasarkan komoditas + kelas, lalu paginate manual (**50** per halaman).

### 1.5 **`exportPlantingHarvest`**: cabang output

| **`export`** | Output |
|--------------|--------|
| **`pdf`** | View **`reports.exports.planting-harvest-pdf`** + **`$plantings`** yang sudah ditransform |
| **`excel`** | **`exportPlantingHarvestExcel($plantings)`** |
| lainnya | **`redirect()->back()`** |

### 1.6 **`exportPlantingHarvestExcel`**

- Nama file CSV: **`Laporan_Realisasi_Tanam_Panen_{tanggal}.csv`**
- **`response()->stream`**, BOM UTF-8 (**`EF BB BF`**).
- Kolom: **No, KOMODITI, KELAS BENIH, VARIETAS, LUAS (ha), LOKASI KEGIATAN, TANAM, PANEN, CALON BENIH (kg), BENIH BERSERTIFIKAT**.
- Angka memakai **`number_format`** dengan pemisah Indonesia (`,`, `.`) di kolom tertentu.

---

## 2. Sarana produksi — `productionSupplies` → `exportProductionSupplies`

### 2.1 Masuk export

Request ada **`export`** → **`exportProductionSupplies($request)`**.

### 2.2 Query **`Expense`**

**`with`:** **`planting.plant`**, **`planting.location`**, **`responsiblePerson`** (penanggung jawab).

Filter (selaras **`productionSupplies`** utama):

| Parameter | Efek |
|-----------|------|
| **`year`** | tahun **`expense_date`** |
| **`date_from`** / **`date_to`** | rentang **`expense_date`** |
| **`plant_id`** | **`whereHas('planting', plant_id)`** |
| **`planting_location_id`** | **`whereHas('planting', planting_location_id)`** |
| **`expense_type`** | **`where('expense_type', …)`** |

Urutan: **`expense_date` DESC**, **`created_at` DESC**.

### 2.3 Cabang export

| **`export`** | Output |
|--------------|--------|
| **`pdf`** | **`reports.exports.production-supplies-pdf`** |
| **`excel`** | **`exportProductionSuppliesExcel`** |
| lainnya | **`redirect()->back()`** |

### 2.4 **`exportProductionSuppliesExcel`**

- File: **`Laporan_Penggunaan_Sarana_Produksi_{tanggal}.csv`**
- Kolom: **No, Tanggal Pengeluaran, Nama Pengeluaran, Jenis Pengeluaran, Komoditas, Lokasi Lahan, Penanggung Jawab, Total Biaya**.
- **`expenseTypes`** memetakan kode **`perawatan`**, **`nutrisi`**, dll. ke label Indonesia.

---

## 3. Penjualan — `sales` → `exportSales`

### 3.1 Masuk export

**`export`** ada → **`exportSales($request)`**.

### 3.2 Query **`SaleItem`**

- **`with`:** **`user`**, **`inventoryType.plant`**, **`inventoryLot.warehouse`**.
- **`whereNotNull('receipt_number')`**.
- Filter tahun / tanggal pada **`sale_date`**.
- **`plant_id`** → **`whereHas('inventoryType.certificationReports.harvest', plant_id)`** (rantai sampai panen untuk filter komoditas).
- **`planting_location_id`** langsung pada **`sale_items`** jika kolom ada.

Registrasi penjualan digrupkan **`groupBy('receipt_number'`**: baris pertama per struk memegang koleksi **`items`**, **`total_items`** = jumlah kuantitas baris.

### 3.3 Cabang export

| **`export`** | Output |
|--------------|--------|
| **`pdf`** | **`reports.exports.sales-pdf`** |
| **`excel`** | **`exportSalesExcel`** |

### 3.4 **`exportSalesExcel`**

- File: **`Laporan_Penjualan_Distribusi_{tanggal}.csv`**
- Kolom: **No, No. Struk, Tanggal Penjualan, Pembeli, Kontak Pembeli, Komoditas**, **Jumlah Item**, **Total Penjualan**, **Metode Pembayaran**, **Status Pembayaran**, **Dicatat Oleh**.
- Komoditas digabung unik nama tanaman dari tiap item.
- Baris total menjumlahkan **`total_amount`** semua struk.

---

## 4. Rekap sertifikasi — `certification` → `exportCertification`

### 4.1 Masuk export

**`export`** ada → **`exportCertification($request)`**.

### 4.2 Query **`Certification`**

**`with`:** **`plant.type`**, **`harvest.plant.type`**, **`plantingLocation`**, **`reports`** ( **`report_date` DESC**).

Filter utama:

- **Tahun:** ada laporan dengan **`report_date`** di tahun itu **atau** **`created_at`** certification di tahun itu (**perlu diperhatikan:** kombinasi **`whereHas`** + **`orWhereYear`** bisa melebar ke luar filter laporan — perilaku sama dengan halaman utama).
- **Tanggal:** kombinasi **`whereHas reports`** dengan **`orWhereDate` pada `created_at`** (mirror halaman).
- **`plant_id`** langsung pada baris **`certifications`**.

### 4.3 Transform status tampilan

Untuk tiap **`Certification`**, **`reports->first()`** = laporan terbaru:

- Kesimpulan **`LULUS`** → **`certification_status`** semantik **`lulus`**.
- **`TIDAK LULUS`** → **`tidak_lulus`**.
- Lainnya → fallback **`dalam_proses`** atau status certification.

### 4.4 Cabang export

| **`export`** | Output |
|--------------|--------|
| **`pdf`** | **`reports.exports.certification-pdf`** |
| **`excel`** | **`exportCertificationExcel`** |

### 4.5 **`exportCertificationExcel`**

- File: **`Laporan_Rekap_Status_Sertifikasi_{tanggal}.csv`**
- Kolom: **No, Komoditas/Tanaman, Varietas, Lokasi Lahan, Kelas Benih Diminta, Status Sertifikasi, Tanggal Laporan Terakhir, Kesimpulan Terakhir, Jumlah Laporan**.

---

## 5. Export per lokasi — `exportByLocation` / `exportByLocationExcel`

Ringkasan (detail struktur **`byLocation`** ada di **Bagian 4**):

1. **`PlantingLocation::findOrFail`**
2. Query paralel planting, treatment, nutrient, expense, task, note, attachment dengan **filter yang sama** seperti **`byLocation`**: tahun, rentang tanggal, **`plant_id`**, dan **`planting_id`** (filter planting memakai kolom **`planting_id`** pada planting; query treatment/nutrient/expense/task/note memakai nama tabel pivot yang konsisten dengan method utama).
3. **`pdf`** → view **`reports.exports.by-location-pdf`**; **`excel`** → **`exportByLocationExcel`** dengan **`Laporan_Per_Lokasi_Lahan_{nama}_{tanggal}.csv`**, BOM UTF-8, beberapa blok baris per jenis data **plus** baris ringkasan total pengeluaran.

---

## 6. Tambah ke stok dari sertifikasi — `CertificationController`

**File:** `app/Http/Controllers/CertificationController.php`

### 6.1 **`addToStock(Request, CertificationReport $report)`**

**Inti:** bukan penyimpanan stok langsung — **redirect** ke halaman stok benih dengan konteks laporan.

1. Validasi: **`inventory_type_id`** wajib dan harus ada di **`inventory_types`**.
2. **`InventoryType::findOrFail`**.
3. **`$report->load('certification.plant.type')`** untuk data tampilan.
4. Redirect ke **`seed-stock.show`** dengan query:
   - **`inventoryType`** = ID tipe,
   - **`certification_report_id`**,
   - **`prefill=true`**,
   - **`tab=certified-seeds`** (tab Data Benih terbuka).

Pengguna melanjutkan pengisian/pemilihan lot di UI stok benih.

### 6.2 **`addToStockDirect(Request, CertificationReport $report)`**

**Langsung menambah catatan stok** tanpa form manual panjang.

1. **Syarat minimal:** **`production_id`** (dari **`planting_batch_number`** / **`harvest_batch_number`** pada laporan), **`expiry_date`**, **`certified_seed_quantity`** — jika kurang → **`back`** dengan error.
2. **`$plant`** dari **`$report->harvest->plant`** — tanpa tanaman → error.
3. **Satuan & estimasi:** dari **`seed_unit`** / **`certified_seed_unit`**, **`estimated_sale_price_per_kg`**.
4. **`InventoryType`** dicari dengan **`whereHas('certificationReports.harvest.planting', plant_id)`**. Jika tidak ada:
   - Buat **`InventoryType`** baru (nama dari tanaman + varietas opsional, **`SKU`** tahun + nomor urut pad, **`category`** dari nama tipe tanaman, **`track_individual_lots`**, ambang stok rendah default, dll.).
5. **Unicitas production dalam satu tipe:** cek **`CertificationReport`** lain dengan **`inventory_type_id`** sama dan **`TRIM(COALESCE(planting_batch_number, harvest_batch_number))`** = production ID yang sama → tolak duplikat.
6. **`StockHistory::create`**: **`transaction_type`** **`mendaftarkan_stok`**, **`quantity`** dari **`certified_seed_quantity`**, **`description`** benih dari sertifikasi, **`new_data`** snapshot **`$report->toArray()`**, **`user_id`** pengguna login.
7. **`$report`**: set **`inventory_type_id`**, **`quantity_added_to_stock`**, **`save`**.
8. Redirect **`certifications.by-plant`** untuk **`$plant`** dengan pesan sukses.

---

## 7. Pranala dokumen

- Ringkas: **`DOKUMENTASI_BACKEND_FUNCTION_QUERY.md`**
- Per controller: **`DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART2_DETAIL_CONTROLLER.md`**
- Walkthrough batch 1: **`DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART3_WALKTHROUGH_METHOD.md`**
- Walkthrough batch 2: **`DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART4_WALKTHROUGH_METHOD.md`**
- Walkthrough batch 3 (file ini — export laporan & tambah stok): **`DOKUMENTASI_BACKEND_FUNCTION_QUERY_PART5_WALKTHROUGH_METHOD.md`**
