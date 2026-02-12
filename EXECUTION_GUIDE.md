# Panduan Eksekusi Migrasi Custom ID

## ⚠️ PERINGATAN PENTING

**BACA SELURUH DOKUMEN INI SEBELUM MEMULAI!**

Migrasi ini adalah operasi yang SANGAT BERISIKO dan TIDAK DAPAT DIBATALKAN dengan mudah. Pastikan Anda:

1. ✅ Sudah membuat BACKUP DATABASE yang lengkap
2. ✅ Sudah testing di environment DEVELOPMENT terlebih dahulu
3. ✅ Sudah memahami setiap langkah yang akan dilakukan
4. ✅ Punya waktu maintenance yang cukup (estimasi: 30-60 menit)
5. ✅ Sudah memberitahu semua user bahwa sistem akan down

## 📋 Checklist Persiapan

### 1. Backup Database

```bash
# Menggunakan script backup yang sudah ada
php artisan db:backup

# Atau manual dengan mysqldump
mysqldump -u username -p sibesti > backup_before_migration_$(date +%Y%m%d_%H%M%S).sql

# Verifikasi backup berhasil
ls -lh backup_before_migration_*.sql
```

### 2. Testing di Development Environment

```bash
# Clone database production ke development
# Jalankan migrasi di development terlebih dahulu
# Pastikan tidak ada error
```

### 3. Matikan Aplikasi

```bash
# Aktifkan maintenance mode
php artisan down --message="Database migration in progress" --retry=60

# Atau edit .env untuk disable aplikasi
# APP_DEBUG=false
# APP_ENV=maintenance
```

### 4. Pastikan Tidak Ada Proses yang Berjalan

```bash
# Cek active connections ke database
# MySQL:
SHOW PROCESSLIST;

# Kill semua connection jika perlu
```

## 🚀 Langkah Eksekusi

### FASE 1: Tambah Kolom Baru (Estimasi: 5-10 menit)

```bash
# Jalankan semua migrasi fase 1
php artisan migrate --path=database/migrations/phase_1/2026_02_10_000001_phase1_add_custom_id_columns_core.php
php artisan migrate --path=database/migrations/phase_1/2026_02_10_000002_phase1_add_custom_id_columns_certification.php
php artisan migrate --path=database/migrations/phase_1/2026_02_10_000003_phase1_add_custom_id_columns_inventory.php
php artisan migrate --path=database/migrations/phase_1/2026_02_10_000004_phase1_add_custom_id_columns_sales.php
php artisan migrate --path=database/migrations/phase_1/2026_02_10_000005_phase1_add_custom_id_columns_support.php
```

**Verifikasi Fase 1:**

```sql
-- Cek apakah kolom baru sudah ditambahkan
DESCRIBE plants;
-- Harus ada kolom: plant_id, new_plant_type_id, new_planting_location_id

DESCRIBE users;
-- Harus ada kolom: user_id

-- Cek semua tabel lain juga
```

**Jika ada error di Fase 1:**
```bash
# Rollback fase 1
php artisan migrate:rollback --path=database/migrations/phase_1

# Perbaiki error
# Ulangi fase 1
```

### FASE 2: Migrasi Data (Estimasi: 15-30 menit tergantung jumlah data)

**PENTING:** Fase ini akan memakan waktu lama jika data banyak. Pantau progress!

```bash
# Jalankan migrasi data
php artisan migrate --path=database/migrations/phase_2/2026_02_10_100001_phase2_migrate_data_core.php

# Tunggu sampai selesai, akan ada output progress
# Migrating users table...
# Migrating plant_types table...
# dst...

# Lanjutkan dengan tabel lainnya
php artisan migrate --path=database/migrations/phase_2/2026_02_10_100002_phase2_migrate_data_remaining.php
```

**Verifikasi Fase 2:**

