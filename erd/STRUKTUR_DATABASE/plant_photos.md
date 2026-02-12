# Struktur Database - Tabel plant_photos

## Deskripsi
Tabel untuk menyimpan foto tanaman

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **id** | BIGINT | - | Primary Key, Auto Increment |
| plant_id | BIGINT | - | Foreign Key → plants.id (NOT NULL) |
| file_path | VARCHAR | 50 | Path file (NOT NULL) |
| file_name | VARCHAR | 50 | Nama file (NOT NULL) |
| file_size | BIGINT | - | Ukuran file dalam bytes (NOT NULL) |
| mime_type | VARCHAR | 50 | Tipe MIME (NOT NULL) |
| description | TEXT | - | Deskripsi foto (NULL) |
| taken_at | DATETIME | - | Tanggal foto diambil (NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `plants` (plant_id, CASCADE DELETE)

## Index
- PRIMARY KEY: `id`
- FOREIGN KEY: `plant_id` → `plants.id` (CASCADE DELETE)












