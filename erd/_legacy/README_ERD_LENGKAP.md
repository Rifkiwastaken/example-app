# ERD Legacy - SIBESTI

> **CATATAN PENTING:** File-file dalam folder ini adalah versi legacy yang telah diperbarui untuk merefleksikan struktur database terkini dengan custom string ID (VARCHAR(36)).

## Perubahan Struktur Database

Semua file ERD di folder ini telah diperbarui dengan perubahan berikut:

### 1. Primary Key Format
- **Sebelum:** `BIGINT UNSIGNED AUTO_INCREMENT`
- **Sesudah:** `VARCHAR(36)` dengan format `{nama_tabel}_id`

### 2. Contoh Perubahan
| Tabel | PK Lama | PK Baru |
|-------|---------|---------|
| users | id | user_id |
| plants | id | plant_id |
| plantings | id | planting_id |
| harvests | id | harvest_id |
| certifications | id | certification_id |
| warehouses | id | warehouse_id |
| inventory_types | id | inventory_type_id |
| sales | id | sale_id |

### 3. Foreign Key
Semua foreign key juga telah diperbarui mengikuti format baru:
- `user_id` → VARCHAR(36) merujuk ke `users.user_id`
- `plant_type_id` → VARCHAR(36) merujuk ke `plant_types.plant_type_id`
- dst.

## Daftar File

### PlantUML Files (.puml)
| File | Deskripsi |
|------|-----------|
| 01_erd_sibit.puml | ERD lengkap SIBESTI |
| 02_erd_sibit_detailed.puml | ERD detail dengan semua kolom |
| 02a_erd_sibit_core.puml | Modul Core (User, Lokasi, Tanaman) |
| 02b_erd_sibit_certification.puml | Modul Sertifikasi |
| 02c_erd_sibit_inventory_sales.puml | Modul Inventory & Penjualan |
| 02d_erd_sibit_support.puml | Modul Support (Notes, Photos) |
| 03_erd_sibit_core.puml | Tabel-tabel core |
| 03_erd_sibit_penanaman.puml | Modul Penanaman |
| 03_erd_sibit_sertifikasi.puml | Modul Sertifikasi |
| 03_erd_sibit_gudang.puml | Modul Gudang/Inventory |
| 03_erd_sibit_penjualan.puml | Modul Penjualan |
| 04_erd_sibesti_lengkap.puml | ERD lengkap dengan package |
| 05_erd_sibesti_updated.puml | ERD updated terbaru |

### SQL Files (.sql)
| File | Deskripsi |
|------|-----------|
| 02a_erd_sibit_core.sql | SQL untuk tabel core |
| 02b_erd_sibit_certification.sql | SQL untuk modul sertifikasi |
| 02c_erd_sibit_inventory_sales.sql | SQL untuk inventory & penjualan |
| 02d_erd_sibit_support.sql | SQL untuk tabel pendukung |
| 03_erd_sibit_core.sql | SQL tabel core |
| 03_erd_sibit_gudang.sql | SQL modul gudang |
| 03_erd_sibit_penanaman.sql | SQL modul penanaman |
| 03_erd_sibit_penjualan.sql | SQL modul penjualan |
| 03_erd_sibit_sertifikasi.sql | SQL modul sertifikasi |

### DBML Files (.dbml)
| File | Deskripsi |
|------|-----------|
| 04_erd_sibesti_lengkap.dbml | DBML schema lengkap |

## Format SQL Simplified

File SQL dalam folder ini menggunakan format yang disederhanakan:
- Hanya menampilkan: nama kolom, tipe data, dan ukuran
- Tidak menyertakan: UNSIGNED, NULL, NOT NULL, AUTO_INCREMENT
- Contoh:

```sql
CREATE TABLE users (
    user_id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    role ENUM('admin','pimpinan','petugas_lapangan','penangkar'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Rekomendasi

Untuk dokumentasi terbaru dan lengkap, silakan gunakan file di folder utama `erd/`:
- `06_erd_sibesti_complete.puml` - ERD PlantUML terbaru
- `06_erd_sibesti_complete.sql` - SQL schema terbaru
- `DOKUMENTASI_TABEL_LENGKAP.md` - Dokumentasi detail semua tabel

---

*Terakhir diperbarui: Februari 2026*
