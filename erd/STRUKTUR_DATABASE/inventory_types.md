# Struktur Database - Tabel inventory_types

## Deskripsi
Tabel untuk menyimpan tipe inventory (jenis benih/stok)

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **inventory_type_id** | VARCHAR | 36 | Primary Key (format: inventory_type_id) |
| category | VARCHAR | 255 | Kategori inventory (NOT NULL) |
| name | VARCHAR | 255 | Nama varietas/komoditas (NOT NULL) |
| sku | VARCHAR | 100 | SKU / ID Internal (UNIQUE, NOT NULL) |
| electronic_id | VARCHAR | 255 | Barcode/RFID (NULL) |
| unit | VARCHAR | 50 | Satuan: 'kg', 'ton', 'kantong', 'unit', 'polybag', 'pcs' (NOT NULL) |
| estimated_value_per_unit | DECIMAL | 15,2 | Estimasi nilai per unit dalam Rp (NULL) |
| estimated_kg_per_unit | DECIMAL | 10,2 | Estimasi kg per unit (NULL) |
| track_individual_lots | BOOLEAN | - | Lacak lot individual (NOT NULL) |
| low_stock_threshold | DECIMAL | 10,2 | Peringatan stok rendah (NULL) |
| low_stock_unit | VARCHAR | 50 | Unit untuk peringatan stok (NULL) |
| low_stock_email | VARCHAR | 255 | Email untuk peringatan stok (NULL) |
| description | TEXT | - | Deskripsi (NULL) |
| plant_id | VARCHAR | 36 | Foreign Key → plants.plant_id (NULL, SET NULL ON DELETE) |
| responsible_person_id | VARCHAR | 36 | Foreign Key → users.user_id (NULL, SET NULL ON DELETE) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `plants` (plant_id), `users` (responsible_person_id)
- One-to-Many dengan: `inventory_lots` (inventory_type_id), `inventory_transactions` (inventory_type_id), `sale_items` (inventory_type_id), `inventory_type_seeds` (inventory_type_id), `inventory_type_certification_reports` (inventory_type_id), `inventory_notes` (inventory_type_id), `inventory_photos` (inventory_type_id), `inventory_type_warehouses` (inventory_type_id)

## Index
- PRIMARY KEY: `inventory_type_id`
- UNIQUE: `sku`
- FOREIGN KEY: `plant_id` → `plants.plant_id` (SET NULL ON DELETE)
- FOREIGN KEY: `responsible_person_id` → `users.user_id` (SET NULL ON DELETE)
