# 🎯 Panduan Lengkap Migrasi Custom ID - SIBESTI

## ✅ Status Persiapan: SELESAI

Semua file dan analisis untuk migrasi database dari BigInt ID ke Custom String ID telah berhasil dibuat.

---

## 📊 Ringkasan Analisis Database

### Database Information
- **Nama Database:** sibit
- **Total Tabel:** 36 tabel
- **Total Foreign Keys:** 100+ relationships
- **Backup Tersedia:** ✅ `database/backups/sibesti_backup_before_migration_20260205_033351.sql`

### Tabel yang Akan Dimigrasi (36 Tabel)

#### Level 0 - Foundation Tables (5 tabel)
1. **users** → `user_id` (USR-XXXXXXXX)
2. **plant_types** → `plant_type_id` (PTY-XXXXXXXX)
3. **warehouses** → `warehouse_id` (WHS-XXXXXXXX)
4. **task_templates** → `task_template_id` (TTP-XXXXXXXX)
5. **landing_page_settings** → `landing_page_setting_id` (LPS-XXXXXXXX)

#### Level 1 - Core Tables (4 tabel)
6. **plants** → `plant_id` (PLT-XXXXXXXX)
7. **planting_locations** → `planting_location_id` (LOC-XXXXXXXX)
8. **bins** → `bin_id` (BIN-XXXXXXXX)
9. **task_series** → `task_series_id` (TSR-XXXXXXXX)

#### Level 2 - Business Logic Tables (3 tabel)
10. **plantings** → `planting_id` (PLN-XXXXXXXX)
11. **inventory_types** → `inventory_type_id` (INV-XXXXXXXX)
12. **certifications** → `certification_id` (CRT-XXXXXXXX)

#### Level 3 - Transaction Tables (6 tabel)
13. **harvests** → `harvest_id` (HRV-XXXXXXXX)
14. **inventory_lots** → `inventory_lot_id` (LOT-XXXXXXXX)
15. **certification_reports** → `certification_report_id` (CRP-XXXXXXXX)
16. **inventory_type_seeds** → `inventory_type_seed_id` (ITS-XXXXXXXX)
17. **tasks** → `task_id` (TSK-XXXXXXXX)
18. **sales** → `sale_id` (SAL-XXXXXXXX)

#### Level 4 - Detail & Support Tables (18 tabel)
19. **inventory_transactions** → `inventory_transaction_id` (TRX-XXXXXXXX)
20. **inventory_type_warehouses** → `inventory_type_warehouse_id` (ITW-XXXXXXXX)
21. **inventory_type_certification_reports** → `inventory_type_certification_report_id` (ICR-XXXXXXXX)
22. **sale_items** → `sale_item_id` (SIT-XXXXXXXX)
23. **expenses** → `expense_id` (EXP-XXXXXXXX)
24. **nutrients** → `nutrient_id` (NTR-XXXXXXXX)
25. **treatments** → `treatment_id` (TRT-XXXXXXXX)
26. **attachments** → `attachment_id` (ATT-XXXXXXXX)
27. **seed_histories** → `seed_history_id` (SDH-XXXXXXXX)
28. **planting_losses** → `planting_loss_id` (PLS-XXXXXXXX)
29. **plant_notes** → `plant_note_id` (PLN-XXXXXXXX)
30. **plant_photos** → `plant_photo_id` (PHP-XXXXXXXX)
31. **planting_location_notes** → `planting_location_note_id` (LCN-XXXXXXXX)
32. **planting_location_photos** → `planting_location_photo_id` (LCP-XXXXXXXX)
33. **inventory_notes** → `inventory_note_id` (INN-XXXXXXXX)
34. **inventory_photos** → `inventory_photo_id` (INP-XXXXXXXX)
35. **user_planting_location_land_manager** → `user_planting_location_land_manager_id` (ULM-XXXXXXXX)
36. **user_planting_location_land_worker** → `user_planting_location_land_worker_id` (ULW-XXXXXXXX)

