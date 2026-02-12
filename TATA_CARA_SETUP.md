# Tata Cara Setup Aplikasi SIBESTI

Panduan lengkap untuk menyiapkan **frontend**, **backend**, dan **database** aplikasi SIBESTI (Sistem Informasi Benih Sehat) dari awal.

---

## Daftar Isi

1. [Persyaratan Sistem](#1-persyaratan-sistem)
2. [Setup Database](#2-setup-database)
3. [Setup Backend (Laravel)](#3-setup-backend-laravel)
4. [Setup Frontend (Assets)](#4-setup-frontend-assets)
5. [Menjalankan Aplikasi](#5-menjalankan-aplikasi)
6. [Akun Default](#6-akun-default)
7. [Troubleshooting](#7-troubleshooting)

---

## 1. Persyaratan Sistem

Pastikan perangkat Anda sudah terpasang:

| Komponen      | Versi minimal | Keterangan |
|---------------|----------------|------------|
| **PHP**       | 8.1 atau lebih | Ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo` |
| **Composer**  | 2.x            | Package manager untuk PHP |
| **Node.js**   | 18.x atau 20.x | Untuk build asset frontend (Vite) |
| **npm**       | 9.x atau 10.x | Biasanya ikut terpasang dengan Node.js |
| **MySQL**     | 5.7+ / 8.x    | Atau MariaDB 10.3+ |
| **Web server**| -             | Apache/Nginx, atau gunakan `php artisan serve` |

**Cek versi di terminal:**

```bash
php -v
composer -V
node -v
npm -v
mysql --version
```

---

## 2. Setup Database

### 2.1 Buat database MySQL

Jalankan MySQL (via XAMPP/Laragon/MySQL Service), lalu buat database untuk SIBESTI.

**Via MySQL client (terminal):**

```bash
mysql -u root -p
```

Di dalam MySQL:

```sql
CREATE DATABASE sibesti CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

Atau satu baris (tanpa password jika kosong):

```bash
mysql -u root -e "CREATE DATABASE sibesti CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

**Catatan:** Nama database bisa diganti (misalnya `sibit`), asalkan sama dengan yang di konfigurasi `.env` (lihat langkah backend).

### 2.2 Pastikan MySQL berjalan

- **Windows (Laragon/XAMPP):** Start Apache + MySQL dari panel.
- **Linux:** `sudo systemctl start mysql` atau `sudo service mysql start`.

---

## 3. Setup Backend (Laravel)

Backend aplikasi SIBESTI adalah Laravel (PHP). Semua route, controller, dan logika aplikasi ada di sini.

### 3.1 Clone / salin project

Jika dari Git:

```bash
git clone <url-repo-sibesti> sibesti
cd sibesti
```

Atau buka folder project yang sudah ada.

### 3.2 Install dependency PHP

```bash
composer install
```

Untuk production (tanpa dev dependencies):

```bash
composer install --no-dev --optimize-autoloader
```

### 3.3 File environment

Salin file contoh environment dan edit sesuai lingkungan Anda:

```bash
# Windows (PowerShell)
Copy-Item .env.example .env

# Linux / macOS
cp .env.example .env
```

Buka file `.env` dan sesuaikan minimal bagian berikut:

```env
APP_NAME="SIBESTI"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sibesti
DB_USERNAME=root
DB_PASSWORD=
```

- **DB_DATABASE:** harus sama dengan nama database yang dibuat di [2.1](#21-buat-database-mysql).
- **DB_USERNAME** dan **DB_PASSWORD:** sesuaikan dengan user MySQL Anda.

### 3.4 Generate Application Key

```bash
php artisan key:generate
```

### 3.5 Jalankan migrasi database

Memakai database yang sudah dibuat di langkah 2:

```bash
php artisan migrate
```

Untuk **reset penuh** database (hapus semua tabel, buat ulang, dan isi akun admin):

```bash
php artisan migrate:fresh --seed
```

**Peringatan:** `migrate:fresh --seed` akan menghapus semua data. Hanya gunakan di development atau saat ingin mulai dari awal.

### 3.6 (Opsional) Hanya isi akun admin

Jika sudah pernah migrate dan hanya ingin memastikan akun admin ada:

```bash
php artisan db:seed --class=UserSeeder
```

### 3.7 Permission folder (terutama Linux/macOS)

Laravel perlu menulis ke `storage` dan `bootstrap/cache`:

```bash
chmod -R 775 storage bootstrap/cache
```

Di production, pastikan user yang menjalankan web server (misalnya `www-data`) adalah pemilik atau punya akses write ke folder tersebut.

---

## 4. Setup Frontend (Assets)

Frontend SIBESTI terdiri dari:

- **Blade templates** di `resources/views/` (tidak perlu “install” terpisah).
- **CSS dan JavaScript** yang di-build dengan **Vite** (`resources/css/app.css`, `resources/js/app.js`).

### 4.1 Install dependency Node

Di **root project** (folder yang sama dengan `package.json`):

```bash
npm install
```

### 4.2 Development (hot reload)

Saat development, jalankan Vite agar asset selalu ter-compile dan browser auto-reload:

```bash
npm run dev
```

Biarkan perintah ini berjalan di satu terminal. Jangan tutup saat Anda mengembangkan aplikasi.

### 4.3 Production (build sekali pakai)

Untuk deploy atau testing production-like:

```bash
npm run build
```

Output akan ke `public/build/`. Laravel otomatis memuat file dari sini jika `APP_ENV=production` atau setelah di-build.

**Ringkasan:**

| Lingkungan   | Perintah       | Kapan dipakai |
|-------------|----------------|----------------|
| Development | `npm run dev`  | Setiap kali coding frontend, biarkan jalan di terminal terpisah. |
| Production  | `npm run build`| Sebelum deploy atau saat tidak menjalankan `npm run dev`. |

---

## 5. Menjalankan Aplikasi

### 5.1 Development (lokal)

**Terminal 1 – Backend (Laravel):**

```bash
php artisan serve
```

Aplikasi akan berjalan di **http://localhost:8000** (atau port lain yang ditampilkan).

**Terminal 2 – Frontend (Vite):**

```bash
npm run dev
```

Lalu buka browser: **http://localhost:8000**

- Tanpa `npm run dev`, halaman tetap bisa dibuka, tetapi CSS/JS mungkin tidak muncul atau tidak ter-update.
- Pastikan **DB_DATABASE**, **DB_USERNAME**, **DB_PASSWORD** di `.env` benar dan MySQL sedang berjalan.

### 5.2 Production (contoh dengan PHP built-in server)

1. Build asset sekali:
   ```bash
   npm run build
   ```
2. Jalankan server:
   ```bash
   php artisan serve
   ```
3. Atau arahkan web server (Apache/Nginx) ke folder **`public`** dan set `APP_ENV=production`, `APP_DEBUG=false` di `.env`.

---

## 6. Akun Default

Setelah menjalankan **migrate** dan **seed** (misalnya `php artisan migrate:fresh --seed` atau `php artisan db:seed --class=UserSeeder`), akun admin default:

| Field    | Nilai            |
|----------|-------------------|
| **Email**   | admin@sibit.com   |
| **Password**| password123       |
| **Role**    | admin             |

Gunakan kredensial ini untuk login pertama kali. Disarankan mengganti password setelah login.

---

## 7. Troubleshooting

### "No application encryption key specified"

Jalankan:

```bash
php artisan key:generate
```

### Error koneksi database

- Pastikan MySQL/MariaDB **sedang berjalan**.
- Cek **DB_HOST**, **DB_PORT**, **DB_DATABASE**, **DB_USERNAME**, **DB_PASSWORD** di `.env`.
- Pastikan database sudah dibuat (lihat [2.1](#21-buat-database-mysql)).
- Tes koneksi: `php artisan db:show` atau buat file PHP yang memakai PDO dengan kredensial yang sama.

### CSS/JS tidak muncul atau 404

- **Development:** pastikan **`npm run dev`** sedang berjalan di terminal lain.
- **Production:** jalankan **`npm run build`** dan pastikan folder `public/build` terisi.
- Pastikan **APP_URL** di `.env` sesuai URL yang Anda pakai (misalnya `http://localhost:8000`).

### Permission denied (storage / bootstrap/cache)

```bash
chmod -R 775 storage bootstrap/cache
```

Di Linux, jika perlu:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
```

### Error saat migrate (tabel sudah ada / constraint)

- Development: bisa pakai **`php artisan migrate:fresh --seed`** (akan drop semua tabel dan isi ulang; data hilang).
- Production: jangan pakai `migrate:fresh`. Backup dulu, lalu perbaiki migrasi atau data sesuai kebutuhan.

### Port 8000 sudah dipakai

Jalankan di port lain:

```bash
php artisan serve --port=8080
```

Lalu akses **http://localhost:8080** dan sesuaikan **APP_URL** di `.env** jika perlu.

---

## Ringkasan Urutan Setup

1. **Database:** Buat database MySQL (misalnya `sibesti`), pastikan MySQL berjalan.
2. **Backend:** `composer install` → copy `.env` → edit DB_* → `php artisan key:generate` → `php artisan migrate` (atau `migrate:fresh --seed`).
3. **Frontend:** `npm install` → development: `npm run dev` (tetap jalan) / production: `npm run build`.
4. **Jalankan:** `php artisan serve` → buka http://localhost:8000 → login dengan akun default.

Dengan mengikuti tata cara di atas, frontend (Blade + Vite), backend (Laravel), dan database SIBESTI siap dipakai.
