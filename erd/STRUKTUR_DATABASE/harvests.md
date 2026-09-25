# Struktur Database - Tabel harvests

## Deskripsi
Tabel untuk menyimpan data hasil panen

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **harvest_id** | VARCHAR | 36 | Primary Key |
| plant_id | VARCHAR | 36 | Foreign Key → plants.plant_id |
| planting_id | VARCHAR | 36 | Foreign Key → plantings.planting_id |
| planting_location_id | VARCHAR | 36 | Foreign Key → planting_locations.planting_location_id |
| harvested_at | DATE | | |
| batch_no | VARCHAR | 50 | |
| note | TEXT | | |
| source | VARCHAR | 50 | |
| quality | VARCHAR | 50 | |
| quantity | DECIMAL | 15,2 | |
| unit | VARCHAR | 50 | |
| loss_quantity | DECIMAL | 15,2 | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `plants` (plant_id, CASCADE DELETE), `plantings` (planting_id, NULL ON DELETE), `planting_locations` (planting_location_id, NULL ON DELETE)
- One-to-Many dengan: `certifications` (harvest_id)

## Index
- PRIMARY KEY: `harvest_id`
- FOREIGN KEY: `plant_id` → `plants.plant_id`
- FOREIGN KEY: `planting_id` → `plantings.planting_id`
- FOREIGN KEY: `planting_location_id` → `planting_locations.planting_location_id`












