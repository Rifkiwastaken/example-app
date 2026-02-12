# Struktur Database - Tabel sale_items

## Deskripsi
Tabel untuk menyimpan item-item dalam penjualan

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **sale_item_id** | VARCHAR | 36 | Primary Key (format: sale_item_id) |
| sale_id | VARCHAR | 36 | Foreign Key → sales.sale_id (NOT NULL, CASCADE DELETE) |
| inventory_type_id | VARCHAR | 36 | Foreign Key → inventory_types.inventory_type_id (NOT NULL) |
| inventory_lot_id | VARCHAR | 36 | Foreign Key → inventory_lots.inventory_lot_id (NULL) |
| quantity | DECIMAL | 15,2 | Jumlah jual (NOT NULL) |
| unit | VARCHAR | 50 | Satuan (NOT NULL) |
| unit_price | DECIMAL | 15,2 | Harga satuan dalam Rp (NOT NULL) |
| subtotal | DECIMAL | 15,2 | Subtotal dalam Rp (NOT NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `sales` (sale_id, CASCADE DELETE), `inventory_types` (inventory_type_id), `inventory_lots` (inventory_lot_id)
- One-to-Many dengan: -

## Index
- PRIMARY KEY: `sale_item_id`
- FOREIGN KEY: `sale_id` → `sales.sale_id` (CASCADE DELETE)
- FOREIGN KEY: `inventory_type_id` → `inventory_types.inventory_type_id`
- FOREIGN KEY: `inventory_lot_id` → `inventory_lots.inventory_lot_id`
