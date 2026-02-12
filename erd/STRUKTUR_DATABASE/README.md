# Dokumentasi Struktur Database SIBESTI

**Sistem Informasi Benih Sertifikasi**  
**Tanggal Update:** Februari 2026

---

Folder ini berisi dokumentasi struktur database untuk setiap tabel dalam sistem SIBESTI.

## Informasi Penting

> **PERHATIAN:** File-file dalam folder ini mungkin belum sepenuhnya diperbarui.
> Untuk dokumentasi terbaru dan paling akurat, gunakan:
> - **`../DOKUMENTASI_TABEL_LENGKAP.md`** - Dokumentasi lengkap semua tabel
> - **`../06_erd_sibesti_complete.sql`** - Struktur SQL lengkap

## Perubahan Utama Database

### Tabel locations Dihapus
- **Tabel `locations`** telah dihapus (migration `drop_locations_table`)
- **users:** Kolom `location_id` dihapus, diganti `location_placement` (VARCHAR)
- **planting_locations:** Kolom `location_id` dihapus

### Format Primary Key Baru
- **Format Lama:** `id BIGINT AUTO_INCREMENT`
- **Format Baru:** `{nama_tabel}_id VARCHAR(36)`

### Contoh Perubahan
| Tabel | Primary Key Lama | Primary Key Baru |
|-------|-----------------|------------------|
| users | id | user_id |
| plants | id | plant_id |
| plantings | id | planting_id |
| warehouses | id | warehouse_id |
| sales | id | sale_id |

## Format Dokumentasi

Setiap file menggunakan format yang konsisten dengan kolom:
- **Nama Atribut**: Nama kolom dalam tabel
- **Tipe Data**: Tipe data kolom (VARCHAR, INT, DECIMAL, ENUM, dll)
- **Ukuran**: Ukuran/precision untuk tipe data tertentu
- **Keterangan**: Deskripsi, constraint, dan nilai default

## Daftar Tabel

### Core & User Management
- [users.md](users.md) - Tabel pengguna sistem

### Penanaman
- [plant_types.md](plant_types.md) - Tabel jenis tanaman
- [plants.md](plants.md) - Tabel data tanaman
- [planting_locations.md](planting_locations.md) - Tabel lokasi penanaman
- [plantings.md](plantings.md) - Tabel data penanaman
- [harvests.md](harvests.md) - Tabel hasil panen
- [planting_losses.md](planting_losses.md) - Tabel kerugian penanaman

### Sertifikasi
- [certifications.md](certifications.md) - Tabel sertifikasi benih
- [certification_reports.md](certification_reports.md) - Tabel laporan pemeriksaan sertifikasi

### Inventory & Gudang
- [warehouses.md](warehouses.md) - Tabel gudang
- [bins.md](bins.md) - Tabel bin/rak di gudang
- [inventory_types.md](inventory_types.md) - Tabel tipe inventory
- [inventory_lots.md](inventory_lots.md) - Tabel lot/batch inventory
- [inventory_transactions.md](inventory_transactions.md) - Tabel transaksi inventory
- [inventory_type_warehouses.md](inventory_type_warehouses.md) - Tabel relasi inventory-gudang

### Penjualan
- [sales.md](sales.md) - Tabel penjualan
- [sale_items.md](sale_items.md) - Tabel item penjualan

### Perawatan & Nutrisi
- [treatments.md](treatments.md) - Tabel perawatan/pengobatan tanaman
- [nutrients.md](nutrients.md) - Tabel nutrisi/pupuk
- [expenses.md](expenses.md) - Tabel pengeluaran

### Support & Media
- [attachments.md](attachments.md) - Tabel attachment/lampiran
- [plant_notes.md](plant_notes.md) - Tabel catatan tanaman
- [plant_photos.md](plant_photos.md) - Tabel foto tanaman
- [planting_location_notes.md](planting_location_notes.md) - Tabel catatan lokasi penanaman
- [planting_location_photos.md](planting_location_photos.md) - Tabel foto lokasi penanaman
- [inventory_notes.md](inventory_notes.md) - Tabel catatan inventory
- [inventory_photos.md](inventory_photos.md) - Tabel foto inventory

### Pivot & Relasi
- [inventory_type_seeds.md](inventory_type_seeds.md) - Relasi inventory dengan benih
- [inventory_type_certification_reports.md](inventory_type_certification_reports.md) - Relasi inventory dengan sertifikasi
- [user_planting_location_land_manager.md](user_planting_location_land_manager.md) - User sebagai pengelola
- [user_planting_location_land_worker.md](user_planting_location_land_worker.md) - User sebagai pekerja

### Lainnya
- [tasks.md](tasks.md) - Tabel tugas

## Catatan Penting

- **Primary Key:** Semua tabel menggunakan VARCHAR(36) dengan format `{nama_tabel}_id`
- **Timestamps:** Semua tabel memiliki `created_at` dan `updated_at`
- **Foreign Key:** Menggunakan CASCADE DELETE atau SET NULL sesuai kebutuhan
- **Sumber Kebenaran:** Laravel migrations di `database/migrations/`

## Informasi Aplikasi

- **Database:** MySQL 8.0+
- **Framework:** Laravel 10.x
- **Tanggal Update:** Februari 2026
- **Aplikasi:** SIBESTI (Sistem Informasi Benih Sertifikasi)
