# Struktur Database - Tabel users

## Deskripsi
Tabel untuk menyimpan data pengguna sistem

## Struktur Tabel

| Nama Atribut | Tipe Data | Ukuran | Keterangan |
|--------------|-----------|--------|------------|
| **user_id** | VARCHAR | 36 | Primary Key (format: user_id) |
| name | VARCHAR | 255 | Nama pengguna (NOT NULL) |
| email | VARCHAR | 255 | Email pengguna (UNIQUE, NOT NULL) |
| email_verified_at | TIMESTAMP | - | Tanggal verifikasi email (NULL) |
| password | VARCHAR | 255 | Password ter-hash (NOT NULL) |
| remember_token | VARCHAR | 100 | Token remember me (NULL) |
| role | ENUM | - | Role: 'admin','kepala_satuan_tugas','petugas_sertifikasi','petugas_gudang','petugas_bbi','penangkar' (NULL) |
| location_placement | VARCHAR | 255 | Penempatan lokasi (NULL) - **location_id dihapus** |
| photo_path | VARCHAR | 255 | Path foto profil (NULL) |
| full_name | VARCHAR | 255 | Nama lengkap (NULL) |
| status | ENUM | - | Status: 'active', 'inactive' (NULL) |
| contact_type | ENUM | - | Tipe kontak: 'pegawai_uptd_bbi_tpph', 'pegawai_gudang', 'petugas_sertifikasi', 'petani', 'penyuluh', 'penangkar', 'lainnya' (NULL) |
| organization | VARCHAR | 255 | Organisasi (NULL) |
| position | VARCHAR | 255 | Posisi/jabatan (NULL) |
| nip | VARCHAR | 50 | NIP (NULL) |
| primary_phone | VARCHAR | 20 | Nomor telepon utama (NULL) |
| primary_phone_is_whatsapp | TINYINT | 1 | Apakah nomor utama WhatsApp (NULL) |
| secondary_phone | VARCHAR | 20 | Nomor telepon sekunder (NULL) |
| address | TEXT | - | Alamat lengkap (NULL) |
| province | VARCHAR | 100 | Provinsi (NULL) |
| city | VARCHAR | 100 | Kota (NULL) |
| district | VARCHAR | 100 | Kecamatan (NULL) |
| village | VARCHAR | 100 | Desa/Kelurahan (NULL) |
| notes | TEXT | - | Catatan tambahan (NULL) |
| created_at | TIMESTAMP | - | Tanggal dibuat (NOT NULL) |
| updated_at | TIMESTAMP | - | Tanggal diupdate (NOT NULL) |

## Relasi
- One-to-Many dengan: `warehouses` (responsible_person_id), `inventory_types` (responsible_person_id), `inventory_transactions` (user_id), `sales` (user_id), `inventory_type_seeds` (filled_by_user_id), `seed_histories` (user_id), `attachments` (created_by, edited_by), `treatments` (responsible_person_id), `nutrients` (responsible_person_id), `planting_location_notes` (user_id), `inventory_notes` (user_id), `inventory_photos` (user_id), `tasks` (created_by)
- Many-to-Many dengan: `planting_locations` melalui `user_planting_location_land_manager` dan `user_planting_location_land_worker`

## Index
- PRIMARY KEY: `user_id`
- UNIQUE: `email`

## Catatan
- Tabel `locations` telah dihapus. Kolom `location_id` diganti dengan `location_placement` (VARCHAR).
