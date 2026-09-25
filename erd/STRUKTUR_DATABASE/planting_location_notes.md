# Struktur Database - Tabel planting_location_notes

## Deskripsi
Tabel untuk menyimpan catatan lokasi penanaman

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **planting_location_note_id** | VARCHAR | 36 | Primary Key |
| planting_location_id | VARCHAR | 36 | Foreign Key → planting_locations.planting_location_id |
| planting_id | VARCHAR | 36 | Foreign Key → plantings.planting_id |
| user_id | VARCHAR | 36 | Foreign Key → users.user_id |
| title | VARCHAR | 255 | |
| description | TEXT | | |
| note_date | DATE | | |
| keywords | VARCHAR | 255 | |
| attachment_path | VARCHAR | 255 | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `planting_locations` (planting_location_id), `plantings` (planting_id), `users` (user_id)

## Index
- PRIMARY KEY: `planting_location_note_id`
- FOREIGN KEY: `planting_location_id` → `planting_locations.planting_location_id`
- FOREIGN KEY: `planting_id` → `plantings.planting_id`
- FOREIGN KEY: `user_id` → `users.user_id`












