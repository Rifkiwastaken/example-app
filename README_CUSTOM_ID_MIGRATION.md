# 🔄 Database Migration: BigInt ID → Custom String ID

## 📖 Ringkasan

Proyek ini berisi strategi dan implementasi lengkap untuk melakukan refactoring database dari Primary Key BigInt Auto-Increment menjadi Custom String ID dengan format PREFIX-RANDOM untuk aplikasi SIBESTI.

### Tujuan
- **Dari**: `id` (BigInt Auto-Increment)
- **Ke**: `[table_singular]_id` (VARCHAR(36) dengan format PREFIX-XXXXXXXX)
- **Contoh**: `plant_id` dengan nilai `PLT-8X92MKA1`

### Mengapa Custom String ID?
1. **Human-Readable**: ID lebih mudah dibaca dan diingat
2. **Identifiable**: Prefix menunjukkan tipe data (PLT = Plant, USR = User)
3. **Secure**: Tidak sequential, lebih sulit diprediksi
4. **Scalable**: Mendukung distributed systems
5. **Professional**: Terlihat lebih profesional di URL dan dokumen

## 📁 Struktur Dokumentasi

### 1. 📋 MIGRATION_STRATEGY_CUSTOM_IDS.md
**Deskripsi**: Dokumen strategi lengkap yang menjelaskan overview, daftar tabel, dan struktur migrasi 3 fase.

**Kapan Dibaca**: Pertama kali, untuk memahami big picture.

**Isi**:
- Ringkasan eksekutif
- Daftar 36+ tabel yang akan dimigrasi
- Penjelasan strategi 3 fase
- Struktur file yang akan dibuat
- Peringatan dan catatan teknis

### 2. 🚀 EXECUTION_GUIDE.md
**Deskripsi**: Panduan eksekusi step-by-step dengan detail lengkap.

**Kapan Dibaca**: Saat akan melakukan migrasi.

**Isi**:
- Checklist persiapan lengkap
- Langkah eksekusi per fase
- Query verifikasi untuk setiap fase
- Troubleshooting common issues
- Rollback procedures
- Post-migration checklist

### 3. 💻 EXAMPLE_MODEL_UPDATE.md
**Deskripsi**: Contoh konkret cara update Model Laravel.

**Kapan Dibaca**: Setelah migrasi database selesai, saat update model.

**Isi**:
- Contoh before/after untuk berbagai model
- Penjelasan setiap perubahan yang diperlukan
- Daftar lengkap model yang harus diupdate
- Testing examples

### 4. ✅ TODO.md
**Deskripsi**: Checklist lengkap untuk tracking progress.

**Kapan Dibaca**: Sepanjang proses migrasi.

**Isi**:
- Checklist persiapan
- Checklist eksekusi per fase
- Checklist update model
- Checklist testing
- Section untuk notes dan observations

### 5. ⚡ QUICK_REFERENCE.md
**Deskripsi**: Referensi cepat untuk command dan query.

**Kapan Dibaca**: Saat eksekusi, sebagai cheat sheet.

**Isi**:
- Format custom ID
- Command eksekusi
- Template update model
- Query verifikasi
- Prefix mapping
- Emergency commands

## 🗂️ Struktur File Implementasi

```
app/
└── Traits/
    └── HasCustomId.php                 # Trait untuk auto-generate custom ID

database/
└── migrations/
    ├── phase_1/                        # FASE 1: Tambah kolom baru
    │   ├── 2026_02_10_000001_phase1_add_custom_id_columns_core.php
    │   ├── 2026_02_10_000002_phase1_add_custom_id_columns_certification.php
    │   ├── 2026_02_10_000003_phase1_add_custom_id_columns_inventory.php
    │   ├── 2026_02_10_000004_phase1_add_custom_id_columns_sales.php
    │   └── 2026_02_10_000005_phase1_add_custom_id_columns_support.php
    │
    ├── phase_2/                        # FASE 2: Migrasi data
    │   ├── 2026_02_10_100001_phase2_migrate_data_core.php
    │   └── 2026_02_10_100002_phase2_migrate_data_remaining.php
    │
    └── phase_3/                        # FASE 3: Finalisasi
        ├── 2026_02_10_200001_phase3_finalize_core.php
        └── 2026_02_10_200002_phase3_finalize_remaining.php
```

## 🎯 Strategi Migrasi 3 Fase

### Fase 1: Persiapan & Penambahan Kolom Baru
- Menambahkan kolom custom ID baru (nullable)
- Menambahkan kolom FK baru (temporary)
- **TIDAK** menghapus kolom lama
- **AMAN** untuk di-rollback

### Fase 2: Migrasi Data
- Generate custom ID untuk semua record
- Update semua FK baru dengan nilai yang sesuai
- Verifikasi data integrity
- **AMAN** untuk di-rollback

### Fase 3: Finalisasi
- Drop kolom ID lama
- Drop FK lama
- Rename kolom baru menjadi nama final
- Set custom ID sebagai Primary Key
- Tambahkan FK constraint baru
- **⚠️ TIDAK BISA di-rollback dengan mudah**

## 🚦 Quick Start

### 1. Persiapan
```bash
# Backup database
php artisan db:backup

# Aktifkan maintenance mode
php artisan down
```

