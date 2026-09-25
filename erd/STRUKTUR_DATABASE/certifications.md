# Struktur Database - Tabel certifications

## Deskripsi
Tabel untuk menyimpan data sertifikasi benih

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **certification_id** | VARCHAR | 36 | Primary Key |
| harvest_id | VARCHAR | 36 | Foreign Key → harvests.harvest_id |
| plant_id | VARCHAR | 36 | Foreign Key → plants.plant_id |
| planting_location_id | VARCHAR | 36 | Foreign Key → planting_locations.planting_location_id |
| certification_status | VARCHAR | 50 | |
| seed_class_requested | VARCHAR | 50 | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `harvests` (harvest_id, CASCADE DELETE), `plants` (plant_id), `planting_locations` (planting_location_id)
- One-to-Many dengan: `certification_reports` (certification_id), `inventory_lots` (certification_id)

## Index
- PRIMARY KEY: `certification_id`
- FOREIGN KEY: `harvest_id` → `harvests.harvest_id` (CASCADE DELETE)
- FOREIGN KEY: `plant_id` → `plants.plant_id`
- FOREIGN KEY: `planting_location_id` → `planting_locations.planting_location_id`
