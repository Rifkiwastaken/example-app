# SIBIT - Sistem Informasi Balai Benih Induk Tanaman

Aplikasi website manajemen pertanian untuk Unit Pelaksana Teknis Daerah (UPTD) Balai Benih Induk (BBI) Tanaman Pangan, Palawija, dan Hortikultura (TPPH).

## Fitur Utama

- **Manajemen Penanaman**: Monitoring dari fase persiapan lahan hingga panen
- **Manajemen Sertifikasi Bibit**: Tracking sertifikasi bibit
- **Manajemen Stok**: Pengelolaan inventori bibit
- **Penjualan Bibit**: Sistem penjualan dan distribusi
- **Dashboard**: Monitoring cuaca dan tugas
- **Manajemen Akun**: Pengelolaan user dan hak akses

## Kredensial Login Default

**Email**: admin@sibit.com  
**Password**: password123

## Instalasi

1. Clone repository dan masuk ke folder project
2. Install dependencies:
   ```bash
   composer install
   npm install
   ```

3. Copy file .env:
   ```bash
   cp .env.example .env
   ```

4. Generate application key:
   ```bash
   php artisan key:generate
   ```

5. Setup database di file .env:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sibit
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. Jalankan migration dan seeder:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

7. Jalankan aplikasi:
   ```bash
   php artisan serve
   ```

8. Akses aplikasi di browser: http://localhost:8000

## Lokasi Monitoring Cuaca

Aplikasi ini dikonfigurasi untuk monitoring cuaca di **Lubuk Minturun, Padang**.

## Teknologi

- Laravel 10
- Bootstrap 5
- Font Awesome 6
- MySQL

## Catatan

- UI responsif untuk desktop dan mobile
- Integrasi dengan OpenWeatherMap API untuk data cuaca real-time
- Sistem authentication menggunakan Laravel built-in auth

