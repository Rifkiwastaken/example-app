# Struktur Database - Tabel inventory_transactions

## Deskripsi
Tabel untuk menyimpan transaksi inventory

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **inventory_transaction_id** | VARCHAR | 36 | Primary Key (format: inventory_transaction_id) |
| inventory_type_id | VARCHAR | 36 | Foreign Key → inventory_types.inventory_type_id (NOT NULL, CASCADE DELETE) |
| inventory_lot_id | VARCHAR | 36 | Foreign Key → inventory_lots.inventory_lot_id (NULL, SET NULL ON DELETE) |
| transaction_type | ENUM | - | Tipe: 'stok_masuk','stok_keluar','penyesuaian_tambah','penyesuaian_kurang','distribusi','pindah_lokasi' (NOT NULL) |
| quantity | DECIMAL | 15,2 | Jumlah transaksi (NOT NULL) |
| unit | VARCHAR | 50 | Satuan (NOT NULL) |
| warehouse_id | VARCHAR | 36 | Foreign Key → warehouses.warehouse_id (NULL, SET NULL ON DELETE) |
| bin_id | VARCHAR | 36 | Foreign Key → bins.bin_id (NULL, SET NULL ON DELETE) |
| reason | VARCHAR | 255 | Alasan: 'Rusak', 'Hilang', dll (NULL) |
| notes | TEXT | - | Catatan transaksi (NULL) |
| user_id | VARCHAR | 36 | Foreign Key → users.user_id (NOT NULL, CASCADE DELETE) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `inventory_types` (inventory_type_id, CASCADE DELETE), `inventory_lots` (inventory_lot_id, SET NULL ON DELETE), `warehouses` (warehouse_id, SET NULL ON DELETE), `bins` (bin_id, SET NULL ON DELETE), `users` (user_id, CASCADE DELETE)

## Index
- PRIMARY KEY: `inventory_transaction_id`
- FOREIGN KEY: `inventory_type_id` → `inventory_types.inventory_type_id` (CASCADE DELETE)
- FOREIGN KEY: `inventory_lot_id` → `inventory_lots.inventory_lot_id` (SET NULL ON DELETE)
- FOREIGN KEY: `warehouse_id` → `warehouses.warehouse_id` (SET NULL ON DELETE)
- FOREIGN KEY: `bin_id` → `bins.bin_id` (SET NULL ON DELETE)
- FOREIGN KEY: `user_id` → `users.user_id` (CASCADE DELETE)
