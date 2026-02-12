# Quick Reference: Custom ID Migration

## 🎯 Tujuan
Mengubah semua Primary Key dari `id` (BigInt) menjadi `[table_singular]_id` (VARCHAR dengan format PREFIX-RANDOM)

## 📋 Format Custom ID
```
PREFIX-XXXXXXXX
```
- **PREFIX**: 3 huruf uppercase (contoh: PLT, USR, SAL)
- **RANDOM**: 8 karakter alfanumerik uppercase
- **Total**: 12 karakter (termasuk dash)
- **Contoh**: `PLT-8X92MKA1`, `USR-A1B2C3D4`, `SAL-9Z8Y7X6W`

## 🗂️ Struktur File

```
app/Traits/HasCustomId.php                          # Trait untuk auto-generate ID

database/migrations/phase_1/                         # Tambah kolom baru
├── 2026_02_10_000001_phase1_add_custom_id_columns_core.php
├── 2026_02_10_000002_phase1_add_custom_id_columns_certification.php
├── 2026_02_10_000003_phase1_add_custom_id_columns_inventory.php
├── 2026_02_10_000004_phase1_add_custom_id_columns_sales.php
└── 2026_02_10_000005_phase1_add_custom_id_columns_support.php

database/migrations/phase_2/                         # Migrasi data
├── 2026_02_10_100001_phase2_migrate_data_core.php
└── 2026_02_10_100002_phase2_migrate_data_remaining.php

database/migrations/phase_3/                         # Finalisasi
├── 2026_02_10_200001_phase3_finalize_core.php
└── 2026_02_10_200002_phase3_finalize_remaining.php
```

## 🚀 Command Eksekusi

### Fase 1: Tambah Kolom
```bash
php artisan migrate --path=database/migrations/phase_1/2026_02_10_000001_phase1_add_custom_id_columns_core.php
php artisan migrate --path=database/migrations/phase_1/2026_02_10_000002_phase1_add_custom_id_columns_certification.php
php artisan migrate --path=database/migrations/phase_1/2026_02_10_000003_phase1_add_custom_id_columns_inventory.php
php artisan migrate --path=database/migrations/phase_1/2026_02_10_000004_phase1_add_custom_id_columns_sales.php
php artisan migrate --path=database/migrations/phase_1/2026_02_10_000005_phase1_add_custom_id_columns_support.php
```

### Fase 2: Migrasi Data
```bash
php artisan migrate --path=database/migrations/phase_2/2026_02_10_100001_phase2_migrate_data_core.php
php artisan migrate --path=database/migrations/phase_2/2026_02_10_100002_phase2_migrate_data_remaining.php
```

### Fase 3: Finalisasi
```bash
php artisan migrate --path=database/migrations/phase_3/2026_02_10_200001_phase3_finalize_core.php
php artisan migrate --path=database/migrations/phase_3/2026_02_10_200002_phase3_finalize_remaining.php
```

## 📝 Update Model Template

```php
<?php

namespace App\Models;

use App\Traits\HasCustomId;
use Illuminate\Database\Eloquent\Model;

class YourModel extends Model
{
    use HasCustomId;

    // Set primary key
    protected $primaryKey = 'your_model_id';
    
    // Set key type
    protected $keyType = 'string';
    
    // Disable auto-increment
    public $incrementing = false;

    protected $fillable = [
        // your fields
    ];

    // Update relationships dengan explicit keys
    public function parent()
    {
        return $this->belongsTo(Parent::class, 'parent_id', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Child::class, 'your_model_id', 'your_model_id');
    }
}
```

## 🔍 Query Verifikasi Cepat

### Cek Kolom Baru (Setelah Fase 1)
```sql
DESCRIBE plants;
-- Harus ada: plant_id, new_plant_type_id, new_planting_location_id
```

### Cek Data Terisi (Setelah Fase 2)
```sql
SELECT id, plant_id, new_plant_type_id FROM plants LIMIT 10;
-- Semua custom ID harus terisi
```

### Cek Primary Key (Setelah Fase 3)
```sql
SHOW KEYS FROM plants WHERE Key_name = 'PRIMARY';
-- Column_name harus 'plant_id'
```

### Cek Foreign Keys (Setelah Fase 3)
```sql
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_SCHEMA = 'sibesti'
AND TABLE_NAME = 'plants';
```

