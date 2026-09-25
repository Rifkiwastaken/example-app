# Struktur Database - Tabel inventory_transactions

## Deskripsi
Tabel untuk menyimpan transaksi inventory

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **inventory_transaction_id** | VARCHAR | 36 | Primary Key |
| inventory_type_id | VARCHAR | 36 | Foreign Key → inventory_types.inventory_type_id |
| inventory_lot_id | VARCHAR | 36 | Foreign Key → inventory_lots.inventory_lot_id |
| warehouse_id | VARCHAR | 36 | Foreign Key → warehouses.warehouse_id |
| bin_id | VARCHAR | 36 | Foreign Key → bins.bin_id |
| user_id | VARCHAR | 36 | Foreign Key → users.user_id |
| transaction_type | ENUM | | |
| quantity | DECIMAL | 15,2 | |
| unit | VARCHAR | 50 | |
| reason | VARCHAR | 50 | |
| notes | TEXT | | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `inventory_types` (inventory_type_id, CASCADE DELETE), `inventory_lots` (inventory_lot_id, SET NULL ON DELETE), `warehouses` (warehouse_id, SET NULL ON DELETE), `bins` (bin_id, SET NULL ON DELETE), `users` (user_id, CASCADE DELETE)

## Index
- PRIMARY KEY: `inventory_transaction_id`
- FOREIGN KEY: `inventory_type_id` → `inventory_types.inventory_type_id` (CASCADE DELETE)
- FOREIGN KEY: `inventory_lot_id` → `inventory_lots.inventory_lot_id` (SET NULL ON DELETE)
- FOREIGN KEY: `warehouse_id` → `warehouses.warehouse_id` (SET NULL ON DELETE)
- FOREIGN KEY: `bin_id` → `bins.bin_id` (SET NULL ON DELETE)
- FOREIGN KEY: `user_id` → `users.user_id` (CASCADE DELETE)
