# Struktur Database - Tabel inventory_types

## Deskripsi
Tabel untuk menyimpan tipe inventory (jenis benih/stok)

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **inventory_type_id** | VARCHAR | 36 | Primary Key |
| plant_id | VARCHAR | 36 | Foreign Key → plants.plant_id |
| responsible_person_id | VARCHAR | 36 | Foreign Key → users.user_id |
| category | VARCHAR | 50 | |
| name | VARCHAR | 50 | |
| sku | VARCHAR | 100 | |
| electronic_id | VARCHAR | 50 | |
| unit | VARCHAR | 50 | |
| estimated_value_per_unit | DECIMAL | 15,2 | |
| estimated_kg_per_unit | DECIMAL | 10,2 | |
| track_individual_lots | TINYINT(1) | | |
| low_stock_threshold | DECIMAL | 10,2 | |
| low_stock_unit | VARCHAR | 50 | |
| low_stock_email | VARCHAR | 50 | |
| description | TEXT | | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `plants` (plant_id), `users` (responsible_person_id)
- One-to-Many dengan: `inventory_lots` (inventory_type_id), `inventory_transactions` (inventory_type_id), `sale_items` (inventory_type_id), `inventory_type_seeds` (inventory_type_id), `inventory_type_certification_reports` (inventory_type_id), `inventory_notes` (inventory_type_id), `inventory_photos` (inventory_type_id), `inventory_type_warehouses` (inventory_type_id)

## Index
- PRIMARY KEY: `inventory_type_id`
- UNIQUE: `sku`
- FOREIGN KEY: `plant_id` → `plants.plant_id` (SET NULL ON DELETE)
- FOREIGN KEY: `responsible_person_id` → `users.user_id` (SET NULL ON DELETE)
