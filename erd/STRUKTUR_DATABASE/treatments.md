# Struktur Database - Tabel treatments

## Deskripsi
Tabel untuk menyimpan data perawatan/pengobatan tanaman

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **treatment_id** | VARCHAR | 36 | Primary Key |
| planting_location_id | VARCHAR | 36 | Foreign Key → planting_locations.planting_location_id |
| planting_id | VARCHAR | 36 | Foreign Key → plantings.planting_id |
| responsible_person_id | VARCHAR | 36 | Foreign Key → users.user_id |
| treatment_type | VARCHAR | 50 | |
| treatment_name | VARCHAR | 50 | |
| product_detail | TEXT | | |
| application_method | VARCHAR | 50 | |
| withholding_period_days | INT | | |
| technician | VARCHAR | 50 | |
| description | TEXT | | |
| treatment_date | DATE | | |
| treatment_location | VARCHAR | 50 | |
| amount_applied | DECIMAL | 15,2 | |
| unit_measurement | VARCHAR | 50 | |
| total_cost | DECIMAL | 15,2 | |
| keywords | VARCHAR | 50 | |
| institution_source | VARCHAR | 50 | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `planting_locations` (planting_location_id, CASCADE DELETE), `users` (responsible_person_id), `attachments` (attachment_id)
- One-to-Many dengan: `expenses` (treatment_id)

## Index
- PRIMARY KEY: `treatment_id`
- FOREIGN KEY: `planting_location_id` → `planting_locations.planting_location_id`
- FOREIGN KEY: `planting_id` → `plantings.planting_id`
- FOREIGN KEY: `responsible_person_id` → `users.user_id`












