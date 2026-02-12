# Struktur Database - Tabel inventory_notes

## Deskripsi
Tabel untuk menyimpan catatan inventory

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **id** | BIGINT | - | Primary Key, Auto Increment |
| inventory_type_id | BIGINT | - | Foreign Key → inventory_types.id (NOT NULL) |
| content | TEXT | - | Isi catatan (NOT NULL) |
| user_id | BIGINT | - | Foreign Key → users.id (NOT NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `inventory_types` (inventory_type_id, CASCADE DELETE), `users` (user_id, CASCADE DELETE)

## Index
- PRIMARY KEY: `id`
- FOREIGN KEY: `inventory_type_id` → `inventory_types.id` (CASCADE DELETE)
- FOREIGN KEY: `user_id` → `users.id` (CASCADE DELETE)












