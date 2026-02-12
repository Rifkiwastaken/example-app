# Struktur Database - Tabel certifications

## Deskripsi
Tabel untuk menyimpan data sertifikasi benih

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **certification_id** | VARCHAR | 36 | Primary Key (format: certification_id) |
| harvest_id | VARCHAR | 36 | Foreign Key → harvests.harvest_id (NOT NULL, CASCADE DELETE) |
| certification_status | VARCHAR | 255 | Status sertifikasi: 'dalam_proses', 'lulus', 'tidak_lulus', 'selesai' (NULL) |
| seed_class_requested | VARCHAR | 255 | Kelas benih yang diminta: 'BS', 'BP', 'BR' (NULL) |
| plant_id | VARCHAR | 36 | Foreign Key → plants.plant_id (NULL) |
| planting_location_id | VARCHAR | 36 | Foreign Key → planting_locations.planting_location_id (NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `harvests` (harvest_id, CASCADE DELETE), `plants` (plant_id), `planting_locations` (planting_location_id)
- One-to-Many dengan: `certification_reports` (certification_id), `inventory_lots` (certification_id)

## Index
- PRIMARY KEY: `certification_id`
- FOREIGN KEY: `harvest_id` → `harvests.harvest_id` (CASCADE DELETE)
- FOREIGN KEY: `plant_id` → `plants.plant_id`
- FOREIGN KEY: `planting_location_id` → `planting_locations.planting_location_id`
