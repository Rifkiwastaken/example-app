# Struktur Database - Tabel users

## Deskripsi
Tabel untuk menyimpan data pengguna sistem

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **user_id** | VARCHAR | 36 | Primary Key |
| name | VARCHAR | 50 | |
| email | VARCHAR | 255 | |
| email_verified_at | TIMESTAMP | | |
| password | VARCHAR | 255 | |
| remember_token | VARCHAR | 100 | |
| role | ENUM | | |
| location_placement | VARCHAR | 50 | |
| photo_path | VARCHAR | 50 | |
| full_name | VARCHAR | 50 | |
| status | ENUM | | |
| contact_type | ENUM | | |
| organization | VARCHAR | 50 | |
| position | VARCHAR | 50 | |
| nip | VARCHAR | 50 | |
| primary_phone | VARCHAR | 20 | |
| primary_phone_is_whatsapp | TINYINT | 1 | |
| secondary_phone | VARCHAR | 20 | |
| address | TEXT | | |
| province | VARCHAR | 50 | |
| city | VARCHAR | 50 | |
| district | VARCHAR | 50 | |
| village | VARCHAR | 50 | |
| notes | TEXT | | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- One-to-Many dengan: `warehouses` (responsible_person_id), `inventory_types` (responsible_person_id), `inventory_transactions` (user_id), `sales` (user_id), `inventory_type_seeds` (filled_by_user_id), `seed_histories` (user_id), `attachments` (created_by, edited_by), `treatments` (responsible_person_id), `nutrients` (responsible_person_id), `planting_location_notes` (user_id), `inventory_notes` (user_id), `inventory_photos` (user_id), `tasks` (created_by)
- Many-to-Many dengan: `planting_locations` melalui `user_planting_location_land_manager` dan `user_planting_location_land_worker`

## Index
- PRIMARY KEY: `user_id`
- UNIQUE: `email`

## Catatan
- Tabel `locations` telah dihapus. Kolom `location_id` diganti dengan `location_placement` (VARCHAR).
