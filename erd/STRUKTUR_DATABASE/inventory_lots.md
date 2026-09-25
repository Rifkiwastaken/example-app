# Struktur Database - Tabel inventory_lots

## Deskripsi
Tabel untuk menyimpan lot/batch inventory

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **inventory_lot_id** | VARCHAR | 36 | Primary Key |
| inventory_type_id | VARCHAR | 36 | Foreign Key → inventory_types.inventory_type_id |
| warehouse_id | VARCHAR | 36 | Foreign Key → warehouses.warehouse_id |
| bin_id | VARCHAR | 36 | Foreign Key → bins.bin_id |
| certification_id | VARCHAR | 36 | Foreign Key → certifications.certification_id |
| production_id | VARCHAR | 50 | |
| expiry_date | DATE | | |
| status | ENUM | | |
| initial_stock | DECIMAL | 15,2 | |
| current_stock | DECIMAL | 15,2 | |
| stock_unit | VARCHAR | 50 | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `inventory_types` (inventory_type_id, CASCADE DELETE), `warehouses` (warehouse_id, SET NULL ON DELETE), `bins` (bin_id, SET NULL ON DELETE), `certifications` (certification_id, SET NULL ON DELETE)
- One-to-Many dengan: `inventory_transactions` (inventory_lot_id), `sale_items` (inventory_lot_id)

## Index
- PRIMARY KEY: `inventory_lot_id`
- FOREIGN KEY: `inventory_type_id` → `inventory_types.inventory_type_id` (CASCADE DELETE)
- FOREIGN KEY: `warehouse_id` → `warehouses.warehouse_id` (SET NULL ON DELETE)
- FOREIGN KEY: `bin_id` → `bins.bin_id` (SET NULL ON DELETE)
- FOREIGN KEY: `certification_id` → `certifications.certification_id` (SET NULL ON DELETE)
