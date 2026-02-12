# Struktur Database - Tabel inventory_type_seeds

## Deskripsi
Tabel untuk menghubungkan inventory type dengan tanaman dan lokasi. Menyimpan data stok benih yang ditambahkan dari sertifikasi atau manual.

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **inventory_type_seed_id** | VARCHAR | 36 | Primary Key (format: inventory_type_seed_id) |
| inventory_type_id | VARCHAR | 36 | Foreign Key → inventory_types.inventory_type_id (NOT NULL, CASCADE DELETE) |
| plant_id | VARCHAR | 36 | Foreign Key → plants.plant_id (NOT NULL, CASCADE DELETE) |
| planting_location_id | VARCHAR | 36 | Foreign Key → planting_locations.planting_location_id (NOT NULL, CASCADE DELETE) |
| certification_report_id | VARCHAR | 36 | Foreign Key → certification_reports.certification_report_id (NULL, SET NULL ON DELETE) |
| quantity | DECIMAL | 12,2 | Jumlah benih (NOT NULL) |
| seed_unit | VARCHAR | 50 | Satuan benih: 'kg', 'ton', 'gram', 'butir', 'pcs', 'batang' (NULL) |
| seed_unit_quantity | DECIMAL | 12,2 | Jumlah satuan benih (NULL) |
| seed_per_unit | DECIMAL | 12,2 | Jumlah benih per satuan (NULL) |
| seed_per_unit_unit | VARCHAR | 50 | Satuan benih per unit (NULL) |
| total_seed_quantity | DECIMAL | 12,2 | Total jumlah benih (NULL) |
| total_seed_unit | VARCHAR | 50 | Satuan total benih (NULL) |
| estimated_sale_price_per_kg | DECIMAL | 12,2 | Estimasi harga jual per kg dalam Rp (NULL) |
| expiry_date | DATE | - | Tanggal kadaluarsa (NULL) |
| filled_by_user_id | VARCHAR | 36 | Foreign Key → users.user_id (NULL, SET NULL ON DELETE) |
| edited_at | TIMESTAMP | - | Tanggal diubah (NULL) |
| edited_by | VARCHAR | 36 | Foreign Key → users.user_id (NULL, SET NULL ON DELETE) |
| storage_number | VARCHAR | 50 | Nomor penyimpanan (NULL) |
| report_type | VARCHAR | 50 | Jenis laporan BPSB (NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: 
  - `inventory_types` (inventory_type_id, CASCADE DELETE)
  - `plants` (plant_id, CASCADE DELETE)
  - `planting_locations` (planting_location_id, CASCADE DELETE)
  - `certification_reports` (certification_report_id, SET NULL ON DELETE)
  - `users` (filled_by_user_id, SET NULL ON DELETE)
  - `users` (edited_by, SET NULL ON DELETE)
- One-to-Many dengan: `seed_histories` (inventory_type_seed_id)

## Index
- PRIMARY KEY: `inventory_type_seed_id`
- FOREIGN KEY: `inventory_type_id` → `inventory_types.inventory_type_id` (CASCADE DELETE)
- FOREIGN KEY: `plant_id` → `plants.plant_id` (CASCADE DELETE)
- FOREIGN KEY: `planting_location_id` → `planting_locations.planting_location_id` (CASCADE DELETE)
- FOREIGN KEY: `certification_report_id` → `certification_reports.certification_report_id` (SET NULL ON DELETE)
- FOREIGN KEY: `filled_by_user_id` → `users.user_id` (SET NULL ON DELETE)
- FOREIGN KEY: `edited_by` → `users.user_id` (SET NULL ON DELETE)
