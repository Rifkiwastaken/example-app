# Struktur Database - Tabel expenses

## Deskripsi
Tabel untuk menyimpan data pengeluaran

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **expense_id** | VARCHAR | 36 | Primary Key |
| planting_location_id | VARCHAR | 36 | Foreign Key → planting_locations.planting_location_id |
| treatment_id | VARCHAR | 36 | Foreign Key → treatments.treatment_id |
| nutrient_id | VARCHAR | 36 | Foreign Key → nutrients.nutrient_id |
| responsible_person_id | VARCHAR | 36 | Foreign Key → users.user_id |
| expense_name | VARCHAR | 50 | |
| amount | DECIMAL | 15,2 | |
| expense_type | VARCHAR | 50 | |
| expense_date | DATE | | |
| notes | TEXT | | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `planting_locations` (planting_location_id, CASCADE DELETE), `treatments` (treatment_id, CASCADE DELETE), `nutrients` (nutrient_id, CASCADE DELETE)

## Index
- PRIMARY KEY: `expense_id`
- FOREIGN KEY: `planting_location_id` → `planting_locations.planting_location_id`
- FOREIGN KEY: `treatment_id` → `treatments.treatment_id`
- FOREIGN KEY: `nutrient_id` → `nutrients.nutrient_id`
- FOREIGN KEY: `responsible_person_id` → `users.user_id`