---

## 📁 File yang Telah Dibuat

### 1. Core Components ✅
```
app/Traits/HasCustomId.php
```
- Trait untuk auto-generate custom ID
- Support untuk semua prefix yang sudah didefinisikan
- Collision detection & handling

### 2. Migration Files ✅
```
database/migrations/phase_1_correct/
├── 2026_02_10_001_phase1_add_custom_id_level_0.php (5 tables)
├── 2026_02_10_002_phase1_add_custom_id_level_1.php (4 tables)
├── 2026_02_10_003_phase1_add_custom_id_level_2.php (3 tables)
├── 2026_02_10_004_phase1_add_custom_id_level_3.php (6 tables)
└── 2026_02_10_005_phase1_add_custom_id_level_4.php (18 tables)
```

### 3. Analysis & Planning Files ✅
```
database_structure_analysis.json    - Analisis lengkap struktur database
migration_plan.json                  - Rencana migrasi detail per tabel
```

### 4. Utility Scripts ✅
```
analyze_database_structure.php      - Script analisis database
generate_correct_migrations.php     - Generator migration plan
auto_generate_migrations.php        - Auto-generator migration files
create_backup.php                   - Script backup database
cleanup_custom_id_columns.php       - Script rollback/cleanup
check_table_structure.php           - Helper check struktur
check_all_tables.php                - Check semua tabel
```

### 5. Documentation ✅
```
MIGRATION_STRATEGY_CUSTOM_IDS.md    - Strategi migrasi lengkap
EXECUTION_GUIDE.md                  - Panduan eksekusi
EXAMPLE_MODEL_UPDATE.md             - Contoh update Model
QUICK_REFERENCE.md                  - Quick reference
README_CUSTOM_ID_MIGRATION.md       - README migrasi
STEP_BY_STEP_EXECUTION.md           - Step-by-step guide
TODO.md                             - Task checklist
```

### 6. Backup ✅
```
database/backups/sibesti_backup_before_migration_20260205_033351.sql
```

---

## 🚀 Cara Menjalankan Migrasi

### ⚠️ PENTING: Baca Ini Dulu!

1. **JANGAN jalankan di production** tanpa testing di development dulu
2. **BACKUP database** sudah dibuat, tapi buat backup tambahan jika perlu
3. **Matikan aplikasi** selama migrasi berlangsung
4. **Jalankan di jam non-peak** jika di production

### Langkah Eksekusi

#### Step 1: Verifikasi Backup
```bash
# Cek apakah backup ada
ls -lh database/backups/

# Atau buat backup baru
php create_backup.php
```

#### Step 2: Review Migration Files
```bash
# Buka dan review migration files
code database/migrations/phase_1_correct/
```

#### Step 3: Jalankan Phase 1 (Tambah Kolom)
```bash
# Jalankan migration phase 1
php artisan migrate --path=database/migrations/phase_1_correct

# Verifikasi kolom sudah ditambahkan
php check_all_tables.php
```

#### Step 4: Generate & Jalankan Phase 2 (Migrasi Data)
```bash
# TODO: Buat script generator untuk Phase 2
# Phase 2 akan mengisi kolom baru dengan custom ID
# dan update semua FK references
```

#### Step 5: Generate & Jalankan Phase 3 (Finalisasi)
```bash
# TODO: Buat script generator untuk Phase 3
# Phase 3 akan:
# - Drop kolom ID lama
# - Rename kolom baru jadi PK
# - Recreate FK constraints
```

---

## 🔍 Temuan Penting dari Analisis

### Kolom User yang Berbeda-beda
Tidak semua tabel menggunakan `user_id`. Beberapa menggunakan:
- `recorded_by` (harvests)
- `edited_by` (harvests, expenses, nutrients, treatments)
- `responsible_person_id` (warehouses, inventory_types, expenses, nutrients, treatments)
- `created_by` (attachments, tasks)
- `assigned_to` (tasks, planting_location_notes)
- `filled_by_user_id` (inventory_type_seeds)

