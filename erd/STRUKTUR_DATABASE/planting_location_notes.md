# Struktur Database - Tabel planting_location_notes

## Deskripsi
Tabel untuk menyimpan catatan lokasi penanaman

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **id** | BIGINT | - | Primary Key, Auto Increment |
| planting_location_id | BIGINT | - | Foreign Key → planting_locations.id (NOT NULL) |
| title | VARCHAR | 50 | Judul catatan (NULL) |
| description | TEXT | - | Deskripsi catatan (NOT NULL) |
| note_date | DATE | - | Tanggal catatan (NOT NULL) |
| keywords | VARCHAR | 50 | Kata kunci (NULL) |
| attachment_path | VARCHAR | 50 | Path attachment (NULL) |
| user_id | BIGINT | - | Foreign Key → users.id (NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `planting_locations` (planting_location_id, CASCADE DELETE), `users` (user_id, SET NULL ON DELETE)

## Index
- PRIMARY KEY: `id`
- FOREIGN KEY: `planting_location_id` → `planting_locations.id` (CASCADE DELETE)
- FOREIGN KEY: `user_id` → `users.id` (SET NULL ON DELETE)












