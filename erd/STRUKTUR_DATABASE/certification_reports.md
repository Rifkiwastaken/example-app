# Struktur Database - Tabel certification_reports

## Deskripsi
Tabel untuk menyimpan laporan pemeriksaan sertifikasi

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **certification_report_id** | VARCHAR | 36 | Primary Key (format: certification_report_id) |
| certification_id | VARCHAR | 36 | Foreign Key → certifications.certification_id (NOT NULL, CASCADE DELETE) |
| report_type | VARCHAR | 50 | Tipe laporan (NULL) |
| report_number_bpsb | VARCHAR | 255 | Nomor laporan BPSB (UNIQUE, NOT NULL) |
| report_date | DATE | - | Tanggal laporan (NOT NULL) |
| growing_season | VARCHAR | 255 | Musim tanam (NULL) |
| inspection_phase | VARCHAR | 255 | Fase inspeksi: 'Vegetatif', 'Generatif', 'Menjelang Panen', 'Lainnya' (NOT NULL) |
| inspector_name | VARCHAR | 255 | Nama petugas pengawas (NULL) |
| reporter_name | VARCHAR | 255 | Nama pelapor (NULL) |
| seed_class_result | VARCHAR | 255 | Kelas benih hasil: 'BS', 'BP', 'BR' (NULL) |
| isolation_north | VARCHAR | 255 | Isolasi utara (NULL) |
| isolation_east | VARCHAR | 255 | Isolasi timur (NULL) |
| isolation_south | VARCHAR | 255 | Isolasi selatan (NULL) |
| isolation_west | VARCHAR | 255 | Isolasi barat (NULL) |
| plant_characteristics_match | BOOLEAN | - | Karakteristik tanaman sesuai (NULL) |
| pest_disease_condition | TEXT | - | Kondisi hama penyakit (NULL) |
| weed_condition | ENUM | - | Kondisi gulma: 'Bersih', 'Cukup Bersih', 'Kotor' (NULL) |
| population_per_sample | INT | - | Populasi per sampel (NULL) |
| other_variety_mix_count | INT | - | Jumlah campuran varietas lain (NULL) |
| other_variety_mix_percentage | DECIMAL | 5,2 | Persentase campuran varietas lain (NULL) |
| estimated_yield | DECIMAL | 12,2 | Estimasi hasil (NULL) |
| conclusion | ENUM | - | Kesimpulan: 'LULUS', 'TIDAK LULUS' (NULL) |
| scan_file_path | VARCHAR | 255 | Path file scan dokumen (NULL) |
| expiry_date | DATE | - | Tanggal kadaluarsa (NULL) |
| certified_seed_quantity | DECIMAL | 12,2 | Jumlah benih bersertifikat (NULL) |
| certified_seed_unit | VARCHAR | 50 | Satuan benih bersertifikat (NULL) |
| seed_unit | VARCHAR | 50 | Satuan benih (NULL) |
| seed_unit_quantity | DECIMAL | 12,2 | Jumlah satuan benih (NULL) |
| harvest_per_unit | DECIMAL | 12,2 | Jumlah panen per satuan benih (NULL) |
| harvest_per_unit_unit | VARCHAR | 50 | Satuan untuk jumlah panen per satuan benih (NULL) |
| estimated_sale_price_per_kg | DECIMAL | 15,2 | Estimasi harga jual per kg (NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- Many-to-One dengan: `certifications` (certification_id, CASCADE DELETE)
- One-to-Many dengan: `inventory_type_certification_reports` (certification_report_id), `inventory_type_seeds` (certification_report_id)

## Index
- PRIMARY KEY: `certification_report_id`
- UNIQUE: `report_number_bpsb`
- FOREIGN KEY: `certification_id` → `certifications.certification_id` (CASCADE DELETE)
