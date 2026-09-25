# Laporan Pengujian SIBESTI

Tanggal uji: **15 September 2026 09:53**
Penguji: skrip QA otomatis alur pemakaian sistem (2 set data contoh QA-A dan QA-B).
Hasil: **71 lulus**, **0 gagal**, dari **71** kasus.

## Urutan pemakaian sistem yang diuji

1. Website publik (beranda, profil, berita, dokumen, harga, kontak, layanan).
2. Login petugas → dasbor dan CMS (konten, pengaturan, struktur).
3. Master data: satuan benih, kategori tanaman, varietas, benih sumber.
4. Lokasi penanaman + lahan (termasuk panjang × lebar).
5. Produksi benih (hanya benih tersedia, nama tanaman, satuan panen terkunci).
6. Gudang, akun petugas, permintaan benih, geowisata, magang.
7. Kasus negatif: form kosong, stok tidak cukup, booking hari libur, benih sumber habis.

## Data contoh yang ditambahkan

### Dataset A
- Kategori: Padi QA-A 20260915095350
- Varietas: Padi QA-A 20260915095350 · Inpari QA-A 20260915095350
- Benih sumber: LOT-QA-A-20260915095350
- Lokasi / lahan: Kebun Induk QA-A 20260915095350 / BLOK-QA-A
- Produksi: TANAM-QA-A-20260915095350
- Gudang: Gudang QA-A 20260915095350
- Konten: Kegiatan panen QA-A 20260915095350
- Permintaan: PB-QA-A-20260915095350
- Geowisata: GEO-2026-06
- Magang: MGN-202609-006

### Dataset B
- Kategori: Cabai QA-B 20260915095350
- Varietas: Cabai QA-B 20260915095350 · Tanjung QA-B 20260915095350
- Benih sumber: LOT-QA-B-20260915095350
- Lokasi / lahan: Kebun Cabang QA-B 20260915095350 / BLOK-QA-B
- Produksi: TANAM-QA-B-20260915095350
- Gudang: Gudang QA-B 20260915095350
- Konten: Artikel budidaya QA-B 20260915095350
- Permintaan: PB-QA-B-20260915095350
- Geowisata: GEO-2026-07
- Magang: MGN-202609-007

## Langkah, hasil yang diharapkan, dan hasil aktual

