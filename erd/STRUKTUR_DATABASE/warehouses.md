# Struktur Database - Tabel warehouses

## Deskripsi
Tabel untuk menyimpan data gudang

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **warehouse_id** | VARCHAR | 36 | Primary Key |
| responsible_person_id | VARCHAR | 36 | Foreign Key → users.user_id |
| name | VARCHAR | 50 | |
| internal_id | VARCHAR | 50 | |
| tracking_type | VARCHAR | 50 | |
| description | TEXT | | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `users` (responsible_person_id)
- One-to-Many dengan: `bins` (warehouse_id), `inventory_lots` (warehouse_id), `inventory_transactions` (warehouse_id), `inventory_type_warehouses` (warehouse_id)

## Index
- PRIMARY KEY: `warehouse_id`
- UNIQUE: `internal_id`
- FOREIGN KEY: `responsible_person_id` → `users.user_id` (SET NULL ON DELETE)
