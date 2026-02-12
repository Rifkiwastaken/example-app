# Dokumentasi Operasi Database dalam Diagram BPMN

Dokumen ini menjelaskan detail operasi database yang terjadi pada setiap activity dalam diagram BPMN.

## Modul Penanaman (01_penanaman.bpmn)

### 1. Task_LoadDataTanaman
**Controller**: `PlantingController::create()`
**Operasi Database**:
```sql
SELECT * FROM plants ORDER BY name;
SELECT * FROM planting_locations ORDER BY name;
```
**Tujuan**: Load data untuk dropdown form

### 2. Task_ValidasiData
**Controller**: `PlantingController::store()`
**Operasi Database**:
```sql
SELECT id FROM plants WHERE id = ?;  -- Validasi plant_id exists
SELECT id FROM planting_locations WHERE id = ?;  -- Validasi planting_location_id exists
```
**Tujuan**: Validasi foreign key sebelum insert

### 3. Task_SimpanDataPenanaman
**Model**: `Planting::create()`
**Operasi Database**:
```sql
INSERT INTO plantings (
    plant_id, planting_location_id, bed_label, days_to_emerge,
    spacing_between_plants, spacing_between_rows, sowing_depth,
    avg_height, start_method, germination_stage, seeds_per_hole,
    light_profile, soil_condition, planting_detail, pruning_detail,
    perennial, days_to_flower, days_to_harvest, harvest_window_days,
    expected_loss_rate, harvest_unit, expected_yield_per_hectare,
    quantity_planted, planted_at, estimated_harvest_date, area_ha,
    planting_format, planting_format_custom, is_completed
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
```
**Return**: `planting_id` (auto-increment)

### 4. Task_QueryPlant
**Model**: `Plant::find(plant_id)`
**Operasi Database**:
```sql
SELECT * FROM plants WHERE id = ?;
```
**Tujuan**: Get plant data untuk redirect

### 5. Task_UpdateTahapGerminasi
**Model**: `Planting::update()`
**Operasi Database**:
```sql
UPDATE plantings 
SET germination_stage = ?, updated_at = NOW() 
WHERE id = ?;
```
**Tujuan**: Update tahap perkembangan tanaman

### 6. Task_SimpanPanen
**Model**: `Harvest::create()`
**Operasi Database**:
```sql
INSERT INTO harvests (
    plant_id, planting_id, planting_location_id, harvested_at,
    batch_no, note, source, quality, quantity, unit,
    loss_quantity, harvest_unit, unit_quantity, quantity_per_unit,
    recorded_by
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
```
**Return**: `harvest_id` (auto-increment)

### 7. Task_UpdatePlantingStatus
**Model**: `Planting::update()`
**Operasi Database**:
```sql
UPDATE plantings 
SET is_completed = true, updated_at = NOW() 
WHERE id = ?;
```
**Tujuan**: Mark planting sebagai completed setelah panen

---

## Modul Sertifikasi (02_sertifikasi.bpmn)

### 1. Task_LoadHarvestData
**Controller**: `CertificationController::create()`
**Operasi Database**:
```sql
SELECT h.*, p.name as plant_name, pl.name as location_name
FROM harvests h
LEFT JOIN plants p ON h.plant_id = p.id
LEFT JOIN planting_locations pl ON h.planting_location_id = pl.id
WHERE h.id = ?;
```
**Tujuan**: Load harvest data dengan relations

### 2. Task_BuatSertifikasi
**Model**: `Certification::firstOrCreate()`
**Operasi Database**:
```sql
-- Check existing
SELECT * FROM certifications WHERE harvest_id = ?;

-- If not exists, INSERT
INSERT INTO certifications (
    harvest_id, planting_location_id, plant_id,
    certification_status, seed_class_requested
) VALUES (?, ?, ?, 'dalam_proses', ?);
```
**Return**: `certification_id`

### 3. Task_ValidasiLaporan
**Controller**: `CertificationController::storeReport()`
**Operasi Database**:
```sql
-- Validasi report_number_bpsb unique
SELECT id FROM certification_reports WHERE report_number_bpsb = ?;
```
**Tujuan**: Validasi nomor laporan unik