### Tabel Tanpa Location ID
- `planting_locations` TIDAK memiliki `location_id` FK

### Kolom yang Sudah Ada
Beberapa tabel sudah memiliki kolom custom ID dari percobaan sebelumnya:
- `harvests.new_recorded_by`
- `harvests.new_edited_by`

---

## ⏭️ Next Steps (Yang Perlu Dilakukan)

### 1. Generate Phase 2 Migrations ⏳
Buat script untuk generate migration Phase 2 yang akan:
- Loop semua data existing
- Generate custom ID untuk setiap row
- Update semua FK references

### 2. Generate Phase 3 Migrations ⏳
Buat script untuk generate migration Phase 3 yang akan:
- Drop old ID columns
- Rename new columns to be PK
- Recreate FK constraints

### 3. Update All Models ⏳
Tambahkan `HasCustomId` trait ke semua 36 models

### 4. Testing ⏳
- Test di development environment
- Verify data integrity
- Test CRUD operations
- Test relationships

---

## 📞 Troubleshooting

### Jika Migration Gagal

1. **JANGAN PANIK**
2. **JANGAN lanjutkan ke fase berikutnya**
3. Rollback migration:
   ```bash
   php artisan migrate:rollback --path=database/migrations/phase_1_correct
   ```
4. Atau restore dari backup:
   ```bash
   # Import backup SQL
   mysql -u root -p sibit < database/backups/sibesti_backup_*.sql
   ```
5. Analisis error log
6. Perbaiki issue
7. Ulangi dari fase yang bermasalah

### Jika Ada Collision ID

Trait `HasCustomId` sudah handle collision dengan:
- Retry mechanism (10 attempts)
- Fallback dengan timestamp

### Jika FK Constraint Error

Pastikan urutan migrasi sesuai dependency level (0 → 1 → 2 → 3 → 4)

---

## 📊 Monitoring Progress

Gunakan script check untuk monitoring:

```bash
# Check struktur semua tabel
php check_all_tables.php

# Check tabel spesifik
php check_table_structure.php users

# Analyze database structure
php analyze_database_structure.php
```

---

## ✅ Checklist Migrasi

### Persiapan
- [x] Backup database
- [x] Analisis struktur database
- [x] Generate migration plan
- [x] Buat Trait HasCustomId
- [x] Generate Phase 1 migrations
- [ ] Generate Phase 2 migrations
- [ ] Generate Phase 3 migrations

### Eksekusi
- [ ] Test Phase 1 di development
- [ ] Test Phase 2 di development
- [ ] Test Phase 3 di development
- [ ] Verify data integrity
- [ ] Update all Models
- [ ] Test CRUD operations
- [ ] Test relationships
- [ ] Deploy to production

### Post-Migration
- [ ] Monitor application
- [ ] Check error logs
- [ ] Verify all features working
- [ ] Update documentation
- [ ] Train team on new ID format

---

## 🎓 Format Custom ID

```
PREFIX-XXXXXXXX

Contoh:
- USR-A1B2C3D4 (User)
- PLT-X9Y8Z7W6 (Plant)
- HRV-M5N4O3P2 (Harvest)
```

- **Prefix:** 3 huruf uppercase (sesuai tabel)
- **Separator:** Dash (-)
- **Random:** 8 karakter alfanumerik uppercase
- **Total Length:** 12 karakter

---

## 📚 Referensi

- `database_structure_analysis.json` - Struktur database lengkap
- `migration_plan.json` - Detail plan per tabel
- `MIGRATION_STRATEGY_CUSTOM_IDS.md` - Strategi lengkap
- `EXECUTION_GUIDE.md` - Panduan eksekusi detail

---

**Dibuat:** 5 Februari 2026  
**Status:** Persiapan Selesai, Siap untuk Phase 2 & 3  
**Next Action:** Generate Phase 2 & 3 migrations
