# Struktur Database - Tabel sale_items

## Deskripsi
Tabel untuk menyimpan item-item dalam penjualan

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **sale_item_id** | VARCHAR | 36 | Primary Key |
| sale_id | VARCHAR | 36 | Foreign Key → sales.sale_id |
| inventory_type_id | VARCHAR | 36 | Foreign Key → inventory_types.inventory_type_id |
| inventory_lot_id | VARCHAR | 36 | Foreign Key → inventory_lots.inventory_lot_id |
| quantity | DECIMAL | 15,2 | |
| unit | VARCHAR | 50 | |
| unit_price | DECIMAL | 15,2 | |
| subtotal | DECIMAL | 15,2 | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `sales` (sale_id, CASCADE DELETE), `inventory_types` (inventory_type_id), `inventory_lots` (inventory_lot_id)
- One-to-Many dengan: -

## Index
- PRIMARY KEY: `sale_item_id`
- FOREIGN KEY: `sale_id` → `sales.sale_id` (CASCADE DELETE)
- FOREIGN KEY: `inventory_type_id` → `inventory_types.inventory_type_id`
- FOREIGN KEY: `inventory_lot_id` → `inventory_lots.inventory_lot_id`
