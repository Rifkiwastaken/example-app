# Struktur Database - Tabel plant_notes

## Deskripsi
Tabel untuk menyimpan catatan tanaman

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **id** | BIGINT | - | Primary Key, Auto Increment |
| plant_id | BIGINT | - | Foreign Key → plants.id (NOT NULL) |
| description | TEXT | - | Deskripsi catatan (NOT NULL) |
| note_date | DATE | - | Tanggal catatan (NOT NULL) |
| keywords | VARCHAR | 50 | Kata kunci (NULL) |
| attachment_path | VARCHAR | 50 | Path attachment (NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `plants` (plant_id, CASCADE DELETE)

## Index
- PRIMARY KEY: `id`
- FOREIGN KEY: `plant_id` → `plants.id` (CASCADE DELETE)












