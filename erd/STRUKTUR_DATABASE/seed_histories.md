# Struktur Database - Tabel seed_histories

## Deskripsi
Tabel untuk menyimpan riwayat perubahan data benih

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **seed_history_id** | VARCHAR | 36 | Primary Key |
| inventory_type_id | VARCHAR | 36 | Foreign Key → inventory_types.inventory_type_id |
| inventory_type_seed_id | VARCHAR | 36 | Foreign Key → inventory_type_seeds.inventory_type_seed_id |
| user_id | VARCHAR | 36 | Foreign Key → users.user_id |
| action | VARCHAR | 50 | |
| description | TEXT | | |
| old_data | JSON | | |
| new_data | JSON | | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `inventory_type_seeds` (inventory_type_seed_id, SET NULL ON DELETE), `users` (user_id, CASCADE DELETE)

## Index
- PRIMARY KEY: `seed_history_id`
- FOREIGN KEY: `inventory_type_seed_id` → `inventory_type_seeds.inventory_type_seed_id` (SET NULL ON DELETE)
- FOREIGN KEY: `user_id` → `users.user_id` (CASCADE DELETE)

## Catatan
- inventory_type_seed_id diubah menjadi nullable (SET NULL ON DELETE) agar riwayat tetap tersimpan ketika seed dihapus
- inventory_type_id ditambahkan untuk memudahkan query setelah seed dihapus
