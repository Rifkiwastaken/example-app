# Struktur Database - Tabel planting_locations

## Deskripsi
Tabel untuk menyimpan lokasi penanaman spesifik

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **planting_location_id** | VARCHAR | 36 | Primary Key (format: planting_location_id) |
| name | VARCHAR | 255 | Nama lokasi penanaman (NOT NULL) |
| location_type | ENUM | - | Tipe lokasi: 'lapangan','greenhouse','grow_room','padang_rumput','petak_ternak','lainnya' (NOT NULL) |
| planting_format | ENUM | - | Format penanaman: 'petak','cover_crop','row','lainnya' (NOT NULL) |
| num_beds | INT | - | Jumlah bed (NULL) |
| bed_length_m | DECIMAL | 8,2 | Panjang bed dalam meter (NULL) |
| bed_width_m | DECIMAL | 8,2 | Lebar bed dalam meter (NULL) |
| map_size | VARCHAR | 255 | Ukuran peta (NULL) |
| light_condition | VARCHAR | 255 | Kondisi cahaya (NULL) |
| description | TEXT | - | Deskripsi lokasi (NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- One-to-Many dengan: `plants` (planting_location_id), `plantings` (planting_location_id), `harvests` (planting_location_id), `certifications` (planting_location_id), `treatments` (planting_location_id), `nutrients` (planting_location_id), `sales` (planting_location_id), `inventory_type_seeds` (planting_location_id), `planting_location_notes` (planting_location_id), `planting_location_photos` (planting_location_id), `expenses` (planting_location_id), `attachments` (planting_location_id), `tasks` (planting_location_id)
- Many-to-Many dengan: `users` melalui `user_planting_location_land_manager` dan `user_planting_location_land_worker`

## Index
- PRIMARY KEY: `planting_location_id`

## Catatan
- Tabel `locations` telah dihapus. Kolom `location_id` dihapus dari tabel ini.
