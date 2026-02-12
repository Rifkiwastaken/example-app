# Strategi Migrasi Database: BigInt ID → Custom String ID

## 📋 Ringkasan Eksekutif

Dokumen ini menjelaskan strategi lengkap untuk mengubah Primary Key dari BigInt Auto-Increment menjadi Custom String ID dengan format PREFIX-RANDOM untuk semua tabel di aplikasi SIBESTI.

## 🎯 Tujuan

- **Dari**: `id` (BigInt Auto-Increment)
- **Ke**: `[table_singular]_id` (VARCHAR(36) dengan format PREFIX-RANDOM)
- **Contoh**: `plant_id` dengan nilai `PLT-8X92MKA1`

## 📊 Daftar Tabel yang Akan Dimigrasi

### Core Tables (Prioritas Tinggi)
1. **users** → `user_id` (Prefix: USR)
2. **plant_types** → `plant_type_id` (Prefix: PTY)
3. **plants** → `plant_id` (Prefix: PLT)
4. **planting_locations** → `planting_location_id` (Prefix: LOC)
5. **plantings** → `planting_id` (Prefix: PLN)
6. **harvests** → `harvest_id` (Prefix: HRV)

### Certification Tables
7. **certifications** → `certification_id` (Prefix: CRT)
8. **certification_reports** → `certification_report_id` (Prefix: CRP)

### Inventory & Warehouse Tables
9. **warehouses** → `warehouse_id` (Prefix: WHS)
10. **bins** → `bin_id` (Prefix: BIN)
11. **inventory_types** → `inventory_type_id` (Prefix: INV)
12. **inventory_lots** → `inventory_lot_id` (Prefix: LOT)
13. **inventory_transactions** → `inventory_transaction_id` (Prefix: TRX)
14. **inventory_type_warehouses** → `inventory_type_warehouse_id` (Prefix: ITW)
15. **inventory_type_seeds** → `inventory_type_seed_id` (Prefix: ITS)
16. **inventory_type_certification_reports** → `inventory_type_certification_report_id` (Prefix: ICR)

### Sales Tables
17. **sales** → `sale_id` (Prefix: SAL)
18. **sale_items** → `sale_item_id` (Prefix: SIT)

### Support Tables
19. **tasks** → `task_id` (Prefix: TSK)
20. **task_series** → `task_series_id` (Prefix: TSR)
21. **task_templates** → `task_template_id` (Prefix: TTP)
22. **locations** → `location_id` (Prefix: LCT)
23. **nutrients** → `nutrient_id` (Prefix: NTR)
24. **treatments** → `treatment_id` (Prefix: TRT)
25. **expenses** → `expense_id` (Prefix: EXP)
26. **attachments** → `attachment_id` (Prefix: ATT)
27. **seed_histories** → `seed_history_id` (Prefix: SDH)
28. **planting_losses** → `planting_loss_id` (Prefix: PLS)

### Notes & Photos Tables
29. **plant_notes** → `plant_note_id` (Prefix: PLN)
30. **plant_photos** → `plant_photo_id` (Prefix: PHP)
31. **planting_location_notes** → `planting_location_note_id` (Prefix: LCN)
32. **planting_location_photos** → `planting_location_photo_id` (Prefix: LCP)
33. **inventory_notes** → `inventory_note_id` (Prefix: INN)
34. **inventory_photos** → `inventory_photo_id` (Prefix: INP)

### Pivot Tables
35. **user_planting_location_land_manager** → `user_planting_location_land_manager_id` (Prefix: ULM)
36. **user_planting_location_land_worker** → `user_planting_location_land_worker_id` (Prefix: ULW)

### System Tables (Optional - Bisa Diabaikan)
- **password_reset_tokens** (sudah menggunakan email sebagai PK)
- **failed_jobs**
- **personal_access_tokens**
- **landing_page_settings**

## 🔄 Strategi Migrasi 3-Fase

### **FASE 1: Persiapan & Penambahan Kolom Baru**
Menambahkan kolom baru tanpa menghapus yang lama.