### 4. Task_SimpanLaporan
**Model**: `CertificationReport::create()`
**Operasi Database**:
```sql
INSERT INTO certification_reports (
    certification_id, report_type, report_number_bpsb, report_date,
    growing_season, inspection_phase, inspector_name, reporter_name,
    seed_class_result, isolation_north, isolation_east, isolation_south,
    isolation_west, plant_characteristics_match, pest_disease_condition,
    weed_condition, population_per_sample, other_variety_mix_count,
    other_variety_mix_percentage, estimated_yield, expiry_date,
    certified_seed_quantity, certified_seed_unit, seed_unit,
    estimated_sale_price_per_kg, conclusion, scan_file_path
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
```
**Return**: `report_id`

### 5. Task_UpdateStatusLulus
**Model**: `Certification::update()`
**Operasi Database**:
```sql
UPDATE certifications 
SET certification_status = 'lulus', updated_at = NOW() 
WHERE id = ?;
```
**Tujuan**: Update status sertifikasi menjadi lulus

### 6. Task_UpdateStatusTidakLulus
**Model**: `Certification::update()`
**Operasi Database**:
```sql
UPDATE certifications 
SET certification_status = 'tidak_lulus', updated_at = NOW() 
WHERE id = ?;
```
**Tujuan**: Update status sertifikasi menjadi tidak lulus

### 7. Task_TambahKeStok
**Model**: `InventoryTypeSeed::create()`
**Operasi Database**:
```sql
INSERT INTO inventory_type_seeds (
    inventory_type_id, plant_id, planting_location_id,
    quantity, seed_unit, seed_unit_quantity, seed_per_unit,
    seed_per_unit_unit, total_seed_quantity, total_seed_unit,
    estimated_sale_price_per_kg, expiry_date, filled_by_user_id
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
```
**Return**: `seed_id`

**Pivot Table**:
```sql
INSERT INTO certification_report_inventory_type (
    certification_report_id, inventory_type_id, quantity
) VALUES (?, ?, ?);
```
**Tujuan**: Link certification report dengan inventory type

---

## Modul Manajemen Stok dan Gudang (03_manajemen_stok_gudang.bpmn)

### 1. Task_BuatGudang
**Model**: `Warehouse::create()`
**Operasi Database**:
```sql
INSERT INTO warehouses (
    name, internal_id, tracking_type, description, responsible_person_id
) VALUES (?, ?, ?, ?, ?);
```
**Return**: `warehouse_id`

### 2. Task_BuatBin
**Model**: `Bin::create()`
**Operasi Database**:
```sql
-- Check internal_id unique
SELECT id FROM bins WHERE warehouse_id = ? AND internal_id = ?;

-- If unique, INSERT
INSERT INTO bins (
    warehouse_id, name, internal_id, max_capacity, capacity_unit, description
) VALUES (?, ?, ?, ?, ?, ?);
```
**Return**: `bin_id`

### 3. Task_SimpanInventoryType
**Model**: `InventoryType::create()`
**Operasi Database**:
```sql
INSERT INTO inventory_types (
    plant_id, name, category, sku, electronic_id, unit,
    estimated_value_per_unit, estimated_kg_per_unit, track_individual_lots,
    low_stock_threshold, low_stock_unit, low_stock_email, description
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
```
**Return**: `inventory_type_id`

**Pivot Table**:
```sql
INSERT INTO inventory_type_warehouse (
    inventory_type_id, warehouse_id, bin_id, warehouse_only
) VALUES (?, ?, ?, ?);
```
**Tujuan**: Link inventory type dengan warehouse dan bin

### 4. Task_SimpanLot
**Model**: `InventoryLot::create()`
**Operasi Database**:
```sql
INSERT INTO inventory_lots (
    inventory_type_id, production_id, initial_stock, current_stock,
    stock_unit, warehouse_id, bin_id, expiry_date, status
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'tersedia');
```
**Return**: `lot_id`

