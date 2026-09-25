# Struktur Database - Tabel plantings

## Deskripsi
Tabel untuk menyimpan data penanaman

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **planting_id** | VARCHAR | 36 | Primary Key |
| plant_id | VARCHAR | 36 | Foreign Key → plants.plant_id |
| planting_location_id | VARCHAR | 36 | Foreign Key → planting_locations.planting_location_id |
| bed_label | VARCHAR | 50 | |
| planting_batch_number | VARCHAR | 50 | |
| days_to_emerge | INT | | |
| spacing_between_plants | VARCHAR | 50 | |
| spacing_between_rows | VARCHAR | 50 | |
| sowing_depth | VARCHAR | 50 | |
| avg_height | VARCHAR | 50 | |
| start_method | ENUM | | |
| germination_stage | ENUM | | |
| seeds_per_hole | INT | | |
| light_profile | ENUM | | |
| soil_condition | ENUM | | |
| planting_detail | TEXT | | |
| pruning_detail | TEXT | | |
| perennial | TINYINT(1) | | |
| days_to_flower | INT | | |
| days_to_harvest | INT | | |
| harvest_window_days | INT | | |
| expected_loss_rate | VARCHAR | 50 | |
| harvest_unit | ENUM | | |
| planting_format | VARCHAR | 50 | |
| planting_format_custom | VARCHAR | 50 | |
| expected_yield_per_hectare | DECIMAL | 12,2 | |
| quantity_planted | INT | | |
| planted_at | DATE | | |
| area_ha | DECIMAL | 10,2 | |
| estimated_harvest_date | DATE | | |
| is_completed | TINYINT(1) | | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `plants` (plant_id, CASCADE DELETE), `planting_locations` (planting_location_id, SET NULL ON DELETE)
- One-to-Many dengan: `harvests` (planting_id), `planting_losses` (planting_id), `tasks` (planting_id)

## Index
- PRIMARY KEY: `planting_id`
- FOREIGN KEY: `plant_id` → `plants.plant_id` (CASCADE DELETE)
- FOREIGN KEY: `planting_location_id` → `planting_locations.planting_location_id` (SET NULL ON DELETE)

## Catatan
- Primary key dan foreign key menggunakan VARCHAR(36) (custom ID).
