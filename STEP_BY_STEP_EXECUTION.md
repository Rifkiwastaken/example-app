# 🚀 Panduan Eksekusi Step-by-Step

## ⚠️ PENTING: Baca Ini Dulu!

Dokumen ini adalah panduan praktis untuk melakukan migrasi database dari BigInt ID ke Custom String ID. Ikuti langkah-langkah ini **SECARA BERURUTAN**.

---

## 📋 TAHAP 1: BACKUP DATABASE (WAJIB!)

### Langkah 1.1: Backup Manual dengan PHP Script

```bash
# Jalankan script backup yang sudah ada
php backup_database.php
```

**Atau gunakan PowerShell:**
```powershell
.\backup_database.ps1
```

**Verifikasi backup berhasil:**
```bash
# Cek file backup terbaru
dir database\backups
```

### Langkah 1.2: Catat Informasi Backup

- **File backup**: `database/backups/sibesti_backup_YYYYMMDD_HHMMSS.sql`
- **Tanggal backup**: _[Catat di sini]_
- **Ukuran file**: _[Catat di sini]_

✅ **CHECKPOINT**: Pastikan file backup ada dan ukurannya masuk akal (tidak 0 KB)

---

## 📋 TAHAP 2: EKSEKUSI FASE 1 - Tambah Kolom Baru

### Langkah 2.1: Jalankan Migrasi Fase 1

```bash
# Fase 1.1 - Core Tables
php artisan migrate --path=database/migrations/phase_1/2026_02_10_000001_phase1_add_custom_id_columns_core.php

# Fase 1.2 - Certification Tables
php artisan migrate --path=database/migrations/phase_1/2026_02_10_000002_phase1_add_custom_id_columns_certification.php

# Fase 1.3 - Inventory Tables
php artisan migrate --path=database/migrations/phase_1/2026_02_10_000003_phase1_add_custom_id_columns_inventory.php

# Fase 1.4 - Sales Tables
php artisan migrate --path=database/migrations/phase_1/2026_02_10_000004_phase1_add_custom_id_columns_sales.php

# Fase 1.5 - Support Tables
php artisan migrate --path=database/migrations/phase_1/2026_02_10_000005_phase1_add_custom_id_columns_support.php
```

### Langkah 2.2: Verifikasi Fase 1

**Buka MySQL/phpMyAdmin dan jalankan query ini:**

```sql
-- Cek tabel users
DESCRIBE users;
-- Harus ada kolom: user_id (varchar 36, nullable)

-- Cek tabel plants
DESCRIBE plants;
-- Harus ada kolom: plant_id, new_plant_type_id, new_planting_location_id

-- Cek tabel sales
DESCRIBE sales;
-- Harus ada kolom: sale_id, new_user_id
```

✅ **CHECKPOINT**: Semua kolom baru harus ada. Jika ada error, STOP dan perbaiki dulu!

**Jika ada error, rollback:**
```bash
php artisan migrate:rollback --step=5
```

---

## 📋 TAHAP 3: EKSEKUSI FASE 2 - Migrasi Data

### ⚠️ PERINGATAN FASE 2
- Fase ini akan memakan waktu 15-30 menit tergantung jumlah data
- Akan ada output progress di console
- JANGAN interrupt proses ini!

### Langkah 3.1: Jalankan Migrasi Data

```bash
# Fase 2.1 - Core Data Migration
php artisan migrate --path=database/migrations/phase_2/2026_02_10_100001_phase2_migrate_data_core.php

# Tunggu sampai selesai, akan ada output:
# Migrating users table...
# Migrating plant_types table...
# dst...

# Fase 2.2 - Remaining Data Migration
php artisan migrate --path=database/migrations/phase_2/2026_02_10_100002_phase2_migrate_data_remaining.php
```

### Langkah 3.2: Verifikasi Fase 2

**Query verifikasi:**

