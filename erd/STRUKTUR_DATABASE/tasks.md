# Struktur Database - Tabel tasks

## Deskripsi
Tabel untuk menyimpan data tugas

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **task_id** | VARCHAR | 36 | Primary Key |
| assigned_to | VARCHAR | 36 | Foreign Key → users.user_id |
| template_id | VARCHAR | 36 | Foreign Key → task_templates.task_template_id |
| series_id | VARCHAR | 36 | Foreign Key → task_series.task_series_id |
| planting_location_id | VARCHAR | 36 | Foreign Key → planting_locations.planting_location_id |
| planting_id | VARCHAR | 36 | Foreign Key → plantings.planting_id |
| created_by | VARCHAR | 36 | Foreign Key → users.user_id |
| last_edited_by | VARCHAR | 36 | Foreign Key → users.user_id |
| title | VARCHAR | 50 | |
| description | TEXT | | |
| priority | ENUM | | |
| status | ENUM | | |
| due_date | DATE | | |
| location | VARCHAR | 50 | |
| location_tagged | VARCHAR | 50 | |
| task_color | VARCHAR | 7 | |
| collaborators | JSON | | |
| repeats | VARCHAR | 50 | |
| hours_spent | DECIMAL | 8,2 | |
| last_edited_at | TIMESTAMP | | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

## Relasi
- Many-to-One dengan: `planting_locations` (planting_location_id, CASCADE DELETE), `plantings` (planting_id, CASCADE DELETE), `users` (created_by)

## Index
- PRIMARY KEY: `task_id`
- FOREIGN KEY: `assigned_to` → `users.user_id`
- FOREIGN KEY: `template_id` → `task_templates.task_template_id`
- FOREIGN KEY: `series_id` → `task_series.task_series_id`
- FOREIGN KEY: `planting_location_id` → `planting_locations.planting_location_id`
- FOREIGN KEY: `planting_id` → `plantings.planting_id`
- FOREIGN KEY: `created_by` → `users.user_id`












