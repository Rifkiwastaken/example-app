# Struktur Database - Tabel attachments

## Deskripsi
Tabel untuk menyimpan attachment/lampiran

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **id** | BIGINT | - | Primary Key, Auto Increment |
| planting_location_id | BIGINT | - | Foreign Key → planting_locations.id (NOT NULL) |
| title | VARCHAR | 50 | Judul attachment (NOT NULL) |
| description | TEXT | - | Deskripsi (NULL) |
| attachment_date | DATE | - | Tanggal attachment (NOT NULL) |
| file_path | VARCHAR | 50 | Path file (NOT NULL) |
| file_name | VARCHAR | 50 | Nama file (NULL) |
| file_size | BIGINT | - | Ukuran file dalam bytes (NULL) |
| mime_type | VARCHAR | 50 | Tipe MIME (NULL) |
| created_by | BIGINT | - | Foreign Key → users.id (NOT NULL) |
| edited_at | TIMESTAMP | - | Tanggal diedit (NULL) |
| edited_by | BIGINT | - | Foreign Key → users.id (NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `planting_locations` (planting_location_id, CASCADE DELETE), `users` (created_by, CASCADE DELETE), `users` (edited_by, SET NULL ON DELETE)
- One-to-Many dengan: `treatments` (attachment_id), `nutrients` (attachment_id)

## Index
- PRIMARY KEY: `id`
- FOREIGN KEY: `planting_location_id` → `planting_locations.id` (CASCADE DELETE)
- FOREIGN KEY: `created_by` → `users.id` (CASCADE DELETE)
- FOREIGN KEY: `edited_by` → `users.id` (SET NULL ON DELETE)