## 🎨 Prefix Mapping

| Tabel | Prefix | Contoh ID |
|-------|--------|-----------|
| users | USR | USR-A1B2C3D4 |
| plant_types | PTY | PTY-X9Y8Z7W6 |
| plants | PLT | PLT-8X92MKA1 |
| planting_locations | LOC | LOC-M5N6O7P8 |
| plantings | PLN | PLN-Q1R2S3T4 |
| harvests | HRV | HRV-U5V6W7X8 |
| certifications | CRT | CRT-Y9Z0A1B2 |
| certification_reports | CRP | CRP-C3D4E5F6 |
| warehouses | WHS | WHS-G7H8I9J0 |
| bins | BIN | BIN-K1L2M3N4 |
| inventory_types | INV | INV-O5P6Q7R8 |
| inventory_lots | LOT | LOT-S9T0U1V2 |
| inventory_transactions | TRX | TRX-W3X4Y5Z6 |
| sales | SAL | SAL-A7B8C9D0 |
| sale_items | SIT | SIT-E1F2G3H4 |
| tasks | TSK | TSK-I5J6K7L8 |
| expenses | EXP | EXP-M9N0O1P2 |

## ⚠️ Critical Checkpoints

### Before Fase 1
- ✅ Database backup created
- ✅ Tested in development
- ✅ Maintenance mode activated

### Before Fase 2
- ✅ Fase 1 completed successfully
- ✅ All new columns exist
- ✅ No errors in log

### Before Fase 3
- ✅ Fase 2 completed successfully
- ✅ All custom IDs generated
- ✅ All FKs updated correctly
- ✅ No NULL values in custom ID columns
- ✅ **POINT OF NO RETURN - VERIFY EVERYTHING!**

### After Fase 3
- ✅ Old 'id' columns removed
- ✅ Custom ID columns are now PKs
- ✅ All FKs reference custom IDs
- ✅ All models updated
- ✅ CRUD operations work
- ✅ Relationships work

## 🔧 Common Commands

### Backup Database
```bash
php artisan db:backup
# atau
mysqldump -u username -p sibesti > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Maintenance Mode
```bash
# Enable
php artisan down --message="Database migration in progress" --retry=60

# Disable
php artisan up
```

### Testing in Tinker
```bash
php artisan tinker

# Test create
>>> $plant = App\Models\Plant::create(['name' => 'Test', 'status' => 'perencanaan']);
>>> $plant->plant_id; // Should be PLT-XXXXXXXX

# Test find
>>> $plant = App\Models\Plant::find('PLT-XXXXXXXX');

# Test relationship
>>> $plant->type;
>>> $plant->plantings;
```

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

## 🆘 Emergency Rollback

### Before Fase 3
```bash
php artisan migrate:rollback --path=database/migrations/phase_2
php artisan migrate:rollback --path=database/migrations/phase_1
```

### After Fase 3
```bash
# Restore from backup
mysql -u username -p sibesti < backup_YYYYMMDD_HHMMSS.sql

# Revert code changes
git checkout app/Models/
```

## 📚 Documentation Files

1. **MIGRATION_STRATEGY_CUSTOM_IDS.md** - Strategi lengkap & overview
2. **EXECUTION_GUIDE.md** - Panduan eksekusi step-by-step
3. **EXAMPLE_MODEL_UPDATE.md** - Contoh update model
4. **TODO.md** - Checklist lengkap
5. **QUICK_REFERENCE.md** - Referensi cepat (file ini)

## 💡 Tips

1. **Selalu backup** sebelum memulai
2. **Test di development** terlebih dahulu
3. **Jalankan fase secara berurutan** (1 → 2 → 3)
4. **Verifikasi setiap fase** sebelum lanjut
5. **Jangan skip verifikasi** di Fase 2
6. **Monitor log** selama dan setelah migrasi
7. **Simpan backup** minimal 1 bulan

## 🎯 Success Criteria

- ✅ Semua tabel menggunakan custom string ID
- ✅ Format ID: PREFIX-XXXXXXXX
- ✅ Semua FK terupdate
- ✅ Semua relationship berfungsi
- ✅ CRUD operations normal
- ✅ No errors in production
- ✅ Performance tetap baik
