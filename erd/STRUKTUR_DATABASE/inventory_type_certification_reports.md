# Struktur Database - Tabel inventory_type_certification_reports

## Deskripsi
Tabel untuk menghubungkan inventory type dengan laporan sertifikasi

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **inventory_type_certification_report_id** | VARCHAR | 36 | Primary Key |
| inventory_type_id | VARCHAR | 36 | Foreign Key → inventory_types.inventory_type_id |
| certification_report_id | VARCHAR | 36 | Foreign Key → certification_reports.certification_report_id |
| quantity | DECIMAL | 12,2 | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `inventory_types` (inventory_type_id, CASCADE DELETE), `certification_reports` (certification_report_id, CASCADE DELETE)

## Index
- PRIMARY KEY: `inventory_type_certification_report_id`
- UNIQUE: (`inventory_type_id`, `certification_report_id`)
- FOREIGN KEY: `inventory_type_id` → `inventory_types.inventory_type_id` (CASCADE DELETE)
- FOREIGN KEY: `certification_report_id` → `certification_reports.certification_report_id` (CASCADE DELETE)