| ID | Langkah | Hasil diharapkan | Status | Hasil aktual |
|----|---------|------------------|--------|--------------|
| PUB-/ | Akses publik Beranda (/) | HTTP 200 | Lulus | HTTP 200 |
| PUB-/profil | Akses publik Profil (/profil) | HTTP 200 | Lulus | HTTP 200 |
| PUB-/berita | Akses publik Berita (/berita) | HTTP 200 | Lulus | HTTP 200 |
| PUB-/dokumen | Akses publik Dokumen (/dokumen) | HTTP 200 | Lulus | HTTP 200 |
| PUB-/informasi-publik | Akses publik Informasi publik (/informasi-publik) | HTTP 200 | Lulus | HTTP 200 |
| PUB-/kontak | Akses publik Kontak (/kontak) | HTTP 200 | Lulus | HTTP 200 |
| PUB-/permintaan | Akses publik Daftar permintaan publik (/permintaan) | HTTP 200 | Lulus | HTTP 200 |
| PUB-/permintaan/buat | Akses publik Form permintaan publik (/permintaan/buat) | HTTP 200 | Lulus | HTTP 200 |
| PUB-/kunjungan-geowisata | Akses publik Geowisata (/kunjungan-geowisata) | HTTP 200 | Lulus | HTTP 200 |
| PUB-/kunjungan-geowisata/buat | Akses publik Form geowisata (/kunjungan-geowisata/buat) | HTTP 200 | Lulus | HTTP 200 |
| PUB-/pendaftaran-magang | Akses publik Magang (/pendaftaran-magang) | HTTP 200 | Lulus | HTTP 200 |
| PUB-/pendaftaran-magang/buat | Akses publik Form magang (/pendaftaran-magang/buat) | HTTP 200 | Lulus | HTTP 200 |
| PUB-/login | Akses publik Login (/login) | HTTP 200 | Lulus | HTTP 200 |
| INT-/dashboard | Akses internal Dasbor (/dashboard) | HTTP 200 | Lulus | HTTP 200 |
| INT-/contents | Akses internal CMS konten (/contents) | HTTP 200 | Lulus | HTTP 200 |
| INT-/contents/create | Akses internal Form konten (/contents/create) | HTTP 200 | Lulus | HTTP 200 |
| INT-/contents/settings | Akses internal Pengaturan situs (/contents/settings) | HTTP 200 | Lulus | HTTP 200 |
| INT-/contents/organization | Akses internal Struktur organisasi (/contents/organization) | HTTP 200 | Lulus | HTTP 200 |
| INT-/plants | Akses internal Daftar tanaman (/plants) | HTTP 200 | Lulus | HTTP 200 |
| INT-/plants/create | Akses internal Form varietas (/plants/create) | HTTP 200 | Lulus | HTTP 200 |
| INT-/plant-types | Akses internal Kategori tanaman (/plant-types) | HTTP 200 | Lulus | HTTP 200 |
| INT-/seed-units | Akses internal Satuan benih (/seed-units) | HTTP 200 | Lulus | HTTP 200 |
| INT-/planting-locations | Akses internal Lokasi penanaman (/planting-locations) | HTTP 200 | Lulus | HTTP 200 |
| INT-/planting-locations/create | Akses internal Form lokasi (/planting-locations/create) | HTTP 200 | Lulus | HTTP 200 |
| INT-/warehouse-locations | Akses internal Gudang (/warehouse-locations) | HTTP 200 | Lulus | HTTP 200 |
| INT-/warehouse-locations/create | Akses internal Form gudang (/warehouse-locations/create) | HTTP 200 | Lulus | HTTP 200 |
| INT-/seed-stock | Akses internal Stok benih (/seed-stock) | HTTP 200 | Lulus | HTTP 200 |
| INT-/seed-requests | Akses internal Permintaan internal (/seed-requests) | HTTP 200 | Lulus | HTTP 200 |
| INT-/seed-requests/create | Akses internal Form permintaan internal (/seed-requests/create) | HTTP 200 | Lulus | HTTP 200 |
| INT-/users | Akses internal Pengguna (/users) | HTTP 200 | Lulus | HTTP 200 |
| INT-/users/create | Akses internal Form pengguna (/users/create) | HTTP 200 | Lulus | HTTP 200 |
| INT-/sales | Akses internal Penjualan (/sales) | HTTP 200 | Lulus | HTTP 200 |
| INT-/pelayanan-publik/kunjungan-geowisata | Akses internal Kelola geowisata (/pelayanan-publik/kunjungan-geowisata) | HTTP 200 | Lulus | HTTP 200 |
| INT-/pelayanan-publik/pendaftaran-magang | Akses internal Kelola magang (/pelayanan-publik/pendaftaran-magang) | HTTP 200 | Lulus | HTTP 200 |
| UI-PILL | Kolom produksi benih memakai pill teks hitam | class data-pill tanpa text-bg-success-subtle | Lulus | pill hitam terpasang |
| VAL-PLANT | Validasi form varietas kosong | Field wajib ditolak | Lulus | plant_type_id, variety, satuan_stok_id, satuan_tanam_id, satuan_panen_id, harga_jual, minimal_stok |
| VAL-LOC | Validasi form lokasi kosong | Nama/alamat/luas/tipe ditolak | Lulus | name, location_summary, location_type, planting_format, map_size |
| A-TYPE | Tambah kategori tanaman Padi QA-A 20260915095350 | Record tersimpan | Lulus | PTY-OZNXMCVB |
| A-PLANT | Tambah varietas Inpari QA-A 20260915095350 | Record tersimpan, nama tanaman tampil bersama varietas | Lulus | Padi QA-A 20260915095350 · Inpari QA-A 20260915095350 |
| A-SRC | Tambah benih sumber LOT-QA-A-20260915095350 | Tersimpan dan tersedia (quantity > 0) | Lulus | sisa 50.00 |
| A-SRC-JSON | API benih sumber hanya menampilkan yang tersedia | Lot kosong tidak ikut, lot sisa > 0 ikut | Lulus | jumlah opsi 1 |
| A-LOC | Tambah lokasi Kebun Induk QA-A 20260915095350 | Record tersimpan | Lulus | LOC-AFD2WTN9 |
| A-FIELD | Tambah lahan BLOK-QA-A dengan panjang x lebar | Record tersimpan termasuk dimensi | Lulus | 100.00x75.00 |
| A-PROD | Tambah produksi benih | Tersimpan, satuan panen mengikuti tanaman (bukan input palsu) | Lulus | satuan SUN-3F0C33B0 |
| A-PROD-OVER | Produksi melebihi sisa benih sumber | Ditolak | Lulus | Jumlah benih yang ditanam melebihi sisa benih sumber. |
| A-WH | Tambah gudang Gudang QA-A 20260915095350 | Record tersimpan | Lulus | WHS-513WLF3N |
| A-CMS | Tambah konten website Kegiatan panen QA-A 20260915095350 | Tersimpan dan terbit | Lulus | kegiatan-panen-qa-a-20260915095350 |
| A-USER | Tambah akun petugas.qa.a.20260915095350@sibesti.test | User aktif tersimpan | Lulus | USR-2R1KXPA0 |
| A-SR-INT | Detail permintaan internal | HTTP 200 menampilkan nomor permintaan | Lulus | HTTP 200 |
| A-SR-PUB | Detail permintaan publik | HTTP 200 menampilkan nomor permintaan | Lulus | HTTP 200 |
| A-GEO | Tambah booking geowisata | Tersimpan (hari kerja tersedia) | Lulus | GEO-2026-06 2026-09-16 |
| A-MAG | Tambah pendaftaran magang + 2 peserta | Tersimpan | Lulus | MGN-202609-006 |
| B-TYPE | Tambah kategori tanaman Cabai QA-B 20260915095350 | Record tersimpan | Lulus | PTY-LJPO8SS5 |
| B-PLANT | Tambah varietas Tanjung QA-B 20260915095350 | Record tersimpan, nama tanaman tampil bersama varietas | Lulus | Cabai QA-B 20260915095350 · Tanjung QA-B 20260915095350 |
| B-SRC | Tambah benih sumber LOT-QA-B-20260915095350 | Tersimpan dan tersedia (quantity > 0) | Lulus | sisa 25.00 |
| B-SRC-JSON | API benih sumber hanya menampilkan yang tersedia | Lot kosong tidak ikut, lot sisa > 0 ikut | Lulus | jumlah opsi 1 |
| B-LOC | Tambah lokasi Kebun Cabang QA-B 20260915095350 | Record tersimpan | Lulus | LOC-NK4H4IVX |
| B-FIELD | Tambah lahan BLOK-QA-B dengan panjang x lebar | Record tersimpan termasuk dimensi | Lulus | 80.00x50.00 |
| B-PROD | Tambah produksi benih | Tersimpan, satuan panen mengikuti tanaman (bukan input palsu) | Lulus | satuan SUN-3F0C33B0 |
| B-PROD-OVER | Produksi melebihi sisa benih sumber | Ditolak | Lulus | Jumlah benih yang ditanam melebihi sisa benih sumber. |
| B-WH | Tambah gudang Gudang QA-B 20260915095350 | Record tersimpan | Lulus | WHS-QSS0SFPS |
| B-CMS | Tambah konten website Artikel budidaya QA-B 20260915095350 | Tersimpan dan terbit | Lulus | artikel-budidaya-qa-b-20260915095350 |
| B-USER | Tambah akun petugas.qa.b.20260915095350@sibesti.test | User aktif tersimpan | Lulus | USR-P1XF3CH9 |
| B-SR-INT | Detail permintaan internal | HTTP 200 menampilkan nomor permintaan | Lulus | HTTP 200 |
| B-SR-PUB | Detail permintaan publik | HTTP 200 menampilkan nomor permintaan | Lulus | HTTP 200 |
| B-GEO | Tambah booking geowisata | Tersimpan (hari kerja tersedia) | Lulus | GEO-2026-07 2026-09-16 |
| B-MAG | Tambah pendaftaran magang + 2 peserta | Tersimpan | Lulus | MGN-202609-007 |
| VAL-SR | Validasi permintaan benih kosong | Field wajib ditolak | Lulus | buyer_name, buyer_contact, buyer_nik, buyer_category, organization, destination_province, destination_city, destination_district, destination_village, planned_location_name, planned_gps, estimated_planting_area, notes, plant_id, packaging_ids, payment_method |
| VAL-GEO-WEEKEND | Booking geowisata di hari Minggu | Ditolak karena kebun tutup | Lulus | Tanggal tidak tersedia (weekend, libur nasional, atau daya tampung kebun induk penuh). |
| UI-PROD-NAME | Dropdown tanaman menampilkan nama tanaman | Nama tipe tanaman muncul di opsi | Lulus | nama tanaman tampil |
| UI-PROD-UNIT | Field satuan panen terkunci | readonly + keterangan mengikuti satuan | Lulus | terkunci |

## Catatan batasan

- Permintaan benih publik yang lengkap memerlukan kemasan stok FEFO. Dua permintaan contoh dibuat agar halaman detail bisa diakses; alur pilih kemasan diuji lewat validasi form kosong.
- Sertifikasi/label gudang dan pembayaran lunas membutuhkan stok kemasan yang sudah dilabel. Kasus itu dicatat sebagai ketergantungan data gudang.
- Pengujian dijalankan lewat HTTP kernel (GET) dan pemanggilan controller (POST) pada database aktif, karena `php artisan serve` tidak dapat bind port di lingkungan ini.