```sql
-- Cek apakah custom ID sudah terisi
SELECT id, user_id FROM users LIMIT 10;
-- user_id harus terisi dengan format USR-XXXXXXXX

SELECT id, plant_id, new_plant_type_id FROM plants LIMIT 10;
-- plant_id harus terisi dengan format PLT-XXXXXXXX
-- new_plant_type_id harus terisi dengan plant_type_id yang sesuai

-- Cek apakah FK sudah terupdate dengan benar
SELECT 
    p.id,
    p.plant_id,
    p.plant_type_id as old_plant_type_id,
    p.new_plant_type_id,
    pt.plant_type_id as actual_plant_type_id
FROM plants p
LEFT JOIN plant_types pt ON p.plant_type_id = pt.id
LIMIT 10;
-- new_plant_type_id harus sama dengan actual_plant_type_id

-- Cek apakah ada data yang NULL (tidak boleh ada)
SELECT COUNT(*) FROM users WHERE user_id IS NULL;
-- Harus return 0

SELECT COUNT(*) FROM plants WHERE plant_id IS NULL;
-- Harus return 0

-- Cek apakah ada FK yang tidak match
SELECT COUNT(*) 
FROM plants p
LEFT JOIN plant_types pt ON p.new_plant_type_id = pt.plant_type_id
WHERE p.new_plant_type_id IS NOT NULL 
AND pt.plant_type_id IS NULL;
-- Harus return 0
```

**Jika ada error di Fase 2:**
```bash
# JANGAN LANJUT KE FASE 3!
# Rollback fase 2
php artisan migrate:rollback --path=database/migrations/phase_2

# Analisis error
# Perbaiki data yang bermasalah
# Ulangi fase 2
```

### FASE 3: Finalisasi (Estimasi: 10-15 menit)

**⚠️ TITIK TIDAK KEMBALI! Setelah fase ini, rollback sangat sulit!**

**Pastikan:**
- ✅ Fase 1 dan 2 berhasil 100%
- ✅ Semua verifikasi sudah dilakukan
- ✅ Backup masih tersedia
- ✅ Anda siap untuk commit perubahan

```bash
# Jalankan finalisasi
php artisan migrate --path=database/migrations/phase_3/2026_02_10_200001_phase3_finalize_core.php

# Tunggu sampai selesai
# Finalizing users table...
# Finalizing plant_types table...
# dst...

# Lanjutkan dengan tabel lainnya
php artisan migrate --path=database/migrations/phase_3/2026_02_10_200002_phase3_finalize_remaining.php
```

**Verifikasi Fase 3:**

```sql
-- Cek apakah kolom 'id' sudah dihapus
DESCRIBE users;
-- Tidak boleh ada kolom 'id', hanya 'user_id' sebagai PK

DESCRIBE plants;
-- Tidak boleh ada kolom 'id', hanya 'plant_id' sebagai PK
-- Tidak boleh ada kolom 'new_plant_type_id', hanya 'plant_type_id'

-- Cek Primary Key
SHOW KEYS FROM users WHERE Key_name = 'PRIMARY';
-- Column_name harus 'user_id'

SHOW KEYS FROM plants WHERE Key_name = 'PRIMARY';
-- Column_name harus 'plant_id'

-- Cek Foreign Keys
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
    REFERENCED_TABLE_SCHEMA = 'sibesti'
    AND TABLE_NAME = 'plants';
-- plant_type_id harus reference ke plant_types.plant_type_id
-- planting_location_id harus reference ke planting_locations.planting_location_id

-- Test query dengan JOIN
SELECT 
    p.plant_id,
    p.name,
    pt.plant_type_id,
    pt.name as type_name
FROM plants p
JOIN plant_types pt ON p.plant_type_id = pt.plant_type_id
LIMIT 10;
-- Harus berfungsi normal
```

## 📝 Update Model Laravel

Setelah migrasi database selesai, update semua Model:

```bash
# Lihat file EXAMPLE_MODEL_UPDATE.md untuk contoh
# Update semua model satu per satu
```