```sql
-- 1. Cek apakah custom ID sudah terisi
SELECT id, user_id FROM users LIMIT 10;
-- user_id harus terisi dengan format USR-XXXXXXXX

SELECT id, plant_id, new_plant_type_id FROM plants LIMIT 10;
-- plant_id harus terisi dengan format PLT-XXXXXXXX
-- new_plant_type_id harus terisi

-- 2. Cek apakah ada yang NULL (TIDAK BOLEH ADA!)
SELECT COUNT(*) as null_count FROM users WHERE user_id IS NULL;
-- Harus return 0

SELECT COUNT(*) as null_count FROM plants WHERE plant_id IS NULL;
-- Harus return 0

-- 3. Cek apakah FK sudah benar
SELECT 
    p.id,
    p.plant_id,
    p.plant_type_id as old_fk,
    p.new_plant_type_id as new_fk,
    pt.plant_type_id as actual_fk
FROM plants p
LEFT JOIN plant_types pt ON p.plant_type_id = pt.id
WHERE p.new_plant_type_id IS NOT NULL
LIMIT 10;
-- new_fk harus sama dengan actual_fk

-- 4. Cek apakah ada FK yang tidak match (TIDAK BOLEH ADA!)
SELECT COUNT(*) as orphan_count
FROM plants p
LEFT JOIN plant_types pt ON p.new_plant_type_id = pt.plant_type_id
WHERE p.new_plant_type_id IS NOT NULL 
AND pt.plant_type_id IS NULL;
-- Harus return 0
```

✅ **CHECKPOINT KRITIS**: 
- Semua custom ID harus terisi
- Tidak ada NULL values
- Semua FK harus match
- **JIKA ADA MASALAH, JANGAN LANJUT KE FASE 3!**

**Jika ada error, rollback:**
```bash
php artisan migrate:rollback --step=2
```

---

## 📋 TAHAP 4: EKSEKUSI FASE 3 - Finalisasi

### ⚠️ PERINGATAN FASE 3 - POINT OF NO RETURN!
- Setelah fase ini, rollback SANGAT SULIT
- Pastikan Fase 1 & 2 100% berhasil
- Pastikan backup masih ada
- **BACA ULANG VERIFIKASI FASE 2!**

### Langkah 4.1: Konfirmasi Terakhir

**Sebelum lanjut, jawab pertanyaan ini:**
- [ ] Apakah Fase 1 berhasil 100%?
- [ ] Apakah Fase 2 berhasil 100%?
- [ ] Apakah semua verifikasi sudah dilakukan?
- [ ] Apakah backup database masih ada?
- [ ] Apakah Anda siap untuk commit perubahan?

**Jika semua jawaban YA, lanjutkan. Jika ada yang TIDAK, STOP!**

### Langkah 4.2: Jalankan Finalisasi

```bash
# Fase 3.1 - Core Tables Finalization
php artisan migrate --path=database/migrations/phase_3/2026_02_10_200001_phase3_finalize_core.php

# Tunggu sampai selesai, akan ada output:
# Finalizing users table...
# Finalizing plant_types table...
# dst...

# Fase 3.2 - Remaining Tables Finalization
php artisan migrate --path=database/migrations/phase_3/2026_02_10_200002_phase3_finalize_remaining.php
```

### Langkah 4.3: Verifikasi Fase 3

**Query verifikasi:**

```sql
-- 1. Cek apakah kolom 'id' lama sudah dihapus
DESCRIBE users;
-- TIDAK BOLEH ada kolom 'id', hanya 'user_id'

DESCRIBE plants;
-- TIDAK BOLEH ada kolom 'id', hanya 'plant_id'
-- TIDAK BOLEH ada kolom 'new_plant_type_id', hanya 'plant_type_id'

-- 2. Cek Primary Key
SHOW KEYS FROM users WHERE Key_name = 'PRIMARY';
-- Column_name harus 'user_id'

SHOW KEYS FROM plants WHERE Key_name = 'PRIMARY';
-- Column_name harus 'plant_id'

-- 3. Cek Foreign Keys
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_SCHEMA = 'sibesti'
AND TABLE_NAME = 'plants';
-- plant_type_id harus reference ke plant_types.plant_type_id

-- 4. Test JOIN query
SELECT 
    p.plant_id,
    p.name,
    pt.plant_type_id,
    pt.name as type_name
FROM plants p
JOIN plant_types pt ON p.plant_type_id = pt.plant_type_id
LIMIT 10;
-- Harus berfungsi normal tanpa error
```

