# Struktur Database - Tabel planting_location_photos

## Deskripsi
Tabel untuk menyimpan foto lokasi penanaman

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **planting_location_photo_id** | VARCHAR | 36 | Primary Key |
| planting_location_id | VARCHAR | 36 | Foreign Key → planting_locations.planting_location_id |
| planting_id | VARCHAR | 36 | Foreign Key → plantings.planting_id |
| file_path | VARCHAR | 255 | |
| file_name | VARCHAR | 255 | |
| file_size | INT | | |
| mime_type | VARCHAR | 100 | |
| description | TEXT | | |
| taken_at | TIMESTAMP | | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `planting_locations` (planting_location_id), `plantings` (planting_id)

## Index
- PRIMARY KEY: `planting_location_photo_id`
- FOREIGN KEY: `planting_location_id` → `planting_locations.planting_location_id`
- FOREIGN KEY: `planting_id` → `plantings.planting_id`












