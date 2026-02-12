# Struktur Database - Tabel planting_location_photos

## Deskripsi
Tabel untuk menyimpan foto lokasi penanaman

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **id** | BIGINT | - | Primary Key, Auto Increment |
| planting_location_id | BIGINT | - | Foreign Key → planting_locations.id (NOT NULL) |
| file_path | VARCHAR | 50 | Path file (NOT NULL) |
| file_name | VARCHAR | 50 | Nama file (NULL) |
| file_size | INT | - | Ukuran file dalam bytes (NULL) |
| mime_type | VARCHAR | 50 | Tipe MIME (NULL) |
| description | TEXT | - | Deskripsi foto (NULL) |
| taken_at | DATE | - | Tanggal foto diambil (NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `planting_locations` (planting_location_id, CASCADE DELETE)

## Index
- PRIMARY KEY: `id`
- FOREIGN KEY: `planting_location_id` → `planting_locations.id` (CASCADE DELETE)












