# Struktur Database - Tabel inventory_type_seeds

## Deskripsi
Tabel untuk menghubungkan inventory type dengan tanaman dan lokasi. Menyimpan data stok benih yang ditambahkan dari sertifikasi atau manual.

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **inventory_type_seed_id** | VARCHAR | 36 | Primary Key |
| inventory_type_id | VARCHAR | 36 | Foreign Key → inventory_types.inventory_type_id |
| plant_id | VARCHAR | 36 | Foreign Key → plants.plant_id |
| planting_location_id | VARCHAR | 36 | Foreign Key → planting_locations.planting_location_id |
| certification_report_id | VARCHAR | 36 | Foreign Key → certification_reports.certification_report_id |
| filled_by_user_id | VARCHAR | 36 | Foreign Key → users.user_id |
| edited_by | VARCHAR | 36 | Foreign Key → users.user_id |
| quantity | DECIMAL | 12,2 | |
| seed_unit | VARCHAR | 50 | |
| seed_unit_quantity | DECIMAL | 12,2 | |
| seed_per_unit | DECIMAL | 12,2 | |
| seed_per_unit_unit | VARCHAR | 50 | |
| total_seed_quantity | DECIMAL | 12,2 | |
| total_seed_unit | VARCHAR | 50 | |
| estimated_sale_price_per_kg | DECIMAL | 12,2 | |
| expiry_date | DATE | | |
| storage_number | VARCHAR | 50 | |
| report_type | VARCHAR | 50 | |
| edited_at | TIMESTAMP | | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

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
