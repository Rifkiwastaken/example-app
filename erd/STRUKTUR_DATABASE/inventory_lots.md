# Struktur Database - Tabel inventory_lots

## Deskripsi
Tabel untuk menyimpan lot/batch inventory

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **inventory_lot_id** | VARCHAR | 36 | Primary Key (format: inventory_lot_id) |
| inventory_type_id | VARCHAR | 36 | Foreign Key → inventory_types.inventory_type_id (NOT NULL, CASCADE DELETE) |
| production_id | VARCHAR | 255 | ID Lot dari Produksi (NULL) |
| expiry_date | DATE | - | Tanggal kadaluarsa (NULL) |
| status | ENUM | - | Status: 'tersedia','segera_kadaluarsa','kadaluarsa','habis' (NOT NULL, default: tersedia) |
| initial_stock | DECIMAL | 15,2 | Stok awal (NOT NULL) |
| current_stock | DECIMAL | 15,2 | Stok tersisa (NOT NULL) |
| stock_unit | VARCHAR | 50 | Satuan stok (NOT NULL) |
| warehouse_id | VARCHAR | 36 | Foreign Key → warehouses.warehouse_id (NULL, SET NULL ON DELETE) |
| bin_id | VARCHAR | 36 | Foreign Key → bins.bin_id (NULL, SET NULL ON DELETE) |
| certification_id | VARCHAR | 36 | Foreign Key → certifications.certification_id (NULL, SET NULL ON DELETE) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `inventory_types` (inventory_type_id, CASCADE DELETE), `warehouses` (warehouse_id, SET NULL ON DELETE), `bins` (bin_id, SET NULL ON DELETE), `certifications` (certification_id, SET NULL ON DELETE)
- One-to-Many dengan: `inventory_transactions` (inventory_lot_id), `sale_items` (inventory_lot_id)

## Index
- PRIMARY KEY: `inventory_lot_id`
- FOREIGN KEY: `inventory_type_id` → `inventory_types.inventory_type_id` (CASCADE DELETE)
- FOREIGN KEY: `warehouse_id` → `warehouses.warehouse_id` (SET NULL ON DELETE)
- FOREIGN KEY: `bin_id` → `bins.bin_id` (SET NULL ON DELETE)
- FOREIGN KEY: `certification_id` → `certifications.certification_id` (SET NULL ON DELETE)
