# Struktur Database - Tabel nutrients

## Deskripsi
Tabel untuk menyimpan data nutrisi/pupuk

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **nutrient_id** | VARCHAR | 36 | Primary Key |
| planting_location_id | VARCHAR | 36 | Foreign Key → planting_locations.planting_location_id |
| planting_id | VARCHAR | 36 | Foreign Key → plantings.planting_id |
| responsible_person_id | VARCHAR | 36 | Foreign Key → users.user_id |
| attachment_id | VARCHAR | 36 | Foreign Key → attachments.attachment_id |
| nutrient_name | VARCHAR | 50 | |
| product_applied | VARCHAR | 50 | |
| amount_applied | DECIMAL | 15,2 | |
| unit | VARCHAR | 50 | |
| application_method | VARCHAR | 50 | |
| application_date | DATE | | |
| total_cost | DECIMAL | 15,2 | |
| technician | VARCHAR | 50 | |
| description | TEXT | | |
| institution_source | VARCHAR | 50 | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `planting_locations` (planting_location_id, CASCADE DELETE), `users` (responsible_person_id), `attachments` (attachment_id)
- One-to-Many dengan: `expenses` (nutrient_id)

## Index
- PRIMARY KEY: `nutrient_id`
- FOREIGN KEY: `planting_location_id` → `planting_locations.planting_location_id`
- FOREIGN KEY: `planting_id` → `plantings.planting_id`
- FOREIGN KEY: `responsible_person_id` → `users.user_id`
- FOREIGN KEY: `attachment_id` → `attachments.attachment_id`












