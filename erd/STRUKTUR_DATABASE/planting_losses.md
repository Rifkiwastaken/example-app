# Struktur Database - Tabel planting_losses

## Deskripsi
Tabel untuk menyimpan data kerugian/kehilangan pada penanaman

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **id** | BIGINT | - | Primary Key, Auto Increment |
| planting_id | BIGINT | - | Foreign Key → plantings.id (NOT NULL) |
| loss_date | DATE | - | Tanggal kerugian (NOT NULL) |
| loss_amount | DECIMAL | 12,2 | Jumlah kerugian (NOT NULL) |
| loss_reason | VARCHAR | 50 | Alasan kerugian (NULL) |
| description | TEXT | - | Deskripsi kerugian (NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `plantings` (planting_id, CASCADE DELETE)

## Index
- PRIMARY KEY: `id`
- FOREIGN KEY: `planting_id` → `plantings.id` (CASCADE DELETE)