### 5. Task_BuatTransaksiMasuk
**Model**: `InventoryTransaction::create()`
**Operasi Database**:
```sql
INSERT INTO inventory_transactions (
    inventory_type_id, inventory_lot_id, transaction_type,
    quantity, unit, warehouse_id, bin_id, reason, notes, user_id
) VALUES (?, ?, 'stok_masuk', ?, ?, ?, ?, ?, ?, ?);
```
**Return**: `transaction_id`

### 6. Task_UpdateStatusLot
**Model**: `InventoryLot::updateStatus()`
**Operasi Database**:
```sql
-- Check expiry and stock
SELECT expiry_date, current_stock FROM inventory_lots WHERE id = ?;

-- Update status based on condition
UPDATE inventory_lots 
SET status = CASE
    WHEN current_stock <= 0 THEN 'habis'
    WHEN expiry_date < CURDATE() THEN 'kadaluarsa'
    ELSE 'tersedia'
END,
updated_at = NOW()
WHERE id = ?;
```

### 7. Task_KurangiStok
**Model**: `InventoryLot::update()`
**Operasi Database**:
```sql
UPDATE inventory_lots 
SET current_stock = current_stock - ?, updated_at = NOW() 
WHERE id = ?;
```
**Tujuan**: Kurangi stok dari lot

### 8. Task_BuatTransaksiKeluar
**Model**: `InventoryTransaction::create()`
**Operasi Database**:
```sql
INSERT INTO inventory_transactions (
    inventory_type_id, inventory_lot_id, transaction_type,
    quantity, unit, warehouse_id, bin_id, reason, notes, user_id
) VALUES (?, ?, 'stok_keluar', ?, ?, ?, ?, ?, ?, ?);
```

### 9. Task_UpdateStok (Penyesuaian)
**Model**: `InventoryLot::update()`
**Operasi Database**:
```sql
-- Untuk penyesuaian tambah
UPDATE inventory_lots 
SET current_stock = current_stock + ?, updated_at = NOW() 
WHERE id = ?;

-- Untuk penyesuaian kurang
UPDATE inventory_lots 
SET current_stock = current_stock - ?, updated_at = NOW() 
WHERE id = ?;
```

### 10. Task_BuatTransaksiPenyesuaian
**Model**: `InventoryTransaction::create()`
**Operasi Database**:
```sql
INSERT INTO inventory_transactions (
    inventory_type_id, inventory_lot_id, transaction_type,
    quantity, unit, warehouse_id, bin_id, reason, notes, user_id
) VALUES (?, ?, 'penyesuaian_tambah' atau 'penyesuaian_kurang', ?, ?, ?, ?, ?, ?, ?, ?);
```

### 11. Task_UpdateLokasi
**Model**: `InventoryLot::update()`
**Operasi Database**:
```sql
UPDATE inventory_lots 
SET warehouse_id = ?, bin_id = ?, updated_at = NOW() 
WHERE id = ?;
```
**Tujuan**: Pindah lot ke lokasi lain

### 12. Task_BuatTransaksiPindah
**Model**: `InventoryTransaction::create()`
**Operasi Database**:
```sql
INSERT INTO inventory_transactions (
    inventory_type_id, inventory_lot_id, transaction_type,
    quantity, unit, warehouse_id, bin_id, reason, notes, user_id
) VALUES (?, ?, 'pindah_lokasi', ?, ?, ?, ?, ?, ?, ?);
```

### 13. Task_Monitoring
**Model**: `InventoryType::lots()->sum()`
**Operasi Database**:
```sql
SELECT 
    inventory_type_id,
    SUM(current_stock) as total_stock,
    COUNT(*) as total_lots
FROM inventory_lots
WHERE inventory_type_id = ?
GROUP BY inventory_type_id;
```
**Tujuan**: Monitoring total stok per inventory type

---

## Modul Penjualan (04_penjualan.bpmn)

### 1. Task_GenerateNomorStruk
**Model**: `Sale::generateReceiptNumber()`
**Operasi Database**:
```sql
SELECT COUNT(*) as count 
FROM sales 
WHERE receipt_number LIKE CONCAT('PJ-', YEAR(NOW()), '-%');
```
**Logic**: Format nomor: `PJ-YYYY-XXX` (XXX = count + 1, zero-padded)