**Daftar Model yang Harus Diupdate:**
- [ ] app/Models/User.php
- [ ] app/Models/PlantType.php
- [ ] app/Models/Plant.php
- [ ] app/Models/PlantingLocation.php
- [ ] app/Models/Planting.php
- [ ] app/Models/Harvest.php
- [ ] app/Models/Certification.php
- [ ] app/Models/CertificationReport.php
- [ ] app/Models/Warehouse.php (jika ada)
- [ ] app/Models/Bin.php
- [ ] app/Models/InventoryType.php
- [ ] app/Models/InventoryLot.php
- [ ] app/Models/InventoryTransaction.php
- [ ] app/Models/Sale.php
- [ ] app/Models/SaleItem.php
- [ ] app/Models/Task.php
- [ ] app/Models/Expense.php
- [ ] Dan semua model lainnya...

## 🧪 Testing Aplikasi

```bash
# Aktifkan aplikasi kembali
php artisan up

# Test basic operations
php artisan tinker

# Test create
>>> $plant = App\Models\Plant::create(['name' => 'Test Plant', 'status' => 'perencanaan']);
>>> $plant->plant_id; // Harus auto-generate: PLT-XXXXXXXX

# Test read
>>> $plant = App\Models\Plant::find('PLT-XXXXXXXX');
>>> $plant->name; // Harus return 'Test Plant'

# Test relationships
>>> $plant->type; // Harus berfungsi
>>> $plant->plantings; // Harus berfungsi

# Test update
>>> $plant->update(['name' => 'Updated Plant']);
>>> $plant->name; // Harus 'Updated Plant'

# Test delete
>>> $plant->delete();
>>> App\Models\Plant::find('PLT-XXXXXXXX'); // Harus null
```

## 🔍 Monitoring & Troubleshooting

### Cek Log Error

```bash
tail -f storage/logs/laravel.log
```

### Common Issues

**Issue 1: Foreign Key Constraint Error**
```
SQLSTATE[23000]: Integrity constraint violation
```
**Solusi:** Ada data yang tidak konsisten. Cek dengan query verifikasi di atas.

**Issue 2: Duplicate Entry**
```
SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry
```
**Solusi:** Ada collision di custom ID generation. Sangat jarang terjadi, tapi bisa di-handle dengan re-run fase 2.

**Issue 3: Column Not Found**
```
SQLSTATE[42S22]: Column not found
```
**Solusi:** Model belum diupdate atau ada typo di nama kolom.

## 🔙 Rollback (Jika Diperlukan)

**Sebelum Fase 3:**
```bash
# Rollback fase 2
php artisan migrate:rollback --path=database/migrations/phase_2

# Rollback fase 1
php artisan migrate:rollback --path=database/migrations/phase_1

# Restore dari backup
mysql -u username -p sibesti < backup_before_migration_YYYYMMDD_HHMMSS.sql
```

**Setelah Fase 3:**
```bash
# Fase 3 tidak bisa di-rollback!
# Harus restore dari backup

# Stop aplikasi
php artisan down

# Restore database
mysql -u username -p sibesti < backup_before_migration_YYYYMMDD_HHMMSS.sql

# Revert semua perubahan di Model
git checkout app/Models/

# Start aplikasi
php artisan up
```

## ✅ Checklist Setelah Migrasi

- [ ] Semua tabel menggunakan custom ID sebagai PK
- [ ] Semua FK sudah terupdate
- [ ] Semua Model sudah diupdate
- [ ] Testing CRUD berfungsi normal
- [ ] Testing relationships berfungsi normal
- [ ] Aplikasi bisa diakses user
- [ ] Tidak ada error di log
- [ ] Performance masih normal
- [ ] Backup lama sudah disimpan dengan aman

## 📞 Support

Jika mengalami masalah:
1. JANGAN PANIK
2. Catat error message lengkap
3. Cek log: `storage/logs/laravel.log`
4. Restore dari backup jika perlu
5. Analisis masalah sebelum retry

## 📚 Referensi

- MIGRATION_STRATEGY_CUSTOM_IDS.md - Strategi lengkap
- EXAMPLE_MODEL_UPDATE.md - Contoh update model
- app/Traits/HasCustomId.php - Trait untuk auto-generate ID
