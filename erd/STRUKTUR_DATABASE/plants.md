# Struktur Database - Tabel plants

## Deskripsi
Tabel untuk menyimpan data tanaman

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **plant_id** | VARCHAR | 36 | Primary Key |
| plant_type_id | VARCHAR | 36 | Foreign Key → plant_types.plant_type_id |
| planting_location_id | VARCHAR | 36 | Foreign Key → planting_locations.planting_location_id |
| name | VARCHAR | 50 | |
| variety | VARCHAR | 50 | |
| status | ENUM | | |
| progress | INT | | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `plant_types` (plant_type_id), `planting_locations` (planting_location_id)
- One-to-Many dengan: `plantings` (plant_id), `harvests` (plant_id), `certifications` (plant_id), `plant_notes` (plant_id), `plant_photos` (plant_id), `inventory_types` (plant_id), `inventory_type_seeds` (plant_id)

## Index
- PRIMARY KEY: `plant_id`
- FOREIGN KEY: `plant_type_id` → `plant_types.plant_type_id`
- FOREIGN KEY: `planting_location_id` → `planting_locations.planting_location_id` (SET NULL ON DELETE)
