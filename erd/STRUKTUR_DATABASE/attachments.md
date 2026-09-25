# Struktur Database - Tabel attachments

## Deskripsi
Tabel untuk menyimpan attachment/lampiran

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **attachment_id** | VARCHAR | 36 | Primary Key |
| planting_location_id | VARCHAR | 36 | Foreign Key → planting_locations.planting_location_id |
| planting_id | VARCHAR | 36 | Foreign Key → plantings.planting_id |
| created_by | VARCHAR | 36 | Foreign Key → users.user_id |
| edited_by | VARCHAR | 36 | Foreign Key → users.user_id |
| title | VARCHAR | 50 | |
| description | TEXT | | |
| attachment_date | DATE | | |
| file_path | VARCHAR | 50 | |
| file_name | VARCHAR | 50 | |
| file_size | INT | | |
| mime_type | VARCHAR | 50 | |
| edited_at | TIMESTAMP | | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `planting_locations` (planting_location_id, CASCADE DELETE), `users` (created_by, CASCADE DELETE), `users` (edited_by, SET NULL ON DELETE)
- One-to-Many dengan: `treatments` (attachment_id), `nutrients` (attachment_id)

## Index
- PRIMARY KEY: `attachment_id`
- FOREIGN KEY: `planting_location_id` → `planting_locations.planting_location_id`
- FOREIGN KEY: `planting_id` → `plantings.planting_id`
- FOREIGN KEY: `created_by` → `users.user_id`
- FOREIGN KEY: `edited_by` → `users.user_id`












