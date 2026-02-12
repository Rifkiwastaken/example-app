# Struktur Database - Tabel treatments

## Deskripsi
Tabel untuk menyimpan data perawatan/pengobatan tanaman

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **id** | BIGINT | - | Primary Key, Auto Increment |
| planting_location_id | BIGINT | - | Foreign Key → planting_locations.id (NOT NULL) |
| treatment_type | VARCHAR | 50 | Tipe perawatan: 'Blight', 'Pupuk', 'Jamur', dll (NOT NULL) |
| product_detail | VARCHAR | 50 | Detail produk (NULL) |
| opt_institution | VARCHAR | 50 | Institusi OPT (NULL) |
| application_method | VARCHAR | 50 | Metode aplikasi: 'Granul', 'Semprot', 'Lainnya' (NOT NULL) |
| withholding_period_days | INT | - | Periode penahanan dalam hari (NULL) |
| technician | VARCHAR | 50 | Teknisi (NULL) |
| description | TEXT | - | Deskripsi perawatan (NULL) |
| treatment_date | DATE | - | Tanggal perawatan (NOT NULL) |
| treatment_location | VARCHAR | 50 | Lokasi perawatan: 'batang', 'daun', 'pohon' (NULL) |
| amount_applied | DECIMAL | 10,2 | Jumlah yang diaplikasikan (NULL) |
| unit_measurement | VARCHAR | 50 | Satuan pengukuran (NULL) |
| total_cost | DECIMAL | 10,2 | Total biaya dalam Rp (NULL) |
| record_expense | BOOLEAN | - | Catat sebagai pengeluaran (NOT NULL) |
| keywords | VARCHAR | 50 | Kata kunci (NULL) |
| nutrient_name | VARCHAR | 50 | Nama nutrisi (NULL) |
| institution_source | VARCHAR | 50 | Sumber institusi (NULL) |
| responsible_person_id | BIGINT | - | Foreign Key → users.id (NULL) |
| attachment_id | BIGINT | - | Foreign Key → attachments.id (NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `planting_locations` (planting_location_id, CASCADE DELETE), `users` (responsible_person_id), `attachments` (attachment_id)
- One-to-Many dengan: `expenses` (treatment_id)

## Index
- PRIMARY KEY: `id`
- FOREIGN KEY: `planting_location_id` → `planting_locations.id` (CASCADE DELETE)
- FOREIGN KEY: `responsible_person_id` → `users.id`
- FOREIGN KEY: `attachment_id` → `attachments.id`












