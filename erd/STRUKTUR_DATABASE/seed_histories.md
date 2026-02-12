# Struktur Database - Tabel seed_histories

## Deskripsi
Tabel untuk menyimpan riwayat perubahan data benih

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **seed_history_id** | VARCHAR | 36 | Primary Key (format: seed_history_id) |
| inventory_type_seed_id | VARCHAR | 36 | Foreign Key → inventory_type_seeds.inventory_type_seed_id (NULL, SET NULL ON DELETE) |
| inventory_type_id | VARCHAR | 36 | Referensi inventory_type untuk query (NULL) |
| action | VARCHAR | 50 | Aksi: 'create', 'update', 'delete', 'reduce_stock' (NOT NULL) |
| description | TEXT | - | Deskripsi aksi (NULL) |
| old_data | JSON | - | Data sebelum perubahan (NULL) |
| new_data | JSON | - | Data setelah perubahan (NULL) |
| user_id | VARCHAR | 36 | Foreign Key → users.user_id (NOT NULL, CASCADE DELETE) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `inventory_type_seeds` (inventory_type_seed_id, SET NULL ON DELETE), `users` (user_id, CASCADE DELETE)

## Index
- PRIMARY KEY: `seed_history_id`
- FOREIGN KEY: `inventory_type_seed_id` → `inventory_type_seeds.inventory_type_seed_id` (SET NULL ON DELETE)
- FOREIGN KEY: `user_id` → `users.user_id` (CASCADE DELETE)

## Catatan
- inventory_type_seed_id diubah menjadi nullable (SET NULL ON DELETE) agar riwayat tetap tersimpan ketika seed dihapus
- inventory_type_id ditambahkan untuk memudahkan query setelah seed dihapus
