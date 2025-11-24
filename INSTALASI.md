# Panduan Instalasi SIBIT

## Persyaratan Sistem
- PHP >= 8.1
- Composer
- Node.js & NPM
- MySQL/MariaDB
- Web Server (Apache/Nginx) atau Laragon

## Langkah Instalasi

### 1. Setup Environment
Copy file `.env.example` ke `.env`:
```bash
cp .env.example .env
```

### 2. Konfigurasi Database
Edit file `.env` dan sesuaikan konfigurasi database:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sibit
DB_USERNAME=root
DB_PASSWORD=
```

Buat database baru:
```sql
CREATE DATABASE sibit;
```

### 3. Install Dependencies
```bash
composer install
npm install
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Jalankan Migration & Seeder
```bash
php artisan migrate
php artisan db:seed
```

### 6. Jalankan Aplikasi
```bash
php artisan serve
```

Akses aplikasi di: http://localhost:8000

## Kredensial Login Default

**Email**: admin@sibit.com  
**Password**: password123

## Fitur yang Tersedia

### ✅ Sudah Diimplementasikan
1. **Halaman Login**
   - Login dengan email dan password
   - Remember me functionality
   - Responsive design

2. **Dashboard**
   - Informasi cuaca Lubuk Minturun, Padang
   - Forecast cuaca per jam dan per hari
   - Ringkasan tugas dengan prioritas
   - Responsive design untuk desktop dan mobile

3. **Manajemen Tugas**
   - Lihat daftar tugas
   - Tambah tugas baru
   - Edit tugas
   - Hapus tugas
   - Filter berdasarkan prioritas (Low, Medium, High, Highest)
   - Status tugas (Pending, In Progress, Completed)
   - Tag lokasi pada tugas
   - Tanggal tenggat

4. **Sidebar Navigation**
   - Dashboard
   - Tugas
   - Menu placeholder untuk fitur lain (Penanaman, Sertifikasi, Stok, dll)

### 🔄 Dalam Pengembangan
1. Manajemen Penanaman
2. Manajemen Sertifikasi Bibit
3. Manajemen Stok Bibit
4. Penjualan Bibit
5. Perencanaan
6. Laporan
7. Peta Lahan
8. Manajemen Akun

## Catatan Penting

### API Cuaca
Aplikasi menggunakan OpenWeatherMap API untuk data cuaca. Untuk mendapatkan data cuaca real-time:

1. Daftar di: https://openweathermap.org/api
2. Dapatkan API key gratis
3. Update file `app/Http/Controllers/DashboardController.php` pada line yang berisi `'appid' => 'your_api_key_here'`

Tanpa API key, aplikasi akan menggunakan data cuaca fallback (dummy data).

### Struktur Database
- **users**: Tabel untuk user authentication
- **tasks**: Tabel untuk manajemen tugas
- **password_reset_tokens**: Tabel untuk reset password
- **personal_access_tokens**: Tabel untuk API tokens
- **failed_jobs**: Tabel untuk tracking job yang gagal

## Troubleshooting

### Error "No application encryption key"
Jalankan:
```bash
php artisan key:generate
```

### Error Database Connection
Pastikan:
- MySQL service sudah berjalan
- Database sudah dibuat
- Kredensial di `.env` sudah benar

### Error Permission Denied
Pastikan folder berikut memiliki permission write:
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Assets Tidak Muncul
Jika menggunakan Vite, jalankan:
```bash
npm run dev
```

Atau untuk production:
```bash
npm run build
```

## Support
Untuk bantuan lebih lanjut, hubungi tim pengembang SIBIT.


















