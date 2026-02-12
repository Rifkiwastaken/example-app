# Struktur Database - Tabel user_planting_location_land_worker

## Deskripsi
Tabel pivot untuk relasi many-to-many antara user dan planting_location (sebagai worker)

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **id** | BIGINT | - | Primary Key, Auto Increment |
| planting_location_id | BIGINT | - | Foreign Key → planting_locations.id (NOT NULL) |
| user_id | BIGINT | - | Foreign Key → users.id (NOT NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `planting_locations` (planting_location_id, CASCADE DELETE), `users` (user_id, CASCADE DELETE)

## Index
- PRIMARY KEY: `id`
- UNIQUE: (`planting_location_id`, `user_id`)
- FOREIGN KEY: `planting_location_id` → `planting_locations.id` (CASCADE DELETE)
- FOREIGN KEY: `user_id` → `users.id` (CASCADE DELETE)












