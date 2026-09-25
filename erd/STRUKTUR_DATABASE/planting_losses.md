# Struktur Database - Tabel planting_losses

## Deskripsi
Tabel untuk menyimpan data kerugian/kehilangan pada penanaman

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **planting_loss_id** | VARCHAR | 36 | Primary Key |
| planting_id | VARCHAR | 36 | Foreign Key → plantings.planting_id |
| loss_date | DATE | | |
| loss_amount | DECIMAL | 15,2 | |
| loss_reason | VARCHAR | 50 | |
| description | TEXT | | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `plantings` (planting_id, CASCADE DELETE)

## Index
- PRIMARY KEY: `planting_loss_id`
- FOREIGN KEY: `planting_id` → `plantings.planting_id`












