# Struktur Database - Tabel inventory_type_warehouses

## Deskripsi
Tabel untuk menghubungkan inventory type dengan warehouse dan bin

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **inventory_type_warehouse_id** | VARCHAR | 36 | Primary Key |
| inventory_type_id | VARCHAR | 36 | Foreign Key → inventory_types.inventory_type_id |
| warehouse_id | VARCHAR | 36 | Foreign Key → warehouses.warehouse_id |
| bin_id | VARCHAR | 36 | Foreign Key → bins.bin_id |
| warehouse_only | TINYINT(1) | | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `inventory_types` (inventory_type_id), `warehouses` (warehouse_id), `bins` (bin_id)

## Index
- PRIMARY KEY: `inventory_type_warehouse_id`
- UNIQUE: (`inventory_type_id`, `warehouse_id`, `bin_id`)
- FOREIGN KEY: `inventory_type_id` → `inventory_types.inventory_type_id`
- FOREIGN KEY: `warehouse_id` → `warehouses.warehouse_id`
- FOREIGN KEY: `bin_id` → `bins.bin_id`












