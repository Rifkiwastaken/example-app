# Struktur Database - Tabel certification_reports

## Deskripsi
Tabel untuk menyimpan laporan pemeriksaan sertifikasi

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **certification_report_id** | VARCHAR | 36 | Primary Key |
| certification_id | VARCHAR | 36 | Foreign Key → certifications.certification_id |
| report_type | VARCHAR | 50 | |
| report_number_bpsb | VARCHAR | 50 | |
| report_date | DATE | | |
| growing_season | VARCHAR | 50 | |
| inspection_phase | VARCHAR | 50 | |
| inspector_name | VARCHAR | 50 | |
| reporter_name | VARCHAR | 50 | |
| seed_class_result | VARCHAR | 50 | |
| isolation_north | VARCHAR | 50 | |
| isolation_east | VARCHAR | 50 | |
| isolation_south | VARCHAR | 50 | |
| isolation_west | VARCHAR | 50 | |
| plant_characteristics_match | TINYINT(1) | | |
| pest_disease_condition | TEXT | | |
| weed_condition | ENUM | | |
| population_per_sample | INT | | |
| other_variety_mix_count | INT | | |
| other_variety_mix_percentage | DECIMAL | 5,2 | |
| estimated_yield | DECIMAL | 15,2 | |
| conclusion | ENUM | | |
| scan_file_path | VARCHAR | 500 | |
| expiry_date | DATE | | |
| certified_seed_quantity | DECIMAL | 12,2 | |
| certified_seed_unit | VARCHAR | 50 | |
| seed_unit | VARCHAR | 50 | |
| seed_unit_quantity | DECIMAL | 12,2 | |
| harvest_per_unit | DECIMAL | 12,2 | |
| harvest_per_unit_unit | VARCHAR | 50 | |
| estimated_sale_price_per_kg | DECIMAL | 15,2 | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `certifications` (certification_id, CASCADE DELETE)
- One-to-Many dengan: `inventory_type_certification_reports` (certification_report_id), `inventory_type_seeds` (certification_report_id)

## Index
- PRIMARY KEY: `certification_report_id`
- UNIQUE: `report_number_bpsb`
- FOREIGN KEY: `certification_id` → `certifications.certification_id` (CASCADE DELETE)
