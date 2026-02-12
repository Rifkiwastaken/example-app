# Struktur Database - Tabel inventory_photos

## Deskripsi
Tabel untuk menyimpan foto inventory

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **id** | BIGINT | - | Primary Key, Auto Increment |
| inventory_type_id | BIGINT | - | Foreign Key → inventory_types.id (NOT NULL) |
| photo_path | VARCHAR | 50 | Path foto (NOT NULL) |
| caption | VARCHAR | 50 | Keterangan foto (NULL) |
| user_id | BIGINT | - | Foreign Key → users.id (NOT NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `inventory_types` (inventory_type_id, CASCADE DELETE), `users` (user_id, CASCADE DELETE)

## Index
- PRIMARY KEY: `id`
- FOREIGN KEY: `inventory_type_id` → `inventory_types.id` (CASCADE DELETE)
- FOREIGN KEY: `user_id` → `users.id` (CASCADE DELETE)












