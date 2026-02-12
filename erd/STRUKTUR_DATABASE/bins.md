# Struktur Database - Tabel bins

## Deskripsi
Tabel untuk menyimpan data bin (rak/kompartemen) di gudang

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **bin_id** | VARCHAR | 36 | Primary Key (format: bin_id) |
| warehouse_id | VARCHAR | 36 | Foreign Key → warehouses.warehouse_id (NOT NULL, CASCADE DELETE) |
| name | VARCHAR | 255 | Nama bin (NOT NULL) |
| internal_id | VARCHAR | 50 | ID internal bin (NULL) |
| max_capacity | DECIMAL | 15,2 | Kapasitas maksimal (NULL) |
| capacity_unit | VARCHAR | 50 | Satuan kapasitas (NULL) |
| description | TEXT | - | Deskripsi bin (NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `warehouses` (warehouse_id, CASCADE DELETE)
- One-to-Many dengan: `inventory_lots` (bin_id), `inventory_transactions` (bin_id), `inventory_type_warehouses` (bin_id)

## Index
- PRIMARY KEY: `bin_id`
- FOREIGN KEY: `warehouse_id` → `warehouses.warehouse_id` (CASCADE DELETE)
