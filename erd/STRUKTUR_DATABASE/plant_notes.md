# Struktur Database - Tabel plant_notes

## Deskripsi
Tabel untuk menyimpan catatan tanaman

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **plant_note_id** | VARCHAR | 36 | Primary Key |
| plant_id | VARCHAR | 36 | Foreign Key → plants.plant_id |
| description | TEXT | | |
| note_date | DATE | | |
| keywords | VARCHAR | 50 | |
| attachment_path | VARCHAR | 50 | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `plants` (plant_id, CASCADE DELETE)

## Index
- PRIMARY KEY: `plant_note_id`
- FOREIGN KEY: `plant_id` → `plants.plant_id`












