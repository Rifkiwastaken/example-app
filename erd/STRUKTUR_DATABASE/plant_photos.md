# Struktur Database - Tabel plant_photos

## Deskripsi
Tabel untuk menyimpan foto tanaman

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **plant_photo_id** | VARCHAR | 36 | Primary Key |
| plant_id | VARCHAR | 36 | Foreign Key → plants.plant_id |
| file_path | VARCHAR | 50 | |
| file_name | VARCHAR | 50 | |
| file_size | INT | | |
| mime_type | VARCHAR | 100 | |
| description | TEXT | | |
| taken_at | TIMESTAMP | | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `plants` (plant_id, CASCADE DELETE)

## Index
- PRIMARY KEY: `plant_photo_id`
- FOREIGN KEY: `plant_id` → `plants.plant_id`












