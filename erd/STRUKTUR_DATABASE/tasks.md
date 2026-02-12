# Struktur Database - Tabel tasks

## Deskripsi
Tabel untuk menyimpan data tugas

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **id** | BIGINT | - | Primary Key, Auto Increment |
| title | VARCHAR | 50 | Judul tugas (NOT NULL) |
| description | TEXT | - | Deskripsi tugas (NULL) |
| priority | ENUM | - | Prioritas: 'low', 'medium', 'high', 'highest' (NOT NULL, Default: 'medium') |
| status | ENUM | - | Status: 'pending', 'in_progress', 'completed' (NOT NULL, Default: 'pending') |
| due_date | DATE | - | Tanggal tenggat (NOT NULL) |
| location | VARCHAR | 50 | Lokasi tugas (NULL) |
| location_tagged | BOOLEAN | - | Apakah ditandai lokasi (NOT NULL, Default: false) |
| planting_location_id | BIGINT | - | Foreign Key → planting_locations.id (NULL) |
| planting_id | BIGINT | - | Foreign Key → plantings.id (NULL) |
| created_by | BIGINT | - | Foreign Key → users.id (NULL) |
| last_edited | TIMESTAMP | - | Terakhir diedit (NULL) |
| task_color | VARCHAR | 50 | Warna tugas (NULL) |
| collaborators | JSON | - | Kolaborator (NULL) |
| repeats | VARCHAR | 50 | Pengulangan (NULL) |
| hours_spent | DECIMAL | 8,2 | Jam yang dihabiskan (NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `planting_locations` (planting_location_id, CASCADE DELETE), `plantings` (planting_id, CASCADE DELETE), `users` (created_by)

## Index
- PRIMARY KEY: `id`
- FOREIGN KEY: `planting_location_id` → `planting_locations.id` (CASCADE DELETE)
- FOREIGN KEY: `planting_id` → `plantings.id` (CASCADE DELETE)
- FOREIGN KEY: `created_by` → `users.id`












