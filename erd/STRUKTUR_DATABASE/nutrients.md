# Struktur Database - Tabel nutrients

## Deskripsi
Tabel untuk menyimpan data nutrisi/pupuk

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **id** | BIGINT | - | Primary Key, Auto Increment |
| planting_location_id | BIGINT | - | Foreign Key → planting_locations.id (NOT NULL) |
| product_applied | VARCHAR | 50 | Produk yang diaplikasikan (NOT NULL) |
| amount_applied | DECIMAL | 10,2 | Jumlah yang diaplikasikan (NOT NULL) |
| application_method | VARCHAR | 50 | Metode aplikasi: 'Penyebaran', 'Kompos', 'Granul', dll (NOT NULL) |
| application_date | DATE | - | Tanggal aplikasi (NOT NULL) |
| nitrogen_n | DECIMAL | 8,2 | Nitrogen (N) (NULL) |
| phosphorus_p | DECIMAL | 8,2 | Fosfor (P) (NULL) |
| potassium_k | DECIMAL | 8,2 | Kalium (K) (NULL) |
| magnesium_mg | DECIMAL | 8,2 | Magnesium (Mg) (NULL) |
| sulfur_s | DECIMAL | 8,2 | Sulfur (S) (NULL) |
| calcium_ca | DECIMAL | 8,2 | Kalsium (Ca) (NULL) |
| boron_b | DECIMAL | 8,2 | Boron (B) (NULL) |
| copper_cu | DECIMAL | 8,2 | Tembaga (Cu) (NULL) |
| iron_fe | DECIMAL | 8,2 | Besi (Fe) (NULL) |
| manganese_mn | DECIMAL | 8,2 | Mangan (Mn) (NULL) |
| zinc_zn | DECIMAL | 8,2 | Seng (Zn) (NULL) |
| description | TEXT | - | Deskripsi (NULL) |
| unit | VARCHAR | 50 | Satuan (NULL) |
| nutrient_name | VARCHAR | 50 | Nama nutrisi (NULL) |
| institution_source | VARCHAR | 50 | Sumber institusi (NULL) |
| responsible_person_id | BIGINT | - | Foreign Key → users.id (NULL) |
| attachment_id | BIGINT | - | Foreign Key → attachments.id (NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `planting_locations` (planting_location_id, CASCADE DELETE), `users` (responsible_person_id), `attachments` (attachment_id)
- One-to-Many dengan: `expenses` (nutrient_id)

## Index
- PRIMARY KEY: `id`
- FOREIGN KEY: `planting_location_id` → `planting_locations.id` (CASCADE DELETE)
- FOREIGN KEY: `responsible_person_id` → `users.id`
- FOREIGN KEY: `attachment_id` → `attachments.id`