### 2. Eksekusi Fase 1
```bash
php artisan migrate --path=database/migrations/phase_1/2026_02_10_000001_phase1_add_custom_id_columns_core.php
php artisan migrate --path=database/migrations/phase_1/2026_02_10_000002_phase1_add_custom_id_columns_certification.php
php artisan migrate --path=database/migrations/phase_1/2026_02_10_000003_phase1_add_custom_id_columns_inventory.php
php artisan migrate --path=database/migrations/phase_1/2026_02_10_000004_phase1_add_custom_id_columns_sales.php
php artisan migrate --path=database/migrations/phase_1/2026_02_10_000005_phase1_add_custom_id_columns_support.php
```

### 3. Eksekusi Fase 2
```bash
php artisan migrate --path=database/migrations/phase_2/2026_02_10_100001_phase2_migrate_data_core.php
php artisan migrate --path=database/migrations/phase_2/2026_02_10_100002_phase2_migrate_data_remaining.php
```

### 4. Eksekusi Fase 3
```bash
php artisan migrate --path=database/migrations/phase_3/2026_02_10_200001_phase3_finalize_core.php
php artisan migrate --path=database/migrations/phase_3/2026_02_10_200002_phase3_finalize_remaining.php
```

### 5. Update Models
Lihat `EXAMPLE_MODEL_UPDATE.md` untuk contoh lengkap.

### 6. Testing & Aktivasi
```bash
# Test aplikasi
php artisan tinker

# Nonaktifkan maintenance mode
php artisan up
```

## ⚠️ Peringatan Penting

1. **BACKUP WAJIB**: Selalu backup database sebelum memulai
2. **TEST DI DEVELOPMENT**: Jangan langsung di production
3. **SEQUENTIAL EXECUTION**: Jalankan fase secara berurutan (1→2→3)
4. **VERIFY EACH PHASE**: Verifikasi setiap fase sebelum lanjut
5. **POINT OF NO RETURN**: Fase 3 sangat sulit di-rollback
6. **DOWNTIME REQUIRED**: Aplikasi harus offline selama migrasi
7. **TIME ESTIMATION**: 30-60 menit tergantung jumlah data

## 📊 Tabel yang Akan Dimigrasi

### Core Tables (15 tabel)
users, plant_types, plants, planting_locations, plantings, harvests, plant_notes, plant_photos, planting_location_notes, planting_location_photos, planting_losses, locations, nutrients, treatments, + 2 pivot tables

### Certification Tables (2 tabel)
certifications, certification_reports

### Inventory Tables (11 tabel)
warehouses, bins, inventory_types, inventory_lots, inventory_transactions, inventory_type_warehouses, inventory_type_seeds, inventory_type_certification_reports, inventory_notes, inventory_photos, seed_histories

### Sales Tables (2 tabel)
sales, sale_items

### Support Tables (5 tabel)
tasks, task_series, task_templates, expenses, attachments

**Total: 35+ tabel**

## 🎨 Format Custom ID

```
PREFIX-XXXXXXXX
```

- **PREFIX**: 3 huruf uppercase (PLT, USR, SAL, dll)
- **RANDOM**: 8 karakter alfanumerik uppercase
- **Total Length**: 12 karakter (termasuk dash)
- **Example**: `PLT-8X92MKA1`, `USR-A1B2C3D4`

## 🔧 Fitur Trait HasCustomId

```php
use App\Traits\HasCustomId;

class Plant extends Model
{
    use HasCustomId;
    
    protected $primaryKey = 'plant_id';
    protected $keyType = 'string';
    public $incrementing = false;
}

// Auto-generate saat create
$plant = Plant::create(['name' => 'Tomat']);
echo $plant->plant_id; // PLT-8X92MKA1 (auto-generated)
```

## 📈 Benefits

### Sebelum (BigInt ID)
```
URL: /plants/1234
ID di Database: 1234
Mudah ditebak: ✅ (security risk)
Human-readable: ❌
Identifiable: ❌
```

### Sesudah (Custom String ID)
```
URL: /plants/PLT-8X92MKA1
ID di Database: PLT-8X92MKA1
Mudah ditebak: ❌ (lebih secure)
Human-readable: ✅
Identifiable: ✅ (PLT = Plant)
```

## 🆘 Support & Troubleshooting

### Jika Menemui Masalah

1. **Cek log**: `storage/logs/laravel.log`
2. **Lihat EXECUTION_GUIDE.md**: Section troubleshooting
3. **Jangan panik**: Backup tersedia untuk restore
4. **Jangan lanjut**: Jika ada error, stop dan analisis dulu

### Common Issues

- **Foreign Key Constraint Error**: Data tidak konsisten, cek dengan query verifikasi
- **Duplicate Entry**: Collision di ID generation (sangat jarang)
- **Column Not Found**: Model belum diupdate

## 📞 Contact

Jika ada pertanyaan atau butuh bantuan:
- Review dokumentasi lengkap di folder ini
- Cek TODO.md untuk tracking progress
- Gunakan QUICK_REFERENCE.md sebagai cheat sheet

## 📝 License & Credits

Dibuat untuk aplikasi SIBESTI (Sistem Informasi Benih Bersertifikat)

---

**⚡ Quick Links:**
- [Strategi Lengkap](MIGRATION_STRATEGY_CUSTOM_IDS.md)
- [Panduan Eksekusi](EXECUTION_GUIDE.md)
- [Contoh Update Model](EXAMPLE_MODEL_UPDATE.md)
- [Checklist TODO](TODO.md)
- [Quick Reference](QUICK_REFERENCE.md)

**Last Updated:** 2026-02-10

**Status:** ✅ Ready for Execution
