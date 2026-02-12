# Dokumentasi Struktur Database SIBESTI

**Sistem Informasi Benih Sertifikasi**  
**Tanggal Update:** Februari 2026

---

## Daftar Isi

1. [Informasi Umum](#informasi-umum)
2. [Konvensi Penamaan](#konvensi-penamaan)
3. [Daftar Tabel](#daftar-tabel)
4. [Detail Struktur Tabel](#detail-struktur-tabel)
   - [Sistem & Autentikasi](#1-sistem--autentikasi)
   - [Manajemen Tugas](#2-manajemen-tugas)
   - [Modul Penanaman](#3-modul-penanaman)
   - [Treatment & Nutrient](#4-treatment--nutrient)
   - [Sertifikasi](#5-sertifikasi)
   - [Gudang & Inventori](#6-gudang--inventori)
   - [Penjualan](#7-penjualan)
   - [Relasi User-Lokasi](#8-relasi-user-lokasi)
   - [Landing Page](#9-landing-page)
5. [Diagram Relasi](#diagram-relasi)

---

## Informasi Umum

### Teknologi Database
- **Database Engine:** MySQL 8.0+
- **Charset:** utf8mb4
- **Collation:** utf8mb4_unicode_ci

### Karakteristik Utama
- **Total Tabel:** 35 tabel
- **Format Primary Key:** Custom String ID dengan format `{nama_tabel}_id`
- **Tipe Data ID:** VARCHAR(36) - Short Unique ID
- **Timestamps:** Semua tabel memiliki kolom `created_at` dan `updated_at`

---

## Konvensi Penamaan

### Primary Key
Semua primary key menggunakan format: `{nama_tabel_singular}_id`

| Tabel | Primary Key |
|-------|-------------|
| users | user_id |
| plants | plant_id |
| plantings | planting_id |
| harvests | harvest_id |
| warehouses | warehouse_id |
| sales | sale_id |
| ... | ... |

### Foreign Key
Foreign key menggunakan nama yang sama dengan primary key tabel yang direferensikan.

### Prefix ID per Tabel

| Tabel | Prefix ID |
|-------|-----------|
| users | USR |
| locations | LOC |
| plant_types | PTY |
| plants | PLT |
| planting_locations | PLO |
| plantings | PLN |
| harvests | HRV |
| certifications | CRT |
| certification_reports | CRR |
| warehouses | WRH |
| bins | BIN |
| inventory_types | ITY |
| inventory_lots | ILT |
| inventory_transactions | ITX |
| sales | SAL |
| sale_items | SIT |
| tasks | TSK |
| task_templates | TTM |
| task_series | TSR |
| treatments | TRT |
| nutrients | NTR |
| expenses | EXP |
| attachments | ATT |

---

## Daftar Tabel

### Ringkasan per Modul

| No | Modul | Jumlah Tabel | Tabel |
|----|-------|--------------|-------|
| 1 | Sistem & Autentikasi | 4 | users, locations, password_reset_tokens, personal_access_tokens |
| 2 | Manajemen Tugas | 3 | task_templates, task_series, tasks |
| 3 | Modul Penanaman | 10 | plant_types, plants, planting_locations, plantings, harvests, planting_losses, plant_notes, plant_photos, planting_location_notes, planting_location_photos |
| 4 | Treatment & Nutrient | 4 | treatments, nutrients, expenses, attachments |
| 5 | Sertifikasi | 2 | certifications, certification_reports |
| 6 | Gudang & Inventori | 10 | warehouses, bins, inventory_types, inventory_lots, inventory_transactions, inventory_type_warehouses, inventory_notes, inventory_photos, inventory_type_seeds, inventory_type_certification_reports |
| 7 | Penjualan | 2 | sales, sale_items |
| 8 | Relasi User-Lokasi | 2 | user_planting_location_land_manager, user_planting_location_land_worker |
| 9 | Landing Page | 1 | landing_page_settings |
| **Total** | | **35** | |

---

## Detail Struktur Tabel

### 1. Sistem & Autentikasi

#### 1.1 Tabel: `users`
**Deskripsi:** Data pengguna sistem

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| user_id | VARCHAR(36) | Primary Key |
| name | VARCHAR(255) | Nama pengguna |
| email | VARCHAR(255) | Email (UNIQUE) |
| email_verified_at | TIMESTAMP | Waktu verifikasi email |
| password | VARCHAR(255) | Password terenkripsi |
| role | ENUM | 'admin', 'pimpinan', 'petugas_lapangan', 'penangkar' |
| location_id | VARCHAR(36) | FK → locations |
| location_placement | VARCHAR(255) | Penempatan di lokasi |
| photo_path | VARCHAR(255) | Path foto profil |
| full_name | VARCHAR(255) | Nama lengkap |
| status | ENUM | 'active', 'inactive' |
| contact_type | ENUM | 'internal', 'external' |
| organization | VARCHAR(255) | Organisasi |
| position | VARCHAR(255) | Jabatan |
| nip | VARCHAR(50) | NIP |
| primary_phone | VARCHAR(20) | Telepon utama |
| secondary_phone | VARCHAR(20) | Telepon sekunder |
| address | TEXT | Alamat |
| province | VARCHAR(100) | Provinsi |
| city | VARCHAR(100) | Kota |
| district | VARCHAR(100) | Kecamatan |
| village | VARCHAR(100) | Desa/Kelurahan |
| notes | TEXT | Catatan |
| remember_token | VARCHAR(100) | Token remember me |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `location_id` → `locations.location_id` (Many-to-One)

---

#### 1.2 Tabel: `locations`
**Deskripsi:** Data lokasi fisik/kantor

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| location_id | VARCHAR(36) | Primary Key |
| name | VARCHAR(255) | Nama lokasi |
| city | VARCHAR(255) | Kota |
| district | VARCHAR(255) | Kecamatan |
| type | ENUM | 'kantor', 'lapangan', 'gudang', 'lainnya' |
| description | TEXT | Deskripsi |
| google_maps_link | VARCHAR(500) | Link Google Maps |
| photo | VARCHAR(255) | Path foto |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

---

#### 1.3 Tabel: `password_reset_tokens`
**Deskripsi:** Token untuk reset password

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| email | VARCHAR(255) | Primary Key |
| token | VARCHAR(255) | Token reset |
| created_at | TIMESTAMP | Waktu dibuat |

---

#### 1.4 Tabel: `personal_access_tokens`
**Deskripsi:** Token akses API (Laravel Sanctum)

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| id | BIGINT | Primary Key (Auto Increment) |
| tokenable_type | VARCHAR(255) | Tipe model |
| tokenable_id | BIGINT | ID model |
| name | VARCHAR(255) | Nama token |
| token | VARCHAR(64) | Token (UNIQUE) |
| abilities | TEXT | Kemampuan token |
| last_used_at | TIMESTAMP | Terakhir digunakan |
| expires_at | TIMESTAMP | Kadaluarsa |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

---

### 2. Manajemen Tugas

#### 2.1 Tabel: `task_templates`
**Deskripsi:** Template tugas yang bisa digunakan berulang

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| task_template_id | VARCHAR(36) | Primary Key |
| name | VARCHAR(255) | Nama template |
| description | TEXT | Deskripsi |
| tasks_list | JSON | Daftar tugas dalam template |
| association | ENUM | 'penanaman', 'sertifikasi', 'gudang', 'penjualan', 'umum' |
| is_active | TINYINT(1) | Status aktif |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

---

#### 2.2 Tabel: `task_series`
**Deskripsi:** Seri tugas berulang

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| task_series_id | VARCHAR(36) | Primary Key |
| name | VARCHAR(255) | Nama seri |
| description | TEXT | Deskripsi |
| template_id | VARCHAR(36) | FK → task_templates |
| series_tasks | JSON | Tugas dalam seri |
| is_active | TINYINT(1) | Status aktif |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `template_id` → `task_templates.task_template_id`

---

#### 2.3 Tabel: `tasks`
**Deskripsi:** Data tugas/laporan

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| task_id | VARCHAR(36) | Primary Key |
| title | VARCHAR(255) | Judul tugas |
| description | TEXT | Deskripsi |
| priority | ENUM | 'rendah', 'sedang', 'tinggi' |
| status | ENUM | 'pending', 'in_progress', 'completed', 'cancelled' |
| due_date | DATE | Tanggal deadline |
| location | VARCHAR(255) | Lokasi |
| location_tagged | VARCHAR(255) | Tag lokasi |
| task_report | TEXT | Laporan tugas |
| checklist | JSON | Daftar checklist |
| attachments | JSON | Lampiran |
| association | ENUM | Asosiasi modul |
| new_status | ENUM | Status baru |
| assigned_to | VARCHAR(36) | FK → users (ditugaskan ke) |
| new_priority | ENUM | Prioritas baru |
| start_date | DATE | Tanggal mulai |
| start_time | TIME | Waktu mulai |
| due_time | TIME | Waktu deadline |
| template_id | VARCHAR(36) | FK → task_templates |
| series_id | VARCHAR(36) | FK → task_series |
| planting_location_id | VARCHAR(36) | FK → planting_locations |
| planting_id | VARCHAR(36) | FK → plantings |
| task_color | VARCHAR(7) | Warna tugas (hex) |
| collaborators | JSON | Kolaborator |
| repeats | VARCHAR(50) | Pengulangan |
| hours_spent | DECIMAL(8,2) | Jam yang dihabiskan |
| created_by | VARCHAR(36) | FK → users (dibuat oleh) |
| last_edited_at | TIMESTAMP | Terakhir diedit |
| last_edited_by | VARCHAR(36) | FK → users (diedit oleh) |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `assigned_to` → `users.user_id`
- `template_id` → `task_templates.task_template_id`
- `series_id` → `task_series.task_series_id`
- `planting_location_id` → `planting_locations.planting_location_id`
- `planting_id` → `plantings.planting_id`
- `created_by` → `users.user_id`
- `last_edited_by` → `users.user_id`

---

### 3. Modul Penanaman

#### 3.1 Tabel: `plant_types`
**Deskripsi:** Jenis/tipe tanaman

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| plant_type_id | VARCHAR(36) | Primary Key |
| name | VARCHAR(255) | Nama tipe tanaman |
| category | VARCHAR(255) | Kategori |
| variety | TEXT | Varietas (multi-value) |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

---

#### 3.2 Tabel: `plants`
**Deskripsi:** Data tanaman individual

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| plant_id | VARCHAR(36) | Primary Key |
| name | VARCHAR(255) | Nama tanaman |
| plant_type_id | VARCHAR(36) | FK → plant_types |
| variety | VARCHAR(255) | Varietas |
| status | ENUM | 'perencanaan', 'aktif', 'panen', 'selesai' |
| progress | INT | Progress (0-100) |
| planting_location_id | VARCHAR(36) | FK → planting_locations |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `plant_type_id` → `plant_types.plant_type_id`
- `planting_location_id` → `planting_locations.planting_location_id`

---

#### 3.3 Tabel: `planting_locations`
**Deskripsi:** Lokasi penanaman/lahan

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| planting_location_id | VARCHAR(36) | Primary Key |
| name | VARCHAR(255) | Nama lokasi |
| location_id | VARCHAR(36) | FK → locations |
| location_type | ENUM | 'sawah', 'kebun', 'greenhouse', 'polybag', 'lainnya' |
| planting_format | ENUM | 'bedengan', 'baris', 'kotak', 'acak', 'lainnya' |
| planting_format_custom | VARCHAR(255) | Format kustom |
| num_beds | INT | Jumlah bedengan |
| bed_length_m | DECIMAL(10,2) | Panjang bedengan (m) |
| bed_width_m | DECIMAL(10,2) | Lebar bedengan (m) |
| map_size | DECIMAL(10,2) | Luas lahan |
| light_condition | VARCHAR(255) | Kondisi cahaya |
| description | TEXT | Deskripsi |
| location_summary | TEXT | Ringkasan lokasi |
| administrative_address | TEXT | Alamat administratif |
| google_maps_link | VARCHAR(500) | Link Google Maps |
| land_status | VARCHAR(255) | Status lahan |
| ownership_status | VARCHAR(255) | Status kepemilikan |
| water_source | VARCHAR(255) | Sumber air |
| soil_type | VARCHAR(255) | Jenis tanah |
| elevation_masl | INT | Ketinggian (mdpl) |
| primary_photo_path | VARCHAR(255) | Foto utama |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `location_id` → `locations.location_id`

---

#### 3.4 Tabel: `plantings`
**Deskripsi:** Data penanaman

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| planting_id | VARCHAR(36) | Primary Key |
| plant_id | VARCHAR(36) | FK → plants |
| planting_location_id | VARCHAR(36) | FK → planting_locations |
| bed_label | VARCHAR(255) | Label bedengan |
| days_to_emerge | INT | Hari hingga muncul |
| spacing_between_plants | DECIMAL(10,2) | Jarak antar tanaman |
| spacing_between_rows | DECIMAL(10,2) | Jarak antar baris |
| sowing_depth | DECIMAL(10,2) | Kedalaman tanam |
| avg_height | DECIMAL(10,2) | Tinggi rata-rata |
| start_method | ENUM | Metode mulai tanam |
| germination_stage | ENUM | Tahap perkecambahan |
| seeds_per_hole | INT | Benih per lubang |
| light_profile | ENUM | Profil cahaya |
| soil_condition | ENUM | Kondisi tanah |
| planting_detail | TEXT | Detail penanaman |
| pruning_detail | TEXT | Detail pemangkasan |
| perennial | TINYINT(1) | Tanaman tahunan |
| days_to_flower | INT | Hari hingga berbunga |
| days_to_harvest | INT | Hari hingga panen |
| harvest_window_days | INT | Jendela panen (hari) |
| expected_loss_rate | DECIMAL(5,2) | Tingkat kehilangan (%) |
| harvest_unit | ENUM | Satuan panen |
| expected_yield_per_hectare | DECIMAL(15,2) | Hasil per hektar |
| quantity_planted | DECIMAL(15,2) | Jumlah ditanam |
| planted_at | DATE | Tanggal tanam |
| is_completed | TINYINT(1) | Status selesai |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `plant_id` → `plants.plant_id`
- `planting_location_id` → `planting_locations.planting_location_id`

---

#### 3.5 Tabel: `harvests`
**Deskripsi:** Data panen

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| harvest_id | VARCHAR(36) | Primary Key |
| plant_id | VARCHAR(36) | FK → plants |
| planting_id | VARCHAR(36) | FK → plantings |
| planting_location_id | VARCHAR(36) | FK → planting_locations |
| harvested_at | DATE | Tanggal panen |
| batch_no | VARCHAR(255) | Nomor batch |
| note | TEXT | Catatan |
| source | VARCHAR(255) | Sumber |
| quality | VARCHAR(255) | Kualitas |
| quantity | DECIMAL(15,2) | Jumlah |
| unit | VARCHAR(50) | Satuan |
| loss_quantity | DECIMAL(15,2) | Jumlah kehilangan |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `plant_id` → `plants.plant_id`
- `planting_id` → `plantings.planting_id`
- `planting_location_id` → `planting_locations.planting_location_id`

---

#### 3.6 Tabel: `planting_losses`
**Deskripsi:** Data kehilangan/kegagalan tanam

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| planting_loss_id | VARCHAR(36) | Primary Key |
| planting_id | VARCHAR(36) | FK → plantings |
| loss_date | DATE | Tanggal kehilangan |
| loss_amount | DECIMAL(15,2) | Jumlah hilang |
| loss_reason | VARCHAR(255) | Alasan |
| description | TEXT | Deskripsi |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `planting_id` → `plantings.planting_id`

---

#### 3.7 Tabel: `plant_notes`
**Deskripsi:** Catatan tanaman

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| plant_note_id | VARCHAR(36) | Primary Key |
| plant_id | VARCHAR(36) | FK → plants |
| description | TEXT | Deskripsi |
| note_date | DATE | Tanggal catatan |
| keywords | VARCHAR(255) | Kata kunci |
| attachment_path | VARCHAR(255) | Path lampiran |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `plant_id` → `plants.plant_id`

---

#### 3.8 Tabel: `plant_photos`
**Deskripsi:** Foto tanaman

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| plant_photo_id | VARCHAR(36) | Primary Key |
| plant_id | VARCHAR(36) | FK → plants |
| file_path | VARCHAR(255) | Path file |
| file_name | VARCHAR(255) | Nama file |
| file_size | INT | Ukuran file (bytes) |
| mime_type | VARCHAR(100) | MIME type |
| description | TEXT | Deskripsi |
| taken_at | TIMESTAMP | Waktu diambil |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `plant_id` → `plants.plant_id`

---

#### 3.9 Tabel: `planting_location_notes`
**Deskripsi:** Catatan lokasi penanaman

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| planting_location_note_id | VARCHAR(36) | Primary Key |
| planting_location_id | VARCHAR(36) | FK → planting_locations |
| title | VARCHAR(255) | Judul |
| description | TEXT | Deskripsi |
| note_date | DATE | Tanggal catatan |
| keywords | VARCHAR(255) | Kata kunci |
| attachment_path | VARCHAR(255) | Path lampiran |
| user_id | VARCHAR(36) | FK → users |
| assigned_to | JSON | Ditugaskan ke |
| read_by | JSON | Dibaca oleh |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `planting_location_id` → `planting_locations.planting_location_id`
- `user_id` → `users.user_id`

---

#### 3.10 Tabel: `planting_location_photos`
**Deskripsi:** Foto lokasi penanaman

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| planting_location_photo_id | VARCHAR(36) | Primary Key |
| planting_location_id | VARCHAR(36) | FK → planting_locations |
| file_path | VARCHAR(255) | Path file |
| file_name | VARCHAR(255) | Nama file |
| file_size | INT | Ukuran file (bytes) |
| mime_type | VARCHAR(100) | MIME type |
| description | TEXT | Deskripsi |
| taken_at | TIMESTAMP | Waktu diambil |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `planting_location_id` → `planting_locations.planting_location_id`

---

### 4. Treatment & Nutrient

#### 4.1 Tabel: `treatments`
**Deskripsi:** Data perlakuan/penanganan tanaman

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| treatment_id | VARCHAR(36) | Primary Key |
| planting_location_id | VARCHAR(36) | FK → planting_locations |
| planting_id | VARCHAR(36) | FK → plantings |
| treatment_type | VARCHAR(255) | Jenis perlakuan |
| treatment_name | VARCHAR(255) | Nama perlakuan |
| product_detail | TEXT | Detail produk |
| opt_institution | VARCHAR(255) | Institusi OPT |
| application_method | VARCHAR(255) | Metode aplikasi |
| withholding_period_days | INT | Masa tunggu (hari) |
| technician | VARCHAR(255) | Teknisi |
| description | TEXT | Deskripsi |
| treatment_date | DATE | Tanggal perlakuan |
| treatment_location | VARCHAR(255) | Lokasi perlakuan |
| amount_applied | DECIMAL(15,2) | Jumlah diaplikasikan |
| unit_measurement | VARCHAR(50) | Satuan |
| total_cost | DECIMAL(15,2) | Total biaya |
| keywords | VARCHAR(255) | Kata kunci |
| responsible_person_id | VARCHAR(36) | FK → users |
| institution_source | VARCHAR(255) | Sumber institusi |
| attachment | VARCHAR(255) | Lampiran |
| batch_number | VARCHAR(255) | Nomor batch |
| retreat_date | DATE | Tanggal pengulangan |
| edited_at | TIMESTAMP | Waktu diedit |
| edited_by | VARCHAR(36) | FK → users |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `planting_location_id` → `planting_locations.planting_location_id`
- `planting_id` → `plantings.planting_id`
- `responsible_person_id` → `users.user_id`
- `edited_by` → `users.user_id`

---

#### 4.2 Tabel: `nutrients`
**Deskripsi:** Data pemupukan/nutrisi

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| nutrient_id | VARCHAR(36) | Primary Key |
| planting_location_id | VARCHAR(36) | FK → planting_locations |
| planting_id | VARCHAR(36) | FK → plantings |
| product_applied | VARCHAR(255) | Produk diaplikasikan |
| amount_applied | DECIMAL(15,2) | Jumlah |
| unit | VARCHAR(50) | Satuan |
| application_method | VARCHAR(255) | Metode aplikasi |
| application_date | DATE | Tanggal aplikasi |
| total_cost | DECIMAL(15,2) | Total biaya |
| technician | VARCHAR(255) | Teknisi |
| description | TEXT | Deskripsi |
| institution_source | VARCHAR(255) | Sumber institusi |
| responsible_person_id | VARCHAR(36) | FK → users |
| attachment_id | VARCHAR(36) | FK → attachments |
| edited_at | TIMESTAMP | Waktu diedit |
| edited_by | VARCHAR(36) | FK → users |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `planting_location_id` → `planting_locations.planting_location_id`
- `planting_id` → `plantings.planting_id`
- `responsible_person_id` → `users.user_id`
- `attachment_id` → `attachments.attachment_id`
- `edited_by` → `users.user_id`

---

#### 4.3 Tabel: `expenses`
**Deskripsi:** Data pengeluaran/biaya

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| expense_id | VARCHAR(36) | Primary Key |
| planting_location_id | VARCHAR(36) | FK → planting_locations |
| expense_name | VARCHAR(255) | Nama pengeluaran |
| amount | DECIMAL(15,2) | Jumlah |
| expense_type | ENUM | 'treatment', 'nutrient', 'labor', 'equipment', 'other' |
| expense_date | DATE | Tanggal |
| treatment_id | VARCHAR(36) | FK → treatments |
| nutrient_id | VARCHAR(36) | FK → nutrients |
| responsible_person_id | VARCHAR(36) | FK → users |
| notes | TEXT | Catatan |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `planting_location_id` → `planting_locations.planting_location_id`
- `treatment_id` → `treatments.treatment_id`
- `nutrient_id` → `nutrients.nutrient_id`
- `responsible_person_id` → `users.user_id`

---

#### 4.4 Tabel: `attachments`
**Deskripsi:** Lampiran dokumen

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| attachment_id | VARCHAR(36) | Primary Key |
| planting_location_id | VARCHAR(36) | FK → planting_locations |
| title | VARCHAR(255) | Judul |
| description | TEXT | Deskripsi |
| attachment_date | DATE | Tanggal |
| file_path | VARCHAR(255) | Path file |
| file_name | VARCHAR(255) | Nama file |
| file_size | INT | Ukuran file |
| mime_type | VARCHAR(100) | MIME type |
| created_by | VARCHAR(36) | FK → users |
| edited_at | TIMESTAMP | Waktu diedit |
| edited_by | VARCHAR(36) | FK → users |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `planting_location_id` → `planting_locations.planting_location_id`
- `created_by` → `users.user_id`
- `edited_by` → `users.user_id`

---

### 5. Sertifikasi

#### 5.1 Tabel: `certifications`
**Deskripsi:** Data sertifikasi benih

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| certification_id | VARCHAR(36) | Primary Key |
| harvest_id | VARCHAR(36) | FK → harvests |
| planting_location_id | VARCHAR(36) | FK → planting_locations |
| plant_id | VARCHAR(36) | FK → plants |
| certification_status | VARCHAR(255) | Status sertifikasi |
| seed_class_requested | VARCHAR(255) | Kelas benih diminta |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `harvest_id` → `harvests.harvest_id`
- `planting_location_id` → `planting_locations.planting_location_id`
- `plant_id` → `plants.plant_id`

---

#### 5.2 Tabel: `certification_reports`
**Deskripsi:** Laporan pemeriksaan sertifikasi

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| certification_report_id | VARCHAR(36) | Primary Key |
| certification_id | VARCHAR(36) | FK → certifications |
| report_number_bpsb | VARCHAR(255) | Nomor laporan BPSB |
| report_date | DATE | Tanggal laporan |
| growing_season | VARCHAR(255) | Musim tanam |
| inspection_phase | VARCHAR(255) | Fase pemeriksaan |
| inspector_name | VARCHAR(255) | Nama pemeriksa |
| seed_class_result | VARCHAR(255) | Hasil kelas benih |
| isolation_north | DECIMAL(10,2) | Isolasi utara (m) |
| isolation_east | DECIMAL(10,2) | Isolasi timur (m) |
| isolation_south | DECIMAL(10,2) | Isolasi selatan (m) |
| isolation_west | DECIMAL(10,2) | Isolasi barat (m) |
| plant_characteristics_match | TINYINT(1) | Karakteristik sesuai |
| pest_disease_condition | TEXT | Kondisi hama penyakit |
| weed_condition | ENUM | 'bersih', 'sedikit', 'sedang', 'banyak' |
| population_per_sample | INT | Populasi per sampel |
| other_variety_mix_count | INT | Jumlah campuran varietas lain |
| other_variety_mix_percentage | DECIMAL(5,2) | Persentase campuran (%) |
| estimated_yield | DECIMAL(15,2) | Perkiraan hasil |
| conclusion | ENUM | 'lulus', 'tidak_lulus', 'perlu_pemeriksaan_ulang' |
| scan_file_path | VARCHAR(255) | Path file scan |
| expiry_date | DATE | Tanggal kadaluarsa |
| certified_seed_quantity | DECIMAL(15,2) | Jumlah benih bersertifikat |
| estimated_sale_price_per_kg | DECIMAL(15,2) | Perkiraan harga/kg |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `certification_id` → `certifications.certification_id`

---

### 6. Gudang & Inventori

#### 6.1 Tabel: `warehouses`
**Deskripsi:** Data gudang

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| warehouse_id | VARCHAR(36) | Primary Key |
| name | VARCHAR(255) | Nama gudang |
| internal_id | VARCHAR(50) | ID internal (UNIQUE) |
| tracking_type | ENUM | 'per_lot', 'aggregate' |
| description | TEXT | Deskripsi |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

---

#### 6.2 Tabel: `bins`
**Deskripsi:** Data rak/bin dalam gudang

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| bin_id | VARCHAR(36) | Primary Key |
| warehouse_id | VARCHAR(36) | FK → warehouses |
| name | VARCHAR(255) | Nama bin |
| internal_id | VARCHAR(50) | ID internal |
| max_capacity | DECIMAL(15,2) | Kapasitas maksimum |
| capacity_unit | VARCHAR(50) | Satuan kapasitas |
| description | TEXT | Deskripsi |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `warehouse_id` → `warehouses.warehouse_id`
**Unique:** (warehouse_id, internal_id)

---

#### 6.3 Tabel: `inventory_types`
**Deskripsi:** Jenis inventori/stok

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| inventory_type_id | VARCHAR(36) | Primary Key |
| category | VARCHAR(255) | Kategori |
| name | VARCHAR(255) | Nama |
| sku | VARCHAR(100) | SKU (UNIQUE) |
| electronic_id | VARCHAR(255) | ID elektronik |
| unit | VARCHAR(50) | Satuan |
| estimated_value_per_unit | DECIMAL(15,2) | Nilai per unit |
| estimated_kg_per_unit | DECIMAL(15,2) | Kg per unit |
| track_individual_lots | TINYINT(1) | Lacak lot individual |
| low_stock_threshold | DECIMAL(15,2) | Batas stok rendah |
| low_stock_unit | VARCHAR(50) | Satuan stok rendah |
| low_stock_email | VARCHAR(255) | Email notifikasi |
| description | TEXT | Deskripsi |
| responsible_person_id | VARCHAR(36) | FK → users |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `responsible_person_id` → `users.user_id`

---

#### 6.4 Tabel: `inventory_lots`
**Deskripsi:** Lot inventori

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| inventory_lot_id | VARCHAR(36) | Primary Key |
| inventory_type_id | VARCHAR(36) | FK → inventory_types |
| production_id | VARCHAR(255) | ID produksi |
| expiry_date | DATE | Tanggal kadaluarsa |
| status | ENUM | 'available', 'reserved', 'sold', 'expired', 'damaged' |
| initial_stock | DECIMAL(15,2) | Stok awal |
| current_stock | DECIMAL(15,2) | Stok saat ini |
| stock_unit | VARCHAR(50) | Satuan stok |
| warehouse_id | VARCHAR(36) | FK → warehouses |
| bin_id | VARCHAR(36) | FK → bins |
| certification_id | VARCHAR(36) | FK → certifications |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `inventory_type_id` → `inventory_types.inventory_type_id`
- `warehouse_id` → `warehouses.warehouse_id`
- `bin_id` → `bins.bin_id`
- `certification_id` → `certifications.certification_id`

---

#### 6.5 Tabel: `inventory_transactions`
**Deskripsi:** Transaksi inventori

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| inventory_transaction_id | VARCHAR(36) | Primary Key |
| inventory_type_id | VARCHAR(36) | FK → inventory_types |
| inventory_lot_id | VARCHAR(36) | FK → inventory_lots |
| transaction_type | ENUM | 'masuk', 'keluar', 'adjustment', 'transfer' |
| quantity | DECIMAL(15,2) | Jumlah |
| unit | VARCHAR(50) | Satuan |
| warehouse_id | VARCHAR(36) | FK → warehouses |
| bin_id | VARCHAR(36) | FK → bins |
| reason | VARCHAR(255) | Alasan |
| notes | TEXT | Catatan |
| user_id | VARCHAR(36) | FK → users |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `inventory_type_id` → `inventory_types.inventory_type_id`
- `inventory_lot_id` → `inventory_lots.inventory_lot_id`
- `warehouse_id` → `warehouses.warehouse_id`
- `bin_id` → `bins.bin_id`
- `user_id` → `users.user_id`

---

#### 6.6 Tabel: `inventory_type_warehouses`
**Deskripsi:** Relasi inventori-gudang

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| inventory_type_warehouse_id | VARCHAR(36) | Primary Key |
| inventory_type_id | VARCHAR(36) | FK → inventory_types |
| warehouse_id | VARCHAR(36) | FK → warehouses |
| bin_id | VARCHAR(36) | FK → bins |
| warehouse_only | TINYINT(1) | Hanya gudang |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `inventory_type_id` → `inventory_types.inventory_type_id`
- `warehouse_id` → `warehouses.warehouse_id`
- `bin_id` → `bins.bin_id`
**Unique:** (inventory_type_id, warehouse_id, bin_id)

---

#### 6.7 Tabel: `inventory_notes`
**Deskripsi:** Catatan inventori

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| inventory_note_id | VARCHAR(36) | Primary Key |
| inventory_type_id | VARCHAR(36) | FK → inventory_types |
| content | TEXT | Isi catatan |
| user_id | VARCHAR(36) | FK → users |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `inventory_type_id` → `inventory_types.inventory_type_id`
- `user_id` → `users.user_id`

---

#### 6.8 Tabel: `inventory_photos`
**Deskripsi:** Foto inventori

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| inventory_photo_id | VARCHAR(36) | Primary Key |
| inventory_type_id | VARCHAR(36) | FK → inventory_types |
| photo_path | VARCHAR(255) | Path foto |
| caption | TEXT | Keterangan |
| user_id | VARCHAR(36) | FK → users |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `inventory_type_id` → `inventory_types.inventory_type_id`
- `user_id` → `users.user_id`

---

#### 6.9 Tabel: `inventory_type_seeds`
**Deskripsi:** Relasi inventori-benih

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| inventory_type_seed_id | VARCHAR(36) | Primary Key |
| inventory_type_id | VARCHAR(36) | FK → inventory_types |
| plant_id | VARCHAR(36) | FK → plants |
| planting_location_id | VARCHAR(36) | FK → planting_locations |
| quantity | DECIMAL(15,2) | Jumlah |
| estimated_sale_price_per_kg | DECIMAL(15,2) | Harga per kg |
| expiry_date | DATE | Tanggal kadaluarsa |
| filled_by_user_id | VARCHAR(36) | FK → users |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `inventory_type_id` → `inventory_types.inventory_type_id`
- `plant_id` → `plants.plant_id`
- `planting_location_id` → `planting_locations.planting_location_id`
- `filled_by_user_id` → `users.user_id`

---

#### 6.10 Tabel: `inventory_type_certification_reports`
**Deskripsi:** Relasi inventori-laporan sertifikasi

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| inventory_type_certification_report_id | VARCHAR(36) | Primary Key |
| inventory_type_id | VARCHAR(36) | FK → inventory_types |
| certification_report_id | VARCHAR(36) | FK → certification_reports |
| quantity | DECIMAL(15,2) | Jumlah |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `inventory_type_id` → `inventory_types.inventory_type_id`
- `certification_report_id` → `certification_reports.certification_report_id`
**Unique:** (inventory_type_id, certification_report_id)

---

### 7. Penjualan

#### 7.1 Tabel: `sales`
**Deskripsi:** Data penjualan

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| sale_id | VARCHAR(36) | Primary Key |
| receipt_number | VARCHAR(50) | Nomor kwitansi (UNIQUE) |
| sale_date | DATE | Tanggal jual |
| buyer_name | VARCHAR(255) | Nama pembeli |
| buyer_contact | VARCHAR(255) | Kontak pembeli |
| total_amount | DECIMAL(15,2) | Total nominal |
| payment_method | ENUM | 'tunai', 'transfer', 'kredit' |
| payment_status | ENUM | 'pending', 'paid', 'partial', 'cancelled' |
| notes | TEXT | Catatan |
| user_id | VARCHAR(36) | FK → users |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `user_id` → `users.user_id`

---

#### 7.2 Tabel: `sale_items`
**Deskripsi:** Item penjualan

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| sale_item_id | VARCHAR(36) | Primary Key |
| sale_id | VARCHAR(36) | FK → sales |
| inventory_type_id | VARCHAR(36) | FK → inventory_types |
| inventory_lot_id | VARCHAR(36) | FK → inventory_lots |
| quantity | DECIMAL(15,2) | Jumlah |
| unit | VARCHAR(50) | Satuan |
| unit_price | DECIMAL(15,2) | Harga satuan |
| subtotal | DECIMAL(15,2) | Subtotal |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `sale_id` → `sales.sale_id`
- `inventory_type_id` → `inventory_types.inventory_type_id`
- `inventory_lot_id` → `inventory_lots.inventory_lot_id`

---

### 8. Relasi User-Lokasi

#### 8.1 Tabel: `user_planting_location_land_manager`
**Deskripsi:** Relasi pengelola lahan

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| user_planting_location_land_manager_id | VARCHAR(36) | Primary Key |
| planting_location_id | VARCHAR(36) | FK → planting_locations |
| user_id | VARCHAR(36) | FK → users |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `planting_location_id` → `planting_locations.planting_location_id`
- `user_id` → `users.user_id`
**Unique:** (planting_location_id, user_id)

---

#### 8.2 Tabel: `user_planting_location_land_worker`
**Deskripsi:** Relasi pekerja lahan

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| user_planting_location_land_worker_id | VARCHAR(36) | Primary Key |
| planting_location_id | VARCHAR(36) | FK → planting_locations |
| user_id | VARCHAR(36) | FK → users |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

**Relasi:**
- `planting_location_id` → `planting_locations.planting_location_id`
- `user_id` → `users.user_id`
**Unique:** (planting_location_id, user_id)

---

### 9. Landing Page

#### 9.1 Tabel: `landing_page_settings`
**Deskripsi:** Pengaturan halaman landing

| Kolom | Tipe Data | Keterangan |
|-------|-----------|------------|
| landing_page_setting_id | VARCHAR(36) | Primary Key |
| key_name | VARCHAR(100) | Nama key (UNIQUE) |
| value | TEXT | Nilai |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diupdate |

---

## Diagram Relasi

### Relasi Antar Modul

```
┌─────────────────┐
│  USERS/AUTH     │
└────────┬────────┘
         │
    ┌────┴────┐
    ▼         ▼
┌───────┐ ┌───────────────┐
│ TASKS │ │   LOCATIONS   │
└───┬───┘ └───────┬───────┘
    │             │
    │    ┌────────┴────────┐
    │    ▼                 ▼
    │ ┌────────────┐ ┌──────────┐
    │ │  PLANTING  │ │ WAREHOUSE│
    │ │  LOCATIONS │ │          │
    │ └─────┬──────┘ └────┬─────┘
    │       │             │
    │  ┌────┴────┐   ┌────┴────┐
    │  ▼         ▼   ▼         ▼
    │┌─────┐ ┌──────┐┌────┐ ┌─────────┐
    ││PLANT││HARVEST││BINS│ │INVENTORY│
    ││     ││       ││    │ │  TYPES  │
    │└──┬──┘└───┬───┘└────┘ └────┬────┘
    │   │       │                │
    │   │  ┌────┴────┐          ▼
    │   │  ▼         ▼    ┌──────────┐
    │   │┌────────────┐   │INVENTORY │
    └───┼┤CERTIFICATION│   │  LOTS    │
        │└─────┬──────┘   └────┬─────┘
        │      │               │
        │      ▼               ▼
        │ ┌──────────┐   ┌──────────┐
        │ │  CERT    │   │  SALES   │
        │ │ REPORTS  │   │          │
        │ └──────────┘   └──────────┘
        │
        ▼
    ┌─────────────┐
    │  TREATMENTS │
    │  NUTRIENTS  │
    │  EXPENSES   │
    └─────────────┘
```

---

## Catatan Penting

1. **Format ID Kustom**: Semua primary key (kecuali `personal_access_tokens`) menggunakan VARCHAR(36) dengan format Short Unique ID
2. **Timestamps**: Semua tabel memiliki `created_at` dan `updated_at` untuk audit trail
3. **Soft Delete**: Beberapa tabel mendukung soft delete (dapat dikonfigurasi di model Laravel)
4. **Foreign Key Constraints**: Menggunakan CASCADE atau SET NULL sesuai kebutuhan bisnis
5. **JSON Columns**: Digunakan untuk data fleksibel seperti checklist, collaborators, dll.

---

*Dokumentasi ini diperbarui secara otomatis dari struktur migrasi Laravel.*