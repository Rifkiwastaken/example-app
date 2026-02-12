# Struktur Database - Tabel plants

## Deskripsi
Tabel untuk menyimpan data tanaman

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **plant_id** | VARCHAR | 36 | Primary Key (format: plant_id) |
| name | VARCHAR | 255 | Nama tanaman (NOT NULL) |
| plant_type_id | VARCHAR | 36 | Foreign Key → plant_types.plant_type_id (NOT NULL) |
| variety | VARCHAR | 255 | Varietas tanaman (NULL) |
| status | ENUM | - | Status: 'perencanaan', 'ditanam', 'dipanen', 'selesai' (NOT NULL, Default: 'perencanaan') |
| progress | TINYINT | - | Progress 0-100 (NULL) |
| planting_location_id | VARCHAR | 36 | Foreign Key → planting_locations.planting_location_id (NULL, SET NULL ON DELETE) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `plant_types` (plant_type_id), `planting_locations` (planting_location_id)
- One-to-Many dengan: `plantings` (plant_id), `harvests` (plant_id), `certifications` (plant_id), `plant_notes` (plant_id), `plant_photos` (plant_id), `inventory_types` (plant_id), `inventory_type_seeds` (plant_id)

## Index
- PRIMARY KEY: `plant_id`
- FOREIGN KEY: `plant_type_id` → `plant_types.plant_type_id`
- FOREIGN KEY: `planting_location_id` → `planting_locations.planting_location_id` (SET NULL ON DELETE)
