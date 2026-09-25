# Struktur Database - Tabel plant_types

## Deskripsi
Tabel untuk menyimpan jenis-jenis tanaman

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **plant_type_id** | VARCHAR | 36 | Primary Key |
| name | VARCHAR | 50 | |
| category | VARCHAR | 50 | |
| variety | TEXT | | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- One-to-Many dengan: `plants` (plant_type_id)

## Index
- PRIMARY KEY: `plant_type_id`
