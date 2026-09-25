# Struktur Database - Tabel bins

## Deskripsi
Tabel untuk menyimpan data bin (rak/kompartemen) di gudang

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **bin_id** | VARCHAR | 36 | Primary Key |
| warehouse_id | VARCHAR | 36 | Foreign Key → warehouses.warehouse_id |
| name | VARCHAR | 50 | |
| internal_id | VARCHAR | 50 | |
| max_capacity | DECIMAL | 15,2 | |
| capacity_unit | VARCHAR | 50 | |
| description | TEXT | | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `warehouses` (warehouse_id, CASCADE DELETE)
- One-to-Many dengan: `inventory_lots` (bin_id), `inventory_transactions` (bin_id), `inventory_type_warehouses` (bin_id)

## Index
- PRIMARY KEY: `bin_id`
- FOREIGN KEY: `warehouse_id` → `warehouses.warehouse_id` (CASCADE DELETE)
