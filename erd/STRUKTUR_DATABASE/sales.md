# Struktur Database - Tabel sales

## Deskripsi
Tabel untuk menyimpan data penjualan benih. Menyimpan informasi pembeli, lokasi sebaran, dan detail pembayaran.

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **sale_id** | VARCHAR | 36 | Primary Key |
| user_id | VARCHAR | 36 | Foreign Key → users.user_id |
| planting_location_id | VARCHAR | 36 | Foreign Key → planting_locations.planting_location_id |
| receipt_number | VARCHAR | 50 | |
| sale_date | DATE | | |
| buyer_name | VARCHAR | 50 | |
| buyer_contact | VARCHAR | 50 | |
| total_amount | DECIMAL | 15,2 | |
| payment_method | VARCHAR | 50 | |
| payment_status | VARCHAR | 50 | |
| notes | TEXT | | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: 
  - `users` (user_id, CASCADE DELETE)
  - `planting_locations` (planting_location_id, SET NULL ON DELETE)
- One-to-Many dengan: `sale_items` (sale_id)

## Index
- PRIMARY KEY: `sale_id`
- UNIQUE: `receipt_number`
- FOREIGN KEY: `user_id` → `users.user_id` (CASCADE DELETE)
- FOREIGN KEY: `planting_location_id` → `planting_locations.planting_location_id` (SET NULL ON DELETE)