✅ **CHECKPOINT FINAL**: 
- Kolom lama sudah dihapus
- Custom ID sudah jadi Primary Key
- FK sudah menggunakan custom ID
- Query JOIN berfungsi normal

---

## 📋 TAHAP 5: UPDATE MODEL LARAVEL

Setelah database selesai, update semua Model Laravel. Lihat file `EXAMPLE_MODEL_UPDATE.md` untuk contoh lengkap.

### Template Update Model:

```php
<?php

namespace App\Models;

use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Model;

class YourModel extends Model
{
    use HasCustomId;

    protected $primaryKey = 'your_model_id';
    protected $keyType = 'string';
    public $incrementing = false;

    // ... rest of model
}
```

### Daftar Model yang Harus Diupdate:

**Core Models:**
- [ ] User.php
- [ ] PlantType.php
- [ ] Plant.php
- [ ] PlantingLocation.php
- [ ] Planting.php
- [ ] Harvest.php

**Certification Models:**
- [ ] Certification.php
- [ ] CertificationReport.php

**Inventory Models:**
- [ ] Bin.php
- [ ] InventoryType.php
- [ ] InventoryLot.php
- [ ] InventoryTransaction.php

**Sales Models:**
- [ ] Sale.php
- [ ] SaleItem.php

**Support Models:**
- [ ] Task.php
- [ ] Expense.php
- [ ] Attachment.php

---

## 📋 TAHAP 6: TESTING

### Test 1: CRUD Operations

```bash
php artisan tinker
```

```php
// Test Create
$plant = App\Models\Plant::create([
    'name' => 'Test Plant',
    'status' => 'perencanaan'
]);
echo $plant->plant_id; // Harus auto-generate: PLT-XXXXXXXX

// Test Read
$plant = App\Models\Plant::find('PLT-XXXXXXXX');
echo $plant->name; // Harus return 'Test Plant'

// Test Update
$plant->update(['name' => 'Updated Plant']);
echo $plant->name; // Harus 'Updated Plant'

// Test Delete
$plant->delete();
$check = App\Models\Plant::find('PLT-XXXXXXXX');
var_dump($check); // Harus null
```

### Test 2: Relationships

```php
// Test belongsTo
$plant = App\Models\Plant::first();
$plantType = $plant->type;
echo $plantType->name; // Harus berfungsi

// Test hasMany
$plantType = App\Models\PlantType::first();
$plants = $plantType->plants;
echo $plants->count(); // Harus berfungsi
```

### Test 3: Aplikasi Web

1. Buka aplikasi di browser
2. Test halaman-halaman utama
3. Test create data baru
4. Test edit data
5. Test delete data
6. Test search/filter
7. Cek tidak ada error di console browser

---

## 🎉 SELESAI!

Jika semua tahap berhasil, migrasi selesai! 

### Checklist Final:
- [ ] Database menggunakan custom string ID
- [ ] Semua model sudah diupdate
- [ ] CRUD berfungsi normal
- [ ] Relationships berfungsi normal
- [ ] Aplikasi bisa diakses normal
- [ ] Tidak ada error di log

### Backup & Dokumentasi:
- [ ] Simpan backup database lama
- [ ] Dokumentasikan perubahan
- [ ] Update ERD jika ada
- [ ] Commit ke git

---

## 🆘 Jika Ada Masalah

### Rollback Fase 1 atau 2:
```bash
php artisan migrate:rollback --step=X
# X = jumlah migration yang ingin di-rollback
```

### Restore dari Backup (Fase 3):
```bash
# Stop aplikasi
php artisan down

# Restore database
mysql -u username -p sibesti < database/backups/sibesti_backup_YYYYMMDD_HHMMSS.sql

# Revert code changes
git checkout app/Models/

# Start aplikasi
php artisan up
```

---

## 📞 Catatan Penting

- **Estimasi Total Waktu**: 60-120 menit
- **Downtime Required**: Ya, aplikasi harus offline
- **Backup Wajib**: Sebelum memulai
- **Testing Wajib**: Setelah selesai
- **Rollback Plan**: Siap jika gagal

**Good luck! 🚀**
