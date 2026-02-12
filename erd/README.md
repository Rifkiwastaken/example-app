# Dokumentasi ERD SIBESTI

**Sistem Informasi Benih Sertifikasi**  
**Tanggal Update:** Februari 2026

---

## Daftar Isi

1. [Tentang Dokumentasi ERD](#tentang-dokumentasi-erd)
2. [File ERD Terbaru](#file-erd-terbaru)
3. [Struktur Database](#struktur-database)
4. [Cara Menggunakan ERD](#cara-menggunakan-erd)
5. [Konvensi Penamaan](#konvensi-penamaan)
6. [File Legacy](#file-legacy)

---

## Tentang Dokumentasi ERD

Folder ini berisi dokumentasi Entity Relationship Diagram (ERD) untuk aplikasi SIBESTI. Dokumentasi mencakup:
- Diagram visual dalam format PlantUML
- Definisi struktur tabel dalam format SQL
- Dokumentasi lengkap dalam format Markdown

### Perubahan Utama (Februari 2026)

Aplikasi SIBESTI telah dimigrasikan untuk menggunakan **Custom String ID** dengan format:
- **Tipe Data:** VARCHAR(36)
- **Format:** `{nama_tabel}_id` (contoh: `plant_id`, `user_id`, `planting_location_id`)
- **Alasan:** Lebih aman, tidak mudah ditebak, mendukung distributed systems

---

## File ERD Terbaru

### File Utama (Gunakan Ini!)

| File | Format | Deskripsi |
|------|--------|-----------|
| **04_erd_sibesti_lengkap.puml** | PlantUML | ERD lengkap semua modul (overview) |
| **04_erd_sibesti_lengkap.sql** | SQL | DDL dengan NOT NULL/NULL eksplisit dan relasi PK/FK |
| **05_erd_sibesti_updated.puml** | PlantUML | ERD updated (detail kolom) |
| **05_erd_sibesti_updated.sql** | SQL | DDL dengan NOT NULL/NULL dan PK/FK (versi detail) |
| **06_erd_sibesti_complete.sql** | SQL | Struktur tabel lengkap dengan format sederhana |
| **06_erd_sibesti_complete.puml** | PlantUML | Diagram ERD visual lengkap |
| **DOKUMENTASI_TABEL_LENGKAP.md** | Markdown | Dokumentasi detail semua tabel |

### Cara Membuka File

#### PlantUML (.puml)
1. **Online:** Buka [PlantUML Web Server](http://www.plantuml.com/plantuml/uml/)
2. **VS Code:** Install extension "PlantUML" kemudian preview dengan `Alt+D`
3. **IntelliJ:** Install plugin "PlantUML Integration"

#### SQL (.sql)
1. Buka dengan text editor atau SQL client
2. File ini untuk **dokumentasi** - BUKAN untuk dieksekusi langsung
3. Migrasi Laravel adalah sumber kebenaran untuk struktur database

---

## Struktur Database

### Statistik

| Item | Jumlah |
|------|--------|
| Total Tabel | 35 |
| Format Primary Key | VARCHAR(36) |
| Modul | 9 |

### Modul dan Tabel

#### 1. Sistem & Autentikasi (4 tabel)
- `users` - Data pengguna
- `locations` - Lokasi fisik/kantor
- `password_reset_tokens` - Token reset password
- `personal_access_tokens` - Token API (Sanctum)

#### 2. Manajemen Tugas (3 tabel)
- `task_templates` - Template tugas
- `task_series` - Seri tugas berulang
- `tasks` - Data tugas/laporan

#### 3. Modul Penanaman (10 tabel)
- `plant_types` - Jenis tanaman
- `plants` - Data tanaman
- `planting_locations` - Lokasi penanaman
- `plantings` - Data penanaman
- `harvests` - Data panen
- `planting_losses` - Kehilangan/kegagalan
- `plant_notes` - Catatan tanaman
- `plant_photos` - Foto tanaman
- `planting_location_notes` - Catatan lokasi
- `planting_location_photos` - Foto lokasi

#### 4. Treatment & Nutrient (4 tabel)
- `treatments` - Perlakuan tanaman
- `nutrients` - Pemupukan/nutrisi
- `expenses` - Pengeluaran/biaya
- `attachments` - Lampiran dokumen

#### 5. Sertifikasi (2 tabel)
- `certifications` - Data sertifikasi
- `certification_reports` - Laporan pemeriksaan

#### 6. Gudang & Inventori (10 tabel)
- `warehouses` - Data gudang
- `bins` - Rak/bin gudang
- `inventory_types` - Jenis inventori
- `inventory_lots` - Lot inventori
- `inventory_transactions` - Transaksi inventori
- `inventory_type_warehouses` - Relasi inventori-gudang
- `inventory_notes` - Catatan inventori
- `inventory_photos` - Foto inventori
- `inventory_type_seeds` - Relasi inventori-benih
- `inventory_type_certification_reports` - Relasi inventori-sertifikasi

#### 7. Penjualan (2 tabel)
- `sales` - Data penjualan
- `sale_items` - Item penjualan

#### 8. Relasi User-Lokasi (2 tabel)
- `user_planting_location_land_manager` - Pengelola lahan
- `user_planting_location_land_worker` - Pekerja lahan

#### 9. Landing Page (1 tabel)
- `landing_page_settings` - Pengaturan halaman landing

---

## Cara Menggunakan ERD

### Untuk Developer

1. **Referensi Struktur Database:**
   - Buka `DOKUMENTASI_TABEL_LENGKAP.md` untuk detail kolom
   - Gunakan `06_erd_sibesti_complete.sql` untuk quick reference

2. **Visualisasi Relasi:**
   - Render `06_erd_sibesti_complete.puml` dengan PlantUML
   - Lihat diagram untuk memahami relasi antar tabel

3. **Migrasi Database:**
   - Sumber kebenaran ada di `database/migrations/`
   - File ERD adalah dokumentasi, bukan sumber eksekusi

### Untuk Dokumentasi

1. **Presentasi:**
   - Export PlantUML ke PNG/SVG
   - Gunakan tabel dari markdown untuk laporan

2. **Analisis:**
   - Gunakan markdown untuk review struktur
   - SQL file untuk referensi teknis

---

## Konvensi Penamaan

### Primary Key

Semua tabel menggunakan format: `{nama_tabel_singular}_id`

```
users           → user_id
plants          → plant_id
plantings       → planting_id
harvests        → harvest_id
warehouses      → warehouse_id
sales           → sale_id
```

### Prefix ID

| Tabel | Prefix |
|-------|--------|
| users | USR |
| plants | PLT |
| plantings | PLN |
| harvests | HRV |
| certifications | CRT |
| warehouses | WRH |
| inventory_types | ITY |
| sales | SAL |

### Contoh ID

```
USR-abc123def456
PLT-xyz789ghi012
WRH-jkl345mno678
```

---

## File Legacy

File-file legacy telah dipindahkan ke folder **`_legacy/`** untuk menjaga kebersihan direktori.

### Isi Folder _legacy/
- File PlantUML lama (01-05)
- File SQL lama (02-03)
- File DBML lama
- README lama

### Alasan Dipindahkan
- Menggunakan `BIGINT AUTO_INCREMENT` untuk primary key
- Tidak sesuai dengan struktur database saat ini (VARCHAR(36))
- Disimpan untuk referensi historis jika diperlukan

### Struktur Folder ERD Saat Ini

```
erd/
├── 04_erd_sibesti_lengkap.puml      ← ERD lengkap (semua modul)
├── 04_erd_sibesti_lengkap.sql        ← SQL dengan NOT NULL/NULL + PK/FK
├── 05_erd_sibesti_updated.puml       ← ERD updated (detail)
├── 05_erd_sibesti_updated.sql        ← SQL dengan NOT NULL/NULL + PK/FK (detail)
├── 06_erd_sibesti_complete.sql       ← File SQL lengkap
├── 06_erd_sibesti_complete.puml      ← File PlantUML utama
├── DOKUMENTASI_TABEL_LENGKAP.md      ← Dokumentasi detail
├── README.md                         ← File ini
├── STRUKTUR_DATABASE/                ← Dokumentasi per tabel
│   ├── users.md
│   ├── plants.md
│   └── ...
└── _legacy/                         ← File lama (tidak digunakan)
    ├── 01_erd_sibit.puml
    ├── 02_*.puml, 02_*.sql
    └── ...
```

---

## Catatan Penting

1. **Sumber Kebenaran:** Migrasi Laravel (`database/migrations/`) adalah sumber kebenaran untuk struktur database

2. **Format ID:** Semua ID menggunakan VARCHAR(36) dengan Short Unique ID

3. **Update Dokumentasi:** Jika ada perubahan struktur database, update juga file:
   - `06_erd_sibesti_complete.sql`
   - `06_erd_sibesti_complete.puml`
   - `DOKUMENTASI_TABEL_LENGKAP.md`

4. **Foreign Key:** Semua foreign key menggunakan tipe data yang sama dengan primary key yang direferensikan (VARCHAR(36))

---

*Dokumentasi ERD SIBESTI - Februari 2026*
