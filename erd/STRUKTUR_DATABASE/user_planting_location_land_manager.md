# Struktur Database - Tabel user_planting_location_land_manager

## Deskripsi
Tabel pivot untuk relasi many-to-many antara user dan planting_location (sebagai manager)

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **user_planting_location_land_manager_id** | VARCHAR | 36 | Primary Key |
| planting_location_id | VARCHAR | 36 | Foreign Key → planting_locations.planting_location_id |
| user_id | VARCHAR | 36 | Foreign Key → users.user_id |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `planting_locations` (planting_location_id), `users` (user_id)

## Index
- PRIMARY KEY: `user_planting_location_land_manager_id`
- UNIQUE: (`planting_location_id`, `user_id`)
- FOREIGN KEY: `planting_location_id` → `planting_locations.planting_location_id`
- FOREIGN KEY: `user_id` → `users.user_id`