### **FASE 2: Migrasi Data**
Mengisi kolom baru dengan ID custom dan update semua FK.

### **FASE 3: Finalisasi**
Menghapus kolom lama dan menjadikan kolom baru sebagai PK.

## 📁 Struktur File yang Akan Dibuat

```
app/
├── Traits/
│   └── HasCustomId.php              # Trait untuk auto-generate custom ID
│
database/
├── migrations/
│   ├── phase_1/
│   │   ├── 2026_02_10_000001_phase1_add_custom_id_columns_core.php
│   │   ├── 2026_02_10_000002_phase1_add_custom_id_columns_certification.php
│   │   ├── 2026_02_10_000003_phase1_add_custom_id_columns_inventory.php
│   │   ├── 2026_02_10_000004_phase1_add_custom_id_columns_sales.php
│   │   └── 2026_02_10_000005_phase1_add_custom_id_columns_support.php
│   │
│   ├── phase_2/
│   │   ├── 2026_02_10_100001_phase2_migrate_data_core.php
│   │   ├── 2026_02_10_100002_phase2_migrate_data_certification.php
│   │   ├── 2026_02_10_100003_phase2_migrate_data_inventory.php
│   │   ├── 2026_02_10_100004_phase2_migrate_data_sales.php
│   │   └── 2026_02_10_100005_phase2_migrate_data_support.php
│   │
│   └── phase_3/
│       ├── 2026_02_10_200001_phase3_finalize_core.php
│       ├── 2026_02_10_200002_phase3_finalize_certification.php
│       ├── 2026_02_10_200003_phase3_finalize_inventory.php
│       ├── 2026_02_10_200004_phase3_finalize_sales.php
│       └── 2026_02_10_200005_phase3_finalize_support.php
│
└── seeders/
    └── CustomIdMigrationSeeder.php  # Optional: untuk testing
```

## ⚠️ Peringatan Penting

1. **BACKUP DATABASE** sebelum menjalankan migrasi
2. **Jalankan di environment testing** terlebih dahulu
3. **Matikan aplikasi** saat migrasi berlangsung
4. **Jalankan fase secara berurutan** (1 → 2 → 3)
5. **Jangan skip fase** atau menjalankan secara acak

## 🚀 Urutan Eksekusi

```bash
# 1. Backup database
php artisan db:backup

# 2. Jalankan Fase 1 (Tambah kolom)
php artisan migrate --path=database/migrations/phase_1

# 3. Jalankan Fase 2 (Migrasi data)
php artisan migrate --path=database/migrations/phase_2

# 4. Verifikasi data
# Cek apakah semua data sudah termigrate dengan benar

# 5. Jalankan Fase 3 (Finalisasi)
php artisan migrate --path=database/migrations/phase_3

# 6. Update semua Model dengan Trait
# 7. Testing menyeluruh
```

## 📝 Catatan Teknis

- **Format ID**: `PREFIX-XXXXXXXX` (3 huruf prefix + 8 karakter alfanumerik)
- **Panjang Total**: 12 karakter (termasuk dash)
- **Karakter**: Uppercase letters dan angka (A-Z, 0-9)
- **Collision**: Sangat rendah dengan 36^8 kombinasi (~2.8 triliun)

## 🔍 Verifikasi Setelah Migrasi

```sql
-- Cek apakah semua tabel sudah menggunakan custom ID
SELECT table_name, column_name, data_type 
FROM information_schema.columns 
WHERE table_schema = 'sibesti' 
AND column_name LIKE '%_id' 
AND is_nullable = 'NO';

-- Cek apakah ada FK yang masih menggunakan bigint
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
    AND REFERENCED_TABLE_NAME IS NOT NULL;
```

## 📞 Support

Jika ada masalah selama migrasi:
1. **JANGAN PANIK**
2. **JANGAN lanjutkan ke fase berikutnya**
3. Restore dari backup
4. Analisis error log
5. Perbaiki issue
6. Ulangi dari fase yang bermasalah
