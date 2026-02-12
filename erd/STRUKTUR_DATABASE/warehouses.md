# Struktur Database - Tabel warehouses

## Deskripsi
Tabel untuk menyimpan data gudang

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **warehouse_id** | VARCHAR | 36 | Primary Key (format: warehouse_id) |
| name | VARCHAR | 255 | Nama gudang (NOT NULL) |
| internal_id | VARCHAR | 50 | ID internal gudang (UNIQUE, NOT NULL) |
| tracking_type | ENUM | - | Tipe tracking: 'bin_separated', 'warehouse_only' (NOT NULL) |
| description | TEXT | - | Deskripsi gudang (NULL) |
| responsible_person_id | VARCHAR | 36 | Foreign Key → users.user_id (NULL, SET NULL ON DELETE) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `users` (responsible_person_id)
- One-to-Many dengan: `bins` (warehouse_id), `inventory_lots` (warehouse_id), `inventory_transactions` (warehouse_id), `inventory_type_warehouses` (warehouse_id)

## Index
- PRIMARY KEY: `warehouse_id`
- UNIQUE: `internal_id`
- FOREIGN KEY: `responsible_person_id` → `users.user_id` (SET NULL ON DELETE)
