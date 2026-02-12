# Struktur Database - Tabel expenses

## Deskripsi
Tabel untuk menyimpan data pengeluaran

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **id** | BIGINT | - | Primary Key, Auto Increment |
| planting_location_id | BIGINT | - | Foreign Key → planting_locations.id (NOT NULL) |
| expense_name | VARCHAR | 50 | Nama pengeluaran (NOT NULL) |
| amount | DECIMAL | 10,2 | Jumlah pengeluaran dalam Rp (NOT NULL) |
| expense_type | ENUM | - | Tipe: 'perawatan', 'nutrisi' (NOT NULL) |
| expense_date | DATE | - | Tanggal pengeluaran (NOT NULL) |
| treatment_id | BIGINT | - | Foreign Key → treatments.id (NULL) |
| nutrient_id | BIGINT | - | Foreign Key → nutrients.id (NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `planting_locations` (planting_location_id, CASCADE DELETE), `treatments` (treatment_id, CASCADE DELETE), `nutrients` (nutrient_id, CASCADE DELETE)

## Index
- PRIMARY KEY: `id`
- FOREIGN KEY: `planting_location_id` → `planting_locations.id` (CASCADE DELETE)
- FOREIGN KEY: `treatment_id` → `treatments.id` (CASCADE DELETE)
- FOREIGN KEY: `nutrient_id` → `nutrients.id` (CASCADE DELETE)












