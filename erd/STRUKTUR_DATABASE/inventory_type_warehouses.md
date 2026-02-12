# Struktur Database - Tabel inventory_type_warehouses

## Deskripsi
Tabel untuk menghubungkan inventory type dengan warehouse dan bin

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **id** | BIGINT | - | Primary Key, Auto Increment |
| inventory_type_id | BIGINT | - | Foreign Key → inventory_types.id (NOT NULL) |
| warehouse_id | BIGINT | - | Foreign Key → warehouses.id (NOT NULL) |
| bin_id | BIGINT | - | Foreign Key → bins.id (NULL) |
| warehouse_only | BOOLEAN | - | Hanya di lokasi gudang tanpa bin (NOT NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `inventory_types` (inventory_type_id, CASCADE DELETE), `warehouses` (warehouse_id, CASCADE DELETE), `bins` (bin_id, CASCADE DELETE)

## Index
- PRIMARY KEY: `id`
- UNIQUE: (`inventory_type_id`, `warehouse_id`, `bin_id`)
- FOREIGN KEY: `inventory_type_id` → `inventory_types.id` (CASCADE DELETE)
- FOREIGN KEY: `warehouse_id` → `warehouses.id` (CASCADE DELETE)
- FOREIGN KEY: `bin_id` → `bins.id` (CASCADE DELETE)












