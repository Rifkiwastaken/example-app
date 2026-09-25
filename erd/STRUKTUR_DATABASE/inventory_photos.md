# Struktur Database - Tabel inventory_photos

## Deskripsi
Tabel untuk menyimpan foto inventory

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **inventory_photo_id** | VARCHAR | 36 | Primary Key |
| inventory_type_id | VARCHAR | 36 | Foreign Key → inventory_types.inventory_type_id |
| user_id | VARCHAR | 36 | Foreign Key → users.user_id |
| photo_path | VARCHAR | 255 | |
| caption | TEXT | | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `inventory_types` (inventory_type_id), `users` (user_id)

## Index
- PRIMARY KEY: `inventory_photo_id`
- FOREIGN KEY: `inventory_type_id` → `inventory_types.inventory_type_id`
- FOREIGN KEY: `user_id` → `users.user_id`












