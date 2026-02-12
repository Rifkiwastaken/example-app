# Struktur Database - Tabel plantings

## Deskripsi
Tabel untuk menyimpan data penanaman

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **id** | BIGINT | - | Primary Key, Auto Increment |
| plant_id | BIGINT | - | Foreign Key → plants.id (NOT NULL) |
| planting_location_id | BIGINT | - | Foreign Key → planting_locations.id (NULL) |
| bed_label | VARCHAR | 50 | Label bed (NULL) |
| days_to_emerge | INT | - | Hari hingga muncul (NULL) |
| spacing_between_plants | VARCHAR | 50 | Jarak antar tanaman (NULL) |
| spacing_between_rows | VARCHAR | 50 | Jarak antar baris (NULL) |
| sowing_depth | VARCHAR | 50 | Kedalaman tanam (NULL) |
| avg_height | VARCHAR | 50 | Tinggi rata-rata (NULL) |
| start_method | ENUM | - | Metode: 'tanam_langsung', 'baki_semai', 'pindahkan_ke_tanah', 'transplant', 'container', 'ditanam_di_baki_semai', 'batang_bawah', 'umbi', 'sambung_okulasi', 'lainnya' (NULL) |
| germination_stage | ENUM | - | Tahap: 'benih_ditanam', 'perkecambahan', 'bibit', 'sudah_ditanam', 'vegetatif', 'berbunga', 'pematangan_buah', 'selesai' (NULL) |
| seeds_per_hole | INT | - | Benih per lubang (NULL) |
| light_profile | ENUM | - | Profil cahaya: 'matahari_penuh', 'matahari_penuh_sebagian', 'matahari_sebagian', 'matahari_setengah_teduh', 'setengah_teduh', 'teduh_sepenuhnya' (NULL) |
| soil_condition | ENUM | - | Kondisi tanah: 'berkapur', 'liat', 'lempung', 'gambut', 'berpasir', 'lanau' (NULL) |
| planting_detail | TEXT | - | Detail penanaman (NULL) |
| pruning_detail | TEXT | - | Detail pemangkasan (NULL) |
| perennial | BOOLEAN | - | Tanaman tahunan (NOT NULL) |
| days_to_flower | INT | - | Hari hingga berbunga (NULL) |
| days_to_harvest | INT | - | Hari hingga panen (NULL) |
| harvest_window_days | INT | - | Jendela panen dalam hari (NULL) |
| expected_loss_rate | VARCHAR | 50 | Tingkat kerugian yang diharapkan (NULL) |
| harvest_unit | ENUM | - | Unit panen: 'ikat', 'barel', 'tandan', 'gantang', 'lusin', 'gram', 'batang', 'kilogram', 'kiloliter', 'liter', 'mililiter', 'satuan', 'ton' (NULL) |
| expected_yield_per_hectare | DECIMAL | 12,2 | Hasil per hektar yang diharapkan (NULL) |
| quantity_planted | INT | - | Jumlah yang ditanam (NULL) |
| planted_at | DATE | - | Tanggal ditanam (NULL) |
| area_ha | DECIMAL | 10,2 | Luas area dalam hektar (NULL) |
| estimated_harvest_date | DATE | - | Estimasi tanggal panen (NULL) |
| is_completed | BOOLEAN | - | Apakah selesai (NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `plants` (plant_id, CASCADE DELETE), `planting_locations` (planting_location_id, NULL ON DELETE)
- One-to-Many dengan: `harvests` (planting_id), `planting_losses` (planting_id), `tasks` (planting_id)

## Index
- PRIMARY KEY: `id`
- FOREIGN KEY: `plant_id` → `plants.id` (CASCADE DELETE)
- FOREIGN KEY: `planting_location_id` → `planting_locations.id` (NULL ON DELETE)












