# Struktur Database - Tabel planting_locations

## Deskripsi
Tabel untuk menyimpan lokasi penanaman/lahan. **Tabel `locations` telah dihapus;** tidak ada kolom `location_id`.

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **planting_location_id** | VARCHAR | 36 | Primary Key |
| name | VARCHAR | 50 | |
| location_type | ENUM | | |
| location_type_custom | VARCHAR | 50 | |
| planting_format | ENUM | | |
| planting_format_custom | VARCHAR | 50 | |
| num_beds | INT | | |
| bed_length_m | DECIMAL | 8,2 | |
| bed_width_m | DECIMAL | 8,2 | |
| map_size | VARCHAR | 50 | |
| light_condition | VARCHAR | 50 | |
| location_summary | VARCHAR | 50 | |
| administrative_address | TEXT | | |
| google_maps_link | VARCHAR | 50 | |
| primary_photo_path | VARCHAR | 50 | |
| land_status | VARCHAR | 50 | |
| ownership_status | VARCHAR | 50 | |
| water_source | VARCHAR | 50 | |
| soil_type | VARCHAR | 50 | |
| description | TEXT | | |
| elevation_masl | INT | | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- One-to-Many dengan: `plants`, `plantings`, `harvests`, `certifications`, `treatments`, `nutrients`, `sales`, `inventory_type_seeds`, `planting_location_notes`, `planting_location_photos`, `expenses`, `attachments`, `tasks`
- Many-to-Many dengan: `users` melalui `user_planting_location_land_manager` dan `user_planting_location_land_worker`

## Index
- PRIMARY KEY: `planting_location_id`

## Catatan
- Tabel **locations** telah dihapus. Kolom **location_id** tidak ada di tabel ini.
- Nilai **location_type** mencakup **'sawah'** (enum diperbarui).