### 2. Task_CekStokTersedia
**Model**: `InventoryLot::where()->get()`
**Operasi Database**:
```sql
SELECT 
    il.*, it.name as inventory_type_name
FROM inventory_lots il
LEFT JOIN inventory_types it ON il.inventory_type_id = it.id
WHERE il.bin_id = ? 
AND il.current_stock > 0
ORDER BY il.created_at ASC;
```
**Tujuan**: Get lots dengan stok tersedia (FIFO order)

### 3. Task_BuatSale
**Model**: `Sale::create()`
**Operasi Database**:
```sql
BEGIN TRANSACTION;

INSERT INTO sales (
    receipt_number, sale_date, buyer_name, buyer_contact,
    planting_location_id, total_amount, payment_method,
    payment_status, notes, user_id
) VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?, ?);
```
**Return**: `sale_id`

### 4. Task_ProsesFIFO
**Model**: `InventoryLot::where()->orderBy()->get()`
**Operasi Database**:
```sql
SELECT * FROM inventory_lots
WHERE bin_id = ?
AND current_stock > 0
ORDER BY created_at ASC
LIMIT ?;
```
**Tujuan**: Get lots tertua terlebih dahulu (FIFO)

### 5. Task_CekKecukupanStok
**Model**: `InventoryLot::sum('current_stock')`
**Operasi Database**:
```sql
SELECT SUM(current_stock) as total_available
FROM inventory_lots
WHERE bin_id = ?
AND current_stock > 0;
```
**Tujuan**: Check total stok tersedia di bin

### 6. Task_KurangiStokLot
**Model**: `InventoryLot::update()`
**Operasi Database**:
```sql
UPDATE inventory_lots 
SET current_stock = current_stock - ?, updated_at = NOW() 
WHERE id = ?;
```
**Tujuan**: Kurangi stok dari lot (FIFO)

### 7. Task_BuatSaleItem
**Model**: `SaleItem::create()`
**Operasi Database**:
```sql
INSERT INTO sale_items (
    sale_id, inventory_type_id, inventory_lot_id,
    quantity, unit, unit_price, subtotal
) VALUES (?, ?, ?, ?, ?, ?, ?);
```
**Return**: `sale_item_id`

### 8. Task_BuatTransaksiInventory
**Model**: `InventoryTransaction::create()`
**Operasi Database**:
```sql
INSERT INTO inventory_transactions (
    inventory_type_id, inventory_lot_id, transaction_type,
    quantity, unit, warehouse_id, bin_id, reason, notes, user_id
) VALUES (?, ?, 'distribusi', ?, ?, ?, ?, 'Penjualan', ?, ?);
```
**Tujuan**: Record transaksi stok keluar untuk penjualan

### 9. Task_HitungTotal
**Model**: `SaleItem::sum('subtotal')`
**Operasi Database**:
```sql
SELECT SUM(subtotal) as total_amount
FROM sale_items
WHERE sale_id = ?;
```

### 10. Task_SimpanPenjualan
**Model**: `Sale::update()`
**Operasi Database**:
```sql
UPDATE sales 
SET total_amount = ?, updated_at = NOW() 
WHERE id = ?;

COMMIT TRANSACTION;
```
**Tujuan**: Update total amount dan commit transaction

---

## Catatan Penting

1. **Transaction Management**: Operasi yang melibatkan multiple database operations menggunakan `BEGIN TRANSACTION` dan `COMMIT TRANSACTION` untuk menjaga konsistensi data.

2. **FIFO Implementation**: Sistem menggunakan `ORDER BY created_at ASC` untuk implementasi First In First Out.

3. **Foreign Key Validation**: Sebelum insert, sistem selalu validasi foreign key exists untuk menjaga referential integrity.

4. **Auto Timestamp**: Kolom `created_at` dan `updated_at` di-update otomatis oleh Laravel Eloquent.

5. **Status Updates**: Status lot otomatis di-update berdasarkan kondisi (tersedia, habis, kadaluarsa).
















