# Struktur Database - Tabel harvests

## Deskripsi
Tabel untuk menyimpan data hasil panen

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **id** | BIGINT | - | Primary Key, Auto Increment |
| plant_id | BIGINT | - | Foreign Key → plants.id (NOT NULL) |
| planting_id | BIGINT | - | Foreign Key → plantings.id (NULL) |
| planting_location_id | BIGINT | - | Foreign Key → planting_locations.id (NULL) |
| harvested_at | DATE | - | Tanggal panen (NOT NULL) |
| batch_no | VARCHAR | 50 | Nomor batch (NULL) |
| note | TEXT | - | Catatan panen (NULL) |
| source | VARCHAR | 50 | Sumber (bed/lokasi) (NULL) |
| quality | VARCHAR | 50 | Kualitas hasil panen (NULL) |
| quantity | DECIMAL | 12,2 | Jumlah hasil panen (NOT NULL) |
| unit | VARCHAR | 50 | Satuan (NULL) |
| loss_quantity | DECIMAL | 12,2 | Jumlah kerugian (NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `plants` (plant_id, CASCADE DELETE), `plantings` (planting_id, NULL ON DELETE), `planting_locations` (planting_location_id, NULL ON DELETE)
- One-to-Many dengan: `certifications` (harvest_id)

## Index
- PRIMARY KEY: `id`
- FOREIGN KEY: `plant_id` → `plants.id` (CASCADE DELETE)
- FOREIGN KEY: `planting_id` → `plantings.id` (NULL ON DELETE)
- FOREIGN KEY: `planting_location_id` → `planting_locations.id` (NULL ON DELETE)












