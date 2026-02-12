# Struktur Database - Tabel sales

## Deskripsi
Tabel untuk menyimpan data penjualan benih. Menyimpan informasi pembeli, lokasi sebaran, dan detail pembayaran.

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **sale_id** | VARCHAR | 36 | Primary Key (format: sale_id) |
| receipt_number | VARCHAR | 50 | Nomor struk/referensi (UNIQUE, NOT NULL) |
| sale_date | DATE | - | Tanggal penjualan (NOT NULL) |
| buyer_name | VARCHAR | 255 | Nama pembeli (NOT NULL) |
| buyer_contact | VARCHAR | 255 | Kontak pembeli (NULL) |
| total_amount | DECIMAL | 15,2 | Total pembayaran dalam Rp (NOT NULL) |
| payment_method | ENUM | - | Metode pembayaran: 'cash', 'transfer_bank' (NOT NULL) |
| payment_status | ENUM | - | Status pembayaran: 'lunas', 'belum_lunas' (NOT NULL) |
| planting_location_id | VARCHAR | 36 | Foreign Key → planting_locations.planting_location_id (NULL, SET NULL ON DELETE) |
| notes | TEXT | - | Keterangan penjualan (NULL) |
| user_id | VARCHAR | 36 | Foreign Key → users.user_id (NOT NULL, CASCADE DELETE) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

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
