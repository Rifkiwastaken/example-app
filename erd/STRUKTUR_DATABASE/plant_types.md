# Struktur Database - Tabel plant_types

## Deskripsi
Tabel untuk menyimpan jenis-jenis tanaman

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **plant_type_id** | VARCHAR | 36 | Primary Key (format: plant_type_id) |
| name | VARCHAR | 255 | Nama jenis tanaman (NOT NULL) |
| category | VARCHAR | 255 | Kategori tanaman: pangan, hortikultura, dll (NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- One-to-Many dengan: `plants` (plant_type_id)

## Index
- PRIMARY KEY: `plant_type_id`
